<?php declare(strict_types=1);

/**
 * HD Tickets Web Routes - Sports Events Entry Tickets Monitoring System
 *
 * This file contains the main web routes for the HD Tickets application,
 * implementing a comprehensive role-based dashboard routing strategy.
 *
 * @version 4.0.0
 *
 * @environment Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
 *
 * ROUTE ARCHITECTURE OVERVIEW:
 * ===========================
 *
 * Dashboard Routing Strategy:
 * - Centralized dispatcher pattern via /dashboard route
 * - Role-based automatic redirection to appropriate dashboards
 * - Hierarchical permission system with fallback protection
 * - Sports events ticket monitoring focus (NOT helpdesk system)
 *
 * User Roles & Dashboard Access:
 * - Admin: Complete system administration (/admin/dashboard)
 * - Agent: Ticket monitoring & purchase decisions (/dashboard/agent)
 * - Customer: Basic sports events monitoring (/dashboard/customer)
 * - Scraper: API-only rotation users (no web interface access)
 *
 * Security Features:
 * - Multi-layer middleware protection (auth, verified, role-based)
 * - CSRF protection for all state-changing routes
 * - Rate limiting for API and AJAX endpoints
 * - Comprehensive access control validation
 */

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseDecisionController;
use App\Http\Controllers\SecurityDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes - No Authentication Required
|--------------------------------------------------------------------------
|
| These routes are accessible to all visitors and handle the public-facing
| aspects of the HD Tickets sports events monitoring system.
|
*/

// Root route - Redirect to appropriate entry point
Route::get('/', function () {
    if (Auth::check()) {
        // If user is logged in, redirect to dashboard
        return redirect()->route('dashboard');
    }
    // If not logged in, redirect to home page
    return redirect()->route('home');
})->name('root');

// Home route - Public landing page for sports events ticket monitoring
Route::get('/home', [App\Http\Controllers\HomeController::class, 'welcome'])->name('home');

// User Preferences Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/preferences', [App\Http\Controllers\UserPreferencesController::class, 'index'])->name('preferences.index');
});

// Legal document routes - Public access to legal documents
Route::get('/legal', [App\Http\Controllers\LegalController::class, 'index'])->name('legal.index');
Route::get('/legal/terms-of-service', [App\Http\Controllers\LegalController::class, 'termsOfService'])->name('legal.terms-of-service');
Route::get('/legal/privacy-policy', [App\Http\Controllers\LegalController::class, 'privacyPolicy'])->name('legal.privacy-policy');
Route::get('/legal/disclaimer', [App\Http\Controllers\LegalController::class, 'disclaimer'])->name('legal.disclaimer');
Route::get('/legal/gdpr-compliance', [App\Http\Controllers\LegalController::class, 'gdprCompliance'])->name('legal.gdpr-compliance');
Route::get('/legal/data-processing-agreement', [App\Http\Controllers\LegalController::class, 'dataProcessingAgreement'])->name('legal.data-processing-agreement');
Route::get('/legal/cookie-policy', [App\Http\Controllers\LegalController::class, 'cookiePolicy'])->name('legal.cookie-policy');
Route::get('/legal/acceptable-use-policy', [App\Http\Controllers\LegalController::class, 'acceptableUsePolicy'])->name('legal.acceptable-use-policy');
Route::get('/legal/legal-notices', [App\Http\Controllers\LegalController::class, 'legalNotices'])->name('legal.legal-notices');

// Placeholder routes for subscription and profile (to be implemented later)
Route::get('/subscription/plans', function () {
    return redirect()->route('home')->with('message', 'Subscription plans coming soon!');
})->name('subscription.plans');

Route::get('/profile/security', function () {
    return redirect()->route('login')->with('message', 'Please login to access security settings.');
})->name('profile.security');

// Temporary CSP debug route
Route::get('/csp-debug', function () {
    $csp = config('security.csp', []);
    $policies = [];

    foreach ($csp as $directive => $sources) {
        if ($directive === 'upgrade-insecure-requests') {
            if ($sources === TRUE) {
                $policies[] = 'upgrade-insecure-requests';
            }
        } elseif (is_array($sources)) {
            $policies[] = $directive . ' ' . implode(' ', $sources);
        }
    }

    return response()->json([
        'config'         => $csp,
        'built_policies' => $policies,
        'final_csp'      => implode('; ', $policies),
        'empty_check'    => empty($policies),
    ]);
})->name('csp.debug');

/*
|--------------------------------------------------------------------------
| HD Tickets Dashboard Routing Strategy
|--------------------------------------------------------------------------
|
| CENTRALIZED DISPATCHER PATTERN:
| The HD Tickets application implements a sophisticated role-based dashboard
| routing system for sports events entry tickets monitoring. The system uses
| a centralized dispatcher pattern where /dashboard serves as the main entry
| point and automatically redirects users to their role-appropriate dashboard.
|
| ROLE HIERARCHY & ACCESS:
| - Admin: Full system access (/admin/dashboard) + can access all dashboards
| - Agent: Ticket monitoring & purchase decisions (/dashboard/agent)
| - Customer: Basic sports events monitoring (/dashboard/customer)
| - Scraper: API-only rotation users (no web interface access)
|
| SECURITY FEATURES:
| - Multi-layer middleware protection (auth, verified, role-based)
| - Hierarchical permission inheritance (admin can access all)
| - Fallback dashboard for edge cases
| - Comprehensive access control validation
|
| ROUTING FLOW:
| 1. User accesses /dashboard (main entry point)
| 2. HomeController@index detects user role
| 3. System redirects to appropriate role-specific dashboard
| 4. Role-based middleware enforces access control
| 5. Dashboard renders with role-appropriate features
|
*/

// Main Dashboard Dispatcher Route
// Entry point for all authenticated users - automatically detects role and redirects
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified']) // Requires authentication and email verification
    ->name('dashboard'); // Named route for easy reference

// Role-Specific Dashboard Routes
// Grouped under /dashboard prefix for consistent URL structure
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    /*
     * Basic Dashboard (Fallback Route)
     * Purpose: Safety net for users without specific role assignments
     * Access: Any authenticated user
     * Features: Limited functionality with basic sports events monitoring
     */
    Route::get('/basic', [DashboardController::class, 'index'])
        ->name('basic'); // Route: dashboard.basic

    /*
     * Customer Dashboard
     * Purpose: Enhanced sports events ticket monitoring for end users
     * Access: Users with 'customer' role + admin inheritance
     * Features: Real-time data, analytics, personalized recommendations, modern UX
     * Controller: EnhancedDashboardController@index
     * View: resources/views/dashboard/customer-enhanced.blade.php
     */
    Route::middleware([App\Http\Middleware\CustomerMiddleware::class])->get('/customer', [App\Http\Controllers\EnhancedDashboardController::class, 'index'])
        ->name('customer'); // Route: dashboard.customer

    /*
     * Legacy Customer Dashboard
     * Purpose: Basic sports events ticket monitoring (legacy support)
     * Access: Users with 'customer' role + admin inheritance
     * Features: Event browsing, basic alerts, personal preferences
     * Controller: DashboardController@index
     * View: resources/views/dashboard/customer.blade.php
     */
    Route::middleware([App\Http\Middleware\CustomerMiddleware::class])->get('/customer/legacy', [DashboardController::class, 'index'])
        ->name('customer.legacy'); // Route: dashboard.customer.legacy

    /*
     * Agent Dashboard
     * Purpose: Advanced ticket monitoring and purchase decision management
     * Access: Users with 'agent' role + admin inheritance
     * Features: Purchase queue, advanced analytics, decision automation
     * Controller: AgentDashboardController@index
     * View: resources/views/dashboard/agent.blade.php
     */
    Route::middleware([App\Http\Middleware\AgentMiddleware::class])->get('/agent', [App\Http\Controllers\AgentDashboardController::class, 'index'])
        ->name('agent'); // Route: dashboard.agent

    /*
     * Scraper Dashboard
     * Purpose: Scraping operations monitoring and job management
     * Access: Users with 'scraper' role + admin inheritance
     * Features: Scraping job status, rotation management, performance metrics
     * Controller: ScraperDashboardController@index
     * View: resources/views/dashboard/scraper.blade.php
     * Note: Scraper users typically don't access web interface (API-only)
     */
    Route::middleware([App\Http\Middleware\ScraperMiddleware::class])->get('/scraper', [App\Http\Controllers\ScraperDashboardController::class, 'index'])
        ->name('scraper'); // Route: dashboard.scraper
        
    /*
     * Responsive Design System Example Dashboard
     * Purpose: Showcase the new responsive design system capabilities
     * Access: Any authenticated user (demo purposes)
     * Features: All responsive components, touch interactions, container queries
     */
    Route::get('/responsive-example', function () {
        return view('examples.responsive-dashboard');
    })->name('responsive-example'); // Route: dashboard.responsive-example
    
    /*
     * IMAP Email Monitoring Dashboard
     * Purpose: Monitor and manage email-based sports event ticket discovery
     * Access: Users with 'admin' or 'agent' role
     * Features: Connection health, email processing stats, platform configuration
     */
    Route::middleware(['role:admin,agent'])->prefix('imap')->name('imap.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ImapDashboardController::class, 'index'])->name('dashboard');
        Route::get('/connections', [\App\Http\Controllers\ImapDashboardController::class, 'connections'])->name('connections');
        Route::get('/platforms', [\App\Http\Controllers\ImapDashboardController::class, 'platforms'])->name('platforms');
        Route::get('/logs', [\App\Http\Controllers\ImapDashboardController::class, 'logs'])->name('logs');
        Route::get('/analytics', [\App\Http\Controllers\ImapDashboardController::class, 'analytics'])->name('analytics');
        
        // Actions
        Route::post('/trigger-monitoring', [\App\Http\Controllers\ImapDashboardController::class, 'triggerMonitoring'])->name('trigger-monitoring');
        Route::post('/clear-cache', [\App\Http\Controllers\ImapDashboardController::class, 'clearCache'])->name('clear-cache');
    });
    
    /*
     * Advanced Analytics Dashboard
     * Purpose: Comprehensive sports event ticket analytics and insights
     * Access: Users with 'admin' or 'agent' role
     * Features: Interactive charts, predictive analytics, anomaly detection, data export
     */
    Route::middleware(['role:admin,agent'])->prefix('analytics')->name('analytics.')->group(function () {
        // Main dashboard
        Route::get('/', [\App\Http\Controllers\AnalyticsDashboardController::class, 'index'])->name('dashboard');
        
        // Data endpoints for AJAX requests
        Route::get('/dashboard-data', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getDashboardData'])->name('dashboard-data');
        Route::get('/overview-metrics', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getOverviewMetrics'])->name('overview-metrics');
        Route::get('/platform-performance', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getPlatformPerformance'])->name('platform-performance');
        Route::get('/pricing-trends', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getPricingTrends'])->name('pricing-trends');
        Route::get('/event-popularity', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getEventPopularity'])->name('event-popularity');
        Route::get('/anomalies', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getAnomalies'])->name('anomalies');
        Route::get('/realtime-data', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getRealtimeData'])->name('realtime-data');
        Route::get('/predictive-insights', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getPredictiveInsights'])->name('predictive-insights');
        Route::get('/historical-comparison', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getHistoricalComparison'])->name('historical-comparison');
        Route::get('/filter-options', [\App\Http\Controllers\AnalyticsDashboardController::class, 'getFilterOptions'])->name('filter-options');
        
        // Actions
        Route::post('/export', [\App\Http\Controllers\AnalyticsDashboardController::class, 'exportData'])->name('export');
        Route::post('/clear-cache', [\App\Http\Controllers\AnalyticsDashboardController::class, 'clearCache'])->name('clear-cache');
        
        // Download route for exports
        Route::get('/download/{file}', function ($file) {
            $path = storage_path('app/analytics/exports/' . $file);
            if (!file_exists($path)) {
                abort(404);
            }
            return response()->download($path);
        })->name('download');
    });
    
    /*
     * Personal Analytics Dashboard
     * Purpose: User-specific analytics and insights for individual ticket monitoring
     * Access: All authenticated users (customers, agents, admins)
     * Features: Personal stats, savings tracking, activity history
     */
    Route::get('/analytics', function () {
        return view('dashboard.analytics');
    })->name('analytics');
    
    /*
     * Real-time Notification System Routes
     * Purpose: Live notifications for ticket alerts, price changes, and system updates
     * Access: All authenticated users
     * Features: WebSocket notifications, notification history, push notifications
     */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', function () {
            return view('notifications.index');
        })->name('index');
        Route::get('/history', function () {
            return view('notifications.history');
        })->name('history');
    });
});

// Scraper dashboard API routes - consistent role middleware application
// Route::middleware(['auth', 'verified', 'role:scraper,admin'])->group(function () {
//     Route::prefix('scraper/api')->name('scraper.api.')->group(function () {
//         Route::get('/realtime-metrics', [App\Http\Controllers\ScraperDashboardController::class, 'getRealtimeMetrics'])
//             ->name('realtime-metrics');
//         Route::get('/job-details/{jobId}', [App\Http\Controllers\ScraperDashboardController::class, 'getJobDetails'])
//             ->name('job-details');
//     });
// });

// Admin routes are now handled in routes/admin.php

// Ticket Sources routes
Route::middleware(['auth', 'verified'])->group(function (): void {
    // Export route must come before resource routes to avoid conflicts
    Route::get('ticket-sources/export', [App\Http\Controllers\TicketSourceController::class, 'export'])->name('ticket-sources.export');
    Route::get('ticket-sources/stats', [App\Http\Controllers\TicketSourceController::class, 'stats'])->name('ticket-sources.stats');
    Route::post('ticket-sources/bulk-action', [App\Http\Controllers\TicketSourceController::class, 'bulkAction'])->name('ticket-sources.bulk-action');

    // Resource routes
    Route::resource('ticket-sources', App\Http\Controllers\TicketSourceController::class);

    // Additional routes that need the {ticket_source} parameter
    Route::patch('ticket-sources/{ticket_source}/toggle', [App\Http\Controllers\TicketSourceController::class, 'toggle'])->name('ticket-sources.toggle');
    Route::get('ticket-sources/{ticket_source}/refresh', [App\Http\Controllers\TicketSourceController::class, 'refresh'])->name('ticket-sources.refresh');
});

// Ticket API Integration routes - using consistent role middleware
// Route::middleware(['auth', 'verified', 'role:admin'])->prefix('ticket-api')->group(function () {
//     Route::get('/', [App\Http\Controllers\TicketApiController::class, 'index'])->name('ticket-api.index');
//     Route::post('/search', [App\Http\Controllers\TicketApiController::class, 'search'])->name('ticket-api.search');
//     Route::post('/import', [App\Http\Controllers\TicketApiController::class, 'importEvents'])->name('ticket-api.import');
//     Route::get('/test-connections', [App\Http\Controllers\TicketApiController::class, 'testConnections'])->name('ticket-api.test');
//     Route::get('/event/{platform}/{eventId}', [App\Http\Controllers\TicketApiController::class, 'getEvent'])->name('ticket-api.event');
// });

// API routes for ticket sources
Route::middleware(['auth:sanctum'])->prefix('api')->group(function (): void {
    Route::get('ticket-sources', [App\Http\Controllers\TicketSourceController::class, 'apiIndex']);
});

// AJAX routes for lazy loading and real-time updates
Route::middleware(['auth', 'verified', 'throttle:60,1'])->prefix('ajax')->name('ajax.')->group(function (): void {
    Route::get('tickets/load', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadTickets'])->name('tickets.load');
    Route::get('tickets/search', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'searchTickets'])->name('tickets.search');
    Route::get('tickets/load-more', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadMore'])->name('tickets.load-more');
    Route::get('dashboard/stats', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadDashboardStats'])->name('dashboard.stats');

    // Dashboard dynamic content endpoints
    Route::get('dashboard/live-tickets', [App\Http\Controllers\Ajax\DashboardController::class, 'liveTickets'])
        ->middleware('throttle:30,1')
        ->name('dashboard.live-tickets');

    Route::get('dashboard/user-recommendations', [App\Http\Controllers\Ajax\DashboardController::class, 'userRecommendations'])
        ->middleware('throttle:10,1')
        ->name('dashboard.user-recommendations');

    Route::get('dashboard/platform-health', [App\Http\Controllers\Ajax\DashboardController::class, 'platformHealth'])
        ->middleware('throttle:20,1')
        ->name('dashboard.platform-health');

    Route::get('dashboard/price-alerts', [App\Http\Controllers\Ajax\DashboardController::class, 'priceAlerts'])
        ->middleware('throttle:30,1')
        ->name('dashboard.price-alerts');

    // Simple dashboard AJAX endpoints
    Route::get('dashboard/stats', [DashboardController::class, 'getDashboardStats'])
        ->middleware('throttle:60,1')
        ->name('dashboard.stats');

    Route::get('dashboard/recent-tickets', [DashboardController::class, 'getRecentTicketsHtml'])
        ->middleware('throttle:60,1')
        ->name('dashboard.recent-tickets');
});

// Main tickets route with enhanced frontend interface
Route::get('/tickets', function () {
    // Get filter data for the initial page load from scraped_tickets table
    $sportTypes = DB::table('scraped_tickets')
        ->select('sport as value', DB::raw('COUNT(*) as count'))
        ->where('status', 'active')
        ->whereNotNull('sport')
        ->where('sport', '!=', '')
        ->groupBy('sport')
        ->orderBy('count', 'desc')
        ->get();
    
    $cities = DB::table('scraped_tickets')
        ->select('location as city', DB::raw('COUNT(*) as count'))
        ->where('status', 'active')
        ->whereNotNull('location')
        ->where('location', '!=', '')
        ->groupBy('location')
        ->orderBy('count', 'desc')
        ->limit(20)
        ->get();
    
    $platforms = DB::table('scraped_tickets')
        ->select('platform as name', DB::raw('COUNT(*) as count'))
        ->where('status', 'active')
        ->whereNotNull('platform')
        ->where('platform', '!=', '')
        ->groupBy('platform')
        ->orderBy('count', 'desc')
        ->get();
    
    $totalTickets = DB::table('scraped_tickets')
        ->where('status', 'active')
        ->count();
    
    $platformsCount = $platforms->count();
    $citiesCount = $cities->count();
    
    return view('tickets.index', compact(
        'sportTypes', 
        'cities', 
        'platforms', 
        'totalTickets', 
        'platformsCount', 
        'citiesCount'
    ));
})->name('tickets.main');

// Legacy redirect for backwards compatibility
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/tickets-legacy', function () {
        return redirect()->route('tickets.scraping.index');
    })->name('tickets.redirect');

    // Temporary test route for debugging dashboard and navigation
    Route::get('/dashboard-test', function () {
        return view('dashboard-test');
    })->name('dashboard.test');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile-debug', function (Request $request) {
        $user = $request->user();

        return view('profile.show-debug', compact('user'));
    })->name('profile.show.debug');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::get('/profile/stats', [ProfileController::class, 'stats'])->name('profile.stats');
    Route::get('/profile/analytics', [ProfileController::class, 'analytics'])->name('profile.analytics');
    Route::get('/profile/analytics/data', [ProfileController::class, 'getAnalyticsData'])->name('profile.analytics.data');
    Route::get('/profile/security/advanced', [ProfileController::class, 'advancedSecurity'])->name('profile.security.advanced');
    Route::post('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo.upload');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Enhanced Security Management
    Route::prefix('profile/security')->name('profile.security.')->group(function (): void {
        Route::get('/download-backup-codes', [ProfileController::class, 'downloadBackupCodes'])->name('download-backup-codes');
        Route::post('/trust-device', [ProfileController::class, 'trustDevice'])->name('trust-device');
        Route::delete('/trusted-device/{deviceIndex}', [ProfileController::class, 'removeTrustedDevice'])->name('remove-trusted-device');
        Route::delete('/session/{sessionId}', [ProfileController::class, 'revokeSession'])->name('revoke-session');
        Route::post('/revoke-all-sessions', [ProfileController::class, 'revokeAllOtherSessions'])->name('revoke-all-sessions');
    });

    // Account Deletion Protection System
    Route::prefix('account/deletion')->name('account.deletion.')->group(function (): void {
        Route::get('/warning', [App\Http\Controllers\AccountDeletionController::class, 'showWarning'])->name('warning');
        Route::post('/initiate', [App\Http\Controllers\AccountDeletionController::class, 'initiate'])->name('initiate');
        Route::post('/export', [App\Http\Controllers\AccountDeletionController::class, 'requestDataExport'])->name('export');
        Route::get('/export/{exportRequest}/download', [App\Http\Controllers\AccountDeletionController::class, 'downloadExport'])->name('export.download');
        Route::get('/audit-log', [App\Http\Controllers\AccountDeletionController::class, 'auditLog'])->name('audit-log');
    });

    // User Preferences Management
    Route::prefix('preferences')->name('preferences.')->group(function (): void {
        Route::get('/', [App\Http\Controllers\UserPreferencesController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\UserPreferencesController::class, 'update'])->name('update');
        Route::post('/update-single', [App\Http\Controllers\UserPreferencesController::class, 'updateSingle'])->name('update-single');
        Route::post('/update-preference', [App\Http\Controllers\UserPreferencesController::class, 'updatePreference'])->name('update-preference');
        Route::post('/update-preferences', [App\Http\Controllers\UserPreferencesController::class, 'updatePreferences'])->name('update-preferences');
        Route::post('/detect-timezone', [App\Http\Controllers\UserPreferencesController::class, 'detectTimezone'])->name('detect-timezone');
        Route::post('/reset', [App\Http\Controllers\UserPreferencesController::class, 'reset'])->name('reset');
        Route::post('/reset-preferences', [App\Http\Controllers\UserPreferencesController::class, 'resetPreferences'])->name('reset-preferences');
        Route::get('/export', [App\Http\Controllers\UserPreferencesController::class, 'export'])->name('export');
        Route::get('/export-preferences', [App\Http\Controllers\UserPreferencesController::class, 'exportPreferences'])->name('export-preferences');
        Route::post('/import', [App\Http\Controllers\UserPreferencesController::class, 'import'])->name('import');
        Route::post('/load-preset/{preset}', [App\Http\Controllers\UserPreferencesController::class, 'loadPreset'])->name('load-preset');

        // Sports Preferences Routes
        Route::get('/sports', [App\Http\Controllers\UserPreferencesController::class, 'getSportsPreferences'])->name('sports.get');
        Route::post('/sports/update-selected', [App\Http\Controllers\UserPreferencesController::class, 'updateSelectedSports'])->name('sports.update-selected');

        // Favorite Teams Routes
        Route::get('/teams/search', [App\Http\Controllers\UserPreferencesController::class, 'searchTeams'])->name('teams.search');
        Route::post('/teams', [App\Http\Controllers\UserPreferencesController::class, 'storeTeam'])->name('teams.store');
        Route::put('/teams/{team}', [App\Http\Controllers\UserPreferencesController::class, 'updateTeam'])->name('teams.update');
        Route::delete('/teams/{team}', [App\Http\Controllers\UserPreferencesController::class, 'destroyTeam'])->name('teams.destroy');

        // Favorite Venues Routes
        Route::get('/venues/search', [App\Http\Controllers\UserPreferencesController::class, 'searchVenues'])->name('venues.search');
        Route::post('/venues', [App\Http\Controllers\UserPreferencesController::class, 'storeVenue'])->name('venues.store');
        Route::put('/venues/{venue}', [App\Http\Controllers\UserPreferencesController::class, 'updateVenue'])->name('venues.update');
        Route::delete('/venues/{venue}', [App\Http\Controllers\UserPreferencesController::class, 'destroyVenue'])->name('venues.destroy');
        
        // Notification Settings
        Route::get('/notifications', function () {
            return view('settings.notifications');
        })->name('notifications');
        Route::put('/notification-preferences', [App\Http\Controllers\UserPreferencesController::class, 'updateNotificationPreferences'])->name('notification-preferences');

        // Price Preferences Routes
        Route::post('/prices', [App\Http\Controllers\UserPreferencesController::class, 'storePricePreference'])->name('prices.store');
        Route::put('/prices/{price}', [App\Http\Controllers\UserPreferencesController::class, 'updatePricePreference'])->name('prices.update');
        Route::delete('/prices/{price}', [App\Http\Controllers\UserPreferencesController::class, 'destroyPricePreference'])->name('prices.destroy');
    });

    // Settings Import/Export Routes
    Route::prefix('settings-export')->name('settings-export.')->group(function (): void {
        Route::get('/', [App\Http\Controllers\SettingsExportController::class, 'index'])->name('index');
        Route::post('/export', [App\Http\Controllers\SettingsExportController::class, 'exportSettings'])->name('export');
        Route::post('/preview', [App\Http\Controllers\SettingsExportController::class, 'previewImport'])->name('preview');
        Route::post('/import', [App\Http\Controllers\SettingsExportController::class, 'importSettings'])->name('import');
        Route::post('/resolve-conflicts', [App\Http\Controllers\SettingsExportController::class, 'resolveConflicts'])->name('resolve-conflicts');
        Route::post('/reset', [App\Http\Controllers\SettingsExportController::class, 'resetToDefaults'])->name('reset');
    });

    // Profile Picture Management
    Route::prefix('profile/picture')->name('profile.picture.')->group(function (): void {
        Route::post('/upload', [App\Http\Controllers\ProfilePictureController::class, 'upload'])->name('upload');
        Route::post('/crop', [App\Http\Controllers\ProfilePictureController::class, 'crop'])->name('crop');
        Route::delete('/delete', [App\Http\Controllers\ProfilePictureController::class, 'delete'])->name('delete');
        Route::delete('/remove', [App\Http\Controllers\ProfilePictureController::class, 'remove'])->name('remove');
        Route::get('/info', [App\Http\Controllers\ProfilePictureController::class, 'info'])->name('info');
        Route::get('/limits', [App\Http\Controllers\ProfilePictureController::class, 'getUploadLimits'])->name('limits');
    });

    // User Activity Dashboard
    Route::prefix('profile/activity')->name('profile.activity.')->group(function (): void {
        Route::get('/', [App\Http\Controllers\UserActivityController::class, 'index'])->name('dashboard');
        Route::get('/widget-data', [App\Http\Controllers\UserActivityController::class, 'getWidgetData'])->name('widget-data');
        Route::get('/export', [App\Http\Controllers\UserActivityController::class, 'exportActivityData'])->name('export');
    });
});

// Notification Settings Route (outside dashboard and preferences groups)
Route::get('/settings/notifications', function () {
    return view('settings.notifications');
})->middleware(['auth', 'verified'])->name('settings.notifications');

// AI Recommendations System Routes
Route::middleware(['auth', 'verified'])->prefix('recommendations')->name('recommendations.')->group(function () {
    Route::get('/', [App\Http\Controllers\RecommendationController::class, 'dashboard'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Security Dashboard Routes
|--------------------------------------------------------------------------
|
| Comprehensive security management dashboard for HD Tickets system.
| Provides real-time security monitoring, threat detection, incident
| management, audit logging, and interactive security demos.
|
| Access: Admin role only for security management
|
*/
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('security')->name('security.')->group(function (): void {
    // Main security dashboard
    Route::get('/dashboard', [SecurityDashboardController::class, 'index'])->name('dashboard.index');
    
    // Security management pages
    Route::get('/incidents', [SecurityDashboardController::class, 'incidents'])->name('dashboard.incidents');
    Route::get('/events', [SecurityDashboardController::class, 'events'])->name('dashboard.events');
    Route::get('/users', [SecurityDashboardController::class, 'users'])->name('dashboard.users');
    Route::get('/audit', [SecurityDashboardController::class, 'audit'])->name('dashboard.audit');
    Route::get('/configuration', [SecurityDashboardController::class, 'configuration'])->name('dashboard.configuration');
    Route::get('/demo', [SecurityDashboardController::class, 'demo'])->name('dashboard.demo');
    
    // API endpoints for real-time data
    Route::prefix('dashboard/api')->name('dashboard.api.')->group(function (): void {
        Route::get('/data', [SecurityDashboardController::class, 'apiDashboardData'])->name('data');
        Route::get('/live-events', [SecurityDashboardController::class, 'apiLiveEvents'])->name('live-events');
    });
});

// Ticket Monitoring & Alerts Routes
Route::middleware(['auth', 'verified'])->prefix('monitoring')->name('monitoring.')->group(function (): void {
    // Main monitoring dashboard
    Route::get('/', [App\Http\Controllers\MonitoringController::class, 'index'])->name('index');
    
    // Alert management
    Route::post('/alerts', [App\Http\Controllers\MonitoringController::class, 'createAlert'])->name('alerts.create');
    Route::get('/alerts/{alert}', [App\Http\Controllers\MonitoringController::class, 'showAlert'])->name('alerts.show');
    Route::patch('/alerts/{alert}', [App\Http\Controllers\MonitoringController::class, 'updateAlert'])->name('alerts.update');
    Route::delete('/alerts/{alert}', [App\Http\Controllers\MonitoringController::class, 'deleteAlert'])->name('alerts.delete');
    Route::patch('/alerts/{alert}/toggle', [App\Http\Controllers\MonitoringController::class, 'toggleAlert'])->name('alerts.toggle');
    Route::post('/alerts/{alert}/dismiss', [App\Http\Controllers\MonitoringController::class, 'dismissAlert'])->name('alerts.dismiss');
    
    // Alert history and analytics
    Route::get('/alerts/{alert}/history', [App\Http\Controllers\MonitoringController::class, 'alertHistory'])->name('alerts.history');
    Route::get('/alerts/{alert}/chart-data', [App\Http\Controllers\MonitoringController::class, 'getAlertChartData'])->name('alerts.chart-data');
    
    // AJAX endpoints for real-time updates
    Route::get('/ajax/dashboard-stats', [App\Http\Controllers\MonitoringController::class, 'getDashboardStats'])
        ->middleware('throttle:30,1')
        ->name('ajax.dashboard-stats');
    
    Route::get('/ajax/alerts', [App\Http\Controllers\MonitoringController::class, 'getAlerts'])
        ->middleware('throttle:60,1')
        ->name('ajax.alerts');
    
    Route::post('/ajax/refresh', [App\Http\Controllers\MonitoringController::class, 'refreshMonitoring'])
        ->middleware('throttle:10,1')
        ->name('ajax.refresh');
    
    Route::get('/ajax/price-updates', [App\Http\Controllers\MonitoringController::class, 'getPriceUpdates'])
        ->middleware('throttle:120,1')
        ->name('ajax.price-updates');
});

// Ticket Scraping Routes
Route::middleware(['auth', 'verified'])->prefix('tickets')->name('tickets.')->group(function (): void {
    // Scraping dashboard and listing
    Route::get('scraping', [App\Http\Controllers\TicketScrapingController::class, 'index'])->name('scraping.index');

    // Search and filtering - SPECIFIC ROUTES MUST COME BEFORE PARAMETERIZED ROUTES
    Route::post('scraping/search', [App\Http\Controllers\TicketScrapingController::class, 'search'])->name('scraping.search');
    Route::get('scraping/manchester-united', [App\Http\Controllers\TicketScrapingController::class, 'manchesterUnited'])->name('scraping.manchester-united');
    Route::get('scraping/high-demand-sports', [App\Http\Controllers\TicketScrapingController::class, 'highDemandSports'])->name('scraping.high-demand-sports');
    Route::get('scraping/trending', [App\Http\Controllers\TicketScrapingController::class, 'trending'])->name('scraping.trending');
    Route::get('scraping/best-deals', [App\Http\Controllers\TicketScrapingController::class, 'bestDeals'])->name('scraping.best-deals');

    // Show individual ticket - MUST COME AFTER SPECIFIC ROUTES
    Route::get('scraping/{ticket}', [App\Http\Controllers\TicketScrapingController::class, 'show'])->name('scraping.show');

    // Purchase functionality
    Route::post('scraping/{ticket}/purchase', [App\Http\Controllers\TicketScrapingController::class, 'purchase'])->name('scraping.purchase');

    // Alert management
    Route::get('alerts', [App\Http\Controllers\TicketScrapingController::class, 'alerts'])->name('alerts.index');
    Route::post('alerts', [App\Http\Controllers\TicketScrapingController::class, 'createAlert'])->name('alerts.create');
    Route::patch('alerts/{alert}', [App\Http\Controllers\TicketScrapingController::class, 'updateAlert'])->name('alerts.update');
    Route::delete('alerts/{alert}', [App\Http\Controllers\TicketScrapingController::class, 'deleteAlert'])->name('alerts.delete');
    Route::post('alerts/check', [App\Http\Controllers\TicketScrapingController::class, 'checkAlerts'])->name('alerts.check');

    // Statistics and analytics
    Route::get('scraping/stats', [App\Http\Controllers\TicketScrapingController::class, 'stats'])->name('scraping.stats');
});

// Ticket Purchase System
Route::middleware(['auth', 'verified'])->prefix('tickets')->name('tickets.purchase.')->group(function (): void {
    // Purchase workflow routes
    Route::get('{ticket}/purchase', [App\Http\Controllers\TicketPurchaseController::class, 'showPurchaseForm'])
        ->name('purchase');
    
    Route::post('{ticket}/purchase', [App\Http\Controllers\TicketPurchaseController::class, 'processPurchase'])
        ->middleware('ticket.purchase.validation')
        ->name('purchase.process');
    
    // Purchase result pages
    Route::get('purchase-success/{purchase}', [App\Http\Controllers\TicketPurchaseController::class, 'showSuccess'])
        ->name('purchase-success');
    
    Route::get('purchase-failed', [App\Http\Controllers\TicketPurchaseController::class, 'showFailed'])
        ->name('purchase-failed');
    
    // Purchase history and management
    Route::get('purchase-history', [App\Http\Controllers\TicketPurchaseController::class, 'purchaseHistory'])
        ->name('purchase-history');
    
    Route::get('purchase-history/export', [App\Http\Controllers\TicketPurchaseController::class, 'exportPurchaseHistory'])
        ->name('purchase-history.export');
    
    // Purchase management
    Route::patch('purchases/{purchase}/cancel', [App\Http\Controllers\TicketPurchaseController::class, 'cancelPurchase'])
        ->name('purchase.cancel');
    
    Route::get('purchases/{purchase}/details', [App\Http\Controllers\TicketPurchaseController::class, 'purchaseDetails'])
        ->name('purchase.details');
});

// Purchase Decision System
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::prefix('purchase-decisions')->name('purchase-decisions.')->group(function (): void {
        Route::get('/', [PurchaseDecisionController::class, 'index'])->name('index');
        Route::get('/select-tickets', [PurchaseDecisionController::class, 'selectTickets'])->name('select-tickets');
        Route::post('/add-to-queue/{scrapedTicket}', [PurchaseDecisionController::class, 'addToQueue'])->name('add-to-queue');
        Route::post('/{purchaseQueue}/process', [PurchaseDecisionController::class, 'processQueue'])->name('process');
        Route::delete('/{purchaseQueue}', [PurchaseDecisionController::class, 'cancelQueue'])->name('cancel');
        Route::post('/bulk-action', [PurchaseDecisionController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{purchaseQueue}', [PurchaseDecisionController::class, 'show'])->name('show');
    });
});

// Health Check Routes for Production Monitoring
Route::get('health', [HealthController::class, 'index'])->name('health.index');
Route::get('health/database', [HealthController::class, 'database'])->name('health.database');
Route::get('health/redis', [HealthController::class, 'redis'])->name('health.redis');

// Production Health Check Routes (Comprehensive Monitoring)
Route::get('health/production', [App\Http\Controllers\ProductionHealthController::class, 'comprehensive'])
    ->name('health.production');
Route::get('health/comprehensive', [App\Http\Controllers\ProductionHealthController::class, 'comprehensive'])
    ->name('health.comprehensive');

// Public Account Deletion Routes (no authentication required)
Route::prefix('account/deletion')->name('account.deletion.')->group(function (): void {
    Route::get('/confirm/{token}', [App\Http\Controllers\AccountDeletionController::class, 'confirm'])->name('confirm');
    Route::get('/cancel/{token}', [App\Http\Controllers\AccountDeletionController::class, 'showCancel'])->name('cancel.show');
    Route::post('/cancel/{token}', [App\Http\Controllers\AccountDeletionController::class, 'cancel'])->name('cancel');

    Route::get('/recovery', [App\Http\Controllers\AccountDeletionController::class, 'showRecovery'])->name('recovery.show');
    Route::post('/recovery', [App\Http\Controllers\AccountDeletionController::class, 'recover'])->name('recovery');
});

// Enhanced AJAX endpoints for ticket scraping (web-authenticated)
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/ajax/ticket-details/{id}', [App\Http\Controllers\Api\ScrapingController::class, 'getTicketDetails'])
        ->where('id', '[0-9]+')
        ->name('ajax.ticket-details');
        
    // Enhanced ticket scraping AJAX endpoints
    Route::post('/tickets/scraping/ajax-filter', [App\Http\Controllers\TicketScrapingController::class, 'ajaxFilter'])
        ->name('tickets.scraping.ajax-filter');
        
    Route::get('/tickets/scraping/search-suggestions', [App\Http\Controllers\TicketScrapingController::class, 'searchSuggestions'])
        ->name('tickets.scraping.suggestions');
        
    Route::post('/tickets/scraping/{ticket}/bookmark', [App\Http\Controllers\TicketScrapingController::class, 'toggleBookmark'])
        ->where('ticket', '[0-9]+')
        ->name('tickets.scraping.bookmark');
        
    Route::post('/tickets/scraping/export', [App\Http\Controllers\TicketScrapingController::class, 'export'])
        ->name('tickets.scraping.export');
        
    Route::get('/tickets/scraping/{ticket}/api', [App\Http\Controllers\TicketScrapingController::class, 'apiShow'])
        ->where('ticket', '[0-9]+')
        ->name('tickets.scraping.api-show');
        
    Route::get('/tickets/scraping/bookmarked', [App\Http\Controllers\TicketScrapingController::class, 'bookmarked'])
        ->name('tickets.scraping.bookmarked');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';

Route::get('/dashboard-test', function () {
    $user = \App\Models\User::where('email', 'admin@hdtickets.local')->first();
    if (!$user) {
        return response()->json(['error' => 'Admin user not found']);
    }
    
    \Auth::login($user);
    
    try {
        $analytics = app(\App\Services\AnalyticsService::class);
        $recommendations = app(\App\Services\RecommendationService::class);
        $controller = new \App\Http\Controllers\EnhancedDashboardController($analytics, $recommendations);
        
        return $controller->index();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('dashboard.test');

// UI/UX Showcase Routes
Route::get('/ui-showcase', function () {
    return view('ui-showcase');
})->name('ui.showcase');

// Enhanced Form UX Examples
Route::get('/examples/forms', function () {
    return view('examples.enhanced-form');
})->name('examples.forms');

// Accessibility Compliance Demo
Route::get('/examples/accessibility', function () {
    return view('examples.accessibility-demo');
})->name('examples.accessibility');

// Responsive Design Demo
Route::get('/examples/responsive', function () {
    return view('examples.responsive-demo');
})->name('examples.responsive');

// PWA Features Demo
Route::get('/examples/pwa', function () {
    return view('examples.pwa-demo');
})->name('examples.pwa');

// Performance Optimization Demo
Route::get('/examples/performance', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'index'])->name('examples.performance');

// Database & Cache Optimization Demo
Route::get('/examples/database-optimization', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'index'])->name('examples.database-optimization');

// Performance Demo API Endpoints
Route::prefix('api/demo')->name('api.demo.')->group(function () {
    Route::get('/sample-content', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'sampleContent']);
    Route::get('/search', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'search']);
    Route::get('/metrics', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'metrics']);
    Route::delete('/search-cache', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'clearSearchCache']);
    
    // Database optimization demo endpoints
    Route::get('/database-stats', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'getDatabaseStats']);
    Route::post('/query-demo', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'runQueryDemo']);
    Route::post('/cache-warmup', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'warmupCacheDemo']);
    Route::post('/cache-clear', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'clearCacheDemo']);
    Route::get('/query-analysis', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'getQueryAnalysis']);
});

// Enhanced Form UX Examples (authenticated access)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/form-examples', function () {
        return view('examples.enhanced-form');
    })->name('dashboard.form-examples');
    
    Route::get('/dashboard/accessibility-demo', function () {
        return view('examples.accessibility-demo');
    })->name('dashboard.accessibility-demo');
    
    Route::get('/dashboard/responsive-demo', function () {
        return view('examples.responsive-demo');
    })->name('dashboard.responsive-demo');
    
    Route::get('/dashboard/pwa-demo', function () {
        return view('examples.pwa-demo');
    })->name('dashboard.pwa-demo');
    
    Route::get('/dashboard/performance-demo', [App\Http\Controllers\Examples\PerformanceDemoController::class, 'index'])->name('dashboard.performance-demo');
    
    Route::get('/dashboard/database-optimization-demo', [App\Http\Controllers\Examples\DatabaseOptimizationDemoController::class, 'index'])->name('dashboard.database-optimization-demo');
});

