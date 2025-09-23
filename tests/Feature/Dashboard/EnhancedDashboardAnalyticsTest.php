<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancedDashboardAnalyticsTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function customer_user_can_fetch_analytics_data(): void
  {
    $user = User::factory()->create([
      'role' => 'customer',
      'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/dashboard/analytics-data');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          'generated_at',
          'totals' => [
            'available_tickets',
            'unique_events'
          ],
          'trends' => [
            'demand' => [
              'high_demand',
              'demand_percentage'
            ],
            'pricing'
          ],
          'platforms'
        ],
        'meta' => [
          'user_id',
          'generated_at'
        ]
      ])
      ->assertJson(['success' => true]);
  }

  /** @test */
  public function unauthenticated_user_cannot_fetch_analytics(): void
  {
    $this->getJson('/api/v1/dashboard/analytics-data')
      ->assertStatus(401);
  }
}
