<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\StubHubController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketmasterController;
use App\Http\Controllers\Api\TickPickController;
use App\Http\Controllers\Api\ViagogoController;
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
    
    
    // Agent and Admin routes
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
