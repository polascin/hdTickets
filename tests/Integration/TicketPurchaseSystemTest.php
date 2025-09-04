<?php

namespace Tests\Integration;

use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\TicketPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPurchaseSystemTest extends TestCase
{
    use RefreshDatabase;

    public function testTicketPurchaseSystemIsWorkingCorrectly(): void
    {
        // Create a customer with active subscription
        $customer = User::factory()->create(['role' => 'customer']);
        
        UserSubscription::create([
            'user_id' => $customer->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'ticket_limit' => 100
        ]);

        // Create a test ticket
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'available_quantity' => 10,
            'is_available' => true
        ]);

        // Get the purchase service
        $purchaseService = app(TicketPurchaseService::class);

        // Check eligibility
        $eligibility = $purchaseService->checkPurchaseEligibility($customer, $ticket, 2);

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertEmpty($eligibility['reasons']);

        // Create purchase
        $purchase = $purchaseService->createPurchase($customer, $ticket, [
            'quantity' => 2,
            'seat_preferences' => [
                'section' => 'VIP'
            ]
        ]);

        $this->assertInstanceOf(TicketPurchase::class, $purchase);
        $this->assertEquals($customer->id, $purchase->user_id);
        $this->assertEquals($ticket->id, $purchase->ticket_id);
        $this->assertEquals(2, $purchase->quantity);
        $this->assertEquals(100.00, $purchase->unit_price);
        $this->assertEquals('pending', $purchase->status);
    }

    public function testAgentHasUnlimitedAccess(): void
    {
        // Create an agent
        $agent = User::factory()->create(['role' => 'agent']);
        
        // Create a test ticket
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'available_quantity' => 10,
            'is_available' => true
        ]);

        // Get the purchase service
        $purchaseService = app(TicketPurchaseService::class);

        // Check eligibility for large quantity
        $eligibility = $purchaseService->checkPurchaseEligibility($agent, $ticket, 50);

        $this->assertTrue($eligibility['can_purchase']);
        $this->assertNull($eligibility['user_info']['ticket_limit']);
    }

    public function testCustomerWithoutSubscriptionIsDenied(): void
    {
        // Create a customer without subscription
        $customer = User::factory()->create(['role' => 'customer', 'created_at' => now()->subDays(10)]);
        
        // Create a test ticket
        $ticket = Ticket::factory()->create([
            'price' => 100.00,
            'available_quantity' => 10,
            'is_available' => true
        ]);

        // Get the purchase service
        $purchaseService = app(TicketPurchaseService::class);

        // Check eligibility
        $eligibility = $purchaseService->checkPurchaseEligibility($customer, $ticket, 1);

        $this->assertFalse($eligibility['can_purchase']);
        $this->assertContains('Active subscription required', $eligibility['reasons']);
    }
}
