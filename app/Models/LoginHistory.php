<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        return $query->where('success', true);
    }

    /**
     * Scope to get failed login attempts.
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to get suspicious login attempts.
     *
     * @param mixed $query
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
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
                $this->device_type ? "({$this->device_type})" : null,
            ]);

            return implode(' on ', $parts) ?: 'Unknown Device';
        });
    }

    /**
     * Get  risk level attribute
     */
    protected function riskLevel(): Attribute
    {
        return Attribute::make(get: function (): string {
            if (! $this->is_suspicious) {
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
        });
    }

    /**
     * Get  risk color attribute
     */
    protected function riskColor(): Attribute
    {
        return Attribute::make(get: fn (): string => match ($this->risk_level) {
            'high'   => 'red',
            'medium' => 'yellow',
            'low'    => 'green',
            default  => 'gray',
        });
    }

    protected function casts(): array
    {
        return [
            'success'          => 'boolean',
            'is_suspicious'    => 'boolean',
            'suspicious_flags' => 'array',
            'attempted_at'     => 'datetime',
            'latitude'         => 'decimal:8',
            'longitude'        => 'decimal:8',
        ];
    }
}
