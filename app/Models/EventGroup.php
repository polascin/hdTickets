<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EventGroup Model
 *
 * Enables users to organize and manage multiple events together with:
 * - Event categorization and grouping
 * - Shared monitoring configurations
 * - Bulk operations support
 * - Performance analytics
 * - Unified management interface
 */
class EventGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'color_code',
        'settings',
        'monitoring_config',
        'is_active',
        'total_events',
        'last_modified_at',
    ];

    protected $casts = [
        'settings'          => 'array',
        'monitoring_config' => 'array',
        'is_active'         => 'boolean',
        'total_events'      => 'integer',
        'last_modified_at'  => 'datetime',
    ];

    protected $attributes = [
        'settings'          => '{}',
        'monitoring_config' => '{}',
        'is_active'         => TRUE,
        'total_events'      => 0,
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_group_events')
            ->withPivot([
                'added_at',
                'priority',
                'custom_settings',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function eventMonitors(): HasMany
    {
        return $this->hasMany(EventMonitor::class);
    }

    // Analytics and Performance Methods

    public function getTotalAlerts(): int
    {
        return $this->events()
            ->join('price_alerts', 'events.id', '=', 'price_alerts.event_id')
            ->where('price_alerts.user_id', $this->user_id)
            ->count();
    }

    public function getLastActivity(): ?Carbon
    {
        $lastMonitorActivity = $this->eventMonitors()
            ->where('is_active', TRUE)
            ->max('last_check_at');

        $lastPriceUpdate = $this->events()
            ->join('price_histories', 'events.id', '=', 'price_histories.event_id')
            ->max('price_histories.recorded_at');

        if (!$lastMonitorActivity && !$lastPriceUpdate) {
            return NULL;
        }

        return Carbon::parse(max($lastMonitorActivity, $lastPriceUpdate));
    }

    public function getPerformanceScore(): float
    {
        $monitors = $this->eventMonitors()->where('is_active', TRUE)->get();

        if ($monitors->isEmpty()) {
            return 0.0;
        }

        $totalScore = 0;
        $criteria = [
            'response_time'  => 0.3,  // 30% weight
            'success_rate'   => 0.4,   // 40% weight
            'alert_accuracy' => 0.2, // 20% weight
            'uptime'         => 0.1,          // 10% weight
        ];

        foreach ($monitors as $monitor) {
            $score = 0;

            // Response time score (lower is better)
            $avgResponseTime = $monitor->getAverageResponseTime();
            $responseScore = max(0, 100 - ($avgResponseTime / 10)); // 1s = 10 points penalty
            $score += $responseScore * $criteria['response_time'];

            // Success rate score
            $successRate = $monitor->getSuccessRate();
            $score += $successRate * $criteria['success_rate'];

            // Alert accuracy score
            $alertAccuracy = $monitor->getAlertAccuracy();
            $score += $alertAccuracy * $criteria['alert_accuracy'];

            // Uptime score
            $uptime = $monitor->getUptime();
            $score += $uptime * $criteria['uptime'];

            $totalScore += $score;
        }

        return round($totalScore / $monitors->count(), 2);
    }

    public function getActiveEventsCount(): int
    {
        return $this->events()
            ->wherePivot('is_active', TRUE)
            ->where('events.is_active', TRUE)
            ->count();
    }

    public function getUpcomingEventsCount(): int
    {
        return $this->events()
            ->where('event_date', '>', now())
            ->where('event_date', '<=', now()->addDays(30))
            ->count();
    }

    public function getTotalPotentialSavings(): float
    {
        return $this->events()
            ->join('price_histories', 'events.id', '=', 'price_histories.event_id')
            ->join('price_alerts', 'events.id', '=', 'price_alerts.event_id')
            ->where('price_alerts.user_id', $this->user_id)
            ->where('price_alerts.is_triggered', TRUE)
            ->sum('price_alerts.potential_savings');
    }

    public function getAverageResponseTime(): float
    {
        return $this->eventMonitors()
            ->where('is_active', TRUE)
            ->avg('last_response_time') ?? 0.0;
    }

    public function getPriceVolatilityIndex(): float
    {
        $priceData = $this->events()
            ->join('price_histories', 'events.id', '=', 'price_histories.event_id')
            ->where('price_histories.recorded_at', '>=', now()->subDays(7))
            ->select([
                'events.id',
                'price_histories.price',
                'price_histories.recorded_at',
            ])
            ->get()
            ->groupBy('id');

        if ($priceData->isEmpty()) {
            return 0.0;
        }

        $totalVolatility = 0;
        $eventCount = 0;

        foreach ($priceData as $eventId => $prices) {
            if ($prices->count() < 2) {
                continue;
            }

            $priceValues = $prices->pluck('price')->toArray();
            $mean = array_sum($priceValues) / count($priceValues);
            $variance = array_sum(array_map(fn ($price) => pow($price - $mean, 2), $priceValues)) / count($priceValues);
            $volatility = sqrt($variance) / $mean; // Coefficient of variation

            $totalVolatility += $volatility;
            $eventCount++;
        }

        return $eventCount > 0 ? round($totalVolatility / $eventCount, 4) : 0.0;
    }

    // Group Management Methods

    public function addEvent(Event $event, array $options = []): bool
    {
        try {
            $this->events()->attach($event->id, [
                'added_at'        => now(),
                'priority'        => $options['priority'] ?? $this->calculateEventPriority($event),
                'custom_settings' => $options['custom_settings'] ?? [],
                'is_active'       => $options['is_active'] ?? TRUE,
            ]);

            $this->updateEventCount();
            $this->touch('last_modified_at');

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function removeEvent(Event $event): bool
    {
        try {
            $this->events()->detach($event->id);
            $this->updateEventCount();
            $this->touch('last_modified_at');

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function updateEventPriority(Event $event, int $priority): bool
    {
        try {
            $this->events()->updateExistingPivot($event->id, [
                'priority' => $priority,
            ]);

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function bulkUpdateEventSettings(array $eventIds, array $settings): array
    {
        $results = [];

        foreach ($eventIds as $eventId) {
            try {
                $this->events()->updateExistingPivot($eventId, [
                    'custom_settings' => $settings,
                ]);
                $results[$eventId] = TRUE;
            } catch (\Exception $e) {
                $results[$eventId] = FALSE;
            }
        }

        return $results;
    }

    // Configuration Methods

    public function getMonitoringInterval(): int
    {
        return $this->monitoring_config['check_interval'] ?? 300;
    }

    public function getEnabledPlatforms(): array
    {
        return $this->monitoring_config['platforms'] ?? ['ticketmaster'];
    }

    public function isAutoSetupEnabled(): bool
    {
        return $this->monitoring_config['auto_setup_monitoring'] ?? TRUE;
    }

    public function getNotificationFrequency(): string
    {
        return $this->settings['notification_frequency'] ?? 'medium';
    }

    public function isPriorityAdjustmentEnabled(): bool
    {
        return $this->settings['auto_priority_adjustment'] ?? TRUE;
    }

    public function isBulkOperationsEnabled(): bool
    {
        return $this->settings['bulk_operations_enabled'] ?? TRUE;
    }

    public function isSharedPriceAlertsEnabled(): bool
    {
        return $this->settings['shared_price_alerts'] ?? FALSE;
    }

    public function isUnifiedReportingEnabled(): bool
    {
        return $this->settings['unified_reporting'] ?? TRUE;
    }

    // Statistics and Analytics

    public function getDailyStats(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return [
            'date'         => $date->toDateString(),
            'total_checks' => $this->eventMonitors()
                ->whereBetween('last_check_at', [$startOfDay, $endOfDay])
                ->count(),
            'price_changes_detected' => $this->events()
                ->join('price_histories', 'events.id', '=', 'price_histories.event_id')
                ->whereBetween('price_histories.recorded_at', [$startOfDay, $endOfDay])
                ->count(),
            'alerts_triggered' => $this->events()
                ->join('price_alerts', 'events.id', '=', 'price_alerts.event_id')
                ->where('price_alerts.user_id', $this->user_id)
                ->whereBetween('price_alerts.triggered_at', [$startOfDay, $endOfDay])
                ->count(),
            'auto_purchases_attempted' => $this->events()
                ->join('auto_purchase_attempts', 'events.id', '=', 'auto_purchase_attempts.event_id')
                ->where('auto_purchase_attempts.user_id', $this->user_id)
                ->whereBetween('auto_purchase_attempts.attempted_at', [$startOfDay, $endOfDay])
                ->count(),
        ];
    }

    public function getWeeklyPerformanceReport(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $this->getDailyStats($date);
        }

        return [
            'period' => [
                'start' => now()->subDays(6)->toDateString(),
                'end'   => now()->toDateString(),
            ],
            'daily_stats'   => $days,
            'weekly_totals' => [
                'total_checks'             => array_sum(array_column($days, 'total_checks')),
                'price_changes_detected'   => array_sum(array_column($days, 'price_changes_detected')),
                'alerts_triggered'         => array_sum(array_column($days, 'alerts_triggered')),
                'auto_purchases_attempted' => array_sum(array_column($days, 'auto_purchases_attempted')),
            ],
            'performance_score' => $this->getPerformanceScore(),
            'recommendations'   => $this->generatePerformanceRecommendations(),
        ];
    }

    // Private Helper Methods

    private function updateEventCount(): void
    {
        $this->update([
            'total_events' => $this->events()->count(),
        ]);
    }

    private function calculateEventPriority(Event $event): int
    {
        $priority = 5; // Base priority

        // Adjust based on event date
        if ($event->event_date) {
            $daysUntilEvent = now()->diffInDays($event->event_date, FALSE);
            if ($daysUntilEvent <= 7) {
                $priority += 3;
            } elseif ($daysUntilEvent <= 30) {
                $priority += 2;
            }
        }

        // Adjust based on recent price activity
        $recentActivity = $event->priceHistories()
            ->where('recorded_at', '>=', now()->subDays(7))
            ->count();

        if ($recentActivity > 20) {
            $priority += 2;
        } elseif ($recentActivity > 10) {
            $priority += 1;
        }

        return min(10, max(1, $priority));
    }

    private function generatePerformanceRecommendations(): array
    {
        $recommendations = [];

        $performanceScore = $this->getPerformanceScore();
        if ($performanceScore < 70) {
            $recommendations[] = [
                'type'     => 'performance',
                'priority' => 'high',
                'message'  => 'Group performance is below optimal. Consider reducing monitoring frequency or reviewing platform configurations.',
            ];
        }

        $volatilityIndex = $this->getPriceVolatilityIndex();
        if ($volatilityIndex > 0.2) {
            $recommendations[] = [
                'type'     => 'volatility',
                'priority' => 'medium',
                'message'  => 'High price volatility detected. Consider setting up more aggressive price alerts.',
            ];
        }

        $avgResponseTime = $this->getAverageResponseTime();
        if ($avgResponseTime > 5000) { // 5 seconds
            $recommendations[] = [
                'type'     => 'response_time',
                'priority' => 'medium',
                'message'  => 'Slow response times detected. Consider optimizing monitoring configurations.',
            ];
        }

        return $recommendations;
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithEventCount($query)
    {
        return $query->withCount('events');
    }

    public function scopeWithActiveMonitors($query)
    {
        return $query->withCount(['eventMonitors' => function ($query) {
            $query->where('is_active', TRUE);
        }]);
    }
}
