<?php

namespace Tests\LoadTesting;

use Tests\TestCase;
use App\Models\User;
use App\Models\ScrapedTicket;
use App\Services\TicketScrapingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConcurrentUserLoadTest extends TestCase
{
    protected bool $seed = false; // Disable seeding for load tests

    public function test_can_handle_1000_concurrent_dashboard_requests()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        // Generate test data
        $this->generateTestTickets(1000);
        
        // Create 1000 users
        $users = User::factory()->count(1000)->create();
        
        $startTime = microtime(true);
        $results = [];
        $errors = 0;
        
        foreach ($users as $index => $user) {
            try {
                $requestStart = microtime(true);
                
                $response = $this->actingAs($user)->get('/dashboard');
                
                $requestTime = (microtime(true) - $requestStart) * 1000;
                
                $results[] = [
                    'user_id' => $user->id,
                    'response_time' => $requestTime,
                    'status_code' => $response->status(),
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ];
                
                if ($response->status() !== 200) {
                    $errors++;
                }
                
                // Progress indicator
                if (($index + 1) % 100 === 0) {
                    echo "\nProcessed " . ($index + 1) . " requests...";
                }
                
            } catch (\Exception $e) {
                $errors++;
                Log::error("Load test error for user {$user->id}: " . $e->getMessage());
            }
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        
        // Calculate statistics
        $responseTimes = array_column($results, 'response_time');
        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $maxResponseTime = max($responseTimes);
        $minResponseTime = min($responseTimes);
        
        // Sort for percentiles
        sort($responseTimes);
        $p95Index = (int) (0.95 * count($responseTimes));
        $p99Index = (int) (0.99 * count($responseTimes));
        
        $p95ResponseTime = $responseTimes[$p95Index] ?? 0;
        $p99ResponseTime = $responseTimes[$p99Index] ?? 0;
        
        $errorRate = ($errors / 1000) * 100;
        $throughput = 1000 / ($totalTime / 1000); // Requests per second
        
        // Log detailed results
        Log::info('Load Test Results', [
            'total_requests' => 1000,
            'total_time_ms' => $totalTime,
            'avg_response_time_ms' => $avgResponseTime,
            'min_response_time_ms' => $minResponseTime,
            'max_response_time_ms' => $maxResponseTime,
            'p95_response_time_ms' => $p95ResponseTime,
            'p99_response_time_ms' => $p99ResponseTime,
            'error_count' => $errors,
            'error_rate_percent' => $errorRate,
            'throughput_rps' => $throughput,
            'peak_memory_mb' => memory_get_peak_usage(true) / 1024 / 1024
        ]);
        
        // Assertions for performance requirements
        $this->assertLessThan(10.0, $errorRate, 'Error rate should be less than 10%');
        $this->assertLessThan(2000, $avgResponseTime, 'Average response time should be less than 2 seconds');
        $this->assertLessThan(5000, $p95ResponseTime, 'P95 response time should be less than 5 seconds');
        $this->assertGreaterThan(10, $throughput, 'Throughput should be at least 10 requests per second');
    }

    public function test_concurrent_ticket_search_performance()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        $this->mockTicketPlatformResponses();
        
        // Create scraper users
        User::factory()->count(10)->create([
            'role' => 'scraper',
            'is_scraper_account' => true
        ]);
        
        $service = new TicketScrapingService();
        $startTime = microtime(true);
        $results = [];
        
        // Simulate 100 concurrent search requests
        for ($i = 0; $i < 100; $i++) {
            $requestStart = microtime(true);
            
            try {
                $searchResults = $service->searchTickets("Event {$i}", [
                    'platforms' => ['stubhub'],
                    'max_price' => 300
                ]);
                
                $requestTime = (microtime(true) - $requestStart) * 1000;
                
                $results[] = [
                    'request_id' => $i,
                    'response_time' => $requestTime,
                    'success' => !empty($searchResults['stubhub']),
                    'ticket_count' => count($searchResults['stubhub'] ?? [])
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'request_id' => $i,
                    'response_time' => 0,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            if (($i + 1) % 10 === 0) {
                echo "\nCompleted " . ($i + 1) . " search requests...";
            }
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        
        // Analyze results
        $successfulRequests = array_filter($results, fn($r) => $r['success']);
        $successRate = (count($successfulRequests) / 100) * 100;
        
        $responseTimes = array_column($successfulRequests, 'response_time');
        $avgResponseTime = !empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
        
        Log::info('Concurrent Search Load Test Results', [
            'total_requests' => 100,
            'successful_requests' => count($successfulRequests),
            'success_rate_percent' => $successRate,
            'avg_response_time_ms' => $avgResponseTime,
            'total_time_ms' => $totalTime
        ]);
        
        $this->assertGreaterThan(80, $successRate, 'Success rate should be above 80%');
        $this->assertLessThan(3000, $avgResponseTime, 'Average search time should be under 3 seconds');
    }

    public function test_database_performance_under_load()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        // Generate large dataset
        $this->generateTestTickets(5000);
        
        $startTime = microtime(true);
        $queryTimes = [];
        
        // Test various database operations under load
        for ($i = 0; $i < 100; $i++) {
            // Test 1: Search queries
            $queryStart = microtime(true);
            $tickets = ScrapedTicket::where('is_available', true)
                ->where('min_price', '>', 50)
                ->orderBy('scraped_at', 'desc')
                ->limit(50)
                ->get();
            $queryTimes['search'][] = (microtime(true) - $queryStart) * 1000;
            
            // Test 2: Aggregation queries
            $queryStart = microtime(true);
            $stats = ScrapedTicket::selectRaw('
                platform,
                COUNT(*) as total,
                AVG(min_price) as avg_price,
                MIN(min_price) as min_price,
                MAX(max_price) as max_price
            ')
            ->groupBy('platform')
            ->get();
            $queryTimes['aggregation'][] = (microtime(true) - $queryStart) * 1000;
            
            // Test 3: Join queries
            $queryStart = microtime(true);
            $ticketsWithCategories = ScrapedTicket::with('category')
                ->where('is_high_demand', true)
                ->limit(20)
                ->get();
            $queryTimes['joins'][] = (microtime(true) - $queryStart) * 1000;
            
            if (($i + 1) % 10 === 0) {
                echo "\nCompleted " . ($i + 1) . " database test cycles...";
            }
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        
        // Calculate average query times
        foreach ($queryTimes as $queryType => $times) {
            $avgTime = array_sum($times) / count($times);
            $maxTime = max($times);
            
            Log::info("Database Performance - {$queryType}", [
                'avg_time_ms' => $avgTime,
                'max_time_ms' => $maxTime,
                'total_queries' => count($times)
            ]);
            
            // Performance assertions
            $this->assertLessThan(500, $avgTime, "{$queryType} queries should average under 500ms");
            $this->assertLessThan(2000, $maxTime, "{$queryType} queries should not exceed 2 seconds");
        }
        
        $this->assertLessThan(60000, $totalTime, 'Total database test should complete within 1 minute');
    }

    public function test_memory_usage_under_load()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        $initialMemory = memory_get_usage(true);
        $memorySnapshots = [];
        
        // Generate increasing amounts of data and monitor memory
        for ($batchSize = 100; $batchSize <= 1000; $batchSize += 100) {
            $beforeBatch = memory_get_usage(true);
            
            // Create tickets in batches
            ScrapedTicket::factory()->count($batchSize)->create();
            
            // Perform operations that could cause memory leaks
            $tickets = ScrapedTicket::all();
            $processedTickets = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'formatted_price' => $ticket->formatted_price,
                    'platform_display_name' => $ticket->platform_display_name
                ];
            });
            
            $afterBatch = memory_get_usage(true);
            $memoryIncrease = $afterBatch - $beforeBatch;
            
            $memorySnapshots[] = [
                'batch_size' => $batchSize,
                'total_tickets' => ScrapedTicket::count(),
                'memory_before_mb' => $beforeBatch / 1024 / 1024,
                'memory_after_mb' => $afterBatch / 1024 / 1024,
                'memory_increase_mb' => $memoryIncrease / 1024 / 1024,
                'peak_memory_mb' => memory_get_peak_usage(true) / 1024 / 1024
            ];
            
            // Clear collections to prevent accumulation
            unset($tickets, $processedTickets);
            gc_collect_cycles();
            
            echo "\nBatch {$batchSize}: Memory usage " . round($afterBatch / 1024 / 1024, 2) . " MB";
        }
        
        $finalMemory = memory_get_usage(true);
        $totalMemoryIncrease = ($finalMemory - $initialMemory) / 1024 / 1024;
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        Log::info('Memory Usage Load Test Results', [
            'initial_memory_mb' => $initialMemory / 1024 / 1024,
            'final_memory_mb' => $finalMemory / 1024 / 1024,
            'total_increase_mb' => $totalMemoryIncrease,
            'peak_memory_mb' => $peakMemory,
            'snapshots' => $memorySnapshots
        ]);
        
        // Memory usage assertions
        $this->assertLessThan(500, $peakMemory, 'Peak memory usage should be under 500MB');
        $this->assertLessThan(200, $totalMemoryIncrease, 'Total memory increase should be under 200MB');
    }

    public function test_cache_performance_under_load()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        $cacheKeys = [];
        $cacheTimes = [];
        
        // Test cache write performance
        $writeStart = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $key = "load_test_key_{$i}";
            $data = [
                'id' => $i,
                'data' => str_repeat('test_data_', 100), // ~1KB of data
                'timestamp' => now()->toDateTimeString()
            ];
            
            $operationStart = microtime(true);
            Cache::put($key, $data, 3600);
            $cacheTimes['write'][] = (microtime(true) - $operationStart) * 1000;
            
            $cacheKeys[] = $key;
        }
        $totalWriteTime = (microtime(true) - $writeStart) * 1000;
        
        // Test cache read performance
        $readStart = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $key = $cacheKeys[$i];
            
            $operationStart = microtime(true);
            $data = Cache::get($key);
            $cacheTimes['read'][] = (microtime(true) - $operationStart) * 1000;
            
            $this->assertNotNull($data, "Cache key {$key} should exist");
        }
        $totalReadTime = (microtime(true) - $readStart) * 1000;
        
        // Calculate statistics
        $avgWriteTime = array_sum($cacheTimes['write']) / count($cacheTimes['write']);
        $avgReadTime = array_sum($cacheTimes['read']) / count($cacheTimes['read']);
        $maxWriteTime = max($cacheTimes['write']);
        $maxReadTime = max($cacheTimes['read']);
        
        Log::info('Cache Performance Load Test Results', [
            'total_write_time_ms' => $totalWriteTime,
            'total_read_time_ms' => $totalReadTime,
            'avg_write_time_ms' => $avgWriteTime,
            'avg_read_time_ms' => $avgReadTime,
            'max_write_time_ms' => $maxWriteTime,
            'max_read_time_ms' => $maxReadTime,
            'write_throughput_ops_per_sec' => 1000 / ($totalWriteTime / 1000),
            'read_throughput_ops_per_sec' => 1000 / ($totalReadTime / 1000)
        ]);
        
        // Performance assertions
        $this->assertLessThan(10, $avgWriteTime, 'Average cache write should be under 10ms');
        $this->assertLessThan(5, $avgReadTime, 'Average cache read should be under 5ms');
        $this->assertLessThan(100, $maxWriteTime, 'Max cache write should be under 100ms');
        $this->assertLessThan(50, $maxReadTime, 'Max cache read should be under 50ms');
        
        // Clean up
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    public function test_concurrent_api_scraping_load()
    {
        $this->markTestSkipped('Load test - run manually with --group=load');
        
        // Mock API responses with realistic delays
        Http::fake([
            'stubhub.com/*' => Http::response($this->getStubHubMockResponse(), 200)
                ->delay(rand(100, 500)), // 100-500ms delay
            'ticketmaster.com/*' => Http::response($this->getTicketmasterMockResponse(), 200)
                ->delay(rand(200, 800)), // 200-800ms delay
            'viagogo.com/*' => Http::response($this->getViagogoMockResponse(), 200)
                ->delay(rand(150, 600))  // 150-600ms delay
        ]);
        
        $service = new TicketScrapingService();
        $startTime = microtime(true);
        $results = [];
        
        // Simulate 50 concurrent scraping operations
        for ($i = 0; $i < 50; $i++) {
            $requestStart = microtime(true);
            
            try {
                $searchResults = $service->searchTickets("Load Test Event {$i}", [
                    'platforms' => ['stubhub', 'ticketmaster', 'viagogo'],
                    'max_price' => rand(100, 500)
                ]);
                
                $requestTime = (microtime(true) - $requestStart) * 1000;
                
                $totalTickets = 0;
                foreach ($searchResults as $platform => $tickets) {
                    $totalTickets += count($tickets);
                }
                
                $results[] = [
                    'request_id' => $i,
                    'response_time' => $requestTime,
                    'success' => true,
                    'total_tickets_found' => $totalTickets,
                    'platforms_searched' => count($searchResults)
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'request_id' => $i,
                    'response_time' => (microtime(true) - $requestStart) * 1000,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            if (($i + 1) % 10 === 0) {
                echo "\nCompleted " . ($i + 1) . " scraping operations...";
            }
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        
        // Analyze results
        $successfulRequests = array_filter($results, fn($r) => $r['success']);
        $failedRequests = array_filter($results, fn($r) => !$r['success']);
        
        $successRate = (count($successfulRequests) / 50) * 100;
        $avgResponseTime = array_sum(array_column($successfulRequests, 'response_time')) / count($successfulRequests);
        $totalTicketsFound = array_sum(array_column($successfulRequests, 'total_tickets_found'));
        
        Log::info('API Scraping Load Test Results', [
            'total_requests' => 50,
            'successful_requests' => count($successfulRequests),
            'failed_requests' => count($failedRequests),
            'success_rate_percent' => $successRate,
            'avg_response_time_ms' => $avgResponseTime,
            'total_time_ms' => $totalTime,
            'total_tickets_found' => $totalTicketsFound,
            'errors' => array_column($failedRequests, 'error')
        ]);
        
        // Performance assertions
        $this->assertGreaterThan(90, $successRate, 'API scraping success rate should be above 90%');
        $this->assertLessThan(10000, $avgResponseTime, 'Average API response time should be under 10 seconds');
        $this->assertGreaterThan(0, $totalTicketsFound, 'Should find some tickets');
    }

    protected function generateTestTickets(int $count = 100): void
    {
        // Clear existing tickets to ensure clean test
        ScrapedTicket::truncate();
        
        echo "\nGenerating {$count} test tickets...";
        
        $batchSize = 100;
        for ($i = 0; $i < $count; $i += $batchSize) {
            $remainingCount = min($batchSize, $count - $i);
            ScrapedTicket::factory()->count($remainingCount)->create();
            
            if (($i + $remainingCount) % 500 === 0) {
                echo "\nGenerated " . ($i + $remainingCount) . " tickets...";
            }
        }
        
        echo "\nGenerated {$count} test tickets successfully.";
    }
}
