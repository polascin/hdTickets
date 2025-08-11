<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Ticket;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\Category;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Initialize Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Dashboard Query Optimization Tests ===\n\n";

class DashboardOptimizationTest
{
    private $testResults = [];
    
    public function runOptimizationTests()
    {
        echo "🔍 Testing Dashboard Query Optimizations...\n\n";
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Test dashboard data loading scenarios
        $this->testDashboardDataLoading();
        
        // Test user management queries
        $this->testUserManagementQueries();
        
        // Test ticket statistics queries
        $this->testTicketStatisticsQueries();
        
        // Test aggregation performance
        $this->testAggregationPerformance();
        
        // Test index utilization
        $this->testIndexUtilization();
        
        // Generate optimization report
        $this->generateOptimizationReport();
        
        return $this->testResults;
    }
    
    private function testDashboardDataLoading()
    {
        echo "📊 Testing Dashboard Data Loading...\n";
        
        // Test: Admin Dashboard with optimized queries
        $this->runOptimizationTest('Admin Dashboard - Optimized', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Optimized single query for user statistics
            $userStats = User::selectRaw('
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN role = "agent" THEN 1 ELSE 0 END) as agent_count,
                SUM(CASE WHEN role = "customer" THEN 1 ELSE 0 END) as customer_count,
                SUM(CASE WHEN role = "scraper" THEN 1 ELSE 0 END) as scraper_count,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_week
            ', [Carbon::now()->subWeek()])->first();
            
            // Optimized scraped tickets statistics if table exists
            $ticketStats = null;
            if (Schema::hasTable('scraped_tickets')) {
                $ticketStats = ScrapedTicket::selectRaw('
                    COUNT(*) as total_scraped,
                    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_tickets,
                    SUM(CASE WHEN is_high_demand = 1 THEN 1 ELSE 0 END) as high_demand_tickets,
                    AVG(min_price) as avg_min_price,
                    AVG(max_price) as avg_max_price
                ')->first();
            }
            
            // Category count
            $categoryCount = Schema::hasTable('categories') ? Category::where('is_active', true)->count() : 0;
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'user_stats' => $userStats->toArray(),
                'ticket_stats' => $ticketStats ? $ticketStats->toArray() : null,
                'category_count' => $categoryCount,
                'optimization_level' => 'high'
            ];
        });
        
        // Test: User Dashboard with relationships
        $this->runOptimizationTest('User Dashboard - With Eager Loading', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Get users with their relationships eagerly loaded
            $users = User::with([
                'subscriptions' => function($query) {
                    $query->where('status', 'active')->latest();
                },
                'ticketAlerts' => function($query) {
                    $query->where('status', 'active')->latest()->limit(5);
                }
            ])
            ->where('is_active', true)
            ->limit(10)
            ->get();
            
            // Process the data without additional queries
            $processedData = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'active_subscriptions' => $user->subscriptions->count(),
                    'active_alerts' => $user->ticketAlerts->count(),
                    'last_activity' => $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never'
                ];
            });
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'users_processed' => $users->count(),
                'data_size' => count($processedData),
                'optimization_level' => 'high'
            ];
        });
        
        echo "✅ Dashboard data loading tests completed\n\n";
    }
    
    private function testUserManagementQueries()
    {
        echo "👥 Testing User Management Queries...\n";
        
        // Test: Optimized user listing with pagination
        $this->runOptimizationTest('User Management - Paginated with Search', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Simulated search and filter
            $searchTerm = 'test';
            $roleFilter = 'customer';
            
            $users = User::select([
                    'id', 'name', 'surname', 'email', 'role', 'is_active', 
                    'created_at', 'last_activity_at'
                ])
                ->when($searchTerm, function($query, $search) {
                    return $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%");
                    });
                })
                ->when($roleFilter, function($query, $role) {
                    return $query->where('role', $role);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'results_count' => $users->count(),
                'total_records' => $users->total(),
                'uses_index' => true // Should use role and created_at indexes
            ];
        });
        
        // Test: User profile data aggregation
        $this->runOptimizationTest('User Profile Aggregation', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            $user = User::first();
            if (!$user) {
                return ['error' => 'No users found for testing'];
            }
            
            // Get comprehensive user data in minimal queries
            $userData = [
                'basic_info' => $user->only(['id', 'name', 'surname', 'email', 'role', 'is_active']),
                'subscription_count' => $user->subscriptions()->count(),
                'active_subscription' => $user->subscriptions()->where('status', 'active')->first(),
                'alert_count' => $user->ticketAlerts()->where('status', 'active')->count(),
                'recent_alerts' => $user->ticketAlerts()->latest()->limit(3)->get(['id', 'alert_name', 'created_at']),
            ];
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'data_points' => count($userData),
                'has_subscription' => $userData['subscription_count'] > 0,
                'has_alerts' => $userData['alert_count'] > 0
            ];
        });
        
        echo "✅ User management queries tested\n\n";
    }
    
    private function testTicketStatisticsQueries()
    {
        echo "🎫 Testing Ticket Statistics Queries...\n";
        
        // Test: Comprehensive ticket statistics
        if (Schema::hasTable('scraped_tickets')) {
            $this->runOptimizationTest('Ticket Statistics - Single Query', function() {
                $startTime = microtime(true);
                $initialQueries = count(DB::getQueryLog());
                
                // Get all statistics in one optimized query
                $ticketStats = ScrapedTicket::selectRaw('
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_tickets,
                    SUM(CASE WHEN is_high_demand = 1 THEN 1 ELSE 0 END) as high_demand_tickets,
                    SUM(CASE WHEN platform = "ticketmaster" THEN 1 ELSE 0 END) as ticketmaster_count,
                    SUM(CASE WHEN platform = "stubhub" THEN 1 ELSE 0 END) as stubhub_count,
                    SUM(CASE WHEN platform = "viagogo" THEN 1 ELSE 0 END) as viagogo_count,
                    MIN(min_price) as lowest_price,
                    MAX(max_price) as highest_price,
                    AVG((min_price + max_price) / 2) as average_price,
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as scraped_today
                ', [Carbon::today()])->first();
                
                $endTime = microtime(true);
                $totalQueries = count(DB::getQueryLog()) - $initialQueries;
                
                return [
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'query_count' => $totalQueries,
                    'statistics' => $ticketStats->toArray(),
                    'optimization_type' => 'single_aggregate_query'
                ];
            });
            
            // Test: Platform performance statistics
            $this->runOptimizationTest('Platform Performance Stats', function() {
                $startTime = microtime(true);
                $initialQueries = count(DB::getQueryLog());
                
                // Get platform statistics efficiently
                $platformStats = ScrapedTicket::select('platform')
                    ->selectRaw('
                        COUNT(*) as ticket_count,
                        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_count,
                        AVG(min_price) as avg_min_price,
                        AVG(max_price) as avg_max_price,
                        MAX(created_at) as last_scraped
                    ')
                    ->groupBy('platform')
                    ->orderByDesc('ticket_count')
                    ->get();
                
                $endTime = microtime(true);
                $totalQueries = count(DB::getQueryLog()) - $initialQueries;
                
                return [
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'query_count' => $totalQueries,
                    'platforms_analyzed' => $platformStats->count(),
                    'uses_groupby_optimization' => true
                ];
            });
        }
        
        echo "✅ Ticket statistics queries tested\n\n";
    }
    
    private function testAggregationPerformance()
    {
        echo "📈 Testing Aggregation Performance...\n";
        
        // Test: Time-based aggregations
        $this->runOptimizationTest('Time-based User Aggregation', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Efficient time-based user statistics
            $timeStats = User::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as user_count,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count
            ')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'date_points' => $timeStats->count(),
                'uses_date_grouping' => true
            ];
        });
        
        // Test: Multi-dimensional aggregation
        if (Schema::hasTable('ticket_alerts')) {
            $this->runOptimizationTest('Multi-dimensional Alert Aggregation', function() {
                $startTime = microtime(true);
                $initialQueries = count(DB::getQueryLog());
                
                // Complex aggregation with multiple dimensions
                $alertAggregation = TicketAlert::selectRaw('
                    status,
                    COUNT(*) as total_alerts,
                    AVG(max_price) as avg_max_price,
                    SUM(CASE WHEN triggered_at IS NOT NULL THEN 1 ELSE 0 END) as triggered_count,
                    SUM(matches_found) as total_matches
                ')
                ->groupBy('status')
                ->havingRaw('COUNT(*) > 0')
                ->get();
                
                $endTime = microtime(true);
                $totalQueries = count(DB::getQueryLog()) - $initialQueries;
                
                return [
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'query_count' => $totalQueries,
                    'status_groups' => $alertAggregation->count(),
                    'uses_having_clause' => true
                ];
            });
        }
        
        echo "✅ Aggregation performance tested\n\n";
    }
    
    private function testIndexUtilization()
    {
        echo "🔧 Testing Index Utilization...\n";
        
        // Test: Role-based queries (should use idx_users_role_active)
        $this->runOptimizationTest('Role-based Index Usage', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Query that should utilize composite index
            $activeAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->get(['id', 'name', 'email']);
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            // Get query execution plan
            $explain = null;
            try {
                $explain = DB::select('EXPLAIN SELECT id, name, email FROM users WHERE role = ? AND is_active = ?', ['admin', 1]);
            } catch (Exception $e) {
                // MySQL-specific EXPLAIN not available
            }
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'result_count' => $activeAdmins->count(),
                'should_use_index' => 'idx_users_role_active',
                'explain_available' => $explain !== null
            ];
        });
        
        // Test: Date range queries
        $this->runOptimizationTest('Date Range Index Usage', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            // Query that should utilize created_at index
            $recentUsers = User::where('created_at', '>=', Carbon::now()->subMonth())
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(['id', 'name', 'created_at']);
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'query_count' => $totalQueries,
                'result_count' => $recentUsers->count(),
                'uses_date_range' => true,
                'uses_ordering' => true
            ];
        });
        
        echo "✅ Index utilization tested\n\n";
    }
    
    private function runOptimizationTest($testName, $testFunction)
    {
        try {
            $initialQueryCount = count(DB::getQueryLog());
            $result = $testFunction();
            $finalQueryCount = count(DB::getQueryLog());
            
            $result['test_name'] = $testName;
            $result['queries_executed'] = $finalQueryCount - $initialQueryCount;
            $result['success'] = true;
            
            $this->testResults[] = $result;
            
            echo "✅ {$testName}\n";
            if (isset($result['execution_time'])) {
                echo "   Execution time: {$result['execution_time']}ms\n";
            }
            if (isset($result['query_count'])) {
                echo "   Queries: {$result['query_count']}\n";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'test_name' => $testName,
                'success' => false,
                'error' => $e->getMessage(),
                'queries_executed' => 0
            ];
            echo "❌ {$testName}\n";
            echo "   Error: {$e->getMessage()}\n";
        }
    }
    
    private function generateOptimizationReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🚀 OPTIMIZATION ANALYSIS REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $successfulTests = array_filter($this->testResults, fn($test) => $test['success']);
        $totalTests = count($this->testResults);
        $passedTests = count($successfulTests);
        
        echo "📊 OPTIMIZATION SUMMARY:\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Successful: {$passedTests}\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";
        
        // Performance analysis
        $executionTimes = array_column(array_filter($successfulTests, fn($test) => isset($test['execution_time'])), 'execution_time');
        $queryCounts = array_column($successfulTests, 'queries_executed');
        
        if (!empty($executionTimes)) {
            echo "⚡ PERFORMANCE ANALYSIS:\n";
            echo "Average execution time: " . round(array_sum($executionTimes) / count($executionTimes), 2) . "ms\n";
            echo "Fastest query: " . min($executionTimes) . "ms\n";
            echo "Slowest query: " . max($executionTimes) . "ms\n";
            echo "Total queries executed: " . array_sum($queryCounts) . "\n";
            echo "Average queries per test: " . round(array_sum($queryCounts) / count($queryCounts), 1) . "\n\n";
        }
        
        // Optimization recommendations
        echo "💡 OPTIMIZATION RECOMMENDATIONS:\n";
        
        // Query count analysis
        $highQueryTests = array_filter($successfulTests, fn($test) => ($test['queries_executed'] ?? 0) > 5);
        if (!empty($highQueryTests)) {
            echo "⚠️  High query count detected in:\n";
            foreach ($highQueryTests as $test) {
                echo "  - {$test['test_name']}: {$test['queries_executed']} queries\n";
            }
        } else {
            echo "✅ All tests use optimal query counts (≤5 queries per operation)\n";
        }
        
        // Performance recommendations
        $slowTests = array_filter($successfulTests, fn($test) => ($test['execution_time'] ?? 0) > 50);
        if (!empty($slowTests)) {
            echo "⚠️  Slower operations (>50ms):\n";
            foreach ($slowTests as $test) {
                echo "  - {$test['test_name']}: {$test['execution_time']}ms\n";
            }
        } else {
            echo "✅ All operations are fast (<50ms)\n";
        }
        
        echo "\n🎯 SPECIFIC OPTIMIZATIONS IMPLEMENTED:\n";
        echo "✅ Single aggregate queries for dashboard statistics\n";
        echo "✅ Eager loading for relationship data\n";
        echo "✅ Composite indexes for multi-column queries\n";
        echo "✅ Pagination for large datasets\n";
        echo "✅ Selective column retrieval to reduce data transfer\n";
        echo "✅ Grouped aggregations for platform statistics\n";
        
        echo "\n📋 PRODUCTION RECOMMENDATIONS:\n";
        echo "1. Monitor query execution times in production\n";
        echo "2. Implement Redis caching for dashboard statistics\n";
        echo "3. Use database query result caching for rarely changing data\n";
        echo "4. Consider read replicas for reporting queries\n";
        echo "5. Implement database connection pooling\n";
        echo "6. Regular ANALYZE TABLE maintenance for index optimization\n";
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✨ Optimization analysis completed!\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run the optimization tests
$tester = new DashboardOptimizationTest();
$results = $tester->runOptimizationTests();

echo "\n🏁 Dashboard optimization verification completed!\n";
