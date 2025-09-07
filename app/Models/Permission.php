<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Model for Advanced RBAC
 *
 * Represents granular permissions that can be assigned to roles and users
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'is_system_permission',
        'created_by',
    ];

    protected $casts = [
        'is_system_permission' => 'boolean',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /**
     * Roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withPivot(['granted_at', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * Users that have this permission directly
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot(['resource_type', 'granted_at', 'expires_at', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * User who created this permission
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get permission by name
     *
     * @param  string          $name
     * @return Permission|null
     */
    public static function findByName(string $name): ?Permission
    {
        return static::where('name', $name)->first();
    }

    /**
     * Check if permission is assigned to any role or user
     *
     * @return bool
     */
    public function isInUse(): bool
    {
        return $this->roles()->exists() || $this->users()->exists();
    }

    /**
     * Get all users who have this permission (directly or through roles)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllUsers(): \Illuminate\Support\Collection
    {
        $users = collect();

        // Users with direct permission
        $users = $users->merge($this->users);

        // Users with permission through roles
        foreach ($this->roles as $role) {
            $users = $users->merge($role->users);
        }

        return $users->unique('id');
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for system permissions
     */
    public function scopeSystemPermissions($query)
    {
        return $query->where('is_system_permission', TRUE);
    }

    /**
     * Scope for custom permissions
     */
    public function scopeCustomPermissions($query)
    {
        return $query->where('is_system_permission', FALSE);
    }

    /**
     * Get permissions grouped by category
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getByCategory(): \Illuminate\Support\Collection
    {
        return static::all()->groupBy('category');
    }
}
