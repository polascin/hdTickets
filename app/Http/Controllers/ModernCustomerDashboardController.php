<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Modern Customer Dashboard Controller
 *
 * Provides a comprehensive, state-of-the-art customer dashboard experience
 * with real-time data, modern UI components, and optimized performance.
 */
class ModernCustomerDashboardController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected RecommendationService $recommendationService,
    ) {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display the modern customer dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            abort(403, 'Access denied. Customer access required.');
        }

        // Get comprehensive dashboard data with caching
        $dashboardData = $this->getDashboardData($user);

        return view('dashboard.customer-modern', $dashboardData);
    }

    /**
     * Get real-time dashboard statistics via AJAX
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $this->getRealtimeStats($user);

        return response()->json([
            'success'   => TRUE,
            'data'      => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get recent tickets with pagination
     */
    public function getTickets(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $page = max(1, (int) $request->get('page', 1));
        $limit = min(50, max(10, (int) $request->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        $tickets = $this->getRecentTickets($user, $limit, $offset);
        $totalCount = $this->getTotalTicketsCount();

        return response()->json([
            'success' => TRUE,
            'data'    => [
                'tickets'    => $tickets,
                'pagination' => [
                    'current_page' => $page,
                    'per_page'     => $limit,
                    'total'        => $totalCount,
                    'last_page'    => ceil($totalCount / $limit),
                ],
            ],
        ]);
    }

    /**
     * Get user alerts with real-time updates
     */
    public function getAlerts(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alerts = $this->getUserAlerts($user);

        return response()->json([
            'success' => TRUE,
            'data'    => $alerts,
        ]);
    }

    /**
     * Get personalized recommendations
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $recommendations = $this->recommendationService->getPersonalizedRecommendations($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $recommendations,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get recommendations: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to load recommendations',
                'data'    => $this->getFallbackRecommendations(),
            ]);
        }
    }

    /**
     * Get market insights and analytics
     */
    public function getMarketInsights(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$this->isAuthorizedUser($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $insights = $this->analyticsService->getMarketInsights($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $insights,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get market insights: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to load market insights',
                'data'    => [],
            ]);
        }
    }

    /**
     * Check if user is authorized to access customer dashboard
     */
    private function isAuthorizedUser(User $user): bool
    {
        return in_array($user->role, ['customer', 'admin']);
    }

    /**
     * Get comprehensive dashboard data
     */
    private function getDashboardData(User $user): array
    {
        $cacheKey = "customer_dashboard_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return [
                'user'                => $user->load(['subscription', 'preferences']),
                'statistics'          => $this->getDashboardStatistics($user),
                'recent_tickets'      => $this->getRecentTickets($user, 10),
                'active_alerts'       => $this->getUserAlerts($user),
                'recommendations'     => $this->getBasicRecommendations($user),
                'market_insights'     => $this->getBasicMarketInsights($user),
                'quick_actions'       => $this->getQuickActions($user),
                'subscription_status' => $this->getSubscriptionStatus($user),
            ];
        });
    }

    /**
     * Get real-time dashboard statistics
     */
    private function getRealtimeStats(User $user): array
    {
        try {
            return [
                'available_tickets' => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')->count(),
                'new_today' => ScrapedTicket::whereDate('created_at', today())
                    ->where('is_available', TRUE)->where('status', 'active')->count(),
                'monitored_events' => $this->getMonitoredEventsCount($user),
                'active_alerts'    => TicketAlert::where('user_id', $user->id)
                    ->where('status', 'active')->count(),
                'total_savings'          => $this->calculateTotalSavings($user),
                'price_alerts_triggered' => $this->getPriceAlertsTriggeredToday($user),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get realtime stats: ' . $e->getMessage());

            return $this->getFallbackStats();
        }
    }

    /**
     * Get dashboard statistics with safe defaults
     */
    private function getDashboardStatistics(User $user): array
    {
        try {
            return [
                'available_tickets' => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')->count(),
                'new_today' => ScrapedTicket::whereDate('created_at', today())
                    ->where('is_available', TRUE)->where('status', 'active')->count(),
                'unique_events' => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')->distinct('title')->count(),
                'monitored_events' => $this->getMonitoredEventsCount($user),
                'active_alerts'    => TicketAlert::where('user_id', $user->id)
                    ->where('status', 'active')->count(),
                'total_savings' => $this->calculateTotalSavings($user),
                'average_price' => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')->avg('min_price') ?? 0,
                'price_trend'            => $this->calculatePriceTrend(),
                'price_alerts_triggered' => $this->getPriceAlertsTriggeredToday($user),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get dashboard statistics: ' . $e->getMessage());

            return $this->getFallbackStats();
        }
    }

    /**
     * Get recent tickets with user context
     */
    private function getRecentTickets(User $user, int $limit = 10, int $offset = 0): Collection
    {
        try {
            return ScrapedTicket::select([
                'id', 'title', 'venue', 'event_date', 'min_price',
                'max_price', 'platform', 'event_type', 'created_at',
                'external_id', 'ticket_url',
            ])
            ->where('is_available', TRUE)
            ->where('status', 'active')
            ->when($user->preferences, function ($query) use ($user) {
                // Apply user preferences for personalization
                $preferences = $user->preferences;
                if ($preferences->favorite_categories) {
                    $categories = explode(',', $preferences->favorite_categories);
                    $query->whereIn('event_type', $categories);
                }
            })
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id'             => $ticket->id,
                    'event_name'     => $ticket->title,
                    'venue_name'     => $ticket->venue ?: 'TBD',
                    'event_date'     => $ticket->event_date ? Carbon::parse($ticket->event_date)->format('M j, Y g:i A') : 'TBD',
                    'price'          => number_format((float) $ticket->min_price, 2),
                    'original_price' => $ticket->max_price ? number_format((float) $ticket->max_price, 2) : NULL,
                    'discount'       => $ticket->max_price && $ticket->min_price < $ticket->max_price ?
                        round((($ticket->max_price - $ticket->min_price) / $ticket->max_price) * 100) : NULL,
                    'platform'     => ucfirst($ticket->platform),
                    'category'     => ucfirst($ticket->event_type),
                    'image_url'    => NULL, // Not available in current schema
                    'external_url' => $ticket->ticket_url,
                    'time_ago'     => $ticket->created_at->diffForHumans(),
                ];
            });
        } catch (Exception $e) {
            Log::error('Failed to get recent tickets: ' . $e->getMessage());

            return collect([]);
        }
    }

    /**
     * Get user alerts with status
     */
    private function getUserAlerts(User $user): Collection
    {
        try {
            return TicketAlert::where('user_id', $user->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($alert) {
                    return [
                        'id'           => $alert->id,
                        'title'        => $alert->event_name ?? $alert->keyword,
                        'criteria'     => $alert->criteria,
                        'status'       => $alert->is_triggered ? 'triggered' : 'active',
                        'created_at'   => $alert->created_at->diffForHumans(),
                        'last_checked' => $alert->updated_at->diffForHumans(),
                    ];
                });
        } catch (Exception $e) {
            Log::error('Failed to get user alerts: ' . $e->getMessage());

            return collect([]);
        }
    }

    /**
     * Get basic recommendations without external service
     */
    private function getBasicRecommendations(User $user): array
    {
        try {
            // Get popular events based on user's activity
            $popular = ScrapedTicket::select('title', 'venue', 'event_type', DB::raw('COUNT(*) as popularity'))
                ->where('is_available', TRUE)
                ->where('status', 'active')
                ->groupBy(['title', 'venue', 'event_type'])
                ->orderBy('popularity', 'desc')
                ->limit(5)
                ->get();

            return [
                'popular_events' => $popular->map(function ($event) {
                    return [
                        'event_name' => $event->title,
                        'venue_name' => $event->venue ?: 'TBD',
                        'category'   => ucfirst($event->event_type),
                        'popularity' => $event->popularity,
                    ];
                })->toArray(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get basic recommendations: ' . $e->getMessage());

            return ['popular_events' => []];
        }
    }

    /**
     * Get basic market insights
     */
    private function getBasicMarketInsights(User $user): array
    {
        try {
            return [
                'trending_categories' => $this->getTrendingCategories(),
                'price_alerts'        => $this->getActivePriceAlerts($user),
                'market_activity'     => $this->getMarketActivity(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get basic market insights: ' . $e->getMessage());

            return [
                'trending_categories' => [],
                'price_alerts'        => [],
                'market_activity'     => [],
            ];
        }
    }

    /**
     * Get quick actions based on user role and activity
     */
    private function getQuickActions(User $user): array
    {
        return [
            [
                'title'       => 'Browse Tickets',
                'description' => 'Discover new events and tickets',
                'icon'        => 'search',
                'url'         => route('tickets.main'),
                'color'       => 'blue',
            ],
            [
                'title'       => 'Create Alert',
                'description' => 'Set up price monitoring',
                'icon'        => 'bell',
                'url'         => route('tickets.alerts.create'),
                'color'       => 'amber',
            ],
            [
                'title'       => 'My Alerts',
                'description' => 'Manage your alerts',
                'icon'        => 'list',
                'url'         => route('tickets.alerts.index'),
                'color'       => 'green',
            ],
            [
                'title'       => 'Account Settings',
                'description' => 'Manage your profile',
                'icon'        => 'settings',
                'url'         => route('profile.show'),
                'color'       => 'purple',
            ],
        ];
    }

    /**
     * Get subscription status information
     */
    private function getSubscriptionStatus(User $user): array
    {
        try {
            $subscription = $user->subscription;

            return [
                'is_active'      => $user->hasActiveSubscription(),
                'plan_name'      => $subscription?->plan_name ?? 'Free Trial',
                'next_billing'   => $subscription?->next_billing_date?->format('M j, Y'),
                'days_remaining' => $user->hasActiveSubscription() ?
                    NULL : $user->getFreeTrialDaysRemaining(),
                'usage_stats' => [
                    'alerts_used'  => TicketAlert::where('user_id', $user->id)->count(),
                    'alerts_limit' => $user->hasActiveSubscription() ? 'unlimited' : 5,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get subscription status: ' . $e->getMessage());

            return [
                'is_active'   => FALSE,
                'plan_name'   => 'Unknown',
                'usage_stats' => ['alerts_used' => 0, 'alerts_limit' => 5],
            ];
        }
    }

    // Helper methods for statistics calculations

    private function getMonitoredEventsCount(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->where('status', 'active')
            ->distinct('alert_name')
            ->count();
    }

    private function calculateTotalSavings(User $user): float
    {
        return 0.0; // Placeholder - implement based on user's purchase history
    }

    private function calculatePriceTrend(): array
    {
        try {
            $currentAvg = ScrapedTicket::where('is_available', TRUE)
                ->where('status', 'active')
                ->whereDate('created_at', '>=', today()->subDays(7))
                ->avg('min_price') ?? 0;

            $previousAvg = ScrapedTicket::where('is_available', TRUE)
                ->where('status', 'active')
                ->whereDate('created_at', '<', today()->subDays(7))
                ->whereDate('created_at', '>=', today()->subDays(14))
                ->avg('min_price') ?? 0;

            $trend = $previousAvg > 0 ? (($currentAvg - $previousAvg) / $previousAvg) * 100 : 0;

            return [
                'direction'  => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
                'percentage' => abs(round($trend, 1)),
            ];
        } catch (Exception $e) {
            return ['direction' => 'stable', 'percentage' => 0];
        }
    }

    private function getPriceAlertsTriggeredToday(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->where('status', 'triggered')
            ->whereDate('triggered_at', today())
            ->count();
    }

    private function getTotalTicketsCount(): int
    {
        return ScrapedTicket::where('is_available', TRUE)
            ->where('status', 'active')->count();
    }

    private function getTrendingCategories(): array
    {
        try {
            return ScrapedTicket::select('event_type', DB::raw('COUNT(*) as count'))
                ->where('is_available', TRUE)
                ->where('status', 'active')
                ->whereDate('created_at', '>=', today()->subDays(7))
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => ucfirst($item->event_type),
                        'count'    => $item->count,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getActivePriceAlerts(User $user): array
    {
        return TicketAlert::where('user_id', $user->id)
            ->where('status', 'active')
            ->limit(3)
            ->get()
            ->map(function ($alert) {
                return [
                    'event_name'   => $alert->alert_name,
                    'target_price' => $alert->max_price ?? $alert->min_price,
                    'status'       => $alert->status === 'triggered' ? 'triggered' : 'monitoring',
                ];
            })
            ->toArray();
    }

    private function getMarketActivity(): array
    {
        try {
            return [
                'tickets_added_today' => ScrapedTicket::whereDate('created_at', today())->count(),
                'active_platforms'    => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')
                    ->distinct('platform')->count(),
                'average_discount' => ScrapedTicket::where('is_available', TRUE)
                    ->where('status', 'active')
                    ->whereNotNull('max_price')
                    ->where('min_price', '<', 'max_price')
                    ->selectRaw('AVG((max_price - min_price) / max_price * 100) as avg_discount')
                    ->value('avg_discount') ?? 0,
            ];
        } catch (Exception $e) {
            return [
                'tickets_added_today' => 0,
                'active_platforms'    => 0,
                'average_discount'    => 0,
            ];
        }
    }

    private function getFallbackStats(): array
    {
        return [
            'available_tickets'      => 0,
            'new_today'              => 0,
            'unique_events'          => 0,
            'monitored_events'       => 0,
            'active_alerts'          => 0,
            'total_savings'          => 0.0,
            'average_price'          => 0.0,
            'price_trend'            => ['direction' => 'stable', 'percentage' => 0],
            'price_alerts_triggered' => 0,
        ];
    }

    private function getFallbackRecommendations(): array
    {
        return [
            'popular_events'         => [],
            'recommended_categories' => [],
            'trending_venues'        => [],
        ];
    }
}
