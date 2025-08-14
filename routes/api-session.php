<?php declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Session Management API Routes
|--------------------------------------------------------------------------
|
| These routes provide API endpoints for session management including
| session extension and timeout warnings for professional authentication.
|
*/

Route::middleware(['auth:sanctum'])->group(function (): void {
    /**
     * Extend User Session
     *
     * Allows authenticated users to extend their session timeout
     * Called by the professional auth features JavaScript
     */
    Route::post('/session/extend', function (Request $request) {
        try {
            // Check if user is still authenticated
            if (! Auth::check()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Regenerate session ID for security
            $request->session()->regenerate();

            // Touch the session to extend its lifetime
            $request->session()->put('last_activity', time());

            // Log the session extension for security auditing
            Log::info('Session extended for user', [
                'user_id'    => Auth::id(),
                'email'      => Auth::user()->email,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp'  => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Session extended successfully',
                'expires_at' => now()->addMinutes(config('session.lifetime'))->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Session extension failed', [
                'user_id'   => Auth::id() ?? 'unknown',
                'error'     => $e->getMessage(),
                'ip'        => $request->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Session extension failed',
            ], 500);
        }
    });

    /**
     * Get Session Status
     *
     * Returns current session information and time remaining
     */
    Route::get('/session/status', function (Request $request) {
        if (! Auth::check()) {
            return response()->json([
                'success'       => FALSE,
                'authenticated' => FALSE,
            ], 401);
        }

        $sessionLifetime = config('session.lifetime') * 60; // Convert minutes to seconds
        $lastActivity = $request->session()->get('last_activity', time());
        $timeRemaining = $sessionLifetime - (time() - $lastActivity);

        return response()->json([
            'success'          => TRUE,
            'authenticated'    => TRUE,
            'session_lifetime' => $sessionLifetime,
            'time_remaining'   => max(0, $timeRemaining),
            'expires_at'       => now()->addSeconds(max(0, $timeRemaining))->toISOString(),
        ]);
    });

    /**
     * Check Session Health
     *
     * Lightweight endpoint to check if session is still valid
     */
    Route::get('/session/health', function (Request $request) {
        return response()->json([
            'success'       => TRUE,
            'authenticated' => Auth::check(),
            'timestamp'     => now()->toISOString(),
        ]);
    });
});

/**
 * Public endpoint for checking system status
 * Used by the support section in auth pages
 */
Route::get('/system/status', function () {
    try {
        // Basic health checks
        $dbConnected = TRUE;

        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $dbConnected = FALSE;
        }

        $cacheWorking = TRUE;

        try {
            Cache::put('health_check', 'ok', 60);
            $cacheWorking = Cache::get('health_check') === 'ok';
        } catch (Exception $e) {
            $cacheWorking = FALSE;
        }

        $overallStatus = $dbConnected && $cacheWorking;

        return response()->json([
            'status'     => $overallStatus ? 'operational' : 'degraded',
            'components' => [
                'database'   => $dbConnected ? 'operational' : 'down',
                'cache'      => $cacheWorking ? 'operational' : 'down',
                'web_server' => 'operational',
            ],
            'timestamp' => now()->toISOString(),
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status'    => 'down',
            'message'   => 'System health check failed',
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});
