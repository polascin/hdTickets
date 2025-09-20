<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates the roles and permissions tables with system-defined values
     * based on the RBAC configuration file.
     */
    public function run(): void
    {
        Log::info('Starting RBAC seeding process');

        DB::transaction(function (): void {
            $this->seedRoles();
            $this->seedPermissions();
            $this->assignPermissionsToRoles();
        });

        Log::info('RBAC seeding completed successfully');
    }

    /**
     * Seed system roles
     */
    private function seedRoles(): void
    {
        $systemRoles = config('rbac.system_roles', ['admin', 'agent', 'customer', 'scraper']);

        foreach ($systemRoles as $roleName) {
            $existingRole = Role::where('name', $roleName)->first();

            if (!$existingRole) {
                Role::create([
                    'name'           => $roleName,
                    'display_name'   => ucfirst($roleName),
                    'description'    => $this->getRoleDescription($roleName),
                    'is_system_role' => TRUE,
                ]);

                Log::info("Created system role: {$roleName}");
            } else {
                Log::info("System role already exists: {$roleName}");
            }
        }
    }

    /**
     * Seed system permissions
     */
    private function seedPermissions(): void
    {
        $systemPermissions = config('rbac.system_permissions', []);

        foreach ($systemPermissions as $permissionName => $config) {
            $existingPermission = Permission::where('name', $permissionName)->first();

            if (!$existingPermission) {
                Permission::create([
                    'name'         => $permissionName,
                    'display_name' => $config['display_name'] ?? ucwords(str_replace(['.', '_'], ' ', $permissionName)),
                    'description'  => $config['description'] ?? NULL,
                    'category'     => $config['category'] ?? 'general',
                ]);

                Log::info("Created permission: {$permissionName}");
            } else {
                Log::info("Permission already exists: {$permissionName}");
            }
        }
    }

    /**
     * Assign default permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        $defaultRolePermissions = config('rbac.default_role_permissions', []);

        foreach ($defaultRolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                Log::warning("Role not found: {$roleName}");

                continue;
            }

            // Clear existing permissions for this role
            $role->permissions()->detach();

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();

                if ($permission) {
                    // Check if the role already has this permission
                    if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
                        $role->permissions()->attach($permission->id, [
                            'granted_at' => now(),
                            'granted_by' => 1, // System user
                        ]);

                        Log::info("Assigned permission '{$permissionName}' to role '{$roleName}'");
                    }
                } else {
                    Log::warning("Permission not found: {$permissionName} for role: {$roleName}");
                }
            }
        }
    }

    /**
     * Get role descriptions
     */
    private function getRoleDescription(string $roleName): string
    {
        $descriptions = [
            'admin'    => 'System Administrator with full access to all features and settings',
            'agent'    => 'Ticket Agent with access to ticket operations, monitoring, and purchase decisions',
            'customer' => 'Customer with basic ticket monitoring and purchase access',
            'scraper'  => 'Automated scraper account for data collection (API-only access)',
        ];

        return $descriptions[$roleName] ?? "System role: {$roleName}";
    }
}
