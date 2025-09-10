<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     */
    #[Test]
    public function it_can_create_a_ticket_purchase(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        $purchase = TicketPurchase::create([
            'user_id'          => $user->id,
            'ticket_id'        => $ticket->id,
            'purchase_id'      => 'PUR-TEST-123',
            'quantity'         => 2,
            'unit_price'       => 100.00,
            'subtotal'         => 200.00,
            'processing_fee'   => 6.00,
            'service_fee'      => 2.50,
            'total_amount'     => 208.50,
            'status'           => 'pending',
            'seat_preferences' => [
                'section'   => 'VIP',
                'row'       => 'A',
                'seat_type' => 'premium',
            ],
            'special_requests' => 'Aisle seats preferred',
        ]);

        $this->assertInstanceOf(TicketPurchase::class, $purchase);
        $this->assertEquals($user->id, $purchase->user_id);
        $this->assertEquals($ticket->id, $purchase->ticket_id);
        $this->assertEquals('PUR-TEST-123', $purchase->purchase_id);
        $this->assertEquals(2, $purchase->quantity);
        $this->assertEquals(100.00, $purchase->unit_price);
        $this->assertEquals(200.00, $purchase->subtotal);
        $this->assertEquals(208.50, $purchase->total_amount);
        $this->assertEquals('pending', $purchase->status);
        $this->assertEquals(['section' => 'VIP', 'row' => 'A', 'seat_type' => 'premium'], $purchase->seat_preferences);
        $this->assertEquals('Aisle seats preferred', $purchase->special_requests);
    }

    /**
     */
    #[Test]
    public function it_has_proper_fillable_attributes(): void
    {
        $purchase = new TicketPurchase();

        $expectedFillable = [
            'user_id', 'ticket_id', 'purchase_id', 'quantity', 'unit_price',
            'subtotal', 'processing_fee', 'service_fee', 'total_amount',
            'status', 'seat_preferences', 'special_requests', 'payment_intent_id',
            'payment_status', 'confirmed_at', 'cancelled_at', 'cancellation_reason',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $purchase->getFillable());
        }
    }

    /**
     */
    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        $purchase = TicketPurchase::create([
            'user_id'          => $user->id,
            'ticket_id'        => $ticket->id,
            'purchase_id'      => 'PUR-TEST-123',
            'quantity'         => 1,
            'unit_price'       => 100.00,
            'subtotal'         => 100.00,
            'total_amount'     => 105.50,
            'status'           => 'pending',
            'seat_preferences' => ['section' => 'A', 'row' => '1'],
            'confirmed_at'     => now(),
            'cancelled_at'     => now()->addHour(),
        ]);

        $this->assertIsArray($purchase->seat_preferences);
        $this->assertInstanceOf(Carbon::class, $purchase->confirmed_at);
        $this->assertInstanceOf(Carbon::class, $purchase->cancelled_at);
        $this->assertIsFloat($purchase->unit_price);
        $this->assertIsFloat($purchase->subtotal);
        $this->assertIsFloat($purchase->total_amount);
    }

    /**
     */
    #[Test]
    public function it_has_relationship_with_user(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        $purchase = TicketPurchase::create([
            'user_id'      => $user->id,
            'ticket_id'    => $ticket->id,
            'purchase_id'  => 'PUR-TEST-123',
            'quantity'     => 1,
            'unit_price'   => 100.00,
            'subtotal'     => 100.00,
            'total_amount' => 105.50,
            'status'       => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $purchase->user);
        $this->assertEquals($user->id, $purchase->user->id);
    }

    /**
     */
    #[Test]
    public function it_has_relationship_with_ticket(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        $purchase = TicketPurchase::create([
            'user_id'      => $user->id,
            'ticket_id'    => $ticket->id,
            'purchase_id'  => 'PUR-TEST-123',
            'quantity'     => 1,
            'unit_price'   => 100.00,
            'subtotal'     => 100.00,
            'total_amount' => 105.50,
            'status'       => 'pending',
        ]);

        $this->assertInstanceOf(Ticket::class, $purchase->ticket);
        $this->assertEquals($ticket->id, $purchase->ticket->id);
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_is_pending(): void
    {
        $purchase = $this->createTestPurchase(['status' => 'pending']);

        $this->assertTrue($purchase->isPending());
        $this->assertFalse($purchase->isConfirmed());
        $this->assertFalse($purchase->isCancelled());
        $this->assertFalse($purchase->isFailed());
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_is_confirmed(): void
    {
        $purchase = $this->createTestPurchase([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->assertTrue($purchase->isConfirmed());
        $this->assertFalse($purchase->isPending());
        $this->assertFalse($purchase->isCancelled());
        $this->assertFalse($purchase->isFailed());
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_is_cancelled(): void
    {
        $purchase = $this->createTestPurchase([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'User requested cancellation',
        ]);

        $this->assertTrue($purchase->isCancelled());
        $this->assertFalse($purchase->isPending());
        $this->assertFalse($purchase->isConfirmed());
        $this->assertFalse($purchase->isFailed());
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_is_failed(): void
    {
        $purchase = $this->createTestPurchase(['status' => 'failed']);

        $this->assertTrue($purchase->isFailed());
        $this->assertFalse($purchase->isPending());
        $this->assertFalse($purchase->isConfirmed());
        $this->assertFalse($purchase->isCancelled());
    }

    /**
     */
    #[Test]
    public function it_can_calculate_total_tickets_for_purchase(): void
    {
        $purchase = $this->createTestPurchase(['quantity' => 5]);

        $this->assertEquals(5, $purchase->getTotalTickets());
    }

    /**
     */
    #[Test]
    public function it_can_get_formatted_purchase_date(): void
    {
        $purchaseDate = now()->subDays(2);
        $purchase = $this->createTestPurchase(['created_at' => $purchaseDate]);

        $this->assertEquals(
            $purchaseDate->format('M j, Y \a\t g:i A'),
            $purchase->getFormattedPurchaseDate(),
        );
    }

    /**
     */
    #[Test]
    public function it_can_get_formatted_total_amount(): void
    {
        $purchase = $this->createTestPurchase(['total_amount' => 1234.56]);

        $this->assertEquals('$1,234.56', $purchase->getFormattedTotalAmount());
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_can_be_cancelled(): void
    {
        $pendingPurchase = $this->createTestPurchase(['status' => 'pending']);
        $confirmedPurchase = $this->createTestPurchase(['status' => 'confirmed']);
        $cancelledPurchase = $this->createTestPurchase(['status' => 'cancelled']);

        $this->assertTrue($pendingPurchase->canBeCancelled());
        $this->assertFalse($confirmedPurchase->canBeCancelled());
        $this->assertFalse($cancelledPurchase->canBeCancelled());
    }

    /**
     */
    #[Test]
    public function it_can_get_processing_fee_percentage(): void
    {
        $purchase = $this->createTestPurchase([
            'subtotal'       => 100.00,
            'processing_fee' => 3.00,
        ]);

        $this->assertEquals(3.0, $purchase->getProcessingFeePercentage());
    }

    /**
     */
    #[Test]
    public function it_handles_zero_subtotal_for_processing_fee_percentage(): void
    {
        $purchase = $this->createTestPurchase([
            'subtotal'       => 0.00,
            'processing_fee' => 0.00,
        ]);

        $this->assertEquals(0.0, $purchase->getProcessingFeePercentage());
    }

    /**
     */
    #[Test]
    public function it_can_get_seat_preferences_summary(): void
    {
        $purchase = $this->createTestPurchase([
            'seat_preferences' => [
                'section'   => 'VIP',
                'row'       => 'A',
                'seat_type' => 'premium',
            ],
        ]);

        $summary = $purchase->getSeatPreferencesSummary();

        $this->assertStringContainsString('Section: VIP', $summary);
        $this->assertStringContainsString('Row: A', $summary);
        $this->assertStringContainsString('Type: Premium', $summary);
    }

    /**
     */
    #[Test]
    public function it_returns_empty_string_for_no_seat_preferences(): void
    {
        $purchase = $this->createTestPurchase(['seat_preferences' => NULL]);

        $this->assertEquals('', $purchase->getSeatPreferencesSummary());

        $purchase = $this->createTestPurchase(['seat_preferences' => []]);

        $this->assertEquals('', $purchase->getSeatPreferencesSummary());
    }

    /**
     */
    #[Test]
    public function it_can_scope_by_status(): void
    {
        $this->createTestPurchase(['status' => 'pending']);
        $this->createTestPurchase(['status' => 'confirmed']);
        $this->createTestPurchase(['status' => 'cancelled']);
        $this->createTestPurchase(['status' => 'failed']);

        $pending = TicketPurchase::pending()->get();
        $confirmed = TicketPurchase::confirmed()->get();
        $cancelled = TicketPurchase::cancelled()->get();
        $failed = TicketPurchase::failed()->get();

        $this->assertEquals(1, $pending->count());
        $this->assertEquals(1, $confirmed->count());
        $this->assertEquals(1, $cancelled->count());
        $this->assertEquals(1, $failed->count());

        $this->assertEquals('pending', $pending->first()->status);
        $this->assertEquals('confirmed', $confirmed->first()->status);
        $this->assertEquals('cancelled', $cancelled->first()->status);
        $this->assertEquals('failed', $failed->first()->status);
    }

    /**
     */
    #[Test]
    public function it_can_scope_by_user(): void
    {
        $user1 = $this->createTestUser();
        $user2 = $this->createTestUser();

        $this->createTestPurchase(['user_id' => $user1->id]);
        $this->createTestPurchase(['user_id' => $user1->id]);
        $this->createTestPurchase(['user_id' => $user2->id]);

        $user1Purchases = TicketPurchase::forUser($user1)->get();
        $user2Purchases = TicketPurchase::forUser($user2)->get();

        $this->assertEquals(2, $user1Purchases->count());
        $this->assertEquals(1, $user2Purchases->count());
    }

    /**
     */
    #[Test]
    public function it_can_scope_by_date_range(): void
    {
        $oldPurchase = $this->createTestPurchase(['created_at' => now()->subWeek()]);
        $recentPurchase = $this->createTestPurchase(['created_at' => now()->subDay()]);
        $todayPurchase = $this->createTestPurchase(['created_at' => now()]);

        $recentPurchases = TicketPurchase::inDateRange(
            now()->subDays(3),
            now(),
        )->get();

        $this->assertEquals(2, $recentPurchases->count());
        $this->assertFalse($recentPurchases->contains($oldPurchase));
        $this->assertTrue($recentPurchases->contains($recentPurchase));
        $this->assertTrue($recentPurchases->contains($todayPurchase));
    }

    /**
     */
    #[Test]
    public function it_can_scope_by_current_month(): void
    {
        $lastMonth = $this->createTestPurchase(['created_at' => now()->subMonth()]);
        $thisMonth = $this->createTestPurchase(['created_at' => now()]);

        $currentMonthPurchases = TicketPurchase::currentMonth()->get();

        $this->assertEquals(1, $currentMonthPurchases->count());
        $this->assertFalse($currentMonthPurchases->contains($lastMonth));
        $this->assertTrue($currentMonthPurchases->contains($thisMonth));
    }

    /**
     */
    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->expectException(QueryException::class);

        // Try to create purchase without required fields
        TicketPurchase::create([
            'quantity'     => 1,
            'total_amount' => 100.00,
            // Missing user_id, ticket_id, purchase_id, etc.
        ]);
    }

    /**
     */
    #[Test]
    public function it_generates_unique_purchase_ids(): void
    {
        $purchase1 = $this->createTestPurchase(['purchase_id' => 'PUR-UNIQUE-1']);
        $purchase2 = $this->createTestPurchase(['purchase_id' => 'PUR-UNIQUE-2']);

        $this->assertNotEquals($purchase1->purchase_id, $purchase2->purchase_id);
    }

    /**
     */
    #[Test]
    public function it_can_get_purchase_age_in_days(): void
    {
        $purchase = $this->createTestPurchase(['created_at' => now()->subDays(5)]);

        $this->assertEquals(5, $purchase->getAgeInDays());
    }

    /**
     */
    #[Test]
    public function it_can_check_if_purchase_is_recent(): void
    {
        $recentPurchase = $this->createTestPurchase(['created_at' => now()->subHour()]);
        $oldPurchase = $this->createTestPurchase(['created_at' => now()->subWeek()]);

        $this->assertTrue($recentPurchase->isRecent());
        $this->assertFalse($oldPurchase->isRecent());
    }

    /**
     * Helper method to create a test purchase
     */
    private function createTestPurchase(array $attributes = []): TicketPurchase
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        $defaultAttributes = [
            'user_id'        => $user->id,
            'ticket_id'      => $ticket->id,
            'purchase_id'    => 'PUR-' . uniqid(),
            'quantity'       => 1,
            'unit_price'     => 100.00,
            'subtotal'       => 100.00,
            'processing_fee' => 3.00,
            'service_fee'    => 2.50,
            'total_amount'   => 105.50,
            'status'         => 'pending',
        ];

        return TicketPurchase::create(array_merge($defaultAttributes, $attributes));
    }
}
