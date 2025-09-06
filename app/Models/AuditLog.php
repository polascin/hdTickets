<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * 
 * Tracks all user actions and system changes for compliance and security auditing
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'changes',
        'ip_address',
        'user_agent',
        'session_id',
        'performed_at'
    ];

    protected $casts = [
        'changes' => 'array',
        'performed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the actual resource model if it exists
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getResource(): ?\Illuminate\Database\Eloquent\Model
    {
        if (!$this->resource_type || !$this->resource_id) {
            return null;
        }

        try {
            $modelClass = $this->getResourceModelClass();
            if (!$modelClass) {
                return null;
            }

            return $modelClass::find($this->resource_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the model class for the resource type
     *
     * @return string|null
     */
    protected function getResourceModelClass(): ?string
    {
        $resourceTypeMap = [
            'user' => \App\Models\User::class,
            'ticket' => \App\Models\Ticket::class,
            'role' => \App\Models\Role::class,
            'permission' => \App\Models\Permission::class,
            'event' => \App\Domain\Event\Models\SportsEvent::class,
            'purchase' => \App\Models\TicketPurchase::class,
            // Add more resource type mappings as needed
        ];

        return $resourceTypeMap[$this->resource_type] ?? null;
    }

    /**
     * Check if this is a sensitive action
     *
     * @return bool
     */
    public function isSensitiveAction(): bool
    {
        $sensitiveActions = [
            'delete',
            'force_delete',
            'restore',
            'login',
            'logout',
            'password_change',
            'role_change',
            'permission_grant',
            'permission_revoke',
            'account_lock',
            'account_unlock',
            'data_export',
            'system_config_change'
        ];

        return in_array($this->action, $sensitiveActions);
    }

    /**
     * Get formatted changes summary
     *
     * @return string
     */
    public function getChangesSummary(): string
    {
        if (empty($this->changes)) {
            return 'No changes recorded';
        }

        $summary = [];
        foreach ($this->changes as $field => $change) {
            if (is_array($change) && isset($change['old'], $change['new'])) {
                $old = $this->formatValue($change['old']);
                $new = $this->formatValue($change['new']);
                $summary[] = "{$field}: {$old} → {$new}";
            } else {
                $summary[] = "{$field}: " . $this->formatValue($change);
            }
        }

        return implode(', ', $summary);
    }

    /**
     * Format value for display
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '[null]';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return '[array(' . count($value) . ')]';
        }

        if (is_string($value) && strlen($value) > 50) {
            return substr($value, 0, 47) . '...';
        }

        return (string) $value;
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific resource type
     */
    public function scopeResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope for specific resource
     */
    public function scopeResource($query, string $resourceType, $resourceId)
    {
        return $query->where('resource_type', $resourceType)
                    ->where('resource_id', $resourceId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for sensitive actions
     */
    public function scopeSensitive($query)
    {
        $sensitiveActions = [
            'delete', 'force_delete', 'restore', 'login', 'logout',
            'password_change', 'role_change', 'permission_grant',
            'permission_revoke', 'account_lock', 'account_unlock',
            'data_export', 'system_config_change'
        ];

        return $query->whereIn('action', $sensitiveActions);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('performed_at', '>=', now()->subHours($hours));
    }

    /**
     * Create audit log entry
     *
     * @param string $action
     * @param User|null $user
     * @param string|null $resourceType
     * @param mixed $resourceId
     * @param array $changes
     * @return AuditLog
     */
    public static function logAction(
        string $action,
        ?User $user = null,
        ?string $resourceType = null,
        $resourceId = null,
        array $changes = []
    ): AuditLog {
        $request = request();

        return static::create([
            'user_id' => $user?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'changes' => $changes,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'session_id' => session()->getId(),
            'performed_at' => now()
        ]);
    }
}
