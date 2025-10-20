<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\EnhancedSmartAlertsService;
use App\Services\NotificationChannels\SmsNotificationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

use function strlen;

/**
 * Enhanced SMS Notification Job
 *
 * Handles SMS delivery with tracking, retry logic, and intelligent content
 */
class SendEnhancedSmsNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 15;

    public int $timeout = 45;

    public function __construct(
        private string $alertId,
        private User $user,
        private array $alertData,
    ) {
    }

    public function handle(
        SmsNotificationService $smsService,
        EnhancedSmartAlertsService $alertsService,
    ): void {
        $startTime = microtime(TRUE);

        try {
            // Validate phone number
            if (empty($this->user->phone)) {
                throw new Exception('User has no phone number');
            }

            $message = $this->buildEnhancedMessage();

            $result = $smsService->send($this->user->phone, $message);

            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            if ($result['success'] ?? FALSE) {
                $alertsService->updateEnhancedDeliveryStatus(
                    $this->alertId,
                    'sms',
                    'delivered',
                    [
                        'response_time' => $responseTime,
                        'message_id'    => $result['message_id'] ?? NULL,
                        'cost'          => $result['cost'] ?? NULL,
                        'segments'      => $result['segments'] ?? 1,
                    ],
                );

                Log::info('Enhanced SMS delivered', [
                    'alert_id'      => $this->alertId,
                    'user_id'       => $this->user->id,
                    'phone'         => $this->maskPhoneNumber($this->user->phone),
                    'response_time' => $responseTime,
                    'segments'      => $result['segments'] ?? 1,
                ]);
            } else {
                throw new Exception($result['error'] ?? 'SMS delivery failed');
            }
        } catch (Exception $e) {
            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            $alertsService->updateEnhancedDeliveryStatus(
                $this->alertId,
                'sms',
                'failed',
                [
                    'error'         => $e->getMessage(),
                    'response_time' => $responseTime,
                    'attempt'       => $this->attempts(),
                ],
            );

            Log::error('Enhanced SMS failed', [
                'alert_id' => $this->alertId,
                'user_id'  => $this->user->id,
                'phone'    => $this->maskPhoneNumber($this->user->phone ?? ''),
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
        Log::error('Enhanced SMS job failed permanently', [
            'alert_id' => $this->alertId,
            'user_id'  => $this->user->id,
            'phone'    => $this->maskPhoneNumber($this->user->phone ?? ''),
            'error'    => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Build enhanced SMS message with smart content optimization
     */
    private function buildEnhancedMessage(): string
    {
        $type = $this->alertData['type'] ?? 'general';
        $urgency = $this->alertData['urgency'] ?? 'medium';
        $eventName = $this->alertData['event_name'] ?? 'Event';

        // Get emoji and urgency prefix
        $prefix = $this->getUrgencyPrefix($urgency);

        // Build core message based on type
        $coreMessage = match ($type) {
            'price_drop'            => $this->buildPriceDropMessage($prefix, $eventName),
            'new_listing'           => $this->buildNewListingMessage($prefix, $eventName),
            'availability_restored' => $this->buildAvailabilityMessage($prefix, $eventName),
            'low_inventory'         => $this->buildLowInventoryMessage($prefix, $eventName),
            default                 => $this->buildGenericMessage($prefix, $eventName),
        };

        // Add call-to-action and link
        $actionUrl = $this->getShortUrl();
        $cta = $this->getCallToAction($type, $urgency);

        $fullMessage = "{$coreMessage} {$cta} {$actionUrl}";

        // Ensure message fits SMS limits (160 chars for single segment)
        if (strlen($fullMessage) > 160) {
            $fullMessage = $this->optimizeForLength($coreMessage, $cta, $actionUrl);
        }

        return $fullMessage;
    }

    /**
     * Get urgency prefix with emoji
     */
    private function getUrgencyPrefix(string $urgency): string
    {
        return match ($urgency) {
            'critical' => '🚨 URGENT',
            'high'     => '⚡ ALERT',
            'medium'   => '📢',
            'low'      => '💡',
            default    => '🎫',
        };
    }

    /**
     * Build price drop SMS message
     */
    private function buildPriceDropMessage(string $prefix, string $eventName): string
    {
        $oldPrice = $this->alertData['old_price'] ?? 0;
        $newPrice = $this->alertData['new_price'] ?? 0;
        $savings = $oldPrice - $newPrice;

        return "{$prefix} {$eventName} tickets dropped £{$savings}! Now £{$newPrice}";
    }

    /**
     * Build new listing SMS message
     */
    private function buildNewListingMessage(string $prefix, string $eventName): string
    {
        $minPrice = $this->alertData['min_price'] ?? 0;
        $platform = $this->alertData['platform'] ?? '';

        if ($platform) {
            return "{$prefix} NEW {$eventName} tickets on {$platform} from £{$minPrice}";
        }

        return "{$prefix} NEW {$eventName} tickets available from £{$minPrice}";
    }

    /**
     * Build availability restored SMS message
     */
    private function buildAvailabilityMessage(string $prefix, string $eventName): string
    {
        $minPrice = $this->alertData['min_price'] ?? 0;

        return "{$prefix} {$eventName} BACK IN STOCK! From £{$minPrice}";
    }

    /**
     * Build low inventory SMS message
     */
    private function buildLowInventoryMessage(string $prefix, string $eventName): string
    {
        $remaining = $this->alertData['remaining_tickets'] ?? 0;

        return "{$prefix} ONLY {$remaining} {$eventName} tickets left!";
    }

    /**
     * Build generic SMS message
     */
    private function buildGenericMessage(string $prefix, string $eventName): string
    {
        $message = $this->alertData['message'] ?? "{$eventName} update";

        return "{$prefix} {$message}";
    }

    /**
     * Get call-to-action based on type and urgency
     */
    private function getCallToAction(string $type, string $urgency): string
    {
        if ($urgency === 'critical') {
            return 'BUY NOW!';
        }

        return match ($type) {
            'price_drop'            => 'Save now!',
            'new_listing'           => 'View tickets!',
            'availability_restored' => 'Get yours!',
            'low_inventory'         => 'Hurry!',
            default                 => 'Check it!',
        };
    }

    /**
     * Get shortened URL for SMS
     */
    private function getShortUrl(): string
    {
        // In production, this would use a URL shortener service
        $eventId = $this->alertData['event_id'] ?? NULL;

        if ($eventId) {
            return "hdtix.co/e/{$eventId}";
        }

        return 'hdtix.co/dash';
    }

    /**
     * Optimize message for SMS length constraints
     */
    private function optimizeForLength(string $coreMessage, string $cta, string $url): string
    {
        $maxLength = 155; // Leave 5 chars buffer
        $requiredLength = strlen($cta) + strlen($url) + 2; // +2 for spaces
        $availableForCore = $maxLength - $requiredLength;

        if (strlen($coreMessage) > $availableForCore) {
            $coreMessage = substr($coreMessage, 0, $availableForCore - 3) . '...';
        }

        return "{$coreMessage} {$cta} {$url}";
    }

    /**
     * Mask phone number for logging
     */
    private function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) < 4) {
            return '****';
        }

        return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
    }
}
