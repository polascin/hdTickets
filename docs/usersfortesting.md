Administrative Account Created Successfully

Account Details:
•  Email: admin@hdtickets.com
•  Password: hdtickets2025
•  Username: admin
•  Full Name: HD Tickets Administrator
•  Role: ADMIN
•  Status: ACTIVE
•  Email Verified: YES

Admin Privileges:
The account has been granted full administrative access including:
•  ✅ Full system access
•  ✅ User management
•  ✅ Ticket management 
•  ✅ Scraping operations control
•  ✅ System configuration
•  ✅ Performance monitoring
•  ✅ Platform administration

Important Notes:
•  The command detected that a user with this email already existed and asked to update it, which was confirmed
•  The account is ready to use immediately
•  The user has been assigned ID 2 in the system
•  Account was originally created on 2025-07-29 16:17:09 and has now been updated with the new credentials

You can now log in to the HD Tickets application using:
•  Email: admin@hdtickets.com
•  Password: hdtickets2025

The administrative user has full access to manage the HD Tickets sports events entry tickets monitoring, scraping and purchase system.
/var/www/hdtickets
Add also user account user@hdtickets.com with password 'user2025'
L I'll create a regular user account for the HD Tickets application. Let me check if there are any specific commands for creating regular users or if I need to use a different approach.
I notice there's only a command for creating root admin users, not regular users. Let me check if there's a Laravel tinker command available or if I need to use database seeders. Let me also check what user-related artisan commands exist:
Since there's no specific command for regular users and to avoid creating a large number of fake users, I'll add the user directly via Laravel Tinker. This method allows you to interact with your application directly.

Let me open Tinker and create the user account:
Perfect! Now I can see the available roles. The system has these roles:
•  admin - System and platform configuration management
•  agent - Ticket selection, purchasing, and monitoring  
•  customer - Legacy role for regular users
•  scraper - Rotation users for scraping (no system access)

For a regular user account, the most appropriate role would be customer (which is the legacy role for regular users). Let me create the user with this role:
Perfect! ✅ The regular user account has been successfully created. Here's a summary of what was accomplished:

👤 Regular User Account Created Successfully

Account Details:
•  Email: user@hdtickets.com
•  Password: user2025
•  Username: user
•  Full Name: Regular User
•  Role: customer (regular user role)
•  Status: Active
•  Email Verified: Yes
•  User ID: 1008
•  Created: 2025-08-04 10:02:24

User Permissions:
As a customer role user, this account has:
•  ✅ System access (can login to web interface)
•  ✅ Basic user permissions 
•  ❌ No administrative privileges
•  ❌ No agent-level permissions (ticket purchasing/monitoring)
•  ❌ No system configuration access

Account Summary:
You now have both user accounts set up in the HD Tickets system:

1. Administrator Account:
•  Email: admin@hdtickets.com
•  Password: hdtickets2025
•  Role: admin
2. Regular User Account:
•  Email: user@hdtickets.com
•  Password: user2025
•  Role: customer

Both accounts are ready to use and you can now log in to the HD Tickets sports events entry tickets monitoring, scraping and purchase system with either account depending on your needs.