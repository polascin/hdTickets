<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TicketController;
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
    
    // Agent and Admin routes
    Route::middleware([CheckApiRole::class . ':agent,admin'])->group(function () {
        // Routes that require agent or admin role
        // Most ticket operations are available to agents and admins
    });
});
