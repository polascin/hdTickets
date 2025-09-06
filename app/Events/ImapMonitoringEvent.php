<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * IMAP Monitoring Event
 * 
 * WebSocket event for broadcasting real-time IMAP email monitoring updates
 * to connected dashboard clients in the HD Tickets system.
 */
class ImapMonitoringEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $eventType;
    public array $data;
    public string $timestamp;

    /**
     * Create a new event instance
     * 
     * @param string $eventType Type of monitoring event
     * @param array $data Event data payload
     */
    public function __construct(string $eventType, array $data)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->timestamp = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on
     * 
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('imap-monitoring'),
            new Channel('imap-monitoring-public'),
        ];
    }

    /**
     * Get the event name for broadcasting
     * 
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'imap.monitoring.update';
    }

    /**
     * Get the data to broadcast
     * 
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->eventType,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Determine if this event should broadcast
     * 
     * @return bool
     */
    public function broadcastWhen(): bool
    {
        // Only broadcast if WebSocket broadcasting is enabled
        return config('broadcasting.default') !== 'null';
    }

    /**
     * Create a connection status update event
     * 
     * @param string $connection Connection name
     * @param bool $isHealthy Connection health status
     * @param array $details Additional connection details
     * @return static
     */
    public static function connectionStatusUpdate(string $connection, bool $isHealthy, array $details = []): static
    {
        return new static('connection_status', [
            'connection' => $connection,
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'details' => $details,
        ]);
    }

    /**
     * Create an email processing update event
     * 
     * @param int $emailsProcessed Number of emails processed
     * @param int $sportsEventsFound Number of sports events discovered
     * @param int $ticketsFound Number of tickets found
     * @param string $platform Platform name
     * @return static
     */
    public static function emailProcessingUpdate(int $emailsProcessed, int $sportsEventsFound, int $ticketsFound, string $platform): static
    {
        return new static('email_processing', [
            'emails_processed' => $emailsProcessed,
            'sports_events_found' => $sportsEventsFound,
            'tickets_found' => $ticketsFound,
            'platform' => $platform,
        ]);
    }

    /**
     * Create a system health update event
     * 
     * @param array $healthMetrics System health metrics
     * @return static
     */
    public static function systemHealthUpdate(array $healthMetrics): static
    {
        return new static('system_health', $healthMetrics);
    }

    /**
     * Create a monitoring started event
     * 
     * @param string $connection Connection name
     * @param array $options Monitoring options
     * @return static
     */
    public static function monitoringStarted(string $connection, array $options = []): static
    {
        return new static('monitoring_started', [
            'connection' => $connection,
            'options' => $options,
        ]);
    }

    /**
     * Create a monitoring completed event
     * 
     * @param string $connection Connection name
     * @param array $results Monitoring results
     * @return static
     */
    public static function monitoringCompleted(string $connection, array $results): static
    {
        return new static('monitoring_completed', [
            'connection' => $connection,
            'results' => $results,
        ]);
    }

    /**
     * Create a new sports event discovered event
     * 
     * @param array $eventData Sports event data
     * @param string $platform Platform where event was discovered
     * @return static
     */
    public static function sportsEventDiscovered(array $eventData, string $platform): static
    {
        return new static('sports_event_discovered', [
            'event' => $eventData,
            'platform' => $platform,
        ]);
    }

    /**
     * Create a ticket availability alert event
     * 
     * @param array $ticketData Ticket data
     * @param string $alertType Type of alert (price_drop, availability, new_listing)
     * @return static
     */
    public static function ticketAvailabilityAlert(array $ticketData, string $alertType): static
    {
        return new static('ticket_alert', [
            'ticket' => $ticketData,
            'alert_type' => $alertType,
        ]);
    }

    /**
     * Create an error event
     * 
     * @param string $errorType Type of error
     * @param string $message Error message
     * @param array $context Error context
     * @return static
     */
    public static function error(string $errorType, string $message, array $context = []): static
    {
        return new static('error', [
            'error_type' => $errorType,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Create a performance metrics update event
     * 
     * @param array $metrics Performance metrics
     * @return static
     */
    public static function performanceMetricsUpdate(array $metrics): static
    {
        return new static('performance_metrics', $metrics);
    }

    /**
     * Create a cache cleared event
     * 
     * @param string|null $connection Connection name (null for all)
     * @param int $itemsCleared Number of items cleared
     * @return static
     */
    public static function cacheCleared(?string $connection, int $itemsCleared): static
    {
        return new static('cache_cleared', [
            'connection' => $connection,
            'items_cleared' => $itemsCleared,
        ]);
    }
}
