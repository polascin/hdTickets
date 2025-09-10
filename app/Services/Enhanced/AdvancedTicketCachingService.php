<?php declare(strict_types=1);

namespace App\Services\Enhanced;

use App\Models\ScrapedTicket;
use App\Models\User;
use App\Services\Enhanced\App\Models\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function count;
use function is_array;

class AdvancedTicketCachingService
{
    // Cache TTL configurations
    public const TTL_ULTRA_SHORT = 30;      // 30 seconds for real-time data

    public const TTL_SHORT = 120;           // 2 minutes for frequent updates

    public const TTL_MEDIUM = 300;          // 5 minutes for moderate updates

    public const TTL_LONG = 900;            // 15 minutes for less frequent data

    public const TTL_EXTENDED = 3600;       // 1 hour for stable data

    public const TTL_DAILY = 86400;         // 24 hours for daily statistics

    // Cache key prefixes
    public const PREFIX_TICKETS = 'tickets:';

    public const PREFIX_EVENTS = 'events:';

    public const PREFIX_PLATFORMS = 'platforms:';

    public const PREFIX_USERS = 'users:';

    public const PREFIX_ANALYTICS = 'analytics:';

    public const PREFIX_SEARCH = 'search:';

    private $redis;

    public function __construct()
    {
        $this->redis = Redis::connection('cache');
    }

    /**
     * Cache frequently accessed ticket data with smart invalidation
     */
    /**
     * CacheTicketData
     */
    public function cacheTicketData(array $criteria = [], int $ttl = self::TTL_MEDIUM): array
    {
        $cacheKey = $this->generateTicketCacheKey($criteria);

        return Cache::remember($cacheKey, $ttl, function () use ($criteria) {
            $query = ScrapedTicket::query();

            // Apply criteria filters
            if (isset($criteria['platform'])) {
                $query->where('platform', $criteria['platform']);
            }

            if (isset($criteria['is_available'])) {
                $query->where('is_available', $criteria['is_available']);
            }

            if (isset($criteria['category'])) {
                $query->where('category', $criteria['category']);
            }

            if (isset($criteria['event_date_from'])) {
                $query->where('event_date', '>=', $criteria['event_date_from']);
            }

            if (isset($criteria['event_date_to'])) {
                $query->where('event_date', '<=', $criteria['event_date_to']);
            }

            if (isset($criteria['price_min'])) {
                $query->where('min_price', '>=', $criteria['price_min']);
            }

            if (isset($criteria['price_max'])) {
                $query->where('max_price', '<=', $criteria['price_max']);
            }

            $tickets = $query->orderBy('created_at', 'desc')
                ->limit($criteria['limit'] ?? 100)
                ->get();

            // Track cache generation
            $this->trackCacheGeneration('ticket_data');

            return $tickets->toArray();
        });
    }

    /**
     * Cache ticket search results with smart autocomplete
     */
    /**
     * CacheSearchResults
     */
    public function cacheSearchResults(string $query, array $filters = [], int $limit = 50): array
    {
        $cacheKey = self::PREFIX_SEARCH . md5($query . serialize($filters) . $limit);

        return Cache::remember($cacheKey, self::TTL_SHORT, function () use ($query, $filters, $limit) {
            $searchQuery = ScrapedTicket::query();

            // Full text search
            $searchQuery->where(function ($q) use ($query): void {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('venue', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('category', 'LIKE', "%{$query}%");
            });

            // Apply additional filters
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $searchQuery->whereIn($field, $value);
                } else {
                    $searchQuery->where($field, $value);
                }
            }

            $results = $searchQuery->orderByRaw('
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN venue LIKE ? THEN 2
                    WHEN description LIKE ? THEN 3
                    ELSE 4
                END
            ', ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->get();

            // Cache search suggestions separately
            $this->cacheSearchSuggestions($query, $results);

            return $results->toArray();
        });
    }

    /**
     * Cache high-demand tickets with priority
     */
    /**
     * CacheHighDemandTickets
     */
    public function cacheHighDemandTickets(int $ttl = self::TTL_ULTRA_SHORT): array
    {
        $cacheKey = self::PREFIX_TICKETS . 'high_demand';

        return Cache::remember($cacheKey, $ttl, function () {
            $tickets = ScrapedTicket::where('is_high_demand', TRUE)
                ->where('is_available', TRUE)
                ->orderBy('priority', 'desc')
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get(['id', 'title', 'venue', 'event_date', 'min_price', 'max_price', 'platform', 'priority', 'updated_at']);

            // Set individual ticket caches for instant access
            foreach ($tickets as $ticket) {
                $this->cacheIndividualTicket($ticket);
            }

            return $tickets->toArray();
        });
    }

    /**
     * Cache individual ticket details for quick access
     *
     * @param mixed $ticket
     */
    /**
     * CacheIndividualTicket
     */
    public function cacheIndividualTicket(Ticket $ticket, int $ttl = self::TTL_MEDIUM): void
    {
        if (is_numeric($ticket)) {
            $ticket = ScrapedTicket::find($ticket);
        }

        if (! $ticket) {
            return;
        }

        $cacheKey = self::PREFIX_TICKETS . "detail:{$ticket->id}";
        Cache::put($cacheKey, $ticket->toArray(), $ttl);

        // Cache ticket availability separately for faster lookups
        $availabilityKey = self::PREFIX_TICKETS . "availability:{$ticket->id}";
        Cache::put($availabilityKey, [
            'is_available' => $ticket->is_available,
            'last_checked' => $ticket->updated_at,
            'price_range'  => [$ticket->min_price, $ticket->max_price],
        ], self::TTL_SHORT);
    }

    /**
     * Get cached ticket details
     */
    /**
     * Get  cached ticket
     */
    public function getCachedTicket(int $ticketId): ?array
    {
        $cacheKey = self::PREFIX_TICKETS . "detail:{$ticketId}";

        $cached = Cache::get($cacheKey);
        if ($cached) {
            $this->trackCacheHit('ticket_detail');

            return $cached;
        }

        // Cache miss - fetch and cache
        $ticket = ScrapedTicket::find($ticketId);
        if ($ticket) {
            $this->cacheIndividualTicket($ticket);
            $this->trackCacheMiss('ticket_detail');

            return $ticket->toArray();
        }

        return NULL;
    }

    /**
     * Cache platform-specific ticket statistics
     */
    /**
     * CachePlatformStats
     */
    public function cachePlatformStats(string $platform, int $ttl = self::TTL_LONG): array
    {
        $cacheKey = self::PREFIX_PLATFORMS . "stats:{$platform}";

        return Cache::remember($cacheKey, $ttl, fn (): array => [
            'total_tickets'     => ScrapedTicket::where('platform', $platform)->count(),
            'available_tickets' => ScrapedTicket::where('platform', $platform)
                ->where('is_available', TRUE)->count(),
            'high_demand_tickets' => ScrapedTicket::where('platform', $platform)
                ->where('is_high_demand', TRUE)->count(),
            'avg_price' => ScrapedTicket::where('platform', $platform)
                ->whereNotNull('min_price')->avg('min_price'),
            'price_range' => [
                'min' => ScrapedTicket::where('platform', $platform)
                    ->whereNotNull('min_price')->min('min_price'),
                'max' => ScrapedTicket::where('platform', $platform)
                    ->whereNotNull('max_price')->max('max_price'),
            ],
            'last_updated' => ScrapedTicket::where('platform', $platform)
                ->max('updated_at'),
            'categories' => ScrapedTicket::where('platform', $platform)
                ->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category')
                ->toArray(),
        ]);
    }

    /**
     * Cache user-specific ticket preferences and recommendations
     */
    /**
     * CacheUserTicketData
     */
    public function cacheUserTicketData(int $userId, int $ttl = self::TTL_MEDIUM): array
    {
        $cacheKey = self::PREFIX_USERS . "tickets:{$userId}";

        return Cache::remember($cacheKey, $ttl, function () use ($userId): array {
            $user = User::find($userId);
            if (! $user) {
                return [];
            }

            // Get user's ticket alerts and preferences
            $preferences = $user->preferences ?? [];
            $watchlist = $user->watchlist ?? [];

            // Get recommended tickets based on user history
            $recommendedTickets = $this->generateTicketRecommendations($user);

            // Get user's recent activity
            $recentActivity = $this->getUserRecentActivity($userId);

            return [
                'preferences'         => $preferences,
                'watchlist'           => $watchlist,
                'recommended_tickets' => $recommendedTickets,
                'recent_activity'     => $recentActivity,
                'cached_at'           => now(),
            ];
        });
    }

    /**
     * Cache real-time ticket availability updates
     */
    /**
     * CacheAvailabilityUpdates
     */
    public function cacheAvailabilityUpdates(array $ticketIds, int $ttl = self::TTL_ULTRA_SHORT): void
    {
        $cacheKey = self::PREFIX_TICKETS . 'availability_batch:' . md5(implode(',', $ticketIds));

        $updates = ScrapedTicket::whereIn('id', $ticketIds)
            ->select('id', 'is_available', 'min_price', 'max_price', 'updated_at')
            ->get()
            ->keyBy('id')
            ->toArray();

        Cache::put($cacheKey, $updates, $ttl);

        // Update individual availability caches
        foreach ($updates as $ticketId => $data) {
            $availabilityKey = self::PREFIX_TICKETS . "availability:{$ticketId}";
            Cache::put($availabilityKey, [
                'is_available' => $data['is_available'],
                'last_checked' => $data['updated_at'],
                'price_range'  => [$data['min_price'], $data['max_price']],
            ], $ttl);
        }
    }

    /**
     * Cache event-specific ticket aggregations
     */
    /**
     * CacheEventTickets
     */
    public function cacheEventTickets(string $eventTitle, string $venue, int $ttl = self::TTL_LONG): array
    {
        $cacheKey = self::PREFIX_EVENTS . md5($eventTitle . $venue);

        return Cache::remember($cacheKey, $ttl, function () use ($eventTitle, $venue): array {
            $tickets = ScrapedTicket::where('title', $eventTitle)
                ->where('venue', $venue)
                ->orderBy('min_price', 'asc')
                ->get();

            return [
                'tickets'            => $tickets->toArray(),
                'platform_breakdown' => $tickets->groupBy('platform')->map(fn ($platformTickets): array => [
                    'count'           => $platformTickets->count(),
                    'min_price'       => $platformTickets->whereNotNull('min_price')->min('min_price'),
                    'max_price'       => $platformTickets->whereNotNull('max_price')->max('max_price'),
                    'available_count' => $platformTickets->where('is_available', TRUE)->count(),
                ])->toArray(),
                'price_analysis' => [
                    'cheapest'       => $tickets->whereNotNull('min_price')->sortBy('min_price')->first()?->toArray(),
                    'most_expensive' => $tickets->whereNotNull('max_price')->sortByDesc('max_price')->first()?->toArray(),
                    'average_price'  => $tickets->whereNotNull('min_price')->avg('min_price'),
                ],
            ];
        });
    }

    /**
     * Cache analytics data for dashboard performance
     */
    /**
     * CacheAnalyticsData
     */
    public function cacheAnalyticsData(string $type, array $params = [], int $ttl = self::TTL_LONG): array
    {
        $cacheKey = self::PREFIX_ANALYTICS . "{$type}:" . md5(serialize($params));

        return Cache::remember($cacheKey, $ttl, fn (): array => match ($type) {
            'ticket_trends'        => $this->generateTicketTrendsData($params),
            'platform_performance' => $this->generatePlatformPerformanceData(),
            'price_analysis'       => $this->generatePriceAnalysisData(),
            'user_engagement'      => $this->generateUserEngagementData(),
            default                => [],
        });
    }

    /**
     * Invalidate cache for specific ticket or pattern
     */
    /**
     * InvalidateCache
     */
    public function invalidateCache(string $pattern): void
    {
        try {
            if (str_contains($pattern, ':')) {
                // Specific key
                Cache::forget($pattern);
                $this->redis->del($pattern);
            } else {
                // Pattern-based invalidation
                $keys = $this->redis->keys("*{$pattern}*");
                if (! empty($keys)) {
                    foreach ($keys as $key) {
                        Cache::forget($key);
                        $this->redis->del($key);
                    }
                }
            }

            Log::info("Cache invalidated for pattern: {$pattern}");
        } catch (Exception $e) {
            Log::warning("Failed to invalidate cache for pattern: {$pattern}", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Smart cache warming for critical data
     */
    /**
     * WarmCriticalCaches
     */
    public function warmCriticalCaches(): array
    {
        $startTime = microtime(TRUE);
        $warmedCaches = [];

        try {
            // Warm high-demand tickets
            $warmedCaches['high_demand'] = $this->cacheHighDemandTickets();

            // Warm platform statistics
            $platforms = ['ticketmaster', 'stubhub', 'viagogo', 'tickpick'];
            foreach ($platforms as $platform) {
                $warmedCaches["platform_{$platform}"] = $this->cachePlatformStats($platform);
            }

            // Warm trending searches
            $warmedCaches['search_trending'] = $this->warmTrendingSearches();

            // Warm critical analytics
            $warmedCaches['analytics_overview'] = $this->cacheAnalyticsData('ticket_trends');

            $duration = microtime(TRUE) - $startTime;

            Log::info('Cache warming completed', [
                'duration'      => $duration,
                'caches_warmed' => count($warmedCaches),
                'memory_used'   => memory_get_usage(TRUE),
            ]);

            return [
                'success'       => TRUE,
                'duration'      => $duration,
                'caches_warmed' => array_keys($warmedCaches),
                'memory_used'   => memory_get_usage(TRUE),
            ];
        } catch (Exception $e) {
            Log::error('Cache warming failed', ['error' => $e->getMessage()]);

            return [
                'success'  => FALSE,
                'error'    => $e->getMessage(),
                'duration' => microtime(TRUE) - $startTime,
            ];
        }
    }

    /**
     * Get cache performance metrics
     */
    /**
     * Get  cache metrics
     */
    public function getCacheMetrics(): array
    {
        try {
            $info = $this->redis->info();

            return [
                'hit_rate'              => $this->calculateHitRate($info),
                'memory_usage'          => $info['used_memory_human'] ?? '0B',
                'total_keys'            => $info['db0:keys'] ?? 0,
                'expires'               => $info['db0:expires'] ?? 0,
                'operations_per_second' => $info['instantaneous_ops_per_sec'] ?? 0,
                'connected_clients'     => $info['connected_clients'] ?? 0,
                'cache_metrics'         => $this->getDetailedCacheMetrics(),
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Private helper methods

    /**
     * GenerateTicketCacheKey
     */
    private function generateTicketCacheKey(array $criteria): string
    {
        return self::PREFIX_TICKETS . 'query:' . md5(serialize($criteria));
    }

    /**
     * CacheSearchSuggestions
     *
     * @param mixed $results
     */
    private function cacheSearchSuggestions(string $query, $results): void
    {
        $suggestions = $results->take(10)->pluck('title')->unique()->values()->toArray();
        $suggestionsKey = self::PREFIX_SEARCH . 'suggestions:' . md5($query);
        Cache::put($suggestionsKey, $suggestions, self::TTL_MEDIUM);
    }

    /**
     * GenerateTicketRecommendations
     */
    private function generateTicketRecommendations(User $user): array
    {
        // Simple recommendation algorithm based on user preferences
        $preferences = $user->preferences ?? [];
        $preferredCategories = $preferences['categories'] ?? [];

        if (empty($preferredCategories)) {
            return [];
        }

        return ScrapedTicket::whereIn('category', $preferredCategories)
            ->where('is_available', TRUE)
            ->orderBy('priority', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'venue', 'event_date', 'min_price', 'platform'])
            ->toArray();
    }

    /**
     * Get  user recent activity
     */
    private function getUserRecentActivity(int $userId): array
    {
        // This would integrate with activity logging system
        return [
            'last_search'      => Cache::get(self::PREFIX_USERS . "last_search:{$userId}"),
            'viewed_tickets'   => Cache::get(self::PREFIX_USERS . "viewed_tickets:{$userId}", []),
            'alerts_triggered' => Cache::get(self::PREFIX_USERS . "alerts:{$userId}", []),
        ];
    }

    /**
     * GenerateTicketTrendsData
     */
    private function generateTicketTrendsData(array $params): array
    {
        $period = $params['period'] ?? '24h';

        return [
            'period'               => $period,
            'total_tickets'        => ScrapedTicket::count(),
            'new_tickets'          => ScrapedTicket::where('created_at', '>=', Carbon::now()->subDay())->count(),
            'availability_changes' => ScrapedTicket::where('updated_at', '>=', Carbon::now()->subDay())->count(),
            'trending_categories'  => ScrapedTicket::selectRaw('category, count(*) as count')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->groupBy('category')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'category')
                ->toArray(),
        ];
    }

    /**
     * GeneratePlatformPerformanceData
     */
    private function generatePlatformPerformanceData(): array
    {
        return ScrapedTicket::selectRaw('platform, count(*) as total_tickets, 
                                        sum(case when is_available = 1 then 1 else 0 end) as available_tickets,
                                        avg(min_price) as avg_price,
                                        max(updated_at) as last_updated')
            ->groupBy('platform')
            ->get()
            ->keyBy('platform')
            ->toArray();
    }

    /**
     * GeneratePriceAnalysisData
     */
    private function generatePriceAnalysisData(): array
    {
        return [
            'price_distribution' => ScrapedTicket::selectRaw('
                CASE 
                    WHEN min_price < 50 THEN "Under $50"
                    WHEN min_price BETWEEN 50 AND 100 THEN "$50-$100"
                    WHEN min_price BETWEEN 100 AND 200 THEN "$100-$200"
                    WHEN min_price BETWEEN 200 AND 500 THEN "$200-$500"
                    ELSE "Over $500"
                END as price_range,
                count(*) as count
            ')
                ->whereNotNull('min_price')
                ->groupBy('price_range')
                ->pluck('count', 'price_range')
                ->toArray(),

            'average_prices_by_platform' => ScrapedTicket::selectRaw('platform, avg(min_price) as avg_price')
                ->whereNotNull('min_price')
                ->groupBy('platform')
                ->pluck('avg_price', 'platform')
                ->toArray(),
        ];
    }

    /**
     * GenerateUserEngagementData
     */
    private function generateUserEngagementData(): array
    {
        return [
            'total_users'      => User::count(),
            'active_users_24h' => User::where('last_login_at', '>=', Carbon::now()->subDay())->count(),
            'users_by_role'    => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
        ];
    }

    /**
     * WarmTrendingSearches
     */
    private function warmTrendingSearches(): array
    {
        // Get trending search terms (this would integrate with search analytics)
        $trendingTerms = ['Premier League', 'Champions League', 'NBA Finals', 'World Cup', 'Wimbledon'];

        foreach ($trendingTerms as $term) {
            $this->cacheSearchResults($term, [], 20);
        }

        return $trendingTerms;
    }

    /**
     * TrackCacheGeneration
     */
    private function trackCacheGeneration(string $type): void
    {
        $metricsKey = "cache_metrics:generation:{$type}";
        $this->redis->incr($metricsKey);
        $this->redis->expire($metricsKey, 3600);
    }

    /**
     * TrackCacheHit
     */
    private function trackCacheHit(string $type): void
    {
        $metricsKey = "cache_metrics:hits:{$type}";
        $this->redis->incr($metricsKey);
        $this->redis->expire($metricsKey, 3600);
    }

    /**
     * TrackCacheMiss
     */
    private function trackCacheMiss(string $type): void
    {
        $metricsKey = "cache_metrics:misses:{$type}";
        $this->redis->incr($metricsKey);
        $this->redis->expire($metricsKey, 3600);
    }

    /**
     * CalculateHitRate
     */
    private function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Get  detailed cache metrics
     */
    private function getDetailedCacheMetrics(): array
    {
        $metrics = [];
        $types = ['ticket_detail', 'ticket_data', 'search'];

        foreach ($types as $type) {
            $hits = $this->redis->get("cache_metrics:hits:{$type}") ?: 0;
            $misses = $this->redis->get("cache_metrics:misses:{$type}") ?: 0;
            $total = $hits + $misses;

            $metrics[$type] = [
                'hits'     => (int) $hits,
                'misses'   => (int) $misses,
                'hit_rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
            ];
        }

        return $metrics;
    }
}
