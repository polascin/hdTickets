<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\TicketPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class TicketPurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketPurchaseService $purchaseService;

    private User $customerUser;

    private User $agentUser;

    private User $adminUser;

    private Ticket $ticket;

    /**
     * @test
     */
    public function it_can_check_purchase_eligibility_for_customer_with_active_subscription(): void
    {
        // Create active subscription
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->customerUser,
            $this->ticket,
            2,
        );

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertEmpty($eligibility['reasons']);
        $this->assertEquals(100, $eligibility['user_info']['ticket_limit']);
        $this->assertEquals(0, $eligibility['user_info']['monthly_usage']);
        $this->assertEquals(100, $eligibility['user_info']['remaining_tickets']);
    }

    /**
     * @test
     */
    public function it_denies_purchase_for_customer_without_active_subscription(): void
    {
        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->customerUser,
            $this->ticket,
            1,
        );

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Active subscription required', $eligibility['reasons']);
    }

    /**
     * @test
     */
    public function it_denies_purchase_for_customer_exceeding_ticket_limit(): void
    {
        // Create subscription with low limit
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 5,
        ]);

        // Create existing purchases for this month
        for ($i = 0; $i < 4; $i++) {
            TicketPurchase::create([
                'user_id'      => $this->customerUser->id,
                'ticket_id'    => $this->ticket->id,
                'purchase_id'  => 'test_' . $i,
                'quantity'     => 1,
                'unit_price'   => 100.00,
                'total_amount' => 100.00,
                'status'       => 'confirmed',
                'created_at'   => now(),
            ]);
        }

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->customerUser,
            $this->ticket,
            2,  // This would exceed the limit (4 + 2 > 5)
        );

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Would exceed monthly ticket limit', $eligibility['reasons']);
    }

    /**
     * @test
     */
    public function it_allows_unlimited_purchases_for_agent(): void
    {
        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->agentUser,
            $this->ticket,
            100,  // Large quantity
        );

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertEmpty($eligibility['reasons']);
        $this->assertNull($eligibility['user_info']['ticket_limit']);
    }

    /**
     * @test
     */
    public function it_allows_unlimited_purchases_for_admin(): void
    {
        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->adminUser,
            $this->ticket,
            100,  // Large quantity
        );

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertEmpty($eligibility['reasons']);
        $this->assertNull($eligibility['user_info']['ticket_limit']);
    }

    /**
     * @test
     */
    public function it_denies_purchase_for_unavailable_ticket(): void
    {
        $unavailableTicket = $this->createTestTicket(['is_available' => FALSE]);

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->agentUser,
            $unavailableTicket,
            1,
        );

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Ticket is not available', $eligibility['reasons']);
    }

    /**
     * @test
     */
    public function it_denies_purchase_when_quantity_exceeds_availability(): void
    {
        $limitedTicket = $this->createTestTicket([
            'available_quantity' => 3,
            'is_available'       => TRUE,
        ]);

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $this->agentUser,
            $limitedTicket,
            5,  // More than available
        );

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Not enough tickets available', $eligibility['reasons']);
    }

    /**
     * @test
     */
    public function it_can_create_successful_purchase(): void
    {
        // Give customer active subscription
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $purchaseData = [
            'quantity'         => 2,
            'seat_preferences' => [
                'section'   => 'VIP',
                'row'       => 'A',
                'seat_type' => 'premium',
            ],
            'special_requests' => 'Aisle seats preferred',
        ];

        $purchase = $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            $purchaseData,
        );

        $this->assertInstanceOf(TicketPurchase::class, $purchase);
        $this->assertEquals($this->customerUser->id, $purchase->user_id);
        $this->assertEquals($this->ticket->id, $purchase->ticket_id);
        $this->assertEquals(2, $purchase->quantity);
        $this->assertEquals(100.00, $purchase->unit_price);
        $this->assertEquals(200.00, $purchase->subtotal);
        $this->assertGreaterThan(200.00, $purchase->total_amount); // Should include fees
        $this->assertEquals('pending', $purchase->status);
        $this->assertNotEmpty($purchase->purchase_id);
        $this->assertEquals('VIP', $purchase->seat_preferences['section']);
        $this->assertEquals('Aisle seats preferred', $purchase->special_requests);
    }

    /**
     * @test
     */
    public function it_calculates_fees_correctly(): void
    {
        $baseAmount = 100.00;
        $fees = $this->purchaseService->calculateFees($baseAmount);

        $this->assertIsArray($fees);
        $this->assertArrayHasKey('processing_fee', $fees);
        $this->assertArrayHasKey('service_fee', $fees);
        $this->assertArrayHasKey('total_fees', $fees);

        // Processing fee should be 3% of base amount
        $this->assertEquals(3.00, $fees['processing_fee']);

        // Service fee should be fixed $2.50
        $this->assertEquals(2.50, $fees['service_fee']);

        // Total fees
        $this->assertEquals(5.50, $fees['total_fees']);
    }

    /**
     * @test
     */
    public function it_generates_unique_purchase_ids(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $purchase1 = $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => 1],
        );

        $purchase2 = $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => 1],
        );

        $this->assertNotEquals($purchase1->purchase_id, $purchase2->purchase_id);
        $this->assertStringStartsWith('PUR-', $purchase1->purchase_id);
        $this->assertStringStartsWith('PUR-', $purchase2->purchase_id);
    }

    /**
     * @test
     */
    public function it_can_confirm_purchase(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $purchase = $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => 1],
        );

        $this->assertEquals('pending', $purchase->status);

        $confirmedPurchase = $this->purchaseService->confirmPurchase($purchase);

        $this->assertEquals('confirmed', $confirmedPurchase->status);
        $this->assertNotNull($confirmedPurchase->confirmed_at);
    }

    /**
     * @test
     */
    public function it_can_cancel_purchase(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $purchase = $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => 1],
        );

        $cancelledPurchase = $this->purchaseService->cancelPurchase($purchase, 'User requested cancellation');

        $this->assertEquals('cancelled', $cancelledPurchase->status);
        $this->assertNotNull($cancelledPurchase->cancelled_at);
        $this->assertEquals('User requested cancellation', $cancelledPurchase->cancellation_reason);
    }

    /**
     * @test
     */
    public function it_can_get_user_monthly_ticket_usage(): void
    {
        // Create purchases for current month
        $currentMonthPurchases = 3;
        for ($i = 0; $i < $currentMonthPurchases; $i++) {
            TicketPurchase::create([
                'user_id'      => $this->customerUser->id,
                'ticket_id'    => $this->ticket->id,
                'purchase_id'  => 'current_' . $i,
                'quantity'     => 2,
                'unit_price'   => 100.00,
                'total_amount' => 200.00,
                'status'       => 'confirmed',
                'created_at'   => now(),
            ]);
        }

        // Create purchases for previous month (should not count)
        TicketPurchase::create([
            'user_id'      => $this->customerUser->id,
            'ticket_id'    => $this->ticket->id,
            'purchase_id'  => 'previous_1',
            'quantity'     => 5,
            'unit_price'   => 100.00,
            'total_amount' => 500.00,
            'status'       => 'confirmed',
            'created_at'   => now()->subMonth(),
        ]);

        $usage = $this->purchaseService->getUserMonthlyTicketUsage($this->customerUser);

        // Should be 3 purchases × 2 tickets each = 6 tickets
        $this->assertEquals(6, $usage);
    }

    /**
     * @test
     */
    public function it_handles_edge_case_of_zero_quantity_purchase(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => 0],
        );
    }

    /**
     * @test
     */
    public function it_handles_negative_quantity_purchase(): void
    {
        UserSubscription::create([
            'user_id'      => $this->customerUser->id,
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addMonth(),
            'ticket_limit' => 100,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        $this->purchaseService->createPurchase(
            $this->customerUser,
            $this->ticket,
            ['quantity' => -1],
        );
    }

    /**
     * @test
     */
    public function it_respects_free_access_period_for_new_customers(): void
    {
        // Customer within free access period (created recently)
        $newCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(3), // Within 7-day free period
        ]);

        // Mock config for free access days
        config(['subscription.free_access_days' => 7]);

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $newCustomer,
            $this->ticket,
            1,
        );

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertEmpty($eligibility['reasons']);
        $this->assertArrayHasKey('free_access_remaining', $eligibility['user_info']);
    }

    /**
     * @test
     */
    public function it_denies_purchase_after_free_access_period_expires(): void
    {
        // Customer beyond free access period
        $oldCustomer = $this->createTestUser([
            'role'       => 'customer',
            'created_at' => now()->subDays(10), // Beyond 7-day free period
        ]);

        config(['subscription.free_access_days' => 7]);

        $eligibility = $this->purchaseService->checkPurchaseEligibility(
            $oldCustomer,
            $this->ticket,
            1,
        );

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Active subscription required', $eligibility['reasons']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseService = app(TicketPurchaseService::class);

        // Create test users
        $this->customerUser = $this->createTestUser(['role' => 'customer']);
        $this->agentUser = $this->createTestUser(['role' => 'agent']);
        $this->adminUser = $this->createTestUser(['role' => 'admin']);

        // Create test ticket
        $this->ticket = $this->createTestTicket([
            'price'              => 100.00,
            'available_quantity' => 10,
            'is_available'       => TRUE,
        ]);

        Event::fake();
    }
}
