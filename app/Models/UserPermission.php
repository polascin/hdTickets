<?php declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterval;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserPermission Model for Advanced RBAC
 *
 * Represents direct permission assignments to users with optional resource scoping and expiration
 */
class UserPermission extends Model
{
    use HasFactory;

    protected $table = 'user_permissions';

    protected $fillable = [
        'user_id',
        'permission_id',
        'resource_type',
        'resource_id',
        'granted_at',
        'expires_at',
        'granted_by',
    ];

    /**
     * User who has the permission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Permission that is granted
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * User who granted the permission
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if permission is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if permission is active (not expired)
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Scope for active permissions only
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q): void {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired permissions only
     *
     * @param mixed $query
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope for specific resource type
     *
     * @param mixed $query
     */
    public function scopeResourceType($query, ?string $resourceType)
    {
        if ($resourceType === null) {
            return $query->whereNull('resource_type');
        }

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
     * Scope for temporary permissions (with expiration)
     *
     * @param mixed $query
     */
    public function scopeTemporary($query)
    {
        return $query->whereNotNull('expires_at');
    }

    /**
     * Scope for permanent permissions (without expiration)
     *
     * @param mixed $query
     */
    public function scopePermanent($query)
    {
        return $query->whereNull('expires_at');
    }

    /**
     * Get remaining time until expiration
     */
    public function getTimeUntilExpiration(): ?CarbonInterval
    {
        if (! $this->expires_at) {
            return null;
        }

        return now()->diffAsCarbonInterval($this->expires_at, false);
    }

    /**
     * Extend expiration time
     */
    public function extend(DateInterval $interval): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        $this->expires_at = $this->expires_at->add($interval);

        return $this->save();
    }

    /**
     * Make permission permanent (remove expiration)
     */
    public function makePermanent(): bool
    {
        $this->expires_at = null;

        return $this->save();
    }

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
