<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model for Advanced RBAC
 *
 * Represents user roles with hierarchical inheritance and permission management
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system_role',
        'created_by',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * Users that have this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['assigned_at', 'expires_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Permissions assigned to this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withPivot(['granted_at', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * User who created this role
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if role has specific permission
     *
     * @param  string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Grant permission to role
     *
     * @param  Permission|string $permission
     * @return bool
     */
    public function grantPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (!$permission) {
            return FALSE;
        }

        if ($this->hasPermission($permission->name)) {
            return TRUE;
        }

        $this->permissions()->attach($permission->id, [
            'granted_at' => now(),
            'granted_by' => auth()->id(),
        ]);

        return TRUE;
    }

    /**
     * Revoke permission from role
     *
     * @param  Permission|string $permission
     * @return bool
     */
    public function revokePermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (!$permission) {
            return FALSE;
        }

        return $this->permissions()->detach($permission->id) > 0;
    }

    /**
     * Get all permissions including inherited ones
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $permissions = $this->permissions->pluck('name');

        // Add inherited permissions based on role hierarchy
        $hierarchy = config('rbac.role_hierarchy', []);
        $inheritedRoles = $hierarchy[$this->name] ?? [];

        foreach ($inheritedRoles as $inheritedRoleName) {
            $inheritedRole = static::where('name', $inheritedRoleName)->first();
            if ($inheritedRole) {
                $permissions = $permissions->merge($inheritedRole->permissions->pluck('name'));
            }
        }

        return $permissions->unique();
    }

    /**
     * Scope for system roles
     */
    public function scopeSystemRoles($query)
    {
        return $query->where('is_system_role', TRUE);
    }

    /**
     * Scope for custom roles
     */
    public function scopeCustomRoles($query)
    {
        return $query->where('is_system_role', FALSE);
    }
}
