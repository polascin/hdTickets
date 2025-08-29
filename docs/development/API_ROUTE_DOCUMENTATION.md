# HD Tickets API Route Documentation

**Application:** HD Tickets - Comprehensive Sport Events Entry Tickets Monitoring System  
**Version:** 4.0.0  
**Last Updated:** January 30, 2025  

## 📚 Table of Contents

1. [API Overview](#api-overview)
2. [Authentication & Authorization](#authentication--authorization) 
3. [Rate Limiting](#rate-limiting)
4. [Core API Endpoints](#core-api-endpoints)
5. [Role-Based Access Control](#role-based-access-control)
6. [Health & Monitoring](#health--monitoring)
7. [Error Handling](#error-handling)

## 🎯 API Overview

The HD Tickets API provides comprehensive access to sports events ticket monitoring, scraping, and purchase automation functionality. The API is designed with security-first principles and implements robust role-based access control.

### Key Features
- **Sports Events Focus:** All endpoints designed for sports ticket monitoring (NOT helpdesk system)
- **Role-Based Access:** Granular permissions based on user roles
- **Rate Limiting:** Intelligent throttling to prevent abuse
- **Real-time Data:** Live updates for tickets, alerts, and system metrics
- **Multi-Platform Support:** Integration with Ticketmaster, StubHub, Viagogo, TickPick

### Base URL
```
Production: https://hdtickets.domain.com/api/v1/
Development: http://localhost:8000/api/v1/
```

## 🔐 Authentication & Authorization

### Authentication Method
All protected API endpoints require **Laravel Sanctum** token-based authentication:

```bash
Authorization: Bearer {your_api_token}
Content-Type: application/json
Accept: application/json
```

### Obtaining API Token
```bash
POST /api/v1/auth/login
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "token": "your-api-token-here",
    "user": {
        "id": 1,
        "email": "user@example.com",
        "role": "agent"
    },
    "expires_at": "2025-02-28T10:30:00Z"
}
```

## 👥 Role-Based Access Control

### User Roles & API Access

| Role | Description | API Access Level |
|------|-------------|------------------|
| **Admin** | System administration | Full API access to all endpoints |
| **Agent** | Ticket monitoring & purchasing | Scraping, purchasing, analytics endpoints |
| **Customer** | Basic monitoring | Read-only access to tickets and preferences |
| **Scraper** | Platform rotation | API-only access (no web interface) |

### Role-Based Middleware
Routes are protected using the `CheckApiRole` middleware:

```php
// Admin only
Route::middleware([CheckApiRole::class . ':admin'])

// Agent and Admin access
Route::middleware([CheckApiRole::class . ':agent,admin'])

// Customer, Agent, and Admin access  
Route::middleware([CheckApiRole::class . ':customer,agent,admin'])
```

## ⚡ Rate Limiting

### Rate Limit Tiers

| Route Group | Rate Limit | Purpose |
|-------------|------------|---------|
| **Public Routes** | 10 requests/minute | Authentication, status checks |
| **Authenticated API** | 120 requests/minute | General API operations |
| **Scraping Operations** | 30-60 requests/minute | Platform scraping (anti-detection) |
| **Real-time Updates** | Variable throttling | Dynamic based on endpoint |

### Rate Limit Headers
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 119
X-RateLimit-Reset: 1640995200
```

## 🚀 Core API Endpoints

### 1. System Status & Health

#### System Status (Public)
```bash
GET /api/v1/status
```
**Response:**
```json
{
    "status": "active",
    "service": "HD Tickets Sports Events Monitoring",
    "version": "2025.07.v4.0",
    "timestamp": "2025-01-30T10:30:00Z",
    "environment": "Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4"
}
```

### 2. Ticket Scraping API

#### Get Scraped Tickets
```bash
GET /api/v1/scraping/tickets
```
**Parameters:**
- `platform` - Filter by platform (ticketmaster, stubhub, viagogo, etc.)
- `sport` - Filter by sport type
- `limit` - Results per page (default: 50)
- `page` - Page number

#### Start Scraping Operation
```bash
POST /api/v1/scraping/start-scraping
{
    "platforms": ["ticketmaster", "stubhub"],
    "criteria": {
        "sport": "NFL",
        "team": "Kansas City Chiefs",
        "max_price": 500
    }
}
```

### 3. Alert Management API

#### Create Alert
```bash
POST /api/v1/alerts
{
    "name": "Chiefs Game Alert",
    "criteria": {
        "team": "Kansas City Chiefs",
        "max_price": 250,
        "min_quantity": 2
    },
    "notification_channels": ["email", "sms"],
    "priority": "high"
}
```

#### Get User Alerts
```bash
GET /api/v1/alerts
```

### 4. Purchase Queue API

#### Add to Purchase Queue
```bash
POST /api/v1/purchases/queue
{
    "ticket_id": "uuid-here",
    "quantity": 2,
    "max_price": 300,
    "priority": "high"
}
```

#### Get Purchase Queue
```bash
GET /api/v1/purchases/queue
```

### 5. Analytics API

#### Get Ticket Trends
```bash
GET /api/v1/analytics/ticket-trends
```

#### Get Platform Performance
```bash
GET /api/v1/analytics/platform-performance
```

### 6. Platform-Specific APIs

#### Ticketmaster Search
```bash
POST /api/v1/ticketmaster/search
{
    "keyword": "NFL",
    "location": "Kansas City",
    "start_date": "2025-02-01",
    "end_date": "2025-03-01"
}
```

#### StubHub Search  
```bash
POST /api/v1/stubhub/search
{
    "event_name": "Chiefs vs Bills",
    "venue": "Arrowhead Stadium"
}
```

## 🔧 Dashboard API Endpoints

### Real-time Dashboard Data
```bash
GET /api/v1/dashboard/stats
GET /api/v1/dashboard/realtime-stats  
GET /api/v1/dashboard/platform-health
GET /api/v1/dashboard/high-demand-tickets
```

### Monitoring API
```bash
GET /api/v1/monitoring/stats
GET /api/v1/monitoring/platform-health
GET /api/v1/monitoring/system-metrics
POST /api/v1/monitoring/monitors/{id}/check-now
```

## 🏥 Health & Monitoring

### System Health Check
```bash
GET /api/v1/dashboard/health
GET /api/v1/dashboard/health/database
GET /api/v1/dashboard/health/redis
GET /api/v1/dashboard/health/websockets
```

### Performance Metrics
```bash
POST /api/v1/performance/metrics
GET /api/v1/performance/dashboard  # Admin only
```

## ⚠️ Error Handling

### Standard Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "email": ["The email field is required."]
        }
    },
    "timestamp": "2025-01-30T10:30:00Z"
}
```

### HTTP Status Codes
- **200** - Success
- **201** - Created  
- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden (Role access denied)
- **404** - Not Found
- **422** - Validation Error
- **429** - Rate Limit Exceeded
- **500** - Server Error

### Role Access Error
```json
{
    "success": false,
    "error": {
        "code": "ACCESS_DENIED",
        "message": "Insufficient permissions. Required role: admin",
        "required_role": "admin",
        "user_role": "customer"
    }
}
```

## 🚦 API Testing Examples

### Authentication Test
```bash
curl -X POST https://hdtickets.domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Get Tickets with Authentication
```bash
curl -X GET https://hdtickets.domain.com/api/v1/scraping/tickets \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

### Create Alert
```bash
curl -X POST https://hdtickets.domain.com/api/v1/alerts \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Alert",
    "criteria": {"sport": "NFL", "max_price": 200},
    "notification_channels": ["email"]
  }'
```

## 📊 API Rate Limiting Test

### Check Rate Limits
```bash
curl -I https://hdtickets.domain.com/api/v1/status
# Check headers:
# X-RateLimit-Limit: 10
# X-RateLimit-Remaining: 9
```

## 🔍 API Documentation Standards

### Request Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest  # For AJAX requests
```

### Response Format
All API responses follow this standard format:
```json
{
    "success": true|false,
    "data": {...},           // Success data
    "error": {...},          // Error details (if success: false)
    "meta": {                // Pagination/metadata
        "current_page": 1,
        "total": 100,
        "per_page": 50
    },
    "timestamp": "2025-01-30T10:30:00Z"
}
```

---

## 🎯 API Best Practices

1. **Always include proper headers** for authentication and content type
2. **Handle rate limiting** gracefully in your applications
3. **Use appropriate HTTP methods** (GET, POST, PUT, DELETE)
4. **Validate user roles** before making restricted API calls
5. **Implement error handling** for all possible response codes
6. **Cache responses** appropriately to reduce API calls
7. **Use pagination** for large data sets
8. **Monitor API usage** and implement circuit breakers

---

**For complete technical documentation, see:**
- [DASHBOARD_ROUTING_DOCUMENTATION.md](DASHBOARD_ROUTING_DOCUMENTATION.md)
- [RBAC_TEST_REPORT.md](RBAC_TEST_REPORT.md)
- [README.md](README.md)

**API Documentation Generated:** January 30, 2025  
**Next Review:** March 2025
