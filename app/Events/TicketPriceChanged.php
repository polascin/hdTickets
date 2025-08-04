<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketPriceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketId;
    public $eventName;
    public $platform;
    public $oldPrice;
    public $newPrice;
    public $priceChange;
    public $changePercentage;
    public $url;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($ticketId, $eventName, $platform, $oldPrice, $newPrice, $url = null)
    {
        $this->ticketId = $ticketId;
        $this->eventName = $eventName;
        $this->platform = $platform;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->priceChange = $newPrice - $oldPrice;
        $this->changePercentage = $oldPrice > 0 ? round(($this->priceChange / $oldPrice) * 100, 2) : 0;
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
            new Channel('price-changes'),
            new Channel('platform.' . $this->platform),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.price.changed';
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
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'price_change' => $this->priceChange,
            'change_percentage' => $this->changePercentage,
            'url' => $this->url,
            'timestamp' => $this->timestamp,
        ];
    }
}
