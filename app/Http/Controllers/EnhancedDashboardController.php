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
    protected AnalyticsService $analytics;

    protected RecommendationService $recommendations;

    public function __construct(AnalyticsService $analytics, RecommendationService $recommendations)
    {
        $this->analytics = $analytics;
        $this->recommendations = $recommendations;
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

        return view('dashboard.customer-enhanced-fixed', $dashboardData);
    }

    /**
     * API endpoint for real-time data updates
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $data = [
                'statistics'       => $this->getEnhancedStatistics($user),
                'recent_tickets'   => $this->getRecentTicketsWithMetadata()->take(5),
                'alerts_triggered' => $this->getRecentlyTriggeredAlerts($user),
                'system_status'    => $this->getSystemStatus(),
                'timestamp'        => Carbon::now()->toISOString(),
            ];

            return response()->json([
                'success'      => TRUE,
                'data'         => $data,
                'cache_status' => 'fresh',
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
     * API endpoint for dashboard analytics
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $timeframe = $request->get('timeframe', '7d'); // 1d, 7d, 30d

        try {
            $analytics = [
                'user_activity'     => $this->getUserActivityAnalytics($user, $timeframe),
                'ticket_trends'     => $this->getTicketTrendsAnalytics($timeframe),
                'alert_performance' => $this->getAlertPerformanceAnalytics($user, $timeframe),
                'popular_content'   => $this->getPopularContentAnalytics($timeframe),
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

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return [
                'user'                        => $user,
                'statistics'                  => $this->getEnhancedStatistics($user),
                'recentTickets'               => $this->getRecentTicketsWithMetadata(),
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
     * Get enhanced statistics with trends and comparisons
     */
    private function getEnhancedStatistics(User $user): array
    {
        $now = Carbon::now();
        $yesterday = $now->copy()->subDay();
        $lastWeek = $now->copy()->subWeek();

        return [
            'available_tickets' => [
                'current'    => $this->getAvailableTicketsCount(),
                'trend'      => $this->getTicketTrend(),
                'change_24h' => $this->getChange24h('tickets'),
            ],
            'high_demand' => [
                'current'    => $this->getHighDemandCount(),
                'trend'      => $this->getDemandTrend(),
                'change_24h' => $this->getChange24h('demand'),
            ],
            'active_alerts' => [
                'current'         => $this->getUserAlertsCount($user),
                'triggered_today' => $this->getTriggeredAlertsToday($user),
                'success_rate'    => $this->getAlertSuccessRate($user),
            ],
            'user_activity' => [
                'views_today'      => $this->getUserViewsToday($user),
                'searches_today'   => $this->getUserSearchesToday($user),
                'engagement_score' => $this->getUserEngagementScore($user),
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
            ->map(function ($ticket) {
                return [
                    'id'                   => $ticket->id,
                    'title'                => $ticket->title,
                    'price'                => $ticket->price,
                    'formatted_price'      => $ticket->formatted_price,
                    'platform'             => $ticket->platform,
                    'venue'                => $ticket->venue,
                    'event_date'           => $ticket->event_date,
                    'scraped_at'           => $ticket->scraped_at,
                    'demand_indicator'     => $this->calculateDemandIndicator($ticket),
                    'price_trend'          => $this->calculatePriceTrend($ticket),
                    'recommendation_score' => $this->calculateRecommendationScore($ticket),
                    'urgency_level'        => $this->calculateUrgencyLevel($ticket),
                ];
            });
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

        return $query->limit(5)->get()->map(function ($ticket) {
            return [
                'ticket'           => $ticket,
                'match_reason'     => $this->getMatchReason($ticket),
                'confidence_score' => $this->getConfidenceScore($ticket),
            ];
        })->toArray();
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
            'triggered_today' => $alerts->sum(function ($alert) {
                return $alert->matches()->whereDate('created_at', Carbon::today())->count();
            }),
            'success_rate'   => $this->calculateAlertSuccessRate($alerts),
            'top_performing' => $alerts->sortByDesc('matches_count')->take(3),
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

    private function getChange24h(string $metric): float
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

    private function getAlertSuccessRate(User $user): float
    {
        // Implement alert success rate calculation
        return 85.0;
    }

    private function getUserViewsToday(User $user): int
    {
        // Implement user views tracking
        return 0;
    }

    private function getUserSearchesToday(User $user): int
    {
        // Implement user searches tracking
        return 0;
    }

    private function getUserEngagementScore(User $user): float
    {
        // Implement engagement score calculation
        return 75.0;
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

    private function calculatePriceTrend($ticket): string
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

    private function getMatchReason($ticket): string
    {
        return 'Matches your preferences';
    }

    private function getConfidenceScore($ticket): float
    {
        return 85.0;
    }

    private function calculateAlertSuccessRate($alerts): float
    {
        if ($alerts->isEmpty()) {
            return 0.0;
        }

        $totalAlerts = $alerts->count();
        $successfulAlerts = $alerts->filter(function ($alert) {
            return $alert->matches_count > 0;
        })->count();

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

    private function getRecentlyTriggeredAlerts(User $user): Collection
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereHas('matches', function ($query): void {
                $query->where('created_at', '>=', Carbon::now()->subHour());
            })
            ->with('matches')
            ->limit(5)
            ->get();
    }

    private function getSystemStatus(): array
    {
        return [
            'status'        => 'operational',
            'uptime'        => $this->getSystemUptime(),
            'response_time' => $this->getApiResponseTime(),
            'last_update'   => Carbon::now()->toISOString(),
        ];
    }

    private function getUserActivityAnalytics(User $user, string $timeframe): array
    {
        // Implement user activity analytics
        return [
            'page_views'     => 150,
            'searches'       => 45,
            'alerts_created' => 8,
            'tickets_viewed' => 120,
        ];
    }

    private function getTicketTrendsAnalytics(string $timeframe): array
    {
        // Implement ticket trends analytics
        return [
            'total_tickets' => 5420,
            'price_changes' => 230,
            'new_events'    => 15,
            'sold_out'      => 8,
        ];
    }

    private function getAlertPerformanceAnalytics(User $user, string $timeframe): array
    {
        // Implement alert performance analytics
        return [
            'alerts_triggered'   => 12,
            'successful_alerts'  => 9,
            'response_time'      => 2.5, // minutes
            'satisfaction_score' => 4.2,
        ];
    }

    private function getPopularContentAnalytics(string $timeframe): array
    {
        // Implement popular content analytics
        return [
            'top_sports'     => ['Football', 'Basketball', 'Baseball'],
            'top_venues'     => ['Stadium A', 'Arena B', 'Field C'],
            'trending_teams' => ['Team 1', 'Team 2', 'Team 3'],
        ];
    }
}
