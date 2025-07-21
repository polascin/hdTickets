# Testing Strategy for HDTickets Platform

This document outlines the comprehensive testing strategy for the HDTickets platform, covering all aspects of ticket platform integrations, API endpoints, and scraping functionality.

## Table of Contents

1. [Testing Overview](#testing-overview)
2. [Test Types and Coverage](#test-types-and-coverage)
3. [Unit Testing Strategy](#unit-testing-strategy)
4. [Integration Testing](#integration-testing)
5. [Performance Testing](#performance-testing)
6. [Security Testing](#security-testing)
7. [Scraping-Specific Testing](#scraping-specific-testing)
8. [Test Data Management](#test-data-management)
9. [Continuous Integration](#continuous-integration)
10. [Monitoring and Alerting](#monitoring-and-alerting)

## Testing Overview

### Testing Philosophy
- **Test-Driven Development (TDD)**: Write tests before implementation where possible
- **Comprehensive Coverage**: Aim for >90% code coverage on business logic
- **Fast Feedback**: Tests should run quickly to enable rapid development
- **Reliable**: Tests should be deterministic and not flaky
- **Maintainable**: Tests should be easy to understand and update

### Testing Pyramid
```
        /\
       /  \      E2E Tests (5%)
      /____\     - Full workflow testing
     /      \    - Browser automation
    /        \   
   /__________\  Integration Tests (25%)
  /            \ - API testing
 /              \- Database integration
/________________\ Unit Tests (70%)
                   - Service classes
                   - Controllers
                   - Models
```

## Test Types and Coverage

### 1. Unit Tests (70%)
- **Target Coverage**: >95%
- **Focus Areas**: 
  - Service classes (TicketApis)
  - Business logic
  - Data transformations
  - Utility functions

### 2. Integration Tests (25%)
- **Target Coverage**: >80%
- **Focus Areas**:
  - API endpoints
  - Database interactions
  - External service integrations
  - Authentication/Authorization

### 3. End-to-End Tests (5%)
- **Target Coverage**: Critical user journeys
- **Focus Areas**:
  - Complete search workflows
  - Import processes
  - Admin dashboard functionality

## Unit Testing Strategy

### Service Class Testing

Each ticket platform client has comprehensive unit tests:

#### FunZone Client Testing
```php
// tests/Unit/Services/TicketApis/FunZoneClientTest.php
class FunZoneClientTest extends TestCase
{
    public function test_slovak_date_parsing()
    {
        // Test Slovak date format parsing
        $client = new FunZoneClient($config);
        $date = $client->parseEventDate('15.12.2024 19:00');
        
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertEquals('2024-12-15', $date->format('Y-m-d'));
    }
    
    public function test_price_range_extraction()
    {
        // Test EUR price extraction with comma decimal separator
        $prices = ['€25,50', '€30,00', '€45,99'];
        $result = $this->extractPriceRange($prices);
        
        $this->assertEquals(25.50, $result['min']);
        $this->assertEquals(45.99, $result['max']);
    }
}
```

#### StubHub Client Testing
```php
// tests/Unit/Services/TicketApis/StubHubClientTest.php
class StubHubClientTest extends TestCase
{
    public function test_api_fallback_to_scraping()
    {
        // Test API failure fallback to scraping
        Http::fake([
            'api.stubhub.com/*' => Http::response('', 500),
            'www.stubhub.com/*' => Http::response($mockHtml, 200)
        ]);
        
        $results = $this->client->searchEvents(['q' => 'test']);
        
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }
    
    public function test_cloudflare_detection()
    {
        // Test Cloudflare bot detection handling
        Http::fake([
            'stubhub.com/*' => Http::response($cloudflareHtml, 403)
        ]);
        
        $results = $this->client->searchEvents(['q' => 'test']);
        
        $this->assertEmpty($results); // Should handle gracefully
    }
}
```

### Mock Data Strategy

#### HTML Response Mocking
```php
// tests/Fixtures/MockHtmlResponses.php
class MockHtmlResponses
{
    public static function getFunZoneSearchResults(): string
    {
        return file_get_contents(__DIR__ . '/html/funzone_search.html');
    }
    
    public static function getStubHubEventDetails(): string
    {
        return file_get_contents(__DIR__ . '/html/stubhub_event.html');
    }
}
```

#### Test Database Seeding
```php
// tests/Seeds/PlatformTestSeeder.php
class PlatformTestSeeder extends Seeder
{
    public function run()
    {
        // Create test events for each platform
        $platforms = ['funzone', 'stubhub', 'viagogo', 'tickpick'];
        
        foreach ($platforms as $platform) {
            Ticket::factory()->count(10)->create([
                'platform' => $platform,
                'status' => 'active'
            ]);
        }
    }
}
```

## Integration Testing

### API Endpoint Testing

#### Authentication Testing
```php
// tests/Integration/Api/AuthenticationTest.php
class AuthenticationTest extends TestCase
{
    public function test_unauthenticated_requests_rejected()
    {
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert'
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_role_based_access_control()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        
        Sanctum::actingAs($customer);
        
        $response = $this->postJson('/api/v1/funzone/import', [
            'keyword' => 'concert'
        ]);
        
        $response->assertStatus(403); // Customer cannot import
    }
}
```

#### Platform-Specific API Testing
```php
// tests/Integration/Api/FunZoneControllerTest.php
class FunZoneControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_search_with_valid_parameters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        Http::fake(['funzone.sk/*' => Http::response($mockHtml, 200)]);
        
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert',
            'location' => 'Bratislava',
            'limit' => 10
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta'
                 ]);
    }
    
    public function test_rate_limiting()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        // Make 31 requests (limit is 30)
        for ($i = 0; $i < 31; $i++) {
            $responses[] = $this->postJson('/api/v1/funzone/search', [
                'keyword' => "test{$i}"
            ]);
        }
        
        $this->assertEquals(429, end($responses)->status());
    }
}
```

### Database Integration Testing
```php
// tests/Integration/Database/TicketModelTest.php
class TicketModelTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_platform_scoping()
    {
        Ticket::factory()->create(['platform' => 'funzone']);
        Ticket::factory()->create(['platform' => 'stubhub']);
        
        $funzoneTickets = Ticket::where('platform', 'funzone')->get();
        
        $this->assertCount(1, $funzoneTickets);
        $this->assertEquals('funzone', $funzoneTickets->first()->platform);
    }
    
    public function test_full_text_search()
    {
        Ticket::factory()->create([
            'title' => 'Rock Concert in Bratislava',
            'description' => 'Amazing rock concert with top artists'
        ]);
        
        $results = Ticket::whereRaw('MATCH(title, description) AGAINST(?)', ['rock'])
                         ->get();
        
        $this->assertCount(1, $results);
    }
}
```

## Performance Testing

### Load Testing
```php
// tests/Performance/LoadTest.php
class LoadTest extends TestCase
{
    public function test_concurrent_search_requests()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $startTime = microtime(true);
        
        // Simulate concurrent requests
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            $promises[] = $this->postJsonAsync('/api/v1/funzone/search', [
                'keyword' => "test{$i}"
            ]);
        }
        
        $responses = Promise::all($promises)->wait();
        $endTime = microtime(true);
        
        $this->assertLessThan(5.0, $endTime - $startTime); // Should complete in < 5s
        $this->assertCount(10, $responses);
    }
    
    public function test_cache_performance()
    {
        // First request (cache miss)
        $start1 = microtime(true);
        $response1 = $this->searchEndpoint();
        $time1 = microtime(true) - $start1;
        
        // Second request (cache hit)
        $start2 = microtime(true);
        $response2 = $this->searchEndpoint();
        $time2 = microtime(true) - $start2;
        
        // Cache hit should be significantly faster
        $this->assertLessThan($time1 * 0.1, $time2);
    }
}
```

### Memory Usage Testing
```php
// tests/Performance/MemoryTest.php
class MemoryTest extends TestCase
{
    public function test_memory_usage_during_large_scraping()
    {
        $initialMemory = memory_get_usage(true);
        
        // Simulate scraping large result set
        $client = new FunZoneClient($config);
        $results = $client->scrapeSearchResults('concert', '', 100);
        
        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Should not increase memory by more than 50MB
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease);
    }
}
```

## Security Testing

### Input Validation Testing
```php
// tests/Security/InputValidationTest.php
class InputValidationTest extends TestCase
{
    public function test_sql_injection_prevention()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $maliciousInput = "'; DROP TABLE tickets; --";
        
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => $maliciousInput
        ]);
        
        // Should not cause database error
        $response->assertStatus(200);
        
        // Verify table still exists
        $this->assertTrue(Schema::hasTable('tickets'));
    }
    
    public function test_xss_prevention()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $xssPayload = '<script>alert("xss")</script>';
        
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => $xssPayload
        ]);
        
        $content = $response->getContent();
        
        // Should not contain unescaped script tags
        $this->assertStringNotContainsString('<script>', $content);
    }
}
```

### Authentication Security Testing
```php
// tests/Security/AuthenticationSecurityTest.php
class AuthenticationSecurityTest extends TestCase
{
    public function test_token_expiration()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', [], now()->addMinutes(-60));
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ])->postJson('/api/v1/funzone/search', ['keyword' => 'test']);
        
        $response->assertStatus(401);
    }
    
    public function test_rate_limiting_bypass_attempts()
    {
        $user = User::factory()->create();
        
        // Try to bypass rate limiting with different tokens
        for ($i = 0; $i < 50; $i++) {
            $token = $user->createToken("token-{$i}");
            
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken
            ])->postJson('/api/v1/funzone/search', ['keyword' => 'test']);
            
            if ($response->status() === 429) {
                break;
            }
        }
        
        // Should still be rate limited
        $this->assertEquals(429, $response->status());
    }
}
```

## Scraping-Specific Testing

### Selector Effectiveness Testing
```php
// tests/Scraping/SelectorTest.php
class SelectorTest extends TestCase
{
    public function test_funzone_selectors()
    {
        $html = MockHtmlResponses::getFunZoneSearchResults();
        $crawler = new Crawler($html);
        
        // Test primary selectors
        $eventCards = $crawler->filter('.event-card');
        $this->assertGreaterThan(0, $eventCards->count());
        
        // Test fallback selectors
        if ($eventCards->count() === 0) {
            $fallbackCards = $crawler->filter('.event-item, .listing');
            $this->assertGreaterThan(0, $fallbackCards->count());
        }
    }
    
    public function test_selector_effectiveness_tracking()
    {
        $client = new FunZoneClient($config);
        
        // Track successful selector usage
        $client->trackSelectorEffectiveness('.event-card', true, 'funzone');
        
        // Verify tracking
        $stats = Cache::get('selector_stats_funzone_.event-card');
        
        $this->assertEquals(1, $stats['successful']);
        $this->assertEquals(0, $stats['failed']);
    }
}
```

### Anti-Bot Detection Testing
```php
// tests/Scraping/AntiBot/DetectionTest.php
class DetectionTest extends TestCase
{
    public function test_cloudflare_detection()
    {
        $client = new StubHubClient($config);
        
        Http::fake([
            'stubhub.com/*' => Http::response(MockHtmlResponses::getBotDetectionResponse(), 403)
        ]);
        
        $this->expectException(ScrapingDetectedException::class);
        
        $client->scrapeSearchResults('test', '', 10);
    }
    
    public function test_rate_limit_handling()
    {
        $client = new StubHubClient($config);
        
        Http::fake([
            'stubhub.com/*' => Http::response(MockHtmlResponses::getRateLimitResponse(), 429, [
                'Retry-After' => '300'
            ])
        ]);
        
        $this->expectException(RateLimitException::class);
        
        $client->scrapeSearchResults('test', '', 10);
    }
}
```

## Test Data Management

### Test Data Factories
```php
// database/factories/TicketFactory.php
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'platform' => $this->faker->randomElement(['funzone', 'stubhub', 'viagogo']),
            'external_id' => $this->faker->uuid(),
            'event_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'location' => $this->faker->city(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'active',
        ];
    }
    
    public function funzone(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'funzone',
            'location' => $this->faker->randomElement(['Bratislava', 'Košice', 'Žilina']),
            'scraped_data' => json_encode([
                'venue' => 'Test Arena',
                'currency' => 'EUR',
                'slovak_specific' => [
                    'region' => 'Bratislavský kraj'
                ]
            ])
        ]);
    }
}
```

### Test Environment Setup
```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test environment
        config(['cache.default' => 'array']);
        config(['queue.default' => 'sync']);
        
        // Mock external HTTP calls by default
        Http::fake();
        
        // Clear all caches
        Cache::flush();
    }
    
    protected function createTestUser(string $role = 'customer'): User
    {
        return User::factory()->create(['role' => $role]);
    }
    
    protected function mockSuccessfulScrapingResponse(string $platform): void
    {
        $html = match($platform) {
            'funzone' => MockHtmlResponses::getFunZoneSearchResults(),
            'stubhub' => MockHtmlResponses::getStubHubSearchResults(),
            default => '<html><body>Mock response</body></html>'
        };
        
        Http::fake([
            "{$platform}.*" => Http::response($html, 200)
        ]);
    }
}
```

## Continuous Integration

### GitHub Actions Workflow
```yaml
# .github/workflows/tests.yml
name: Run Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: hdtickets_test
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, mysql, pdo_mysql
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run tests
      run: php artisan test --coverage-clover=coverage.xml
      
    - name: Upload coverage
      uses: codecov/codecov-action@v1
      with:
        file: ./coverage.xml
```

### Pre-commit Hooks
```bash
#!/bin/sh
# .git/hooks/pre-commit

# Run tests before commit
php artisan test --stop-on-failure

if [ $? -ne 0 ]; then
    echo "Tests failed. Commit aborted."
    exit 1
fi

# Run code style checks
vendor/bin/pint --test

if [ $? -ne 0 ]; then
    echo "Code style check failed. Run 'vendor/bin/pint' to fix."
    exit 1
fi

echo "All checks passed. Proceeding with commit."
```

## Monitoring and Alerting

### Test Metrics Collection
```php
// tests/Metrics/TestMetricsCollector.php
class TestMetricsCollector
{
    public function recordTestRun(string $suite, float $duration, int $passed, int $failed): void
    {
        $metrics = [
            'suite' => $suite,
            'duration' => $duration,
            'passed' => $passed,
            'failed' => $failed,
            'success_rate' => $passed / ($passed + $failed) * 100,
            'timestamp' => now()
        ];
        
        // Store metrics for monitoring
        Cache::put("test_metrics:{$suite}:" . now()->timestamp, $metrics, 3600);
    }
    
    public function getTestTrends(string $suite, int $days = 7): array
    {
        // Retrieve and analyze test trends
        // Implementation would fetch historical data and calculate trends
    }
}
```

### Automated Test Reporting
```php
// app/Console/Commands/GenerateTestReport.php
class GenerateTestReport extends Command
{
    protected $signature = 'test:report {--days=7}';
    
    public function handle(): void
    {
        $days = $this->option('days');
        
        // Generate comprehensive test report
        $report = [
            'coverage' => $this->getCoverageStats(),
            'performance' => $this->getPerformanceMetrics(),
            'flaky_tests' => $this->getFlakyTests(),
            'platform_health' => $this->getPlatformTestHealth()
        ];
        
        $this->info('Test Report Generated');
        $this->table(['Metric', 'Value'], [
            ['Overall Coverage', $report['coverage']['overall'] . '%'],
            ['Unit Test Coverage', $report['coverage']['unit'] . '%'],
            ['Integration Coverage', $report['coverage']['integration'] . '%'],
            ['Avg Test Duration', $report['performance']['avg_duration'] . 's'],
            ['Flaky Tests', count($report['flaky_tests'])],
        ]);
    }
}
```

This comprehensive testing strategy ensures robust, reliable, and maintainable code for the HDTickets platform. The multi-layered approach covers all aspects from unit tests to end-to-end scenarios, with special attention to the unique challenges of web scraping and multi-platform integration.
