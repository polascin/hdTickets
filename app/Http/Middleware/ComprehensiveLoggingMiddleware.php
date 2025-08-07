<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class ComprehensiveLoggingMiddleware
{
    protected $activityLogger;
    protected $startTime;
    protected $queries = [];

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): BaseResponse
    {
        // Start timing
        $this->startTime = microtime(true);
        
        // Start database query logging
        $this->startDatabaseLogging();
        
        // Log API access
        $this->activityLogger->logApiAccess($request, [
            'request_headers' => $this->getSafeHeaders($request),
            'content_type' => $request->header('content-type'),
        ]);

        try {
            $response = $next($request);
            
            // Log successful response
            $this->logResponse($request, $response, 'success');
            
            return $response;
        } catch (\Throwable $e) {
            // Log error response
            $this->activityLogger->logCriticalError($e, [
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'request_params' => $request->all(),
            ]);
            
            throw $e;
        } finally {
            // Stop database query logging
            $this->stopDatabaseLogging();
            
            // Log performance metrics
            $this->logPerformanceMetrics($request);
        }
    }

    /**
     * Start database query logging
     */
    protected function startDatabaseLogging(): void
    {
        $this->queries = [];
        
        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time / 1000, // Convert to seconds
            ];
        });
    }

    /**
     * Stop database query logging and log queries
     */
    protected function stopDatabaseLogging(): void
    {
        foreach ($this->queries as $query) {
            $this->activityLogger->logDatabaseQuery(
                $query['sql'],
                $query['bindings'],
                $query['time'],
                ['query_context' => 'api_request']
            );
        }
    }

    /**
     * Log response details
     */
    protected function logResponse(Request $request, BaseResponse $response, string $status): void
    {
        $context = [
            'status' => $status,
            'status_code' => $response->getStatusCode(),
            'response_size_bytes' => strlen($response->getContent()),
            'content_type' => $response->headers->get('content-type'),
            'cache_control' => $response->headers->get('cache-control'),
        ];

        // Log based on status code
        if ($response->getStatusCode() >= 400) {
            Log::channel('monitoring')->warning('API Error Response', array_merge([
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl(),
                'user_id' => auth()->id(),
            ], $context));
        } else {
            Log::channel('ticket_apis')->info('API Success Response', $context);
        }
    }

    /**
     * Log performance metrics
     */
    protected function logPerformanceMetrics(Request $request): void
    {
        $endTime = microtime(true);
        $duration = $endTime - $this->startTime;
        $memoryUsage = memory_get_peak_usage(true);
        $queryCount = count($this->queries);
        $totalQueryTime = array_sum(array_column($this->queries, 'time'));

        $metrics = [
            'request_duration_ms' => round($duration * 1000, 2),
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'query_count' => $queryCount,
            'total_query_time_ms' => round($totalQueryTime * 1000, 2),
            'average_query_time_ms' => $queryCount > 0 ? round(($totalQueryTime / $queryCount) * 1000, 2) : 0,
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'user_id' => auth()->id(),
        ];

        // Determine if performance is concerning
        $isSlowRequest = $duration > 2.0; // More than 2 seconds
        $hasSlowQueries = $totalQueryTime > 1.0; // More than 1 second in queries
        $hasManyQueries = $queryCount > 20; // More than 20 queries

        if ($isSlowRequest || $hasSlowQueries || $hasManyQueries) {
            Log::channel('performance')->warning('Slow API Request Detected', $metrics);
        } else {
            Log::channel('performance')->info('API Request Performance', $metrics);
        }
    }

    /**
     * Get safe headers (excluding sensitive information)
     */
    protected function getSafeHeaders(Request $request): array
    {
        $excludeHeaders = [
            'authorization',
            'cookie',
            'set-cookie',
            'x-api-key',
            'x-auth-token',
        ];

        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            if (!in_array(strtolower($key), $excludeHeaders)) {
                $headers[$key] = is_array($values) ? implode(', ', $values) : $values;
            }
        }

        return $headers;
    }
}
