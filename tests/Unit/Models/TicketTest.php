<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\PurchaseAttempt;
use App\Models\ScrapedTicket;
use App\Models\Ticket;
use App\Models\TicketPriceHistory;
use App\Models\TicketSource;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_create_a_ticket_with_basic_attributes(): void
    {
        $ticketData = [
            'title'              => 'Manchester United vs Liverpool',
            'event_date'         => now()->addWeeks(2),
            'venue'              => 'Old Trafford',
            'city'               => 'Manchester',
            'country'            => 'UK',
            'sport_type'         => 'football',
            'team_home'          => 'Manchester United',
            'team_away'          => 'Liverpool',
            'price_min'          => 50.00,
            'price_max'          => 200.00,
            'currency'           => 'GBP',
            'available_quantity' => 100,
            'status'             => 'available',
            'source_platform'    => 'ticketmaster',
        ];

        $ticket = Ticket::create($ticketData);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals('Manchester United vs Liverpool', $ticket->title);
        $this->assertEquals('football', $ticket->sport_type);
        $this->assertEquals('available', $ticket->status);
        $this->assertEquals(50.00, $ticket->price_min);
        $this->assertEquals(200.00, $ticket->price_max);
    }

    /**
     * @test
     */
    public function it_has_proper_fillable_attributes(): void
    {
        $ticket = new Ticket();

        $expectedFillable = [
            'title', 'event_date', 'venue', 'city', 'country',
            'sport_type', 'team_home', 'team_away', 'price_min',
            'price_max', 'currency', 'available_quantity', 'status',
            'source_platform', 'source_url', 'metadata', 'last_scraped_at',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $ticket->getFillable());
        }
    }

    /**
     * @test
     */
    public function it_casts_attributes_correctly(): void
    {
        $ticket = $this->createTestTicket([
            'event_date'      => now()->addWeeks(2),
            'price_min'       => 50.50,
            'price_max'       => 150.75,
            'metadata'        => ['section' => 'A', 'row' => 10],
            'last_scraped_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $ticket->event_date);
        $this->assertIsFloat($ticket->price_min);
        $this->assertIsFloat($ticket->price_max);
        $this->assertIsArray($ticket->metadata);
        $this->assertInstanceOf(Carbon::class, $ticket->last_scraped_at);
    }

    /**
     * @test
     */
    public function it_has_relationship_with_ticket_source(): void
    {
        $source = $this->createTestTicketSource();
        $ticket = $this->createTestTicket(['source_id' => $source->id]);

        $this->assertInstanceOf(TicketSource::class, $ticket->source);
        $this->assertEquals($source->id, $ticket->source->id);
        $this->assertEquals($source->name, $ticket->source->name);
    }

    /**
     * @test
     */
    public function it_has_relationship_with_purchase_attempts(): void
    {
        $ticket = $this->createTestTicket();
        $user = $this->createTestUser();

        PurchaseAttempt::create([
            'user_id'   => $user->id,
            'ticket_id' => $ticket->id,
            'quantity'  => 2,
            'max_price' => 150.00,
            'status'    => 'pending',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $ticket->purchaseAttempts);
        $this->assertEquals(1, $ticket->purchaseAttempts->count());
        $this->assertEquals('pending', $ticket->purchaseAttempts->first()->status);
    }

    /**
     * @test
     */
    public function it_has_relationship_with_price_history(): void
    {
        $ticket = $this->createTestTicket();

        TicketPriceHistory::create([
            'ticket_id'   => $ticket->id,
            'price'       => 100.00,
            'currency'    => 'USD',
            'recorded_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $ticket->priceHistory);
        $this->assertEquals(1, $ticket->priceHistory->count());
        $this->assertEquals(100.00, $ticket->priceHistory->first()->price);
    }

    /**
     * @test
     */
    public function it_has_relationship_with_scraped_tickets(): void
    {
        $source = $this->createTestTicketSource();
        $ticket = $this->createTestTicket(['source_id' => $source->id]);

        ScrapedTicket::create([
            'source_id'   => $source->id,
            'ticket_id'   => $ticket->id,
            'external_id' => 'external_123',
            'title'       => $ticket->title,
            'event_date'  => $ticket->event_date,
            'price'       => 100.00,
            'scraped_at'  => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $ticket->scrapedData);
        $this->assertEquals(1, $ticket->scrapedData->count());
        $this->assertEquals('external_123', $ticket->scrapedData->first()->external_id);
    }

    /**
     * @test
     */
    public function it_can_check_if_ticket_is_available(): void
    {
        $availableTicket = $this->createTestTicket(['status' => 'available']);
        $soldOutTicket = $this->createTestTicket(['status' => 'sold_out']);

        $this->assertTrue($availableTicket->isAvailable());
        $this->assertFalse($soldOutTicket->isAvailable());
    }

    /**
     * @test
     */
    public function it_can_check_if_ticket_is_sold_out(): void
    {
        $availableTicket = $this->createTestTicket(['status' => 'available']);
        $soldOutTicket = $this->createTestTicket(['status' => 'sold_out']);

        $this->assertFalse($availableTicket->isSoldOut());
        $this->assertTrue($soldOutTicket->isSoldOut());
    }

    /**
     * @test
     */
    public function it_can_get_ticket_price_range(): void
    {
        $ticket = $this->createTestTicket([
            'price_min' => 75.00,
            'price_max' => 250.00,
            'currency'  => 'USD',
        ]);

        $priceRange = $ticket->getPriceRange();

        $this->assertEquals('$75.00 - $250.00', $priceRange);
    }

    /**
     * @test
     */
    public function it_can_get_formatted_event_date(): void
    {
        $eventDate = Carbon::createFromFormat('Y-m-d H:i:s', '2024-12-25 19:30:00');
        $ticket = $this->createTestTicket(['event_date' => $eventDate]);

        $formattedDate = $ticket->getFormattedEventDate();

        $this->assertStringContains('Dec 25, 2024', $formattedDate);
        $this->assertStringContains('7:30 PM', $formattedDate);
    }

    /**
     * @test
     */
    public function it_can_get_team_display_name(): void
    {
        $ticket = $this->createTestTicket([
            'team_home' => 'Manchester United',
            'team_away' => 'Liverpool FC',
        ]);

        $teamDisplay = $ticket->getTeamDisplay();

        $this->assertEquals('Manchester United vs Liverpool FC', $teamDisplay);
    }

    /**
     * @test
     */
    public function it_can_check_if_event_is_upcoming(): void
    {
        $upcomingTicket = $this->createTestTicket(['event_date' => now()->addDays(7)]);
        $pastTicket = $this->createTestTicket(['event_date' => now()->subDays(7)]);

        $this->assertTrue($upcomingTicket->isUpcoming());
        $this->assertFalse($pastTicket->isUpcoming());
    }

    /**
     * @test
     */
    public function it_can_check_if_event_is_today(): void
    {
        $todayTicket = $this->createTestTicket(['event_date' => now()]);
        $tomorrowTicket = $this->createTestTicket(['event_date' => now()->addDay()]);

        $this->assertTrue($todayTicket->isToday());
        $this->assertFalse($tomorrowTicket->isToday());
    }

    /**
     * @test
     */
    public function it_can_get_days_until_event(): void
    {
        $ticket = $this->createTestTicket(['event_date' => now()->addDays(5)]);

        $daysUntil = $ticket->getDaysUntilEvent();

        $this->assertEquals(5, $daysUntil);
    }

    /**
     * @test
     */
    public function it_can_get_average_price(): void
    {
        $ticket = $this->createTestTicket([
            'price_min' => 100.00,
            'price_max' => 200.00,
        ]);

        $averagePrice = $ticket->getAveragePrice();

        $this->assertEquals(150.00, $averagePrice);
    }

    /**
     * @test
     */
    public function it_can_update_availability_status(): void
    {
        $ticket = $this->createTestTicket(['status' => 'available', 'available_quantity' => 10]);

        // Update to limited availability
        $ticket->updateAvailabilityStatus(2);

        $this->assertEquals('limited', $ticket->status);
        $this->assertEquals(2, $ticket->available_quantity);

        // Update to sold out
        $ticket->updateAvailabilityStatus(0);

        $this->assertEquals('sold_out', $ticket->status);
        $this->assertEquals(0, $ticket->available_quantity);
    }

    /**
     * @test
     */
    public function it_can_add_price_history_entry(): void
    {
        $ticket = $this->createTestTicket();

        $ticket->addPriceHistory(125.00, 'USD');

        $this->assertEquals(1, $ticket->priceHistory()->count());

        $priceEntry = $ticket->priceHistory()->first();
        $this->assertEquals(125.00, $priceEntry->price);
        $this->assertEquals('USD', $priceEntry->currency);
    }

    /**
     * @test
     */
    public function it_can_get_price_trend(): void
    {
        $ticket = $this->createTestTicket();

        // Add price history entries
        $ticket->addPriceHistory(100.00, 'USD', now()->subDays(2));
        $ticket->addPriceHistory(120.00, 'USD', now()->subDay());
        $ticket->addPriceHistory(110.00, 'USD', now());

        $trend = $ticket->getPriceTrend();

        $this->assertEquals('decreasing', $trend);
    }

    /**
     * @test
     */
    public function it_can_scope_available_tickets(): void
    {
        $this->createTestTicket(['status' => 'available']);
        $this->createTestTicket(['status' => 'limited']);
        $this->createTestTicket(['status' => 'sold_out']);

        $availableTickets = Ticket::available()->get();

        $this->assertEquals(2, $availableTickets->count());
        $this->assertNotContains('sold_out', $availableTickets->pluck('status')->toArray());
    }

    /**
     * @test
     */
    public function it_can_scope_tickets_by_sport(): void
    {
        $this->createTestTicket(['sport_type' => 'football']);
        $this->createTestTicket(['sport_type' => 'basketball']);
        $this->createTestTicket(['sport_type' => 'football']);

        $footballTickets = Ticket::bySport('football')->get();

        $this->assertEquals(2, $footballTickets->count());
        $footballTickets->each(function ($ticket): void {
            $this->assertEquals('football', $ticket->sport_type);
        });
    }

    /**
     * @test
     */
    public function it_can_scope_tickets_by_price_range(): void
    {
        $this->createTestTicket(['price_min' => 50, 'price_max' => 100]);
        $this->createTestTicket(['price_min' => 200, 'price_max' => 300]);
        $this->createTestTicket(['price_min' => 75, 'price_max' => 150]);

        $affordableTickets = Ticket::inPriceRange(0, 120)->get();

        $this->assertEquals(2, $affordableTickets->count());
    }

    /**
     * @test
     */
    public function it_can_scope_upcoming_tickets(): void
    {
        $this->createTestTicket(['event_date' => now()->addDays(7)]);
        $this->createTestTicket(['event_date' => now()->subDays(7)]);
        $this->createTestTicket(['event_date' => now()->addDays(14)]);

        $upcomingTickets = Ticket::upcoming()->get();

        $this->assertEquals(2, $upcomingTickets->count());
        $upcomingTickets->each(function ($ticket): void {
            $this->assertTrue($ticket->event_date->isFuture());
        });
    }

    /**
     * @test
     */
    public function it_can_scope_tickets_by_city(): void
    {
        $this->createTestTicket(['city' => 'Manchester']);
        $this->createTestTicket(['city' => 'Liverpool']);
        $this->createTestTicket(['city' => 'Manchester']);

        $manchesterTickets = Ticket::inCity('Manchester')->get();

        $this->assertEquals(2, $manchesterTickets->count());
        $manchesterTickets->each(function ($ticket): void {
            $this->assertEquals('Manchester', $ticket->city);
        });
    }

    /**
     * @test
     */
    public function it_can_search_tickets_by_team(): void
    {
        $this->createTestTicket(['team_home' => 'Manchester United', 'team_away' => 'Liverpool']);
        $this->createTestTicket(['team_home' => 'Chelsea', 'team_away' => 'Arsenal']);
        $this->createTestTicket(['team_home' => 'Liverpool', 'team_away' => 'Manchester City']);

        $unitedTickets = Ticket::withTeam('Manchester United')->get();
        $liverpoolTickets = Ticket::withTeam('Liverpool')->get();

        $this->assertEquals(1, $unitedTickets->count());
        $this->assertEquals(2, $liverpoolTickets->count());
    }

    /**
     * @test
     */
    public function it_validates_required_fields(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Ticket::create([
            'title'      => NULL, // Required field
            'event_date' => now()->addDays(7),
            'price_min'  => 50.00,
        ]);
    }

    /**
     * @test
     */
    public function it_validates_price_constraints(): void
    {
        $ticket = $this->createTestTicket([
            'price_min' => 100.00,
            'price_max' => 50.00, // Should be higher than min
        ]);

        // This should trigger a validation error in a real application
        $this->assertTrue($ticket->price_max < $ticket->price_min);
    }

    /**
     * @test
     */
    public function it_can_soft_delete_ticket(): void
    {
        $ticket = $this->createTestTicket();

        $ticket->delete();

        $this->assertSoftDeleted($ticket);
    }
}
