<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PasswordCompromiseCheckService
{
    /**
     * HaveIBeenPwned API endpoint for password ranges
     */
    const HIBP_API_URL = 'https://api.pwnedpasswords.com/range/';

    /**
     * Cache TTL for password checks (24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Check if password appears in known data breaches
     *
     * @param string $password
     * @return array
     */
    public function checkPasswordCompromise(string $password): array
    {
        try {
            // Generate SHA-1 hash of password
            $sha1Hash = strtoupper(sha1($password));
            $hashPrefix = substr($sha1Hash, 0, 5);
            $hashSuffix = substr($sha1Hash, 5);

            // Check cache first
            $cacheKey = "password_check_{$hashPrefix}";
            $response = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($hashPrefix) {
                return $this->fetchPasswordHashes($hashPrefix);
            });

            if ($response === null) {
                return $this->createResponse(false, 0, 'Unable to check password compromise status');
            }

            // Parse response and look for our hash suffix
            $compromiseCount = $this->parseResponseForHash($response, $hashSuffix);

            return $this->createResponse(
                $compromiseCount > 0,
                $compromiseCount,
                $compromiseCount > 0 
                    ? "This password has been found in {$compromiseCount} data breach(es)" 
                    : "This password was not found in known data breaches"
            );

        } catch (\Exception $e) {
            Log::warning('Password compromise check failed', [
                'error' => $e->getMessage(),
                'hash_prefix' => $hashPrefix ?? 'unknown'
            ]);

            return $this->createResponse(false, 0, 'Unable to check password compromise status');
        }
    }

    /**
     * Fetch password hashes from HaveIBeenPwned API
     *
     * @param string $hashPrefix
     * @return string|null
     */
    private function fetchPasswordHashes(string $hashPrefix): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'HDTickets-PasswordChecker/1.0',
                    'Add-Padding' => 'true'
                ])
                ->get(self::HIBP_API_URL . $hashPrefix);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning('HIBP API request failed', [
                'status' => $response->status(),
                'hash_prefix' => $hashPrefix
            ]);

            return null;

        } catch (\Exception $e) {
            Log::warning('HIBP API request exception', [
                'error' => $e->getMessage(),
                'hash_prefix' => $hashPrefix
            ]);

            return null;
        }
    }

    /**
     * Parse API response to find specific hash suffix
     *
     * @param string $response
     * @param string $hashSuffix
     * @return int
     */
    private function parseResponseForHash(string $response, string $hashSuffix): int
    {
        $lines = explode("\n", trim($response));

        foreach ($lines as $line) {
            $parts = explode(':', trim($line));
            if (count($parts) === 2) {
                [$suffix, $count] = $parts;
                if (strtoupper($suffix) === $hashSuffix) {
                    return (int) $count;
                }
            }
        }

        return 0;
    }

    /**
     * Create standardized response array
     *
     * @param bool $isCompromised
     * @param int $count
     * @param string $message
     * @return array
     */
    private function createResponse(bool $isCompromised, int $count, string $message): array
    {
        return [
            'is_compromised' => $isCompromised,
            'breach_count' => $count,
            'message' => $message,
            'severity' => $this->getSeverityLevel($count),
            'recommendation' => $this->getRecommendation($isCompromised, $count),
            'checked_at' => now()->toISOString()
        ];
    }

    /**
     * Get severity level based on breach count
     *
     * @param int $count
     * @return string
     */
    private function getSeverityLevel(int $count): string
    {
        if ($count === 0) return 'safe';
        if ($count < 10) return 'low';
        if ($count < 100) return 'medium';
        if ($count < 1000) return 'high';
        return 'critical';
    }

    /**
     * Get recommendation based on compromise status
     *
     * @param bool $isCompromised
     * @param int $count
     * @return string
     */
    private function getRecommendation(bool $isCompromised, int $count): string
    {
        if (!$isCompromised) {
            return 'This password appears to be safe from known data breaches.';
        }

        if ($count < 10) {
            return 'This password has appeared in a few data breaches. Consider using a different password.';
        }

        if ($count < 100) {
            return 'This password has been compromised multiple times. We strongly recommend choosing a different password.';
        }

        return 'This password is very commonly compromised and should not be used. Please choose a completely different password.';
    }

    /**
     * Check multiple passwords at once
     *
     * @param array $passwords
     * @return array
     */
    public function checkMultiplePasswords(array $passwords): array
    {
        $results = [];

        foreach ($passwords as $key => $password) {
            $results[$key] = $this->checkPasswordCompromise($password);
        }

        return $results;
    }

    /**
     * Get password compromise validation rule
     *
     * @param bool $strict Whether to reject any compromised password
     * @return \Closure
     */
    public function getCompromiseValidationRule(bool $strict = false): \Closure
    {
        return function ($attribute, $value, $fail) use ($strict) {
            $result = $this->checkPasswordCompromise($value);

            if ($result['is_compromised']) {
                if ($strict || $result['breach_count'] >= 100) {
                    $fail('This password has been found in data breaches and cannot be used.');
                } elseif ($result['breach_count'] >= 10) {
                    // Warning but don't fail - could be logged for admin review
                    Log::info('User attempted to use compromised password', [
                        'breach_count' => $result['breach_count'],
                        'severity' => $result['severity']
                    ]);
                }
            }
        };
    }

    /**
     * Get compromise check statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $cacheKeys = Cache::get('hibp_cache_keys', []);
        
        return [
            'cached_prefixes' => count($cacheKeys),
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'api_status' => $this->checkApiStatus(),
            'last_check' => Cache::get('hibp_last_check'),
            'total_checks_today' => Cache::get('hibp_daily_checks', 0)
        ];
    }

    /**
     * Calculate cache hit rate
     *
     * @return float
     */
    private function calculateCacheHitRate(): float
    {
        $hits = Cache::get('hibp_cache_hits', 0);
        $total = Cache::get('hibp_total_requests', 0);

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Check API status
     *
     * @return array
     */
    private function checkApiStatus(): array
    {
        try {
            // Use a known compromised hash prefix for testing
            $testResponse = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'HDTickets-PasswordChecker/1.0'])
                ->get(self::HIBP_API_URL . '5E884');

            return [
                'status' => $testResponse->successful() ? 'online' : 'error',
                'response_time' => $testResponse->transferStats->getTransferTime() ?? null,
                'last_checked' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'error' => $e->getMessage(),
                'last_checked' => now()->toISOString()
            ];
        }
    }

    /**
     * Clear password check cache
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        $cacheKeys = Cache::get('hibp_cache_keys', []);
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        Cache::forget('hibp_cache_keys');
        Cache::forget('hibp_cache_hits');
        Cache::forget('hibp_total_requests');
        Cache::forget('hibp_daily_checks');

        return true;
    }

    /**
     * Increment cache statistics
     *
     * @param bool $wasHit
     * @return void
     */
    private function incrementCacheStats(bool $wasHit): void
    {
        Cache::increment('hibp_total_requests');
        
        if ($wasHit) {
            Cache::increment('hibp_cache_hits');
        }

        // Daily counter with expiration
        $dailyKey = 'hibp_daily_checks_' . now()->format('Y-m-d');
        Cache::increment($dailyKey, 1);
        Cache::put($dailyKey, Cache::get($dailyKey, 1), now()->endOfDay());
    }
}
