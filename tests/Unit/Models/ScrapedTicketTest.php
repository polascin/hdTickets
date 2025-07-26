<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ScrapedTicket;
use App\Models\Category;
use Carbon\Carbon;

class ScrapedTicketTest extends TestCase
{
    public function test_scraped_ticket_has_correct_fillable_attributes()
    {
        $fillable = [
            'uuid',
            'platform',
            'external_id',
            'title',
            'venue',
            'location',
            'event_type',
            'sport',
            'team',
            'event_date',
            'min_price',
            'max_price',
            'currency',
            'availability',
            'is_available',
            'is_high_demand',
            'ticket_url',
            'search_keyword',
            'metadata',
            'scraped_at',
            'category_id'
        ];

        $ticket = new ScrapedTicket();
        $this->assertEquals($fillable, $ticket->getFillable());
    }

    public function test_scraped_ticket_has_correct_casts()
    {
        $ticket = new ScrapedTicket();
        $expectedCasts = [
            'id' => 'int',
            'event_date' => 'datetime',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_high_demand' => 'boolean',
            'scraped_at' => 'datetime',
            'metadata' => 'array'
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $ticket->getCasts()[$attribute] ?? null);
        }
    }

    public function test_scraped_ticket_automatically_generates_uuid_on_create()
    {
        $ticket = ScrapedTicket::factory()->create(['uuid' => null]);
        
        $this->assertNotNull($ticket->uuid);
        $this->assertTrue(is_string($ticket->uuid));
        $this->assertEquals(36, strlen($ticket->uuid)); // UUID v4 length
    }

    public function test_scraped_ticket_automatically_sets_scraped_at_on_create()
    {
        $before = now();
        $ticket = ScrapedTicket::factory()->create(['scraped_at' => null]);
        $after = now();
        
        $this->assertNotNull($ticket->scraped_at);
        $this->assertTrue($ticket->scraped_at->between($before, $after));
    }

    public function test_scraped_ticket_belongs_to_category()
    {
        $category = Category::factory()->create();
        $ticket = ScrapedTicket::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $ticket->category);
        $this->assertEquals($category->id, $ticket->category->id);
    }

    public function test_high_demand_scope()
    {
        ScrapedTicket::factory()->create(['is_high_demand' => true]);
        ScrapedTicket::factory()->create(['is_high_demand' => false]);

        $highDemandTickets = ScrapedTicket::highDemand()->get();
        
        $this->assertCount(1, $highDemandTickets);
        $this->assertTrue($highDemandTickets->first()->is_high_demand);
    }

    public function test_by_platform_scope()
    {
        ScrapedTicket::factory()->create(['platform' => 'stubhub']);
        ScrapedTicket::factory()->create(['platform' => 'ticketmaster']);

        $stubhubTickets = ScrapedTicket::byPlatform('stubhub')->get();
        
        $this->assertCount(1, $stubhubTickets);
        $this->assertEquals('stubhub', $stubhubTickets->first()->platform);
    }

    public function test_available_scope()
    {
        ScrapedTicket::factory()->create(['is_available' => true]);
        ScrapedTicket::factory()->create(['is_available' => false]);

        $availableTickets = ScrapedTicket::available()->get();
        
        $this->assertCount(1, $availableTickets);
        $this->assertTrue($availableTickets->first()->is_available);
    }

    public function test_for_event_scope()
    {
        ScrapedTicket::factory()->create([
            'title' => 'Manchester United vs Liverpool',
            'search_keyword' => 'man utd'
        ]);
        ScrapedTicket::factory()->create([
            'title' => 'Arsenal vs Chelsea',
            'search_keyword' => 'arsenal'
        ]);

        $manchesterTickets = ScrapedTicket::forEvent('Manchester')->get();
        
        $this->assertCount(1, $manchesterTickets);
        $this->assertStringContainsString('Manchester', $manchesterTickets->first()->title);
    }

    public function test_price_range_scope()
    {
        ScrapedTicket::factory()->create(['min_price' => 50, 'max_price' => 100]);
        ScrapedTicket::factory()->create(['min_price' => 150, 'max_price' => 300]);

        $affordableTickets = ScrapedTicket::priceRange(null, 200)->get();
        
        $this->assertCount(1, $affordableTickets);
        $this->assertLessThanOrEqual(200, $affordableTickets->first()->max_price);
    }

    public function test_formatted_price_attribute()
    {
        $ticket = ScrapedTicket::factory()->create([
            'min_price' => 50.00,
            'max_price' => 100.00,
            'currency' => 'GBP'
        ]);

        $this->assertEquals('GBP 100.00', $ticket->formatted_price);
    }

    public function test_formatted_price_attribute_uses_min_price_when_max_is_null()
    {
        $ticket = ScrapedTicket::factory()->create([
            'min_price' => 75.50,
            'max_price' => null,
            'currency' => 'USD'
        ]);

        $this->assertEquals('USD 75.50', $ticket->formatted_price);
    }

    public function test_is_recent_attribute()
    {
        $recentTicket = ScrapedTicket::factory()->create([
            'scraped_at' => now()->subHours(12)
        ]);
        
        $oldTicket = ScrapedTicket::factory()->create([
            'scraped_at' => now()->subHours(48)
        ]);

        $this->assertTrue($recentTicket->is_recent);
        $this->assertFalse($oldTicket->is_recent);
    }

    public function test_platform_display_name_attribute()
    {
        $stubhubTicket = ScrapedTicket::factory()->create(['platform' => 'stubhub']);
        $ticketmasterTicket = ScrapedTicket::factory()->create(['platform' => 'ticketmaster']);
        $viagogoTicket = ScrapedTicket::factory()->create(['platform' => 'viagogo']);
        $otherTicket = ScrapedTicket::factory()->create(['platform' => 'other']);

        $this->assertEquals('StubHub', $stubhubTicket->platform_display_name);
        $this->assertEquals('Ticketmaster', $ticketmasterTicket->platform_display_name);
        $this->assertEquals('Viagogo', $viagogoTicket->platform_display_name);
        $this->assertEquals('Other', $otherTicket->platform_display_name);
    }

    public function test_metadata_is_cast_to_array()
    {
        $metadata = ['source' => 'api', 'version' => '1.0'];
        $ticket = ScrapedTicket::factory()->create(['metadata' => $metadata]);

        $this->assertIsArray($ticket->metadata);
        $this->assertEquals($metadata, $ticket->metadata);
    }

    public function test_event_date_is_cast_to_carbon()
    {
        $eventDate = '2024-12-25 15:00:00';
        $ticket = ScrapedTicket::factory()->create(['event_date' => $eventDate]);

        $this->assertInstanceOf(Carbon::class, $ticket->event_date);
        $this->assertEquals('2024-12-25', $ticket->event_date->format('Y-m-d'));
    }
}
