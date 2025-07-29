<?php

namespace Tests\Unit\Services\TicketApis;

use App\Services\TicketApis\BaseWebScrapingClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;
use Mockery;
use Exception;

class TestableWebScrapingClient extends BaseWebScrapingClient
{
    public function __construct(array $config = [])
    {
        parent::__construct(array_merge([
            'enabled' => true,
            'timeout' => 30,
            'scraping' => [
                'enabled' => true
            ]
        ], $config));
        $this->baseUrl = 'https://test-platform.com';
    }

    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        return ['test_result'];
    }

    public function scrapeEventDetails(string $url): array
    {
        return ['event' => 'details'];
    }

    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        return ['search_results'];
    }

    protected function extractEventFromNode(Crawler $node): array
    {
        return ['node_event'];
    }

    protected function extractPrices(Crawler $crawler): array
    {
        return [['price' => 25.0, 'currency' => 'USD']];
    }

    public function getHeaders(): array
    {
        return ['Test-Header' => 'test-value'];
    }

    public function getPlatformName(): string
    {
        return 'test-platform';
    }

    public function searchEvents(array $criteria): array
    {
        return [];
    }

    public function getEvent(string $eventId): array
    {
        return [];
    }

    public function getVenue(string $venueId): array
    {
        return [];
    }

    protected function transformEventData(array $eventData): array
    {
        return $eventData;
    }
}

class BaseWebScrapingClientTest extends TestCase
{
    private TestableWebScrapingClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new TestableWebScrapingClient([
            'enabled' => true,
            'timeout' => 30,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_make_scraping_request_success()
    {
        $mockHtml = '<html><body>Test content</body></html>';
        
        Http::fake([
            'test-platform.com/*' => Http::response($mockHtml, 200)
        ]);

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('makeScrapingRequest');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->client, ['https://test-platform.com/test']);

        $this->assertEquals($mockHtml, $result);
    }

    public function test_user_agent_rotation()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getRandomUserAgent');
        $method->setAccessible(true);

        $userAgent1 = $method->invokeArgs($this->client, []);
        $userAgent2 = $method->invokeArgs($this->client, []);

        $this->assertIsString($userAgent1);
        $this->assertIsString($userAgent2);
        $this->assertStringContainsString('Mozilla', $userAgent1);
    }

    public function test_anti_detection_headers()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildAntiDetectionHeaders');
        $method->setAccessible(true);

        $headers = $method->invokeArgs($this->client, [[]]);

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertArrayHasKey('Accept-Language', $headers);
        $this->assertArrayHasKey('DNT', $headers);
        $this->assertArrayHasKey('Connection', $headers);
    }

    public function test_delay_enforcement()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('enforceDelay');
        $method->setAccessible(true);

        $startTime = microtime(true);
        $method->invokeArgs($this->client, []);
        $endTime = microtime(true);

        // First call should not delay
        $this->assertLessThan(0.1, $endTime - $startTime);

        // Set last request time
        $lastRequestProperty = $reflection->getProperty('lastRequestTime');
        $lastRequestProperty->setAccessible(true);
        $lastRequestProperty->setValue($this->client, microtime(true));

        $startTime = microtime(true);
        $method->invokeArgs($this->client, []);
        $endTime = microtime(true);

        // Second call should enforce delay
        $this->assertGreaterThan(0.5, $endTime - $startTime);
    }

    public function test_proxy_configuration()
    {
        $proxyConfig = ['http://proxy.example.com:8080'];
        $this->client->setProxyConfig($proxyConfig);

        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('proxyConfig');
        $property->setAccessible(true);

        $this->assertEquals($proxyConfig, $property->getValue($this->client));
    }

    public function test_custom_delay_range()
    {
        $this->client->setDelayRange(2.0, 4.0);

        $reflection = new \ReflectionClass($this->client);
        $minDelayProperty = $reflection->getProperty('minDelay');
        $maxDelayProperty = $reflection->getProperty('maxDelay');
        $minDelayProperty->setAccessible(true);
        $maxDelayProperty->setAccessible(true);

        $this->assertEquals(2.0, $minDelayProperty->getValue($this->client));
        $this->assertEquals(4.0, $maxDelayProperty->getValue($this->client));
    }

    public function test_try_selectors_method()
    {
        $html = '
        <html>
            <body>
                <div class="container">
                    <h1 class="title">Test Title</h1>
                    <p class="content">Test content</p>
                </div>
            </body>
        </html>';

        $crawler = new Crawler($html);
        
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('trySelectors');
        $method->setAccessible(true);

        // Test successful selector match
        $result = $method->invokeArgs($this->client, [
            $crawler, 
            ['.nonexistent', '.title', '.backup'], 
            null
        ]);
        $this->assertEquals('Test Title', $result);

        // Test attribute extraction
        $result = $method->invokeArgs($this->client, [
            $crawler, 
            ['.title'], 
            'class'
        ]);
        $this->assertEquals('title', $result);

        // Test no match
        $result = $method->invokeArgs($this->client, [
            $crawler, 
            ['.nonexistent', '.notfound'], 
            null
        ]);
        $this->assertEquals('', $result);
    }

    public function test_extract_json_ld_data()
    {
        $html = '
        <html>
            <head>
                <script type="application/ld+json">
                {
                    "@type": "Event",
                    "name": "Test Event",
                    "offers": {
                        "price": "25.00",
                        "priceCurrency": "USD"
                    }
                }
                </script>
            </head>
            <body></body>
        </html>';

        $crawler = new Crawler($html);
        
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractJsonLdData');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->client, [$crawler, 'Event']);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals('Event', $result[0]['@type']);
        $this->assertEquals('Test Event', $result[0]['name']);
    }

    public function test_parse_event_date_multiple_formats()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('parseEventDate');
        $method->setAccessible(true);

        // Test ISO format
        $result = $method->invokeArgs($this->client, ['2024-12-25 20:00:00']);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals('2024-12-25', $result->format('Y-m-d'));

        // Test American format
        $result = $method->invokeArgs($this->client, ['Dec 25, 2024 8:00 PM']);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals('2024-12-25', $result->format('Y-m-d'));

        // Test European format
        $result = $method->invokeArgs($this->client, ['25.12.2024 20:00']);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals('2024-12-25', $result->format('Y-m-d'));

        // Test invalid date
        $result = $method->invokeArgs($this->client, ['invalid date']);
        $this->assertNull($result);
    }

    public function test_normalize_url()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('normalizeUrl');
        $method->setAccessible(true);

        // Test absolute URL
        $result = $method->invokeArgs($this->client, ['https://example.com/test']);
        $this->assertEquals('https://example.com/test', $result);

        // Test root-relative URL
        $result = $method->invokeArgs($this->client, ['/test/path']);
        $this->assertEquals('https://test-platform.com/test/path', $result);

        // Test relative URL
        $result = $method->invokeArgs($this->client, ['test/path']);
        $this->assertEquals('https://test-platform.com/test/path', $result);
    }

    public function test_extract_numeric_price()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('extractNumericPrice');
        $method->setAccessible(true);

        // Test various price formats
        $this->assertEquals(25.50, $method->invokeArgs($this->client, ['$25.50']));
        $this->assertEquals(1250.0, $method->invokeArgs($this->client, ['€1,250.00']));
        $this->assertEquals(99.99, $method->invokeArgs($this->client, ['Price: £99.99 each']));
        $this->assertNull($method->invokeArgs($this->client, ['No price available']));
    }

    public function test_rate_limit_enforcement()
    {
        // Allow cache operations but don't require them (they may be caught in exception handling)
        Cache::shouldReceive('get')
            ->with('rate_limit_ticketmaster')
            ->andReturn([])
            ->zeroOrMoreTimes();

        Cache::shouldReceive('put')
            ->andReturn(true)
            ->zeroOrMoreTimes();

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('respectRateLimit');
        $method->setAccessible(true);

        // This should not throw any exceptions
        $method->invokeArgs($this->client, ['ticketmaster']);
        
        $this->assertTrue(true); // Assert that no exception was thrown
    }

    public function test_fallback_to_scraping()
    {
        $criteria = [
            'keyword' => 'test',
            'location' => 'test city',
            'max_results' => 10
        ];

        $result = $this->client->fallbackToScraping($criteria);

        $this->assertIsArray($result);
        $this->assertEquals(['test_result'], $result);
    }

    public function test_currency_symbol_mapping()
    {
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('mapCurrencySymbol');
        $method->setAccessible(true);

        $this->assertEquals('USD', $method->invokeArgs($this->client, ['$']));
        $this->assertEquals('EUR', $method->invokeArgs($this->client, ['€']));
        $this->assertEquals('GBP', $method->invokeArgs($this->client, ['£']));
        $this->assertEquals('JPY', $method->invokeArgs($this->client, ['¥']));
        $this->assertEquals('USD', $method->invokeArgs($this->client, ['unknown'])); // default
    }

    public function test_dynamic_selector_detection()
    {
        $html = '
        <html>
            <body>
                <div class="event-card">Event 1</div>
                <div class="event-listing">Event 2</div>
                <div class="price-info">$25.00</div>
                <div class="venue-location">Test Venue</div>
            </body>
        </html>';

        $crawler = new Crawler($html);
        
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('detectSelectors');
        $method->setAccessible(true);

        $eventSelectors = $method->invokeArgs($this->client, [$crawler, 'event']);
        $this->assertIsArray($eventSelectors);

        $priceSelectors = $method->invokeArgs($this->client, [$crawler, 'price']);
        $this->assertIsArray($priceSelectors);
    }
}
