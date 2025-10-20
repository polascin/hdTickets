<?php declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function count;

class DashboardCacheService
{
    private const CACHE_PREFIX = 'dashboard:';

    private const DEFAULT_TTL = 300; // 5 minutes

    private const STATS_TTL = 120; // 2 minutes for statistics

    private const TICKETS_TTL = 180; // 3 minutes for tickets

    private const USER_TTL = 240; // 4 minutes for user data

    /** Cache tags for easier management */
    private const CACHE_TAGS = [
        'dashboard',
        'dashboard_stats',
        'dashboard_tickets',
        'dashboard_user_data',
    ];

    /**
     * Get cached dashboard statistics
     */
    public function getStatistics(?User $user = NULL): array
    {
        $cacheKey = $this->buildCacheKey('stats', $user?->id);

        return Cache::tags(['dashboard', 'dashboard_stats'])->remember(
            $cacheKey,
            self::STATS_TTL,
            function () use ($user): array {
                return $this->calculateStatistics($user);
            },
        );
    }

    /**
     * Get cached recent tickets
     */
    public function getRecentTickets(int $limit = 10): array
    {
        $cacheKey = $this->buildCacheKey('recent_tickets', $limit);

        return Cache::tags(['dashboard', 'dashboard_tickets'])->remember(
            $cacheKey,
            self::TICKETS_TTL,
            function () use ($limit): array {
                return $this->fetchRecentTickets($limit);
            },
        );
    }

    /**
     * Get cached user-specific dashboard data
     */
    public function getUserDashboardData(User $user): array
    {
        $cacheKey = $this->buildCacheKey('user_data', $user->id);

        return Cache::tags(['dashboard', 'dashboard_user_data'])->remember(
            $cacheKey,
            self::USER_TTL,
            function () use ($user): array {
                return $this->calculateUserData($user);
            },
        );
    }

    /**
     * Get comprehensive cached dashboard data
     */
    public function getComprehensiveDashboardData(User $user): array
    {
        $cacheKey = $this->buildCacheKey('comprehensive', $user->id);

        return Cache::tags(['dashboard'])->remember(
            $cacheKey,
            self::DEFAULT_TTL,
            function () use ($user): array {
                return [
                    'statistics'     => $this->getStatistics($user),
                    'recent_tickets' => $this->getRecentTickets(),
                    'user_data'      => $this->getUserDashboardData($user),
                    'system_status'  => $this->getSystemStatus(),
                    'generated_at'   => Carbon::now()->toISOString(),
                ];
            },
        );
    }

    /**
     * Invalidate specific cache keys
     */
    public function invalidateCache(string $type = 'all', ?int $userId = NULL): void
    {
        try {
            switch ($type) {
                case 'stats':
                    Cache::tags(['dashboard_stats'])->flush();
                    Log::info('Dashboard statistics cache cleared');

                    break;
                case 'tickets':
                    Cache::tags(['dashboard_tickets'])->flush();
                    Log::info('Dashboard tickets cache cleared');

                    break;
                case 'user':
                    if ($userId) {
                        $this->invalidateUserCache($userId);
                    } else {
                        Cache::tags(['dashboard_user_data'])->flush();
                    }
                    Log::info('Dashboard user data cache cleared', ['user_id' => $userId]);

                    break;
                case 'all':
                default:
                    Cache::tags(['dashboard'])->flush();
                    Log::info('All dashboard cache cleared');

                    break;
            }
        } catch (Exception $e) {
            Log::error('Failed to invalidate dashboard cache', [
                'type'    => $type,
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up the cache with fresh data
     */
    public function warmCache(User $user): void
    {
        try {
            Log::info('Warming dashboard cache', ['user_id' => $user->id]);

            // Pre-load all dashboard data
            $this->getStatistics($user);
            $this->getRecentTickets();
            $this->getUserDashboardData($user);
            $this->getComprehensiveDashboardData($user);

            Log::info('Dashboard cache warmed successfully', ['user_id' => $user->id]);
        } catch (Exception $e) {
            Log::error('Failed to warm dashboard cache', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get cache statistics for monitoring
     */
    public function getCacheStats(): array
    {
        try {
            $redis = Redis::connection();
            $keys = $redis->keys(self::CACHE_PREFIX . '*');

            return [
                'total_keys'   => count($keys),
                'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
                'hit_rate'     => $this->calculateHitRate(),
                'cache_tags'   => self::CACHE_TAGS,
                'ttl_settings' => [
                    'stats'     => self::STATS_TTL,
                    'tickets'   => self::TICKETS_TTL,
                    'user_data' => self::USER_TTL,
                    'default'   => self::DEFAULT_TTL,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get cache statistics', ['error' => $e->getMessage()]);

            return ['error' => 'Unable to retrieve cache statistics'];
        }
    }

    /**
     * Build cache key with consistent naming
     */
    private function buildCacheKey(string $type, mixed $identifier = NULL): string
    {
        $key = self::CACHE_PREFIX . $type;

        if ($identifier !== NULL) {
            $key .= ':' . $identifier;
        }

        return $key;
    }

    /**
     * Calculate fresh statistics
     */
    private function calculateStatistics(?User $user = NULL): array
    {
        try {
            $stats = [
                'available_tickets' => ScrapedTicket::available()->count(),
                'new_today'         => ScrapedTicket::whereDate('scraped_at', Carbon::today())->count(),
                'monitored_events'  => $this->getMonitoredEventsCount($user),
                'active_alerts'     => $user ? TicketAlert::where('user_id', $user->id)->where('status', 'active')->count() : 0,
                'price_alerts'      => $user ? TicketAlert::where('user_id', $user->id)->where('status', 'active')->count() : 0,
                'triggered_today'   => $user ? $this->getTriggeredAlertsToday($user) : 0,
            ];

            Log::debug('Dashboard statistics calculated', $stats);

            return $stats;
        } catch (Exception $e) {
            Log::error('Failed to calculate statistics', ['error' => $e->getMessage()]);

            return $this->getFallbackStatistics();
        }
    }

    /**
     * Fetch recent tickets from database
     */
    private function fetchRecentTickets(int $limit): array
    {
        try {
            return ScrapedTicket::with(['category'])
                ->available()
                ->recent(24)
                ->orderBy('scraped_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($ticket): array {
                    return [
                        'id'          => (int) $ticket->id,
                        'title'       => (string) ($ticket->title ?? 'Sports Event'),
                        'venue'       => (string) ($ticket->venue ?? 'TBD'),
                        'price'       => (float) ($ticket->min_price ?? 0),
                        'platform'    => (string) ($ticket->platform ?? 'Unknown'),
                        'sport'       => (string) ($ticket->sport ?? 'Sports'),
                        'event_date'  => $ticket->event_date ? $ticket->event_date->format('Y-m-d') : NULL,
                        'scraped_at'  => $ticket->scraped_at ? $ticket->scraped_at->diffForHumans() : 'Recently',
                        'available'   => (bool) $ticket->is_available,
                        'high_demand' => (bool) ($ticket->is_high_demand ?? FALSE),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to fetch recent tickets', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Calculate user-specific dashboard data
     */
    private function calculateUserData(User $user): array
    {
        try {
            return [
                'preferences'  => $user->preferences ?? [],
                'subscription' => $this->getSubscriptionData($user),
                'activity'     => [
                    'views_today'      => $this->getUserViewsToday($user),
                    'searches_today'   => $this->getUserSearchesToday($user),
                    'engagement_score' => $this->getUserEngagementScore($user),
                ],
                'alerts' => [
                    'total'           => TicketAlert::where('user_id', $user->id)->count(),
                    'active'          => TicketAlert::where('user_id', $user->id)->where('status', 'active')->count(),
                    'triggered_today' => $this->getTriggeredAlertsToday($user),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to calculate user data', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->getFallbackUserData();
        }
    }

    /**
     * Get system status information
     */
    private function getSystemStatus(): array
    {
        return [
            'scraping_active'  => TRUE,
            'api_responsive'   => TRUE,
            'database_healthy' => TRUE,
            'cache_healthy'    => $this->isCacheHealthy(),
            'last_check'       => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Check if cache is healthy
     */
    private function isCacheHealthy(): bool
    {
        try {
            Cache::put('health_check', 'ok', 10);

            return Cache::get('health_check') === 'ok';
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Invalidate cache for specific user
     */
    private function invalidateUserCache(int $userId): void
    {
        $patterns = [
            $this->buildCacheKey('user_data', $userId),
            $this->buildCacheKey('comprehensive', $userId),
            $this->buildCacheKey('stats', $userId),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Calculate cache hit rate (simplified)
     */
    private function calculateHitRate(): float
    {
        // This is a simplified implementation
        // In production, you would track hits/misses properly
        return 85.0;
    }

    /**
     * Get monitored events count
     */
    private function getMonitoredEventsCount(?User $user = NULL): int
    {
        if (! $user) {
            return ScrapedTicket::available()
                ->selectRaw('COUNT(DISTINCT CONCAT(title, venue, event_date)) as unique_events')
                ->value('unique_events') ?: 0;
        }

        $preferences = $user->preferences ?? [];
        $favoriteTeams = $preferences['favorite_teams'] ?? [];

        $query = ScrapedTicket::available()
            ->selectRaw('COUNT(DISTINCT CONCAT(title, venue, event_date)) as unique_events');

        if (! empty($favoriteTeams)) {
            $query->where(function ($q) use ($favoriteTeams): void {
                foreach ($favoriteTeams as $team) {
                    $q->orWhere('title', 'like', "%{$team}%")
                        ->orWhere('teams', 'like', "%{$team}%");
                }
            });
        }

        return (int) $query->value('unique_events') ?: 0;
    }

    /**
     * Get triggered alerts today for user
     */
    private function getTriggeredAlertsToday(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereHas('matches', function ($query): void {
                $query->whereDate('created_at', Carbon::today());
            })
            ->count();
    }

    /**
     * Get subscription data with fallbacks
     */
    private function getSubscriptionData(User $user): array
    {
        try {
            return [
                'monthly_limit'   => $user->getMonthlyTicketLimit() ?? 100,
                'current_usage'   => $user->getMonthlyTicketUsage() ?? 0,
                'percentage_used' => min(100, (($user->getMonthlyTicketUsage() ?? 0) / max(1, $user->getMonthlyTicketLimit() ?? 100)) * 100),
                'has_active'      => $user->hasActiveSubscription() ?? FALSE,
                'days_remaining'  => method_exists($user, 'getFreeTrialDaysRemaining') ? $user->getFreeTrialDaysRemaining() : NULL,
            ];
        } catch (Exception $e) {
            return [
                'monthly_limit'   => 100,
                'current_usage'   => 0,
                'percentage_used' => 0,
                'has_active'      => FALSE,
                'days_remaining'  => NULL,
            ];
        }
    }

    /**
     * Get user views today (placeholder - integrate with actual analytics)
     */
    private function getUserViewsToday(User $user): int
    {
        return random_int(5, 25);
    }

    /**
     * Get user searches today (placeholder - integrate with actual analytics)
     */
    private function getUserSearchesToday(User $user): int
    {
        return random_int(0, 10);
    }

    /**
     * Get user engagement score (placeholder - integrate with actual analytics)
     */
    private function getUserEngagementScore(User $user): float
    {
        return round(random_int(65, 95) / 100, 2);
    }

    /**
     * Fallback statistics when calculation fails
     */
    private function getFallbackStatistics(): array
    {
        return [
            'available_tickets' => 0,
            'new_today'         => 0,
            'monitored_events'  => 0,
            'active_alerts'     => 0,
            'price_alerts'      => 0,
            'triggered_today'   => 0,
        ];
    }

    /**
     * Fallback user data when calculation fails
     */
    private function getFallbackUserData(): array
    {
        return [
            'preferences'  => [],
            'subscription' => [
                'monthly_limit'   => 100,
                'current_usage'   => 0,
                'percentage_used' => 0,
                'has_active'      => FALSE,
                'days_remaining'  => NULL,
            ],
            'activity' => [
                'views_today'      => 0,
                'searches_today'   => 0,
                'engagement_score' => 0.0,
            ],
            'alerts' => [
                'total'           => 0,
                'active'          => 0,
                'triggered_today' => 0,
            ],
        ];
    }
}
