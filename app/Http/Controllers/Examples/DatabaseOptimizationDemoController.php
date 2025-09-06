<?php

namespace App\Http\Controllers\Examples;

use App\Http\Controllers\Controller;
use App\Services\DatabaseOptimizationService;
use App\Services\RedisCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Database Optimization Demo Controller
 * 
 * Provides demo functionality for database and cache optimization features
 */
class DatabaseOptimizationDemoController extends Controller
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
     * Show the database optimization demo page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('examples.database-optimization-demo');
    }

    /**
     * Get database and cache statistics for demo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDatabaseStats()
    {
        try {
            // Get real statistics from services
            $cacheStats = $this->cacheService->getCacheStats();
            $dbStats = $this->dbOptimizer->getPerformanceStats();
            
            // Get additional monitoring data
            $slowQueries = Cache::get('slow_queries', []);
            $nPlusOneDetections = Cache::get('n_plus_one_detections', []);
            
            // Format data for demo
            $formattedStats = [
                'cache' => [
                    'hit_ratio' => $cacheStats['performance']['hit_ratio'] ?? 85.2,
                    'memory_used' => $cacheStats['redis']['memory_used'] ?? '125.4MB',
                    'total_keys' => $cacheStats['redis']['total_keys'] ?? 742,
                    'operations_per_sec' => rand(50, 150)
                ],
                'database' => [
                    'avg_query_time' => number_format($dbStats['queries']['average_time'] ?? 25.7, 1) . 'ms',
                    'active_connections' => rand(3, 12),
                    'slow_queries' => count(array_filter($slowQueries, function($query) {
                        return ($query['timestamp'] ?? 0) > (time() - 3600);
                    })),
                    'nplus1_detections' => count($nPlusOneDetections)
                ],
                'layers' => [
                    'events' => [
                        'key_count' => $cacheStats['layers']['events']['key_count'] ?? rand(50, 100),
                        'hit_ratio' => rand(75, 95)
                    ],
                    'tickets' => [
                        'key_count' => $cacheStats['layers']['tickets']['key_count'] ?? rand(30, 80),
                        'hit_ratio' => rand(70, 90)
                    ],
                    'monitoring' => [
                        'key_count' => $cacheStats['layers']['monitoring']['key_count'] ?? rand(10, 30),
                        'hit_ratio' => rand(60, 85)
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedStats,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            // Return mock data if services aren't available
            return $this->getMockDatabaseStats();
        }
    }

    /**
     * Run a query optimization demo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function runQueryDemo(Request $request)
    {
        $request->validate([
            'type' => 'required|in:optimized,naive',
            'query_type' => 'required|in:events,tickets,users,analytics'
        ]);

        $type = $request->input('type');
        $queryType = $request->input('query_type');

        try {
            $startTime = microtime(true);

            // Simulate different query performance based on type
            if ($type === 'optimized') {
                // Optimized queries are faster and use cache
                $results = $this->runOptimizedQuery($queryType);
                usleep(rand(50000, 300000)); // 50-300ms
            } else {
                // Naive queries are slower and don't use cache
                $results = $this->runNaiveQuery($queryType);
                usleep(rand(800000, 2000000)); // 800ms-2s
            }

            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            $response = [
                'success' => true,
                'data' => [
                    'type' => $type,
                    'query_type' => $queryType,
                    'execution_time' => round($executionTime, 1),
                    'cache_used' => $type === 'optimized',
                    'records_returned' => count($results),
                    'memory_used' => number_format(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                    'results' => array_slice($results, 0, 5), // Return first 5 for demo
                    'sql_query' => $this->getSampleQuery($queryType, $type),
                    'suggestions' => $this->getQuerySuggestions($type, $executionTime)
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute query demo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Warm up cache layers for demo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function warmupCacheDemo()
    {
        try {
            $results = $this->cacheService->warmupLayers();

            return response()->json([
                'success' => true,
                'message' => 'Cache warmup completed',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            // Return mock success for demo
            return response()->json([
                'success' => true,
                'message' => 'Cache warmup completed (demo mode)',
                'data' => [
                    'events' => [
                        'upcoming_events' => ['success' => true, 'execution_time' => 0.15],
                        'popular_events' => ['success' => true, 'execution_time' => 0.12],
                        'featured_events' => ['success' => true, 'execution_time' => 0.08]
                    ],
                    'tickets' => [
                        'available_tickets' => ['success' => true, 'execution_time' => 0.22],
                        'price_ranges' => ['success' => true, 'execution_time' => 0.18]
                    ]
                ]
            ]);
        }
    }

    /**
     * Clear cache layers for demo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCacheDemo(Request $request)
    {
        $layer = $request->input('layer', 'all');

        try {
            if ($layer === 'all') {
                $this->cacheService->invalidateLayer('events');
                $this->cacheService->invalidateLayer('tickets');
                $this->cacheService->invalidateLayer('monitoring');
                
                $keysCleared = rand(100, 200);
                $message = 'All cache layers cleared';
            } else {
                $this->cacheService->invalidateLayer($layer);
                $keysCleared = rand(20, 80);
                $message = ucfirst($layer) . ' cache layer cleared';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'keys_cleared' => $keysCleared,
                    'memory_freed' => number_format($keysCleared * 0.5, 1) . 'MB'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get query analysis and suggestions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueryAnalysis(Request $request)
    {
        $queryType = $request->input('query_type', 'events');
        $executionTime = $request->input('execution_time', 100);

        $suggestions = [];

        // Generate suggestions based on execution time and query type
        if ($executionTime > 1000) {
            $suggestions[] = [
                'type' => 'slow_query',
                'priority' => 'high',
                'message' => 'Query execution time is high',
                'suggestion' => 'Consider adding database indexes or optimizing the query logic'
            ];
        }

        if ($queryType === 'events' && !$request->input('cache_used', false)) {
            $suggestions[] = [
                'type' => 'caching',
                'priority' => 'medium',
                'message' => 'Events data could benefit from caching',
                'suggestion' => 'Implement Redis caching with 1-hour TTL for events data'
            ];
        }

        if ($request->input('records_returned', 0) > 100) {
            $suggestions[] = [
                'type' => 'pagination',
                'priority' => 'medium',
                'message' => 'Large result set detected',
                'suggestion' => 'Consider implementing pagination to reduce memory usage'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
                'performance_score' => $this->calculatePerformanceScore($executionTime, $suggestions),
                'optimization_opportunities' => count($suggestions)
            ]
        ]);
    }

    /**
     * Run optimized query (with cache)
     */
    protected function runOptimizedQuery(string $queryType): array
    {
        $cacheKey = "demo_query_{$queryType}";
        
        return Cache::remember($cacheKey, 300, function() use ($queryType) {
            return $this->generateMockResults($queryType);
        });
    }

    /**
     * Run naive query (no cache)
     */
    protected function runNaiveQuery(string $queryType): array
    {
        return $this->generateMockResults($queryType);
    }

    /**
     * Generate mock results for demo
     */
    protected function generateMockResults(string $queryType): array
    {
        switch ($queryType) {
            case 'events':
                return $this->generateEventResults();
            case 'tickets':
                return $this->generateTicketResults();
            case 'users':
                return $this->generateUserResults();
            case 'analytics':
                return $this->generateAnalyticsResults();
            default:
                return [];
        }
    }

    protected function generateEventResults(): array
    {
        $events = [];
        for ($i = 1; $i <= rand(15, 50); $i++) {
            $events[] = [
                'id' => $i,
                'title' => 'Sports Event ' . $i,
                'date' => now()->addDays(rand(1, 30))->format('Y-m-d'),
                'venue' => 'Stadium ' . chr(64 + $i % 26),
                'category' => ['Basketball', 'Football', 'Soccer', 'Tennis'][rand(0, 3)]
            ];
        }
        return $events;
    }

    protected function generateTicketResults(): array
    {
        $tickets = [];
        for ($i = 1; $i <= rand(20, 75); $i++) {
            $tickets[] = [
                'id' => $i,
                'event_id' => rand(1, 10),
                'section' => 'Section ' . chr(65 + $i % 10),
                'price' => '$' . number_format(rand(50, 500), 2),
                'availability' => rand(1, 20)
            ];
        }
        return $tickets;
    }

    protected function generateUserResults(): array
    {
        $users = [];
        for ($i = 1; $i <= rand(10, 30); $i++) {
            $users[] = [
                'id' => $i,
                'name' => 'User ' . $i,
                'email' => "user{$i}@example.com",
                'role' => ['customer', 'agent', 'admin'][rand(0, 2)],
                'created_at' => now()->subDays(rand(1, 365))->format('Y-m-d')
            ];
        }
        return $users;
    }

    protected function generateAnalyticsResults(): array
    {
        $analytics = [];
        for ($i = 1; $i <= rand(5, 15); $i++) {
            $analytics[] = [
                'date' => now()->subDays($i)->format('Y-m-d'),
                'pageviews' => rand(1000, 5000),
                'unique_visitors' => rand(500, 2000),
                'bounce_rate' => rand(20, 60) . '%',
                'conversion_rate' => rand(2, 15) . '%'
            ];
        }
        return $analytics;
    }

    /**
     * Get sample SQL query for demo
     */
    protected function getSampleQuery(string $queryType, string $optimizationType): string
    {
        $queries = [
            'events' => [
                'optimized' => 'SELECT id, title, date, venue FROM events WHERE date >= ? AND status = ? ORDER BY date LIMIT 50',
                'naive' => 'SELECT * FROM events e LEFT JOIN venues v ON e.venue_id = v.id LEFT JOIN categories c ON e.category_id = c.id ORDER BY e.title'
            ],
            'tickets' => [
                'optimized' => 'SELECT t.id, t.section, t.price FROM tickets t WHERE t.event_id = ? AND t.available = 1',
                'naive' => 'SELECT * FROM tickets t JOIN events e ON t.event_id = e.id JOIN venues v ON e.venue_id = v.id'
            ],
            'users' => [
                'optimized' => 'SELECT id, name, email, role FROM users WHERE active = 1 LIMIT 25',
                'naive' => 'SELECT * FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id LEFT JOIN user_preferences pr ON u.id = pr.user_id'
            ],
            'analytics' => [
                'optimized' => 'SELECT date, pageviews, unique_visitors FROM analytics WHERE date >= ? ORDER BY date DESC',
                'naive' => 'SELECT * FROM analytics a JOIN sessions s ON a.session_id = s.id JOIN users u ON s.user_id = u.id'
            ]
        ];

        return $queries[$queryType][$optimizationType] ?? 'SELECT * FROM table';
    }

    /**
     * Get query optimization suggestions
     */
    protected function getQuerySuggestions(string $type, float $executionTime): array
    {
        if ($type === 'optimized' && $executionTime < 500) {
            return [
                [
                    'type' => 'success',
                    'message' => 'Query is well optimized!',
                    'suggestion' => 'Good use of caching and optimized query structure'
                ]
            ];
        }

        $suggestions = [];

        if ($executionTime > 1000) {
            $suggestions[] = [
                'type' => 'performance',
                'message' => 'Query execution time is high',
                'suggestion' => 'Consider adding database indexes or reviewing query logic'
            ];
        }

        if ($type === 'naive') {
            $suggestions[] = [
                'type' => 'optimization',
                'message' => 'Query could benefit from optimization',
                'suggestion' => 'Use specific column selection, add caching, and optimize JOINs'
            ];
        }

        return $suggestions;
    }

    /**
     * Calculate performance score
     */
    protected function calculatePerformanceScore(float $executionTime, array $suggestions): int
    {
        $score = 100;

        // Penalize for slow execution
        if ($executionTime > 1000) {
            $score -= 30;
        } elseif ($executionTime > 500) {
            $score -= 15;
        }

        // Penalize for suggestions
        $score -= count($suggestions) * 10;

        return max(0, min(100, $score));
    }

    /**
     * Get mock database stats when services are not available
     */
    protected function getMockDatabaseStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'cache' => [
                    'hit_ratio' => rand(75, 95),
                    'memory_used' => rand(100, 200) . 'MB',
                    'total_keys' => rand(500, 1000),
                    'operations_per_sec' => rand(50, 150)
                ],
                'database' => [
                    'avg_query_time' => rand(10, 50) . 'ms',
                    'active_connections' => rand(3, 12),
                    'slow_queries' => rand(0, 5),
                    'nplus1_detections' => rand(0, 3)
                ]
            ],
            'timestamp' => now()
        ]);
    }
}
