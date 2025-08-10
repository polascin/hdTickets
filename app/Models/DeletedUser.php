<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeletedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_user_id',
        'user_data',
        'related_data',
        'deletion_reason',
        'deleted_at',
        'recoverable_until',
        'is_recovered',
        'recovered_at',
    ];

    protected $casts = [
        'user_data' => 'array',
        'related_data' => 'array',
        'deleted_at' => 'datetime',
        'recoverable_until' => 'datetime',
        'is_recovered' => 'boolean',
        'recovered_at' => 'datetime',
    ];

    /**
     * Check if the user can still be recovered
     */
    public function isRecoverable(): bool
    {
        return !$this->is_recovered && $this->recoverable_until->isFuture();
    }

    /**
     * Check if the recovery period has expired
     */
    public function isRecoveryExpired(): bool
    {
        return !$this->is_recovered && $this->recoverable_until->isPast();
    }

    /**
     * Get the remaining recovery time
     */
    public function getRemainingRecoveryTime(): ?Carbon
    {
        if (!$this->isRecoverable()) {
            return null;
        }

        return $this->recoverable_until;
    }

    /**
     * Get human readable time remaining for recovery
     */
    public function getRecoveryTimeRemainingAttribute(): ?string
    {
        if (!$this->isRecoverable()) {
            return null;
        }

        return $this->recoverable_until->diffForHumans();
    }

    /**
     * Mark as recovered
     */
    public function markRecovered(): bool
    {
        if (!$this->isRecoverable()) {
            return false;
        }

        $this->update([
            'is_recovered' => true,
            'recovered_at' => now(),
        ]);

        return true;
    }

    /**
     * Scope to get recoverable users
     */
    public function scopeRecoverable($query)
    {
        return $query->where('is_recovered', false)
                    ->where('recoverable_until', '>', now());
    }

    /**
     * Scope to get expired recovery users
     */
    public function scopeRecoveryExpired($query)
    {
        return $query->where('is_recovered', false)
                    ->where('recoverable_until', '<=', now());
    }

    /**
     * Scope to get recovered users
     */
    public function scopeRecovered($query)
    {
        return $query->where('is_recovered', true);
    }
}
