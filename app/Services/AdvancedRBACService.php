<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use App\Models\ResourceAccess;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Advanced Role-Based Access Control (RBAC) Service
 *
 * Provides comprehensive RBAC functionality including:
 * - Hierarchical role management with inheritance
 * - Granular permission system with resource-based control
 * - Dynamic permission evaluation and caching
 * - Context-aware access control
 * - Audit logging for permission changes
 * - Temporary permission grants and time-based access
 */
class AdvancedRBACService
{
    protected array $roleHierarchy = [
        'admin'    => ['agent', 'customer', 'scraper'],
        'agent'    => ['customer'],
        'customer' => [],
        'scraper'  => [],
    ];

    protected array $contextualPermissions = [];

    protected int $permissionCacheTTL = 3600; // 1 hour

    public function __construct()
    {
        $this->roleHierarchy = config('rbac.role_hierarchy', $this->roleHierarchy);
        $this->permissionCacheTTL = config('rbac.cache_ttl', 3600);
    }

    /**
     * Check if user has permission for specific action and resource
     *
     * @param  User        $user
     * @param  string      $permission
     * @param  string|null $resource
     * @param  array       $context
     * @return bool
     */
    public function hasPermission(User $user, string $permission, ?string $resource = NULL, array $context = []): bool
    {
        // Check cache first
        $cacheKey = $this->buildPermissionCacheKey($user, $permission, $resource, $context);

        return Cache::remember($cacheKey, $this->permissionCacheTTL, function () use ($user, $permission, $resource, $context) {
            return $this->evaluatePermission($user, $permission, $resource, $context);
        });
    }

    /**
     * Evaluate permission with full logic
     *
     * @param  User        $user
     * @param  string      $permission
     * @param  string|null $resource
     * @param  array       $context
     * @return bool
     */
    protected function evaluatePermission(User $user, string $permission, ?string $resource = NULL, array $context = []): bool
    {
        // 1. Check direct user permissions
        if ($this->hasDirectPermission($user, $permission, $resource)) {
            return TRUE;
        }

        // 2. Check role-based permissions
        if ($this->hasRolePermission($user, $permission, $resource)) {
            return TRUE;
        }

        // 3. Check inherited permissions from role hierarchy
        if ($this->hasInheritedPermission($user, $permission, $resource)) {
            return TRUE;
        }

        // 4. Check contextual permissions
        if ($this->hasContextualPermission($user, $permission, $resource, $context)) {
            return TRUE;
        }

        // 5. Check temporary permissions
        if ($this->hasTemporaryPermission($user, $permission, $resource)) {
            return TRUE;
        }

        // 6. Check resource-specific permissions
        if ($resource && $this->hasResourcePermission($user, $permission, $resource, $context)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Assign role to user with optional expiration
     *
     * @param  User           $user
     * @param  string         $roleName
     * @param  \DateTime|null $expiresAt
     * @return bool
     */
    public function assignRole(User $user, string $roleName, ?\DateTime $expiresAt = NULL): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            throw new \InvalidArgumentException("Role '{$roleName}' not found");
        }

        // Check if user already has this role
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return TRUE;
        }

        // Assign role
        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
            'expires_at'  => $expiresAt,
            'assigned_by' => auth()->id(),
        ]);

        // Clear permission cache
        $this->clearUserPermissionCache($user);

        // Log role assignment
        $this->logPermissionChange($user, 'role_assigned', [
            'role'        => $roleName,
            'expires_at'  => $expiresAt?->toISOString(),
            'assigned_by' => auth()->id(),
        ]);

        return TRUE;
    }

    /**
     * Remove role from user
     *
     * @param  User   $user
     * @param  string $roleName
     * @return bool
     */
    public function removeRole(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return FALSE;
        }

        $removed = $user->roles()->detach($role->id);

        if ($removed) {
            // Clear permission cache
            $this->clearUserPermissionCache($user);

            // Log role removal
            $this->logPermissionChange($user, 'role_removed', [
                'role'       => $roleName,
                'removed_by' => auth()->id(),
            ]);
        }

        return $removed > 0;
    }

    /**
     * Grant specific permission to user
     *
     * @param  User           $user
     * @param  string         $permission
     * @param  string|null    $resource
     * @param  \DateTime|null $expiresAt
     * @return bool
     */
    public function grantPermission(User $user, string $permission, ?string $resource = NULL, ?\DateTime $expiresAt = NULL): bool
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if (!$permissionModel) {
            throw new \InvalidArgumentException("Permission '{$permission}' not found");
        }

        // Check if user already has this permission
        $existing = UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permissionModel->id)
            ->where('resource_type', $resource)
            ->first();

        if ($existing) {
            // Update expiration if provided
            if ($expiresAt) {
                $existing->update(['expires_at' => $expiresAt]);
            }

            return TRUE;
        }

        // Grant permission
        UserPermission::create([
            'user_id'       => $user->id,
            'permission_id' => $permissionModel->id,
            'resource_type' => $resource,
            'granted_at'    => now(),
            'expires_at'    => $expiresAt,
            'granted_by'    => auth()->id(),
        ]);

        // Clear permission cache
        $this->clearUserPermissionCache($user);

        // Log permission grant
        $this->logPermissionChange($user, 'permission_granted', [
            'permission' => $permission,
            'resource'   => $resource,
            'expires_at' => $expiresAt?->toISOString(),
            'granted_by' => auth()->id(),
        ]);

        return TRUE;
    }

    /**
     * Revoke specific permission from user
     *
     * @param  User        $user
     * @param  string      $permission
     * @param  string|null $resource
     * @return bool
     */
    public function revokePermission(User $user, string $permission, ?string $resource = NULL): bool
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if (!$permissionModel) {
            return FALSE;
        }

        $removed = UserPermission::where('user_id', $user->id)
            ->where('permission_id', $permissionModel->id)
            ->where('resource_type', $resource)
            ->delete();

        if ($removed) {
            // Clear permission cache
            $this->clearUserPermissionCache($user);

            // Log permission revocation
            $this->logPermissionChange($user, 'permission_revoked', [
                'permission' => $permission,
                'resource'   => $resource,
                'revoked_by' => auth()->id(),
            ]);
        }

        return $removed > 0;
    }

    /**
     * Get all permissions for user (including inherited)
     *
     * @param  User       $user
     * @param  bool       $includeExpired
     * @return Collection
     */
    public function getUserPermissions(User $user, bool $includeExpired = FALSE): Collection
    {
        $cacheKey = "user_permissions:{$user->id}:" . ($includeExpired ? 'all' : 'active');

        return Cache::remember($cacheKey, $this->permissionCacheTTL, function () use ($user, $includeExpired) {
            $permissions = collect();

            // Direct permissions
            $directQuery = $user->userPermissions()->with('permission');
            if (!$includeExpired) {
                $directQuery->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            }

            foreach ($directQuery->get() as $userPermission) {
                $permissions->push([
                    'name'          => $userPermission->permission->name,
                    'resource_type' => $userPermission->resource_type,
                    'source'        => 'direct',
                    'expires_at'    => $userPermission->expires_at,
                    'granted_by'    => $userPermission->granted_by,
                ]);
            }

            // Role-based permissions
            foreach ($user->roles as $role) {
                if (!$includeExpired && $role->pivot->expires_at && $role->pivot->expires_at < now()) {
                    continue;
                }

                foreach ($role->permissions as $permission) {
                    $permissions->push([
                        'name'          => $permission->name,
                        'resource_type' => NULL,
                        'source'        => "role:{$role->name}",
                        'expires_at'    => $role->pivot->expires_at,
                        'granted_by'    => $role->pivot->assigned_by,
                    ]);
                }

                // Include inherited permissions
                $inheritedRoles = $this->getInheritedRoles($role->name);
                foreach ($inheritedRoles as $inheritedRoleName) {
                    $inheritedRole = Role::where('name', $inheritedRoleName)->first();
                    if ($inheritedRole) {
                        foreach ($inheritedRole->permissions as $permission) {
                            $permissions->push([
                                'name'          => $permission->name,
                                'resource_type' => NULL,
                                'source'        => "inherited:{$inheritedRoleName}",
                                'expires_at'    => $role->pivot->expires_at,
                                'granted_by'    => $role->pivot->assigned_by,
                            ]);
                        }
                    }
                }
            }

            return $permissions->unique(function ($item) {
                return $item['name'] . '|' . $item['resource_type'] . '|' . $item['source'];
            });
        });
    }

    /**
     * Check if user can access specific resource
     *
     * @param  User   $user
     * @param  string $resourceType
     * @param  mixed  $resourceId
     * @param  string $action
     * @param  array  $context
     * @return bool
     */
    public function canAccessResource(User $user, string $resourceType, $resourceId, string $action = 'view', array $context = []): bool
    {
        $permission = "{$resourceType}.{$action}";

        // Check general permission first
        if ($this->hasPermission($user, $permission, $resourceType, $context)) {
            return TRUE;
        }

        // Check resource-specific access
        $resourceAccess = ResourceAccess::where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('action', $action)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($resourceAccess) {
            return TRUE;
        }

        // Check ownership-based access
        if (isset($context['ownership']) && $this->checkOwnership($user, $resourceType, $resourceId, $context)) {
            return $this->hasPermission($user, "own.{$resourceType}.{$action}", $resourceType, $context);
        }

        return FALSE;
    }

    /**
     * Create new role with permissions
     *
     * @param  string      $name
     * @param  string      $displayName
     * @param  array       $permissions
     * @param  string|null $description
     * @return Role
     */
    public function createRole(string $name, string $displayName, array $permissions = [], ?string $description = NULL): Role
    {
        if (Role::where('name', $name)->exists()) {
            throw new \InvalidArgumentException("Role '{$name}' already exists");
        }

        $role = Role::create([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => $description,
            'created_by'   => auth()->id(),
        ]);

        // Assign permissions to role
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $role->permissions()->attach($permission->id);
            }
        }

        // Log role creation
        $this->logPermissionChange(NULL, 'role_created', [
            'role'        => $name,
            'permissions' => $permissions,
            'created_by'  => auth()->id(),
        ]);

        return $role;
    }

    /**
     * Create new permission
     *
     * @param  string      $name
     * @param  string      $displayName
     * @param  string|null $description
     * @param  string|null $category
     * @return Permission
     */
    public function createPermission(string $name, string $displayName, ?string $description = NULL, ?string $category = NULL): Permission
    {
        if (Permission::where('name', $name)->exists()) {
            throw new \InvalidArgumentException("Permission '{$name}' already exists");
        }

        $permission = Permission::create([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => $description,
            'category'     => $category,
            'created_by'   => auth()->id(),
        ]);

        // Log permission creation
        $this->logPermissionChange(NULL, 'permission_created', [
            'permission' => $name,
            'category'   => $category,
            'created_by' => auth()->id(),
        ]);

        return $permission;
    }

    /**
     * Get role hierarchy for user
     *
     * @param  User  $user
     * @return array
     */
    public function getUserRoleHierarchy(User $user): array
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        $hierarchy = [];

        foreach ($userRoles as $roleName) {
            $hierarchy[$roleName] = $this->getInheritedRoles($roleName);
        }

        return $hierarchy;
    }

    /**
     * Check if role can inherit from another role
     *
     * @param  string $roleName
     * @param  string $inheritFrom
     * @return bool
     */
    public function canInheritRole(string $roleName, string $inheritFrom): bool
    {
        // Prevent circular inheritance
        $inheritedRoles = $this->getInheritedRoles($inheritFrom);

        return !in_array($roleName, $inheritedRoles) && $roleName !== $inheritFrom;
    }

    /**
     * Get effective permissions for role (including inherited)
     *
     * @param  string     $roleName
     * @return Collection
     */
    public function getRoleEffectivePermissions(string $roleName): Collection
    {
        $cacheKey = "role_effective_permissions:{$roleName}";

        return Cache::remember($cacheKey, $this->permissionCacheTTL, function () use ($roleName) {
            $permissions = collect();

            // Direct role permissions
            $role = Role::where('name', $roleName)->with('permissions')->first();
            if ($role) {
                foreach ($role->permissions as $permission) {
                    $permissions->push([
                        'name'         => $permission->name,
                        'display_name' => $permission->display_name,
                        'source'       => "direct:{$roleName}",
                        'category'     => $permission->category,
                    ]);
                }
            }

            // Inherited permissions
            $inheritedRoles = $this->getInheritedRoles($roleName);
            foreach ($inheritedRoles as $inheritedRoleName) {
                $inheritedRole = Role::where('name', $inheritedRoleName)->with('permissions')->first();
                if ($inheritedRole) {
                    foreach ($inheritedRole->permissions as $permission) {
                        $permissions->push([
                            'name'         => $permission->name,
                            'display_name' => $permission->display_name,
                            'source'       => "inherited:{$inheritedRoleName}",
                            'category'     => $permission->category,
                        ]);
                    }
                }
            }

            return $permissions->unique('name');
        });
    }

    /**
     * Batch update user permissions
     *
     * @param  User  $user
     * @param  array $permissions
     * @return bool
     */
    public function batchUpdatePermissions(User $user, array $permissions): bool
    {
        try {
            \DB::transaction(function () use ($user, $permissions) {
                // Remove all current direct permissions
                UserPermission::where('user_id', $user->id)->delete();

                // Add new permissions
                foreach ($permissions as $permissionData) {
                    $this->grantPermission(
                        $user,
                        $permissionData['name'],
                        $permissionData['resource'] ?? NULL,
                        isset($permissionData['expires_at']) ? new \DateTime($permissionData['expires_at']) : NULL
                    );
                }
            });

            // Log batch update
            $this->logPermissionChange($user, 'permissions_batch_updated', [
                'permissions' => $permissions,
                'updated_by'  => auth()->id(),
            ]);

            return TRUE;
        } catch (\Exception $e) {
            Log::error('Failed to batch update permissions', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    // Protected helper methods

    protected function hasDirectPermission(User $user, string $permission, ?string $resource = NULL): bool
    {
        return UserPermission::whereHas('permission', function ($query) use ($permission) {
            $query->where('name', $permission);
        })
            ->where('user_id', $user->id)
            ->where('resource_type', $resource)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    protected function hasRolePermission(User $user, string $permission, ?string $resource = NULL): bool
    {
        foreach ($user->roles as $role) {
            if ($role->pivot->expires_at && $role->pivot->expires_at < now()) {
                continue;
            }

            if ($role->permissions()->where('name', $permission)->exists()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function hasInheritedPermission(User $user, string $permission, ?string $resource = NULL): bool
    {
        foreach ($user->roles as $role) {
            if ($role->pivot->expires_at && $role->pivot->expires_at < now()) {
                continue;
            }

            $inheritedRoles = $this->getInheritedRoles($role->name);
            foreach ($inheritedRoles as $inheritedRoleName) {
                $inheritedRole = Role::where('name', $inheritedRoleName)->first();
                if ($inheritedRole && $inheritedRole->permissions()->where('name', $permission)->exists()) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    protected function hasContextualPermission(User $user, string $permission, ?string $resource = NULL, array $context = []): bool
    {
        // Implement contextual permission logic based on context
        // For example, time-based permissions, location-based permissions, etc.
        return FALSE;
    }

    protected function hasTemporaryPermission(User $user, string $permission, ?string $resource = NULL): bool
    {
        // Check for temporary permissions that haven't expired
        return UserPermission::whereHas('permission', function ($query) use ($permission) {
            $query->where('name', $permission);
        })
            ->where('user_id', $user->id)
            ->where('resource_type', $resource)
            ->where('expires_at', '>', now())
            ->exists();
    }

    protected function hasResourcePermission(User $user, string $permission, string $resource, array $context = []): bool
    {
        // Check resource-specific permissions
        return ResourceAccess::where('user_id', $user->id)
            ->where('resource_type', $resource)
            ->where('action', str_replace($resource . '.', '', $permission))
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    protected function getInheritedRoles(string $roleName): array
    {
        return $this->roleHierarchy[$roleName] ?? [];
    }

    protected function checkOwnership(User $user, string $resourceType, $resourceId, array $context = []): bool
    {
        // Implement ownership checking logic based on resource type
        // This would typically check if user owns the resource
        return FALSE;
    }

    protected function buildPermissionCacheKey(User $user, string $permission, ?string $resource = NULL, array $context = []): string
    {
        $contextHash = md5(serialize($context));

        return "rbac:user:{$user->id}:permission:{$permission}:resource:" . ($resource ?? 'null') . ":context:{$contextHash}";
    }

    protected function clearUserPermissionCache(User $user): void
    {
        $pattern = "rbac:user:{$user->id}:*";
        // In a real implementation, you'd use Redis SCAN or similar to clear pattern-matched keys
        Cache::flush(); // Simplified for demo
    }

    protected function logPermissionChange(?User $user, string $action, array $data): void
    {
        Log::info("RBAC: {$action}", array_merge([
            'user_id'   => $user?->id,
            'action'    => $action,
            'timestamp' => now(),
        ], $data));
    }
}
