<?php declare(strict_types=1);

/**
 * HD Tickets Health Check Routes
 * Sports Events Entry Tickets Monitoring System
 *
 * Routes for health checks and system status monitoring
 */

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for deployment monitoring, load balancer health
| checks, and system status verification. They should be accessible
| without authentication for operational monitoring.
|
*/

// Basic health check for load balancers (lightweight, fast response)
Route::get('/health', [HealthCheckController::class, 'basic'])
    ->name('health.basic')
    ->withoutMiddleware(['auth', 'throttle']);

// Detailed health check with comprehensive system status
Route::get('/health/detailed', [HealthCheckController::class, 'detailed'])
    ->name('health.detailed')
    ->withoutMiddleware(['auth'])
    ->middleware(['throttle:health']);

// Application status monitoring
Route::get('/application/status', [HealthCheckController::class, 'applicationStatus'])
    ->name('application.status')
    ->withoutMiddleware(['auth'])
    ->middleware(['throttle:health']);

// Legacy aliases for backward compatibility
Route::get('/status', [HealthCheckController::class, 'basic'])
    ->name('status.basic');

Route::get('/ping', function () {
    return response()->json(['status' => 'pong', 'timestamp' => now()->toISOString()]);
})->name('ping');

/*
|--------------------------------------------------------------------------
| Monitoring & Metrics Routes
|--------------------------------------------------------------------------
|
| Additional routes for monitoring sports events ticket system
| performance and business metrics
|
*/

// Sports events system metrics
Route::get('/metrics/sports-events', function () {
    try {
        $metrics = [
            'sports_events' => [
                'total_events'    => DB::table('sports_events')->count(),
                'upcoming_events' => DB::table('sports_events')->where('event_date', '>=', now())->count(),
                'events_today'    => DB::table('sports_events')->whereDate('event_date', today())->count(),
            ],
            'ticket_listings' => [
                'total_listings'             => DB::table('ticket_listings')->count(),
                'available_listings'         => DB::table('ticket_listings')->where('is_available', TRUE)->count(),
                'listings_updated_last_hour' => DB::table('ticket_listings')->where('updated_at', '>=', now()->subHour())->count(),
            ],
            'user_activity' => [
                'total_users'            => DB::table('users')->count(),
                'active_alerts'          => DB::table('user_alerts')->where('is_active', TRUE)->count(),
                'alerts_triggered_today' => DB::table('alert_triggers')->whereDate('triggered_at', today())->count(),
            ],
            'system_activity' => [
                'scraping_jobs_last_hour'      => DB::table('scraping_logs')->where('created_at', '>=', now()->subHour())->count(),
                'successful_scrapes_last_hour' => DB::table('scraping_logs')->where('created_at', '>=', now()->subHour())->where('status', 'success')->count(),
            ],
        ];

        return response()->json([
            'status'    => 'success',
            'timestamp' => now()->toISOString(),
            'metrics'   => $metrics,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Failed to collect metrics: ' . $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
})->name('metrics.sports-events')
    ->withoutMiddleware(['auth'])
    ->middleware(['throttle:metrics']);

// System performance metrics
Route::get('/metrics/performance', function () {
    try {
        $metrics = [
            'response_times' => [
                'database' => $this->measureDatabaseResponseTime(),
                'cache'    => $this->measureCacheResponseTime(),
            ],
            'system_resources' => [
                'memory_usage' => [
                    'current_mb' => round(memory_get_usage(TRUE) / 1024 / 1024, 2),
                    'peak_mb'    => round(memory_get_peak_usage(TRUE) / 1024 / 1024, 2),
                    'limit'      => ini_get('memory_limit'),
                ],
                'disk_usage' => [
                    'free_gb'  => round(disk_free_space(storage_path()) / 1024 / 1024 / 1024, 2),
                    'total_gb' => round(disk_total_space(storage_path()) / 1024 / 1024 / 1024, 2),
                ],
            ],
            'queue_stats' => [
                'pending_jobs' => Queue::size(),
                'failed_jobs'  => DB::table('failed_jobs')->count(),
            ],
        ];

        return response()->json([
            'status'    => 'success',
            'timestamp' => now()->toISOString(),
            'metrics'   => $metrics,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status'    => 'error',
            'message'   => 'Failed to collect performance metrics: ' . $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
})->name('metrics.performance')
    ->withoutMiddleware(['auth'])
    ->middleware(['throttle:metrics']);

// Application readiness check (for Kubernetes-style deployments)
Route::get('/ready', function () {
    try {
        // Check if application is ready to serve requests
        DB::connection()->getPdo();
        Cache::get('test', 'default');

        return response()->json([
            'status'    => 'ready',
            'timestamp' => now()->toISOString(),
            'checks'    => [
                'database' => 'ok',
                'cache'    => 'ok',
            ],
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status'    => 'not_ready',
            'message'   => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 503);
    }
})->name('readiness')
    ->withoutMiddleware(['auth']);

// Application liveness check (for Kubernetes-style deployments)
Route::get('/live', function () {
    return response()->json([
        'status'    => 'alive',
        'timestamp' => now()->toISOString(),
        'service'   => 'HD Tickets Sports Events Monitoring',
        'version'   => config('app.version', '1.0.0'),
    ]);
})->name('liveness')
    ->withoutMiddleware(['auth']);

/*
|--------------------------------------------------------------------------
| Helper Functions for Metrics
|--------------------------------------------------------------------------
*/

if (! function_exists('measureDatabaseResponseTime')) {
    function measureDatabaseResponseTime()
    {
        $start = microtime(TRUE);

        try {
            DB::select('SELECT 1');

            return round((microtime(TRUE) - $start) * 1000, 2);
        } catch (Exception $e) {
            return;
        }
    }
}

if (! function_exists('measureCacheResponseTime')) {
    function measureCacheResponseTime()
    {
        $start = microtime(TRUE);

        try {
            $key = 'perf_test_' . uniqid();
            Cache::put($key, 'test', 60);
            Cache::get($key);
            Cache::forget($key);

            return round((microtime(TRUE) - $start) * 1000, 2);
        } catch (Exception $e) {
            return;
        }
    }
}
