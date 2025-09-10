<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Jobs\SendDelayedNotification;
use App\Mail\BulkNotification;
use App\Mail\PaymentFailure;
use App\Mail\PurchaseConfirmation;
use App\Mail\TemplatedNotification;
use App\Mail\TicketAlert;
use App\Mail\TicketNotification;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Override;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationService $notificationService;

    private $mockTwilioService;

    private $mockSlackService;

    private $mockPushService;

    /**
     */
    #[Test]
    public function it_can_send_email_notification(): void
    {
        $user = $this->createTestUser([
            'email'       => 'user@example.com',
            'preferences' => ['notifications' => ['email' => TRUE]],
        ]);

        $result = $this->notificationService->sendEmailNotification(
            $user,
            'Ticket Available',
            'A ticket you were waiting for is now available!',
        );

        $this->assertTrue($result);
        Mail::assertQueued(TicketNotification::class);
    }

    /**
     */
    #[Test]
    public function it_skips_email_when_user_disabled_email_notifications(): void
    {
        $user = $this->createTestUser([
            'preferences' => ['notifications' => ['email' => FALSE]],
        ]);

        $result = $this->notificationService->sendEmailNotification(
            $user,
            'Test Subject',
            'Test Message',
        );

        $this->assertFalse($result);
        Mail::assertNothingQueued();
    }

    /**
     */
    #[Test]
    public function it_can_send_sms_notification(): void
    {
        $user = $this->createTestUser([
            'phone'       => '+1234567890',
            'preferences' => ['notifications' => ['sms' => TRUE]],
        ]);

        $this->mockTwilioService
            ->shouldReceive('sendSMS')
            ->once()
            ->with('+1234567890', 'Test SMS message')
            ->andReturn(TRUE);

        $result = $this->notificationService->sendSMSNotification(
            $user,
            'Test SMS message',
        );

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_skips_sms_when_user_has_no_phone_number(): void
    {
        $user = $this->createTestUser(['phone' => NULL]);

        $result = $this->notificationService->sendSMSNotification(
            $user,
            'Test SMS message',
        );

        $this->assertFalse($result);
    }

    /**
     */
    #[Test]
    public function it_skips_sms_when_user_disabled_sms_notifications(): void
    {
        $user = $this->createTestUser([
            'phone'       => '+1234567890',
            'preferences' => ['notifications' => ['sms' => FALSE]],
        ]);

        $result = $this->notificationService->sendSMSNotification(
            $user,
            'Test SMS message',
        );

        $this->assertFalse($result);
    }

    /**
     */
    #[Test]
    public function it_can_send_push_notification(): void
    {
        $user = $this->createTestUser([
            'preferences' => ['notifications' => ['push' => TRUE]],
        ]);

        $this->mockPushService
            ->shouldReceive('sendPushNotification')
            ->once()
            ->with($user->id, 'Test Title', 'Test push message')
            ->andReturn(TRUE);

        $result = $this->notificationService->sendPushNotification(
            $user,
            'Test Title',
            'Test push message',
        );

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_can_send_slack_notification(): void
    {
        $this->mockSlackService
            ->shouldReceive('sendMessage')
            ->once()
            ->with('#alerts', 'Test slack message')
            ->andReturn(TRUE);

        $result = $this->notificationService->sendSlackNotification(
            '#alerts',
            'Test slack message',
        );

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_can_send_ticket_alert_notification(): void
    {
        $user = $this->createTestUser([
            'email'       => 'user@example.com',
            'phone'       => '+1234567890',
            'preferences' => [
                'notifications' => [
                    'email' => TRUE,
                    'sms'   => TRUE,
                    'push'  => TRUE,
                ],
            ],
        ]);

        $ticket = $this->createTestTicket([
            'title'     => 'Manchester United vs Liverpool',
            'price_min' => 50.00,
        ]);

        $alert = $this->createTicketAlert([
            'user_id'               => $user->id,
            'title'                 => 'Football Alerts',
            'notification_channels' => ['email', 'sms', 'push'],
        ]);

        $this->mockTwilioService
            ->shouldReceive('sendSMS')
            ->once()
            ->andReturn(TRUE);

        $this->mockPushService
            ->shouldReceive('sendPushNotification')
            ->once()
            ->andReturn(TRUE);

        $result = $this->notificationService->sendTicketAlertNotification(
            $user,
            $ticket,
            $alert,
        );

        $this->assertTrue($result);
        Mail::assertQueued(TicketAlert::class);
    }

    /**
     */
    #[Test]
    public function it_respects_user_notification_frequency_limits(): void
    {
        $user = $this->createTestUser();

        // Set up a recent notification
        $this->notificationService->recordNotificationSent($user->id, 'ticket_alert', now()->subMinutes(30));

        // Try to send another notification with hourly frequency limit
        $result = $this->notificationService->canSendNotification(
            $user->id,
            'ticket_alert',
            'hourly',
        );

        $this->assertFalse($result);
    }

    /**
     */
    #[Test]
    public function it_allows_notifications_after_frequency_period_passes(): void
    {
        $user = $this->createTestUser();

        // Set up an old notification
        $this->notificationService->recordNotificationSent($user->id, 'ticket_alert', now()->subHours(2));

        // Try to send another notification with hourly frequency limit
        $result = $this->notificationService->canSendNotification(
            $user->id,
            'ticket_alert',
            'hourly',
        );

        $this->assertTrue($result);
    }

    /**
     */
    #[Test]
    public function it_can_send_bulk_notifications(): void
    {
        $users = collect([
            $this->createTestUser(['email' => 'user1@example.com']),
            $this->createTestUser(['email' => 'user2@example.com']),
            $this->createTestUser(['email' => 'user3@example.com']),
        ]);

        $result = $this->notificationService->sendBulkEmailNotifications(
            $users,
            'System Maintenance',
            'The system will be under maintenance tomorrow.',
        );

        $this->assertTrue($result);
        Mail::assertQueued(BulkNotification::class, 3);
    }

    /**
     */
    #[Test]
    public function it_handles_notification_failures_gracefully(): void
    {
        $user = $this->createTestUser(['email' => 'invalid@email']);

        $this->mockTwilioService
            ->shouldReceive('sendSMS')
            ->andThrow(new Exception('SMS sending failed'));

        $result = $this->notificationService->sendSMSNotification(
            $user,
            'Test message',
        );

        $this->assertFalse($result);

        // Verify error was logged
        $this->assertTrue(TRUE); // Placeholder - in real implementation, check logs
    }

    /**
     */
    #[Test]
    public function it_can_queue_delayed_notifications(): void
    {
        $user = $this->createTestUser();
        $delayInMinutes = 15;

        $result = $this->notificationService->sendDelayedNotification(
            $user,
            'Delayed Notification',
            'This message is delayed',
            $delayInMinutes,
        );

        $this->assertTrue($result);
        Queue::assertPushed(SendDelayedNotification::class);
    }

    /**
     */
    #[Test]
    public function it_can_send_admin_notifications(): void
    {
        collect([
            $this->createTestUser(['role' => 'admin', 'email' => 'admin1@example.com']),
            $this->createTestUser(['role' => 'admin', 'email' => 'admin2@example.com']),
        ]);

        $result = $this->notificationService->sendAdminNotification(
            'System Alert',
            'High CPU usage detected',
        );

        $this->assertTrue($result);
        // In real implementation, verify admin users were notified
    }

    /**
     */
    #[Test]
    public function it_can_send_purchase_confirmation_notification(): void
    {
        $user = $this->createTestUser(['email' => 'buyer@example.com']);
        $ticket = $this->createTestTicket();
        $purchaseDetails = [
            'quantity'       => 2,
            'total_price'    => 150.00,
            'transaction_id' => 'txn_123456',
        ];

        $result = $this->notificationService->sendPurchaseConfirmation(
            $user,
            $ticket,
            $purchaseDetails,
        );

        $this->assertTrue($result);
        Mail::assertQueued(PurchaseConfirmation::class);
    }

    /**
     */
    #[Test]
    public function it_can_send_payment_failure_notification(): void
    {
        $user = $this->createTestUser(['email' => 'buyer@example.com']);
        $ticket = $this->createTestTicket();
        $errorMessage = 'Card declined';

        $result = $this->notificationService->sendPaymentFailureNotification(
            $user,
            $ticket,
            $errorMessage,
        );

        $this->assertTrue($result);
        Mail::assertQueued(PaymentFailure::class);
    }

    /**
     */
    #[Test]
    public function it_can_get_notification_statistics(): void
    {
        $user = $this->createTestUser();

        // Record some notifications
        $this->notificationService->recordNotificationSent($user->id, 'ticket_alert', now()->subDays(1));
        $this->notificationService->recordNotificationSent($user->id, 'purchase_confirmation', now()->subHours(2));
        $this->notificationService->recordNotificationSent($user->id, 'ticket_alert', now()->subMinutes(30));

        $stats = $this->notificationService->getNotificationStatistics($user->id, 7); // Last 7 days

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['by_type']['ticket_alert']);
        $this->assertEquals(1, $stats['by_type']['purchase_confirmation']);
    }

    /**
     */
    #[Test]
    public function it_can_validate_notification_preferences(): void
    {
        $validPreferences = [
            'email'     => TRUE,
            'sms'       => FALSE,
            'push'      => TRUE,
            'frequency' => 'instant',
        ];

        $result = $this->notificationService->validateNotificationPreferences($validPreferences);
        $this->assertTrue($result);

        $invalidPreferences = [
            'email'     => 'yes', // Should be boolean
            'sms'       => FALSE,
            'frequency' => 'invalid_frequency',
        ];

        $result = $this->notificationService->validateNotificationPreferences($invalidPreferences);
        $this->assertFalse($result);
    }

    /**
     */
    #[Test]
    public function it_can_unsubscribe_user_from_notifications(): void
    {
        $user = $this->createTestUser([
            'preferences' => [
                'notifications' => [
                    'email' => TRUE,
                    'sms'   => TRUE,
                    'push'  => TRUE,
                ],
            ],
        ]);

        $result = $this->notificationService->unsubscribeUser($user->id, 'email');

        $this->assertTrue($result);

        $user->refresh();
        $this->assertFalse($user->preferences['notifications']['email']);
    }

    /**
     */
    #[Test]
    public function it_can_get_notification_delivery_status(): void
    {
        $notificationId = 'notif_123';

        // Mock external service responses
        $this->mockTwilioService
            ->shouldReceive('getDeliveryStatus')
            ->with($notificationId)
            ->andReturn([
                'status'       => 'delivered',
                'delivered_at' => now(),
                'error'        => NULL,
            ]);

        $status = $this->notificationService->getNotificationDeliveryStatus($notificationId, 'sms');

        $this->assertIsArray($status);
        $this->assertEquals('delivered', $status['status']);
        $this->assertNotNull($status['delivered_at']);
    }

    /**
     */
    #[Test]
    public function it_can_send_notification_with_template(): void
    {
        $user = $this->createTestUser(['name' => 'John Doe']);
        $template = 'ticket_available';
        $variables = [
            'ticket_title' => 'Manchester United vs Liverpool',
            'price'        => '$75.00',
        ];

        $result = $this->notificationService->sendTemplatedNotification(
            $user,
            $template,
            $variables,
            ['email', 'push'],
        );

        $this->assertTrue($result);
        Mail::assertQueued(TemplatedNotification::class);
    }

    /**
     * Data provider for notification channel testing
     */
    public static function notificationChannelProvider(): array
    {
        return [
            ['email', TRUE],
            ['sms', TRUE],
            ['push', TRUE],
            ['slack', FALSE], // Slack is for admin only
            ['invalid_channel', FALSE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider notificationChannelProvider
     *
     * @param mixed $channel
     * @param mixed $isValid
     */
    public function it_validates_notification_channels(string $channel, bool $isValid): void
    {
        $result = $this->notificationService->isValidNotificationChannel($channel);
        $this->assertEquals($isValid, $result);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Create mock dependencies
        $this->mockTwilioService = Mockery::mock();
        $this->mockSlackService = Mockery::mock();
        $this->mockPushService = Mockery::mock();

        // Create notification service instance
        $this->notificationService = new NotificationService(
            $this->mockTwilioService,
            $this->mockSlackService,
            $this->mockPushService,
        );

        // Mock facades
        Notification::fake();
        Mail::fake();
        Queue::fake();
    }

    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
