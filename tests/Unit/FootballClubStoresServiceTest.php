<?php

namespace Tests\Unit;

use App\Services\Platforms\FootballClubStoresService;
use App\Models\ScrapedTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FootballClubStoresServiceTest extends TestCase
{
    use RefreshDatabase;

    private FootballClubStoresService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FootballClubStoresService();
    }

    public function test_can_get_supported_clubs(): void
    {
        $supportedClubs = $this->service->getSupportedClubs();
        
        $this->assertIsArray($supportedClubs);
        $this->assertNotEmpty($supportedClubs);
        
        // Check that Arsenal is supported
        $this->assertArrayHasKey('arsenal', $supportedClubs);
        $this->assertEquals('Arsenal FC', $supportedClubs['arsenal']['name']);
        $this->assertEquals('Premier League', $supportedClubs['arsenal']['league']);
        $this->assertEquals('England', $supportedClubs['arsenal']['country']);
        
        // Check that Real Madrid is supported
        $this->assertArrayHasKey('real_madrid', $supportedClubs);
        $this->assertEquals('Real Madrid', $supportedClubs['real_madrid']['name']);
        $this->assertEquals('La Liga', $supportedClubs['real_madrid']['league']);
        $this->assertEquals('Spain', $supportedClubs['real_madrid']['country']);
    }

    public function test_can_get_statistics(): void
    {
        // Create some test tickets
        ScrapedTicket::factory()->create([
            'platform' => 'football_clubs',
            'availability_status' => 'available',
            'metadata' => json_encode([
                'club' => 'Arsenal FC',
                'league' => 'Premier League'
            ])
        ]);
        
        ScrapedTicket::factory()->create([
            'platform' => 'football_clubs',
            'availability_status' => 'sold_out',
            'metadata' => json_encode([
                'club' => 'Chelsea FC',
                'league' => 'Premier League'
            ])
        ]);
        
        $stats = $this->service->getStatistics();
        
        $this->assertIsArray($stats);
        $this->assertEquals('football_clubs', $stats['platform']);
        $this->assertEquals(2, $stats['total_tickets']);
        $this->assertEquals(1, $stats['available_tickets']);
        $this->assertEquals(50.0, $stats['availability_rate']);
    }

    public function test_search_tickets_with_unknown_club_returns_error(): void
    {
        $result = $this->service->searchTickets(['unknown_club']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertContains('Unknown club: unknown_club', $result['errors']);
    }

    public function test_search_tickets_with_valid_clubs(): void
    {
        // Mock HTTP responses for club websites
        Http::fake([
            'https://www.arsenal.com/api/tickets*' => Http::response([
                'fixtures' => [
                    [
                        'id' => 'arsenal_001',
                        'title' => 'Arsenal vs Chelsea',
                        'date' => '2024-03-15 15:00:00',
                        'venue' => 'Emirates Stadium',
                        'competition' => 'Premier League',
                        'tickets' => [
                            [
                                'category' => 'Lower Tier',
                                'price' => 65.00,
                                'available' => true
                            ]
                        ]
                    ]
                ]
            ], 200),
            'https://www.arsenal.com/tickets*' => Http::response('<html><body>Arsenal Tickets</body></html>', 200)
        ]);

        Cache::flush(); // Clear cache for testing
        
        $result = $this->service->searchTickets(['arsenal']);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['clubs_searched']);
        $this->assertEquals(1, $result['successful_searches']);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('arsenal', $result['results']);
    }

    public function test_import_tickets_with_mocked_search_results(): void
    {
        // Mock successful search results
        Http::fake([
            'https://www.chelsea.com/api/tickets*' => Http::response([
                'matches' => [
                    [
                        'matchId' => 'chelsea_001',
                        'opponent' => 'Liverpool',
                        'kickOff' => '2024-03-20 17:30:00',
                        'venue' => 'Stamford Bridge',
                        'competition' => 'Premier League',
                        'ticketing' => [
                            [
                                'category' => 'Matthew Harding Lower',
                                'price' => 55.00,
                                'available' => true
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        Cache::flush();
        
        $result = $this->service->importTickets(['chelsea']);
        
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['imported_count']);
        
        // Verify ticket was created in database
        $this->assertDatabaseHas('scraped_tickets', [
            'platform' => 'football_clubs',
            'event_title' => 'Chelsea FC vs Liverpool'
        ]);
    }

    public function test_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'https://www.liverpool.com/api/fixtures-and-tickets*' => Http::response([], 500),
            'https://www.liverpoolfc.com/tickets*' => Http::response('<html><body>Server Error</body></html>', 500)
        ]);

        Cache::flush();
        
        $result = $this->service->searchTickets(['liverpool']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function test_currency_mapping_for_different_countries(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getCurrencyForCountry');
        $method->setAccessible(true);
        
        $this->assertEquals('GBP', $method->invoke($this->service, 'England'));
        $this->assertEquals('EUR', $method->invoke($this->service, 'Spain'));
        $this->assertEquals('EUR', $method->invoke($this->service, 'Italy'));
        $this->assertEquals('EUR', $method->invoke($this->service, 'Germany'));
        $this->assertEquals('EUR', $method->invoke($this->service, 'France'));
    }

    public function test_language_mapping_for_different_countries(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getLanguageForCountry');
        $method->setAccessible(true);
        
        $this->assertEquals('en-GB,en;q=0.9', $method->invoke($this->service, 'England'));
        $this->assertEquals('es-ES,es;q=0.9,en;q=0.8', $method->invoke($this->service, 'Spain'));
        $this->assertEquals('it-IT,it;q=0.9,en;q=0.8', $method->invoke($this->service, 'Italy'));
        $this->assertEquals('de-DE,de;q=0.9,en;q=0.8', $method->invoke($this->service, 'Germany'));
        $this->assertEquals('fr-FR,fr;q=0.9,en;q=0.8', $method->invoke($this->service, 'France'));
    }

    public function test_extract_opponent_from_title(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractOpponent');
        $method->setAccessible(true);
        
        $this->assertEquals('Chelsea', $method->invoke($this->service, 'Arsenal vs Chelsea', 'Arsenal'));
        $this->assertEquals('Liverpool', $method->invoke($this->service, 'Real Madrid v Liverpool', 'Real Madrid'));
        $this->assertEquals('Barcelona', $method->invoke($this->service, 'PSG - Barcelona', 'PSG'));
    }

    public function test_parse_ticket_categories(): void
    {
        $mockTickets = [
            [
                'category' => 'VIP Box',
                'price' => 150.00,
                'available' => true
            ],
            [
                'category' => 'General Admission',
                'price' => 25.00,
                'available' => false,
                'status' => 'sold_out'
            ]
        ];
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseTicketCategories');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->service, $mockTickets);
        
        $this->assertCount(2, $result);
        $this->assertEquals('VIP Box', $result[0]['category']);
        $this->assertEquals(150.00, $result[0]['price']);
        $this->assertTrue($result[0]['available']);
        $this->assertEquals('premium', $result[0]['seat_type']);
        
        $this->assertEquals('General Admission', $result[1]['category']);
        $this->assertEquals(25.00, $result[1]['price']);
        $this->assertFalse($result[1]['available']);
        $this->assertEquals('standard', $result[1]['seat_type']);
    }

    public function test_leagues_and_countries_coverage(): void
    {
        $supportedClubs = $this->service->getSupportedClubs();
        
        $leagues = array_unique(array_column($supportedClubs, 'league'));
        $countries = array_unique(array_column($supportedClubs, 'country'));
        
        // Verify major European leagues are covered
        $this->assertContains('Premier League', $leagues);
        $this->assertContains('La Liga', $leagues);
        $this->assertContains('Serie A', $leagues);
        $this->assertContains('Bundesliga', $leagues);
        $this->assertContains('Ligue 1', $leagues);
        
        // Verify major European countries are covered
        $this->assertContains('England', $countries);
        $this->assertContains('Spain', $countries);
        $this->assertContains('Italy', $countries);
        $this->assertContains('Germany', $countries);
        $this->assertContains('France', $countries);
    }
}
