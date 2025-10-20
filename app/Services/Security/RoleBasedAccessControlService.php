<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use InvalidArgumentException as UnauthorizedActionException; // Alias to satisfy previous usage without custom class

use function array_key_exists;
use function count;
use function in_array;

/**
 * Role-Based Access Control (RBAC) Service
 *
 * Manages user roles, permissions, and access control for the HD Tickets system.
 * Supports four primary roles: customer, agent, admin, scraper
 */
class RoleBasedAccessControlService
{
    // Role hierarchy (lower values have higher privileges)
    private const ROLE_HIERARCHY = [
        'admin'    => 1,
        'agent'    => 2,
        'customer' => 3,
        'scraper'  => 4,
    ];

    // Core permissions for each role
    private const ROLE_PERMISSIONS = [
        'admin' => [
            'system.*',
            'security.*',
            'users.*',
            'tickets.*',
            'purchases.*',
            'analytics.*',
            'audit.*',
            'configuration.*',
        ],
        'agent' => [
            'tickets.view',
            'tickets.search',
            'tickets.purchase',
            'purchases.view',
            'purchases.manage',
            'analytics.basic',
            'profile.manage',
        ],
        'customer' => [
            'tickets.view',
            'tickets.search',
            'tickets.purchase.limited',
            'purchases.view.own',
            'profile.manage',
        ],
        'scraper' => [
            'system.scrape',
            'tickets.create',
            'tickets.update.automated',
        ],
    ];

    // Restricted actions that require special handling
    private const RESTRICTED_ACTIONS = [
        'security.config.modify',
        'users.role.change',
        'system.maintenance',
        'audit.export',
        'purchases.unlimited',
    ];

    public function __construct(private SecurityMonitoringService $securityMonitoring)
    {
    }

    /**
     * Check if user has permission for a specific action
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // Cache key for user permissions
        $cacheKey = "user_permissions_{$user->id}";

        $userPermissions = Cache::remember($cacheKey, 300, fn (): array => $this->getUserPermissions($user));

        // Check direct permission match
        if (in_array($permission, $userPermissions, TRUE)) {
            return TRUE;
        }

        // Check wildcard permissions
        foreach ($userPermissions as $userPermission) {
            if (str_ends_with((string) $userPermission, '*')) {
                $prefix = substr((string) $userPermission, 0, -1);
                if (str_starts_with($permission, $prefix)) {
                    return TRUE;
                }
            }
        }

        // Log permission denial for security monitoring
        $this->securityMonitoring->recordSecurityEvent(
            'permission_denied',
            'Permission denied for user',
        );

        return FALSE;
    }

    /**
     * Check if user can access a specific resource
     */
    public function canAccess(User $user, string $resource, array $context = []): bool
    {
        // Special handling for system resources
        if (str_starts_with($resource, 'system.')) {
            return $this->canAccessSystemResource($user, $resource);
        }

        // Special handling for security resources
        if (str_starts_with($resource, 'security.')) {
            return $this->canAccessSecurityResource($user, $resource, $context);
        }

        // Special handling for ticket purchases
        if (str_starts_with($resource, 'tickets.purchase')) {
            return $this->canAccessTicketPurchase($user, $context);
        }

        // Default permission check
        return $this->hasPermission($user, $resource);
    }

    /**
     * Get all permissions for a user based on their role
     */
    public function getUserPermissions(User $user): array
    {
        $basePermissions = self::ROLE_PERMISSIONS[$user->role] ?? [];

        // Add dynamic permissions based on user state
        $dynamicPermissions = $this->getDynamicPermissions($user);

        return array_merge($basePermissions, $dynamicPermissions);
    }

    /**
     * Check if user can perform admin actions
     */
    public function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Check if user can perform agent actions
     */
    public function isAgent(User $user): bool
    {
        return in_array($user->role, ['admin', 'agent'], TRUE);
    }

    /**
     * Check if user is a paying customer
     */
    public function isCustomer(User $user): bool
    {
        return $user->role === 'customer';
    }

    /**
     * Check if user is a scraper (system user)
     */
    public function isScraper(User $user): bool
    {
        return $user->role === 'scraper';
    }

    /**
     * Check if user has higher privileges than target user
     */
    public function hasHigherPrivileges(User $user, User $targetUser): bool
    {
        $userLevel = self::ROLE_HIERARCHY[$user->role] ?? 999;
        $targetLevel = self::ROLE_HIERARCHY[$targetUser->role] ?? 999;

        return $userLevel < $targetLevel;
    }

    /**
     * Get users by role with pagination
     */
    /**
     * @return Collection<int,User>
     */
    public function getUsersByRole(string $role, int $perPage = 15): Collection
    {
        if (! array_key_exists($role, self::ROLE_PERMISSIONS)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }

        $paginator = User::where('role', $role)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Convert paginator items to collection to satisfy return type while preserving side effects
        return collect($paginator->items());
    }

    /**
     * Change user role (admin only)
     */
    public function changeUserRole(User $adminUser, User $targetUser, string $newRole): bool
    {
        // Only admins can change roles
        if (! $this->isAdmin($adminUser)) {
            $this->securityMonitoring->recordSecurityEvent(
                'unauthorized_role_change',
                'Unauthorized attempt to change user role',
            );

            return FALSE;
        }

        // Validate new role
        if (! array_key_exists($newRole, self::ROLE_PERMISSIONS)) {
            throw new InvalidArgumentException("Invalid role: {$newRole}");
        }

        // Admins cannot demote themselves
        if ($adminUser->id === $targetUser->id && $newRole !== 'admin') {
            return FALSE;
        }

        $oldRole = $targetUser->role;

        DB::transaction(function () use ($targetUser, $newRole): void {
            // Update user role
            $targetUser->update(['role' => $newRole]);

            // Clear cached permissions
            Cache::forget("user_permissions_{$targetUser->id}");

            // Log role change
            $this->securityMonitoring->recordSecurityEvent(
                'role_changed',
                'User role changed by admin',
            );
        });

        return TRUE;
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics(): array
    {
        $stats = [];

        foreach (array_keys(self::ROLE_PERMISSIONS) as $role) {
            $stats[$role] = [
                'count'        => User::where('role', $role)->count(),
                'active_count' => User::where('role', $role)
                    ->where('last_login_at', '>=', Carbon::now()->subDays(30))
                    ->count(),
                'permissions_count' => count(self::ROLE_PERMISSIONS[$role]),
            ];
        }

        return $stats;
    }

    /**
     * Validate user access to ticket purchase functionality
     */
    public function validateTicketPurchaseAccess(User $user, array $purchaseContext = []): array
    {
        $validation = [
            'can_purchase' => FALSE,
            'reasons'      => [],
            'limitations'  => [],
        ];

        // Scrapers cannot purchase tickets
        if ($this->isScraper($user)) {
            $validation['reasons'][] = 'Scraper accounts cannot purchase tickets';

            return $validation;
        }

        // Admins and agents have unlimited access
        if ($this->isAdmin($user) || $this->isAgent($user)) {
            $validation['can_purchase'] = TRUE;
            $validation['limitations'][] = 'Unlimited ticket access';

            return $validation;
        }

        // Customer validation
        if ($this->isCustomer($user)) {
            return $this->validateCustomerTicketAccess($user, $purchaseContext);
        }

        $validation['reasons'][] = 'Invalid user role for ticket purchases';

        return $validation;
    }

    /**
     * Get security permissions summary for user
     */
    public function getSecurityPermissionsSummary(User $user): array
    {
        return [
            'role'               => $user->role,
            'role_level'         => self::ROLE_HIERARCHY[$user->role] ?? 999,
            'permissions'        => $this->getUserPermissions($user),
            'can_admin'          => $this->isAdmin($user),
            'can_agent'          => $this->isAgent($user),
            'restricted_actions' => $this->getRestrictedActions($user),
            'security_score'     => $this->calculateSecurityScore($user),
        ];
    }

    /**
     * Bulk role assignment (admin only)
     */
    public function bulkRoleAssignment(User $adminUser, array $userIds, string $newRole): array
    {
        if (! $this->isAdmin($adminUser)) {
            throw new UnauthorizedActionException('Only admins can perform bulk role assignments');
        }

        if (! array_key_exists($newRole, self::ROLE_PERMISSIONS)) {
            throw new InvalidArgumentException("Invalid role: {$newRole}");
        }

        $results = [
            'success' => [],
            'failed'  => [],
            'skipped' => [],
        ];

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            // Skip if trying to change admin's own role to non-admin
            if ($adminUser->id === $user->id && $newRole !== 'admin') {
                $results['skipped'][] = [
                    'user_id' => $user->id,
                    'reason'  => 'Cannot change own admin role',
                ];

                continue;
            }

            try {
                if ($this->changeUserRole($adminUser, $user, $newRole)) {
                    $results['success'][] = $user->id;
                } else {
                    $results['failed'][] = [
                        'user_id' => $user->id,
                        'reason'  => 'Role change failed',
                    ];
                }
            } catch (Exception $e) {
                $results['failed'][] = [
                    'user_id' => $user->id,
                    'reason'  => $e->getMessage(),
                ];
            }
        }

        // Log bulk operation
        $this->securityMonitoring->recordSecurityEvent(
            'bulk_role_assignment',
            'Bulk role assignment performed',
        );

        return $results;
    }

    /**
     * Check system resource access
     */
    private function canAccessSystemResource(User $user, string $resource): bool
    {
        // Only admins can access most system resources
        if (! $this->isAdmin($user) && ! str_contains($resource, 'scrape')) {
            return FALSE;
        }

        // Scrapers can only access scraping resources
        if ($this->isScraper($user)) {
            return str_contains($resource, 'scrape');
        }

        return $this->hasPermission($user, $resource);
    }

    /**
     * Check security resource access
     */
    private function canAccessSecurityResource(User $user, string $resource, array $context): bool
    {
        // Only admins can access security resources
        if (! $this->isAdmin($user)) {
            return FALSE;
        }

        // Additional validation for critical security actions
        if (in_array($resource, self::RESTRICTED_ACTIONS, TRUE)) {
            return $this->validateCriticalAction($user, $resource, $context);
        }

        return $this->hasPermission($user, $resource);
    }

    /**
     * Check ticket purchase access
     */
    private function canAccessTicketPurchase(User $user, array $context): bool
    {
        $validation = $this->validateTicketPurchaseAccess($user, $context);

        return $validation['can_purchase'];
    }

    /**
     * Get dynamic permissions based on user state
     *
     * @return string[]
     */
    private function getDynamicPermissions(User $user): array
    {
        $permissions = [];

        // Add subscription-based permissions for customers
        if ($this->isCustomer($user)) {
            $permissions[] = $user->hasActiveSubscription() ? 'tickets.purchase.full' : 'tickets.purchase.trial';
        }

        // Add MFA-based permissions
        if ($user->mfa_enabled) {
            $permissions[] = 'security.mfa.enabled';
        }

        return $permissions;
    }

    /**
     * Validate customer ticket access
     */
    private function validateCustomerTicketAccess(User $user, array $context): array
    {
        $validation = [
            'can_purchase' => FALSE,
            'reasons'      => [],
            'limitations'  => [],
        ];

        // Check if within free access period
        $withinFreeAccess = $user->created_at->diffInDays(now()) <= config('subscription.free_access_days', 7);

        if (! $withinFreeAccess && ! $user->hasActiveSubscription()) {
            $validation['reasons'][] = 'Active subscription required';

            return $validation;
        }

        // Check monthly ticket limits
        $monthlyUsage = $user->getMonthlyTicketUsage();
        $monthlyLimit = $user->getMonthlyTicketLimit();

        $requestedQuantity = $context['quantity'] ?? 1;

        if (($monthlyUsage + $requestedQuantity) > $monthlyLimit) {
            $validation['reasons'][] = 'Would exceed monthly ticket limit';
            $validation['limitations'][] = "Monthly limit: {$monthlyLimit}, Current usage: {$monthlyUsage}";

            return $validation;
        }

        $validation['can_purchase'] = TRUE;
        $validation['limitations'][] = 'Remaining tickets this month: ' . ($monthlyLimit - $monthlyUsage);

        return $validation;
    }

    /**
     * Get restricted actions for user
     */
    private function getRestrictedActions(User $user): array
    {
        $restricted = [];

        foreach (self::RESTRICTED_ACTIONS as $action) {
            if (! $this->hasPermission($user, $action)) {
                $restricted[] = $action;
            }
        }

        return $restricted;
    }

    /**
     * Calculate security score for user
     */
    private function calculateSecurityScore(User $user): int
    {
        $score = 0;

        // Base score by role
        $roleScores = [
            'admin'    => 100,
            'agent'    => 80,
            'customer' => 60,
            'scraper'  => 40,
        ];

        $score += $roleScores[$user->role] ?? 0;

        // MFA bonus
        if ($user->mfa_enabled) {
            $score += 20;
        }

        // Email verification bonus
        if ($user->email_verified_at) {
            $score += 10;
        }

        // Phone verification bonus
        if ($user->phone_verified_at) {
            $score += 10;
        }

        // Active subscription bonus (for customers)
        if ($this->isCustomer($user) && $user->hasActiveSubscription()) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Validate critical security actions
     */
    private function validateCriticalAction(User $user, string $action, array $context): bool
    {
        // Require MFA for critical actions
        if (! $user->mfa_enabled) {
            return FALSE;
        }

        // Additional validation based on action type
        return match ($action) {
            'security.config.modify' => $this->isAdmin($user),
            'users.role.change'      => $this->isAdmin($user) && isset($context['target_user']),
            'system.maintenance'     => $this->isAdmin($user),
            default                  => $this->hasPermission($user, $action),
        };
    }
}
