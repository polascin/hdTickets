<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketPriceChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The unique identifier for the ticket
     * Can be string UUID or integer ID depending on platform
     */
    public string|int $ticketId;

    /** The name of the sports event for this ticket */
    public string $eventName;

    /**
     * The platform where this ticket is available
     * Examples: 'ticketmaster', 'stubhub', 'seatgeek', 'vivid_seats'
     */
    public string $platform;

    /** The previous price of the ticket in decimal format */
    public float $oldPrice;

    /** The new price of the ticket in decimal format */
    public float $newPrice;

    /** The calculated price change amount (newPrice - oldPrice) */
    public float $priceChange;

    /** The percentage change in price */
    public float $changePercentage;

    /** The URL to the ticket on the platform */
    public ?string $url;

    /** Timestamp when the price change was detected */
    public string $timestamp;

    /**
     * Create a new event instance.
     *
     * @param int|string  $ticketId  The unique identifier for the ticket
     * @param string      $eventName The name of the sports event
     * @param string      $platform  The platform where ticket is available
     * @param float       $oldPrice  The previous price of the ticket
     * @param float       $newPrice  The new price of the ticket
     * @param string|null $url       The URL to the ticket on the platform
     */
    public function __construct(
        string|int $ticketId,
        string $eventName,
        string $platform,
        float $oldPrice,
        float $newPrice,
        ?string $url = NULL,
    ) {
        $this->ticketId = $ticketId;
        $this->eventName = $eventName;
        $this->platform = $platform;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->priceChange = $newPrice - $oldPrice;
        $this->changePercentage = $oldPrice > 0 ? round(($this->priceChange / $oldPrice) * 100, 2) : 0;
        $this->url = $url;
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
            new Channel('price-changes'),
            new Channel('platform.' . $this->platform),
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
        return 'ticket.price.changed';
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
            'ticket_id'         => $this->ticketId,
            'event_name'        => $this->eventName,
            'platform'          => $this->platform,
            'old_price'         => $this->oldPrice,
            'new_price'         => $this->newPrice,
            'price_change'      => $this->priceChange,
            'change_percentage' => $this->changePercentage,
            'url'               => $this->url,
            'timestamp'         => $this->timestamp,
        ];
    }
}
