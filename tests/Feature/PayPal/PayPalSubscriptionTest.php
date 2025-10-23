<?php declare(strict_types=1);

namespace Tests\Feature\PayPal;

use App\Models\PaymentPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\AuditService;
use App\Services\PayPal\PayPalService;
use App\Services\PayPal\PayPalSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PayPalSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PaymentPlan $monthlyPlan;

    private PaymentPlan $annualPlan;

    #[Test]
    public function user_can_access_subscription_checkout_page(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get(route('subscriptions.checkout'));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('subscriptions.checkout');
        $response->assertSee('PayPal');
        $response->assertSee('Credit/Debit Card');
    }

    #[Test]
    public function user_can_create_paypal_subscription_via_api(): void
    {
        // Arrange
        $this->mockPayPalServices();

        $subscriptionData = [
            'plan_type'    => 'monthly',
            'billing_info' => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'email'      => 'john.doe@example.com',
                'address'    => '123 Main St',
                'city'       => 'New York',
                'state'      => 'NY',
                'postalCode' => '10001',
                'country'    => 'US',
            ],
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.create'), array_merge($subscriptionData, [
                'payment_method' => 'paypal',
            ]));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => TRUE,
            'message' => 'Subscription created successfully',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id'         => $this->user->id,
            'payment_plan_id' => $this->monthlyPlan->id,
            'payment_method'  => 'paypal',
            'status'          => 'pending',
        ]);
    }

    #[Test]
    public function paypal_subscription_approval_creates_subscription(): void
    {
        // Arrange
        $this->mockPayPalServices();

        $approvalData = [
            'subscription_id' => 'I-BW452GLLEP1G',
            'plan_type'       => 'monthly',
            'billing_info'    => [
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'email'     => 'john.doe@example.com',
            ],
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.paypal.approve'), $approvalData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => TRUE,
            'message' => 'Subscription approved successfully',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id'                => $this->user->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
        ]);
    }

    #[Test]
    public function paypal_subscription_activation_updates_status(): void
    {
        // Arrange
        $this->mockPayPalServices();

        // Create a pending subscription
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'pending',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        $activationData = [
            'subscription_id' => 'I-BW452GLLEP1G',
            'billing_info'    => [
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'email'     => 'john.doe@example.com',
            ],
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.paypal.activate'), $activationData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => TRUE,
            'message' => 'Subscription activated successfully',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'id'                     => $subscription->id,
            'status'                 => 'active',
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
        ]);
    }

    #[Test]
    public function user_can_cancel_paypal_subscription(): void
    {
        // Arrange
        $this->mockPayPalServices();

        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.cancel'));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => TRUE,
            'message' => 'Subscription cancelled successfully',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'id'     => $subscription->id,
            'status' => 'cancelled',
        ]);
    }

    #[Test]
    public function paypal_webhook_handles_subscription_created(): void
    {
        // Arrange
        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.CREATED',
            'resource'   => [
                'id'      => 'I-BW452GLLEP1G',
                'status'  => 'APPROVAL_PENDING',
                'plan_id' => 'P-5ML4271244454362WXNWU5NQ',
            ],
        ];

        // Mock webhook signature verification
        $this->mockWebhookVerification(TRUE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID'   => 'test-transmission-id',
            'PAYPAL-CERT-ID'           => 'test-cert-id',
            'PAYPAL-AUTH-ALGO'         => 'SHA256withRSA',
            'PAYPAL-TRANSMISSION-SIG'  => 'test-signature',
            'PAYPAL-TRANSMISSION-TIME' => now()->toISOString(),
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['status' => 'success']);
    }

    #[Test]
    public function paypal_webhook_handles_subscription_activated(): void
    {
        // Arrange
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'pending',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
            'resource'   => [
                'id'     => 'I-BW452GLLEP1G',
                'status' => 'ACTIVE',
            ],
        ];

        $this->mockWebhookVerification(TRUE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('user_subscriptions', [
            'id'     => $subscription->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function paypal_webhook_handles_subscription_cancelled(): void
    {
        // Arrange
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.CANCELLED',
            'resource'   => [
                'id'     => 'I-BW452GLLEP1G',
                'status' => 'CANCELLED',
            ],
        ];

        $this->mockWebhookVerification(TRUE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('user_subscriptions', [
            'id'     => $subscription->id,
            'status' => 'cancelled',
        ]);
    }

    #[Test]
    public function paypal_webhook_handles_subscription_payment_completed(): void
    {
        // Arrange
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED',
            'resource'   => [
                'billing_agreement_id' => 'I-BW452GLLEP1G',
                'amount'               => [
                    'total'    => '29.99',
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->mockWebhookVerification(TRUE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('user_subscriptions', [
            'id'     => $subscription->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function paypal_webhook_handles_subscription_payment_failed(): void
    {
        // Arrange
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
            'resource'   => [
                'billing_agreement_id' => 'I-BW452GLLEP1G',
                'failure_reason'       => 'INSUFFICIENT_FUNDS',
                'amount'               => [
                    'total'    => '29.99',
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->mockWebhookVerification(TRUE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);

        // Check that audit log was created for failed payment
        $this->assertDatabaseHas('audit_logs', [
            'event_type'     => 'paypal_failed_payment',
            'auditable_id'   => $subscription->user_id,
            'auditable_type' => User::class,
        ]);
    }

    #[Test]
    public function invalid_webhook_signature_is_rejected(): void
    {
        // Arrange
        $webhookPayload = [
            'id'         => 'WH-1234567890',
            'event_type' => 'BILLING.SUBSCRIPTION.CREATED',
        ];

        $this->mockWebhookVerification(FALSE);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'invalid-transmission-id',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function user_can_view_current_subscription(): void
    {
        // Arrange
        $subscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-BW452GLLEP1G',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->getJson(route('api.subscriptions.current'));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success'      => TRUE,
            'subscription' => [
                'id'             => $subscription->id,
                'status'         => 'active',
                'payment_method' => 'paypal',
                'plan_name'      => $this->monthlyPlan->name,
            ],
        ]);
    }

    #[Test]
    public function user_can_view_subscription_history(): void
    {
        // Arrange
        $oldSubscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-OLD123456',
            'payment_method'         => 'paypal',
            'status'                 => 'cancelled',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now()->subMonths(2),
            'ends_at'                => now()->subMonth(),
        ]);

        $currentSubscription = UserSubscription::create([
            'user_id'                => $this->user->id,
            'payment_plan_id'        => $this->monthlyPlan->id,
            'paypal_subscription_id' => 'I-CURRENT123',
            'payment_method'         => 'paypal',
            'status'                 => 'active',
            'amount_paid'            => $this->monthlyPlan->price,
            'starts_at'              => now(),
            'ends_at'                => now()->addMonth(),
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->getJson(route('api.subscriptions.history'));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'subscriptions' => [
                '*' => [
                    'id',
                    'status',
                    'payment_method',
                    'plan_name',
                    'amount',
                    'created_at',
                ],
            ],
        ]);

        $responseData = $response->json();
        $this->assertCount(2, $responseData['subscriptions']);
    }

    #[Test]
    public function subscription_requires_authentication(): void
    {
        // Act & Assert
        $this->postJson(route('api.subscriptions.create'), [
            'payment_method' => 'paypal',
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->getJson(route('api.subscriptions.current'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->getJson(route('api.subscriptions.history'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function subscription_validation_prevents_invalid_data(): void
    {
        // Act & Assert - Missing plan type
        $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.paypal.approve'), [
                'subscription_id' => 'I-TEST123456',
                // Missing plan_type and billing_info
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Invalid plan type
        $this->actingAs($this->user)
            ->postJson(route('api.subscriptions.paypal.approve'), [
                'subscription_id' => 'I-TEST123456',
                'plan_type'       => 'invalid_plan',
                'billing_info'    => [],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = $this->createTestUser([
            'email' => 'test@example.com',
            'name'  => 'Test User',
        ], 'customer');

        // Create test payment plans
        $this->monthlyPlan = PaymentPlan::create([
            'name'                  => 'Monthly Plan',
            'slug'                  => 'monthly',
            'price'                 => 29.99,
            'currency'              => 'USD',
            'interval'              => 'monthly',
            'interval_days'         => 30,
            'max_tickets_per_month' => 100,
            'is_active'             => TRUE,
            'features'              => [
                'ticket_limit'  => 100,
                'price_alerts'  => TRUE,
                'notifications' => TRUE,
            ],
        ]);

        $this->annualPlan = PaymentPlan::create([
            'name'                  => 'Annual Plan',
            'slug'                  => 'annual',
            'price'                 => 299.99,
            'currency'              => 'USD',
            'interval'              => 'annual',
            'interval_days'         => 365,
            'max_tickets_per_month' => 100,
            'is_active'             => TRUE,
            'features'              => [
                'ticket_limit'     => 100,
                'price_alerts'     => TRUE,
                'notifications'    => TRUE,
                'priority_support' => TRUE,
            ],
        ]);
    }

    private function mockPayPalServices(): void
    {
        // Mock PayPal Service
        $paypalService = Mockery::mock(PayPalService::class);
        $paypalService->shouldReceive('createSubscription')->andReturn([
            'subscription_id' => 'I-BW452GLLEP1G',
            'status'          => 'APPROVAL_PENDING',
            'approve_link'    => 'https://sandbox.paypal.com/approve?subscription_id=I-BW452GLLEP1G',
        ]);
        $paypalService->shouldReceive('cancelSubscription')->andReturn(TRUE);

        $this->app->instance(PayPalService::class, $paypalService);

        // Mock PayPal Subscription Service
        $subscriptionService = Mockery::mock(PayPalSubscriptionService::class);
        $subscriptionService->shouldReceive('activateSubscription')->andReturn(TRUE);
        $subscriptionService->shouldReceive('processRenewal')->andReturn(new UserSubscription());

        $this->app->instance(PayPalSubscriptionService::class, $subscriptionService);

        // Mock Audit Service
        $auditService = Mockery::mock(AuditService::class);
        $auditService->shouldReceive('logPayPalSubscription')->andReturn(NULL);
        $auditService->shouldReceive('logPayPalWebhook')->andReturn(NULL);
        $auditService->shouldReceive('logFailedPayment')->andReturn(NULL);

        $this->app->instance(AuditService::class, $auditService);
    }

    private function mockWebhookVerification(bool $isValid): void
    {
        $paypalService = Mockery::mock(PayPalService::class);
        $paypalService->shouldReceive('verifyWebhookSignature')->andReturn($isValid);

        $this->app->instance(PayPalService::class, $paypalService);
    }
}
