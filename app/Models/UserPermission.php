<?php declare(strict_types=1);

namespace App\Models;

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

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if permission is active (not expired)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope for active permissions only
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired permissions only
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope for specific resource type
     */
    public function scopeResourceType($query, ?string $resourceType)
    {
        if ($resourceType === NULL) {
            return $query->whereNull('resource_type');
        }

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
     * Scope for temporary permissions (with expiration)
     */
    public function scopeTemporary($query)
    {
        return $query->whereNotNull('expires_at');
    }

    /**
     * Scope for permanent permissions (without expiration)
     */
    public function scopePermanent($query)
    {
        return $query->whereNull('expires_at');
    }

    /**
     * Get remaining time until expiration
     *
     * @return \Carbon\CarbonInterval|null
     */
    public function getTimeUntilExpiration(): ?\Carbon\CarbonInterval
    {
        if (!$this->expires_at) {
            return NULL;
        }

        return now()->diffAsCarbonInterval($this->expires_at, FALSE);
    }

    /**
     * Extend expiration time
     *
     * @param  \DateInterval $interval
     * @return bool
     */
    public function extend(\DateInterval $interval): bool
    {
        if (!$this->expires_at) {
            return FALSE;
        }

        $this->expires_at = $this->expires_at->add($interval);

        return $this->save();
    }

    /**
     * Make permission permanent (remove expiration)
     *
     * @return bool
     */
    public function makePermanent(): bool
    {
        $this->expires_at = NULL;

        return $this->save();
    }
}
