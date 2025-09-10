<?php declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealTimeSystemUpdate implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** ISO timestamp when the update occurred */
    public string $timestamp;

    /**
     * Create a new event instance.
     *
     * @param string $type    The type of system update
     * @param string $message Human-readable message describing the update
     * @param mixed  $data    Real-time update data payload
     * @param string $level   The severity level ('info', 'warning', 'error', 'success')
     */
    public function __construct(
        public string $type,
        public string $message,
        public mixed $data = NULL,
        public string $level = 'info',
    ) {
        // info, warning, error, success
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
            new Channel('system-updates'),
            new Channel('realtime-dashboard'),
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
        return 'system.update';
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
            'type'      => $this->type,
            'message'   => $this->message,
            'data'      => $this->data,
            'level'     => $this->level,
            'timestamp' => $this->timestamp,
        ];
    }
}
