<?php declare(strict_types=1);

namespace App\Services\NotificationSystem;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Services\NotificationSystem\Channels\EmailChannel;
use App\Services\NotificationSystem\Channels\PusherChannel;
use App\Services\NotificationSystem\Channels\SMSChannel;
use App\Services\NotificationSystem\Channels\WebPushChannel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationManager
{
    protected $channels = [];

    protected $priorityQueue = [];

    public function __construct()
    {
        $this->initializeChannels();
    }

    /**
     * Send price drop alert
     */
    /**
     * SendPriceDropAlert
     */
    public function sendPriceDropAlert(User $user, ScrapedTicket $ticket, float $oldPrice, float $newPrice): bool
    {
        $notification = [
            'type'    => 'price_drop',
            'title'   => 'Price Drop Alert! 📉',
            'message' => "Price dropped for {$ticket->event_name} from {$ticket->currency}{$oldPrice} to {$ticket->currency}{$newPrice}",
            'data'    => [
                'ticket_id'  => $ticket->id,
                'event_name' => $ticket->event_name,
                'venue'      => $ticket->venue,
                'event_date' => $ticket->event_date?->toISOString(),
                'old_price'  => $oldPrice,
                'new_price'  => $newPrice,
                'savings'    => $oldPrice - $newPrice,
                'platform'   => $ticket->platform,
                'ticket_url' => $ticket->ticket_url,
                'image_url'  => $ticket->image_url ?? NULL,
            ],
            'priority' => $this->calculatePriority('price_drop', [
                'savings_amount'     => $oldPrice - $newPrice,
                'savings_percentage' => (($oldPrice - $newPrice) / $oldPrice) * 100,
                'event_date'         => $ticket->event_date,
            ]),
            'channels'   => $this->getUserPreferredChannels($user, 'price_drop'),
            'expires_at' => now()->addHours(2),
        ];

        return $this->sendNotification($user, $notification);
    }

    /**
     * Send ticket availability notification
     */
    /**
     * SendTicketAvailabilityAlert
     */
    public function sendTicketAvailabilityAlert(User $user, ScrapedTicket $ticket, TicketAlert $alert): bool
    {
        $notification = [
            'type'    => 'ticket_available',
            'title'   => 'Tickets Available! 🎟️',
            'message' => "New tickets available for {$ticket->event_name} at {$ticket->venue}",
            'data'    => [
                'ticket_id'      => $ticket->id,
                'alert_id'       => $alert->id,
                'event_name'     => $ticket->event_name,
                'venue'          => $ticket->venue,
                'event_date'     => $ticket->event_date?->toISOString(),
                'price'          => $ticket->price,
                'currency'       => $ticket->currency,
                'quantity'       => $ticket->quantity,
                'section'        => $ticket->section,
                'row'            => $ticket->row,
                'platform'       => $ticket->platform,
                'ticket_url'     => $ticket->ticket_url,
                'is_high_demand' => $ticket->is_high_demand,
                'demand_score'   => $ticket->demand_score,
                'match_criteria' => $alert->keywords,
            ],
            'priority' => $this->calculatePriority('ticket_available', [
                'is_high_demand' => $ticket->is_high_demand,
                'demand_score'   => $ticket->demand_score,
                'event_date'     => $ticket->event_date,
                'alert_keywords' => $alert->keywords,
            ]),
            'channels'   => $this->getUserPreferredChannels($user, 'ticket_available'),
            'expires_at' => now()->addMinutes(30),
        ];

        return $this->sendNotification($user, $notification);
    }

    /**
     * Send system status update
     */
    /**
     * SendSystemStatusUpdate
     */
    public function sendSystemStatusUpdate(array $users, string $status, string $message, array $details = []): bool
    {
        $notification = [
            'type'    => 'system_status',
            'title'   => 'System Status Update',
            'message' => $message,
            'data'    => [
                'status'               => $status,
                'timestamp'            => now()->toISOString(),
                'details'              => $details,
                'affected_platforms'   => $details['platforms'] ?? [],
                'estimated_resolution' => $details['eta'] ?? NULL,
            ],
            'priority' => $this->calculatePriority('system_status', [
                'status'   => $status,
                'severity' => $details['severity'] ?? 'medium',
            ]),
            'channels'   => ['pusher', 'email'], // System updates go to all users
            'expires_at' => now()->addHours(1),
        ];

        $success = TRUE;
        foreach ($users as $user) {
            if (! $this->sendNotification($user, $notification)) {
                $success = FALSE;
            }
        }

        return $success;
    }

    /**
     * Send custom alert rule notification
     */
    /**
     * SendCustomAlertRule
     */
    public function sendCustomAlertRule(User $user, array $ruleData, ScrapedTicket $ticket): bool
    {
        $notification = [
            'type'    => 'custom_alert',
            'title'   => $ruleData['title'] ?? 'Custom Alert Triggered!',
            'message' => $ruleData['message'] ?? 'Your custom alert rule has been triggered',
            'data'    => [
                'rule_id'            => $ruleData['id'],
                'rule_name'          => $ruleData['name'],
                'rule_type'          => $ruleData['type'],
                'ticket_id'          => $ticket->id,
                'event_name'         => $ticket->event_name,
                'venue'              => $ticket->venue,
                'event_date'         => $ticket->event_date?->toISOString(),
                'price'              => $ticket->price,
                'platform'           => $ticket->platform,
                'ticket_url'         => $ticket->ticket_url,
                'trigger_conditions' => $ruleData['conditions'],
                'match_details'      => $ruleData['match_details'] ?? [],
            ],
            'priority'   => $ruleData['priority'] ?? 3,
            'channels'   => $ruleData['channels'] ?? $this->getUserPreferredChannels($user, 'custom_alert'),
            'expires_at' => now()->addHours(1),
        ];

        return $this->sendNotification($user, $notification);
    }

    /**
     * Get notification statistics
     */
    /**
     * Get  statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_sent'          => Cache::get('notifications_total_sent', 0),
            'success_rate'        => Cache::get('notifications_success_rate', 100),
            'channel_performance' => [
                'pusher'   => Cache::get('notifications_pusher_success', 100),
                'email'    => Cache::get('notifications_email_success', 95),
                'sms'      => Cache::get('notifications_sms_success', 98),
                'web_push' => Cache::get('notifications_web_push_success', 90),
            ],
            'type_breakdown' => [
                'price_drop'       => Cache::get('notifications_price_drop_count', 0),
                'ticket_available' => Cache::get('notifications_ticket_available_count', 0),
                'system_status'    => Cache::get('notifications_system_status_count', 0),
                'custom_alert'     => Cache::get('notifications_custom_alert_count', 0),
            ],
        ];
    }

    /**
     * InitializeChannels
     */
    protected function initializeChannels(): void
    {
        $this->channels = [
            'pusher'   => new PusherChannel(),
            'email'    => new EmailChannel(),
            'sms'      => new SMSChannel(),
            'web_push' => new WebPushChannel(),
        ];
    }

    /**
     * Send notification to user through specified channels
     */
    /**
     * SendNotification
     */
    protected function sendNotification(User $user, array $notification): bool
    {
        $success = TRUE;
        $channels = $notification['channels'];

        // Add timestamp with CSS cache busting
        $notification['data']['css_timestamp'] = now()->timestamp;

        // Check rate limiting
        if ($this->isRateLimited($user, $notification['type'])) {
            Log::info('Notification rate limited', [
                'user_id' => $user->id,
                'type'    => $notification['type'],
            ]);

            return FALSE;
        }

        // Send through each channel
        foreach ($channels as $channelName) {
            if (! isset($this->channels[$channelName])) {
                Log::warning("Unknown notification channel: {$channelName}");

                continue;
            }

            try {
                $channel = $this->channels[$channelName];

                if (! $channel->send($user, $notification)) {
                    $success = FALSE;
                    Log::error("Failed to send notification via {$channelName}", [
                        'user_id' => $user->id,
                        'type'    => $notification['type'],
                    ]);
                }
            } catch (Throwable $e) {
                $success = FALSE;
                Log::error("Exception sending notification via {$channelName}", [
                    'user_id' => $user->id,
                    'type'    => $notification['type'],
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        // Update rate limiting
        $this->updateRateLimit($user, $notification['type']);

        // Store notification in database for history
        $this->storeNotification($user, $notification);

        return $success;
    }

    /**
     * Calculate notification priority based on type and context
     */
    /**
     * CalculatePriority
     */
    protected function calculatePriority(string $type, array $context): int
    {
        $basePriority = match ($type) {
            'price_drop'       => 4,
            'ticket_available' => 5,
            'system_status'    => 3,
            'custom_alert'     => 3,
            default            => 2,
        };

        // Adjust based on context
        switch ($type) {
            case 'price_drop':
                if (($context['savings_percentage'] ?? 0) > 25) {
                    $basePriority = 5; // High priority for big savings
                }

                break;
            case 'ticket_available':
                if ($context['is_high_demand'] ?? FALSE) {
                    $basePriority = 5;
                }
                if (($context['demand_score'] ?? 0) > 8) {
                    $basePriority = 5;
                }

                break;
            case 'system_status':
                if (($context['severity'] ?? 'medium') === 'critical') {
                    $basePriority = 5;
                }

                break;
        }

        // Time-based urgency for events
        if (isset($context['event_date']) && $context['event_date']) {
            $eventDate = Carbon::parse($context['event_date']);
            $daysUntil = $eventDate->diffInDays(now());

            if ($daysUntil <= 1) {
                $basePriority = min(5, $basePriority + 1);
            } elseif ($daysUntil <= 7) {
                $basePriority = min(5, $basePriority);
            }
        }

        return min(5, max(1, $basePriority));
    }

    /**
     * Get user's preferred notification channels for a type
     */
    /**
     * Get  user preferred channels
     */
    protected function getUserPreferredChannels(User $user, string $type): array
    {
        // Get user preferences from cache or database
        $preferences = Cache::remember("user_notification_prefs_{$user->id}", 3600, fn () => $user->notification_preferences ?? [
            'price_drop'       => ['pusher', 'email'],
            'ticket_available' => ['pusher', 'email', 'web_push'],
            'system_status'    => ['pusher'],
            'custom_alert'     => ['pusher', 'email'],
        ]);

        return $preferences[$type] ?? ['pusher'];
    }

    /**
     * Check if user is rate limited for notification type
     */
    /**
     * Check if  rate limited
     */
    protected function isRateLimited(User $user, string $type): bool
    {
        $limits = [
            'price_drop'       => ['count' => 10, 'period' => 3600], // 10 per hour
            'ticket_available' => ['count' => 20, 'period' => 3600], // 20 per hour
            'system_status'    => ['count' => 5, 'period' => 3600], // 5 per hour
            'custom_alert'     => ['count' => 15, 'period' => 3600], // 15 per hour
        ];

        if (! isset($limits[$type])) {
            return FALSE;
        }

        $limit = $limits[$type];
        $key = "notification_rate_limit_{$user->id}_{$type}";
        $count = Cache::get($key, 0);

        return $count >= $limit['count'];
    }

    /**
     * Update rate limiting counter
     */
    /**
     * UpdateRateLimit
     */
    protected function updateRateLimit(User $user, string $type): void
    {
        $limits = [
            'price_drop'       => 3600,
            'ticket_available' => 3600,
            'system_status'    => 3600,
            'custom_alert'     => 3600,
        ];

        if (! isset($limits[$type])) {
            return;
        }

        $key = "notification_rate_limit_{$user->id}_{$type}";
        $ttl = $limits[$type];

        Cache::increment($key, 1);
        Cache::expire($key, $ttl);
    }

    /**
     * Store notification in database for history
     */
    /**
     * StoreNotification
     */
    protected function storeNotification(User $user, array $notification): void
    {
        try {
            $user->notifications()->create([
                'type' => $notification['type'],
                'data' => [
                    'title'      => $notification['title'],
                    'message'    => $notification['message'],
                    'data'       => $notification['data'],
                    'priority'   => $notification['priority'],
                    'channels'   => $notification['channels'],
                    'expires_at' => $notification['expires_at'],
                ],
                'read_at' => NULL,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to store notification in database', [
                'user_id' => $user->id,
                'type'    => $notification['type'],
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
