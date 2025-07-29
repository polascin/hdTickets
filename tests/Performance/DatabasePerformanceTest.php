<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\ScrapedTicket;
use App\Models\User;
use App\Models\Category;
use App\Models\TicketAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder;

class DatabasePerformanceTest extends TestCase
{
    protected bool $seed = false;

    public function test_scraped_tickets_index_performance()
    {
        // Generate large dataset
        $this->generateTestData();
        
        // Test various query patterns that should use indexes
        $queries = [
            // Platform index
            function() {
                return ScrapedTicket::where('platform', 'stubhub')->count();
            },
            
            // Availability index
            function() {
                return ScrapedTicket::where('is_available', true)->count();
            },
            
            // High demand index
            function() {
                return ScrapedTicket::where('is_high_demand', true)->count();
            },
            
            // Date range index
            function() {
                return ScrapedTicket::where('event_date', '>', now())
                    ->where('event_date', '<', now()->addDays(30))
                    ->count();
            },
            
            // Price range index
            function() {
                return ScrapedTicket::where('min_price', '>=', 50)
                    ->where('max_price', '<=', 300)
                    ->count();
            },
            
            // Composite index (platform + availability)
            function() {
                return ScrapedTicket::where('platform', 'ticketmaster')
                    ->where('is_available', true)
                    ->count();
            },
            
            // Text search (should use full-text index if available)
            function() {
                return ScrapedTicket::where('title', 'LIKE', '%Manchester%')->count();
            }
        ];
        
        $queryTimes = [];
        
        foreach ($queries as $index => $query) {
            // Clear query cache
            DB::flushQueryLog();
            
            $startTime = microtime(true);
            $result = $query();
            $endTime = microtime(true);
            
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $queryTimes["query_{$index}"] = $executionTime;
            
            // Query should complete quickly (under 100ms for indexed queries)
            $this->assertLessThan(100, $executionTime, "Query {$index} took too long: {$executionTime}ms");
            $this->assertGreaterThanOrEqual(0, $result, "Query {$index} should return valid result");
        }
        
        echo "\nDatabase Index Performance Results:\n";
        foreach ($queryTimes as $queryName => $time) {
            echo "  {$queryName}: " . round($time, 2) . "ms\n";
        }
    }

    public function test_complex_aggregation_performance()
    {
        $this->generateTestData();
        
        $startTime = microtime(true);
        
        // Complex aggregation query
        $stats = ScrapedTicket::selectRaw('
            platform,
            COUNT(*) as total_tickets,
            COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_tickets,
            COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_tickets,
            AVG(min_price) as avg_min_price,
            AVG(max_price) as avg_max_price,
            MIN(min_price) as lowest_price,
            MAX(max_price) as highest_price,
            COUNT(DISTINCT venue) as unique_venues
        ')
        ->where('event_date', '>', now())
        ->groupBy('platform')
        ->having('total_tickets', '>', 10)
        ->orderBy('total_tickets', 'desc')
        ->get();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(500, $executionTime, 'Complex aggregation should complete within 500ms');
        $this->assertGreaterThan(0, $stats->count(), 'Aggregation should return results');
        
        // Verify result structure
        $firstStat = $stats->first();
        $this->assertNotNull($firstStat->platform);
        $this->assertGreaterThan(0, $firstStat->total_tickets);
        $this->assertNotNull($firstStat->avg_min_price);
        
        echo "\nComplex Aggregation Performance: " . round($executionTime, 2) . "ms\n";
    }

    public function test_join_query_performance()
    {
        $this->generateTestData();
        
        $startTime = microtime(true);
        
        // Join with categories and users (through alerts)
        $results = ScrapedTicket::select([
                'scraped_tickets.id',
                'scraped_tickets.title',
                'scraped_tickets.platform',
                'scraped_tickets.min_price',
                'categories.name as category_name'
            ])
            ->join('categories', 'scraped_tickets.category_id', '=', 'categories.id')
            ->where('scraped_tickets.is_available', true)
            ->where('scraped_tickets.event_date', '>', now())
            ->orderBy('scraped_tickets.scraped_at', 'desc')
            ->limit(100)
            ->get();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(200, $executionTime, 'Join query should complete within 200ms');
        $this->assertGreaterThan(0, $results->count(), 'Join query should return results');
        
        // Verify join worked correctly
        $firstResult = $results->first();
        $this->assertNotNull($firstResult->category_name);
        
        echo "\nJoin Query Performance: " . round($executionTime, 2) . "ms\n";
    }

    public function test_pagination_performance()
    {
        $this->generateTestData();
        
        // Test pagination at different offsets
        $offsets = [0, 100, 500, 1000, 2000];
        $pageSize = 50;
        
        foreach ($offsets as $offset) {
            $startTime = microtime(true);
            
            $results = ScrapedTicket::where('is_available', true)
                ->orderBy('scraped_at', 'desc')
                ->offset($offset)
                ->limit($pageSize)
                ->get();
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            // Even deep pagination should be reasonably fast
            $this->assertLessThan(300, $executionTime, "Pagination at offset {$offset} took too long: {$executionTime}ms");
            $this->assertCount(min($pageSize, 50), $results, "Should return correct number of results");
            
            echo "  Offset {$offset}: " . round($executionTime, 2) . "ms\n";
        }
    }

    public function test_search_query_performance()
    {
        $this->generateTestData();
        
        $searchTerms = [
            'Manchester',
            'United',
            'Football',
            'Old Trafford',
            'Premier League'
        ];
        
        foreach ($searchTerms as $term) {
            $startTime = microtime(true);
            
            // Test LIKE search performance
            $results = ScrapedTicket::where(function($query) use ($term) {
                $query->where('title', 'LIKE', "%{$term}%")
                      ->orWhere('venue', 'LIKE', "%{$term}%")
                      ->orWhere('location', 'LIKE', "%{$term}%");
            })
            ->where('is_available', true)
            ->orderBy('scraped_at', 'desc')
            ->limit(50)
            ->get();
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            // Search queries should complete within reasonable time
            $this->assertLessThan(400, $executionTime, "Search for '{$term}' took too long: {$executionTime}ms");
            
            echo "  Search '{$term}': " . round($executionTime, 2) . "ms (" . $results->count() . " results)\n";
        }
    }

    public function test_bulk_insert_performance()
    {
        $batchSizes = [100, 500, 1000];
        
        foreach ($batchSizes as $batchSize) {
            // Generate test data
            $ticketData = ScrapedTicket::factory()->count($batchSize)->make()->map(function($ticket) {
                return $ticket->toArray();
            })->toArray();
            
            $startTime = microtime(true);
            
            // Bulk insert
            ScrapedTicket::insert($ticketData);
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            // Bulk operations should be efficient
            $insertRate = $batchSize / ($executionTime / 1000); // Records per second
            
            $this->assertGreaterThan(100, $insertRate, "Bulk insert rate too slow for batch size {$batchSize}");
            
            echo "  Batch size {$batchSize}: " . round($executionTime, 2) . "ms (" . round($insertRate, 0) . " records/sec)\n";
            
            // Clean up
            ScrapedTicket::whereIn('id', ScrapedTicket::latest('id')->limit($batchSize)->pluck('id'))->delete();
        }
    }

    public function test_update_performance()
    {
        $this->generateTestData();
        
        $startTime = microtime(true);
        
        // Bulk update operation
        $affected = ScrapedTicket::where('platform', 'stubhub')
            ->where('scraped_at', '<', now()->subHours(1))
            ->update([
                'is_available' => false,
                'updated_at' => now()
            ]);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(500, $executionTime, 'Bulk update should complete within 500ms');
        $this->assertGreaterThan(0, $affected, 'Update should affect some records');
        
        echo "\nBulk Update Performance: " . round($executionTime, 2) . "ms ({$affected} records updated)\n";
    }

    public function test_delete_performance()
    {
        $this->generateTestData();
        
        $startTime = microtime(true);
        
        // Delete old records
        $deleted = ScrapedTicket::where('scraped_at', '<', now()->subDays(30))
            ->delete();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(300, $executionTime, 'Bulk delete should complete within 300ms');
        
        echo "\nBulk Delete Performance: " . round($executionTime, 2) . "ms ({$deleted} records deleted)\n";
    }

    public function test_subquery_performance()
    {
        $this->generateTestData();
        
        $startTime = microtime(true);
        
        // Complex subquery: Find tickets for events that have high demand tickets
        $results = ScrapedTicket::whereIn('title', function($query) {
            $query->select('title')
                  ->from('scraped_tickets')
                  ->where('is_high_demand', true)
                  ->distinct();
        })
        ->where('is_available', true)
        ->orderBy('min_price')
        ->limit(100)
        ->get();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(600, $executionTime, 'Subquery should complete within 600ms');
        $this->assertGreaterThan(0, $results->count(), 'Subquery should return results');
        
        echo "\nSubquery Performance: " . round($executionTime, 2) . "ms\n";
    }

    public function test_concurrent_read_performance()
    {
        $this->generateTestData();
        
        $queries = [];
        $startTime = microtime(true);
        
        // Simulate multiple concurrent read operations
        for ($i = 0; $i < 10; $i++) {
            $queryStart = microtime(true);
            
            $result = ScrapedTicket::where('platform', 'stubhub')
                ->where('is_available', true)
                ->where('event_date', '>', now())
                ->orderBy('scraped_at', 'desc')
                ->limit(20)
                ->get();
            
            $queryTime = (microtime(true) - $queryStart) * 1000;
            $queries[] = $queryTime;
            
            $this->assertGreaterThan(0, $result->count());
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        $avgQueryTime = array_sum($queries) / count($queries);
        $maxQueryTime = max($queries);
        
        $this->assertLessThan(200, $avgQueryTime, 'Average concurrent query time should be under 200ms');
        $this->assertLessThan(500, $maxQueryTime, 'Max concurrent query time should be under 500ms');
        
        echo "\nConcurrent Read Performance:\n";
        echo "  Total time: " . round($totalTime, 2) . "ms\n";
        echo "  Average query: " . round($avgQueryTime, 2) . "ms\n";
        echo "  Max query: " . round($maxQueryTime, 2) . "ms\n";
    }

    public function test_database_connection_pooling()
    {
        // Test that database connections are reused efficiently
        $connectionsBefore = DB::getConnections();
        
        // Perform multiple database operations
        for ($i = 0; $i < 5; $i++) {
            User::count();
            ScrapedTicket::count();
            Category::count();
        }
        
        $connectionsAfter = DB::getConnections();
        
        // Should not create excessive connections
        $this->assertCount(count($connectionsBefore), $connectionsAfter, 'Should reuse database connections');
    }

    public function test_query_optimization_suggestions()
    {
        $this->generateTestData();
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Run some potentially unoptimized queries
        $results = ScrapedTicket::all(); // Should use pagination
        $this->assertLessThan(1000, $results->count(), 'Should limit large result sets');
        
        // Query without indexes
        $unindexedResults = ScrapedTicket::where('metadata', 'LIKE', '%test%')->get();
        
        // N+1 query problem test
        $ticketsWithCategories = ScrapedTicket::limit(10)->get();
        foreach ($ticketsWithCategories as $ticket) {
            $category = $ticket->category; // This could cause N+1 queries
        }
        
        $queries = DB::getQueryLog();
        
        // Should not have excessive number of queries for N+1 test
        $this->assertLessThan(15, count($queries), 'Should avoid N+1 query problems');
        
        DB::disableQueryLog();
    }

    public function test_memory_efficient_queries()
    {
        $this->generateTestData();
        
        $initialMemory = memory_get_usage();
        
        // Test chunk processing for large datasets
        $processedCount = 0;
        ScrapedTicket::where('is_available', true)
            ->chunk(100, function($tickets) use (&$processedCount) {
                $processedCount += $tickets->count();
                
                // Process tickets without loading all into memory
                foreach ($tickets as $ticket) {
                    // Simulate processing
                    $processed = [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                        'price' => $ticket->min_price
                    ];
                }
            });
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = ($finalMemory - $initialMemory) / 1024 / 1024; // MB
        
        $this->assertGreaterThan(0, $processedCount, 'Should process some tickets');
        $this->assertLessThan(50, $memoryIncrease, 'Memory usage should remain reasonable during chunk processing');
        
        echo "\nMemory Efficient Query Test:\n";
        echo "  Processed: {$processedCount} tickets\n";
        echo "  Memory increase: " . round($memoryIncrease, 2) . " MB\n";
    }

    protected function generateTestData(): void
    {
        // Clear existing data
        ScrapedTicket::truncate();
        
        // Create categories first
        $categories = Category::factory()->count(5)->create();
        
        // Generate test data in batches to avoid memory issues
        $totalTickets = 3000;
        $batchSize = 500;
        
        echo "\nGenerating {$totalTickets} test tickets for performance testing...\n";
        
        for ($i = 0; $i < $totalTickets; $i += $batchSize) {
            $remainingCount = min($batchSize, $totalTickets - $i);
            
            ScrapedTicket::factory()
                ->count($remainingCount)
                ->create([
                    'category_id' => $categories->random()->id
                ]);
            
            if (($i + $remainingCount) % 1000 === 0) {
                echo "Generated " . ($i + $remainingCount) . " tickets...\n";
            }
        }
        
        echo "Generated {$totalTickets} test tickets successfully.\n";
    }
}
