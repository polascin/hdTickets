<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ScrapedTicket extends Model
{
    use HasFactory;

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
        'ticket_url',
        'search_keyword',
        'metadata',
        'scraped_at',
        'category_id'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_high_demand' => 'boolean',
        'scraped_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'event_date',
        'scraped_at'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->scraped_at)) {
                $ticket->scraped_at = now();
            }
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeHighDemand($query)
    {
        return $query->where('is_high_demand', true);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForEvent($query, $keywords)
    {
        return $query->where('title', 'LIKE', '%' . $keywords . '%')
                    ->orWhere('search_keyword', 'LIKE', '%' . $keywords . '%');
    }

    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('min_price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('max_price', '<=', $maxPrice);
        }
        return $query;
    }

    // Helpers
    public function getFormattedPriceAttribute()
    {
        $price = $this->max_price ?? $this->min_price ?? 0;
        return $this->currency . ' ' . number_format($price, 2);
    }

    public function getIsRecentAttribute()
    {
        return $this->scraped_at->diffInHours(now()) <= 24;
    }

    public function getPlatformDisplayNameAttribute()
    {
        return match($this->platform) {
            'stubhub' => 'StubHub',
            'ticketmaster' => 'Ticketmaster',
            'viagogo' => 'Viagogo',
            default => ucfirst($this->platform)
        };
    }
}
