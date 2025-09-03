<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AccountDeletionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_COMPLETED = 'completed';

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
        'user_data_snapshot'      => 'array',
        'metadata'                => 'array',
        'initiated_at'            => 'datetime',
        'email_confirmed_at'      => 'datetime',
        'grace_period_expires_at' => 'datetime',
        'deleted_at'              => 'datetime',
        'cancelled_at'            => 'datetime',
    ];

    /**
     * Get the user that owns the deletion request
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the request is pending email confirmation
     */
    /**
     * Check if  pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is confirmed
     */
    /**
     * Check if  confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the request is cancelled
     */
    /**
     * Check if  cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the request has expired
     */
    /**
     * Check if  expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the request is completed
     */
    /**
     * Check if  completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the grace period is active
     */
    /**
     * Check if  in grace period
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
    /**
     * Check if  grace period expired
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
    /**
     * Get  remaining grace time
     */
    public function getRemainingGraceTime(): ?Carbon
    {
        if (! $this->isInGracePeriod()) {
            return NULL;
        }

        return $this->grace_period_expires_at;
    }

    /**
     * Get human readable time remaining
     */
    /**
     * Get  time remaining attribute
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (! $this->isInGracePeriod()) {
            return NULL;
        }

        return $this->grace_period_expires_at->diffForHumans();
    }

    /**
     * Cancel the deletion request
     */
    /**
     * Check if can cel
     */
    public function cancel(?string $reason = NULL): bool
    {
        if (! $this->isPending() && ! $this->isConfirmed()) {
            return FALSE;
        }

        $this->update([
            'status'       => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'metadata'     => array_merge($this->metadata ?? [], [
                'cancellation_reason'     => $reason,
                'cancelled_by_ip'         => request()->ip(),
                'cancelled_by_user_agent' => request()->userAgent(),
            ]),
        ]);

        return TRUE;
    }

    /**
     * Confirm the deletion request and start grace period
     */
    /**
     * Confirm
     */
    public function confirm(): bool
    {
        if (! $this->isPending()) {
            return FALSE;
        }

        $gracePeriodEnd = now()->addHours(24); // 24-hour grace period

        $this->update([
            'status'                  => self::STATUS_CONFIRMED,
            'email_confirmed_at'      => now(),
            'grace_period_expires_at' => $gracePeriodEnd,
            'metadata'                => array_merge($this->metadata ?? [], [
                'confirmed_by_ip'         => request()->ip(),
                'confirmed_by_user_agent' => request()->userAgent(),
            ]),
        ]);

        return TRUE;
    }

    /**
     * Mark the deletion as completed
     */
    /**
     * MarkCompleted
     */
    public function markCompleted(): bool
    {
        if (! $this->isConfirmed()) {
            return FALSE;
        }

        $this->update([
            'status'     => self::STATUS_COMPLETED,
            'deleted_at' => now(),
        ]);

        return TRUE;
    }

    /**
     * Mark the request as expired
     */
    /**
     * MarkExpired
     */
    public function markExpired(): bool
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);

        return TRUE;
    }

    /**
     * Scope to get active requests (pending or confirmed)
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
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope to get requests in grace period
     *
     * @param mixed $query
     */
    public function scopeInGracePeriod($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
            ->where('grace_period_expires_at', '>', now());
    }

    /**
     * Scope to get expired grace period requests
     *
     * @param mixed $query
     */
    public function scopeGracePeriodExpired($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
            ->where('grace_period_expires_at', '<=', now());
    }

    /**
     * Get all available statuses
     */
    /**
     * Get  statuses
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
