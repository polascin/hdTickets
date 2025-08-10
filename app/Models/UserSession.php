<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'operating_system',
        'country',
        'city',
        'is_current',
        'is_trusted',
        'last_activity',
        'expires_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'is_trusted' => 'boolean',
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get expired sessions.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get trusted sessions.
     */
    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', true);
    }

    /**
     * Scope to get current session.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Get formatted location string.
     */
    public function getLocationStringAttribute(): string
    {
        if ($this->city && $this->country) {
            return "{$this->city}, {$this->country}";
        }

        if ($this->country) {
            return $this->country;
        }

        return 'Unknown Location';
    }

    /**
     * Get formatted device information.
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([
            $this->browser,
            $this->operating_system,
            $this->device_type ? "({$this->device_type})" : null
        ]);

        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get device icon based on device type.
     */
    public function getDeviceIconAttribute(): string
    {
        return match (strtolower($this->device_type ?? '')) {
            'mobile' => 'device-mobile',
            'tablet' => 'device-tablet',
            'desktop' => 'computer-desktop',
            default => 'device-desktop'
        };
    }

    /**
     * Check if session is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get time since last activity.
     */
    public function getTimeSinceLastActivityAttribute(): string
    {
        return $this->last_activity->diffForHumans();
    }

    /**
     * Get session duration.
     */
    public function getSessionDurationAttribute(): string
    {
        if ($this->expires_at) {
            return $this->created_at->diffForHumans($this->expires_at, true);
        }

        return $this->created_at->diffForHumans(now(), true);
    }

    /**
     * Mark session as trusted device.
     */
    public function markAsTrusted(): void
    {
        $this->update(['is_trusted' => true]);
    }

    /**
     * Revoke the session.
     */
    public function revoke(): void
    {
        $this->delete();
    }
}
