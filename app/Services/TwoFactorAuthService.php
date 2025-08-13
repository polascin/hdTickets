<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

use function count;
use function in_array;
use function is_array;
use function strlen;

class TwoFactorAuthService
{
    /** @var Google2FA */
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for 2FA
     */
    /**
     * GenerateSecretKey
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for authenticator app setup
     */
    /**
     * Get  q r code url
     */
    public function getQRCodeUrl(User $user, string $secretKey): string
    {
        $companyName = config('app.name', 'HDTickets');

        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secretKey,
        );
    }

    /**
     * Generate QR code SVG for display
     */
    /**
     * Get  q r code svg
     */
    public function getQRCodeSvg(User $user, string $secretKey): string
    {
        $qrCodeUrl = $this->getQRCodeUrl($user, $secretKey);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd(),
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify TOTP code
     */
    /**
     * VerifyCode
     */
    public function verifyCode(string $secretKey, string $code): bool
    {
        return $this->google2fa->verifyKey($secretKey, $code);
    }

    /**
     * Enable 2FA for a user
     */
    /**
     * EnableTwoFactor
     */
    public function enableTwoFactor(User $user, string $secretKey, string $verificationCode): bool
    {
        // Verify the code first
        if (! $this->verifyCode($secretKey, $verificationCode)) {
            return FALSE;
        }

        // Enable 2FA and store the secret
        $user->update([
            'two_factor_secret'         => encrypt($secretKey),
            'two_factor_enabled'        => TRUE,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ]);

        // Log the 2FA activation
        activity('two_factor_enabled')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'method'     => 'authenticator_app',
            ])
            ->log('Two-factor authentication enabled');

        return TRUE;
    }

    /**
     * Disable 2FA for a user
     */
    /**
     * DisableTwoFactor
     */
    public function disableTwoFactor(User $user): bool
    {
        $user->update([
            'two_factor_secret'         => NULL,
            'two_factor_enabled'        => FALSE,
            'two_factor_confirmed_at'   => NULL,
            'two_factor_recovery_codes' => NULL,
        ]);

        // Log the 2FA deactivation
        activity('two_factor_disabled')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Two-factor authentication disabled');

        return TRUE;
    }

    /**
     * Generate recovery codes
     */
    /**
     * @return array<string>
     */
    /**
     * GenerateRecoveryCodes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        return $codes;
    }

    /**
     * Regenerate recovery codes
     */
    /**
     * @return array<string>
     */
    /**
     * RegenerateRecoveryCodes
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $newCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($newCodes)),
        ]);

        // Log recovery codes regeneration
        activity('two_factor_recovery_codes_regenerated')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Two-factor recovery codes regenerated');

        return $newCodes;
    }

    /**
     * Verify recovery code
     */
    /**
     * VerifyRecoveryCode
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return FALSE;
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), TRUE);

        if (! is_array($recoveryCodes) || ! in_array(strtoupper($code), $recoveryCodes, TRUE)) {
            return FALSE;
        }

        // Remove the used recovery code
        $updatedCodes = array_diff($recoveryCodes, [strtoupper($code)]);

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($updatedCodes))),
        ]);

        // Log recovery code usage
        activity('two_factor_recovery_code_used')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
                'remaining_codes' => count($updatedCodes),
            ])
            ->log('Two-factor recovery code used');

        return TRUE;
    }

    /**
     * Get remaining recovery codes count
     */
    /**
     * Get  remaining recovery codes count
     */
    public function getRemainingRecoveryCodesCount(User $user): int
    {
        if (! $user->two_factor_recovery_codes) {
            return 0;
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), TRUE);

        return is_array($recoveryCodes) ? count($recoveryCodes) : 0;
    }

    /**
     * Send SMS 2FA code (backup method)
     */
    /**
     * SendSmsCode
     */
    public function sendSmsCode(User $user): bool
    {
        if (! $user->phone) {
            return FALSE;
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 5 minutes
        Cache::put(
            "sms_2fa_code:{$user->id}",
            $code,
            now()->addMinutes(5),
        );

        // In a real implementation, integrate with SMS service like Twilio
        // For now, we'll just log it (in production, remove this and implement SMS)
        activity('two_factor_sms_sent')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'phone'      => $user->phone,
                'ip_address' => request()->ip(),
                'code'       => $code, // Remove this in production
            ])
            ->log('Two-factor SMS code sent');

        return TRUE;
    }

    /**
     * Verify SMS 2FA code
     */
    /**
     * VerifySmsCode
     */
    public function verifySmsCode(User $user, string $code): bool
    {
        $storedCode = Cache::get("sms_2fa_code:{$user->id}");

        if (! $storedCode || $storedCode !== $code) {
            return FALSE;
        }

        // Remove the code after successful verification
        Cache::forget("sms_2fa_code:{$user->id}");

        // Log successful SMS verification
        activity('two_factor_sms_verified')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Two-factor SMS code verified');

        return TRUE;
    }

    /**
     * Send email 2FA code (backup method)
     */
    /**
     * SendEmailCode
     */
    public function sendEmailCode(User $user): bool
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        Cache::put(
            "email_2fa_code:{$user->id}",
            $code,
            now()->addMinutes(10),
        );

        try {
            // Send email with 2FA code
            Mail::send('emails.two-factor-code', [
                'user'      => $user,
                'code'      => $code,
                'expiresIn' => 10,
            ], function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject('[' . config('app.name') . '] Two-Factor Authentication Code');
            });

            // Log email sent
            activity('two_factor_email_sent')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'email'      => $user->email,
                    'ip_address' => request()->ip(),
                ])
                ->log('Two-factor email code sent');

            return TRUE;
        } catch (Exception $e) {
            // Log email sending failure
            activity('two_factor_email_failed')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'email'      => $user->email,
                    'error'      => $e->getMessage(),
                    'ip_address' => request()->ip(),
                ])
                ->log('Two-factor email code sending failed');

            return FALSE;
        }
    }

    /**
     * Verify email 2FA code
     */
    /**
     * VerifyEmailCode
     */
    public function verifyEmailCode(User $user, string $code): bool
    {
        $storedCode = Cache::get("email_2fa_code:{$user->id}");

        if (! $storedCode || $storedCode !== $code) {
            return FALSE;
        }

        // Remove the code after successful verification
        Cache::forget("email_2fa_code:{$user->id}");

        // Log successful email verification
        activity('two_factor_email_verified')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Two-factor email code verified');

        return TRUE;
    }

    /**
     * Check if user has 2FA enabled
     */
    /**
     * Check if  enabled
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && $user->two_factor_secret;
    }

    /**
     * Get user's 2FA secret (decrypted)
     */
    /**
     * Get  secret
     */
    public function getSecret(User $user): ?string
    {
        if (! $user->two_factor_secret) {
            return NULL;
        }

        try {
            return decrypt($user->two_factor_secret);
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Get user's recovery codes (decrypted)
     */
    /**
     * @return array<string>
     */
    /**
     * Get  recovery codes
     */
    public function getRecoveryCodes(User $user): array
    {
        if (! $user->two_factor_recovery_codes) {
            return [];
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), TRUE);

            return is_array($codes) ? $codes : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Validate 2FA setup requirements
     */
    /**
     * @return array<string, mixed>
     */
    /**
     * ValidateSetupRequirements
     */
    public function validateSetupRequirements(User $user): array
    {
        $requirements = [
            'has_email'      => ! empty($user->email),
            'email_verified' => NULL !== $user->email_verified_at,
            'has_phone'      => ! empty($user->phone),
            'account_secure' => NULL !== $user->password && strlen($user->password) > 8,
        ];

        $requirements['all_met'] = array_reduce($requirements, function ($carry, $item) {
            return $carry && $item;
        }, TRUE);

        return $requirements;
    }

    /**
     * Get 2FA statistics for admin dashboard
     */
    /**
     * @return array<string, mixed>
     */
    /**
     * Get  two factor stats
     */
    public function getTwoFactorStats(): array
    {
        $totalUsers = User::count();
        $enabledUsers = User::where('two_factor_enabled', TRUE)->count();
        $confirmedUsers = User::whereNotNull('two_factor_confirmed_at')->count();

        return [
            'total_users'          => $totalUsers,
            'enabled_users'        => $enabledUsers,
            'confirmed_users'      => $confirmedUsers,
            'enabled_percentage'   => $totalUsers > 0 ? round(($enabledUsers / $totalUsers) * 100, 1) : 0,
            'confirmed_percentage' => $totalUsers > 0 ? round(($confirmedUsers / $totalUsers) * 100, 1) : 0,
            'adoption_rate'        => $totalUsers > 0 ? round(($confirmedUsers / $totalUsers) * 100, 1) : 0,
        ];
    }

    /**
     * Generate backup codes for admin emergency access
     */
    /**
     * @return array<string>
     */
    /**
     * GenerateAdminBackupCodes
     */
    public function generateAdminBackupCodes(User $admin, User $targetUser): array
    {
        if (! $admin->isAdmin()) {
            throw new Exception('Only administrators can generate backup codes.');
        }

        $backupCodes = $this->generateRecoveryCodes();

        // Store backup codes with admin attribution
        Cache::put(
            "admin_backup_codes:{$targetUser->id}",
            [
                'codes'        => $backupCodes,
                'generated_by' => $admin->id,
                'generated_at' => now(),
            ],
            now()->addHours(24), // Valid for 24 hours
        );

        // Log admin backup code generation
        activity('admin_backup_codes_generated')
            ->performedOn($targetUser)
            ->causedBy($admin)
            ->withProperties([
                'target_user_id' => $targetUser->id,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ])
            ->log('Admin backup codes generated for user 2FA');

        return $backupCodes;
    }

    /**
     * Verify admin backup code
     */
    /**
     * VerifyAdminBackupCode
     */
    public function verifyAdminBackupCode(User $user, string $code): bool
    {
        $backupData = Cache::get("admin_backup_codes:{$user->id}");

        if (! $backupData || ! isset($backupData['codes'])) {
            return FALSE;
        }

        if (! in_array(strtoupper($code), $backupData['codes'], TRUE)) {
            return FALSE;
        }

        // Remove the used code and update cache
        $updatedCodes = array_diff($backupData['codes'], [strtoupper($code)]);
        $backupData['codes'] = array_values($updatedCodes);

        if (count($updatedCodes) > 0) {
            Cache::put("admin_backup_codes:{$user->id}", $backupData, now()->addHours(24));
        } else {
            Cache::forget("admin_backup_codes:{$user->id}");
        }

        // Log admin backup code usage
        activity('admin_backup_code_used')
            ->performedOn($user)
            ->causedBy(User::find($backupData['generated_by']))
            ->withProperties([
                'target_user_id'  => $user->id,
                'remaining_codes' => count($updatedCodes),
                'ip_address'      => request()->ip(),
            ])
            ->log('Admin backup code used for user 2FA');

        return TRUE;
    }
}
