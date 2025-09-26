<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    public $incrementing = FALSE;

    protected $table = 'user_sessions';

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

    /**
     * Get the user that owns the session.
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active sessions.
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
        return $query->where(function ($q): void {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get expired sessions.
     *
     * @param mixed $query
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get trusted sessions.
     *
     * @param mixed $query
     */
    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', TRUE);
    }

    /**
     * Scope to get current session.
     *
     * @param mixed $query
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', TRUE);
    }

    /**
     * Check if session is expired.
     */
    /**
     * Check if  expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    /**
     * Check if session is active.
     */
    /**
     * Check if  active
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Mark session as trusted device.
     */
    /**
     * MarkAsTrusted
     */
    public function markAsTrusted(): void
    {
        $this->update(['is_trusted' => TRUE]);
    }

    /**
     * Revoke the session.
     */
    /**
     * Revoke
     */
    public function revoke(): void
    {
        $this->delete();
    }

    /**
     * Get  location string attribute
     */
    protected function locationString(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->city && $this->country) {
                return "{$this->city}, {$this->country}";
            }
            if ($this->country) {
                return $this->country;
            }

            return 'Unknown Location';
        });
    }

    /**
     * Get  device info attribute
     */
    protected function deviceInfo(): Attribute
    {
        return Attribute::make(get: function (): string {
            $parts = array_filter([
                $this->browser,
                $this->operating_system,
                $this->device_type ? "({$this->device_type})" : NULL,
            ]);

            return implode(' on ', $parts) ?: 'Unknown Device';
        });
    }

    /**
     * Get  device icon attribute
     */
    protected function deviceIcon(): Attribute
    {
        return Attribute::make(get: fn (): string => match (strtolower($this->device_type ?? '')) {
            'mobile'  => 'device-mobile',
            'tablet'  => 'device-tablet',
            'desktop' => 'computer-desktop',
            default   => 'device-desktop',
        });
    }

    /**
     * Get  time since last activity attribute
     */
    protected function timeSinceLastActivity(): Attribute
    {
        return Attribute::make(get: fn () => $this->last_activity->diffForHumans());
    }

    /**
     * Get  session duration attribute
     */
    protected function sessionDuration(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->expires_at) {
                return $this->created_at->diffForHumans($this->expires_at, ['syntax' => 1]);
            }

            return $this->created_at->diffForHumans(now(), ['syntax' => 1]);
        });
    }

    protected function casts(): array
    {
        return [
            'is_current'    => 'boolean',
            'is_trusted'    => 'boolean',
            'last_activity' => 'datetime',
            'expires_at'    => 'datetime',
        ];
    }
}
