<?php

namespace Tests\Unit\Services\TicketApis;

use App\Services\TicketApis\StubHubClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class StubHubClientTest extends TestCase
{
    private StubHubClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new StubHubClient([
            'enabled' => true,
            'api_key' => 'test-api-key',
            'app_token' => 'test-app-token',
            'timeout' => 30,
            'sandbox' => true
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_events_with_api_credentials()
    {
        $mockApiResponse = [
            'events' => [
                [
                    'id' => '12345',
                    'name' => 'Test Concert',
                    'date' => '2024-12-25T20:00:00Z',
                    'venue' => 'Test Arena',
                    'city' => 'New York'
                ]
            ]
        ];

        Http::fake([
            'api.stubhub.com/*' => Http::response($mockApiResponse, 200)
        ]);

        $criteria = [
            'q' => 'concert',
            'city' => 'New York',
            'per_page' => 10
        ];

        $results = $this->client->searchEvents($criteria);

        $this->assertIsArray($results);
    }

    public function test_search_events_fallback_to_scraping()
    {
        // Create client without API credentials
        $clientWithoutApi = new StubHubClient([
            'enabled' => true,
            'timeout' => 30,
        ]);

        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'stubhub.com/*' => Http::response($mockHtml, 200)
        ]);

        $criteria = [
            'q' => 'concert',
            'city' => 'New York',
            'per_page' => 10
        ];

        $results = $clientWithoutApi->searchEvents($criteria);

        $this->assertIsArray($results);
    }

    public function test_scrape_search_results_method()
    {
        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'stubhub.com/*' => Http::response($mockHtml, 200)
        ]);

        $results = $this->client->scrapeSearchResults('concert', 'New York', 25);

        $this->assertIsArray($results);
    }

    public function test_scrape_event_details_method()
    {
        $mockHtml = $this->getMockEventDetailsHtml();
        
        Http::fake([
            'stubhub.com/*' => Http::response($mockHtml, 200)
        ]);

        $eventUrl = 'https://www.stubhub.com/event/12345';
        $result = $this->client->scrapeEventDetails($eventUrl);

        $this->assertIsArray($result);
    }

    public function test_get_event_with_api()
    {
        $mockApiResponse = [
            'id' => '12345',
            'name' => 'Test Concert',
            'date' => '2024-12-25T20:00:00Z',
            'venue' => 'Test Arena'
        ];

        Http::fake([
            'api.stubhub.com/*' => Http::response($mockApiResponse, 200)
        ]);

        $eventId = '12345';
        $result = $this->client->getEvent($eventId);

        $this->assertIsArray($result);
    }

    public function test_get_event_fallback_to_scraping()
    {
        // Mock API failure, then successful scraping
        Http::fake([
            'api.stubhub.com/*' => Http::response('', 500),
            'stubhub.com/*' => Http::response($this->getMockEventDetailsHtml(), 200)
        ]);

        $eventId = '12345';
        $result = $this->client->getEvent($eventId);

        $this->assertIsArray($result);
        $this->assertEquals('stubhub', $result['platform'] ?? '');
    }

    public function test_build_scraping_search_url()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildScrapingSearchUrl');
        $method->setAccessible(true);

        $criteria = [
            'q' => 'test concert',
            'city' => 'New York',
            'date_start' => '2024-12-01',
            'date_end' => '2024-12-31',
            'per_page' => 20
        ];

        $url = $method->invokeArgs($this->client, [$criteria]);

        $this->assertStringContains('stubhub.com', $url);
        $this->assertStringContains('q=test%2Bconcert', $url);
        $this->assertStringContains('city=New%2BYork', $url);
    }

    public function test_extract_price_range()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractPriceRange');
        $method->setAccessible(true);

        $event = [];
        $prices = ['$25', '$30', '$45'];
        
        $method->invokeArgs($this->client, [&$event, $prices]);

        $this->assertArrayHasKey('price_min', $event);
        $this->assertArrayHasKey('price_max', $event);
        $this->assertEquals(25.0, $event['price_min']);
        $this->assertEquals(45.0, $event['price_max']);
    }

    public function test_rate_limiting()
    {
        Cache::shouldReceive('get')
            ->with('rate_limit_stubhub')
            ->once()
            ->andReturn([]);

        Cache::shouldReceive('put')
            ->once()
            ->andReturn(true);

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('respectRateLimit');
        $method->setAccessible(true);

        // This should not throw any exceptions
        $method->invokeArgs($this->client, ['stubhub']);
        
        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_anti_detection_headers()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildAntiDetectionHeaders');
        $method->setAccessible(true);

        $headers = $method->invokeArgs($this->client, []);

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertArrayHasKey('Accept-Language', $headers);
    }

    public function test_error_handling_for_bot_detection()
    {
        $mockBotDetectionHtml = '<html><body>Access Denied - Captcha required</body></html>';
        
        Http::fake([
            'stubhub.com/*' => Http::response($mockBotDetectionHtml, 403)
        ]);

        $results = $this->client->searchEvents(['q' => 'test']);
        
        // Should handle bot detection gracefully
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function test_venue_basic_info()
    {
        $venueId = 'test-venue-123';
        $result = $this->client->getVenue($venueId);

        $this->assertIsArray($result);
        $this->assertEquals($venueId, $result['id']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('city', $result);
        $this->assertArrayHasKey('country', $result);
    }

    private function getMockSearchResultsHtml(): string
    {
        return '
        <html>
            <body>
                <div class="EventCard">
                    <h3 class="event-name">Test Concert</h3>
                    <a href="/event/test-concert-123">View Event</a>
                    <span class="date">Dec 25, 2024 8:00 PM</span>
                    <span class="venue">Madison Square Garden</span>
                    <span class="price">$75</span>
                </div>
                <div class="SearchResultCard">
                    <h4 class="title">Broadway Show</h4>
                    <a href="/event/broadway-show-456">View Show</a>
                    <span class="event-date">Dec 26, 2024 7:30 PM</span>
                    <span class="venue-name">Theatre District</span>
                    <span class="price">$125</span>
                </div>
            </body>
        </html>';
    }

    private function getMockEventDetailsHtml(): string
    {
        return '
        <html>
            <body>
                <h1 class="event-title">Test Concert - Live in NYC</h1>
                <span class="event-date">December 25, 2024 8:00 PM</span>
                <span class="venue">Madison Square Garden</span>
                <address>New York, NY</address>
                <div class="description">An amazing concert experience</div>
                <div class="listing">
                    <span class="price">$75</span>
                    <span class="section">General Admission</span>
                </div>
                <div class="ticket-listing">
                    <span class="price">$150</span>
                    <span class="section">VIP</span>
                </div>
            </body>
        </html>';
    }
}
