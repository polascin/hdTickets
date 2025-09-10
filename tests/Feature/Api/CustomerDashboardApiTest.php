<?php declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_stats_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/dashboard/stats')->assertStatus(401);
    }

    #[Test]
    public function test_it_returns_stats_structure(): void
    {
        $user = User::factory()->create();
        ScrapedTicket::factory()->count(3)->create(['is_available' => TRUE]);
        TicketAlert::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user)->getJson('/api/dashboard/stats');
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'stats' => [
                    'available_tickets', 'new_today', 'monitored_events', 'active_alerts', 'price_alerts', 'triggered_today',
                ],
                'meta' => ['refreshed_at', 'cache_ttl', 'version'],
            ]);
    }

    #[Test]
    public function test_tickets_endpoint_supports_filters_and_pagination(): void
    {
        $user = User::factory()->create();
        ScrapedTicket::factory()->count(60)->create(['is_available' => TRUE, 'sport' => 'football']);
        ScrapedTicket::factory()->count(10)->create(['is_available' => TRUE, 'sport' => 'basketball']);

        $response = $this->actingAs($user)->getJson('/api/dashboard/tickets?per_page=25&page=2&sport=football');
        $response->assertOk()
            ->assertJsonStructure([
                'success', 'tickets', 'count', 'pagination' => ['total', 'per_page', 'current_page', 'last_page'], 'demand' => ['sport_distribution', 'platform_distribution'],
            ]);
    }

    #[Test]
    public function test_recommendations_endpoint_returns_data(): void
    {
        $user = User::factory()->create();
        ScrapedTicket::factory()->count(5)->create(['is_available' => TRUE]);

        $response = $this->actingAs($user)->getJson('/api/dashboard/recommendations');
        $response->assertOk()->assertJsonStructure([
            'success', 'recommendations', 'meta' => ['refreshed_at', 'cache_ttl'],
        ]);
    }
}
