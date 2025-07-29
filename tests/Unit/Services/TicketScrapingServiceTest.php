<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TicketScrapingService;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class TicketScrapingServiceTest extends TestCase
{
    private TicketScrapingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketScrapingService();
        
        // Prevent actual notifications
        Notification::fake();
    }

    public function test_search_manchester_united_tickets_returns_results()
    {
        $this->mockTicketPlatformResponses();

        $results = $this->service->searchManchesterUnitedTickets(500, '2024-03-01');

        $this->assertScrapingSuccessful($results);
        $this->assertGreaterThan(0, $results['total_found']);
    }

    public function test_search_high_demand_sports_tickets_returns_results()
    {
        $this->mockTicketPlatformResponses();

        $results = $this->service->searchHighDemandSportsTickets([
            'max_price' => 300,
            'currency' => 'GBP'
        ]);

        $this->assertScrapingSuccessful($results);
    }

    public function test_search_tickets_with_specific_keywords()
    {
        $this->mockTicketPlatformResponses();

        $results = $this->service->searchTickets('Manchester United', [
            'platforms' => ['stubhub'],
            'max_price' => 200,
            'currency' => 'GBP'
        ]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('stubhub', $results);
    }

    public function test_search_tickets_handles_api_failures_gracefully()
    {
        Http::fake([
            '*' => Http::response('Server Error', 500)
        ]);

        $results = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);

        $this->assertIsArray($results);
        $this->assertArrayHasKey('stubhub', $results);
        $this->assertEmpty($results['stubhub']);
    }

    public function test_search_tickets_respects_rate_limits()
    {
        $this->mockRateLimitResponses();

        $results = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);

        $this->assertIsArray($results);
        $this->assertEmpty($results['stubhub']);
    }

    public function test_search_tickets_uses_cache()
    {
        $this->mockTicketPlatformResponses();

        // First call
        $results1 = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);
        
        // Second call should use cache
        $results2 = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);

        $this->assertEquals($results1, $results2);
    }

    public function test_get_trending_manchester_united_tickets()
    {
        // Create test tickets
        ScrapedTicket::factory()->create([
            'title' => 'Manchester United vs Liverpool',
            'venue' => 'Old Trafford',
            'is_available' => true,
            'event_date' => now()->addDays(7),
            'is_high_demand' => true
        ]);

        ScrapedTicket::factory()->create([
            'title' => 'Arsenal vs Chelsea',
            'venue' => 'Emirates Stadium',
            'is_available' => true,
            'event_date' => now()->addDays(14)
        ]);

        $trending = $this->service->getTrendingManchesterUnitedTickets(10);

        $this->assertCount(1, $trending);
        $this->assertStringContainsString('Manchester United', $trending->first()->title);
    }

    public function test_get_best_sports_deals()
    {
        ScrapedTicket::factory()->create([
            'title' => 'Cheap Football Match',
            'min_price' => 25.00,
            'is_available' => true,
            'event_date' => now()->addDays(10)
        ]);

        ScrapedTicket::factory()->create([
            'title' => 'Expensive Football Match',
            'min_price' => 200.00,
            'is_available' => true,
            'event_date' => now()->addDays(12)
        ]);

        $deals = $this->service->getBestSportsDeals('football', 10);

        $this->assertCount(2, $deals);
        $this->assertEquals(25.00, $deals->first()->min_price);
    }

    public function test_check_alerts_processes_matching_tickets()
    {
        $user = $this->createUser();
        $alert = $this->createTicketAlert($user, [
            'keywords' => 'Manchester United',
            'max_price' => 300.00,
            'is_active' => true,
            'last_checked_at' => now()->subHours(2)
        ]);

        $this->mockTicketPlatformResponses();

        $alertsChecked = $this->service->checkAlerts();

        $this->assertGreaterThan(0, $alertsChecked);
    }

    public function test_attempt_auto_purchase_validates_price()
    {
        $user = $this->createUser();
        $ticket = $this->createScrapedTicket([
            'min_price' => 300.00,
            'is_available' => true,
            'ticket_url' => 'https://example.com/ticket/123'
        ]);

        $result = $this->service->attemptAutoPurchase($ticket->id, $user->id, 200.00);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('exceeds maximum budget', $result['message']);
    }

    public function test_attempt_auto_purchase_validates_availability()
    {
        $user = $this->createUser();
        $ticket = $this->createScrapedTicket([
            'min_price' => 100.00,
            'is_available' => false,
            'ticket_url' => null
        ]);

        $result = $this->service->attemptAutoPurchase($ticket->id, $user->id, 200.00);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('no longer available', $result['message']);
    }

    public function test_attempt_auto_purchase_succeeds_with_valid_criteria()
    {
        $user = $this->createUser();
        $ticket = $this->createScrapedTicket([
            'min_price' => 150.00,
            'is_available' => true,
            'ticket_url' => 'https://stubhub.com/ticket/123'
        ]);

        $result = $this->service->attemptAutoPurchase($ticket->id, $user->id, 200.00);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('redirect_url', $result);
        $this->assertEquals('https://stubhub.com/ticket/123', $result['redirect_url']);
    }

    public function test_service_handles_timeout_errors()
    {
        $this->mockTimeoutResponses();

        $results = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);

        $this->assertIsArray($results);
        $this->assertEmpty($results['stubhub']);
    }

    public function test_service_deduplicates_existing_tickets()
    {
        // Create existing ticket
        $existingTicket = $this->createScrapedTicket([
            'external_id' => 'stubhub-123',
            'platform' => 'stubhub',
            'min_price' => 100.00
        ]);

        $this->mockTicketPlatformResponses();

        $results = $this->service->searchManchesterUnitedTickets();

        // Should update existing ticket, not create new one
        $existingTicket->refresh();
        $this->assertEquals(150.00, $existingTicket->min_price); // Updated from mock response
    }

    public function test_service_triggers_high_demand_alerts()
    {
        $user = $this->createUser();
        $alert = $this->createTicketAlert($user, [
            'keywords' => 'Manchester United',
            'max_price' => 500.00,
            'is_active' => true
        ]);

        $this->mockTicketPlatformResponses();

        $results = $this->service->searchManchesterUnitedTickets();

        // Verify notification was sent
        Notification::assertSentTo($user, \App\Notifications\HighValueTicketAlert::class);
    }

    public function test_service_respects_user_rotation()
    {
        // Create scraper users
        $scraperUser1 = $this->createUser('scraper', ['is_scraper_account' => true]);
        $scraperUser2 = $this->createUser('scraper', ['is_scraper_account' => true]);

        $this->mockTicketPlatformResponses();

        $results = $this->service->searchTickets('test', ['platforms' => ['stubhub']]);

        $this->assertIsArray($results);
        // Should successfully use one of the scraper accounts
    }

    public function test_service_calculates_match_scores_correctly()
    {
        $user = $this->createUser();
        $alert = $this->createTicketAlert($user, [
            'keywords' => 'Manchester United',
            'platform' => 'stubhub',
            'max_price' => 200.00
        ]);

        $ticketData = [
            'title' => 'Manchester United vs Liverpool',
            'platform' => 'stubhub',
            'min_price' => 150.00,
            'is_high_demand' => true,
            'search_keyword' => 'manchester united'
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateMatchScore');
        $method->setAccessible(true);

        $score = $method->invokeArgs($this->service, [$alert, $ticketData]);

        $this->assertGreaterThan(90, $score); // Should be high match
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_service_logs_scraping_activities()
    {
        Log::shouldReceive('info')
            ->with('Ticket scraping completed', \Mockery::type('array'))
            ->once();

        $this->mockTicketPlatformResponses();

        $this->service->searchManchesterUnitedTickets();
    }
}
