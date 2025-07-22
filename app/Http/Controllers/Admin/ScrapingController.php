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

        return view('admin.scraping.index', compact(
            'stats', 'platforms', 'recentOperations', 'userRotationStats'
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
            'success_rate_today' => $this->getSuccessRate($today),
            'success_rate_week' => $this->getSuccessRate($lastWeek),
            'avg_response_time' => ScrapingStats::whereDate('created_at', '>=', $lastWeek)
                ->avg('response_time_ms'),
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
}
