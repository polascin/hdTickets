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
 * EventMonitor Model
 *
 * Manages individual event monitoring configurations with:
 * - Real-time monitoring settings
 * - Performance tracking and analytics
 * - Platform-specific configurations
 * - Alert and notification management
 * - Success rate and uptime monitoring
 */
class EventMonitor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'event_group_id',
        'is_active',
        'priority',
        'check_interval',
        'platforms',
        'notification_preferences',
        'custom_settings',
        'last_check_at',
        'last_response_time',
        'success_count',
        'failure_count',
        'total_checks',
        'last_error',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active'                => 'boolean',
        'priority'                 => 'integer',
        'check_interval'           => 'integer',
        'platforms'                => 'array',
        'notification_preferences' => 'array',
        'custom_settings'          => 'array',
        'last_check_at'            => 'datetime',
        'last_response_time'       => 'float',
        'success_count'            => 'integer',
        'failure_count'            => 'integer',
        'total_checks'             => 'integer',
    ];

    protected $attributes = [
        'is_active'                => TRUE,
        'priority'                 => 5,
        'check_interval'           => 300, // 5 minutes
        'platforms'                => '["ticketmaster"]',
        'notification_preferences' => '["email"]',
        'custom_settings'          => '{}',
        'success_count'            => 0,
        'failure_count'            => 0,
        'total_checks'             => 0,
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventGroup(): BelongsTo
    {
        return $this->belongsTo(EventGroup::class);
    }

    public function priceAlerts(): HasMany
    {
        return $this->hasMany(PriceAlert::class, 'event_id', 'event_id')
            ->where('user_id', $this->user_id);
    }

    public function autoPurchaseConfigs(): HasMany
    {
        return $this->hasMany(AutoPurchaseConfig::class, 'event_id', 'event_id')
            ->where('user_id', $this->user_id);
    }

    public function monitoringLogs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class);
    }

    // Performance Analytics Methods

    public function getSuccessRate(): float
    {
        if ($this->total_checks === 0) {
            return 100.0;
        }

        return round(($this->success_count / $this->total_checks) * 100, 2);
    }

    public function getFailureRate(): float
    {
        return 100.0 - $this->getSuccessRate();
    }

    public function getAverageResponseTime(): float
    {
        $recentLogs = $this->monitoringLogs()
            ->where('created_at', '>=', now()->subDays(7))
            ->where('response_time', '>', 0)
            ->avg('response_time');

        return round($recentLogs ?? $this->last_response_time ?? 0.0, 2);
    }

    public function getUptime(): float
    {
        $totalPeriod = now()->diffInMinutes($this->created_at);
        $downtimeMinutes = $this->monitoringLogs()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('downtime_duration') ?? 0;

        if ($totalPeriod === 0) {
            return 100.0;
        }

        $uptimePercentage = (($totalPeriod - $downtimeMinutes) / $totalPeriod) * 100;

        return round(max(0, min(100, $uptimePercentage)), 2);
    }

    public function getAlertAccuracy(): float
    {
        $totalAlerts = $this->priceAlerts()->count();
        if ($totalAlerts === 0) {
            return 100.0;
        }

        $accurateAlerts = $this->priceAlerts()
            ->where('is_triggered', TRUE)
            ->where('accuracy_verified', TRUE)
            ->count();

        return round(($accurateAlerts / $totalAlerts) * 100, 2);
    }

    public function getCheckFrequency(): float
    {
        $recentChecks = $this->monitoringLogs()
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return round($recentChecks / 24, 2); // Checks per hour
    }

    // Configuration Methods

    public function updateCheckInterval(int $intervalSeconds): bool
    {
        try {
            $this->update(['check_interval' => max(60, $intervalSeconds)]); // Minimum 1 minute

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function addPlatform(string $platform): bool
    {
        try {
            $platforms = $this->platforms ?? [];
            if (!in_array($platform, $platforms)) {
                $platforms[] = $platform;
                $this->update(['platforms' => $platforms]);
            }

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function removePlatform(string $platform): bool
    {
        try {
            $platforms = $this->platforms ?? [];
            $platforms = array_filter($platforms, fn ($p) => $p !== $platform);
            $this->update(['platforms' => array_values($platforms)]);

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function updateNotificationPreferences(array $preferences): bool
    {
        try {
            $validPreferences = ['email', 'sms', 'push', 'webhook'];
            $filteredPreferences = array_filter(
                $preferences,
                fn ($pref) => in_array($pref, $validPreferences)
            );

            $this->update(['notification_preferences' => $filteredPreferences]);

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function updatePriority(int $priority): bool
    {
        try {
            $priority = max(1, min(10, $priority)); // Clamp between 1-10
            $this->update(['priority' => $priority]);

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    // Monitoring Operations

    public function recordCheck(bool $success, float $responseTime = 0.0, ?string $error = NULL): void
    {
        $this->increment('total_checks');

        if ($success) {
            $this->increment('success_count');
            $this->update([
                'last_check_at'      => now(),
                'last_response_time' => $responseTime,
                'last_error'         => NULL,
            ]);
        } else {
            $this->increment('failure_count');
            $this->update([
                'last_check_at' => now(),
                'last_error'    => $error,
            ]);
        }

        // Log the monitoring attempt
        $this->monitoringLogs()->create([
            'status'        => $success ? 'success' : 'failed',
            'response_time' => $responseTime,
            'error_message' => $error,
            'checked_at'    => now(),
        ]);
    }

    public function isOverdue(): bool
    {
        if (!$this->is_active || !$this->last_check_at) {
            return FALSE;
        }

        $nextCheckDue = $this->last_check_at->addSeconds($this->check_interval);

        return now()->greaterThan($nextCheckDue);
    }

    public function getNextCheckTime(): ?Carbon
    {
        if (!$this->is_active || !$this->last_check_at) {
            return NULL;
        }

        return $this->last_check_at->addSeconds($this->check_interval);
    }

    public function shouldBeChecked(): bool
    {
        if (!$this->is_active) {
            return FALSE;
        }

        // If never checked, should be checked
        if (!$this->last_check_at) {
            return TRUE;
        }

        // Check if interval has passed
        return $this->isOverdue();
    }

    // Performance Optimization

    public function optimizeInterval(): void
    {
        $recentActivity = $this->event->priceHistories()
            ->where('recorded_at', '>=', now()->subDays(7))
            ->count();

        $eventDateProximity = $this->event->event_date
            ? now()->diffInDays($this->event->event_date, FALSE)
            : 365;

        // Base interval calculation
        $newInterval = $this->check_interval;

        // Adjust based on activity
        if ($recentActivity > 50) {
            $newInterval = max(60, $newInterval * 0.7); // More frequent
        } elseif ($recentActivity < 5) {
            $newInterval = min(3600, $newInterval * 1.3); // Less frequent
        }

        // Adjust based on event proximity
        if ($eventDateProximity <= 7) {
            $newInterval = max(60, $newInterval * 0.5); // Very frequent
        } elseif ($eventDateProximity <= 30) {
            $newInterval = max(120, $newInterval * 0.8); // More frequent
        } elseif ($eventDateProximity > 180) {
            $newInterval = min(3600, $newInterval * 1.5); // Less frequent
        }

        // Apply if significantly different
        if (abs($newInterval - $this->check_interval) > 30) {
            $this->updateCheckInterval((int) $newInterval);
        }
    }

    // Status and Health Methods

    public function getHealthStatus(): array
    {
        $status = 'healthy';
        $issues = [];

        // Check success rate
        $successRate = $this->getSuccessRate();
        if ($successRate < 90) {
            $status = 'warning';
            $issues[] = "Low success rate: {$successRate}%";
        }
        if ($successRate < 70) {
            $status = 'critical';
        }

        // Check response time
        $avgResponseTime = $this->getAverageResponseTime();
        if ($avgResponseTime > 5000) { // 5 seconds
            $status = $status === 'healthy' ? 'warning' : $status;
            $issues[] = "Slow response time: {$avgResponseTime}ms";
        }

        // Check if overdue
        if ($this->isOverdue()) {
            $status = 'warning';
            $issues[] = 'Check overdue by ' . now()->diffForHumans($this->getNextCheckTime());
        }

        // Check recent failures
        $recentFailures = $this->monitoringLogs()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHours(1))
            ->count();

        if ($recentFailures > 3) {
            $status = 'critical';
            $issues[] = "{$recentFailures} failures in the last hour";
        }

        return [
            'status'  => $status,
            'issues'  => $issues,
            'metrics' => [
                'success_rate'      => $successRate,
                'avg_response_time' => $avgResponseTime,
                'uptime'            => $this->getUptime(),
                'check_frequency'   => $this->getCheckFrequency(),
            ],
        ];
    }

    public function getPerformanceInsights(): array
    {
        return [
            'efficiency_score'         => $this->calculateEfficiencyScore(),
            'optimization_suggestions' => $this->getOptimizationSuggestions(),
            'comparison_to_similar'    => $this->compareToSimilarMonitors(),
            'trend_analysis'           => $this->analyzeTrends(),
        ];
    }

    // Statistics and Reports

    public function getDailyReport(Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $logs = $this->monitoringLogs()
            ->whereBetween('checked_at', [$startOfDay, $endOfDay])
            ->get();

        return [
            'date'              => $date->toDateString(),
            'total_checks'      => $logs->count(),
            'successful_checks' => $logs->where('status', 'success')->count(),
            'failed_checks'     => $logs->where('status', 'failed')->count(),
            'avg_response_time' => $logs->where('response_time', '>', 0)->avg('response_time'),
            'min_response_time' => $logs->where('response_time', '>', 0)->min('response_time'),
            'max_response_time' => $logs->where('response_time', '>', 0)->max('response_time'),
            'errors'            => $logs->where('status', 'failed')->pluck('error_message')->unique()->values(),
        ];
    }

    // Private Helper Methods

    private function calculateEfficiencyScore(): float
    {
        $successRate = $this->getSuccessRate();
        $responseTime = $this->getAverageResponseTime();
        $uptime = $this->getUptime();

        // Normalize response time (5000ms = 0 points, 500ms = 100 points)
        $responseScore = max(0, 100 - (($responseTime - 500) / 45));

        return round(($successRate * 0.4) + ($responseScore * 0.3) + ($uptime * 0.3), 2);
    }

    private function getOptimizationSuggestions(): array
    {
        $suggestions = [];

        if ($this->getAverageResponseTime() > 3000) {
            $suggestions[] = 'Consider reducing the number of platforms being monitored simultaneously';
        }

        if ($this->getSuccessRate() < 85) {
            $suggestions[] = 'Review platform configurations and network connectivity';
        }

        if ($this->check_interval < 120 && $this->event->event_date && now()->diffInDays($this->event->event_date) > 30) {
            $suggestions[] = 'Consider increasing check interval for events far in the future';
        }

        return $suggestions;
    }

    private function compareToSimilarMonitors(): array
    {
        $similarMonitors = self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', TRUE)
            ->get();

        if ($similarMonitors->isEmpty()) {
            return ['message' => 'No similar monitors for comparison'];
        }

        $avgSuccessRate = $similarMonitors->avg(fn ($m) => $m->getSuccessRate());
        $avgResponseTime = $similarMonitors->avg(fn ($m) => $m->getAverageResponseTime());

        return [
            'success_rate_comparison' => [
                'yours'      => $this->getSuccessRate(),
                'average'    => round($avgSuccessRate, 2),
                'percentile' => $this->calculatePercentile($similarMonitors, 'getSuccessRate'),
            ],
            'response_time_comparison' => [
                'yours'      => $this->getAverageResponseTime(),
                'average'    => round($avgResponseTime, 2),
                'percentile' => $this->calculatePercentile($similarMonitors, 'getAverageResponseTime', FALSE),
            ],
        ];
    }

    private function analyzeTrends(): array
    {
        $last7Days = $this->monitoringLogs()
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, AVG(response_time) as avg_response_time, COUNT(*) as total_checks')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'response_time_trend'   => $this->calculateTrend($last7Days->pluck('avg_response_time')),
            'check_frequency_trend' => $this->calculateTrend($last7Days->pluck('total_checks')),
            'daily_data'            => $last7Days->toArray(),
        ];
    }

    private function calculatePercentile($collection, string $method, bool $higherIsBetter = TRUE): int
    {
        $values = $collection->map(fn ($item) => $item->{$method}())->sort();
        $myValue = $this->{$method}();

        $count = $values->count();
        $position = $values->search(function ($value) use ($myValue, $higherIsBetter) {
            return $higherIsBetter ? $value >= $myValue : $value <= $myValue;
        });

        return $position !== FALSE ? (int) round(($position / $count) * 100) : 50;
    }

    private function calculateTrend($values): string
    {
        if ($values->count() < 2) {
            return 'insufficient_data';
        }

        $first = $values->take(3)->avg();
        $last = $values->reverse()->take(3)->avg();

        $change = (($last - $first) / $first) * 100;

        if (abs($change) < 5) {
            return 'stable';
        }

        return $change > 0 ? 'increasing' : 'decreasing';
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_active', TRUE)
            ->whereNotNull('last_check_at')
            ->whereRaw('DATE_ADD(last_check_at, INTERVAL check_interval SECOND) < NOW()');
    }

    public function scopeNeedsCheck($query)
    {
        return $query->where('is_active', TRUE)
            ->where(function ($query) {
                $query->whereNull('last_check_at')
                    ->orWhereRaw('DATE_ADD(last_check_at, INTERVAL check_interval SECOND) < NOW()');
            });
    }

    public function scopeWithPerformanceMetrics($query)
    {
        return $query->selectRaw('
            event_monitors.*,
            CASE 
                WHEN total_checks = 0 THEN 100 
                ELSE (success_count / total_checks) * 100 
            END as success_rate
        ');
    }
}
