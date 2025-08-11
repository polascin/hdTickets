# Dashboard Routing Analysis - HD Tickets Sports Events System

## Overview
The HD Tickets application is a **Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System** that implements role-based dashboard routing to provide different user experiences based on user privileges.

## Current Routing Architecture

### 1. Main Entry Point
**Route**: `/dashboard` (GET)
**Controller**: `HomeController::index()`
**Middleware**: `['auth', 'verified']`
**Route Name**: `dashboard`

### 2. Role-Based Routing Logic

#### HomeController::index() Flow:
```php
public function index()
{
    $user = Auth::user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Route based on user role
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isAgent()) {
        return redirect()->route('agent.dashboard');
    } else {
        // For customers and other roles, use customer dashboard
        return redirect()->route('customer.dashboard');
    }
}
```

## User Role System

### Role Constants (from User Model):
- `ROLE_ADMIN = 'admin'` - System and platform configuration management
- `ROLE_AGENT = 'agent'` - Ticket selection, purchasing, and monitoring  
- `ROLE_CUSTOMER = 'customer'` - Legacy role (deprecated for new system)
- `ROLE_SCRAPER = 'scraper'` - Rotation users for scraping (no system access)

### Role Determination Methods:
```php
// Role check methods in User model
public function isAdmin() { return $this->hasRole(self::ROLE_ADMIN); }
public function isAgent() { return $this->hasRole(self::ROLE_AGENT); }
public function isCustomer() { return $this->hasRole(self::ROLE_CUSTOMER); }
public function isScraper() { return $this->hasRole(self::ROLE_SCRAPER); }
```

### System Access Control:
```php
public function canAccessSystem() { return !$this->isScraper(); }
public function canLoginToWeb() { return !$this->isScraper(); }
```

## Dashboard Routes by Role

### 1. Admin Dashboard
**Route**: `/admin/dashboard` (GET)
**Controller**: `Admin\DashboardController::index()`
**Route Name**: `admin.dashboard`
**Middleware**: `['auth', 'verified', \App\Http\Middleware\AdminMiddleware::class]`

#### Admin Permissions:
- System and platform configuration management
- User management (`canManageUsers()`)
- System configuration (`canManageSystem()`)
- Platform management (`canManagePlatforms()`)
- Financial reports (`canAccessFinancials()`)
- API access management (`canManageApiAccess()`)
- Root admin can delete any data (`canDeleteAnyData()`)

#### Admin Features:
- Complete system overview
- User management and role assignment
- System health monitoring
- Scraping platform configuration
- Financial and analytics reports
- Activity logging and audit trails

### 2. Agent Dashboard
**Route**: `/agent-dashboard` (GET)
**Controller**: `AgentDashboardController::index()`
**Route Name**: `agent.dashboard`
**Middleware**: `['auth', 'verified']` (with role check in controller)

#### Agent Permissions:
- Ticket selection and purchasing (`canSelectAndPurchaseTickets()`)
- Purchase decisions (`canMakePurchaseDecisions()`)
- Monitoring management (`canManageMonitoring()`)
- Scraping performance metrics (`canViewScrapingMetrics()`)

#### Agent Features:
- Sports events ticket monitoring
- Purchase queue management
- Price drop detection
- Alert management
- Performance tracking
- Trend analysis

### 3. Customer Dashboard
**Route**: `/customer-dashboard` (GET)
**Controller**: `DashboardController::index()`
**Route Name**: `customer.dashboard`
**Middleware**: `['auth', 'verified']`

#### Customer Features:
- View available sports events tickets
- Set up ticket alerts
- Monitor personal preferences
- Basic sports events browsing
- Price tracking for preferred events

## Access Control Middleware

### AdminMiddleware
```php
public function handle(Request $request, Closure $next, string $permission = null)
{
    if (!auth()->check() || !auth()->user()->isAdmin()) {
        abort(403);
    }
    return $next($request);
}
```

### AgentMiddleware
```php
public function handle(Request $request, Closure $next): Response
{
    if (!Auth::check()) {
        return redirect('login');
    }
    
    $user = Auth::user();
    if (!$user->isAgent() && !$user->isAdmin()) {
        abort(403, 'Access denied. Agent role required.');
    }
    
    return $next($request);
}
```

## Expected User Flow by Role

### Admin User Flow:
1. Login → `/dashboard` → `HomeController::index()`
2. Role check: `$user->isAdmin()` returns `true`
3. Redirect to: `route('admin.dashboard')` → `/admin/dashboard`
4. AdminMiddleware validates admin role
5. `Admin\DashboardController::index()` renders admin dashboard view
6. Access to all system management features

### Agent User Flow:
1. Login → `/dashboard` → `HomeController::index()`  
2. Role check: `$user->isAgent()` returns `true`
3. Redirect to: `route('agent.dashboard')` → `/agent-dashboard`
4. AgentDashboardController validates agent/admin role
5. `AgentDashboardController::index()` renders agent dashboard view
6. Access to sports events monitoring and purchase features

### Customer User Flow:
1. Login → `/dashboard` → `HomeController::index()`
2. Role check: Neither admin nor agent
3. Redirect to: `route('customer.dashboard')` → `/customer-dashboard`
4. `DashboardController::index()` renders customer dashboard view
5. Access to basic sports events browsing and personal alerts

### Scraper User Flow:
1. Scraper users **CANNOT** access web interface
2. `canLoginToWeb()` returns `false` for scrapers
3. `canAccessSystem()` returns `false` for scrapers
4. These are rotation users for web scraping only

## Security Features

### Role-Based Access Control:
- Each dashboard requires appropriate role/permissions
- AdminMiddleware blocks non-admin access to admin routes
- AgentMiddleware allows both agents and admins to access agent features
- Scraper users are completely blocked from web interface

### Permission Methods:
The User model includes comprehensive permission checking:
- System-level permissions for admins
- Feature-specific permissions for agents
- Access restrictions for scrapers

### Logging and Audit:
- HomeController logs role-based routing decisions
- Activity logging tracks user access patterns
- Comprehensive audit trail for admin actions

## Route Files Structure

### Main Routes (`routes/web.php`):
- Entry point dashboard route
- Customer dashboard route
- Basic authenticated routes

### Admin Routes (`routes/admin.php`):
- All admin-specific routes under `/admin` prefix
- Protected by AdminMiddleware
- Comprehensive admin functionality

### Additional Route Files:
- `routes/auth.php` - Authentication routes
- `routes/test.php` - Testing routes

## Key Insights

1. **Clear Role Separation**: The system maintains strict separation between admin (system management), agent (ticket operations), and customer (basic access) roles.

2. **Security-First Approach**: Multiple layers of access control including middleware, controller checks, and model-level permissions.

3. **Sports Events Focus**: This is specifically designed for sports event ticket monitoring and purchasing, not a general helpdesk system.

4. **Scalable Architecture**: The role-based system allows for easy addition of new roles and permissions.

5. **Comprehensive Logging**: All routing decisions and access attempts are logged for security and debugging.

## Potential Issues & Considerations

1. **Scraper Role Confusion**: Scraper users exist in the system but cannot access any web interface, which might cause confusion during user management.

2. **Customer Role Deprecation**: Comments indicate customer role is "deprecated for new system" but it's still the fallback for non-admin/non-agent users.

3. **Admin Privilege Escalation**: Admins can access agent features, which follows principle of hierarchical permissions.

4. **Route Naming Consistency**: Some inconsistency in route naming patterns between different dashboard types.

This analysis shows a well-structured role-based dashboard system designed specifically for sports event ticket monitoring and management, with clear separation of concerns and comprehensive security measures.
