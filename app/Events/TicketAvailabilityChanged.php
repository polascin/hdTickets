<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketId;
    public $eventName;
    public $platform;
    public $oldStatus;
    public $newStatus;
    public $oldQuantity;
    public $newQuantity;
    public $url;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $eventName, $platform, $oldStatus, $newStatus, $oldQuantity = null, $newQuantity = null, $url = null)
    {
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
     * @return array<int, \Illuminate\Broadcasting\Channel>
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
            'ticket_id' => $this->ticketId,
            'event_name' => $this->eventName,
            'platform' => $this->platform,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_quantity' => $this->oldQuantity,
            'new_quantity' => $this->newQuantity,
            'url' => $this->url,
            'timestamp' => $this->timestamp,
        ];
    }
}
