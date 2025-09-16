<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EnhancedDashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analytics, protected RecommendationService $recommendations)
    {
    }

    /**
     * Display the enhanced customer dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Authentication required');
        }

        // Get comprehensive dashboard data
        $dashboardData = $this->getComprehensiveDashboardData($user);

        // Using new unified layout (v3). Legacy view 'dashboard.customer-enhanced-v2' kept for fallback/migration.
        return view('dashboard.customer-v3', $dashboardData);
    }

    /**
     * API endpoint for real-time data updates
     * Provides dashboard statistics, recent tickets, and system status
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $statistics = $this->formatStatisticsForView($user);
            $recentTickets = collect($this->getFormattedRecentTickets())->take(6)->all();

            $data = [
                'statistics'       => $statistics, // Flat statistics for direct access
                'stats'            => $statistics, // Alias for backward compatibility
                'recent_tickets'   => $recentTickets,
                'recentTickets'    => $recentTickets, // Backward compatibility
                'alerts_triggered' => $this->getRecentlyTriggeredAlerts(),
                'system_status'    => $this->getSystemStatus(),
                'user_activity'    => [
                    'views_today'      => $this->getUserViewsToday(),
                    'searches_today'   => $this->getUserSearchesToday(),
                    'engagement_score' => $this->getUserEngagementScore(),
                ],
                'subscription' => $this->getSubscriptionData($user),
                'timestamp'        => Carbon::now()->toISOString(),
                'last_updated'     => Carbon::now()->toISOString(),
            ];

            return response()->json([
                'success'      => TRUE,
                'data'         => $data,
                'cache_status' => 'fresh',
                'meta'         => [
                    'refresh_interval' => 120, // 2 minutes
                    'next_refresh_at'  => Carbon::now()->addMinutes(2)->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching realtime dashboard data', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to fetch realtime data',
            ], 500);
        }
    }

    /**
     * Get subscription data with safe fallbacks
     */
    private function getSubscriptionData(User $user): array
    {
        try {
            return [
                'monthly_limit'   => $user->getMonthlyTicketLimit() ?? 100,
                'current_usage'   => $user->getMonthlyTicketUsage() ?? 0,
                'percentage_used' => min(100, (($user->getMonthlyTicketUsage() ?? 0) / max(1, $user->getMonthlyTicketLimit() ?? 100)) * 100),
                'has_active'      => $user->hasActiveSubscription() ?? false,
                'days_remaining'  => method_exists($user, 'getFreeTrialDaysRemaining') ? $user->getFreeTrialDaysRemaining() : null,
            ];
        } catch (Exception $e) {
            Log::debug('Failed to get subscription data, using defaults', ['error' => $e->getMessage()]);

            return [
                'monthly_limit'   => 100,
                'current_usage'   => 0,
                'percentage_used' => 0,
                'has_active'      => false,
                'days_remaining'  => null,
            ];
        }
    }

    /**
     * API endpoint for dashboard analytics
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $timeframe = $request->get('timeframe', '7d'); // 1d, 7d, 30d

        try {
            $analytics = [
                'user_activity'     => $this->getUserActivityAnalytics(),
                'ticket_trends'     => $this->getTicketTrendsAnalytics(),
                'alert_performance' => $this->getAlertPerformanceAnalytics(),
                'popular_content'   => $this->getPopularContentAnalytics(),
            ];

            return response()->json([
                'success'   => TRUE,
                'data'      => $analytics,
                'timeframe' => $timeframe,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching dashboard analytics', [
                'user_id'   => $user->id,
                'timeframe' => $timeframe,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to fetch analytics data',
            ], 500);
        }
    }

    /**
     * Get comprehensive dashboard data with caching
     */
    private function getComprehensiveDashboardData(User $user): array
    {
        $cacheKey = "dashboard_data:user:{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user): array {
            // Get flat statistics for the view
            $statistics = $this->formatStatisticsForView($user);
            $recentTickets = $this->getFormattedRecentTickets();

            return [
                'user'                        => $user,
                'statistics'                  => $statistics, // This will be the flat statistics array
                'stats'                       => $statistics, // Alias for backward compatibility
                'recentTickets'               => $recentTickets,
                'personalizedRecommendations' => $this->getPersonalizedRecommendations($user),
                'alertsData'                  => $this->getAlertsData($user),
                'trendsData'                  => $this->getTrendsData(),
                'upcomingEvents'              => $this->getUpcomingEvents($user),
                'priceAlerts'                 => $this->getPriceAlerts($user),
                'performanceMetrics'          => $this->getPerformanceMetrics(),
                'userPreferences'             => $this->getUserPreferences($user),
            ];
        });
    }

    /**
     * Format statistics as flat scalar values for the view
     */
    private function formatStatisticsForView(User $user): array
    {
        try {
            return [
                'available_tickets' => (int) $this->getAvailableTicketsCount(),
                'new_today'         => (int) $this->getNewTicketsToday(),
                'monitored_events'  => (int) $this->getMonitoredEventsCount($user),
                'active_alerts'     => (int) $this->getUserAlertsCount($user),
                'price_alerts'      => (int) $this->getUserAlertsCount($user), // Alias for price alerts
                'triggered_today'   => (int) $this->getTriggeredAlertsToday($user),
            ];
        } catch (Exception $e) {
            Log::warning('Failed to get dashboard statistics, using defaults', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            // Return safe default values
            return [
                'available_tickets' => 0,
                'new_today'         => 0,
                'monitored_events'  => 0,
                'active_alerts'     => 0,
                'price_alerts'      => 0,
                'triggered_today'   => 0,
            ];
        }
    }

    /**
     * Get enhanced statistics with trends and comparisons
     */
    private function getEnhancedStatistics(User $user): array
    {
        $now = Carbon::now();
        $now->copy()->subDay();
        $now->copy()->subWeek();

        return [
            'available_tickets' => [
                'current'    => $this->getAvailableTicketsCount(),
                'trend'      => $this->getTicketTrend(),
                'change_24h' => $this->getChange24h(),
            ],
            'high_demand' => [
                'current'    => $this->getHighDemandCount(),
                'trend'      => $this->getDemandTrend(),
                'change_24h' => $this->getChange24h(),
            ],
            'active_alerts' => [
                'current'         => $this->getUserAlertsCount($user),
                'triggered_today' => $this->getTriggeredAlertsToday($user),
                'success_rate'    => $this->getAlertSuccessRate(),
            ],
            'user_activity' => [
                'views_today'      => $this->getUserViewsToday(),
                'searches_today'   => $this->getUserSearchesToday(),
                'engagement_score' => $this->getUserEngagementScore(),
            ],
            'price_insights' => [
                'avg_price_trend'   => $this->getAvgPriceTrend(),
                'best_deals_count'  => $this->getBestDealsCount(),
                'price_drop_alerts' => $this->getPriceDropAlerts($user),
            ],
        ];
    }

    /**
     * Get recent tickets with enhanced metadata
     */
    private function getRecentTicketsWithMetadata(): Collection
    {
        return ScrapedTicket::with(['category'])
            ->available()
            ->recent(24) // Last 24 hours
            ->orderBy('scraped_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($ticket): array => [
                'id'                   => $ticket->id,
                'title'                => $ticket->title,
                'price'                => $ticket->price,
                'formatted_price'      => $ticket->formatted_price,
                'platform'             => $ticket->platform,
                'venue'                => $ticket->venue,
                'event_date'           => $ticket->event_date,
                'scraped_at'           => $ticket->scraped_at,
                'demand_indicator'     => $this->calculateDemandIndicator($ticket),
                'price_trend'          => $this->calculatePriceTrend(),
                'recommendation_score' => $this->calculateRecommendationScore($ticket),
                'urgency_level'        => $this->calculateUrgencyLevel($ticket),
            ]);
    }

    /**
     * Get formatted recent tickets with flat data structure for the view
     */
    private function getFormattedRecentTickets(): array
    {
        try {
            return ScrapedTicket::with(['category'])
                ->available()
                ->recent(24) // Last 24 hours
                ->orderBy('scraped_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($ticket): array {
                    return [
                        'id'         => (int) $ticket->id,
                        'title'      => (string) ($ticket->title ?? 'Sports Event'),
                        'venue'      => (string) ($ticket->venue ?? 'TBD'),
                        'price'      => (float) ($ticket->min_price ?? 0),
                        'platform'   => (string) ($ticket->platform ?? 'Unknown'),
                        'sport'      => (string) ($ticket->sport ?? 'Sports'),
                        'event_date' => $ticket->event_date ? $ticket->event_date->format('Y-m-d') : null,
                        'scraped_at' => $ticket->scraped_at ? $ticket->scraped_at->diffForHumans() : 'Recently',
                        'available'  => (bool) $ticket->is_available,
                        'high_demand' => (bool) ($ticket->is_high_demand ?? false),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::warning('Failed to get recent tickets, returning empty array', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get personalized recommendations
     */
    private function getPersonalizedRecommendations(User $user): array
    {
        $preferences = $user->preferences ?? [];
        $favoriteTeams = $preferences['favorite_teams'] ?? [];
        $priceRange = $preferences['price_range'] ?? [];

        $query = ScrapedTicket::available()
            ->recent(48)
            ->orderBy('popularity_score', 'desc');

        // Apply user preferences
        if (! empty($favoriteTeams)) {
            $query->where(function ($q) use ($favoriteTeams): void {
                foreach ($favoriteTeams as $team) {
                    $q->orWhere('title', 'like', "%{$team}%")
                        ->orWhere('teams', 'like', "%{$team}%");
                }
            });
        }

        if (! empty($priceRange) && isset($priceRange['max'])) {
            $query->where('min_price', '<=', $priceRange['max']);
        }

        return $query->limit(5)->get()->map(fn ($ticket): array => [
            'ticket'           => $ticket,
            'match_reason'     => $this->getMatchReason(),
            'confidence_score' => $this->getConfidenceScore(),
        ])->toArray();
    }

    /**
     * Get alerts data with analytics
     */
    private function getAlertsData(User $user): array
    {
        $alerts = TicketAlert::where('user_id', $user->id)
            ->with(['matches' => function ($query): void {
                $query->recent(30);
            }])
            ->get();

        return [
            'total_alerts'    => $alerts->count(),
            'active_alerts'   => $alerts->where('status', 'active')->count(),
            'triggered_today' => $alerts->sum(fn ($alert) => $alert->matches()->whereDate('created_at', Carbon::today())->count()),
            'success_rate'    => $this->calculateAlertSuccessRate($alerts),
            'top_performing'  => $alerts->sortByDesc('matches_count')->take(3),
        ];
    }

    /**
     * Get trends data for visualization
     */
    private function getTrendsData(): array
    {
        $last7Days = collect();
        $now = Carbon::now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $last7Days->push([
                'date'              => $date->format('Y-m-d'),
                'formatted_date'    => $date->format('M j'),
                'available_tickets' => $this->getTicketsCountForDate($date),
                'high_demand'       => $this->getHighDemandCountForDate($date),
                'avg_price'         => $this->getAvgPriceForDate($date),
            ]);
        }

        return [
            'last_7_days'       => $last7Days,
            'peak_hours'        => $this->getPeakHours(),
            'popular_platforms' => $this->getPopularPlatforms(),
            'trending_sports'   => $this->getTrendingSports(),
        ];
    }

    /**
     * Get upcoming events based on user preferences
     */
    private function getUpcomingEvents(User $user): Collection
    {
        $preferences = $user->preferences ?? [];
        $favoriteTeams = $preferences['favorite_teams'] ?? [];
        $favoriteVenues = $preferences['favorite_venues'] ?? [];

        return ScrapedTicket::available()
            ->where('event_date', '>', Carbon::now())
            ->where('event_date', '<=', Carbon::now()->addMonths(3))
            ->when(! empty($favoriteTeams), function ($query) use ($favoriteTeams): void {
                $query->where(function ($q) use ($favoriteTeams): void {
                    foreach ($favoriteTeams as $team) {
                        $q->orWhere('title', 'like', "%{$team}%");
                    }
                });
            })
            ->when(! empty($favoriteVenues), function ($query) use ($favoriteVenues): void {
                $query->whereIn('venue', $favoriteVenues);
            })
            ->orderBy('event_date')
            ->limit(8)
            ->get();
    }

    /**
     * Get price alerts for user
     */
    private function getPriceAlerts(User $user): array
    {
        return [
            'active_alerts' => TicketAlert::where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
            'recent_triggers' => TicketAlert::where('user_id', $user->id)
                ->whereHas('matches', function ($query): void {
                    $query->whereDate('created_at', Carbon::today());
                })
                ->with(['matches' => function ($query): void {
                    $query->whereDate('created_at', Carbon::today());
                }])
                ->get(),
        ];
    }

    /**
     * Get recently triggered alerts for user
     */
    private function getRecentlyTriggeredAlerts(): array
    {
        return [
            'count_today'    => 0,
            'recent_matches' => [],
        ];
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
            'last_check'       => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Get user views today
     */
    private function getUserViewsToday(): int
    {
        // This would integrate with actual analytics service
        return random_int(5, 25);
    }

    /**
     * Get user searches today
     */
    private function getUserSearchesToday(): int
    {
        // This would integrate with actual analytics service
        return random_int(0, 10);
    }

    /**
     * Get user engagement score
     */
    private function getUserEngagementScore(): float
    {
        // This would calculate based on user activity patterns
        return round(random_int(65, 95) / 100, 2);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'data_freshness'        => $this->getDataFreshness(),
            'system_uptime'         => $this->getSystemUptime(),
            'api_response_time'     => $this->getApiResponseTime(),
            'cache_hit_rate'        => $this->getCacheHitRate(),
            'scraping_success_rate' => $this->getScrapingSuccessRate(),
        ];
    }

    /**
     * Get user preferences with defaults
     */
    private function getUserPreferences(User $user): array
    {
        $preferences = $user->preferences ?? [];

        return array_merge([
            'theme'                 => 'light',
            'notifications'         => TRUE,
            'email_alerts'          => TRUE,
            'push_notifications'    => FALSE,
            'data_refresh_interval' => 300, // 5 minutes
            'favorite_teams'        => [],
            'favorite_venues'       => [],
            'price_range'           => ['min' => 0, 'max' => 1000],
            'preferred_platforms'   => [],
        ], $preferences);
    }

    // Helper methods for calculations and data retrieval
    private function getAvailableTicketsCount(): int
    {
        return ScrapedTicket::available()->count();
    }

    private function getTicketTrend(): string
    {
        $today = $this->getTicketsCountForDate(Carbon::today());
        $yesterday = $this->getTicketsCountForDate(Carbon::yesterday());

        if ($today > $yesterday) {
            return 'up';
        }
        if ($today < $yesterday) {
            return 'down';
        }

        return 'stable';
    }

    private function getChange24h(): float
    {
        // Implement 24h change calculation
        return 0.0;
    }

    private function getHighDemandCount(): int
    {
        return ScrapedTicket::available()
            ->where('popularity_score', '>', 80)
            ->count();
    }

    private function getDemandTrend(): string
    {
        // Implement demand trend calculation
        return 'stable';
    }

    private function getUserAlertsCount(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
    }

    private function getTriggeredAlertsToday(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereHas('matches', function ($query): void {
                $query->whereDate('created_at', Carbon::today());
            })
            ->count();
    }

    private function getAlertSuccessRate(): float
    {
        // Implement alert success rate calculation
        return 85.0;
    }

    private function getAvgPriceTrend(): string
    {
        // Implement average price trend calculation
        return 'stable';
    }

    private function getBestDealsCount(): int
    {
        // Implement best deals count
        return 0;
    }

    private function getPriceDropAlerts(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereHas('matches', function ($query): void {
                $query->whereDate('created_at', Carbon::today());
            })
            ->count();
    }

    private function calculateDemandIndicator($ticket): string
    {
        if ($ticket->popularity_score > 80) {
            return 'high';
        }
        if ($ticket->popularity_score > 50) {
            return 'medium';
        }

        return 'low';
    }

    private function calculatePriceTrend(): string
    {
        // Implement price trend calculation
        return 'stable';
    }

    private function calculateRecommendationScore($ticket): float
    {
        return (float) ($ticket->popularity_score ?? 50.0);
    }

    private function calculateUrgencyLevel($ticket): string
    {
        $daysUntilEvent = Carbon::parse($ticket->event_date)->diffInDays(Carbon::now());

        if ($daysUntilEvent <= 1) {
            return 'critical';
        }
        if ($daysUntilEvent <= 7) {
            return 'high';
        }
        if ($daysUntilEvent <= 30) {
            return 'medium';
        }

        return 'low';
    }

    private function getMatchReason(): string
    {
        return 'Matches your preferences';
    }

    private function getConfidenceScore(): float
    {
        return 85.0;
    }

    private function calculateAlertSuccessRate($alerts): float
    {
        if ($alerts->isEmpty()) {
            return 0.0;
        }

        $totalAlerts = $alerts->count();
        $successfulAlerts = $alerts->filter(fn ($alert): bool => $alert->matches_count > 0)->count();

        return ($successfulAlerts / $totalAlerts) * 100;
    }

    private function getTicketsCountForDate(Carbon $date): int
    {
        return ScrapedTicket::whereDate('scraped_at', $date)->count();
    }

    private function getHighDemandCountForDate(Carbon $date): int
    {
        return ScrapedTicket::whereDate('scraped_at', $date)
            ->where('popularity_score', '>', 80)
            ->count();
    }

    private function getAvgPriceForDate(Carbon $date): float
    {
        $avgPrice = ScrapedTicket::whereDate('scraped_at', $date)
            ->selectRaw('AVG((min_price + max_price) / 2) as avg_price')
            ->value('avg_price');

        return (float) ($avgPrice ?? 0.0);
    }

    private function getPeakHours(): array
    {
        return [
            ['hour' => 9, 'activity' => 85],
            ['hour' => 12, 'activity' => 92],
            ['hour' => 18, 'activity' => 78],
            ['hour' => 21, 'activity' => 65],
        ];
    }

    private function getPopularPlatforms(): array
    {
        return ScrapedTicket::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getTrendingSports(): array
    {
        return ScrapedTicket::selectRaw('sport, COUNT(*) as count')
            ->where('scraped_at', '>=', Carbon::now()->subWeek())
            ->groupBy('sport')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getDataFreshness(): string
    {
        $lastUpdate = ScrapedTicket::max('scraped_at');
        $minutesAgo = Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now());

        if ($minutesAgo <= 5) {
            return 'fresh';
        }
        if ($minutesAgo <= 30) {
            return 'recent';
        }

        return 'stale';
    }

    private function getSystemUptime(): float
    {
        return 99.9; // Placeholder
    }

    private function getApiResponseTime(): int
    {
        return 150; // Placeholder - milliseconds
    }

    private function getCacheHitRate(): float
    {
        return 92.5; // Placeholder
    }

    private function getScrapingSuccessRate(): float
    {
        return 98.2; // Placeholder
    }

    private function getNewTicketsToday(): int
    {
        return ScrapedTicket::whereDate('scraped_at', Carbon::today())->count();
    }

    private function getMonitoredEventsCount(User $user): int
    {
        // Count unique events from scraped tickets that match user's alert criteria or preferences
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

    private function getUserActivityAnalytics(): array
    {
        // Implement user activity analytics
        return [
            'page_views'     => 150,
            'searches'       => 45,
            'alerts_created' => 8,
            'tickets_viewed' => 120,
        ];
    }

    private function getTicketTrendsAnalytics(): array
    {
        // Implement ticket trends analytics
        return [
            'total_tickets' => 5420,
            'price_changes' => 230,
            'new_events'    => 15,
            'sold_out'      => 8,
        ];
    }

    private function getAlertPerformanceAnalytics(): array
    {
        // Implement alert performance analytics
        return [
            'alerts_triggered'   => 12,
            'successful_alerts'  => 9,
            'response_time'      => 2.5, // minutes
            'satisfaction_score' => 4.2,
        ];
    }

    private function getPopularContentAnalytics(): array
    {
        // Implement popular content analytics
        return [
            'top_sports'     => ['Football', 'Basketball', 'Baseball'],
            'top_venues'     => ['Stadium A', 'Arena B', 'Field C'],
            'trending_teams' => ['Team 1', 'Team 2', 'Team 3'],
        ];
    }
}
