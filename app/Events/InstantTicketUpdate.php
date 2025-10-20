<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use function count;
use function in_array;

/**
 * Real-time ticket update event for instant dashboard updates
 */
class InstantTicketUpdate implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Event $event,
        public array $changes,
        public ?int $userId = NULL,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('instant-tickets'),
            new Channel("event.{$this->event->id}"),
        ];

        // Add user-specific channel if user ID provided
        if ($this->userId) {
            $channels[] = new PrivateChannel("user.{$this->userId}.tickets");
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event_id'              => $this->event->id,
            'event_name'            => $this->event->name,
            'changes'               => $this->changes,
            'timestamp'             => now()->toISOString(),
            'microsecond_timestamp' => microtime(TRUE),
            'summary'               => $this->generateChangeSummary(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'instant.ticket.update';
    }

    /**
     * Generate a summary of the changes
     */
    private function generateChangeSummary(): array
    {
        $types = array_column($this->changes, 'type');
        $platforms = array_unique(array_column($this->changes, 'platform'));
        $urgentCount = count(array_filter($this->changes, fn ($c) => $c['urgency'] === 'high'));

        return [
            'total_changes'             => count($this->changes),
            'change_types'              => array_count_values($types),
            'platforms_affected'        => $platforms,
            'urgent_alerts'             => $urgentCount,
            'has_new_listings'          => in_array('new_listing', $types, TRUE),
            'has_price_drops'           => in_array('price_drop', $types, TRUE),
            'has_availability_restored' => in_array('availability_restored', $types, TRUE),
        ];
    }
}
