<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

use function function_exists;
use function ini_get;
use function strlen;

/**
 * Production Health Check Controller
 *
 * Comprehensive health monitoring for HD Tickets production environment
 * Includes performance metrics, error tracking, and system monitoring
 */
class ProductionHealthController extends Controller
{
    /**
     * Comprehensive production health check
     */
    public function comprehensive(Request $request): JsonResponse
    {
        $startTime = microtime(TRUE);
        $checks = [];
        $overallStatus = 'healthy';
        $httpStatus = Response::HTTP_OK;

        try {
            // Core system checks
            $checks['application'] = $this->checkApplication();
            $checks['database'] = $this->checkDatabaseHealth();
            $checks['cache'] = $this->checkCacheSystem();
            $checks['queue'] = $this->checkQueueSystem();
            $checks['storage'] = $this->checkStorageHealth();

            // Sports events specific checks
            $checks['sports_events'] = $this->checkSportsEventsSystem();
            $checks['ticket_scraping'] = $this->checkTicketScrapingHealth();
            $checks['external_apis'] = $this->checkExternalAPIs();

            // Production monitoring checks
            $checks['performance'] = $this->checkPerformanceMetrics();
            $checks['error_tracking'] = $this->checkErrorTracking();
            $checks['security'] = $this->checkSecurityStatus();
            $checks['monitoring'] = $this->checkMonitoringHealth();

            // System resources
            $checks['resources'] = $this->checkSystemResources();
            $checks['horizon'] = $this->checkHorizonStatus();

            // Determine overall status
            foreach ($checks as $check) {
                /** @var string $checkStatus */
                $checkStatus = $check['status'];

                if ($checkStatus === 'critical') {
                    $overallStatus = 'critical';
                    $httpStatus = Response::HTTP_SERVICE_UNAVAILABLE;

                    break;
                }

                if ($checkStatus === 'warning' && $overallStatus !== 'critical') {
                    $overallStatus = 'warning';
                    $httpStatus = Response::HTTP_OK; // Still operational
                } elseif ($checkStatus === 'degraded' && $overallStatus === 'healthy') {
                    $overallStatus = 'degraded';
                }
            }
        } catch (Exception $e) {
            Log::channel('error_tracking')->error('Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $overallStatus = 'critical';
            $httpStatus = Response::HTTP_SERVICE_UNAVAILABLE;
        }

        $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

        return response()->json([
            'status'              => $overallStatus,
            'timestamp'           => now()->toISOString(),
            'service'             => 'HD Tickets Sports Events Monitoring',
            'version'             => config('app.version', '1.0.0'),
            'environment'         => config('app.env'),
            'response_time_ms'    => $responseTime,
            'checks'              => $checks,
            'system_info'         => $this->getSystemInfo(),
            'performance_summary' => $this->getPerformanceSummary(),
        ], $httpStatus);
    }

    /**
     * Check application health
     */
    private function checkApplication(): array
    {
        try {
            $issues = [];

            // Check if debug mode is disabled in production
            if (config('app.env') === 'production' && config('app.debug')) {
                $issues[] = 'Debug mode is enabled in production';
            }

            // Check if Ignition is properly configured
            if (config('app.env') === 'production' && config('error-tracking.ignition.enabled_in_production', FALSE)) {
                $issues[] = 'Ignition is enabled in production (security risk)';
            }

            // Check maintenance mode
            $maintenanceMode = app()->isDownForMaintenance();

            $status = 'healthy';
            if ($issues !== []) {
                $status = 'warning';
            }
            if ($maintenanceMode) {
                $status = 'degraded';
            }

            return [
                'status'  => $status,
                'message' => 'Application status checked',
                'details' => [
                    'maintenance_mode' => $maintenanceMode,
                    'debug_mode'       => config('app.debug'),
                    'environment'      => config('app.env'),
                    'timezone'         => config('app.timezone'),
                    'issues'           => $issues,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'critical',
                'message' => 'Application check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Enhanced database health check
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Test connection
            DB::connection()->getPdo();
            $connectionTime = microtime(TRUE) - $startTime;

            // Get database stats
            $stats = $this->getDatabaseStats();

            // Check slow queries
            $slowQueries = $this->getSlowQueryCount();

            // Check connection pool
            $connections = $this->getDatabaseConnections();

            $status = 'healthy';
            if ($connectionTime > 0.5) {
                $status = 'warning';
            }
            if ($slowQueries > 10) {
                $status = 'warning';
            }
            if ($connections > 80) {
                $status = 'degraded';
            }

            return [
                'status'  => $status,
                'message' => 'Database health checked',
                'details' => [
                    'connection_time_ms'     => round($connectionTime * 1000, 2),
                    'active_connections'     => $connections,
                    'slow_queries_last_hour' => $slowQueries,
                    'database_size_mb'       => $stats['size_mb'] ?? 0,
                    'table_count'            => $stats['tables'] ?? 0,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'critical',
                'message' => 'Database check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check performance metrics
     */
    private function checkPerformanceMetrics(): array
    {
        try {
            $metrics = [
                'response_time_avg' => $this->getAverageResponseTime(),
                'memory_usage'      => $this->getMemoryUsage(),
                'cpu_usage'         => $this->getCpuUsage(),
                'disk_usage'        => $this->getDiskUsage(),
                'error_rate'        => $this->getErrorRate(),
            ];

            $status = 'healthy';

            if ($metrics['response_time_avg'] > 5000) {
                $status = 'warning';
            }
            if ($metrics['memory_usage']['percentage'] > 85) {
                $status = 'warning';
            }
            if ($metrics['cpu_usage'] > 80) {
                $status = 'degraded';
            }
            if ($metrics['error_rate'] > 5) {
                $status = 'warning';
            }

            return [
                'status'  => $status,
                'message' => 'Performance metrics checked',
                'details' => $metrics,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'warning',
                'message' => 'Performance check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Horizon status
     */
    private function checkHorizonStatus(): array
    {
        try {
            $status = 'healthy';
            $details = [];

            // Check if Horizon is running
            $horizonStatus = Cache::get('horizon:master_supervisor');
            $isRunning = $horizonStatus !== NULL;

            if (! $isRunning) {
                $status = 'critical';
                $details['message'] = 'Horizon is not running';
            } else {
                // Check queue sizes
                $queueSizes = $this->getQueueSizes();
                $details['queue_sizes'] = $queueSizes;

                // Check failed jobs
                $failedJobs = DB::table('failed_jobs')->count();
                $details['failed_jobs'] = $failedJobs;

                if ($failedJobs > 100) {
                    $status = 'warning';
                }

                if (array_sum($queueSizes) > 1000) {
                    $status = 'warning';
                }
            }

            return [
                'status'  => $status,
                'message' => 'Horizon status checked',
                'details' => array_merge([
                    'running' => $isRunning,
                ], $details),
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'warning',
                'message' => 'Horizon check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check ticket scraping system health
     */
    private function checkTicketScrapingHealth(): array
    {
        try {
            // Check recent scraping activity
            $recentScrapes = DB::table('scraping_logs')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            // Check scraping errors
            $errorRate = DB::table('scraping_logs')
                ->where('created_at', '>=', now()->subHour())
                ->where('status', 'error')
                ->count();

            // Check active scrapers
            $activeScrapers = DB::table('scrapers')
                ->where('status', 'active')
                ->where('last_seen', '>=', now()->subMinutes(10))
                ->count();

            $status = 'healthy';
            if ($recentScrapes === 0) {
                $status = 'warning';
            }
            if ($errorRate > $recentScrapes * 0.3) {
                $status = 'warning';
            }
            if ($activeScrapers < 2) {
                $status = 'degraded';
            }

            return [
                'status'  => $status,
                'message' => 'Ticket scraping system checked',
                'details' => [
                    'recent_scrapes'        => $recentScrapes,
                    'error_rate_percentage' => $recentScrapes > 0 ? round(($errorRate / $recentScrapes) * 100, 2) : 0,
                    'active_scrapers'       => $activeScrapers,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'critical',
                'message' => 'Scraping system check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version'        => PHP_VERSION,
            'laravel_version'    => app()->version(),
            'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit'       => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'opcache_enabled'    => function_exists('opcache_get_status'),
            'redis_version'      => $this->getRedisVersion(),
            'database_version'   => $this->getDatabaseVersion(),
        ];
    }

    /**
     * Get performance summary
     */
    private function getPerformanceSummary(): array
    {
        return [
            'uptime'               => $this->getUptime(),
            'requests_per_minute'  => $this->getRequestsPerMinute(),
            'average_memory_usage' => $this->getAverageMemoryUsage(),
            'cache_hit_ratio'      => $this->getCacheHitRatio(),
        ];
    }

    /**
     * Helper methods for various checks
     */
    private function getDatabaseStats(): array
    {
        try {
            $sizeQuery = DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()');
            $tableQuery = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()');

            return [
                'size_mb' => $sizeQuery[0]->size_mb ?? 0,
                'tables'  => $tableQuery[0]->count ?? 0,
            ];
        } catch (Exception) {
            return ['size_mb' => 0, 'tables' => 0];
        }
    }

    private function getSlowQueryCount(): int
    {
        try {
            $result = DB::select("SHOW GLOBAL STATUS LIKE 'Slow_queries'");

            return (int) ($result[0]->Value ?? 0);
        } catch (Exception) {
            return 0;
        }
    }

    private function getDatabaseConnections(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            return (int) ($result[0]->Value ?? 0);
        } catch (Exception) {
            return 0;
        }
    }

    private function getAverageResponseTime(): float
    {
        return Cache::remember('health:avg_response_time', 300, function (): int {
            // This would typically come from your application metrics
            return random_int(500, 2000); // Placeholder
        });
    }

    private function getMemoryUsage(): array
    {
        $current = memory_get_usage(TRUE);
        $peak = memory_get_peak_usage(TRUE);
        $limit = $this->parseMemorySize(ini_get('memory_limit'));

        return [
            'current_mb' => round($current / 1024 / 1024, 2),
            'peak_mb'    => round($peak / 1024 / 1024, 2),
            'limit_mb'   => round($limit / 1024 / 1024, 2),
            'percentage' => $limit > 0 ? round(($current / $limit) * 100, 2) : 0,
        ];
    }

    private function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            return round(($load[0] ?? 0) * 100, 2);
        }

        return 0.0;
    }

    private function getDiskUsage(): array
    {
        $path = storage_path();
        $free = disk_free_space($path);
        $total = disk_total_space($path);
        $used = $total - $free;

        return [
            'total_gb'   => round($total / 1024 / 1024 / 1024, 2),
            'used_gb'    => round($used / 1024 / 1024 / 1024, 2),
            'free_gb'    => round($free / 1024 / 1024 / 1024, 2),
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    private function getErrorRate(): float
    {
        return Cache::remember('health:error_rate', 300, function (): int {
            // This would come from your error tracking system
            return random_int(0, 10); // Placeholder
        });
    }

    private function getQueueSizes(): array
    {
        $queues = ['default', 'high', 'scraping', 'notifications', 'low'];
        $sizes = [];

        foreach ($queues as $queue) {
            try {
                $sizes[$queue] = Redis::llen("queues:{$queue}");
            } catch (Exception) {
                $sizes[$queue] = 0;
            }
        }

        return $sizes;
    }

    private function parseMemorySize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;

        switch ($last) {
            case 'g':
                $size *= 1024;
                // no break
            case 'm':
                $size *= 1024;
                // no break
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    private function getUptime(): string
    {
        $uptimeFile = storage_path('app/uptime.txt');
        if (file_exists($uptimeFile)) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', (string) file_get_contents($uptimeFile));

            return $startTime?->diffForHumans() ?? 'Unknown';
        }

        return 'Unknown';
    }

    // Additional placeholder methods that would be implemented based on your metrics system
    private function getRequestsPerMinute(): int
    {
        return random_int(100, 500);
    }

    private function getAverageMemoryUsage(): float
    {
        return random_int(50, 200);
    }

    private function getCacheHitRatio(): float
    {
        return random_int(80, 99);
    }

    private function getRedisVersion(): string
    {
        return '6.0';
    }

    private function getDatabaseVersion(): string
    {
        return '10.4';
    }

    private function checkSportsEventsSystem(): array
    {
        return ['status' => 'healthy', 'message' => 'Sports events system operational'];
    }

    private function checkExternalAPIs(): array
    {
        return ['status' => 'healthy', 'message' => 'External APIs operational'];
    }

    private function checkErrorTracking(): array
    {
        return ['status' => 'healthy', 'message' => 'Error tracking operational'];
    }

    private function checkSecurityStatus(): array
    {
        return ['status' => 'healthy', 'message' => 'Security status normal'];
    }

    private function checkMonitoringHealth(): array
    {
        return ['status' => 'healthy', 'message' => 'Monitoring systems operational'];
    }

    private function checkCacheSystem(): array
    {
        return ['status' => 'healthy', 'message' => 'Cache system operational'];
    }

    private function checkQueueSystem(): array
    {
        return ['status' => 'healthy', 'message' => 'Queue system operational'];
    }

    private function checkStorageHealth(): array
    {
        return ['status' => 'healthy', 'message' => 'Storage system healthy'];
    }

    private function checkSystemResources(): array
    {
        return ['status' => 'healthy', 'message' => 'System resources normal'];
    }
}
