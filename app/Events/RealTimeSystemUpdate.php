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

    /**
     * The type of system update
     * Examples: 'scraper_status', 'platform_health', 'data_sync', 'alert'
     */
    public string $type;

    /** Human-readable message describing the update */
    public string $message;

    /**
     * Real-time update data payload
     *
     * Can contain different structures based on update type:
     * - For 'scraper_status': {platform: string, status: string, tickets_found: int}
     * - For 'platform_health': {platform: string, response_time: float, success_rate: float}
     * - For 'data_sync': {records_updated: int, sync_time: string}
     * - For 'alert': {severity: string, affected_platforms: array, details: array}
     */
    public mixed $data;

    /**
     * The severity level of the update
     * Values: 'info', 'warning', 'error', 'success'
     */
    public string $level;

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
        string $type,
        string $message,
        mixed $data = NULL,
        string $level = 'info',
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
        $this->level = $level; // info, warning, error, success
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
