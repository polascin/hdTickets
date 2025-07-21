<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FunZoneController;
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
            'version' => '1.0',
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
    
    // Ticket comments
    Route::post('/tickets/{ticket:uuid}/comments', [CommentController::class, 'store']);
    
    // Attachments
    Route::post('/attachments', [AttachmentController::class, 'store']);
    Route::get('/attachments/{attachment:uuid}/download', [AttachmentController::class, 'download'])
        ->name('api.attachments.download');
    Route::delete('/attachments/{attachment:uuid}', [AttachmentController::class, 'destroy']);
    
    // Admin-only routes
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
    
    // FunZone routes
    Route::prefix('funzone')->middleware([ApiRateLimit::class . ':scraping,30,1'])->group(function () {
        Route::post('/search', [FunZoneController::class, 'search']);
        Route::post('/event-details', [FunZoneController::class, 'getEventDetails']);
        Route::get('/stats', [FunZoneController::class, 'stats']);
        
        Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
            Route::post('/import', [FunZoneController::class, 'import']);
            Route::post('/import-urls', [FunZoneController::class, 'importUrls']);
        });
    });
    
    // Agent and Admin routes
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
