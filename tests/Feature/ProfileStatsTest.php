<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileStatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     */
    #[Test]
    public function it_returns_profile_stats_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('profile.stats'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'stats' => [
                    'monitored_events',
                    'total_alerts',
                    'active_searches',
                    'recent_purchases',
                    'login_count',
                    'last_login_display',
                    'profile_completion',
                    'security_score',
                    'account_age_days',
                ],
                'updated_at',
                'cached',
            ])
            ->assertJson(['success' => TRUE]);
    }
}
