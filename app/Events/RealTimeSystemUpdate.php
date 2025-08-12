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

    public string $type;

    public string $message;

    public mixed $data;

    public string $level;

    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $type,
        string $message,
        mixed $data = NULL,
        string $level = 'info',
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
        $this->level = $level; // info, warning, error, success
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
            new Channel('system-updates'),
            new Channel('realtime-dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
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
