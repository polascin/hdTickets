<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Price Alert Model
 * 
 * Manages user-configured price alerts and notifications
 */
class PriceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'name',
        'alert_type',
        'target_price',
        'target_percentage',
        'baseline_price',
        'is_active',
        'platforms',
        'notification_channels',
        'min_interval_minutes',
        'max_triggers_per_day',
        'conditions',
        'metadata',
        'last_triggered_at',
        'trigger_count',
        'expires_at'
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'target_percentage' => 'decimal:1',
        'baseline_price' => 'decimal:2',
        'is_active' => 'boolean',
        'platforms' => 'array',
        'notification_channels' => 'array',
        'min_interval_minutes' => 'integer',
        'max_triggers_per_day' => 'integer',
        'conditions' => 'array',
        'metadata' => 'array',
        'trigger_count' => 'integer',
        'last_triggered_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Alert type constants
     */
    public const TYPE_PRICE_DROP = 'price_drop';
    public const TYPE_PRICE_DROP_PERCENTAGE = 'price_drop_percentage';
    public const TYPE_ABSOLUTE_PRICE = 'absolute_price';
    public const TYPE_BEST_DEAL = 'best_deal';
    public const TYPE_INVENTORY_LOW = 'inventory_low';

    /**
     * Get the user that owns this alert
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event this alert is for
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope for active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for specific alert type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope for alerts that can be triggered (respecting intervals)
     */
    public function scopeCanTrigger($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_triggered_at')
              ->orWhereRaw('last_triggered_at <= DATE_SUB(NOW(), INTERVAL min_interval_minutes MINUTE)');
        });
    }

    /**
     * Scope for alerts under daily trigger limit
     */
    public function scopeUnderDailyLimit($query)
    {
        return $query->whereRaw('
            (SELECT COUNT(*) FROM price_alert_triggers 
             WHERE price_alert_id = price_alerts.id 
             AND DATE(triggered_at) = CURDATE()) < max_triggers_per_day
        ');
    }

    /**
     * Check if alert can be triggered now
     */
    public function canTrigger(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check minimum interval
        if ($this->last_triggered_at) {
            $minutesSinceLastTrigger = $this->last_triggered_at->diffInMinutes(now());
            if ($minutesSinceLastTrigger < $this->min_interval_minutes) {
                return false;
            }
        }

        // Check daily trigger limit
        if ($this->max_triggers_per_day > 0) {
            $todayTriggers = $this->getTodayTriggerCount();
            if ($todayTriggers >= $this->max_triggers_per_day) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get trigger count for today
     */
    public function getTodayTriggerCount(): int
    {
        return \DB::table('price_alert_triggers')
            ->where('price_alert_id', $this->id)
            ->whereDate('triggered_at', today())
            ->count();
    }

    /**
     * Check if price meets alert conditions
     */
    public function checkPriceCondition(array $priceData): bool
    {
        $currentPrice = $priceData['price_min'] ?? 0;

        return match ($this->alert_type) {
            self::TYPE_PRICE_DROP => $this->checkPriceDrop($currentPrice),
            self::TYPE_PRICE_DROP_PERCENTAGE => $this->checkPercentageDrop($currentPrice),
            self::TYPE_ABSOLUTE_PRICE => $currentPrice <= $this->target_price,
            self::TYPE_BEST_DEAL => $this->checkBestDeal($priceData),
            self::TYPE_INVENTORY_LOW => $this->checkLowInventory($priceData),
            default => false
        };
    }

    /**
     * Check price drop condition
     */
    private function checkPriceDrop(float $currentPrice): bool
    {
        return $currentPrice <= $this->target_price;
    }

    /**
     * Check percentage drop condition
     */
    private function checkPercentageDrop(float $currentPrice): bool
    {
        $basePrice = $this->baseline_price ?? $this->target_price;
        
        if ($basePrice <= 0) {
            return false;
        }

        $dropPercentage = (($basePrice - $currentPrice) / $basePrice) * 100;
        return $dropPercentage >= $this->target_percentage;
    }

    /**
     * Check best deal condition
     */
    private function checkBestDeal(array $priceData): bool
    {
        // Compare with historical data
        $historicalLow = PriceHistory::where('event_id', $this->event_id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->min('price_min') ?? PHP_FLOAT_MAX;

        $currentPrice = $priceData['price_min'] ?? 0;
        return $currentPrice <= $historicalLow * 1.05; // Within 5% of historical low
    }

    /**
     * Check low inventory condition
     */
    private function checkLowInventory(array $priceData): bool
    {
        $inventory = $priceData['total_listings'] ?? $priceData['available_quantity'] ?? 0;
        $threshold = $this->conditions['inventory_threshold'] ?? 10;
        
        return $inventory <= $threshold;
    }

    /**
     * Trigger the alert
     */
    public function trigger(array $priceData): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'trigger_count' => $this->trigger_count + 1
        ]);

        // Record trigger in separate table for analytics
        \DB::table('price_alert_triggers')->insert([
            'price_alert_id' => $this->id,
            'triggered_at' => now(),
            'trigger_data' => json_encode($priceData),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get alert effectiveness (percentage of successful triggers)
     */
    public function getEffectiveness(): float
    {
        if ($this->trigger_count === 0) {
            return 0.0;
        }

        $successfulTriggers = \DB::table('price_alert_triggers')
            ->where('price_alert_id', $this->id)
            ->whereJsonContains('trigger_data->user_acted', true)
            ->count();

        return round(($successfulTriggers / $this->trigger_count) * 100, 1);
    }

    /**
     * Get estimated savings from this alert
     */
    public function getEstimatedSavings(): float
    {
        $avgMarketPrice = PriceHistory::where('event_id', $this->event_id)
            ->where('recorded_at', '>=', now()->subDays(7))
            ->avg('price_average') ?? 0;

        return max(0, $avgMarketPrice - $this->target_price);
    }

    /**
     * Check if alert should auto-disable
     */
    public function shouldAutoDisable(): bool
    {
        // Auto-disable if event has passed
        if ($this->event && $this->event->event_date && $this->event->event_date->isPast()) {
            return true;
        }

        // Auto-disable if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        // Auto-disable if too many failed triggers (low effectiveness)
        if ($this->trigger_count >= 10 && $this->getEffectiveness() < 10) {
            return true;
        }

        return false;
    }

    /**
     * Get formatted alert description
     */
    public function getDescription(): string
    {
        $eventName = $this->event->name ?? 'Unknown Event';

        return match ($this->alert_type) {
            self::TYPE_PRICE_DROP => "Alert when {$eventName} tickets drop to £{$this->target_price}",
            self::TYPE_PRICE_DROP_PERCENTAGE => "Alert when {$eventName} tickets drop by {$this->target_percentage}%",
            self::TYPE_ABSOLUTE_PRICE => "Alert when {$eventName} tickets reach £{$this->target_price}",
            self::TYPE_BEST_DEAL => "Alert for best deals on {$eventName} tickets",
            self::TYPE_INVENTORY_LOW => "Alert when {$eventName} ticket inventory is low",
            default => "Price alert for {$eventName}"
        };
    }

    /**
     * Get alert configuration summary
     */
    public function getConfigSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->alert_type,
            'description' => $this->getDescription(),
            'target_price' => $this->target_price,
            'target_percentage' => $this->target_percentage,
            'is_active' => $this->is_active,
            'platforms' => $this->platforms ?? [],
            'notification_channels' => $this->notification_channels ?? [],
            'trigger_count' => $this->trigger_count,
            'effectiveness' => $this->getEffectiveness(),
            'estimated_savings' => $this->getEstimatedSavings(),
            'expires_at' => $this->expires_at?->toISOString(),
            'last_triggered_at' => $this->last_triggered_at?->toISOString()
        ];
    }

    /**
     * Create default price alert
     */
    public static function createDefault(User $user, Event $event, float $targetPrice): self
    {
        return self::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'name' => "Price Alert: {$event->name}",
            'alert_type' => self::TYPE_PRICE_DROP,
            'target_price' => $targetPrice,
            'is_active' => true,
            'platforms' => ['ticketmaster', 'seatgeek', 'stubhub'],
            'notification_channels' => ['email', 'push'],
            'min_interval_minutes' => 15,
            'max_triggers_per_day' => 5,
            'conditions' => [
                'require_availability' => true,
                'min_quantity' => 1
            ]
        ]);
    }

    /**
     * Optimize alert based on performance
     */
    public function optimize(): void
    {
        $effectiveness = $this->getEffectiveness();
        
        // Adjust trigger frequency based on effectiveness
        if ($effectiveness > 80) {
            // High effectiveness - allow more frequent triggers
            $this->update(['min_interval_minutes' => max(5, $this->min_interval_minutes - 5)]);
        } elseif ($effectiveness < 20) {
            // Low effectiveness - reduce trigger frequency
            $this->update(['min_interval_minutes' => min(120, $this->min_interval_minutes + 15)]);
        }
    }
}