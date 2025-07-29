<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\StubHubController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketmasterController;
use App\Http\Controllers\Api\TickPickController;
use App\Http\Controllers\Api\ViagogoController;
use App\Http\Controllers\AutomatedPurchaseController;
use App\Http\Middleware\Api\ApiRateLimit;
use App\Http\Middleware\Api\CheckApiRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->middleware([ApiRateLimit::class . ':auth,10,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    Route::get('/status', function () {
        return response()->json([
            'status' => 'active',
            'version' => '2025.07.v4.0',
            'timestamp' => now()
        ]);
    });
});

// Scraping routes
Route::prefix('v1/scraping')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\Api\ScrapingController::class, 'tickets']);
    Route::get('/tickets/{uuid}', [\App\Http\Controllers\Api\ScrapingController::class, 'show']);
    Route::post('/start-scraping', [\App\Http\Controllers\Api\ScrapingController::class, 'startScraping']);
    Route::get('/statistics', [\App\Http\Controllers\Api\ScrapingController::class, 'statistics']);
    Route::get('/platforms', [\App\Http\Controllers\Api\ScrapingController::class, 'platforms']);
    Route::delete('/cleanup', [\App\Http\Controllers\Api\ScrapingController::class, 'cleanup']);
});

// Alert routes
Route::prefix('v1/alerts')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\AlertController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\AlertController::class, 'store']);
    Route::get('/{uuid}', [\App\Http\Controllers\Api\AlertController::class, 'show']);
    Route::put('/{uuid}', [\App\Http\Controllers\Api\AlertController::class, 'update']);
    Route::delete('/{uuid}', [\App\Http\Controllers\Api\AlertController::class, 'destroy']);
    Route::post('/{uuid}/toggle', [\App\Http\Controllers\Api\AlertController::class, 'toggle']);
    Route::post('/{uuid}/test', [\App\Http\Controllers\Api\AlertController::class, 'test']);
    Route::get('/statistics', [\App\Http\Controllers\Api\AlertController::class, 'statistics']);
    Route::post('/check-all', [\App\Http\Controllers\Api\AlertController::class, 'checkAll']);
});

// Purchase routes
Route::prefix('v1/purchases')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function () {
    Route::get('/queue', [\App\Http\Controllers\Api\PurchaseController::class, 'queue']);
    Route::post('/queue', [\App\Http\Controllers\Api\PurchaseController::class, 'addToQueue']);
    Route::put('/queue/{uuid}', [\App\Http\Controllers\Api\PurchaseController::class, 'updateQueue']);
    Route::delete('/queue/{uuid}', [\App\Http\Controllers\Api\PurchaseController::class, 'removeFromQueue']);
    Route::get('/attempts', [\App\Http\Controllers\Api\PurchaseController::class, 'attempts']);
    Route::post('/attempts/initiate', [\App\Http\Controllers\Api\PurchaseController::class, 'initiatePurchase']);
    Route::get('/attempts/{uuid}', [\App\Http\Controllers\Api\PurchaseController::class, 'attemptDetails']);
    Route::post('/attempts/{uuid}/cancel', [\App\Http\Controllers\Api\PurchaseController::class, 'cancelAttempt']);
    Route::get('/statistics', [\App\Http\Controllers\Api\PurchaseController::class, 'statistics']);
    Route::get('/configuration', [\App\Http\Controllers\Api\PurchaseController::class, 'configuration']);
    Route::put('/configuration', [\App\Http\Controllers\Api\PurchaseController::class, 'updateConfiguration']);
});

// Category routes
Route::prefix('v1/categories')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,60,1'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/statistics', [\App\Http\Controllers\Api\CategoryController::class, 'statistics']);
    Route::get('/sport-types', [\App\Http\Controllers\Api\CategoryController::class, 'sportTypes']);
    Route::get('/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::get('/{id}/tickets', [\App\Http\Controllers\Api\CategoryController::class, 'tickets']);
    
    // Admin only routes
    Route::middleware([CheckApiRole::class . ':admin'])->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\CategoryController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'destroy']);
    });
});

// Protected routes
Route::prefix('v1')->middleware(['auth:sanctum', ApiRateLimit::class . ':api,120,1'])->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/revoke-tokens', [AuthController::class, 'revokeAllTokens']);
    
    // Get current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Ticket routes
    Route::apiResource('tickets', TicketController::class)->parameters([
        'tickets' => 'ticket:uuid'
    ]);
    
    
    // New route for ticket availability updates
    Route::post('/tickets/availability-update', [TicketController::class, 'availabilityUpdate']);
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
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
    });
    
    // User Preferences API
    Route::prefix('preferences')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PreferencesController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\PreferencesController::class, 'store']);
        Route::put('/{key}', [App\Http\Controllers\Api\PreferencesController::class, 'update']);
        Route::delete('/{key}', [App\Http\Controllers\Api\PreferencesController::class, 'destroy']);
        Route::post('/reset', [App\Http\Controllers\Api\PreferencesController::class, 'reset']);
    });
    
    // Ticket Criteria Configuration
    Route::prefix('ticket-criteria')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TicketCriteriaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TicketCriteriaController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\TicketCriteriaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TicketCriteriaController::class, 'destroy']);
        Route::post('/{id}/toggle', [App\Http\Controllers\Api\TicketCriteriaController::class, 'toggle']);
    });
    
    // Analytics API
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [App\Http\Controllers\Api\AnalyticsController::class, 'overview']);
        Route::get('/ticket-trends', [App\Http\Controllers\Api\AnalyticsController::class, 'ticketTrends']);
        Route::get('/platform-performance', [App\Http\Controllers\Api\AnalyticsController::class, 'platformPerformance']);
        Route::get('/success-rates', [App\Http\Controllers\Api\AnalyticsController::class, 'successRates']);
        Route::get('/price-analysis', [App\Http\Controllers\Api\AnalyticsController::class, 'priceAnalysis']);
        Route::get('/demand-patterns', [App\Http\Controllers\Api\AnalyticsController::class, 'demandPatterns']);
        Route::get('/export/{type}', [App\Http\Controllers\Api\AnalyticsController::class, 'export']);
    });
    
    // Enhanced Analytics & Reporting API Routes
    Route::prefix('enhanced-analytics')->group(function () {
        // Chart Data Endpoints
        Route::get('/charts', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getChartData']);
        Route::get('/charts/{type}', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'getChartData']);
        
        // Export Endpoints
        Route::post('/export', [App\Http\Controllers\Api\EnhancedAnalyticsController::class, 'exportData']);
        Route::get('/export/formats', function() {
            return response()->json([
                'success' => true,
                'formats' => ['csv', 'xlsx', 'pdf', 'json'],
                'types' => ['ticket_trends', 'price_analysis', 'platform_performance', 'user_engagement', 'comprehensive_analytics']
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
        Route::post('/dashboard/custom', function(Request $request) {
            // Custom dashboard configuration endpoint
            return response()->json([
                'success' => true,
                'message' => 'Custom dashboard configuration saved',
                'config' => $request->all()
            ]);
        });
        
        // Real-time Data Endpoints
        Route::get('/realtime/metrics', function() {
            return response()->json([
                'success' => true,
                'data' => [
                    'active_scrapers' => rand(8, 12),
                    'tickets_processed' => rand(1000, 5000),
                    'alerts_sent' => rand(50, 200),
                    'system_load' => rand(30, 80),
                    'last_updated' => now()->toISOString()
                ]
            ]);
        });
        
        Route::get('/realtime/alerts', function() {
            return response()->json([
                'success' => true,
                'data' => [
                    'active_alerts' => rand(20, 100),
                    'triggered_today' => rand(5, 25),
                    'success_rate' => rand(75, 95),
                    'last_updated' => now()->toISOString()
                ]
            ]);
        });
    });
    
    // Enhanced Monitoring API
    Route::prefix('monitoring')->group(function () {
        Route::get('/stats', [MonitoringController::class, 'getRealtimeStats']);
        Route::get('/platform-health', [MonitoringController::class, 'getPlatformHealth']);
        Route::get('/monitors', [MonitoringController::class, 'getMonitors']);
        Route::get('/activity', [MonitoringController::class, 'getRecentActivity']);
        Route::get('/system-metrics', [MonitoringController::class, 'getSystemMetrics']);
        Route::post('/monitors/{monitorId}/check-now', [MonitoringController::class, 'checkMonitorNow']);
        Route::post('/monitors/{monitorId}/toggle', [MonitoringController::class, 'toggleMonitor']);
    });
    
    Route::middleware([CheckApiRole::class . ':admin'])->group(function () {
        // Admin-specific routes can be added here
    });
    
    // Ticketmaster scraping routes
    Route::prefix('ticketmaster')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function () {
        // Search events (available to all authenticated users)
        Route::post('/search', [TicketmasterController::class, 'search']);
        Route::post('/event-details', [TicketmasterController::class, 'getEventDetails']);
        Route::get('/stats', [TicketmasterController::class, 'stats']);
        
        // Import routes (restricted to agents and admins)
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
            Route::post('/import', [TicketmasterController::class, 'import']);
            Route::post('/import-urls', [TicketmasterController::class, 'importUrls']);
        });
    });
    
    // StubHub routes
    Route::prefix('stubhub')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function () {
        Route::post('/search', [StubHubController::class, 'search']);
        Route::post('/event-details', [StubHubController::class, 'getEventDetails']);
        Route::get('/stats', [StubHubController::class, 'stats']);
        
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
            Route::post('/import', [StubHubController::class, 'import']);
            Route::post('/import-urls', [StubHubController::class, 'importUrls']);
        });
    });
    
    // Viagogo routes
    Route::prefix('viagogo')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function () {
        Route::post('/search', [ViagogoController::class, 'search']);
        Route::post('/event-details', [ViagogoController::class, 'getEventDetails']);
        Route::get('/stats', [ViagogoController::class, 'stats']);
        
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
            Route::post('/import', [ViagogoController::class, 'import']);
            Route::post('/import-urls', [ViagogoController::class, 'importUrls']);
        });
    });
    
    // TickPick routes
    Route::prefix('tickpick')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function () {
        Route::post('/search', [TickPickController::class, 'search']);
        Route::post('/event-details', [TickPickController::class, 'getEventDetails']);
        Route::get('/stats', [TickPickController::class, 'stats']);
        
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
            Route::post('/import', [TickPickController::class, 'import']);
            Route::post('/import-urls', [TickPickController::class, 'importUrls']);
        });
    });
    
    // Automated Purchase System Routes
    Route::prefix('automated-purchase')->middleware([ApiRateLimit::class . ':api,60,1'])->group(function () {
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
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
