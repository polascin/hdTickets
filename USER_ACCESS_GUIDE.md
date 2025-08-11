# HD Tickets - User Access Guide

## 🎫 Sports Event Ticket Monitoring Platform

This document provides access information for the HD Tickets application - a comprehensive sports event ticket monitoring and purchasing platform.

---

## 🔐 User Accounts

All user accounts are **active**, **email verified**, and ready for immediate access:

### 👑 Administrator Account
- **Email:** `admin@hdtickets.com`
- **Password:** `password`
- **Role:** `admin`
- **Dashboard:** https://hdtickets.com/admin/dashboard
- **Permissions:**
  - Full system management
  - User management and registration
  - Platform and scraper configuration
  - Financial reports and analytics
  - API access management
  - System monitoring and logs

### 🎯 Agent Account  
- **Email:** `agent@hdtickets.com`
- **Password:** `password`
- **Role:** `agent`
- **Dashboard:** https://hdtickets.com/agent-dashboard
- **Permissions:**
  - Ticket selection and monitoring
  - Purchase queue management
  - Performance analytics
  - Alert management
  - Platform monitoring
  - Customer ticket recommendations

### 👤 Customer Account
- **Email:** `customer@hdtickets.com`  
- **Password:** `password`
- **Role:** `customer`
- **Dashboard:** https://hdtickets.com/customer-dashboard
- **Permissions:**
  - Personal ticket alerts and monitoring
  - Purchase history and queue status
  - Sports event browsing
  - Alert configuration
  - Basic analytics

---

## 🌐 Access URLs

### Primary Login
- **Login Page:** https://hdtickets.com/login
- **Main Dashboard:** https://hdtickets.com/dashboard *(auto-redirects based on role)*

### Direct Dashboard Access
- **Admin Dashboard:** https://hdtickets.com/admin/dashboard
- **Agent Dashboard:** https://hdtickets.com/agent-dashboard  
- **Customer Dashboard:** https://hdtickets.com/customer-dashboard
- **Scraper Dashboard:** https://hdtickets.com/scraper-dashboard *(system users only)*

---

## 🔒 Security Features

- **Role-Based Access Control:** Each user type has specific permissions and dashboard features
- **Middleware Protection:** All routes are protected by authentication and role verification
- **Email Verification:** All accounts are pre-verified for immediate access
- **Session Management:** Secure session handling with Laravel Sanctum
- **HTTPS Enforced:** All connections secured with SSL certificates

---

## 🎮 Dashboard Features

### Admin Dashboard
- Real-time system analytics
- User management interface  
- Platform performance monitoring
- Revenue and financial reports
- Scraper status and configuration
- API usage statistics

### Agent Dashboard
- Live ticket monitoring feeds
- Purchase queue management
- Customer alert oversight
- Performance metrics and KPIs
- Platform health monitoring
- Ticket recommendation engine

### Customer Dashboard  
- Personal ticket alerts
- Sports event browsing
- Purchase history
- Price tracking charts
- Alert configuration
- Recommendation feed

---

## 🚀 Getting Started

1. **Navigate to:** https://hdtickets.com/login
2. **Choose your role** and use the corresponding credentials above
3. **Login** with email and password
4. **Automatic redirect** to your role-specific dashboard
5. **Explore** the features available to your role

---

## 🛠️ Technical Information

- **Framework:** Laravel 11.x
- **Database:** MySQL 8.0
- **Web Server:** Apache 2.4 with SSL
- **Operating System:** Ubuntu 24.04 LTS
- **PHP Version:** 8.2+
- **Asset Management:** Laravel Vite with cache busting

---

## 📞 Support

For technical issues or account problems:
- Check application logs at `/var/www/hdtickets/storage/logs/`
- Review Apache logs for server issues  
- All user accounts are pre-configured and tested
- Password can be reset through Laravel's built-in password reset functionality

---

**Status:** ✅ **PRODUCTION READY**  
**Last Updated:** August 11, 2025  
**Environment:** Live Production Server

---
