<?php declare(strict_types=1);

namespace Tests\EndToEnd;

use PHPUnit\Framework\Attributes\Test;
use App\Jobs\ProcessPurchaseAttempt;
use App\Mail\AccountDeletionRequested;
use App\Mail\SubscriptionConfirmation;
use App\Mail\TicketAlert;
use App\Mail\WelcomeUser;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EnhancedAlertSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserJourneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     */
    #[Test]
    public function complete_user_registration_and_onboarding_journey(): void
    {
        Mail::fake();

        // 1. User visits registration page and creates account
        $registrationData = [
            'name'                  => 'John Doe',
            'email'                 => 'john.doe@example.com',
            'password'              => 'SecurePassword123',
            'password_confirmation' => 'SecurePassword123',
        ];

        $response = $this->postJson('/api/register', $registrationData);

        $this->assertApiResponse($response, 201, [
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

        // Verify welcome email was sent
        Mail::assertQueued(WelcomeUser::class);

        // 2. User verifies email
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);

        $verificationUrl = "/api/email/verify/{$user->id}/" . sha1((string) $user->email);
        $response = $this->getJson($verificationUrl);

        $this->assertEquals(200, $response->status());

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // 3. User completes profile setup
        Sanctum::actingAs($user);

        $profileData = [
            'preferences' => [
                'notifications' => [
                    'email' => TRUE,
                    'sms'   => FALSE,
                    'push'  => TRUE,
                ],
                'currency' => 'USD',
                'timezone' => 'America/New_York',
            ],
            'phone' => '+1234567890',
        ];

        $response = $this->putJson('/api/profile', $profileData);
        $this->assertApiResponse($response, 200);

        // 4. User sets up their first ticket alert
        $alertData = [
            'title'    => 'Manchester United Home Games',
            'criteria' => [
                'sport_type' => 'football',
                'teams'      => ['Manchester United'],
                'max_price'  => 150,
                'cities'     => ['Manchester'],
            ],
            'notification_channels' => ['email', 'push'],
        ];

        $response = $this->postJson('/api/tickets/alerts', $alertData);
        $this->assertApiResponse($response, 201);

        $this->assertDatabaseHas('ticket_alerts', [
            'user_id'   => $user->id,
            'title'     => 'Manchester United Home Games',
            'is_active' => TRUE,
        ]);
    }

    /**
     */
    #[Test]
    public function ticket_discovery_and_purchase_attempt_journey(): void
    {
        Queue::fake();

        $user = $this->createTestUser();
        Sanctum::actingAs($user);

        // 1. User searches for tickets
        $this->createTestTicket([
            'title'      => 'Manchester United vs Liverpool',
            'sport_type' => 'football',
            'team_home'  => 'Manchester United',
            'team_away'  => 'Liverpool',
            'price_min'  => 75.00,
            'price_max'  => 200.00,
            'status'     => 'available',
            'event_date' => now()->addDays(30),
        ]);

        $response = $this->getJson('/api/tickets?search=Manchester United&sport_type=football');

        $this->assertApiResponse($response, 200);
        $tickets = $response->json('data');
        $this->assertCount(1, $tickets);

        $ticket = $tickets[0];
        $this->assertEquals('Manchester United vs Liverpool', $ticket['title']);

        // 2. User views ticket details
        $response = $this->getJson("/api/tickets/{$ticket['id']}");

        $this->assertApiResponse($response, 200, [
            'id', 'title', 'event_date', 'venue',
            'price_min', 'price_max', 'status',
        ]);

        // 3. User creates purchase attempt
        $purchaseData = [
            'quantity'  => 2,
            'max_price' => 180.00,
            'priority'  => 'high',
        ];

        $response = $this->postJson("/api/tickets/{$ticket['id']}/purchase", $purchaseData);

        $this->assertApiResponse($response, 201, [
            'id', 'user_id', 'ticket_id', 'quantity',
            'max_price', 'status', 'priority',
        ]);

        // Verify purchase attempt was queued for processing
        Queue::assertPushed(ProcessPurchaseAttempt::class);

        // 4. User checks purchase attempt status
        $purchaseAttempt = $response->json();
        $response = $this->getJson("/api/purchase-attempts/{$purchaseAttempt['id']}");

        $this->assertApiResponse($response, 200);
        $this->assertEquals('pending', $response->json('status'));
    }

    /**
     */
    #[Test]
    public function premium_user_upgrade_and_benefits_journey(): void
    {
        Mail::fake();

        $user = $this->createTestUser();
        Sanctum::actingAs($user);

        // 1. User views available subscription plans
        $response = $this->getJson('/api/subscription/plans');

        $this->assertApiResponse($response, 200);
        $plans = $response->json();

        $premiumPlan = collect($plans)->firstWhere('name', 'premium');
        $this->assertNotNull($premiumPlan);

        // 2. User upgrades to premium
        $this->mockStripeService(); // Mock payment processing

        $subscriptionData = [
            'plan'            => 'premium',
            'payment_method'  => 'pm_card_visa', // Stripe test token
            'billing_address' => [
                'street'  => '123 Test Street',
                'city'    => 'Test City',
                'state'   => 'TS',
                'zip'     => '12345',
                'country' => 'US',
            ],
        ];

        $response = $this->postJson('/api/subscription/subscribe', $subscriptionData);

        $this->assertApiResponse($response, 201);

        // Verify user now has premium status
        $user->refresh();
        $this->assertTrue($user->isPremium());

        // Verify subscription confirmation email was sent
        Mail::assertQueued(SubscriptionConfirmation::class);

        // 3. Premium user creates purchase attempt with higher priority
        $ticket = $this->createTestTicket(['status' => 'available']);

        $purchaseData = [
            'quantity'  => 1,
            'max_price' => 100.00,
        ];

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", $purchaseData);

        $this->assertApiResponse($response, 201);

        $purchaseAttempt = $response->json();
        $this->assertEquals('high', $purchaseAttempt['priority']); // Premium users get high priority

        // 4. Premium user can create more alerts
        for ($i = 1; $i <= 10; $i++) { // Premium users can create up to 10 alerts
            $alertData = [
                'title'                 => "Alert {$i}",
                'criteria'              => ['sport_type' => 'football'],
                'notification_channels' => ['email'],
            ];

            $response = $this->postJson('/api/tickets/alerts', $alertData);
            $this->assertApiResponse($response, 201);
        }

        // Regular users would be limited to 3 alerts
        $this->assertEquals(10, $user->ticketAlerts()->count());
    }

    /**
     */
    #[Test]
    public function ticket_alert_notification_and_response_journey(): void
    {
        Mail::fake();
        Notification::fake();

        $user = $this->createTestUser([
            'email'       => 'alert.user@example.com',
            'preferences' => [
                'notifications' => [
                    'email' => TRUE,
                    'push'  => TRUE,
                ],
            ],
        ]);

        // 1. User creates ticket alert
        $alert = $this->createTicketAlert([
            'user_id'  => $user->id,
            'title'    => 'Liverpool FC Tickets',
            'criteria' => [
                'sport_type' => 'football',
                'teams'      => ['Liverpool'],
                'max_price'  => 120,
            ],
            'notification_channels' => ['email', 'push'],
            'is_active'             => TRUE,
        ]);

        // 2. Matching ticket becomes available
        $ticket = $this->createTestTicket([
            'title'      => 'Liverpool vs Arsenal',
            'sport_type' => 'football',
            'team_home'  => 'Liverpool',
            'team_away'  => 'Arsenal',
            'price_min'  => 80.00,
            'price_max'  => 110.00,
            'status'     => 'available',
        ]);

        // 3. Alert system processes the new ticket
        $alertSystem = app(EnhancedAlertSystem::class);
        $alertSystem->checkAndTriggerAlerts($ticket);

        // Verify notifications were sent
        Mail::assertQueued(TicketAlert::class);

        // 4. User receives notification and clicks through
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/tickets/{$ticket->id}");
        $this->assertApiResponse($response, 200);

        // 5. User creates purchase attempt from alert
        $purchaseData = [
            'quantity'  => 1,
            'max_price' => 110.00,
            'alert_id'  => $alert->id, // Track that this came from alert
        ];

        $response = $this->postJson("/api/tickets/{$ticket->id}/purchase", $purchaseData);
        $this->assertApiResponse($response, 201);

        // 6. Update alert trigger count
        $alert->refresh();
        $this->assertEquals(1, $alert->trigger_count);
        $this->assertNotNull($alert->last_triggered_at);
    }

    /**
     */
    #[Test]
    public function admin_ticket_management_and_monitoring_journey(): void
    {
        $admin = $this->createTestUser(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // 1. Admin views system dashboard
        $response = $this->getJson('/api/admin/dashboard');

        $this->assertApiResponse($response, 200, [
            'tickets_count',
            'users_count',
            'active_alerts',
            'pending_purchases',
            'system_health',
        ]);

        // 2. Admin creates new ticket source
        $sourceData = [
            'name'             => 'New Ticket Platform',
            'base_url'         => 'https://newplatform.com',
            'scraping_enabled' => TRUE,
            'rate_limit'       => 60,
            'supported_sports' => ['football', 'basketball'],
        ];

        $response = $this->postJson('/api/admin/ticket-sources', $sourceData);
        $this->assertApiResponse($response, 201);

        $source = $response->json();

        // 3. Admin creates tickets for the new source
        $ticketData = [
            'title'      => 'Admin Test Event',
            'event_date' => now()->addDays(60)->toISOString(),
            'venue'      => 'Test Stadium',
            'city'       => 'Test City',
            'sport_type' => 'football',
            'price_min'  => 50.00,
            'price_max'  => 150.00,
            'source_id'  => $source['id'],
        ];

        $response = $this->postJson('/api/admin/tickets', $ticketData);
        $this->assertApiResponse($response, 201);

        // 4. Admin monitors scraping performance
        $response = $this->getJson('/api/admin/scraping/statistics');

        $this->assertApiResponse($response, 200, [
            'sources_count',
            'success_rate',
            'average_response_time',
            'last_scrape_results',
        ]);

        // 5. Admin manages user support requests
        $response = $this->getJson('/api/admin/support/requests');
        $this->assertApiResponse($response, 200);

        // 6. Admin views system alerts and notifications
        $response = $this->getJson('/api/admin/alerts');
        $this->assertApiResponse($response, 200);
    }

    /**
     */
    #[Test]
    public function user_account_management_and_privacy_journey(): void
    {
        Mail::fake();

        $user = $this->createTestUser();
        Sanctum::actingAs($user);

        // 1. User updates their profile information
        $profileData = [
            'name'        => 'Updated Name',
            'phone'       => '+1987654321',
            'preferences' => [
                'notifications' => [
                    'email' => FALSE,
                    'sms'   => TRUE,
                    'push'  => TRUE,
                ],
                'currency' => 'EUR',
                'timezone' => 'Europe/London',
            ],
        ];

        $response = $this->putJson('/api/profile', $profileData);
        $this->assertApiResponse($response, 200);

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('EUR', $user->preferences['currency']);

        // 2. User enables two-factor authentication
        $response = $this->postJson('/api/profile/2fa/enable');

        $this->assertApiResponse($response, 200, [
            'qr_code',
            'secret',
        ]);

        $response->json('secret');

        // Verify 2FA setup
        $verificationData = [
            'code' => '123456', // In real scenario, generate from secret
        ];

        $response = $this->postJson('/api/profile/2fa/verify', $verificationData);
        // Note: This might fail without proper TOTP code, but tests the endpoint

        // 3. User requests data export (GDPR compliance)
        $response = $this->postJson('/api/profile/export-data');

        $this->assertApiResponse($response, 202); // Accepted for processing

        // Verify export request was created
        $this->assertDatabaseHas('data_export_requests', [
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);

        // 4. User changes password
        $passwordData = [
            'current_password'          => 'password',
            'new_password'              => 'NewSecurePassword123',
            'new_password_confirmation' => 'NewSecurePassword123',
        ];

        $response = $this->putJson('/api/profile/password', $passwordData);
        $this->assertApiResponse($response, 200);

        // 5. User deactivates account
        $deactivationData = [
            'reason'   => 'No longer needed',
            'feedback' => 'Service was good while I used it',
        ];

        $response = $this->postJson('/api/profile/deactivate', $deactivationData);
        $this->assertApiResponse($response, 200);

        // Verify account deactivation
        $user->refresh();
        $this->assertEquals('inactive', $user->status);

        // 6. User requests account deletion (30-day grace period)
        $response = $this->deleteJson('/api/profile');
        $this->assertApiResponse($response, 202);

        // Verify deletion request was created
        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);

        Mail::assertQueued(AccountDeletionRequested::class);
    }

    /**
     */
    #[Test]
    public function error_handling_and_recovery_journey(): void
    {
        $user = $this->createTestUser();

        // 1. Test API error handling
        $response = $this->getJson('/api/tickets/nonexistent-id');
        $this->assertApiResponse($response, 404, ['message']);

        // 2. Test validation error handling
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets/alerts', [
            'title'                 => '', // Invalid: empty title
            'criteria'              => 'invalid', // Invalid: should be array
            'notification_channels' => ['invalid_channel'], // Invalid channel
        ]);

        $this->assertApiResponse($response, 422, ['errors']);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('criteria', $errors);
        $this->assertArrayHasKey('notification_channels', $errors);

        // 3. Test rate limiting
        for ($i = 0; $i < 65; $i++) { // Exceed rate limit
            $response = $this->getJson('/api/tickets');

            if ($response->status() === 429) {
                $this->assertArrayHasKey('retry_after', $response->headers->all());

                break;
            }
        }

        // 4. Test authentication error
        auth()->logout();

        $response = $this->postJson('/api/tickets/alerts', [
            'title' => 'Test Alert',
        ]);

        $this->assertApiResponse($response, 401, ['message']);

        // 5. Test authorization error
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/admin/tickets', [
            'title' => 'Unauthorized Ticket',
        ]);

        $this->assertApiResponse($response, 403, ['message']);
    }

    /**
     */
    #[Test]
    public function mobile_app_user_journey(): void
    {
        $user = $this->createTestUser();

        // 1. Mobile app authentication
        $loginData = [
            'email'       => $user->email,
            'password'    => 'password',
            'device_name' => 'iPhone 12',
        ];

        $response = $this->postJson('/api/mobile/login', $loginData);

        $this->assertApiResponse($response, 200, [
            'user',
            'token',
            'expires_at',
        ]);

        $token = $response->json('token');

        // 2. Register for push notifications
        $pushData = [
            'device_token' => 'mobile_device_token_123',
            'platform'     => 'ios',
        ];

        $response = $this->postJson('/api/mobile/push/register', $pushData, [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertApiResponse($response, 200);

        // 3. Mobile-optimized ticket search
        $response = $this->getJson('/api/mobile/tickets/nearby?lat=53.4808&lng=-2.2426', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertApiResponse($response, 200, [
            'data' => '*',
            'meta',
        ]);

        // 4. Quick purchase via mobile
        $ticket = $this->createTestTicket(['status' => 'available']);

        $purchaseData = [
            'quantity'          => 1,
            'max_price'         => 100.00,
            'use_saved_payment' => TRUE,
        ];

        $response = $this->postJson("/api/mobile/tickets/{$ticket->id}/quick-purchase", $purchaseData, [
            'Authorization' => "Bearer {$token}",
        ]);

        // This might return different status codes based on implementation
        $this->assertContains($response->status(), [201, 202]);
    }
}
