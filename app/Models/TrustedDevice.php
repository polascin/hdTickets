<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'ip_address',
        'user_agent',
        'expires_at',
        'last_used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime'
    ];

    /**
     * Get the user that owns the trusted device
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if device trust has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if device is still trusted (not expired)
     */
    public function isTrusted(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(): bool
    {
        return $this->update(['last_used_at' => now()]);
    }

    /**
     * Extend trust period
     */
    public function extendTrust(int $days = 30): bool
    {
        return $this->update([
            'expires_at' => now()->addDays($days),
            'last_used_at' => now()
        ]);
    }

    /**
     * Revoke device trust
     */
    public function revokeTrust(): bool
    {
        return $this->update(['expires_at' => now()]);
    }

    /**
     * Get device type based on user agent
     */
    public function getDeviceType(): string
    {
        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserName(): string
    {
        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'chrome') && !str_contains($userAgent, 'edg')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'safari') && !str_contains($userAgent, 'chrome')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'edg')) {
            return 'Edge';
        } elseif (str_contains($userAgent, 'opera')) {
            return 'Opera';
        }
        
        return 'Unknown';
    }

    /**
     * Get operating system from user agent
     */
    public function getOperatingSystem(): string
    {
        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'mac')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'iOS';
        }
        
        return 'Unknown';
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(): int
    {
        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Scope for active (not expired) devices
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope for expired devices
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope for recently used devices
     */
    public function scopeRecentlyUsed($query, int $days = 7)
    {
        return $query->where('last_used_at', '>', now()->subDays($days));
    }

    /**
     * Scope for devices by type
     */
    public function scopeByType($query, string $type)
    {
        $userAgentPattern = match($type) {
            'mobile' => '%mobile%',
            'tablet' => '%tablet%',
            'desktop' => '%',
            default => '%'
        };
        
        return $query->where('user_agent', 'like', $userAgentPattern);
    }
}
