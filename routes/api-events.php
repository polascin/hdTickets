<?php declare(strict_types=1);

use App\Http\Controllers\Api\EventMonitoringController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Event Monitoring API Routes
|--------------------------------------------------------------------------
|
| These routes provide API endpoints for monitoring the event-driven
| architecture, including event store statistics, projection status,
| processing failures, and administrative operations.
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('events')->group(function (): void {
    // Overview and statistics
    Route::get('/overview', [EventMonitoringController::class, 'overview']);
    Route::get('/statistics', [EventMonitoringController::class, 'statistics']);

    // Event monitoring
    Route::get('/recent', [EventMonitoringController::class, 'recentEvents']);

    // Projection management
    Route::get('/projections', [EventMonitoringController::class, 'projections']);
    Route::post('/projections/{projectionName}/rebuild', [EventMonitoringController::class, 'rebuildProjection']);

    // Failure management
    Route::get('/failures', [EventMonitoringController::class, 'failures']);
    Route::post('/failures/{failureId}/resolve', [EventMonitoringController::class, 'resolveFailure']);
});
