<?php declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TicketAlertObserverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_flushes_user_active_alerts_cache_on_alert_created(): void
    {
        $user = User::factory()->create();
        $cacheKey = "stats:user:{$user->id}:active_alerts";

        // Prime cache
        Cache::put($cacheKey, 9, 60);

        // Act: create alert for this user (observer should forget cache key)
        TicketAlert::factory()->create([
            'user_id' => $user->id,
            'status'  => 'active',
        ]);

        $this->assertTrue(Cache::missing($cacheKey));
    }
}
