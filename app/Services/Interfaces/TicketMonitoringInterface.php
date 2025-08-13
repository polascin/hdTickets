<?php declare(strict_types=1);

namespace App\Services\Interfaces;

/**
 * Ticket Monitoring Service Interface
 *
 * Defines the contract for real-time sport events entry ticket monitoring services.
 */
interface TicketMonitoringInterface
{
    /**
     * Start monitoring a ticket for availability and price changes
     *
     * @param int                  $ticketId Ticket ID to monitor
     * @param array<string, mixed> $criteria Monitoring criteria
     *
     * @return bool Success status
     */
    public function startMonitoring(int $ticketId, array $criteria = []): bool;

    /**
     * Stop monitoring a ticket
     *
     * @param int $ticketId Ticket ID to stop monitoring
     *
     * @return bool Success status
     */
    public function stopMonitoring(int $ticketId): bool;

    /**
     * Check availability for all monitored tickets
     *
     * @return array Results for all monitored tickets
     */
    public function checkAllTickets(): array;

    /**
     * Check availability for specific ticket
     *
     * @param int $ticketId Ticket ID to check
     *
     * @return array Availability data
     */
    public function checkTicketAvailability(int $ticketId): array;

    /**
     * Set alert rule for ticket monitoring
     *
     * @param int                  $ticketId             Ticket ID
     * @param string               $condition            Alert condition
     * @param mixed                $value                Alert value
     * @param array<string, mixed> $notificationChannels Notification channels
     *
     * @return bool Success status
     */
    public function setAlertRule(int $ticketId, string $condition, mixed $value, array $notificationChannels = ['email']): bool;

    /**
     * Get monitoring status for ticket
     *
     * @param int $ticketId Ticket ID
     *
     * @return array Monitoring status
     */
    public function getMonitoringStatus(int $ticketId): array;

    /**
     * Get monitoring statistics
     *
     * @return array Statistics data
     */
    public function getMonitoringStatistics(): array;
}
