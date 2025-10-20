<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Event\Models\SportsEvent;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function count;
use function defined;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function strlen;

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
        'performed_at',
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
     */
    public function getResource(): ?Model
    {
        if (! $this->resource_type || ! $this->resource_id) {
            return NULL;
        }

        try {
            $modelClass = $this->getResourceModelClass();
            if (! $modelClass) {
                return NULL;
            }

            return $modelClass::find($this->resource_id);
        } catch (Exception) {
            return NULL;
        }
    }

    /**
     * Check if this is a sensitive action
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
            'system_config_change',
        ];

        return in_array($this->action, $sensitiveActions, TRUE);
    }

    /**
     * Get formatted changes summary
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
     * Scope for specific user
     *
     * @param mixed $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific action
     *
     * @param mixed $query
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific resource type
     *
     * @param mixed $query
     */
    public function scopeResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope for specific resource
     *
     * @param mixed $query
     * @param mixed $resourceId
     */
    public function scopeResource($query, string $resourceType, $resourceId)
    {
        return $query->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId);
    }

    /**
     * Scope for date range
     *
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for sensitive actions
     *
     * @param mixed $query
     */
    public function scopeSensitive($query)
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
            'system_config_change',
        ];

        return $query->whereIn('action', $sensitiveActions);
    }

    /**
     * Scope for recent activities
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('performed_at', '>=', now()->subHours($hours));
    }

    /**
     * Create audit log entry
     *
     * @param mixed $resourceId
     */
    public static function logAction(
        string $action,
        ?User $user = NULL,
        ?string $resourceType = NULL,
        $resourceId = NULL,
        array $changes = [],
    ): self {
        $request = request();

        return static::create([
            'user_id'       => $user?->id,
            'action'        => $action,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'changes'       => $changes,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->header('User-Agent'),
            'session_id'    => defined('PHPSTAN_RUNNING') ? NULL : session()->getId(),
            'performed_at'  => now(),
        ]);
    }

    /**
     * Get the model class for the resource type
     */
    protected function getResourceModelClass(): ?string
    {
        $resourceTypeMap = [
            'user'       => User::class,
            'ticket'     => Ticket::class,
            'role'       => Role::class,
            'permission' => Permission::class,
            'event'      => SportsEvent::class,
            'purchase'   => TicketPurchase::class,
            // Add more resource type mappings as needed
        ];

        return $resourceTypeMap[$this->resource_type] ?? NULL;
    }

    /**
     * Format value for display
     *
     * @param mixed $value
     */
    protected function formatValue($value): string
    {
        if (NULL === $value) {
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

    protected function casts(): array
    {
        return [
            'changes'      => 'array',
            'performed_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
