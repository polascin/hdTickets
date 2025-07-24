<?php

namespace App\Services;

use App\Models\User;
use App\Events\TicketAvailabilityUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Exception;

class InAppNotificationService
{
    protected $notificationTypes = [
        'ticket_status_changed',
        'price_alert',
        'availability_alert',
        'new_ticket_match',
        'system_alert',
        'monitoring_alert'
    ];

    protected $priorities = [
        'low' => 1,
        'normal' => 2,
        'high' => 3,
        'urgent' => 4
    ];

    /**
     * Send in-app notification to user
     */
    public function sendNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal'
    ): array {
        try {
            $notification = [
                'id' => uniqid('notif_'),
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'priority' => $this->priorities[$priority] ?? 2,
                'priority_label' => $priority,
                'is_read' => false,
                'is_dismissed' => false,
                'created_at' => now()->toISOString(),
                'expires_at' => $this->calculateExpiryTime($type, $priority)
            ];

            // Store notification in database
            $this->storeNotification($notification);

            // Send real-time notification
            $this->broadcastNotification($user, $notification);

            // Update user notification counters
            $this->updateUserNotificationCounters($user->id);

            Log::info('In-app notification sent', [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'priority' => $priority
            ]);

            return $notification;

        } catch (Exception $e) {
            Log::error('Failed to send in-app notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulkNotification(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal'
    ): array {
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($userIds as $userId) {
            try {
                $user = User::find($userId);
                if (!$user) {
                    $failed++;
                    continue;
                }

                $notification = $this->sendNotification($user, $type, $title, $message, $data, $priority);
                $results[] = $notification;
                $successful++;

            } catch (Exception $e) {
                $failed++;
                Log::error("Failed to send bulk notification to user {$userId}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Bulk notification completed', [
            'total_users' => count($userIds),
            'successful' => $successful,
            'failed' => $failed,
            'type' => $type
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'notifications' => $results
        ];
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(
        int $userId,
        array $filters = [],
        int $page = 1,
        int $perPage = 20
    ): array {
        $cacheKey = "user_notifications_{$userId}_" . md5(json_encode($filters) . "_{$page}_{$perPage}");
        
        return Cache::remember($cacheKey, 300, function () use ($userId, $filters, $page, $perPage) {
            $query = DB::table('in_app_notifications')
                ->where('user_id', $userId)
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc');

            // Apply filters
            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['is_read'])) {
                $query->where('is_read', $filters['is_read']);
            }

            if (isset($filters['priority'])) {
                $query->where('priority', $this->priorities[$filters['priority']] ?? 2);
            }

            if (isset($filters['since'])) {
                $query->where('created_at', '>=', $filters['since']);
            }

            $total = $query->count();
            $notifications = $query
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get()
                ->map(function ($notification) {
                    $notification->data = json_decode($notification->data, true);
                    return $notification;
                });

            return [
                'notifications' => $notifications,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId): bool
    {
        try {
            $affected = DB::table('in_app_notifications')
                ->where('id', $notificationId)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($affected > 0) {
                // Update user counters
                $notification = DB::table('in_app_notifications')
                    ->where('id', $notificationId)
                    ->first();

                if ($notification) {
                    $this->updateUserNotificationCounters($notification->user_id);
                }

                // Clear cache
                $this->clearUserNotificationCache($notification->user_id);
                
                Log::info("Notification {$notificationId} marked as read");
                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error("Failed to mark notification {$notificationId} as read", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        try {
            $affected = DB::table('in_app_notifications')
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            if ($affected > 0) {
                $this->updateUserNotificationCounters($userId);
                $this->clearUserNotificationCache($userId);
            }

            Log::info("Marked {$affected} notifications as read for user {$userId}");
            return $affected;

        } catch (Exception $e) {
            Log::error("Failed to mark all notifications as read for user {$userId}", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Dismiss notification
     */
    public function dismissNotification(string $notificationId): bool
    {
        try {
            $affected = DB::table('in_app_notifications')
                ->where('id', $notificationId)
                ->update([
                    'is_dismissed' => true,
                    'dismissed_at' => now()
                ]);

            if ($affected > 0) {
                $notification = DB::table('in_app_notifications')
                    ->where('id', $notificationId)
                    ->first();

                if ($notification) {
                    $this->updateUserNotificationCounters($notification->user_id);
                    $this->clearUserNotificationCache($notification->user_id);
                }
                
                Log::info("Notification {$notificationId} dismissed");
                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error("Failed to dismiss notification {$notificationId}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get notification statistics for user
     */
    public function getUserNotificationStats(int $userId): array
    {
        $cacheKey = "user_notification_stats_{$userId}";
        
        return Cache::remember($cacheKey, 300, function () use ($userId) {
            $stats = DB::table('in_app_notifications')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread'),
                    DB::raw('SUM(CASE WHEN priority = 4 THEN 1 ELSE 0 END) as urgent'),
                    DB::raw('SUM(CASE WHEN priority = 3 THEN 1 ELSE 0 END) as high'),
                    DB::raw('MAX(created_at) as latest')
                )
                ->where('user_id', $userId)
                ->where('expires_at', '>', now())
                ->where('is_dismissed', false)
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'unread' => $stats->unread ?? 0,
                'urgent' => $stats->urgent ?? 0,
                'high' => $stats->high ?? 0,
                'latest' => $stats->latest
            ];
        });
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications(): int
    {
        try {
            $deleted = DB::table('in_app_notifications')
                ->where('expires_at', '<=', now())
                ->delete();

            Log::info("Cleaned up {$deleted} expired notifications");
            return $deleted;

        } catch (Exception $e) {
            Log::error('Failed to cleanup expired notifications', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Store notification in database
     */
    protected function storeNotification(array $notification): void
    {
        DB::table('in_app_notifications')->insert([
            'id' => $notification['id'],
            'user_id' => $notification['user_id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'data' => json_encode($notification['data']),
            'priority' => $notification['priority'],
            'is_read' => $notification['is_read'],
            'is_dismissed' => $notification['is_dismissed'],
            'created_at' => $notification['created_at'],
            'expires_at' => $notification['expires_at']
        ]);
    }

    /**
     * Broadcast notification via WebSocket
     */
    protected function broadcastNotification(User $user, array $notification): void
    {
        try {
            broadcast(new TicketAvailabilityUpdated(
                $notification['id'],
                'in_app_notification'
            ))->toOthers();

            // Also broadcast to user-specific channel
            broadcast(new TicketAvailabilityUpdated(
                $notification['id'],
                'in_app_notification'
            ))->to("user.{$user->id}");

        } catch (Exception $e) {
            Log::warning('Failed to broadcast notification', [
                'notification_id' => $notification['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user notification counters
     */
    protected function updateUserNotificationCounters(int $userId): void
    {
        $stats = $this->getUserNotificationStats($userId);
        
        Cache::put("user_notification_counters_{$userId}", $stats, 3600);
        
        // Update user model if it has notification counter fields
        try {
            User::where('id', $userId)->update([
                'unread_notifications_count' => $stats['unread']
            ]);
        } catch (Exception $e) {
            // Ignore if column doesn't exist
        }
    }

    /**
     * Clear user notification cache
     */
    protected function clearUserNotificationCache(int $userId): void
    {
        $pattern = "user_notifications_{$userId}_*";
        
        // This is a simplified version - in production you'd want to use Redis SCAN
        Cache::forget("user_notification_stats_{$userId}");
        Cache::forget("user_notification_counters_{$userId}");
    }

    /**
     * Calculate notification expiry time based on type and priority
     */
    protected function calculateExpiryTime(string $type, string $priority): string
    {
        $defaultHours = 24 * 7; // 1 week default

        $expiryHours = match ($type) {
            'ticket_status_changed' => 24 * 3, // 3 days
            'price_alert' => 24 * 2, // 2 days
            'availability_alert' => 24, // 1 day
            'system_alert' => 24 * 7, // 1 week
            default => $defaultHours
        };

        // Adjust based on priority
        if ($priority === 'urgent') {
            $expiryHours *= 2; // Keep urgent notifications longer
        } elseif ($priority === 'low') {
            $expiryHours /= 2; // Expire low priority notifications sooner
        }

        return now()->addHours($expiryHours)->toISOString();
    }

    /**
     * Create notification preferences for user
     */
    public function createUserPreferences(int $userId, array $preferences = []): array
    {
        $defaultPreferences = [
            'ticket_status_changed' => true,
            'price_alert' => true,
            'availability_alert' => true,
            'new_ticket_match' => true,
            'system_alert' => true,
            'monitoring_alert' => false,
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
            'max_notifications_per_day' => 50
        ];

        $userPreferences = array_merge($defaultPreferences, $preferences);
        $userPreferences['user_id'] = $userId;
        $userPreferences['created_at'] = now()->toISOString();
        $userPreferences['updated_at'] = now()->toISOString();

        Cache::put("user_notification_preferences_{$userId}", $userPreferences, 3600 * 24);

        return $userPreferences;
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId): array
    {
        return Cache::remember("user_notification_preferences_{$userId}", 3600 * 24, function () use ($userId) {
            // In a real implementation, this would fetch from database
            return $this->createUserPreferences($userId);
        });
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): array
    {
        $currentPreferences = $this->getUserPreferences($userId);
        $updatedPreferences = array_merge($currentPreferences, $preferences);
        $updatedPreferences['updated_at'] = now()->toISOString();

        Cache::put("user_notification_preferences_{$userId}", $updatedPreferences, 3600 * 24);

        Log::info("Updated notification preferences for user {$userId}");

        return $updatedPreferences;
    }

    /**
     * Check if user should receive notification based on preferences
     */
    public function shouldSendNotification(int $userId, string $type): bool
    {
        $preferences = $this->getUserPreferences($userId);
        
        // Check if notification type is enabled
        if (!($preferences[$type] ?? true)) {
            return false;
        }

        // Check daily limit
        $dailyCount = $this->getDailyNotificationCount($userId);
        $maxDaily = $preferences['max_notifications_per_day'] ?? 50;
        
        if ($dailyCount >= $maxDaily) {
            Log::info("Daily notification limit reached for user {$userId}");
            return false;
        }

        // Check quiet hours
        if ($this->isInQuietHours($preferences)) {
            Log::info("User {$userId} is in quiet hours, skipping notification");
            return false;
        }

        return true;
    }

    /**
     * Get daily notification count for user
     */
    protected function getDailyNotificationCount(int $userId): int
    {
        $today = now()->format('Y-m-d');
        $cacheKey = "daily_notification_count_{$userId}_{$today}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            return DB::table('in_app_notifications')
                ->where('user_id', $userId)
                ->whereDate('created_at', now())
                ->count();
        });
    }

    /**
     * Check if current time is in user's quiet hours
     */
    protected function isInQuietHours(array $preferences): bool
    {
        $quietStart = $preferences['quiet_hours_start'] ?? '22:00';
        $quietEnd = $preferences['quiet_hours_end'] ?? '08:00';
        
        $currentTime = now()->format('H:i');
        
        // Handle quiet hours that span midnight
        if ($quietStart > $quietEnd) {
            return $currentTime >= $quietStart || $currentTime <= $quietEnd;
        }
        
        return $currentTime >= $quietStart && $currentTime <= $quietEnd;
    }
}
