<?php declare(strict_types=1);

namespace Tests\Integration\Api;

use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Override;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    /**
     * @test
     */
    public function it_can_list_tickets_without_authentication(): void
    {
        // Create some test tickets
        $this->createTestTicket(['status' => 'available', 'sport_type' => 'football']);
        $this->createTestTicket(['status' => 'available', 'sport_type' => 'basketball']);
        $this->createTestTicket(['status' => 'sold_out', 'sport_type' => 'football']);

        $response = $this->getJson('/api/tickets');

        $this->assertApiResponse($response, 200, [
            'data' => '*',
            'meta' => [
                'current_page',
                'total',
                'per_page',
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    /**
     * @test
     */
    public function it_can_filter_tickets_by_sport_type(): void
    {
        $this->createTestTicket(['sport_type' => 'football']);
        $this->createTestTicket(['sport_type' => 'basketball']);
        $this->createTestTicket(['sport_type' => 'football']);

        $response = $this->getJson('/api/tickets?sport_type=football');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $ticket) {
            $this->assertEquals('football', $ticket['sport_type']);
        }
    }

    /**
     * @test
     */
    public function it_can_filter_tickets_by_price_range(): void
    {
        $this->createTestTicket(['price_min' => 50, 'price_max' => 100]);
        $this->createTestTicket(['price_min' => 200, 'price_max' => 300]);
        $this->createTestTicket(['price_min' => 75, 'price_max' => 150]);

        $response = $this->getJson('/api/tickets?min_price=60&max_price=120');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /**
     * @test
     */
    public function it_can_filter_tickets_by_availability(): void
    {
        $this->createTestTicket(['status' => 'available']);
        $this->createTestTicket(['status' => 'sold_out']);
        $this->createTestTicket(['status' => 'limited']);

        $response = $this->getJson('/api/tickets?status=available');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // available and limited
    }

    /**
     * @test
     */
    public function it_can_search_tickets_by_team(): void
    {
        $this->createTestTicket([
            'team_home' => 'Manchester United',
            'team_away' => 'Liverpool',
        ]);
        $this->createTestTicket([
            'team_home' => 'Chelsea',
            'team_away' => 'Arsenal',
        ]);

        $response = $this->getJson('/api/tickets?search=Manchester United');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Manchester United', $data[0]['team_home']);
    }

    /**
     * @test
     */
    public function it_can_sort_tickets_by_price(): void
    {
        $this->createTestTicket(['price_min' => 100]);
        $this->createTestTicket(['price_min' => 50]);
        $this->createTestTicket(['price_min' => 150]);

        $response = $this->getJson('/api/tickets?sort=price_asc');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertEquals(50, $data[0]['price_min']);
        $this->assertEquals(150, $data[2]['price_min']);
    }

    /**
     * @test
     */
    public function it_can_sort_tickets_by_event_date(): void
    {
        $this->createTestTicket(['event_date' => now()->addDays(10)]);
        $this->createTestTicket(['event_date' => now()->addDays(5)]);
        $this->createTestTicket(['event_date' => now()->addDays(15)]);

        $response = $this->getJson('/api/tickets?sort=date_asc');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $firstEventDate = Carbon::parse($data[0]['event_date']);
        $lastEventDate = Carbon::parse($data[2]['event_date']);

        $this->assertTrue($firstEventDate->lessThan($lastEventDate));
    }

    /**
     * @test
     */
    public function it_can_get_single_ticket_details(): void
    {
        $ticket = $this->createTestTicket([
            'title'      => 'Manchester United vs Liverpool',
            'sport_type' => 'football',
            'price_min'  => 75.00,
            'price_max'  => 200.00,
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $this->assertApiResponse($response, 200, [
            'id',
            'title',
            'event_date',
            'venue',
            'sport_type',
            'price_min',
            'price_max',
            'status',
        ]);

        $data = $response->json();
        $this->assertEquals('Manchester United vs Liverpool', $data['title']);
        $this->assertEquals('football', $data['sport_type']);
    }

    /**
     * @test
     */
    public function it_returns_404_for_nonexistent_ticket(): void
    {
        $response = $this->getJson('/api/tickets/99999');

        $this->assertApiResponse($response, 404);
        $this->assertEquals('Ticket not found', $response->json('message'));
    }

    /**
     * @test
     */
    public function it_requires_authentication_to_create_ticket_alerts(): void
    {
        $response = $this->postJson('/api/tickets/alerts', [
            'title'    => 'Football Alerts',
            'criteria' => ['sport_type' => 'football'],
        ]);

        $this->assertApiResponse($response, 401);
    }

    /**
     * @test
     */
    public function authenticated_user_can_create_ticket_alert(): void
    {
        Sanctum::actingAs($this->user);

        $alertData = [
            'title'    => 'Football Alerts',
            'criteria' => [
                'sport_type' => 'football',
                'max_price'  => 100,
                'teams'      => ['Manchester United'],
            ],
            'notification_channels' => ['email', 'push'],
        ];

        $response = $this->postJson('/api/tickets/alerts', $alertData);

        $this->assertApiResponse($response, 201, [
            'id',
            'title',
            'criteria',
            'notification_channels',
            'is_active',
        ]);

        $this->assertDatabaseHas('ticket_alerts', [
            'user_id'   => $this->user->id,
            'title'     => 'Football Alerts',
            'is_active' => TRUE,
        ]);
    }

    /**
     * @test
     */
    public function it_validates_ticket_alert_creation_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/tickets/alerts', [
            'title'                 => '', // Required field
            'criteria'              => 'invalid', // Should be array
            'notification_channels' => ['invalid_channel'],
        ]);

        $this->assertApiResponse($response, 422);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('criteria', $errors);
        $this->assertArrayHasKey('notification_channels', $errors);
    }

    /**
     * @test
     */
    public function authenticated_user_can_list_their_alerts(): void
    {
        Sanctum::actingAs($this->user);

        // Create alerts for this user
        $this->createTicketAlert(['user_id' => $this->user->id, 'title' => 'Football Alerts']);
        $this->createTicketAlert(['user_id' => $this->user->id, 'title' => 'Basketball Alerts']);

        // Create alert for another user
        $otherUser = $this->createTestUser();
        $this->createTicketAlert(['user_id' => $otherUser->id, 'title' => 'Other User Alert']);

        $response = $this->getJson('/api/tickets/alerts');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Only user's alerts

        foreach ($data as $alert) {
            $this->assertEquals($this->user->id, $alert['user_id']);
        }
    }

    /**
     * @test
     */
    public function authenticated_user_can_update_their_alert(): void
    {
        Sanctum::actingAs($this->user);

        $alert = $this->createTicketAlert([
            'user_id' => $this->user->id,
            'title'   => 'Original Title',
        ]);

        $updateData = [
            'title'     => 'Updated Title',
            'is_active' => FALSE,
        ];

        $response = $this->putJson("/api/tickets/alerts/{$alert->id}", $updateData);

        $this->assertApiResponse($response, 200);

        $this->assertDatabaseHas('ticket_alerts', [
            'id'        => $alert->id,
            'title'     => 'Updated Title',
            'is_active' => FALSE,
        ]);
    }

    /**
     * @test
     */
    public function user_cannot_update_other_users_alert(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = $this->createTestUser();
        $alert = $this->createTicketAlert(['user_id' => $otherUser->id]);

        $response = $this->putJson("/api/tickets/alerts/{$alert->id}", [
            'title' => 'Hacked Title',
        ]);

        $this->assertApiResponse($response, 403);
    }

    /**
     * @test
     */
    public function authenticated_user_can_delete_their_alert(): void
    {
        Sanctum::actingAs($this->user);

        $alert = $this->createTicketAlert(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tickets/alerts/{$alert->id}");

        $this->assertApiResponse($response, 204);

        $this->assertSoftDeleted($alert);
    }

    /**
     * @test
     */
    public function it_requires_authentication_for_purchase_attempts(): void
    {
        $ticket = $this->createTestTicket();

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", [
            'quantity'  => 2,
            'max_price' => 150.00,
        ]);

        $this->assertApiResponse($response, 401);
    }

    /**
     * @test
     */
    public function authenticated_user_can_create_purchase_attempt(): void
    {
        Sanctum::actingAs($this->user);

        $ticket = $this->createTestTicket(['status' => 'available']);

        $purchaseData = [
            'quantity'  => 2,
            'max_price' => 150.00,
            'priority'  => 'high',
        ];

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", $purchaseData);

        $this->assertApiResponse($response, 201, [
            'id',
            'user_id',
            'ticket_id',
            'quantity',
            'max_price',
            'status',
        ]);

        $this->assertDatabaseHas('purchase_attempts', [
            'user_id'   => $this->user->id,
            'ticket_id' => $ticket->id,
            'quantity'  => 2,
            'status'    => 'pending',
        ]);
    }

    /**
     * @test
     */
    public function it_validates_purchase_attempt_data(): void
    {
        Sanctum::actingAs($this->user);

        $ticket = $this->createTestTicket();

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", [
            'quantity'  => 0, // Invalid
            'max_price' => -10, // Invalid
            'priority'  => 'invalid_priority',
        ]);

        $this->assertApiResponse($response, 422);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('quantity', $errors);
        $this->assertArrayHasKey('max_price', $errors);
        $this->assertArrayHasKey('priority', $errors);
    }

    /**
     * @test
     */
    public function it_prevents_purchase_attempts_for_sold_out_tickets(): void
    {
        Sanctum::actingAs($this->user);

        $ticket = $this->createTestTicket(['status' => 'sold_out']);

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", [
            'quantity'  => 1,
            'max_price' => 100.00,
        ]);

        $this->assertApiResponse($response, 400);
        $this->assertEquals('Ticket is not available for purchase', $response->json('message'));
    }

    /**
     * @test
     */
    public function premium_user_gets_higher_priority_for_purchases(): void
    {
        $premiumUser = $this->createPremiumUser();
        Sanctum::actingAs($premiumUser);

        $ticket = $this->createTestTicket();

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", [
            'quantity'  => 1,
            'max_price' => 100.00,
        ]);

        $this->assertApiResponse($response, 201);

        $purchaseAttempt = $response->json();
        $this->assertEquals('high', $purchaseAttempt['priority']);
    }

    /**
     * @test
     */
    public function admin_can_access_ticket_management_endpoints(): void
    {
        Sanctum::actingAs($this->admin);

        $source = $this->createTestTicketSource();
        $ticketData = [
            'title'      => 'Admin Created Ticket',
            'event_date' => now()->addDays(30)->toISOString(),
            'venue'      => 'Test Stadium',
            'city'       => 'Test City',
            'sport_type' => 'football',
            'price_min'  => 50.00,
            'price_max'  => 150.00,
            'source_id'  => $source->id,
        ];

        $response = $this->postJson('/api/admin/tickets', $ticketData);

        $this->assertApiResponse($response, 201);

        $this->assertDatabaseHas('tickets', [
            'title'      => 'Admin Created Ticket',
            'sport_type' => 'football',
        ]);
    }

    /**
     * @test
     */
    public function regular_user_cannot_access_admin_endpoints(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/admin/tickets', [
            'title' => 'Unauthorized Ticket',
        ]);

        $this->assertApiResponse($response, 403);
    }

    /**
     * @test
     */
    public function it_can_get_ticket_statistics(): void
    {
        // Create various tickets for statistics
        $this->createTestTicket(['sport_type' => 'football', 'status' => 'available']);
        $this->createTestTicket(['sport_type' => 'football', 'status' => 'sold_out']);
        $this->createTestTicket(['sport_type' => 'basketball', 'status' => 'available']);

        $response = $this->getJson('/api/tickets/statistics');

        $this->assertApiResponse($response, 200, [
            'total_tickets',
            'by_sport',
            'by_status',
            'average_price',
            'upcoming_events',
        ]);

        $stats = $response->json();
        $this->assertEquals(3, $stats['total_tickets']);
        $this->assertEquals(2, $stats['by_sport']['football']);
        $this->assertEquals(1, $stats['by_sport']['basketball']);
    }

    /**
     * @test
     */
    public function it_can_get_trending_tickets(): void
    {
        // Create tickets with different view counts or interest levels
        $this->createTestTicket([
            'title'    => 'Popular Event',
            'metadata' => ['view_count' => 1000],
        ]);

        $this->createTestTicket([
            'title'    => 'Regular Event',
            'metadata' => ['view_count' => 100],
        ]);

        $response = $this->getJson('/api/tickets/trending');

        $this->assertApiResponse($response, 200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // Popular ticket should be first
        $this->assertEquals('Popular Event', $data[0]['title']);
    }

    /**
     * @test
     */
    public function it_implements_rate_limiting_for_api_endpoints(): void
    {
        // Make multiple rapid requests to test rate limiting
        for ($i = 0; $i < 61; $i++) { // Assuming 60 requests per minute limit
            $response = $this->getJson('/api/tickets');

            if ($i < 60) {
                $this->assertLessThan(429, $response->status());
            } else {
                $this->assertEquals(429, $response->status());

                break;
            }
        }
    }

    /**
     * @test
     */
    public function it_returns_proper_pagination_metadata(): void
    {
        // Create more tickets than per-page limit
        for ($i = 0; $i < 25; $i++) {
            $this->createTestTicket(['title' => "Ticket {$i}"]);
        }

        $response = $this->getJson('/api/tickets?per_page=10');

        $this->assertApiResponse($response, 200);

        $meta = $response->json('meta');
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(25, $meta['total']);
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(3, $meta['last_page']);
    }

    /**
     * @test
     */
    public function it_handles_api_versioning(): void
    {
        $response = $this->getJson('/api/v1/tickets');
        $this->assertApiResponse($response, 200);

        // Test version header
        $response = $this->get('/api/tickets', [
            'Accept' => 'application/vnd.hdtickets.v1+json',
        ]);
        $this->assertApiResponse($response, 200);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createTestUser();
        $this->admin = $this->createTestUser(['role' => 'admin']);
    }

    private function createTicketAlert(array $attributes = []): TicketAlert
    {
        return $this->testDataFactory->createTicketAlert($attributes);
    }
}
