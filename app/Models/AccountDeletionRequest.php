<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AccountDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'confirmation_token',
        'status',
        'user_data_snapshot',
        'initiated_at',
        'email_confirmed_at',
        'grace_period_expires_at',
        'deleted_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'user_data_snapshot' => 'array',
        'metadata' => 'array',
        'initiated_at' => 'datetime',
        'email_confirmed_at' => 'datetime',
        'grace_period_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user that owns the deletion request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the request is pending email confirmation
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the request has expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the grace period is active
     */
    public function isInGracePeriod(): bool
    {
        return $this->isConfirmed() 
               && $this->grace_period_expires_at 
               && $this->grace_period_expires_at->isFuture();
    }

    /**
     * Check if the grace period has expired
     */
    public function isGracePeriodExpired(): bool
    {
        return $this->isConfirmed() 
               && $this->grace_period_expires_at 
               && $this->grace_period_expires_at->isPast();
    }

    /**
     * Get the remaining time in the grace period
     */
    public function getRemainingGraceTime(): ?Carbon
    {
        if (!$this->isInGracePeriod()) {
            return null;
        }

        return $this->grace_period_expires_at;
    }

    /**
     * Get human readable time remaining
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (!$this->isInGracePeriod()) {
            return null;
        }

        return $this->grace_period_expires_at->diffForHumans();
    }

    /**
     * Cancel the deletion request
     */
    public function cancel(?string $reason = null): bool
    {
        if (!$this->isPending() && !$this->isConfirmed()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'cancellation_reason' => $reason,
                'cancelled_by_ip' => request()->ip(),
                'cancelled_by_user_agent' => request()->userAgent(),
            ]),
        ]);

        return true;
    }

    /**
     * Confirm the deletion request and start grace period
     */
    public function confirm(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $gracePeriodEnd = now()->addHours(24); // 24-hour grace period

        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'email_confirmed_at' => now(),
            'grace_period_expires_at' => $gracePeriodEnd,
            'metadata' => array_merge($this->metadata ?? [], [
                'confirmed_by_ip' => request()->ip(),
                'confirmed_by_user_agent' => request()->userAgent(),
            ]),
        ]);

        return true;
    }

    /**
     * Mark the deletion as completed
     */
    public function markCompleted(): bool
    {
        if (!$this->isConfirmed()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'deleted_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark the request as expired
     */
    public function markExpired(): bool
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);

        return true;
    }

    /**
     * Scope to get active requests (pending or confirmed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope to get requests in grace period
     */
    public function scopeInGracePeriod($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
                    ->where('grace_period_expires_at', '>', now());
    }

    /**
     * Scope to get expired grace period requests
     */
    public function scopeGracePeriodExpired($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
                    ->where('grace_period_expires_at', '<=', now());
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_EXPIRED,
            self::STATUS_COMPLETED,
        ];
    }
}
