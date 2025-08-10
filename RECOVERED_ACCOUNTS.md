# HD Tickets - Recovered User Accounts

## 📧 Account Summary

The following user accounts have been successfully restored to the HD Tickets system:

| Email | Username | Role | Default Password | Status |
|-------|----------|------|------------------|---------|
| admin@hdtickets.com | admin | Admin | HDTickets2025! | ✅ Active |
| agent@hdtickets.com | agent | Agent | HDAgent2025! | ✅ Active |
| customer@hdtickets.com | customer | Customer | HDCustomer2025! | ✅ Active |

## 🔐 Account Details

### 1. Administrator Account
- **Email**: admin@hdtickets.com
- **Username**: admin
- **Role**: Admin
- **Password**: HDTickets2025!
- **Permissions**: Full system access, user management, platform configuration
- **Theme**: Auto (Professional)
- **Features**: System monitoring, security alerts, user reports

### 2. Agent Account
- **Email**: agent@hdtickets.com
- **Username**: agent  
- **Role**: Agent
- **Password**: HDAgent2025!
- **Permissions**: Ticket selection, purchasing, monitoring management
- **Theme**: Auto (Agent focused)
- **Features**: Ticket alerts, purchase confirmations, monitoring dashboards

### 3. Customer Account
- **Email**: customer@hdtickets.com
- **Username**: customer
- **Role**: Customer
- **Password**: HDCustomer2025!
- **Permissions**: Basic account access (legacy role)
- **Theme**: Light (Customer friendly)
- **Features**: Account management, basic notifications

## 🛡️ Security Configuration

All accounts have been created with the following security settings:

- ✅ **Active**: All accounts are active and ready to use
- ✅ **Email Verified**: Email verification completed
- ❌ **2FA Disabled**: Two-factor authentication is disabled by default
- ✅ **Deletion Protection**: Account deletion protection enabled
- 🔒 **Password Security**: Strong default passwords with expiration tracking

## ⚡ Quick Login Testing

Use this command to test login credentials:

```bash
# Test admin login
php artisan test:login admin@hdtickets.com "HDTickets2025!"

# Test agent login  
php artisan test:login agent@hdtickets.com "HDAgent2025!"

# Test customer login
php artisan test:login customer@hdtickets.com "HDCustomer2025!"
```

## 🔧 Default Preferences

### Admin Preferences
- **Notifications**: All system alerts enabled
- **Display**: Professional theme, 50 items per page
- **Dashboard**: Full admin layout with advanced filters
- **Alerts**: System errors, security warnings, performance monitoring

### Agent Preferences  
- **Notifications**: Ticket alerts, purchase confirmations
- **Display**: Agent-focused theme, 25 items per page
- **Dashboard**: Ticket-centered layout with monitoring tools
- **Alerts**: Ticket availability, price changes, deadlines

### Customer Preferences
- **Notifications**: Email updates only (promotions disabled)
- **Display**: Light theme, 10 items per page
- **Dashboard**: Simple customer layout
- **Alerts**: Account changes only

## 🚨 IMPORTANT SECURITY NOTES

### ⚠️ IMMEDIATE ACTION REQUIRED:

1. **Change Default Passwords**: All accounts use temporary default passwords that should be changed immediately
2. **Enable 2FA**: For enhanced security, enable two-factor authentication on admin and agent accounts
3. **Review Permissions**: Verify that role permissions match your security requirements
4. **Update Contact Info**: Add proper contact information and profile pictures
5. **Security Audit**: Review login history and security settings

### 📋 Recommended Next Steps:

1. **Admin Account**:
   - Change password to a secure, unique password
   - Enable 2FA with authenticator app
   - Set up security alerts and monitoring
   - Review user management permissions

2. **Agent Account**:
   - Change password to a secure, unique password  
   - Enable 2FA for enhanced security
   - Configure ticket alerts and monitoring preferences
   - Set up purchase notification preferences

3. **Customer Account**:
   - Change password (if still in use)
   - Consider disabling if not needed (legacy role)
   - Review permissions and access levels

## 🔄 Account Recovery Process

These accounts were recovered using the `LostAccountsSeeder` seeder class:

```bash
php artisan db:seed --class=LostAccountsSeeder
```

The seeder can be run again safely - it will only create accounts that don't already exist.

## 📞 Support Information

- **Recovery Date**: 2025-08-10 08:21:25 UTC
- **Recovery Method**: Database seeder with secure password hashing
- **Account Status**: All verified and active
- **Next Review**: Should be scheduled within 30 days

---

**⚠️ WARNING**: This document contains sensitive login information. Store it securely and delete after passwords have been changed.

**✅ SUCCESS**: All three lost accounts have been successfully restored to HD Tickets with appropriate roles, permissions, and security settings.
