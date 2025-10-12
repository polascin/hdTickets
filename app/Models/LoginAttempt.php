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
        return ! $this->success;
    }

    /**
     * Get a human-readable device description
     */
    public function getDeviceDescription(): string
    {
        if (str_contains($this->user_agent, 'Mobile')) {
            return 'Mobile Device';
        }
        if (str_contains($this->user_agent, 'Chrome')) {
            return 'Chrome Browser';
        }
        if (str_contains($this->user_agent, 'Firefox')) {
            return 'Firefox Browser';
        }
        if (str_contains($this->user_agent, 'Safari')) {
            return 'Safari Browser';
        }
        if (str_contains($this->user_agent, 'Edge')) {
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
        }
        if ($this->country_code) {
            return $this->country_code;
        }

        return 'Unknown Location';
    }

    /**
     * Scope for successful attempts
     *
     * @param mixed $query
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed attempts
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for recent attempts
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $minutes = 15)
    {
        return $query->where('attempted_at', '>', now()->subMinutes($minutes));
    }

    /**
     * Scope for attempts by IP
     *
     * @param mixed $query
     */
    public function scopeByIP($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for attempts from specific country
     *
     * @param mixed $query
     */
    public function scopeFromCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    protected function casts(): array
    {
        return [
            'success'      => 'boolean',
            'used_2fa'     => 'boolean',
            'attempted_at' => 'datetime',
        ];
    }
}
