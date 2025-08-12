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

    public $ticketUuid;

    public $status;

    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param mixed $ticketUuid
     * @param mixed $status
     */
    public function __construct($ticketUuid, $status)
    {
        $this->ticketUuid = $ticketUuid;
        $this->status = $status;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
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
    public function broadcastAs(): string
    {
        return 'ticket.availability.updated';
    }

    /**
     * Get the data to broadcast.
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
