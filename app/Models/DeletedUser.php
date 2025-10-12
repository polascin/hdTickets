<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Check if the user can still be recovered
     */
    /**
     * Check if  recoverable
     */
    public function isRecoverable(): bool
    {
        return ! $this->is_recovered && $this->recoverable_until->isFuture();
    }

    /**
     * Check if the recovery period has expired
     */
    /**
     * Check if  recovery expired
     */
    public function isRecoveryExpired(): bool
    {
        return ! $this->is_recovered && $this->recoverable_until->isPast();
    }

    /**
     * Get the remaining recovery time
     */
    /**
     * Get  remaining recovery time
     */
    public function getRemainingRecoveryTime(): ?Carbon
    {
        if (! $this->isRecoverable()) {
            return null;
        }

        return $this->recoverable_until;
    }

    /**
     * Mark as recovered
     */
    /**
     * MarkRecovered
     */
    public function markRecovered(): bool
    {
        if (! $this->isRecoverable()) {
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
     *
     * @param mixed $query
     */
    public function scopeRecoverable($query)
    {
        return $query->where('is_recovered', false)
            ->where('recoverable_until', '>', now());
    }

    /**
     * Scope to get expired recovery users
     *
     * @param mixed $query
     */
    public function scopeRecoveryExpired($query)
    {
        return $query->where('is_recovered', false)
            ->where('recoverable_until', '<=', now());
    }

    /**
     * Scope to get recovered users
     *
     * @param mixed $query
     */
    public function scopeRecovered($query)
    {
        return $query->where('is_recovered', true);
    }

    /**
     * Get  recovery time remaining attribute
     */
    protected function recoveryTimeRemaining(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->isRecoverable()) {
                return;
            }

            return $this->recoverable_until->diffForHumans();
        });
    }

    protected function casts(): array
    {
        return [
            'user_data'         => 'array',
            'related_data'      => 'array',
            'deleted_at'        => 'datetime',
            'recoverable_until' => 'datetime',
            'is_recovered'      => 'boolean',
            'recovered_at'      => 'datetime',
        ];
    }
}
