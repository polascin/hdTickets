<?php declare(strict_types=1);

namespace App\Services;

use App\Events\SystemNotification;
use App\Services\Core\BaseService;
use App\Services\Interfaces\NotificationInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Override;

use function count;
use function in_array;
use function is_array;

/**
 * Consolidated Notification Service
 *
 * Unified multi-channel notification delivery for sport events entry tickets
 * with intelligent routing, rate limiting, and user preference management.
 */
class NotificationService extends BaseService implements NotificationInterface
{
    private const NOTIFICATIONS_PREFIX = 'notifications:';

    /** @var array<int, string> */
    private const CHANNELS = ['database', 'broadcast', 'push', 'mail', 'sms', 'discord', 'slack', 'telegram', 'webhook'];

    private const PRIORITY_HIGH = 'high';

    private const PRIORITY_NORMAL = 'normal';

    private const PRIORITY_LOW = 'low';

    public $analytics;

    /**
     * Send ticket availability alert
     *
     * @param array<string, mixed> $ticketData Ticket information
     * @param array<int, int>      $userIds    Target user IDs (empty for auto-discovery)
     * @param string               $priority   Alert priority level
     */
    /**
     * SendTicketAlert
     */
    public function sendTicketAlert(array $ticketData, array $userIds = [], string $priority = self::PRIORITY_NORMAL): void
    {
        try {
            $notification = [
                'id'         => $this->generateNotificationId(),
                'type'       => 'ticket_alert',
                'title'      => 'New Tickets Available!',
                'message'    => $this->buildTicketAlertMessage($ticketData),
                'data'       => $ticketData,
                'priority'   => $priority,
                'channels'   => $this->getChannelsForPriority($priority),
                'created_at' => Carbon::now()->toISOString(),
                'expires_at' => Carbon::now()->addHours(24)->toISOString(),
            ];

            if ($userIds === []) {
                // Send to all subscribers interested in this sport/event
                $userIds = $this->getInterestedUsers();
            }

            $this->dispatchNotification($notification, $userIds);
            $this->trackNotificationSent($notification, count($userIds));
        } catch (Exception $e) {
            Log::error('Failed to send ticket alert', [
                'ticket_data' => $ticketData,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send price update notification
     *
     * @param int             $ticketId Ticket ID for price update
     * @param float           $oldPrice Previous ticket price
     * @param float           $newPrice New ticket price
     * @param array<int, int> $userIds  Target user IDs (empty for auto-discovery)
     */
    /**
     * SendPriceUpdate
     */
    public function sendPriceUpdate(int $ticketId, float $oldPrice, float $newPrice, array $userIds = []): void
    {
        try {
            $priceChange = $newPrice - $oldPrice;
            $percentChange = ($priceChange / $oldPrice) * 100;
            $isPriceDrop = $priceChange < 0;

            $notification = [
                'id'      => $this->generateNotificationId(),
                'type'    => 'price_update',
                'title'   => $isPriceDrop ? 'Price Drop Alert!' : 'Price Update',
                'message' => $this->buildPriceUpdateMessage($oldPrice, $newPrice, $percentChange),
                'data'    => [
                    'ticket_id'      => $ticketId,
                    'old_price'      => $oldPrice,
                    'new_price'      => $newPrice,
                    'change_amount'  => $priceChange,
                    'change_percent' => $percentChange,
                    'is_price_drop'  => $isPriceDrop,
                ],
                'priority'   => $isPriceDrop ? self::PRIORITY_HIGH : self::PRIORITY_NORMAL,
                'channels'   => $isPriceDrop ? ['database', 'broadcast', 'push'] : ['database', 'broadcast'],
                'created_at' => Carbon::now()->toISOString(),
                'expires_at' => Carbon::now()->addHours(6)->toISOString(),
            ];

            if ($userIds === []) {
                // Send to users watching this ticket
                $userIds = $this->getUsersWatchingTicket();
            }

            $this->dispatchNotification($notification, $userIds);
            $this->trackNotificationSent($notification, count($userIds));

            // Track analytics
            $this->analytics->trackEvent('price_update_notification', [
                'ticket_id'      => $ticketId,
                'price_change'   => $priceChange,
                'percent_change' => $percentChange,
                'user_count'     => count($userIds),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send price update', [
                'ticket_id' => $ticketId,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send system notification
     *
     * @param string               $message Notification message
     * @param string               $type    Notification type (info, warning, error, etc.)
     * @param array<int, int>      $userIds Target user IDs (empty for broadcast)
     * @param array<string, mixed> $data    Additional notification data
     */
    /**
     * SendSystemNotification
     */
    public function sendSystemNotification(string $message, string $type = 'info', array $userIds = [], array $data = []): void
    {
        try {
            $notification = [
                'id'         => $this->generateNotificationId(),
                'type'       => 'system_notification',
                'title'      => $this->getSystemNotificationTitle($type),
                'message'    => $message,
                'data'       => array_merge($data, ['notification_type' => $type]),
                'priority'   => $this->getPriorityForSystemNotification($type),
                'channels'   => $this->getChannelsForSystemNotification($type),
                'created_at' => Carbon::now()->toISOString(),
                'expires_at' => Carbon::now()->addDays(3)->toISOString(),
            ];

            // Send to all active users for important system notifications
            if (empty($userIds) && in_array($type, ['maintenance', 'security', 'outage'], TRUE)) {
                $userIds = $this->getAllActiveUsers();
            }

            $this->dispatchNotification($notification, $userIds);
            $this->trackNotificationSent($notification, count($userIds));
        } catch (Exception $e) {
            Log::error('Failed to send system notification', [
                'message' => $message,
                'type'    => $type,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send bulk notification to multiple users
     *
     * @param array<string, mixed> $notification Notification data
     * @param array<int, int>      $userIds      Target user IDs
     */
    /**
     * SendBulkNotification
     */
    public function sendBulkNotification(array $notification, array $userIds): void
    {
        try {
            $batchSize = 1000; // Process in batches
            $batches = array_chunk($userIds, $batchSize);

            foreach ($batches as $batch) {
                $this->processBulkBatch($notification, $batch);
            }

            $this->trackNotificationSent($notification, count($userIds));
        } catch (Exception $e) {
            Log::error('Failed to send bulk notification', [
                'notification' => $notification,
                'user_count'   => count($userIds),
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get user notification preferences
     *
     * @param int $userId User ID to get preferences for
     *
     * @return array<string, mixed> User notification preferences
     */
    /**
     * Get  user notification preferences
     */
    public function getUserNotificationPreferences(int $userId): array
    {
        try {
            $prefsKey = self::NOTIFICATIONS_PREFIX . 'preferences:' . $userId;
            $preferences = Redis::hgetall($prefsKey);

            return [
                'ticket_alerts'        => $preferences['ticket_alerts'] ?? 'enabled',
                'price_updates'        => $preferences['price_updates'] ?? 'enabled',
                'system_notifications' => $preferences['system_notifications'] ?? 'enabled',
                'email_notifications'  => $preferences['email_notifications'] ?? 'enabled',
                'push_notifications'   => $preferences['push_notifications'] ?? 'enabled',
                'sms_notifications'    => $preferences['sms_notifications'] ?? 'disabled',
                'quiet_hours_start'    => $preferences['quiet_hours_start'] ?? '22:00',
                'quiet_hours_end'      => $preferences['quiet_hours_end'] ?? '08:00',
                'preferred_sports'     => json_decode($preferences['preferred_sports'] ?? '[]', TRUE),
                'price_threshold'      => (float) ($preferences['price_threshold'] ?? 0),
                'frequency_limit'      => $preferences['frequency_limit'] ?? 'normal',
            ];
        } catch (Exception $e) {
            Log::error('Failed to get user notification preferences', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return $this->getDefaultNotificationPreferences();
        }
    }

    /**
     * Update user notification preferences
     *
     * @param int                  $userId      User ID to update preferences for
     * @param array<string, mixed> $preferences New preference values
     *
     * @return bool Whether update was successful
     */
    /**
     * UpdateUserNotificationPreferences
     */
    public function updateUserNotificationPreferences(int $userId, array $preferences): bool
    {
        try {
            $prefsKey = self::NOTIFICATIONS_PREFIX . 'preferences:' . $userId;

            // Validate and sanitize preferences
            $validatedPrefs = $this->validateNotificationPreferences($preferences);

            foreach ($validatedPrefs as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                Redis::hset($prefsKey, $key, $value);
            }

            Redis::expire($prefsKey, 7776000); // 90 days retention

            $this->analytics->trackEvent('notification_preferences_updated', [
                'user_id'        => $userId,
                'updated_fields' => array_keys($validatedPrefs),
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to update user notification preferences', [
                'user_id'     => $userId,
                'preferences' => $preferences,
                'error'       => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get user notifications with pagination
     *
     * @param int $userId  User ID to get notifications for
     * @param int $page    Page number for pagination
     * @param int $perPage Number of notifications per page
     *
     * @return array<string, mixed> Paginated notifications data
     */
    /**
     * Get  user notifications
     */
    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 50): array
    {
        try {
            $userNotificationsKey = self::NOTIFICATIONS_PREFIX . 'user:' . $userId;
            $start = ($page - 1) * $perPage;
            $end = $start + $perPage - 1;

            $notificationIds = Redis::lrange($userNotificationsKey, $start, $end);
            $notifications = [];

            foreach ($notificationIds as $notificationId) {
                $notificationKey = self::NOTIFICATIONS_PREFIX . 'data:' . $notificationId;
                $notificationData = Redis::hgetall($notificationKey);

                if (! empty($notificationData)) {
                    $notifications[] = [
                        'id'         => $notificationId,
                        'type'       => $notificationData['type'] ?? '',
                        'title'      => $notificationData['title'] ?? '',
                        'message'    => $notificationData['message'] ?? '',
                        'data'       => json_decode($notificationData['data'] ?? '{}', TRUE),
                        'read'       => $notificationData['read'] ?? FALSE,
                        'priority'   => $notificationData['priority'] ?? self::PRIORITY_NORMAL,
                        'created_at' => $notificationData['created_at'] ?? '',
                        'read_at'    => $notificationData['read_at'] ?? NULL,
                    ];
                }
            }

            $totalCount = Redis::llen($userNotificationsKey);

            return [
                'notifications' => $notifications,
                'pagination'    => [
                    'current_page' => $page,
                    'per_page'     => $perPage,
                    'total'        => $totalCount,
                    'last_page'    => ceil($totalCount / $perPage),
                    'has_more'     => $end < $totalCount - 1,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get user notifications', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return ['notifications' => [], 'pagination' => []];
        }
    }

    /**
     * Mark notification as read
     */
    /**
     * MarkNotificationAsRead
     */
    public function markNotificationAsRead(string $notificationId, int $userId): bool
    {
        try {
            $notificationKey = self::NOTIFICATIONS_PREFIX . 'data:' . $notificationId;

            Redis::hset($notificationKey, 'read', TRUE);
            Redis::hset($notificationKey, 'read_at', Carbon::now()->toISOString());

            // Update unread count
            $unreadKey = self::NOTIFICATIONS_PREFIX . 'unread:' . $userId;
            Redis::decr($unreadKey);

            $this->analytics->trackEvent('notification_read', [
                'notification_id' => $notificationId,
                'user_id'         => $userId,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id'         => $userId,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get unread notification count for user
     */
    /**
     * Get  unread notification count
     */
    public function getUnreadNotificationCount(int $userId): int
    {
        try {
            $unreadKey = self::NOTIFICATIONS_PREFIX . 'unread:' . $userId;

            return (int) Redis::get($unreadKey) ?: 0;
        } catch (Exception $e) {
            Log::error('Failed to get unread notification count', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Clean up expired notifications
     */
    /**
     * CleanupExpiredNotifications
     */
    public function cleanupExpiredNotifications(): int
    {
        try {
            $cleaned = 0;
            $now = Carbon::now();
            $pattern = self::NOTIFICATIONS_PREFIX . 'data:*';
            $keys = Redis::keys($pattern);

            foreach ($keys as $key) {
                $expiresAt = Redis::hget($key, 'expires_at');
                if ($expiresAt && Carbon::parse($expiresAt)->isPast()) {
                    Redis::del($key);
                    $cleaned++;
                }
            }

            Log::info('Cleaned up expired notifications', ['count' => $cleaned]);

            return $cleaned;
        } catch (Exception $e) {
            Log::error('Failed to cleanup expired notifications', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * OnInitialize
     */
    #[Override]
    protected function onInitialize(): void
    {
        $this->validateDependencies(['analyticsService']);
    }

    /**
     * Private helper methods
     */
    /**
     * GenerateNotificationId
     */
    private function generateNotificationId(): string
    {
        return 'notif_' . uniqid() . '_' . time();
    }

    /**
     * Build ticket alert message from ticket data
     *
     * @param array<string, mixed> $ticketData Ticket information
     *
     * @return string Formatted alert message
     */
    /**
     * BuildTicketAlertMessage
     */
    private function buildTicketAlertMessage(array $ticketData): string
    {
        $event = $ticketData['event_name'] ?? 'Unknown Event';
        $venue = $ticketData['venue'] ?? 'Unknown Venue';
        $price = $ticketData['price'] ?? 0;

        return "New tickets available for {$event} at {$venue}. Starting from $" . number_format($price, 2);
    }

    /**
     * BuildPriceUpdateMessage
     */
    private function buildPriceUpdateMessage(float $oldPrice, float $newPrice, float $percentChange): string
    {
        $direction = $percentChange < 0 ? 'dropped' : 'increased';
        $percentFormatted = number_format(abs($percentChange), 1);

        return "Ticket price {$direction} by {$percentFormatted}%! From $" .
               number_format($oldPrice, 2) . ' to $' . number_format($newPrice, 2);
    }

    /**
     * @return array<int, string>
     */
    /**
     * Get  channels for priority
     */
    private function getChannelsForPriority(string $priority): array
    {
        return match ($priority) {
            self::PRIORITY_HIGH   => ['database', 'broadcast', 'push', 'mail'],
            self::PRIORITY_NORMAL => ['database', 'broadcast', 'push'],
            self::PRIORITY_LOW    => ['database', 'broadcast'],
            default               => ['database'],
        };
    }

    /**
     * Get  system notification title
     */
    private function getSystemNotificationTitle(string $type): string
    {
        return match ($type) {
            'maintenance' => 'Scheduled Maintenance',
            'security'    => 'Security Alert',
            'outage'      => 'Service Outage',
            'update'      => 'System Update',
            'warning'     => 'Warning',
            'success'     => 'Success',
            default       => 'System Notification',
        };
    }

    /**
     * Get  priority for system notification
     */
    private function getPriorityForSystemNotification(string $type): string
    {
        return match ($type) {
            'security', 'outage' => self::PRIORITY_HIGH,
            'maintenance', 'warning' => self::PRIORITY_NORMAL,
            default => self::PRIORITY_LOW,
        };
    }

    /**
     * @return array<int, string>
     */
    /**
     * Get  channels for system notification
     */
    private function getChannelsForSystemNotification(string $type): array
    {
        return match ($type) {
            'security', 'outage' => ['database', 'broadcast', 'push', 'mail'],
            'maintenance' => ['database', 'broadcast', 'push'],
            default       => ['database', 'broadcast'],
        };
    }

    /**
     * Dispatch notification through all configured channels
     *
     * @param array<string, mixed> $notification Notification data
     * @param array<int, int>      $userIds      Target user IDs
     */
    /**
     * DispatchNotification
     */
    private function dispatchNotification(array $notification, array $userIds): void
    {
        // Store notification data
        $notificationKey = self::NOTIFICATIONS_PREFIX . 'data:' . $notification['id'];
        foreach ($notification as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Redis::hset($notificationKey, $key, $value);
        }
        Redis::expire($notificationKey, 7776000); // 90 days retention

        // Add to user notification lists
        foreach ($userIds as $userId) {
            $userNotificationsKey = self::NOTIFICATIONS_PREFIX . 'user:' . $userId;
            Redis::lpush($userNotificationsKey, $notification['id']);
            Redis::ltrim($userNotificationsKey, 0, 999); // Keep last 1000 notifications

            // Update unread count
            $unreadKey = self::NOTIFICATIONS_PREFIX . 'unread:' . $userId;
            Redis::incr($unreadKey);
        }

        // Broadcast real-time notification
        if (in_array('broadcast', $notification['channels'], TRUE)) {
            $this->broadcastNotification($notification, $userIds);
        }

        // Send push notifications
        if (in_array('push', $notification['channels'], TRUE)) {
            $this->sendPushNotifications();
        }

        // Send email notifications
        if (in_array('mail', $notification['channels'], TRUE)) {
            $this->sendEmailNotifications();
        }
    }

    /**
     * Broadcast notification to users via WebSocket
     *
     * @param array<string, mixed> $notification Notification data
     * @param array<int, int>      $userIds      Target user IDs
     */
    /**
     * BroadcastNotification
     */
    private function broadcastNotification(array $notification, array $userIds): void
    {
        try {
            // Broadcast to specific users
            foreach ($userIds as $userId) {
                broadcast(new SystemNotification($notification, $userId));
            }
        } catch (Exception $e) {
            Log::error('Failed to broadcast notification', [
                'notification_id' => $notification['id'],
                'error'           => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send push notifications to users
     */
    /**
     * SendPushNotifications
     */
    private function sendPushNotifications(): void
    {
        // Implementation for push notifications
        // This would integrate with services like FCM, APNs, etc.
    }

    /**
     * Send email notifications to users
     */
    /**
     * SendEmailNotifications
     */
    private function sendEmailNotifications(): void
    {
        // Implementation for email notifications
        // This would use Laravel's mail system
    }

    /**
     * ProcessBulkBatch
     */
    private function processBulkBatch(array $notification, array $userIds): void
    {
        // Process notification for batch of users
        $this->dispatchNotification($notification, $userIds);
    }

    /**
     * @return array<int, int>
     */
    /**
     * Get  interested users
     */
    private function getInterestedUsers(): array
    {
        // Implementation to get users interested in specific ticket types
        return [];
    }

    /**
     * @return array<int, int>
     */
    /**
     * Get  users watching ticket
     */
    private function getUsersWatchingTicket(): array
    {
        // Implementation to get users watching specific ticket
        return [];
    }

    /**
     * @return array<int, int>
     */
    /**
     * Get  all active users
     */
    private function getAllActiveUsers(): array
    {
        // Implementation to get all active users
        return [];
    }

    /**
     * @param array<string, mixed> $preferences
     *
     * @return array<string, mixed>
     */
    /**
     * ValidateNotificationPreferences
     */
    private function validateNotificationPreferences(array $preferences): array
    {
        $validated = [];
        $allowedKeys = [
            'ticket_alerts', 'price_updates', 'system_notifications',
            'email_notifications', 'push_notifications', 'sms_notifications',
            'quiet_hours_start', 'quiet_hours_end', 'preferred_sports',
            'price_threshold', 'frequency_limit',
        ];

        foreach ($preferences as $key => $value) {
            if (in_array($key, $allowedKeys, TRUE)) {
                $validated[$key] = $value;
            }
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  default notification preferences
     */
    private function getDefaultNotificationPreferences(): array
    {
        return [
            'ticket_alerts'        => 'enabled',
            'price_updates'        => 'enabled',
            'system_notifications' => 'enabled',
            'email_notifications'  => 'enabled',
            'push_notifications'   => 'enabled',
            'sms_notifications'    => 'disabled',
            'quiet_hours_start'    => '22:00',
            'quiet_hours_end'      => '08:00',
            'preferred_sports'     => [],
            'price_threshold'      => 0,
            'frequency_limit'      => 'normal',
        ];
    }

    /**
     * Track notification sent event in analytics
     *
     * @param array<string, mixed> $notification Notification data
     * @param int                  $userCount    Number of users notified
     */
    /**
     * TrackNotificationSent
     */
    private function trackNotificationSent(array $notification, int $userCount): void
    {
        $this->analytics->trackEvent('notification_sent', [
            'notification_type' => $notification['type'],
            'priority'          => $notification['priority'],
            'user_count'        => $userCount,
            'channels'          => $notification['channels'],
        ]);
    }
}