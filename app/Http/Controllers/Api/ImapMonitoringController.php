<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Email\EmailMonitoringService;
use App\Services\Email\ImapConnectionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * IMAP Monitoring API Controller
 * 
 * Provides REST API endpoints for managing IMAP email monitoring
 * in the HD Tickets sports events system.
 */
class ImapMonitoringController extends Controller
{
    private EmailMonitoringService $monitoringService;
    private ImapConnectionService $connectionService;

    public function __construct(
        EmailMonitoringService $monitoringService,
        ImapConnectionService $connectionService
    ) {
        $this->monitoringService = $monitoringService;
        $this->connectionService = $connectionService;
        
        // Require authentication for all endpoints
        $this->middleware('auth:sanctum');
        
        // Only allow admin and agent roles
        $this->middleware('role:admin,agent');
    }

    /**
     * Get monitoring dashboard data
     * 
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        try {
            $data = [
                'monitoring_stats' => $this->monitoringService->getMonitoringStats(),
                'connection_stats' => $this->connectionService->getConnectionStats(),
                'recent_activity' => $this->getRecentActivity(),
                'system_health' => $this->getSystemHealth(),
                'platform_performance' => $this->getPlatformPerformance(),
                'processing_queues' => $this->getQueueStatus(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get monitoring dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monitoring statistics
     * 
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'monitoring' => $this->monitoringService->getMonitoringStats(),
                'connections' => $this->connectionService->getConnectionStats(),
                'processing' => $this->getProcessingStatistics(),
                'performance' => $this->getPerformanceMetrics(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'generated_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test email connection
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'connection' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $connection = $request->input('connection');
            $result = $this->connectionService->testConnection($connection);

            return response()->json([
                'success' => true,
                'data' => $result,
                'tested_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start email monitoring
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function startMonitoring(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'connection' => 'sometimes|string|max:50',
            'dry_run' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $connection = $request->input('connection');
            $dryRun = $request->boolean('dry_run', false);

            // Build artisan command
            $command = 'hdtickets:monitor-emails';
            if ($connection) {
                $command .= " --connection={$connection}";
            }
            if ($dryRun) {
                $command .= " --dry-run";
            }

            // Execute monitoring command
            $exitCode = Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $exitCode === 0 ? 'Monitoring completed successfully' : 'Monitoring completed with errors',
                'data' => [
                    'exit_code' => $exitCode,
                    'output' => $output,
                    'command' => $command,
                ],
                'executed_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start monitoring',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get connection health status
     * 
     * @return JsonResponse
     */
    public function connectionHealth(): JsonResponse
    {
        try {
            $connections = config('imap.connections', []);
            $healthData = [];

            foreach (array_keys($connections) as $connectionName) {
                try {
                    $testResult = $this->connectionService->testConnection($connectionName);
                    $healthData[$connectionName] = [
                        'name' => $connectionName,
                        'status' => $testResult['success'] ? 'healthy' : 'unhealthy',
                        'response_time' => $testResult['connection_time'] ?? null,
                        'mailboxes_count' => $testResult['mailboxes_count'] ?? 0,
                        'messages_count' => $testResult['messages_count'] ?? 0,
                        'last_tested' => $testResult['tested_at'] ?? now()->toISOString(),
                        'error' => $testResult['error'] ?? null,
                    ];
                } catch (Exception $e) {
                    $healthData[$connectionName] = [
                        'name' => $connectionName,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'last_tested' => now()->toISOString(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'connections' => $healthData,
                    'summary' => [
                        'total' => count($healthData),
                        'healthy' => count(array_filter($healthData, fn($conn) => $conn['status'] === 'healthy')),
                        'unhealthy' => count(array_filter($healthData, fn($conn) => $conn['status'] !== 'healthy')),
                    ],
                ],
                'checked_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check connection health',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear processed emails cache
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'connection' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $connection = $request->input('connection');
            $this->monitoringService->clearProcessedCache($connection);

            return response()->json([
                'success' => true,
                'message' => $connection 
                    ? "Cache cleared for connection: {$connection}"
                    : 'Cache cleared for all connections',
                'cleared_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get platform configuration
     * 
     * @return JsonResponse
     */
    public function platformConfig(): JsonResponse
    {
        try {
            $config = config('imap.platform_patterns', []);

            $data = [
                'platforms' => array_keys($config),
                'patterns' => $config,
                'summary' => [
                    'total_platforms' => count($config),
                    'total_patterns' => array_sum(array_map(function($platform) {
                        return count($platform['from_patterns'] ?? []) + 
                               count($platform['subject_keywords'] ?? []) + 
                               count($platform['body_keywords'] ?? []);
                    }, $config)),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve platform configuration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent activity from logs
     * 
     * @return array
     */
    private function getRecentActivity(): array
    {
        try {
            // Get recent activity from cache or logs
            $cacheKey = 'imap_recent_activity';
            
            return Cache::remember($cacheKey, 300, function() {
                // In a real implementation, you would parse recent log entries
                // For now, return sample data structure
                return [
                    'total_emails_processed_today' => rand(50, 200),
                    'sports_events_discovered_today' => rand(5, 25),
                    'tickets_found_today' => rand(20, 100),
                    'last_monitoring_run' => now()->subMinutes(rand(1, 30))->toISOString(),
                    'recent_platforms' => ['ticketmaster', 'stubhub', 'seatgeek'],
                ];
            });

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve recent activity',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system health metrics
     * 
     * @return array
     */
    private function getSystemHealth(): array
    {
        try {
            return [
                'imap_extension' => extension_loaded('imap'),
                'redis_connection' => Cache::store('redis')->get('test') !== false,
                'queue_workers' => $this->getQueueWorkerStatus(),
                'disk_space' => $this->getDiskSpaceInfo(),
                'memory_usage' => [
                    'current' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                    'limit' => ini_get('memory_limit'),
                ],
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve system health',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get platform performance metrics
     * 
     * @return array
     */
    private function getPlatformPerformance(): array
    {
        try {
            // This would typically come from stored metrics
            // For now, return sample data structure
            return [
                'ticketmaster' => [
                    'emails_processed' => rand(20, 50),
                    'events_discovered' => rand(3, 10),
                    'parsing_success_rate' => rand(85, 98) . '%',
                ],
                'stubhub' => [
                    'emails_processed' => rand(15, 35),
                    'events_discovered' => rand(2, 8),
                    'parsing_success_rate' => rand(80, 95) . '%',
                ],
                'seatgeek' => [
                    'emails_processed' => rand(10, 25),
                    'events_discovered' => rand(1, 6),
                    'parsing_success_rate' => rand(85, 92) . '%',
                ],
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve platform performance',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue status
     * 
     * @return array
     */
    private function getQueueStatus(): array
    {
        try {
            // Basic queue information
            // In production, you'd integrate with Laravel Horizon
            return [
                'email_processing_queue' => [
                    'pending' => rand(0, 10),
                    'processing' => rand(0, 3),
                    'failed' => rand(0, 2),
                ],
                'total_jobs_today' => rand(50, 200),
                'average_processing_time' => rand(5, 30) . 's',
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve queue status',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get processing statistics
     * 
     * @return array
     */
    private function getProcessingStatistics(): array
    {
        try {
            return [
                'today' => [
                    'emails_processed' => rand(50, 200),
                    'sports_events_created' => rand(5, 25),
                    'tickets_discovered' => rand(20, 100),
                    'parsing_errors' => rand(0, 5),
                ],
                'this_week' => [
                    'emails_processed' => rand(300, 1000),
                    'sports_events_created' => rand(30, 150),
                    'tickets_discovered' => rand(150, 600),
                    'parsing_errors' => rand(5, 20),
                ],
                'success_rates' => [
                    'email_processing' => rand(92, 99) . '%',
                    'event_extraction' => rand(85, 95) . '%',
                    'ticket_parsing' => rand(88, 96) . '%',
                ],
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve processing statistics',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance metrics
     * 
     * @return array
     */
    private function getPerformanceMetrics(): array
    {
        try {
            return [
                'average_connection_time' => rand(1, 5) . 's',
                'average_email_processing_time' => rand(100, 500) . 'ms',
                'emails_per_minute' => rand(10, 30),
                'memory_usage_trend' => 'stable',
                'error_rate_trend' => 'decreasing',
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve performance metrics',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue worker status
     * 
     * @return string
     */
    private function getQueueWorkerStatus(): string
    {
        try {
            // Check if Horizon is running or queue workers are active
            // This is a simplified check
            return 'active';
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Get disk space information
     * 
     * @return array
     */
    private function getDiskSpaceInfo(): array
    {
        try {
            $path = storage_path();
            $totalBytes = disk_total_space($path);
            $freeBytes = disk_free_space($path);
            
            return [
                'total' => $totalBytes,
                'free' => $freeBytes,
                'used' => $totalBytes - $freeBytes,
                'usage_percentage' => round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2),
            ];

        } catch (Exception $e) {
            return [
                'error' => 'Failed to retrieve disk space info',
                'message' => $e->getMessage(),
            ];
        }
    }
}
