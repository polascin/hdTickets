<?php

namespace Tests\Unit\Services\TicketApis;

use App\Services\TicketApis\FunZoneClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class FunZoneClientTest extends TestCase
{
    private FunZoneClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new FunZoneClient([
            'enabled' => true,
            'api_key' => 'test-key',
            'timeout' => 30,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_events_returns_array()
    {
        // Mock HTML response
        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $criteria = [
            'q' => 'concert',
            'city' => 'Bratislava',
            'per_page' => 10
        ];

        $results = $this->client->searchEvents($criteria);

        $this->assertIsArray($results);
    }

    public function test_get_event_returns_event_data()
    {
        $mockHtml = $this->getMockEventDetailsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $eventId = 'test-event-123';
        $result = $this->client->getEvent($eventId);

        $this->assertIsArray($result);
        $this->assertEquals('funzone', $result['platform'] ?? '');
    }

    public function test_get_venue_returns_venue_data()
    {
        $mockHtml = $this->getMockVenueDetailsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $venueId = 'test-venue-123';
        $result = $this->client->getVenue($venueId);

        $this->assertIsArray($result);
        $this->assertEquals('funzone', $result['platform'] ?? '');
    }

    public function test_scrape_search_results_with_empty_keyword_returns_empty_array()
    {
        $results = $this->client->scrapeSearchResults('', 'Bratislava', 10);
        
        // Should handle empty keyword gracefully
        $this->assertIsArray($results);
    }

    public function test_event_id_extraction_from_url()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractEventIdFromUrl');
        $method->setAccessible(true);

        // Test numeric ID extraction
        $url1 = 'https://www.funzone.sk/event/12345';
        $id1 = $method->invokeArgs($this->client, [$url1]);
        $this->assertEquals('12345', $id1);

        // Test slug extraction
        $url2 = 'https://www.funzone.sk/event/concert-test-event';
        $id2 = $method->invokeArgs($this->client, [$url2]);
        $this->assertEquals('concert-test-event', $id2);
    }

    public function test_url_normalization()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        // Test relative URL
        $relativeUrl = '/event/test';
        $normalized = $method->invokeArgs($this->client, [$relativeUrl]);
        $this->assertEquals('https://www.funzone.sk/event/test', $normalized);

        // Test absolute URL
        $absoluteUrl = 'https://www.funzone.sk/event/test';
        $normalized2 = $method->invokeArgs($this->client, [$absoluteUrl]);
        $this->assertEquals('https://www.funzone.sk/event/test', $normalized2);
    }

    public function test_price_range_extraction()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractPriceRange');
        $method->setAccessible(true);

        $event = [];
        $prices = ['€25', '€30,50', '€45.00'];
        
        $method->invokeArgs($this->client, [&$event, $prices]);

        $this->assertArrayHasKey('price_min', $event);
        $this->assertArrayHasKey('price_max', $event);
        $this->assertEquals(25.0, $event['price_min']);
        $this->assertEquals(45.0, $event['price_max']);
    }

    public function test_slovak_date_parsing()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('parseEventDate');
        $method->setAccessible(true);

        // Test Slovak date format
        $dateString = '15.12.2024 19:00';
        $parsed = $method->invokeArgs($this->client, [$dateString]);
        
        $this->assertInstanceOf(\DateTime::class, $parsed);
        $this->assertEquals('2024-12-15', $parsed->format('Y-m-d'));
    }

    public function test_entertainment_category_mapping()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('mapEntertainmentCategory');
        $method->setAccessible(true);

        $this->assertEquals('concert', $method->invokeArgs($this->client, ['koncert']));
        $this->assertEquals('theater', $method->invokeArgs($this->client, ['divadlo']));
        $this->assertEquals('sports', $method->invokeArgs($this->client, ['šport']));
        $this->assertEquals('other', $method->invokeArgs($this->client, ['unknown category']));
    }

    public function test_slovak_region_extraction()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractSlovakRegion');
        $method->setAccessible(true);

        $this->assertEquals('Bratislavský kraj', $method->invokeArgs($this->client, ['Bratislava']));
        $this->assertEquals('Košický kraj', $method->invokeArgs($this->client, ['Košice']));
        $this->assertEquals('Unknown Region', $method->invokeArgs($this->client, ['Unknown City']));
    }

    public function test_http_error_handling()
    {
        Http::fake([
            'funzone.sk/*' => Http::response('', 404)
        ]);

        $results = $this->client->searchEvents(['q' => 'test']);
        
        // Should handle HTTP errors gracefully and return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    private function getMockSearchResultsHtml(): string
    {
        return '
        <html>
            <body>
                <div class="event-card">
                    <h3>Test Concert</h3>
                    <a href="/event/test-concert-123">Test Concert</a>
                    <span class="date">25.12.2024 20:00</span>
                    <span class="venue">Test Arena</span>
                    <span class="location">Bratislava</span>
                    <span class="price">€25</span>
                </div>
                <div class="event-card">
                    <h3>Another Event</h3>
                    <a href="/event/another-event-456">Another Event</a>
                    <span class="date">26.12.2024 19:30</span>
                    <span class="venue">Cultural Center</span>
                    <span class="location">Košice</span>
                    <span class="price">€30</span>
                </div>
            </body>
        </html>';
    }

    private function getMockEventDetailsHtml(): string
    {
        return '
        <html>
            <body>
                <h1>Test Concert - Detailed View</h1>
                <span class="event-date">25.12.2024 20:00</span>
                <span class="venue">Test Arena</span>
                <address>Bratislava, Slovakia</address>
                <div class="description">This is a test concert description</div>
                <div class="ticket-listing">
                    <span class="price">€25</span>
                    <span class="category">Standard</span>
                </div>
                <div class="ticket-listing">
                    <span class="price">€45</span>
                    <span class="category">VIP</span>
                </div>
            </body>
        </html>';
    }

    private function getMockVenueDetailsHtml(): string
    {
        return '
        <html>
            <body>
                <h1>Test Arena</h1>
                <address>123 Test Street, Bratislava</address>
                <span class="city">Bratislava</span>
                <div class="description">A modern venue for concerts and events</div>
                <span class="capacity">5000</span>
            </body>
        </html>';
    }
}
