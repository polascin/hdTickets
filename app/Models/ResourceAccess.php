<?php declare(strict_types=1);

namespace App\Models;

use App\Domain\Event\Models\SportsEvent;
use DateTime;
use Exception;
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
        'context',
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
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if access is active (not expired)
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Scope for active access only
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
     * Scope for expired access only
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
     * Scope for specific action
     *
     * @param mixed $query
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for user access
     *
     * @param mixed $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the actual resource model if it exists
     */
    public function getResource(): ?Model
    {
        if (! $this->resource_type || ! $this->resource_id) {
            return null;
        }

        try {
            $modelClass = $this->getResourceModelClass();
            if (! $modelClass) {
                return null;
            }

            return $modelClass::find($this->resource_id);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Check if context matches given criteria
     */
    public function matchesContext(array $criteria): bool
    {
        if (empty($this->context) || $criteria === []) {
            return empty($this->context) && $criteria === [];
        }

        foreach ($criteria as $key => $value) {
            if (! isset($this->context[$key]) || $this->context[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Grant access to resource for user
     *
     * @param mixed $resourceId
     */
    public static function grant(
        User $user,
        string $resourceType,
        $resourceId,
        string $action,
        array $context = [],
        ?DateTime $expiresAt = null,
    ): self {
        return static::create([
            'user_id'       => $user->id,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'action'        => $action,
            'context'       => $context,
            'granted_at'    => now(),
            'expires_at'    => $expiresAt,
            'granted_by'    => auth()->id(),
        ]);
    }

    /**
     * Revoke access to resource for user
     *
     * @param mixed $resourceId
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
     * @param mixed $resourceId
     */
    public static function hasAccess(
        User $user,
        string $resourceType,
        $resourceId,
        string $action,
        array $context = [],
    ): bool {
        $query = static::forUser($user->id)
            ->resource($resourceType, $resourceId)
            ->action($action)
            ->active();

        if ($context !== []) {
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

    /**
     * Get the model class for the resource type
     */
    protected function getResourceModelClass(): ?string
    {
        $resourceTypeMap = [
            'ticket'     => Ticket::class,
            'user'       => User::class,
            'role'       => Role::class,
            'permission' => Permission::class,
            'event'      => SportsEvent::class,
            // Add more resource type mappings as needed
        ];

        return $resourceTypeMap[$this->resource_type] ?? null;
    }

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'context'    => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
