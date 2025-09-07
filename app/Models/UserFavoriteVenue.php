<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavoriteVenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'venue_name',
        'venue_slug',
        'city',
        'state_province',
        'country',
        'capacity',
        'venue_types',
        'latitude',
        'longitude',
        'venue_image_url',
        'aliases',
        'email_alerts',
        'push_alerts',
        'sms_alerts',
        'priority',
    ];

    protected $casts = [
        'venue_types'  => 'array',
        'aliases'      => 'array',
        'email_alerts' => 'boolean',
        'push_alerts'  => 'boolean',
        'sms_alerts'   => 'boolean',
        'priority'     => 'integer',
        'capacity'     => 'integer',
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
    ];

    /**
     * Get the user that owns this favorite venue
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by city
     *
     * @param mixed $query
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to filter by state/province
     *
     * @param mixed $query
     */
    public function scopeByStateProvince($query, string $stateProvince)
    {
        return $query->where('state_province', $stateProvince);
    }

    /**
     * Scope to filter by country
     *
     * @param mixed $query
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to filter by venue type
     *
     * @param mixed $query
     */
    public function scopeByVenueType($query, string $venueType)
    {
        return $query->whereJsonContains('venue_types', $venueType);
    }

    /**
     * Scope to filter by priority
     *
     * @param mixed $query
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for high priority venues
     *
     * @param mixed $query
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 4);
    }

    /**
     * Scope for venues with email alerts enabled
     *
     * @param mixed $query
     */
    public function scopeWithEmailAlerts($query)
    {
        return $query->where('email_alerts', TRUE);
    }

    /**
     * Search venues by name or city
     *
     * @param mixed $query
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('venue_name', 'LIKE', "%{$term}%")
                ->orWhere('city', 'LIKE', "%{$term}%")
                ->orWhere('state_province', 'LIKE', "%{$term}%")
                ->orWhereJsonContains('aliases', $term);
        });
    }

    /**
     * Scope to find venues within a radius (miles)
     *
     * @param mixed $query
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, int $radiusMiles)
    {
        $radiusDegrees = $radiusMiles / 69; // Approximate conversion

        return $query->whereRaw(
            'SQRT(POW(69.1 * (latitude - ?), 2) + POW(69.1 * (? - longitude) * COS(latitude / 57.3), 2)) <= ?',
            [$latitude, $longitude, $radiusMiles],
        );
    }

    /**
     * Get the full venue display name
     */
    /**
     * Get  full name attribute
     */
    public function getFullNameAttribute(): string
    {
        $location = collect([$this->city, $this->state_province, $this->country])
            ->filter()
            ->join(', ');

        return $this->venue_name . ($location ? " ({$location})" : '');
    }

    /**
     * Generate venue slug from name and location
     */
    /**
     * GenerateSlug
     */
    public function generateSlug(): string
    {
        $name = $this->venue_name . ' ' . $this->city;

        return strtolower(str_replace([' ', '&', '.'], ['-', 'and', ''], $name));
    }

    /**
     * Set venue slug automatically
     *
     * @param mixed $value
     */
    /**
     * Set  venue slug attribute
     *
     * @param mixed $value
     */
    public function setVenueSlugAttribute($value): void
    {
        $this->attributes['venue_slug'] = $value ?: $this->generateSlug();
    }

    /**
     * Get all available venue types
     */
    /**
     * Get  available venue types
     */
    public static function getAvailableVenueTypes(): array
    {
        return [
            'stadium'           => 'Stadium',
            'arena'             => 'Arena',
            'amphitheater'      => 'Amphitheater',
            'theater'           => 'Theater',
            'concert_hall'      => 'Concert Hall',
            'convention_center' => 'Convention Center',
            'outdoor_venue'     => 'Outdoor Venue',
            'club'              => 'Club/Bar',
            'racetrack'         => 'Racetrack',
            'golf_course'       => 'Golf Course',
            'other'             => 'Other',
        ];
    }

    /**
     * Get popular venues for autocomplete
     */
    /**
     * Get  popular venues
     */
    public static function getPopularVenues(?string $city = NULL): array
    {
        $query = self::select('venue_name', 'city', 'state_province', 'country', 'venue_types')
            ->selectRaw('COUNT(*) as popularity')
            ->groupBy(['venue_name', 'city', 'state_province', 'country', 'venue_types']);

        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }

        return $query->orderByDesc('popularity')
            ->limit(50)
            ->get()
            ->map(function ($venue) {
                return [
                    'name'           => $venue->venue_name,
                    'city'           => $venue->city,
                    'state_province' => $venue->state_province,
                    'country'        => $venue->country,
                    'full_name'      => $venue->venue_name . " ({$venue->city})",
                    'venue_types'    => $venue->venue_types,
                    'popularity'     => $venue->popularity,
                ];
            })
            ->toArray();
    }

    /**
     * Check if venue matches search criteria
     */
    /**
     * MatchesSearch
     */
    public function matchesSearch(string $term): bool
    {
        $term = strtolower($term);

        return str_contains(strtolower($this->venue_name), $term)
               || str_contains(strtolower($this->city), $term)
               || str_contains(strtolower($this->state_province ?? ''), $term)
               || collect($this->aliases ?? [])->contains(function ($alias) use ($term) {
                   return str_contains(strtolower($alias), $term);
               });
    }

    /**
     * Get notification settings as array
     */
    /**
     * Get  notification settings
     */
    public function getNotificationSettings(): array
    {
        return [
            'email' => $this->email_alerts,
            'push'  => $this->push_alerts,
            'sms'   => $this->sms_alerts,
        ];
    }

    /**
     * Update notification settings
     */
    /**
     * UpdateNotificationSettings
     */
    public function updateNotificationSettings(array $settings): void
    {
        $this->update([
            'email_alerts' => $settings['email'] ?? $this->email_alerts,
            'push_alerts'  => $settings['push'] ?? $this->push_alerts,
            'sms_alerts'   => $settings['sms'] ?? $this->sms_alerts,
        ]);
    }

    /**
     * Calculate distance to venue from given coordinates
     */
    /**
     * DistanceFrom
     */
    public function distanceFrom(float $latitude, float $longitude): ?float
    {
        if (!$this->latitude || !$this->longitude) {
            return NULL;
        }

        $earthRadius = 3959; // Miles

        $latDelta = deg2rad($this->latitude - $latitude);
        $lonDelta = deg2rad($this->longitude - $longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($latitude)) * cos(deg2rad($this->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get venue capacity tier
     */
    /**
     * Get  capacity tier attribute
     */
    public function getCapacityTierAttribute(): string
    {
        if (!$this->capacity) {
            return 'unknown';
        }

        if ($this->capacity >= 50000) {
            return 'large';
        }
        if ($this->capacity >= 20000) {
            return 'medium';
        }
        if ($this->capacity >= 5000) {
            return 'small';
        }

        return 'intimate';
    }

    /**
     * Check if venue is outdoors
     */
    /**
     * Check if  outdoor
     */
    public function isOutdoor(): bool
    {
        $outdoorTypes = ['stadium', 'amphitheater', 'outdoor_venue', 'racetrack', 'golf_course'];

        return !empty(array_intersect($this->venue_types ?? [], $outdoorTypes));
    }

    /**
     * Get venue statistics for dashboard
     */
    /**
     * Get  venue stats
     */
    public static function getVenueStats(int $userId): array
    {
        $venues = self::where('user_id', $userId)->get();

        $byCity = $venues->groupBy('city')->map(function ($group) {
            return $group->count();
        })->sortDesc();

        $byVenueType = $venues->flatMap(function ($venue) {
            return $venue->venue_types ?? [];
        })->countBy()->sortDesc();

        return [
            'total_venues'        => $venues->count(),
            'cities_count'        => $venues->groupBy('city')->count(),
            'countries_count'     => $venues->groupBy('country')->count(),
            'high_priority_count' => $venues->where('priority', '>=', 4)->count(),
            'email_alerts_count'  => $venues->where('email_alerts', TRUE)->count(),
            'most_popular_city'   => $byCity->keys()->first(),
            'by_city'             => $byCity->take(10)->toArray(),
            'by_venue_type'       => $byVenueType->take(5)->toArray(),
            'outdoor_venues'      => $venues->filter(function ($venue) {
                return $venue->isOutdoor();
            })->count(),
        ];
    }

    /**
     * Suggest similar venues
     */
    /**
     * Get  similar venues
     */
    public function getSimilarVenues(int $limit = 5): array
    {
        return self::where('id', '!=', $this->id)
            ->where(function ($query): void {
                $query->where('city', $this->city)
                    ->orWhere('state_province', $this->state_province)
                    ->orWhere(function ($q): void {
                        foreach ($this->venue_types ?? [] as $type) {
                            $q->orWhereJsonContains('venue_types', $type);
                        }
                    });
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
