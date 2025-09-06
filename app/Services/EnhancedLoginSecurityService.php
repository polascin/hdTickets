<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\TrustedDevice;
use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Enhanced Login Security Service
 * 
 * Provides advanced login security features including:
 * - Device fingerprinting and trusted device management
 * - Geolocation-based suspicious activity detection
 * - Account lockout and rate limiting
 * - Automated threat detection and response
 * - Comprehensive security event logging
 */
class EnhancedLoginSecurityService
{
    protected int $maxFailedAttempts = 5;
    protected int $lockoutDuration = 900; // 15 minutes
    protected int $suspiciousThreshold = 3;
    protected array $trustedCountries = ['US', 'CA', 'GB', 'AU']; // Example trusted countries
    
    public function __construct()
    {
        $this->maxFailedAttempts = config('security.max_failed_attempts', 5);
        $this->lockoutDuration = config('security.lockout_duration', 900);
        $this->suspiciousThreshold = config('security.suspicious_threshold', 3);
        $this->trustedCountries = config('security.trusted_countries', ['US', 'CA', 'GB', 'AU']);
    }

    /**
     * Validate login attempt with enhanced security checks
     *
     * @param User $user
     * @param Request $request
     * @return array
     */
    public function validateLoginAttempt(User $user, Request $request): array
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        // Check if account is locked
        if ($this->isAccountLocked($user)) {
            $this->logSecurityEvent($user, 'login_attempt_locked', [
                'ip' => $ipAddress,
                'user_agent' => $userAgent
            ]);

            return [
                'allowed' => false,
                'reason' => 'account_locked',
                'lockout_expires' => $this->getLockoutExpiration($user),
                'requires_2fa' => false
            ];
        }

        // Check rate limiting
        if ($this->isRateLimited($ipAddress)) {
            $this->logSecurityEvent($user, 'login_rate_limited', [
                'ip' => $ipAddress,
                'attempts' => $this->getFailedAttempts($ipAddress)
            ]);

            return [
                'allowed' => false,
                'reason' => 'rate_limited',
                'retry_after' => $this->getRateLimitRetryAfter($ipAddress),
                'requires_2fa' => false
            ];
        }

        // Perform geolocation check
        $geoCheck = $this->performGeolocationCheck($user, $ipAddress);
        
        // Check device trust
        $deviceCheck = $this->checkDeviceTrust($user, $deviceFingerprint);
        
        // Determine if 2FA is required
        $requires2FA = $this->requires2FA($user, $geoCheck, $deviceCheck);

        // Check for suspicious activity
        $suspiciousActivity = $this->detectSuspiciousActivity($user, $request);

        return [
            'allowed' => true,
            'requires_2fa' => $requires2FA,
            'device_trusted' => $deviceCheck['trusted'],
            'location_suspicious' => !$geoCheck['trusted'],
            'suspicious_activity' => $suspiciousActivity,
            'security_score' => $this->calculateSecurityScore($geoCheck, $deviceCheck, $suspiciousActivity),
            'recommendations' => $this->getSecurityRecommendations($geoCheck, $deviceCheck, $suspiciousActivity)
        ];
    }

    /**
     * Record successful login
     *
     * @param User $user
     * @param Request $request
     * @param bool $used2FA
     * @return LoginAttempt
     */
    public function recordSuccessfulLogin(User $user, Request $request, bool $used2FA = false): LoginAttempt
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $geolocation = $this->getGeolocation($ipAddress);

        // Clear failed attempts
        $this->clearFailedAttempts($user, $ipAddress);

        // Record login attempt
        $loginAttempt = LoginAttempt::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFingerprint,
            'country_code' => $geolocation['country_code'] ?? null,
            'city' => $geolocation['city'] ?? null,
            'success' => true,
            'used_2fa' => $used2FA,
            'attempted_at' => now()
        ]);

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress
        ]);

        // Check if device should be trusted
        if ($used2FA && !$this->isDeviceTrusted($user, $deviceFingerprint)) {
            $this->addTrustedDevice($user, $deviceFingerprint, $request);
        }

        $this->logSecurityEvent($user, 'login_successful', [
            'ip' => $ipAddress,
            'country' => $geolocation['country_code'] ?? 'unknown',
            'used_2fa' => $used2FA,
            'device_trusted' => $this->isDeviceTrusted($user, $deviceFingerprint)
        ]);

        return $loginAttempt;
    }

    /**
     * Record failed login attempt
     *
     * @param User|null $user
     * @param Request $request
     * @param string $reason
     * @return LoginAttempt
     */
    public function recordFailedLogin(?User $user, Request $request, string $reason = 'invalid_credentials'): LoginAttempt
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $geolocation = $this->getGeolocation($ipAddress);

        // Record failed attempt
        $loginAttempt = LoginAttempt::create([
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFingerprint,
            'country_code' => $geolocation['country_code'] ?? null,
            'city' => $geolocation['city'] ?? null,
            'success' => false,
            'failure_reason' => $reason,
            'used_2fa' => false,
            'attempted_at' => now()
        ]);

        // Increment failed attempts
        $this->incrementFailedAttempts($user, $ipAddress);

        // Check if account should be locked
        if ($user && $this->shouldLockAccount($user)) {
            $this->lockAccount($user);
        }

        $this->logSecurityEvent($user, 'login_failed', [
            'ip' => $ipAddress,
            'reason' => $reason,
            'country' => $geolocation['country_code'] ?? 'unknown',
            'failed_attempts' => $this->getFailedAttempts($ipAddress, $user)
        ]);

        return $loginAttempt;
    }

    /**
     * Generate device fingerprint
     *
     * @param Request $request
     * @return string
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            $request->header('Accept'),
            $request->server('HTTP_SEC_CH_UA'),
            $request->server('HTTP_SEC_CH_UA_MOBILE'),
            $request->server('HTTP_SEC_CH_UA_PLATFORM')
        ];

        // Include additional fingerprinting data if available
        if ($request->has('screen_resolution')) {
            $components[] = $request->input('screen_resolution');
        }

        if ($request->has('timezone')) {
            $components[] = $request->input('timezone');
        }

        if ($request->has('browser_plugins')) {
            $components[] = $request->input('browser_plugins');
        }

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Check if account is locked
     *
     * @param User $user
     * @return bool
     */
    public function isAccountLocked(User $user): bool
    {
        $lockKey = "account_lock:{$user->id}";
        return Cache::has($lockKey);
    }

    /**
     * Lock user account
     *
     * @param User $user
     * @return void
     */
    public function lockAccount(User $user): void
    {
        $lockKey = "account_lock:{$user->id}";
        $expiresAt = now()->addSeconds($this->lockoutDuration);
        
        Cache::put($lockKey, [
            'locked_at' => now(),
            'expires_at' => $expiresAt,
            'reason' => 'too_many_failed_attempts'
        ], $expiresAt);

        $this->logSecurityEvent($user, 'account_locked', [
            'expires_at' => $expiresAt,
            'duration' => $this->lockoutDuration
        ]);

        // Send notification email (implement as needed)
        // $this->sendAccountLockedNotification($user, $expiresAt);
    }

    /**
     * Get lockout expiration time
     *
     * @param User $user
     * @return Carbon|null
     */
    public function getLockoutExpiration(User $user): ?Carbon
    {
        $lockKey = "account_lock:{$user->id}";
        $lockData = Cache::get($lockKey);
        
        return $lockData ? Carbon::parse($lockData['expires_at']) : null;
    }

    /**
     * Perform geolocation check
     *
     * @param User $user
     * @param string $ipAddress
     * @return array
     */
    public function performGeolocationCheck(User $user, string $ipAddress): array
    {
        $geolocation = $this->getGeolocation($ipAddress);
        
        if (!$geolocation) {
            return ['trusted' => false, 'reason' => 'unknown_location'];
        }

        // Check if country is in trusted list
        $countryTrusted = in_array($geolocation['country_code'], $this->trustedCountries);
        
        // Check user's login history for this location
        $locationHistory = $this->hasLocationHistory($user, $geolocation);
        
        return [
            'trusted' => $countryTrusted || $locationHistory,
            'country_code' => $geolocation['country_code'],
            'country_name' => $geolocation['country_name'],
            'city' => $geolocation['city'],
            'region' => $geolocation['region'],
            'country_trusted' => $countryTrusted,
            'location_history' => $locationHistory,
            'distance_from_last' => $this->calculateDistanceFromLastLogin($user, $geolocation)
        ];
    }

    /**
     * Get geolocation data for IP address
     *
     * @param string $ipAddress
     * @return array|null
     */
    protected function getGeolocation(string $ipAddress): ?array
    {
        // For demo purposes, return mock data
        // In production, integrate with services like MaxMind, IPInfo, etc.
        
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return [
                'country_code' => 'US',
                'country_name' => 'United States',
                'region' => 'Local',
                'city' => 'Localhost',
                'latitude' => 0,
                'longitude' => 0
            ];
        }

        // Cache geolocation data
        $cacheKey = "geolocation:{$ipAddress}";
        
        return Cache::remember($cacheKey, 86400, function() use ($ipAddress) {
            // Mock geolocation data for demo
            $mockLocations = [
                'US' => ['country_name' => 'United States', 'city' => 'New York', 'latitude' => 40.7128, 'longitude' => -74.0060],
                'CA' => ['country_name' => 'Canada', 'city' => 'Toronto', 'latitude' => 43.6532, 'longitude' => -79.3832],
                'GB' => ['country_name' => 'United Kingdom', 'city' => 'London', 'latitude' => 51.5074, 'longitude' => -0.1278],
                'DE' => ['country_name' => 'Germany', 'city' => 'Berlin', 'latitude' => 52.5200, 'longitude' => 13.4050],
                'FR' => ['country_name' => 'France', 'city' => 'Paris', 'latitude' => 48.8566, 'longitude' => 2.3522],
                'CN' => ['country_name' => 'China', 'city' => 'Beijing', 'latitude' => 39.9042, 'longitude' => 116.4074]
            ];

            $country = array_keys($mockLocations)[crc32($ipAddress) % count($mockLocations)];
            $location = $mockLocations[$country];

            return array_merge([
                'country_code' => $country,
                'region' => $location['city'] . ' Region'
            ], $location);
        });
    }

    /**
     * Check device trust status
     *
     * @param User $user
     * @param string $deviceFingerprint
     * @return array
     */
    public function checkDeviceTrust(User $user, string $deviceFingerprint): array
    {
        $trustedDevice = $user->trustedDevices()
            ->where('device_fingerprint', $deviceFingerprint)
            ->where('expires_at', '>', now())
            ->first();

        return [
            'trusted' => $trustedDevice !== null,
            'device_id' => $trustedDevice?->id,
            'trusted_at' => $trustedDevice?->created_at,
            'expires_at' => $trustedDevice?->expires_at,
            'last_used' => $trustedDevice?->last_used_at
        ];
    }

    /**
     * Add trusted device
     *
     * @param User $user
     * @param string $deviceFingerprint
     * @param Request $request
     * @return TrustedDevice
     */
    public function addTrustedDevice(User $user, string $deviceFingerprint, Request $request): TrustedDevice
    {
        // Remove existing trusted device with same fingerprint
        $user->trustedDevices()
            ->where('device_fingerprint', $deviceFingerprint)
            ->delete();

        $device = TrustedDevice::create([
            'user_id' => $user->id,
            'device_fingerprint' => $deviceFingerprint,
            'device_name' => $this->generateDeviceName($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addDays(30), // Trust for 30 days
            'last_used_at' => now()
        ]);

        $this->logSecurityEvent($user, 'device_trusted', [
            'device_id' => $device->id,
            'device_name' => $device->device_name,
            'ip' => $request->ip()
        ]);

        return $device;
    }

    /**
     * Detect suspicious activity patterns
     *
     * @param User $user
     * @param Request $request
     * @return array
     */
    public function detectSuspiciousActivity(User $user, Request $request): array
    {
        $suspiciousFactors = [];
        $riskScore = 0;

        // Check for rapid successive login attempts
        $recentAttempts = $this->getRecentLoginAttempts($user, 300); // Last 5 minutes
        if ($recentAttempts > 3) {
            $suspiciousFactors[] = 'rapid_attempts';
            $riskScore += 30;
        }

        // Check for multiple IP addresses
        $recentIPs = $this->getRecentUniqueIPs($user, 3600); // Last hour
        if ($recentIPs > 3) {
            $suspiciousFactors[] = 'multiple_ips';
            $riskScore += 40;
        }

        // Check for unusual time of day
        $currentHour = now()->hour;
        $usualLoginHours = $this->getUserUsualLoginHours($user);
        if (!in_array($currentHour, $usualLoginHours)) {
            $suspiciousFactors[] = 'unusual_time';
            $riskScore += 15;
        }

        // Check for automated tool signatures
        if ($this->detectAutomatedTools($request)) {
            $suspiciousFactors[] = 'automated_tools';
            $riskScore += 50;
        }

        // Check for TOR or VPN usage
        if ($this->detectTorOrVPN($request->ip())) {
            $suspiciousFactors[] = 'tor_or_vpn';
            $riskScore += 25;
        }

        return [
            'is_suspicious' => $riskScore >= 50,
            'risk_score' => $riskScore,
            'factors' => $suspiciousFactors,
            'recommendations' => $this->getSuspiciousActivityRecommendations($suspiciousFactors, $riskScore)
        ];
    }

    /**
     * Determine if 2FA is required
     *
     * @param User $user
     * @param array $geoCheck
     * @param array $deviceCheck
     * @return bool
     */
    public function requires2FA(User $user, array $geoCheck, array $deviceCheck): bool
    {
        // Always require 2FA if user has it enabled and device is not trusted
        if ($user->two_factor_enabled) {
            return !$deviceCheck['trusted'];
        }

        // Require 2FA for suspicious locations
        if (!$geoCheck['trusted']) {
            return true;
        }

        return false;
    }

    /**
     * Calculate security score
     *
     * @param array $geoCheck
     * @param array $deviceCheck
     * @param array $suspiciousActivity
     * @return int
     */
    public function calculateSecurityScore(array $geoCheck, array $deviceCheck, array $suspiciousActivity): int
    {
        $score = 100;

        // Deduct for untrusted location
        if (!$geoCheck['trusted']) {
            $score -= 20;
        }

        // Deduct for untrusted device
        if (!$deviceCheck['trusted']) {
            $score -= 15;
        }

        // Deduct for suspicious activity
        $score -= $suspiciousActivity['risk_score'] * 0.5;

        return max(0, min(100, (int) $score));
    }

    /**
     * Get security recommendations
     *
     * @param array $geoCheck
     * @param array $deviceCheck
     * @param array $suspiciousActivity
     * @return array
     */
    public function getSecurityRecommendations(array $geoCheck, array $deviceCheck, array $suspiciousActivity): array
    {
        $recommendations = [];

        if (!$geoCheck['trusted']) {
            $recommendations[] = 'Consider enabling 2FA for enhanced security from new locations';
        }

        if (!$deviceCheck['trusted']) {
            $recommendations[] = 'This device will be remembered for future logins after 2FA verification';
        }

        if ($suspiciousActivity['is_suspicious']) {
            $recommendations[] = 'Suspicious activity detected - please verify this login attempt';
        }

        if ($suspiciousActivity['risk_score'] > 70) {
            $recommendations[] = 'High risk login detected - consider changing your password';
        }

        return $recommendations;
    }

    // Helper methods (abbreviated for space - full implementations would follow)

    protected function isRateLimited(string $ipAddress): bool
    {
        $key = "login_attempts:{$ipAddress}";
        return Cache::get($key, 0) >= $this->maxFailedAttempts;
    }

    protected function getRateLimitRetryAfter(string $ipAddress): int
    {
        $key = "login_attempts:{$ipAddress}";
        return Cache::getStore()->getRedis()->ttl(config('cache.prefix') . ':' . $key);
    }

    protected function getFailedAttempts(string $ipAddress, ?User $user = null): int
    {
        $key = "login_attempts:{$ipAddress}";
        return Cache::get($key, 0);
    }

    protected function incrementFailedAttempts(?User $user, string $ipAddress): void
    {
        $key = "login_attempts:{$ipAddress}";
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(15));
    }

    protected function clearFailedAttempts(?User $user, string $ipAddress): void
    {
        Cache::forget("login_attempts:{$ipAddress}");
    }

    protected function shouldLockAccount(User $user): bool
    {
        return $this->getRecentFailedAttempts($user) >= $this->maxFailedAttempts;
    }

    protected function getRecentFailedAttempts(User $user): int
    {
        return LoginAttempt::where('user_id', $user->id)
            ->where('success', false)
            ->where('attempted_at', '>', now()->subMinutes(15))
            ->count();
    }

    protected function isDeviceTrusted(User $user, string $deviceFingerprint): bool
    {
        return $user->trustedDevices()
            ->where('device_fingerprint', $deviceFingerprint)
            ->where('expires_at', '>', now())
            ->exists();
    }

    protected function hasLocationHistory(User $user, array $geolocation): bool
    {
        return LoginAttempt::where('user_id', $user->id)
            ->where('success', true)
            ->where('country_code', $geolocation['country_code'])
            ->exists();
    }

    protected function calculateDistanceFromLastLogin(User $user, array $geolocation): ?float
    {
        // Simplified distance calculation - would use more sophisticated geolocation in production
        return null;
    }

    protected function generateDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        // Simple device name generation from user agent
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile Device';
        } elseif (str_contains($userAgent, 'Chrome')) {
            return 'Chrome Browser';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox Browser';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari Browser';
        }
        
        return 'Unknown Device';
    }

    protected function getRecentLoginAttempts(User $user, int $minutes): int
    {
        return LoginAttempt::where('user_id', $user->id)
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    protected function getRecentUniqueIPs(User $user, int $seconds): int
    {
        return LoginAttempt::where('user_id', $user->id)
            ->where('attempted_at', '>', now()->subSeconds($seconds))
            ->distinct('ip_address')
            ->count('ip_address');
    }

    protected function getUserUsualLoginHours(User $user): array
    {
        // This would analyze user's login history to determine usual hours
        // For now, return business hours as default
        return [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
    }

    protected function detectAutomatedTools(Request $request): bool
    {
        $userAgent = $request->userAgent();
        
        $automatedTools = ['curl', 'wget', 'python', 'bot', 'crawler', 'spider', 'scraper'];
        
        foreach ($automatedTools as $tool) {
            if (str_contains(strtolower($userAgent), $tool)) {
                return true;
            }
        }
        
        return false;
    }

    protected function detectTorOrVPN(string $ipAddress): bool
    {
        // This would integrate with TOR/VPN detection services
        // For now, return false
        return false;
    }

    protected function getSuspiciousActivityRecommendations(array $factors, int $riskScore): array
    {
        $recommendations = [];
        
        if (in_array('rapid_attempts', $factors)) {
            $recommendations[] = 'Consider enabling account lockout protection';
        }
        
        if (in_array('multiple_ips', $factors)) {
            $recommendations[] = 'Review recent login locations for unauthorized access';
        }
        
        if (in_array('automated_tools', $factors)) {
            $recommendations[] = 'Potential automated attack detected - consider IP blocking';
        }
        
        if ($riskScore > 70) {
            $recommendations[] = 'High risk activity - consider mandatory password reset';
        }
        
        return $recommendations;
    }

    protected function logSecurityEvent(?User $user, string $eventType, array $data): void
    {
        SecurityEvent::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'occurred_at' => now()
        ]);

        Log::info("Security event: {$eventType}", array_merge([
            'user_id' => $user?->id,
            'ip' => request()->ip()
        ], $data));
    }
}
