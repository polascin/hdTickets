<?php declare(strict_types=1);

namespace App\Services;

use App\Mail\AccountDeletionCancelledMail;
use App\Mail\AccountDeletionCompletedMail;
use App\Mail\AccountDeletionConfirmationMail;
use App\Mail\AccountDeletionWarningMail;
use App\Models\AccountDeletionAuditLog;
use App\Models\AccountDeletionRequest;
use App\Models\DataExportRequest;
use App\Models\DeletedUser;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function is_array;

class AccountDeletionProtectionService
{
    public function __construct(protected DataExportService $dataExportService)
    {
    }

    /**
     * Initiate account deletion process
     */
    /**
     * InitiateDeletion
     */
    public function initiateDeletion(User $user): AccountDeletionRequest
    {
        // Check if user already has an active deletion request
        if ($user->hasActiveDeletionRequest()) {
            throw new Exception('User already has an active deletion request.');
        }

        // Update deletion attempt tracking
        $user->update([
            'last_deletion_attempt_at' => now(),
            'deletion_attempt_count'   => ($user->deletion_attempt_count ?? 0) + 1,
        ]);

        DB::beginTransaction();

        try {
            // Create deletion request
            $deletionRequest = $user->deletionRequests()->create([
                'confirmation_token' => $this->generateConfirmationToken(),
                'status'             => AccountDeletionRequest::STATUS_PENDING,
                'user_data_snapshot' => $this->createUserDataSnapshot($user),
                'initiated_at'       => now(),
                'metadata'           => [
                    'ip_address'       => request()->ip(),
                    'user_agent'       => request()->userAgent(),
                    'initiated_reason' => 'User requested account deletion',
                ],
            ]);

            // Log the initiation
            AccountDeletionAuditLog::log(
                $user->id,
                AccountDeletionAuditLog::ACTION_INITIATED,
                'Account deletion process initiated by user',
                [
                    'deletion_request_id' => $deletionRequest->id,
                    'attempt_count'       => $user->deletion_attempt_count,
                ],
            );

            // Send warning email with data export options
            Mail::to($user->email)->send(new AccountDeletionWarningMail($user, $deletionRequest));

            AccountDeletionAuditLog::log(
                $user->id,
                AccountDeletionAuditLog::ACTION_EMAIL_SENT,
                'Deletion warning email sent to user',
                [
                    'email'               => $user->email,
                    'deletion_request_id' => $deletionRequest->id,
                ],
            );

            DB::commit();

            return $deletionRequest;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Confirm deletion request via email token
     */
    /**
     * ConfirmDeletion
     */
    public function confirmDeletion(string $token): bool
    {
        $deletionRequest = AccountDeletionRequest::where('confirmation_token', $token)
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->first();

        if (!$deletionRequest) {
            throw new Exception('Invalid or expired confirmation token.');
        }

        DB::beginTransaction();

        try {
            // Confirm the request and start grace period
            $deletionRequest->confirm();

            // Log the confirmation
            AccountDeletionAuditLog::log(
                $deletionRequest->user_id,
                AccountDeletionAuditLog::ACTION_CONFIRMED,
                'Account deletion confirmed via email',
                [
                    'deletion_request_id'     => $deletionRequest->id,
                    'grace_period_expires_at' => $deletionRequest->grace_period_expires_at->toISOString(),
                ],
                AccountDeletionRequest::STATUS_PENDING,
                AccountDeletionRequest::STATUS_CONFIRMED,
            );

            // Send confirmation email with grace period information
            Mail::to($deletionRequest->user->email)->send(
                new AccountDeletionConfirmationMail($deletionRequest->user, $deletionRequest),
            );

            DB::commit();

            return TRUE;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Cancel deletion request
     */
    /**
     * Check if can cel deletion
     */
    public function cancelDeletion(AccountDeletionRequest $deletionRequest, ?string $reason = NULL): bool
    {
        if (!$deletionRequest->isPending() && !$deletionRequest->isConfirmed()) {
            throw new Exception('Cannot cancel a deletion request that is not pending or confirmed.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $deletionRequest->status;
            $deletionRequest->cancel($reason);

            // Log the cancellation
            AccountDeletionAuditLog::log(
                $deletionRequest->user_id,
                AccountDeletionAuditLog::ACTION_CANCELLED,
                'Account deletion cancelled by user',
                [
                    'deletion_request_id' => $deletionRequest->id,
                    'cancellation_reason' => $reason,
                ],
                $oldStatus,
                AccountDeletionRequest::STATUS_CANCELLED,
            );

            // Send cancellation email
            Mail::to($deletionRequest->user->email)->send(
                new AccountDeletionCancelledMail($deletionRequest->user, $deletionRequest),
            );

            DB::commit();

            return TRUE;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Process expired grace period deletions
     */
    /**
     * ProcessExpiredDeletions
     */
    public function processExpiredDeletions(): int
    {
        $expiredRequests = AccountDeletionRequest::gracePeriodExpired()->get();
        $processedCount = 0;

        foreach ($expiredRequests as $request) {
            try {
                $this->executeAccountDeletion($request);
                $processedCount++;
            } catch (Exception $e) {
                // Log error but continue processing other requests
                AccountDeletionAuditLog::log(
                    $request->user_id,
                    AccountDeletionAuditLog::ACTION_GRACE_PERIOD_EXPIRED,
                    'Failed to process expired deletion request: ' . $e->getMessage(),
                    [
                        'deletion_request_id' => $request->id,
                        'error'               => $e->getMessage(),
                    ],
                );
            }
        }

        return $processedCount;
    }

    /**
     * Recover deleted user account
     */
    /**
     * RecoverAccount
     */
    public function recoverAccount(int $originalUserId): User
    {
        $deletedUser = DeletedUser::where('original_user_id', $originalUserId)
            ->recoverable()
            ->first();

        if (!$deletedUser) {
            throw new Exception('Account not found or recovery period has expired.');
        }

        DB::beginTransaction();

        try {
            // Restore user data
            $userData = $deletedUser->user_data;
            $userData['deleted_at'] = NULL; // Remove soft delete

            $user = User::withTrashed()->find($originalUserId);
            if ($user) {
                // Restore existing soft-deleted user
                $user->restore();
                $user->fill($userData);
                $user->save();
            } else {
                // Create new user if hard deleted
                $user = User::create($userData);
            }

            // Restore related data (simplified - you may need more complex restoration)
            $relatedData = $deletedUser->related_data;
            // You would implement restoration of related data based on your needs

            // Mark as recovered
            $deletedUser->markRecovered();

            // Log the recovery
            AccountDeletionAuditLog::log(
                $user->id,
                AccountDeletionAuditLog::ACTION_RECOVERED,
                'Account recovered from deletion',
                [
                    'recovered_from_id'       => $deletedUser->id,
                    'recovery_days_remaining' => $deletedUser->recoverable_until->diffInDays(now()),
                ],
            );

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Create data export for user
     */
    /**
     * CreateDataExport
     */
    public function createDataExport(User $user, string $format = 'json', array $dataTypes = ['all']): DataExportRequest
    {
        return $user->dataExportRequests()->create([
            'export_type' => DataExportRequest::EXPORT_TYPE_FULL,
            'data_types'  => $dataTypes,
            'format'      => $format,
            'status'      => DataExportRequest::STATUS_PENDING,
            'expires_at'  => now()->addDays(7),
        ]);
    }

    /**
     * Process data export request
     */
    /**
     * ProcessDataExport
     */
    public function processDataExport(DataExportRequest $exportRequest): bool
    {
        if (!$exportRequest->isPending()) {
            return FALSE;
        }

        $exportRequest->markAsProcessing();

        try {
            $user = $exportRequest->user;

            // Collect user data
            $userData = [
                'profile'           => $user->getEnhancedUserInfo(),
                'preferences'       => $user->preferences ?? [],
                'favorite_teams'    => $user->favoriteTeams ?? [],
                'favorite_venues'   => $user->favoriteVenues ?? [],
                'price_preferences' => $user->pricePreferences ?? [],
                'subscriptions'     => $user->subscriptions()->get(),
                'login_history'     => $user->loginHistory()->latest()->limit(100)->get(),
                'export_metadata'   => [
                    'exported_at'   => now()->toISOString(),
                    'export_format' => $exportRequest->format,
                    'user_id'       => $user->id,
                ],
            ];

            // Generate file
            $filename = "user_data_export_{$user->id}_" . now()->format('Y_m_d_H_i_s');

            if ($exportRequest->format === 'json') {
                $content = json_encode($userData, JSON_PRETTY_PRINT);
                $filePath = "exports/user-data/{$filename}.json";
            } else {
                // CSV format - flatten the data
                $content = $this->convertToCSV($userData);
                $filePath = "exports/user-data/{$filename}.csv";
            }

            // Store file
            Storage::put($filePath, $content);
            $fileSize = Storage::size($filePath);

            // Mark as completed
            $exportRequest->markAsCompleted($filePath, $fileSize);

            // Log the export
            AccountDeletionAuditLog::log(
                $user->id,
                AccountDeletionAuditLog::ACTION_DATA_EXPORTED,
                'User data export completed',
                [
                    'export_request_id' => $exportRequest->id,
                    'file_size'         => $fileSize,
                    'format'            => $exportRequest->format,
                ],
            );

            return TRUE;
        } catch (Exception $e) {
            $exportRequest->markAsFailed($e->getMessage());

            return FALSE;
        }
    }

    /**
     * Clean up expired data export files
     */
    /**
     * CleanupExpiredExports
     */
    public function cleanupExpiredExports(): int
    {
        $expiredExports = DataExportRequest::expired()->get();
        $cleanedCount = 0;

        foreach ($expiredExports as $export) {
            if ($export->deleteFile()) {
                $cleanedCount++;
            }
        }

        return $cleanedCount;
    }

    /**
     * Execute actual account deletion
     */
    /**
     * ExecuteAccountDeletion
     */
    protected function executeAccountDeletion(AccountDeletionRequest $deletionRequest): void
    {
        $user = $deletionRequest->user;

        if (!$user) {
            throw new Exception('User not found for deletion request.');
        }

        DB::beginTransaction();

        try {
            // Create backup of user data before deletion
            $this->createUserBackup($user, $deletionRequest);

            // Soft delete the user
            $user->delete();

            // Mark deletion request as completed
            $deletionRequest->markCompleted();

            // Log the completion
            AccountDeletionAuditLog::log(
                $user->id,
                AccountDeletionAuditLog::ACTION_COMPLETED,
                'Account deletion completed after grace period expiry',
                [
                    'deletion_request_id' => $deletionRequest->id,
                    'deleted_at'          => now()->toISOString(),
                ],
            );

            // Send final deletion notification
            Mail::to($user->email)->send(
                new AccountDeletionCompletedMail($user, $deletionRequest),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Create user data backup for recovery
     */
    /**
     * CreateUserBackup
     */
    protected function createUserBackup(User $user, AccountDeletionRequest $deletionRequest): DeletedUser
    {
        // Collect related data
        $relatedData = [
            'preferences'         => $user->preferences ?? [],
            'favorite_teams'      => $user->favoriteTeams ?? [],
            'favorite_venues'     => $user->favoriteVenues ?? [],
            'price_preferences'   => $user->pricePreferences ?? [],
            'subscriptions'       => $user->subscriptions()->get()->toArray(),
            'login_history'       => $user->loginHistory()->latest()->limit(50)->get()->toArray(),
            'deletion_audit_logs' => $user->deletionAuditLogs()->get()->toArray(),
        ];

        return DeletedUser::create([
            'original_user_id'  => $user->id,
            'user_data'         => $user->toArray(),
            'related_data'      => $relatedData,
            'deletion_reason'   => 'User requested account deletion',
            'deleted_at'        => now(),
            'recoverable_until' => now()->addDays(30), // 30-day recovery period
        ]);
    }

    /**
     * Generate unique confirmation token
     */
    /**
     * GenerateConfirmationToken
     */
    protected function generateConfirmationToken(): string
    {
        do {
            $token = Str::random(64);
        } while (AccountDeletionRequest::where('confirmation_token', $token)->exists());

        return $token;
    }

    /**
     * Create user data snapshot for deletion request
     */
    /**
     * CreateUserDataSnapshot
     */
    protected function createUserDataSnapshot(User $user): array
    {
        return [
            'user_data'           => $user->toArray(),
            'enhanced_info'       => $user->getEnhancedUserInfo(),
            'permissions'         => $user->getPermissions(),
            'snapshot_created_at' => now()->toISOString(),
        ];
    }

    /**
     * Convert array data to CSV format
     */
    /**
     * ConvertToCSV
     */
    protected function convertToCSV(array $data): string
    {
        $output = '';
        $this->arrayToCSV($data, $output);

        return $output;
    }

    /**
     * Recursively convert array to CSV
     */
    /**
     * ArrayToCSV
     *
     * @param mixed $output
     */
    protected function arrayToCSV(array $data, string &$output, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix !== '' && $prefix !== '0' ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->arrayToCSV($value, $output, $fullKey);
            } else {
                $output .= "\"{$fullKey}\",\"" . str_replace('"', '""', $value) . "\"\n";
            }
        }
    }
}
