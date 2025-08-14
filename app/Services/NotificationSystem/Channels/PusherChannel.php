<?php declare(strict_types=1);

namespace App\Services\NotificationSystem\Channels;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use Throwable;

use function in_array;

class PusherChannel implements NotificationChannelInterface
{
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster'   => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS'    => TRUE,
                'encrypted' => TRUE,
            ],
        );
    }

    /**
     * Send
     */
    public function send(User $user, array $notification): bool
    {
        try {
            // Send to user's private channel
            $channelName = "private-user.{$user->id}";
            $eventName = "notification.{$notification['type']}";

            $data = [
                'id'            => uniqid('notif_'),
                'type'          => $notification['type'],
                'title'         => $notification['title'],
                'message'       => $notification['message'],
                'data'          => $notification['data'],
                'priority'      => $notification['priority'],
                'timestamp'     => now()->toISOString(),
                'expires_at'    => $notification['expires_at']->toISOString(),
                'css_timestamp' => $notification['data']['css_timestamp'] ?? now()->timestamp,
                'actions'       => $this->buildActions($notification),
            ];

            // Send notification
            $this->pusher->trigger($channelName, $eventName, $data);

            // Also send to general notification channel for desktop display
            $this->pusher->trigger($channalName, 'notification', [
                'show_desktop' => TRUE,
                'priority'     => $notification['priority'],
                ...$data,
            ]);

            // Send system-wide notifications if needed
            if (in_array($notification['type'], ['system_status'], TRUE)) {
                $this->pusher->trigger('system-updates', 'notification', $data);
            }

            Log::info('Pusher notification sent successfully', [
                'user_id' => $user->id,
                'type'    => $notification['type'],
                'channel' => $channelName,
            ]);

            return TRUE;
        } catch (Throwable $e) {
            Log::error('Failed to send Pusher notification', [
                'user_id' => $user->id,
                'type'    => $notification['type'],
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Check if  available
     */
    public function isAvailable(): bool
    {
        return ! empty(config('broadcasting.connections.pusher.key'))
               && ! empty(config('broadcasting.connections.pusher.secret'))
               && ! empty(config('broadcasting.connections.pusher.app_id'));
    }

    /**
     * BuildActions
     */
    protected function buildActions(array $notification): array
    {
        $actions = [];

        switch ($notification['type']) {
            case 'price_drop':
            case 'ticket_available':
                $actions = [
                    [
                        'label'    => 'View Tickets',
                        'url'      => $notification['data']['ticket_url'] ?? '#',
                        'style'    => 'primary',
                        'external' => TRUE,
                    ],
                    [
                        'label' => 'View Details',
                        'url'   => route('tickets.scraping.show', $notification['data']['ticket_id']),
                        'style' => 'secondary',
                    ],
                ];

                break;
            case 'system_status':
                $actions = [
                    [
                        'label' => 'View Status',
                        'url'   => route('system.status'),
                        'style' => 'primary',
                    ],
                ];

                break;
            case 'custom_alert':
                $actions = [
                    [
                        'label' => 'View Alert',
                        'url'   => route('tickets.alerts.show', $notification['data']['rule_id']),
                        'style' => 'primary',
                    ],
                ];

                break;
        }

        return $actions;
    }
}
