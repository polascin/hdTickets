<?php declare(strict_types=1);

namespace Tests\Feature\PayPal;

use App\Models\PurchaseAttempt;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PayPal\PayPalService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class PayPalTicketPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = $this->createTestUser([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ], 'customer');

        // Create test ticket
        $this->ticket = $this->createTestTicket([
            'title' => 'Test Sports Event',
            'price' => 99.99,
            'currency' => 'USD',
            'available_quantity' => 10,
            'is_available' => true,
            'venue' => 'Test Arena',
            'location' => 'Test City, TC',
            'sport' => 'basketball',
            'event_date' => now()->addDays(30)
        ]);
    }

    /** @test */
    public function user_can_access_ticket_purchase_page(): void
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get(route('tickets.purchase', $this->ticket));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('tickets.purchase');
        $response->assertSee('PayPal');
        $response->assertSee('Credit/Debit Card');
        $response->assertSee($this->ticket->title);
    }

    /** @test */
    public function user_can_purchase_ticket_with_stripe(): void
    {
        // Arrange
        $this->mockPaymentServices();

        $purchaseData = [
            'quantity' => 2,
            'payment_method' => 'stripe',
            'seat_preferences' => [
                'section' => 'Lower level',
                'row' => 'A',
                'seat_type' => 'standard'
            ],
            'special_requests' => 'Wheelchair accessible seats please',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('purchase_attempts', [
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 2,
            'payment_method' => 'stripe',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function user_can_purchase_ticket_with_paypal(): void
    {
        // Arrange
        $this->mockPaymentServices();

        $purchaseData = [
            'quantity' => 1,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'seat_preferences' => [
                'section' => 'Upper level',
                'seat_type' => 'premium'
            ],
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('purchase_attempts', [
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 1,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function paypal_order_creation_stores_attempt(): void
    {
        // Arrange
        $this->mockPaymentServices();

        $purchaseData = [
            'quantity' => 3,
            'payment_method' => 'paypal',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);

        $attempt = PurchaseAttempt::where('user_id', $this->user->id)
            ->where('ticket_id', $this->ticket->id)
            ->first();

        $this->assertNotNull($attempt);
        $this->assertEquals('paypal', $attempt->payment_method);
        $this->assertEquals(3, $attempt->quantity);
    }

    /** @test */
    public function paypal_payment_capture_completes_purchase(): void
    {
        // Arrange
        $this->mockPaymentServices();

        // Create a purchase attempt
        $attempt = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 2,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price * 2 + 6.48, // Including fees
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'status' => 'pending',
            'metadata' => [
                'seat_preferences' => ['section' => 'Lower level']
            ]
        ]);

        $captureData = [
            'paypal_order_id' => 'ORDER123456',
            'paypal_capture_id' => 'CAPTURE123456'
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase.paypal.capture', $this->ticket), $captureData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment captured successfully'
        ]);

        $this->assertDatabaseHas('purchase_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
            'paypal_capture_id' => 'CAPTURE123456'
        ]);
    }

    /** @test */
    public function paypal_webhook_handles_payment_capture_completed(): void
    {
        // Arrange
        $attempt = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 1,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price + 3.24,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'status' => 'pending'
        ]);

        $webhookPayload = [
            'id' => 'WH-1234567890',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => [
                'id' => 'CAPTURE123456',
                'status' => 'COMPLETED',
                'amount' => [
                    'value' => '103.23',
                    'currency_code' => 'USD'
                ],
                'custom_id' => 'ticket_' . $this->ticket->id,
                'invoice_id' => 'HDT_' . time()
            ]
        ];

        $this->mockWebhookVerification(true);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id',
            'PAYPAL-CERT-ID' => 'test-cert-id',
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-TRANSMISSION-SIG' => 'test-signature',
            'PAYPAL-TRANSMISSION-TIME' => now()->toISOString()
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['status' => 'success']);

        // Verify audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'paypal_transaction',
            'action' => 'payment_captured'
        ]);
    }

    /** @test */
    public function paypal_webhook_handles_payment_capture_denied(): void
    {
        // Arrange
        $attempt = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 1,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price + 3.24,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'status' => 'pending'
        ]);

        $webhookPayload = [
            'id' => 'WH-1234567890',
            'event_type' => 'PAYMENT.CAPTURE.DENIED',
            'resource' => [
                'id' => 'CAPTURE123456',
                'status' => 'DECLINED',
                'status_details' => [
                    'reason' => 'INSUFFICIENT_FUNDS'
                ],
                'amount' => [
                    'value' => '103.23',
                    'currency_code' => 'USD'
                ]
            ]
        ];

        $this->mockWebhookVerification(true);

        // Act
        $response = $this->postJson(route('webhooks.paypal'), $webhookPayload, [
            'PAYPAL-TRANSMISSION-ID' => 'test-transmission-id'
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK);

        // Verify audit log was created for denied payment
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'paypal_transaction',
            'action' => 'payment_denied'
        ]);
    }

    /** @test */
    public function paypal_refund_processes_successfully(): void
    {
        // Arrange
        $this->mockPaymentServices();

        $attempt = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 1,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price + 3.24,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'paypal_capture_id' => 'CAPTURE123456',
            'status' => 'completed'
        ]);

        $refundData = [
            'amount' => 50.00,
            'reason' => 'Customer request'
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase.refund', $attempt), $refundData);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true,
            'message' => 'Refund processed successfully'
        ]);

        $this->assertDatabaseHas('purchase_attempts', [
            'id' => $attempt->id,
            'status' => 'refunded'
        ]);
    }

    /** @test */
    public function purchase_validation_prevents_invalid_data(): void
    {
        // Act & Assert - Missing required fields
        $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'payment_method' => 'paypal'
                // Missing quantity, terms acceptance
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Invalid quantity
        $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity' => 0,
                'payment_method' => 'paypal',
                'accept_terms' => true,
                'confirm_purchase' => true
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Invalid payment method
        $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity' => 1,
                'payment_method' => 'invalid_method',
                'accept_terms' => true,
                'confirm_purchase' => true
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function purchase_respects_ticket_availability(): void
    {
        // Arrange - Ticket with limited quantity
        $limitedTicket = $this->createTestTicket([
            'title' => 'Limited Availability Event',
            'price' => 150.00,
            'available_quantity' => 2,
            'is_available' => true
        ]);

        $purchaseData = [
            'quantity' => 3, // More than available
            'payment_method' => 'paypal',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $limitedTicket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function unavailable_ticket_cannot_be_purchased(): void
    {
        // Arrange - Unavailable ticket
        $unavailableTicket = $this->createTestTicket([
            'title' => 'Sold Out Event',
            'price' => 200.00,
            'is_available' => false
        ]);

        $purchaseData = [
            'quantity' => 1,
            'payment_method' => 'paypal',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $unavailableTicket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'success' => false,
            'message' => 'Ticket is not available for purchase'
        ]);
    }

    /** @test */
    public function guest_user_cannot_purchase_tickets(): void
    {
        // Arrange
        $purchaseData = [
            'quantity' => 1,
            'payment_method' => 'paypal',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // Act
        $response = $this->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        // Assert
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_view_purchase_history(): void
    {
        // Arrange
        $attempt1 = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 2,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price * 2,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'ORDER123456',
            'status' => 'completed'
        ]);

        $attempt2 = PurchaseAttempt::create([
            'user_id' => $this->user->id,
            'ticket_id' => $this->ticket->id,
            'quantity' => 1,
            'unit_price' => $this->ticket->price,
            'total_price' => $this->ticket->price,
            'payment_method' => 'stripe',
            'status' => 'completed'
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('tickets.purchase-history'));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('tickets.purchase-history');
        $response->assertSee('ORDER123456');
        $response->assertSee('paypal');
        $response->assertSee('stripe');
    }

    /** @test */
    public function user_cannot_exceed_subscription_limits(): void
    {
        // This test would check subscription limits for customer users
        // For now, we'll test the basic scenario
        
        $purchaseData = [
            'quantity' => 1,
            'payment_method' => 'paypal',
            'accept_terms' => true,
            'confirm_purchase' => true
        ];

        // If user has no subscription and free trial is expired, purchase should fail
        config(['subscription.free_access_days' => 0]);

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        // Assert - This might return different status based on business logic
        $this->assertTrue(in_array($response->status(), [
            Response::HTTP_FORBIDDEN,
            Response::HTTP_UNPROCESSABLE_ENTITY
        ]));
    }

    private function mockPaymentServices(): void
    {
        // Mock PayPal Service
        $paypalService = Mockery::mock(PayPalService::class);
        $paypalService->shouldReceive('createOrder')->andReturn([
            'order_id' => 'ORDER123456',
            'status' => 'CREATED',
            'approve_link' => 'https://sandbox.paypal.com/approve?token=ORDER123456'
        ]);
        $paypalService->shouldReceive('captureOrder')->andReturn([
            'status' => 'COMPLETED',
            'capture_id' => 'CAPTURE123456',
            'amount' => '103.23',
            'currency' => 'USD'
        ]);
        $paypalService->shouldReceive('refundOrder')->andReturn([
            'refund_id' => 'REFUND123456',
            'status' => 'COMPLETED',
            'amount' => '50.00',
            'currency' => 'USD'
        ]);
        
        $this->app->instance(PayPalService::class, $paypalService);

        // Mock Payment Service
        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('processPayPalPayment')->andReturn([
            'success' => true,
            'purchase_attempt_id' => 1,
            'redirect_url' => '/tickets/purchase-success'
        ]);
        $paymentService->shouldReceive('refundPayPalTransaction')->andReturn([
            'success' => true,
            'refund_id' => 'REFUND123456'
        ]);
        
        $this->app->instance(PaymentService::class, $paymentService);

        // Mock Audit Service
        $auditService = Mockery::mock(AuditService::class);
        $auditService->shouldReceive('logPayPalTransaction')->andReturn(null);
        $auditService->shouldReceive('logPaymentAttempt')->andReturn(null);
        $auditService->shouldReceive('logPayPalWebhook')->andReturn(null);
        
        $this->app->instance(AuditService::class, $auditService);
    }

    private function mockWebhookVerification(bool $isValid): void
    {
        $paypalService = Mockery::mock(PayPalService::class);
        $paypalService->shouldReceive('verifyWebhookSignature')->andReturn($isValid);
        
        $this->app->instance(PayPalService::class, $paypalService);
    }
}