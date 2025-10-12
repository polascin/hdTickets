<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Subscription Model
 * 
 * Manages user subscription data including:
 * - Plan details and pricing
 * - Stripe integration references
 * - Billing cycles and trial periods
 * - Subscription status tracking
 * - Payment and usage history
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_name',
        'stripe_subscription_id',
        'stripe_subscription_item_id',
        'status',
        'price',
        'currency',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
        'cancel_at',
        'cancellation_reason',
        'last_payment_failed_at',
        'metadata'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_at' => 'datetime',
        'last_payment_failed_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
        'cancel_at',
        'last_payment_failed_at'
    ];

    // Subscription statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';
    public const STATUS_CANCEL_AT_PERIOD_END = 'cancel_at_period_end';

    // Plan names
    public const PLAN_STARTER = 'starter';
    public const PLAN_PRO = 'pro';
    public const PLAN_ENTERPRISE = 'enterprise';

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment history for this subscription
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the usage records for this subscription
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE,
            self::STATUS_TRIALING,
            self::STATUS_CANCEL_AT_PERIOD_END
        ]);
    }

    /**
     * Check if subscription is in trial period
     */
    public function onTrial(): bool
    {
        return $this->status === self::STATUS_TRIALING || 
               ($this->trial_ends_at && $this->trial_ends_at->isFuture());
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if subscription will cancel at period end
     */
    public function willCancelAtPeriodEnd(): bool
    {
        return $this->status === self::STATUS_CANCEL_AT_PERIOD_END;
    }

    /**
     * Check if subscription is past due
     */
    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    /**
     * Get days remaining in current billing period
     */
    public function daysRemainingInPeriod(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return max(0, now()->diffInDays($this->current_period_end));
    }

    /**
     * Get trial days remaining
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get subscription plan details
     */
    public function getPlanDetails(): array
    {
        $plans = [
            self::PLAN_STARTER => [
                'name' => 'Starter',
                'price' => 19.00,
                'features' => [
                    'events_limit' => 5,
                    'monitors_limit' => 10,
                    'api_requests_per_hour' => 100,
                    'price_alerts_limit' => 20,
                    'webhook_endpoints' => 1,
                    'auto_purchase_configs' => 1,
                    'data_retention_days' => 30,
                    'support_level' => 'email'
                ]
            ],
            self::PLAN_PRO => [
                'name' => 'Pro',
                'price' => 49.00,
                'features' => [
                    'events_limit' => 25,
                    'monitors_limit' => 50,
                    'api_requests_per_hour' => 1000,
                    'price_alerts_limit' => 100,
                    'webhook_endpoints' => 5,
                    'auto_purchase_configs' => 5,
                    'data_retention_days' => 90,
                    'support_level' => 'priority_email'
                ]
            ],
            self::PLAN_ENTERPRISE => [
                'name' => 'Enterprise',
                'price' => 199.00,
                'features' => [
                    'events_limit' => 100,
                    'monitors_limit' => 250,
                    'api_requests_per_hour' => 10000,
                    'price_alerts_limit' => 500,
                    'webhook_endpoints' => 25,
                    'auto_purchase_configs' => 25,
                    'data_retention_days' => 365,
                    'support_level' => 'phone_and_email'
                ]
            ]
        ];

        return $plans[$this->plan_name] ?? [];
    }

    /**
     * Get next billing date
     */
    public function getNextBillingDate(): ?Carbon
    {
        if ($this->willCancelAtPeriodEnd()) {
            return null;
        }

        return $this->current_period_end;
    }

    /**
     * Calculate prorated amount for plan change
     */
    public function calculateProratedAmount(string $newPlanName): float
    {
        $currentPlan = $this->getPlanDetails();
        $newPlan = $this->getNewPlanDetails($newPlanName);
        
        if (empty($currentPlan) || empty($newPlan)) {
            return 0.0;
        }

        $daysRemaining = $this->daysRemainingInPeriod();
        $totalDays = now()->diffInDays($this->current_period_start) + $daysRemaining;
        
        if ($totalDays <= 0) {
            return 0.0;
        }

        $currentDailyRate = $currentPlan['price'] / $totalDays;
        $newDailyRate = $newPlan['price'] / $totalDays;
        
        $refund = $currentDailyRate * $daysRemaining;
        $charge = $newDailyRate * $daysRemaining;
        
        return round($charge - $refund, 2);
    }

    /**
     * Get subscription status badge info
     */
    public function getStatusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => [
                'text' => 'Active',
                'color' => 'green',
                'icon' => 'check-circle'
            ],
            self::STATUS_TRIALING => [
                'text' => 'Trial',
                'color' => 'blue',
                'icon' => 'clock'
            ],
            self::STATUS_PAST_DUE => [
                'text' => 'Past Due',
                'color' => 'yellow',
                'icon' => 'exclamation-triangle'
            ],
            self::STATUS_CANCELLED => [
                'text' => 'Cancelled',
                'color' => 'red',
                'icon' => 'times-circle'
            ],
            self::STATUS_CANCEL_AT_PERIOD_END => [
                'text' => 'Cancelling',
                'color' => 'orange',
                'icon' => 'calendar-times'
            ],
            default => [
                'text' => 'Unknown',
                'color' => 'gray',
                'icon' => 'question-circle'
            ]
        };
    }

    /**
     * Get billing summary
     */
    public function getBillingSummary(): array
    {
        $plan = $this->getPlanDetails();
        
        return [
            'plan_name' => $plan['name'] ?? 'Unknown',
            'price' => $this->price,
            'currency' => strtoupper($this->currency),
            'billing_cycle' => 'monthly',
            'next_billing_date' => $this->getNextBillingDate()?->format('Y-m-d'),
            'status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at?->format('Y-m-d'),
            'days_remaining' => $this->daysRemainingInPeriod(),
            'features' => $plan['features'] ?? []
        ];
    }

    /**
     * Check if user can access specific feature
     */
    public function hasFeatureAccess(string $feature): bool
    {
        $plan = $this->getPlanDetails();
        $features = $plan['features'] ?? [];
        
        // Check specific feature limits
        if (str_ends_with($feature, '_limit')) {
            return isset($features[$feature]) && $features[$feature] > 0;
        }
        
        // Check feature availability based on plan
        $planFeatures = [
            self::PLAN_STARTER => [
                'real_time_monitoring',
                'basic_price_alerts',
                'email_notifications',
                'basic_analytics'
            ],
            self::PLAN_PRO => [
                'real_time_monitoring',
                'advanced_price_alerts',
                'smart_notifications',
                'comprehensive_analytics',
                'automated_purchasing',
                'multi_event_management',
                'api_access',
                'bulk_operations',
                'price_predictions'
            ],
            self::PLAN_ENTERPRISE => [
                'real_time_monitoring',
                'advanced_price_alerts',
                'intelligent_notifications',
                'comprehensive_analytics',
                'automated_purchasing',
                'multi_event_management',
                'full_api_access',
                'bulk_operations',
                'price_predictions',
                'custom_integrations',
                'priority_support',
                'dedicated_account_manager',
                'custom_reporting',
                'white_label_options'
            ]
        ];
        
        return in_array($feature, $planFeatures[$this->plan_name] ?? []);
    }

    /**
     * Get feature limit for specific resource
     */
    public function getFeatureLimit(string $feature): int
    {
        $plan = $this->getPlanDetails();
        $features = $plan['features'] ?? [];
        
        return $features[$feature . '_limit'] ?? 0;
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACTIVE,
            self::STATUS_TRIALING,
            self::STATUS_CANCEL_AT_PERIOD_END
        ]);
    }

    /**
     * Scope to get cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope to get trialing subscriptions
     */
    public function scopeTrialing($query)
    {
        return $query->where('status', self::STATUS_TRIALING)
                     ->orWhere(function ($query) {
                         $query->whereNotNull('trial_ends_at')
                               ->where('trial_ends_at', '>', now());
                     });
    }

    /**
     * Scope to get subscriptions ending soon
     */
    public function scopeEndingSoon($query, int $days = 7)
    {
        return $query->where('current_period_end', '<=', now()->addDays($days))
                     ->where('current_period_end', '>', now());
    }

    // Private helper methods

    private function getNewPlanDetails(string $planName): array
    {
        $plans = [
            self::PLAN_STARTER => ['name' => 'Starter', 'price' => 19.00],
            self::PLAN_PRO => ['name' => 'Pro', 'price' => 49.00],
            self::PLAN_ENTERPRISE => ['name' => 'Enterprise', 'price' => 199.00]
        ];

        return $plans[$planName] ?? [];
    }
}