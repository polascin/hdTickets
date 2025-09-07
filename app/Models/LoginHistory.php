<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function count;
use function is_array;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'operating_system',
        'country',
        'city',
        'latitude',
        'longitude',
        'success',
        'failure_reason',
        'is_suspicious',
        'suspicious_flags',
        'session_id',
        'attempted_at',
    ];

    protected $casts = [
        'success'          => 'boolean',
        'is_suspicious'    => 'boolean',
        'suspicious_flags' => 'array',
        'attempted_at'     => 'datetime',
        'latitude'         => 'decimal:8',
        'longitude'        => 'decimal:8',
    ];

    /**
     * Get the user that owns the login history record.
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get successful login attempts.
     *
     * @param mixed $query
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', TRUE);
    }

    /**
     * Scope to get failed login attempts.
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('success', FALSE);
    }

    /**
     * Scope to get suspicious login attempts.
     *
     * @param mixed $query
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', TRUE);
    }

    /**
     * Scope to get recent login attempts.
     *
     * @param mixed $query
     * @param mixed $days
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('attempted_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted location string.
     */
    /**
     * Get  location string attribute
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
    /**
     * Get  device info attribute
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([
            $this->browser,
            $this->operating_system,
            $this->device_type ? "({$this->device_type})" : NULL,
        ]);

        return implode(' on ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get risk level based on suspicious flags.
     */
    /**
     * Get  risk level attribute
     */
    public function getRiskLevelAttribute(): string
    {
        if (!$this->is_suspicious) {
            return 'low';
        }

        $flagCount = is_array($this->suspicious_flags) ? count($this->suspicious_flags) : 0;

        if ($flagCount >= 3) {
            return 'high';
        }

        if ($flagCount >= 1) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get risk level color for UI display.
     */
    /**
     * Get  risk color attribute
     */
    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_level) {
            'high'   => 'red',
            'medium' => 'yellow',
            'low'    => 'green',
            default  => 'gray',
        };
    }
}
