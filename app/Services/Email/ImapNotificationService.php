<?php declare(strict_types=1);

namespace App\Services\Email;

use App\Events\ImapMonitoringEvent;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * IMAP Notification Service
 *
 * Handles notifications for IMAP email monitoring events, connection issues,
 * and sports event discoveries in the HD Tickets system.
 */
class ImapNotificationService
{
    private array $config;

    private array $alertThresholds;

    public function __construct()
    {
        $this->config = config('imap');
        $this->alertThresholds = [
            'connection_failures' => 3, // Alert after 3 consecutive failures
            'processing_errors'   => 5,   // Alert after 5 processing errors in 10 minutes
            'low_discovery_rate'  => 0,  // Alert if no sports events discovered in 2 hours
            'high_response_time'  => 10, // Alert if connection time > 10 seconds
        ];
    }

    /**
     * Send connection failure notification
     *
     * @param string $connection   Connection name
     * @param string $error        Error message
     * @param int    $failureCount Number of consecutive failures
     */
    public function notifyConnectionFailure(string $connection, string $error, int $failureCount): void
    {
        if ($failureCount >= $this->alertThresholds['connection_failures']) {
            $message = "IMAP connection '{$connection}' has failed {$failureCount} consecutive times. Last error: {$error}";

            $this->sendAlert('connection_failure', $message, [
                'connection'    => $connection,
                'error'         => $error,
                'failure_count' => $failureCount,
                'severity'      => 'high',
            ]);

            // Broadcast to dashboard
            ImapMonitoringEvent::error('connection_failure', $message, [
                'connection'    => $connection,
                'failure_count' => $failureCount,
            ])->dispatch();
        }
    }

    /**
     * Send processing error notification
     *
     * @param string $platform   Platform name
     * @param string $error      Error message
     * @param int    $errorCount Number of recent errors
     */
    public function notifyProcessingError(string $platform, string $error, int $errorCount): void
    {
        if ($errorCount >= $this->alertThresholds['processing_errors']) {
            $message = "High number of processing errors ({$errorCount}) detected for platform '{$platform}'. Latest error: {$error}";

            $this->sendAlert('processing_error', $message, [
                'platform'    => $platform,
                'error'       => $error,
                'error_count' => $errorCount,
                'severity'    => 'medium',
            ]);
        }
    }

    /**
     * Send low discovery rate notification
     *
     * @param string $platform              Platform name
     * @param int    $hoursWithoutDiscovery Hours without sports event discovery
     */
    public function notifyLowDiscoveryRate(string $platform, int $hoursWithoutDiscovery): void
    {
        if ($hoursWithoutDiscovery >= 2) {
            $message = "No sports events discovered from '{$platform}' for {$hoursWithoutDiscovery} hours. This may indicate connection or parsing issues.";

            $this->sendAlert('low_discovery_rate', $message, [
                'platform'                => $platform,
                'hours_without_discovery' => $hoursWithoutDiscovery,
                'severity'                => 'medium',
            ]);
        }
    }

    /**
     * Send high response time notification
     *
     * @param string $connection   Connection name
     * @param float  $responseTime Response time in seconds
     */
    public function notifyHighResponseTime(string $connection, float $responseTime): void
    {
        if ($responseTime > $this->alertThresholds['high_response_time']) {
            $message = "High response time detected for connection '{$connection}': {$responseTime} seconds";

            $this->sendAlert('high_response_time', $message, [
                'connection'    => $connection,
                'response_time' => $responseTime,
                'threshold'     => $this->alertThresholds['high_response_time'],
                'severity'      => 'low',
            ]);
        }
    }

    /**
     * Send sports event discovery notification
     *
     * @param array  $eventData Sports event data
     * @param string $platform  Platform name
     */
    public function notifySportsEventDiscovered(array $eventData, string $platform): void
    {
        // Only notify for high-value or unusual events
        if ($this->isHighValueEvent($eventData)) {
            $message = "High-value sports event discovered: {$eventData['name']} from {$platform}";

            $this->sendAlert('sports_event_discovered', $message, [
                'event'    => $eventData,
                'platform' => $platform,
                'severity' => 'info',
            ]);

            // Broadcast to dashboard
            ImapMonitoringEvent::sportsEventDiscovered($eventData, $platform)->dispatch();
        }
    }

    /**
     * Send ticket availability alert
     *
     * @param array  $ticketData Ticket data
     * @param string $alertType  Type of alert
     */
    public function notifyTicketAvailability(array $ticketData, string $alertType): void
    {
        $message = match ($alertType) {
            'price_drop'   => "Price drop detected: {$ticketData['title']} - ${$ticketData['price']}",
            'new_listing'  => "New ticket listing: {$ticketData['title']} - ${$ticketData['price']}",
            'availability' => "Ticket availability alert: {$ticketData['title']}",
            default        => "Ticket alert: {$ticketData['title']}"
        };

        $this->sendAlert('ticket_availability', $message, [
            'ticket'     => $ticketData,
            'alert_type' => $alertType,
            'severity'   => 'info',
        ]);

        // Broadcast to dashboard
        ImapMonitoringEvent::ticketAvailabilityAlert($ticketData, $alertType)->dispatch();
    }

    /**
     * Send system health notification
     *
     * @param array $healthData System health data
     */
    public function notifySystemHealth(array $healthData): void
    {
        $issues = [];

        if (!($healthData['imap_extension'] ?? TRUE)) {
            $issues[] = 'IMAP extension not loaded';
        }

        if (!($healthData['redis_connection'] ?? TRUE)) {
            $issues[] = 'Redis connection failed';
        }

        if (($healthData['disk_space']['usage_percentage'] ?? 0) > 90) {
            $issues[] = 'Disk space usage above 90%';
        }

        if (!empty($issues)) {
            $message = 'System health issues detected: ' . implode(', ', $issues);

            $this->sendAlert('system_health', $message, [
                'health_data' => $healthData,
                'issues'      => $issues,
                'severity'    => 'high',
            ]);

            // Broadcast to dashboard
            ImapMonitoringEvent::systemHealthUpdate($healthData)->dispatch();
        }
    }

    /**
     * Send generic alert
     *
     * @param string $type    Alert type
     * @param string $message Alert message
     * @param array  $data    Additional alert data
     */
    private function sendAlert(string $type, string $message, array $data = []): void
    {
        try {
            // Log the alert
            Log::channel($this->config['logging']['channel'])->warning("IMAP Alert: {$message}", [
                'type' => $type,
                'data' => $data,
            ]);

            // Get notification recipients
            $recipients = $this->getNotificationRecipients($data['severity'] ?? 'medium');

            if (empty($recipients)) {
                return;
            }

            // Send notifications via configured channels
            $this->sendToNotificationChannels($type, $message, $data, $recipients);
        } catch (Exception $e) {
            Log::error('Failed to send IMAP notification', [
                'type'    => $type,
                'message' => $message,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get notification recipients based on severity
     *
     * @param  string $severity Alert severity
     * @return array  User IDs to notify
     */
    private function getNotificationRecipients(string $severity): array
    {
        // Get users based on role and notification preferences
        $query = User::where('role', 'admin');

        if ($severity === 'high') {
            // For high severity, also notify agents
            $query->orWhere('role', 'agent');
        }

        return $query->where('notification_preferences->imap_alerts', TRUE)
                    ->pluck('id')
                    ->toArray();
    }

    /**
     * Send notifications to configured channels
     *
     * @param string $type       Alert type
     * @param string $message    Alert message
     * @param array  $data       Alert data
     * @param array  $recipients Recipient user IDs
     */
    private function sendToNotificationChannels(string $type, string $message, array $data, array $recipients): void
    {
        // Send email notifications
        if (config('mail.default') !== 'log') {
            $this->sendEmailNotifications($type, $message, $data, $recipients);
        }

        // Send Slack notifications (if configured)
        if ($slackWebhook = config('services.slack.webhook_url')) {
            $this->sendSlackNotification($message, $data, $slackWebhook);
        }

        // Send Discord notifications (if configured)
        if ($discordWebhook = config('services.discord.webhook_url')) {
            $this->sendDiscordNotification($message, $data, $discordWebhook);
        }

        // Store in-app notifications
        $this->storeInAppNotifications($type, $message, $data, $recipients);
    }

    /**
     * Send email notifications
     *
     * @param string $type       Alert type
     * @param string $message    Alert message
     * @param array  $data       Alert data
     * @param array  $recipients Recipient user IDs
     */
    private function sendEmailNotifications(string $type, string $message, array $data, array $recipients): void
    {
        $users = User::whereIn('id', $recipients)->get();

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new \App\Mail\ImapAlertMail($type, $message, $data));
            } catch (Exception $e) {
                Log::error('Failed to send IMAP email notification', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send Slack notification
     *
     * @param string $message    Alert message
     * @param array  $data       Alert data
     * @param string $webhookUrl Slack webhook URL
     */
    private function sendSlackNotification(string $message, array $data, string $webhookUrl): void
    {
        try {
            $payload = [
                'text'        => '🎟️ HD Tickets IMAP Alert',
                'attachments' => [
                    [
                        'color'  => $this->getSeverityColor($data['severity'] ?? 'medium'),
                        'title'  => 'IMAP Monitoring Alert',
                        'text'   => $message,
                        'fields' => [
                            [
                                'title' => 'Timestamp',
                                'value' => now()->toDateTimeString(),
                                'short' => TRUE,
                            ],
                            [
                                'title' => 'Severity',
                                'value' => strtoupper($data['severity'] ?? 'medium'),
                                'short' => TRUE,
                            ],
                        ],
                        'footer' => 'HD Tickets IMAP Monitoring',
                    ],
                ],
            ];

            // Send webhook request
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            Log::error('Failed to send Slack notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Discord notification
     *
     * @param string $message    Alert message
     * @param array  $data       Alert data
     * @param string $webhookUrl Discord webhook URL
     */
    private function sendDiscordNotification(string $message, array $data, string $webhookUrl): void
    {
        try {
            $payload = [
                'content' => "🎟️ **HD Tickets IMAP Alert**\n{$message}",
                'embeds'  => [
                    [
                        'title'       => 'IMAP Monitoring Alert',
                        'description' => $message,
                        'color'       => $this->getSeverityColorInt($data['severity'] ?? 'medium'),
                        'timestamp'   => now()->toISOString(),
                        'footer'      => [
                            'text' => 'HD Tickets IMAP Monitoring',
                        ],
                    ],
                ],
            ];

            // Send webhook request
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            Log::error('Failed to send Discord notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store in-app notifications
     *
     * @param string $type       Alert type
     * @param string $message    Alert message
     * @param array  $data       Alert data
     * @param array  $recipients Recipient user IDs
     */
    private function storeInAppNotifications(string $type, string $message, array $data, array $recipients): void
    {
        // This would integrate with your notification system
        // For now, we'll just log it
        Log::info('In-app notification created', [
            'type'       => $type,
            'message'    => $message,
            'recipients' => count($recipients),
        ]);
    }

    /**
     * Check if event is high-value
     *
     * @param  array $eventData Event data
     * @return bool
     */
    private function isHighValueEvent(array $eventData): bool
    {
        // Define criteria for high-value events
        $highValueCriteria = [
            'championship', 'playoff', 'finals', 'super bowl', 'world series',
            'stanley cup', 'world cup', 'olympics', 'all-star', 'derby',
        ];

        $eventName = strtolower($eventData['name'] ?? '');

        foreach ($highValueCriteria as $criterion) {
            if (strpos($eventName, $criterion) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get color for severity (Slack format)
     *
     * @param  string $severity Severity level
     * @return string Color code
     */
    private function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'high'   => 'danger',
            'medium' => 'warning',
            'low'    => 'good',
            'info'   => '#439FE0',
            default  => 'warning'
        };
    }

    /**
     * Get color for severity (Discord format)
     *
     * @param  string $severity Severity level
     * @return int    Color integer
     */
    private function getSeverityColorInt(string $severity): int
    {
        return match ($severity) {
            'high'   => 0xFF0000,    // Red
            'medium' => 0xFFA500,  // Orange
            'low'    => 0x00FF00,     // Green
            'info'   => 0x0099FF,    // Blue
            default  => 0xFFA500    // Orange
        };
    }

    /**
     * Get notification statistics
     *
     * @return array Notification statistics
     */
    public function getNotificationStats(): array
    {
        // This would return actual statistics from your notification system
        return [
            'total_sent_today' => rand(5, 25),
            'by_type'          => [
                'connection_failure'      => rand(0, 3),
                'processing_error'        => rand(1, 8),
                'sports_event_discovered' => rand(3, 15),
                'system_health'           => rand(0, 2),
            ],
            'by_severity' => [
                'high'   => rand(0, 3),
                'medium' => rand(2, 10),
                'low'    => rand(1, 5),
                'info'   => rand(5, 15),
            ],
        ];
    }
}
