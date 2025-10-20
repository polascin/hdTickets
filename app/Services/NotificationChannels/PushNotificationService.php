<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

use App\Models\PushSubscription;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

use function in_array;

class PushNotificationService
{
    private ?WebPush $webPush = NULL;

    private array $config;

    public function __construct()
    {
        $this->config = config('services.webpush', []);

        if ($this->isConfigured()) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject'    => $this->config['vapid']['subject'] ?? config('app.url'),
                    'publicKey'  => $this->config['vapid']['public_key'],
                    'privateKey' => $this->config['vapid']['private_key'],
                ],
                'TTL'     => 300, // 5 minutes
                'urgency' => 'normal',
            ]);
        }
    }

    /**
     * Send push notification to user
     */
    public function send(User $user, array $payload): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Push notification service not configured');

            return FALSE;
        }

        // Get active push subscriptions for the user
        $subscriptions = PushSubscription::where('user_id', $user->id)
            ->where('last_used_at', '>=', now()->subDays(30))
            ->get();

        if ($subscriptions->isEmpty()) {
            Log::info('No push subscriptions found for user', ['user_id' => $user->id]);

            return FALSE;
        }

        $successCount = 0;
        $totalCount = $subscriptions->count();

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $successCount++;
            }
        }

        Log::info('Push notifications sent', [
            'user_id'       => $user->id,
            'success_count' => $successCount,
            'total_count'   => $totalCount,
        ]);

        return $successCount > 0;
    }

    /**
     * Send push notification to specific subscription
     */
    public function sendToSubscription(PushSubscription $subscription, array $payload): bool
    {
        if (! $this->isConfigured() || $this->webPush === NULL) {
            Log::warning('Push notification service not configured for subscription');

            return FALSE;
        }

        try {
            $webPushSubscription = Subscription::create([
                'endpoint'  => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_key,
            ]);

            $notification = $this->webPush->sendOneNotification(
                $webPushSubscription,
                json_encode($this->formatPayload($payload)),
            );

            if ($notification->isSuccess()) {
                $subscription->update([
                    'successful_notifications' => $subscription->successful_notifications + 1,
                    'last_used_at'             => now(),
                ]);

                return TRUE;
            }

            // Handle specific errors
            $statusCode = $notification->getResponse()->getStatusCode();

            if (in_array($statusCode, [400, 404, 410, 413], TRUE)) {
                // Subscription is invalid, remove it
                Log::info('Removing invalid push subscription', [
                    'subscription_id' => $subscription->id,
                    'status_code'     => $statusCode,
                ]);
                $subscription->delete();
            }

            Log::warning('Push notification failed', [
                'subscription_id' => $subscription->id,
                'status_code'     => $statusCode,
                'reason'          => $notification->getReason(),
            ]);

            return FALSE;
        } catch (Exception $e) {
            Log::error('Push notification exception', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $userIds, array $payload): array
    {
        $results = [];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $results[$userId] = $this->send($user, $payload);
            } else {
                $results[$userId] = FALSE;
            }
        }

        return $results;
    }

    /**
     * Send broadcast notification to all subscribed users
     */
    public function broadcast(array $payload, array $criteria = []): int
    {
        $query = PushSubscription::query()->with('user');

        // Apply criteria filters
        if (! empty($criteria['user_roles'])) {
            $query->whereHas('user', function ($q) use ($criteria): void {
                $q->whereIn('role', $criteria['user_roles']);
            });
        }

        if (! empty($criteria['created_after'])) {
            $query->where('created_at', '>=', $criteria['created_after']);
        }

        $subscriptions = $query->get();
        $successCount = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $successCount++;
            }
        }

        Log::info('Broadcast push notification sent', [
            'total_subscriptions' => $subscriptions->count(),
            'successful_sends'    => $successCount,
            'criteria'            => $criteria,
        ]);

        return $successCount;
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(User $user, array $subscriptionData): PushSubscription
    {
        // Remove existing subscription for the same endpoint
        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $subscriptionData['endpoint'])
            ->delete();

        return PushSubscription::create([
            'user_id'    => $user->id,
            'endpoint'   => $subscriptionData['endpoint'],
            'p256dh_key' => $subscriptionData['keys']['p256dh'],
            'auth_key'   => $subscriptionData['keys']['auth'],
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(User $user, ?string $endpoint = NULL): int
    {
        $query = PushSubscription::where('user_id', $user->id);

        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }

        return $query->delete();
    }

    /**
     * Get VAPID public key for client-side subscription
     */
    public function getVapidPublicKey(): string
    {
        return $this->config['vapid']['public_key'] ?? '';
    }

    /**
     * Test push notification functionality
     */
    public function testNotification(User $user): bool
    {
        $testPayload = [
            'title' => '🎫 HD Tickets Test',
            'body'  => 'Push notifications are working correctly!',
            'data'  => [
                'type' => 'test',
                'url'  => route('dashboard'),
            ],
        ];

        return $this->send($user, $testPayload);
    }

    /**
     * Get subscription statistics
     */
    public function getStats(): array
    {
        return [
            'total_subscriptions'                => PushSubscription::count(),
            'active_subscriptions'               => PushSubscription::where('last_used_at', '>=', now()->subDays(30))->count(),
            'subscriptions_today'                => PushSubscription::whereDate('created_at', today())->count(),
            'avg_notifications_per_subscription' => PushSubscription::avg('successful_notifications'),
        ];
    }

    /**
     * Clean up expired or invalid subscriptions
     */
    public function cleanupSubscriptions(): int
    {
        // Remove subscriptions not used in the last 90 days
        $deletedCount = PushSubscription::where('last_used_at', '<', now()->subDays(90))
            ->orWhere(function ($query): void {
                $query->whereNull('last_used_at')
                    ->where('created_at', '<', now()->subDays(7));
            })
            ->delete();

        Log::info('Cleaned up expired push subscriptions', [
            'deleted_count' => $deletedCount,
        ]);

        return $deletedCount;
    }

    /**
     * Format payload for web push
     */
    private function formatPayload(array $payload): array
    {
        return [
            'title' => $payload['title'] ?? 'HD Tickets',
            'body'  => $payload['body'] ?? '',
            'icon'  => $payload['icon'] ?? asset('images/logo-hdtickets-enhanced.svg'),
            'badge' => $payload['badge'] ?? asset('images/logo-hdtickets-enhanced.svg'),
            'image' => $payload['image'] ?? NULL,
            'data'  => array_merge([
                'timestamp' => now()->toISOString(),
                'origin'    => config('app.url'),
            ], $payload['data'] ?? []),
            'actions' => $payload['actions'] ?? [
                [
                    'action' => 'view',
                    'title'  => 'View Details',
                    'icon'   => asset('images/icons/view.svg'),
                ],
                [
                    'action' => 'dismiss',
                    'title'  => 'Dismiss',
                    'icon'   => asset('images/icons/close.svg'),
                ],
            ],
            'tag'                => $payload['tag'] ?? 'hd-tickets-notification',
            'requireInteraction' => $payload['require_interaction'] ?? FALSE,
            'silent'             => $payload['silent'] ?? FALSE,
            'vibrate'            => $payload['vibrate'] ?? [200, 100, 200],
        ];
    }

    /**
     * Check if push notification service is configured
     */
    private function isConfigured(): bool
    {
        return ! empty($this->config['vapid']['public_key'])
               && ! empty($this->config['vapid']['private_key']);
    }
}
