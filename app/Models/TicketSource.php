<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TicketSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'external_id',
        'platform',
        'event_name',
        'event_date',
        'venue',
        'price_min',
        'price_max',
        'currency',
        'language',
        'country',
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

    // Available platforms (matching database enum)
    const PLATFORM_OFFICIAL = 'official';
    const PLATFORM_TICKETMASTER = 'ticketmaster';
    const PLATFORM_STUBHUB = 'stubhub';
    const PLATFORM_VIAGOGO = 'viagogo';
    const PLATFORM_SEATGEEK = 'seatgeek';
    const PLATFORM_TICKPICK = 'tickpick';
    const PLATFORM_AXS = 'axs';
    const PLATFORM_EVENTBRITE = 'eventbrite';
    const PLATFORM_EVENTIM = 'eventim';
    const PLATFORM_BANDSINTOWN = 'bandsintown';
    const PLATFORM_TICKETEK_UK = 'ticketek_uk';
    const PLATFORM_SEETICKETS_UK = 'seetickets_uk';
    const PLATFORM_VIVATICKET = 'vivaticket';
    const PLATFORM_FNAC_SPECTACLES = 'fnac_spectacles';
    const PLATFORM_ENTRADAS = 'entradas';
    const PLATFORM_OTHER = 'other';
    
    // Sports clubs/venues
    const PLATFORM_MANCHESTER_UNITED = 'manchester_united';
    const PLATFORM_MANCHESTER_CITY = 'manchester_city';
    const PLATFORM_ARSENAL = 'arsenal';
    const PLATFORM_CHELSEA = 'chelsea';
    const PLATFORM_LIVERPOOL = 'liverpoolfc';
    const PLATFORM_TOTTENHAM = 'tottenham';
    const PLATFORM_NEWCASTLE_UNITED = 'newcastle_united';
    const PLATFORM_REAL_MADRID = 'real_madrid';
    const PLATFORM_BARCELONA = 'barcelona';
    const PLATFORM_AC_MILAN = 'ac_milan';
    const PLATFORM_INTER_MILAN = 'inter_milan';
    const PLATFORM_JUVENTUS = 'juventus';
    const PLATFORM_BAYERN_MUNICH = 'bayern_munich';
    const PLATFORM_BORUSSIA_DORTMUND = 'borussia_dortmund';
    const PLATFORM_PSG = 'psg';
    const PLATFORM_ATLETICO_MADRID = 'atletico_madrid';
    const PLATFORM_CELTIC = 'celtic';
    
    // Venues
    const PLATFORM_WEMBLEY = 'wembley';
    const PLATFORM_WIMBLEDON = 'wimbledon';
    const PLATFORM_LORDS_CRICKET = 'lords_cricket';
    const PLATFORM_ENGLAND_CRICKET = 'england_cricket';
    const PLATFORM_TWICKENHAM = 'twickenham';
    const PLATFORM_SILVERSTONE_F1 = 'silverstone_f1';

    // Availability statuses
    const STATUS_AVAILABLE = 'available';
    const STATUS_LOW_INVENTORY = 'low_inventory';
    const STATUS_SOLD_OUT = 'sold_out';
    const STATUS_NOT_ON_SALE = 'not_on_sale';
    const STATUS_UNKNOWN = 'unknown';

    public static function getPlatforms()
    {
        return [
            // General platforms
            self::PLATFORM_OFFICIAL => 'Official',
            self::PLATFORM_TICKETMASTER => 'Ticketmaster',
            self::PLATFORM_STUBHUB => 'StubHub',
            self::PLATFORM_VIAGOGO => 'Viagogo',
            self::PLATFORM_SEATGEEK => 'SeatGeek',
            self::PLATFORM_TICKPICK => 'TickPick',
            self::PLATFORM_AXS => 'AXS',
            self::PLATFORM_EVENTBRITE => 'Eventbrite',
            self::PLATFORM_EVENTIM => 'Eventim',
            self::PLATFORM_BANDSINTOWN => 'Bandsintown',
            self::PLATFORM_TICKETEK_UK => 'Ticketek UK',
            self::PLATFORM_SEETICKETS_UK => 'SeeTickets UK',
            self::PLATFORM_VIVATICKET => 'VivaTicket',
            self::PLATFORM_FNAC_SPECTACLES => 'Fnac Spectacles',
            self::PLATFORM_ENTRADAS => 'Entradas',
            
            // Football Clubs
            self::PLATFORM_MANCHESTER_UNITED => 'Manchester United',
            self::PLATFORM_MANCHESTER_CITY => 'Manchester City',
            self::PLATFORM_ARSENAL => 'Arsenal',
            self::PLATFORM_CHELSEA => 'Chelsea',
            self::PLATFORM_LIVERPOOL => 'Liverpool FC',
            self::PLATFORM_TOTTENHAM => 'Tottenham',
            self::PLATFORM_NEWCASTLE_UNITED => 'Newcastle United',
            self::PLATFORM_REAL_MADRID => 'Real Madrid',
            self::PLATFORM_BARCELONA => 'Barcelona',
            self::PLATFORM_AC_MILAN => 'AC Milan',
            self::PLATFORM_INTER_MILAN => 'Inter Milan',
            self::PLATFORM_JUVENTUS => 'Juventus',
            self::PLATFORM_BAYERN_MUNICH => 'Bayern Munich',
            self::PLATFORM_BORUSSIA_DORTMUND => 'Borussia Dortmund',
            self::PLATFORM_PSG => 'Paris Saint-Germain',
            self::PLATFORM_ATLETICO_MADRID => 'Atlético Madrid',
            self::PLATFORM_CELTIC => 'Celtic',
            
            // Venues
            self::PLATFORM_WEMBLEY => 'Wembley Stadium',
            self::PLATFORM_WIMBLEDON => 'Wimbledon',
            self::PLATFORM_LORDS_CRICKET => 'Lord\'s Cricket',
            self::PLATFORM_ENGLAND_CRICKET => 'England Cricket',
            self::PLATFORM_TWICKENHAM => 'Twickenham',
            self::PLATFORM_SILVERSTONE_F1 => 'Silverstone F1',
            
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

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('event_date', '<', now());
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeInPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price_min', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price_max', '<=', $maxPrice);
        }
        return $query;
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    // Helper methods
    public function getFormattedPriceAttribute()
    {
        $symbol = $this->getCurrencySymbol();
        
        if ($this->price_min && $this->price_max) {
            if ($this->price_min == $this->price_max) {
                return $symbol . number_format($this->price_min, 2);
            }
            return $symbol . number_format($this->price_min, 2) . ' - ' . $symbol . number_format($this->price_max, 2);
        } elseif ($this->price_min) {
            return 'From ' . $symbol . number_format($this->price_min, 2);
        } elseif ($this->price_max) {
            return 'Up to ' . $symbol . number_format($this->price_max, 2);
        }
        
        return 'Price TBD';
    }

    public function getCurrencySymbol()
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];
        
        return $symbols[$this->currency] ?? $this->currency;
    }

    public function getTimeUntilEventAttribute()
    {
        if (!$this->event_date) {
            return null;
        }
        
        return Carbon::now()->diffForHumans($this->event_date, true);
    }

    public function getLastCheckedHumanAttribute()
    {
        if (!$this->last_checked) {
            return 'Never';
        }
        
        return $this->last_checked->diffForHumans();
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            self::STATUS_AVAILABLE => 'bg-green-100 text-green-800',
            self::STATUS_LOW_INVENTORY => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SOLD_OUT => 'bg-red-100 text-red-800',
            self::STATUS_NOT_ON_SALE => 'bg-gray-100 text-gray-800',
            self::STATUS_UNKNOWN => 'bg-blue-100 text-blue-800',
        ];
        
        return $classes[$this->availability_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function isPlatformClub()
    {
        $clubs = [
            self::PLATFORM_MANCHESTER_UNITED,
            self::PLATFORM_MANCHESTER_CITY,
            self::PLATFORM_ARSENAL,
            self::PLATFORM_CHELSEA,
            self::PLATFORM_LIVERPOOL,
            self::PLATFORM_TOTTENHAM,
            self::PLATFORM_NEWCASTLE_UNITED,
            self::PLATFORM_REAL_MADRID,
            self::PLATFORM_BARCELONA,
            self::PLATFORM_AC_MILAN,
            self::PLATFORM_INTER_MILAN,
            self::PLATFORM_JUVENTUS,
            self::PLATFORM_BAYERN_MUNICH,
            self::PLATFORM_BORUSSIA_DORTMUND,
            self::PLATFORM_PSG,
            self::PLATFORM_ATLETICO_MADRID,
            self::PLATFORM_CELTIC,
        ];
        
        return in_array($this->platform, $clubs);
    }

    public function isPlatformVenue()
    {
        $venues = [
            self::PLATFORM_WEMBLEY,
            self::PLATFORM_WIMBLEDON,
            self::PLATFORM_LORDS_CRICKET,
            self::PLATFORM_ENGLAND_CRICKET,
            self::PLATFORM_TWICKENHAM,
            self::PLATFORM_SILVERSTONE_F1,
        ];
        
        return in_array($this->platform, $venues);
    }

    public static function getCurrencies()
    {
        return [
            'GBP' => 'British Pound',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'JPY' => 'Japanese Yen',
        ];
    }

    public static function getCountries()
    {
        return [
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'JP' => 'Japan',
        ];
    }
}
