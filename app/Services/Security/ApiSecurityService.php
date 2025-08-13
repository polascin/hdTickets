<?php declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use App\Services\SecurityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function count;

class ApiSecurityService
{
    /** Rate limiting configurations per endpoint */
    public const RATE_LIMITS = [
        'auth.login' => [
            'limit'       => 5,
            'window'      => 900, // 15 minutes
            'penalty'     => 3600, // 1 hour lockout
            'progressive' => TRUE,
        ],
        'tickets.search' => [
            'limit'        => 1000,
            'window'       => 3600, // 1 hour
            'burst_limit'  => 50,
            'burst_window' => 60, // 1 minute
        ],
        'tickets.purchase' => [
            'limit'            => 10,
            'window'           => 3600, // 1 hour
            'require_2fa'      => TRUE,
            'high_value_limit' => 3,
        ],
        'scraping.execute' => [
            'limit'            => 500,
            'window'           => 3600, // 1 hour
            'concurrent_limit' => 5,
        ],
        'admin.users' => [
            'limit'         => 100,
            'window'        => 3600, // 1 hour
            'require_admin' => TRUE,
        ],
        'reports.export' => [
            'limit'              => 20,
            'window'             => 3600, // 1 hour
            'resource_intensive' => TRUE,
        ],
    ];

    /** IP whitelist patterns */
    public const IP_WHITELIST_PATTERNS = [
        'admin_ips' => [
            // Add specific admin IP ranges
        ],
        'api_partners' => [
            // Add partner IP ranges
        ],
        'internal_services' => [
            '127.0.0.1',
            '::1',
        ],
    ];

    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Check rate limits for API endpoint
     */
    /**
     * CheckRateLimit
     */
    public function checkRateLimit(Request $request, string $endpoint, ?User $user = NULL): array
    {
        $config = $this->getRateLimitConfig($endpoint);
        $identifier = $this->getRateLimitIdentifier($request, $user);

        // Check IP-based rate limiting
        $ipResult = $this->checkIpRateLimit($request, $endpoint, $config);
        if (! $ipResult['allowed']) {
            return $ipResult;
        }

        // Check user-based rate limiting
        if ($user) {
            $userResult = $this->checkUserRateLimit($user, $endpoint, $config);
            if (! $userResult['allowed']) {
                return $userResult;
            }
        }

        // Check concurrent request limits
        if (isset($config['concurrent_limit'])) {
            $concurrentResult = $this->checkConcurrentLimit($identifier, $endpoint, $config);
            if (! $concurrentResult['allowed']) {
                return $concurrentResult;
            }
        }

        // Check burst limits
        if (isset($config['burst_limit'])) {
            $burstResult = $this->checkBurstLimit($identifier, $endpoint, $config);
            if (! $burstResult['allowed']) {
                return $burstResult;
            }
        }

        // Record successful request
        $this->recordRequest($identifier, $endpoint, $request);

        return [
            'allowed'    => TRUE,
            'remaining'  => $this->getRemainingRequests($identifier, $endpoint, $config),
            'reset_time' => $this->getResetTime($identifier, $endpoint, $config),
        ];
    }

    /**
     * Generate API key with specific permissions
     */
    /**
     * GenerateApiKey
     */
    public function generateApiKey(User $user, array $scopes = [], array $options = []): array
    {
        $keyId = Str::uuid();
        $keySecret = $this->generateSecureKey(64);
        $keyHash = Hash::make($keySecret);

        $apiKey = [
            'id'              => $keyId,
            'user_id'         => $user->id,
            'name'            => $options['name'] ?? 'API Key',
            'key_hash'        => $keyHash,
            'scopes'          => $scopes,
            'rate_limit_tier' => $options['tier'] ?? 'standard',
            'ip_whitelist'    => $options['ip_whitelist'] ?? [],
            'expires_at'      => isset($options['expires_at']) ? Carbon::parse($options['expires_at']) : NULL,
            'last_used_at'    => NULL,
            'created_at'      => now(),
            'is_active'       => TRUE,
        ];

        // Store API key
        Cache::put("api_key:{$keyId}", $apiKey, now()->addYears(5));

        // Store user's API keys list
        $userKeys = Cache::get("user_api_keys:{$user->id}", []);
        $userKeys[] = $keyId;
        Cache::put("user_api_keys:{$user->id}", $userKeys, now()->addYears(5));

        // Log API key creation
        $this->securityService->logSecurityActivity('API key generated', [
            'key_id' => $keyId,
            'scopes' => $scopes,
            'tier'   => $apiKey['rate_limit_tier'],
        ], $user);

        return [
            'id'         => $keyId,
            'key'        => 'hdtickets_' . base64_encode($keyId . ':' . $keySecret),
            'scopes'     => $scopes,
            'created_at' => $apiKey['created_at']->toISOString(),
        ];
    }

    /**
     * Validate API key and get associated user
     */
    /**
     * ValidateApiKey
     */
    public function validateApiKey(string $apiKey): ?array
    {
        // Parse API key
        if (! str_starts_with($apiKey, 'hdtickets_')) {
            return NULL;
        }

        $keyData = base64_decode(substr($apiKey, 10), TRUE);
        $parts = explode(':', $keyData, 2);

        if (count($parts) !== 2) {
            return NULL;
        }

        [$keyId, $keySecret] = $parts;

        // Get API key data
        $apiKeyData = Cache::get("api_key:{$keyId}");
        if (! $apiKeyData || ! $apiKeyData['is_active']) {
            return NULL;
        }

        // Verify key secret
        if (! Hash::check($keySecret, $apiKeyData['key_hash'])) {
            $this->logInvalidKeyAttempt($keyId);

            return NULL;
        }

        // Check expiration
        if ($apiKeyData['expires_at'] && $apiKeyData['expires_at']->isPast()) {
            return NULL;
        }

        // Update last used timestamp
        $apiKeyData['last_used_at'] = now();
        Cache::put("api_key:{$keyId}", $apiKeyData, now()->addYears(5));

        // Get user
        $user = User::find($apiKeyData['user_id']);
        if (! $user || ! $user->is_active) {
            return NULL;
        }

        return [
            'user'         => $user,
            'key_id'       => $keyId,
            'scopes'       => $apiKeyData['scopes'],
            'tier'         => $apiKeyData['rate_limit_tier'],
            'ip_whitelist' => $apiKeyData['ip_whitelist'],
        ];
    }

    /**
     * Rotate API key
     */
    /**
     * RotateApiKey
     */
    public function rotateApiKey(string $keyId, User $user): ?array
    {
        $apiKeyData = Cache::get("api_key:{$keyId}");
        if (! $apiKeyData || $apiKeyData['user_id'] !== $user->id) {
            return NULL;
        }

        // Generate new key secret
        $newKeySecret = $this->generateSecureKey(64);
        $newKeyHash = Hash::make($newKeySecret);

        // Archive old key
        $archivedKey = $apiKeyData;
        $archivedKey['archived_at'] = now();
        Cache::put("api_key_archived:{$keyId}_" . time(), $archivedKey, now()->addMonths(6));

        // Update with new secret
        $apiKeyData['key_hash'] = $newKeyHash;
        $apiKeyData['rotated_at'] = now();
        Cache::put("api_key:{$keyId}", $apiKeyData, now()->addYears(5));

        // Log key rotation
        $this->securityService->logSecurityActivity('API key rotated', [
            'key_id' => $keyId,
        ], $user);

        return [
            'id'         => $keyId,
            'key'        => 'hdtickets_' . base64_encode($keyId . ':' . $newKeySecret),
            'rotated_at' => $apiKeyData['rotated_at']->toISOString(),
        ];
    }

    /**
     * Revoke API key
     */
    /**
     * RevokeApiKey
     */
    public function revokeApiKey(string $keyId, User $user): bool
    {
        $apiKeyData = Cache::get("api_key:{$keyId}");
        if (! $apiKeyData || $apiKeyData['user_id'] !== $user->id) {
            return FALSE;
        }

        // Mark as inactive
        $apiKeyData['is_active'] = FALSE;
        $apiKeyData['revoked_at'] = now();
        Cache::put("api_key:{$keyId}", $apiKeyData, now()->addYears(5));

        // Remove from user's active keys
        $userKeys = Cache::get("user_api_keys:{$user->id}", []);
        $userKeys = array_filter($userKeys, fn ($k) => $k !== $keyId);
        Cache::put("user_api_keys:{$user->id}", $userKeys, now()->addYears(5));

        // Log key revocation
        $this->securityService->logSecurityActivity('API key revoked', [
            'key_id' => $keyId,
        ], $user);

        return TRUE;
    }

    /**
     * Verify request signature
     */
    /**
     * VerifyRequestSignature
     */
    public function verifyRequestSignature(Request $request, string $signature, string $keySecret): bool
    {
        $timestamp = $request->header('X-Timestamp');
        if (! $timestamp || abs(time() - $timestamp) > config('security.api.timestamp_tolerance', 300)) {
            return FALSE;
        }

        // Build signature payload
        $payload = implode("\n", [
            $request->method(),
            $request->getPathInfo(),
            $request->getQueryString() ?? '',
            $request->getContent(),
            $timestamp,
        ]);

        // Calculate expected signature
        $expectedSignature = hash_hmac(
            config('security.api.signature_algorithm', 'sha256'),
            $payload,
            $keySecret,
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Check IP whitelist
     */
    /**
     * CheckIpWhitelist
     */
    public function checkIpWhitelist(Request $request, array $allowedIps = []): bool
    {
        if (empty($allowedIps)) {
            return TRUE;
        }

        $clientIp = $request->ip();

        foreach ($allowedIps as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get API usage analytics
     */
    /**
     * Get  api usage analytics
     */
    public function getApiUsageAnalytics(User $user, array $options = []): array
    {
        $period = $options['period'] ?? 'last_30_days';
        $startDate = $this->getAnalyticsPeriodStart($period);

        $userKeys = Cache::get("user_api_keys:{$user->id}", []);
        $analytics = [
            'period'                => $period,
            'total_requests'        => 0,
            'successful_requests'   => 0,
            'failed_requests'       => 0,
            'rate_limited_requests' => 0,
            'endpoints'             => [],
            'keys'                  => [],
            'daily_usage'           => [],
        ];

        foreach ($userKeys as $keyId) {
            $keyAnalytics = $this->getKeyUsageAnalytics($keyId, $startDate);
            $analytics['keys'][$keyId] = $keyAnalytics;

            $analytics['total_requests'] += $keyAnalytics['total_requests'];
            $analytics['successful_requests'] += $keyAnalytics['successful_requests'];
            $analytics['failed_requests'] += $keyAnalytics['failed_requests'];
            $analytics['rate_limited_requests'] += $keyAnalytics['rate_limited_requests'];

            // Merge endpoint usage
            foreach ($keyAnalytics['endpoints'] as $endpoint => $count) {
                $analytics['endpoints'][$endpoint] = ($analytics['endpoints'][$endpoint] ?? 0) + $count;
            }
        }

        // Calculate success rate
        $analytics['success_rate'] = $analytics['total_requests'] > 0
            ? round(($analytics['successful_requests'] / $analytics['total_requests']) * 100, 2)
            : 0;

        return $analytics;
    }

    /**
     * Get system-wide API analytics (admin only)
     */
    /**
     * Get  system api analytics
     */
    public function getSystemApiAnalytics(array $options = []): array
    {
        $period = $options['period'] ?? 'last_24_hours';
        $cacheKey = "system_api_analytics:{$period}";

        return Cache::remember($cacheKey, 300, function () use ($period) {
            return [
                'period'                    => $period,
                'total_requests'            => $this->getSystemRequestCount($period),
                'unique_users'              => $this->getUniqueApiUsers($period),
                'top_endpoints'             => $this->getTopEndpoints($period),
                'rate_limit_violations'     => $this->getRateLimitViolations($period),
                'geographical_distribution' => $this->getGeographicalDistribution($period),
                'response_times'            => $this->getAverageResponseTimes($period),
            ];
        });
    }

    /**
     * Implement progressive rate limiting
     */
    /**
     * CheckProgressiveRateLimit
     */
    protected function checkProgressiveRateLimit(string $identifier, string $endpoint, array $config): array
    {
        $violations = Cache::get("rate_violations:{$identifier}:{$endpoint}", 0);

        // Calculate progressive penalty
        $baseLimit = $config['limit'];
        $penaltyMultiplier = min(pow(2, $violations), 32); // Cap at 32x penalty
        $adjustedLimit = max(1, (int) ($baseLimit / $penaltyMultiplier));

        $currentCount = Cache::get("rate_limit:{$identifier}:{$endpoint}", 0);

        if ($currentCount >= $adjustedLimit) {
            // Increase violation count
            Cache::put("rate_violations:{$identifier}:{$endpoint}", $violations + 1, now()->addHours(24));

            return [
                'allowed'     => FALSE,
                'reason'      => 'progressive_rate_limit_exceeded',
                'retry_after' => $this->calculateProgressiveRetryAfter($violations),
            ];
        }

        return ['allowed' => TRUE];
    }

    /**
     * Get rate limit configuration for endpoint
     */
    /**
     * Get  rate limit config
     */
    protected function getRateLimitConfig(string $endpoint): array
    {
        return self::RATE_LIMITS[$endpoint] ?? [
            'limit'  => 1000,
            'window' => 3600,
        ];
    }

    /**
     * Get rate limit identifier (IP + User ID if available)
     */
    /**
     * Get  rate limit identifier
     */
    protected function getRateLimitIdentifier(Request $request, ?User $user = NULL): string
    {
        $parts = [$request->ip()];
        if ($user) {
            $parts[] = "user:{$user->id}";
        }

        return implode(':', $parts);
    }

    /**
     * Check IP-based rate limiting
     */
    /**
     * CheckIpRateLimit
     */
    protected function checkIpRateLimit(Request $request, string $endpoint, array $config): array
    {
        $ipIdentifier = 'ip:' . $request->ip();

        return $this->checkBasicRateLimit($ipIdentifier, $endpoint, $config);
    }

    /**
     * Check user-based rate limiting
     */
    /**
     * CheckUserRateLimit
     */
    protected function checkUserRateLimit(User $user, string $endpoint, array $config): array
    {
        $userIdentifier = 'user:' . $user->id;

        return $this->checkBasicRateLimit($userIdentifier, $endpoint, $config);
    }

    /**
     * Basic rate limit check
     */
    /**
     * CheckBasicRateLimit
     */
    protected function checkBasicRateLimit(string $identifier, string $endpoint, array $config): array
    {
        $key = "rate_limit:{$identifier}:{$endpoint}";
        $current = Cache::get($key, 0);
        $window = $config['window'];
        $limit = $config['limit'];

        if ($current >= $limit) {
            return [
                'allowed'     => FALSE,
                'reason'      => 'rate_limit_exceeded',
                'retry_after' => $this->getRetryAfter($key, $window),
            ];
        }

        // Increment counter
        Cache::put($key, $current + 1, now()->addSeconds($window));

        return ['allowed' => TRUE];
    }

    /**
     * Check concurrent request limits
     */
    /**
     * CheckConcurrentLimit
     */
    protected function checkConcurrentLimit(string $identifier, string $endpoint, array $config): array
    {
        $concurrentKey = "concurrent:{$identifier}:{$endpoint}";
        $current = Cache::get($concurrentKey, 0);

        if ($current >= $config['concurrent_limit']) {
            return [
                'allowed'     => FALSE,
                'reason'      => 'concurrent_limit_exceeded',
                'retry_after' => 5, // Short retry time for concurrent limits
            ];
        }

        return ['allowed' => TRUE];
    }

    /**
     * Check burst limits
     */
    /**
     * CheckBurstLimit
     */
    protected function checkBurstLimit(string $identifier, string $endpoint, array $config): array
    {
        $burstKey = "burst:{$identifier}:{$endpoint}";
        $current = Cache::get($burstKey, 0);

        if ($current >= $config['burst_limit']) {
            return [
                'allowed'     => FALSE,
                'reason'      => 'burst_limit_exceeded',
                'retry_after' => $config['burst_window'],
            ];
        }

        // Increment burst counter
        Cache::put($burstKey, $current + 1, now()->addSeconds($config['burst_window']));

        return ['allowed' => TRUE];
    }

    /**
     * Record API request for analytics
     */
    /**
     * RecordRequest
     */
    protected function recordRequest(string $identifier, string $endpoint, Request $request): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('Y-m-d:H');

        // Daily stats
        Cache::increment("api_stats:daily:{$today}");
        Cache::increment("api_stats:daily:{$today}:endpoint:{$endpoint}");

        // Hourly stats
        Cache::increment("api_stats:hourly:{$hour}");
        Cache::increment("api_stats:hourly:{$hour}:endpoint:{$endpoint}");

        // User stats if authenticated
        if (str_contains($identifier, 'user:')) {
            $userId = explode(':', $identifier)[1];
            Cache::increment("api_stats:user:{$userId}:daily:{$today}");
        }
    }

    /**
     * Generate secure API key
     */
    /**
     * GenerateSecureKey
     */
    protected function generateSecureKey(int $length = 64): string
    {
        return Str::random($length);
    }

    /**
     * Check if IP matches pattern
     */
    /**
     * IpMatches
     */
    protected function ipMatches(string $ip, string $pattern): bool
    {
        // Handle CIDR notation
        if (str_contains($pattern, '/')) {
            return $this->ipInCidr($ip, $pattern);
        }

        // Handle wildcards
        if (str_contains($pattern, '*')) {
            return fnmatch($pattern, $ip);
        }

        // Exact match
        return $ip === $pattern;
    }

    /**
     * Check if IP is in CIDR range
     */
    /**
     * IpInCidr
     */
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }

        // IPv6 support would go here
        return FALSE;
    }

    /**
     * Get remaining requests for rate limit
     */
    /**
     * Get  remaining requests
     */
    protected function getRemainingRequests(string $identifier, string $endpoint, array $config): int
    {
        $key = "rate_limit:{$identifier}:{$endpoint}";
        $current = Cache::get($key, 0);

        return max(0, $config['limit'] - $current);
    }

    /**
     * Get rate limit reset time
     */
    /**
     * Get  reset time
     */
    protected function getResetTime(string $identifier, string $endpoint, array $config): int
    {
        $key = "rate_limit:{$identifier}:{$endpoint}";
        $ttl = Cache::getStore()->getRedis()->ttl(Cache::getStore()->getPrefix() . $key);

        return $ttl > 0 ? time() + $ttl : time() + $config['window'];
    }

    /**
     * Get retry after time
     */
    /**
     * Get  retry after
     */
    protected function getRetryAfter(string $key, int $window): int
    {
        $ttl = Cache::getStore()->getRedis()->ttl(Cache::getStore()->getPrefix() . $key);

        return max(1, $ttl);
    }

    /**
     * Calculate progressive retry after time
     */
    /**
     * CalculateProgressiveRetryAfter
     */
    protected function calculateProgressiveRetryAfter(int $violations): int
    {
        return min(3600, (int) (60 * pow(2, $violations))); // Cap at 1 hour
    }

    /**
     * Log invalid API key attempt
     */
    /**
     * LogInvalidKeyAttempt
     */
    protected function logInvalidKeyAttempt(string $keyId): void
    {
        $this->securityService->logSecurityActivity('Invalid API key attempt', [
            'key_id'     => $keyId,
            'risk_level' => 'medium',
        ]);

        // Track invalid attempts
        Cache::increment("invalid_api_key_attempts:{$keyId}", 1, 3600);
    }

    /**
     * Get analytics period start date
     */
    /**
     * Get  analytics period start
     */
    protected function getAnalyticsPeriodStart(string $period): Carbon
    {
        return match ($period) {
            'last_24_hours' => now()->subHours(24),
            'last_7_days'   => now()->subDays(7),
            'last_30_days'  => now()->subDays(30),
            'last_90_days'  => now()->subDays(90),
            default         => now()->subDays(30),
        };
    }

    /**
     * Get usage analytics for specific API key
     */
    /**
     * Get  key usage analytics
     */
    protected function getKeyUsageAnalytics(string $keyId, Carbon $startDate): array
    {
        // This would typically query a more persistent analytics store
        // For now, return placeholder data
        return [
            'total_requests'        => 0,
            'successful_requests'   => 0,
            'failed_requests'       => 0,
            'rate_limited_requests' => 0,
            'endpoints'             => [],
        ];
    }

    /**
     * Get system request count for period
     */
    /**
     * Get  system request count
     */
    protected function getSystemRequestCount(string $period): int
    {
        // Aggregate from cache or analytics store
        return 0; // Placeholder
    }

    /**
     * Get unique API users for period
     */
    /**
     * Get  unique api users
     */
    protected function getUniqueApiUsers(string $period): int
    {
        return 0; // Placeholder
    }

    /**
     * Get top API endpoints for period
     */
    /**
     * Get  top endpoints
     */
    protected function getTopEndpoints(string $period): array
    {
        return []; // Placeholder
    }

    /**
     * Get rate limit violations for period
     */
    /**
     * Get  rate limit violations
     */
    protected function getRateLimitViolations(string $period): int
    {
        return 0; // Placeholder
    }

    /**
     * Get geographical distribution of API requests
     */
    /**
     * Get  geographical distribution
     */
    protected function getGeographicalDistribution(string $period): array
    {
        return []; // Placeholder
    }

    /**
     * Get average response times for period
     */
    /**
     * Get  average response times
     */
    protected function getAverageResponseTimes(string $period): array
    {
        return []; // Placeholder
    }
}
