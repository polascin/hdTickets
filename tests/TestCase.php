<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use App\Models\ScrapedTicket;
use App\Models\Category;
use App\Models\TicketAlert;
use Database\Seeders\CategorySeeder;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;
    
    protected bool $seed = false;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all caches before each test (only if not mocked)
        try {
            if (!app()->environment('testing') || !Cache::getStore() instanceof \Mockery\MockInterface) {
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Ignore cache flush errors in testing
        }
        
        // Reset HTTP fake
        Http::preventStrayRequests();
        
        // Fake queue for testing
        Queue::fake();
        
        // Seed basic data if needed
        if ($this->seed) {
            $this->seed(CategorySeeder::class);
        }
    }
    
    protected function tearDown(): void
    {
        // Clear caches after each test (safely)
        try {
            if (!app()->environment('testing') || !Cache::getStore() instanceof \Mockery\MockInterface) {
                Cache::flush();
            }
        } catch (\Exception $e) {
            // Ignore cache flush errors in testing
        }
        
        parent::tearDown();
    }
    
    /**
     * Create a test user with specific role
     */
    protected function createUser(string $role = 'customer', array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'is_scraper_account' => $role === 'scraper',
        ], $attributes));
    }
    
    /**
     * Create a test scraped ticket
     */
    protected function createScrapedTicket(array $attributes = []): ScrapedTicket
    {
        return ScrapedTicket::factory()->create($attributes);
    }
    
    /**
     * Create a test ticket alert
     */
    protected function createTicketAlert(User $user = null, array $attributes = []): TicketAlert
    {
        $user = $user ?? $this->createUser();
        
        return TicketAlert::factory()->create(array_merge([
            'user_id' => $user->id,
        ], $attributes));
    }
    
    /**
     * Mock HTTP responses for ticket platforms
     */
    protected function mockTicketPlatformResponses(): void
    {
        Http::fake([
            'stubhub.com/*' => Http::response($this->getStubHubMockResponse(), 200),
            'ticketmaster.com/*' => Http::response($this->getTicketmasterMockResponse(), 200),
            'viagogo.com/*' => Http::response($this->getViagogoMockResponse(), 200),
            'funzone.sk/*' => Http::response($this->getFunZoneMockResponse(), 200),
        ]);
    }
    
    /**
     * Mock HTTP rate limit responses
     */
    protected function mockRateLimitResponses(): void
    {
        Http::fake([
            '*' => Http::response('Rate limit exceeded', 429, [
                'Retry-After' => '60',
                'X-RateLimit-Reset' => time() + 60
            ])
        ]);
    }
    
    /**
     * Mock HTTP timeout responses
     */
    protected function mockTimeoutResponses(): void
    {
        Http::fake(function ($request) {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
        });
    }
    
    /**
     * Get mock StubHub API response
     */
    protected function getStubHubMockResponse(): array
    {
        return [
            'events' => [
                [
                    'id' => 'stubhub-123',
                    'name' => 'Manchester United vs Liverpool',
                    'venue' => ['name' => 'Old Trafford', 'city' => 'Manchester'],
                    'eventDateLocal' => '2024-03-15T15:00:00',
                    'ticketInfo' => [
                        'minListPrice' => 150.00,
                        'maxListPrice' => 800.00,
                        'currencyCode' => 'GBP',
                        'totalTickets' => 45
                    ],
                    'webURI' => 'https://stubhub.com/manchester-united-tickets/123'
                ]
            ]
        ];
    }
    
    /**
     * Get mock Ticketmaster API response
     */
    protected function getTicketmasterMockResponse(): array
    {
        return [
            '_embedded' => [
                'events' => [
                    [
                        'id' => 'tm-456',
                        'name' => 'Manchester United vs Arsenal',
                        '_embedded' => [
                            'venues' => [
                                [
                                    'name' => 'Old Trafford',
                                    'city' => ['name' => 'Manchester'],
                                    'country' => ['name' => 'UK']
                                ]
                            ]
                        ],
                        'dates' => [
                            'start' => ['dateTime' => '2024-04-20T17:30:00Z']
                        ],
                        'priceRanges' => [
                            ['min' => 120.00, 'max' => 600.00, 'currency' => 'GBP']
                        ],
                        'promoter' => ['name' => 'Official'],
                        'url' => 'https://ticketmaster.com/manchester-united-tickets/456'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get mock Viagogo API response
     */
    protected function getViagogoMockResponse(): array
    {
        return [
            'listings' => [
                [
                    'id' => 'vg-789',
                    'event' => [
                        'name' => 'Manchester United vs Chelsea',
                        'venue' => 'Old Trafford',
                        'start_date' => '2024-05-10T15:00:00Z'
                    ],
                    'price' => ['amount' => 200.00, 'currency_code' => 'GBP'],
                    'quantity' => 2,
                    'url' => 'https://viagogo.com/manchester-united-tickets/789'
                ]
            ]
        ];
    }
    
    /**
     * Get mock FunZone HTML response
     */
    protected function getFunZoneMockResponse(): string
    {
        return '
        <html>
            <body>
                <div class="event-card">
                    <h3>Slovan Bratislava vs Sparta Praha</h3>
                    <a href="/event/slovan-sparta-123">Match Details</a>
                    <span class="date">25.03.2024 18:00</span>
                    <span class="venue">Tehelné pole</span>
                    <span class="location">Bratislava</span>
                    <span class="price">€25</span>
                </div>
            </body>
        </html>';
    }
    
    /**
     * Assert that a scraping operation completed successfully
     */
    protected function assertScrapingSuccessful(array $result): void
    {
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_found', $result);
        $this->assertArrayHasKey('saved', $result);
        $this->assertGreaterThanOrEqual(0, $result['total_found']);
        $this->assertGreaterThanOrEqual(0, $result['saved']);
    }
    
    /**
     * Assert that a ticket has required fields
     */
    protected function assertValidTicketStructure(array $ticket): void
    {
        $requiredFields = ['platform', 'title', 'venue', 'location', 'min_price', 'currency'];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $ticket, "Ticket missing required field: {$field}");
        }
    }
    
    /**
     * Generate performance test data
     */
    protected function generateTestTickets(int $count = 100): void
    {
        ScrapedTicket::factory()->count($count)->create();
    }
    
    /**
     * Simulate concurrent user load
     */
    protected function simulateConcurrentLoad(int $userCount = 10): array
    {
        $users = User::factory()->count($userCount)->create();
        $results = [];
        
        foreach ($users as $user) {
            $startTime = microtime(true);
            
            // Simulate user actions
            $response = $this->actingAs($user)->get('/dashboard');
            
            $results[] = [
                'user_id' => $user->id,
                'response_time' => (microtime(true) - $startTime) * 1000,
                'status_code' => $response->status()
            ];
        }
        
        return $results;
    }
}
