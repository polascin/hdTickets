<?php declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'is_enabled',
        'webhook_url',
        'channel',
        'slack_user_id',
        'ping_role_id',
        'discord_user_id',
        'chat_id',
        'auth_type',
        'auth_token',
        'api_key',
        'basic_username',
        'basic_password',
        'webhook_secret',
        'custom_headers',
        'max_retries',
        'retry_delay',
        'settings',
    ];

    protected $casts = [
        'is_enabled'     => 'boolean',
        'settings'       => 'array',
        'custom_headers' => 'array',
        'max_retries'    => 'integer',
        'retry_delay'    => 'integer',
    ];

    protected $hidden = [
        'auth_token',
        'api_key',
        'basic_password',
        'webhook_secret',
    ];

    /**
     * Get the user that owns this notification setting
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for enabled settings
     *
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', TRUE);
    }

    /**
     * Scope for specific channel
     *
     * @param mixed $query
     */
    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Check if the channel is properly configured
     */
    /**
     * Check if  configured
     */
    public function isConfigured(): bool
    {
        switch ($this->channel) {
            case 'slack':
                return ! empty($this->webhook_url) || ! empty($this->slack_user_id);
            case 'discord':
                return ! empty($this->webhook_url) || ! empty($this->discord_user_id);
            case 'telegram':
                return ! empty($this->chat_id);
            case 'webhook':
                return ! empty($this->webhook_url);
            default:
                return FALSE;
        }
    }

    /**
     * Get channel-specific settings
     */
    /**
     * Get  channel settings
     */
    public function getChannelSettings(): array
    {
        $baseSettings = [
            'is_enabled' => $this->is_enabled,
            'channel'    => $this->channel,
        ];

        switch ($this->channel) {
            case 'slack':
                return array_merge($baseSettings, [
                    'webhook_url'   => $this->webhook_url,
                    'channel'       => $this->channel,
                    'slack_user_id' => $this->slack_user_id,
                    'ping_role_id'  => $this->ping_role_id,
                ]);

            case 'discord':
                return array_merge($baseSettings, [
                    'webhook_url'     => $this->webhook_url,
                    'discord_user_id' => $this->discord_user_id,
                    'ping_role_id'    => $this->ping_role_id,
                ]);

            case 'telegram':
                return array_merge($baseSettings, [
                    'chat_id' => $this->chat_id,
                ]);

            case 'webhook':
                return array_merge($baseSettings, [
                    'webhook_url'    => $this->webhook_url,
                    'auth_type'      => $this->auth_type,
                    'max_retries'    => $this->max_retries,
                    'retry_delay'    => $this->retry_delay,
                    'custom_headers' => $this->custom_headers,
                ]);

            default:
                return $baseSettings;
        }
    }

    /**
     * Update channel settings
     */
    /**
     * UpdateChannelSettings
     */
    public function updateChannelSettings(array $settings): bool
    {
        $fillableSettings = array_intersect_key($settings, array_flip($this->fillable));

        return $this->update($fillableSettings);
    }

    /**
     * Test the notification channel
     */
    /**
     * Test
     */
    public function test(): array
    {
        if (! $this->is_enabled) {
            return [
                'success' => FALSE,
                'message' => 'Channel is disabled',
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => FALSE,
                'message' => 'Channel is not properly configured',
            ];
        }

        try {
            switch ($this->channel) {
                case 'slack':
                    $channel = new \App\Services\NotificationChannels\SlackNotificationChannel();

                    return $channel->testConnection($this->user);
                case 'discord':
                    $channel = new \App\Services\NotificationChannels\DiscordNotificationChannel();

                    return $channel->testConnection($this->user);
                case 'telegram':
                    $channel = new \App\Services\NotificationChannels\TelegramNotificationChannel();

                    return $channel->testConnection($this->user);
                case 'webhook':
                    $channel = new \App\Services\NotificationChannels\WebhookNotificationChannel();

                    return $channel->testConnection($this->user);
                default:
                    return [
                        'success' => FALSE,
                        'message' => 'Unknown channel type',
                    ];
            }
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => 'Test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get supported notification channels
     */
    /**
     * Get  supported channels
     */
    public static function getSupportedChannels(): array
    {
        return [
            'slack' => [
                'name'            => 'Slack',
                'description'     => 'Send notifications to Slack channels or direct messages',
                'required_fields' => ['webhook_url OR slack_user_id'],
                'optional_fields' => ['channel', 'ping_role_id'],
            ],
            'discord' => [
                'name'            => 'Discord',
                'description'     => 'Send notifications to Discord channels or direct messages',
                'required_fields' => ['webhook_url OR discord_user_id'],
                'optional_fields' => ['ping_role_id'],
            ],
            'telegram' => [
                'name'            => 'Telegram',
                'description'     => 'Send notifications via Telegram bot',
                'required_fields' => ['chat_id'],
                'optional_fields' => [],
            ],
            'webhook' => [
                'name'            => 'Webhook',
                'description'     => 'Send notifications to custom webhook endpoints',
                'required_fields' => ['webhook_url'],
                'optional_fields' => ['auth_type', 'auth_token', 'api_key', 'custom_headers', 'max_retries'],
            ],
        ];
    }
}
