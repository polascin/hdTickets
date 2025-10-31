<?php declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use App\Services\SecurityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function in_array;

class RBACService
{
    /** Permission definitions with hierarchical structure */
    public const PERMISSIONS = [
        // System Management
        'system.manage' => [
            'description' => 'Full system management access',
            'category'    => 'system',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'system.view' => [
            'description' => 'View system information',
            'category'    => 'system',
            'level'       => 'admin',
            'inherits'    => [],
        ],

        // User Management
        'users.manage' => [
            'description' => 'Full user management',
            'category'    => 'users',
            'level'       => 'admin',
            'inherits'    => ['users.view', 'users.create', 'users.update', 'users.delete'],
        ],
        'users.view' => [
            'description' => 'View users',
            'category'    => 'users',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'users.create' => [
            'description' => 'Create new users',
            'category'    => 'users',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'users.update' => [
            'description' => 'Update user information',
            'category'    => 'users',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'users.delete' => [
            'description' => 'Delete users',
            'category'    => 'users',
            'level'       => 'admin',
            'inherits'    => [],
        ],

        // Ticket Management
        'tickets.manage' => [
            'description' => 'Full ticket management',
            'category'    => 'tickets',
            'level'       => 'agent',
            'inherits'    => ['tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.purchase'],
        ],
        'tickets.view' => [
            'description' => 'View tickets',
            'category'    => 'tickets',
            'level'       => 'customer',
            'inherits'    => [],
        ],
        'tickets.create' => [
            'description' => 'Create ticket alerts',
            'category'    => 'tickets',
            'level'       => 'customer',
            'inherits'    => [],
        ],
        'tickets.update' => [
            'description' => 'Update ticket information',
            'category'    => 'tickets',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'tickets.delete' => [
            'description' => 'Delete tickets',
            'category'    => 'tickets',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'tickets.purchase' => [
            'description' => 'Make ticket purchases',
            'category'    => 'tickets',
            'level'       => 'agent',
            'inherits'    => ['tickets.view'],
        ],

        // Platform Management
        'platforms.manage' => [
            'description' => 'Manage scraping platforms',
            'category'    => 'platforms',
            'level'       => 'admin',
            'inherits'    => ['platforms.view', 'platforms.configure', 'platforms.monitor'],
        ],
        'platforms.view' => [
            'description' => 'View platform information',
            'category'    => 'platforms',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'platforms.configure' => [
            'description' => 'Configure platform settings',
            'category'    => 'platforms',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'platforms.monitor' => [
            'description' => 'Monitor platform performance',
            'category'    => 'platforms',
            'level'       => 'admin',
            'inherits'    => [],
        ],

        // Scraping Operations
        'scraping.manage' => [
            'description' => 'Full scraping management',
            'category'    => 'scraping',
            'level'       => 'admin',
            'inherits'    => ['scraping.view', 'scraping.execute', 'scraping.configure'],
        ],
        'scraping.view' => [
            'description' => 'View scraping results',
            'category'    => 'scraping',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'scraping.execute' => [
            'description' => 'Execute scraping operations',
            'category'    => 'scraping',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'scraping.configure' => [
            'description' => 'Configure scraping settings',
            'category'    => 'scraping',
            'level'       => 'admin',
            'inherits'    => [],
        ],

        // Financial Operations
        'finance.view' => [
            'description' => 'View financial information',
            'category'    => 'finance',
            'level'       => 'admin',
            'inherits'    => [],
        ],
        'finance.transactions' => [
            'description' => 'Process financial transactions',
            'category'    => 'finance',
            'level'       => 'admin',
            'inherits'    => ['finance.view'],
        ],

        // Analytics and Reporting
        'analytics.view' => [
            'description' => 'View analytics dashboard',
            'category'    => 'analytics',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'analytics.advanced' => [
            'description' => 'Access advanced analytics',
            'category'    => 'analytics',
            'level'       => 'admin',
            'inherits'    => ['analytics.view'],
        ],
        'reports.generate' => [
            'description' => 'Generate reports',
            'category'    => 'reports',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'reports.export' => [
            'description' => 'Export reports',
            'category'    => 'reports',
            'level'       => 'agent',
            'inherits'    => ['reports.generate'],
        ],

        // API Access
        'api.access' => [
            'description' => 'Basic API access',
            'category'    => 'api',
            'level'       => 'customer',
            'inherits'    => [],
        ],
        'api.admin' => [
            'description' => 'Administrative API access',
            'category'    => 'api',
            'level'       => 'admin',
            'inherits'    => ['api.access'],
        ],

        // Bulk Operations
        'bulk.operations' => [
            'description' => 'Perform bulk operations',
            'category'    => 'bulk',
            'level'       => 'agent',
            'inherits'    => [],
        ],
        'bulk.delete' => [
            'description' => 'Bulk delete operations',
            'category'    => 'bulk',
            'level'       => 'admin',
            'inherits'    => ['bulk.operations'],
        ],
    ];

    /** Role definitions with default permissions */
    public const ROLES = [
        'admin' => [
            'description'         => 'System Administrator',
            'default_permissions' => [
                'system.manage',
                'users.manage',
                'tickets.manage',
                'platforms.manage',
                'scraping.manage',
                'finance.view',
                'finance.transactions',
                'analytics.advanced',
                'reports.export',
                'api.admin',
                'bulk.delete',
            ],
        ],
        'agent' => [
            'description'         => 'Ticket Agent',
            'default_permissions' => [
                'tickets.manage',
                'platforms.view',
                'scraping.view',
                'scraping.execute',
                'analytics.view',
                'reports.generate',
                'api.access',
                'bulk.operations',
            ],
        ],
        'customer' => [
            'description'         => 'Customer',
            'default_permissions' => [
                'tickets.view',
                'tickets.create',
                'api.access',
            ],
        ],
        'scraper' => [
            'description'         => 'Scraper Bot',
            'default_permissions' => [
                'scraping.execute',
            ],
        ],
    ];

    public function __construct(protected SecurityService $securityService)
    {
    }

    /**
     * Check if user has specific permission
     */
    /**
     * Check if has  permission
     */
    public function hasPermission(User $user, string $permission, array $context = []): bool
    {
        // Cache key for user permissions
        $cacheKey = "user_permissions:{$user->id}";

        // Get user permissions from cache or calculate
        $userPermissions = Cache::remember($cacheKey, 3600, fn (): array => $this->calculateUserPermissions($user));

        // Check direct permission
        if (in_array($permission, $userPermissions, TRUE)) {
            return TRUE;
        }

        // Check inherited permissions
        if ($this->hasInheritedPermission($permission, $userPermissions)) {
            return TRUE;
        }

        // Check resource-based permissions
        if ($this->hasResourcePermission($user, $permission, $context)) {
            return TRUE;
        }

        // Log permission check
        $this->logPermissionCheck($user, $permission, FALSE, $context);

        return FALSE;
    }

    /**
     * Check multiple permissions (user must have ALL)
     */
    /**
     * Check if has  all permissions
     */
    public function hasAllPermissions(User $user, array $permissions, array $context = []): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission, $context)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check multiple permissions (user must have ANY)
     */
    /**
     * Check if has  any permission
     */
    public function hasAnyPermission(User $user, array $permissions, array $context = []): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission, $context)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Grant permission to user
     */
    /**
     * GrantPermission
     */
    public function grantPermission(User $user, string $permission, ?User $grantedBy = NULL): bool
    {
        if (!$this->isValidPermission($permission)) {
            return FALSE;
        }

        $customPermissions = $user->custom_permissions ?? [];

        if (!in_array($permission, $customPermissions, TRUE)) {
            $customPermissions[] = $permission;
            $user->update(['custom_permissions' => $customPermissions]);

            // Clear cache
            Cache::forget("user_permissions:{$user->id}");

            // Log permission grant
            $this->securityService->logSecurityActivity('Permission granted', [
                'permission'     => $permission,
                'target_user_id' => $user->id,
                'granted_by'     => $grantedBy?->id,
            ], $user);
        }

        return TRUE;
    }

    /**
     * Revoke permission from user
     */
    /**
     * RevokePermission
     */
    public function revokePermission(User $user, string $permission, ?User $revokedBy = NULL): bool
    {
        $customPermissions = $user->custom_permissions ?? [];
        $key = array_search($permission, $customPermissions, TRUE);

        if ($key !== FALSE) {
            unset($customPermissions[$key]);
            $user->update(['custom_permissions' => array_values($customPermissions)]);

            // Clear cache
            Cache::forget("user_permissions:{$user->id}");

            // Log permission revocation
            $this->securityService->logSecurityActivity('Permission revoked', [
                'permission'     => $permission,
                'target_user_id' => $user->id,
                'revoked_by'     => $revokedBy?->id,
            ], $user);
        }

        return TRUE;
    }

    /**
     * Assign role to user
     */
    /**
     * AssignRole
     */
    public function assignRole(User $user, string $role, ?User $assignedBy = NULL): bool
    {
        if (!$this->isValidRole($role)) {
            return FALSE;
        }

        $oldRole = $user->role;
        $user->update(['role' => $role]);

        // Clear cache
        Cache::forget("user_permissions:{$user->id}");

        // Log role assignment
        $this->securityService->logSecurityActivity('Role assigned', [
            'old_role'       => $oldRole,
            'new_role'       => $role,
            'target_user_id' => $user->id,
            'assigned_by'    => $assignedBy?->id,
        ], $user);

        return TRUE;
    }

    /**
     * Get user's effective permissions
     */
    /**
     * Get  user permissions
     */
    public function getUserPermissions(User $user): array
    {
        $cacheKey = "user_permissions:{$user->id}";

        return Cache::remember($cacheKey, 3600, fn (): array => $this->calculateUserPermissions($user));
    }

    /**
     * Get permissions for a role
     */
    /**
     * Get  role permissions
     */
    public function getRolePermissions(string $role): array
    {
        if (!isset(self::ROLES[$role])) {
            return [];
        }

        $permissions = self::ROLES[$role]['default_permissions'];
        $expandedPermissions = [];

        foreach ($permissions as $permission) {
            $expandedPermissions[] = $permission;
            $expandedPermissions = array_merge($expandedPermissions, $this->getInheritedPermissions($permission));
        }

        return array_unique($expandedPermissions);
    }

    /**
     * Create dynamic role
     */
    /**
     * CreateDynamicRole
     */
    public function createDynamicRole(string $roleName, array $permissions, User $createdBy): bool
    {
        $dynamicRoles = Cache::get('dynamic_roles', []);

        $dynamicRoles[$roleName] = [
            'description' => "Dynamic role: {$roleName}",
            'permissions' => $permissions,
            'created_by'  => $createdBy->id,
            'created_at'  => now()->toISOString(),
        ];

        Cache::put('dynamic_roles', $dynamicRoles, now()->addDays(30));

        // Log dynamic role creation
        $this->securityService->logSecurityActivity('Dynamic role created', [
            'role_name'   => $roleName,
            'permissions' => $permissions,
            'created_by'  => $createdBy->id,
        ], $createdBy);

        return TRUE;
    }

    /**
     * Check resource-based permissions
     *
     * @param mixed|null $resourceId
     */
    /**
     * CheckResourcePermission
     *
     * @param mixed $resourceId
     */
    public function checkResourcePermission(User $user, string $resource, string $action, $resourceId = NULL): bool
    {
        $permission = "{$resource}.{$action}";

        // Check basic permission first
        if (!$this->hasPermission($user, $permission)) {
            return FALSE;
        }

        // Resource-specific logic
        return match ($resource) {
            'tickets'   => $this->checkTicketPermission($user, $action, $resourceId),
            'users'     => $this->checkUserPermission($user, $action, $resourceId),
            'platforms' => $this->checkPlatformPermission($user, $action, $resourceId),
            default     => TRUE,
        };
    }

    /**
     * Get permission matrix for all roles
     */
    /**
     * Get  permission matrix
     */
    public function getPermissionMatrix(): array
    {
        $matrix = [];

        foreach (self::ROLES as $role => $roleData) {
            $matrix[$role] = [
                'description' => $roleData['description'],
                'permissions' => $this->getRolePermissions($role),
            ];
        }

        return $matrix;
    }

    /**
     * Validate permission structure
     */
    /**
     * ValidatePermissionStructure
     */
    public function validatePermissionStructure(): array
    {
        $issues = [];

        foreach (self::PERMISSIONS as $permission => $config) {
            // Check for circular dependencies
            if ($this->hasCircularDependency($permission)) {
                $issues[] = "Circular dependency detected in permission: {$permission}";
            }

            // Check if inherited permissions exist
            foreach ($config['inherits'] as $inherited) {
                if (!isset(self::PERMISSIONS[$inherited])) {
                    $issues[] = "Permission {$permission} inherits non-existent permission: {$inherited}";
                }
            }
        }

        return $issues;
    }

    /**
     * Clear user permissions cache
     */
    /**
     * ClearUserPermissionsCache
     */
    public function clearUserPermissionsCache(User $user): void
    {
        Cache::forget("user_permissions:{$user->id}");
    }

    /**
     * Clear all permissions cache
     */
    /**
     * ClearAllPermissionsCache
     */
    public function clearAllPermissionsCache(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            Cache::forget("user_permissions:{$user->id}");
        }
    }

    /**
     * Calculate user's effective permissions
     */
    /**
     * CalculateUserPermissions
     */
    protected function calculateUserPermissions(User $user): array
    {
        $permissions = [];

        // Add role-based permissions
        $rolePermissions = $this->getRolePermissions($user->role);
        $permissions = array_merge($permissions, $rolePermissions);

        // Add custom permissions
        $customPermissions = $user->custom_permissions ?? [];
        $permissions = array_merge($permissions, $customPermissions);

        // Add dynamic role permissions if applicable
        $dynamicRoles = Cache::get('dynamic_roles', []);
        if (isset($dynamicRoles[$user->role])) {
            $permissions = array_merge($permissions, $dynamicRoles[$user->role]['permissions']);
        }

        // Remove duplicates and return
        return array_unique($permissions);
    }

    /**
     * Check if user has inherited permission
     */
    /**
     * Check if has  inherited permission
     */
    protected function hasInheritedPermission(string $permission, array $userPermissions): bool
    {
        foreach ($userPermissions as $userPermission) {
            $inheritedPermissions = $this->getInheritedPermissions($userPermission);
            if (in_array($permission, $inheritedPermissions, TRUE)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get inherited permissions for a permission
     */
    /**
     * Get  inherited permissions
     */
    protected function getInheritedPermissions(string $permission): array
    {
        if (!isset(self::PERMISSIONS[$permission])) {
            return [];
        }

        $inherited = self::PERMISSIONS[$permission]['inherits'];
        $allInherited = $inherited;

        // Recursively get inherited permissions
        foreach ($inherited as $inheritedPermission) {
            $allInherited = array_merge($allInherited, $this->getInheritedPermissions($inheritedPermission));
        }

        return array_unique($allInherited);
    }

    /**
     * Check resource-based permissions
     */
    /**
     * Check if has  resource permission
     */
    protected function hasResourcePermission(User $user, string $permission, array $context): bool
    {
        // Resource-specific permission logic
        if (isset($context['resource_type'], $context['resource_id'])) {
            return $this->checkResourcePermission(
                $user,
                $context['resource_type'],
                explode('.', $permission)[1] ?? 'view',
                $context['resource_id'],
            );
        }

        return FALSE;
    }

    /**
     * Check ticket-specific permissions
     *
     * @param mixed|null $ticketId
     */
    /**
     * CheckTicketPermission
     *
     * @param mixed $ticketId
     */
    protected function checkTicketPermission(User $user, string $action, $ticketId = NULL): bool
    {
        // Agents can manage all tickets, customers only their own
        if ($user->isAgent() || $user->isAdmin()) {
            return TRUE;
        }

        return $user->isCustomer() && $ticketId;
        // Check if ticket belongs to user (implement based on your ticket model)
        // Placeholder
    }

    /**
     * Check user-specific permissions
     *
     * @param mixed|null $targetUserId
     */
    /**
     * CheckUserPermission
     *
     * @param mixed $targetUserId
     */
    protected function checkUserPermission(User $user, string $action, $targetUserId = NULL): bool
    {
        // Admins can manage all users
        if ($user->isAdmin()) {
            return TRUE;
        }

        // Users can only manage themselves
        return $targetUserId && (int) $targetUserId === $user->id && in_array($action, ['view', 'update'], TRUE);
    }

    /**
     * Check platform-specific permissions
     *
     * @param mixed|null $platformId
     */
    /**
     * CheckPlatformPermission
     *
     * @param mixed $platformId
     */
    protected function checkPlatformPermission(User $user, string $action, $platformId = NULL): bool
    {
        // Only admins can manage platforms
        return $user->isAdmin();
    }

    /**
     * Check if permission is valid
     */
    /**
     * Check if  valid permission
     */
    protected function isValidPermission(string $permission): bool
    {
        return isset(self::PERMISSIONS[$permission]);
    }

    /**
     * Check if role is valid
     */
    /**
     * Check if  valid role
     */
    protected function isValidRole(string $role): bool
    {
        return isset(self::ROLES[$role]) || Cache::has("dynamic_roles.{$role}");
    }

    /**
     * Check for circular dependencies in permissions
     */
    /**
     * Check if has  circular dependency
     */
    protected function hasCircularDependency(string $permission, array $visited = []): bool
    {
        if (in_array($permission, $visited, TRUE)) {
            return TRUE;
        }

        if (!isset(self::PERMISSIONS[$permission])) {
            return FALSE;
        }

        $visited[] = $permission;

        foreach (self::PERMISSIONS[$permission]['inherits'] as $inherited) {
            if ($this->hasCircularDependency($inherited, $visited)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Log permission check
     */
    /**
     * LogPermissionCheck
     */
    protected function logPermissionCheck(User $user, string $permission, bool $granted, array $context): void
    {
        if (config('security.logging.log_permission_checks', FALSE)) {
            $this->securityService->logSecurityActivity('Permission check', [
                'permission' => $permission,
                'granted'    => $granted,
                'context'    => $context,
            ], $user);
        }
    }
}
