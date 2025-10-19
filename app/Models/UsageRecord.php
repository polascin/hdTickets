<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Usage Record Model
 *
 * Tracks resource usage for billing and analytics:
 * - API request usage tracking
 * - Feature usage monitoring
 * - Billing period calculations
 * - Overage and limit enforcement
 * - Usage analytics and reporting
 */
class UsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'resource_type',
        'quantity',
        'unit_price',
        'total_amount',
        'billing_period_start',
        'billing_period_end',
        'recorded_at',
        'metadata',
    ];

    protected $casts = [
        'quantity'             => 'integer',
        'unit_price'           => 'decimal:4',
        'total_amount'         => 'decimal:2',
        'billing_period_start' => 'datetime',
        'billing_period_end'   => 'datetime',
        'recorded_at'          => 'datetime',
        'metadata'             => 'array',
    ];

    protected $dates = [
        'billing_period_start',
        'billing_period_end',
        'recorded_at',
    ];

    // Resource types
    public const RESOURCE_API_REQUESTS = 'api_requests';

    public const RESOURCE_EVENTS_MONITORED = 'events_monitored';

    public const RESOURCE_PRICE_ALERTS = 'price_alerts';

    public const RESOURCE_WEBHOOK_DELIVERIES = 'webhook_deliveries';

    public const RESOURCE_AUTO_PURCHASES = 'auto_purchases';

    public const RESOURCE_DATA_STORAGE = 'data_storage';

    public const RESOURCE_SUPPORT_TICKETS = 'support_tickets';

    /**
     * Get the user that owns the usage record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the usage record
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmount(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPrice(): string
    {
        return '$' . number_format($this->unit_price, 4);
    }

    /**
     * Get resource type display name
     */
    public function getResourceTypeDisplayName(): string
    {
        return match ($this->resource_type) {
            self::RESOURCE_API_REQUESTS       => 'API Requests',
            self::RESOURCE_EVENTS_MONITORED   => 'Events Monitored',
            self::RESOURCE_PRICE_ALERTS       => 'Price Alerts',
            self::RESOURCE_WEBHOOK_DELIVERIES => 'Webhook Deliveries',
            self::RESOURCE_AUTO_PURCHASES     => 'Auto Purchases',
            self::RESOURCE_DATA_STORAGE       => 'Data Storage (GB)',
            self::RESOURCE_SUPPORT_TICKETS    => 'Support Tickets',
            default                           => ucwords(str_replace('_', ' ', $this->resource_type))
        };
    }

    /**
     * Get usage unit for display
     */
    public function getUsageUnit(): string
    {
        return match ($this->resource_type) {
            self::RESOURCE_API_REQUESTS       => 'requests',
            self::RESOURCE_EVENTS_MONITORED   => 'events',
            self::RESOURCE_PRICE_ALERTS       => 'alerts',
            self::RESOURCE_WEBHOOK_DELIVERIES => 'deliveries',
            self::RESOURCE_AUTO_PURCHASES     => 'purchases',
            self::RESOURCE_DATA_STORAGE       => 'GB',
            self::RESOURCE_SUPPORT_TICKETS    => 'tickets',
            default                           => 'units'
        };
    }

    /**
     * Check if usage is within current billing period
     */
    public function isCurrentBillingPeriod(): bool
    {
        $now = now();

        return $this->billing_period_start <= $now && $this->billing_period_end >= $now;
    }

    /**
     * Calculate cost per unit
     */
    public function getCostPerUnit(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }

        return round($this->total_amount / $this->quantity, 4);
    }

    /**
     * Get usage efficiency score (lower is better)
     */
    public function getEfficiencyScore(): float
    {
        if ($this->unit_price <= 0) {
            return 0;
        }

        $actualCost = $this->getCostPerUnit();

        return round($actualCost / $this->unit_price, 2);
    }

    /**
     * Create usage record for current billing period
     */
    public static function recordUsage(
        User $user,
        string $resourceType,
        int $quantity,
        float $unitPrice = 0.0,
        array $metadata = []
    ): self {
        $subscription = $user->activeSubscription();
        $billingPeriod = self::getCurrentBillingPeriod($subscription);

        return self::create([
            'user_id'              => $user->id,
            'subscription_id'      => $subscription?->id,
            'resource_type'        => $resourceType,
            'quantity'             => $quantity,
            'unit_price'           => $unitPrice,
            'total_amount'         => $quantity * $unitPrice,
            'billing_period_start' => $billingPeriod['start'],
            'billing_period_end'   => $billingPeriod['end'],
            'recorded_at'          => now(),
            'metadata'             => $metadata,
        ]);
    }

    /**
     * Get total usage for user in current billing period
     */
    public static function getCurrentPeriodUsage(User $user, string $resourceType): int
    {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return 0;
        }

        $billingPeriod = self::getCurrentBillingPeriod($subscription);

        return self::where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->whereBetween('recorded_at', [$billingPeriod['start'], $billingPeriod['end']])
            ->sum('quantity');
    }

    /**
     * Get usage summary for user in current billing period
     */
    public static function getCurrentPeriodSummary(User $user): array
    {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return [];
        }

        $billingPeriod = self::getCurrentBillingPeriod($subscription);

        $records = self::where('user_id', $user->id)
            ->whereBetween('recorded_at', [$billingPeriod['start'], $billingPeriod['end']])
            ->selectRaw('resource_type, SUM(quantity) as total_quantity, SUM(total_amount) as total_cost')
            ->groupBy('resource_type')
            ->get();

        $summary = [];
        foreach ($records as $record) {
            $summary[$record->resource_type] = [
                'quantity'     => $record->total_quantity,
                'cost'         => $record->total_cost,
                'display_name' => (new self(['resource_type' => $record->resource_type]))->getResourceTypeDisplayName(),
                'unit'         => (new self(['resource_type' => $record->resource_type]))->getUsageUnit(),
            ];
        }

        return $summary;
    }

    /**
     * Check if user has exceeded limit for resource
     */
    public static function hasExceededLimit(User $user, string $resourceType): bool
    {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return TRUE; // No subscription = free tier limits
        }

        $limit = $subscription->getFeatureLimit($resourceType);
        if ($limit <= 0) {
            return FALSE; // Unlimited
        }

        $currentUsage = self::getCurrentPeriodUsage($user, $resourceType);

        return $currentUsage >= $limit;
    }

    /**
     * Get remaining quota for resource
     */
    public static function getRemainingQuota(User $user, string $resourceType): int
    {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            return 0;
        }

        $limit = $subscription->getFeatureLimit($resourceType);
        if ($limit <= 0) {
            return PHP_INT_MAX; // Unlimited
        }

        $currentUsage = self::getCurrentPeriodUsage($user, $resourceType);

        return max(0, $limit - $currentUsage);
    }

    /**
     * Scope to get records for specific resource type
     */
    public function scopeForResource($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope to get records for current billing period
     */
    public function scopeCurrentBillingPeriod($query, ?Subscription $subscription = NULL)
    {
        if (!$subscription) {
            return $query->whereNull('id'); // Return empty result
        }

        $billingPeriod = self::getCurrentBillingPeriod($subscription);

        return $query->whereBetween('billing_period_start', [$billingPeriod['start'], $billingPeriod['end']]);
    }

    /**
     * Scope to get records for specific date range
     */
    public function scopeDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get records with overage costs
     */
    public function scopeWithOverage($query)
    {
        return $query->where('total_amount', '>', 0);
    }

    // Private helper methods

    private static function getCurrentBillingPeriod(?Subscription $subscription): array
    {
        if (!$subscription) {
            // For free tier, use monthly periods starting from account creation
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
        } else {
            $start = $subscription->current_period_start;
            $end = $subscription->current_period_end;
        }

        return [
            'start' => $start,
            'end'   => $end,
        ];
    }
}
