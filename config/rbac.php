<?php declare(strict_types=1);

use App\Domain\Event\Models\SportsEvent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Control Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the advanced RBAC system
    | including role hierarchy, permission caching, and system settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchy
    |--------------------------------------------------------------------------
    |
    | Define the hierarchical relationships between roles. Each role can
    | inherit permissions from the roles listed in its array.
    |
    */
    'role_hierarchy' => [
        'admin'    => ['agent', 'customer'],
        'agent'    => ['customer'],
        'customer' => [],
        'scraper'  => [], // Scrapers have no inheritance and minimal permissions
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure how permissions are cached to improve performance.
    |
    */
    'cache_ttl'    => env('RBAC_CACHE_TTL', 3600), // 1 hour
    'cache_prefix' => 'rbac',

    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    |
    | Define roles that are managed by the system and cannot be deleted.
    |
    */
    'system_roles' => [
        'admin',
        'agent',
        'customer',
        'scraper',
    ],

    /*
    |--------------------------------------------------------------------------
    | System Permissions Categories
    |--------------------------------------------------------------------------
    |
    | Predefined permission categories for better organization.
    |
    */
    'permission_categories' => [
        'system'     => 'System Administration',
        'users'      => 'User Management',
        'tickets'    => 'Ticket Management',
        'events'     => 'Sports Events',
        'monitoring' => 'Monitoring & Analytics',
        'purchases'  => 'Purchase Management',
        'scraping'   => 'Scraping Operations',
        'reports'    => 'Reports & Analytics',
        'api'        => 'API Access',
        'security'   => 'Security Management',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default System Permissions
    |--------------------------------------------------------------------------
    |
    | Base permissions that will be created during system setup.
    |
    */
    'system_permissions' => [
        // System Administration
        'system.manage'      => ['category' => 'system', 'display_name' => 'Manage System Settings'],
        'system.config'      => ['category' => 'system', 'display_name' => 'Configure System'],
        'system.logs'        => ['category' => 'system', 'display_name' => 'View System Logs'],
        'system.maintenance' => ['category' => 'system', 'display_name' => 'System Maintenance'],

        // User Management
        'users.create'      => ['category' => 'users', 'display_name' => 'Create Users'],
        'users.read'        => ['category' => 'users', 'display_name' => 'View Users'],
        'users.update'      => ['category' => 'users', 'display_name' => 'Update Users'],
        'users.delete'      => ['category' => 'users', 'display_name' => 'Delete Users'],
        'users.roles'       => ['category' => 'users', 'display_name' => 'Manage User Roles'],
        'users.permissions' => ['category' => 'users', 'display_name' => 'Manage User Permissions'],

        // Ticket Management (Sports Event Tickets)
        'tickets.create'   => ['category' => 'tickets', 'display_name' => 'Create Tickets'],
        'tickets.read'     => ['category' => 'tickets', 'display_name' => 'View Tickets'],
        'tickets.update'   => ['category' => 'tickets', 'display_name' => 'Update Tickets'],
        'tickets.delete'   => ['category' => 'tickets', 'display_name' => 'Delete Tickets'],
        'tickets.purchase' => ['category' => 'tickets', 'display_name' => 'Purchase Tickets'],
        'tickets.assign'   => ['category' => 'tickets', 'display_name' => 'Assign Tickets'],

        // Sports Events
        'events.create' => ['category' => 'events', 'display_name' => 'Create Events'],
        'events.read'   => ['category' => 'events', 'display_name' => 'View Events'],
        'events.update' => ['category' => 'events', 'display_name' => 'Update Events'],
        'events.delete' => ['category' => 'events', 'display_name' => 'Delete Events'],
        'events.manage' => ['category' => 'events', 'display_name' => 'Manage Events'],

        // Monitoring & Analytics
        'monitoring.dashboard' => ['category' => 'monitoring', 'display_name' => 'View Monitoring Dashboard'],
        'monitoring.alerts'    => ['category' => 'monitoring', 'display_name' => 'Manage Alerts'],
        'monitoring.metrics'   => ['category' => 'monitoring', 'display_name' => 'View Performance Metrics'],
        'monitoring.configure' => ['category' => 'monitoring', 'display_name' => 'Configure Monitoring'],

        // Purchase Management
        'purchases.create'  => ['category' => 'purchases', 'display_name' => 'Create Purchases'],
        'purchases.read'    => ['category' => 'purchases', 'display_name' => 'View Purchases'],
        'purchases.update'  => ['category' => 'purchases', 'display_name' => 'Update Purchases'],
        'purchases.cancel'  => ['category' => 'purchases', 'display_name' => 'Cancel Purchases'],
        'purchases.refund'  => ['category' => 'purchases', 'display_name' => 'Process Refunds'],
        'purchases.history' => ['category' => 'purchases', 'display_name' => 'View Purchase History'],

        // Scraping Operations
        'scraping.manage'    => ['category' => 'scraping', 'display_name' => 'Manage Scraping'],
        'scraping.configure' => ['category' => 'scraping', 'display_name' => 'Configure Scrapers'],
        'scraping.view'      => ['category' => 'scraping', 'display_name' => 'View Scraping Data'],
        'scraping.metrics'   => ['category' => 'scraping', 'display_name' => 'View Scraping Metrics'],

        // Reports & Analytics
        'reports.view'      => ['category' => 'reports', 'display_name' => 'View Reports'],
        'reports.create'    => ['category' => 'reports', 'display_name' => 'Create Reports'],
        'reports.export'    => ['category' => 'reports', 'display_name' => 'Export Reports'],
        'reports.financial' => ['category' => 'reports', 'display_name' => 'View Financial Reports'],

        // API Access
        'api.access' => ['category' => 'api', 'display_name' => 'API Access'],
        'api.manage' => ['category' => 'api', 'display_name' => 'Manage API Keys'],
        'api.logs'   => ['category' => 'api', 'display_name' => 'View API Logs'],

        // Security Management
        'security.audit'  => ['category' => 'security', 'display_name' => 'Security Auditing'],
        'security.manage' => ['category' => 'security', 'display_name' => 'Manage Security Settings'],
        'security.logs'   => ['category' => 'security', 'display_name' => 'View Security Logs'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Role Permissions
    |--------------------------------------------------------------------------
    |
    | Define which permissions are assigned to each system role by default.
    |
    */
    'default_role_permissions' => [
        'admin' => [
            // Full system access
            'system.manage', 'system.config', 'system.logs', 'system.maintenance',
            'users.create', 'users.read', 'users.update', 'users.delete', 'users.roles', 'users.permissions',
            'tickets.create', 'tickets.read', 'tickets.update', 'tickets.delete', 'tickets.purchase', 'tickets.assign',
            'events.create', 'events.read', 'events.update', 'events.delete', 'events.manage',
            'monitoring.dashboard', 'monitoring.alerts', 'monitoring.metrics', 'monitoring.configure',
            'purchases.create', 'purchases.read', 'purchases.update', 'purchases.cancel', 'purchases.refund', 'purchases.history',
            'scraping.manage', 'scraping.configure', 'scraping.view', 'scraping.metrics',
            'reports.view', 'reports.create', 'reports.export', 'reports.financial',
            'api.access', 'api.manage', 'api.logs',
            'security.audit', 'security.manage', 'security.logs',
        ],

        'agent' => [
            // Ticket operations and monitoring
            'users.read', // Can view user info for tickets
            'tickets.create', 'tickets.read', 'tickets.update', 'tickets.purchase', 'tickets.assign',
            'events.read', 'events.update',
            'monitoring.dashboard', 'monitoring.alerts', 'monitoring.metrics',
            'purchases.create', 'purchases.read', 'purchases.update', 'purchases.history',
            'scraping.view', 'scraping.metrics',
            'reports.view', 'reports.export',
            'api.access',
        ],

        'customer' => [
            // Limited access for ticket purchasing
            'tickets.read', 'tickets.purchase',
            'events.read',
            'purchases.read', 'purchases.history',
        ],

        'scraper' => [
            // No permissions - scraper accounts are for rotation only
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Access Configuration
    |--------------------------------------------------------------------------
    |
    | Configure resource-specific access control settings.
    |
    */
    'resource_access' => [
        'default_actions' => ['view', 'create', 'update', 'delete'],
        'resource_types'  => [
            'ticket'     => Ticket::class,
            'user'       => User::class,
            'event'      => SportsEvent::class,
            'role'       => Role::class,
            'permission' => Permission::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related RBAC configuration.
    |
    */
    'security' => [
        'log_permission_changes'                        => TRUE,
        'require_confirmation_for_sensitive_operations' => TRUE,
        'max_role_assignments_per_user'                 => 5,
        'permission_cleanup_days'                       => 90, // Clean expired permissions after X days
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for RBAC user interface components.
    |
    */
    'ui' => [
        'show_inherited_permissions'    => TRUE,
        'group_permissions_by_category' => TRUE,
        'show_permission_source'        => TRUE,
        'enable_bulk_operations'        => TRUE,
    ],
];
