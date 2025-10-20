<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Price History Model
 *
 * Stores historical price data for comprehensive analytics
 */
class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_histories';

    protected $fillable = [
        'event_id',
        'platform',
        'price_min',
        'price_max',
        'price_average',
        'total_listings',
        'available_quantity',
        'currency',
        'market_conditions',
        'quality_score',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'price_min'          => 'decimal:2',
        'price_max'          => 'decimal:2',
        'price_average'      => 'decimal:2',
        'total_listings'     => 'integer',
        'available_quantity' => 'integer',
        'quality_score'      => 'decimal:2',
        'market_conditions'  => 'array',
        'metadata'           => 'array',
        'recorded_at'        => 'datetime',
    ];

    /**
     * Get the event this price history belongs to
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope for recent price data
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for specific platform
     *
     * @param mixed $query
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for date range
     *
     * @param mixed $query
     */
    public function scopeDateRange($query, \Carbon\Carbon $start, \Carbon\Carbon $end)
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }

    /**
     * Scope for price range
     *
     * @param mixed $query
     */
    public function scopePriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price_min', [$minPrice, $maxPrice]);
    }

    /**
     * Scope for high quality data
     *
     * @param mixed $query
     */
    public function scopeHighQuality($query, float $threshold = 0.7)
    {
        return $query->where('quality_score', '>=', $threshold);
    }

    /**
     * Get price change from previous record
     */
    public function getPriceChange(): ?array
    {
        $previousRecord = self::where('event_id', $this->event_id)
            ->where('platform', $this->platform)
            ->where('recorded_at', '<', $this->recorded_at)
            ->orderByDesc('recorded_at')
            ->first();

        if (! $previousRecord) {
            return NULL;
        }

        $minChange = $this->price_min - $previousRecord->price_min;
        $maxChange = $this->price_max - $previousRecord->price_max;
        $avgChange = $this->price_average - $previousRecord->price_average;

        return [
            'min_change'            => round($minChange, 2),
            'max_change'            => round($maxChange, 2),
            'avg_change'            => round($avgChange, 2),
            'min_change_percentage' => $previousRecord->price_min > 0 ?
                round(($minChange / $previousRecord->price_min) * 100, 2) : 0,
            'avg_change_percentage' => $previousRecord->price_average > 0 ?
                round(($avgChange / $previousRecord->price_average) * 100, 2) : 0,
        ];
    }

    /**
     * Check if this is a significant price drop
     */
    public function isSignificantDrop(float $threshold = 10.0): bool
    {
        $change = $this->getPriceChange();

        if (! $change) {
            return FALSE;
        }

        return $change['avg_change_percentage'] <= -$threshold;
    }

    /**
     * Get market condition summary
     */
    public function getMarketConditionSummary(): string
    {
        $conditions = $this->market_conditions ?? [];

        $demand = $conditions['demand_level'] ?? 'medium';
        $inventory = $conditions['inventory_status'] ?? 'normal';
        $volatility = $conditions['price_volatility'] ?? 'stable';

        return ucfirst($demand) . ' demand, ' . $inventory . ' inventory, ' . $volatility . ' prices';
    }

    /**
     * Check if data is fresh (recorded within last hour)
     */
    public function isFresh(): bool
    {
        return $this->recorded_at->diffInHours(now()) < 1;
    }

    /**
     * Get formatted price range
     */
    public function getFormattedPriceRange(): string
    {
        if ($this->price_min === $this->price_max) {
            return "£{$this->price_min}";
        }

        return "£{$this->price_min} - £{$this->price_max}";
    }

    /**
     * Calculate price spread percentage
     */
    public function getPriceSpread(): float
    {
        if ($this->price_min <= 0) {
            return 0.0;
        }

        return round((($this->price_max - $this->price_min) / $this->price_min) * 100, 2);
    }

    /**
     * Get demand indicator (inverse of availability)
     */
    public function getDemandIndicator(): float
    {
        if ($this->available_quantity <= 0) {
            return 1.0; // Maximum demand
        }

        // Normalize demand based on typical inventory levels
        $normalizedInventory = min($this->available_quantity / 100, 1.0);

        return round(1.0 - $normalizedInventory, 2);
    }

    /**
     * Check if inventory is critically low
     */
    public function isCriticalInventory(): bool
    {
        return $this->available_quantity <= 10;
    }

    /**
     * Get analytics summary for this record
     */
    public function getAnalyticsSummary(): array
    {
        return [
            'record_id'             => $this->id,
            'event_id'              => $this->event_id,
            'platform'              => $this->platform,
            'price_range'           => $this->getFormattedPriceRange(),
            'price_spread'          => $this->getPriceSpread(),
            'demand_indicator'      => $this->getDemandIndicator(),
            'market_summary'        => $this->getMarketConditionSummary(),
            'quality_score'         => $this->quality_score,
            'is_fresh'              => $this->isFresh(),
            'is_critical_inventory' => $this->isCriticalInventory(),
            'recorded_at'           => $this->recorded_at->toISOString(),
        ];
    }
}
