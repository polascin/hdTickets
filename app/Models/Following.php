<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Following Model
 *
 * Represents user following relationships with teams and venues
 * Uses polymorphic relationships to support following different entity types
 */
class Following extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'followable_type',
        'followable_id',
        'notifications_enabled',
        'last_activity_at',
        'followed_at',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'last_activity_at'      => 'datetime',
        'followed_at'           => 'datetime',
    ];

    /**
     * Get the user who is following
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the followable model (team or venue)
     */
    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for active followings (with notifications enabled)
     *
     * @param mixed $query
     */
    public function scopeWithNotifications($query)
    {
        return $query->where('notifications_enabled', TRUE);
    }

    /**
     * Scope for recent activity
     *
     * @param mixed $query
     */
    public function scopeRecentActivity($query, int $days = 7)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for team followings
     *
     * @param mixed $query
     */
    public function scopeTeams($query)
    {
        return $query->where('followable_type', Team::class);
    }

    /**
     * Scope for venue followings
     *
     * @param mixed $query
     */
    public function scopeVenues($query)
    {
        return $query->where('followable_type', Venue::class);
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $model->followed_at = now();
        });
    }
}
