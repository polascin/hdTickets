<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScrapingStats;
use App\Models\User;
use App\Services\UserRotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScrapingController extends Controller
{
    protected $userRotationService;

    public function __construct(UserRotationService $userRotationService)
    {
        $this->middleware('auth');
        $this->middleware('admin:access_scraping');
        $this->userRotationService = $userRotationService;
    }

    /**
     * Display scraping dashboard
     */
    public function index()
    {
        $stats = $this->getScrapingStats();
        $platforms = $this->getPlatformStats();
        $recentOperations = $this->getRecentOperations();
        $userRotationStats = $this->userRotationService->getRotationStatistics();
        $advancedStats = $this->getAdvancedStats();

        return view('admin.scraping.index', compact(
            'stats', 'platforms', 'recentOperations', 'userRotationStats', 'advancedStats'
        ));
    }

    /**
     * Get scraping statistics
     */
    public function getStats()
    {
        return response()->json($this->getScrapingStats());
    }

    /**
     * Get platform-specific statistics
     */
    public function getPlatformStats()
    {
        $platforms = ['stubhub', 'viagogo', 'seatgeek', 'tickpick', 'fanzone'];
        $stats = [];

        foreach ($platforms as $platform) {
            $platformStats = ScrapingStats::where('platform', $platform)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->select([
                    DB::raw('COUNT(*) as total_operations'),
                    DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
                    DB::raw('AVG(response_time_ms) as avg_response_time'),
                    DB::raw('SUM(results_count) as total_results')
                ])
                ->first();

            $successRate = $platformStats->total_operations > 0 
                ? ($platformStats->successful_operations / $platformStats->total_operations) * 100 
                : 0;

            $stats[$platform] = [
                'name' => ucfirst($platform),
                'total_operations' => $platformStats->total_operations ?? 0,
                'successful_operations' => $platformStats->successful_operations ?? 0,
                'success_rate' => round($successRate, 2),
                'avg_response_time' => round($platformStats->avg_response_time ?? 0, 2),
                'total_results' => $platformStats->total_results ?? 0,
                'status' => $this->getPlatformStatus($successRate),
                'dedicated_users' => User::where('email', 'LIKE', "%{$platform}.agent%@scrapingtest.com")->count()
            ];
        }

        return $stats;
    }

    /**
     * Get recent scraping operations
     */
    public function getRecentOperations(int $limit = 50)
    {
        return ScrapingStats::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($stat) {
                return [
                    'id' => $stat->id,
                    'platform' => $stat->platform,
                    'operation' => $stat->operation,
                    'status' => $stat->status,
                    'response_time' => $stat->response_time_ms,
                    'results_count' => $stat->results_count,
                    'user_agent' => $stat->user_agent ? substr($stat->user_agent, 0, 50) . '...' : null,
                    'ip_address' => $stat->ip_address,
                    'error_message' => $stat->error_message,
                    'created_at' => $stat->created_at->toISOString(),
                    'formatted_time' => $stat->created_at->diffForHumans()
                ];
            });
    }

    /**
     * Get user rotation management data
     */
    public function getUserRotation()
    {
        $rotationStats = $this->userRotationService->getRotationStatistics();
        
        $scrapingUsers = [
            'premium_customers' => User::where('email', 'LIKE', 'premium.customer%@scrapingtest.com')->get(),
            'platform_agents' => User::where('email', 'LIKE', '%.agent%@scrapingtest.com')->get(),
            'rotation_pool' => User::where('email', 'LIKE', '%@rotationpool.com')->get()
        ];

        return response()->json([
            'statistics' => $rotationStats,
            'users' => $scrapingUsers
        ]);
    }

    /**
     * Test user rotation
     */
    public function testRotation(Request $request)
    {
        $request->validate([
            'platform' => 'nullable|string',
            'operation' => 'nullable|string',
            'count' => 'integer|min:1|max:10'
        ]);

        $results = [];
        $count = $request->get('count', 1);

        for ($i = 0; $i < $count; $i++) {
            $user = $this->userRotationService->getRotatedUser(
                $request->get('platform'),
                $request->get('operation')
            );

            $results[] = [
                'attempt' => $i + 1,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'user_role' => $user->role ?? null,
                'success' => $user !== null
            ];
        }

        return response()->json([
            'test_results' => $results,
            'summary' => [
                'total_attempts' => $count,
                'successful_rotations' => collect($results)->where('success', true)->count(),
                'success_rate' => (collect($results)->where('success', true)->count() / $count) * 100
            ]
        ]);
    }

    /**
     * Manage scraping configuration
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'max_concurrent_requests' => 'integer|min:1|max:100',
            'request_delay_ms' => 'integer|min:0|max:10000',
            'retry_attempts' => 'integer|min:1|max:10',
            'user_rotation_enabled' => 'boolean',
            'platform_rotation_interval' => 'integer|min:1|max:3600'
        ]);

        $config = $request->only([
            'max_concurrent_requests',
            'request_delay_ms',
            'retry_attempts',
            'user_rotation_enabled',
            'platform_rotation_interval'
        ]);

        Cache::put('scraping_config', $config, now()->addHours(24));

        return response()->json([
            'success' => true,
            'message' => 'Scraping configuration updated successfully',
            'config' => $config
        ]);
    }

    /**
     * Get current scraping configuration
     */
    public function getConfig()
    {
        $defaultConfig = [
            'max_concurrent_requests' => 5,
            'request_delay_ms' => 1000,
            'retry_attempts' => 3,
            'user_rotation_enabled' => true,
            'platform_rotation_interval' => 300
        ];

        $config = Cache::get('scraping_config', $defaultConfig);

        return response()->json($config);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics()
    {
        $last24Hours = ScrapingStats::whereDate('created_at', '>=', Carbon::now()->subDay())
            ->select([
                DB::raw('platform'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_requests'),
                DB::raw('AVG(response_time_ms) as avg_response_time'),
                DB::raw('MIN(response_time_ms) as min_response_time'),
                DB::raw('MAX(response_time_ms) as max_response_time')
            ])
            ->groupBy('platform')
            ->get();

        $hourlyStats = ScrapingStats::whereDate('created_at', '>=', Carbon::now()->subDay())
            ->select([
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as requests'),
                DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful'),
                DB::raw('AVG(response_time_ms) as avg_response_time')
            ])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'platform_metrics' => $last24Hours,
            'hourly_performance' => $hourlyStats,
            'summary' => [
                'total_requests_24h' => $last24Hours->sum('total_requests'),
                'successful_requests_24h' => $last24Hours->sum('successful_requests'),
                'overall_success_rate' => $last24Hours->sum('total_requests') > 0 
                    ? ($last24Hours->sum('successful_requests') / $last24Hours->sum('total_requests')) * 100 
                    : 0,
                'avg_response_time_24h' => $last24Hours->avg('avg_response_time')
            ]
        ]);
    }

    /**
     * Private helper methods
     */
    private function getScrapingStats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $lastWeek = Carbon::now()->subWeek();

        return [
            'total_operations' => ScrapingStats::count(),
            'operations_today' => ScrapingStats::whereDate('created_at', $today)->count(),
            'operations_yesterday' => ScrapingStats::whereDate('created_at', $yesterday)->count(),
            'success_rate' => $this->getSuccessRate($today),
            'success_rate_today' => $this->getSuccessRate($today),
            'success_rate_week' => $this->getSuccessRate($lastWeek),
            'avg_response_time' => ScrapingStats::whereDate('created_at', '>=', $lastWeek)
                ->avg('response_time_ms') ?? 0,
            'active_platforms' => ScrapingStats::distinct('platform')->count(),
            'total_results_scraped' => ScrapingStats::sum('results_count'),
            'error_rate_today' => $this->getErrorRate($today)
        ];
    }

    private function getSuccessRate($since)
    {
        $total = ScrapingStats::whereDate('created_at', '>=', $since)->count();
        $successful = ScrapingStats::whereDate('created_at', '>=', $since)
            ->where('status', 'success')->count();

        return $total > 0 ? ($successful / $total) * 100 : 0;
    }

    private function getErrorRate($since)
    {
        $total = ScrapingStats::whereDate('created_at', '>=', $since)->count();
        $errors = ScrapingStats::whereDate('created_at', '>=', $since)
            ->whereIn('status', ['failed', 'timeout', 'rate_limited', 'bot_detected'])
            ->count();

        return $total > 0 ? ($errors / $total) * 100 : 0;
    }

    private function getPlatformStatus($successRate)
    {
        if ($successRate >= 90) return 'excellent';
        if ($successRate >= 75) return 'good';
        if ($successRate >= 50) return 'warning';
        return 'critical';
    }

    /**
     * Get advanced scraping statistics for anti-detection and high-demand features
     */
    private function getAdvancedStats()
    {
        $today = Carbon::today();
        $totalOpsToday = ScrapingStats::whereDate('created_at', $today)->count();
        
        return [
            // Anti-detection operations (operations with special user agents or proxy rotation)
            'anti_detection_operations' => ScrapingStats::whereDate('created_at', $today)
                ->where(function($query) {
                    $query->whereNotNull('user_agent')
                          ->orWhereNotNull('ip_address')
                          ->orWhere('operation', 'LIKE', '%protected%');
                })->count(),
            
            // High-demand sessions (operations with high results count or priority platforms)
            'high_demand_sessions' => ScrapingStats::whereDate('created_at', $today)
                ->where(function($query) {
                    $query->where('results_count', '>', 100)
                          ->orWhereIn('platform', ['stubhub', 'ticketmaster'])
                          ->orWhere('operation', 'LIKE', '%priority%');
                })->count(),
            
            // Average response time for high-demand operations
            'high_demand_avg_time' => ScrapingStats::whereDate('created_at', $today)
                ->where(function($query) {
                    $query->where('results_count', '>', 100)
                          ->orWhereIn('platform', ['stubhub', 'ticketmaster']);
                })->avg('response_time_ms') ?? 0,
            
            // Success rate with protection mechanisms
            'protected_success_rate' => $this->getProtectedSuccessRate($today),
            
            // Threat detection stats
            'threats_detected' => ScrapingStats::whereDate('created_at', $today)
                ->whereIn('status', ['bot_detected', 'rate_limited', 'captcha_required'])
                ->count(),
            
            'threats_blocked' => ScrapingStats::whereDate('created_at', $today)
                ->where('status', 'bot_detected')
                ->count(),
            
            // Queue and optimization stats
            'priority_queue_size' => rand(15, 35), // Simulated - replace with actual queue size
            'dedicated_pools' => 3, // Number of dedicated proxy pools
            'cache_hit_rate' => rand(75, 95), // Simulated cache hit rate
        ];
    }

    /**
     * Get success rate for operations with protection mechanisms
     */
    private function getProtectedSuccessRate($since)
    {
        $protectedTotal = ScrapingStats::whereDate('created_at', '>=', $since)
            ->where(function($query) {
                $query->whereNotNull('user_agent')
                      ->orWhereNotNull('ip_address');
            })->count();
        
        $protectedSuccessful = ScrapingStats::whereDate('created_at', '>=', $since)
            ->where('status', 'success')
            ->where(function($query) {
                $query->whereNotNull('user_agent')
                      ->orWhereNotNull('ip_address');
            })->count();
        
        return $protectedTotal > 0 ? round(($protectedSuccessful / $protectedTotal) * 100, 1) : 0;
    }

    /**
     * Test anti-detection systems
     */
    public function testAntiDetection(Request $request)
    {
        $request->validate([
            'platforms' => 'array',
            'platforms.*' => 'string',
            'test_count' => 'integer|min:1|max:10'
        ]);

        $platforms = $request->get('platforms', ['stubhub', 'ticketmaster', 'seatgeek']);
        $testCount = $request->get('test_count', 3);
        
        $results = [];
        $totalTests = 0;
        $successfulTests = 0;
        $totalResponseTime = 0;

        foreach ($platforms as $platform) {
            for ($i = 0; $i < $testCount; $i++) {
                $totalTests++;
                $responseTime = rand(800, 3000); // Simulated response time
                $totalResponseTime += $responseTime;
                
                // Simulate anti-detection test result
                $bypassed = rand(0, 100) > 30; // 70% success rate for demo
                if ($bypassed) {
                    $successfulTests++;
                }
                
                $results[] = [
                    'platform' => ucfirst($platform),
                    'protection_methods' => ['User-Agent Rotation', 'IP Masking', 'Request Timing'],
                    'bypassed' => $bypassed,
                    'response_time' => $responseTime
                ];
            }
        }

        return response()->json([
            'summary' => [
                'total_tests' => $totalTests,
                'successful_tests' => $successfulTests,
                'detection_rate' => round((($totalTests - $successfulTests) / $totalTests) * 100, 1),
                'avg_response_time' => round($totalResponseTime / $totalTests)
            ],
            'platform_results' => $results
        ]);
    }

    /**
     * Test high-demand scraping optimizations
     */
    public function testHighDemand(Request $request)
    {
        $request->validate([
            'concurrent_requests' => 'integer|min:1|max:20',
            'priority_events' => 'array'
        ]);

        $concurrentRequests = $request->get('concurrent_requests', 10);
        $priorityEvents = $request->get('priority_events', ['concert', 'sports']);
        
        // Simulate high-demand test results
        $totalProcessed = $concurrentRequests * rand(8, 12);
        $successfulProcessed = round($totalProcessed * (rand(85, 95) / 100));
        
        $queueStats = [
            [
                'queue_type' => 'Priority Queue',
                'processed' => round($totalProcessed * 0.4),
                'avg_wait_time' => rand(50, 200)
            ],
            [
                'queue_type' => 'Standard Queue', 
                'processed' => round($totalProcessed * 0.6),
                'avg_wait_time' => rand(200, 500)
            ]
        ];

        return response()->json([
            'summary' => [
                'concurrent_requests' => $concurrentRequests,
                'total_processed' => $totalProcessed,
                'success_rate' => round(($successfulProcessed / $totalProcessed) * 100, 1),
                'avg_processing_time' => rand(1200, 2500)
            ],
            'queue_stats' => $queueStats
        ]);
    }

    /**
     * Get advanced scraping logs
     */
    public function getAdvancedLogs(Request $request)
    {
        // Simulate advanced logs data
        $logs = [
            [
                'timestamp' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
                'type' => 'anti-detection',
                'message' => 'User-Agent rotation successful for StubHub scraping',
                'details' => 'Rotated to Chrome/120.0 Windows agent'
            ],
            [
                'timestamp' => now()->subMinutes(12)->format('Y-m-d H:i:s'),
                'type' => 'high-demand',
                'message' => 'Priority queue processed 45 concert ticket requests',
                'details' => 'Average processing time: 1.2s per request'
            ],
            [
                'timestamp' => now()->subMinutes(18)->format('Y-m-d H:i:s'),
                'type' => 'anti-detection',
                'message' => 'IP rotation triggered for Ticketmaster',
                'details' => 'Switched from proxy pool A to pool B'
            ],
            [
                'timestamp' => now()->subMinutes(25)->format('Y-m-d H:i:s'),
                'type' => 'error',
                'message' => 'Rate limit detected on SeatGeek',
                'details' => 'Implemented exponential backoff strategy'
            ],
            [
                'timestamp' => now()->subMinutes(32)->format('Y-m-d H:i:s'),
                'type' => 'high-demand',
                'message' => 'Load balancer optimized for sports events',
                'details' => 'Redistributed traffic across 3 dedicated pools'
            ]
        ];

        return response()->json([
            'logs' => $logs
        ]);
    }

    /**
     * Configure anti-detection settings
     */
    public function configureAntiDetection(Request $request)
    {
        $request->validate([
            'user_agent_rotation' => 'string|in:aggressive,moderate,conservative',
            'ip_rotation' => 'string|in:high,medium,low',
            'min_delay' => 'integer|min:0',
            'max_delay' => 'integer|min:0',
            'fingerprint_protection' => 'boolean',
            'captcha_solver' => 'boolean'
        ]);

        $config = $request->only([
            'user_agent_rotation',
            'ip_rotation', 
            'min_delay',
            'max_delay',
            'fingerprint_protection',
            'captcha_solver'
        ]);

        // Store configuration in cache
        Cache::put('scraping.anti_detection_config', $config, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Anti-detection configuration saved successfully',
            'config' => $config
        ]);
    }

    /**
     * Configure high-demand optimization settings
     */  
    public function configureHighDemand(Request $request)
    {
        $request->validate([
            'priority_queue_size' => 'integer|min:10',
            'dedicated_pools' => 'integer|min:1',
            'load_balancing' => 'string|in:round_robin,least_connections,weighted',
            'cache_ttl' => 'integer|min:1',
            'max_cache_size' => 'integer|min:50',
            'auto_scaling' => 'boolean'
        ]);

        $config = $request->only([
            'priority_queue_size',
            'dedicated_pools',
            'load_balancing',
            'cache_ttl',
            'max_cache_size',
            'auto_scaling'
        ]);

        // Store configuration in cache
        Cache::put('scraping.high_demand_config', $config, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'High-demand optimization configuration saved successfully',
            'config' => $config
        ]);
    }
}
