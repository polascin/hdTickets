<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\RefreshController;

/*
|--------------------------------------------------------------------------
| Background Sync API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for background sync and auto-refresh functionality.
| These routes handle data synchronization for PWA offline capabilities.
|
*/

Route::middleware(['auth:sanctum', 'throttle:sync'])->prefix('sync')->group(function () {
    // Background sync endpoints
    Route::post('/alerts', [SyncController::class, 'syncAlerts'])->name('sync.alerts');
    Route::post('/prices', [SyncController::class, 'syncPrices'])->name('sync.prices');
    Route::post('/preferences', [SyncController::class, 'syncPreferences'])->name('sync.preferences');
    Route::post('/purchases', [SyncController::class, 'syncPurchases'])->name('sync.purchases');
    Route::post('/watchlist', [SyncController::class, 'syncWatchlist'])->name('sync.watchlist');
    Route::post('/analytics', [SyncController::class, 'syncAnalytics'])->name('sync.analytics');
    
    // Bulk sync endpoint
    Route::post('/bulk', [SyncController::class, 'syncBulk'])->name('sync.bulk');
    
    // Sync status and conflicts
    Route::get('/status', [SyncController::class, 'getSyncStatus'])->name('sync.status');
    Route::post('/conflicts/resolve', [SyncController::class, 'resolveConflicts'])->name('sync.conflicts.resolve');
});

Route::middleware(['auth:sanctum', 'throttle:refresh'])->prefix('refresh')->group(function () {
    // Auto-refresh endpoints
    Route::get('/ticket-prices', [RefreshController::class, 'getTicketPrices'])->name('refresh.ticket-prices');
    Route::get('/ticket-alerts', [RefreshController::class, 'getTicketAlerts'])->name('refresh.ticket-alerts');
    Route::get('/watchlist', [RefreshController::class, 'getWatchlist'])->name('refresh.watchlist');
    Route::get('/dashboard', [RefreshController::class, 'getDashboard'])->name('refresh.dashboard');
    Route::get('/analytics', [RefreshController::class, 'getAnalytics'])->name('refresh.analytics');
    Route::get('/notifications', [RefreshController::class, 'getNotifications'])->name('refresh.notifications');
    
    // Conditional refresh endpoints with last-modified support
    Route::get('/conditional/ticket-prices', [RefreshController::class, 'getTicketPricesConditional'])->name('refresh.conditional.ticket-prices');
    Route::get('/conditional/alerts', [RefreshController::class, 'getAlertsConditional'])->name('refresh.conditional.alerts');
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Sync queue management
    Route::get('/queue', [SyncController::class, 'getSyncQueue'])->name('sync.queue');
    Route::post('/queue/clear', [SyncController::class, 'clearSyncQueue'])->name('sync.queue.clear');
    Route::delete('/queue/{id}', [SyncController::class, 'removeSyncQueueItem'])->name('sync.queue.remove');
    
    // Sync statistics and health
    Route::get('/stats', [SyncController::class, 'getSyncStats'])->name('sync.stats');
    Route::get('/health', [SyncController::class, 'getSyncHealth'])->name('sync.health');
});
