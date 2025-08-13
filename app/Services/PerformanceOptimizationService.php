<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\ScrapedTicket;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceOptimizationService
{
    public function __construct()
    {
        // No dependencies needed - using Laravel's built-in Cache facade
    }

    /**
     * Warm cache for popular queries and upcoming events
     */
    /**
     * WarmCache
     */
    public function warmCache(): array
    {
        $results = [];

        try {
            // Warm popular event searches
            $results['popular_events'] = $this->warmPopularEvents();

            // Warm upcoming events by date
            $results['upcoming_events'] = $this->warmUpcomingEvents();

            // Warm platform statistics
            $results['platform_stats'] = $this->warmPlatformStatistics();

            // Warm user analytics
            $results['user_analytics'] = $this->warmUserAnalytics();

            // Warm hot sports events
            $results['hot_sports'] = $this->warmHotSportsEvents();

            Log::info('Cache warming completed successfully', $results);
        } catch (Exception $e) {
            Log::error('Cache warming failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Optimize database by analyzing tables and updating statistics
     */
    /**
     * OptimizeDatabase
     */
    public function optimizeDatabase(): array
    {
        $results = [];

        try {
            // Analyze frequently used tables
            $tables = ['scraped_tickets', 'events', 'users', 'purchase_attempts', 'ticket_alerts'];

            foreach ($tables as $table) {
                DB::statement("ANALYZE TABLE {$table}");
                $results['analyzed'][] = $table;
            }

            // Clean up old data
            $results['cleanup'] = $this->cleanupOldData();

            // Optimize table structure
            $results['optimization'] = $this->optimizeTableStructure();

            Log::info('Database optimization completed', $results);
        } catch (Exception $e) {
            Log::error('Database optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get cache statistics
     */
    /**
     * Get  cache statistics
     */
    public function getCacheStatistics(): array
    {
        return [
            'redis_info'     => $this->getRedisInfo(),
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'memory_usage'   => $this->getCacheMemoryUsage(),
        ];
    }

    /**
     * Warm cache for popular events based on recent activity
     */
    /**
     * WarmPopularEvents
     */
    private function warmPopularEvents(): int
    {
        $count = 0;

        // Get most scraped events in the last 7 days
        $popularEvents = ScrapedTicket::select('title', 'platform')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('title', 'platform')
            ->havingRaw('COUNT(*) >= 5')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(50)
            ->get();

        foreach ($popularEvents as $event) {
            $cacheKey = "popular_event:{$event->platform}:{$event->title}";

            // Cache event details for 2 hours
            $eventData = ScrapedTicket::where('title', $event->title)
                ->where('platform', $event->platform)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($eventData) {
                Cache::put($cacheKey, $eventData->toArray(), now()->addHours(2));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Warm cache for upcoming events in the next 30 days
     */
    /**
     * WarmUpcomingEvents
     */
    private function warmUpcomingEvents(): int
    {
        $count = 0;
        $dateRanges = [
            'today'      => [Carbon::today(), Carbon::today()->endOfDay()],
            'tomorrow'   => [Carbon::tomorrow(), Carbon::tomorrow()->endOfDay()],
            'this_week'  => [Carbon::now(), Carbon::now()->endOfWeek()],
            'next_week'  => [Carbon::now()->startOfWeek()->addWeek(), Carbon::now()->endOfWeek()->addWeek()],
            'this_month' => [Carbon::now(), Carbon::now()->endOfMonth()],
        ];

        foreach ($dateRanges as $period => $range) {
            $cacheKey = "upcoming_events:{$period}";

            $events = ScrapedTicket::whereBetween('event_date', $range)
                ->where('availability', '!=', 'sold_out')
                ->select('title', 'platform', 'event_date', 'venue', 'min_price', 'max_price')
                ->distinct()
                ->orderBy('event_date')
                ->limit(100)
                ->get();

            Cache::put($cacheKey, $events->toArray(), now()->addHours(3));
            $count += $events->count();
        }

        return $count;
    }

    /**
     * Warm cache for platform statistics
     */
    /**
     * WarmPlatformStatistics
     */
    private function warmPlatformStatistics(): int
    {
        $count = 0;
        $platforms = ['ticketmaster', 'stubhub', 'seatgeek', 'viagogo'];

        foreach ($platforms as $platform) {
            $stats = [
                'total_tickets'     => ScrapedTicket::where('platform', $platform)->count(),
                'available_tickets' => ScrapedTicket::where('platform', $platform)
                    ->where('availability', 'available')->count(),
                'avg_price' => ScrapedTicket::where('platform', $platform)
                    ->where('min_price', '>', 0)->avg('min_price'),
                'total_events' => ScrapedTicket::where('platform', $platform)
                    ->distinct('title')->count(),
                'last_updated' => ScrapedTicket::where('platform', $platform)
                    ->max('updated_at'),
            ];

            Cache::put("platform_stats:{$platform}", $stats, now()->addHours(1));
            $count++;
        }

        return $count;
    }

    /**
     * Warm cache for user analytics
     */
    /**
     * WarmUserAnalytics
     */
    private function warmUserAnalytics(): int
    {
        $analytics = [
            'total_users'        => User::count(),
            'active_users_today' => User::where('last_login_at', '>=', Carbon::today())->count(),
            'active_users_week'  => User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count(),
            'new_users_today'    => User::whereDate('created_at', Carbon::today())->count(),
            'admin_users'        => User::where('role', 'admin')->count(),
        ];

        Cache::put('user_analytics', $analytics, now()->addHours(1));

        return 1;
    }

    /**
     * Warm cache for hot sports events based on trending patterns
     */
    /**
     * WarmHotSportsEvents
     */
    private function warmHotSportsEvents(): int
    {
        $count = 0;
        $hotKeywords = ['NBA', 'NFL', 'MLB', 'NHL', 'FIFA', 'Champions League', 'Premier League', 'Concert'];

        foreach ($hotKeywords as $keyword) {
            $cacheKey = "hot_events:{$keyword}";

            $events = ScrapedTicket::where('title', 'LIKE', "%{$keyword}%")
                ->where('event_date', '>=', Carbon::now())
                ->where('availability', '!=', 'sold_out')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            if ($events->count() > 0) {
                Cache::put($cacheKey, $events->toArray(), now()->addHours(2));
                $count += $events->count();
            }
        }

        return $count;
    }

    /**
     * Clean up old data to improve performance
     */
    /**
     * CleanupOldData
     */
    private function cleanupOldData(): array
    {
        $results = [];

        // Remove old scraped tickets older than 30 days
        $oldTickets = ScrapedTicket::where('created_at', '<', Carbon::now()->subDays(30))
            ->where('availability', 'sold_out')
            ->count();

        ScrapedTicket::where('created_at', '<', Carbon::now()->subDays(30))
            ->where('availability', 'sold_out')
            ->delete();

        $results['old_tickets_removed'] = $oldTickets;

        // Clean up expired purchase attempts older than 7 days
        $expiredAttempts = DB::table('purchase_attempts')
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->where('status', 'expired')
            ->count();

        DB::table('purchase_attempts')
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->where('status', 'expired')
            ->delete();

        $results['expired_attempts_removed'] = $expiredAttempts;

        // Clean up old logs
        $oldLogs = DB::table('activity_log')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->count();

        DB::table('activity_log')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->delete();

        $results['old_logs_removed'] = $oldLogs;

        return $results;
    }

    /**
     * Optimize table structure and indexes
     */
    /**
     * OptimizeTableStructure
     */
    private function optimizeTableStructure(): array
    {
        $results = [];

        try {
            // Check and create missing indexes
            $indexes = [
                'scraped_tickets' => [
                    ['platform', 'title'],
                    ['event_date'],
                    ['availability'],
                    ['created_at'],
                ],
                'events' => [
                    ['date'],
                    ['category'],
                    ['status'],
                ],
                'purchase_attempts' => [
                    ['user_id', 'status'],
                    ['created_at'],
                ],
                'ticket_alerts' => [
                    ['user_id', 'active'],
                    ['platform'],
                ],
            ];

            foreach ($indexes as $table => $tableIndexes) {
                foreach ($tableIndexes as $columns) {
                    $indexName = $table . '_' . implode('_', $columns) . '_index';

                    // Check if index exists
                    $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

                    if (empty($exists)) {
                        $columnList = implode(', ', $columns);
                        DB::statement("CREATE INDEX {$indexName} ON {$table} ({$columnList})");
                        $results['indexes_created'][] = $indexName;
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning('Some indexes could not be created', [
                'error' => $e->getMessage(),
            ]);
            $results['index_errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get Redis information if Redis is being used
     */
    /**
     * Get  redis info
     */
    private function getRedisInfo(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getStore()->getRedis();
                $info = $redis->info();

                return [
                    'version'     => $info['redis_version'] ?? 'unknown',
                    'memory_used' => $info['used_memory_human'] ?? 'unknown',
                    'keys'        => $redis->dbsize(),
                    'hits'        => $info['keyspace_hits'] ?? 0,
                    'misses'      => $info['keyspace_misses'] ?? 0,
                ];
            }
        } catch (Exception $e) {
            Log::warning('Could not get Redis info', ['error' => $e->getMessage()]);
        }

        return ['status' => 'not_available'];
    }

    /**
     * Calculate cache hit rate
     */
    /**
     * CalculateCacheHitRate
     */
    private function calculateCacheHitRate(): float
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getStore()->getRedis();
                $info = $redis->info();

                $hits = $info['keyspace_hits'] ?? 0;
                $misses = $info['keyspace_misses'] ?? 0;
                $total = $hits + $misses;

                return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
            }
        } catch (Exception $e) {
            Log::warning('Could not calculate cache hit rate', ['error' => $e->getMessage()]);
        }

        return 0;
    }

    /**
     * Get cache memory usage
     */
    /**
     * Get  cache memory usage
     */
    private function getCacheMemoryUsage(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getStore()->getRedis();
                $info = $redis->info();

                return [
                    'used_memory'       => $info['used_memory'] ?? 0,
                    'used_memory_human' => $info['used_memory_human'] ?? '0B',
                    'max_memory'        => $info['maxmemory'] ?? 0,
                    'max_memory_human'  => $info['maxmemory_human'] ?? 'unlimited',
                ];
            }
        } catch (Exception $e) {
            Log::warning('Could not get cache memory usage', ['error' => $e->getMessage()]);
        }

        return ['status' => 'not_available'];
    }
}
