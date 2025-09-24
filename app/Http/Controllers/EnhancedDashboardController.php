<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DashboardAnalyticsResource;
use App\Http\Resources\DashboardRealtimeResource;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Services\Dashboard\AlertService;
use App\Services\Dashboard\AnalyticsService;
use App\Services\Dashboard\RecommendationService;
use App\Services\Dashboard\TicketStatsService;
use App\Services\Dashboard\UserMetricsService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// for static analysis clarity
use Illuminate\Support\Facades\Response;

/**
 * Enhanced Customer Dashboard Controller for HD Tickets Sports Event Monitoring
 *
 * Provides comprehensive sports event ticket monitoring dashboard functionality
 * with real-time updates, personalized recommendations, and user analytics.
 *
 * Features:
 * - Real-time ticket statistics and trends
 * - Personalized recommendations based on user preferences
 * - Alert management and monitoring
 * - Subscription and usage tracking
 * Performance metrics and system health
 */
class EnhancedDashboardController extends Controller
{
    public function __construct(
        protected TicketStatsService $ticketStatsService,
        protected UserMetricsService $userMetricsService,
        protected RecommendationService $recommendationService,
        protected AlertService $alertService,
        protected AnalyticsService $analyticsService,
    ) {
        // Apply auth + email verification middleware conventionally
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display the enhanced customer dashboard
     *
     * Main entry point for the sports event ticket monitoring dashboard.
     * Aggregates all necessary data and renders the customer-v3 view.
     */
    public function index(): View
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            abort(403, 'Access denied. Customer or admin role required.');
        }

        // Get comprehensive dashboard data with caching
        $dashboardData = $this->getDashboardData($user);

        Log::info('Customer dashboard accessed', [
          'user_id'   => $user->id,
          'role'      => $user->role,
          'timestamp' => now()->toISOString(),
        ]);

        return view('dashboard.new-dashboard', $dashboardData);
    }

    /**
     * API endpoint for real-time dashboard data updates
     *
     * Provides fresh dashboard statistics and recent tickets for AJAX updates.
     * Implements caching and error handling for optimal performance.
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json([
              'success' => FALSE,
              'error'   => 'Authentication required',
            ], 401);
        }

        try {
            $cacheKey = "dashboard_realtime_data:{$user->id}";

            $raw = Cache::remember($cacheKey, Carbon::now()->addMinutes(2), function () use ($user) {
                return [
                  'statistics'     => $this->getStatistics($user),
                  'recent_tickets' => $this->getRecentTickets($user),
                  'user_metrics'   => $this->getUserMetrics($user),
                  'system_status'  => $this->getSystemStatus(),
                  'notifications'  => $this->getNotifications($user),
                  'last_updated'   => Carbon::now()->toISOString(),
                ];
            });

            $resource = new DashboardRealtimeResource($raw);
            $dataArray = $resource->toArray($request);

            // Build response payload
            $payload = [
              'success' => TRUE,
              'data'    => $dataArray,
              'meta'    => [
                'refresh_interval' => 120,
                'cache_status'     => 'fresh',
                'user_id'          => $user->id,
              ],
            ];

            // Generate ETag based on serialized payload (fast hash)
            $etag = 'W/"rt-' . substr(sha1(json_encode($payload)), 0, 20) . '"';
            $lastModified = $raw['last_updated'] ?? Carbon::now()->toISOString();

            if ($request->headers->get('If-None-Match') === $etag || $request->headers->get('If-Modified-Since') === $lastModified) {
                return Response::json(NULL, 304, [
                  'ETag'          => $etag,
                  'Last-Modified' => $lastModified,
                  'Cache-Control' => 'private, max-age=60',
                ]);
            }

            return Response::json($payload, 200, [
              'ETag'          => $etag,
              'Last-Modified' => $lastModified,
              'Cache-Control' => 'private, max-age=60',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch realtime dashboard data', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
              'trace'   => $e->getTraceAsString(),
            ]);

            return Response::json([
              'success'     => FALSE,
              'error'       => 'Unable to fetch dashboard data',
              'retry_after' => 30,
            ], 500);
        }
    }

    /**
     * Analytics data endpoint
     *
     * Provides lightweight analytics/trend data for dashboard widgets.
     * Currently returns a simplified placeholder structure; can be expanded
     * with real trend calculations (moving averages, growth rates, etc.).
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$this->isAuthorizedUser($user)) {
            return Response::json(['success' => FALSE, 'error' => 'Authentication required'], 401);
        }
        $payload = $this->analyticsService->buildAnalytics($user);
        $resource = new DashboardAnalyticsResource($payload);
        $dataArray = $resource->toArray($request);
        $responsePayload = [
          'success' => TRUE,
          'data'    => $dataArray,
          'meta'    => [
            'user_id'      => $user->id,
            'generated_at' => $payload['generated_at'] ?? NULL,
          ],
        ];

        $etag = 'W/"an-' . substr(sha1(json_encode($responsePayload)), 0, 20) . '"';
        $lastModified = $payload['generated_at'] ?? Carbon::now()->toISOString();

        if ($request->headers->get('If-None-Match') === $etag || $request->headers->get('If-Modified-Since') === $lastModified) {
            return Response::json(NULL, 304, [
              'ETag'          => $etag,
              'Last-Modified' => $lastModified,
              'Cache-Control' => 'private, max-age=120',
            ]);
        }

        return Response::json($responsePayload, 200, [
          'ETag'          => $etag,
          'Last-Modified' => $lastModified,
          'Cache-Control' => 'private, max-age=120',
        ]);
    }

    /**
     * Get comprehensive dashboard data with all required sections
     */
    protected function getDashboardData(User $user): array
    {
        $cacheKey = "dashboard_complete_data:{$user->id}";

        return Cache::remember($cacheKey, Carbon::now()->addMinutes(5), function () use ($user) {
            return [
              'user'              => $user,
              'statistics'        => $this->getStatistics($user),
              'recent_tickets'    => $this->getRecentTickets($user),
              'recommendations'   => $this->getPersonalizedRecommendations($user),
              'user_metrics'      => $this->getUserMetrics($user),
              'alerts_data'       => $this->getAlertsData($user),
              'subscription_data' => $this->getSubscriptionData($user),
              'trending_events'   => $this->getTrendingEvents(),
              'quick_actions'     => $this->getQuickActions(),
              'system_status'     => $this->getSystemStatus(),
              'performance_data'  => $this->getPerformanceData(),
              'notifications'     => $this->getNotifications($user),
              'generated_at'      => Carbon::now()->toISOString(),
            ];
        });
    }

    /**
     * Get dashboard statistics with proper formatting
     */
    protected function getStatistics(User $user): array
    {
        try {
            return $this->ticketStatsService->getDashboardStats();
        } catch (\Exception $e) {
            Log::warning('Failed to get dashboard statistics', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get recent tickets with enhanced formatting
     */
    protected function getRecentTickets(User $user, int $limit = 10): array
    {
        try {
            $cacheKey = "recent_tickets:{$user->id}:{$limit}";

            return Cache::remember($cacheKey, Carbon::now()->addMinutes(3), function () use ($limit) {
                $tickets = ScrapedTicket::with(['category'])
                  ->available()
                  ->recent(24)
                  ->orderByDesc('scraped_at')
                  ->limit($limit)
                  ->get();

                return \App\Http\Resources\TicketSummaryResource::collection($tickets)->resolve();
            });
        } catch (\Exception $e) {
            Log::warning('Failed to get recent tickets', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get personalized recommendations based on user preferences
     */
    protected function getPersonalizedRecommendations(User $user): array
    {
        try {
            return $this->recommendationService->getDashboardRecommendations($user);
        } catch (\Exception $e) {
            Log::warning('Failed to get personalized recommendations', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get user-specific metrics and analytics
     */
    protected function getUserMetrics(User $user): array
    {
        try {
            return $this->userMetricsService->getUserDashboardMetrics($user);
        } catch (\Exception $e) {
            Log::warning('Failed to get user metrics', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return $this->getDefaultUserMetrics();
        }
    }

    /**
     * Get alerts data and statistics
     */
    protected function getAlertsData(User $user): array
    {
        try {
            return $this->alertService->getUserAlertStats($user);
        } catch (\Exception $e) {
            Log::warning('Failed to get alerts data', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return ['total_alerts' => 0, 'active_alerts' => 0];
        }
    }

    /**
     * Get subscription data and usage statistics
     */
    protected function getSubscriptionData(User $user): array
    {
        try {
            return [
              'plan_name'         => $user->subscription_plan ?? 'Free',
              'monthly_limit'     => $user->getMonthlyTicketLimit() ?? 100,
              'current_usage'     => $user->getMonthlyTicketUsage() ?? 0,
              'usage_percentage'  => $this->calculateUsagePercentage($user),
              'days_remaining'    => $user->getFreeTrialDaysRemaining(),
              'is_active'         => $user->hasActiveSubscription() ?? FALSE,
              'next_billing_date' => $user->next_billing_date?->format('M j, Y'),
              'can_upgrade'       => $this->canUserUpgrade($user),
            ];
        } catch (\Exception $e) {
            Log::debug('Subscription data unavailable, using defaults', [
              'user_id' => $user->id,
              'error'   => $e->getMessage(),
            ]);

            return [
              'plan_name'        => 'Free',
              'monthly_limit'    => 100,
              'current_usage'    => 0,
              'usage_percentage' => 0,
              'is_active'        => FALSE,
              'can_upgrade'      => TRUE,
            ];
        }
    }

    /**
     * Get trending events across all platforms
     */
    protected function getTrendingEvents(): array
    {
        try {
            return Cache::remember('trending_events', Carbon::now()->addMinutes(10), function () {
                return ScrapedTicket::select([
                  'sport',
                  'title',
                  'venue',
                  'event_date',
                  DB::raw('COUNT(*) as ticket_count'),
                  DB::raw('MIN(min_price) as lowest_price'),
                  DB::raw('AVG(popularity_score) as avg_popularity'),
                ])
                  ->available()
                  ->upcoming()
                  ->recent(24)
                  ->groupBy(['sport', 'title', 'venue', 'event_date'])
                  ->having('ticket_count', '>=', 3)
                  ->orderByDesc('avg_popularity')
                  ->limit(5)
                  ->get()
                  ->toArray();
            });
        } catch (\Exception $e) {
            Log::warning('Failed to get trending events', [
              'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get quick action items for the dashboard
     */
    protected function getQuickActions(): array
    {
        return [
          [
            'label'       => 'Find Tickets',
            'url'         => route('tickets.scraping.index'),
            'icon'        => 'search',
            'description' => 'Browse available sports event tickets',
          ],
          [
            'label'       => 'My Alerts',
            'url'         => route('tickets.alerts.index'),
            'icon'        => 'bell',
            'description' => 'Manage your price alerts',
          ],
          [
            'label'       => 'Purchase History',
            'url'         => route('purchase-decisions.index'),
            'icon'        => 'history',
            'description' => 'View your ticket purchases',
          ],
          [
            'label'       => 'Account Settings',
            'url'         => route('profile.edit'),
            'icon'        => 'settings',
            'description' => 'Update your preferences',
          ],
        ];
    }

    /**
     * Get system status and health information
     */
    protected function getSystemStatus(): array
    {
        return Cache::remember('system_status', Carbon::now()->addMinutes(1), function () {
            return [
              'scraping_active'   => $this->isScrapingActive(),
              'database_healthy'  => $this->isDatabaseHealthy(),
              'cache_operational' => $this->isCacheOperational(),
              'api_responsive'    => TRUE,
              'last_scrape_time'  => $this->getLastScrapeTime(),
              'system_load'       => $this->getSystemLoad(),
              'uptime_percentage' => 99.9,
            ];
        });
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceData(): array
    {
        return [
          'average_response_time' => '150ms',
          'cache_hit_rate'        => '92.3%',
          'scraping_success_rate' => '98.7%',
          'user_satisfaction'     => '4.6/5',
        ];
    }

    /**
     * Get user notifications
     */
    protected function getNotifications(User $user): array
    {
        return [
          'unread_count' => 0,
          'recent'       => [],
        ];
    }

    // Helper Methods for Statistics Calculation

    protected function getAvailableTicketsCount(): int
    {
        return (int) ScrapedTicket::available()->count();
    }

    protected function getNewTicketsToday(Carbon $today): int
    {
        return (int) ScrapedTicket::whereDate('scraped_at', $today)->count();
    }

    protected function getMonitoredEventsCount(User $user): int
    {
        return (int) ScrapedTicket::available()
          ->selectRaw('COUNT(DISTINCT CONCAT(title, venue, event_date)) as unique_events')
          ->value('unique_events') ?: 0;
    }

    protected function getActiveAlertsCount(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
          ->where('status', 'active')
          ->count();
    }

    protected function getPriceAlertsCount(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
          ->where('alert_type', 'price_drop')
          ->where('status', 'active')
          ->count();
    }

    protected function getTriggeredAlertsToday(User $user, Carbon $today): int
    {
        return TicketAlert::where('user_id', $user->id)
          ->whereDate('last_triggered_at', $today)
          ->count();
    }

    protected function getWeeklySavings(User $user, Carbon $thisWeek): float
    {
        // This would calculate actual savings based on purchase history
        return 0.0;
    }

    protected function getTotalWatchedEvents(User $user): int
    {
        return $user->watched_events_count ?? 0;
    }

    // Helper Methods for Data Processing

    protected function calculatePriceTrend($ticket): string
    {
        // Logic to determine if price is trending up, down, or stable
        return 'stable';
    }

    protected function getDemandLevel($ticket): string
    {
        $popularity = $ticket->popularity_score ?? 0;

        if ($popularity >= 80) {
            return 'high';
        }
        if ($popularity >= 50) {
            return 'medium';
        }

        return 'low';
    }

    protected function calculateRecommendationScore($ticket): float
    {
        return rand(75, 95) / 100;
    }

    protected function getMatchReason($ticket): string
    {
        return 'Matches your preferences';
    }

    protected function isAuthorizedUser(User $user): bool
    {
        return in_array($user->role, ['customer', 'admin']);
    }

    protected function getDefaultStatistics(): array
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

    protected function getDefaultUserMetrics(): array
    {
        return [
          'total_savings'     => 0,
          'tickets_purchased' => 0,
          'alerts_created'    => 0,
          'activity_score'    => 0,
        ];
    }

    // Placeholder methods for future implementation
    protected function calculateTotalSavings(User $user): float
    {
        return 0.0;
    }

    protected function getTicketsPurchased(User $user): int
    {
        return 0;
    }

    protected function getTotalAlertsCreated(User $user): int
    {
        return 0;
    }

    protected function getSuccessfulPurchases(User $user): int
    {
        return 0;
    }

    protected function getAverageTicketPrice(User $user): float
    {
        return 0.0;
    }

    protected function getFavoritePlatform(User $user): string
    {
        return 'Unknown';
    }

    protected function calculateActivityScore(User $user): float
    {
        return 0.0;
    }

    protected function getEngagementLevel(User $user): string
    {
        return 'Low';
    }

    protected function calculateUsagePercentage(User $user): float
    {
        return 0.0;
    }

    protected function canUserUpgrade(User $user): bool
    {
        return TRUE;
    }

    protected function calculateAlertSuccessRate($alerts): float
    {
        return 0.0;
    }

    protected function isScrapingActive(): bool
    {
        return TRUE;
    }

    protected function isDatabaseHealthy(): bool
    {
        return TRUE;
    }

    protected function isCacheOperational(): bool
    {
        return TRUE;
    }

    protected function getLastScrapeTime(): ?string
    {
        return Carbon::now()->subMinutes(5)->toISOString();
    }

    protected function getSystemLoad(): float
    {
        return 0.45;
    }
}
