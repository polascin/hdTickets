<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Webhook Model
 *
 * Manages webhook endpoints for real-time notifications with:
 * - Event subscription management
 * - Delivery tracking and analytics
 * - Retry policy configuration
 * - Security and authentication
 */
class Webhook extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'events',
        'secret',
        'is_active',
        'retry_policy',
        'custom_headers',
        'timeout',
        'total_deliveries',
        'successful_deliveries',
        'failed_deliveries',
        'last_delivery_at',
        'last_successful_delivery_at',
    ];

    protected $casts = [
        'events'                      => 'array',
        'retry_policy'                => 'array',
        'custom_headers'              => 'array',
        'is_active'                   => 'boolean',
        'timeout'                     => 'integer',
        'total_deliveries'            => 'integer',
        'successful_deliveries'       => 'integer',
        'failed_deliveries'           => 'integer',
        'last_delivery_at'            => 'datetime',
        'last_successful_delivery_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $attributes = [
        'is_active'             => TRUE,
        'timeout'               => 10,
        'total_deliveries'      => 0,
        'successful_deliveries' => 0,
        'failed_deliveries'     => 0,
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    // Analytics and Performance Methods

    public function getSuccessRate(): float
    {
        if ($this->total_deliveries === 0) {
            return 100.0;
        }

        return round(($this->successful_deliveries / $this->total_deliveries) * 100, 2);
    }

    public function getFailureRate(): float
    {
        return 100.0 - $this->getSuccessRate();
    }

    public function getAverageResponseTime(): float
    {
        return round(
            $this->logs()
                ->where('status', 'success')
                ->where('response_time', '>', 0)
                ->avg('response_time') ?? 0.0,
            2
        );
    }

    public function getRecentSuccessRate(int $hours = 24): float
    {
        $recentLogs = $this->logs()
            ->where('delivered_at', '>=', now()->subHours($hours))
            ->get();

        if ($recentLogs->isEmpty()) {
            return 100.0;
        }

        $successCount = $recentLogs->where('status', 'success')->count();

        return round(($successCount / $recentLogs->count()) * 100, 2);
    }

    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'disabled';
        }

        if ($this->total_deliveries === 0) {
            return 'untested';
        }

        $recentSuccessRate = $this->getRecentSuccessRate(24);

        if ($recentSuccessRate >= 95) {
            return 'healthy';
        } elseif ($recentSuccessRate >= 80) {
            return 'degraded';
        } else {
            return 'failing';
        }
    }

    public function getHealthScore(): float
    {
        if (!$this->is_active) {
            return 0.0;
        }

        $score = 100.0;

        // Deduct points for low success rate
        $successRate = $this->getSuccessRate();
        if ($successRate < 95) {
            $score -= (95 - $successRate) * 2; // Up to 190 points deduction
        }

        // Deduct points for slow response times
        $avgResponseTime = $this->getAverageResponseTime();
        if ($avgResponseTime > 5000) { // 5 seconds
            $score -= min(50, ($avgResponseTime - 5000) / 100); // Up to 50 points deduction
        }

        // Deduct points for recent failures
        $recentSuccessRate = $this->getRecentSuccessRate(24);
        if ($recentSuccessRate < $successRate) {
            $score -= ($successRate - $recentSuccessRate) * 1.5; // Recent failures are weighted more
        }

        return max(0.0, min(100.0, $score));
    }

    // Event Subscription Management

    public function subscribesToEvent(string $eventType): bool
    {
        return in_array($eventType, $this->events ?? []);
    }

    public function addEvent(string $eventType): bool
    {
        $events = $this->events ?? [];
        if (!in_array($eventType, $events)) {
            $events[] = $eventType;

            return $this->update(['events' => $events]);
        }

        return TRUE;
    }

    public function removeEvent(string $eventType): bool
    {
        $events = $this->events ?? [];
        $filteredEvents = array_filter($events, fn ($event) => $event !== $eventType);

        return $this->update(['events' => array_values($filteredEvents)]);
    }

    public function updateEvents(array $events): bool
    {
        $validEvents = [
            'price_alert', 'monitoring_update', 'purchase_complete',
            'system_notification', 'ticket_available', 'price_drop',
        ];

        $filteredEvents = array_filter($events, fn ($event) => in_array($event, $validEvents));

        return $this->update(['events' => array_values(array_unique($filteredEvents))]);
    }

    // Delivery Management

    public function recordDelivery(string $status, array $details = []): void
    {
        $this->increment('total_deliveries');

        if ($status === 'success') {
            $this->increment('successful_deliveries');
            $this->update(['last_successful_delivery_at' => now()]);
        } else {
            $this->increment('failed_deliveries');
        }

        $this->update(['last_delivery_at' => now()]);
    }

    public function shouldRetry(int $attemptNumber): bool
    {
        $maxAttempts = $this->retry_policy['max_attempts'] ?? 3;

        return $attemptNumber < $maxAttempts;
    }

    public function getRetryDelay(int $attemptNumber): int
    {
        $strategy = $this->retry_policy['backoff_strategy'] ?? 'exponential';
        $baseDelay = $this->retry_policy['base_delay'] ?? 60; // 1 minute

        return match ($strategy) {
            'linear'      => $baseDelay * $attemptNumber,
            'exponential' => $baseDelay * (2 ** ($attemptNumber - 1)),
            default       => $baseDelay
        };
    }

    // Configuration Management

    public function updateRetryPolicy(array $policy): bool
    {
        $defaultPolicy = [
            'max_attempts'     => 3,
            'backoff_strategy' => 'exponential',
            'base_delay'       => 60,
        ];

        $mergedPolicy = array_merge($defaultPolicy, $policy);

        return $this->update(['retry_policy' => $mergedPolicy]);
    }

    public function updateCustomHeaders(array $headers): bool
    {
        // Filter out potentially dangerous headers
        $forbiddenHeaders = ['authorization', 'cookie', 'x-forwarded-for'];
        $filteredHeaders = array_filter($headers, function ($key) use ($forbiddenHeaders) {
            return !in_array(strtolower($key), $forbiddenHeaders);
        }, ARRAY_FILTER_USE_KEY);

        return $this->update(['custom_headers' => $filteredHeaders]);
    }

    public function updateTimeout(int $timeout): bool
    {
        $timeout = max(1, min(30, $timeout)); // Clamp between 1-30 seconds

        return $this->update(['timeout' => $timeout]);
    }

    // Security and Validation

    public function regenerateSecret(): string
    {
        $newSecret = \Str::random(32);
        $this->update(['secret' => $newSecret]);

        return $newSecret;
    }

    public function validateSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($expectedSignature, $signature);
    }

    // Statistics and Reports

    public function getDailyDeliveryStats(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $logs = $this->logs()
            ->whereBetween('delivered_at', [$startOfDay, $endOfDay])
            ->get();

        return [
            'date'                  => $date->toDateString(),
            'total_deliveries'      => $logs->count(),
            'successful_deliveries' => $logs->where('status', 'success')->count(),
            'failed_deliveries'     => $logs->where('status', 'failed')->count(),
            'avg_response_time'     => $logs->where('response_time', '>', 0)->avg('response_time'),
            'events_breakdown'      => $logs->countBy('event_type')->toArray(),
        ];
    }

    public function getWeeklyReport(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $this->getDailyDeliveryStats($date);
        }

        return [
            'period' => [
                'start' => now()->subDays(6)->toDateString(),
                'end'   => now()->toDateString(),
            ],
            'daily_stats'   => $days,
            'weekly_totals' => [
                'total_deliveries'      => array_sum(array_column($days, 'total_deliveries')),
                'successful_deliveries' => array_sum(array_column($days, 'successful_deliveries')),
                'failed_deliveries'     => array_sum(array_column($days, 'failed_deliveries')),
            ],
            'success_rate' => $this->getSuccessRate(),
            'health_score' => $this->getHealthScore(),
        ];
    }

    public function getRecentFailures(int $limit = 10): array
    {
        return $this->logs()
            ->where('status', 'failed')
            ->orderByDesc('delivered_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'event_type'     => $log->event_type,
                    'error_message'  => $log->error_message,
                    'response_code'  => $log->response_code,
                    'attempt_number' => $log->attempt_number,
                    'delivered_at'   => $log->delivered_at,
                ];
            })
            ->toArray();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    public function scopeForEvent($query, string $eventType)
    {
        return $query->whereJsonContains('events', $eventType);
    }

    public function scopeHealthy($query)
    {
        return $query->whereRaw('(successful_deliveries / GREATEST(total_deliveries, 1)) >= 0.95');
    }

    public function scopeRecentlyActive($query, int $hours = 24)
    {
        return $query->where('last_delivery_at', '>=', now()->subHours($hours));
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    // Accessors

    public function getFormattedUrlAttribute(): string
    {
        return parse_url($this->url, PHP_URL_HOST) . parse_url($this->url, PHP_URL_PATH);
    }

    public function getEventLabelsAttribute(): array
    {
        $labels = [
            'price_alert'         => 'Price Alerts',
            'monitoring_update'   => 'Monitoring Updates',
            'purchase_complete'   => 'Purchase Completions',
            'system_notification' => 'System Notifications',
            'ticket_available'    => 'Ticket Availability',
            'price_drop'          => 'Price Drops',
        ];

        return array_map(
            fn ($event) => $labels[$event] ?? $event,
            $this->events ?? []
        );
    }

    public function getLastDeliveryAttribute(): ?string
    {
        return $this->last_delivery_at?->diffForHumans() ?? 'Never';
    }

    public function getIsHealthyAttribute(): bool
    {
        return $this->getHealthScore() >= 80;
    }
}
