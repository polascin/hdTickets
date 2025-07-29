<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlatformCachingService
{
    /**
     * Cache TTL values for different data types (in seconds)
     */
    const CACHE_TTL = [
        'search_results' => 300,     // 5 minutes for search results
        'event_details' => 1800,     // 30 minutes for event details
        'venue_info' => 3600,        // 1 hour for venue information
        'platform_stats' => 900,     // 15 minutes for statistics
        'rate_limits' => 3600,       // 1 hour for rate limit data
        'selectors' => 86400,        // 24 hours for selector effectiveness
        'html_responses' => 600,     // 10 minutes for HTML responses (debug)
    ];

    /**
     * Cache key prefixes for organization
     */
    const CACHE_PREFIXES = [
        'search' => 'platform_search',
        'event' => 'platform_event',
        'venue' => 'platform_venue',
        'stats' => 'platform_stats',
        'rate_limit' => 'rate_limit',
        'selector' => 'selector_stats',
        'html' => 'html_response',
    ];

    /**
     * Generate a cache key for search results
     */
    public function getSearchCacheKey(string $platform, array $criteria): string
    {
        $hash = md5(json_encode(array_merge($criteria, ['platform' => $platform])));
        return self::CACHE_PREFIXES['search'] . ":{$platform}:{$hash}";
    }

    /**
     * Get cached search results
     */
    public function getCachedSearchResults(string $platform, array $criteria): ?array
    {
        $cacheKey = $this->getSearchCacheKey($platform, $criteria);
        
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                Log::channel('ticket_apis')->info('Cache hit for search results', [
                    'platform' => $platform,
                    'cache_key' => $cacheKey,
                    'criteria' => $criteria
                ]);
                
                return $cached;
            }
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->warning('Cache retrieval failed', [
                'platform' => $platform,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Cache search results
     */
    public function cacheSearchResults(string $platform, array $criteria, array $results): void
    {
        $cacheKey = $this->getSearchCacheKey($platform, $criteria);
        $ttl = self::CACHE_TTL['search_results'];
        
        try {
            // Add metadata to cached results
            $cacheData = [
                'results' => $results,
                'cached_at' => now()->toISOString(),
                'platform' => $platform,
                'criteria' => $criteria,
                'result_count' => count($results)
            ];
            
            Cache::put($cacheKey, $cacheData, $ttl);
            
            Log::channel('ticket_apis')->info('Search results cached', [
                'platform' => $platform,
                'cache_key' => $cacheKey,
                'result_count' => count($results),
                'ttl' => $ttl
            ]);
            
            // Store cache statistics
            $this->updateCacheStats($platform, 'search', 'write');
            
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->error('Cache storage failed', [
                'platform' => $platform,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cached event details
     */
    public function getCachedEventDetails(string $platform, string $eventId): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['event'] . ":{$platform}:{$eventId}";
        
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                Log::channel('ticket_apis')->info('Cache hit for event details', [
                    'platform' => $platform,
                    'event_id' => $eventId,
                    'cache_key' => $cacheKey
                ]);
                
                $this->updateCacheStats($platform, 'event', 'hit');
                return $cached;
            }
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->warning('Event cache retrieval failed', [
                'platform' => $platform,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }

        $this->updateCacheStats($platform, 'event', 'miss');
        return null;
    }

    /**
     * Cache event details
     */
    public function cacheEventDetails(string $platform, string $eventId, array $eventData): void
    {
        $cacheKey = self::CACHE_PREFIXES['event'] . ":{$platform}:{$eventId}";
        $ttl = self::CACHE_TTL['event_details'];
        
        try {
            $cacheData = [
                'event_data' => $eventData,
                'cached_at' => now()->toISOString(),
                'platform' => $platform,
                'event_id' => $eventId
            ];
            
            Cache::put($cacheKey, $cacheData, $ttl);
            
            $this->updateCacheStats($platform, 'event', 'write');
            
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->error('Event cache storage failed', [
                'platform' => $platform,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cache platform statistics
     */
    public function cachePlatformStats(string $platform, array $stats): void
    {
        $cacheKey = self::CACHE_PREFIXES['stats'] . ":{$platform}";
        $ttl = self::CACHE_TTL['platform_stats'];
        
        Cache::put($cacheKey, $stats, $ttl);
    }

    /**
     * Get cached platform statistics
     */
    public function getCachedPlatformStats(string $platform): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['stats'] . ":{$platform}";
        return Cache::get($cacheKey);
    }

    /**
     * Cache rate limit information
     */
    public function cacheRateLimit(string $platform, array $rateLimitData): void
    {
        $cacheKey = self::CACHE_PREFIXES['rate_limit'] . ":{$platform}";
        $ttl = self::CACHE_TTL['rate_limits'];
        
        Cache::put($cacheKey, $rateLimitData, $ttl);
    }

    /**
     * Get cached rate limit information
     */
    public function getCachedRateLimit(string $platform): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['rate_limit'] . ":{$platform}";
        return Cache::get($cacheKey);
    }

    /**
     * Cache selector effectiveness data
     */
    public function cacheSelectorStats(string $platform, string $selector, array $stats): void
    {
        $cacheKey = self::CACHE_PREFIXES['selector'] . ":{$platform}:" . md5($selector);
        $ttl = self::CACHE_TTL['selectors'];
        
        Cache::put($cacheKey, $stats, $ttl);
    }

    /**
     * Get cached selector effectiveness data
     */
    public function getCachedSelectorStats(string $platform, string $selector): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['selector'] . ":{$platform}:" . md5($selector);
        return Cache::get($cacheKey);
    }

    /**
     * Cache HTML response for debugging
     */
    public function cacheHtmlResponse(string $platform, string $url, string $html): void
    {
        if (!config('app.debug')) {
            return; // Only cache HTML in debug mode
        }
        
        $cacheKey = self::CACHE_PREFIXES['html'] . ":{$platform}:" . md5($url);
        $ttl = self::CACHE_TTL['html_responses'];
        
        $cacheData = [
            'html' => $html,
            'url' => $url,
            'cached_at' => now()->toISOString(),
            'size' => strlen($html)
        ];
        
        Cache::put($cacheKey, $cacheData, $ttl);
    }

    /**
     * Get cached HTML response
     */
    public function getCachedHtmlResponse(string $platform, string $url): ?string
    {
        if (!config('app.debug')) {
            return null;
        }
        
        $cacheKey = self::CACHE_PREFIXES['html'] . ":{$platform}:" . md5($url);
        $cached = Cache::get($cacheKey);
        
        return $cached ? $cached['html'] : null;
    }

    /**
     * Clear cache for a specific platform
     */
    public function clearPlatformCache(string $platform): void
    {
        $patterns = [
            self::CACHE_PREFIXES['search'] . ":{$platform}:*",
            self::CACHE_PREFIXES['event'] . ":{$platform}:*",
            self::CACHE_PREFIXES['venue'] . ":{$platform}:*",
            self::CACHE_PREFIXES['stats'] . ":{$platform}",
            self::CACHE_PREFIXES['rate_limit'] . ":{$platform}",
            self::CACHE_PREFIXES['selector'] . ":{$platform}:*",
            self::CACHE_PREFIXES['html'] . ":{$platform}:*",
        ];

        foreach ($patterns as $pattern) {
            $this->clearCachePattern($pattern);
        }

        Log::channel('ticket_apis')->info('Platform cache cleared', [
            'platform' => $platform
        ]);
    }

    /**
     * Clear cache entries matching a pattern (Redis only)
     */
    private function clearCachePattern(string $pattern): void
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->warning('Cache pattern clear failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update cache statistics
     */
    private function updateCacheStats(string $platform, string $type, string $operation): void
    {
        $statsKey = "cache_stats:{$platform}:{$type}";
        
        try {
            $stats = Cache::get($statsKey, [
                'hits' => 0,
                'misses' => 0,
                'writes' => 0,
                'last_activity' => null
            ]);
            
            $stats[$operation === 'hit' ? 'hits' : ($operation === 'miss' ? 'misses' : 'writes')]++;
            $stats['last_activity'] = now()->toISOString();
            
            Cache::put($statsKey, $stats, 86400); // Store stats for 24 hours
            
        } catch (\Exception $e) {
            // Ignore stats update failures to avoid breaking main functionality
        }
    }

    /**
     * Get cache statistics for a platform
     */
    public function getCacheStats(string $platform): array
    {
        $types = ['search', 'event', 'venue', 'stats'];
        $stats = [];
        
        foreach ($types as $type) {
            $statsKey = "cache_stats:{$platform}:{$type}";
            $typeStats = Cache::get($statsKey, [
                'hits' => 0,
                'misses' => 0,
                'writes' => 0,
                'last_activity' => null
            ]);
            
            $total = $typeStats['hits'] + $typeStats['misses'];
            $typeStats['hit_rate'] = $total > 0 ? round(($typeStats['hits'] / $total) * 100, 2) : 0;
            
            $stats[$type] = $typeStats;
        }
        
        return $stats;
    }

    /**
     * Warm cache for popular searches
     */
    public function warmCache(string $platform, array $popularSearches): void
    {
        foreach ($popularSearches as $searchCriteria) {
            $cacheKey = $this->getSearchCacheKey($platform, $searchCriteria);
            
            if (!Cache::has($cacheKey)) {
                // This would typically trigger a background job to perform the search
                // For now, we just log that cache warming is needed
                Log::channel('ticket_apis')->info('Cache warming needed', [
                    'platform' => $platform,
                    'criteria' => $searchCriteria,
                    'cache_key' => $cacheKey
                ]);
            }
        }
    }

    /**
     * Get cache memory usage statistics
     */
    public function getCacheMemoryStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info('memory');
                
                return [
                    'used_memory' => $info['used_memory'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? '0B',
                    'max_memory' => $info['maxmemory'] ?? 0,
                    'max_memory_human' => $info['maxmemory_human'] ?? '0B',
                ];
            }
        } catch (\Exception $e) {
            Log::channel('ticket_apis')->warning('Cache memory stats failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return [];
    }

    /**
     * Cleanup expired cache entries
     */
    public function cleanupExpiredCache(): void
    {
        Log::channel('ticket_apis')->info('Starting cache cleanup');
        
        // This is typically handled automatically by Redis/Memcached
        // But we can log cleanup activities for monitoring
        
        $platforms = ['funzone', 'stubhub', 'viagogo', 'tickpick', 'ticketmaster'];
        
        foreach ($platforms as $platform) {
            $stats = $this->getCacheStats($platform);
            
            Log::channel('ticket_apis')->info('Cache stats for platform', [
                'platform' => $platform,
                'stats' => $stats
            ]);
        }
    }
}
