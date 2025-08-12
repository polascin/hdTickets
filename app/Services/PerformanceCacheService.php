<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Log;

use function count;
use function strlen;

class PerformanceCacheService
{
    public const CACHE_TTL_SHORT = 120; // 2 minutes

    public const CACHE_TTL_MEDIUM = 300; // 5 minutes

    public const CACHE_TTL_LONG = 900; // 15 minutes

    /**
     * Get cached ticket statistics
     */
    public function getTicketStats(): array
    {
        return Cache::remember('ticket_stats', self::CACHE_TTL_MEDIUM, function () {
            return [
                'total_tickets'       => ScrapedTicket::count(),
                'available_tickets'   => ScrapedTicket::where('is_available', TRUE)->count(),
                'high_demand_tickets' => ScrapedTicket::where('is_high_demand', TRUE)->count(),
                'platforms_monitored' => ScrapedTicket::distinct('platform')->count('platform'),
                'today_tickets'       => ScrapedTicket::whereDate('created_at', today())->count(),
                'this_week_tickets'   => ScrapedTicket::whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ])->count(),
                'avg_price'  => ScrapedTicket::whereNotNull('min_price')->avg('min_price'),
                'min_price'  => ScrapedTicket::whereNotNull('min_price')->min('min_price'),
                'max_price'  => ScrapedTicket::whereNotNull('max_price')->max('max_price'),
                'updated_at' => now(),
            ];
        });
    }

    /**
     * Get cached platform breakdown
     */
    public function getPlatformBreakdown(): array
    {
        return Cache::remember('platform_breakdown', self::CACHE_TTL_MEDIUM, function () {
            return ScrapedTicket::select('platform')
                ->selectRaw('count(*) as count')
                ->selectRaw('sum(case when is_available = 1 then 1 else 0 end) as available_count')
                ->selectRaw('avg(min_price) as avg_price')
                ->groupBy('platform')
                ->get()
                ->keyBy('platform')
                ->toArray();
        });
    }

    /**
     * Get cached price ranges distribution
     */
    public function getPriceRanges(): array
    {
        return Cache::remember('price_ranges', self::CACHE_TTL_LONG, function () {
            return [
                'under_50'   => ScrapedTicket::where('min_price', '<', 50)->count(),
                '50_to_100'  => ScrapedTicket::whereBetween('min_price', [50, 100])->count(),
                '100_to_200' => ScrapedTicket::whereBetween('min_price', [100, 200])->count(),
                '200_to_500' => ScrapedTicket::whereBetween('min_price', [200, 500])->count(),
                'above_500'  => ScrapedTicket::where('min_price', '>', 500)->count(),
            ];
        });
    }

    /**
     * Get cached trending events
     */
    public function getTrendingEvents(): array
    {
        return Cache::remember('trending_events', self::CACHE_TTL_MEDIUM, function () {
            return ScrapedTicket::select('title', 'venue')
                ->selectRaw('count(*) as ticket_count')
                ->selectRaw('min(min_price) as min_price')
                ->selectRaw('max(max_price) as max_price')
                ->selectRaw('sum(case when is_available = 1 then 1 else 0 end) as available_count')
                ->groupBy('title', 'venue')
                ->orderByDesc('ticket_count')
                ->limit(10)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get cached user activity statistics
     */
    public function getUserActivityStats(): array
    {
        return Cache::remember('user_activity_stats', self::CACHE_TTL_LONG, function () {
            return [
                'total_users'        => User::count(),
                'active_users_today' => User::whereDate('last_login_at', today())->count(),
                'active_users_week'  => User::where('last_login_at', '>=', Carbon::now()->subWeek())->count(),
                'new_users_today'    => User::whereDate('created_at', today())->count(),
                'new_users_week'     => User::where('created_at', '>=', Carbon::now()->subWeek())->count(),
                'users_by_role'      => User::select('role')
                    ->selectRaw('count(*) as count')
                    ->groupBy('role')
                    ->pluck('count', 'role')
                    ->toArray(),
            ];
        });
    }

    /**
     * Get cached ticket availability timeline
     */
    public function getAvailabilityTimeline(): array
    {
        return Cache::remember('availability_timeline', self::CACHE_TTL_MEDIUM, function () {
            $timeline = [];

            for ($i = 23; $i >= 0; $i--) {
                $hour = Carbon::now()->subHours($i);
                $key = $hour->format('H:00');

                $timeline[$key] = [
                    'hour'      => $key,
                    'available' => ScrapedTicket::where('is_available', TRUE)
                        ->whereBetween('updated_at', [
                            $hour->copy()->startOfHour(),
                            $hour->copy()->endOfHour(),
                        ])
                        ->count(),
                    'sold_out' => ScrapedTicket::where('is_available', FALSE)
                        ->whereBetween('updated_at', [
                            $hour->copy()->startOfHour(),
                            $hour->copy()->endOfHour(),
                        ])
                        ->count(),
                ];
            }

            return array_values($timeline);
        });
    }

    /**
     * Get cached top events by price
     */
    public function getTopEventsByPrice(): array
    {
        return Cache::remember('top_events_by_price', self::CACHE_TTL_LONG, function () {
            return ScrapedTicket::select('title', 'venue', 'event_date')
                ->selectRaw('max(max_price) as highest_price')
                ->selectRaw('min(min_price) as lowest_price')
                ->selectRaw('count(*) as ticket_count')
                ->whereNotNull('max_price')
                ->groupBy('title', 'venue', 'event_date')
                ->orderByDesc('highest_price')
                ->limit(20)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get cached search suggestions
     */
    public function getSearchSuggestions(string $query, int $limit = 10): array
    {
        $cacheKey = 'search_suggestions_' . md5($query);

        return Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () use ($query, $limit) {
            return ScrapedTicket::where('title', 'like', '%' . $query . '%')
                ->orWhere('venue', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->select('title', 'venue', 'platform')
                ->distinct()
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Invalidate ticket-related caches
     */
    public function invalidateTicketCaches(): void
    {
        $keys = [
            'ticket_stats',
            'platform_breakdown',
            'price_ranges',
            'trending_events',
            'availability_timeline',
            'top_events_by_price',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Clear search suggestion caches (they have dynamic keys)
        Cache::flush(); // This is more aggressive but ensures all caches are cleared

        Log::info('Ticket caches invalidated');
    }

    /**
     * Warm up critical caches
     */
    public function warmUpCaches(): void
    {
        // Warm up the most frequently accessed caches
        $this->getTicketStats();
        $this->getPlatformBreakdown();
        $this->getTrendingEvents();
        $this->getUserActivityStats();

        Log::info('Caches warmed up successfully');
    }

    /**
     * Get cache status and statistics
     */
    public function getCacheStatus(): array
    {
        $keys = [
            'ticket_stats',
            'platform_breakdown',
            'price_ranges',
            'trending_events',
            'availability_timeline',
            'top_events_by_price',
            'user_activity_stats',
        ];

        $status = [];
        foreach ($keys as $key) {
            $status[$key] = [
                'exists' => Cache::has($key),
                'size'   => Cache::has($key) ? strlen(serialize(Cache::get($key))) : 0,
            ];
        }

        return $status;
    }

    /**
     * Get database performance metrics
     */
    public function getDatabaseMetrics(): array
    {
        return Cache::remember('db_metrics', self::CACHE_TTL_SHORT, function () {
            $start = microtime(TRUE);

            // Simple query to test response time
            DB::table('scraped_tickets')->limit(1)->get();

            $responseTime = (microtime(TRUE) - $start) * 1000; // in milliseconds

            return [
                'response_time_ms' => round($responseTime, 2),
                'connections'      => DB::connection()->getPdo() ? 1 : 0,
                'status'           => $responseTime < 100 ? 'excellent' : ($responseTime < 500 ? 'good' : 'slow'),
            ];
        });
    }

    /**
     * Optimize cache usage based on patterns
     */
    public function optimizeCacheUsage(): array
    {
        $optimizations = [];

        // Check if frequently accessed data should have longer TTL
        $ticketStats = $this->getTicketStats();
        if ($ticketStats['total_tickets'] > 10000) {
            $optimizations[] = 'Consider increasing cache TTL for large datasets';
        }

        // Check cache hit ratios (this would need Redis or Memcached for real metrics)
        $cacheStatus = $this->getCacheStatus();
        $cachedItems = array_filter($cacheStatus, fn ($item) => $item['exists']);

        if (count($cachedItems) < count($cacheStatus) * 0.7) {
            $optimizations[] = 'Cache hit ratio is low, consider warming up caches more frequently';
        }

        return $optimizations;
    }
}
