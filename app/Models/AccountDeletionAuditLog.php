<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDeletionAuditLog extends Model
{
    use HasFactory;
    
    protected $table = 'account_deletion_audit_log';

    protected $fillable = [
        'user_id',
        'action',
        'status_from',
        'status_to',
        'description',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    const ACTION_INITIATED = 'initiated';
    const ACTION_EMAIL_SENT = 'email_sent';
    const ACTION_CONFIRMED = 'confirmed';
    const ACTION_CANCELLED = 'cancelled';
    const ACTION_COMPLETED = 'completed';
    const ACTION_RECOVERED = 'recovered';
    const ACTION_DATA_EXPORTED = 'data_exported';
    const ACTION_GRACE_PERIOD_EXPIRED = 'grace_period_expired';

    /**
     * Create a new audit log entry
     */
    public static function log(
        int $userId,
        string $action,
        string $description,
        array $context = [],
        ?string $statusFrom = null,
        ?string $statusTo = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'description' => $description,
            'context' => array_merge($context, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ]),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Scope to get logs for a specific action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    /**
     * Get all available actions
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_INITIATED,
            self::ACTION_EMAIL_SENT,
            self::ACTION_CONFIRMED,
            self::ACTION_CANCELLED,
            self::ACTION_COMPLETED,
            self::ACTION_RECOVERED,
            self::ACTION_DATA_EXPORTED,
            self::ACTION_GRACE_PERIOD_EXPIRED,
        ];
    }
}
