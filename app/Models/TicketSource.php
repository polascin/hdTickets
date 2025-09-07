<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function in_array;

class TicketSource extends Model
{
    use HasFactory;

    // Available platforms (matching database enum)
    public const PLATFORM_OFFICIAL = 'official';

    public const PLATFORM_TICKETMASTER = 'ticketmaster';

    public const PLATFORM_STUBHUB = 'stubhub';

    public const PLATFORM_VIAGOGO = 'viagogo';

    public const PLATFORM_SEATGEEK = 'seatgeek';

    public const PLATFORM_TICKPICK = 'tickpick';

    public const PLATFORM_AXS = 'axs';

    public const PLATFORM_EVENTBRITE = 'eventbrite';

    public const PLATFORM_EVENTIM = 'eventim';

    public const PLATFORM_BANDSINTOWN = 'bandsintown';

    public const PLATFORM_TICKETEK_UK = 'ticketek_uk';

    public const PLATFORM_SEETICKETS_UK = 'seetickets_uk';

    public const PLATFORM_VIVATICKET = 'vivaticket';

    public const PLATFORM_FNAC_SPECTACLES = 'fnac_spectacles';

    public const PLATFORM_ENTRADAS = 'entradas';

    public const PLATFORM_OTHER = 'other';

    // Sports clubs/venues
    public const PLATFORM_MANCHESTER_UNITED = 'manchester_united';

    public const PLATFORM_MANCHESTER_CITY = 'manchester_city';

    public const PLATFORM_ARSENAL = 'arsenal';

    public const PLATFORM_CHELSEA = 'chelsea';

    public const PLATFORM_LIVERPOOL = 'liverpoolfc';

    public const PLATFORM_TOTTENHAM = 'tottenham';

    public const PLATFORM_NEWCASTLE_UNITED = 'newcastle_united';

    public const PLATFORM_REAL_MADRID = 'real_madrid';

    public const PLATFORM_BARCELONA = 'barcelona';

    public const PLATFORM_AC_MILAN = 'ac_milan';

    public const PLATFORM_INTER_MILAN = 'inter_milan';

    public const PLATFORM_JUVENTUS = 'juventus';

    public const PLATFORM_BAYERN_MUNICH = 'bayern_munich';

    public const PLATFORM_BORUSSIA_DORTMUND = 'borussia_dortmund';

    public const PLATFORM_PSG = 'psg';

    public const PLATFORM_ATLETICO_MADRID = 'atletico_madrid';

    public const PLATFORM_CELTIC = 'celtic';

    // Venues
    public const PLATFORM_WEMBLEY = 'wembley';

    public const PLATFORM_WIMBLEDON = 'wimbledon';

    public const PLATFORM_LORDS_CRICKET = 'lords_cricket';

    public const PLATFORM_ENGLAND_CRICKET = 'england_cricket';

    public const PLATFORM_TWICKENHAM = 'twickenham';

    public const PLATFORM_SILVERSTONE_F1 = 'silverstone_f1';

    // Availability statuses
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_LOW_INVENTORY = 'low_inventory';

    public const STATUS_SOLD_OUT = 'sold_out';

    public const STATUS_NOT_ON_SALE = 'not_on_sale';

    public const STATUS_UNKNOWN = 'unknown';

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
        'event_date'   => 'datetime',
        'last_checked' => 'datetime',
        'is_active'    => 'boolean',
        'price_min'    => 'decimal:2',
        'price_max'    => 'decimal:2',
    ];

    public static function getPlatforms()
    {
        return [
            // General platforms
            self::PLATFORM_OFFICIAL        => 'Official',
            self::PLATFORM_TICKETMASTER    => 'Ticketmaster',
            self::PLATFORM_STUBHUB         => 'StubHub',
            self::PLATFORM_VIAGOGO         => 'Viagogo',
            self::PLATFORM_SEATGEEK        => 'SeatGeek',
            self::PLATFORM_TICKPICK        => 'TickPick',
            self::PLATFORM_AXS             => 'AXS',
            self::PLATFORM_EVENTBRITE      => 'Eventbrite',
            self::PLATFORM_EVENTIM         => 'Eventim',
            self::PLATFORM_BANDSINTOWN     => 'Bandsintown',
            self::PLATFORM_TICKETEK_UK     => 'Ticketek UK',
            self::PLATFORM_SEETICKETS_UK   => 'SeeTickets UK',
            self::PLATFORM_VIVATICKET      => 'VivaTicket',
            self::PLATFORM_FNAC_SPECTACLES => 'Fnac Spectacles',
            self::PLATFORM_ENTRADAS        => 'Entradas',

            // Football Clubs
            self::PLATFORM_MANCHESTER_UNITED => 'Manchester United',
            self::PLATFORM_MANCHESTER_CITY   => 'Manchester City',
            self::PLATFORM_ARSENAL           => 'Arsenal',
            self::PLATFORM_CHELSEA           => 'Chelsea',
            self::PLATFORM_LIVERPOOL         => 'Liverpool FC',
            self::PLATFORM_TOTTENHAM         => 'Tottenham',
            self::PLATFORM_NEWCASTLE_UNITED  => 'Newcastle United',
            self::PLATFORM_REAL_MADRID       => 'Real Madrid',
            self::PLATFORM_BARCELONA         => 'Barcelona',
            self::PLATFORM_AC_MILAN          => 'AC Milan',
            self::PLATFORM_INTER_MILAN       => 'Inter Milan',
            self::PLATFORM_JUVENTUS          => 'Juventus',
            self::PLATFORM_BAYERN_MUNICH     => 'Bayern Munich',
            self::PLATFORM_BORUSSIA_DORTMUND => 'Borussia Dortmund',
            self::PLATFORM_PSG               => 'Paris Saint-Germain',
            self::PLATFORM_ATLETICO_MADRID   => 'Atlético Madrid',
            self::PLATFORM_CELTIC            => 'Celtic',

            // Venues
            self::PLATFORM_WEMBLEY         => 'Wembley Stadium',
            self::PLATFORM_WIMBLEDON       => 'Wimbledon',
            self::PLATFORM_LORDS_CRICKET   => 'Lord\'s Cricket',
            self::PLATFORM_ENGLAND_CRICKET => 'England Cricket',
            self::PLATFORM_TWICKENHAM      => 'Twickenham',
            self::PLATFORM_SILVERSTONE_F1  => 'Silverstone F1',

            self::PLATFORM_OTHER => 'Other',
        ];
    }

    /**
     * Get  statuses
     */
    public function getStatuses(): string
    {
        return [
            self::STATUS_AVAILABLE     => 'Available',
            self::STATUS_LOW_INVENTORY => 'Low Inventory',
            self::STATUS_SOLD_OUT      => 'Sold Out',
            self::STATUS_NOT_ON_SALE   => 'Not On Sale',
            self::STATUS_UNKNOWN       => 'Unknown',
        ];
    }

    /**
     * Get  platform name attribute
     */
    public function getPlatformNameAttribute(): string
    {
        $platforms = self::getPlatforms();

        return $platforms[$this->platform] ?? 'Unknown';
    }

    /**
     * Get  status name attribute
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = self::getStatuses();

        return $statuses[$this->availability_status] ?? 'Unknown';
    }

    /**
     * Check if  available
     */
    public function isAvailable(): bool
    {
        return $this->availability_status === self::STATUS_AVAILABLE;
    }

    /**
     * ScopeActive
     *
     * @param mixed $query
     */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', TRUE);
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

    public function scopeInPriceRange($query, $minPrice = NULL, $maxPrice = NULL)
    {
        if ($minPrice !== NULL) {
            $query->where('price_min', '>=', $minPrice);
        }
        if ($maxPrice !== NULL) {
            $query->where('price_max', '<=', $maxPrice);
        }

        return $query;
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Helper methods
    /**
     * Get  formatted price attribute
     */
    public function getFormattedPriceAttribute(): float
    {
        $symbol = $this->getCurrencySymbol();

        if ($this->price_min && $this->price_max) {
            if ($this->price_min === $this->price_max) {
                return $symbol . number_format($this->price_min, 2);
            }

            return $symbol . number_format($this->price_min, 2) . ' - ' . $symbol . number_format($this->price_max, 2);
        }
        if ($this->price_min) {
            return 'From ' . $symbol . number_format($this->price_min, 2);
        }
        if ($this->price_max) {
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
            return;
        }

        return Carbon::now()->diffForHumans($this->event_date, TRUE);
    }

    public function getLastCheckedHumanAttribute()
    {
        if (!$this->last_checked) {
            return 'Never';
        }

        return $this->last_checked->diffForHumans();
    }

    /**
     * Get  status badge class attribute
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            self::STATUS_AVAILABLE     => 'bg-green-100 text-green-800',
            self::STATUS_LOW_INVENTORY => 'bg-yellow-100 text-yellow-800',
            self::STATUS_SOLD_OUT      => 'bg-red-100 text-red-800',
            self::STATUS_NOT_ON_SALE   => 'bg-gray-100 text-gray-800',
            self::STATUS_UNKNOWN       => 'bg-blue-100 text-blue-800',
        ];

        return $classes[$this->availability_status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if  platform club
     */
    public function isPlatformClub(): bool
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

        return in_array($this->platform, $clubs, TRUE);
    }

    /**
     * Check if  platform venue
     */
    public function isPlatformVenue(): bool
    {
        $venues = [
            self::PLATFORM_WEMBLEY,
            self::PLATFORM_WIMBLEDON,
            self::PLATFORM_LORDS_CRICKET,
            self::PLATFORM_ENGLAND_CRICKET,
            self::PLATFORM_TWICKENHAM,
            self::PLATFORM_SILVERSTONE_F1,
        ];

        return in_array($this->platform, $venues, TRUE);
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

    /**
     * Get  countries
     */
    public function getCountries(): int
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
