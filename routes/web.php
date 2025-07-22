<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseDecisionController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ScrapingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isAgent()) {
        return view('dashboard.agent');
    } else {
        return view('dashboard.customer');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Role-specific dashboard routes (legacy, kept for compatibility)

Route::middleware(['auth', 'verified', 'agent'])->group(function () {
    Route::get('/agent/dashboard', function () {
        return view('dashboard.agent');
    })->name('agent.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customer/dashboard', function () {
        return view('dashboard.customer');
    })->name('customer.dashboard');
});

// Admin dashboard and management routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/stats.json', [App\Http\Controllers\Admin\DashboardController::class, 'getStats']);
    Route::get('/admin/chart/status.json', [App\Http\Controllers\Admin\DashboardController::class, 'getTicketStatusChart']);
    Route::get('/admin/chart/priority.json', [App\Http\Controllers\Admin\DashboardController::class, 'getTicketPriorityChart']);
    Route::get('/admin/chart/monthly-trend.json', [App\Http\Controllers\Admin\DashboardController::class, 'getMonthlyTrend']);

    Route::resource('admin/users', App\Http\Controllers\Admin\UserManagementController::class)->names('admin.users');
    Route::patch('admin/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');

    Route::resource('admin/categories', App\Http\Controllers\Admin\CategoryManagementController::class)->names('admin.categories');
    Route::patch('admin/categories/{category}/toggle-status', [App\Http\Controllers\Admin\CategoryManagementController::class, 'toggleStatus'])->name('admin.categories.toggle-status');
    Route::post('admin/categories/reorder', [App\Http\Controllers\Admin\CategoryManagementController::class, 'reorder'])->name('admin.categories.reorder');

    // Ticket Management
    Route::prefix('admin/tickets')->name('admin.tickets.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TicketManagementController::class, 'index'])->name('index');
        Route::post('{ticket}/assign', [App\Http\Controllers\Admin\TicketManagementController::class, 'assign'])->name('assign');
        Route::post('bulk-assign', [App\Http\Controllers\Admin\TicketManagementController::class, 'bulkAssign'])->name('bulk-assign');
        Route::patch('{ticket}/status', [App\Http\Controllers\Admin\TicketManagementController::class, 'updateStatus'])->name('update-status');
        Route::patch('{ticket}/priority', [App\Http\Controllers\Admin\TicketManagementController::class, 'updatePriority'])->name('update-priority');
        Route::post('bulk-status', [App\Http\Controllers\Admin\TicketManagementController::class, 'bulkUpdateStatus'])->name('bulk-status');
        Route::patch('{ticket}/due-date', [App\Http\Controllers\Admin\TicketManagementController::class, 'setDueDate'])->name('due-date');
        Route::get('statistics', [App\Http\Controllers\Admin\TicketManagementController::class, 'getStatistics'])->name('statistics');
    });

    // Reports and Analytics
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
        Route::get('ticket-volume', [App\Http\Controllers\Admin\ReportsController::class, 'ticketVolume'])->name('ticket-volume');
        Route::get('agent-performance', [App\Http\Controllers\Admin\ReportsController::class, 'agentPerformance'])->name('agent-performance');
        Route::get('category-analysis', [App\Http\Controllers\Admin\ReportsController::class, 'categoryAnalysis'])->name('category-analysis');
        Route::get('response-time', [App\Http\Controllers\Admin\ReportsController::class, 'responseTime'])->name('response-time');
        Route::get('export', [App\Http\Controllers\Admin\ReportsController::class, 'export'])->name('export');
    });

    // System Management Routes
    Route::prefix('admin/system')->name('admin.system.')->middleware('admin:manage_system')->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::get('health', [SystemController::class, 'getHealth'])->name('health');
        Route::get('configuration', [SystemController::class, 'getConfiguration'])->name('configuration');
        Route::post('configuration', [SystemController::class, 'updateConfiguration'])->name('configuration.update');
        Route::get('logs', [SystemController::class, 'getLogs'])->name('logs');
        Route::post('cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
        Route::post('maintenance', [SystemController::class, 'runMaintenance'])->name('maintenance');
        Route::get('disk-usage', [SystemController::class, 'getDiskUsage'])->name('disk-usage');
        Route::get('database-info', [SystemController::class, 'getDatabaseInfo'])->name('database-info');
    });

    // Scraping Management Routes
    Route::prefix('admin/scraping')->name('admin.scraping.')->middleware('admin:access_scraping')->group(function () {
        Route::get('/', [ScrapingController::class, 'index'])->name('index');
        Route::get('stats', [ScrapingController::class, 'getStats'])->name('stats');
        Route::get('platforms', [ScrapingController::class, 'getPlatformStats'])->name('platforms');
        Route::get('operations', [ScrapingController::class, 'getRecentOperations'])->name('operations');
        Route::get('user-rotation', [ScrapingController::class, 'getUserRotation'])->name('user-rotation');
        Route::post('rotation-test', [ScrapingController::class, 'testRotation'])->name('rotation-test');
        Route::get('configuration', [ScrapingController::class, 'getConfig'])->name('configuration');
        Route::post('configuration', [ScrapingController::class, 'updateConfig'])->name('configuration.update');
        Route::get('performance', [ScrapingController::class, 'getPerformanceMetrics'])->name('performance');
    });

    // Activity and Health APIs
    Route::get('admin/activities/recent', function () {
        $activities = [
            [
                'id' => 1,
                'type' => 'user',
                'message' => 'New user registered: john@example.com',
                'status' => 'completed',
                'timestamp' => now()->subMinutes(5)->toISOString()
            ],
            [
                'id' => 2,
                'type' => 'ticket',
                'message' => 'Ticket #123 was assigned to Agent Smith',
                'status' => 'completed',
                'timestamp' => now()->subMinutes(15)->toISOString()
            ],
            [
                'id' => 3,
                'type' => 'config',
                'message' => 'System configuration updated',
                'status' => 'completed',
                'timestamp' => now()->subHour()->toISOString()
            ]
        ];
        return response()->json($activities);
    })->name('admin.activities.recent');
});

// Ticket Sources routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ticket-sources', App\Http\Controllers\TicketSourceController::class);
    Route::patch('ticket-sources/{ticket_source}/toggle', [App\Http\Controllers\TicketSourceController::class, 'toggle'])->name('ticket-sources.toggle');
});

// Ticket API Integration routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('ticket-api')->group(function () {
    Route::get('/', [App\Http\Controllers\TicketApiController::class, 'index'])->name('ticket-api.index');
    Route::post('/search', [App\Http\Controllers\TicketApiController::class, 'search'])->name('ticket-api.search');
    Route::post('/import', [App\Http\Controllers\TicketApiController::class, 'importEvents'])->name('ticket-api.import');
    Route::get('/test-connections', [App\Http\Controllers\TicketApiController::class, 'testConnections'])->name('ticket-api.test');
    Route::get('/event/{platform}/{eventId}', [App\Http\Controllers\TicketApiController::class, 'getEvent'])->name('ticket-api.event');
});

// API routes for ticket sources
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('ticket-sources', [App\Http\Controllers\TicketSourceController::class, 'apiIndex']);
});

// Ticket Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Main ticket resource routes
    Route::resource('tickets', App\Http\Controllers\TicketController::class);
    
    // Additional ticket routes
    Route::patch('tickets/{ticket}/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('tickets/{ticket}/priority', [App\Http\Controllers\TicketController::class, 'updatePriority'])->name('tickets.priority');
    Route::patch('tickets/{ticket}/assign', [App\Http\Controllers\TicketController::class, 'assign'])->name('tickets.assign');
    
    // Comment routes
    Route::post('tickets/{ticket}/comments', [App\Http\Controllers\TicketController::class, 'addComment'])->name('tickets.comments.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Ticket Scraping Routes
Route::middleware(['auth', 'verified'])->prefix('tickets')->name('tickets.')->group(function () {
    // Scraping dashboard and listing
    Route::get('scraping', [App\Http\Controllers\TicketScrapingController::class, 'index'])->name('scraping.index');
    Route::get('scraping/{ticket}', [App\Http\Controllers\TicketScrapingController::class, 'show'])->name('scraping.show');
    
    // Search and filtering
    Route::post('scraping/search', [App\Http\Controllers\TicketScrapingController::class, 'search'])->name('scraping.search');
    Route::get('scraping/manchester-united', [App\Http\Controllers\TicketScrapingController::class, 'manchesterUnited'])->name('scraping.manchester-united');
    Route::get('scraping/high-demand-sports', [App\Http\Controllers\TicketScrapingController::class, 'highDemandSports'])->name('scraping.high-demand-sports');
    Route::get('scraping/trending', [App\Http\Controllers\TicketScrapingController::class, 'trending'])->name('scraping.trending');
    Route::get('scraping/best-deals', [App\Http\Controllers\TicketScrapingController::class, 'bestDeals'])->name('scraping.best-deals');
    
// Purchase Decision System
Route::middleware(['auth', 'verified'])-group(function () {
    Route::prefix('purchase-decisions')-name('purchase-decisions.')-group(function () {
        Route::get('/', [PurchaseDecisionController::class, 'index'])-name('index');
        Route::get('/select-tickets', [PurchaseDecisionController::class, 'selectTickets'])-name('select-tickets');
        Route::post('/add-to-queue/{scrapedTicket}', [PurchaseDecisionController::class, 'addToQueue'])-name('add-to-queue');
        Route::post('/{purchaseQueue}/process', [PurchaseDecisionController::class, 'processQueue'])-name('process');
        Route::delete('/{purchaseQueue}', [PurchaseDecisionController::class, 'cancelQueue'])-name('cancel');
        Route::post('/bulk-action', [PurchaseDecisionController::class, 'bulkAction'])-name('bulk-action');
        Route::get('/{purchaseQueue}', [PurchaseDecisionController::class, 'show'])-name('show');
    });
});

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

require __DIR__.'/auth.php';
