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
        'event_title',
        'event_date',
        'venue',
        'section',
        'row',
        'seat_numbers',
        'price',
        'currency',
        'fees',
        'total_price',
        'availability_status',
        'quantity_available',
        'is_high_demand',
        'demand_score',
        'ticket_url',
        'image_url',
        'scraped_at',
        'metadata',
        'search_keywords',
        'listing_id'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'price' => 'decimal:2',
        'fees' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_high_demand' => 'boolean',
        'demand_score' => 'integer',
        'scraped_at' => 'datetime',
        'metadata' => 'array',
        'seat_numbers' => 'array'
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
        return $query->where('availability_status', 'available');
    }

    public function scopeForEvent($query, $keywords)
    {
        return $query->where('event_title', 'LIKE', '%' . $keywords . '%')
                    ->orWhere('search_keywords', 'LIKE', '%' . $keywords . '%');
    }

    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('total_price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('total_price', '<=', $maxPrice);
        }
        return $query;
    }

    // Helpers
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_price, 2);
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
