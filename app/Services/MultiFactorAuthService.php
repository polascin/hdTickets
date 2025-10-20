<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OTPHP\TOTP;
use RuntimeException;

use function count;
use function strlen;

/**
 * MultiFactorAuthService
 *
 * Comprehensive MFA service that provides:
 * - Google Authenticator (TOTP) integration
 * - SMS backup verification
 * - Recovery codes generation and validation
 * - Device trust management
 * - MFA enforcement policies
 * - Security event logging
 */
class MultiFactorAuthService
{
    private const BACKUP_CODE_LENGTH = 8;

    private const BACKUP_CODE_COUNT = 10;

    private const TOTP_WINDOW = 30; // seconds

    private const TOTP_LEEWAY = 1; // Allow 1 step before/after for time sync issues

    private const RATE_LIMIT_ATTEMPTS = 5;

    private const RATE_LIMIT_WINDOW = 300; // 5 minutes

    private const TRUSTED_DEVICE_DURATION = 2592000;

    public function __construct(protected SecurityMonitoringService $securityMonitoring)
    {
    }

    /**
     * Generate MFA setup data for a user including QR code and backup codes
     */
    public function generateSetup(User $user): array
    {
        try {
            // Generate secret key
            $secret = $this->generateSecretKey();

            // Create TOTP instance
            $totp = TOTP::create($secret);
            $totp->setLabel($user->email);
            $totp->setIssuer(config('app.name', 'HD Tickets'));
            $totp->setPeriod(self::TOTP_WINDOW);

            // Generate QR code
            $qrCodeUrl = $this->generateQRCode($totp->getProvisioningUri());

            // Generate backup codes
            $backupCodes = $this->generateBackupCodes($user);

            // Store setup data temporarily (don't enable MFA until confirmed)
            Cache::put("mfa_setup_{$user->id}", [
                'secret'       => $secret,
                'backup_codes' => $backupCodes,
                'created_at'   => now()->toISOString(),
            ], 900); // 15 minutes to complete setup

            Log::info('MFA setup initiated', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);

            return [
                'secret_key'   => $secret,
                'qr_code_url'  => $qrCodeUrl,
                'backup_codes' => $backupCodes,
                'issuer'       => config('app.name', 'HD Tickets'),
                'account'      => $user->email,
            ];
        } catch (Exception $e) {
            Log::error('MFA setup generation failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Failed to generate MFA setup data', $e->getCode(), $e);
        }
    }

    /**
     * Verify MFA code and confirm setup
     */
    public function verifyAndConfirmSetup(User $user, string $code): bool
    {
        try {
            $setupData = Cache::get("mfa_setup_{$user->id}");

            if (! $setupData) {
                Log::warning('MFA setup verification attempted without active setup', [
                    'user_id' => $user->id,
                ]);

                return FALSE;
            }

            // Verify the code
            if ($this->verifyTOTPCode($setupData['secret'], $code)) {
                // Enable MFA for the user
                $user->update([
                    'mfa_enabled'      => TRUE,
                    'mfa_secret'       => encrypt($setupData['secret']),
                    'mfa_backup_codes' => encrypt(json_encode($setupData['backup_codes'])),
                    'mfa_enabled_at'   => now(),
                ]);

                // Clear setup cache
                Cache::forget("mfa_setup_{$user->id}");

                // Log security event
                $this->securityMonitoring->logSecurityEvent(
                    'mfa_enabled',
                    $user,
                    request(),
                    ['setup_method' => 'totp'],
                );

                Log::info('MFA enabled for user', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);

                return TRUE;
            }

            // Log failed verification
            $this->logFailedMFAAttempt($user, $code, 'setup_verification');

            return FALSE;
        } catch (Exception $e) {
            Log::error('MFA setup verification failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Verify MFA code during login
     */
    public function verifyCode(User $user, string $code, ?string $backupCode = NULL): bool
    {
        if (! $user->mfa_enabled) {
            return TRUE; // MFA not enabled for user
        }

        // Check rate limiting
        if (! $this->checkRateLimit($user)) {
            $this->securityMonitoring->logSecurityEvent(
                'mfa_rate_limit_exceeded',
                $user,
                request(),
                ['attempts_exceeded' => self::RATE_LIMIT_ATTEMPTS],
            );

            return FALSE;
        }

        try {
            // First try backup code if provided
            if ($backupCode && $this->verifyBackupCode($user, $backupCode)) {
                $this->resetRateLimit($user);
                $this->logSuccessfulMFAAttempt($user, 'backup_code');

                return TRUE;
            }

            // Then try TOTP code
            if ($code && $this->verifyUserTOTPCode($user, $code)) {
                $this->resetRateLimit($user);
                $this->logSuccessfulMFAAttempt($user, 'totp');

                return TRUE;
            }

            // Log failed attempt
            $this->logFailedMFAAttempt($user, $code, 'login_verification');
            $this->incrementRateLimit($user);

            return FALSE;
        } catch (Exception $e) {
            Log::error('MFA verification failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Disable MFA for a user
     */
    public function disableMFA(User $user, User $disabledBy, string $reason = ''): bool
    {
        try {
            if (! $user->mfa_enabled) {
                return TRUE;
            }

            $user->update([
                'mfa_enabled'         => FALSE,
                'mfa_secret'          => NULL,
                'mfa_backup_codes'    => NULL,
                'mfa_disabled_at'     => now(),
                'mfa_disabled_by'     => $disabledBy->id,
                'mfa_disabled_reason' => $reason,
            ]);

            // Clear rate limiting
            $this->resetRateLimit($user);

            // Remove trusted devices
            $this->revokeAllTrustedDevices($user);

            // Log security event
            $this->securityMonitoring->logSecurityEvent(
                'mfa_disabled',
                $user,
                request(),
                [
                    'disabled_by'       => $disabledBy->id,
                    'disabled_by_email' => $disabledBy->email,
                    'reason'            => $reason,
                ],
            );

            Log::info('MFA disabled for user', [
                'user_id'     => $user->id,
                'disabled_by' => $disabledBy->id,
                'reason'      => $reason,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('MFA disable failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Generate new backup codes for a user
     */
    public function generateBackupCodes(User $user, int $count = self::BACKUP_CODE_COUNT): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(self::BACKUP_CODE_LENGTH));
        }

        // If MFA is already enabled, store the new codes
        if ($user->mfa_enabled) {
            $user->update([
                'mfa_backup_codes'              => encrypt(json_encode($codes)),
                'mfa_backup_codes_generated_at' => now(),
            ]);

            $this->securityMonitoring->logSecurityEvent(
                'mfa_backup_codes_regenerated',
                $user,
                request(),
                ['codes_count' => $count],
            );
        }

        return $codes;
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        try {
            if (! $user->mfa_enabled || ! $user->mfa_backup_codes) {
                return FALSE;
            }

            $backupCodes = json_decode((string) decrypt($user->mfa_backup_codes), TRUE);
            $codeIndex = array_search(strtoupper($code), $backupCodes, TRUE);

            if ($codeIndex !== FALSE) {
                // Remove used backup code
                unset($backupCodes[$codeIndex]);
                $backupCodes = array_values($backupCodes); // Reindex array

                // Update user's backup codes
                $user->update([
                    'mfa_backup_codes' => encrypt(json_encode($backupCodes)),
                ]);

                // Log security event
                $this->securityMonitoring->logSecurityEvent(
                    'mfa_backup_code_used',
                    $user,
                    request(),
                    ['remaining_codes' => count($backupCodes)],
                );

                return TRUE;
            }

            return FALSE;
        } catch (Exception $e) {
            Log::error('Backup code verification failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Trust a device for a user
     *
     * @return string Device token
     */
    public function trustDevice(User $user, string $deviceName, array $deviceInfo = []): string
    {
        try {
            $deviceToken = Str::random(64);
            $deviceFingerprint = $this->generateDeviceFingerprint($deviceInfo);

            DB::table('trusted_devices')->insert([
                'user_id'            => $user->id,
                'device_name'        => $deviceName,
                'device_token'       => Hash::make($deviceToken),
                'device_fingerprint' => $deviceFingerprint,
                'device_info'        => json_encode($deviceInfo),
                'trusted_at'         => now(),
                'expires_at'         => now()->addSeconds(self::TRUSTED_DEVICE_DURATION),
                'last_used_at'       => now(),
            ]);

            $this->securityMonitoring->logSecurityEvent(
                'mfa_device_trusted',
                $user,
                request(),
                [
                    'device_name'        => $deviceName,
                    'device_fingerprint' => substr($deviceFingerprint, 0, 8) . '...',
                ],
            );

            return $deviceToken;
        } catch (Exception $e) {
            Log::error('Device trust failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw new RuntimeException('Failed to trust device', $e->getCode(), $e);
        }
    }

    /**
     * Check if device is trusted
     */
    public function isDeviceTrusted(User $user, string $deviceToken): bool
    {
        try {
            $trustedDevices = DB::table('trusted_devices')
                ->where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->get();

            foreach ($trustedDevices as $device) {
                if (Hash::check($deviceToken, $device->device_token)) {
                    // Update last used timestamp
                    DB::table('trusted_devices')
                        ->where('id', $device->id)
                        ->update(['last_used_at' => now()]);

                    return TRUE;
                }
            }

            return FALSE;
        } catch (Exception $e) {
            Log::error('Device trust check failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Revoke all trusted devices for a user
     *
     * @return int Number of devices revoked
     */
    public function revokeAllTrustedDevices(User $user): int
    {
        try {
            $count = DB::table('trusted_devices')
                ->where('user_id', $user->id)
                ->delete();

            if ($count > 0) {
                $this->securityMonitoring->logSecurityEvent(
                    'mfa_trusted_devices_revoked',
                    $user,
                    request(),
                    ['devices_revoked' => $count],
                );
            }

            return $count;
        } catch (Exception $e) {
            Log::error('Trusted device revocation failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get user's MFA status and statistics
     */
    public function getMFAStatus(User $user): array
    {
        try {
            $backupCodesCount = 0;
            if ($user->mfa_enabled && $user->mfa_backup_codes) {
                $backupCodes = json_decode((string) decrypt($user->mfa_backup_codes), TRUE);
                $backupCodesCount = count($backupCodes);
            }

            $trustedDevicesCount = DB::table('trusted_devices')
                ->where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->count();

            return [
                'enabled'               => $user->mfa_enabled,
                'enabled_at'            => $user->mfa_enabled_at,
                'backup_codes_count'    => $backupCodesCount,
                'trusted_devices_count' => $trustedDevicesCount,
                'last_successful_auth'  => $this->getLastSuccessfulMFAAuth(),
                'failed_attempts_today' => $this->getFailedAttemptsToday(),
                'security_score'        => $this->calculateMFASecurityScore($user),
            ];
        } catch (Exception $e) {
            Log::error('MFA status retrieval failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [
                'enabled' => FALSE,
                'error'   => 'Status retrieval failed',
            ];
        }
    }

    /**
     * Send MFA code via SMS (backup method)
     */
    public function sendSMSCode(User $user): bool
    {
        try {
            if (! $user->phone || ! $user->phone_verified) {
                return FALSE;
            }

            // Generate 6-digit code
            $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Store code temporarily
            Cache::put("sms_mfa_{$user->id}", [
                'code'       => Hash::make($code),
                'expires_at' => now()->addMinutes(5),
                'attempts'   => 0,
            ], 300); // 5 minutes

            // Send SMS (implement with your SMS provider)
            $this->sendSMS($user->phone, "Your HD Tickets verification code is: {$code}");

            $this->securityMonitoring->logSecurityEvent(
                'mfa_sms_sent',
                $user,
                request(),
                ['phone' => substr((string) $user->phone, -4)], // Only log last 4 digits
            );

            return TRUE;
        } catch (Exception $e) {
            Log::error('SMS MFA code send failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Verify SMS code
     */
    public function verifySMSCode(User $user, string $code): bool
    {
        try {
            $smsData = Cache::get("sms_mfa_{$user->id}");

            if (! $smsData || now()->gt($smsData['expires_at'])) {
                return FALSE;
            }

            // Check attempt limit
            if ($smsData['attempts'] >= 3) {
                Cache::forget("sms_mfa_{$user->id}");

                return FALSE;
            }

            if (Hash::check($code, $smsData['code'])) {
                Cache::forget("sms_mfa_{$user->id}");

                $this->securityMonitoring->logSecurityEvent(
                    'mfa_sms_verified',
                    $user,
                    request(),
                );

                return TRUE;
            }

            // Increment attempt counter
            $smsData['attempts']++;
            Cache::put("sms_mfa_{$user->id}", $smsData, 300);

            return FALSE;
        } catch (Exception $e) {
            Log::error('SMS code verification failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    // Private helper methods

    private function generateSecretKey(): string
    {
        return trim(base32_encode(random_bytes(20)), '=');
    }

    private function generateQRCode(string $uri): string
    {
        try {
            $options = new QROptions([
                'version'     => 5,
                'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'    => QRCode::ECC_L,
                'scale'       => 5,
                'imageBase64' => TRUE,
            ]);

            $qrcode = new QRCode($options);

            return $qrcode->render($uri);
        } catch (Exception $e) {
            Log::error('QR code generation failed', ['error' => $e->getMessage()]);

            throw new RuntimeException('Failed to generate QR code', $e->getCode(), $e);
        }
    }

    private function verifyTOTPCode(string $secret, string $code): bool
    {
        try {
            $totp = TOTP::create($secret);
            $totp->setPeriod(self::TOTP_WINDOW);

            return $totp->verify($code, NULL, self::TOTP_LEEWAY);
        } catch (Exception $e) {
            Log::error('TOTP verification failed', ['error' => $e->getMessage()]);

            return FALSE;
        }
    }

    private function verifyUserTOTPCode(User $user, string $code): bool
    {
        if (! $user->mfa_secret) {
            return FALSE;
        }

        try {
            $secret = decrypt($user->mfa_secret);

            return $this->verifyTOTPCode($secret, $code);
        } catch (Exception $e) {
            Log::error('User TOTP verification failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    private function checkRateLimit(User $user): bool
    {
        $key = "mfa_attempts_{$user->id}";
        $attempts = Cache::get($key, 0);

        return $attempts < self::RATE_LIMIT_ATTEMPTS;
    }

    private function incrementRateLimit(User $user): void
    {
        $key = "mfa_attempts_{$user->id}";
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, self::RATE_LIMIT_WINDOW);
    }

    private function resetRateLimit(User $user): void
    {
        Cache::forget("mfa_attempts_{$user->id}");
    }

    private function logSuccessfulMFAAttempt(User $user, string $method): void
    {
        $this->securityMonitoring->logSecurityEvent(
            'mfa_verification_success',
            $user,
            request(),
            ['method' => $method],
        );
    }

    private function logFailedMFAAttempt(User $user, string $code, string $context): void
    {
        $this->securityMonitoring->logSecurityEvent(
            'mfa_verification_failed',
            $user,
            request(),
            [
                'context'     => $context,
                'code_length' => strlen($code),
            ],
        );
    }

    private function generateDeviceFingerprint(array $deviceInfo): string
    {
        $fingerprint = implode('|', [
            $deviceInfo['user_agent'] ?? '',
            $deviceInfo['screen_resolution'] ?? '',
            $deviceInfo['timezone'] ?? '',
            $deviceInfo['language'] ?? '',
            request()->ip(),
        ]);

        return hash('sha256', $fingerprint);
    }

    private function getLastSuccessfulMFAAuth(): ?Carbon
    {
        // Implementation would query security events for last successful MFA
        return NULL;
    }

    private function getFailedAttemptsToday(): int
    {
        // Implementation would count failed MFA attempts today
        return 0;
    }

    private function calculateMFASecurityScore(User $user): int
    {
        $score = 0;

        if ($user->mfa_enabled) {
            $score += 40;
        }

        // Add points for backup codes
        if ($user->mfa_backup_codes) {
            $codes = json_decode((string) decrypt($user->mfa_backup_codes), TRUE);
            if (count($codes) >= 5) {
                $score += 20;
            }
        }

        // Add points for trusted devices (but not too many)
        $trustedDevices = DB::table('trusted_devices')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->count();

        $score += min($trustedDevices * 5, 20);

        // Deduct points for recent failures
        $recentFailures = $this->getFailedAttemptsToday();
        $score -= $recentFailures * 5;

        return max(0, min(100, $score));
    }

    private function sendSMS(string $phone, string $message): bool
    {
        // Implement SMS sending with your provider (Twilio, Nexmo, etc.)
        // For now, return true to indicate success
        Log::info('SMS would be sent', [
            'phone'          => substr($phone, -4),
            'message_length' => strlen($message),
        ]);

        return TRUE;
    }
}
