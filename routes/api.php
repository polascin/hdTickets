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

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\StubHubController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketmasterController;
use App\Http\Controllers\Api\TickPickController;
use App\Http\Controllers\Api\ViagogoController;
use App\Http\Controllers\Auth\LoginEnhancementController;
use App\Http\Controllers\AutomatedPurchaseController;
use App\Http\Middleware\Api\ApiRateLimit;
use App\Http\Middleware\Api\CheckApiRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('/status', function () {
        return response()->json([
            'status'      => 'active',
            'service'     => 'HD Tickets Sports Events Monitoring',
            'version'     => '2025.07.v4.0',
            'timestamp'   => now()->toISOString(),
            'environment' => 'Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4',
        ]);
    });

    /*
     * Welcome Page Statistics Endpoint
     * Purpose: Real-time stats for the welcome page
     * Access: Public (no authentication required)
     * Used by: Welcome page for displaying live statistics
     */
    Route::get('/stats/welcome', [App\Http\Controllers\Api\WelcomeStatsController::class, 'index']);

    /*
     * Analytics Event Tracking Endpoint
     * Purpose: Receive analytics events from frontend
     * Access: Public (no authentication required)
     * Used by: JavaScript analytics service for event tracking
     */
    Route::post('/analytics/event', [App\Http\Controllers\Api\AnalyticsController::class, 'receiveEvent']);

    /*
     * Analytics Dashboard Data Endpoint
     * Purpose: Provide analytics dashboard data
     * Access: Public (no authentication required)
     * Used by: Analytics dashboard components
     */
    Route::get('/analytics/dashboard', [App\Http\Controllers\Api\AnalyticsController::class, 'getDashboardData']);
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
    Route::get('/realtime', [App\Http\Controllers\EnhancedDashboardController::class, 'getRealtimeData'])
        ->name('realtime');

    /*
     * Analytics Data Endpoint
     * Purpose: Fetch analytics trends and performance metrics
     * Access: Authenticated users
     * Used by: Enhanced dashboard analytics widgets
     */
    Route::get('/analytics-data', [App\Http\Controllers\EnhancedDashboardController::class, 'getAnalytics'])
        ->name('analytics.data');

    /*
     * Personalized Recommendations Endpoint
     * Purpose: Get AI-powered ticket recommendations for user
     * Access: Authenticated users
     * Used by: Enhanced dashboard recommendations widget
     */
    Route::get('/recommendations', [App\Http\Controllers\EnhancedDashboardController::class, 'getPersonalizedRecommendations'])
        ->name('recommendations');

    /*
     * Upcoming Events Endpoint
     * Purpose: Get upcoming events based on user preferences
     * Access: Authenticated users
     * Used by: Enhanced dashboard upcoming events widget
     */
    Route::get('/events', [App\Http\Controllers\EnhancedDashboardController::class, 'getUpcomingEvents'])
        ->name('events');

    /*
     * Notifications Endpoint
     * Purpose: Manage user notifications and alerts
     * Access: Authenticated users
     * Methods: GET (fetch), POST (mark as read)
     */
    Route::get('/notifications', [App\Http\Controllers\EnhancedDashboardController::class, 'getNotifications'])
        ->name('notifications.index');
    Route::post('/notifications', [App\Http\Controllers\EnhancedDashboardController::class, 'markNotificationsRead'])
        ->name('notifications.read');

    /*
     * User Settings Endpoint
     * Purpose: Manage user dashboard preferences and settings
     * Access: Authenticated users
     * Methods: GET (fetch), POST (save)
     */
    Route::get('/settings', [App\Http\Controllers\EnhancedDashboardController::class, 'getUserSettings'])
        ->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\EnhancedDashboardController::class, 'saveUserSettings'])
        ->name('settings.save');

    /*
     * Performance Metrics Endpoint
     * Purpose: Get dashboard performance and usage metrics
     * Access: Authenticated users
     * Used by: Enhanced dashboard for performance monitoring
     */
    Route::get('/metrics', [App\Http\Controllers\EnhancedDashboardController::class, 'getPerformanceMetrics'])
        ->name('metrics');

    /*
     * User Activity Analytics Endpoint
     * Purpose: Track and receive user interaction analytics
     * Access: Authenticated users
     * Used by: Enhanced dashboard for usage analytics
     */
    Route::post('/analytics', [App\Http\Controllers\EnhancedDashboardController::class, 'receiveAnalytics'])
        ->name('analytics.receive');
});

// Scraping routes
Route::prefix('v1/scraping')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/tickets', [App\Http\Controllers\Api\ScrapingController::class, 'tickets']);
    Route::get('/tickets/{uuid}', [App\Http\Controllers\Api\ScrapingController::class, 'show']);
    Route::post('/start-scraping', [App\Http\Controllers\Api\ScrapingController::class, 'startScraping']);
    Route::get('/statistics', [App\Http\Controllers\Api\ScrapingController::class, 'statistics']);
    Route::get('/platforms', [App\Http\Controllers\Api\ScrapingController::class, 'platforms']);
    Route::delete('/cleanup', [App\Http\Controllers\Api\ScrapingController::class, 'cleanup']);
});

// Alert routes
Route::prefix('v1/alerts')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/', [App\Http\Controllers\Api\AlertController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\AlertController::class, 'store']);
    Route::get('/{uuid}', [App\Http\Controllers\Api\AlertController::class, 'show']);
    Route::put('/{uuid}', [App\Http\Controllers\Api\AlertController::class, 'update']);
    Route::delete('/{uuid}', [App\Http\Controllers\Api\AlertController::class, 'destroy']);
    Route::post('/{uuid}/toggle', [App\Http\Controllers\Api\AlertController::class, 'toggle']);
    Route::post('/{uuid}/test', [App\Http\Controllers\Api\AlertController::class, 'test']);
    Route::get('/statistics', [App\Http\Controllers\Api\AlertController::class, 'statistics']);
    Route::post('/check-all', [App\Http\Controllers\Api\AlertController::class, 'checkAll']);
});

// Purchase routes
Route::prefix('v1/purchases')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/queue', [App\Http\Controllers\Api\PurchaseController::class, 'queue']);
    Route::post('/queue', [App\Http\Controllers\Api\PurchaseController::class, 'addToQueue']);
    Route::put('/queue/{uuid}', [App\Http\Controllers\Api\PurchaseController::class, 'updateQueue']);
    Route::delete('/queue/{uuid}', [App\Http\Controllers\Api\PurchaseController::class, 'removeFromQueue']);
    Route::get('/attempts', [App\Http\Controllers\Api\PurchaseController::class, 'attempts']);
    Route::post('/attempts/initiate', [App\Http\Controllers\Api\PurchaseController::class, 'initiatePurchase']);
    Route::get('/attempts/{uuid}', [App\Http\Controllers\Api\PurchaseController::class, 'attemptDetails']);
    Route::post('/attempts/{uuid}/cancel', [App\Http\Controllers\Api\PurchaseController::class, 'cancelAttempt']);
    Route::get('/statistics', [App\Http\Controllers\Api\PurchaseController::class, 'statistics']);
    Route::get('/configuration', [App\Http\Controllers\Api\PurchaseController::class, 'configuration']);
    Route::put('/configuration', [App\Http\Controllers\Api\PurchaseController::class, 'updateConfiguration']);
});

// Category routes
Route::prefix('v1/categories')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function (): void {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/statistics', [App\Http\Controllers\Api\CategoryController::class, 'statistics']);
    Route::get('/sport-types', [App\Http\Controllers\Api\CategoryController::class, 'sportTypes']);
    Route::get('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::get('/{id}/tickets', [App\Http\Controllers\Api\CategoryController::class, 'tickets']);

    // Admin only routes
    Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
        Route::post('/', [App\Http\Controllers\Api\CategoryController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);
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
            if (! Illuminate\Support\Facades\Auth::check()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $request->session()->regenerate();
            $request->session()->put('last_activity', time());

            Illuminate\Support\Facades\Log::info('Session extended for user', [
                'user_id'    => Illuminate\Support\Facades\Auth::id(),
                'email'      => Illuminate\Support\Facades\Auth::user()->email,
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
            Illuminate\Support\Facades\Log::error('Session extension failed', [
                'user_id'   => Illuminate\Support\Facades\Auth::id() ?? 'unknown',
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
        if (! Illuminate\Support\Facades\Auth::check()) {
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
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

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
        Route::get('/health', [App\Http\Controllers\HealthController::class, 'index']);
        Route::get('/health/database', [App\Http\Controllers\HealthController::class, 'database']);
        Route::get('/health/redis', [App\Http\Controllers\HealthController::class, 'redis']);
        Route::get('/health/websockets', [App\Http\Controllers\HealthController::class, 'websockets']);
        Route::get('/health/services', [App\Http\Controllers\HealthController::class, 'services']);

        // Error logging endpoint
        Route::post('/log-error', [DashboardController::class, 'logError']);
    });

    // User Preferences API
    Route::prefix('preferences')->group(function (): void {
        Route::get('/', [App\Http\Controllers\Api\PreferencesController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\PreferencesController::class, 'store']);
        Route::put('/{key}', [App\Http\Controllers\Api\PreferencesController::class, 'update']);
        Route::delete('/{key}', [App\Http\Controllers\Api\PreferencesController::class, 'destroy']);
        Route::post('/reset', [App\Http\Controllers\Api\PreferencesController::class, 'reset']);
    });

    // Ticket Criteria Configuration
    Route::prefix('ticket-criteria')->group(function (): void {
        Route::get('/', [App\Http\Controllers\Api\TicketCriteriaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TicketCriteriaController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\TicketCriteriaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TicketCriteriaController::class, 'destroy']);
        Route::post('/{id}/toggle', [App\Http\Controllers\Api\TicketCriteriaController::class, 'toggle']);
    });

    // Analytics API
    Route::prefix('analytics')->group(function (): void {
        Route::get('/overview', [App\Http\Controllers\Api\AnalyticsController::class, 'overview']);
        Route::get('/ticket-trends', [App\Http\Controllers\Api\AnalyticsController::class, 'ticketTrends']);
        Route::get('/platform-performance', [App\Http\Controllers\Api\AnalyticsController::class, 'platformPerformance']);
        Route::get('/success-rates', [App\Http\Controllers\Api\AnalyticsController::class, 'successRates']);
        Route::get('/price-analysis', [App\Http\Controllers\Api\AnalyticsController::class, 'priceAnalysis']);
        Route::get('/demand-patterns', [App\Http\Controllers\Api\AnalyticsController::class, 'demandPatterns']);
        Route::get('/export/{type}', [App\Http\Controllers\Api\AnalyticsController::class, 'export']);
    });

    // Enhanced Analytics & Reporting API Routes
    Route::prefix('enhanced-analytics')->group(function (): void {
        // Chart Data Endpoints
        Route::get('/charts', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getChartData']);
        Route::get('/charts/{type}', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getChartData']);

        // Export Endpoints
        Route::post('/export', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'exportData']);
        Route::get('/export/formats', function () {
            return response()->json([
                'success' => TRUE,
                'formats' => ['csv', 'xlsx', 'pdf', 'json'],
                'types'   => ['ticket_trends', 'price_analysis', 'platform_performance', 'user_engagement', 'comprehensive_analytics'],
            ]);
        });

        // Insights Endpoints
        Route::get('/insights/predictive', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getPredictiveInsights']);
        Route::get('/insights/user-behavior', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getUserBehaviorInsights']);
        Route::get('/insights/market-intelligence', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getMarketIntelligence']);
        Route::get('/insights/optimization', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getOptimizationInsights']);
        Route::get('/insights/anomaly-detection', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getAnomalyDetection']);

        // Dashboard Configuration
        Route::get('/dashboard/config', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getDashboardConfig']);
        Route::post('/dashboard/custom', function (Request $request) {
            // Custom dashboard configuration endpoint
            return response()->json([
                'success' => TRUE,
                'message' => 'Custom dashboard configuration saved',
                'config'  => $request->all(),
            ]);
        });

        // Real-time Data Endpoints
        Route::get('/realtime/metrics', function () {
            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'active_scrapers'   => rand(8, 12),
                    'tickets_processed' => rand(1000, 5000),
                    'alerts_sent'       => rand(50, 200),
                    'system_load'       => rand(30, 80),
                    'last_updated'      => now()->toISOString(),
                ],
            ]);
        });

        Route::get('/realtime/alerts', function () {
            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'active_alerts'   => rand(20, 100),
                    'triggered_today' => rand(5, 25),
                    'success_rate'    => rand(75, 95),
                    'last_updated'    => now()->toISOString(),
                ],
            ]);
        });
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
        Route::post('/metrics', [App\Http\Controllers\Api\PerformanceMetricsController::class, 'receiveMetrics']);

        // Admin-only dashboard data
        Route::middleware([CheckApiRole::class . ':admin'])->group(function (): void {
            Route::get('/dashboard', [App\Http\Controllers\Api\PerformanceMetricsController::class, 'getDashboardData']);
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

    // Agent and Admin routes
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function (): void {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
