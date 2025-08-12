<?php declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;
use function ini_get;

class PerformanceMonitoringService
{
    protected array $metrics = [];

    protected array $thresholds = [];

    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('monitoring.enabled', TRUE);
        $this->thresholds = config('monitoring.thresholds', [
            'slow_query'    => 1000, // milliseconds
            'memory_usage'  => 128 * 1024 * 1024, // 128MB
            'response_time' => 2000, // milliseconds
            'cpu_usage'     => 80, // percentage
        ]);
    }

    /**
     * Start monitoring a metric
     */
    public function startTimer(string $name): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->metrics[$name] = [
            'start_time'   => microtime(TRUE),
            'start_memory' => memory_get_usage(TRUE),
            'type'         => 'timer',
        ];
    }

    /**
     * End monitoring and record metric
     */
    public function endTimer(string $name): array
    {
        if (! $this->enabled || ! isset($this->metrics[$name])) {
            return [];
        }

        $metric = $this->metrics[$name];
        $endTime = microtime(TRUE);
        $endMemory = memory_get_usage(TRUE);

        $result = [
            'name'        => $name,
            'duration'    => ($endTime - $metric['start_time']) * 1000, // Convert to milliseconds
            'memory_used' => $endMemory - $metric['start_memory'],
            'peak_memory' => memory_get_peak_usage(TRUE),
            'timestamp'   => now()->toISOString(),
        ];

        // Check thresholds and log warnings
        $this->checkThresholds($result);

        // Store metric for analysis
        $this->storeMetric($result);

        unset($this->metrics[$name]);

        return $result;
    }

    /**
     * Record a counter metric
     */
    public function increment(string $name, int $value = 1, array $tags = []): void
    {
        if (! $this->enabled) {
            return;
        }

        $key = "metrics:counter:{$name}:" . date('Y-m-d-H');
        Cache::increment($key, $value);
        Cache::expire($key, 3600 * 25); // Keep for 25 hours

        Log::debug('Counter incremented', [
            'name'  => $name,
            'value' => $value,
            'tags'  => $tags,
        ]);
    }

    /**
     * Record a gauge metric
     */
    public function gauge(string $name, float $value, array $tags = []): void
    {
        if (! $this->enabled) {
            return;
        }

        $key = "metrics:gauge:{$name}";
        Cache::put($key, [
            'value'     => $value,
            'timestamp' => now()->toISOString(),
            'tags'      => $tags,
        ], 3600);

        Log::debug('Gauge recorded', [
            'name'  => $name,
            'value' => $value,
            'tags'  => $tags,
        ]);
    }

    /**
     * Monitor database query performance
     */
    public function monitorQuery(string $sql, array $bindings, float $time): void
    {
        if (! $this->enabled) {
            return;
        }

        $timeMs = $time * 1000;

        if ($timeMs > $this->thresholds['slow_query']) {
            Log::warning('Slow query detected', [
                'sql'       => $sql,
                'bindings'  => $bindings,
                'time_ms'   => $timeMs,
                'threshold' => $this->thresholds['slow_query'],
            ]);

            $this->increment('slow_queries');
        }

        // Store query metrics
        $this->storeMetric([
            'type'      => 'database_query',
            'duration'  => $timeMs,
            'sql'       => $sql,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Monitor HTTP request performance
     */
    public function monitorRequest(string $method, string $uri, int $statusCode, float $duration): void
    {
        if (! $this->enabled) {
            return;
        }

        $durationMs = $duration * 1000;

        $this->increment('http_requests', 1, [
            'method' => $method,
            'status' => $statusCode,
            'route'  => $uri,
        ]);

        if ($durationMs > $this->thresholds['response_time']) {
            Log::warning('Slow HTTP request detected', [
                'method'      => $method,
                'uri'         => $uri,
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'threshold'   => $this->thresholds['response_time'],
            ]);

            $this->increment('slow_requests');
        }

        $this->storeMetric([
            'type'        => 'http_request',
            'method'      => $method,
            'uri'         => $uri,
            'status_code' => $statusCode,
            'duration'    => $durationMs,
            'timestamp'   => now()->toISOString(),
        ]);
    }

    /**
     * Get system metrics
     */
    public function getSystemMetrics(): array
    {
        if (! $this->enabled) {
            return [];
        }

        return [
            'memory' => [
                'usage' => memory_get_usage(TRUE),
                'peak'  => memory_get_peak_usage(TRUE),
                'limit' => ini_get('memory_limit'),
            ],
            'cpu'  => $this->getCpuUsage(),
            'disk' => [
                'total'           => disk_total_space('/'),
                'free'            => disk_free_space('/'),
                'used_percentage' => (1 - (disk_free_space('/') / disk_total_space('/'))) * 100,
            ],
            'database' => $this->getDatabaseMetrics(),
            'cache'    => $this->getCacheMetrics(),
        ];
    }

    /**
     * Get performance report
     */
    public function getPerformanceReport(int $hours = 24): array
    {
        if (! $this->enabled) {
            return [];
        }

        $startTime = now()->subHours($hours);

        return [
            'timeframe' => [
                'start' => $startTime->toISOString(),
                'end'   => now()->toISOString(),
                'hours' => $hours,
            ],
            'requests' => $this->getRequestMetrics($hours),
            'database' => $this->getDatabasePerformance($hours),
            'errors'   => $this->getErrorMetrics($hours),
            'system'   => $this->getSystemMetrics(),
            'alerts'   => $this->getPerformanceAlerts($hours),
        ];
    }

    /**
     * Enable/disable monitoring
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Check if monitoring is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Check performance thresholds
     */
    protected function checkThresholds(array $metric): void
    {
        if (isset($metric['duration']) && $metric['duration'] > $this->thresholds['response_time']) {
            Log::warning('Performance threshold exceeded', [
                'metric'    => $metric['name'],
                'duration'  => $metric['duration'],
                'threshold' => $this->thresholds['response_time'],
            ]);
        }

        if (isset($metric['memory_used']) && $metric['memory_used'] > $this->thresholds['memory_usage']) {
            Log::warning('Memory usage threshold exceeded', [
                'metric'      => $metric['name'],
                'memory_used' => $metric['memory_used'],
                'threshold'   => $this->thresholds['memory_usage'],
            ]);
        }
    }

    /**
     * Store metric for analysis
     */
    protected function storeMetric(array $metric): void
    {
        $key = 'metrics:' . date('Y-m-d-H') . ':' . $metric['type'] ?? 'general';

        try {
            $existing = Cache::get($key, []);
            $existing[] = $metric;

            // Keep only last 1000 metrics per hour
            if (count($existing) > 1000) {
                $existing = array_slice($existing, -1000);
            }

            Cache::put($key, $existing, 3600 * 25);
        } catch (Exception $e) {
            Log::error('Failed to store metric', [
                'metric' => $metric,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get CPU usage (mock implementation)
     */
    protected function getCpuUsage(): array
    {
        // This would need actual system monitoring
        return [
            'current' => rand(10, 80),
            'average' => rand(20, 60),
            'cores'   => 4,
        ];
    }

    /**
     * Get database performance metrics
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            $connections = DB::getConnections();
            $metrics = [];

            foreach ($connections as $name => $connection) {
                $metrics[$name] = [
                    'active_connections' => 1, // Would need actual monitoring
                    'slow_queries'       => Cache::get('metrics:counter:slow_queries:' . date('Y-m-d-H'), 0),
                    'total_queries'      => Cache::get('metrics:counter:database_queries:' . date('Y-m-d-H'), 0),
                ];
            }

            return $metrics;
        } catch (Exception $e) {
            Log::error('Failed to get database metrics', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get cache performance metrics
     */
    protected function getCacheMetrics(): array
    {
        try {
            return [
                'hits'     => Cache::get('metrics:counter:cache_hits:' . date('Y-m-d-H'), 0),
                'misses'   => Cache::get('metrics:counter:cache_misses:' . date('Y-m-d-H'), 0),
                'hit_rate' => $this->calculateCacheHitRate(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get cache metrics', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateCacheHitRate(): float
    {
        $hits = Cache::get('metrics:counter:cache_hits:' . date('Y-m-d-H'), 0);
        $misses = Cache::get('metrics:counter:cache_misses:' . date('Y-m-d-H'), 0);
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get request metrics for timeframe
     */
    protected function getRequestMetrics(int $hours): array
    {
        $totalRequests = 0;
        $slowRequests = 0;

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $totalRequests += Cache::get("metrics:counter:http_requests:{$hour}", 0);
            $slowRequests += Cache::get("metrics:counter:slow_requests:{$hour}", 0);
        }

        return [
            'total'            => $totalRequests,
            'slow'             => $slowRequests,
            'average_per_hour' => $hours > 0 ? $totalRequests / $hours : 0,
        ];
    }

    /**
     * Get database performance for timeframe
     */
    protected function getDatabasePerformance(int $hours): array
    {
        $totalQueries = 0;
        $slowQueries = 0;

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $totalQueries += Cache::get("metrics:counter:database_queries:{$hour}", 0);
            $slowQueries += Cache::get("metrics:counter:slow_queries:{$hour}", 0);
        }

        return [
            'total_queries'         => $totalQueries,
            'slow_queries'          => $slowQueries,
            'slow_query_percentage' => $totalQueries > 0 ? ($slowQueries / $totalQueries) * 100 : 0,
        ];
    }

    /**
     * Get error metrics for timeframe
     */
    protected function getErrorMetrics(int $hours): array
    {
        $errors = 0;

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $errors += Cache::get("metrics:counter:errors:{$hour}", 0);
        }

        return [
            'total_errors' => $errors,
            'error_rate'   => $errors / max($hours, 1),
        ];
    }

    /**
     * Get performance alerts
     */
    protected function getPerformanceAlerts(int $hours): array
    {
        // Mock implementation - would integrate with actual alerting system
        return [
            'critical' => 0,
            'warning'  => 0,
            'info'     => 0,
        ];
    }
}
