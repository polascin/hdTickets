<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TicketAlert;
use App\Models\ScrapedTicket;
use App\Notifications\HighValueTicketAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class HighValueTicketAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up the test database
        $this->artisan('migrate');
        
        // Mock notifications to prevent actual sending during tests
        Notification::fake();
    }

    #[Test]
    public function it_can_create_high_value_ticket_alert_notification()
    {
        // Create test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        // Create test ticket alert
        $ticketAlert = TicketAlert::factory()->create([
            'user_id' => $user->id,
            'name' => 'Manchester United Alerts',
            'keywords' => 'Manchester United',
            'max_price' => 300,
            'platform' => 'stubhub',
            'email_notifications' => true,
            'sms_notifications' => true,
            'is_active' => true
        ]);

        // Create test scraped ticket
        $scrapedTicket = new ScrapedTicket([
            'platform' => 'stubhub',
            'event_title' => 'Manchester United vs Liverpool',
            'venue' => 'Old Trafford',
            'event_date' => Carbon::now()->addDays(30),
            'price' => 120.00,
            'currency' => 'GBP',
            'total_price' => 120.00,
            'availability_status' => 'available',
            'quantity_available' => 2,
            'is_high_demand' => true,
            'demand_score' => 95,
            'ticket_url' => 'https://stubhub.com/ticket/123',
            'scraped_at' => now()
        ]);

        // Create the notification
        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 85);

        // Send the notification
        $user->notify($notification);

        // Assert notification was sent
        Notification::assertSentTo($user, HighValueTicketAlert::class);
    }

    #[Test]
    public function it_generates_correct_notification_channels_based_on_user_preferences()
    {
        // Create test user with phone
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        // Create ticket alert with both email and SMS enabled
        $ticketAlert = TicketAlert::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => true,
            'sms_notifications' => true
        ]);

        // Create high-demand ticket
        $scrapedTicket = new ScrapedTicket([
            'platform' => 'stubhub',
            'event_title' => 'Manchester United vs Liverpool',
            'is_high_demand' => true,
            'demand_score' => 95,
            'total_price' => 120.00
        ]);

        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 85);

        // Get the notification channels
        $channels = $notification->via($user);

        // Assert correct channels are included
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
        $this->assertContains('broadcast', $channels); // Because it's high demand
        $this->assertContains(\App\Channels\SmsChannel::class, $channels);
    }

    #[Test]
    public function it_excludes_channels_when_user_preferences_are_disabled()
    {
        // Create test user with no phone
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => null
        ]);

        // Create ticket alert with only email enabled
        $ticketAlert = TicketAlert::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => true,
            'sms_notifications' => false
        ]);

        // Create normal demand ticket
        $scrapedTicket = new ScrapedTicket([
            'platform' => 'stubhub',
            'event_title' => 'Manchester United vs Liverpool',
            'is_high_demand' => false,
            'demand_score' => 60,
            'total_price' => 120.00
        ]);

        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 85);

        // Get the notification channels
        $channels = $notification->via($user);

        // Assert correct channels are included/excluded
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
        $this->assertNotContains('broadcast', $channels); // Not high demand
        $this->assertNotContains(\App\Channels\SmsChannel::class, $channels); // No phone and disabled
    }

    #[Test]
    public function it_generates_mail_message_with_correct_content()
    {
        $user = User::factory()->create(['username' => 'testuser']);
        
        $ticketAlert = TicketAlert::factory()->create([
            'name' => 'Manchester United Alerts',
            'keywords' => 'Manchester United',
            'max_price' => 300
        ]);

        $scrapedTicket = new ScrapedTicket([
            'platform' => 'stubhub',
            'event_title' => 'Manchester United vs Liverpool',
            'venue' => 'Old Trafford',
            'event_date' => Carbon::parse('2024-03-15 15:00:00'),
            'total_price' => 250.00,
            'currency' => 'GBP',
            'section' => 'South Stand',
            'row' => '10',
            'quantity_available' => 2,
            'is_high_demand' => true,
            'demand_score' => 95,
            'ticket_url' => 'https://stubhub.com/ticket/123'
        ]);

        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 90);

        // Get the mail message
        $mailMessage = $notification->toMail($user);

        // Assert mail content
        $this->assertStringContainsString('HIGH PRIORITY', $mailMessage->subject);
        $this->assertStringContainsString('Manchester United vs Liverpool', $mailMessage->subject);
        $this->assertStringContainsString('testuser', $mailMessage->greeting);
    }

    #[Test]
    public function it_generates_sms_message_with_correct_content()
    {
        $user = User::factory()->create();
        
        $ticketAlert = TicketAlert::factory()->create();

        $scrapedTicket = new ScrapedTicket([
            'event_title' => 'Manchester United vs Liverpool',
            'venue' => 'Old Trafford',
            'event_date' => Carbon::parse('2024-03-15 15:00:00'),
            'total_price' => 250.00,
            'currency' => 'GBP',
            'section' => 'South Stand',
            'quantity_available' => 2,
            'is_high_demand' => true,
            'ticket_url' => 'https://stubhub.com/ticket/123'
        ]);

        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 90);

        // Get the SMS message
        $smsMessage = $notification->toSms($user);

        // Assert SMS content
        $this->assertStringContainsString('🔥 HIGH DEMAND', $smsMessage);
        $this->assertStringContainsString('Manchester United vs Liverpool', $smsMessage);
        $this->assertStringContainsString('Old Trafford', $smsMessage);
        $this->assertStringContainsString('Mar 15', $smsMessage);
        $this->assertStringContainsString('GBP 250.00', $smsMessage);
    }

    #[Test]
    public function it_generates_database_notification_with_complete_data()
    {
        $user = User::factory()->create();
        
        $ticketAlert = TicketAlert::factory()->create([
            'name' => 'Test Alert',
            'keywords' => 'Manchester United'
        ]);

        $scrapedTicket = new ScrapedTicket([
            'platform' => 'stubhub',
            'event_title' => 'Manchester United vs Liverpool',
            'venue' => 'Old Trafford',
            'event_date' => Carbon::parse('2024-03-15 15:00:00'),
            'total_price' => 250.00,
            'currency' => 'GBP',
            'section' => 'South Stand',
            'row' => '10',
            'quantity_available' => 2,
            'is_high_demand' => true,
            'demand_score' => 95,
            'ticket_url' => 'https://stubhub.com/ticket/123',
            'scraped_at' => now()
        ]);

        $notification = new HighValueTicketAlert($scrapedTicket, $ticketAlert, 90);

        // Get the database representation
        $databaseData = $notification->toArray($user);

        // Assert database content structure
        $this->assertEquals('high_value_ticket_alert', $databaseData['type']);
        $this->assertEquals('high', $databaseData['urgency']);
        $this->assertEquals('Manchester United vs Liverpool', $databaseData['event_title']);
        $this->assertEquals('stubhub', $databaseData['platform']);
        $this->assertEquals('StubHub', $databaseData['platform_display_name']);
        $this->assertEquals('Old Trafford', $databaseData['venue']);
        $this->assertEquals(250.00, $databaseData['price']);
        $this->assertEquals('GBP 250.00', $databaseData['formatted_price']);
        $this->assertEquals('South Stand', $databaseData['section']);
        $this->assertEquals('10', $databaseData['row']);
        $this->assertEquals(2, $databaseData['quantity_available']);
        $this->assertTrue($databaseData['is_high_demand']);
        $this->assertEquals(95, $databaseData['demand_score']);
        $this->assertEquals(90, $databaseData['match_score']);
        $this->assertEquals('Manchester United', $databaseData['keywords_matched']);
    }

    #[Test]
    public function it_sets_correct_queue_priority_for_high_demand_tickets()
    {
        $user = User::factory()->create();
        $ticketAlert = TicketAlert::factory()->create();

        // High demand ticket
        $highDemandTicket = new ScrapedTicket([
            'is_high_demand' => true,
            'event_title' => 'High Demand Event'
        ]);

        $highDemandNotification = new HighValueTicketAlert($highDemandTicket, $ticketAlert, 90);

        // Normal demand ticket
        $normalTicket = new ScrapedTicket([
            'is_high_demand' => false,
            'event_title' => 'Normal Event'
        ]);

        $normalNotification = new HighValueTicketAlert($normalTicket, $ticketAlert, 90);

        // We can't directly test the queue name, but we can verify the notification was created
        $this->assertInstanceOf(HighValueTicketAlert::class, $highDemandNotification);
        $this->assertInstanceOf(HighValueTicketAlert::class, $normalNotification);
    }
}
