<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_key',
        'user_agent',
        'ip_address',
        'successful_notifications',
        'failed_notifications',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at'             => 'datetime',
        'successful_notifications' => 'integer',
        'failed_notifications'     => 'integer',
    ];

    /**
     * Get the user that owns the push subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('last_used_at', '>=', now()->subDays(30));
    }

    /**
     * Scope to get recent subscriptions
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if subscription is considered active
     */
    public function isActive(): bool
    {
        return $this->last_used_at && $this->last_used_at->isAfter(now()->subDays(30));
    }

    /**
     * Get success rate for this subscription
     */
    public function getSuccessRate(): float
    {
        $total = $this->successful_notifications + $this->failed_notifications;

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->successful_notifications / $total) * 100, 2);
    }

    /**
     * Increment successful notifications counter
     */
    public function incrementSuccessful(): void
    {
        $this->increment('successful_notifications');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Increment failed notifications counter
     */
    public function incrementFailed(): void
    {
        $this->increment('failed_notifications');
    }
}
