<?php declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

use function get_class;

class ActivityLogger
{
    protected static $performanceTimings = [];

    protected static $requestId;

    protected static $criticalErrorThreshold = 5; // Critical errors per minute

    public function __construct()
    {
        if (! self::$requestId) {
            self::$requestId = uniqid('req_', TRUE);
        }
    }

    /**
     * Start performance timing for a specific operation
     */
    public function startTiming(string $operation): void
    {
        self::$performanceTimings[$operation] = [
            'start'        => microtime(TRUE),
            'memory_start' => memory_get_usage(TRUE),
        ];
    }

    /**
     * End performance timing and log if threshold exceeded
     */
    public function endTiming(string $operation, float $warningThreshold = 1.0): void
    {
        if (! isset(self::$performanceTimings[$operation])) {
            return;
        }

        $timing = self::$performanceTimings[$operation];
        $duration = microtime(TRUE) - $timing['start'];
        $memoryUsed = memory_get_usage(TRUE) - $timing['memory_start'];

        $context = [
            'operation'      => $operation,
            'duration_ms'    => round($duration * 1000, 2),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'request_id'     => self::$requestId,
        ];

        if ($duration > $warningThreshold) {
            Log::channel('performance')->warning('Slow operation detected', $context);
        } else {
            Log::channel('performance')->info('Operation completed', $context);
        }

        unset(self::$performanceTimings[$operation]);
    }

    /**
     * Log API endpoint access with detailed context
     */
    public function logApiAccess(Request $request, array $context = []): void
    {
        $user = Auth::user();

        $logData = [
            'request_id'         => self::$requestId,
            'timestamp'          => Carbon::now()->toISOString(),
            'method'             => $request->method(),
            'url'                => $request->fullUrl(),
            'route_name'         => $request->route() ? $request->route()->getName() : NULL,
            'user_id'            => $user ? $user->id : NULL,
            'user_email'         => $user ? $user->email : 'guest',
            'user_role'          => $user ? $user->role : NULL,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'query_params'       => $request->query(),
            'request_size_bytes' => mb_strlen($request->getContent()),
            'context'            => $context,
        ];

        Log::channel('ticket_apis')->info('API Access', $logData);
    }

    /**
     * Log database query with performance metrics
     */
    public function logDatabaseQuery(string $query, array $bindings = [], ?float $duration = NULL, array $context = []): void
    {
        $logData = [
            'request_id'    => self::$requestId,
            'timestamp'     => Carbon::now()->toISOString(),
            'query'         => $query,
            'bindings'      => $bindings,
            'duration_ms'   => $duration ? round($duration * 1000, 2) : NULL,
            'affected_rows' => $context['affected_rows'] ?? NULL,
            'context'       => $context,
        ];

        $channel = ($duration && $duration > 1.0) ? 'performance' : 'ticket_apis';
        $level = ($duration && $duration > 1.0) ? 'warning' : 'debug';

        Log::channel($channel)->log($level, 'Database Query', $logData);
    }

    /**
     * Log JavaScript initialization and errors
     */
    public function logJavaScriptEvent(string $event, array $context = []): void
    {
        $logData = [
            'request_id' => self::$requestId,
            'timestamp'  => Carbon::now()->toISOString(),
            'event'      => $event,
            'url'        => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'context'    => $context,
        ];

        Log::channel('monitoring')->info('JavaScript Event', $logData);
    }

    /**
     * Log WebSocket connection events
     */
    public function logWebSocketEvent(string $event, array $context = []): void
    {
        $user = Auth::user();

        $logData = [
            'request_id' => self::$requestId,
            'timestamp'  => Carbon::now()->toISOString(),
            'event'      => $event,
            'user_id'    => $user ? $user->id : NULL,
            'ip_address' => request()->ip(),
            'context'    => $context,
        ];

        Log::channel('monitoring')->info('WebSocket Event', $logData);
    }

    /**
     * Log critical errors with admin notification
     */
    public function logCriticalError(Throwable $exception, array $context = [], bool $notifyAdmin = TRUE): void
    {
        $user = Auth::user();

        $logData = [
            'request_id'    => self::$requestId,
            'timestamp'     => Carbon::now()->toISOString(),
            'error_class'   => get_class($exception),
            'error_message' => $exception->getMessage(),
            'error_file'    => $exception->getFile(),
            'error_line'    => $exception->getLine(),
            'stack_trace'   => $exception->getTraceAsString(),
            'user_id'       => $user ? $user->id : NULL,
            'user_email'    => $user ? $user->email : 'guest',
            'url'           => request()->fullUrl(),
            'method'        => request()->method(),
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'context'       => $context,
        ];

        Log::channel('critical_alerts')->error('Critical Error', $logData);

        if ($notifyAdmin) {
            $this->checkAndNotifyAdmin($logData);
        }
    }

    /**
     * Log admin activity
     */
    public function logAdminActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();

        $logData = [
            'request_id'  => self::$requestId,
            'timestamp'   => Carbon::now()->toISOString(),
            'user_id'     => $user ? $user->id : NULL,
            'user_email'  => $user ? $user->email : 'system',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'context'     => $context,
        ];

        Log::channel('audit')->info('Admin Activity: ' . $action, $logData);
    }

    /**
     * Log system activity
     */
    public function logSystemActivity(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'action'      => $action,
            'description' => $description,
            'context'     => $context,
        ];

        Log::channel('single')->info('System Activity: ' . $action, $logData);
    }

    /**
     * Log user activity
     */
    public function logUserActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();

        $logData = [
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'user_id'     => $user ? $user->id : NULL,
            'user_email'  => $user ? $user->email : 'guest',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'context'     => $context,
        ];

        Log::channel('single')->info('User Activity: ' . $action, $logData);
    }

    /**
     * Log ticket monitoring activity
     */
    public function logTicketActivity(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'action'      => $action,
            'description' => $description,
            'context'     => $context,
        ];

        Log::channel('single')->info('Ticket Activity: ' . $action, $logData);
    }

    /**
     * Log security-related activity
     */
    public function logSecurityActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();

        $logData = [
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'user_id'     => $user ? $user->id : NULL,
            'user_email'  => $user ? $user->email : 'unknown',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'context'     => $context,
        ];

        Log::channel('single')->warning('Security Activity: ' . $action, $logData);
    }

    /**
     * Log error activity
     */
    public function logError(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'action'      => $action,
            'description' => $description,
            'context'     => $context,
        ];

        Log::channel('single')->error('Error Activity: ' . $action, $logData);
    }

    /**
     * Check if admin notification is needed and send it
     */
    protected function checkAndNotifyAdmin(array $errorData): void
    {
        $cacheKey = 'critical_errors_count_' . now()->format('Y-m-d-H-i');
        $errorCount = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $errorCount, now()->addMinutes(5));

        if ($errorCount >= self::$criticalErrorThreshold) {
            // Send admin notification (implement as needed)
            Log::channel('critical_alerts')->emergency('Admin Notification: Critical Error Threshold Exceeded', [
                'errors_per_minute' => $errorCount,
                'threshold'         => self::$criticalErrorThreshold,
                'latest_error'      => $errorData,
                'timestamp'         => Carbon::now()->toISOString(),
            ]);
        }
    }

    /**
     * Get formatted log entry
     */
    private function formatLogEntry(string $level, string $action, string $description, array $context = []): array
    {
        $user = Auth::user();

        return [
            'level'       => $level,
            'timestamp'   => Carbon::now()->toDateTimeString(),
            'user_id'     => $user ? $user->id : NULL,
            'user_email'  => $user ? $user->email : 'system',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'context'     => $context,
        ];
    }
}
