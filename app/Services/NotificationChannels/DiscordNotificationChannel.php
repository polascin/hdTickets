<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

use App\Models\User;
use App\Models\UserNotificationSettings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;

class DiscordNotificationChannel
{
    protected $webhookUrl;

    protected $botToken;

    protected $applicationId;

    public function __construct()
    {
        $this->webhookUrl = config('services.discord.webhook_url');
        $this->botToken = config('services.discord.bot_token');
        $this->applicationId = config('services.discord.application_id');
    }

    /**
     * Send notification to Discord
     */
    /**
     * Send
     */
    public function send(User $user, array $alertData): bool
    {
        try {
            $discordSettings = $this->getUserDiscordSettings($user);

            if (! $discordSettings || ! $discordSettings->is_enabled) {
                Log::info('Discord notifications disabled for user', ['user_id' => $user->id]);

                return FALSE;
            }

            $message = $this->buildDiscordMessage($alertData, $discordSettings);

            // Use webhook for channel messages
            if ($discordSettings->webhook_url || $this->webhookUrl) {
                return $this->sendViaWebhook($message, $discordSettings);
            }

            // Use bot API for direct messages
            if ($this->botToken && $discordSettings->discord_user_id) {
                return $this->sendViaBotAPI($message, $discordSettings);
            }

            Log::warning('No Discord webhook or bot configuration available');

            return FALSE;
        } catch (Exception $e) {
            Log::error('Failed to send Discord notification', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return FALSE;
        }
    }

    /**
     * Send Discord ping with role mention
     */
    /**
     * SendPing
     */
    public function sendPing(User $user, array $alertData): bool
    {
        $alertData['ping'] = TRUE;

        return $this->send($user, $alertData);
    }

    /**
     * Test Discord connection
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
                'message' => $success ? 'Discord test notification sent successfully' : 'Failed to send Discord test notification',
            ];
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => 'Discord test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send reaction to existing message
     */
    /**
     * AddReaction
     */
    public function addReaction(string $channelId, string $messageId, string $emoji): bool
    {
        if (! $this->botToken) {
            return FALSE;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
            ])->timeout(10)->put("https://discord.com/api/channels/{$channelId}/messages/{$messageId}/reactions/{$emoji}/@me");

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to add Discord reaction', [
                'channel_id' => $channelId,
                'message_id' => $messageId,
                'error'      => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Edit existing Discord message
     */
    /**
     * EditMessage
     */
    public function editMessage(string $channelId, string $messageId, array $alertData): bool
    {
        if (! $this->botToken) {
            return FALSE;
        }

        try {
            $discordSettings = new stdClass();
            $discordSettings->is_enabled = TRUE;

            $message = $this->buildDiscordMessage($alertData, $discordSettings);

            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->patch("https://discord.com/api/channels/{$channelId}/messages/{$messageId}", [
                'content'    => $message['content'],
                'embeds'     => $message['embeds'],
                'components' => $message['components'],
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to edit Discord message', [
                'channel_id' => $channelId,
                'message_id' => $messageId,
                'error'      => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Send thread message
     */
    /**
     * SendThreadMessage
     */
    public function sendThreadMessage(string $channelId, string $messageId, string $content): bool
    {
        if (! $this->botToken) {
            return FALSE;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post("https://discord.com/api/channels/{$channelId}/messages", [
                'content'           => $content,
                'message_reference' => [
                    'message_id' => $messageId,
                    'channel_id' => $channelId,
                ],
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Failed to send Discord thread message', [
                'channel_id' => $channelId,
                'message_id' => $messageId,
                'error'      => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get Discord guild/server information
     */
    /**
     * Get  guild info
     */
    public function getGuildInfo(string $guildId): ?array
    {
        if (! $this->botToken) {
            return NULL;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
            ])->timeout(10)->get("https://discord.com/api/guilds/{$guildId}");

            if ($response->successful()) {
                return $response->json();
            }

            return NULL;
        } catch (Exception $e) {
            Log::error('Failed to get Discord guild info', [
                'guild_id' => $guildId,
                'error'    => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Get available roles in a guild
     */
    /**
     * Get  guild roles
     */
    public function getGuildRoles(string $guildId): array
    {
        if (! $this->botToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
            ])->timeout(10)->get("https://discord.com/api/guilds/{$guildId}/roles");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (Exception $e) {
            Log::error('Failed to get Discord guild roles', [
                'guild_id' => $guildId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build Discord message payload
     *
     * @param mixed $discordSettings
     */
    /**
     * BuildDiscordMessage
     *
     * @param mixed $discordSettings
     */
    protected function buildDiscordMessage(array $alertData, $discordSettings): array
    {
        $ticket = $alertData['ticket'];
        $alert = $alertData['alert'];
        $priority = $alertData['priority_label'] ?? 'Normal';
        $isPing = $alertData['ping'] ?? FALSE;
        $isUrgent = $alertData['urgent'] ?? FALSE;

        // Priority configuration
        $priorityConfig = $this->getPriorityConfig($alertData['priority'] ?? 2, $isUrgent);

        // Build content with mentions if ping is enabled
        $content = '';
        if ($isPing && $discordSettings->ping_role_id) {
            $content = "<@&{$discordSettings->ping_role_id}> ";
        } elseif ($isPing && $discordSettings->discord_user_id) {
            $content = "<@{$discordSettings->discord_user_id}> ";
        }

        // Add urgent prefix
        if ($isUrgent) {
            $content .= "🚨 **URGENT TICKET ALERT** 🚨\n";
        }

        // Build embed
        $embed = [
            'title'     => $priorityConfig['emoji'] . ' Ticket Alert - ' . $priority . ' Priority',
            'color'     => hexdec(str_replace('#', '', $priorityConfig['color'])),
            'timestamp' => now()->toISOString(),
            'fields'    => [
                [
                    'name'   => 'Event',
                    'value'  => $ticket['event_name'],
                    'inline' => FALSE,
                ],
                [
                    'name'   => 'Price',
                    'value'  => '$' . number_format($ticket['price'], 2),
                    'inline' => TRUE,
                ],
                [
                    'name'   => 'Available',
                    'value'  => $ticket['quantity'] . ' tickets',
                    'inline' => TRUE,
                ],
                [
                    'name'   => 'Platform',
                    'value'  => $ticket['platform'],
                    'inline' => TRUE,
                ],
            ],
        ];

        // Add venue and date if available
        if (! empty($ticket['venue'])) {
            $embed['fields'][] = [
                'name'   => 'Venue',
                'value'  => $ticket['venue'],
                'inline' => TRUE,
            ];
        }

        if (! empty($ticket['event_date'])) {
            $embed['fields'][] = [
                'name'   => 'Date',
                'value'  => date('M j, Y g:i A', strtotime($ticket['event_date'])),
                'inline' => TRUE,
            ];
        }

        // Add ML predictions if available
        if (isset($alertData['prediction'])) {
            $prediction = $alertData['prediction'];
            $embed['fields'][] = [
                'name'  => '🔮 AI Insights',
                'value' => "**Price trend:** {$prediction['price_trend']} ({$prediction['price_change']}%)\n" .
                          "**Availability:** {$prediction['availability_trend']} ({$prediction['availability_change']}%)\n" .
                          "**Demand level:** {$prediction['demand_level']}",
                'inline' => FALSE,
            ];
        }

        // Add context information
        if (isset($alertData['context'])) {
            $context = $alertData['context'];
            $contextValue = '';

            if (isset($context['time_until_event'])) {
                $contextValue .= "**Event in:** {$context['time_until_event']}\n";
            }

            if (isset($context['recommendation'])) {
                $contextValue .= "**Recommendation:** {$context['recommendation']}\n";
            }

            if ($contextValue) {
                $embed['fields'][] = [
                    'name'   => 'ℹ️ Context',
                    'value'  => trim($contextValue),
                    'inline' => FALSE,
                ];
            }
        }

        // Add escalation info if present
        if (isset($alertData['escalation'])) {
            $escalation = $alertData['escalation'];
            $embed['fields'][] = [
                'name'   => '⚠️ Escalation Alert',
                'value'  => "**Attempt {$escalation['attempt']}:** {$escalation['message']}",
                'inline' => FALSE,
            ];
        }

        // Add footer
        $embed['footer'] = [
            'text'     => 'HDTickets Alert System',
            'icon_url' => 'https://example.com/icon.png', // Replace with actual icon URL
        ];

        // Build components (buttons)
        $components = [];
        if (isset($alertData['actions'])) {
            $actionRow = [
                'type'       => 1, // Action Row
                'components' => [
                    [
                        'type'  => 2, // Button
                        'style' => 5, // Link
                        'label' => 'View Details',
                        'url'   => $alertData['actions']['view_ticket'],
                    ],
                    [
                        'type'  => 2, // Button
                        'style' => 5, // Link
                        'label' => 'Purchase Now',
                        'url'   => $alertData['actions']['purchase_now'],
                    ],
                ],
            ];

            // Add snooze button for non-urgent alerts
            if (! $isUrgent && isset($alertData['actions']['snooze_alert'])) {
                $actionRow['components'][] = [
                    'type'  => 2, // Button
                    'style' => 5, // Link
                    'label' => 'Snooze',
                    'url'   => $alertData['actions']['snooze_alert'],
                ];
            }

            $components[] = $actionRow;
        }

        return [
            'content'    => $content,
            'embeds'     => [$embed],
            'components' => $components,
            'username'   => 'HDTickets Bot',
            'avatar_url' => 'https://example.com/avatar.png', // Replace with actual avatar URL
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
            5 => ['color' => '#ff0000', 'emoji' => '🚨'], // Critical - Red
            4 => ['color' => '#ff6600', 'emoji' => '⚠️'], // High - Orange
            3 => ['color' => '#ffcc00', 'emoji' => '📢'], // Medium - Yellow
            2 => ['color' => '#36a64f', 'emoji' => '🎫'], // Normal - Green
            1 => ['color' => '#cccccc', 'emoji' => '💭'], // Low - Gray
        ];

        $config = $configs[$priority] ?? $configs[2];

        if ($isUrgent) {
            $config['color'] = '#ff0000';
            $config['emoji'] = '🚨';
        }

        return $config;
    }

    /**
     * Send via Discord webhook
     *
     * @param mixed $discordSettings
     */
    /**
     * SendViaWebhook
     *
     * @param mixed $discordSettings
     */
    protected function sendViaWebhook(array $message, $discordSettings): bool
    {
        $webhookUrl = $discordSettings->webhook_url ?? $this->webhookUrl;

        if (! $webhookUrl) {
            Log::warning('No Discord webhook URL configured');

            return FALSE;
        }

        $response = Http::timeout(10)->post($webhookUrl, $message);

        if ($response->successful()) {
            Log::info('Discord webhook notification sent successfully');

            return TRUE;
        }
        Log::error('Discord webhook failed', [
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);

        return FALSE;
    }

    /**
     * Send via Discord Bot API (Direct Message)
     *
     * @param mixed $discordSettings
     */
    /**
     * SendViaBotAPI
     *
     * @param mixed $discordSettings
     */
    protected function sendViaBotAPI(array $message, $discordSettings): bool
    {
        if (! $this->botToken || ! $discordSettings->discord_user_id) {
            Log::warning('No Discord bot token or user ID configured');

            return FALSE;
        }

        try {
            // Create DM channel first
            $dmResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post('https://discord.com/api/users/@me/channels', [
                'recipient_id' => $discordSettings->discord_user_id,
            ]);

            if (! $dmResponse->successful()) {
                Log::error('Failed to create Discord DM channel', [
                    'status'   => $dmResponse->status(),
                    'response' => $dmResponse->body(),
                ]);

                return FALSE;
            }

            $dmChannel = $dmResponse->json();
            $channelId = $dmChannel['id'];

            // Send message to DM channel
            $messageResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->botToken,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post("https://discord.com/api/channels/{$channelId}/messages", [
                'content'    => $message['content'],
                'embeds'     => $message['embeds'],
                'components' => $message['components'],
            ]);

            if ($messageResponse->successful()) {
                Log::info('Discord Bot API notification sent successfully');

                return TRUE;
            }
            Log::error('Discord Bot API message failed', [
                'status'   => $messageResponse->status(),
                'response' => $messageResponse->body(),
            ]);

            return FALSE;
        } catch (Exception $e) {
            Log::error('Discord Bot API error', [
                'error' => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get user's Discord settings
     */
    protected function getUserDiscordSettings(User $user)
    {
        return Cache::remember("discord_settings:{$user->id}", 3600, function () use ($user) {
            return UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', 'discord')
                ->first();
        });
    }
}
