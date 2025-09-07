<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The unique identifier for the ticket
     * Can be string UUID or integer ID depending on platform
     */
    public string|int $ticketId;

    /** The name of the sports event */
    public string $eventName;

    /**
     * The platform where this ticket is available
     * Examples: 'ticketmaster', 'stubhub', 'seatgeek', 'vivid_seats'
     */
    public string $platform;

    /**
     * The previous availability status
     * Values: 'available', 'sold_out', 'limited', 'unavailable'
     */
    public string $oldStatus;

    /**
     * The new availability status
     * Values: 'available', 'sold_out', 'limited', 'unavailable'
     */
    public string $newStatus;

    /** The previous quantity of available tickets (if known) */
    public ?int $oldQuantity;

    /** The new quantity of available tickets (if known) */
    public ?int $newQuantity;

    /** The URL to the ticket on the platform */
    public ?string $url;

    /** ISO timestamp when the availability change was detected */
    public string $timestamp;

    /**
     * Create a new event instance.
     *
     * @param int|string  $ticketId    The unique identifier for the ticket
     * @param string      $eventName   The name of the sports event
     * @param string      $platform    The platform where ticket is available
     * @param string      $oldStatus   The previous availability status
     * @param string      $newStatus   The new availability status
     * @param int|null    $oldQuantity The previous quantity of available tickets
     * @param int|null    $newQuantity The new quantity of available tickets
     * @param string|null $url         The URL to the ticket on the platform
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
            // Public channel for specific ticket
            new Channel("ticket.{$this->ticketId}"),

            // Public channel for platform-wide updates
            new Channel("platform.{$this->platform}"),

            // Public channel for availability alerts
            new Channel('availability-alerts'),
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
        return 'TicketAvailabilityChanged';
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
            'ticket_id'          => $this->ticketId,
            'event_name'         => $this->eventName,
            'event_title'        => $this->eventName,
            'platform'           => $this->platform,
            'old_status'         => $this->oldStatus,
            'new_status'         => $this->newStatus,
            'available_quantity' => $this->newQuantity ?? 0,
            'total_quantity'     => $this->newQuantity ?? 0,
            'is_available'       => $this->newStatus === 'available',
            'old_quantity'       => $this->oldQuantity,
            'new_quantity'       => $this->newQuantity,
            'url'                => $this->url,
            'timestamp'          => $this->timestamp,
        ];
    }
}
