# Security Enhancements Documentation

## Overview

This document outlines the comprehensive security enhancements implemented in the hdTickets application, including activity logging, permission-based action visibility, secure bulk operations, and CSRF protection.

## 1. Activity Logging System

### Features Implemented:
- **Comprehensive Activity Tracking**: All user actions are logged with detailed context
- **Risk-based Categorization**: Activities are classified as low, medium, or high risk
- **Multiple Log Types**: 
  - `security`: Security-related events (login failures, permission denials)
  - `user_actions`: Regular user activities (page views, data modifications)
  - `bulk_operations`: Bulk operations with detailed tracking
  - `authentication`: Login/logout events
  - `user_changes`: Model changes tracked automatically

### Components:
- **SecurityService**: `app/Services/SecurityService.php`
- **ActivityLoggerMiddleware**: `app/Http/Middleware/ActivityLoggerMiddleware.php`
- **ActivityLogController**: `app/Http/Controllers/Admin/ActivityLogController.php`
- **Configuration**: `config/activitylog.php`

### Key Features:
- IP address and user agent tracking
- Session ID correlation
- Automatic risk level calculation
- Detailed property logging
- Export functionality
- Cleanup capabilities for old logs

## 2. Permission-Based Action Visibility

### Enhanced User Model Permissions:
The User model now includes comprehensive permission checking methods:

#### Admin Permissions:
- `canManageUsers()`: User management access
- `canManageSystem()`: System configuration access  
- `canManagePlatforms()`: Platform administration
- `canAccessFinancials()`: Financial reports access
- `canManageApiAccess()`: API access management
- `canDeleteAnyData()`: Root admin only destructive operations

#### Agent Permissions:
- `canSelectAndPurchaseTickets()`: Ticket selection and purchasing
- `canMakePurchaseDecisions()`: Purchase decision access
- `canManageMonitoring()`: Monitoring management
- `canViewScrapingMetrics()`: Scraping performance metrics

#### Security Restrictions:
- `canAccessSystem()`: Prevents scraper users from system access
- `canLoginToWeb()`: Prevents scraper users from web interface
- `isScrapingRotationUser()`: Identifies rotation-only users

### Implementation:
- Middleware-based permission checking
- Detailed permission logging
- Role-based access control
- Custom permission validation

## 3. Secure Bulk Operations

### Security Features:
- **Rate Limiting**: Users are limited in bulk operation frequency
- **Item Count Limits**: Role-based maximum item limits
- **Operation Validation**: Destructive operations have additional restrictions
- **Secure Token System**: Bulk operations require time-limited tokens
- **Detailed Logging**: All bulk operations are comprehensively logged

### User Limits:
- **Root Admin**: 1000 items, unlimited destructive operations
- **Admin**: 500 items (100 for destructive operations)
- **Agent**: 100 items (10 for destructive operations)
- **Other Users**: 10 items maximum

### Token Security:
- Time-limited tokens (5-minute window)
- Operation-specific validation
- User-specific binding
- Nonce-based uniqueness

### Enhanced UserManagementController:
- Secure bulk user operations
- Enhanced validation
- Comprehensive error handling
- Performance timing

## 4. CSRF Protection

### Enhanced CSRF Features:
- **Standard Laravel CSRF**: All forms protected with CSRF tokens
- **Bulk Operation Tokens**: Additional security layer for bulk operations
- **API Protection**: API endpoints with proper CSRF handling
- **AJAX Protection**: All AJAX requests include CSRF tokens

### Implementation:
- `VerifyCsrfToken` middleware active on all web routes
- Custom bulk operation token validation
- Proper token rotation
- Exception handling for token failures

## 5. Middleware Integration

### ActivityLoggerMiddleware:
- Automatic activity logging for all web requests
- Intelligent filtering to avoid log spam
- Route-specific activity mapping
- Parameter sanitization (removes sensitive data)

### Integration:
- Added to web middleware group for automatic activation
- Excludes asset requests and polling endpoints
- Provides detailed request context
- Maps routes to meaningful activities

## 6. Database Schema

### Activity Log Table:
- Standard Spatie Activity Log schema
- Enhanced with risk level properties
- IP address and session tracking
- JSON property storage for flexible data

### User Model Enhancements:
- Activity logging trait integration
- Automatic change tracking
- Permission-based field logging
- Secure attribute handling

## 7. Admin Interface

### Activity Log Dashboard:
- **Filtering**: By log type, user, risk level, date range
- **Statistics**: Real-time activity statistics
- **Export**: CSV export with filtering
- **Cleanup**: Old log cleanup for root admins
- **Detailed View**: Individual activity inspection

### Features:
- Risk-based color coding
- User correlation
- IP address tracking
- Real-time updates
- Pagination support

## 8. API Endpoints

### Security API Routes:
- `GET /admin/activity-logs/api/security-activities`: Recent security events
- `GET /admin/activity-logs/api/user-summary/{user}`: User activity summary
- `POST /admin/activity-logs/api/bulk-token`: Generate bulk operation tokens
- `GET /admin/activity-logs/export`: Export activity logs
- `DELETE /admin/activity-logs/cleanup`: Cleanup old logs

### Authentication:
- All endpoints require authentication
- Permission-based access control
- CSRF protection on state-changing operations
- Rate limiting on sensitive endpoints

## 9. Configuration

### Environment Variables:
```env
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOGGER_DB_CONNECTION=mysql
```

### Configuration Files:
- `config/activitylog.php`: Activity logging configuration
- `app/Http/Kernel.php`: Middleware registration
- `routes/web.php`: Protected route definitions

## 10. Security Best Practices Implemented

### Data Protection:
- Sensitive parameter filtering
- Encrypted data handling
- Secure token generation
- Session security

### Access Control:
- Role-based permissions
- Permission logging
- Failed access tracking
- Rate limiting

### Audit Trail:
- Comprehensive activity logging
- Change tracking
- Bulk operation monitoring
- Security event recording

### Error Handling:
- Secure error messages
- Detailed logging without exposure
- Graceful failure handling
- User-friendly error pages

## 11. Monitoring and Alerting

### Risk-Based Monitoring:
- High-risk activities highlighted
- Security event aggregation
- Failed access attempt tracking
- Unusual activity detection

### Statistics Tracking:
- Daily, weekly, monthly activity counts
- Security event metrics
- Bulk operation monitoring
- User activity summaries

## 12. Future Enhancements

### Recommended Additions:
- Real-time alerting for high-risk activities
- Machine learning-based anomaly detection
- Enhanced export formats (PDF, Excel)
- Integration with external SIEM systems
- Automated security reporting

### Scalability Considerations:
- Activity log partitioning
- Archival strategies
- Performance optimization
- Distributed logging support

## Conclusion

The implemented security enhancements provide comprehensive protection through:
- Detailed activity logging and monitoring
- Permission-based access control
- Secure bulk operations with rate limiting
- Enhanced CSRF protection
- Real-time security event tracking

All components work together to create a robust security framework that maintains detailed audit trails while providing administrators with the tools needed to monitor and secure the application effectively.
