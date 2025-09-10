<?php declare(strict_types=1);

namespace Tests\Performance;

use App\Jobs\SendBulkNotifications;
use App\Models\PurchaseAttempt;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AutomatedPurchaseEngine;
use App\Services\Core\ScrapingService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

use function count;

class SystemPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const int PERFORMANCE_THRESHOLD_MS = 1000; // 1 second max

    private const int MEMORY_THRESHOLD_MB = 100; // 100MB max

    private const int CONCURRENT_USERS = 10;

    /**
     * @test
     */
    public function it_can_handle_high_volume_ticket_listing_requests(): void
    {
        // Create a large dataset
        $this->createLargeTicketDataset(1000);

        $metrics = $this->measurePerformance(function () {
            $response = $this->getJson('/api/tickets?per_page=50');
            $this->assertEquals(200, $response->status());

            return $response;
        });

        $this->assertLessThan(self::PERFORMANCE_THRESHOLD_MS, $metrics['execution_time'] * 1000);
        $this->assertLessThan(self::MEMORY_THRESHOLD_MB * 1024 * 1024, $metrics['memory_used']);
    }

    /**
     * @test
     */
    public function it_can_handle_complex_ticket_filtering_efficiently(): void
    {
        $this->createLargeTicketDataset(500);

        $metrics = $this->measurePerformance(function () {
            $response = $this->getJson('/api/tickets?sport_type=football&min_price=50&max_price=200&city=Manchester&sort=price_asc');
            $this->assertEquals(200, $response->status());

            return $response;
        });

        $this->assertLessThan(self::PERFORMANCE_THRESHOLD_MS, $metrics['execution_time'] * 1000);
    }

    /**
     * @test
     */
    public function it_can_handle_concurrent_user_registrations(): void
    {
        $concurrentRequests = [];
        $startTime = microtime(TRUE);

        // Simulate concurrent user registrations
        for ($i = 0; $i < self::CONCURRENT_USERS; $i++) {
            $concurrentRequests[] = $this->postJson('/api/register', [
                'name'                  => "User {$i}",
                'email'                 => "user{$i}@example.com",
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        $endTime = microtime(TRUE);
        $totalTime = $endTime - $startTime;

        // Verify all requests completed successfully
        foreach ($concurrentRequests as $response) {
            $this->assertContains($response->status(), [201, 422]); // 422 for validation errors
        }

        // Should handle concurrent requests within reasonable time
        $this->assertLessThan(5.0, $totalTime); // 5 seconds max for all concurrent requests
    }

    /**
     * @test
     */
    public function it_can_handle_bulk_purchase_attempts_efficiently(): void
    {
        $user = $this->createTestUser();
        $tickets = $this->createMultipleTickets(100);

        $this->actingAs($user, 'sanctum');

        $metrics = $this->measurePerformance(function () use ($tickets): void {
            foreach ($tickets as $ticket) {
                $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", [
                    'quantity'  => 1,
                    'max_price' => 100.00,
                ]);
            }
        });

        // Should process 100 purchase attempts efficiently
        $this->assertLessThan(5.0, $metrics['execution_time']);
        $this->assertLessThan(self::MEMORY_THRESHOLD_MB * 1024 * 1024, $metrics['memory_used']);
    }

    /**
     * @test
     */
    public function database_queries_are_optimized_for_ticket_listing(): void
    {
        $this->createLargeTicketDataset(200);

        // Enable query logging
        DB::enableQueryLog();

        $response = $this->getJson('/api/tickets?per_page=20');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEquals(200, $response->status());

        // Should not exceed reasonable number of queries (avoiding N+1 problem)
        $this->assertLessThan(10, count($queries));

        // Check for expensive queries (longer than 100ms)
        foreach ($queries as $query) {
            $this->assertLessThan(100, $query['time']); // 100ms max per query
        }
    }

    /**
     * @test
     */
    public function scraping_service_performs_efficiently_under_load(): void
    {
        $sources = $this->createMultipleTicketSources(5);
        $scrapingService = app(ScrapingService::class);

        $metrics = $this->measurePerformance(function () use ($scrapingService, $sources): void {
            foreach ($sources as $source) {
                $scrapingService->scrapeSource($source->id);
            }
        });

        // Should scrape 5 sources within reasonable time
        $this->assertLessThan(10.0, $metrics['execution_time']);
    }

    /**
     * @test
     */
    public function notification_service_handles_bulk_notifications_efficiently(): void
    {
        $users = $this->createMultipleUsers(100);
        $notificationService = app(NotificationService::class);

        $metrics = $this->measurePerformance(function () use ($notificationService, $users): void {
            $notificationService->sendBulkEmailNotifications(
                collect($users),
                'Test Notification',
                'This is a test message',
            );
        });

        // Should queue 100 notifications efficiently
        $this->assertLessThan(2.0, $metrics['execution_time']);

        // Verify notifications were queued, not sent immediately
        Queue::assertPushed(SendBulkNotifications::class);
    }

    /**
     * @test
     */
    public function cache_improves_api_response_times(): void
    {
        $this->createLargeTicketDataset(500);

        // First request (no cache)
        $firstMetrics = $this->measurePerformance(function (): void {
            $response = $this->getJson('/api/tickets/statistics');
            $this->assertEquals(200, $response->status());
        });

        // Second request (with cache)
        $secondMetrics = $this->measurePerformance(function (): void {
            $response = $this->getJson('/api/tickets/statistics');
            $this->assertEquals(200, $response->status());
        });

        // Cached response should be significantly faster
        $this->assertLessThan($firstMetrics['execution_time'] * 0.5, $secondMetrics['execution_time']);
    }

    /**
     * @test
     */
    public function purchase_engine_processes_queue_efficiently(): void
    {
        $users = $this->createMultipleUsers(50);
        $tickets = $this->createMultipleTickets(50);
        $purchaseEngine = app(AutomatedPurchaseEngine::class);

        // Create purchase attempts
        foreach ($users as $index => $user) {
            $this->createPurchaseAttempt([
                'user_id'   => $user->id,
                'ticket_id' => $tickets[$index]->id,
                'status'    => 'pending',
            ]);
        }

        $metrics = $this->measurePerformance(function () use ($purchaseEngine): void {
            $purchaseEngine->processPendingPurchases(50);
        });

        // Should process 50 purchase attempts efficiently
        $this->assertLessThan(30.0, $metrics['execution_time']); // 30 seconds max
    }

    /**
     * @test
     */
    public function memory_usage_remains_stable_during_long_operations(): void
    {
        $initialMemory = memory_get_usage();

        // Perform memory-intensive operations
        for ($i = 0; $i < 10; $i++) {
            $this->createTestTicket();
            $this->createTestUser();

            // Check memory usage every iteration
            $currentMemory = memory_get_usage();
            $memoryIncrease = $currentMemory - $initialMemory;

            // Memory shouldn't grow excessively
            $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease); // 50MB max increase
        }
    }

    /**
     * @test
     */
    public function api_rate_limiting_performs_efficiently(): void
    {
        $metrics = $this->measurePerformance(function (): void {
            // Make requests up to the rate limit
            for ($i = 0; $i < 60; $i++) {
                $response = $this->getJson('/api/tickets');
                if ($response->status() === 429) {
                    break;
                }
            }
        });

        // Rate limiting checks shouldn't significantly impact performance
        $this->assertLessThan(5.0, $metrics['execution_time']);
    }

    /**
     * @test
     */
    public function database_connection_pool_handles_concurrent_requests(): void
    {
        $concurrentQueries = [];

        $metrics = $this->measurePerformance(function () use (&$concurrentQueries): void {
            // Simulate multiple concurrent database operations
            for ($i = 0; $i < 20; $i++) {
                $concurrentQueries[] = DB::table('tickets')->count();
            }
        });

        $this->assertCount(20, $concurrentQueries);
        $this->assertLessThan(3.0, $metrics['execution_time']);
    }

    /**
     * @test
     */
    public function search_functionality_performs_well_with_large_dataset(): void
    {
        $this->createLargeTicketDataset(1000);

        $searchTerms = [
            'Manchester United',
            'football',
            'stadium',
            'Liverpool',
        ];

        foreach ($searchTerms as $term) {
            $metrics = $this->measurePerformance(function () use ($term): void {
                $response = $this->getJson("/api/tickets?search={$term}");
                $this->assertEquals(200, $response->status());
            });

            $this->assertLessThan(self::PERFORMANCE_THRESHOLD_MS, $metrics['execution_time'] * 1000);
        }
    }

    /**
     * @test
     */
    public function pagination_performs_efficiently_across_large_datasets(): void
    {
        $this->createLargeTicketDataset(10000);

        // Test different page sizes
        $pageSizes = [10, 50, 100];

        foreach ($pageSizes as $pageSize) {
            $metrics = $this->measurePerformance(function () use ($pageSize): void {
                // Test first page
                $response = $this->getJson("/api/tickets?per_page={$pageSize}&page=1");
                $this->assertEquals(200, $response->status());

                // Test middle page
                $response = $this->getJson("/api/tickets?per_page={$pageSize}&page=50");
                $this->assertEquals(200, $response->status());

                // Test last page
                $totalPages = ceil(10000 / $pageSize);
                $response = $this->getJson("/api/tickets?per_page={$pageSize}&page={$totalPages}");
                $this->assertEquals(200, $response->status());
            });

            $this->assertLessThan(3.0, $metrics['execution_time']);
        }
    }

    /**
     * Create a large dataset of tickets for performance testing
     */
    private function createLargeTicketDataset(int $count): void
    {
        $sources = $this->createMultipleTicketSources(5);

        // Use database transactions for better performance
        DB::transaction(function () use ($count, $sources): void {
            for ($i = 0; $i < $count; $i++) {
                $this->createTestTicket([
                    'title'      => "Performance Test Ticket {$i}",
                    'sport_type' => ['football', 'basketball', 'baseball'][$i % 3],
                    'city'       => ['Manchester', 'Liverpool', 'London', 'Birmingham'][$i % 4],
                    'price_min'  => random_int(25, 100),
                    'price_max'  => random_int(100, 500),
                    'source_id'  => $sources[$i % 5]->id,
                ]);
            }
        });
    }

    /**
     * Create multiple ticket sources
     */
    private function createMultipleTicketSources(int $count): array
    {
        $sources = [];

        for ($i = 0; $i < $count; $i++) {
            $sources[] = $this->createTestTicketSource([
                'name' => "Performance Test Source {$i}",
            ]);
        }

        return $sources;
    }

    /**
     * Create multiple users
     */
    private function createMultipleUsers(int $count): array
    {
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $users[] = $this->createTestUser([
                'email' => "perftest{$i}@example.com",
            ]);
        }

        return $users;
    }

    /**
     * Create multiple tickets
     */
    private function createMultipleTickets(int $count): array
    {
        $tickets = [];

        for ($i = 0; $i < $count; $i++) {
            $tickets[] = $this->createTestTicket([
                'title' => "Perf Ticket {$i}",
            ]);
        }

        return $tickets;
    }

    /**
     * Create a purchase attempt
     */
    private function createPurchaseAttempt(array $attributes = []): PurchaseAttempt
    {
        return $this->testDataFactory->createPurchaseAttempt($attributes);
    }
}
