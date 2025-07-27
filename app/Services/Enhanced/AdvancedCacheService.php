<?php

namespace App\Services\Enhanced;

use App\Models\ScrapedTicket;
use App\Models\User;
use App\Services\PerformanceCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdvancedCacheService extends PerformanceCacheService
{
    const CACHE_TTL_MICRO = 30;      // 30 seconds for real-time data
    const CACHE_TTL_SHORT = 120;     // 2 minutes for frequently changing data
    const CACHE_TTL_MEDIUM = 300;    // 5 minutes for semi-static data
    const CACHE_TTL_LONG = 900;      // 15 minutes for stable data
    const CACHE_TTL_EXTENDED = 3600; // 1 hour for rarely changing data

    private $redis;
    
    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    /**
     * Multi-layered cache retrieval with fallback
     */
    public function getMultiLayered(string $key, callable $callback, int $ttl = self::CACHE_TTL_MEDIUM): mixed
    {
        // Layer 1: Memory cache (Redis)
        try {
            $cached = $this->redis->get($key);
            if ($cached !== null) {
                Log::channel('performance')->debug('Cache hit (Redis)', ['key' => $key]);
                return unserialize($cached);
            }
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Redis cache miss', ['key' => $key, 'error' => $e->getMessage()]);
        }

        // Layer 2: Application cache
        $result = Cache::remember($key, $ttl, function () use ($callback, $key) {
            Log::channel('performance')->info('Cache miss - executing callback', ['key' => $key]);
            return $callback();
        });

        // Store in Redis for faster access
        try {
            $this->redis->setex($key, min($ttl, 300), serialize($result));
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Redis cache store failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Intelligent cache warming based on usage patterns
     */
    public function intelligentWarmUp(): void
    {
        $popularKeys = [
            'ticket_stats' => [$this, 'getTicketStats'],
            'platform_breakdown' => [$this, 'getPlatformBreakdown'],
            'trending_events' => [$this, 'getTrendingEvents'],
            'user_activity_stats' => [$this, 'getUserActivityStats'],
        ];

        foreach ($popularKeys as $key => $callback) {
            if (!Cache::has($key)) {
                $this->getMultiLayered($key, $callback, self::CACHE_TTL_MEDIUM);
                Log::channel('performance')->info('Cache warmed', ['key' => $key]);
            }
        }

        // Warm popular search patterns
        $this->warmPopularSearches();
    }

    /**
     * Warm cache for popular search patterns
     */
    private function warmPopularSearches(): void
    {
        $popularSearches = [
            ['platform' => 'ticketmaster', 'sport' => 'football'],
            ['platform' => 'stubhub', 'sport' => 'basketball'],
            ['is_high_demand' => true, 'is_available' => true],
            ['event_date' => Carbon::now()->addWeeks(1)->toDateString()],
        ];

        foreach ($popularSearches as $criteria) {
            $cacheKey = 'popular_search_' . md5(serialize($criteria));
            
            if (!Cache::has($cacheKey)) {
                $this->getMultiLayered($cacheKey, function () use ($criteria) {
                    return ScrapedTicket::where($criteria)->limit(50)->get();
                }, self::CACHE_TTL_SHORT);
            }
        }
    }

    /**
     * Enhanced ticket statistics with performance optimization
     */
    public function getEnhancedTicketStats(): array
    {
        return $this->getMultiLayered('enhanced_ticket_stats', function () {
            // Use single query with aggregation for better performance
            $stats = DB::table('scraped_tickets')
                ->selectRaw('
                    COUNT(*) as total_tickets,
                    COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_tickets,
                    COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_tickets,
                    COUNT(DISTINCT platform) as platforms_monitored,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_tickets,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_tickets,
                    AVG(CASE WHEN min_price IS NOT NULL THEN min_price END) as avg_price,
                    MIN(CASE WHEN min_price IS NOT NULL THEN min_price END) as min_price,
                    MAX(CASE WHEN max_price IS NOT NULL THEN max_price END) as max_price
                ')
                ->first();

            return [
                'total_tickets' => $stats->total_tickets ?? 0,
                'available_tickets' => $stats->available_tickets ?? 0,
                'high_demand_tickets' => $stats->high_demand_tickets ?? 0,
                'platforms_monitored' => $stats->platforms_monitored ?? 0,
                'today_tickets' => $stats->today_tickets ?? 0,
                'this_week_tickets' => $stats->week_tickets ?? 0,
                'avg_price' => round($stats->avg_price ?? 0, 2),
                'min_price' => $stats->min_price ?? 0,
                'max_price' => $stats->max_price ?? 0,
                'updated_at' => now()->toISOString(),
            ];
        }, self::CACHE_TTL_MEDIUM);
    }

    /**
     * Optimized platform breakdown with Redis sorted sets
     */
    public function getOptimizedPlatformBreakdown(): array
    {
        return $this->getMultiLayered('optimized_platform_breakdown', function () {
            // Use more efficient grouping query
            $breakdown = DB::table('scraped_tickets')
                ->select([
                    'platform',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_count'),
                    DB::raw('AVG(CASE WHEN min_price IS NOT NULL THEN min_price END) as avg_price'),
                    DB::raw('COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_count'),
                    DB::raw('MAX(created_at) as last_update')
                ])
                ->groupBy('platform')
                ->orderByDesc('count')
                ->get();

            return $breakdown->mapWithKeys(function ($item) {
                return [$item->platform => [
                    'count' => $item->count,
                    'available_count' => $item->available_count,
                    'avg_price' => round($item->avg_price ?? 0, 2),
                    'high_demand_count' => $item->high_demand_count,
                    'availability_rate' => $item->count > 0 ? round(($item->available_count / $item->count) * 100, 2) : 0,
                    'last_update' => $item->last_update,
                ]];
            })->toArray();
        }, self::CACHE_TTL_MEDIUM);
    }

    /**
     * Real-time metrics with micro-caching
     */
    public function getRealTimeMetrics(): array
    {
        return $this->getMultiLayered('realtime_metrics', function () {
            return [
                'active_scraping_sessions' => $this->getActiveScrapingSessions(),
                'recent_ticket_updates' => $this->getRecentTicketUpdates(),
                'system_load' => $this->getSystemLoadMetrics(),
                'cache_performance' => $this->getCachePerformanceMetrics(),
            ];
        }, self::CACHE_TTL_MICRO);
    }

    /**
     * Get active scraping sessions count
     */
    private function getActiveScrapingSessions(): int
    {
        try {
            // Count active Redis keys for scraping sessions
            $pattern = 'scraping_session:*';
            $keys = $this->redis->keys($pattern);
            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get recent ticket updates
     */
    private function getRecentTicketUpdates(): int
    {
        return ScrapedTicket::where('updated_at', '>=', Carbon::now()->subMinutes(5))->count();
    }

    /**
     * Get system load metrics
     */
    private function getSystemLoadMetrics(): array
    {
        return [
            'database_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
            'redis_memory_usage' => $this->getRedisMemoryUsage(),
            'cache_hit_rate' => $this->calculateCacheHitRate(),
        ];
    }

    /**
     * Get Redis memory usage
     */
    private function getRedisMemoryUsage(): array
    {
        try {
            $info = $this->redis->info('memory');
            return [
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'used_memory_percentage' => isset($info['used_memory'], $info['maxmemory']) && $info['maxmemory'] > 0 
                    ? round(($info['used_memory'] / $info['maxmemory']) * 100, 2) 
                    : 0,
            ];
        } catch (\Exception $e) {
            return ['used_memory' => 0, 'used_memory_human' => '0B', 'used_memory_percentage' => 0];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateCacheHitRate(): float
    {
        try {
            $info = $this->redis->info('stats');
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache performance metrics
     */
    private function getCachePerformanceMetrics(): array
    {
        return [
            'redis_hit_rate' => $this->calculateCacheHitRate(),
            'active_keys' => $this->redis->dbsize(),
            'expired_keys' => $this->redis->info('stats')['expired_keys'] ?? 0,
            'memory_fragmentation_ratio' => $this->redis->info('memory')['mem_fragmentation_ratio'] ?? 1,
        ];
    }

    /**
     * Advanced search results caching with query fingerprinting
     */
    public function getCachedSearchResults(array $criteria, callable $searchCallback): array
    {
        $fingerprint = $this->generateQueryFingerprint($criteria);
        $cacheKey = "search_results:{$fingerprint}";

        return $this->getMultiLayered($cacheKey, function () use ($searchCallback, $criteria) {
            $results = $searchCallback($criteria);
            
            // Cache metadata for analytics
            $this->storeCacheMetadata($cacheKey, [
                'criteria' => $criteria,
                'result_count' => count($results),
                'cached_at' => now()->toISOString(),
            ]);

            return $results;
        }, self::CACHE_TTL_SHORT);
    }

    /**
     * Generate query fingerprint for consistent caching
     */
    private function generateQueryFingerprint(array $criteria): string
    {
        // Sort criteria for consistent fingerprinting
        ksort($criteria);
        return hash('sha256', serialize($criteria));
    }

    /**
     * Store cache metadata for analytics
     */
    private function storeCacheMetadata(string $cacheKey, array $metadata): void
    {
        try {
            $metadataKey = "cache_metadata:{$cacheKey}";
            $this->redis->setex($metadataKey, 3600, serialize($metadata));
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Failed to store cache metadata', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk cache invalidation with pattern matching
     */
    public function bulkInvalidate(array $patterns): int
    {
        $invalidated = 0;
        
        foreach ($patterns as $pattern) {
            try {
                $keys = $this->redis->keys($pattern);
                if (!empty($keys)) {
                    $this->redis->del($keys);
                    $invalidated += count($keys);
                }
                
                // Also clear Laravel cache
                Cache::flush();
                
            } catch (\Exception $e) {
                Log::channel('performance')->error('Bulk invalidation failed', [
                    'pattern' => $pattern,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::channel('performance')->info('Bulk cache invalidation completed', [
            'patterns' => $patterns,
            'invalidated_keys' => $invalidated
        ]);

        return $invalidated;
    }

    /**
     * Cache preloading for anticipated requests
     */
    public function preloadAnticipatedData(): void
    {
        $preloadTasks = [
            'upcoming_events' => function () {
                return ScrapedTicket::where('event_date', '>=', Carbon::now())
                    ->where('event_date', '<=', Carbon::now()->addWeeks(2))
                    ->where('is_available', true)
                    ->orderBy('event_date')
                    ->limit(100)
                    ->get();
            },
            'trending_platforms' => function () {
                return $this->getOptimizedPlatformBreakdown();
            },
            'price_analytics' => function () {
                return $this->getPriceAnalytics();
            }
        ];

        foreach ($preloadTasks as $key => $callback) {
            $this->getMultiLayered("preload:{$key}", $callback, self::CACHE_TTL_LONG);
        }
    }

    /**
     * Get price analytics with caching
     */
    private function getPriceAnalytics(): array
    {
        $result = DB::table('scraped_tickets')
            ->selectRaw('
                AVG(min_price) as avg_min_price,
                AVG(max_price) as avg_max_price,
                MIN(min_price) as lowest_price,
                MAX(max_price) as highest_price,
                COUNT(CASE WHEN min_price < 50 THEN 1 END) as under_50,
                COUNT(CASE WHEN min_price BETWEEN 50 AND 100 THEN 1 END) as price_50_100,
                COUNT(CASE WHEN min_price BETWEEN 100 AND 200 THEN 1 END) as price_100_200,
                COUNT(CASE WHEN min_price BETWEEN 200 AND 500 THEN 1 END) as price_200_500,
                COUNT(CASE WHEN min_price > 500 THEN 1 END) as above_500
            ')
            ->where('is_available', true)
            ->first();
            
        return $result ? (array) $result : [];
    }

    /**
     * Advanced cache statistics and health monitoring
     */
    public function getAdvancedCacheStats(): array
    {
        return [
            'redis_info' => $this->getRedisInfo(),
            'cache_distribution' => $this->getCacheDistribution(),
            'performance_metrics' => $this->getCachePerformanceMetrics(),
            'memory_analysis' => $this->getMemoryAnalysis(),
        ];
    }

    /**
     * Get comprehensive Redis information
     */
    private function getRedisInfo(): array
    {
        try {
            return [
                'version' => $this->redis->info('server')['redis_version'] ?? 'unknown',
                'uptime' => $this->redis->info('server')['uptime_in_seconds'] ?? 0,
                'connected_clients' => $this->redis->info('clients')['connected_clients'] ?? 0,
                'total_commands_processed' => $this->redis->info('stats')['total_commands_processed'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get cache key distribution analysis
     */
    private function getCacheDistribution(): array
    {
        try {
            $patterns = [
                'ticket_*' => 'Ticket Data',
                'search_*' => 'Search Results',
                'platform_*' => 'Platform Stats',
                'user_*' => 'User Data',
                'cache_*' => 'Cache Metadata',
            ];

            $distribution = [];
            foreach ($patterns as $pattern => $label) {
                $keys = $this->redis->keys($pattern);
                $distribution[$label] = count($keys);
            }

            return $distribution;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get memory analysis
     */
    private function getMemoryAnalysis(): array
    {
        try {
            $info = $this->redis->info('memory');
            return [
                'peak_memory' => $info['used_memory_peak_human'] ?? '0B',
                'memory_efficiency' => isset($info['used_memory'], $info['used_memory_rss']) 
                    ? round(($info['used_memory'] / $info['used_memory_rss']) * 100, 2)
                    : 100,
                'fragmentation_ratio' => $info['mem_fragmentation_ratio'] ?? 1,
                'allocator' => $info['mem_allocator'] ?? 'unknown',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
