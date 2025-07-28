<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'metadata'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'metadata' => 'array'
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment plan for this subscription
     */
    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                          ->orWhere('ends_at', '>', now());
                    });
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->whereNotNull('ends_at')
                          ->where('ends_at', '<=', now());
                    });
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial')
                    ->where(function ($q) {
                        $q->whereNull('trial_ends_at')
                          ->orWhere('trial_ends_at', '>', now());
                    });
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if subscription is on trial
     */
    public function isOnTrial(): bool
    {
        if ($this->status !== 'trial' && $this->status !== 'active') {
            return false;
        }

        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription has expired
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Get days remaining in subscription
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->ends_at) {
            return null; // Unlimited
        }

        $days = now()->diffInDays($this->ends_at, false);
        return $days > 0 ? $days : 0;
    }

    /**
     * Get trial days remaining
     */
    public function getTrialDaysRemainingAttribute(): ?int
    {
        if (!$this->trial_ends_at) {
            return null;
        }

        $days = now()->diffInDays($this->trial_ends_at, false);
        return $days > 0 ? $days : 0;
    }

    /**
     * Cancel the subscription
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Expire the subscription
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
    public function activate(): bool
    {
        $this->status = 'active';
        
        // Set start date if not already set
        if (!$this->starts_at) {
            $this->starts_at = now();
        }

        // Calculate end date based on billing cycle
        if ($this->paymentPlan && !$this->ends_at) {
            switch ($this->paymentPlan->billing_cycle) {
                case 'monthly':
                    $this->ends_at = $this->starts_at->copy()->addMonth();
                    break;
                case 'yearly':
                    $this->ends_at = $this->starts_at->copy()->addYear();
                    break;
                case 'lifetime':
                    $this->ends_at = null; // No expiration
                    break;
            }
        }

        return $this->save();
    }

    /**
     * Start trial period
     */
    public function startTrial(int $days = 14): bool
    {
        $this->status = 'trial';
        $this->starts_at = now();
        $this->trial_ends_at = now()->addDays($days);
        
        return $this->save();
    }

    /**
     * Get subscription status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'trial' => 'blue',
            'cancelled' => 'red',
            'expired' => 'red',
            'inactive' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get formatted status for display
     */
    public function getFormattedStatusAttribute(): string
    {
        if ($this->isOnTrial()) {
            $days = $this->trial_days_remaining;
            return "Trial ({$days} days left)";
        }

        return ucfirst($this->status);
    }
}
