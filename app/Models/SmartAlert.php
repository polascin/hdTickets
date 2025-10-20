<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use function count;
use function in_array;

/**
 * Smart Alert Model
 *
 * Represents intelligent ticket monitoring alerts inspired by TicketScoutie's
 * smart alert system. Supports multi-channel notifications and complex
 * trigger conditions for price drops, availability changes, and more.
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property string              $name
 * @property string|null         $description
 * @property string              $alert_type
 * @property array               $trigger_conditions
 * @property array               $notification_channels
 * @property array               $notification_settings
 * @property bool                $is_active
 * @property string              $priority
 * @property int                 $cooldown_minutes
 * @property int                 $max_triggers_per_day
 * @property int                 $trigger_count
 * @property \Carbon\Carbon|null $last_triggered_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class SmartAlert extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** Alert types */
    public const ALERT_TYPES = [
        'price_drop'       => 'Price Drop',
        'availability'     => 'New Availability',
        'instant_deal'     => 'Instant Deal',
        'price_comparison' => 'Price Comparison',
        'venue_alert'      => 'Venue Alert',
        'league_alert'     => 'League Alert',
        'keyword_alert'    => 'Keyword Alert',
    ];

    /** Notification channels */
    public const NOTIFICATION_CHANNELS = [
        'email'   => 'Email',
        'sms'     => 'SMS',
        'push'    => 'Push Notification',
        'webhook' => 'Webhook',
    ];

    /** Priority levels */
    public const PRIORITIES = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
        'urgent' => 'Urgent',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'alert_type',
        'trigger_conditions',
        'notification_channels',
        'notification_settings',
        'is_active',
        'priority',
        'cooldown_minutes',
        'max_triggers_per_day',
        'trigger_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'trigger_conditions'    => 'array',
        'notification_channels' => 'array',
        'notification_settings' => 'array',
        'is_active'             => 'boolean',
        'cooldown_minutes'      => 'integer',
        'max_triggers_per_day'  => 'integer',
        'trigger_count'         => 'integer',
        'last_triggered_at'     => 'datetime',
    ];

    /**
     * Get the user that owns the alert
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if alert can be triggered (respects cooldown and daily limits)
     */
    public function canTrigger(): bool
    {
        if (! $this->is_active) {
            return FALSE;
        }

        // Check cooldown period
        if ($this->last_triggered_at
            && $this->last_triggered_at->addMinutes($this->cooldown_minutes)->isFuture()) {
            return FALSE;
        }

        // Check daily trigger limit
        if ($this->getTriggersToday() >= $this->max_triggers_per_day) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Get number of triggers today
     */
    public function getTriggersToday(): int
    {
        if (! $this->last_triggered_at) {
            return 0;
        }

        return $this->last_triggered_at->isToday() ? 1 : 0;
    }

    /**
     * Get average triggers per day
     */
    public function getAverageTriggersPerDay(): float
    {
        if (! $this->created_at || $this->trigger_count === 0) {
            return 0.0;
        }

        $daysSinceCreation = $this->created_at->diffInDays(now()) ?: 1;

        return round($this->trigger_count / $daysSinceCreation, 2);
    }

    /**
     * Increment trigger count
     */
    public function incrementTriggerCount(): void
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Check if alert matches given conditions
     */
    public function matchesConditions(array $data): bool
    {
        $conditions = $this->trigger_conditions;

        switch ($this->alert_type) {
            case 'price_drop':
                return $this->matchesPriceDropConditions($data, $conditions);
            case 'availability':
                return $this->matchesAvailabilityConditions($data, $conditions);
            case 'instant_deal':
                return $this->matchesInstantDealConditions($data, $conditions);
            case 'price_comparison':
                return $this->matchesPriceComparisonConditions($data, $conditions);
            case 'venue_alert':
                return $this->matchesVenueConditions($data, $conditions);
            case 'league_alert':
                return $this->matchesLeagueConditions($data, $conditions);
            case 'keyword_alert':
                return $this->matchesKeywordConditions($data, $conditions);
            default:
                return FALSE;
        }
    }

    /**
     * Get alert type label
     */
    public function getAlertTypeLabel(): string
    {
        return self::ALERT_TYPES[$this->alert_type] ?? $this->alert_type;
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Get notification channels labels
     */
    public function getNotificationChannelsLabels(): array
    {
        return array_map(function ($channel) {
            return self::NOTIFICATION_CHANNELS[$channel] ?? $channel;
        }, $this->notification_channels);
    }

    /**
     * Scope: Active alerts
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    /**
     * Scope: By alert type
     *
     * @param mixed $query
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope: By priority
     *
     * @param mixed $query
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check price drop conditions
     */
    private function matchesPriceDropConditions(array $data, array $conditions): bool
    {
        $currentPrice = $data['current_price'] ?? 0;
        $previousPrice = $data['previous_price'] ?? 0;

        if ($previousPrice <= 0 || $currentPrice >= $previousPrice) {
            return FALSE;
        }

        // Check absolute threshold
        if (isset($conditions['price_threshold'])
            && $currentPrice > $conditions['price_threshold']) {
            return FALSE;
        }

        // Check percentage drop
        if (isset($conditions['percentage_drop'])) {
            $dropPercentage = (($previousPrice - $currentPrice) / $previousPrice) * 100;
            if ($dropPercentage < $conditions['percentage_drop']) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check availability conditions
     */
    private function matchesAvailabilityConditions(array $data, array $conditions): bool
    {
        // Check event keywords
        if (! empty($conditions['event_keywords'])) {
            $eventTitle = strtolower($data['event_title'] ?? '');
            foreach ($conditions['event_keywords'] as $keyword) {
                if (str_contains($eventTitle, strtolower($keyword))) {
                    return TRUE;
                }
            }

            return FALSE;
        }

        // Check venue keywords
        if (! empty($conditions['venue_keywords'])) {
            $venue = strtolower($data['venue'] ?? '');
            foreach ($conditions['venue_keywords'] as $keyword) {
                if (str_contains($venue, strtolower($keyword))) {
                    return TRUE;
                }
            }

            return FALSE;
        }

        // Check date range
        if (! empty($conditions['date_range'])) {
            $eventDate = $data['event_date'] ?? NULL;
            if ($eventDate) {
                $eventDate = \Carbon\Carbon::parse($eventDate);
                $start = $conditions['date_range']['start'] ? \Carbon\Carbon::parse($conditions['date_range']['start']) : NULL;
                $end = $conditions['date_range']['end'] ? \Carbon\Carbon::parse($conditions['date_range']['end']) : NULL;

                if ($start && $eventDate->isBefore($start)) {
                    return FALSE;
                }

                if ($end && $eventDate->isAfter($end)) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Check instant deal conditions
     */
    private function matchesInstantDealConditions(array $data, array $conditions): bool
    {
        // Check discount percentage
        if (isset($conditions['discount_percentage'])) {
            $discount = $data['discount_percentage'] ?? 0;
            if ($discount < $conditions['discount_percentage']) {
                return FALSE;
            }
        }

        // Check limited quantity
        if (isset($conditions['limited_quantity']) && $conditions['limited_quantity']) {
            if (! ($data['is_limited_quantity'] ?? FALSE)) {
                return FALSE;
            }
        }

        // Check time sensitivity
        if (isset($conditions['time_sensitive']) && $conditions['time_sensitive']) {
            if (! ($data['is_time_sensitive'] ?? FALSE)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check price comparison conditions
     */
    private function matchesPriceComparisonConditions(array $data, array $conditions): bool
    {
        $platforms = $conditions['platforms'] ?? [];
        $threshold = $conditions['price_difference_threshold'] ?? 0;

        $prices = $data['platform_prices'] ?? [];

        if (count($prices) < 2) {
            return FALSE;
        }

        $minPrice = min($prices);
        $maxPrice = max($prices);

        $priceDifference = $maxPrice - $minPrice;
        $percentageDifference = ($priceDifference / $maxPrice) * 100;

        return $percentageDifference >= $threshold;
    }

    /**
     * Check venue conditions
     */
    private function matchesVenueConditions(array $data, array $conditions): bool
    {
        $venue = strtolower($data['venue'] ?? '');
        $targetVenues = array_map('strtolower', $conditions['venues'] ?? []);

        foreach ($targetVenues as $targetVenue) {
            if (str_contains($venue, $targetVenue)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check league conditions
     */
    private function matchesLeagueConditions(array $data, array $conditions): bool
    {
        $league = strtolower($data['league'] ?? '');
        $targetLeagues = array_map('strtolower', $conditions['leagues'] ?? []);

        return in_array($league, $targetLeagues, TRUE);
    }

    /**
     * Check keyword conditions
     */
    private function matchesKeywordConditions(array $data, array $conditions): bool
    {
        $keywords = $conditions['keywords'] ?? [];
        $searchText = strtolower(implode(' ', [
            $data['event_title'] ?? '',
            $data['venue'] ?? '',
            $data['description'] ?? '',
        ]));

        foreach ($keywords as $keyword) {
            if (str_contains($searchText, strtolower($keyword))) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
