<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function in_array;

/**
 * Notification Preference Model
 *
 * Stores user preferences for smart alerts system
 */
class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enabled_channels',
        'urgency_settings',
        'quiet_hours',
        'rate_limits',
        'event_type_preferences',
        'intelligent_delivery',
        'delivery_confirmations',
        'channel_priorities',
    ];

    protected $casts = [
        'enabled_channels'       => 'array',
        'urgency_settings'       => 'array',
        'quiet_hours'            => 'array',
        'rate_limits'            => 'array',
        'event_type_preferences' => 'array',
        'intelligent_delivery'   => 'array',
        'delivery_confirmations' => 'array',
        'channel_priorities'     => 'array',
    ];

    /**
     * Get the user that owns the notification preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default notification preferences
     */
    public static function getDefaults(): array
    {
        return [
            'enabled_channels' => ['email', 'push'],
            'urgency_settings' => [
                'critical' => ['push', 'sms', 'email'],
                'high'     => ['push', 'email'],
                'medium'   => ['push'],
                'low'      => ['email'],
                'info'     => ['email'],
            ],
            'quiet_hours' => [
                'enabled'  => TRUE,
                'start'    => '22:00',
                'end'      => '08:00',
                'timezone' => 'UTC',
            ],
            'rate_limits' => [
                'max_per_hour'    => 20,
                'max_per_day'     => 100,
                'sms_max_per_day' => 10,
            ],
            'event_type_preferences' => [
                'price_drop' => [
                    'channels'    => ['push', 'email'],
                    'threshold'   => 10.0,
                    'instant_sms' => FALSE,
                ],
                'new_listing' => [
                    'channels'      => ['push', 'email'],
                    'priority_push' => TRUE,
                ],
                'availability_restored' => [
                    'channels'         => ['push', 'sms', 'email'],
                    'instant_delivery' => TRUE,
                ],
                'low_inventory' => [
                    'channels'  => ['push'],
                    'threshold' => 5,
                ],
            ],
            'intelligent_delivery' => [
                'learn_preferences' => TRUE,
                'auto_escalate'     => TRUE,
                'response_tracking' => TRUE,
            ],
            'delivery_confirmations' => [
                'track_opens'     => TRUE,
                'track_clicks'    => TRUE,
                'track_responses' => TRUE,
            ],
            'channel_priorities' => [
                'critical' => ['push', 'sms', 'email'],
                'high'     => ['push', 'email'],
                'medium'   => ['push'],
                'low'      => ['email'],
            ],
        ];
    }

    /**
     * Create default preferences for user
     */
    public static function createDefaultsForUser(User $user): self
    {
        return self::create([
            'user_id' => $user->id,
            ...self::getDefaults(),
        ]);
    }

    /**
     * Check if channel is enabled for urgency level
     */
    public function isChannelEnabledForUrgency(string $channel, string $urgency): bool
    {
        $urgencySettings = $this->urgency_settings ?? [];
        $enabledChannels = $urgencySettings[$urgency] ?? [];

        return in_array($channel, $enabledChannels, TRUE);
    }

    /**
     * Check if user is in quiet hours
     */
    public function isInQuietHours(): bool
    {
        $quietHours = $this->quiet_hours ?? [];

        if (! ($quietHours['enabled'] ?? FALSE)) {
            return FALSE;
        }

        $timezone = $quietHours['timezone'] ?? 'UTC';
        $userTime = now()->setTimezone($timezone);
        $currentTime = $userTime->format('H:i');

        $startTime = $quietHours['start'] ?? '22:00';
        $endTime = $quietHours['end'] ?? '08:00';

        // Handle overnight quiet hours
        if ($startTime > $endTime) {
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Get rate limit for specific period
     */
    public function getRateLimit(string $period): int
    {
        $limits = $this->rate_limits ?? [];

        return match ($period) {
            'hour'    => $limits['max_per_hour'] ?? 20,
            'day'     => $limits['max_per_day'] ?? 100,
            'sms_day' => $limits['sms_max_per_day'] ?? 10,
            default   => 20,
        };
    }

    /**
     * Get channels for event type
     */
    public function getChannelsForEventType(string $eventType): array
    {
        $eventPrefs = $this->event_type_preferences ?? [];

        return $eventPrefs[$eventType]['channels'] ?? ['email'];
    }

    /**
     * Update learning preferences based on user behavior
     */
    public function updateLearningPreferences(array $behaviorData): void
    {
        if (! ($this->intelligent_delivery['learn_preferences'] ?? FALSE)) {
            return;
        }

        // Update preferences based on user behavior
        $currentPrefs = $this->event_type_preferences ?? [];

        foreach ($behaviorData as $eventType => $data) {
            if (isset($currentPrefs[$eventType])) {
                // Adjust based on engagement
                if ($data['engagement_score'] > 0.8) {
                    // High engagement - maintain or increase frequency
                    $currentPrefs[$eventType]['priority'] = 'high';
                } elseif ($data['engagement_score'] < 0.3) {
                    // Low engagement - reduce frequency
                    $currentPrefs[$eventType]['priority'] = 'low';
                }
            }
        }

        $this->update(['event_type_preferences' => $currentPrefs]);
    }
}
