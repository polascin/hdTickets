<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\DatabaseOptimizationService;
use App\Services\RedisCacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Query Performance Monitoring Middleware
 *
 * Monitors database queries in real-time and provides:
 * - Query execution time tracking
 * - Slow query detection and logging
 * - N+1 query detection
 * - Query optimization suggestions
 * - Performance metrics collection
 * - Real-time performance dashboard data
 */
class QueryPerformanceMonitoringMiddleware
{
    protected DatabaseOptimizationService $dbOptimizer;

    protected RedisCacheService $cacheService;

    protected array $queryLog = [];

    protected float $requestStartTime;

    protected int $queryCount = 0;

    protected float $totalQueryTime = 0;

    public function __construct(
        DatabaseOptimizationService $dbOptimizer,
        RedisCacheService $cacheService
    ) {
        $this->dbOptimizer = $dbOptimizer;
        $this->cacheService = $cacheService;
    }

    /**
     * Handle an incoming request
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip monitoring for certain routes or conditions
        if ($this->shouldSkipMonitoring($request)) {
            return $next($request);
        }

        $this->requestStartTime = microtime(TRUE);
        $this->startQueryLogging();

        $response = $next($request);

        $this->stopQueryLogging();
        $this->analyzeRequestPerformance($request, $response);

        return $response;
    }

    /**
     * Start monitoring database queries
     */
    protected function startQueryLogging(): void
    {
        DB::listen(function ($query) {
            $this->logQuery($query);
        });
    }

    /**
     * Stop query logging and cleanup
     */
    protected function stopQueryLogging(): void
    {
        // DB::stopListening() doesn't exist, but we can clear our log
        $this->queryLog = [];
    }

    /**
     * Log individual query with performance metrics
     */
    protected function logQuery($query): void
    {
        $executionTime = $query->time;
        $this->queryCount++;
        $this->totalQueryTime += $executionTime;

        $queryInfo = [
            'sql'        => $query->sql,
            'bindings'   => $query->bindings,
            'time'       => $executionTime,
            'timestamp'  => microtime(TRUE),
            'memory'     => memory_get_usage(TRUE),
            'connection' => $query->connectionName ?? 'default',
        ];

        $this->queryLog[] = $queryInfo;

        // Detect and log slow queries
        $slowThreshold = config('database.slow_query_threshold', 1000); // milliseconds
        if ($executionTime > $slowThreshold) {
            $this->logSlowQuery($queryInfo);
        }

        // Detect potential N+1 queries
        if ($this->detectNPlusOneQuery($queryInfo)) {
            $this->logNPlusOneQuery($queryInfo);
        }

        // Store query metrics in cache for dashboard
        $this->storeQueryMetrics($queryInfo);
    }

    /**
     * Log slow query with optimization suggestions
     */
    protected function logSlowQuery(array $queryInfo): void
    {
        Log::warning('Slow query detected', [
            'execution_time' => $queryInfo['time'],
            'sql'            => $queryInfo['sql'],
            'bindings'       => $queryInfo['bindings'],
            'memory_usage'   => $queryInfo['memory'],
            'connection'     => $queryInfo['connection'],
        ]);

        // Store slow query for analysis
        $slowQueries = Cache::get('slow_queries', []);
        $slowQueries[] = array_merge($queryInfo, [
            'detected_at' => now(),
            'route'       => request()->route()?->getName(),
            'url'         => request()->url(),
        ]);

        // Keep only last 100 slow queries
        if (count($slowQueries) > 100) {
            $slowQueries = array_slice($slowQueries, -100);
        }

        Cache::put('slow_queries', $slowQueries, 3600); // 1 hour
    }

    /**
     * Detect potential N+1 queries
     */
    protected function detectNPlusOneQuery(array $queryInfo): bool
    {
        // Simple heuristic: similar queries with different bindings executed close together
        $recentQueries = array_slice($this->queryLog, -10); // Check last 10 queries
        $similarQueries = 0;

        $currentSqlPattern = $this->extractSqlPattern($queryInfo['sql']);

        foreach ($recentQueries as $recentQuery) {
            if ($this->extractSqlPattern($recentQuery['sql']) === $currentSqlPattern) {
                $similarQueries++;
            }
        }

        // If we see more than 3 similar queries, it might be N+1
        return $similarQueries > 3;
    }

    /**
     * Extract SQL pattern for N+1 detection
     */
    protected function extractSqlPattern(string $sql): string
    {
        // Replace placeholders with generic markers for pattern matching
        return preg_replace('/\?/', '?', $sql);
    }

    /**
     * Log potential N+1 query
     */
    protected function logNPlusOneQuery(array $queryInfo): void
    {
        Log::info('Potential N+1 query detected', [
            'sql'        => $queryInfo['sql'],
            'suggestion' => 'Consider using eager loading with ->with() method',
            'route'      => request()->route()?->getName(),
        ]);

        // Store N+1 detection for dashboard
        $nPlusOneDetections = Cache::get('n_plus_one_detections', []);
        $nPlusOneDetections[] = [
            'sql_pattern' => $this->extractSqlPattern($queryInfo['sql']),
            'detected_at' => now(),
            'route'       => request()->route()?->getName(),
            'url'         => request()->url(),
            'count'       => 1,
        ];

        Cache::put('n_plus_one_detections', $nPlusOneDetections, 3600);
    }

    /**
     * Store query metrics in cache for dashboard
     */
    protected function storeQueryMetrics(array $queryInfo): void
    {
        $metrics = [
            'query_count'  => 1,
            'total_time'   => $queryInfo['time'],
            'memory_usage' => $queryInfo['memory'],
            'timestamp'    => $queryInfo['timestamp'],
        ];

        // Store in Redis with layer-specific key
        $key = 'query_metrics:' . date('Y-m-d:H:i');
        $existing = $this->cacheService->getLayer(
            RedisCacheService::LAYER_MONITORING,
            $key,
            []
        );

        // Aggregate metrics
        $aggregated = [
            'query_count'  => ($existing['query_count'] ?? 0) + 1,
            'total_time'   => ($existing['total_time'] ?? 0) + $queryInfo['time'],
            'max_time'     => max($existing['max_time'] ?? 0, $queryInfo['time']),
            'memory_peak'  => max($existing['memory_peak'] ?? 0, $queryInfo['memory']),
            'last_updated' => $queryInfo['timestamp'],
        ];

        $this->cacheService->putLayer(
            RedisCacheService::LAYER_MONITORING,
            $key,
            $aggregated,
            ['ttl' => RedisCacheService::TTL_SHORT]
        );
    }

    /**
     * Analyze overall request performance
     */
    protected function analyzeRequestPerformance(Request $request, $response): void
    {
        $requestTime = microtime(TRUE) - $this->requestStartTime;

        $performanceData = [
            'route'                 => $request->route()?->getName(),
            'method'                => $request->method(),
            'url'                   => $request->url(),
            'total_time'            => $requestTime * 1000, // Convert to milliseconds
            'query_count'           => $this->queryCount,
            'query_time'            => $this->totalQueryTime,
            'query_time_percentage' => $this->totalQueryTime > 0 ? ($this->totalQueryTime / ($requestTime * 1000)) * 100 : 0,
            'memory_peak'           => memory_get_peak_usage(TRUE),
            'response_code'         => $response->getStatusCode(),
            'timestamp'             => now(),
        ];

        // Store request performance metrics
        $this->storeRequestMetrics($performanceData);

        // Log slow requests
        $slowRequestThreshold = config('monitoring.slow_request_threshold', 2000); // 2 seconds
        if ($requestTime * 1000 > $slowRequestThreshold) {
            $this->logSlowRequest($performanceData);
        }

        // Add performance headers for debugging (only in debug mode)
        if (config('app.debug')) {
            $response->headers->set('X-Query-Count', $this->queryCount);
            $response->headers->set('X-Query-Time', number_format($this->totalQueryTime, 2) . 'ms');
            $response->headers->set('X-Request-Time', number_format($requestTime * 1000, 2) . 'ms');
            $response->headers->set('X-Memory-Peak', $this->formatBytes(memory_get_peak_usage(TRUE)));
        }
    }

    /**
     * Store request performance metrics
     */
    protected function storeRequestMetrics(array $performanceData): void
    {
        $key = 'request_metrics:' . date('Y-m-d:H');

        $existing = $this->cacheService->getLayer(
            RedisCacheService::LAYER_MONITORING,
            $key,
            []
        );

        // Aggregate hourly metrics
        $aggregated = [
            'request_count'     => ($existing['request_count'] ?? 0) + 1,
            'total_time'        => ($existing['total_time'] ?? 0) + $performanceData['total_time'],
            'total_query_count' => ($existing['total_query_count'] ?? 0) + $performanceData['query_count'],
            'total_query_time'  => ($existing['total_query_time'] ?? 0) + $performanceData['query_time'],
            'max_request_time'  => max($existing['max_request_time'] ?? 0, $performanceData['total_time']),
            'memory_peak'       => max($existing['memory_peak'] ?? 0, $performanceData['memory_peak']),
            'last_updated'      => now(),
        ];

        $this->cacheService->putLayer(
            RedisCacheService::LAYER_MONITORING,
            $key,
            $aggregated,
            ['ttl' => RedisCacheService::TTL_EXTENDED]
        );
    }

    /**
     * Log slow request with details
     */
    protected function logSlowRequest(array $performanceData): void
    {
        Log::warning('Slow request detected', $performanceData);

        // Store for dashboard analysis
        $slowRequests = Cache::get('slow_requests', []);
        $slowRequests[] = $performanceData;

        // Keep only last 50 slow requests
        if (count($slowRequests) > 50) {
            $slowRequests = array_slice($slowRequests, -50);
        }

        Cache::put('slow_requests', $slowRequests, 3600);
    }

    /**
     * Check if monitoring should be skipped for this request
     */
    protected function shouldSkipMonitoring(Request $request): bool
    {
        $skipPatterns = [
            '/health*',
            '/metrics*',
            '/_debugbar*',
            '/telescope*',
        ];

        $path = $request->path();

        foreach ($skipPatterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
