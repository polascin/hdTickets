<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScrapedTicket extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SOLD_OUT = 'sold_out';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_PENDING_VERIFICATION = 'pending_verification';

    public const STATUS_INVALID = 'invalid';

    protected $fillable = [
        'uuid',
        'platform',
        'external_id',
        'title',
        'venue',
        'location',
        'event_type',
        'sport',
        'team',
        'event_date',
        'min_price',
        'max_price',
        'currency',
        'availability',
        'is_available',
        'is_high_demand',
        'status',
        'ticket_url',
        'search_keyword',
        'metadata',
        'scraped_at',
        'category_id',
    ];

    protected $casts = [
        'event_date'     => 'datetime',
        'min_price'      => 'decimal:2',
        'max_price'      => 'decimal:2',
        'is_available'   => 'boolean',
        'is_high_demand' => 'boolean',
        'scraped_at'     => 'datetime',
        'metadata'       => 'array',
    ];

    protected $dates = [
        'event_date',
        'scraped_at',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Optimized Scopes for better performance
    public function scopeHighDemand($query)
    {
        return $query->where('is_high_demand', TRUE);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', TRUE);
    }

    public function scopeForEvent($query, $keywords)
    {
        return $query->where(function ($q) use ($keywords): void {
            $q->where('title', 'LIKE', '%' . $keywords . '%')
                ->orWhere('search_keyword', 'LIKE', '%' . $keywords . '%')
                ->orWhere('venue', 'LIKE', '%' . $keywords . '%');
        });
    }

    public function scopePriceRange($query, $minPrice = NULL, $maxPrice = NULL)
    {
        if ($minPrice) {
            $query->where('min_price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('max_price', '<=', $maxPrice);
        }

        return $query;
    }

    // Additional optimized scopes
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('scraped_at', '>=', now()->subHours($hours));
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }

    public function scopeByDateRange($query, $from = NULL, $to = NULL)
    {
        if ($from) {
            $query->where('event_date', '>=', $from);
        }
        if ($to) {
            $query->where('event_date', '<=', $to);
        }

        return $query;
    }

    public function scopeBySport($query, $sport)
    {
        return $query->where('sport', $sport);
    }

    public function scopeByTeam($query, $team)
    {
        return $query->where('team', 'LIKE', '%' . $team . '%');
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', '%' . $location . '%');
    }

    // Optimized search scope with full-text capabilities
    public function scopeFullTextSearch($query, $searchTerm)
    {
        return $query->whereRaw(
            'MATCH(title, venue, search_keyword, location) AGAINST(? IN BOOLEAN MODE)',
            [$searchTerm],
        );
    }

    // Performance-optimized scopes for analytics
    public function scopeWithinWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeWithinMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    // Helpers
    public function getFormattedPriceAttribute()
    {
        $price = $this->max_price ?? $this->min_price ?? 0;

        return ($this->currency ?? 'USD') . ' ' . number_format($price, 2);
    }

    public function getTotalPriceAttribute()
    {
        return $this->max_price ?? $this->min_price ?? 0;
    }

    public function getIsRecentAttribute()
    {
        return $this->scraped_at->diffInHours(now()) <= 24;
    }

    public function getPlatformDisplayNameAttribute()
    {
        return match ($this->platform) {
            'stubhub'      => 'StubHub',
            'ticketmaster' => 'Ticketmaster',
            'viagogo'      => 'Viagogo',
            default        => ucfirst($this->platform),
        };
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($ticket): void {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->scraped_at)) {
                $ticket->scraped_at = now();
            }
        });
    }
}
