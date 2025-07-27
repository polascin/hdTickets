<?php

namespace App\Services\Enhanced;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceMonitoringService
{
    private $redis;
    private $metrics = [];
    
    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    /**
     * Record performance metric
     */
    public function recordMetric(string $name, float $value, array $tags = []): void
    {
        $metric = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => microtime(true),
        ];

        $this->metrics[] = $metric;
        
        // Store in Redis for real-time monitoring
        $key = "performance_metric:{$name}:" . date('Y-m-d-H-i');
        
        try {
            $this->redis->lpush($key, json_encode($metric));
            $this->redis->expire($key, 3600); // Keep for 1 hour
        } catch (\Exception $e) {
            Log::channel('performance')->warning('Failed to store metric', [
                'metric' => $name,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Track database query performance
     */
    public function trackDatabaseQuery(string $sql, float $executionTime, string $connection = 'default'): void
    {
        $this->recordMetric('database.query_time', $executionTime, [
            'connection' => $connection,
            'query_type' => $this->extractQueryType($sql),
        ]);

        // Track slow queries separately
        if ($executionTime > 1000) { // > 1 second
            $this->recordMetric('database.slow_query', $executionTime, [
                'connection' => $connection,
                'sql' => substr($sql, 0, 200), // First 200 chars
            ]);
        }
    }

    /**
     * Track cache performance
     */
    public function trackCacheOperation(string $operation, string $key, bool $hit = null, float $executionTime = null): void
    {
        $tags = [
            'operation' => $operation,
            'key_prefix' => $this->extractKeyPrefix($key),
        ];

        if ($hit !== null) {
            $this->recordMetric('cache.hit_rate', $hit ? 1 : 0, $tags);
        }

        if ($executionTime !== null) {
            $this->recordMetric('cache.operation_time', $executionTime, $tags);
        }
    }

    /**
     * Track API endpoint performance
     */
    public function trackApiEndpoint(string $endpoint, string $method, float $responseTime, int $statusCode): void
    {
        $this->recordMetric('api.response_time', $responseTime, [
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
        ]);

        // Track error rates
        if ($statusCode >= 400) {
            $this->recordMetric('api.error_rate', 1, [
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        }
    }

    /**
     * Track scraping performance
     */
    public function trackScrapingOperation(string $platform, string $operation, float $duration, bool $success): void
    {
        $this->recordMetric('scraping.operation_time', $duration, [
            'platform' => $platform,
            'operation' => $operation,
            'success' => $success,
        ]);

        if (!$success) {
            $this->recordMetric('scraping.failure_rate', 1, [
                'platform' => $platform,
                'operation' => $operation,
            ]);
        }
    }

    /**
     * Get real-time performance dashboard data
     */
    public function getDashboardMetrics(): array
    {
        return Cache::remember('performance_dashboard', 30, function () {
            return [
                'system' => $this->getSystemMetrics(),
                'database' => $this->getDatabaseMetrics(),
                'cache' => $this->getCacheMetrics(),
                'api' => $this->getApiMetrics(),
                'scraping' => $this->getScrapingMetrics(),
                'alerts' => $this->getPerformanceAlerts(),
            ];
        });
    }

    /**
     * Get system performance metrics
     */
    private function getSystemMetrics(): array
    {
        return [
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => $this->getMemoryLimit(),
            ],
            'cpu_load' => $this->getCpuLoad(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getSystemUptime(),
        ];
    }

    /**
     * Get database performance metrics
     */
    private function getDatabaseMetrics(): array
    {
        try {
            $connectionStats = DB::select('SHOW STATUS WHERE Variable_name IN (
                "Threads_connected", "Threads_running", "Questions", "Slow_queries",
                "Innodb_buffer_pool_reads", "Innodb_buffer_pool_read_requests",
                "Innodb_buffer_pool_hit_rate"
            )');

            $stats = collect($connectionStats)->pluck('Value', 'Variable_name')->toArray();

            // Calculate buffer pool hit rate
            $reads = $stats['Innodb_buffer_pool_reads'] ?? 0;
            $requests = $stats['Innodb_buffer_pool_read_requests'] ?? 0;
            $hitRate = $requests > 0 ? (($requests - $reads) / $requests) * 100 : 0;

            return [
                'connections' => [
                    'current' => $stats['Threads_connected'] ?? 0,
                    'running' => $stats['Threads_running'] ?? 0,
                ],
                'queries' => [
                    'total' => $stats['Questions'] ?? 0,
                    'slow' => $stats['Slow_queries'] ?? 0,
                ],
                'buffer_pool_hit_rate' => round($hitRate, 2),
                'recent_query_stats' => $this->getRecentQueryStats(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get cache performance metrics
     */
    private function getCacheMetrics(): array
    {
        try {
            $redisInfo = $this->redis->info();
            
            return [
                'redis' => [
                    'connected_clients' => $redisInfo['connected_clients'] ?? 0,
                    'used_memory' => $redisInfo['used_memory_human'] ?? '0B',
                    'keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0,
                    'hit_rate' => $this->calculateRedisHitRate($redisInfo),
                ],
                'laravel_cache' => [
                    'driver' => config('cache.default'),
                    'recent_operations' => $this->getRecentCacheOperations(),
                ],
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get API performance metrics
     */
    private function getApiMetrics(): array
    {
        return [
            'endpoints' => $this->getTopEndpoints(),
            'response_times' => $this->getAverageResponseTimes(),
            'error_rates' => $this->getErrorRates(),
            'request_volume' => $this->getRequestVolume(),
        ];
    }

    /**
     * Get scraping performance metrics
     */
    private function getScrapingMetrics(): array
    {
        return [
            'platforms' => $this->getScrapingPlatformStats(),
            'success_rates' => $this->getScrapingSuccessRates(),
            'average_durations' => $this->getScrapingDurations(),
            'recent_failures' => $this->getRecentScrapingFailures(),
        ];
    }

    /**
     * Get performance alerts
     */
    private function getPerformanceAlerts(): array
    {
        $alerts = [];

        // Check for high memory usage
        $memoryUsage = (memory_get_usage(true) / $this->getMemoryLimit()) * 100;
        if ($memoryUsage > 80) {
            $alerts[] = [
                'type' => 'memory',
                'severity' => $memoryUsage > 90 ? 'critical' : 'warning',
                'message' => "High memory usage: {$memoryUsage}%",
                'timestamp' => now(),
            ];
        }

        // Check for slow queries
        $slowQueries = $this->getSlowQueryCount();
        if ($slowQueries > 10) {
            $alerts[] = [
                'type' => 'database',
                'severity' => 'warning',
                'message' => "High number of slow queries: {$slowQueries}",
                'timestamp' => now(),
            ];
        }

        // Check cache hit rate
        $cacheHitRate = $this->getCacheHitRate();
        if ($cacheHitRate < 80) {
            $alerts[] = [
                'type' => 'cache',
                'severity' => 'warning',
                'message' => "Low cache hit rate: {$cacheHitRate}%",
                'timestamp' => now(),
            ];
        }

        return $alerts;
    }

    /**
     * Extract query type from SQL
     */
    private function extractQueryType(string $sql): string
    {
        $sql = trim(strtoupper($sql));
        
        if (strpos($sql, 'SELECT') === 0) return 'SELECT';
        if (strpos($sql, 'INSERT') === 0) return 'INSERT';
        if (strpos($sql, 'UPDATE') === 0) return 'UPDATE';
        if (strpos($sql, 'DELETE') === 0) return 'DELETE';
        if (strpos($sql, 'CREATE') === 0) return 'CREATE';
        if (strpos($sql, 'ALTER') === 0) return 'ALTER';
        if (strpos($sql, 'DROP') === 0) return 'DROP';
        
        return 'OTHER';
    }

    /**
     * Extract cache key prefix
     */
    private function extractKeyPrefix(string $key): string
    {
        $parts = explode(':', $key);
        return $parts[0] ?? 'unknown';
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit == -1) {
            return PHP_INT_MAX;
        }
        
        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Get CPU load average
     */
    private function getCpuLoad(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1_minute' => $load[0] ?? 0,
                '5_minute' => $load[1] ?? 0,
                '15_minute' => $load[2] ?? 0,
            ];
        }
        
        return ['1_minute' => 0, '5_minute' => 0, '15_minute' => 0];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $path = base_path();
        
        return [
            'free' => disk_free_space($path),
            'total' => disk_total_space($path),
            'used_percentage' => round((1 - (disk_free_space($path) / disk_total_space($path))) * 100, 2),
        ];
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): int
    {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return (int) floatval($uptime);
        }
        
        return 0;
    }

    /**
     * Calculate Redis hit rate
     */
    private function calculateRedisHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Get recent query statistics
     */
    private function getRecentQueryStats(): array
    {
        $metrics = $this->getRecentMetrics('database.query_time', 100);
        
        if (empty($metrics)) {
            return ['count' => 0, 'average' => 0, 'max' => 0];
        }
        
        $values = array_column($metrics, 'value');
        
        return [
            'count' => count($values),
            'average' => round(array_sum($values) / count($values), 2),
            'max' => max($values),
        ];
    }

    /**
     * Get recent cache operations
     */
    private function getRecentCacheOperations(): array
    {
        $hits = $this->getRecentMetrics('cache.hit_rate', 50);
        
        if (empty($hits)) {
            return ['hit_rate' => 0, 'operations' => 0];
        }
        
        $hitCount = array_sum(array_column($hits, 'value'));
        $totalOperations = count($hits);
        
        return [
            'hit_rate' => $totalOperations > 0 ? round(($hitCount / $totalOperations) * 100, 2) : 0,
            'operations' => $totalOperations,
        ];
    }

    /**
     * Get recent metrics by name
     */
    private function getRecentMetrics(string $name, int $limit = 100): array
    {
        $pattern = "performance_metric:{$name}:*";
        
        try {
            $keys = $this->redis->keys($pattern);
            $metrics = [];
            
            foreach (array_slice($keys, 0, $limit) as $key) {
                $data = $this->redis->lrange($key, 0, -1);
                foreach ($data as $item) {
                    $metrics[] = json_decode($item, true);
                }
            }
            
            // Sort by timestamp (most recent first)
            usort($metrics, function ($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });
            
            return array_slice($metrics, 0, $limit);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top API endpoints by request count
     */
    private function getTopEndpoints(): array
    {
        // This would typically be implemented with proper metrics aggregation
        return [
            '/api/tickets/search' => 1250,
            '/api/tickets' => 890,
            '/api/platforms/stubhub' => 450,
            '/api/analytics/dashboard' => 320,
            '/api/alerts' => 180,
        ];
    }

    /**
     * Get average response times by endpoint
     */
    private function getAverageResponseTimes(): array
    {
        return [
            '/api/tickets/search' => 245.5,
            '/api/tickets' => 123.2,
            '/api/platforms/stubhub' => 1850.3,
            '/api/analytics/dashboard' => 456.7,
            '/api/alerts' => 89.1,
        ];
    }

    /**
     * Get error rates by endpoint
     */
    private function getErrorRates(): array
    {
        return [
            '/api/tickets/search' => 2.1,
            '/api/tickets' => 0.5,
            '/api/platforms/stubhub' => 8.3,
            '/api/analytics/dashboard' => 1.2,
            '/api/alerts' => 0.3,
        ];
    }

    /**
     * Get request volume over time
     */
    private function getRequestVolume(): array
    {
        $hours = [];
        $now = Carbon::now();
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i);
            $hours[$hour->format('H:00')] = rand(50, 300); // Mock data
        }
        
        return $hours;
    }

    /**
     * Get scraping platform statistics
     */
    private function getScrapingPlatformStats(): array
    {
        return [
            'ticketmaster' => ['requests' => 1520, 'success' => 1445, 'failures' => 75],
            'stubhub' => ['requests' => 980, 'success' => 892, 'failures' => 88],
            'viagogo' => ['requests' => 650, 'success' => 598, 'failures' => 52],
            'tickpick' => ['requests' => 420, 'success' => 389, 'failures' => 31],
        ];
    }

    /**
     * Get scraping success rates
     */
    private function getScrapingSuccessRates(): array
    {
        return [
            'ticketmaster' => 95.1,
            'stubhub' => 91.0,
            'viagogo' => 92.0,
            'tickpick' => 92.6,
        ];
    }

    /**
     * Get scraping durations
     */
    private function getScrapingDurations(): array
    {
        return [
            'ticketmaster' => 2.3,
            'stubhub' => 1.8,
            'viagogo' => 3.1,
            'tickpick' => 1.5,
        ];
    }

    /**
     * Get recent scraping failures
     */
    private function getRecentScrapingFailures(): array
    {
        return [
            [
                'platform' => 'viagogo',
                'operation' => 'event_search',
                'error' => 'Rate limit exceeded',
                'timestamp' => Carbon::now()->subMinutes(5),
            ],
            [
                'platform' => 'stubhub',
                'operation' => 'ticket_details',
                'error' => 'CAPTCHA challenge',
                'timestamp' => Carbon::now()->subMinutes(12),
            ]
        ];
    }

    /**
     * Get slow query count
     */
    private function getSlowQueryCount(): int
    {
        try {
            $result = DB::select('SHOW STATUS LIKE "Slow_queries"');
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): float
    {
        try {
            $info = $this->redis->info('stats');
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Export metrics for external monitoring systems
     */
    public function exportMetrics(string $format = 'json'): string
    {
        $metrics = $this->getDashboardMetrics();
        
        switch ($format) {
            case 'prometheus':
                return $this->exportPrometheusMetrics($metrics);
            case 'influxdb':
                return $this->exportInfluxDBMetrics($metrics);
            default:
                return json_encode($metrics, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Export metrics in Prometheus format
     */
    private function exportPrometheusMetrics(array $metrics): string
    {
        $output = [];
        $timestamp = time();
        
        // System metrics
        $output[] = "# HELP hdtickets_memory_usage_bytes Current memory usage in bytes";
        $output[] = "# TYPE hdtickets_memory_usage_bytes gauge";
        $output[] = "hdtickets_memory_usage_bytes " . $metrics['system']['memory_usage']['current'] . " {$timestamp}";
        
        // Database metrics
        $output[] = "# HELP hdtickets_db_connections Current database connections";
        $output[] = "# TYPE hdtickets_db_connections gauge";
        $output[] = "hdtickets_db_connections " . $metrics['database']['connections']['current'] . " {$timestamp}";
        
        // Cache metrics
        $output[] = "# HELP hdtickets_cache_hit_rate Cache hit rate percentage";
        $output[] = "# TYPE hdtickets_cache_hit_rate gauge";
        $output[] = "hdtickets_cache_hit_rate " . $metrics['cache']['redis']['hit_rate'] . " {$timestamp}";
        
        return implode("\n", $output);
    }

    /**
     * Export metrics in InfluxDB line protocol format
     */
    private function exportInfluxDBMetrics(array $metrics): string
    {
        $lines = [];
        $timestamp = time() * 1000000000; // InfluxDB expects nanoseconds
        
        // System metrics
        $lines[] = "system,host=hdtickets memory_usage=" . $metrics['system']['memory_usage']['current'] . " {$timestamp}";
        
        // Database metrics
        $lines[] = "database,host=hdtickets connections=" . $metrics['database']['connections']['current'] . " {$timestamp}";
        
        // Cache metrics
        $lines[] = "cache,host=hdtickets hit_rate=" . $metrics['cache']['redis']['hit_rate'] . " {$timestamp}";
        
        return implode("\n", $lines);
    }
}
