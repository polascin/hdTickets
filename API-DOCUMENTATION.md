# HDTickets API Documentation
**Version:** 4.0.0  
**System:** Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System

## Overview
The HDTickets API provides comprehensive RESTful endpoints for managing a Sports Events Entry Tickets Monitoring, Scraping and Purchase System. This advanced API enables external systems to interact with all major features including automated scraping, AI-powered alerts, purchase automation, and advanced analytics.

## Base URL
```
https://your-domain.com/api/v1/
```

## Authentication
All protected endpoints require Bearer token authentication:
```
Authorization: Bearer {your-token}
```

## Rate Limiting
- Public routes: 10 requests per minute
- Authenticated routes: 120 requests per minute
- Scraping routes: 60 requests per minute

---

## Endpoints

### Authentication

#### POST /auth/login
Login and obtain API token
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

#### POST /auth/logout
Revoke current token (requires authentication)

#### GET /auth/profile
Get current user profile (requires authentication)

---

### Scraping Management

#### GET /scraping/tickets
Get all scraped tickets with filtering and pagination
- Query parameters: `platform`, `status`, `sport`, `team`, `venue`, `location`, `min_price`, `max_price`, `is_available`, `is_high_demand`, `event_date_from`, `event_date_to`, `search`, `category_id`, `sort`, `direction`, `per_page`

#### GET /scraping/tickets/{uuid}
Get specific scraped ticket by UUID

#### POST /scraping/start-scraping
Initiate scraping for specific platforms
```json
{
  "platforms": ["stubhub", "ticketmaster", "viagogo"],
  "keywords": "basketball championship",
  "location": "New York",
  "max_price": 500,
  "limit": 100
}
```

#### GET /scraping/statistics
Get comprehensive scraping statistics and metrics

#### GET /scraping/platforms
Get available scraping platforms and their status

#### DELETE /scraping/cleanup
Clean up old scraped tickets
```json
{
  "older_than_days": 30,
  "status": ["sold_out", "expired"],
  "platform": "stubhub",
  "dry_run": false
}
```

---

### Alert Management

#### GET /alerts
Get all alerts for authenticated user
- Query parameters: `is_active`, `platform`, `search`, `per_page`

#### POST /alerts
Create new ticket alert
```json
{
  "name": "NBA Finals Alert",
  "keywords": "NBA Finals",
  "platform": "stubhub",
  "max_price": 300,
  "currency": "USD",
  "filters": {
    "venue": "Madison Square Garden",
    "location": "New York",
    "event_date_from": "2025-06-01"
  },
  "email_notifications": true,
  "sms_notifications": false
}
```

#### GET /alerts/{uuid}
Get specific alert by UUID

#### PUT /alerts/{uuid}
Update existing alert

#### DELETE /alerts/{uuid}
Delete alert

#### POST /alerts/{uuid}/toggle
Toggle alert active status

#### POST /alerts/{uuid}/test
Test alert against current tickets

#### GET /alerts/statistics
Get alert statistics for user

#### POST /alerts/check-all
Manually trigger check for all active alerts

---

### Purchase Management

#### GET /purchases/queue
Get user's purchase queue
- Query parameters: `status`, `platform`, `per_page`

#### POST /purchases/queue
Add ticket to purchase queue
```json
{
  "ticket_uuid": "12345-abcde-67890",
  "max_price": 250,
  "quantity": 2,
  "priority": 8,
  "auto_purchase": true,
  "notes": "Must be lower bowl seats"
}
```

#### PUT /purchases/queue/{uuid}
Update queue item

#### DELETE /purchases/queue/{uuid}
Remove item from queue

#### GET /purchases/attempts
Get purchase attempts history
- Query parameters: `status`, `platform`, `date_from`, `date_to`, `per_page`

#### POST /purchases/attempts/initiate
Initiate manual purchase attempt
```json
{
  "ticket_uuid": "12345-abcde-67890",
  "quantity": 2,
  "max_price": 300,
  "payment_method": "credit_card",
  "delivery_method": "email",
  "priority": "high"
}
```

#### GET /purchases/attempts/{uuid}
Get purchase attempt details

#### POST /purchases/attempts/{uuid}/cancel
Cancel pending purchase attempt

#### GET /purchases/statistics
Get comprehensive purchase statistics

#### GET /purchases/configuration
Get user's purchase configuration

#### PUT /purchases/configuration
Update purchase configuration
```json
{
  "auto_purchase_enabled": true,
  "max_daily_spend": 1000,
  "default_quantity": 2,
  "preferred_delivery_method": "email",
  "notification_preferences": {
    "purchase_success": true,
    "purchase_failure": true,
    "queue_updates": true,
    "price_drops": false
  },
  "risk_settings": {
    "max_price_increase_percent": 10,
    "require_manual_approval_above": 500,
    "max_attempts_per_ticket": 3
  }
}
```

---

### Category Management

#### GET /categories
Get all categories with ticket counts
- Query parameters: `search`, `sport_type`, `is_active`, `sort`, `direction`, `per_page`

#### GET /categories/statistics
Get category statistics summary

#### GET /categories/sport-types
Get available sport types

#### GET /categories/{id}
Get category details with statistics

#### GET /categories/{id}/tickets
Get tickets for specific category
- Query parameters: `platform`, `is_available`, `is_high_demand`, `min_price`, `max_price`, `search`, `sort`, `direction`, `per_page`

#### POST /categories (Admin only)
Create new category
```json
{
  "name": "NBA Basketball",
  "description": "Professional basketball games",
  "sport_type": "Basketball",
  "is_active": true,
  "metadata": {}
}
```

#### PUT /categories/{id} (Admin only)
Update category

#### DELETE /categories/{id} (Admin only)
Delete category (only if no associated tickets)

---

### Platform-Specific Scraping

#### StubHub (/stubhub)
- POST /search - Search events
- POST /event-details - Get event details
- GET /stats - Get platform statistics
- POST /import (Agent/Admin) - Import tickets
- POST /import-urls (Agent/Admin) - Import from URLs

#### Ticketmaster (/ticketmaster)
- POST /search - Search events
- POST /event-details - Get event details
- GET /stats - Get platform statistics
- POST /import (Agent/Admin) - Import tickets
- POST /import-urls (Agent/Admin) - Import from URLs

#### Viagogo (/viagogo)
- POST /search - Search events
- POST /event-details - Get event details
- GET /stats - Get platform statistics
- POST /import (Agent/Admin) - Import tickets
- POST /import-urls (Agent/Admin) - Import from URLs

#### TickPick (/tickpick)
- POST /search - Search events
- POST /event-details - Get event details
- GET /stats - Get platform statistics
- POST /import (Agent/Admin) - Import tickets
- POST /import-urls (Agent/Admin) - Import from URLs

---

### Dashboard & Analytics

#### GET /dashboard/stats
Get dashboard statistics

#### GET /dashboard/analytics
Get analytics data

#### GET /dashboard/platform-health
Get platform health status

#### GET /analytics/overview
Get analytics overview

#### GET /analytics/ticket-trends
Get ticket trends analysis

#### GET /analytics/platform-performance
Get platform performance metrics

#### GET /enhanced-analytics/charts
Get chart data for analytics

#### GET /enhanced-analytics/insights/predictive
Get predictive insights

---

### Monitoring

#### GET /monitoring/stats
Get real-time monitoring statistics

#### GET /monitoring/platform-health
Get platform health metrics

#### GET /monitoring/system-metrics
Get system performance metrics

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "meta": { ... } // For paginated responses
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... } // Validation errors
}
```

### Pagination Meta
```json
{
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 20,
    "to": 20,
    "total": 200
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

## Status Codes
- 200: Success
- 201: Created
- 204: No Content
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 429: Rate Limited
- 500: Server Error

## Supported Platforms
- StubHub
- Ticketmaster
- Viagogo
- TickPick
- SeatGeek
- AXS
- Eventbrite
- LiveNation

## Features Supported
1. **Ticket Scraping**: Multi-platform scraping with real-time monitoring
2. **Smart Alerts**: Configurable alerts with multiple notification channels
3. **Automated Purchasing**: Queue-based purchase automation with risk management
4. **Analytics**: Comprehensive analytics and reporting
5. **User Management**: Role-based access control
6. **Real-time Monitoring**: System health and performance monitoring
7. **Data Export**: Multiple export formats (CSV, Excel, PDF, JSON)

## Integration Examples

### JavaScript/Node.js
```javascript
const axios = require('axios');

const api = axios.create({
  baseURL: 'https://your-domain.com/api/v1/',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json'
  }
});

// Start scraping
const response = await api.post('/scraping/start-scraping', {
  platforms: ['stubhub', 'ticketmaster'],
  keywords: 'NBA Finals',
  max_price: 500
});
```

### Python
```python
import requests

headers = {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json'
}

# Create alert
response = requests.post(
    'https://your-domain.com/api/v1/alerts',
    json={
        'name': 'Concert Alert',
        'keywords': 'Taylor Swift',
        'max_price': 300
    },
    headers=headers
)
```

### cURL
```bash
# Get scraped tickets
curl -X GET \
  "https://your-domain.com/api/v1/scraping/tickets?platform=stubhub&is_available=true" \
  -H "Authorization: Bearer your-token-here"
```

This API provides comprehensive access to all major features of the HDTickets system, enabling external integrations and custom applications to leverage the platform's capabilities effectively.
