<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** Timestamp when the availability was updated */
    public string $timestamp;

    /**
     * Create a new event instance.
     *
     * @param int|string $ticketUuid The unique identifier for the ticket
     * @param string     $status     The availability status of the ticket
     */
    public function __construct(public string|int $ticketUuid, public string $status)
    {
        $this->timestamp = now()->toISOString() ?? now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    /**
     * BroadcastOn
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('ticket-updates'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    /**
     * BroadcastAs
     */
    public function broadcastAs(): string
    {
        return 'ticket.availability.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    /**
     * BroadcastWith
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_uuid' => $this->ticketUuid,
            'status'      => $this->status,
            'timestamp'   => $this->timestamp,
        ];
    }
}
