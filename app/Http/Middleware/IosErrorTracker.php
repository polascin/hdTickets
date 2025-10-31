<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\UserAgentHelper;
use Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function get_class;

/**
 * iOS Error Tracker Middleware
 *
 * Comprehensive error tracking and logging for iOS devices.
 * Catches and logs all exceptions that occur during iOS requests
 * to provide visibility into iOS-specific issues.
 */
class IosErrorTracker
{
    /**
     * Handle an incoming request
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is an iOS request
        $isIOS = UserAgentHelper::isIOS($request);

        if (!$isIOS) {
            // Not an iOS device, continue without extra tracking
            return $next($request);
        }

        // Get iOS device information
        $deviceInfo = UserAgentHelper::getDeviceInfo($request);

        // Log iOS request start
        $this->logIOSRequestStart($request, $deviceInfo);

        try {
            // Process the request
            $response = $next($request);

            // Log successful iOS response
            $this->logIOSRequestComplete($request, $response, $deviceInfo);

            return $response;
        } catch (Throwable $e) {
            // Log iOS-specific error with full context
            $this->logIOSError($request, $e, $deviceInfo);

            // Re-throw the exception to let Laravel's exception handler process it
            throw $e;
        }
    }

    /**
     * Log the start of an iOS request
     */
    protected function logIOSRequestStart(Request $request, array $deviceInfo): void
    {
        Log::info('iOS request started', [
            'ios_version'    => $deviceInfo['ios_version'],
            'safari_version' => $deviceInfo['safari_version'],
            'device_type'    => $deviceInfo['device_type'],
            'path'           => $request->path(),
            'method'         => $request->method(),
            'ip'             => $request->ip(),
            'referer'        => $request->header('referer'),
            'timestamp'      => now()->toIso8601String(),
        ]);
    }

    /**
     * Log successful iOS request completion
     */
    protected function logIOSRequestComplete(Request $request, Response $response, array $deviceInfo): void
    {
        $statusCode = $response->getStatusCode();

        // Log warnings for 4xx and 5xx responses
        if ($statusCode >= 400) {
            Log::warning('iOS request completed with error status', [
                'status_code'    => $statusCode,
                'ios_version'    => $deviceInfo['ios_version'],
                'safari_version' => $deviceInfo['safari_version'],
                'device_type'    => $deviceInfo['device_type'],
                'path'           => $request->path(),
                'method'         => $request->method(),
                'ip'             => $request->ip(),
                'timestamp'      => now()->toIso8601String(),
            ]);
        } else {
            Log::debug('iOS request completed successfully', [
                'status_code' => $statusCode,
                'ios_version' => $deviceInfo['ios_version'],
                'device_type' => $deviceInfo['device_type'],
                'path'        => $request->path(),
                'timestamp'   => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Log iOS-specific error with full context
     */
    protected function logIOSError(Request $request, Throwable $e, array $deviceInfo): void
    {
        Log::error('iOS request error', [
            // Error details
            'error_message' => $e->getMessage(),
            'error_class'   => get_class($e),
            'error_file'    => $e->getFile(),
            'error_line'    => $e->getLine(),
            'error_code'    => $e->getCode(),
            'stack_trace'   => $e->getTraceAsString(),

            // iOS device information
            'ios_version'    => $deviceInfo['ios_version'],
            'safari_version' => $deviceInfo['safari_version'],
            'device_type'    => $deviceInfo['device_type'],
            'is_automated'   => $deviceInfo['is_automated'],
            'user_agent'     => UserAgentHelper::sanitise($deviceInfo['user_agent'] ?? NULL),

            // Request information
            'path'    => $request->path(),
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
            'referer' => $request->header('referer'),

            // Request headers (sanitized)
            'accept'          => $request->header('accept'),
            'accept_language' => $request->header('accept-language'),
            'accept_encoding' => $request->header('accept-encoding'),

            // Timing
            'timestamp' => now()->toIso8601String(),

            // Session information (if available)
            'session_id' => $request->session()->getId() ?? NULL,
            'user_id'    => $request->user()->id ?? NULL,

            // Additional context
            'context' => [
                'route_name'   => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
                'has_session'  => $request->hasSession(),
                'is_ajax'      => $request->ajax(),
                'is_json'      => $request->expectsJson(),
            ],
        ]);

        // Increment iOS error counter in cache for monitoring
        $this->incrementIOSErrorCounter($deviceInfo);
    }

    /**
     * Increment iOS error counter for monitoring
     */
    protected function incrementIOSErrorCounter(array $deviceInfo): void
    {
        try {
            $now = now();
            $iosVersion = $deviceInfo['ios_version'] ?? 'unknown';
            $deviceType = $deviceInfo['device_type'] ?? 'unknown';

            // Increment hourly counter
            $hourlyKey = "ios_errors:{$now->format('Y-m-d:H')}";
            Cache::increment($hourlyKey);
            Cache::put($hourlyKey . ':expires', TRUE, 7200); // 2 hours

            // Increment daily counter
            $dailyKey = "ios_errors:{$now->format('Y-m-d')}";
            Cache::increment($dailyKey);
            Cache::put($dailyKey . ':expires', TRUE, 172800); // 2 days

            // Increment counter by iOS version
            $versionKey = "ios_errors:version:{$iosVersion}:{$now->format('Y-m-d')}";
            Cache::increment($versionKey);
            Cache::put($versionKey . ':expires', TRUE, 172800); // 2 days

            // Increment counter by device type
            $deviceKey = "ios_errors:device:{$deviceType}:{$now->format('Y-m-d')}";
            Cache::increment($deviceKey);
            Cache::put($deviceKey . ':expires', TRUE, 172800); // 2 days
        } catch (Throwable $e) {
            // Don't let error counting fail the request
            Log::debug('Failed to increment iOS error counter', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
