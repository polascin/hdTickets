<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Models\DataExportRequest;
use App\Models\DeletedUser;
use App\Services\AccountDeletionProtectionService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountDeletionController extends Controller
{
    protected AccountDeletionProtectionService $deletionService;

    public function __construct(AccountDeletionProtectionService $deletionService)
    {
        $this->deletionService = $deletionService;
    }

    /**
     * Show account deletion warning page
     */
    /**
     * ShowWarning
     */
    public function showWarning(Request $request): View
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'User must be authenticated');
        }

        $currentDeletionRequest = $user->getCurrentDeletionRequest();

        // Get recent data export requests
        $recentExports = $user->dataExportRequests()
            ->latest()
            ->limit(5)
            ->get();

        return view('account.deletion.warning', [
            'user'                   => $user,
            'currentDeletionRequest' => $currentDeletionRequest,
            'recentExports'          => $recentExports,
        ]);
    }

    /**
     * Initiate account deletion process
     */
    /**
     * Initiate
     */
    public function initiate(Request $request): RedirectResponse
    {
        $request->validate([
            'password'                => ['required', 'current_password'],
            'confirm_deletion'        => ['required', 'accepted'],
            'understand_consequences' => ['required', 'accepted'],
        ]);

        $user = $request->user();

        if (! $user) {
            abort(401, 'User must be authenticated');
        }

        try {
            $deletionRequest = $this->deletionService->initiateDeletion($user);

            return redirect()->route('account.deletion.warning')->with(
                'success',
                'Account deletion initiated. Please check your email to confirm the deletion.',
            );
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Confirm deletion via email token
     */
    /**
     * Confirm
     */
    public function confirm(Request $request, string $token): View|RedirectResponse
    {
        try {
            $this->deletionService->confirmDeletion($token);

            $deletionRequest = AccountDeletionRequest::where('confirmation_token', $token)->first();

            return view('account.deletion.confirmed', [
                'deletionRequest' => $deletionRequest,
            ]);
        } catch (Exception $e) {
            return view('account.deletion.error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show cancellation page
     */
    /**
     * ShowCancel
     */
    public function showCancel(Request $request, string $token): View
    {
        $deletionRequest = AccountDeletionRequest::where('confirmation_token', $token)
            ->active()
            ->first();

        if (! $deletionRequest) {
            return view('account.deletion.error', [
                'error' => 'Invalid or expired cancellation token.',
            ]);
        }

        return view('account.deletion.cancel', [
            'deletionRequest' => $deletionRequest,
            'token'           => $token,
        ]);
    }

    /**
     * Cancel deletion request
     */
    /**
     * Check if can cel
     */
    public function cancel(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $deletionRequest = AccountDeletionRequest::where('confirmation_token', $token)
            ->active()
            ->first();

        if (! $deletionRequest) {
            return redirect()->route('login')->withErrors([
                'error' => 'Invalid or expired cancellation token.',
            ]);
        }

        try {
            $this->deletionService->cancelDeletion($deletionRequest, $request->input('reason'));

            return redirect()->route('login')->with(
                'success',
                'Account deletion has been cancelled. Your account is safe.',
            );
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Request data export
     */
    /**
     * RequestDataExport
     */
    public function requestDataExport(Request $request): RedirectResponse
    {
        $request->validate([
            'format'       => ['required', 'in:json,csv'],
            'data_types'   => ['array'],
            'data_types.*' => ['string'],
        ]);

        $user = $request->user();

        if (! $user) {
            abort(401, 'User must be authenticated');
        }

        $format = $request->input('format', 'json');
        $dataTypes = $request->input('data_types', ['all']);

        try {
            $exportRequest = $this->deletionService->createDataExport($user, $format, $dataTypes);

            return back()->with(
                'success',
                'Data export request created. You will be notified when it\'s ready for download.',
            );
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Download data export
     */
    /**
     * DownloadExport
     */
    public function downloadExport(Request $request, DataExportRequest $exportRequest): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'User must be authenticated');
        }

        if ($exportRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized access to export.');
        }

        if (! $exportRequest->isAvailableForDownload()) {
            return back()->withErrors([
                'error' => 'Export is not available for download or has expired.',
            ]);
        }

        return redirect($exportRequest->getDownloadUrl());
    }

    /**
     * Show account recovery page
     */
    /**
     * ShowRecovery
     */
    public function showRecovery(): View
    {
        return view('account.deletion.recovery');
    }

    /**
     * Process account recovery
     */
    /**
     * Recover
     */
    public function recover(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'email'   => ['required', 'email'],
        ]);

        $userId = $request->input('user_id');
        $email = $request->input('email');

        // Verify that the deleted user exists and email matches
        $deletedUser = DeletedUser::where('original_user_id', $userId)
            ->recoverable()
            ->first();

        if (! $deletedUser || $deletedUser->user_data['email'] !== $email) {
            return back()->withErrors([
                'error' => 'Account not found or recovery period has expired.',
            ]);
        }

        try {
            $user = $this->deletionService->recoverAccount($userId);

            Auth::login($user);

            return redirect()->route('profile.show')->with(
                'success',
                'Your account has been successfully recovered!',
            );
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show deletion audit log
     */
    /**
     * AuditLog
     */
    public function auditLog(Request $request): View
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'User must be authenticated');
        }

        $auditLogs = $user->deletionAuditLogs()
            ->latest('occurred_at')
            ->paginate(20);

        return view('account.deletion.audit-log', [
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Admin: View all deletion requests
     */
    /**
     * AdminIndex
     */
    public function adminIndex(Request $request): View
    {
        $this->authorize('manage_users');

        $query = AccountDeletionRequest::with('user');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by user email/name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search): void {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $deletionRequests = $query->latest('created_at')->paginate(20);

        return view('admin.account-deletion.index', [
            'deletionRequests' => $deletionRequests,
            'statuses'         => AccountDeletionRequest::getStatuses(),
        ]);
    }

    /**
     * Admin: View specific deletion request
     */
    /**
     * AdminShow
     */
    public function adminShow(Request $request, AccountDeletionRequest $deletionRequest): View
    {
        $this->authorize('manage_users');

        $deletionRequest->load('user');

        $auditLogs = $deletionRequest->user?->deletionAuditLogs()
            ?->latest('occurred_at')
            ?->get() ?? collect();

        return view('admin.account-deletion.show', [
            'deletionRequest' => $deletionRequest,
            'auditLogs'       => $auditLogs,
        ]);
    }
}
