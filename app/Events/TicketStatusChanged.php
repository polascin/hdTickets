<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Ticket Status Changed Event
 *
 * Broadcast when a scraped ticket's status changes (active, inactive, removed, etc.),
 * enabling real-time status monitoring for users watching specific tickets.
 */
class TicketStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $ticket_id;

    public $old_status;

    public $new_status;

    public $reason;

    public $timestamp;

    public $platform;

    public $event_title;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $ticket_id,
        string $old_status,
        string $new_status,
        ?string $reason = NULL,
        ?string $platform = NULL,
        ?string $event_title = NULL
    ) {
        $this->ticket_id = $ticket_id;
        $this->old_status = $old_status;
        $this->new_status = $new_status;
        $this->reason = $reason;
        $this->platform = $platform;
        $this->event_title = $event_title;
        $this->timestamp = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Public channel for specific ticket
            new Channel("ticket.{$this->ticket_id}"),

            // Public channel for platform-wide updates
            new Channel("platform.{$this->platform}"),

            // Public channel for status alerts
            new Channel('status-alerts'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id'    => $this->ticket_id,
            'old_status'   => $this->old_status,
            'new_status'   => $this->new_status,
            'reason'       => $this->reason,
            'platform'     => $this->platform,
            'event_title'  => $this->event_title,
            'timestamp'    => $this->timestamp,
            'status_color' => $this->getStatusColor($this->new_status),
            'is_critical'  => in_array($this->new_status, ['inactive', 'removed', 'sold_out']),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TicketStatusChanged';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function shouldBroadcast(): bool
    {
        // Always broadcast status changes as they are important
        return TRUE;
    }

    /**
     * Get color class for status.
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'active'   => 'green',
            'inactive' => 'yellow',
            'removed'  => 'red',
            'sold_out' => 'gray',
            default    => 'gray'
        };
    }
}
