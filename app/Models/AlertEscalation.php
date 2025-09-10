<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'cancellation_reason',
    ];

    /**
     * Get the alert that owns this escalation
     */
    /**
     * Alert
     */
    public function alert(): BelongsTo
    {
        return $this->belongsTo(TicketAlert::class, 'alert_id');
    }

    /**
     * Get the user for this escalation
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active escalations
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
        return $query->whereIn('status', ['scheduled', 'retrying']);
    }

    /**
     * Scope for failed escalations
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for completed escalations
     *
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if escalation is still valid
     */
    /**
     * Check if  valid
     */
    public function isValid(): bool
    {
        return $this->status === 'scheduled' || $this->status === 'retrying';
    }

    /**
     * Check if escalation has exceeded max attempts
     */
    /**
     * Check if has  exceeded max attempts
     */
    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Get escalation progress percentage
     */
    /**
     * Get  progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->max_attempts <= 0) {
            return 0;
        }

        return min(100, (int) (($this->attempts / $this->max_attempts) * 100));
    }

    protected function casts(): array
    {
        return [
            'scheduled_at'      => 'datetime',
            'last_attempted_at' => 'datetime',
            'next_retry_at'     => 'datetime',
            'alert_data'        => 'array',
            'escalation_config' => 'array',
        ];
    }
}
