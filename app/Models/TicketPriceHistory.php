<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function count;

class TicketPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'price',
        'quantity',
        'recorded_at',
        'source',
        'metadata',
    ];

    /**
     * Get the ticket that owns this price history record
     */
    /**
     * Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class, 'ticket_id');
    }

    /**
     * Scope for recent price records
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific time range
     *
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Calculate average price for a time period
     */
    /**
     * Get  average price
     */
    public static function getAveragePrice(int $ticketId, int $days = 7): ?float
    {
        return static::where('ticket_id', $ticketId)
            ->recent($days)
            ->avg('price');
    }

    /**
     * Get price volatility (standard deviation) for a time period
     */
    /**
     * Get  price volatility
     */
    public static function getPriceVolatility(int $ticketId, int $days = 7): float
    {
        $prices = static::where('ticket_id', $ticketId)
            ->recent($days)
            ->pluck('price')
            ->toArray();

        if (count($prices) < 2) {
            return 0;
        }

        $mean = array_sum($prices) / count($prices);
        $variance = array_sum(array_map(fn ($price): float|int => ($price - $mean) ** 2, $prices)) / count($prices);

        return sqrt($variance);
    }

    /**
     * Get price trend direction
     */
    /**
     * Get  price trend
     */
    public static function getPriceTrend(int $ticketId, int $days = 7): string
    {
        $records = static::where('ticket_id', $ticketId)
            ->recent($days)
            ->orderBy('recorded_at')
            ->get(['price', 'recorded_at']);

        if ($records->count() < 2) {
            return 'stable';
        }

        $firstPrice = $records->first()->price;
        $lastPrice = $records->last()->price;
        $changePercent = (($lastPrice - $firstPrice) / $firstPrice) * 100;

        if ($changePercent > 5) {
            return 'increasing';
        }
        if ($changePercent < -5) {
            return 'decreasing';
        }

        return 'stable';
    }

    /**
     * Record price history for a ticket
     */
    /**
     * RecordPrice
     */
    public static function recordPrice(int $ticketId, float $price, int $quantity, string $source = 'scraper'): void
    {
        // Only record if price or quantity has changed significantly
        $lastRecord = static::where('ticket_id', $ticketId)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($lastRecord) {
            $priceChange = abs($price - $lastRecord->price);
            $quantityChange = abs($quantity - $lastRecord->quantity);

            // Skip if changes are minimal and recorded recently
            if ($priceChange < 0.01 && $quantityChange === 0
                && $lastRecord->recorded_at->diffInMinutes(now()) < 15) {
                return;
            }
        }

        static::create([
            'ticket_id'   => $ticketId,
            'price'       => $price,
            'quantity'    => $quantity,
            'recorded_at' => now(),
            'source'      => $source,
            'metadata'    => [
                'price_change'    => $lastRecord ? round($price - $lastRecord->price, 2) : 0,
                'quantity_change' => $lastRecord ? $quantity - $lastRecord->quantity : 0,
            ],
        ]);
    }

    /**
     * Clean up old price history records
     */
    /**
     * Cleanup
     */
    public static function cleanup(int $daysToKeep = 90): int
    {
        return static::where('recorded_at', '<', now()->subDays($daysToKeep))->delete();
    }

    /**
     * Get  price change attribute
     */
    protected function priceChange(): Attribute
    {
        return Attribute::make(get: function (): NULL|int|float {
            $previousRecord = static::where('ticket_id', $this->ticket_id)
                ->where('recorded_at', '<', $this->recorded_at)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if (!$previousRecord || $previousRecord->price === 0) {
                return NULL;
            }

            return (($this->price - $previousRecord->price) / $previousRecord->price) * 100;
        });
    }

    /**
     * Get  quantity change attribute
     */
    protected function quantityChange(): Attribute
    {
        return Attribute::make(get: function (): NULL|int|float {
            $previousRecord = static::where('ticket_id', $this->ticket_id)
                ->where('recorded_at', '<', $this->recorded_at)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if (!$previousRecord) {
                return NULL;
            }

            return $this->quantity - $previousRecord->quantity;
        });
    }

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'price'       => 'decimal:2',
            'quantity'    => 'integer',
            'metadata'    => 'array',
        ];
    }
}
