<?php declare(strict_types=1);

namespace App\Application\EventHandlers\Ticket;

use App\Domain\Ticket\Events\TicketDiscovered;
use App\Infrastructure\EventBus\EventBusInterface;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketDiscoveredHandler
{
    public function __construct(
        private readonly EventBusInterface $eventBus,
    ) {
    }

    /**
     * Handle
     */
    public function handle(TicketDiscovered $event): void
    {
        try {
            // Update ticket read model
            $this->updateTicketReadModel($event);

            // Update cache
            $this->updateTicketCache($event);

            // Trigger alerts if needed
            $this->checkAlertConditions($event);

            // Update platform statistics
            $this->updatePlatformStats($event);

            Log::info('Ticket discovered and processed', [
                'ticket_id'  => $event->ticketId->getValue(),
                'event_name' => $event->eventName,
                'platform'   => $event->platformSource->getValue(),
                'price'      => $event->price->getAmount(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to handle TicketDiscovered event', [
                'ticket_id' => $event->ticketId->getValue(),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * UpdateTicketReadModel
     */
    private function updateTicketReadModel(TicketDiscovered $event): void
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
                'is_high_demand'       => $this->determineHighDemand($event),
                'is_sold_out'          => FALSE,
                'first_discovered_at'  => DB::raw('COALESCE(first_discovered_at, NOW())'),
                'last_updated_at'      => now(),
                'version'              => DB::raw('version + 1'),
            ],
        );
    }

    /**
     * UpdateTicketCache
     */
    private function updateTicketCache(TicketDiscovered $event): void
    {
        $cacheKey = "ticket:{$event->ticketId->getValue()}";

        $ticketData = [
            'id'                 => $event->ticketId->getValue(),
            'event_name'         => $event->eventName,
            'event_category'     => $event->eventCategory,
            'venue'              => $event->venue,
            'event_date'         => $event->eventDate->format('Y-m-d H:i:s'),
            'price'              => $event->price->getAmount(),
            'currency'           => $event->price->getCurrency(),
            'platform'           => $event->platformSource->getValue(),
            'available_quantity' => $event->availableQuantity,
            'status'             => 'available',
            'discovered_at'      => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ];

        Cache::put($cacheKey, $ticketData, now()->addHours(2));

        // Also add to platform-specific cache
        $platformCacheKey = "platform:{$event->platformSource->getValue()}:tickets";
        $platformTickets = Cache::get($platformCacheKey, []);
        $platformTickets[$event->ticketId->getValue()] = $ticketData;
        Cache::put($platformCacheKey, $platformTickets, now()->addHours(1));
    }

    /**
     * CheckAlertConditions
     */
    private function checkAlertConditions(TicketDiscovered $event): void
    {
        // Check if any user alerts match this ticket
        $matchingAlerts = DB::table('ticket_alerts')
            ->where('is_active', TRUE)
            ->where(function ($query) use ($event): void {
                $query->where('event_name', 'like', '%' . $event->eventName . '%')
                    ->orWhere('venue', 'like', '%' . $event->venue . '%')
                    ->orWhere('category', $event->eventCategory);
            })
            ->where('max_price', '>=', $event->price->getAmount())
            ->get();

        foreach ($matchingAlerts as $alert) {
            // Dispatch alert triggered event
            $alertEvent = new \App\Domain\Monitoring\Events\AlertTriggered(
                alertId: 'alert-' . $alert->id . '-' . time(),
                monitorId: 'ticket-alert-' . $alert->id,
                userId: $alert->user_id,
                alertType: 'ticket_discovery',
                severity: 'info',
                alertData: [
                    'ticket_id'      => $event->ticketId->getValue(),
                    'event_name'     => $event->eventName,
                    'price'          => $event->price->getAmount(),
                    'alert_criteria' => $alert,
                ],
                triggeredAt: $event->getOccurredAt(),
            );

            $this->eventBus->dispatch($alertEvent);
        }
    }

    /**
     * UpdatePlatformStats
     */
    private function updatePlatformStats(TicketDiscovered $event): void
    {
        $platform = $event->platformSource->getValue();
        $today = now()->format('Y-m-d');

        // Update daily discovery stats
        DB::table('platform_statistics')->updateOrInsert(
            [
                'platform' => $platform,
                'date'     => $today,
                'metric'   => 'tickets_discovered',
            ],
            [
                'value'      => DB::raw('value + 1'),
                'updated_at' => now(),
            ],
        );

        // Update category stats
        DB::table('platform_statistics')->updateOrInsert(
            [
                'platform' => $platform,
                'date'     => $today,
                'metric'   => 'category_' . strtolower($event->eventCategory),
            ],
            [
                'value'      => DB::raw('value + 1'),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * DetermineHighDemand
     */
    private function determineHighDemand(TicketDiscovered $event): bool
    {
        // Simple high demand logic - can be made more sophisticated
        $highDemandKeywords = ['final', 'cup', 'championship', 'derby', 'champions league'];
        $eventName = strtolower($event->eventName);

        foreach ($highDemandKeywords as $keyword) {
            if (str_contains($eventName, $keyword)) {
                return TRUE;
            }
        }

        // Check if venue is high-demand
        $highDemandVenues = ['wembley', 'old trafford', 'emirates', 'anfield'];
        $venue = strtolower($event->venue);

        foreach ($highDemandVenues as $highDemandVenue) {
            if (str_contains($venue, $highDemandVenue)) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
