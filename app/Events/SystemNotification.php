<?php

namespace App\Events;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SystemNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Collection $notifications;
    public Collection $targetUsers;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info', // info, warning, error, success, maintenance
        public ?array $data = null,
        public ?string $targetRole = null, // null for all users, or 'admin', 'agent', 'customer'
        public ?Collection $specificUsers = null,
        public string $priority = 'normal' // low, normal, high, critical
    ) {
        // Determine target users
        $this->targetUsers = $this->getTargetUsers();

        // Create notifications for target users
        $this->notifications = collect();
        
        foreach ($this->targetUsers as $user) {
            // Check if user should receive this notification based on their settings
            if (!$this->shouldNotifyUser($user)) {
                continue;
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => 'system',
                'title' => $this->title,
                'message' => $this->message,
                'data' => array_merge($this->data ?? [], [
                    'system_type' => $this->type,
                    'priority' => $this->priority,
                    'broadcast_time' => now()->toISOString(),
                    'target_role' => $this->targetRole,
                    'action_url' => $this->data['action_url'] ?? null,
                    'dismissible' => $this->data['dismissible'] ?? true,
                    'auto_dismiss' => $this->data['auto_dismiss'] ?? null, // seconds to auto-dismiss
                ]),
                'read_at' => null,
                'created_at' => now()
            ]);

            $this->notifications->push($notification);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to individual user channels
        foreach ($this->targetUsers as $user) {
            $channels[] = new PrivateChannel('notifications.' . $user->id);
        }

        // Broadcast to role-based channels if targeting specific roles
        if ($this->targetRole) {
            $channels[] = new PrivateChannel('system-notifications.' . $this->targetRole);
        } else {
            // Broadcast to general system channel for all users
            $channels[] = new Channel('system-notifications');
        }

        // Add priority-based channels for critical notifications
        if ($this->priority === 'critical') {
            $channels[] = new Channel('critical-notifications');
        }

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
            'system_notification' => [
                'title' => $this->title,
                'message' => $this->message,
                'type' => $this->type,
                'priority' => $this->priority,
                'target_role' => $this->targetRole,
                'data' => $this->data,
                'dismissible' => $this->data['dismissible'] ?? true,
                'auto_dismiss' => $this->data['auto_dismiss'] ?? null,
                'created_at' => now()->toISOString()
            ],
            'notifications' => $this->notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => null
                ];
            }),
            'target_users_count' => $this->targetUsers->count(),
            'affected_roles' => $this->getAffectedRoles(),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SystemNotification';
    }

    /**
     * Determine if this event should be broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return $this->targetUsers->count() > 0;
    }

    /**
     * Get the queue that should be used to broadcast this event.
     */
    public function onQueue(): string
    {
        // Use high priority queue for critical notifications
        return $this->priority === 'critical' ? 'high' : 'broadcasting';
    }

    /**
     * Get tags that should be assigned to the queued event.
     */
    public function tags(): array
    {
        $tags = [
            'system-notification',
            'type:' . $this->type,
            'priority:' . $this->priority
        ];

        if ($this->targetRole) {
            $tags[] = 'role:' . $this->targetRole;
        }

        return $tags;
    }

    /**
     * Get target users based on role or specific users
     */
    private function getTargetUsers(): Collection
    {
        if ($this->specificUsers) {
            return $this->specificUsers;
        }

        $query = User::where('is_active', true);

        if ($this->targetRole) {
            $query->where('role', $this->targetRole);
        }

        // Don't send system notifications to scrapers unless specifically targeted
        if (!$this->specificUsers && !$this->targetRole) {
            $query->where('role', '!=', 'scraper');
        }

        return $query->with('notificationSettings')->get();
    }

    /**
     * Check if user should receive this notification
     */
    private function shouldNotifyUser(User $user): bool
    {
        $settings = $user->notificationSettings;

        if (!$settings || !$settings->system_alerts_enabled) {
            return false;
        }

        if ($settings->snoozed_until && $settings->snoozed_until > now()) {
            return false;
        }

        // Always send critical notifications regardless of settings
        if ($this->priority === 'critical') {
            return true;
        }

        return true;
    }

    /**
     * Get affected roles for this notification
     */
    private function getAffectedRoles(): array
    {
        if ($this->targetRole) {
            return [$this->targetRole];
        }

        if ($this->specificUsers) {
            return $this->specificUsers->pluck('role')->unique()->values()->toArray();
        }

        return ['admin', 'agent', 'customer']; // All roles except scrapers
    }

    /**
     * Create a maintenance notification
     */
    public static function maintenance(
        string $title,
        string $message,
        ?\DateTime $startTime = null,
        ?\DateTime $endTime = null,
        ?string $affectedServices = null
    ): self {
        return new self(
            title: $title,
            message: $message,
            type: 'maintenance',
            data: [
                'start_time' => $startTime?->toISOString(),
                'end_time' => $endTime?->toISOString(),
                'affected_services' => $affectedServices,
                'dismissible' => false,
                'auto_dismiss' => null
            ],
            priority: 'high'
        );
    }

    /**
     * Create a security alert notification
     */
    public static function securityAlert(
        string $title,
        string $message,
        ?array $affectedUsers = null
    ): self {
        $users = $affectedUsers ? User::whereIn('id', $affectedUsers)->get() : null;

        return new self(
            title: $title,
            message: $message,
            type: 'error',
            data: [
                'security_alert' => true,
                'dismissible' => true,
                'action_url' => route('account.security')
            ],
            specificUsers: $users,
            priority: 'critical'
        );
    }

    /**
     * Create a feature announcement notification
     */
    public static function featureAnnouncement(
        string $title,
        string $message,
        ?string $featureUrl = null
    ): self {
        return new self(
            title: $title,
            message: $message,
            type: 'success',
            data: [
                'feature_announcement' => true,
                'action_url' => $featureUrl,
                'dismissible' => true,
                'auto_dismiss' => 10 // Auto-dismiss after 10 seconds
            ],
            priority: 'normal'
        );
    }

    /**
     * Create a promotional notification
     */
    public static function promotional(
        string $title,
        string $message,
        ?string $promoCode = null,
        ?string $actionUrl = null,
        ?string $targetRole = null
    ): self {
        return new self(
            title: $title,
            message: $message,
            type: 'info',
            data: [
                'promotional' => true,
                'promo_code' => $promoCode,
                'action_url' => $actionUrl,
                'dismissible' => true
            ],
            targetRole: $targetRole,
            priority: 'low'
        );
    }

    /**
     * Create a service status update notification
     */
    public static function serviceStatus(
        string $title,
        string $message,
        string $status = 'operational', // operational, degraded, outage, maintenance
        ?array $affectedServices = null
    ): self {
        return new self(
            title: $title,
            message: $message,
            type: $status === 'operational' ? 'success' : ($status === 'outage' ? 'error' : 'warning'),
            data: [
                'service_status' => $status,
                'affected_services' => $affectedServices,
                'status_page_url' => config('app.status_page_url'),
                'dismissible' => true
            ],
            priority: $status === 'outage' ? 'critical' : 'normal'
        );
    }
}
