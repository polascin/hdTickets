<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

use function ini_get;

/**
 * HD Tickets Health Check Controller
 * Sports Events Entry Tickets Monitoring System
 *
 * Comprehensive health checks for deployment monitoring
 */
class HealthCheckController extends Controller
{
    /**
     * Basic health check endpoint for load balancer
     */
    /**
     * Basic
     */
    public function basic(): JsonResponse
    {
        return response()->json([
            'status'    => 'healthy',
            'timestamp' => now()->toISOString(),
            'service'   => 'HD Tickets Sports Events Monitoring',
            'version'   => config('app.version', '1.0.0'),
        ], 200);
    }

    /**
     * Comprehensive health check with detailed system status
     */
    /**
     * Detailed
     */
    public function detailed(): JsonResponse
    {
        $startTime = microtime(TRUE);
        $checks = [];
        $overallStatus = 'healthy';
        $httpStatus = 200;

        try {
            // Database connectivity check
            $checks['database'] = $this->checkDatabase();

            // Cache system check
            $checks['cache'] = $this->checkCache();

            // Queue system check
            $checks['queue'] = $this->checkQueue();

            // Storage check
            $checks['storage'] = $this->checkStorage();

            // Sports events specific checks
            $checks['sports_events'] = $this->checkSportsEventsSystem();

            // Ticket scraping system check
            $checks['scraping'] = $this->checkScrapingSystem();

            // External APIs check
            $checks['external_apis'] = $this->checkExternalAPIs();

            // Determine overall status
            foreach ($checks as $check) {
                if ($check['status'] === 'unhealthy') {
                    $overallStatus = 'unhealthy';
                    $httpStatus = 503;

                    break;
                }
                if ($check['status'] === 'degraded' && $overallStatus === 'healthy') {
                    $overallStatus = 'degraded';
                    $httpStatus = 200; // Still operational but degraded
                }
            }
        } catch (Exception $e) {
            Log::error('Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $overallStatus = 'unhealthy';
            $httpStatus = 503;
        }

        $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

        return response()->json([
            'status'           => $overallStatus,
            'timestamp'        => now()->toISOString(),
            'service'          => 'HD Tickets Sports Events Monitoring',
            'version'          => config('app.version', '1.0.0'),
            'environment'      => config('app.env'),
            'response_time_ms' => $responseTime,
            'checks'           => $checks,
            'system_info'      => [
                'php_version'     => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage'    => $this->getMemoryUsage(),
                'uptime'          => $this->getUptime(),
            ],
        ], $httpStatus);
    }

    /**
     * Deployment status endpoint
     */
    /**
     * DeploymentStatus
     */
    public function deploymentStatus(): JsonResponse
    {
        $environment = config('app.env');
        $color = config('deployment.environments.' . $environment . '.deployment_color', 'unknown');

        return response()->json([
            'deployment' => [
                'environment' => $environment,
                'color'       => $color,
                'version'     => config('app.version', '1.0.0'),
                'deployed_at' => config('app.deployed_at', NULL),
                'commit_hash' => config('app.commit_hash', NULL),
            ],
            'status'    => 'active',
            'timestamp' => now()->toISOString(),
        ]);
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

            // Test basic connectivity
            DB::connection()->getPdo();

            // Test a simple query
            $result = DB::select('SELECT 1 as test');

            // Check sports events table accessibility
            $eventsCount = DB::table('sports_events')->count();

            // Check recent activity
            $recentEvents = DB::table('sports_events')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            return [
                'status'           => 'healthy',
                'message'          => 'Database connection successful',
                'response_time_ms' => $responseTime,
                'details'          => [
                    'connection'              => config('database.default'),
                    'sports_events_total'     => $eventsCount,
                    'recent_events_last_hour' => $recentEvents,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Database connection failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check cache system (Redis/File)
     */
    /**
     * CheckCache
     */
    private function checkCache(): array
    {
        try {
            $startTime = microtime(TRUE);
            $testKey = 'health_check_' . time();
            $testValue = 'test_' . uniqid();

            // Test cache write
            Cache::put($testKey, $testValue, 60);

            // Test cache read
            $retrieved = Cache::get($testKey);

            // Clean up
            Cache::forget($testKey);

            $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            if ($retrieved === $testValue) {
                return [
                    'status'           => 'healthy',
                    'message'          => 'Cache system operational',
                    'response_time_ms' => $responseTime,
                    'details'          => [
                        'driver' => config('cache.default'),
                    ],
                ];
            }

            return [
                'status'           => 'degraded',
                'message'          => 'Cache read/write inconsistency',
                'response_time_ms' => $responseTime,
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Cache system failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check queue system
     */
    /**
     * CheckQueue
     */
    private function checkQueue(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Get queue connection info
            $connection = config('queue.default');

            // Check Redis queues if using Redis
            if ($connection === 'redis') {
                $redis = Redis::connection();
                $queueLength = $redis->llen('queues:default');
                $failedJobs = $redis->llen('queues:failed');

                $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

                $status = 'healthy';
                $message = 'Queue system operational';

                // Check for excessive queue length (might indicate processing issues)
                if ($queueLength > 1000) {
                    $status = 'degraded';
                    $message = 'High queue length detected';
                }

                if ($failedJobs > 100) {
                    $status = 'degraded';
                    $message = 'High number of failed jobs';
                }

                return [
                    'status'           => $status,
                    'message'          => $message,
                    'response_time_ms' => $responseTime,
                    'details'          => [
                        'connection'   => $connection,
                        'pending_jobs' => $queueLength,
                        'failed_jobs'  => $failedJobs,
                    ],
                ];
            }

            return [
                'status'           => 'healthy',
                'message'          => 'Queue system available',
                'response_time_ms' => round((microtime(TRUE) - $startTime) * 1000, 2),
                'details'          => [
                    'connection' => $connection,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Queue system failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check storage system
     */
    /**
     * CheckStorage
     */
    private function checkStorage(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Check storage path exists and is writable
            $storagePath = storage_path();
            $logsPath = storage_path('logs');

            if (! is_writable($storagePath)) {
                return [
                    'status'           => 'unhealthy',
                    'message'          => 'Storage directory not writable',
                    'response_time_ms' => NULL,
                ];
            }

            // Check disk space
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedPercentage = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2);

            $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            $status = 'healthy';
            $message = 'Storage system operational';

            if ($usedPercentage > 90) {
                $status = 'degraded';
                $message = 'Low disk space';
            } elseif ($usedPercentage > 95) {
                $status = 'unhealthy';
                $message = 'Critical disk space';
            }

            return [
                'status'           => $status,
                'message'          => $message,
                'response_time_ms' => $responseTime,
                'details'          => [
                    'disk_usage_percent' => $usedPercentage,
                    'free_space_gb'      => round($freeBytes / 1024 / 1024 / 1024, 2),
                    'total_space_gb'     => round($totalBytes / 1024 / 1024 / 1024, 2),
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Storage check failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check sports events system specifically
     */
    /**
     * CheckSportsEventsSystem
     */
    private function checkSportsEventsSystem(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Check if we have recent sports events data
            $recentEvents = DB::table('sports_events')
                ->where('created_at', '>=', now()->subDay())
                ->count();

            // Check if we have upcoming events
            $upcomingEvents = DB::table('sports_events')
                ->where('event_date', '>=', now())
                ->count();

            // Check if ticket listings are being updated
            $recentListings = DB::table('ticket_listings')
                ->where('updated_at', '>=', now()->subHour())
                ->count();

            $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            $status = 'healthy';
            $message = 'Sports events system operational';

            if ($recentEvents === 0) {
                $status = 'degraded';
                $message = 'No recent sports events data';
            }

            if ($recentListings === 0) {
                $status = 'degraded';
                $message = 'Ticket listings not being updated';
            }

            return [
                'status'           => $status,
                'message'          => $message,
                'response_time_ms' => $responseTime,
                'details'          => [
                    'recent_events_24h'        => $recentEvents,
                    'upcoming_events'          => $upcomingEvents,
                    'recent_ticket_updates_1h' => $recentListings,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Sports events system check failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check ticket scraping system
     */
    /**
     * CheckScrapingSystem
     */
    private function checkScrapingSystem(): array
    {
        try {
            $startTime = microtime(TRUE);

            // Check recent scraping activity
            $recentScrapes = DB::table('scraping_logs')
                ->where('created_at', '>=', now()->subHour())
                ->count();

            // Check for scraping errors
            $recentErrors = DB::table('scraping_logs')
                ->where('created_at', '>=', now()->subHour())
                ->where('status', 'error')
                ->count();

            $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            $status = 'healthy';
            $message = 'Scraping system operational';

            if ($recentScrapes === 0) {
                $status = 'degraded';
                $message = 'No recent scraping activity';
            }

            $errorRate = $recentScrapes > 0 ? ($recentErrors / $recentScrapes) * 100 : 0;
            if ($errorRate > 50) {
                $status = 'degraded';
                $message = 'High scraping error rate';
            }

            return [
                'status'           => $status,
                'message'          => $message,
                'response_time_ms' => $responseTime,
                'details'          => [
                    'recent_scrapes_1h'  => $recentScrapes,
                    'recent_errors_1h'   => $recentErrors,
                    'error_rate_percent' => round($errorRate, 2),
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'           => 'unhealthy',
                'message'          => 'Scraping system check failed: ' . $e->getMessage(),
                'response_time_ms' => NULL,
            ];
        }
    }

    /**
     * Check external APIs (ticket platforms)
     */
    /**
     * CheckExternalAPIs
     */
    private function checkExternalAPIs(): array
    {
        $startTime = microtime(TRUE);
        $apiChecks = [];
        $overallStatus = 'healthy';

        // Check configured ticket platforms
        $platforms = config('deployment.environments.' . config('app.env') . '.ticket_platforms', []);

        foreach ($platforms as $platform => $config) {
            if (! ($config['enabled'] ?? FALSE)) {
                continue;
            }

            try {
                // Simple connectivity check (just checking if we can resolve the domain)
                $endpoint = $config['endpoints'][array_key_first($config['endpoints'])] ?? NULL;
                if ($endpoint) {
                    $host = parse_url($endpoint, PHP_URL_HOST);
                    $reachable = gethostbyname($host) !== $host;

                    $apiChecks[$platform] = [
                        'status'    => $reachable ? 'healthy' : 'degraded',
                        'reachable' => $reachable,
                    ];

                    if (! $reachable && $overallStatus === 'healthy') {
                        $overallStatus = 'degraded';
                    }
                }
            } catch (Exception $e) {
                $apiChecks[$platform] = [
                    'status' => 'unhealthy',
                    'error'  => $e->getMessage(),
                ];
                $overallStatus = 'degraded';
            }
        }

        $responseTime = round((microtime(TRUE) - $startTime) * 1000, 2);

        return [
            'status'           => $overallStatus,
            'message'          => 'External APIs check completed',
            'response_time_ms' => $responseTime,
            'details'          => $apiChecks,
        ];
    }

    /**
     * Get memory usage information
     */
    /**
     * Get  memory usage
     */
    private function getMemoryUsage(): array
    {
        return [
            'current_mb' => round(memory_get_usage(TRUE) / 1024 / 1024, 2),
            'peak_mb'    => round(memory_get_peak_usage(TRUE) / 1024 / 1024, 2),
            'limit'      => ini_get('memory_limit'),
        ];
    }

    /**
     * Get application uptime
     */
    /**
     * Get  uptime
     */
    private function getUptime(): string
    {
        $uptimeFile = storage_path('app/uptime.txt');

        if (file_exists($uptimeFile)) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', file_get_contents($uptimeFile));

            return $startTime->diffForHumans();
        }

        return 'Unknown';
    }
}
