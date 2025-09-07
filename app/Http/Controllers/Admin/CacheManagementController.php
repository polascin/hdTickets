<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseOptimizationService;
use App\Services\RedisCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Cache Management Controller
 *
 * Provides comprehensive cache management capabilities:
 * - Cache statistics and monitoring
 * - Manual cache control (clear, warm-up, invalidate)
 * - Cache health monitoring and optimization
 * - Performance metrics dashboard
 * - Layer-specific cache management
 */
class CacheManagementController extends Controller
{
    protected DatabaseOptimizationService $dbOptimizer;

    protected RedisCacheService $cacheService;

    public function __construct(
        DatabaseOptimizationService $dbOptimizer,
        RedisCacheService $cacheService
    ) {
        $this->dbOptimizer = $dbOptimizer;
        $this->cacheService = $cacheService;
    }

    /**
     * Show cache management dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $cacheStats = $this->cacheService->getCacheStats();
        $dbStats = $this->dbOptimizer->getPerformanceStats();

        $slowQueries = Cache::get('slow_queries', []);
        $slowRequests = Cache::get('slow_requests', []);
        $nPlusOneDetections = Cache::get('n_plus_one_detections', []);

        return view('admin.cache-management', compact(
            'cacheStats',
            'dbStats',
            'slowQueries',
            'slowRequests',
            'nPlusOneDetections'
        ));
    }

    /**
     * Get real-time cache statistics
     *
     * @return JsonResponse
     */
    public function getCacheStats(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getCacheStats();
            $dbStats = $this->dbOptimizer->getPerformanceStats();

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'cache'     => $stats,
                    'database'  => $dbStats,
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Cache stats retrieval failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve cache statistics',
            ], 500);
        }
    }

    /**
     * Clear cache by layer or specific keys
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'layer'   => 'nullable|string|in:events,tickets,monitoring,users,system,analytics,all',
            'keys'    => 'nullable|array',
            'keys.*'  => 'string',
            'cascade' => 'boolean',
        ]);

        try {
            $layer = $request->input('layer', 'all');
            $keys = $request->input('keys', []);
            $cascade = $request->input('cascade', TRUE);

            $results = [];

            if ($layer === 'all') {
                // Clear all cache layers
                foreach ([
                    RedisCacheService::LAYER_EVENTS,
                    RedisCacheService::LAYER_TICKETS,
                    RedisCacheService::LAYER_MONITORING,
                    RedisCacheService::LAYER_USERS,
                    RedisCacheService::LAYER_SYSTEM,
                    RedisCacheService::LAYER_ANALYTICS,
                ] as $layerName) {
                    $invalidated = $this->cacheService->invalidateLayer($layerName, [], $cascade);
                    $results[$layerName] = $invalidated;
                }

                // Also clear Laravel's default cache
                Cache::flush();
            } else {
                // Clear specific layer
                $invalidated = $this->cacheService->invalidateLayer($layer, $keys, $cascade);
                $results[$layer] = $invalidated;
            }

            Log::info('Cache cleared', [
                'layer'   => $layer,
                'keys'    => $keys,
                'cascade' => $cascade,
                'results' => $results,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Cache cleared successfully',
                'data'    => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'layer' => $request->input('layer'),
                'keys'  => $request->input('keys'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up cache layers
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function warmupCache(Request $request): JsonResponse
    {
        $request->validate([
            'layers'         => 'nullable|array',
            'layers.*'       => 'string|in:events,tickets,monitoring,users,system,analytics',
            'custom_queries' => 'nullable|array',
        ]);

        try {
            $layers = $request->input('layers', []);
            $customQueries = $request->input('custom_queries', []);

            $warmupConfig = [];

            // Build warmup configuration based on selected layers
            if (empty($layers) || in_array('events', $layers)) {
                $warmupConfig[RedisCacheService::LAYER_EVENTS] = $this->getEventWarmupQueries();
            }

            if (empty($layers) || in_array('tickets', $layers)) {
                $warmupConfig[RedisCacheService::LAYER_TICKETS] = $this->getTicketWarmupQueries();
            }

            if (empty($layers) || in_array('system', $layers)) {
                $warmupConfig[RedisCacheService::LAYER_SYSTEM] = $this->getSystemWarmupQueries();
            }

            // Add custom queries if provided
            foreach ($customQueries as $layer => $queries) {
                if (isset($warmupConfig[$layer])) {
                    $warmupConfig[$layer] = array_merge($warmupConfig[$layer], $queries);
                } else {
                    $warmupConfig[$layer] = $queries;
                }
            }

            $results = $this->cacheService->warmupLayers($warmupConfig);

            Log::info('Cache warmup completed', [
                'layers'  => $layers,
                'results' => $results,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Cache warmup completed',
                'data'    => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Cache warmup failed', [
                'error'  => $e->getMessage(),
                'layers' => $request->input('layers'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to warm up cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cache health check
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getCacheStats();
            $health = $stats['health'] ?? [];

            $overallHealth = [
                'status'          => $health['status'] ?? 'unknown',
                'issues'          => $health['issues'] ?? [],
                'recommendations' => $health['recommendations'] ?? [],
                'checks'          => [],
            ];

            // Perform additional health checks
            $overallHealth['checks'] = [
                'redis_connection' => $this->checkRedisConnection(),
                'memory_usage'     => $this->checkMemoryUsage(),
                'hit_ratio'        => $this->checkHitRatio(),
                'slow_queries'     => $this->checkSlowQueries(),
                'error_rate'       => $this->checkErrorRate(),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $overallHealth,
            ]);
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Health check failed',
                'data'    => [
                    'status'          => 'error',
                    'issues'          => ['Health check system unavailable'],
                    'recommendations' => ['Check system logs for errors'],
                ],
            ]);
        }
    }

    /**
     * Get query performance analysis
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getQueryAnalysis(Request $request): JsonResponse
    {
        try {
            $timeframe = $request->input('timeframe', '1h'); // 1h, 6h, 24h, 7d

            $slowQueries = Cache::get('slow_queries', []);
            $slowRequests = Cache::get('slow_requests', []);
            $nPlusOneDetections = Cache::get('n_plus_one_detections', []);

            // Filter by timeframe
            $cutoffTime = $this->getCutoffTime($timeframe);

            $filteredSlowQueries = array_filter($slowQueries, function ($query) use ($cutoffTime) {
                return ($query['timestamp'] ?? 0) > $cutoffTime;
            });

            $filteredSlowRequests = array_filter($slowRequests, function ($request) use ($cutoffTime) {
                return isset($request['timestamp']) && $request['timestamp']->timestamp > $cutoffTime;
            });

            $filteredNPlusOne = array_filter($nPlusOneDetections, function ($detection) use ($cutoffTime) {
                return isset($detection['detected_at']) && $detection['detected_at']->timestamp > $cutoffTime;
            });

            // Generate optimization suggestions
            $suggestions = $this->generateOptimizationSuggestions(
                $filteredSlowQueries,
                $filteredSlowRequests,
                $filteredNPlusOne
            );

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'slow_queries'          => array_values($filteredSlowQueries),
                    'slow_requests'         => array_values($filteredSlowRequests),
                    'n_plus_one_detections' => array_values($filteredNPlusOne),
                    'suggestions'           => $suggestions,
                    'timeframe'             => $timeframe,
                    'stats'                 => [
                        'slow_query_count'   => count($filteredSlowQueries),
                        'slow_request_count' => count($filteredSlowRequests),
                        'n_plus_one_count'   => count($filteredNPlusOne),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Query analysis failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate query analysis',
            ], 500);
        }
    }

    /**
     * Export cache and performance metrics
     *
     * @param  Request                                                         $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportMetrics(Request $request)
    {
        $format = $request->input('format', 'json'); // json, csv, xlsx

        try {
            $data = [
                'cache_stats'           => $this->cacheService->getCacheStats(),
                'db_stats'              => $this->dbOptimizer->getPerformanceStats(),
                'slow_queries'          => Cache::get('slow_queries', []),
                'slow_requests'         => Cache::get('slow_requests', []),
                'n_plus_one_detections' => Cache::get('n_plus_one_detections', []),
                'exported_at'           => now(),
            ];

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data);
                case 'xlsx':
                    return $this->exportToXlsx($data);
                default:
                    return response()->json($data);
            }
        } catch (\Exception $e) {
            Log::error('Metrics export failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export metrics',
            ], 500);
        }
    }

    /**
     * Optimize cache configuration
     *
     * @return JsonResponse
     */
    public function optimizeCache(): JsonResponse
    {
        try {
            $optimizations = [];
            $stats = $this->cacheService->getCacheStats();

            // Analyze and suggest optimizations
            $hitRatio = $stats['performance']['hit_ratio'] ?? 0;
            if ($hitRatio < 80) {
                $optimizations[] = [
                    'type'       => 'hit_ratio',
                    'issue'      => "Low cache hit ratio: {$hitRatio}%",
                    'suggestion' => 'Increase TTL for frequently accessed data',
                    'action'     => 'increase_ttl',
                    'priority'   => 'high',
                ];
            }

            // Check for memory optimization opportunities
            $redisInfo = $stats['redis'] ?? [];
            if (isset($redisInfo['memory_used'])) {
                $memoryUsed = $this->parseMemoryString($redisInfo['memory_used']);
                if ($memoryUsed > 512 * 1024 * 1024) { // 512MB
                    $optimizations[] = [
                        'type'       => 'memory',
                        'issue'      => "High memory usage: {$redisInfo['memory_used']}",
                        'suggestion' => 'Enable compression for large data sets',
                        'action'     => 'enable_compression',
                        'priority'   => 'medium',
                    ];
                }
            }

            // Check for layer-specific optimizations
            foreach ($stats['layers'] ?? [] as $layer => $layerStats) {
                if (($layerStats['hit_ratio'] ?? 0) < 60) {
                    $optimizations[] = [
                        'type'       => 'layer_optimization',
                        'issue'      => "Poor performance in {$layer} layer",
                        'suggestion' => "Review {$layer} caching strategy",
                        'action'     => 'review_layer',
                        'priority'   => 'medium',
                        'layer'      => $layer,
                    ];
                }
            }

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'optimizations'             => $optimizations,
                    'current_performance_score' => $this->calculatePerformanceScore($stats),
                    'recommendations'           => $this->generateRecommendations($optimizations),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Cache optimization analysis failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to analyze cache optimization opportunities',
            ], 500);
        }
    }

    /**
     * Get event warmup queries
     */
    protected function getEventWarmupQueries(): array
    {
        return [
            'upcoming_events' => function () {
                // This would be replaced with actual event query
                return ['upcoming' => 'events_data'];
            },
            'popular_events' => function () {
                return ['popular' => 'events_data'];
            },
            'featured_events' => function () {
                return ['featured' => 'events_data'];
            },
        ];
    }

    /**
     * Get ticket warmup queries
     */
    protected function getTicketWarmupQueries(): array
    {
        return [
            'available_tickets' => function () {
                return ['available' => 'tickets_data'];
            },
            'price_ranges' => function () {
                return ['price_ranges' => 'pricing_data'];
            },
        ];
    }

    /**
     * Get system warmup queries
     */
    protected function getSystemWarmupQueries(): array
    {
        return [
            'app_config' => function () {
                return config('app');
            },
            'feature_flags' => function () {
                return ['features' => 'enabled'];
            },
        ];
    }

    /**
     * Check Redis connection health
     */
    protected function checkRedisConnection(): array
    {
        try {
            $redis = Redis::connection();
            $response = $redis->ping();

            return [
                'status'        => 'healthy',
                'message'       => 'Redis connection is active',
                'response_time' => 0, // Would measure actual response time
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check memory usage health
     */
    protected function checkMemoryUsage(): array
    {
        try {
            $stats = $this->cacheService->getCacheStats();
            $redisInfo = $stats['redis'] ?? [];
            $memoryUsed = $redisInfo['memory_used'] ?? 'unknown';

            if ($memoryUsed === 'unknown') {
                return [
                    'status'  => 'warning',
                    'message' => 'Unable to determine memory usage',
                ];
            }

            $memoryBytes = $this->parseMemoryString($memoryUsed);
            $threshold = 1024 * 1024 * 1024; // 1GB

            return [
                'status'          => $memoryBytes > $threshold ? 'warning' : 'healthy',
                'message'         => "Memory usage: {$memoryUsed}",
                'usage_bytes'     => $memoryBytes,
                'threshold_bytes' => $threshold,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Memory check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache hit ratio health
     */
    protected function checkHitRatio(): array
    {
        try {
            $stats = $this->cacheService->getCacheStats();
            $hitRatio = $stats['performance']['hit_ratio'] ?? 0;

            $status = 'healthy';
            if ($hitRatio < 70) {
                $status = 'warning';
            } elseif ($hitRatio < 50) {
                $status = 'error';
            }

            return [
                'status'    => $status,
                'message'   => "Cache hit ratio: {$hitRatio}%",
                'hit_ratio' => $hitRatio,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Hit ratio check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check slow queries health
     */
    protected function checkSlowQueries(): array
    {
        try {
            $slowQueries = Cache::get('slow_queries', []);
            $recentCount = count(array_filter($slowQueries, function ($query) {
                return ($query['timestamp'] ?? 0) > (time() - 3600); // Last hour
            }));

            $status = 'healthy';
            if ($recentCount > 10) {
                $status = 'warning';
            } elseif ($recentCount > 50) {
                $status = 'error';
            }

            return [
                'status'       => $status,
                'message'      => "Slow queries in last hour: {$recentCount}",
                'recent_count' => $recentCount,
                'total_count'  => count($slowQueries),
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Slow query check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check error rate health
     */
    protected function checkErrorRate(): array
    {
        try {
            $stats = $this->cacheService->getCacheStats();
            // This would be implemented based on actual error tracking
            $errorRate = 0; // Placeholder

            return [
                'status'     => $errorRate > 5 ? 'warning' : 'healthy',
                'message'    => "Error rate: {$errorRate}%",
                'error_rate' => $errorRate,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Error rate check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get cutoff time for timeframe filtering
     */
    protected function getCutoffTime(string $timeframe): int
    {
        switch ($timeframe) {
            case '1h':
                return time() - 3600;
            case '6h':
                return time() - (6 * 3600);
            case '24h':
                return time() - (24 * 3600);
            case '7d':
                return time() - (7 * 24 * 3600);
            default:
                return time() - 3600;
        }
    }

    /**
     * Generate optimization suggestions
     */
    protected function generateOptimizationSuggestions(array $slowQueries, array $slowRequests, array $nPlusOne): array
    {
        $suggestions = [];

        if (count($slowQueries) > 0) {
            $suggestions[] = [
                'type'       => 'slow_queries',
                'priority'   => 'high',
                'suggestion' => 'Review and optimize ' . count($slowQueries) . ' slow queries',
                'action'     => 'Add database indexes or optimize query logic',
            ];
        }

        if (count($nPlusOne) > 0) {
            $suggestions[] = [
                'type'       => 'n_plus_one',
                'priority'   => 'high',
                'suggestion' => 'Fix ' . count($nPlusOne) . ' potential N+1 query issues',
                'action'     => 'Implement eager loading with ->with() method',
            ];
        }

        if (count($slowRequests) > 0) {
            $suggestions[] = [
                'type'       => 'slow_requests',
                'priority'   => 'medium',
                'suggestion' => 'Optimize ' . count($slowRequests) . ' slow requests',
                'action'     => 'Implement caching or optimize business logic',
            ];
        }

        return $suggestions;
    }

    /**
     * Calculate overall performance score
     */
    protected function calculatePerformanceScore(array $stats): int
    {
        $score = 100;

        // Penalize for low hit ratio
        $hitRatio = $stats['performance']['hit_ratio'] ?? 0;
        if ($hitRatio < 80) {
            $score -= (80 - $hitRatio);
        }

        // Penalize for slow queries
        $slowQueries = Cache::get('slow_queries', []);
        $score -= min(30, count($slowQueries) * 2);

        // Penalize for N+1 queries
        $nPlusOne = Cache::get('n_plus_one_detections', []);
        $score -= min(20, count($nPlusOne) * 3);

        return max(0, $score);
    }

    /**
     * Generate recommendations based on optimizations
     */
    protected function generateRecommendations(array $optimizations): array
    {
        $recommendations = [];

        foreach ($optimizations as $optimization) {
            switch ($optimization['type']) {
                case 'hit_ratio':
                    $recommendations[] = 'Increase TTL values for frequently accessed data';
                    $recommendations[] = 'Implement cache warming for critical queries';

                    break;
                case 'memory':
                    $recommendations[] = 'Enable compression for large data sets';
                    $recommendations[] = 'Review cache expiration policies';

                    break;
                case 'layer_optimization':
                    $recommendations[] = "Review caching strategy for {$optimization['layer']} layer";

                    break;
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Parse memory string to bytes
     */
    protected function parseMemoryString(string $memory): int
    {
        preg_match('/([0-9.]+)([KMGT]?)/', $memory, $matches);

        if (empty($matches)) {
            return 0;
        }

        $value = (float) $matches[1];
        $unit = $matches[2] ?? '';

        $multipliers = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
            'T' => 1024 * 1024 * 1024 * 1024,
        ];

        return (int) ($value * ($multipliers[$unit] ?? 1));
    }

    /**
     * Export data to CSV format (placeholder)
     */
    protected function exportToCsv(array $data)
    {
        // Implementation would create CSV export
        return response()->json(['message' => 'CSV export not yet implemented']);
    }

    /**
     * Export data to XLSX format (placeholder)
     */
    protected function exportToXlsx(array $data)
    {
        // Implementation would create XLSX export
        return response()->json(['message' => 'XLSX export not yet implemented']);
    }
}
