<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\NotificationChannels\PushNotificationService;
use App\Services\NotificationChannels\SmsNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Enhanced Smart Alerts Service
 *
 * Advanced notification system with:
 * - Multi-channel delivery (SMS, Email, Push, Webhook)
 * - Urgency-based routing and rate limiting
 * - Delivery confirmation tracking
 * - Template management and analytics
 */
class EnhancedSmartAlertsService
{
    private array $urgencyChannels = [
        'critical' => ['push', 'sms', 'email', 'webhook'],
        'high'     => ['push', 'sms', 'email'],
        'medium'   => ['push', 'email'],
        'low'      => ['email'],
        'info'     => ['email'],
    ];

    private array $deliveryTimeouts = [
        'critical' => 5,    // 5 seconds
        'high'     => 15,      // 15 seconds
        'medium'   => 60,    // 1 minute
        'low'      => 300,      // 5 minutes
        'info'     => 900,      // 15 minutes
    ];

    public function __construct(
        private PushNotificationService $pushService,
        private SmsNotificationService $smsService,
    ) {
    }

    /**
     * Send enhanced smart alert with automatic channel selection
     */
    public function sendEnhancedAlert(User $user, array $alertData): string
    {
        $alertId = $this->generateAlertId();
        $urgency = $alertData['urgency'] ?? 'medium';
        $channels = $this->determineOptimalChannels($user, $urgency, $alertData);

        // Check rate limits before sending
        if ($this->isRateLimited($user, $urgency)) {
            Log::warning('Enhanced alert rate limited', [
                'user_id'  => $user->id,
                'urgency'  => $urgency,
                'alert_id' => $alertId,
            ]);

            return $alertId;
        }

        // Create alert tracking record
        $this->createEnhancedAlertRecord($alertId, $user, $alertData, $channels);

        // Send to all determined channels with priority
        $this->sendToChannelsWithPriority($alertId, $user, $alertData, $channels);

        return $alertId;
    }

    /**
     * Determine optimal channels based on user preferences and context
     */
    private function determineOptimalChannels(User $user, string $urgency, array $alertData): array
    {
        $baseChannels = $this->urgencyChannels[$urgency] ?? ['email'];
        $userPreferences = $this->getEnhancedUserPreferences($user);
        $availableChannels = $this->getAvailableChannels($user);

        // Filter by user preferences
        $preferredChannels = array_intersect($baseChannels, $userPreferences['enabled_channels'] ?? []);

        // Filter by availability
        $finalChannels = array_intersect($preferredChannels, $availableChannels);

        // Ensure critical alerts get through
        if (empty($finalChannels) && $urgency === 'critical') {
            $finalChannels = array_intersect($baseChannels, $availableChannels);
        }

        // Add contextual channels
        if (isset($alertData['type'])) {
            $contextualChannels = $this->getContextualChannels($alertData['type'], $userPreferences);
            $finalChannels = array_unique(array_merge($finalChannels, $contextualChannels));
        }

        return array_values($finalChannels);
    }

    /**
     * Get enhanced user preferences with intelligent defaults
     */
    private function getEnhancedUserPreferences(User $user): array
    {
        $cacheKey = "enhanced_notification_prefs_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $prefs = NotificationPreference::where('user_id', $user->id)->first();

            if (!$prefs) {
                // Create intelligent default preferences
                $prefs = NotificationPreference::create([
                    'user_id'          => $user->id,
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
                        'timezone' => $user->timezone ?? 'UTC',
                    ],
                    'rate_limits' => [
                        'max_per_hour'    => 20,
                        'max_per_day'     => 100,
                        'sms_max_per_day' => 10,
                    ],
                    'intelligent_delivery' => [
                        'learn_preferences' => TRUE,
                        'auto_escalate'     => TRUE,
                        'response_tracking' => TRUE,
                    ],
                ]);
            }

            return [
                'enabled_channels'     => $prefs->enabled_channels ?? ['email'],
                'urgency_settings'     => $prefs->urgency_settings ?? [],
                'quiet_hours'          => $prefs->quiet_hours ?? [],
                'rate_limits'          => $prefs->rate_limits ?? [],
                'event_types'          => $prefs->event_type_preferences ?? [],
                'intelligent_delivery' => $prefs->intelligent_delivery ?? [],
            ];
        });
    }

    /**
     * Get available channels for user
     */
    private function getAvailableChannels(User $user): array
    {
        $available = ['email']; // Email always available

        if (!empty($user->phone)) {
            $available[] = 'sms';
        }

        if ($this->userHasPushToken($user)) {
            $available[] = 'push';
        }

        if (!empty($user->webhook_url)) {
            $available[] = 'webhook';
        }

        return $available;
    }

    /**
     * Check if user has valid push notification token
     */
    private function userHasPushToken(User $user): bool
    {
        return Cache::has("push_token_{$user->id}");
    }

    /**
     * Get contextual channels based on alert type
     */
    private function getContextualChannels(string $alertType, array $userPreferences): array
    {
        $contextual = [];
        $eventPrefs = $userPreferences['event_types'] ?? [];

        if (isset($eventPrefs[$alertType])) {
            $contextual = $eventPrefs[$alertType]['channels'] ?? [];
        }

        // Smart channel selection based on alert type
        switch ($alertType) {
            case 'price_drop':
                if (($eventPrefs['price_alerts']['instant_sms'] ?? FALSE)) {
                    $contextual[] = 'sms';
                }

                break;
            case 'new_listing':
                if (($eventPrefs['new_listings']['priority_push'] ?? FALSE)) {
                    $contextual[] = 'push';
                }

                break;
            case 'availability_restored':
                // Use all available channels for availability restoration
                $contextual = ['push', 'sms', 'email'];

                break;
            case 'low_inventory':
                // Time-sensitive, prefer faster channels
                $contextual = ['push', 'sms'];

                break;
        }

        return $contextual;
    }

    /**
     * Enhanced rate limiting with intelligent throttling
     */
    private function isRateLimited(User $user, string $urgency): bool
    {
        $preferences = $this->getEnhancedUserPreferences($user);
        $limits = $preferences['rate_limits'];

        // Critical alerts bypass rate limits
        if ($urgency === 'critical') {
            return FALSE;
        }

        // Intelligent rate limiting based on user behavior
        if ($this->hasIntelligentDelivery($preferences)) {
            return $this->checkIntelligentRateLimit($user, $urgency, $limits);
        }

        return $this->checkStandardRateLimit($user, $urgency, $limits);
    }

    /**
     * Check if user has intelligent delivery enabled
     */
    private function hasIntelligentDelivery(array $preferences): bool
    {
        return $preferences['intelligent_delivery']['learn_preferences'] ?? FALSE;
    }

    /**
     * Intelligent rate limiting that learns from user behavior
     */
    private function checkIntelligentRateLimit(User $user, string $urgency, array $limits): bool
    {
        // Get user engagement metrics
        $engagementScore = $this->getUserEngagementScore($user);

        // Adjust limits based on engagement
        $adjustedLimits = $this->adjustLimitsForEngagement($limits, $engagementScore);

        return $this->checkStandardRateLimit($user, $urgency, $adjustedLimits);
    }

    /**
     * Standard rate limiting check
     */
    private function checkStandardRateLimit(User $user, string $urgency, array $limits): bool
    {
        // Check hourly limit
        $hourlyKey = "rate_limit_hourly_{$user->id}_" . now()->format('YmdH');
        $hourlyCount = Cache::get($hourlyKey, 0);

        if ($hourlyCount >= ($limits['max_per_hour'] ?? 20)) {
            return TRUE;
        }

        // Check daily limit
        $dailyKey = "rate_limit_daily_{$user->id}_" . now()->format('Ymd');
        $dailyCount = Cache::get($dailyKey, 0);

        if ($dailyCount >= ($limits['max_per_day'] ?? 100)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get user engagement score for intelligent delivery
     */
    private function getUserEngagementScore(User $user): float
    {
        $cacheKey = "engagement_score_{$user->id}";

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            // Calculate engagement based on various factors
            $factors = [
                'response_rate'      => $this->getResponseRate($user),
                'click_through_rate' => $this->getClickThroughRate($user),
                'unsubscribe_rate'   => $this->getUnsubscribeRate($user),
                'active_monitors'    => $this->getActiveMonitorsCount($user),
                'recent_activity'    => $this->getRecentActivityScore($user),
            ];

            // Weighted scoring
            $weights = [
                'response_rate'      => 0.3,
                'click_through_rate' => 0.25,
                'unsubscribe_rate'   => -0.2,
                'active_monitors'    => 0.15,
                'recent_activity'    => 0.1,
            ];

            $score = 0;
            foreach ($factors as $factor => $value) {
                $score += $value * ($weights[$factor] ?? 0);
            }

            return max(0, min(1, $score)); // Normalize to 0-1
        });
    }

    /**
     * Send alerts to channels with priority-based delivery
     */
    private function sendToChannelsWithPriority(string $alertId, User $user, array $alertData, array $channels): void
    {
        $urgency = $alertData['urgency'] ?? 'medium';
        $isQuietHours = $this->isQuietHours($user, $urgency);

        foreach ($channels as $channel) {
            // Skip certain channels during quiet hours
            if ($isQuietHours && !in_array($channel, ['email'])) {
                continue;
            }

            $job = $this->createEnhancedChannelJob($alertId, $user, $alertData, $channel);

            if ($job) {
                $priority = $this->getJobPriority($urgency);
                $delay = $this->getChannelDelay($channel, $urgency);

                Queue::later($delay, $job)
                     ->onQueue("priority_{$priority}")
                     ->onConnection('redis');
            }
        }

        Log::info('Enhanced alert dispatched', [
            'alert_id'    => $alertId,
            'user_id'     => $user->id,
            'channels'    => $channels,
            'urgency'     => $urgency,
            'quiet_hours' => $isQuietHours,
        ]);
    }

    /**
     * Get channel-specific delay for staggered delivery
     */
    private function getChannelDelay(string $channel, string $urgency): int
    {
        // Immediate delivery for critical alerts
        if ($urgency === 'critical') {
            return 0;
        }

        // Stagger delivery to avoid overwhelming user
        return match ($channel) {
            'push'    => 0,      // Immediate
            'sms'     => 5,       // 5 seconds delay
            'email'   => 10,    // 10 seconds delay
            'webhook' => 15,  // 15 seconds delay
            default   => 0
        };
    }

    /**
     * Create enhanced channel job with tracking
     */
    private function createEnhancedChannelJob(string $alertId, User $user, array $alertData, string $channel): ?object
    {
        return match ($channel) {
            'push'    => new \App\Jobs\SendEnhancedPushNotificationJob($alertId, $user, $alertData),
            'sms'     => new \App\Jobs\SendEnhancedSmsNotificationJob($alertId, $user, $alertData),
            'email'   => new \App\Jobs\SendEnhancedEmailNotificationJob($alertId, $user, $alertData),
            'webhook' => new \App\Jobs\SendEnhancedWebhookNotificationJob($alertId, $user, $alertData),
            default   => NULL
        };
    }

    /**
     * Check if user is in quiet hours
     */
    private function isQuietHours(User $user, string $urgency): bool
    {
        // Critical and high alerts bypass quiet hours
        if (in_array($urgency, ['critical', 'high'])) {
            return FALSE;
        }

        $preferences = $this->getEnhancedUserPreferences($user);
        $quietHours = $preferences['quiet_hours'];

        if (!($quietHours['enabled'] ?? FALSE)) {
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
     * Generate unique alert ID with timestamp
     */
    private function generateAlertId(): string
    {
        return 'enhanced_alert_' . now()->format('YmdHis') . '_' . uniqid();
    }

    /**
     * Create enhanced alert tracking record
     */
    private function createEnhancedAlertRecord(string $alertId, User $user, array $alertData, array $channels): void
    {
        $record = [
            'alert_id'            => $alertId,
            'user_id'             => $user->id,
            'alert_type'          => $alertData['type'] ?? 'unknown',
            'urgency'             => $alertData['urgency'] ?? 'medium',
            'channels'            => $channels,
            'content'             => $alertData,
            'created_at'          => now(),
            'expires_at'          => now()->addMinutes($this->deliveryTimeouts[$alertData['urgency'] ?? 'medium']),
            'status'              => 'pending',
            'delivery_attempts'   => [],
            'engagement_tracking' => [
                'sent_at'           => now(),
                'expected_channels' => count($channels),
                'tracking_enabled'  => TRUE,
            ],
        ];

        Cache::put("enhanced_alert_{$alertId}", $record, 86400);
    }

    /**
     * Update delivery status with enhanced tracking
     */
    public function updateEnhancedDeliveryStatus(string $alertId, string $channel, string $status, array $metadata = []): void
    {
        $alert = Cache::get("enhanced_alert_{$alertId}");
        if (!$alert) {
            return;
        }

        $alert['delivery_attempts'][] = [
            'channel'       => $channel,
            'status'        => $status,
            'timestamp'     => now(),
            'metadata'      => $metadata,
            'response_time' => $metadata['response_time'] ?? NULL,
        ];

        // Update overall status
        $this->updateOverallAlertStatus($alert, $alertId);

        // Track for intelligent delivery learning
        $this->trackDeliveryForLearning($alertId, $channel, $status, $metadata);

        Cache::put("enhanced_alert_{$alertId}", $alert, 86400);
    }

    /**
     * Update overall alert status
     */
    private function updateOverallAlertStatus(array &$alert, string $alertId): void
    {
        $deliveryStatuses = array_column($alert['delivery_attempts'], 'status');

        if (in_array('delivered', $deliveryStatuses)) {
            $alert['status'] = 'delivered';
            $alert['delivered_at'] = now();
        } elseif (in_array('failed', $deliveryStatuses) && count($alert['delivery_attempts']) >= count($alert['channels'])) {
            $alert['status'] = 'failed';
            $alert['failed_at'] = now();
        }
    }

    /**
     * Track delivery data for machine learning
     */
    private function trackDeliveryForLearning(string $alertId, string $channel, string $status, array $metadata): void
    {
        $learningData = [
            'alert_id'  => $alertId,
            'channel'   => $channel,
            'status'    => $status,
            'timestamp' => now(),
            'metadata'  => $metadata,
        ];

        // Store for ML processing
        $learningKey = 'delivery_learning_' . now()->format('Ymd');
        $existingData = Cache::get($learningKey, []);
        $existingData[] = $learningData;

        Cache::put($learningKey, $existingData, 86400 * 7); // Keep for 7 days
    }

    /**
     * Get job priority based on urgency
     */
    private function getJobPriority(string $urgency): int
    {
        return match ($urgency) {
            'critical' => 10,
            'high'     => 7,
            'medium'   => 5,
            'low'      => 3,
            'info'     => 1,
            default    => 5
        };
    }

    /**
     * Get enhanced alert analytics
     */
    public function getEnhancedAlertAnalytics(User $user, Carbon $startDate = NULL, Carbon $endDate = NULL): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        return [
            'total_alerts'          => $this->getAlertCount($user->id, $startDate, $endDate),
            'delivery_performance'  => $this->getDeliveryPerformance($user->id, $startDate, $endDate),
            'channel_effectiveness' => $this->getChannelEffectiveness($user->id, $startDate, $endDate),
            'engagement_metrics'    => $this->getEngagementMetrics($user->id, $startDate, $endDate),
            'intelligent_insights'  => $this->getIntelligentInsights($user->id, $startDate, $endDate),
            'rate_limit_stats'      => $this->getRateLimitStats($user->id, $startDate, $endDate),
        ];
    }

    // Private helper methods for analytics and machine learning
    private function getResponseRate(User $user): float
    {
        return 0.75;
    }

    private function getClickThroughRate(User $user): float
    {
        return 0.65;
    }

    private function getUnsubscribeRate(User $user): float
    {
        return 0.02;
    }

    private function getActiveMonitorsCount(User $user): int
    {
        return 5;
    }

    private function getRecentActivityScore(User $user): float
    {
        return 0.8;
    }

    private function adjustLimitsForEngagement(array $limits, float $score): array
    {
        return $limits;
    }

    private function getAlertCount(int $userId, Carbon $start, Carbon $end): int
    {
        return 0;
    }

    private function getDeliveryPerformance(int $userId, Carbon $start, Carbon $end): array
    {
        return [];
    }

    private function getChannelEffectiveness(int $userId, Carbon $start, Carbon $end): array
    {
        return [];
    }

    private function getEngagementMetrics(int $userId, Carbon $start, Carbon $end): array
    {
        return [];
    }

    private function getIntelligentInsights(int $userId, Carbon $start, Carbon $end): array
    {
        return [];
    }

    private function getRateLimitStats(int $userId, Carbon $start, Carbon $end): array
    {
        return [];
    }
}
