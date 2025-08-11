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

// Initialize Laravel application for testing
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== HD Tickets Database Verification Tests ===\n\n";

class DatabaseVerificationTest
{
    private $testResults = [];
    private $queryLog = [];
    
    public function runAllTests()
    {
        echo "Starting comprehensive database verification...\n\n";
        
        // Enable query logging
        DB::enableQueryLog();
        
        // 1. Test Eloquent Relationships
        $this->testEloquentRelationships();
        
        // 2. Test N+1 Query Prevention
        $this->testEagerLoading();
        
        // 3. Test Dashboard Aggregation Queries
        $this->testDashboardAggregations();
        
        // 4. Test Database Indexes Performance
        $this->testIndexPerformance();
        
        // 5. Test Complex Query Scenarios
        $this->testComplexQueries();
        
        // 6. Test Data Scenarios
        $this->testDataScenarios();
        
        // Generate comprehensive report
        $this->generateReport();
        
        return $this->testResults;
    }
    
    private function testEloquentRelationships()
    {
        echo "🔍 Testing Eloquent Relationships...\n";
        
        // Test User->tickets relationship
        $this->runTest('User->tickets relationship', function() {
            $user = User::first();
            if ($user) {
                $tickets = $user->tickets;
                return [
                    'success' => true,
                    'count' => $tickets->count(),
                    'relationship_exists' => true
                ];
            }
            return ['success' => false, 'error' => 'No users found'];
        });
        
        // Test User->assignedTickets relationship
        $this->runTest('User->assignedTickets relationship', function() {
            $user = User::where('role', 'agent')->first();
            if ($user) {
                $assignedTickets = $user->assignedTickets;
                return [
                    'success' => true,
                    'count' => $assignedTickets->count(),
                    'relationship_exists' => true
                ];
            }
            return ['success' => true, 'count' => 0, 'no_agents' => true];
        });
        
        // Test User->subscriptions relationship
        $this->runTest('User->subscriptions relationship', function() {
            $user = User::first();
            if ($user) {
                $subscriptions = $user->subscriptions;
                return [
                    'success' => true,
                    'count' => $subscriptions->count(),
                    'relationship_exists' => true
                ];
            }
            return ['success' => false, 'error' => 'No users found'];
        });
        
        // Test User->ticketAlerts relationship
        $this->runTest('User->ticketAlerts relationship', function() {
            $user = User::first();
            if ($user) {
                // Check if relationship method exists
                if (method_exists($user, 'ticketAlerts')) {
                    $alerts = $user->ticketAlerts;
                    return [
                        'success' => true,
                        'count' => $alerts->count(),
                        'relationship_exists' => true
                    ];
                }
                return ['success' => false, 'error' => 'ticketAlerts relationship not defined in User model'];
            }
            return ['success' => false, 'error' => 'No users found'];
        });
        
        // Test Ticket->category relationship
        $this->runTest('Ticket->category relationship', function() {
            $ticket = Ticket::first();
            if ($ticket) {
                $category = $ticket->category;
                return [
                    'success' => true,
                    'has_category' => $category !== null,
                    'category_name' => $category ? $category->name : null
                ];
            }
            return ['success' => true, 'no_tickets' => true];
        });
        
        // Test Category->tickets relationship
        $this->runTest('Category->tickets relationship', function() {
            $category = Category::first();
            if ($category) {
                // Check if relationship method exists
                if (method_exists($category, 'tickets')) {
                    $tickets = $category->tickets;
                    return [
                        'success' => true,
                        'count' => $tickets->count(),
                        'relationship_exists' => true
                    ];
                }
                return ['success' => false, 'error' => 'tickets relationship not defined in Category model'];
            }
            return ['success' => true, 'no_categories' => true];
        });
        
        // Test ScrapedTicket->category relationship
        $this->runTest('ScrapedTicket->category relationship', function() {
            $scrapedTicket = ScrapedTicket::first();
            if ($scrapedTicket) {
                $category = $scrapedTicket->category;
                return [
                    'success' => true,
                    'has_category' => $category !== null,
                    'category_name' => $category ? $category->name : null
                ];
            }
            return ['success' => true, 'no_scraped_tickets' => true];
        });
        
        echo "✅ Eloquent relationships tested\n\n";
    }
    
    private function testEagerLoading()
    {
        echo "🚀 Testing N+1 Query Prevention with Eager Loading...\n";
        
        // Test Users with tickets (without eager loading)
        $this->runTest('Users query WITHOUT eager loading', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            $users = User::limit(5)->get();
            foreach ($users as $user) {
                $ticketCount = $user->tickets->count(); // This will cause N+1 if not eager loaded
            }
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'success' => true,
                'query_count' => $totalQueries,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'users_tested' => $users->count()
            ];
        });
        
        // Test Users with tickets (with eager loading)
        $this->runTest('Users query WITH eager loading', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            $users = User::with('tickets')->limit(5)->get();
            foreach ($users as $user) {
                $ticketCount = $user->tickets->count(); // Should not cause additional queries
            }
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'success' => true,
                'query_count' => $totalQueries,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'users_tested' => $users->count()
            ];
        });
        
        // Test ScrapedTickets with category eager loading
        $this->runTest('ScrapedTickets WITH category eager loading', function() {
            $startTime = microtime(true);
            $initialQueries = count(DB::getQueryLog());
            
            $tickets = ScrapedTicket::with('category')->limit(10)->get();
            foreach ($tickets as $ticket) {
                $categoryName = $ticket->category ? $ticket->category->name : 'No Category';
            }
            
            $endTime = microtime(true);
            $totalQueries = count(DB::getQueryLog()) - $initialQueries;
            
            return [
                'success' => true,
                'query_count' => $totalQueries,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'tickets_tested' => $tickets->count()
            ];
        });
        
        echo "✅ N+1 query prevention tested\n\n";
    }
    
    private function testDashboardAggregations()
    {
        echo "📊 Testing Dashboard Data Aggregation Queries...\n";
        
        // Test user statistics aggregation
        $this->runTest('User statistics aggregation', function() {
            $startTime = microtime(true);
            
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'users_by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->pluck('count', 'role')
                    ->toArray(),
                'new_users_this_week' => User::where('created_at', '>=', Carbon::now()->subWeek())->count(),
                'users_with_subscriptions' => User::whereHas('subscriptions', function($query) {
                    $query->where('status', 'active');
                })->count()
            ];
            
            $endTime = microtime(true);
            
            return [
                'success' => true,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'stats' => $stats
            ];
        });
        
        // Test scraped tickets aggregation
        $this->runTest('ScrapedTickets aggregation', function() {
            if (!Schema::hasTable('scraped_tickets')) {
                return ['success' => false, 'error' => 'scraped_tickets table does not exist'];
            }
            
            $startTime = microtime(true);
            
            $stats = [
                'total_scraped' => ScrapedTicket::count(),
                'available_tickets' => ScrapedTicket::where('is_available', true)->count(),
                'high_demand_tickets' => ScrapedTicket::where('is_high_demand', true)->count(),
                'tickets_by_platform' => ScrapedTicket::select('platform', DB::raw('count(*) as count'))
                    ->groupBy('platform')
                    ->pluck('count', 'platform')
                    ->toArray(),
                'price_statistics' => ScrapedTicket::selectRaw('
                    AVG(min_price) as avg_min_price,
                    AVG(max_price) as avg_max_price,
                    MIN(min_price) as lowest_price,
                    MAX(max_price) as highest_price
                ')->first()->toArray()
            ];
            
            $endTime = microtime(true);
            
            return [
                'success' => true,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'stats' => $stats
            ];
        });
        
        // Test ticket alerts aggregation
        $this->runTest('TicketAlerts aggregation', function() {
            if (!Schema::hasTable('ticket_alerts')) {
                return ['success' => false, 'error' => 'ticket_alerts table does not exist'];
            }
            
            $startTime = microtime(true);
            
            $stats = [
                'total_alerts' => TicketAlert::count(),
                'active_alerts' => TicketAlert::where('status', 'active')->count(),
                'alerts_by_user' => TicketAlert::select('user_id', DB::raw('count(*) as alert_count'))
                    ->groupBy('user_id')
                    ->orderByDesc('alert_count')
                    ->limit(5)
                    ->get()
                    ->toArray(),
                'alerts_triggered_today' => TicketAlert::whereDate('triggered_at', Carbon::today())->count(),
                'avg_max_price' => TicketAlert::avg('max_price')
            ];
            
            $endTime = microtime(true);
            
            return [
                'success' => true,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'stats' => $stats
            ];
        });
        
        echo "✅ Dashboard aggregation queries tested\n\n";
    }
    
    private function testIndexPerformance()
    {
        echo "🔧 Testing Database Index Performance...\n";
        
        // Test indexed vs non-indexed queries
        $this->runTest('Indexed query performance (users by role)', function() {
            $startTime = microtime(true);
            
            // This should use index: idx_users_role_active
            $activeAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->count();
            
            $endTime = microtime(true);
            
            return [
                'success' => true,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'result_count' => $activeAdmins,
                'likely_uses_index' => true
            ];
        });
        
        // Test ScrapedTickets indexed queries
        if (Schema::hasTable('scraped_tickets')) {
            $this->runTest('ScrapedTickets indexed query (platform + available)', function() {
                $startTime = microtime(true);
                
                $availableTicketmaster = ScrapedTicket::where('platform', 'ticketmaster')
                    ->where('is_available', true)
                    ->count();
                
                $endTime = microtime(true);
                
                return [
                    'success' => true,
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'result_count' => $availableTicketmaster,
                    'likely_uses_index' => true
                ];
            });
        }
        
        // Test explain plans for complex queries
        $this->runTest('Query execution plan analysis', function() {
            $plans = [];
            
            try {
                // Analyze user query plan
                $userPlan = DB::select("EXPLAIN SELECT * FROM users WHERE role = ? AND is_active = ?", ['admin', 1]);
                $plans['user_role_active'] = $userPlan;
                
                if (Schema::hasTable('scraped_tickets')) {
                    $ticketPlan = DB::select("EXPLAIN SELECT * FROM scraped_tickets WHERE platform = ? AND is_available = ?", ['ticketmaster', 1]);
                    $plans['scraped_tickets_platform'] = $ticketPlan;
                }
                
                return [
                    'success' => true,
                    'plans_analyzed' => count($plans),
                    'plans' => $plans
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Could not analyze execution plans: ' . $e->getMessage()
                ];
            }
        });
        
        echo "✅ Index performance tested\n\n";
    }
    
    private function testComplexQueries()
    {
        echo "🎯 Testing Complex Query Scenarios...\n";
        
        // Test complex dashboard query with joins
        $this->runTest('Complex dashboard query with joins', function() {
            $startTime = microtime(true);
            
            try {
                $dashboardData = User::select([
                        'users.id',
                        'users.name',
                        'users.role',
                        DB::raw('COUNT(DISTINCT subscriptions.id) as subscription_count'),
                        DB::raw('MAX(subscriptions.created_at) as latest_subscription')
                    ])
                    ->leftJoin('user_subscriptions as subscriptions', 'users.id', '=', 'subscriptions.user_id')
                    ->where('users.is_active', true)
                    ->groupBy('users.id', 'users.name', 'users.role')
                    ->orderBy('subscription_count', 'desc')
                    ->limit(10)
                    ->get();
                
                $endTime = microtime(true);
                
                return [
                    'success' => true,
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'result_count' => $dashboardData->count(),
                    'query_complexity' => 'high'
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Complex query failed: ' . $e->getMessage()
                ];
            }
        });
        
        // Test subquery performance
        $this->runTest('Subquery performance test', function() {
            $startTime = microtime(true);
            
            try {
                $usersWithRecentActivity = User::whereExists(function ($query) {
                    $query->selectRaw(1)
                        ->from('user_subscriptions')
                        ->whereColumn('user_subscriptions.user_id', 'users.id')
                        ->where('user_subscriptions.created_at', '>=', Carbon::now()->subMonth());
                })
                ->count();
                
                $endTime = microtime(true);
                
                return [
                    'success' => true,
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'result_count' => $usersWithRecentActivity
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Subquery failed: ' . $e->getMessage()
                ];
            }
        });
        
        echo "✅ Complex queries tested\n\n";
    }
    
    private function testDataScenarios()
    {
        echo "🌟 Testing Different Data Scenarios...\n";
        
        // Test empty data scenario
        $this->runTest('Empty data scenario handling', function() {
            try {
                // Create queries that might return empty results
                $nonExistentUser = User::where('email', 'definitely-does-not-exist@example.com')->first();
                $futureTickets = ScrapedTicket::where('event_date', '>', Carbon::now()->addYears(10))->count();
                
                return [
                    'success' => true,
                    'handles_null' => $nonExistentUser === null,
                    'handles_empty_count' => $futureTickets >= 0
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Empty data scenario failed: ' . $e->getMessage()
                ];
            }
        });
        
        // Test large dataset performance
        $this->runTest('Large dataset handling', function() {
            $startTime = microtime(true);
            
            try {
                // Test pagination performance
                $largeDataset = User::paginate(50);
                
                $endTime = microtime(true);
                
                return [
                    'success' => true,
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'pagination_works' => $largeDataset->hasPages(),
                    'per_page' => $largeDataset->perPage(),
                    'total' => $largeDataset->total()
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Large dataset test failed: ' . $e->getMessage()
                ];
            }
        });
        
        // Test date range queries
        $this->runTest('Date range query performance', function() {
            $startTime = microtime(true);
            
            try {
                $lastMonth = Carbon::now()->subMonth();
                $now = Carbon::now();
                
                $recentUsers = User::whereBetween('created_at', [$lastMonth, $now])->count();
                $recentTickets = 0;
                
                if (Schema::hasTable('scraped_tickets')) {
                    $recentTickets = ScrapedTicket::whereBetween('created_at', [$lastMonth, $now])->count();
                }
                
                $endTime = microtime(true);
                
                return [
                    'success' => true,
                    'execution_time' => round(($endTime - $startTime) * 1000, 2),
                    'recent_users' => $recentUsers,
                    'recent_tickets' => $recentTickets
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => 'Date range query failed: ' . $e->getMessage()
                ];
            }
        });
        
        echo "✅ Data scenarios tested\n\n";
    }
    
    private function runTest($testName, $testFunction)
    {
        try {
            $initialQueryCount = count(DB::getQueryLog());
            $result = $testFunction();
            $finalQueryCount = count(DB::getQueryLog());
            
            $result['queries_executed'] = $finalQueryCount - $initialQueryCount;
            $result['test_name'] = $testName;
            
            $this->testResults[] = $result;
            
            $status = $result['success'] ? '✅' : '❌';
            echo "{$status} {$testName}\n";
            
            if (!$result['success'] && isset($result['error'])) {
                echo "   Error: {$result['error']}\n";
            } elseif ($result['success'] && isset($result['execution_time'])) {
                echo "   Execution time: {$result['execution_time']}ms\n";
            }
            
            if (isset($result['queries_executed'])) {
                echo "   Queries executed: {$result['queries_executed']}\n";
            }
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'test_name' => $testName,
                'success' => false,
                'error' => $e->getMessage(),
                'queries_executed' => 0
            ];
            echo "❌ {$testName}\n";
            echo "   Exception: {$e->getMessage()}\n";
        }
    }
    
    private function generateReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📋 COMPREHENSIVE TEST REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, fn($test) => $test['success']));
        $failedTests = $totalTests - $passedTests;
        
        echo "📊 TEST SUMMARY:\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Passed: {$passedTests}\n";
        echo "Failed: {$failedTests}\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";
        
        // Performance Analysis
        $executionTimes = array_filter(
            array_map(fn($test) => $test['execution_time'] ?? null, $this->testResults)
        );
        
        if (!empty($executionTimes)) {
            echo "⚡ PERFORMANCE METRICS:\n";
            echo "Average execution time: " . round(array_sum($executionTimes) / count($executionTimes), 2) . "ms\n";
            echo "Fastest query: " . min($executionTimes) . "ms\n";
            echo "Slowest query: " . max($executionTimes) . "ms\n\n";
        }
        
        // Query Analysis
        $totalQueries = array_sum(array_map(fn($test) => $test['queries_executed'] ?? 0, $this->testResults));
        echo "🗄️ QUERY ANALYSIS:\n";
        echo "Total queries executed: {$totalQueries}\n";
        echo "Average queries per test: " . round($totalQueries / $totalTests, 1) . "\n\n";
        
        // Failed Tests Details
        if ($failedTests > 0) {
            echo "❌ FAILED TESTS DETAILS:\n";
            foreach ($this->testResults as $test) {
                if (!$test['success']) {
                    echo "- {$test['test_name']}: {$test['error']}\n";
                }
            }
            echo "\n";
        }
        
        // Recommendations
        echo "💡 RECOMMENDATIONS:\n";
        
        // Check for N+1 queries
        $eagerLoadingTests = array_filter($this->testResults, function($test) {
            return strpos($test['test_name'], 'eager loading') !== false;
        });
        
        if (!empty($eagerLoadingTests)) {
            $withoutEager = null;
            $withEager = null;
            
            foreach ($eagerLoadingTests as $test) {
                if (strpos($test['test_name'], 'WITHOUT') !== false) {
                    $withoutEager = $test;
                } elseif (strpos($test['test_name'], 'WITH') !== false) {
                    $withEager = $test;
                }
            }
            
            if ($withoutEager && $withEager && isset($withoutEager['query_count']) && isset($withEager['query_count'])) {
                if ($withoutEager['query_count'] > $withEager['query_count'] + 1) {
                    echo "- ✅ Eager loading is properly implemented (prevented N+1 queries)\n";
                } else {
                    echo "- ⚠️ Consider implementing eager loading to prevent N+1 queries\n";
                }
            }
        }
        
        // Check for slow queries
        $slowQueries = array_filter($this->testResults, function($test) {
            return isset($test['execution_time']) && $test['execution_time'] > 100; // >100ms
        });
        
        if (!empty($slowQueries)) {
            echo "- ⚠️ Some queries are slow (>100ms). Consider optimizing:\n";
            foreach ($slowQueries as $test) {
                echo "  * {$test['test_name']}: {$test['execution_time']}ms\n";
            }
        } else {
            echo "- ✅ All queries are performing well (<100ms)\n";
        }
        
        // Check for index usage
        echo "- 💡 Ensure indexes are properly utilized for frequently queried columns\n";
        echo "- 💡 Consider implementing database query caching for dashboard metrics\n";
        echo "- 💡 Monitor query performance in production environment\n";
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✨ Database verification completed!\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run the tests
$tester = new DatabaseVerificationTest();
$results = $tester->runAllTests();

// Additional specific checks for HD Tickets application
echo "\n🎫 HD TICKETS SPECIFIC CHECKS:\n";
echo str_repeat("-", 40) . "\n";

// Check critical tables exist
$criticalTables = ['users', 'scraped_tickets', 'categories', 'user_subscriptions'];
foreach ($criticalTables as $table) {
    $exists = Schema::hasTable($table);
    $status = $exists ? '✅' : '❌';
    echo "{$status} Table '{$table}' " . ($exists ? 'exists' : 'missing') . "\n";
}

// Check for essential indexes
echo "\nIndex checks:\n";
try {
    $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name LIKE 'idx_%'");
    echo "✅ Users table has " . count($indexes) . " custom indexes\n";
} catch (Exception $e) {
    echo "⚠️ Could not check indexes: " . $e->getMessage() . "\n";
}

// Check role distribution
$roleStats = User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get();
echo "\nUser role distribution:\n";
foreach ($roleStats as $role) {
    echo "- {$role->role}: {$role->count} users\n";
}

echo "\n🏁 All verifications completed!\n";
