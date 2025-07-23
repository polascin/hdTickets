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
        return redirect()->route('agent.dashboard');
    } else {
        return redirect()->route('dashboard.basic');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Basic dashboard for users without admin or agent access
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/basic-dashboard', function () {
        return view('dashboard.basic');
    })->name('dashboard.basic');
});

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

// Admin routes are now handled in routes/admin.php


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

// AJAX routes for lazy loading and real-time updates
Route::middleware(['auth', 'verified'])->prefix('ajax')->name('ajax.')->group(function () {
    Route::get('tickets/load', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadTickets'])->name('tickets.load');
    Route::get('tickets/search', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'searchTickets'])->name('tickets.search');
    Route::get('tickets/load-more', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadMore'])->name('tickets.load-more');
    Route::get('dashboard/stats', [App\Http\Controllers\Ajax\TicketLazyLoadController::class, 'loadDashboardStats'])->name('dashboard.stats');
});

// Main tickets route redirects to sports event ticket scraping
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tickets', function() {
        return redirect()->route('tickets.scraping.index');
    })->name('tickets.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
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

require __DIR__.'/auth.php';
require __DIR__.'/test.php';
