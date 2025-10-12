<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

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
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Check if permission is assigned to any role or user
     */
    public function isInUse(): bool
    {
        if ($this->roles()->exists()) {
            return true;
        }

        return $this->users()->exists();
    }

    /**
     * Get all users who have this permission (directly or through roles)
     */
    public function getAllUsers(): Collection
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
     *
     * @param mixed $query
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for system permissions
     *
     * @param mixed $query
     */
    public function scopeSystemPermissions($query)
    {
        return $query->where('is_system_permission', true);
    }

    /**
     * Scope for custom permissions
     *
     * @param mixed $query
     */
    public function scopeCustomPermissions($query)
    {
        return $query->where('is_system_permission', false);
    }

    /**
     * Get permissions grouped by category
     */
    public static function getByCategory(): Collection
    {
        return static::all()->groupBy('category');
    }

    protected function casts(): array
    {
        return [
            'is_system_permission' => 'boolean',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
        ];
    }
}
