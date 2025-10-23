<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use PHPUnit\Framework\Attributes\Test;
class EnhancedDashboardRealtimeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_user_can_fetch_realtime_dashboard_data(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard/realtime');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'statistics',
                    'recent_tickets',
                    'user_metrics',
                    'system_status',
                    'notifications',
                    'last_updated',
                ],
                'meta' => [
                    'refresh_interval',
                    'cache_status',
                    'user_id',
                ],
            ])
            ->assertJson(['success' => TRUE]);
    }

    #[Test]
    public function unauthenticated_user_is_rejected(): void
    {
        $this->getJson('/api/v1/dashboard/realtime')
            ->assertStatus(401);
    }
}
