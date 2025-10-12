<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function in_array;
use function is_array;

class SecurityEvent extends Model
{
    use HasFactory;

    /** Security event types */
    public const EVENT_TYPES = [
        'login_successful'         => 'Login Successful',
        'login_failed'             => 'Login Failed',
        'login_attempt_locked'     => 'Login Attempt While Locked',
        'login_rate_limited'       => 'Login Rate Limited',
        'account_locked'           => 'Account Locked',
        'account_unlocked'         => 'Account Unlocked',
        'device_trusted'           => 'Device Trusted',
        'device_revoked'           => 'Device Trust Revoked',
        '2fa_enabled'              => '2FA Enabled',
        '2fa_disabled'             => '2FA Disabled',
        '2fa_backup_used'          => '2FA Backup Code Used',
        '2fa_recovery_used'        => '2FA Recovery Code Used',
        'password_changed'         => 'Password Changed',
        'password_reset_requested' => 'Password Reset Requested',
        'password_reset_completed' => 'Password Reset Completed',
        'suspicious_activity'      => 'Suspicious Activity Detected',
        'high_risk_login'          => 'High Risk Login Detected',
        'permission_denied'        => 'Permission Denied',
        'role_changed'             => 'User Role Changed',
        'data_export'              => 'Data Export',
        'data_deletion'            => 'Data Deletion',
    ];

    /** Risk levels */
    public const RISK_LEVELS = [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ];

    protected $fillable = [
        'user_id',
        'event_type',
        'severity',
        'ip_address',
        'user_agent',
        'location',
        'event_data',
        'request_data',
        'session_id',
        'threat_score',
        'incident_id',
        'occurred_at',
    ];

    /**
     * Get the user associated with the security event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the security incident this event is linked to
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(SecurityIncident::class, 'incident_id');
    }

    /**
     * Determine risk level based on event type
     */
    public function getRiskLevel(): string
    {
        return match ($this->event_type) {
            'account_locked', 'suspicious_activity', 'high_risk_login' => 'high',
            'login_failed', 'login_rate_limited', 'device_revoked', '2fa_disabled' => 'medium',
            'permission_denied', 'login_attempt_locked' => 'medium',
            '2fa_recovery_used', 'password_reset_requested' => 'medium',
            'login_successful', 'device_trusted', '2fa_enabled', 'password_changed' => 'low',
            default => 'low',
        };
    }

    /**
     * Check if event is security-related
     */
    public function isSecurityCritical(): bool
    {
        return in_array($this->event_type, [
            'suspicious_activity',
            'high_risk_login',
            'account_locked',
            '2fa_disabled',
            'password_reset_completed',
            '2fa_recovery_used',
        ], true);
    }

    /**
     * Get risk level color for UI
     */
    public function getRiskLevelColor(): string
    {
        return match ($this->getRiskLevel()) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'yellow',
            'low'      => 'green',
            default    => 'gray',
        };
    }

    /**
     * Scope for events by type
     *
     * @param mixed $query
     */
    public function scopeByType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for events by risk level
     *
     * @param mixed $query
     */
    public function scopeByRiskLevel($query, string $riskLevel)
    {
        $eventTypes = [];

        foreach (array_keys(self::EVENT_TYPES) as $type) {
            $event = new self(['event_type' => $type]);
            if ($event->getRiskLevel() === $riskLevel) {
                $eventTypes[] = $type;
            }
        }

        return $query->whereIn('event_type', $eventTypes);
    }

    /**
     * Scope for recent events
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>', now()->subHours($hours));
    }

    /**
     * Scope for security critical events
     *
     * @param mixed $query
     */
    public function scopeSecurityCritical($query)
    {
        return $query->whereIn('event_type', [
            'suspicious_activity',
            'high_risk_login',
            'account_locked',
            '2fa_disabled',
            'password_reset_completed',
            '2fa_recovery_used',
        ]);
    }

    /**
     * Scope for login-related events
     *
     * @param mixed $query
     */
    public function scopeLoginRelated($query)
    {
        return $query->whereIn('event_type', [
            'login_successful',
            'login_failed',
            'login_attempt_locked',
            'login_rate_limited',
        ]);
    }

    /**
     * Scope for 2FA-related events
     *
     * @param mixed $query
     */
    public function scopeTwoFactorRelated($query)
    {
        return $query->whereIn('event_type', [
            '2fa_enabled',
            '2fa_disabled',
            '2fa_backup_used',
            '2fa_recovery_used',
        ]);
    }

    /**
     * Scope for events from specific IP
     *
     * @param mixed $query
     */
    public function scopeFromIP($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for events in date range
     *
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Get event summary for dashboard
     */
    public static function getEventSummary(int $hours = 24): array
    {
        $events = self::recent($hours)->get();

        $summary = [
            'total'           => $events->count(),
            'by_type'         => $events->groupBy('event_type')->map->count(),
            'by_risk'         => [],
            'critical_events' => $events->filter(fn ($event) => $event->isSecurityCritical())->count(),
        ];

        // Group by risk level
        foreach (['low', 'medium', 'high', 'critical'] as $risk) {
            $summary['by_risk'][$risk] = $events->filter(fn ($event): bool => $event->getRiskLevel() === $risk)->count();
        }

        return $summary;
    }

    /**
     * Get human-readable event type
     */
    protected function eventTypeName(): Attribute
    {
        return Attribute::make(get: fn () => self::EVENT_TYPES[$this->event_type] ?? $this->event_type);
    }

    /**
     * Get formatted location string
     */
    protected function formattedLocation(): Attribute
    {
        return Attribute::make(get: function () {
            if (! $this->location || ! is_array($this->location)) {
                return 'Unknown Location';
            }
            if (isset($this->location['city'], $this->location['country'])) {
                return "{$this->location['city']}, {$this->location['country']}";
            }

            return $this->location['country'] ?? 'Unknown Location';
        });
    }

    /**
     * Get device information
     */
    protected function deviceInfo(): Attribute
    {
        return Attribute::make(get: function () {
            if (isset($this->event_data['device_name'])) {
                return $this->event_data['device_name'];
            }
            // Extract from user agent
            if ($this->user_agent) {
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
            }

            return 'Unknown Device';
        });
    }

    protected function casts(): array
    {
        return [
            'event_data'   => 'array',
            'request_data' => 'array',
            'location'     => 'array',
            'occurred_at'  => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
