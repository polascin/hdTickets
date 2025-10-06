<?php declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public string $updateType,
        public array $data,
        public ?string $message = null
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard.' . $this->user->id),
            new PrivateChannel('notifications.' . $this->user->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'dashboard.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'update_type' => $this->updateType,
            'data' => $this->data,
            'message' => $this->message,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Determine if this event should be broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return true;
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
            'dashboard-update',
            'user:' . $this->user->id,
            'type:' . $this->updateType,
        ];
    }
}