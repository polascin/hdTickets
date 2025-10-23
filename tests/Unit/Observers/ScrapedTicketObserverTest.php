<?php declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Models\ScrapedTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ScrapedTicketObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_flushes_stats_caches_on_scraped_ticket_created(): void
    {
        // Prime caches
        Cache::put('stats:available_tickets', 123, 60);
        Cache::put('stats:new_today', 10, 60);
        Cache::put('stats:unique_events', 5, 60);
        Cache::put('stats:average_price', 55.25, 60);

        // Act: create a ScrapedTicket (triggers observer)
        ScrapedTicket::factory()->create([
            'is_available' => true,
            'status'       => 'active',
        ]);

        // Assert caches are flushed
        $this->assertTrue(Cache::missing('stats:available_tickets'));
        $this->assertTrue(Cache::missing('stats:new_today'));
        $this->assertTrue(Cache::missing('stats:unique_events'));
        $this->assertTrue(Cache::missing('stats:average_price'));
    }
}
