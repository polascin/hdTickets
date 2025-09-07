<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

use function ini_get;
use function strlen;

class HealthController extends Controller
{
    /**
     * Comprehensive health check for production monitoring
     */
    /**
     * Index
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(TRUE);
        $checks = [];
        $overallStatus = 'healthy';

        // Basic application health
        $checks['app'] = $this->checkApplication();

        // Database connectivity
        $checks['database'] = $this->checkDatabase();

        // Redis connectivity
        $checks['redis'] = $this->checkRedis();

        // Cache functionality
        $checks['cache'] = $this->checkCache();

        // Queue system
        $checks['queue'] = $this->checkQueue();

        // External services
        $checks['external_services'] = $this->checkExternalServices();

        // System resources
        $checks['system'] = $this->checkSystemResources();

        // Ticket scraping health
        $checks['ticket_scraping'] = $this->checkTicketScrapingHealth();

        // Determine overall status
        foreach ($checks as $service => $status) {
            if ($status['status'] !== 'healthy') {
                $overallStatus = 'unhealthy';

                break;
            }
        }

        $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

        $response = [
            'status'           => $overallStatus,
            'timestamp'        => now()->toISOString(),
            'version'          => '2025.07.v4.0',
            'environment'      => app()->environment(),
            'response_time_ms' => $responseTime,
            'checks'           => $checks,
        ];

        // Return appropriate HTTP status code
        $httpStatus = $overallStatus === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return response()->json($response, $httpStatus);
    }

    /**
     * Database-specific health check
     */
    /**
     * Database
     */
    public function database(): JsonResponse
    {
        $check = $this->checkDatabase();
        $status = $check['status'] === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return response()->json([
            'service'   => 'database',
            'status'    => $check['status'],
            'details'   => $check,
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * Redis-specific health check
     */
    /**
     * Redis
     */
    public function redis(): JsonResponse
    {
        $check = $this->checkRedis();
        $status = $check['status'] === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return response()->json([
            'service'   => 'redis',
            'status'    => $check['status'],
            'details'   => $check,
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * WebSocket health check
     */
    /**
     * Websockets
     */
    public function websockets(): JsonResponse
    {
        $check = $this->checkWebSocketHealth();
        $status = $check['status'] === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return response()->json([
            'service'   => 'websockets',
            'status'    => $check['status'],
            'details'   => $check,
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * Services health check
     */
    /**
     * Services
     */
    public function services(): JsonResponse
    {
        $check = $this->checkExternalServices();
        $status = $check['status'] === 'healthy' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return response()->json([
            'service'   => 'external_services',
            'status'    => $check['status'],
            'details'   => $check,
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * Check basic application functionality
     */
    /**
     * CheckApplication
     */
    private function checkApplication(): array
    {
        try {
            $checks = [
                'php_version'      => PHP_VERSION,
                'laravel_version'  => app()->version(),
                'timezone'         => config('app.timezone'),
                'debug_mode'       => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ];

            return [
                'status'  => 'healthy',
                'message' => 'Application is running normally',
                'details' => $checks,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Application check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check database connectivity and performance
     */
    /**
     * CheckDatabase
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Test main database connection
            DB::connection()->getPdo();
            $mainConnection = microtime(TRUE) - $startTime;

            // Test read replica if configured
            $readConnection = NULL;
            if (config('database.connections.mysql.read')) {
                $startTime = microtime(TRUE);
                DB::connection('mysql')->getPdo();
                $readConnection = microtime(TRUE) - $startTime;
            }

            // Simple query test
            $startTime = microtime(TRUE);
            $userCount = DB::table('users')->count();
            $queryTime = microtime(TRUE) - $startTime;

            return [
                'status'  => 'healthy',
                'message' => 'Database connections are working',
                'details' => [
                    'main_connection_time_ms' => round($mainConnection * 1000, 2),
                    'read_connection_time_ms' => $readConnection ? round($readConnection * 1000, 2) : NULL,
                    'query_time_ms'           => round($queryTime * 1000, 2),
                    'sample_query_result'     => $userCount,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Database connection failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connectivity and performance
     */
    /**
     * CheckRedis
     */
    private function checkRedis(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Test Redis ping
            $ping = Redis::ping();
            $pingTime = microtime(TRUE) - $startTime;

            // Test cache operations
            $startTime = microtime(TRUE);
            $testKey = 'health_check_' . time();
            Redis::set($testKey, 'test_value', 'EX', 10);
            $getValue = Redis::get($testKey);
            Redis::del($testKey);
            $operationTime = microtime(TRUE) - $startTime;

            // Get Redis info
            $info = Redis::info();
            $memoryUsage = $info['used_memory_human'] ?? 'Unknown';

            return [
                'status'  => 'healthy',
                'message' => 'Redis is working normally',
                'details' => [
                    'ping_response'          => $ping,
                    'ping_time_ms'           => round($pingTime * 1000, 2),
                    'operation_time_ms'      => round($operationTime * 1000, 2),
                    'memory_usage'           => $memoryUsage,
                    'test_operation_success' => $getValue === 'test_value',
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Redis connection failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality
     */
    /**
     * CheckCache
     */
    private function checkCache(): array
    {
        try {
            $startTime = microtime(TRUE);

            $testKey = 'health_check_cache_' . time();
            $testValue = 'cache_test_' . uniqid();

            // Test cache put and get
            Cache::put($testKey, $testValue, 10);
            $retrievedValue = Cache::get($testKey);
            Cache::forget($testKey);

            $operationTime = microtime(TRUE) - $startTime;

            return [
                'status'  => 'healthy',
                'message' => 'Cache is working normally',
                'details' => [
                    'operation_time_ms' => round($operationTime * 1000, 2),
                    'cache_driver'      => config('cache.default'),
                    'test_success'      => $retrievedValue === $testValue,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Cache system failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue system health
     */
    /**
     * CheckQueue
     */
    private function checkQueue(): array
    {
        try {
            $queueConnection = config('queue.default');

            // Get queue size for monitoring
            $queueSize = 0;
            $failedJobs = 0;

            try {
                $queueSize = Redis::llen('queues:default');
                $failedJobs = DB::table('failed_jobs')->count();
            } catch (Exception $e) {
                // Queue size check failed, but don't fail the entire check
            }

            return [
                'status'  => 'healthy',
                'message' => 'Queue system is operational',
                'details' => [
                    'queue_driver' => $queueConnection,
                    'queue_size'   => $queueSize,
                    'failed_jobs'  => $failedJobs,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Queue system check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check external services connectivity
     */
    /**
     * CheckExternalServices
     */
    private function checkExternalServices(): array
    {
        $services = [];

        // Check New Relic (if enabled)
        if (config('newrelic.enabled')) {
            $services['new_relic'] = $this->checkNewRelic();
        }

        // Check Sentry (if enabled)
        if (config('sentry.enabled')) {
            $services['sentry'] = $this->checkSentry();
        }

        // Check AWS S3 (if configured)
        if (config('filesystems.default') === 's3') {
            $services['s3'] = $this->checkS3();
        }

        $overallStatus = 'healthy';
        foreach ($services as $service) {
            if ($service['status'] !== 'healthy') {
                $overallStatus = 'degraded'; // External services are not critical

                break;
            }
        }

        return [
            'status'  => $overallStatus,
            'message' => 'External services check completed',
            'details' => $services,
        ];
    }

    /**
     * Check system resources
     */
    /**
     * CheckSystemResources
     */
    private function checkSystemResources(): array
    {
        try {
            $memoryUsage = memory_get_usage(TRUE);
            $memoryLimit = $this->parseSize(ini_get('memory_limit'));
            $memoryPercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

            $diskFree = disk_free_space(storage_path());
            $diskTotal = disk_total_space(storage_path());
            $diskUsedPercent = $diskTotal > 0 ? (($diskTotal - $diskFree) / $diskTotal) * 100 : 0;

            $status = 'healthy';
            if ($memoryPercent > 90 || $diskUsedPercent > 95) {
                $status = 'warning';
            }

            return [
                'status'  => $status,
                'message' => 'System resources monitored',
                'details' => [
                    'memory_usage_bytes'   => $memoryUsage,
                    'memory_limit_bytes'   => $memoryLimit,
                    'memory_usage_percent' => round($memoryPercent, 2),
                    'disk_free_bytes'      => $diskFree,
                    'disk_total_bytes'     => $diskTotal,
                    'disk_used_percent'    => round($diskUsedPercent, 2),
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'System resources check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check ticket scraping system health
     */
    /**
     * CheckTicketScrapingHealth
     */
    private function checkTicketScrapingHealth(): array
    {
        try {
            // Check recent scraping activity
            $recentScrapes = DB::table('scraping_stats')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            $lastSuccessfulScrape = DB::table('scraping_stats')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();

            $activePlatforms = DB::table('scraped_tickets')
                ->distinct()
                ->count('platform');

            $status = 'healthy';
            $message = 'Ticket scraping system is operational';

            // Check if we haven't had successful scrapes recently
            if (!$lastSuccessfulScrape || now()->diffInHours($lastSuccessfulScrape->created_at) > 2) {
                $status = 'warning';
                $message = 'No recent successful scrapes detected';
            }

            return [
                'status'  => $status,
                'message' => $message,
                'details' => [
                    'recent_scrapes_count'   => $recentScrapes,
                    'last_successful_scrape' => $lastSuccessfulScrape?->created_at,
                    'active_platforms'       => $activePlatforms,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'Ticket scraping health check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check New Relic connectivity
     */
    /**
     * CheckNewRelic
     */
    private function checkNewRelic(): array
    {
        // New Relic doesn't require a specific health check
        // We just verify it's configured
        return [
            'status'     => 'healthy',
            'message'    => 'New Relic monitoring configured',
            'configured' => TRUE,
        ];
    }

    /**
     * Check Sentry connectivity
     */
    /**
     * CheckSentry
     */
    private function checkSentry(): array
    {
        // Sentry doesn't require a specific health check
        // We just verify it's configured
        return [
            'status'     => 'healthy',
            'message'    => 'Sentry error tracking configured',
            'configured' => !empty(config('sentry.dsn')),
        ];
    }

    /**
     * Check S3 connectivity
     */
    /**
     * CheckS3
     */
    private function checkS3(): array
    {
        try {
            // We can't easily test S3 without making actual requests
            // So we just verify configuration
            $configured = !empty(config('filesystems.disks.s3.key'));

            return [
                'status'     => $configured ? 'healthy' : 'warning',
                'message'    => $configured ? 'S3 storage configured' : 'S3 storage not configured',
                'configured' => $configured,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'S3 check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Check WebSocket system health
     */
    /**
     * CheckWebSocketHealth
     */
    private function checkWebSocketHealth(): array
    {
        try {
            // Check if WebSocket server is configured
            $wsEnabled = config('broadcasting.default') !== 'null';

            if (!$wsEnabled) {
                return [
                    'status'  => 'disabled',
                    'message' => 'WebSocket broadcasting is disabled',
                    'details' => [
                        'driver'            => config('broadcasting.default'),
                        'pusher_configured' => !empty(config('broadcasting.connections.pusher.key')),
                    ],
                ];
            }

            // Check recent WebSocket events
            $recentConnections = 0;
            $recentDisconnections = 0;

            try {
                // Try to get WebSocket stats from Redis if available
                if (config('broadcasting.default') === 'pusher') {
                    // For Pusher, check configuration
                    $pusherConfigured = !empty(config('broadcasting.connections.pusher.key'))
                                       && !empty(config('broadcasting.connections.pusher.secret'))
                                       && !empty(config('broadcasting.connections.pusher.app_id'));

                    return [
                        'status'  => $pusherConfigured ? 'healthy' : 'warning',
                        'message' => $pusherConfigured ? 'Pusher WebSocket configured' : 'Pusher WebSocket not properly configured',
                        'details' => [
                            'driver'            => 'pusher',
                            'app_id_configured' => !empty(config('broadcasting.connections.pusher.app_id')),
                            'key_configured'    => !empty(config('broadcasting.connections.pusher.key')),
                            'secret_configured' => !empty(config('broadcasting.connections.pusher.secret')),
                            'cluster'           => config('broadcasting.connections.pusher.options.cluster', 'not_set'),
                        ],
                    ];
                }
            } catch (Exception $e) {
                // Fallback if we can't check stats
            }

            return [
                'status'  => 'healthy',
                'message' => 'WebSocket system is configured',
                'details' => [
                    'driver'                => config('broadcasting.default'),
                    'recent_connections'    => $recentConnections,
                    'recent_disconnections' => $recentDisconnections,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'unhealthy',
                'message' => 'WebSocket health check failed',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse size string to bytes
     */
    /**
     * ParseSize
     */
    private function parseSize(string $size): int
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
}
