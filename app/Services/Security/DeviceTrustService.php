<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Device Trust Management Service
 *
 * Manages trusted devices, device fingerprinting, and device-based security
 * for the HD Tickets system.
 */
class DeviceTrustService
{
    // Device trust levels
    private const TRUST_LEVELS = [
        'untrusted'     => 0,
        'new'           => 25,
        'recognized'    => 50,
        'trusted'       => 75,
        'fully_trusted' => 100,
    ];

    // Maximum trusted devices per user
    private const MAX_TRUSTED_DEVICES = 10;

    // Trust token expiry in days
    private const TRUST_TOKEN_EXPIRY_DAYS = 90;

    public function __construct(private SecurityMonitoringService $securityMonitoring)
    {
    }

    /**
     * Generate device fingerprint from request
     */
    public function generateDeviceFingerprint(Request $request, array $clientData = []): string
    {
        $fingerprint = [];

        // Server-side fingerprint components
        $fingerprint['user_agent'] = $request->userAgent() ?? '';
        $fingerprint['ip_address'] = $request->ip();
        $fingerprint['accept_language'] = $request->header('Accept-Language', '');
        $fingerprint['accept_encoding'] = $request->header('Accept-Encoding', '');

        // Client-side fingerprint components (from JavaScript)
        if ($clientData !== []) {
            $fingerprint['screen_resolution'] = $clientData['screen_resolution'] ?? '';
            $fingerprint['timezone'] = $clientData['timezone'] ?? '';
            $fingerprint['platform'] = $clientData['platform'] ?? '';
            $fingerprint['canvas_fingerprint'] = $clientData['canvas_fingerprint'] ?? '';
            $fingerprint['webgl_fingerprint'] = $clientData['webgl_fingerprint'] ?? '';
        }

        // Sort for consistency
        ksort($fingerprint);

        // Create hash
        $fingerprintString = json_encode($fingerprint);

        return hash('sha256', $fingerprintString);
    }

    /**
     * Check if device is trusted for user
     */
    public function isDeviceTrusted(User $user, string $deviceFingerprint): array
    {
        $device = TrustedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();

        if (! $device) {
            return [
                'trusted'     => FALSE,
                'trust_level' => 'untrusted',
                'trust_score' => 0,
                'device'      => NULL,
            ];
        }

        // Check if device is still valid
        if ($device->expires_at && $device->expires_at->isPast()) {
            $device->delete();

            return [
                'trusted'     => FALSE,
                'trust_level' => 'untrusted',
                'trust_score' => 0,
                'device'      => NULL,
            ];
        }

        // Calculate trust score
        $trustScore = $this->calculateTrustScore($device);
        $trustLevel = $this->getTrustLevel($trustScore);

        return [
            'trusted'     => $trustScore >= self::TRUST_LEVELS['trusted'],
            'trust_level' => $trustLevel,
            'trust_score' => $trustScore,
            'device'      => $device,
        ];
    }

    /**
     * Add device to trusted devices
     */
    public function trustDevice(User $user, Request $request, array $clientData = [], ?string $deviceName = NULL): string
    {
        $deviceFingerprint = $this->generateDeviceFingerprint($request, $clientData);

        // Check if device already exists
        $existingDevice = TrustedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->first();

        if ($existingDevice) {
            // Update existing device
            $existingDevice->update([
                'last_used_at' => now(),
                'usage_count'  => $existingDevice->usage_count + 1,
                'expires_at'   => now()->addDays(self::TRUST_TOKEN_EXPIRY_DAYS),
            ]);

            return $existingDevice->trust_token;
        }

        // Clean up old devices if at limit
        $this->cleanupOldDevices($user);

        // Generate unique trust token
        $trustToken = $this->generateTrustToken();

        // Create new trusted device
        TrustedDevice::create([
            'user_id'            => $user->id,
            'device_fingerprint' => $deviceFingerprint,
            'trust_token'        => $trustToken,
            'device_name'        => $deviceName ?? $this->generateDeviceName($request),
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'location_data'      => $this->getLocationData($request->ip()),
            'first_seen_at'      => now(),
            'last_used_at'       => now(),
            'expires_at'         => now()->addDays(self::TRUST_TOKEN_EXPIRY_DAYS),
            'usage_count'        => 1,
            'trust_score'        => self::TRUST_LEVELS['new'],
            'is_active'          => TRUE,
        ]);

        // Log device trust event
        $this->securityMonitoring->recordSecurityEvent(
            'device_trusted',
            'New device added to trusted devices',
        );

        return $trustToken;
    }

    /**
     * Remove device from trusted devices
     */
    public function untrustDevice(User $user, string $deviceId): bool
    {
        $device = TrustedDevice::where('user_id', $user->id)
            ->where('id', $deviceId)
            ->first();

        if (! $device) {
            return FALSE;
        }

        // Log device untrust event
        $this->securityMonitoring->recordSecurityEvent(
            'device_untrusted',
            'Device removed from trusted devices',
        );

        $device->delete();

        return TRUE;
    }

    /**
     * Get user's trusted devices
     */
    public function getUserTrustedDevices(User $user): Collection
    {
        return TrustedDevice::where('user_id', $user->id)
            ->where('is_active', TRUE)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    /**
     * Validate trust token
     */
    public function validateTrustToken(User $user, string $trustToken): bool
    {
        $device = TrustedDevice::where('user_id', $user->id)
            ->where('trust_token', $trustToken)
            ->where('is_active', TRUE)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $device) {
            return FALSE;
        }

        // Update usage statistics
        $device->increment('usage_count');
        $device->update(['last_used_at' => now()]);

        // Increase trust score based on usage
        $this->updateTrustScore($device);

        return TRUE;
    }

    /**
     * Revoke all trusted devices for user
     */
    public function revokeAllTrustedDevices(User $user): int
    {
        $count = TrustedDevice::where('user_id', $user->id)->count();

        TrustedDevice::where('user_id', $user->id)->delete();

        // Log bulk device revocation
        $this->securityMonitoring->recordSecurityEvent(
            'all_devices_revoked',
            'All trusted devices revoked for user',
        );

        return $count;
    }

    /**
     * Get device risk assessment
     */
    public function assessDeviceRisk(Request $request, ?User $user = NULL): array
    {
        $risk = [
            'risk_level'      => 'low',
            'risk_score'      => 0,
            'risk_factors'    => [],
            'recommendations' => [],
        ];

        $riskScore = 0;

        // Check for suspicious user agent
        $userAgent = $request->userAgent();
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $riskScore += 30;
            $risk['risk_factors'][] = 'Suspicious user agent detected';
        }

        // Check IP reputation
        $ipReputation = $this->getIpReputation($request->ip());
        if ($ipReputation['is_malicious']) {
            $riskScore += 50;
            $risk['risk_factors'][] = 'Malicious IP address detected';
        }

        // Check geolocation anomaly
        if ($user && $this->isLocationAnomalous($user, $request->ip())) {
            $riskScore += 25;
            $risk['risk_factors'][] = 'Unusual location detected';
        }

        // Check if device is known
        if ($user instanceof User) {
            $deviceFingerprint = $this->generateDeviceFingerprint($request);
            $deviceTrust = $this->isDeviceTrusted($user, $deviceFingerprint);

            if (! $deviceTrust['trusted']) {
                $riskScore += 20;
                $risk['risk_factors'][] = 'Unknown device';
            }
        }

        // Check for automation indicators
        if ($this->hasAutomationIndicators($request)) {
            $riskScore += 40;
            $risk['risk_factors'][] = 'Automated tool detected';
        }

        // Determine risk level
        $risk['risk_score'] = $riskScore;

        if ($riskScore >= 70) {
            $risk['risk_level'] = 'critical';
            $risk['recommendations'][] = 'Block access immediately';
            $risk['recommendations'][] = 'Require additional verification';
        } elseif ($riskScore >= 50) {
            $risk['risk_level'] = 'high';
            $risk['recommendations'][] = 'Require MFA verification';
            $risk['recommendations'][] = 'Monitor closely';
        } elseif ($riskScore >= 30) {
            $risk['risk_level'] = 'medium';
            $risk['recommendations'][] = 'Consider additional verification';
        } else {
            $risk['risk_level'] = 'low';
            $risk['recommendations'][] = 'Normal access allowed';
        }

        return $risk;
    }

    /**
     * Generate device analytics report
     */
    public function generateDeviceAnalytics(User $user): array
    {
        $devices = $this->getUserTrustedDevices($user);

        return [
            'total_devices'      => $devices->count(),
            'active_devices'     => $devices->where('last_used_at', '>=', now()->subDays(30))->count(),
            'trust_distribution' => [
                'fully_trusted' => $devices->where('trust_score', '>=', self::TRUST_LEVELS['fully_trusted'])->count(),
                'trusted'       => $devices->whereBetween('trust_score', [self::TRUST_LEVELS['trusted'], self::TRUST_LEVELS['fully_trusted'] - 1])->count(),
                'recognized'    => $devices->whereBetween('trust_score', [self::TRUST_LEVELS['recognized'], self::TRUST_LEVELS['trusted'] - 1])->count(),
                'new'           => $devices->where('trust_score', '<', self::TRUST_LEVELS['recognized'])->count(),
            ],
            'device_types'    => $this->analyzeDeviceTypes($devices),
            'location_spread' => $this->analyzeLocationSpread($devices),
            'usage_patterns'  => $this->analyzeUsagePatterns($devices),
        ];
    }

    /**
     * Calculate device trust score
     */
    private function calculateTrustScore(TrustedDevice $device): int
    {
        $score = $device->trust_score;

        // Age bonus (older devices are more trusted)
        $ageInDays = $device->first_seen_at->diffInDays(now());
        $ageBonus = min($ageInDays * 2, 20);

        // Usage bonus (more usage = more trust)
        $usageBonus = min($device->usage_count * 1, 15);

        // Recent activity bonus
        $recentActivityBonus = 0;
        if ($device->last_used_at->isAfter(now()->subDays(7))) {
            $recentActivityBonus = 10;
        }

        $totalScore = $score + $ageBonus + $usageBonus + $recentActivityBonus;

        return min($totalScore, 100);
    }

    /**
     * Get trust level from score
     */
    private function getTrustLevel(int $score): string
    {
        foreach (array_reverse(self::TRUST_LEVELS, TRUE) as $level => $threshold) {
            if ($score >= $threshold) {
                return $level;
            }
        }

        return 'untrusted';
    }

    /**
     * Generate unique trust token
     */
    private function generateTrustToken(): string
    {
        return Hash::make(Str::random(64) . now()->timestamp);
    }

    /**
     * Generate device name from request
     */
    private function generateDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent();

        // Extract browser and OS info
        $browser = $this->extractBrowser($userAgent);
        $os = $this->extractOS($userAgent);

        return "{$browser} on {$os}";
    }

    /**
     * Clean up old devices when limit reached
     */
    private function cleanupOldDevices(User $user): void
    {
        $deviceCount = TrustedDevice::where('user_id', $user->id)->count();

        if ($deviceCount >= self::MAX_TRUSTED_DEVICES) {
            // Remove oldest unused devices
            $devicesToRemove = $deviceCount - self::MAX_TRUSTED_DEVICES + 1;

            TrustedDevice::where('user_id', $user->id)
                ->orderBy('last_used_at', 'asc')
                ->take($devicesToRemove)
                ->delete();
        }
    }

    /**
     * Update trust score based on usage
     */
    private function updateTrustScore(TrustedDevice $device): void
    {
        $newScore = $this->calculateTrustScore($device);

        if ($newScore > $device->trust_score) {
            $device->update(['trust_score' => $newScore]);
        }
    }

    /**
     * Check for suspicious user agent
     */
    private function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/phantom/i',
            '/selenium/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get IP reputation data
     */
    private function getIpReputation(string $ip): array
    {
        // Cache key for IP reputation
        $cacheKey = "ip_reputation_{$ip}";

        return Cache::remember($cacheKey, 3600, fn (): array => // This would integrate with IP reputation services
            // For now, return basic check
            [
                'is_malicious'     => FALSE,
                'reputation_score' => 100,
                'categories'       => [],
            ]);
    }

    /**
     * Check if location is anomalous for user
     */
    private function isLocationAnomalous(User $user, string $ip): bool
    {
        // Get user's typical locations
        $userLocations = TrustedDevice::where('user_id', $user->id)
            ->whereNotNull('location_data')
            ->pluck('location_data')
            ->map(fn ($location): mixed => json_decode((string) $location, TRUE));

        if ($userLocations->isEmpty()) {
            return FALSE;
        }

        // Get current location
        $currentLocation = $this->getLocationData($ip);

        if (! $currentLocation) {
            return FALSE;
        }

        // Check if current location is significantly different
        foreach ($userLocations as $knownLocation) {
            $distance = $this->calculateDistance(
                $currentLocation['latitude'] ?? 0,
                $currentLocation['longitude'] ?? 0,
                $knownLocation['latitude'] ?? 0,
                $knownLocation['longitude'] ?? 0,
            );

            // If within 100km of known location, not anomalous
            if ($distance <= 100) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check for automation indicators
     */
    private function hasAutomationIndicators(Request $request): bool
    {
        $indicators = 0;

        // Check headers
        $suspiciousHeaders = ['X-Automated', 'X-Headless', 'X-Selenium'];
        foreach ($suspiciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                $indicators++;
            }
        }

        // Check user agent
        if ($this->isSuspiciousUserAgent($request->userAgent() ?? '')) {
            $indicators++;
        }

        // Check request timing patterns (would need session data)
        // This could be implemented with request timing analysis

        return $indicators >= 1;
    }

    /**
     * Get location data from IP
     */
    private function getLocationData(string $ip): ?array
    {
        // This would integrate with geolocation services
        // For now, return null for local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [
                'country'   => 'Unknown',
                'city'      => 'Unknown',
                'latitude'  => 0,
                'longitude' => 0,
            ];
        }

        return NULL;
    }

    /**
     * Extract browser from user agent
     */
    private function extractBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'Opera')) {
            return 'Opera';
        }

        return 'Unknown Browser';
    }

    /**
     * Extract OS from user agent
     */
    private function extractOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'Macintosh')) {
            return 'macOS';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }
        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }

        return 'Unknown OS';
    }

    /**
     * Calculate distance between two coordinates
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Analyze device types from trusted devices
     */
    private function analyzeDeviceTypes(Collection $devices): array
    {
        $types = [];

        foreach ($devices as $device) {
            $userAgent = $device->user_agent;
            $os = $this->extractOS($userAgent);
            $browser = $this->extractBrowser($userAgent);

            $type = "{$browser} / {$os}";
            $types[$type] = ($types[$type] ?? 0) + 1;
        }

        return $types;
    }

    /**
     * Analyze location spread from trusted devices
     */
    private function analyzeLocationSpread(Collection $devices): array
    {
        $locations = [];

        foreach ($devices as $device) {
            if ($device->location_data) {
                $location = json_decode($device->location_data, TRUE);
                $country = $location['country'] ?? 'Unknown';
                $locations[$country] = ($locations[$country] ?? 0) + 1;
            }
        }

        return $locations;
    }

    /**
     * Analyze usage patterns from trusted devices
     */
    private function analyzeUsagePatterns(Collection $devices): array
    {
        return [
            'total_usage'     => $devices->sum('usage_count'),
            'average_usage'   => round((float) $devices->avg('usage_count'), 2),
            'most_used'       => $devices->sortByDesc('usage_count')->first()?->device_name,
            'recent_activity' => $devices->where('last_used_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
