<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Subscription Model
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $payment_plan_id
 * @property string      $status
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $trial_ends_at
 * @property string|null $stripe_subscription_id
 * @property string|null $stripe_customer_id
 * @property float|null  $amount_paid
 * @property string|null $payment_method
 * @property array|null  $metadata
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property User        $user
 * @property PaymentPlan $paymentPlan
 * @property int|null    $days_remaining
 * @property int|null    $trial_days_remaining
 * @property string      $status_color
 * @property string      $formatted_status
 */
class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'stripe_subscription_id',
        'stripe_customer_id',
        'amount_paid',
        'payment_method',
        'metadata',
        'ticket_limit',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            if (empty($subscription->payment_plan_id)) {
                $basic = PaymentPlan::firstOrCreate(
                    ['slug' => 'basic'],
                    [
                        'name'                    => 'Basic',
                        'description'             => 'Default basic plan',
                        'price'                   => 19.99,
                        'billing_cycle'           => 'monthly',
                        'features'                => ['Email & push notifications'],
                        'max_tickets_per_month'   => 25,
                        'max_concurrent_purchases'=> 2,
                        'max_platforms'           => 2,
                        'priority_support'        => false,
                        'advanced_analytics'      => false,
                        'automated_purchasing'    => false,
                        'is_active'               => true,
                        'sort_order'              => 2,
                    ]
                );
                $subscription->payment_plan_id = $basic->id;
            }
        });
    }

    /**
     * Get the user that owns the subscription
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment plan for this subscription
     */
    /**
     * PaymentPlan
     */
    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    /**
     * Scope for active subscriptions
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
        return $query->where('status', 'active')
            ->where(function ($q): void {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope for expired subscriptions
     *
     * @param mixed $query
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q): void {
                $q->whereNotNull('ends_at')
                    ->where('ends_at', '<=', now());
            });
    }

    /**
     * Scope for trial subscriptions
     *
     * @param mixed $query
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial')
            ->where(function ($q): void {
                $q->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '>', now());
            });
    }

    /**
     * Check if subscription is active
     */
    /**
     * Check if  active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return FALSE;
        }

        return ! ($this->ends_at && $this->ends_at->isPast());
    }

    /**
     * Check if subscription is on trial
     */
    /**
     * Check if  on trial
     */
    public function isOnTrial(): bool
    {
        if ($this->status !== 'trial' && $this->status !== 'active') {
            return FALSE;
        }

        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    /**
     * Check if  trial expired
     */
    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription has expired
     */
    /**
     * Check if  expired
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Cancel the subscription
     */
    /**
     * Check if can cel
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';

        return $this->save();
    }

    /**
     * Expire the subscription
     */
    /**
     * Expire
     */
    public function expire(): bool
    {
        $this->status = 'expired';
        $this->ends_at = now();

        return $this->save();
    }

    /**
     * Activate the subscription
     */
    /**
     * Activate
     */
    public function activate(): bool
    {
        $this->status = 'active';

        // Set start date if not already set
        if (! $this->starts_at) {
            $this->starts_at = now();
        }

        // Calculate end date based on billing cycle
        if ($this->paymentPlan && ! $this->ends_at) {
            switch ($this->paymentPlan->billing_cycle) {
                case 'monthly':
                    $this->ends_at = $this->starts_at->copy()->addMonth();

                    break;
                case 'yearly':
                    $this->ends_at = $this->starts_at->copy()->addYear();

                    break;
                case 'lifetime':
                    $this->ends_at = NULL; // No expiration

                    break;
            }
        }

        return $this->save();
    }

    /**
     * Start trial period
     */
    /**
     * StartTrial
     */
    public function startTrial(int $days = 14): bool
    {
        $this->status = 'trial';
        $this->starts_at = now();
        $this->trial_ends_at = now()->addDays($days);

        return $this->save();
    }

    /**
     * Get  days remaining attribute
     */
    protected function daysRemaining(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->ends_at) {
                return; // Unlimited
            }
            $days = now()->diffInDays($this->ends_at, FALSE);

            return $days > 0 ? $days : 0;
        });
    }

    /**
     * Get  trial days remaining attribute
     */
    protected function trialDaysRemaining(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->trial_ends_at) {
                return;
            }
            $days = now()->diffInDays($this->trial_ends_at, FALSE);

            return $days > 0 ? $days : 0;
        });
    }

    /**
     * Get  status color attribute
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(get: fn (): string => match ($this->status) {
            'active'    => 'green',
            'trial'     => 'blue',
            'cancelled' => 'red',
            'expired'   => 'red',
            'inactive'  => 'gray',
            default     => 'gray',
        });
    }

    /**
     * Get  formatted status attribute
     */
    protected function formattedStatus(): Attribute
    {
        return Attribute::make(get: function (): string {
            if ($this->isOnTrial()) {
                $days = $this->trial_days_remaining;

                return "Trial ({$days} days left)";
            }

            return ucfirst($this->status);
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at'       => 'datetime',
            'ends_at'         => 'datetime',
            'trial_ends_at'   => 'datetime',
            'amount_paid'     => 'decimal:2',
            'user_id'         => 'integer',
            'payment_plan_id' => 'integer',
            'metadata'        => 'array',
            'ticket_limit'    => 'integer',
        ];
    }
}
