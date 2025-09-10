<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

use App\Models\User;
use App\Models\UserNotificationSettings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class SlackNotificationChannel
{
    protected $webhookUrl;

    protected $defaultChannel;

    protected $botToken;

    public function __construct()
    {
        $this->webhookUrl = config('services.slack.webhook_url');
        $this->defaultChannel = config('services.slack.default_channel', '#ticket-alerts');
        $this->botToken = config('services.slack.bot_token');
    }

    /**
     * Send notification to Slack
     */
    /**
     * Send
     */
    public function send(User $user, array $alertData): bool
    {
        try {
            $slackSettings = $this->getUserSlackSettings($user);

            if (! $slackSettings || ! $slackSettings->is_enabled) {
                Log::info('Slack notifications disabled for user', ['user_id' => $user->id]);

                return FALSE;
            }

            $message = $this->buildSlackMessage($alertData, $slackSettings);

            // Use webhook or bot API based on configuration
            if ($this->shouldUseWebhook($slackSettings)) {
                return $this->sendViaWebhook($message, $slackSettings);
            }

            return $this->sendViaBotAPI($message, $slackSettings);
        } catch (Exception $e) {
            Log::error('Failed to send Slack notification', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return FALSE;
        }
    }

    /**
     * Send urgent Slack message with high priority formatting
     */
    /**
     * SendUrgent
     */
    public function sendUrgent(User $user, array $alertData): bool
    {
        $alertData['urgent'] = TRUE;

        return $this->send($user, $alertData);
    }

    /**
     * Test Slack connection
     */
    /**
     * TestConnection
     */
    public function testConnection(User $user): array
    {
        try {
            $testMessage = [
                'ticket' => [
                    'event_name' => 'Test Event',
                    'price'      => 99.99,
                    'quantity'   => 2,
                    'platform'   => 'Test Platform',
                    'venue'      => 'Test Venue',
                    'event_date' => now()->addDays(7)->toISOString(),
                ],
                'alert'          => ['id' => 0],
                'priority'       => 2,
                'priority_label' => 'Test',
                'actions'        => [
                    'view_ticket'  => 'https://example.com/test',
                    'purchase_now' => 'https://example.com/test',
                    'snooze_alert' => 'https://example.com/test',
                ],
            ];

            $success = $this->send($user, $testMessage);

            return [
                'success' => $success,
                'message' => $success ? 'Slack test notification sent successfully' : 'Failed to send Slack test notification',
            ];
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => 'Slack test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format message for thread reply
     */
    /**
     * SendThreadReply
     */
    public function sendThreadReply(User $user, array $alertData, string $threadTs): bool
    {
        try {
            $slackSettings = $this->getUserSlackSettings($user);

            if (! $this->botToken || ! $slackSettings) {
                return FALSE;
            }

            $message = '🔄 Alert Update: ' . ($alertData['context']['recommendation'] ?? 'Status updated');

            $payload = [
                'channel'   => $slackSettings->channel ?? $this->defaultChannel,
                'text'      => $message,
                'thread_ts' => $threadTs,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->botToken,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post('https://slack.com/api/chat.postMessage', $payload);

            return $response->json()['ok'] ?? FALSE;
        } catch (Exception $e) {
            Log::error('Failed to send Slack thread reply', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Build Slack message payload
     *
     * @param mixed $slackSettings
     */
    /**
     * BuildSlackMessage
     *
     * @param mixed $slackSettings
     */
    protected function buildSlackMessage(array $alertData, $slackSettings): array
    {
        $ticket = $alertData['ticket'];
        $priority = $alertData['priority_label'] ?? 'Normal';
        $isUrgent = $alertData['urgent'] ?? FALSE;

        // Priority colors and emojis
        $priorityConfig = $this->getPriorityConfig($alertData['priority'] ?? 2, $isUrgent);

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => $priorityConfig['emoji'] . ' Ticket Alert - ' . $priority . ' Priority',
                ],
            ],
            [
                'type'   => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Event:*\n{$ticket['event_name']}",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Price:*\n$" . number_format($ticket['price'], 2),
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Available:*\n{$ticket['quantity']} tickets",
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Platform:*\n{$ticket['platform']}",
                    ],
                ],
            ],
        ];

        // Add venue and date if available
        if (! empty($ticket['venue'])) {
            $blocks[1]['fields'][] = [
                'type' => 'mrkdwn',
                'text' => "*Venue:*\n{$ticket['venue']}",
            ];
        }

        if (! empty($ticket['event_date'])) {
            $blocks[1]['fields'][] = [
                'type' => 'mrkdwn',
                'text' => "*Date:*\n" . date('M j, Y g:i A', strtotime((string) $ticket['event_date'])),
            ];
        }

        // Add ML predictions if available
        if (isset($alertData['prediction'])) {
            $prediction = $alertData['prediction'];
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => ":crystal_ball: *AI Insights:*\n" .
                             "• Price trend: {$prediction['price_trend']} ({$prediction['price_change']}%)\n" .
                             "• Availability: {$prediction['availability_trend']} ({$prediction['availability_change']}%)\n" .
                             "• Demand level: {$prediction['demand_level']}",
                ],
            ];
        }

        // Add context information
        if (isset($alertData['context'])) {
            $context = $alertData['context'];
            $contextText = ":information_source: *Context:*\n";

            if (isset($context['time_until_event'])) {
                $contextText .= "• Event in: {$context['time_until_event']}\n";
            }

            if (isset($context['recommendation'])) {
                $contextText .= "• Recommendation: {$context['recommendation']}\n";
            }

            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $contextText,
                ],
            ];
        }

        // Add escalation info if present
        if (isset($alertData['escalation'])) {
            $escalation = $alertData['escalation'];
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => ":warning: *Escalation Alert (Attempt {$escalation['attempt']}):*\n{$escalation['message']}",
                ],
            ];
        }

        // Add action buttons
        $blocks[] = [
            'type'     => 'actions',
            'elements' => [
                [
                    'type'  => 'button',
                    'text'  => ['type' => 'plain_text', 'text' => 'View Details'],
                    'style' => 'primary',
                    'url'   => $alertData['actions']['view_ticket'],
                ],
                [
                    'type'  => 'button',
                    'text'  => ['type' => 'plain_text', 'text' => 'Purchase Now'],
                    'style' => $priorityConfig['button_style'],
                    'url'   => $alertData['actions']['purchase_now'],
                ],
            ],
        ];

        // Add snooze button for non-urgent alerts
        if (! $isUrgent) {
            $blocks[count($blocks) - 1]['elements'][] = [
                'type' => 'button',
                'text' => ['type' => 'plain_text', 'text' => 'Snooze'],
                'url'  => $alertData['actions']['snooze_alert'],
            ];
        }

        return [
            'channel'     => $slackSettings->channel ?? $this->defaultChannel,
            'username'    => 'HDTickets Bot',
            'icon_emoji'  => ':ticket:',
            'attachments' => [
                [
                    'color'  => $priorityConfig['color'],
                    'blocks' => $blocks,
                ],
            ],
        ];
    }

    /**
     * Get priority configuration for styling
     */
    /**
     * Get  priority config
     */
    protected function getPriorityConfig(int $priority, bool $isUrgent): array
    {
        $configs = [
            5 => ['color' => '#ff0000', 'emoji' => '🚨', 'button_style' => 'danger'],   // Critical
            4 => ['color' => '#ff6600', 'emoji' => '⚠️', 'button_style' => 'primary'],   // High
            3 => ['color' => '#ffcc00', 'emoji' => '📢', 'button_style' => 'primary'],   // Medium
            2 => ['color' => '#36a64f', 'emoji' => '🎫', 'button_style' => 'primary'],   // Normal
            1 => ['color' => '#cccccc', 'emoji' => '💭', 'button_style' => 'primary'],   // Low
        ];

        $config = $configs[$priority] ?? $configs[2];

        if ($isUrgent) {
            $config['color'] = '#ff0000';
            $config['emoji'] = '🚨';
            $config['button_style'] = 'danger';
        }

        return $config;
    }

    /**
     * Send via Slack webhook
     *
     * @param mixed $slackSettings
     */
    /**
     * SendViaWebhook
     *
     * @param mixed $slackSettings
     */
    protected function sendViaWebhook(array $message, $slackSettings): bool
    {
        $webhookUrl = $slackSettings->webhook_url ?? $this->webhookUrl;

        if (! $webhookUrl) {
            Log::warning('No Slack webhook URL configured');

            return FALSE;
        }

        $response = Http::timeout(10)->post($webhookUrl, $message);

        if ($response->successful()) {
            Log::info('Slack webhook notification sent successfully');

            return TRUE;
        }
        Log::error('Slack webhook failed', [
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);

        return FALSE;
    }

    /**
     * Send via Slack Bot API
     *
     * @param mixed $slackSettings
     */
    /**
     * SendViaBotAPI
     *
     * @param mixed $slackSettings
     */
    protected function sendViaBotAPI(array $message, $slackSettings): bool
    {
        if (! $this->botToken) {
            Log::warning('No Slack bot token configured');

            return FALSE;
        }

        // Convert message for Bot API format
        $payload = [
            'channel'     => $slackSettings->slack_user_id ?? $slackSettings->channel ?? $this->defaultChannel,
            'text'        => 'Ticket Alert',
            'attachments' => $message['attachments'],
            'username'    => $message['username'],
            'icon_emoji'  => $message['icon_emoji'],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->botToken,
            'Content-Type'  => 'application/json',
        ])->timeout(10)->post('https://slack.com/api/chat.postMessage', $payload);

        $result = $response->json();

        if ($result['ok'] ?? FALSE) {
            Log::info('Slack Bot API notification sent successfully');

            return TRUE;
        }
        Log::error('Slack Bot API failed', [
            'error'    => $result['error'] ?? 'Unknown error',
            'response' => $result,
        ]);

        return FALSE;
    }

    /**
     * Get user's Slack settings
     */
    protected function getUserSlackSettings(User $user)
    {
        return Cache::remember("slack_settings:{$user->id}", 3600, fn () => UserNotificationSettings::where('user_id', $user->id)
            ->where('channel', 'slack')
            ->first());
    }

    /**
     * Determine if webhook should be used over Bot API
     *
     * @param mixed $slackSettings
     */
    /**
     * ShouldUseWebhook
     *
     * @param mixed $slackSettings
     */
    protected function shouldUseWebhook($slackSettings): bool
    {
        // Use webhook if user has custom webhook or no bot token available
        return ! empty($slackSettings->webhook_url) || empty($this->botToken);
    }
}
