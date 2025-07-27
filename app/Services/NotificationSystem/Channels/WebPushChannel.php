<?php

namespace App\Services\NotificationSystem\Channels;

use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushChannel implements NotificationChannelInterface
{
    protected $webPush;

    public function __construct()
    {
        if ($this->isAvailable()) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ]);
        }
    }

    public function send(User $user, array $notification): bool
    {
        try {
            if (!$this->webPush) {
                Log::warning('WebPush not configured, skipping push notification');
                return false;
            }

            // Get user's push subscriptions
            $subscriptions = WebPushSubscription::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            if ($subscriptions->isEmpty()) {
                Log::info('No active push subscriptions for user', [
                    'user_id' => $user->id,
                    'type' => $notification['type'],
                ]);
                return false;
            }

            $payload = $this->buildPushPayload($notification);
            $success = true;

            foreach ($subscriptions as $subscription) {
                try {
                    $sub = Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->p256dh_key,
                        'authToken' => $subscription->auth_token,
                    ]);

                    $result = $this->webPush->sendOneNotification($sub, json_encode($payload));

                    if (!$result->isSuccess()) {
                        $success = false;
                        Log::error('Push notification failed', [
                            'user_id' => $user->id,
                            'subscription_id' => $subscription->id,
                            'error' => $result->getReason(),
                        ]);

                        // Handle expired subscriptions
                        if ($result->isSubscriptionExpired()) {
                            $subscription->update(['is_active' => false]);
                        }
                    }

                } catch (\Throwable $e) {
                    $success = false;
                    Log::error('Exception sending push notification', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($success) {
                Log::info('Web push notifications sent successfully', [
                    'user_id' => $user->id,
                    'type' => $notification['type'],
                    'subscriptions_count' => $subscriptions->count(),
                ]);
            }

            return $success;

        } catch (\Throwable $e) {
            Log::error('Failed to send web push notification', [
                'user_id' => $user->id,
                'type' => $notification['type'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function buildPushPayload(array $notification): array
    {
        $payload = [
            'title' => $notification['title'],
            'body' => $notification['message'],
            'icon' => '/images/icons/notification-icon.png',
            'badge' => '/images/icons/badge-icon.png',
            'tag' => $notification['type'],
            'data' => [
                'type' => $notification['type'],
                'timestamp' => now()->timestamp,
                'css_timestamp' => $notification['data']['css_timestamp'] ?? now()->timestamp,
                'url' => $this->getNotificationUrl($notification),
                'data' => $notification['data'],
            ],
            'actions' => $this->buildPushActions($notification),
            'requireInteraction' => $notification['priority'] >= 4,
            'silent' => false,
        ];

        // Add custom fields based on notification type
        switch ($notification['type']) {
            case 'price_drop':
                $payload['icon'] = '/images/icons/price-drop-icon.png';
                $payload['body'] = "💰 " . $payload['body'];
                break;

            case 'ticket_available':
                $payload['icon'] = '/images/icons/ticket-icon.png';
                $payload['body'] = "🎟️ " . $payload['body'];
                break;

            case 'system_status':
                $payload['icon'] = '/images/icons/system-icon.png';
                $payload['body'] = "⚙️ " . $payload['body'];
                break;

            case 'custom_alert':
                $payload['icon'] = '/images/icons/alert-icon.png';
                $payload['body'] = "🔔 " . $payload['body'];
                break;
        }

        return $payload;
    }

    protected function buildPushActions(array $notification): array
    {
        $actions = [];

        switch ($notification['type']) {
            case 'price_drop':
            case 'ticket_available':
                $actions = [
                    [
                        'action' => 'view',
                        'title' => 'View Tickets',
                        'icon' => '/images/icons/view-icon.png',
                    ],
                    [
                        'action' => 'dismiss',
                        'title' => 'Dismiss',
                        'icon' => '/images/icons/dismiss-icon.png',
                    ],
                ];
                break;

            case 'system_status':
                $actions = [
                    [
                        'action' => 'status',
                        'title' => 'View Status',
                        'icon' => '/images/icons/status-icon.png',
                    ],
                ];
                break;
        }

        return $actions;
    }

    protected function getNotificationUrl(array $notification): string
    {
        switch ($notification['type']) {
            case 'price_drop':
            case 'ticket_available':
                if (!empty($notification['data']['ticket_id'])) {
                    return route('tickets.scraping.show', $notification['data']['ticket_id']);
                }
                return $notification['data']['ticket_url'] ?? route('tickets.scraping.index');

            case 'system_status':
                return route('system.status');

            case 'custom_alert':
                if (!empty($notification['data']['rule_id'])) {
                    return route('tickets.alerts.show', $notification['data']['rule_id']);
                }
                return route('tickets.alerts.index');

            default:
                return route('dashboard');
        }
    }

    public function isAvailable(): bool
    {
        return !empty(config('services.webpush.public_key')) &&
               !empty(config('services.webpush.private_key'));
    }
}
