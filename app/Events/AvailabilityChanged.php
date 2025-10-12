<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserWatchlist;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

use function in_array;

class AvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Collection $notifications;

    public Collection $watchingUsers;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
        public ?int $oldQuantity = NULL,
        public ?int $newQuantity = NULL,
    ) {
        // Get users watching this ticket
        $this->watchingUsers = UserWatchlist::with(['user.notificationSettings'])
            ->where('ticket_id', $this->ticket->id)
            ->get()
            ->pluck('user')
            ->filter(function ($user): bool {
                $settings = $user->notificationSettings;

                return $settings
                       && $settings->availability_alerts_enabled
                       && (!$settings->snoozed_until || $settings->snoozed_until <= now());
            });

        // Create notifications for watching users
        $this->notifications = collect();

        foreach ($this->watchingUsers as $user) {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type'    => 'availability_alert',
                'title'   => 'Availability Update: ' . $this->ticket->event_title,
                'message' => $this->getStatusMessage(),
                'data'    => [
                    'ticket_id'    => $this->ticket->id,
                    'event_title'  => $this->ticket->event_title,
                    'venue'        => $this->ticket->venue,
                    'event_date'   => $this->ticket->event_date,
                    'old_status'   => $this->oldStatus,
                    'new_status'   => $this->newStatus,
                    'old_quantity' => $this->oldQuantity,
                    'new_quantity' => $this->newQuantity,
                    'price'        => $this->ticket->price,
                    'action_url'   => route('tickets.show', $this->ticket->id),
                    'ticket_url'   => route('tickets.show', $this->ticket->id),
                    'purchase_url' => $this->newStatus === 'available' ? route('tickets.purchase', $this->ticket->id) : NULL,
                ],
                'read_at'    => NULL,
                'created_at' => now(),
            ]);

            $this->notifications->push($notification);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to each watching user's private channel
        foreach ($this->watchingUsers as $user) {
            $channels[] = new PrivateChannel('notifications.' . $user->id);
            $channels[] = new PrivateChannel('availability-alerts.' . $user->id);
        }

        // Also broadcast to a general ticket channel for real-time updates
        $channels[] = new Channel('ticket.' . $this->ticket->id);

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id'                 => $this->ticket->id,
                'event_title'        => $this->ticket->event_title,
                'venue'              => $this->ticket->venue,
                'event_date'         => $this->ticket->event_date,
                'price'              => $this->ticket->price,
                'old_status'         => $this->oldStatus,
                'new_status'         => $this->newStatus,
                'old_quantity'       => $this->oldQuantity,
                'new_quantity'       => $this->newQuantity,
                'status'             => $this->ticket->status,
                'quantity_available' => $this->ticket->quantity_available,
                'url'                => route('tickets.show', $this->ticket->id),
            ],
            'change_type'   => $this->getChangeType(),
            'urgency'       => $this->getUrgencyLevel(),
            'message'       => $this->getStatusMessage(),
            'notifications' => $this->notifications->map(fn ($notification): array => [
                'id'         => $notification->id,
                'user_id'    => $notification->user_id,
                'type'       => $notification->type,
                'title'      => $notification->title,
                'message'    => $notification->message,
                'data'       => $notification->data,
                'created_at' => $notification->created_at->toISOString(),
                'read_at'    => NULL,
            ]),
            'watching_users_count' => $this->watchingUsers->count(),
            'timestamp'            => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AvailabilityChanged';
    }

    /**
     * Determine if this event should be broadcast.
     */
    public function shouldBroadcast(): bool
    {
        // Only broadcast if there are users watching this ticket
        if ($this->watchingUsers->count() > 0) {
            return TRUE;
        }

        return $this->isSignificantChange();
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
            'availability-alert',
            'ticket:' . $this->ticket->id,
            'status:' . $this->newStatus,
            'urgency:' . $this->getUrgencyLevel(),
        ];
    }

    /**
     * Get channels for specific user
     */
    public function getChannelsForUser(User $user): array
    {
        return [
            new PrivateChannel('notifications.' . $user->id),
            new PrivateChannel('availability-alerts.' . $user->id),
        ];
    }

    /**
     * Check if user should receive this notification
     */
    public function shouldNotifyUser(User $user): bool
    {
        $settings = $user->notificationSettings;

        if (!$settings || !$settings->availability_alerts_enabled) {
            return FALSE;
        }

        if ($settings->snoozed_until && $settings->snoozed_until > now()) {
            return FALSE;
        }

        // Check if user is watching this ticket
        return UserWatchlist::where([
            'user_id'   => $user->id,
            'ticket_id' => $this->ticket->id,
        ])->exists();
    }

    /**
     * Get the status change message
     */
    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'available' => match ($this->oldStatus) {
                'sold_out' => 'Tickets are now available again!',
                'limited'  => 'More tickets became available!',
                default    => 'Tickets are now available!',
            },
            'limited'      => 'Only limited tickets remaining!',
            'selling_fast' => 'Tickets are selling fast!',
            'sold_out'     => 'Event is now sold out.',
            'cancelled'    => 'Event has been cancelled.',
            'postponed'    => 'Event has been postponed.',
            default        => "Status changed from {$this->oldStatus} to {$this->newStatus}.",
        };
    }

    /**
     * Get the type of change that occurred
     */
    private function getChangeType(): string
    {
        if ($this->oldStatus === 'sold_out' && $this->newStatus === 'available') {
            return 'back_in_stock';
        }

        if ($this->newStatus === 'sold_out') {
            return 'sold_out';
        }

        if ($this->newStatus === 'limited' && $this->oldStatus !== 'limited') {
            return 'low_stock';
        }

        if ($this->newStatus === 'selling_fast') {
            return 'high_demand';
        }

        if (in_array($this->newStatus, ['cancelled', 'postponed'], TRUE)) {
            return 'event_change';
        }

        return 'status_update';
    }

    /**
     * Get the urgency level of this change
     */
    private function getUrgencyLevel(): string
    {
        return match ($this->getChangeType()) {
            'back_in_stock' => 'high',
            'sold_out'      => 'medium',
            'low_stock'     => 'medium',
            'high_demand'   => 'medium',
            'event_change'  => 'high',
            default         => 'low',
        };
    }

    /**
     * Check if this is a significant change worth broadcasting
     */
    private function isSignificantChange(): bool
    {
        $significantChanges = [
            'back_in_stock',
            'sold_out',
            'low_stock',
            'event_change',
        ];

        return in_array($this->getChangeType(), $significantChanges, TRUE);
    }
}
