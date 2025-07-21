<?php

namespace Tests\Integration\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FunZoneControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $agent;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'customer']);
        $this->agent = User::factory()->create(['role' => 'agent']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_search_requires_authentication()
    {
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert',
            'location' => 'Bratislava'
        ]);

        $response->assertStatus(401);
    }

    public function test_search_with_valid_parameters()
    {
        Sanctum::actingAs($this->user);

        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert',
            'location' => 'Bratislava',
            'limit' => 10
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta' => [
                         'keyword',
                         'location',
                         'total_results',
                         'limit'
                     ]
                 ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_search_validation_errors()
    {
        Sanctum::actingAs($this->user);

        // Test missing keyword
        $response = $this->postJson('/api/v1/funzone/search', [
            'location' => 'Bratislava'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['keyword']);

        // Test keyword too short
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'a'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['keyword']);

        // Test limit too high
        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert',
            'limit' => 200
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['limit']);
    }

    public function test_get_event_details_requires_authentication()
    {
        $response = $this->postJson('/api/v1/funzone/event-details', [
            'url' => 'https://www.funzone.com/event/test'
        ]);

        $response->assertStatus(401);
    }

    public function test_get_event_details_with_valid_url()
    {
        Sanctum::actingAs($this->user);

        $mockHtml = $this->getMockEventDetailsHtml();
        
        Http::fake([
            'funzone.com/*' => Http::response($mockHtml, 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/event-details', [
            'url' => 'https://www.funzone.com/event/test-concert-123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_get_event_details_url_validation()
    {
        Sanctum::actingAs($this->user);

        // Test invalid URL
        $response = $this->postJson('/api/v1/funzone/event-details', [
            'url' => 'not-a-url'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['url']);

        // Test non-FunZone URL
        $response = $this->postJson('/api/v1/funzone/event-details', [
            'url' => 'https://www.google.com'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['url']);
    }

    public function test_import_requires_agent_or_admin_role()
    {
        // Test as regular user
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/funzone/import', [
            'keyword' => 'concert'
        ]);

        $response->assertStatus(403);
    }

    public function test_import_as_agent()
    {
        Sanctum::actingAs($this->agent);

        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/import', [
            'keyword' => 'concert',
            'location' => 'Bratislava',
            'limit' => 5
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'total_found',
                     'imported',
                     'errors',
                     'message'
                 ]);
    }

    public function test_import_as_admin()
    {
        Sanctum::actingAs($this->admin);

        $mockHtml = $this->getMockSearchResultsHtml();
        
        Http::fake([
            'funzone.sk/*' => Http::response($mockHtml, 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/import', [
            'keyword' => 'concert',
            'location' => 'Bratislava',
            'limit' => 5
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_import_urls_requires_agent_or_admin()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/funzone/import-urls', [
            'urls' => ['https://www.funzone.com/event/test']
        ]);

        $response->assertStatus(403);
    }

    public function test_import_urls_validation()
    {
        Sanctum::actingAs($this->agent);

        // Test empty URLs array
        $response = $this->postJson('/api/v1/funzone/import-urls', [
            'urls' => []
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['urls']);

        // Test too many URLs
        $urls = array_fill(0, 15, 'https://www.funzone.com/event/test');
        $response = $this->postJson('/api/v1/funzone/import-urls', [
            'urls' => $urls
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['urls']);

        // Test invalid URL format
        $response = $this->postJson('/api/v1/funzone/import-urls', [
            'urls' => ['not-a-url', 'https://www.google.com']
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['urls.0', 'urls.1']);
    }

    public function test_import_urls_success()
    {
        Sanctum::actingAs($this->agent);

        $mockHtml = $this->getMockEventDetailsHtml();
        
        Http::fake([
            'funzone.com/*' => Http::response($mockHtml, 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/import-urls', [
            'urls' => [
                'https://www.funzone.com/event/test-concert-123',
                'https://www.funzone.com/event/another-event-456'
            ]
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'total_urls',
                     'imported',
                     'errors',
                     'message'
                 ]);
    }

    public function test_stats_endpoint()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/funzone/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'platform',
                         'total_scraped',
                         'last_scrape',
                         'success_rate',
                         'avg_response_time'
                     ]
                 ]);

        $this->assertEquals('funzone', $response->json('data.platform'));
    }

    public function test_rate_limiting()
    {
        Sanctum::actingAs($this->user);

        Http::fake([
            'funzone.sk/*' => Http::response('<html><body>Mock</body></html>', 200)
        ]);

        // Make multiple requests quickly to test rate limiting
        $responses = [];
        for ($i = 0; $i < 35; $i++) {
            $responses[] = $this->postJson('/api/v1/funzone/search', [
                'keyword' => "test$i"
            ]);
        }

        // Some requests should be rate limited
        $rateLimitedCount = collect($responses)->filter(function ($response) {
            return $response->status() === 429;
        })->count();

        $this->assertGreaterThan(0, $rateLimitedCount);
    }

    public function test_http_error_handling()
    {
        Sanctum::actingAs($this->user);

        // Mock HTTP error
        Http::fake([
            'funzone.sk/*' => Http::response('', 500)
        ]);

        $response = $this->postJson('/api/v1/funzone/search', [
            'keyword' => 'concert'
        ]);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false
                 ]);
    }

    public function test_import_with_no_events_found()
    {
        Sanctum::actingAs($this->agent);

        // Mock empty response
        Http::fake([
            'funzone.sk/*' => Http::response('<html><body>No events found</body></html>', 200)
        ]);

        $response = $this->postJson('/api/v1/funzone/import', [
            'keyword' => 'nonexistent-event'
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'imported' => 0
                 ]);
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
}
