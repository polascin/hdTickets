<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketPurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;

    private User $agentUser;

    private User $adminUser;

    private Ticket $ticket;

    #[Test]
    public function customer_with_active_subscription_can_complete_purchase_workflow(): void
    {
        // Create active subscription
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        // Step 1: View purchase page
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase', $this->ticket));

        $response->assertOk()
            ->assertViewIs('tickets.purchase')
            ->assertViewHas('ticket', $this->ticket)
            ->assertViewHas('user', $this->customerUser)
            ->assertViewHas('eligibilityInfo');

        $eligibilityInfo = $response->viewData('eligibilityInfo');
        $this->assertTrue($eligibilityInfo['can_purchase']);

        // Step 2: Submit purchase request
        $purchaseData = [
            'quantity'         => 2,
            'seat_preferences' => [
                'section'   => 'VIP',
                'row'       => 'A',
                'seat_type' => 'premium',
            ],
            'special_requests' => 'Aisle seats preferred',
            'accept_terms'     => TRUE,
            'confirm_purchase' => TRUE,
        ];

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), $purchaseData);

        $response->assertOk()
            ->assertJson([
                'success' => TRUE,
                'message' => 'Purchase completed successfully!',
            ]);

        // Step 3: Verify purchase was created
        $purchase = TicketPurchase::where('user_id', $this->customerUser->id)->first();

        $this->assertNotNull($purchase);
        $this->assertEquals($this->ticket->id, $purchase->ticket_id);
        $this->assertEquals(2, $purchase->quantity);
        $this->assertEquals(100.00, $purchase->unit_price);
        $this->assertEquals(200.00, $purchase->subtotal);
        $this->assertGreaterThan(200.00, $purchase->total_amount);
        $this->assertEquals('pending', $purchase->status);
        $this->assertEquals('VIP', $purchase->seat_preferences['section']);
        $this->assertEquals('Aisle seats preferred', $purchase->special_requests);

        // Step 4: Navigate to success page
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase-success', $purchase));

        $response->assertOk()
            ->assertViewIs('tickets.purchase-success')
            ->assertViewHas('purchase', $purchase);

        // Step 5: Check purchase history
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase-history'));

        $response->assertOk()
            ->assertViewIs('tickets.purchase-history')
            ->assertSee($purchase->purchase_id)
            ->assertSee('Championship Game');
    }

    #[Test]
    public function customer_without_subscription_is_redirected_to_subscription_plans(): void
    {
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase', $this->ticket));

        $response->assertOk()
            ->assertViewIs('tickets.purchase');

        $eligibilityInfo = $response->viewData('eligibilityInfo');
        $this->assertFalse($eligibilityInfo['can_purchase']);
        $this->assertContains('Active subscription required', $eligibilityInfo['reasons']);

        // Attempt to purchase should fail
        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 1,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => FALSE,
                'reasons' => ['Active subscription required'],
            ]);
    }

    #[Test]
    public function customer_exceeding_ticket_limit_receives_proper_error(): void
    {
        // Create subscription with low limit
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 3,
        ]);

        // Create existing purchases that consume the limit
        TicketPurchase::create([
            'user_id'      => $this->customerUser->id,
            'ticket_id'    => $this->ticket->id,
            'purchase_id'  => 'existing_purchase',
            'quantity'     => 2,
            'unit_price'   => 100.00,
            'total_amount' => 200.00,
            'status'       => 'confirmed',
            'created_at'   => now(),
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 2,  // Would exceed limit (2 + 2 > 3)
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => FALSE,
                'reasons' => ['Would exceed monthly ticket limit'],
            ]);

        // Verify user info shows current usage
        $responseData = $response->json();
        $this->assertEquals(3, $responseData['user_info']['ticket_limit']);
        $this->assertEquals(2, $responseData['user_info']['monthly_usage']);
        $this->assertEquals(1, $responseData['user_info']['remaining_tickets']);
    }

    #[Test]
    public function agent_can_purchase_unlimited_tickets(): void
    {
        $response = $this->actingAs($this->agentUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 50,  // Large quantity
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => TRUE,
                'message' => 'Purchase completed successfully!',
            ]);

        $purchase = TicketPurchase::where('user_id', $this->agentUser->id)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(50, $purchase->quantity);
    }

    #[Test]
    public function admin_can_purchase_unlimited_tickets(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 100,  // Very large quantity
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => TRUE,
                'message' => 'Purchase completed successfully!',
            ]);

        $purchase = TicketPurchase::where('user_id', $this->adminUser->id)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(100, $purchase->quantity);
    }

    #[Test]
    public function purchase_fails_for_unavailable_ticket(): void
    {
        $unavailableTicket = $this->createTestTicket(['is_available' => FALSE]);

        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $unavailableTicket), [
                'quantity'         => 1,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => FALSE,
                'reasons' => ['Ticket is not available'],
            ]);
    }

    #[Test]
    public function purchase_fails_when_quantity_exceeds_availability(): void
    {
        $limitedTicket = $this->createTestTicket([
            'available_quantity' => 3,
            'is_available'       => TRUE,
        ]);

        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $limitedTicket), [
                'quantity'         => 5,  // More than available
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => FALSE,
                'reasons' => ['Not enough tickets available'],
            ]);
    }

    #[Test]
    public function purchase_history_shows_correct_information(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        // Create multiple purchases
        $purchases = [];
        for ($i = 0; $i < 3; $i++) {
            $purchase = TicketPurchase::create([
                'user_id'      => $this->customerUser->id,
                'ticket_id'    => $this->ticket->id,
                'purchase_id'  => 'history_test_' . $i,
                'quantity'     => $i + 1,
                'unit_price'   => 100.00,
                'subtotal'     => ($i + 1) * 100.00,
                'total_amount' => (($i + 1) * 100.00) + 5.50, // Include fees
                'status'       => $i === 0 ? 'pending' : 'confirmed',
                'created_at'   => now()->subHours($i),
            ]);
            $purchases[] = $purchase;
        }

        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase-history'));

        $response->assertOk()
            ->assertViewIs('tickets.purchase-history')
            ->assertViewHas('purchases');

        // Check that all purchases are shown
        foreach ($purchases as $purchase) {
            $response->assertSee($purchase->purchase_id)
                ->assertSee($purchase->status);
        }

        // Check filtering by status
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase-history', ['status' => 'pending']));

        $response->assertOk()
            ->assertSee('history_test_0')
            ->assertDontSee('history_test_1')
            ->assertDontSee('history_test_2');
    }

    #[Test]
    public function purchase_failure_shows_appropriate_error_page(): void
    {
        $response = $this->actingAs($this->customerUser)
            ->get(route('tickets.purchase-failed', [
                'ticket'            => $this->ticket->id,
                'errorMessage'      => 'Payment processing failed',
                'attemptedQuantity' => 2,
            ]));

        $response->assertOk()
            ->assertViewIs('tickets.purchase-failed')
            ->assertSee('Payment processing failed')
            ->assertSee('Championship Game');
    }

    #[Test]
    public function new_customer_within_free_access_period_can_purchase(): void
    {
        $newCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(3), // Within 7-day free period
        ]);

        config(['subscription.free_access_days' => 7]);

        $response = $this->actingAs($newCustomer)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 1,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => TRUE,
                'message' => 'Purchase completed successfully!',
            ]);

        $purchase = TicketPurchase::where('user_id', $newCustomer->id)->first();
        $this->assertNotNull($purchase);
    }

    #[Test]
    public function customer_beyond_free_access_period_cannot_purchase_without_subscription(): void
    {
        $oldCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(10), // Beyond 7-day free period
        ]);

        config(['subscription.free_access_days' => 7]);

        $response = $this->actingAs($oldCustomer)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 1,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => FALSE,
                'reasons' => ['Active subscription required'],
            ]);
    }

    #[Test]
    public function purchase_validation_requires_terms_acceptance(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 1,
                'confirm_purchase' => TRUE,
                // Missing accept_terms
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['accept_terms']);
    }

    #[Test]
    public function purchase_validation_requires_purchase_confirmation(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'     => 1,
                'accept_terms' => TRUE,
                // Missing confirm_purchase
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['confirm_purchase']);
    }

    #[Test]
    public function purchase_with_invalid_quantity_is_rejected(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 0,  // Invalid quantity
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    #[Test]
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('tickets.purchase', $this->ticket));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function purchase_includes_proper_fee_calculation(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 2,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertOk();

        $purchase = TicketPurchase::where('user_id', $this->customerUser->id)->first();

        // Base amount: 2 × $100 = $200
        // Processing fee: 3% of $200 = $6.00
        // Service fee: $2.50
        // Total: $200 + $6.00 + $2.50 = $208.50

        $this->assertEquals(200.00, $purchase->subtotal);
        $this->assertEquals(208.50, $purchase->total_amount);
    }

    #[Test]
    public function user_can_cancel_pending_purchase(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        // Create a purchase first
        $response = $this->actingAs($this->customerUser)
            ->postJson(route('tickets.purchase', $this->ticket), [
                'quantity'         => 1,
                'accept_terms'     => TRUE,
                'confirm_purchase' => TRUE,
            ]);

        $response->assertOk();

        $purchase = TicketPurchase::where('user_id', $this->customerUser->id)->first();
        $this->assertEquals('pending', $purchase->status);

        // Cancel the purchase
        $response = $this->actingAs($this->customerUser)
            ->patchJson(route('tickets.purchase.cancel', $purchase));

        $response->assertOk()
            ->assertJson([
                'success' => TRUE,
                'message' => 'Purchase cancelled successfully',
            ]);

        $purchase->refresh();
        $this->assertEquals('cancelled', $purchase->status);
        $this->assertNotNull($purchase->cancelled_at);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerUser = $this->createTestUser(['role' => 'customer']);
        $this->agentUser = $this->createTestUser(['role' => 'agent']);
        $this->adminUser = $this->createTestUser(['role' => 'admin']);

        $this->ticket = $this->createTestTicket([
            'price'              => 100.00,
            'available_quantity' => 10,
            'is_available'       => TRUE,
            'title'              => 'Championship Game',
            'event_date'         => now()->addMonth(),
            'venue'              => 'Great Stadium',
        ]);

        Event::fake();
        Mail::fake();
    }
}
