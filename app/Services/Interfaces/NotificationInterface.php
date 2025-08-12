<?php declare(strict_types=1);

namespace App\Services\Interfaces;

/**
 * Notification Service Interface
 *
 * Defines the contract for multi-channel notification services
 * for sport events entry ticket alerts and communications.
 */
interface NotificationInterface
{
    /**
     * Send ticket availability alert
     *
     * @param array  $ticketData Ticket data
     * @param array  $userIds    User IDs to notify
     * @param string $priority   Priority level
     */
    public function sendTicketAlert(array $ticketData, array $userIds = [], string $priority = 'normal'): void;

    /**
     * Send price update notification
     *
     * @param int   $ticketId Ticket ID
     * @param float $oldPrice Old price
     * @param float $newPrice New price
     * @param array $userIds  User IDs to notify
     */
    public function sendPriceUpdate(int $ticketId, float $oldPrice, float $newPrice, array $userIds = []): void;

    /**
     * Send system notification
     *
     * @param string $message Notification message
     * @param string $type    Notification type
     * @param array  $userIds User IDs to notify
     * @param array  $data    Additional data
     */
    public function sendSystemNotification(string $message, string $type = 'info', array $userIds = [], array $data = []): void;

    /**
     * Get user notification preferences
     *
     * @param int $userId User ID
     *
     * @return array Notification preferences
     */
    public function getUserNotificationPreferences(int $userId): array;

    /**
     * Update user notification preferences
     *
     * @param int   $userId      User ID
     * @param array $preferences Preferences to update
     *
     * @return bool Success status
     */
    public function updateUserNotificationPreferences(int $userId, array $preferences): bool;

    /**
     * Get user notifications with pagination
     *
     * @param int $userId  User ID
     * @param int $page    Page number
     * @param int $perPage Items per page
     *
     * @return array Paginated notifications
     */
    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 50): array;

    /**
     * Mark notification as read
     *
     * @param string $notificationId Notification ID
     * @param int    $userId         User ID
     *
     * @return bool Success status
     */
    public function markNotificationAsRead(string $notificationId, int $userId): bool;

    /**
     * Get unread notification count
     *
     * @param int $userId User ID
     *
     * @return int Unread count
     */
    public function getUnreadNotificationCount(int $userId): int;
}
