<?php declare(strict_types=1);

namespace App\Services;

use App\Models\TwoFactorBackupCode;
use App\Models\TwoFactorRecovery;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

use function count;
use function sprintf;
use function strlen;

/**
 * Two-Factor Authentication Service
 *
 * Provides comprehensive 2FA functionality including:
 * - Google Authenticator TOTP integration
 * - Backup codes generation and validation
 * - Recovery options and account recovery
 * - Security monitoring and logging
 * - Rate limiting and brute force protection
 */
class TwoFactorAuthenticationService
{
    protected Google2FA $google2fa;

    protected string $appName;

    protected int $codeWindow = 2; // 2 time windows (60 seconds each)

    protected int $maxBackupCodes = 8;

    protected int $maxAttempts = 5;

    protected int $lockoutMinutes = 15;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->appName = config('app.name', 'HD Tickets');
    }

    /**
     * Enable 2FA for a user
     */
    public function enable2FA(User $user): array
    {
        if ($user->two_factor_enabled) {
            throw new Exception('2FA is already enabled for this user');
        }

        // Generate secret key
        $secretKey = $this->google2fa->generateSecretKey();

        // Store temporarily until confirmed
        $user->update([
            'two_factor_secret'       => encrypt($secretKey),
            'two_factor_confirmed_at' => NULL,
            'two_factor_enabled'      => FALSE,
        ]);

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes($user);

        // Generate QR code
        $qrCodeUrl = $this->generateQRCode($user, $secretKey);

        Log::info('2FA setup initiated', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return [
            'secret_key'       => $secretKey,
            'qr_code_url'      => $qrCodeUrl,
            'backup_codes'     => $backupCodes,
            'manual_entry_key' => $secretKey,
        ];
    }

    /**
     * Confirm 2FA setup with verification code
     */
    public function confirm2FA(User $user, string $code): bool
    {
        if ($user->two_factor_enabled) {
            throw new Exception('2FA is already confirmed and enabled');
        }

        if (! $user->two_factor_secret) {
            throw new Exception('2FA setup has not been initiated');
        }

        // Check rate limiting
        if ($this->isRateLimited($user, '2fa_confirm')) {
            throw new Exception('Too many confirmation attempts. Please try again later.');
        }

        $secretKey = decrypt($user->two_factor_secret);

        if ($this->google2fa->verifyKey($secretKey, $code, $this->codeWindow)) {
            // Enable 2FA
            $user->update([
                'two_factor_enabled'      => TRUE,
                'two_factor_confirmed_at' => now(),
            ]);

            // Clear rate limiting
            $this->clearRateLimit($user, '2fa_confirm');

            Log::info('2FA confirmed and enabled', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => request()->ip(),
            ]);

            return TRUE;
        }

        // Record failed attempt
        $this->recordFailedAttempt($user, '2fa_confirm');

        Log::warning('2FA confirmation failed', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => request()->ip(),
        ]);

        return FALSE;
    }

    /**
     * Verify 2FA code for authentication
     */
    public function verify2FA(User $user, string $code): bool
    {
        if (! $user->two_factor_enabled || ! $user->two_factor_secret) {
            return FALSE;
        }

        // Check rate limiting
        if ($this->isRateLimited($user, '2fa_verify')) {
            Log::warning('2FA verification rate limited', [
                'user_id' => $user->id,
                'ip'      => request()->ip(),
            ]);

            return FALSE;
        }

        $secretKey = decrypt($user->two_factor_secret);

        // Verify TOTP code
        if ($this->google2fa->verifyKey($secretKey, $code, $this->codeWindow)) {
            $this->clearRateLimit($user, '2fa_verify');

            Log::info('2FA verification successful', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => request()->ip(),
            ]);

            return TRUE;
        }

        // Check if it's a backup code
        if ($this->verifyBackupCode($user, $code)) {
            $this->clearRateLimit($user, '2fa_verify');

            return TRUE;
        }

        // Record failed attempt
        $this->recordFailedAttempt($user, '2fa_verify');

        Log::warning('2FA verification failed', [
            'user_id'     => $user->id,
            'email'       => $user->email,
            'ip'          => request()->ip(),
            'code_length' => strlen($code),
        ]);

        return FALSE;
    }

    /**
     * Disable 2FA for a user
     */
    public function disable2FA(User $user, string $password): bool
    {
        if (! Hash::check($password, $user->password)) {
            throw new Exception('Invalid password');
        }

        if (! $user->two_factor_enabled) {
            throw new Exception('2FA is not enabled for this user');
        }

        // Disable 2FA
        $user->update([
            'two_factor_enabled'      => FALSE,
            'two_factor_secret'       => NULL,
            'two_factor_confirmed_at' => NULL,
        ]);

        // Remove all backup codes
        $user->twoFactorBackupCodes()->delete();

        // Remove any recovery tokens
        $user->twoFactorRecoveries()->delete();

        Log::warning('2FA disabled', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return TRUE;
    }

    /**
     * Generate backup codes for a user
     */
    public function generateBackupCodes(User $user): array
    {
        // Remove existing backup codes
        $user->twoFactorBackupCodes()->delete();

        $codes = [];
        for ($i = 0; $i < $this->maxBackupCodes; $i++) {
            $code = $this->generateBackupCode();
            $codes[] = $code;

            TwoFactorBackupCode::create([
                'user_id' => $user->id,
                'code'    => Hash::make($code),
                'used_at' => NULL,
            ]);
        }

        Log::info('Backup codes generated', [
            'user_id' => $user->id,
            'count'   => count($codes),
        ]);

        return $codes;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $backupCodes = $user->twoFactorBackupCodes()->whereNull('used_at')->get();

        foreach ($backupCodes as $backupCode) {
            if (Hash::check($code, $backupCode->code)) {
                // Mark as used
                $backupCode->update(['used_at' => now()]);

                Log::info('Backup code used', [
                    'user_id'        => $user->id,
                    'backup_code_id' => $backupCode->id,
                ]);

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get unused backup codes count
     */
    public function getUnusedBackupCodesCount(User $user): int
    {
        return $user->twoFactorBackupCodes()->whereNull('used_at')->count();
    }

    /**
     * Generate recovery code for account recovery
     */
    public function generateRecoveryCode(User $user): string
    {
        // Remove existing recovery codes
        $user->twoFactorRecoveries()->delete();

        $recoveryCode = Str::random(32);
        $expiresAt = now()->addHours(24);

        TwoFactorRecovery::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($recoveryCode),
            'expires_at' => $expiresAt,
            'used_at'    => NULL,
        ]);

        Log::info('Recovery code generated', [
            'user_id'    => $user->id,
            'expires_at' => $expiresAt,
        ]);

        return $recoveryCode;
    }

    /**
     * Verify recovery code and disable 2FA
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recovery = $user->twoFactorRecoveries()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $recovery) {
            return FALSE;
        }

        if (Hash::check($code, $recovery->code)) {
            // Mark recovery as used
            $recovery->update(['used_at' => now()]);

            // Disable 2FA
            $user->update([
                'two_factor_enabled'      => FALSE,
                'two_factor_secret'       => NULL,
                'two_factor_confirmed_at' => NULL,
            ]);

            // Remove backup codes
            $user->twoFactorBackupCodes()->delete();

            Log::warning('2FA disabled via recovery code', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => request()->ip(),
            ]);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get 2FA statistics for a user
     */
    public function get2FAStats(User $user): array
    {
        return [
            'enabled'                => $user->two_factor_enabled,
            'confirmed_at'           => $user->two_factor_confirmed_at?->toISOString(),
            'backup_codes_remaining' => $this->getUnusedBackupCodesCount($user),
            'has_recovery_code'      => $user->twoFactorRecoveries()
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->exists(),
            'last_used' => $this->getLastUsedTime($user),
        ];
    }

    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts(User $user, string $action): int
    {
        $key = "2fa_attempts:{$user->id}:{$action}";
        $attempts = Cache::get($key, 0);

        return max(0, $this->maxAttempts - $attempts);
    }

    /**
     * Get time until lockout expires
     *
     * @return int|null Minutes until lockout expires
     */
    public function getLockoutTimeRemaining(User $user, string $action): ?int
    {
        if (! $this->isRateLimited($user, $action)) {
            return NULL;
        }

        $key = "2fa_attempts:{$user->id}:{$action}";
        $ttl = Cache::getStore()->getRedis()->ttl(config('cache.prefix') . ':' . $key);

        return $ttl > 0 ? ceil($ttl / 60) : NULL;
    }

    /**
     * Validate 2FA setup requirements
     */
    public function canEnable2FA(User $user): bool
    {
        return $user->email_verified_at !== NULL
            && ! $user->two_factor_enabled
            && $user->isActive();
    }

    /**
     * Get 2FA configuration
     */
    public function getConfiguration(): array
    {
        return [
            'app_name'         => $this->appName,
            'code_window'      => $this->codeWindow,
            'max_backup_codes' => $this->maxBackupCodes,
            'max_attempts'     => $this->maxAttempts,
            'lockout_minutes'  => $this->lockoutMinutes,
            'code_length'      => 6,
            'time_step'        => 30, // seconds
        ];
    }

    /**
     * Generate QR code for Google Authenticator
     */
    protected function generateQRCode(User $user, string $secretKey): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $this->appName,
            $user->email,
            $secretKey,
        );

        // If BaconQrCode library isn't installed, return otpauth URL for client-side generation
        if (! class_exists(Writer::class) || ! class_exists(ImageRenderer::class)) {
            return $qrCodeUrl; // Fallback: raw provisioning URI
        }

        try {
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd(),
            );

            $writer = new Writer($renderer);

            return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeUrl));
        } catch (Exception $e) {
            Log::warning('QR code generation failed (falling back to URI)', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return $qrCodeUrl;
        }
    }

    /**
     * Generate a backup code
     */
    protected function generateBackupCode(): string
    {
        return sprintf(
            '%s-%s',
            strtoupper(Str::random(4)),
            strtoupper(Str::random(4)),
        );
    }

    /**
     * Check if user is rate limited for specific action
     */
    protected function isRateLimited(User $user, string $action): bool
    {
        $key = "2fa_attempts:{$user->id}:{$action}";
        $attempts = Cache::get($key, 0);

        return $attempts >= $this->maxAttempts;
    }

    /**
     * Record failed attempt
     */
    protected function recordFailedAttempt(User $user, string $action): void
    {
        $key = "2fa_attempts:{$user->id}:{$action}";
        $attempts = Cache::get($key, 0) + 1;

        Cache::put($key, $attempts, now()->addMinutes($this->lockoutMinutes));

        if ($attempts >= $this->maxAttempts) {
            Log::warning('2FA rate limit exceeded', [
                'user_id'  => $user->id,
                'action'   => $action,
                'attempts' => $attempts,
                'ip'       => request()->ip(),
            ]);
        }
    }

    /**
     * Clear rate limit for user action
     */
    protected function clearRateLimit(User $user, string $action): void
    {
        $key = "2fa_attempts:{$user->id}:{$action}";
        Cache::forget($key);
    }

    /**
     * Get last time 2FA was used
     */
    protected function getLastUsedTime(User $user): ?string
    {
        // This would typically come from a sessions/login log table
        // For now, return null as placeholder
        return NULL;
    }
}
