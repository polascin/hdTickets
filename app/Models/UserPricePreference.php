<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

use function in_array;

class UserPricePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preference_name',
        'sport_type',
        'event_category',
        'min_price',
        'max_price',
        'preferred_quantity',
        'seat_preferences',
        'section_preferences',
        'price_drop_threshold',
        'price_increase_threshold',
        'auto_purchase_enabled',
        'auto_purchase_max_price',
        'email_alerts',
        'push_alerts',
        'sms_alerts',
        'alert_frequency',
        'is_active',
    ];

    protected $casts = [
        'min_price'                => 'decimal:2',
        'max_price'                => 'decimal:2',
        'preferred_quantity'       => 'integer',
        'seat_preferences'         => 'array',
        'section_preferences'      => 'array',
        'price_drop_threshold'     => 'decimal:2',
        'price_increase_threshold' => 'decimal:2',
        'auto_purchase_enabled'    => 'boolean',
        'auto_purchase_max_price'  => 'decimal:2',
        'email_alerts'             => 'boolean',
        'push_alerts'              => 'boolean',
        'sms_alerts'               => 'boolean',
        'is_active'                => 'boolean',
    ];

    /**
     * Get the user that owns this price preference
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by sport type
     *
     * @param mixed $query
     */
    public function scopeBySport($query, string $sportType)
    {
        return $query->where('sport_type', $sportType);
    }

    /**
     * Scope to filter by event category
     *
     * @param mixed $query
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Scope for active preferences
     *
     * @param mixed $query
     */
    /**
     * ScopeActive
     *
     * @param mixed $query
     */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', TRUE);
    }

    /**
     * Scope for preferences with auto-purchase enabled
     *
     * @param mixed $query
     */
    public function scopeWithAutoPurchase($query)
    {
        return $query->where('auto_purchase_enabled', TRUE);
    }

    /**
     * Scope for preferences within a price range
     *
     * @param mixed $query
     */
    public function scopeWithinPriceRange($query, float $price)
    {
        return $query->where(function ($q) use ($price): void {
            $q->where(function ($subQ) use ($price): void {
                $subQ->whereNull('min_price')
                    ->orWhere('min_price', '<=', $price);
            })
                ->where('max_price', '>=', $price);
        });
    }

    /**
     * Scope for preferences with email alerts
     *
     * @param mixed $query
     */
    public function scopeWithEmailAlerts($query)
    {
        return $query->where('email_alerts', TRUE);
    }

    /**
     * Get all available event categories
     */
    /**
     * Get  event categories
     */
    public static function getEventCategories(): array
    {
        return [
            'regular_season' => 'Regular Season',
            'preseason'      => 'Preseason',
            'playoffs'       => 'Playoffs',
            'championship'   => 'Championship',
            'all_star'       => 'All-Star Game',
            'exhibition'     => 'Exhibition',
            'tournament'     => 'Tournament',
            'special_event'  => 'Special Event',
        ];
    }

    /**
     * Get all available seat preferences
     */
    /**
     * Get  seat preferences
     */
    public static function getSeatPreferences(): array
    {
        return [
            'lower_level'           => 'Lower Level',
            'club_level'            => 'Club Level',
            'upper_level'           => 'Upper Level',
            'suite'                 => 'Suite/Box',
            'field_level'           => 'Field Level',
            'behind_bench'          => 'Behind Bench',
            'behind_plate'          => 'Behind Home Plate',
            'sideline'              => 'Sideline',
            'corner'                => 'Corner',
            'endzone'               => 'End Zone',
            'center_court'          => 'Center Court',
            'baseline'              => 'Baseline',
            'aisle'                 => 'Aisle Seats',
            'wheelchair_accessible' => 'Wheelchair Accessible',
        ];
    }

    /**
     * Get all available alert frequencies
     */
    /**
     * Get  alert frequencies
     */
    public static function getAlertFrequencies(): array
    {
        return [
            'immediate' => 'Immediate',
            'hourly'    => 'Hourly Summary',
            'daily'     => 'Daily Digest',
        ];
    }

    /**
     * Check if a ticket price matches this preference
     */
    /**
     * MatchesPrice
     */
    public function matchesPrice(float $ticketPrice): bool
    {
        if ($this->min_price && $ticketPrice < $this->min_price) {
            return FALSE;
        }

        return ! ($ticketPrice > $this->max_price);
    }

    /**
     * Check if price drop threshold is met
     */
    /**
     * Check if  price drop significant
     */
    public function isPriceDropSignificant(float $oldPrice, float $newPrice): bool
    {
        if ($oldPrice <= 0) {
            return FALSE;
        }

        $percentageChange = (($oldPrice - $newPrice) / $oldPrice) * 100;

        return $percentageChange >= $this->price_drop_threshold;
    }

    /**
     * Check if price increase threshold is met
     */
    /**
     * Check if  price increase significant
     */
    public function isPriceIncreaseSignificant(float $oldPrice, float $newPrice): bool
    {
        if ($oldPrice <= 0) {
            return FALSE;
        }

        $percentageChange = (($newPrice - $oldPrice) / $oldPrice) * 100;

        return $percentageChange >= $this->price_increase_threshold;
    }

    /**
     * Check if seat preferences match ticket
     */
    /**
     * MatchesSeatPreferences
     */
    public function matchesSeatPreferences(array $ticketSeatInfo): bool
    {
        if (empty($this->seat_preferences)) {
            return TRUE; // No specific seat preferences
        }

        foreach ($this->seat_preferences as $preference) {
            if (in_array($preference, $ticketSeatInfo, TRUE)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if section preferences match ticket
     */
    /**
     * MatchesSectionPreferences
     */
    public function matchesSectionPreferences(string $ticketSection): bool
    {
        if (empty($this->section_preferences)) {
            return TRUE; // No specific section preferences
        }

        return in_array($ticketSection, $this->section_preferences, TRUE);
    }

    /**
     * Check if auto-purchase should trigger
     */
    /**
     * ShouldAutoPurchase
     */
    public function shouldAutoPurchase(float $ticketPrice): bool
    {
        if (! $this->auto_purchase_enabled || ! $this->auto_purchase_max_price) {
            return FALSE;
        }

        return $ticketPrice <= $this->auto_purchase_max_price;
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
            'email'     => $this->email_alerts,
            'push'      => $this->push_alerts,
            'sms'       => $this->sms_alerts,
            'frequency' => $this->alert_frequency,
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
            'email_alerts'    => $settings['email'] ?? $this->email_alerts,
            'push_alerts'     => $settings['push'] ?? $this->push_alerts,
            'sms_alerts'      => $settings['sms'] ?? $this->sms_alerts,
            'alert_frequency' => $settings['frequency'] ?? $this->alert_frequency,
        ]);
    }

    /**
     * Get formatted price range
     */
    /**
     * Get  formatted price range
     */
    public function getFormattedPriceRange(): string
    {
        $min = $this->min_price ? '$' . number_format($this->min_price, 2) : 'Any';
        $max = '$' . number_format($this->max_price, 2);

        return $this->min_price ? "{$min} - {$max}" : "Up to {$max}";
    }

    /**
     * Get average target price
     */
    /**
     * Get  average target price
     */
    public function getAverageTargetPrice(): float
    {
        if ($this->min_price) {
            return ($this->min_price + $this->max_price) / 2;
        }

        return $this->max_price * 0.7; // Assume target is 70% of max if no min
    }

    /**
     * Clone preference for different sport/category
     */
    /**
     * CloneFor
     */
    public function cloneFor(?string $sportType = NULL, ?string $eventCategory = NULL): self
    {
        $clone = $this->replicate();
        $clone->preference_name = $this->preference_name . ' (Copy)';

        if ($sportType) {
            $clone->sport_type = $sportType;
        }

        if ($eventCategory) {
            $clone->event_category = $eventCategory;
        }

        $clone->save();

        return $clone;
    }

    /**
     * Get price preference statistics for user
     */
    /**
     * Get  price stats
     */
    public static function getPriceStats(int $userId): array
    {
        $preferences = self::where('user_id', $userId)->get();

        $activePref = $preferences->where('is_active', TRUE);
        $avgMaxPrice = $activePref->avg('max_price') ?? 0;
        $avgMinPrice = $activePref->where('min_price', '>', 0)->avg('min_price') ?? 0;

        return [
            'total_preferences'     => $preferences->count(),
            'active_preferences'    => $activePref->count(),
            'auto_purchase_enabled' => $preferences->where('auto_purchase_enabled', TRUE)->count(),
            'average_max_price'     => round($avgMaxPrice, 2),
            'average_min_price'     => round($avgMinPrice, 2),
            'total_budget'          => round($activePref->sum('max_price'), 2),
            'by_sport'              => $preferences->groupBy('sport_type')->map(function ($group) {
                return [
                    'count'         => $group->count(),
                    'avg_max_price' => round($group->avg('max_price'), 2),
                ];
            })->toArray(),
            'by_category' => $preferences->groupBy('event_category')->map(function ($group) {
                return $group->count();
            })->toArray(),
            'alert_methods' => [
                'email' => $preferences->where('email_alerts', TRUE)->count(),
                'push'  => $preferences->where('push_alerts', TRUE)->count(),
                'sms'   => $preferences->where('sms_alerts', TRUE)->count(),
            ],
        ];
    }

    /**
     * Get similar preferences for suggestions
     */
    /**
     * Get  similar preferences
     */
    public function getSimilarPreferences(int $limit = 5): array
    {
        return self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query): void {
                $query->where('sport_type', $this->sport_type)
                    ->orWhere('event_category', $this->event_category)
                    ->orWhereBetween('max_price', [
                        $this->max_price * 0.8,
                        $this->max_price * 1.2,
                    ]);
            })
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Validate preference data
     */
    /**
     * ValidatePreferenceData
     */
    public static function validatePreferenceData(array $data): array
    {
        $errors = [];

        if (isset($data['min_price'], $data['max_price']) && $data['min_price'] > $data['max_price']) {
            $errors[] = 'Minimum price cannot be greater than maximum price';
        }

        if (isset($data['price_drop_threshold']) && ($data['price_drop_threshold'] < 0 || $data['price_drop_threshold'] > 100)) {
            $errors[] = 'Price drop threshold must be between 0 and 100';
        }

        if (isset($data['preferred_quantity']) && $data['preferred_quantity'] < 1) {
            $errors[] = 'Preferred quantity must be at least 1';
        }

        if (isset($data['auto_purchase_max_price'], $data['max_price'])
            && $data['auto_purchase_max_price'] > $data['max_price']) {
            $errors[] = 'Auto-purchase maximum cannot exceed the general maximum price';
        }

        return $errors;
    }
}
