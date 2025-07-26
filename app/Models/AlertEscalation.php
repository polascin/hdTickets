<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_id',
        'user_id',
        'priority',
        'strategy',
        'scheduled_at',
        'attempts',
        'max_attempts',
        'status',
        'alert_data',
        'escalation_config',
        'last_attempted_at',
        'next_retry_at',
        'cancellation_reason'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'alert_data' => 'array',
        'escalation_config' => 'array'
    ];

    /**
     * Get the alert that owns this escalation
     */
    public function alert(): BelongsTo
    {
        return $this->belongsTo(TicketAlert::class, 'alert_id');
    }

    /**
     * Get the user for this escalation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active escalations
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'retrying']);
    }

    /**
     * Scope for failed escalations
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for completed escalations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if escalation is still valid
     */
    public function isValid(): bool
    {
        return $this->status === 'scheduled' || $this->status === 'retrying';
    }

    /**
     * Check if escalation has exceeded max attempts
     */
    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Get escalation progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->max_attempts <= 0) {
            return 0;
        }

        return min(100, intval(($this->attempts / $this->max_attempts) * 100));
    }
}
