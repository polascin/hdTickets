<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_id',
        'platform',
        'event_name',
        'event_date',
        'venue',
        'price_min',
        'price_max',
        'availability_status',
        'url',
        'description',
        'last_checked',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'last_checked' => 'datetime',
        'is_active' => 'boolean',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    // Available platforms
    const PLATFORM_OFFICIAL = 'official';
    const PLATFORM_TICKETMASTER = 'ticketmaster';
    const PLATFORM_STUBHUB = 'stubhub';
    const PLATFORM_VIAGOGO = 'viagogo';
    const PLATFORM_SEATGEEK = 'seatgeek';
    const PLATFORM_TICKPICK = 'tickpick';
    const PLATFORM_FANZONE = 'fanzone';
    const PLATFORM_OTHER = 'other';

    // Availability statuses
    const STATUS_AVAILABLE = 'available';
    const STATUS_LOW_INVENTORY = 'low_inventory';
    const STATUS_SOLD_OUT = 'sold_out';
    const STATUS_NOT_ON_SALE = 'not_on_sale';
    const STATUS_UNKNOWN = 'unknown';

    public static function getPlatforms()
    {
        return [
            self::PLATFORM_OFFICIAL => 'Official Club App',
            self::PLATFORM_TICKETMASTER => 'Ticketmaster',
            self::PLATFORM_STUBHUB => 'StubHub',
            self::PLATFORM_VIAGOGO => 'Viagogo',
            self::PLATFORM_SEATGEEK => 'SeatGeek',
            self::PLATFORM_TICKPICK => 'TickPick',
            self::PLATFORM_FANZONE => 'FanZone',
            self::PLATFORM_OTHER => 'Other',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_LOW_INVENTORY => 'Low Inventory',
            self::STATUS_SOLD_OUT => 'Sold Out',
            self::STATUS_NOT_ON_SALE => 'Not On Sale',
            self::STATUS_UNKNOWN => 'Unknown',
        ];
    }

    public function getPlatformNameAttribute()
    {
        $platforms = self::getPlatforms();
        return $platforms[$this->platform] ?? 'Unknown';
    }

    public function getStatusNameAttribute()
    {
        $statuses = self::getStatuses();
        return $statuses[$this->availability_status] ?? 'Unknown';
    }

    public function isAvailable()
    {
        return $this->availability_status === self::STATUS_AVAILABLE;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability_status', self::STATUS_AVAILABLE);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}
