<?php declare(strict_types=1);

namespace App\Infrastructure\Projections;

use App\Domain\Shared\Events\DomainEventInterface;
use App\Domain\Ticket\Events\TicketAvailabilityChanged;
use App\Domain\Ticket\Events\TicketDiscovered;
use App\Domain\Ticket\Events\TicketPriceChanged;
use App\Domain\Ticket\Events\TicketSoldOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function get_class;
use function in_array;

class TicketReadModelProjection implements ProjectionInterface
{
    /**
     * Get  name
     */
    public function getName(): string
    {
        return 'ticket_read_model';
    }

    /**
     * Get  handled event types
     */
    public function getHandledEventTypes(): array
    {
        return [
            TicketDiscovered::class,
            TicketPriceChanged::class,
            TicketAvailabilityChanged::class,
            TicketSoldOut::class,
        ];
    }

    /**
     * Handles
     */
    public function handles(string $eventType): bool
    {
        return in_array($eventType, $this->getHandledEventTypes(), TRUE);
    }

    /**
     * Project
     */
    public function project(DomainEventInterface $event): void
    {
        match (get_class($event)) {
            TicketDiscovered::class          => $this->handleTicketDiscovered($event),
            TicketPriceChanged::class        => $this->handleTicketPriceChanged($event),
            TicketAvailabilityChanged::class => $this->handleTicketAvailabilityChanged($event),
            TicketSoldOut::class             => $this->handleTicketSoldOut($event),
            default                          => Log::warning('Unhandled event type in TicketReadModelProjection', [
                'event_type' => get_class($event),
                'event_id'   => $event->getEventId(),
            ]),
        };
    }

    /**
     * Reset
     */
    public function reset(): void
    {
        DB::table('ticket_read_models')->truncate();
        Log::info('Ticket read model projection reset');
    }

    /**
     * Get  state
     */
    public function getState(): array
    {
        return [
            'total_tickets'       => DB::table('ticket_read_models')->count(),
            'available_tickets'   => DB::table('ticket_read_models')->where('is_sold_out', FALSE)->count(),
            'sold_out_tickets'    => DB::table('ticket_read_models')->where('is_sold_out', TRUE)->count(),
            'high_demand_tickets' => DB::table('ticket_read_models')->where('is_high_demand', TRUE)->count(),
            'platforms'           => DB::table('ticket_read_models')->select('platform_source')->distinct()->pluck('platform_source'),
            'last_updated'        => DB::table('ticket_read_models')->max('last_updated_at'),
        ];
    }

    /**
     * HandleTicketDiscovered
     */
    private function handleTicketDiscovered(TicketDiscovered $event): void
    {
        $priceHistory = [
            [
                'price'     => $event->price->getAmount(),
                'currency'  => $event->price->getCurrency(),
                'timestamp' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
            ],
        ];

        $availabilityHistory = [
            [
                'status'    => 'available',
                'quantity'  => $event->availableQuantity,
                'timestamp' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
            ],
        ];

        DB::table('ticket_read_models')->updateOrInsert(
            ['ticket_id' => $event->ticketId->getValue()],
            [
                'platform_source'      => $event->platformSource->getValue(),
                'event_name'           => $event->eventName,
                'event_category'       => $event->eventCategory,
                'venue'                => $event->venue,
                'event_date'           => $event->eventDate->format('Y-m-d H:i:s'),
                'current_price'        => $event->price->getAmount(),
                'original_price'       => $event->price->getAmount(),
                'availability_status'  => 'available',
                'available_quantity'   => $event->availableQuantity,
                'price_history'        => json_encode($priceHistory),
                'availability_history' => json_encode($availabilityHistory),
                'is_high_demand'       => $this->isHighDemandEvent($event->eventName, $event->venue),
                'is_sold_out'          => FALSE,
                'first_discovered_at'  => DB::raw('COALESCE(first_discovered_at, NOW())'),
                'last_updated_at'      => now(),
                'version'              => DB::raw('version + 1'),
            ],
        );
    }

    /**
     * HandleTicketPriceChanged
     */
    private function handleTicketPriceChanged(TicketPriceChanged $event): void
    {
        $ticket = DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->first();

        if (!$ticket) {
            Log::warning('Attempting to update price for non-existent ticket', [
                'ticket_id' => $event->ticketId->getValue(),
            ]);

            return;
        }

        $priceHistory = json_decode($ticket->price_history, TRUE) ?? [];
        $priceHistory[] = [
            'price'     => $event->newPrice->getAmount(),
            'currency'  => $event->newPrice->getCurrency(),
            'timestamp' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ];

        DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->update([
                'current_price'   => $event->newPrice->getAmount(),
                'price_history'   => json_encode($priceHistory),
                'last_updated_at' => now(),
                'version'         => DB::raw('version + 1'),
            ]);
    }

    /**
     * HandleTicketAvailabilityChanged
     */
    private function handleTicketAvailabilityChanged(TicketAvailabilityChanged $event): void
    {
        $ticket = DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->first();

        if (!$ticket) {
            Log::warning('Attempting to update availability for non-existent ticket', [
                'ticket_id' => $event->ticketId->getValue(),
            ]);

            return;
        }

        $availabilityHistory = json_decode($ticket->availability_history, TRUE) ?? [];
        $availabilityHistory[] = [
            'status'    => $event->newStatus->getValue(),
            'timestamp' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ];

        $updateData = [
            'availability_status'  => $event->newStatus->getValue(),
            'availability_history' => json_encode($availabilityHistory),
            'last_updated_at'      => now(),
            'version'              => DB::raw('version + 1'),
        ];

        if ($event->soldOut()) {
            $updateData['is_sold_out'] = TRUE;
        }

        DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->update($updateData);
    }

    /**
     * HandleTicketSoldOut
     */
    private function handleTicketSoldOut(TicketSoldOut $event): void
    {
        $ticket = DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->first();

        if (!$ticket) {
            Log::warning('Attempting to mark non-existent ticket as sold out', [
                'ticket_id' => $event->ticketId->getValue(),
            ]);

            return;
        }

        $availabilityHistory = json_decode($ticket->availability_history, TRUE) ?? [];
        $availabilityHistory[] = [
            'status'           => 'sold_out',
            'timestamp'        => $event->soldOutAt->format('Y-m-d H:i:s'),
            'duration_minutes' => $event->durationOnSaleMinutes,
        ];

        DB::table('ticket_read_models')
            ->where('ticket_id', $event->ticketId->getValue())
            ->update([
                'availability_status'  => 'sold_out',
                'is_sold_out'          => TRUE,
                'available_quantity'   => 0,
                'availability_history' => json_encode($availabilityHistory),
                'last_updated_at'      => now(),
                'version'              => DB::raw('version + 1'),
            ]);
    }

    /**
     * Check if  high demand event
     */
    private function isHighDemandEvent(string $eventName, string $venue): bool
    {
        $highDemandKeywords = ['final', 'cup', 'championship', 'derby', 'champions league', 'playoff'];
        $eventName = strtolower($eventName);

        foreach ($highDemandKeywords as $keyword) {
            if (str_contains($eventName, $keyword)) {
                return TRUE;
            }
        }

        $highDemandVenues = ['wembley', 'old trafford', 'emirates', 'anfield', 'camp nou', 'bernabeu'];
        $venue = strtolower($venue);

        foreach ($highDemandVenues as $highDemandVenue) {
            if (str_contains($venue, $highDemandVenue)) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
