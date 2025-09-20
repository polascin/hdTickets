<?php declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function array_slice;
use function count;

/**
 * Redis-based Rate Limiting Service
 *
 * Provides comprehensive rate limiting functionality using Redis for:
 * - API endpoints
 * - Scraping operations
 * - User actions
 * - Account protection
 */
class RedisRateLimitService
{
    protected $redis;

    protected $defaultTtl = 3600; // 1 hour

    public function __construct()
    {
        $this->redis = Redis::connection('rate_limiting');
    }

    /**
     * Check if request is within rate limits
     *
     * @param string $key          Unique identifier for the rate limit
     * @param int    $maxAttempts  Maximum attempts allowed
     * @param int    $decaySeconds Time window in seconds
     * @param string $prefix       Key prefix
     *
     * @return array Rate limit status
     */
    /**
     * CheckRateLimit
     */
    public function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds, string $prefix = 'rate_limit'): array
    {
        $redisKey = $this->buildKey($prefix, $key);
        $window = $this->getTimeWindow($decaySeconds);
        $windowKey = "{$redisKey}:{$window}";

        try {
            // Get current count
            $currentCount = (int) $this->redis->get($windowKey);

            // Check if limit exceeded
            $isAllowed = $currentCount < $maxAttempts;
            $remaining = max(0, $maxAttempts - $currentCount);
            $retryAfter = $isAllowed ? 0 : $this->getRetryAfter($decaySeconds, $window);

            return [
                'allowed'           => $isAllowed,
                'current_count'     => $currentCount,
                'max_attempts'      => $maxAttempts,
                'remaining'         => $remaining,
                'retry_after'       => $retryAfter,
                'window_expires_at' => Carbon::createFromTimestamp($window + $decaySeconds),
            ];
        } catch (Exception $e) {
            Log::error('Rate limit check failed', [
                'key'   => $redisKey,
                'error' => $e->getMessage(),
            ]);

            // Fail open - allow request if Redis is unavailable
            return [
                'allowed'           => TRUE,
                'current_count'     => 0,
                'max_attempts'      => $maxAttempts,
                'remaining'         => $maxAttempts,
                'retry_after'       => 0,
                'window_expires_at' => now()->addSeconds($decaySeconds),
            ];
        }
    }

    /**
     * Increment rate limit counter
     *
     * @return int New count
     */
    /**
     * Hit
     */
    public function hit(string $key, int $decaySeconds, string $prefix = 'rate_limit'): int
    {
        $redisKey = $this->buildKey($prefix, $key);
        $window = $this->getTimeWindow($decaySeconds);
        $windowKey = "{$redisKey}:{$window}";

        try {
            // Increment counter
            $newCount = $this->redis->incr($windowKey);

            // Set expiration on first hit
            if ($newCount === 1) {
                $this->redis->expire($windowKey, $decaySeconds);
            }

            return $newCount;
        } catch (Exception $e) {
            Log::error('Rate limit hit failed', [
                'key'   => $redisKey,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Rate limit for API endpoints
     */
    /**
     * LimitApiRequest
     */
    public function limitApiRequest(Request $request, string $endpoint, int $maxAttempts = 60, int $decayMinutes = 1): array
    {
        $key = $this->buildApiKey($request, $endpoint);

        return $this->checkRateLimit($key, $maxAttempts, $decayMinutes * 60, 'api');
    }

    /**
     * Rate limit for scraping operations
     */
    /**
     * LimitScrapingRequest
     */
    public function limitScrapingRequest(string $platform, string $userAgent, string $ipAddress, int $maxAttempts = 10, int $decaySeconds = 60): array
    {
        $key = $this->buildScrapingKey($platform, $userAgent, $ipAddress);

        return $this->checkRateLimit($key, $maxAttempts, $decaySeconds, 'scraping');
    }

    /**
     * Rate limit for user login attempts
     */
    /**
     * LimitLoginAttempts
     */
    public function limitLoginAttempts(string $email, string $ip, int $maxAttempts = 5, int $decayMinutes = 15): array
    {
        // Check both email and IP separately
        $emailKey = 'login:email:' . hash('sha256', $email);
        $ipKey = 'login:ip:' . hash('sha256', $ip);

        $emailLimit = $this->checkRateLimit($emailKey, $maxAttempts, $decayMinutes * 60, 'auth');
        $ipLimit = $this->checkRateLimit($ipKey, $maxAttempts * 3, $decayMinutes * 60, 'auth'); // More lenient for IP

        // Most restrictive wins
        return [
            'allowed'        => $emailLimit['allowed'] && $ipLimit['allowed'],
            'email_attempts' => $emailLimit['current_count'],
            'ip_attempts'    => $ipLimit['current_count'],
            'retry_after'    => max($emailLimit['retry_after'], $ipLimit['retry_after']),
            'blocked_by'     => $emailLimit['allowed'] ? (!$ipLimit['allowed'] ? 'ip' : NULL) : ('email'),
        ];
    }

    /**
     * Rate limit for bulk operations
     */
    /**
     * LimitBulkOperation
     */
    public function limitBulkOperation(int $userId, string $operation, int $maxAttempts = 10, int $decayMinutes = 60): array
    {
        $key = "bulk:{$operation}:user:{$userId}";

        return $this->checkRateLimit($key, $maxAttempts, $decayMinutes * 60, 'bulk');
    }

    /**
     * Rate limit for CAPTCHA solving requests
     */
    /**
     * LimitCaptchaRequests
     */
    public function limitCaptchaRequests(string $service, string $userAgent, int $maxAttempts = 50, int $decayMinutes = 60): array
    {
        $key = "captcha:{$service}:" . hash('sha256', $userAgent);

        return $this->checkRateLimit($key, $maxAttempts, $decayMinutes * 60, 'captcha');
    }

    /**
     * Global rate limiting per IP
     */
    /**
     * LimitGlobalRequests
     */
    public function limitGlobalRequests(string $ip, int $maxAttempts = 1000, int $decayMinutes = 60): array
    {
        $key = 'global:ip:' . hash('sha256', $ip);

        return $this->checkRateLimit($key, $maxAttempts, $decayMinutes * 60, 'global');
    }

    /**
     * Increment login attempt counter
     */
    /**
     * RecordLoginAttempt
     */
    public function recordLoginAttempt(string $email, string $ip, int $decayMinutes = 15): void
    {
        $emailKey = 'login:email:' . hash('sha256', $email);
        $ipKey = 'login:ip:' . hash('sha256', $ip);

        $this->hit($emailKey, $decayMinutes * 60, 'auth');
        $this->hit($ipKey, $decayMinutes * 60, 'auth');
    }

    /**
     * Clear rate limit for a key
     */
    /**
     * ClearRateLimit
     */
    public function clearRateLimit(string $key, string $prefix = 'rate_limit'): bool
    {
        try {
            $redisKey = $this->buildKey($prefix, $key);
            $pattern = "{$redisKey}:*";

            // Get all keys matching the pattern
            $keys = $this->redis->keys($pattern);

            if (!empty($keys)) {
                $this->redis->del($keys);
            }

            return TRUE;
        } catch (Exception $e) {
            Log::error('Clear rate limit failed', [
                'key'    => $key,
                'prefix' => $prefix,
                'error'  => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get rate limit statistics
     */
    /**
     * Get  statistics
     */
    public function getStatistics(?string $prefix = NULL): array
    {
        try {
            $prefixes = $prefix ? [$prefix] : ['api', 'scraping', 'auth', 'bulk', 'captcha', 'global'];
            $stats = [];

            foreach ($prefixes as $p) {
                $pattern = "{$p}:*";
                $keys = $this->redis->keys($pattern);

                $stats[$p] = [
                    'active_limits'    => count($keys),
                    'total_requests'   => 0,
                    'blocked_requests' => 0,
                ];

                // Sample some keys to get request counts
                $sampleKeys = array_slice($keys, 0, 100);
                foreach ($sampleKeys as $key) {
                    $value = $this->redis->get($key);
                    if ($value) {
                        $stats[$p]['total_requests'] += (int) $value;
                    }
                }
            }

            return $stats;
        } catch (Exception $e) {
            Log::error('Get rate limit statistics failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Monitor for suspicious activity patterns
     */
    /**
     * DetectSuspiciousActivity
     */
    public function detectSuspiciousActivity(string $ip, int $threshold = 100, int $timeWindow = 300): bool
    {
        try {
            $key = 'suspicious:ip:' . hash('sha256', $ip);
            $window = $this->getTimeWindow($timeWindow);
            $windowKey = "{$key}:{$window}";

            $currentCount = (int) $this->redis->get($windowKey);

            if ($currentCount >= $threshold) {
                // Log suspicious activity
                Log::warning('Suspicious activity detected', [
                    'ip'        => $ip,
                    'count'     => $currentCount,
                    'threshold' => $threshold,
                    'window'    => $timeWindow,
                ]);

                return TRUE;
            }

            return FALSE;
        } catch (Exception $e) {
            Log::error('Suspicious activity detection failed', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Track request for suspicious activity monitoring
     */
    /**
     * TrackRequest
     */
    public function trackRequest(string $ip, int $timeWindow = 300): void
    {
        try {
            $key = 'suspicious:ip:' . hash('sha256', $ip);
            $window = $this->getTimeWindow($timeWindow);
            $windowKey = "{$key}:{$window}";

            $newCount = $this->redis->incr($windowKey);

            if ($newCount === 1) {
                $this->redis->expire($windowKey, $timeWindow);
            }
        } catch (Exception $e) {
            Log::error('Request tracking failed', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get leaky bucket rate limiter (for smoother rate limiting)
     *
     * @param float $leakRate (tokens per second)
     */
    /**
     * CheckLeakyBucket
     */
    public function checkLeakyBucket(string $key, int $capacity, float $leakRate, string $prefix = 'bucket'): array
    {
        $redisKey = $this->buildKey($prefix, $key);
        $now = microtime(TRUE);

        try {
            // Get current bucket state
            $bucketData = $this->redis->hmget($redisKey, ['tokens', 'last_update']);
            $currentTokens = $bucketData[0] !== NULL ? (float) $bucketData[0] : $capacity;
            $lastUpdate = $bucketData[1] !== NULL ? (float) $bucketData[1] : $now;

            // Calculate tokens to leak
            $timePassed = $now - $lastUpdate;
            $tokensToLeak = $timePassed * $leakRate;
            $newTokens = min($capacity, $currentTokens + $tokensToLeak);

            // Check if request can be processed
            $allowed = $newTokens >= 1;

            if ($allowed) {
                --$newTokens;
            }

            // Update bucket state
            $this->redis->hmset($redisKey, [
                'tokens'      => $newTokens,
                'last_update' => $now,
            ]);
            $this->redis->expire($redisKey, 3600); // Expire after 1 hour of inactivity

            return [
                'allowed'          => $allowed,
                'tokens_remaining' => $newTokens,
                'capacity'         => $capacity,
                'leak_rate'        => $leakRate,
                'retry_after'      => $allowed ? 0 : (1 - $newTokens) / $leakRate,
            ];
        } catch (Exception $e) {
            Log::error('Leaky bucket check failed', [
                'key'   => $redisKey,
                'error' => $e->getMessage(),
            ]);

            // Fail open
            return [
                'allowed'          => TRUE,
                'tokens_remaining' => $capacity,
                'capacity'         => $capacity,
                'leak_rate'        => $leakRate,
                'retry_after'      => 0,
            ];
        }
    }

    /**
     * Cleanup expired rate limit keys (run periodically)
     *
     * @return int Number of keys cleaned up
     */
    /**
     * Cleanup
     */
    public function cleanup(): int
    {
        try {
            $patterns = [
                'rate_limit:*',
                'suspicious:*',
                'bucket:*',
            ];

            $cleaned = 0;

            foreach ($patterns as $pattern) {
                $keys = $this->redis->keys($pattern);

                foreach ($keys as $key) {
                    $ttl = $this->redis->ttl($key);

                    // Remove keys that have expired or have no TTL but are old
                    // No TTL set, check if it's an old key by trying to parse timestamp
                    if ($ttl === -1 && preg_match('/:(\d+)$/', (string) $key, $matches)) {
                        $timestamp = (int) $matches[1];
                        if ($timestamp < (time() - 3600)) { // Older than 1 hour
                            $this->redis->del($key);
                            $cleaned++;
                        }
                    }
                }
            }

            return $cleaned;
        } catch (Exception $e) {
            Log::error('Rate limit cleanup failed', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Build Redis key
     */
    /**
     * BuildKey
     */
    protected function buildKey(string $prefix, string $key): string
    {
        return "rate_limit:{$prefix}:{$key}";
    }

    /**
     * Build API rate limit key
     */
    /**
     * BuildApiKey
     */
    protected function buildApiKey(Request $request, string $endpoint): string
    {
        $user = $request->user();

        if ($user) {
            return "api:{$endpoint}:user:{$user->id}";
        }

        return "api:{$endpoint}:ip:" . hash('sha256', (string) $request->ip());
    }

    /**
     * Build scraping rate limit key
     */
    /**
     * BuildScrapingKey
     */
    protected function buildScrapingKey(string $platform, string $userAgent, string $ipAddress): string
    {
        $userAgentHash = hash('md5', $userAgent);
        $ipHash = hash('sha256', $ipAddress);

        return "scraping:{$platform}:{$userAgentHash}:{$ipHash}";
    }

    /**
     * Get time window for rate limiting
     */
    /**
     * Get  time window
     */
    protected function getTimeWindow(int $decaySeconds): int
    {
        return (int) (time() / $decaySeconds) * $decaySeconds;
    }

    /**
     * Calculate retry after seconds
     */
    /**
     * Get  retry after
     */
    protected function getRetryAfter(int $decaySeconds, int $window): int
    {
        return ($window + $decaySeconds) - time();
    }
}
