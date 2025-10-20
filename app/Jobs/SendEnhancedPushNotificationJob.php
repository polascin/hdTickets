<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\EnhancedSmartAlertsService;
use App\Services\NotificationChannels\PushNotificationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Enhanced Push Notification Job
 *
 * Handles push notification delivery with tracking and retry logic
 */
class SendEnhancedPushNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $timeout = 30;

    public function __construct(
        private string $alertId,
        private User $user,
        private array $alertData,
    ) {
    }

    public function handle(
        PushNotificationService $pushService,
        EnhancedSmartAlertsService $alertsService,
    ): void {
        $startTime = microtime(TRUE);

        try {
            $payload = $this->buildEnhancedPayload();

            $result = $pushService->send($this->user, $payload);

            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            if ($result['success'] ?? FALSE) {
                $alertsService->updateEnhancedDeliveryStatus(
                    $this->alertId,
                    'push',
                    'delivered',
                    [
                        'response_time' => $responseTime,
                        'message_id'    => $result['message_id'] ?? NULL,
                        'delivery_id'   => $result['delivery_id'] ?? NULL,
                    ],
                );

                Log::info('Enhanced push notification delivered', [
                    'alert_id'      => $this->alertId,
                    'user_id'       => $this->user->id,
                    'response_time' => $responseTime,
                ]);
            } else {
                throw new Exception($result['error'] ?? 'Push notification failed');
            }
        } catch (Exception $e) {
            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            $alertsService->updateEnhancedDeliveryStatus(
                $this->alertId,
                'push',
                'failed',
                [
                    'error'         => $e->getMessage(),
                    'response_time' => $responseTime,
                    'attempt'       => $this->attempts(),
                ],
            );

            Log::error('Enhanced push notification failed', [
                'alert_id' => $this->alertId,
                'user_id'  => $this->user->id,
                'error'    => $e->getMessage(),
                'attempt'  => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff * $this->attempts());
            } else {
                $this->fail($e);
            }
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Enhanced push notification job failed permanently', [
            'alert_id' => $this->alertId,
            'user_id'  => $this->user->id,
            'error'    => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Build enhanced push payload with rich content
     */
    private function buildEnhancedPayload(): array
    {
        $urgency = $this->alertData['urgency'] ?? 'medium';
        $type = $this->alertData['type'] ?? 'general';

        $basePayload = [
            'title' => $this->getTitle(),
            'body'  => $this->getBody(),
            'icon'  => asset('images/logo-hdtickets-enhanced.svg'),
            'badge' => asset('images/notification-badge.png'),
            'data'  => [
                'alert_id'  => $this->alertId,
                'type'      => $type,
                'urgency'   => $urgency,
                'timestamp' => now()->toISOString(),
                'url'       => $this->getActionUrl(),
            ],
        ];

        // Add urgency-specific enhancements
        if ($urgency === 'critical') {
            $basePayload['requireInteraction'] = TRUE;
            $basePayload['silent'] = FALSE;
            $basePayload['vibrate'] = [200, 100, 200, 100, 200];
        }

        // Add rich actions based on alert type
        $basePayload['actions'] = $this->getActions($type);

        // Add rich media if available
        if (isset($this->alertData['image'])) {
            $basePayload['image'] = $this->alertData['image'];
        }

        return $basePayload;
    }

    /**
     * Get notification title based on alert type
     */
    private function getTitle(): string
    {
        $urgency = $this->alertData['urgency'] ?? 'medium';
        $type = $this->alertData['type'] ?? 'general';

        $prefix = match ($urgency) {
            'critical' => '🚨 URGENT',
            'high'     => '⚡ ALERT',
            'medium'   => '📢 Notice',
            'low'      => '💡 Info',
            default    => '📣 HD Tickets',
        };

        return match ($type) {
            'price_drop'            => "{$prefix}: Price Drop Alert!",
            'new_listing'           => "{$prefix}: New Tickets Available!",
            'availability_restored' => "{$prefix}: Tickets Back in Stock!",
            'low_inventory'         => "{$prefix}: Limited Tickets Left!",
            default                 => "{$prefix}: Ticket Alert",
        };
    }

    /**
     * Get notification body content
     */
    private function getBody(): string
    {
        $type = $this->alertData['type'] ?? 'general';
        $eventName = $this->alertData['event_name'] ?? 'Event';

        return match ($type) {
            'price_drop'            => "{$eventName} tickets dropped to £{$this->alertData['new_price']} (was £{$this->alertData['old_price']})!",
            'new_listing'           => "New {$eventName} tickets available from £{$this->alertData['min_price']}!",
            'availability_restored' => "{$eventName} tickets are back in stock! Starting at £{$this->alertData['min_price']}",
            'low_inventory'         => "Only {$this->alertData['remaining_tickets']} tickets left for {$eventName}!",
            default                 => $this->alertData['message'] ?? 'Check your HD Tickets dashboard for updates.',
        };
    }

    /**
     * Get action URL for the notification
     */
    private function getActionUrl(): string
    {
        $type = $this->alertData['type'] ?? 'general';
        $eventId = $this->alertData['event_id'] ?? NULL;

        return match ($type) {
            'price_drop', 'new_listing', 'availability_restored', 'low_inventory' => $eventId ? route('events.show', $eventId) : route('dashboard'),
            default => route('dashboard'),
        };
    }

    /**
     * Get notification actions based on alert type
     */
    private function getActions(string $type): array
    {
        $baseActions = [
            [
                'action' => 'view',
                'title'  => 'View Details',
                'icon'   => asset('images/icons/view.png'),
            ],
        ];

        switch ($type) {
            case 'price_drop':
            case 'new_listing':
            case 'availability_restored':
                $baseActions[] = [
                    'action' => 'buy',
                    'title'  => 'Buy Now',
                    'icon'   => asset('images/icons/buy.png'),
                ];

                break;
            case 'low_inventory':
                $baseActions[] = [
                    'action' => 'hurry',
                    'title'  => 'Buy Before Sold Out',
                    'icon'   => asset('images/icons/urgent.png'),
                ];

                break;
        }

        return $baseActions;
    }
}
