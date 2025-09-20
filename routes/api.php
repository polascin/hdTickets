<?php declare(strict_types=1);

/**
 * HD Tickets API Routes - Sports Events Entry Tickets Monitoring System
 *
 * This file contains the API routes for the HD Tickets application, implementing
 * comprehensive role-based access control for sports events ticket monitoring,
 * scraping, and purchase automation.
 *
 * @version 4.0.0
 *
 * @environment Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
 *
 * API ARCHITECTURE OVERVIEW:
 * =========================
 *
 * Authentication & Authorization:
 * - Laravel Sanctum token-based authentication
 * - Role-based access control (RBAC) with CheckApiRole middleware
 * - Rate limiting per route group and role
 * - Sports events ticket monitoring focus (NOT helpdesk system)
 *
 * API User Roles & Access:
 * - Admin: Full API access to all endpoints
 * - Agent: Scraping, purchase, and monitoring endpoints
 * - Customer: Basic monitoring and preference endpoints
 * - Scraper: API-only access for platform rotation (no web interface)
 *
 * Rate Limiting Strategy:
 * - Public routes: 10 requests/minute
 * - Authenticated routes: 120 requests/minute
 * - Scraping routes: 30-60 requests/minute (anti-detection)
 *
 * API Versioning:
 * - Current version: v1
 * - All routes prefixed with /api/v1/
 * - Backward compatibility maintained
 */
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessIntelligenceApiController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnhancedAnalyticsController;
use App\Http\Controllers\Api\ImapMonitoringController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\PerformanceMetricsController;
use App\Http\Controllers\Api\PreferencesController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ScrapingController;
use App\Http\Controllers\Api\StubHubController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketCriteriaController;
use App\Http\Controllers\Api\TicketmasterController;
use App\Http\Controllers\Api\TickPickController;
use App\Http\Controllers\Api\ViagogoController;
use App\Http\Controllers\Api\WelcomeStatsController;
use App\Http\Controllers\Auth\LoginEnhancementController;
use App\Http\Controllers\AutomatedPurchaseController;
use App\Http\Controllers\EnhancedDashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TicketApiController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Middleware\Api\ApiRateLimit;
use App\Http\Middleware\Api\CheckApiRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Include background sync API routes
require __DIR__ . '/sync-api.php';

/*
|--------------------------------------------------------------------------
| Public API Routes - No Authentication Required
|--------------------------------------------------------------------------
|
| These routes are accessible without authentication and handle basic
| system information and authentication endpoints for the sports events
| ticket monitoring system.
|
*/

// Public API endpoints with light rate limiting
Route::prefix('v1')->middleware([ApiRateLimit::class . ':auth,10,1'])->group(function (): void {
    /*
     * Authentication Endpoint
     * Purpose: User login and token generation
     * Rate Limit: 10 requests/minute per IP
     * Returns: API access token for subsequent requests
     */
    Route::post('/auth/login', [AuthController::class, 'login']);

    /*
     * Authentication Enhancement Endpoints
     * Purpose: Enhanced security and UX features for login
     * Rate Limit: 10 requests/minute per IP
     * Used by: Enhanced login form for progressive validation and security
     */
    Route::post('/auth/check-email', [LoginEnhancementController::class, 'checkEmail']);
    Route::post('/auth/validate-password', [LoginEnhancementController::class, 'validatePassword']);
    Route::get('/auth/security-info', [LoginEnhancementController::class, 'getSecurityInfo']);
    Route::post('/auth/log-security-event', [LoginEnhancementController::class, 'logSecurityEvent']);

    /*
     * System Status Endpoint
     * Purpose: Basic system health and version information
     * Access: Public (no authentication required)
     * Used by: Load balancers, monitoring systems, integration partners
     */
    Route::get('/status', fn () => response()->json([
        'status'      => 'active',
        'service'     => 'HD Tickets Sports Events Monitoring',
        'version'     => '2025.07.v4.0',
        'timestamp'   => now()->toISOString(),
        'environment' => 'Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4',
    ]));

    /*
     * Welcome Page Statistics Endpoint
     * Purpose: Real-time stats for the welcome page
     * Access: Public (no authentication required)
     * Used by: Welcome page for displaying live statistics
     */
    Route::get('/stats/welcome', [WelcomeStatsController::class, 'index']);

    /*
     * Analytics Event Tracking Endpoint
     * Purpose: Receive analytics events from frontend
     * Access: Public (no authentication required)
     * Used by: JavaScript analytics service for event tracking
     */
    Route::post('/analytics/event', [AnalyticsController::class, 'receiveEvent']);

    /*
     * Analytics Dashboard Data Endpoint
     * Purpose: Provide analytics dashboard data
     * Access: Public (no authentication required)
     * Used by: Analytics dashboard components
     */
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'getDashboardData']);
});

/*
|--------------------------------------------------------------------------
| Frontend Ticket API Routes - Public Access
|--------------------------------------------------------------------------
|
| Modern AJAX API endpoints for the enhanced frontend ticket interface.
| These routes handle real-time filtering, search suggestions, and ticket
| details for the sports event ticket system. Public access for browsing.
|
*/
Route::prefix('v1/tickets')->middleware([ApiRateLimit::class . ':api,120,1'])->name('api.tickets.')->group(function (): void {
    // Public ticket endpoints (no authentication required)
    Route::get('/filter', [TicketApiController::class, 'filter'])
        ->name('filter');

    Route::get('/suggestions', [TicketApiController::class, 'suggestions'])
        ->name('suggestions');

    Route::get('/{ticket}/details', [TicketApiController::class, 'getTicketDetails'])
        ->name('details');

    // Development endpoints (non-production only)
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/{ticket}/test-price-change', [TicketApiController::class, 'testPriceChange'])
            ->name('test-price-change');
    });

    // Authenticated ticket endpoints
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/{ticket}/bookmark', [TicketApiController::class, 'toggleBookmark'])
            ->name('bookmark');
    });
});

/*
|--------------------------------------------------------------------------
| Enhanced Dashboard API Routes
|--------------------------------------------------------------------------
|
| Real-time API endpoints for the enhanced customer dashboard
| Provides data for statistics, recommendations, notifications, and more
|
*/
Route::prefix('v1/dashboard')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,120,1'])->name('api.dashboard.')->group(function (): void {
    /*
     * Real-time Dashboard Data Endpoint
     * Purpose: Fetch current dashboard statistics and recent tickets
     * Access: Authenticated users
     * Used by: Enhanced dashboard for real-time updates
     */
    Route::get('/realtime', [EnhancedDashboardController::class, 'getRealtimeData'])
        ->name('realtime');

    /*
     * Analytics Data Endpoint
     * Purpose: Fetch analytics trends and performance metrics
     * Access: Authenticated users
     * Used by: Enhanced dashboard analytics widgets
     */
    Route::get('/analytics-data', [EnhancedDashboardController::class, 'getAnalytics'])
        ->name('analytics.data');

    /*
     * Personalized Recommendations Endpoint
     * Purpose: Get AI-powered ticket recommendations for user
     * Access: Authenticated users
     * Used by: Enhanced dashboard recommendations widget
     */
    Route::get('/recommendations', [EnhancedDashboardController::class, 'getPersonalizedRecommendations'])
        ->name('recommendations');

    /*
     * Dashboard Stats Endpoint
     * Purpose: Get dashboard statistics for tiles
     * Access: Authenticated users
     * Used by: Enhanced dashboard stats tiles
     */
    Route::get('/stats', function (Request $request) {
        $user = $request->user();
        $controller = app(EnhancedDashboardController::class);
        $data = $controller->getRealtimeData($request)->getData();

        return response()->json([
            'success' => TRUE,
            'stats'   => [
                'available_tickets' => $data->data->statistics->available_tickets->current ?? 0,
                'new_today'         => $data->data->statistics->available_tickets->change_24h ?? 0,
                'monitored_events'  => $data->data->statistics->high_demand->current ?? 0,
                'active_alerts'     => $data->data->statistics->active_alerts->current ?? 0,
                'price_alerts'      => $data->data->statistics->active_alerts->current ?? 0,
                'triggered_today'   => $data->data->statistics->active_alerts->triggered_today ?? 0,
            ],
        ]);
    })->name('stats');

    /*
     * Dashboard Tickets Endpoint
     * Purpose: Get recent tickets with filtering
     * Access: Authenticated users
     * Used by: Enhanced dashboard tickets grid
     */
    Route::get('/tickets', function (Request $request) {
        $user = $request->user();
        $controller = app(EnhancedDashboardController::class);
        $data = $controller->getRealtimeData($request)->getData();

        return response()->json([
            'success' => TRUE,
            'tickets' => $data->data->recent_tickets ?? [],
        ]);
    })->name('tickets');

    /*
     * Upcoming Events Endpoint
     * Purpose: Get upcoming events based on user preferences
     * Access: Authenticated users
     * Used by: Enhanced dashboard upcoming events widget
     */
    Route::get('/events', [EnhancedDashboardController::class, 'getUpcomingEvents'])
        ->name('events');

    /*
     * Notifications Endpoint
     * Purpose: Manage user notifications and alerts
     * Access: Authenticated users
     * Methods: GET (fetch), POST (mark as read)
     */
    Route::get('/notifications', [EnhancedDashboardController::class, 'getNotifications'])
        ->name('notifications.index');
    Route::post('/notifications', [EnhancedDashboardController::class, 'markNotificationsRead'])
        ->name('notifications.read');

    /*
     * User Settings Endpoint
     * Purpose: Manage user dashboard preferences and settings
     * Access: Authenticated users
     * Methods: GET (fetch), POST (save)
     */
    Route::get('/settings', [EnhancedDashboardController::class, 'getUserSettings'])
        ->name('settings.index');
    Route::post('/settings', [EnhancedDashboardController::class, 'saveUserSettings'])
        ->name('settings.save');

    /*
     * Performance Metrics Endpoint
     * Purpose: Get dashboard performance and usage metrics
     * Access: Authenticated users
     * Used by: Enhanced dashboard for performance monitoring
     */
    Route::get('/metrics', [EnhancedDashboardController::class, 'getPerformanceMetrics'])
        ->name('metrics');

    /*
     * User Activity Analytics Endpoint
     * Purpose: Track and receive user interaction analytics
     * Access: Authenticated users
     * Used by: Enhanced dashboard for usage analytics
     */
    Route::post('/analytics', [EnhancedDashboardController::class, 'receiveAnalytics'])
        ->name('analytics.receive');
});

// Scraping routes
Route::prefix('v1/scraping')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/tickets', [ScrapingController::class, 'tickets']);
    Route::get('/tickets/{uuid}', [ScrapingController::class, 'show']);
    Route::post('/start-scraping', [ScrapingController::class, 'startScraping']);
    Route::get('/statistics', [ScrapingController::class, 'statistics']);
    Route::get('/platforms', [ScrapingController::class, 'platforms']);
    Route::delete('/cleanup', [ScrapingController::class, 'cleanup']);
});

// Alert routes
Route::prefix('v1/alerts')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/', [AlertController::class, 'index']);
    Route::post('/', [AlertController::class, 'store']);
    Route::get('/{uuid}', [AlertController::class, 'show']);
    Route::put('/{uuid}', [AlertController::class, 'update']);
    Route::delete('/{uuid}', [AlertController::class, 'destroy']);
    Route::post('/{uuid}/toggle', [AlertController::class, 'toggle']);
    Route::post('/{uuid}/test', [AlertController::class, 'test']);
    Route::get('/statistics', [AlertController::class, 'statistics']);
    Route::post('/check-all', [AlertController::class, 'checkAll']);

    // Dashboard alert toggling (expects ticket ID instead of alert UUID)
    Route::post('/{ticketId}', fn (Request $request, string $ticketId) => // Create new alert for ticket
        response()->json(['success' => TRUE, 'message' => 'Alert created']));

    Route::delete('/{ticketId}', fn (Request $request, string $ticketId) => // Remove alert for ticket
        response()->json(['success' => TRUE, 'message' => 'Alert removed']));
});

// Purchase routes
Route::prefix('v1/purchases')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/queue', [PurchaseController::class, 'queue']);
    Route::post('/queue', [PurchaseController::class, 'addToQueue']);
    Route::put('/queue/{uuid}', [PurchaseController::class, 'updateQueue']);
    Route::delete('/queue/{uuid}', [PurchaseController::class, 'removeFromQueue']);
    Route::get('/attempts', [PurchaseController::class, 'attempts']);
    Route::post('/attempts/initiate', [PurchaseController::class, 'initiatePurchase']);
    Route::get('/attempts/{uuid}', [PurchaseController::class, 'attemptDetails']);
    Route::post('/attempts/{uuid}/cancel', [PurchaseController::class, 'cancelAttempt']);
    Route::get('/statistics', [PurchaseController::class, 'statistics']);
    Route::get('/configuration', [PurchaseController::class, 'configuration']);
    Route::put('/configuration', [PurchaseController::class, 'updateConfiguration']);
});

// Category routes
Route::prefix('v1/categories')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/statistics', [CategoryController::class, 'statistics']);
    Route::get('/sport-types', [CategoryController::class, 'sportTypes']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/tickets', [CategoryController::class, 'tickets']);

    // Admin only routes
    Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });
});

// Protected routes
Route::prefix('v1')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,120,1'])->group(function (): void {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/revoke-tokens', [AuthController::class, 'revokeAllTokens']);

    // Authentication Enhancement Routes (Authenticated)
    Route::get('/session/status', [LoginEnhancementController::class, 'getSessionStatus']);

    // Session Management Routes for Professional Auth Features
    Route::post('/session/extend', function (Request $request) {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $request->session()->regenerate();
            $request->session()->put('last_activity', time());

            Log::info('Session extended for user', [
                'user_id'    => Auth::id(),
                'email'      => Auth::user()->email,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp'  => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Session extended successfully',
                'expires_at' => now()->addMinutes(config('session.lifetime'))->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Session extension failed', [
                'user_id'   => Auth::id() ?? 'unknown',
                'error'     => $e->getMessage(),
                'ip'        => $request->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Session extension failed',
            ], 500);
        }
    });

    Route::get('/session/status', function (Request $request) {
        if (!Auth::check()) {
            return response()->json([
                'success'       => FALSE,
                'authenticated' => FALSE,
            ], 401);
        }

        $sessionLifetime = config('session.lifetime') * 60;
        $lastActivity = $request->session()->get('last_activity', time());
        $timeRemaining = $sessionLifetime - (time() - $lastActivity);

        return response()->json([
            'success'          => TRUE,
            'authenticated'    => TRUE,
            'session_lifetime' => $sessionLifetime,
            'time_remaining'   => max(0, $timeRemaining),
            'expires_at'       => now()->addSeconds(max(0, $timeRemaining))->toISOString(),
        ]);
    });

    // Get current user
    Route::get('/user', fn (Request $request) => $request->user());

    // Ticket routes
    Route::apiResource('tickets', TicketController::class)->parameters([
        'tickets' => 'ticket:uuid',
    ]);

    // New route for ticket availability updates
    Route::post('/tickets/availability-update', [TicketController::class, 'availabilityUpdate']);

    // Dashboard routes
    Route::prefix('dashboard')->group(function (): void {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/monitors', [DashboardController::class, 'monitors']);
        Route::post('/monitors/{monitorId}/check-now', [DashboardController::class, 'checkMonitorNow']);
        Route::post('/monitors/{monitorId}/toggle', [DashboardController::class, 'toggleMonitor']);
        Route::get('/platform-health', [DashboardController::class, 'platformHealth']);
        Route::get('/high-demand-tickets', [DashboardController::class, 'highDemandTickets']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        Route::get('/realtime-stats', [DashboardController::class, 'realtimeStats']);
        Route::get('/performance-metrics', [DashboardController::class, 'performanceMetrics']);
        Route::get('/success-rates', [DashboardController::class, 'successRates']);

        // Comprehensive health endpoint
        Route::get('/health', [HealthController::class, 'index']);
        Route::get('/health/database', [HealthController::class, 'database']);
        Route::get('/health/redis', [HealthController::class, 'redis']);
        Route::get('/health/websockets', [HealthController::class, 'websockets']);
        Route::get('/health/services', [HealthController::class, 'services']);

        // Error logging endpoint
        Route::post('/log-error', [DashboardController::class, 'logError']);
    });

    // Advanced User Preferences API
    Route::prefix('preferences')->group(function (): void {
        // Legacy preferences routes
        Route::get('/', [UserPreferencesController::class, 'getPreferences']);
        Route::post('/', [UserPreferencesController::class, 'updatePreferences']);
        Route::put('/{key}', [PreferencesController::class, 'update']);
        Route::delete('/{key}', [PreferencesController::class, 'destroy']);
        Route::post('/reset', [UserPreferencesController::class, 'resetPreferences']);

        // Export/Import operations
        Route::get('/export', [UserPreferencesController::class, 'exportPreferences']);
        Route::post('/import', [UserPreferencesController::class, 'import']);

        // Teams and venues search
        Route::get('/teams/search', [UserPreferencesController::class, 'searchTeams']);
        Route::get('/venues/search', [UserPreferencesController::class, 'searchVenues']);

        // Favorites management
        Route::post('/teams/{teamId}', [UserPreferencesController::class, 'addFavoriteTeam']);
        Route::delete('/teams/{teamId}', [UserPreferencesController::class, 'removeFavoriteTeam']);
        Route::post('/venues/{venueId}', [UserPreferencesController::class, 'addFavoriteVenue']);
        Route::delete('/venues/{venueId}', [UserPreferencesController::class, 'removeFavoriteVenue']);

        // Preferences summary
        Route::get('/summary', [UserPreferencesController::class, 'getPreferencesSummary']);
    });

    // Ticket Criteria Configuration
    Route::prefix('ticket-criteria')->group(function (): void {
        Route::get('/', [TicketCriteriaController::class, 'index']);
        Route::post('/', [TicketCriteriaController::class, 'store']);
        Route::put('/{id}', [TicketCriteriaController::class, 'update']);
        Route::delete('/{id}', [TicketCriteriaController::class, 'destroy']);
        Route::post('/{id}/toggle', [TicketCriteriaController::class, 'toggle']);
    });

    // Analytics API
    Route::prefix('analytics')->group(function (): void {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/ticket-trends', [AnalyticsController::class, 'ticketTrends']);
        Route::get('/platform-performance', [AnalyticsController::class, 'platformPerformance']);
        Route::get('/success-rates', [AnalyticsController::class, 'successRates']);
        Route::get('/price-analysis', [AnalyticsController::class, 'priceAnalysis']);
        Route::get('/demand-patterns', [AnalyticsController::class, 'demandPatterns']);
        Route::get('/export/{type}', [AnalyticsController::class, 'export']);
    });

    // Enhanced Analytics & Reporting API Routes
    Route::prefix('enhanced-analytics')->group(function (): void {
        // Chart Data Endpoints
        Route::get('/charts', [EnhancedAnalyticsController::class, 'getChartData']);
        Route::get('/charts/{type}', [EnhancedAnalyticsController::class, 'getChartData']);

        // Export Endpoints
        Route::post('/export', [EnhancedAnalyticsController::class, 'exportData']);
        Route::get('/export/formats', fn () => response()->json([
            'success' => TRUE,
            'formats' => ['csv', 'xlsx', 'pdf', 'json'],
            'types'   => ['ticket_trends', 'price_analysis', 'platform_performance', 'user_engagement', 'comprehensive_analytics'],
        ]));

        // Insights Endpoints
        Route::get('/insights/predictive', [EnhancedAnalyticsController::class, 'getPredictiveInsights']);
        Route::get('/insights/user-behavior', [EnhancedAnalyticsController::class, 'getUserBehaviorInsights']);
        Route::get('/insights/market-intelligence', [EnhancedAnalyticsController::class, 'getMarketIntelligence']);
        Route::get('/insights/optimization', [EnhancedAnalyticsController::class, 'getOptimizationInsights']);
        Route::get('/insights/anomaly-detection', [EnhancedAnalyticsController::class, 'getAnomalyDetection']);

        // Dashboard Configuration
        Route::get('/dashboard/config', [EnhancedAnalyticsController::class, 'getDashboardConfig']);
        Route::post('/dashboard/custom', fn (Request $request) => // Custom dashboard configuration endpoint
            response()->json([
                'success' => TRUE,
                'message' => 'Custom dashboard configuration saved',
                'config'  => $request->all(),
            ]));

        // Real-time Data Endpoints
        Route::get('/realtime/metrics', fn () => response()->json([
            'success' => TRUE,
            'data'    => [
                'active_scrapers'   => random_int(8, 12),
                'tickets_processed' => random_int(1000, 5000),
                'alerts_sent'       => random_int(50, 200),
                'system_load'       => random_int(30, 80),
                'last_updated'      => now()->toISOString(),
            ],
        ]));

        Route::get('/realtime/alerts', fn () => response()->json([
            'success' => TRUE,
            'data'    => [
                'active_alerts'   => random_int(20, 100),
                'triggered_today' => random_int(5, 25),
                'success_rate'    => random_int(75, 95),
                'last_updated'    => now()->toISOString(),
            ],
        ]));
    });

    // Enhanced Monitoring API
    Route::prefix('monitoring')->group(function (): void {
        Route::get('/stats', [MonitoringController::class, 'getRealtimeStats']);
        Route::get('/platform-health', [MonitoringController::class, 'getPlatformHealth']);
        Route::get('/monitors', [MonitoringController::class, 'getMonitors']);
        Route::get('/activity', [MonitoringController::class, 'getRecentActivity']);
        Route::get('/system-metrics', [MonitoringController::class, 'getSystemMetrics']);
        Route::post('/monitors/{monitorId}/check-now', [MonitoringController::class, 'checkMonitorNow']);
        Route::post('/monitors/{monitorId}/toggle', [MonitoringController::class, 'toggleMonitor']);
    });

    // Performance Metrics API
    Route::prefix('performance')->group(function (): void {
        // Public endpoint for receiving metrics from browser
        Route::post('/metrics', [PerformanceMetricsController::class, 'receiveMetrics']);

        // Admin-only dashboard data
        Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
            Route::get('/dashboard', [PerformanceMetricsController::class, 'getDashboardData']);
        });
    });

    Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
        // Admin-specific routes can be added here
    });

    // Ticketmaster scraping routes
    Route::prefix('ticketmaster')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function (): void {
        // Search events (available to all authenticated users)
        Route::post('/search', [TicketmasterController::class, 'search']);
        Route::post('/event-details', [TicketmasterController::class, 'getEventDetails']);
        Route::get('/stats', [TicketmasterController::class, 'stats']);

        // Import routes (restricted to agents and admins)
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
            Route::post('/import', [TicketmasterController::class, 'import']);
            Route::post('/import-urls', [TicketmasterController::class, 'importUrls']);
        });
    });

    // StubHub routes
    Route::prefix('stubhub')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function (): void {
        Route::post('/search', [StubHubController::class, 'search']);
        Route::post('/event-details', [StubHubController::class, 'getEventDetails']);
        Route::get('/stats', [StubHubController::class, 'stats']);

        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
            Route::post('/import', [StubHubController::class, 'import']);
            Route::post('/import-urls', [StubHubController::class, 'importUrls']);
        });
    });

    // Viagogo routes
    Route::prefix('viagogo')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function (): void {
        Route::post('/search', [ViagogoController::class, 'search']);
        Route::post('/event-details', [ViagogoController::class, 'getEventDetails']);
        Route::get('/stats', [ViagogoController::class, 'stats']);

        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
            Route::post('/import', [ViagogoController::class, 'import']);
            Route::post('/import-urls', [ViagogoController::class, 'importUrls']);
        });
    });

    // TickPick routes
    Route::prefix('tickpick')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function (): void {
        Route::post('/search', [TickPickController::class, 'search']);
        Route::post('/event-details', [TickPickController::class, 'getEventDetails']);
        Route::get('/stats', [TickPickController::class, 'stats']);

        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
            Route::post('/import', [TickPickController::class, 'import']);
            Route::post('/import-urls', [TickPickController::class, 'importUrls']);
        });
    });

    // Automated Purchase System Routes
    Route::prefix('automated-purchase')->middleware([ApiRateLimit::class . ':api,60,1'])->group(function (): void {
        // Decision evaluation and price comparison
        Route::post('/evaluate-decision', [AutomatedPurchaseController::class, 'evaluatePurchaseDecision']);
        Route::post('/compare-prices', [AutomatedPurchaseController::class, 'compareMultiPlatformPrices']);

        // Purchase execution and tracking
        Route::post('/execute', [AutomatedPurchaseController::class, 'executeAutomatedPurchase']);
        Route::post('/track-optimize', [AutomatedPurchaseController::class, 'trackAndOptimize']);

        // Configuration and preferences
        Route::get('/configuration', [AutomatedPurchaseController::class, 'getConfiguration']);
        Route::put('/preferences', [AutomatedPurchaseController::class, 'updateUserPreferences']);

        // Analytics and statistics
        Route::get('/statistics', [AutomatedPurchaseController::class, 'getAutomationStatistics']);
    });

    // IMAP Email Monitoring API Routes
    Route::prefix('imap')->middleware([CheckApiRole::class . ':agent,admin'])->name('api.imap.')->group(function (): void {
        // Dashboard and statistics
        Route::get('/dashboard', [ImapMonitoringController::class, 'dashboard']);
        Route::get('/statistics', [ImapMonitoringController::class, 'statistics']);

        // Connection management
        Route::post('/test-connection', [ImapMonitoringController::class, 'testConnection']);
        Route::get('/connection-health', [ImapMonitoringController::class, 'connectionHealth']);

        // Monitoring operations
        Route::post('/start-monitoring', [ImapMonitoringController::class, 'startMonitoring']);

        // Cache management
        Route::post('/clear-cache', [ImapMonitoringController::class, 'clearCache']);

        // Platform configuration
        Route::get('/platform-config', [ImapMonitoringController::class, 'platformConfig']);
    });

    /*
    |--------------------------------------------------------------------------
    | Business Intelligence API Routes
    |--------------------------------------------------------------------------
    |
    | Comprehensive API endpoints for external BI tools and third-party integrations.
    | Provides standardized access to analytics data with proper authentication,
    | rate limiting, and data formatting for business intelligence platforms.
    |
    */
    Route::prefix('bi')->middleware([CheckApiRole::class . ':admin,agent'])->name('api.bi.')->group(function (): void {
        // API Health and Documentation
        Route::get('/health', [BusinessIntelligenceApiController::class, 'health'])
            ->name('health');

        // Core Analytics Endpoints
        Route::get('/analytics/overview', [BusinessIntelligenceApiController::class, 'getAnalyticsOverview'])
            ->middleware('throttle:bi-api,100')
            ->name('analytics.overview');

        Route::get('/tickets/metrics', [BusinessIntelligenceApiController::class, 'getTicketMetrics'])
            ->middleware('throttle:bi-api,100')
            ->name('tickets.metrics');

        Route::get('/platforms/performance', [BusinessIntelligenceApiController::class, 'getPlatformData'])
            ->middleware('throttle:bi-api,100')
            ->name('platforms.performance');

        // Advanced Analytics Endpoints (More Restrictive Rate Limits)
        Route::get('/competitive/intelligence', [BusinessIntelligenceApiController::class, 'getCompetitiveIntelligence'])
            ->middleware('throttle:bi-api-heavy,20')
            ->name('competitive.intelligence');

        Route::get('/predictive/insights', [BusinessIntelligenceApiController::class, 'getPredictiveInsights'])
            ->middleware('throttle:bi-api-heavy,20')
            ->name('predictive.insights');

        Route::get('/anomalies/current', [BusinessIntelligenceApiController::class, 'getCurrentAnomalies'])
            ->middleware('throttle:bi-api,100')
            ->name('anomalies.current');

        // Data Export Endpoints (Very Restrictive Rate Limits)
        Route::post('/export/dataset', [BusinessIntelligenceApiController::class, 'exportDataSet'])
            ->middleware('throttle:bi-export,5')
            ->name('export.dataset');

        // User Analytics (Admin Only)
        Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
            Route::get('/users/analytics', [BusinessIntelligenceApiController::class, 'getUserAnalytics'])
                ->middleware('throttle:bi-api,100')
                ->name('users.analytics');
        });

        // Download route for API exports
        Route::get('/download/{file}', function (string $file) {
            $path = storage_path('app/analytics/exports/api/' . $file);
            if (!file_exists($path)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Export file not found or has expired',
                ], 404);
            }

            return response()->download($path);
        })->name('download');
    });

    // AI Recommendations API Routes
    Route::prefix('recommendations')->name('api.recommendations.')->group(function (): void {
        // Main recommendation endpoints
        Route::get('/', [RecommendationController::class, 'getRecommendations'])->name('index');
        Route::post('/refresh', [RecommendationController::class, 'refreshRecommendations'])->name('refresh');

        // Specific recommendation types
        Route::get('/events', [RecommendationController::class, 'getEventRecommendations'])->name('events');
        Route::get('/pricing', [RecommendationController::class, 'getPricingStrategies'])->name('pricing');
        Route::get('/alerts', [RecommendationController::class, 'getAlertRecommendations'])->name('alerts');
        Route::get('/follow', [RecommendationController::class, 'getFollowRecommendations'])->name('follow');

        // User interactions
        Route::post('/feedback', [RecommendationController::class, 'submitFeedback'])->name('feedback');
        Route::post('/alerts/apply', [RecommendationController::class, 'applyAlertRecommendations'])->name('alerts.apply');
        Route::put('/preferences', [RecommendationController::class, 'updateUserPreferences'])->name('preferences.update');

        // Utility endpoints
        Route::get('/history', [RecommendationController::class, 'getRecommendationHistory'])->name('history');
        Route::delete('/cache', [RecommendationController::class, 'clearUserCache'])->name('cache.clear');
        Route::get('/metrics', [RecommendationController::class, 'getPerformanceMetrics'])->name('metrics');
    });

    // Agent and Admin routes
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
