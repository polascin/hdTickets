<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ResourceAccess Model for Advanced RBAC
 * 
 * Represents specific access permissions to individual resources
 */
class ResourceAccess extends Model
{
    use HasFactory;

    protected $table = 'resource_access';

    protected $fillable = [
        'user_id',
        'resource_type',
        'resource_id',
        'action',
        'granted_at',
        'expires_at',
        'granted_by',
        'context'
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * User who has the resource access
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who granted the access
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if access is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if access is active (not expired)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope for active access only
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired access only
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
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
     * Scope for specific action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for user access
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
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
            'ticket' => \App\Models\Ticket::class,
            'user' => \App\Models\User::class,
            'role' => \App\Models\Role::class,
            'permission' => \App\Models\Permission::class,
            'event' => \App\Domain\Event\Models\SportsEvent::class,
            // Add more resource type mappings as needed
        ];

        return $resourceTypeMap[$this->resource_type] ?? null;
    }

    /**
     * Check if context matches given criteria
     *
     * @param array $criteria
     * @return bool
     */
    public function matchesContext(array $criteria): bool
    {
        if (empty($this->context) || empty($criteria)) {
            return empty($this->context) && empty($criteria);
        }

        foreach ($criteria as $key => $value) {
            if (!isset($this->context[$key]) || $this->context[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Grant access to resource for user
     *
     * @param User $user
     * @param string $resourceType
     * @param mixed $resourceId
     * @param string $action
     * @param array $context
     * @param \DateTime|null $expiresAt
     * @return ResourceAccess
     */
    public static function grant(
        User $user,
        string $resourceType,
        $resourceId,
        string $action,
        array $context = [],
        ?\DateTime $expiresAt = null
    ): ResourceAccess {
        return static::create([
            'user_id' => $user->id,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => $action,
            'context' => $context,
            'granted_at' => now(),
            'expires_at' => $expiresAt,
            'granted_by' => auth()->id()
        ]);
    }

    /**
     * Revoke access to resource for user
     *
     * @param User $user
     * @param string $resourceType
     * @param mixed $resourceId
     * @param string $action
     * @return int
     */
    public static function revoke(User $user, string $resourceType, $resourceId, string $action): int
    {
        return static::where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('action', $action)
            ->delete();
    }

    /**
     * Check if user has access to resource
     *
     * @param User $user
     * @param string $resourceType
     * @param mixed $resourceId
     * @param string $action
     * @param array $context
     * @return bool
     */
    public static function hasAccess(
        User $user,
        string $resourceType,
        $resourceId,
        string $action,
        array $context = []
    ): bool {
        $query = static::forUser($user->id)
            ->resource($resourceType, $resourceId)
            ->action($action)
            ->active();

        if (!empty($context)) {
            // For context matching, we need to check each record individually
            foreach ($query->get() as $access) {
                if ($access->matchesContext($context)) {
                    return true;
                }
            }
            return false;
        }

        return $query->exists();
    }
}
