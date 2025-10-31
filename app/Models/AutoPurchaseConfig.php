<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Auto Purchase Configuration Model
 *
 * Stores user preferences for automated ticket purchasing
 */
class AutoPurchaseConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'name',
        'is_active',
        'max_price',
        'desired_quantity',
        'preferred_sections',
        'preferred_platforms',
        'payment_method',
        'purchase_window_start',
        'purchase_window_end',
        'priority_score',
        'retry_attempts',
        'fallback_enabled',
        'notification_preferences',
        'advanced_settings',
    ];

    protected $casts = [
        'is_active'                => 'boolean',
        'max_price'                => 'decimal:2',
        'desired_quantity'         => 'integer',
        'preferred_sections'       => 'array',
        'preferred_platforms'      => 'array',
        'purchase_window_start'    => 'datetime',
        'purchase_window_end'      => 'datetime',
        'priority_score'           => 'integer',
        'retry_attempts'           => 'integer',
        'fallback_enabled'         => 'boolean',
        'notification_preferences' => 'array',
        'advanced_settings'        => 'array',
    ];

    /**
     * Get the user that owns this configuration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event this configuration is for
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get purchase attempts for this configuration
     */
    public function purchaseAttempts(): HasMany
    {
        return $this->hasMany(PurchaseAttempt::class);
    }

    /**
     * Check if configuration is within purchase window
     */
    public function isWithinPurchaseWindow(): bool
    {
        $now = now();

        if ($this->purchase_window_start && $now->lt($this->purchase_window_start)) {
            return FALSE;
        }

        if ($this->purchase_window_end && $now->gt($this->purchase_window_end)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check if configuration can make more purchase attempts today
     */
    public function canAttemptPurchase(): bool
    {
        if (!$this->is_active) {
            return FALSE;
        }

        if (!$this->isWithinPurchaseWindow()) {
            return FALSE;
        }

        $todayAttempts = $this->purchaseAttempts()
            ->whereDate('created_at', today())
            ->count();

        $maxDailyAttempts = $this->advanced_settings['max_daily_attempts'] ?? 10;

        return $todayAttempts < $maxDailyAttempts;
    }

    /**
     * Get successful purchases for this configuration
     */
    public function getSuccessfulPurchases(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->purchaseAttempts()
            ->where('status', 'completed')
            ->whereJsonContains('result_data->success', TRUE)
            ->get();
    }

    /**
     * Calculate success rate for this configuration
     */
    public function getSuccessRate(): float
    {
        $totalAttempts = $this->purchaseAttempts()->count();

        if ($totalAttempts === 0) {
            return 0.0;
        }

        $successfulAttempts = $this->getSuccessfulPurchases()->count();

        return round(($successfulAttempts / $totalAttempts) * 100, 2);
    }

    /**
     * Get average execution time for purchases
     */
    public function getAverageExecutionTime(): ?float
    {
        $attempts = $this->purchaseAttempts()
            ->whereNotNull('execution_time_ms')
            ->get();

        if ($attempts->isEmpty()) {
            return NULL;
        }

        $totalTime = $attempts->sum('execution_time_ms');

        return round($totalTime / $attempts->count(), 2);
    }

    /**
     * Scope for active configurations
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    /**
     * Scope for configurations within purchase window
     *
     * @param mixed $query
     */
    public function scopeWithinWindow($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now): void {
            $q->whereNull('purchase_window_start')
                ->orWhere('purchase_window_start', '<=', $now);
        })->where(function ($q) use ($now): void {
            $q->whereNull('purchase_window_end')
                ->orWhere('purchase_window_end', '>=', $now);
        });
    }

    /**
     * Scope for high priority configurations
     *
     * @param mixed $query
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority_score', '>=', 8);
    }

    /**
     * Get default configuration settings
     */
    public static function getDefaults(): array
    {
        return [
            'is_active'                => TRUE,
            'max_price'                => 500.00,
            'desired_quantity'         => 2,
            'preferred_sections'       => [],
            'preferred_platforms'      => ['ticketmaster', 'seatgeek'],
            'payment_method'           => 'stripe',
            'priority_score'           => 5,
            'retry_attempts'           => 3,
            'fallback_enabled'         => TRUE,
            'notification_preferences' => [
                'success_notifications' => ['push', 'email', 'sms'],
                'failure_notifications' => ['push', 'email'],
                'attempt_notifications' => ['push'],
            ],
            'advanced_settings' => [
                'max_daily_attempts'         => 10,
                'auto_preload_context'       => TRUE,
                'use_anti_bot_bypass'        => TRUE,
                'parallel_purchase_attempts' => TRUE,
                'fallback_strategies'        => ['relaxed_criteria', 'alternative_platforms'],
                'execution_timeout'          => 30,
                'retry_delay_seconds'        => 60,
            ],
        ];
    }

    /**
     * Create configuration with defaults
     */
    public static function createWithDefaults(array $attributes): self
    {
        return self::create(array_merge(self::getDefaults(), $attributes));
    }

    /**
     * Update priority score based on success rate
     */
    public function updatePriorityScore(): void
    {
        $successRate = $this->getSuccessRate();
        $avgExecutionTime = $this->getAverageExecutionTime();

        $newScore = 5; // Base score

        // Adjust based on success rate
        if ($successRate >= 80) {
            $newScore += 3;
        } elseif ($successRate >= 60) {
            $newScore += 2;
        } elseif ($successRate >= 40) {
            ++$newScore;
        } elseif ($successRate < 20) {
            $newScore -= 2;
        }

        // Adjust based on execution time (faster is better)
        if ($avgExecutionTime && $avgExecutionTime < 5000) { // Less than 5 seconds
            ++$newScore;
        } elseif ($avgExecutionTime && $avgExecutionTime > 15000) { // More than 15 seconds
            --$newScore;
        }

        // Keep score within bounds
        $this->update(['priority_score' => max(1, min(10, $newScore))]);
    }

    /**
     * Check if configuration needs context preloading
     */
    public function needsContextPreloading(): bool
    {
        if (!($this->advanced_settings['auto_preload_context'] ?? TRUE)) {
            return FALSE;
        }

        $cacheKey = "auto_purchase_preload_{$this->id}";
        $preloadData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$preloadData) {
            return TRUE;
        }

        // Check if preload data is older than 30 minutes
        $preloadedAt = $preloadData['preloaded_at'] ?? NULL;
        if (!$preloadedAt || now()->diffInMinutes($preloadedAt) > 30) {
            return TRUE;
        }

        return FALSE;
    }
}
