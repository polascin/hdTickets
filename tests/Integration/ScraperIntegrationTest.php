<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\TicketScrapingService;
use App\Services\TicketApis\StubHubClient;
use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\FunZoneClient;
use App\Models\ScrapedTicket;
use App\Models\User;
use App\Models\TicketAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;

class ScraperIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake queue and notifications for integration tests
        Queue::fake();
        Notification::fake();
    }

    public function test_full_scraping_workflow_with_stubhub()
    {
        // Mock StubHub responses
        Http::fake([
            'stubhub.com/*' => Http::response($this->getStubHubMockResponse(), 200)
        ]);

        $service = new TicketScrapingService();
        
        // Test search functionality
        $results = $service->searchTickets('Manchester United', [
            'platforms' => ['stubhub'],
            'max_price' => 500,
            'currency' => 'GBP'
        ]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('stubhub', $results);
        
        // Verify ticket data structure
        if (!empty($results['stubhub'])) {
            $ticket = $results['stubhub'][0];
            $this->assertValidTicketStructure($ticket);
        }
    }

    public function test_full_scraping_workflow_with_ticketmaster()
    {
        // Mock Ticketmaster responses
        Http::fake([
            'ticketmaster.com/*' => Http::response($this->getTicketmasterMockResponse(), 200)
        ]);

        $service = new TicketScrapingService();
        
        $results = $service->searchTickets('Football', [
            'platforms' => ['ticketmaster'],
            'max_price' => 400,
            'currency' => 'GBP'
        ]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('ticketmaster', $results);
    }

    public function test_scraper_with_web_scraping_fallback()
    {
        // Mock FunZone HTML responses
        Http::fake([
            'funzone.sk/*' => Http::response($this->getFunZoneMockResponse(), 200)
        ]);

        $client = new FunZoneClient([
            'enabled' => true,
            'timeout' => 30,
            'scraping' => ['enabled' => true]
        ]);

        $results = $client->searchEvents([
            'q' => 'football',
            'city' => 'Bratislava',
            'per_page' => 10
        ]);

        $this->assertIsArray($results);
    }

    public function test_multi_platform_scraping_integration()
    {
        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // Test scraping across multiple platforms
        $results = $service->searchTickets('Sports Event', [
            'platforms' => ['stubhub', 'ticketmaster', 'viagogo'],
            'max_price' => 300
        ]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('stubhub', $results);
        $this->assertArrayHasKey('ticketmaster', $results);
        $this->assertArrayHasKey('viagogo', $results);
    }

    public function test_scraping_with_user_alerts_integration()
    {
        $user = $this->createUser();
        $alert = $this->createTicketAlert($user, [
            'keywords' => 'Manchester United',
            'max_price' => 400.00,
            'platform' => 'stubhub',
            'is_active' => true
        ]);

        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // Trigger alert checking
        $alertsChecked = $service->checkAlerts();

        $this->assertGreaterThan(0, $alertsChecked);
        
        // Verify alert was triggered
        Notification::assertSentTo($user, \App\Notifications\HighValueTicketAlert::class);
    }

    public function test_scraping_error_handling_integration()
    {
        // Mock various error conditions
        Http::fake([
            'stubhub.com/*' => Http::response('Server Error', 500),
            'ticketmaster.com/*' => Http::response('Rate Limit', 429, [
                'Retry-After' => '60'
            ]),
            'viagogo.com/*' => Http::response('Not Found', 404)
        ]);

        $service = new TicketScrapingService();
        
        $results = $service->searchTickets('Test Event', [
            'platforms' => ['stubhub', 'ticketmaster', 'viagogo']
        ]);

        // Should handle errors gracefully and return empty arrays
        $this->assertIsArray($results);
        $this->assertEmpty($results['stubhub']);
        $this->assertEmpty($results['ticketmaster']);
        $this->assertEmpty($results['viagogo']);
    }

    public function test_scraping_data_persistence_integration()
    {
        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // Initial count
        $initialCount = ScrapedTicket::count();
        
        // Search and save results
        $results = $service->searchManchesterUnitedTickets();

        // Verify tickets were saved to database
        $finalCount = ScrapedTicket::count();
        $this->assertGreaterThan($initialCount, $finalCount);
        
        // Verify ticket data integrity
        $savedTicket = ScrapedTicket::first();
        $this->assertNotNull($savedTicket->uuid);
        $this->assertNotNull($savedTicket->scraped_at);
        $this->assertTrue(in_array($savedTicket->platform, ['stubhub', 'ticketmaster', 'viagogo']));
    }

    public function test_scraping_deduplication_integration()
    {
        // Create existing ticket
        $existing = $this->createScrapedTicket([
            'external_id' => 'stubhub-123',
            'platform' => 'stubhub',
            'min_price' => 100.00,
            'max_price' => 200.00
        ]);

        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        $initialCount = ScrapedTicket::count();
        
        // Run scraping - should update existing ticket, not create new one
        $results = $service->searchManchesterUnitedTickets();

        $finalCount = ScrapedTicket::count();
        
        // Should not create duplicate
        $this->assertEquals($initialCount + $results['saved'] - 1, $finalCount);
        
        // Should update existing ticket with new price
        $existing->refresh();
        $this->assertEquals(150.00, $existing->min_price); // From mock response
    }

    public function test_scraping_with_concurrent_users()
    {
        // Create multiple scraper users
        $scraperUsers = User::factory()->count(3)->create([
            'role' => 'scraper',
            'is_scraper_account' => true
        ]);

        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // Simulate concurrent scraping requests
        $results1 = $service->searchTickets('Event 1', ['platforms' => ['stubhub']]);
        $results2 = $service->searchTickets('Event 2', ['platforms' => ['stubhub']]);
        $results3 = $service->searchTickets('Event 3', ['platforms' => ['stubhub']]);

        // All should succeed with user rotation
        $this->assertIsArray($results1);
        $this->assertIsArray($results2);
        $this->assertIsArray($results3);
    }

    public function test_scraping_performance_monitoring()
    {
        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        $startTime = microtime(true);
        
        $results = $service->searchTickets('Performance Test', [
            'platforms' => ['stubhub', 'ticketmaster']
        ]);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Should complete within reasonable time (5 seconds)
        $this->assertLessThan(5000, $executionTime);
        
        // Should return results
        $this->assertIsArray($results);
    }

    public function test_scraping_with_cache_integration()
    {
        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // First request
        $startTime1 = microtime(true);
        $results1 = $service->searchTickets('Cache Test', ['platforms' => ['stubhub']]);
        $time1 = (microtime(true) - $startTime1) * 1000;
        
        // Second request (should use cache)
        $startTime2 = microtime(true);
        $results2 = $service->searchTickets('Cache Test', ['platforms' => ['stubhub']]);
        $time2 = (microtime(true) - $startTime2) * 1000;
        
        // Cached request should be faster
        $this->assertLessThan($time1, $time2);
        
        // Results should be identical
        $this->assertEquals($results1, $results2);
    }

    public function test_scraping_with_queue_integration()
    {
        $this->mockTicketPlatformResponses();

        // Create user with alert
        $user = $this->createUser();
        $alert = $this->createTicketAlert($user, [
            'keywords' => 'Manchester United',
            'max_price' => 500.00,
            'is_active' => true
        ]);

        $service = new TicketScrapingService();
        
        // Run scraping (should queue notifications)
        $service->searchManchesterUnitedTickets();

        // Verify jobs were queued
        Queue::assertPushed(\Illuminate\Notifications\SendQueuedNotifications::class);
    }

    public function test_full_end_to_end_scraping_workflow()
    {
        // Setup: Create users and alerts
        $customer = $this->createUser('customer');
        $scraperUser = $this->createUser('scraper', ['is_scraper_account' => true]);
        
        $alert = $this->createTicketAlert($customer, [
            'keywords' => 'Manchester United',
            'max_price' => 300.00,
            'platform' => null, // Any platform
            'is_active' => true,
            'last_checked_at' => now()->subHours(2)
        ]);

        // Mock all platform responses
        $this->mockTicketPlatformResponses();

        $service = new TicketScrapingService();
        
        // Step 1: Search for tickets
        $searchResults = $service->searchManchesterUnitedTickets(400, null);
        
        $this->assertScrapingSuccessful($searchResults);
        $this->assertGreaterThan(0, $searchResults['saved']);

        // Step 2: Check alerts
        $alertsChecked = $service->checkAlerts();
        $this->assertGreaterThan(0, $alertsChecked);

        // Step 3: Verify notifications sent
        Notification::assertSentTo($customer, \App\Notifications\HighValueTicketAlert::class);

        // Step 4: Verify data in database
        $tickets = ScrapedTicket::where('platform', '!=', null)->get();
        $this->assertGreaterThan(0, $tickets->count());

        // Step 5: Test auto-purchase workflow
        $ticket = $tickets->first();
        $purchaseResult = $service->attemptAutoPurchase(
            $ticket->id,
            $customer->id,
            500.00
        );

        $this->assertTrue($purchaseResult['success']);
        $this->assertArrayHasKey('redirect_url', $purchaseResult);
    }
}
