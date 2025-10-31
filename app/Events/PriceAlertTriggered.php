<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Notification;
use App\Models\PriceAlert;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceAlertTriggered implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PriceAlert $priceAlert,
        public Ticket $ticket,
        public User $user,
        public float $oldPrice,
        public float $newPrice,
    ) {
        // Create notification record
        $this->notification = Notification::create([
            'user_id' => $this->user->id,
            'type'    => 'price_alert',
            'title'   => 'Price Alert: ' . $this->ticket->event_title,
            'message' => "Price dropped from \${$this->oldPrice} to \${$this->newPrice} for {$this->ticket->event_title}",
            'data'    => [
                'ticket_id'    => $this->ticket->id,
                'event_title'  => $this->ticket->event_title,
                'venue'        => $this->ticket->venue,
                'event_date'   => $this->ticket->event_date,
                'old_price'    => $this->oldPrice,
                'new_price'    => $this->newPrice,
                'savings'      => $this->oldPrice - $this->newPrice,
                'alert_id'     => $this->priceAlert->id,
                'action_url'   => route('tickets.show', $this->ticket->id),
                'ticket_url'   => route('tickets.show', $this->ticket->id),
                'purchase_url' => route('tickets.purchase', $this->ticket->id),
            ],
            'read_at'    => NULL,
            'created_at' => now(),
        ]);

        // Mark price alert as triggered
        $this->priceAlert->update([
            'status'          => 'triggered',
            'triggered_at'    => now(),
            'triggered_price' => $this->newPrice,
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.' . $this->user->id),
            new PrivateChannel('price-alerts.' . $this->user->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'id'         => $this->notification->id,
                'type'       => $this->notification->type,
                'title'      => $this->notification->title,
                'message'    => $this->notification->message,
                'data'       => $this->notification->data,
                'created_at' => $this->notification->created_at->toISOString(),
                'read_at'    => NULL,
            ],
            'price_alert' => [
                'id'              => $this->priceAlert->id,
                'target_price'    => $this->priceAlert->target_price,
                'triggered_price' => $this->newPrice,
                'savings'         => $this->oldPrice - $this->newPrice,
                'percentage_drop' => round((($this->oldPrice - $this->newPrice) / $this->oldPrice) * 100, 2),
            ],
            'ticket' => [
                'id'          => $this->ticket->id,
                'event_title' => $this->ticket->event_title,
                'venue'       => $this->ticket->venue,
                'event_date'  => $this->ticket->event_date,
                'old_price'   => $this->oldPrice,
                'new_price'   => $this->newPrice,
                'status'      => $this->ticket->status,
                'url'         => route('tickets.show', $this->ticket->id),
            ],
            'user' => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PriceAlertTriggered';
    }

    /**
     * Determine if this event should be broadcast.
     */
    public function shouldBroadcast(): bool
    {
        // Only broadcast if user has notification settings enabled
        $settings = $this->user->notificationSettings;

        return $settings
               && $settings->price_alerts_enabled
               && $settings->push_notifications
               && (!$settings->snoozed_until || $settings->snoozed_until <= now());
    }

    /**
     * Get the queue that should be used to broadcast this event.
     */
    public function onQueue(): string
    {
        return 'broadcasting';
    }

    /**
     * Get tags that should be assigned to the queued event.
     */
    public function tags(): array
    {
        return [
            'price-alert',
            'user:' . $this->user->id,
            'ticket:' . $this->ticket->id,
            'notification:' . $this->notification->id,
        ];
    }
}
