<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseDecisionController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ScrapingController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Home route
Route::get('/', [App\Http\Controllers\HomeController::class, 'welcome'])->name('home');

// Role-based dashboard routing after login
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Main dashboard for customers and fallback
Route::get('/customer-dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('customer.dashboard');

// Agent dashboard routes - using proper controller
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/agent-dashboard', [App\Http\Controllers\AgentDashboardController::class, 'index'])
        ->name('agent.dashboard');
});

// Basic dashboard for users without admin or agent access
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/basic-dashboard', function () {
        return view('dashboard.basic');
    })->name('dashboard.basic');
});

// Admin routes are now handled in routes/admin.php


// Ticket Sources routes
Route::middleware(['auth', 'verified'])->group(function () {
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

// Ticket API Integration routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\AdminMiddleware::class])->prefix('ticket-api')->group(function () {
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

// AJAX routes for lazy loading and real-time updates
Route::middleware(['auth', 'verified', 'throttle:60,1'])->prefix('ajax')->name('ajax.')->group(function () {
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
});

// Main tickets route redirects to sports event ticket scraping
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tickets', function() {
        return redirect()->route('tickets.scraping.index');
    })->name('tickets.redirect');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
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

// Purchase Decision System
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('purchase-decisions')->name('purchase-decisions.')->group(function () {
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

// WebSocket Testing Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('websocket-test', function () {
        return view('websocket-test');
    })->name('websocket.test');
});

// Dashboard Widgets Demo
Route::get('dashboard-widgets-demo', function () {
    return view('dashboard-widgets-demo');
})->name('dashboard.widgets.demo');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/test.php';
