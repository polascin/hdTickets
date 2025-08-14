<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function count;

class PriceVolatilityAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'analysis_date',
        'avg_price',
        'min_price',
        'max_price',
        'volatility_score',
        'price_changes_count',
        'max_single_change',
        'trend_direction',
        'hourly_data',
    ];

    protected $casts = [
        'analysis_date'       => 'date',
        'avg_price'           => 'decimal:2',
        'min_price'           => 'decimal:2',
        'max_price'           => 'decimal:2',
        'volatility_score'    => 'decimal:4',
        'price_changes_count' => 'integer',
        'max_single_change'   => 'decimal:2',
        'hourly_data'         => 'array',
    ];

    /**
     * Get the ticket this analytics data belongs to
     */
    /**
     * Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class, 'ticket_id');
    }

    /**
     * Scope for high volatility tickets
     *
     * @param mixed $query
     * @param mixed $threshold
     */
    public function scopeHighVolatility($query, $threshold = 0.15)
    {
        return $query->where('volatility_score', '>', $threshold);
    }

    /**
     * Scope for specific trend direction
     *
     * @param mixed $query
     * @param mixed $direction
     */
    public function scopeTrending($query, $direction)
    {
        return $query->where('trend_direction', $direction);
    }

    /**
     * Scope for recent analysis
     *
     * @param mixed $query
     * @param mixed $days
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('analysis_date', '>=', now()->subDays($days));
    }

    /**
     * Get price range
     */
    /**
     * Get  price range attribute
     */
    public function getPriceRangeAttribute(): float
    {
        return $this->max_price - $this->min_price;
    }

    /**
     * Get volatility classification
     */
    /**
     * Get  volatility classification attribute
     */
    public function getVolatilityClassificationAttribute(): string
    {
        if ($this->volatility_score >= 0.25) {
            return 'very_high';
        }
        if ($this->volatility_score >= 0.15) {
            return 'high';
        }
        if ($this->volatility_score >= 0.08) {
            return 'medium';
        }
        if ($this->volatility_score >= 0.03) {
            return 'low';
        }

        return 'very_low';
    }

    /**
     * Get formatted volatility score as percentage
     */
    /**
     * Get  formatted volatility attribute
     */
    public function getFormattedVolatilityAttribute(): string
    {
        return number_format($this->volatility_score * 100, 2) . '%';
    }

    /**
     * Calculate analytics data for a specific ticket and date
     *
     * @param mixed|null $date
     */
    /**
     * CalculateForTicket
     *
     * @param mixed $date
     */
    public static function calculateForTicket(int $ticketId, $date = NULL): ?self
    {
        $date = $date ?: now()->format('Y-m-d');

        $priceHistory = TicketPriceHistory::where('ticket_id', $ticketId)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at')
            ->get();

        if ($priceHistory->isEmpty()) {
            return NULL;
        }

        $prices = $priceHistory->pluck('price');
        $avgPrice = $prices->avg();
        $minPrice = $prices->min();
        $maxPrice = $prices->max();

        // Calculate volatility (coefficient of variation)
        $standardDeviation = sqrt($prices->map(function ($price) use ($avgPrice) {
            return pow($price - $avgPrice, 2);
        })->avg());

        $volatilityScore = $avgPrice > 0 ? $standardDeviation / $avgPrice : 0;

        // Calculate price changes
        $priceChanges = [];
        for ($i = 1; $i < $priceHistory->count(); $i++) {
            $previous = $priceHistory[$i - 1]->price;
            $current = $priceHistory[$i]->price;
            if ($previous > 0) {
                $priceChanges[] = abs(($current - $previous) / $previous) * 100;
            }
        }

        $maxSingleChange = ! empty($priceChanges) ? max($priceChanges) : 0;

        // Determine trend direction
        $firstPrice = $priceHistory->first()->price;
        $lastPrice = $priceHistory->last()->price;
        $changePercent = $firstPrice > 0 ? (($lastPrice - $firstPrice) / $firstPrice) * 100 : 0;

        $trendDirection = 'stable';
        if ($changePercent > 5) {
            $trendDirection = 'increasing';
        } elseif ($changePercent < -5) {
            $trendDirection = 'decreasing';
        }

        // Create or update analytics record
        return static::updateOrCreate(
            [
                'ticket_id'     => $ticketId,
                'analysis_date' => $date,
            ],
            [
                'avg_price'           => $avgPrice,
                'min_price'           => $minPrice,
                'max_price'           => $maxPrice,
                'volatility_score'    => $volatilityScore,
                'price_changes_count' => count($priceChanges),
                'max_single_change'   => $maxSingleChange,
                'trend_direction'     => $trendDirection,
                'hourly_data'         => static::generateHourlyData($priceHistory),
            ],
        );
    }

    /**
     * Generate hourly aggregated data
     *
     * @param mixed $priceHistory
     */
    /**
     * GenerateHourlyData
     *
     * @param mixed $priceHistory
     */
    private static function generateHourlyData($priceHistory): array
    {
        return $priceHistory->groupBy(function ($record) {
            return $record->recorded_at->format('H');
        })->map(function ($hourData) {
            $prices = $hourData->pluck('price');

            return [
                'avg_price' => $prices->avg(),
                'min_price' => $prices->min(),
                'max_price' => $prices->max(),
                'count'     => $prices->count(),
            ];
        })->toArray();
    }
}
