# User Role Redesign for Scraping System

## Overview

The hdTickets system has been redesigned to focus on ticket scraping operations with clearly defined user roles and responsibilities.

## Redesigned User Roles

### 1. **Admin Role** - System and Platform Configuration Management
- **Purpose**: Complete system administration and configuration management
- **Responsibilities**:
  - Manage system configurations and settings
  - Handle platform administration and integrations  
  - Oversee financial reporting and analytics
  - Manage API access and security settings
  - User management and role assignments
  - System monitoring and performance oversight
  - Data backup and recovery operations

**Key Permissions**:
- `manage_users` - Create, edit, delete users
- `manage_system` - System configuration access
- `manage_platforms` - Platform integrations and settings
- `access_financials` - Financial reports and analytics
- `manage_api_access` - API key and access management
- `delete_any_data` - Critical data operations (root admin only)

### 2. **Agent Role** - Ticket Selection, Purchasing, and Monitoring
- **Purpose**: Handle operational aspects of ticket acquisition and monitoring
- **Responsibilities**:
  - Select tickets for purchase based on criteria
  - Make purchasing decisions and execute transactions
  - Monitor scraping performance and success rates
  - Manage monitoring alerts and notifications
  - Track ticket availability and pricing
  - Optimize purchasing strategies

**Key Permissions**:
- `select_and_purchase_tickets` - Ticket selection and purchasing
- `make_purchase_decisions` - Transaction authorization
- `manage_monitoring` - Monitoring system management
- `view_scraping_metrics` - Performance analytics access

### 3. **Customer Role** - Legacy (Deprecated)
- **Purpose**: Legacy role maintained for backward compatibility
- **Status**: Deprecated for new scraping-focused system
- **Note**: Existing customers retain access but new focus is on Admin/Agent roles

### 4. **Scraper Role** - Rotation Users (NO System Access)
- **Purpose**: 1000+ fake users for scraping rotation to avoid detection
- **Responsibilities**:
  - Used purely for scraping rotation
  - No access to web interface or system functions
  - Automated rotation for scraping operations
  - Maintain anonymity and avoid rate limiting

**Key Restrictions**:
- `canAccessSystem()` - **FALSE**
- `canLoginToWeb()` - **FALSE** 
- `isScrapingRotationUser()` - **TRUE**
- No permissions to any system functions

## Permission Matrix

| Permission | Admin | Agent | Customer | Scraper |
|------------|-------|-------|----------|---------|
| System Access | ✅ | ✅ | ✅ | ❌ |
| Web Login | ✅ | ✅ | ✅ | ❌ |
| Manage Users | ✅ | ❌ | ❌ | ❌ |
| Manage System | ✅ | ❌ | ❌ | ❌ |
| Manage Platforms | ✅ | ❌ | ❌ | ❌ |
| Access Financials | ✅ | ❌ | ❌ | ❌ |
| Select/Purchase Tickets | ✅ | ✅ | ❌ | ❌ |
| Make Purchase Decisions | ✅ | ✅ | ❌ | ❌ |
| Manage Monitoring | ✅ | ✅ | ❌ | ❌ |
| View Scraping Metrics | ✅ | ✅ | ❌ | ❌ |
| Scraping Rotation | ❌ | ❌ | ❌ | ✅ |

## Implementation Details

### Database Changes
- Added `scraper` to user role enum in migration: `2025_07_22_162550_update_user_roles_for_scraping_system.php`
- Updated User model with new role constants and permission methods

### Security Measures
- `PreventScraperWebAccess` middleware blocks scraper users from web interface
- Authorization gates updated in `AuthServiceProvider`
- Comprehensive logging of unauthorized access attempts

### Seeder for Scraper Users
- `ScraperUsersSeeder` creates 1200+ fake users for rotation
- Users have format: `scraper_0001`, `scraper_0002`, etc.
- Fake email domains: `@scraper.hdtickets.fake`

## Usage Examples

### Checking Permissions in Controllers
```php
// Check if user can manage system (Admins only)
if (!Gate::allows('manage-system')) {
    abort(403);
}

// Check if user can make purchase decisions (Agents + Admins)
if (!Gate::allows('make-purchase-decisions')) {
    return redirect()->back()->with('error', 'Access denied');
}

// Prevent scraper access
if (Auth::user()->isScraper()) {
    abort(403, 'Scrapers cannot access this resource');
}
```

### Blade Templates
```blade
@can('manage-system')
    <!-- Admin-only system management -->
@endcan

@can('select-purchase-tickets')
    <!-- Agent ticket purchasing interface -->
@endcan

@unless(Auth::user()->isScraper())
    <!-- Hide from scraper users -->
@endunless
```

### API Usage
```php
// Get user permissions
$permissions = Auth::user()->getPermissions();

// Check specific role
if (Auth::user()->isAgent()) {
    // Agent-specific logic
}

// Ensure not a scraper
if (Auth::user()->canAccessSystem()) {
    // Safe to proceed
}
```

## Migration Instructions

### 1. Run the Migration
```bash
php artisan migrate
```

### 2. Generate Scraper Users
```bash
php artisan db:seed --class=ScraperUsersSeeder
```

### 3. Update Middleware (if needed)
Register the `PreventScraperWebAccess` middleware in your HTTP kernel and apply to web routes.

### 4. Update Existing Controllers
Review and update controllers to use new permission gates and role checks.

## Security Notes

1. **Scraper Isolation**: Scraper users are completely isolated from system access
2. **Monitoring**: All scraper web access attempts are logged
3. **Role Hierarchy**: Admin > Agent > Customer > Scraper (in terms of permissions)
4. **Backward Compatibility**: Existing gates maintained for legacy support

## Future Considerations

- Monitor scraper rotation effectiveness
- Implement automated scraper user management
- Add metrics for role-based usage patterns
- Consider adding sub-roles within Agent category for specialized functions

---

**Last Updated**: 2025-07-22  
**Version**: 1.0  
**Implemented By**: hdTickets Development Team
