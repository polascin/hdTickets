<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'country_code',
        'city',
        'success',
        'failure_reason',
        'used_2fa',
        'attempted_at',
    ];

    protected $casts = [
        'success'      => 'boolean',
        'used_2fa'     => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Get the user that made the login attempt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if login attempt was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if login attempt failed
     */
    public function wasFailed(): bool
    {
        return !$this->success;
    }

    /**
     * Get a human-readable device description
     */
    public function getDeviceDescription(): string
    {
        if (str_contains($this->user_agent, 'Mobile')) {
            return 'Mobile Device';
        } elseif (str_contains($this->user_agent, 'Chrome')) {
            return 'Chrome Browser';
        } elseif (str_contains($this->user_agent, 'Firefox')) {
            return 'Firefox Browser';
        } elseif (str_contains($this->user_agent, 'Safari')) {
            return 'Safari Browser';
        } elseif (str_contains($this->user_agent, 'Edge')) {
            return 'Edge Browser';
        }

        return 'Unknown Device';
    }

    /**
     * Get location string
     */
    public function getLocationString(): string
    {
        if ($this->city && $this->country_code) {
            return "{$this->city}, {$this->country_code}";
        } elseif ($this->country_code) {
            return $this->country_code;
        }

        return 'Unknown Location';
    }

    /**
     * Scope for successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', TRUE);
    }

    /**
     * Scope for failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->where('success', FALSE);
    }

    /**
     * Scope for recent attempts
     */
    public function scopeRecent($query, int $minutes = 15)
    {
        return $query->where('attempted_at', '>', now()->subMinutes($minutes));
    }

    /**
     * Scope for attempts by IP
     */
    public function scopeByIP($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for attempts from specific country
     */
    public function scopeFromCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
}
