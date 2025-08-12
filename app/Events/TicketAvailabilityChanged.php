<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public string|int $ticketId;

    public string $eventName;

    public string $platform;

    public string $oldStatus;

    public string $newStatus;

    public ?int $oldQuantity;

    public ?int $newQuantity;

    public ?string $url;

    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string|int $ticketId,
        string $eventName,
        string $platform,
        string $oldStatus,
        string $newStatus,
        ?int $oldQuantity = NULL,
        ?int $newQuantity = NULL,
        ?string $url = NULL,
    ) {
        $this->ticketId = $ticketId;
        $this->eventName = $eventName;
        $this->platform = $platform;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
        $this->url = $url;
        $this->timestamp = now()->toISOString();
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
            new Channel('availability-changes'),
            new Channel('platform.' . $this->platform),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.availability.changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id'    => $this->ticketId,
            'event_name'   => $this->eventName,
            'platform'     => $this->platform,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->newStatus,
            'old_quantity' => $this->oldQuantity,
            'new_quantity' => $this->newQuantity,
            'url'          => $this->url,
            'timestamp'    => $this->timestamp,
        ];
    }
}
