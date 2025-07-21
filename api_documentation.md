# hdTickets RESTful API Documentation

## Overview
This document defines the RESTful API structure for the hdTickets high-demand ticket monitoring system. The API supports authentication, account management, monitoring criteria configuration, dashboard data, purchase history tracking, and system settings management.

## Base URL
```
https://api.hdtickets.com/api/v1
```

## Authentication
All API endpoints except authentication routes require a Bearer token in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

---

## 1. Authentication Routes: `/api/auth/*`

### POST `/api/auth/register`
Register a new user account.

**Request Body:**
```json
{
  "username": "string (3-50 chars, unique, alphanumeric + underscore)",
  "email": "string (valid email format, max 191 chars)",
  "password": "string (min 8 chars, must contain uppercase, lowercase, number)",
  "first_name": "string (optional, max 100 chars)",
  "last_name": "string (optional, max 100 chars)",
  "phone": "string (optional, valid phone format)",
  "timezone": "string (optional, valid timezone, default: UTC)"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "User registered successfully. Please verify your email.",
  "data": {
    "user": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "username": "johnsmith",
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Smith",
      "status": "active",
      "created_at": "2024-01-15T10:30:00Z"
    }
  }
}
```

**Validation Rules:**
- Username: Required, 3-50 characters, alphanumeric + underscore only, unique
- Email: Required, valid email format, max 191 characters, unique
- Password: Required, minimum 8 characters, must contain uppercase, lowercase, number
- Phone: Optional, valid phone number format
- Timezone: Optional, valid timezone identifier

### POST `/api/auth/login`
Authenticate user and receive access token.

**Request Body:**
```json
{
  "email": "string (required)",
  "password": "string (required)",
  "remember_me": "boolean (optional, default: false)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_at": "2024-01-16T10:30:00Z",
    "user": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "username": "johnsmith",
      "email": "john@example.com",
      "status": "active"
    }
  }
}
```

**Validation Rules:**
- Email: Required, valid email format
- Password: Required, non-empty string
- Rate limiting: 5 attempts per minute per IP

### POST `/api/auth/logout`
Invalidate current authentication token.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

### POST `/api/auth/refresh`
Refresh authentication token.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2024-01-16T10:30:00Z"
  }
}
```

### POST `/api/auth/forgot-password`
Request password reset email.

**Request Body:**
```json
{
  "email": "string (required, valid email)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Password reset email sent if account exists"
}
```

---

## 2. Account Management: `/api/accounts/*`

### GET `/api/accounts/profile`
Retrieve current user profile information.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "user": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "username": "johnsmith",
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Smith",
      "phone": "+44 7123 456789",
      "timezone": "Europe/London",
      "status": "active",
      "email_verified_at": "2024-01-15T10:35:00Z",
      "two_factor_enabled": false,
      "last_login_at": "2024-01-15T09:15:00Z",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  }
}
```

### PUT `/api/accounts/profile`
Update user profile information.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "first_name": "string (optional, max 100 chars)",
  "last_name": "string (optional, max 100 chars)",
  "phone": "string (optional, valid phone format)",
  "timezone": "string (optional, valid timezone)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "user": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "first_name": "John",
      "last_name": "Smith",
      "phone": "+44 7123 456789",
      "timezone": "Europe/London",
      "updated_at": "2024-01-15T11:45:00Z"
    }
  }
}
```

### PUT `/api/accounts/password`
Change user password.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "current_password": "string (required)",
  "new_password": "string (required, min 8 chars)",
  "new_password_confirmation": "string (required, must match new_password)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Password updated successfully"
}
```

### GET `/api/accounts/ticket-accounts`
Retrieve user's ticket platform accounts.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "ticket_accounts": [
      {
        "uuid": "660e8400-e29b-41d4-a716-446655440001",
        "account_type": "ticketmaster",
        "account_name": "My Ticketmaster Account",
        "validation_status": "valid",
        "last_validated_at": "2024-01-15T08:00:00Z",
        "is_active": true,
        "usage_count": 45,
        "last_used_at": "2024-01-15T07:30:00Z",
        "created_at": "2024-01-10T14:20:00Z"
      }
    ]
  }
}
```

### POST `/api/accounts/ticket-accounts`
Add new ticket platform account.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "account_type": "enum (ticketmaster|manchester_united|other)",
  "account_name": "string (required, max 100 chars)",
  "username": "string (required, will be encrypted)",
  "password": "string (required, will be encrypted)",
  "additional_data": "object (optional, security questions, phone, etc.)"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Ticket account added successfully",
  "data": {
    "ticket_account": {
      "uuid": "770e8400-e29b-41d4-a716-446655440002",
      "account_type": "ticketmaster",
      "account_name": "My Ticketmaster Account",
      "validation_status": "unknown",
      "is_active": true,
      "created_at": "2024-01-15T12:00:00Z"
    }
  }
}
```

### PUT `/api/accounts/ticket-accounts/{uuid}`
Update ticket platform account.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "account_name": "string (optional, max 100 chars)",
  "username": "string (optional, will be encrypted)",
  "password": "string (optional, will be encrypted)",
  "additional_data": "object (optional)",
  "is_active": "boolean (optional)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Ticket account updated successfully"
}
```

### DELETE `/api/accounts/ticket-accounts/{uuid}`
Delete ticket platform account.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "message": "Ticket account deleted successfully"
}
```

---

## 3. Monitoring Criteria: `/api/criteria/*`

### GET `/api/criteria`
List user's monitoring criteria with filtering and pagination.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 15, max: 100)
- `is_active` (boolean, optional)
- `priority_level` (integer, 1-10, optional)
- `account_type` (enum: ticketmaster|manchester_united|other, optional)
- `sort_by` (string: created_at|priority_level|next_check_at, default: created_at)
- `sort_direction` (string: asc|desc, default: desc)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "criteria": [
      {
        "uuid": "880e8400-e29b-41d4-a716-446655440003",
        "name": "Manchester United vs Liverpool - Premium Seats",
        "description": "Looking for premium seats for the big match",
        "event_keywords": ["Manchester United", "Liverpool"],
        "venue_keywords": ["Old Trafford"],
        "date_range_start": "2024-02-01",
        "date_range_end": "2024-02-15",
        "time_range_start": "15:00:00",
        "time_range_end": "17:00:00",
        "price_range_min": 50.00,
        "price_range_max": 200.00,
        "currency": "GBP",
        "seat_preferences": {
          "sections": ["East Stand", "West Stand"],
          "accessibility": false,
          "min_together": 2
        },
        "ticket_quantity_min": 2,
        "ticket_quantity_max": 4,
        "priority_level": 1,
        "auto_purchase_enabled": true,
        "notification_enabled": true,
        "is_active": true,
        "monitoring_frequency": "continuous",
        "last_checked_at": "2024-01-15T11:30:00Z",
        "next_check_at": "2024-01-15T11:35:00Z",
        "ticket_account": {
          "uuid": "660e8400-e29b-41d4-a716-446655440001",
          "account_type": "manchester_united",
          "account_name": "My Man Utd Account"
        },
        "created_at": "2024-01-10T09:15:00Z",
        "updated_at": "2024-01-15T10:20:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 23,
      "total_pages": 2,
      "has_next": true,
      "has_previous": false
    }
  }
}
```

### POST `/api/criteria`
Create new monitoring criterion.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "ticket_account_uuid": "string (required, valid ticket account UUID)",
  "name": "string (required, max 150 chars)",
  "description": "string (optional)",
  "event_keywords": "array of strings (optional)",
  "venue_keywords": "array of strings (optional)",
  "date_range_start": "date (optional, YYYY-MM-DD format)",
  "date_range_end": "date (optional, YYYY-MM-DD format)",
  "time_range_start": "time (optional, HH:MM:SS format)",
  "time_range_end": "time (optional, HH:MM:SS format)",
  "price_range_min": "decimal (optional, min 0.01)",
  "price_range_max": "decimal (optional, must be >= price_range_min)",
  "currency": "string (optional, 3-char code, default: GBP)",
  "seat_preferences": "object (optional)",
  "ticket_quantity_min": "integer (optional, min 1, default: 1)",
  "ticket_quantity_max": "integer (optional, max 10, must be >= min)",
  "priority_level": "integer (optional, 1-10, default: 5)",
  "auto_purchase_enabled": "boolean (optional, default: false)",
  "notification_enabled": "boolean (optional, default: true)",
  "monitoring_frequency": "enum (continuous|hourly|daily|weekly, default: hourly)"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Monitoring criterion created successfully",
  "data": {
    "criterion": {
      "uuid": "990e8400-e29b-41d4-a716-446655440004",
      "name": "Manchester United vs Liverpool - Premium Seats",
      "is_active": true,
      "priority_level": 1,
      "next_check_at": "2024-01-15T12:05:00Z",
      "created_at": "2024-01-15T12:00:00Z"
    }
  }
}
```

**Validation Rules:**
- Name: Required, max 150 characters
- Ticket account UUID: Required, must belong to authenticated user
- Date ranges: End date must be after start date if both provided
- Price ranges: Max must be greater than or equal to min if both provided
- Quantity ranges: Max must be greater than or equal to min
- Priority level: Integer between 1-10 (1 = highest priority)

### GET `/api/criteria/{uuid}`
Get specific monitoring criterion details.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "criterion": {
      // ... full criterion object as shown above
      "recent_checks": [
        {
          "uuid": "aa0e8400-e29b-41d4-a716-446655440005",
          "status": "success",
          "tickets_found": 3,
          "started_at": "2024-01-15T11:30:00Z",
          "completed_at": "2024-01-15T11:30:15Z"
        }
      ]
    }
  }
}
```

### PUT `/api/criteria/{uuid}`
Update monitoring criterion.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** (Same as POST, all fields optional)

**Response 200:**
```json
{
  "success": true,
  "message": "Monitoring criterion updated successfully"
}
```

### DELETE `/api/criteria/{uuid}`
Delete monitoring criterion.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "message": "Monitoring criterion deleted successfully"
}
```

### POST `/api/criteria/{uuid}/toggle`
Toggle criterion active status.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "message": "Monitoring criterion status updated",
  "data": {
    "is_active": true
  }
}
```

### POST `/api/criteria/{uuid}/check-now`
Manually trigger immediate check for this criterion.

**Headers:** `Authorization: Bearer {token}`

**Response 202:**
```json
{
  "success": true,
  "message": "Manual check initiated",
  "data": {
    "check_uuid": "bb0e8400-e29b-41d4-a716-446655440006",
    "estimated_completion": "2024-01-15T12:02:00Z"
  }
}
```

---

## 4. Dashboard Data: `/api/dashboard/*`

### GET `/api/dashboard`
Retrieve comprehensive dashboard data for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `period` (string: 1h|24h|7d|30d, default: 24h)
- `timezone` (string: optional, user timezone for date calculations)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "active_criteria": 12,
      "total_checks_today": 456,
      "successful_purchases": 3,
      "pending_notifications": 2,
      "active_accounts": 4,
      "system_uptime": "99.8%"
    },
    "recent_activity": [
      {
        "type": "purchase_success",
        "title": "Tickets purchased successfully",
        "description": "2 tickets for Manchester United vs Liverpool",
        "timestamp": "2024-01-15T11:45:00Z",
        "priority": "high",
        "data": {
          "event_name": "Manchester United vs Liverpool",
          "quantity": 2,
          "total_price": 180.00,
          "currency": "GBP"
        }
      },
      {
        "type": "ticket_found",
        "title": "New tickets available",
        "description": "Premium seats found matching your criteria",
        "timestamp": "2024-01-15T11:30:00Z",
        "priority": "medium"
      }
    ],
    "performance_metrics": {
      "checks_per_hour": [
        { "hour": "10:00", "count": 45 },
        { "hour": "11:00", "count": 52 }
      ],
      "success_rate": 94.2,
      "average_response_time": 1.8,
      "platform_status": {
        "ticketmaster": "operational",
        "manchester_united": "degraded"
      }
    },
    "upcoming_events": [
      {
        "uuid": "cc0e8400-e29b-41d4-a716-446655440007",
        "event_name": "Manchester United vs Liverpool",
        "event_date": "2024-02-10",
        "event_time": "15:00:00",
        "venue_name": "Old Trafford",
        "status": "on_sale",
        "monitoring_criteria_count": 3
      }
    ],
    "alerts": [
      {
        "type": "warning",
        "message": "Ticketmaster account validation expired",
        "action_required": true,
        "timestamp": "2024-01-15T10:00:00Z"
      }
    ]
  }
}
```

### GET `/api/dashboard/activity-feed`
Get paginated activity feed.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 20, max: 50)
- `type` (string: optional, filter by activity type)
- `since` (datetime: optional, ISO 8601 format)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "activities": [
      // ... activity objects as shown above
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 145,
      "has_next": true
    }
  }
}
```

### GET `/api/dashboard/analytics`
Get detailed analytics data.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `period` (string: 1h|24h|7d|30d|90d, default: 7d)
- `metric` (string: checks|purchases|notifications|performance, default: all)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "period": "7d",
    "checks": {
      "total": 2456,
      "successful": 2312,
      "failed": 144,
      "success_rate": 94.1,
      "daily_breakdown": [
        { "date": "2024-01-15", "total": 456, "successful": 430 }
      ]
    },
    "purchases": {
      "total": 12,
      "successful": 11,
      "failed": 1,
      "total_value": 1240.00,
      "currency": "GBP",
      "average_price": 112.73
    },
    "performance": {
      "average_response_time": 1.9,
      "peak_response_time": 4.2,
      "uptime_percentage": 99.8
    }
  }
}
```

---

## 5. Purchase History: `/api/purchases/*`

### GET `/api/purchases`
Retrieve user's purchase history with filtering and pagination.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (integer, default: 1)
- `per_page` (integer, default: 15, max: 100)
- `status` (enum: success|failed|in_progress|cancelled, optional)
- `date_from` (date: YYYY-MM-DD, optional)
- `date_to` (date: YYYY-MM-DD, optional)
- `event_name` (string: partial match, optional)
- `sort_by` (string: started_at|total_price|event_date, default: started_at)
- `sort_direction` (string: asc|desc, default: desc)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "purchases": [
      {
        "uuid": "dd0e8400-e29b-41d4-a716-446655440008",
        "status": "success",
        "ticket_quantity": 2,
        "ticket_price_each": 90.00,
        "total_price": 180.00,
        "fees": 18.00,
        "currency": "GBP",
        "seat_details": {
          "section": "East Stand",
          "row": "15",
          "seats": ["126", "127"]
        },
        "order_reference": "TM-2024-001234",
        "confirmation_number": "CONF-567890",
        "payment_method": "credit_card",
        "started_at": "2024-01-15T11:45:00Z",
        "completed_at": "2024-01-15T11:47:30Z",
        "event": {
          "uuid": "cc0e8400-e29b-41d4-a716-446655440007",
          "event_name": "Manchester United vs Liverpool",
          "event_date": "2024-02-10",
          "event_time": "15:00:00",
          "venue_name": "Old Trafford"
        },
        "monitoring_criterion": {
          "uuid": "880e8400-e29b-41d4-a716-446655440003",
          "name": "Manchester United vs Liverpool - Premium Seats"
        },
        "ticket_account": {
          "account_type": "manchester_united",
          "account_name": "My Man Utd Account"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 28,
      "total_pages": 2
    },
    "summary": {
      "total_purchases": 28,
      "successful_purchases": 25,
      "total_spent": 2340.00,
      "currency": "GBP",
      "average_price": 93.60
    }
  }
}
```

### GET `/api/purchases/{uuid}`
Get detailed information about specific purchase.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "purchase": {
      // ... full purchase object as shown above
      "attempt_history": [
        {
          "attempt_number": 1,
          "status": "success",
          "started_at": "2024-01-15T11:45:00Z",
          "completed_at": "2024-01-15T11:47:30Z",
          "response_time_ms": 2500
        }
      ],
      "notifications_sent": [
        {
          "type": "purchase_success",
          "channel": "email",
          "sent_at": "2024-01-15T11:48:00Z"
        }
      ]
    }
  }
}
```

### POST `/api/purchases/{uuid}/retry`
Retry failed purchase attempt.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "reason": "string (optional, reason for retry)"
}
```

**Response 202:**
```json
{
  "success": true,
  "message": "Purchase retry initiated",
  "data": {
    "new_attempt_uuid": "ee0e8400-e29b-41d4-a716-446655440009",
    "estimated_completion": "2024-01-15T12:05:00Z"
  }
}
```

### GET `/api/purchases/statistics`
Get purchase statistics for the authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `period` (string: 7d|30d|90d|1y, default: 30d)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "period": "30d",
    "total_attempts": 45,
    "successful_purchases": 42,
    "failed_purchases": 3,
    "success_rate": 93.3,
    "total_spent": 3780.00,
    "currency": "GBP",
    "average_price_per_ticket": 89.52,
    "most_purchased_event_type": "Football",
    "preferred_price_range": {
      "min": 45.00,
      "max": 150.00
    },
    "platform_breakdown": {
      "ticketmaster": 15,
      "manchester_united": 27,
      "other": 3
    }
  }
}
```

---

## 6. System Settings: `/api/settings/*`

### GET `/api/settings`
Retrieve user's system settings and preferences.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "settings": {
      "notifications": {
        "email_enabled": true,
        "sms_enabled": false,
        "push_enabled": true,
        "webhook_enabled": false,
        "webhook_url": null,
        "frequency": {
          "ticket_found": "immediate",
          "purchase_success": "immediate",
          "purchase_failed": "immediate",
          "account_issues": "immediate",
          "system_updates": "daily"
        },
        "quiet_hours": {
          "enabled": true,
          "start": "22:00",
          "end": "08:00",
          "timezone": "Europe/London"
        }
      },
      "monitoring": {
        "max_concurrent_checks": 10,
        "default_check_frequency": "hourly",
        "auto_pause_on_failure": true,
        "failure_threshold": 5,
        "proxy_rotation_enabled": true,
        "respect_rate_limits": true
      },
      "purchasing": {
        "auto_purchase_enabled": false,
        "max_price_per_ticket": 200.00,
        "max_total_spend_per_day": 500.00,
        "require_seat_together": true,
        "preferred_payment_method": "credit_card",
        "purchase_timeout_seconds": 300
      },
      "dashboard": {
        "default_period": "24h",
        "show_all_users_stats": false,
        "refresh_interval_seconds": 30,
        "theme": "light",
        "timezone": "Europe/London"
      },
      "privacy": {
        "data_retention_days": 365,
        "share_analytics": false,
        "activity_logging": true
      }
    }
  }
}
```

### PUT `/api/settings`
Update user's system settings.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** (All settings are optional - only send what you want to update)
```json
{
  "notifications": {
    "email_enabled": "boolean (optional)",
    "sms_enabled": "boolean (optional)",
    "push_enabled": "boolean (optional)",
    "webhook_enabled": "boolean (optional)",
    "webhook_url": "string (optional, valid URL if webhook_enabled is true)",
    "frequency": {
      "ticket_found": "enum (immediate|hourly|daily|disabled, optional)",
      "purchase_success": "enum (immediate|hourly|daily|disabled, optional)",
      "purchase_failed": "enum (immediate|hourly|daily|disabled, optional)",
      "account_issues": "enum (immediate|hourly|daily|disabled, optional)",
      "system_updates": "enum (immediate|hourly|daily|disabled, optional)"
    },
    "quiet_hours": {
      "enabled": "boolean (optional)",
      "start": "time (optional, HH:MM format)",
      "end": "time (optional, HH:MM format)",
      "timezone": "string (optional, valid timezone)"
    }
  },
  "monitoring": {
    "max_concurrent_checks": "integer (optional, 1-50, default: 10)",
    "default_check_frequency": "enum (continuous|hourly|daily|weekly, optional)",
    "auto_pause_on_failure": "boolean (optional)",
    "failure_threshold": "integer (optional, 1-20, default: 5)",
    "proxy_rotation_enabled": "boolean (optional)",
    "respect_rate_limits": "boolean (optional)"
  },
  "purchasing": {
    "auto_purchase_enabled": "boolean (optional)",
    "max_price_per_ticket": "decimal (optional, min 1.00, max 10000.00)",
    "max_total_spend_per_day": "decimal (optional, min 1.00, max 50000.00)",
    "require_seat_together": "boolean (optional)",
    "preferred_payment_method": "enum (credit_card|debit_card|paypal, optional)",
    "purchase_timeout_seconds": "integer (optional, 60-600, default: 300)"
  },
  "dashboard": {
    "default_period": "enum (1h|24h|7d|30d, optional)",
    "show_all_users_stats": "boolean (optional, admin only)",
    "refresh_interval_seconds": "integer (optional, 15-300, default: 30)",
    "theme": "enum (light|dark|auto, optional)",
    "timezone": "string (optional, valid timezone)"
  },
  "privacy": {
    "data_retention_days": "integer (optional, 30-1095, default: 365)",
    "share_analytics": "boolean (optional)",
    "activity_logging": "boolean (optional)"
  }
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    "updated_fields": [
      "notifications.email_enabled",
      "monitoring.max_concurrent_checks",
      "purchasing.auto_purchase_enabled"
    ]
  }
}
```

**Validation Rules:**
- Webhook URL: Required if webhook_enabled is true, must be valid HTTPS URL
- Time fields: Must be in HH:MM format
- Timezone: Must be valid timezone identifier
- Numeric limits: Enforced as specified in field descriptions
- Quiet hours: End time can be earlier than start time (crosses midnight)

### GET `/api/settings/notifications/test`
Send test notification to verify settings.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `channels` (string: comma-separated list of email,sms,push,webhook)

**Response 200:**
```json
{
  "success": true,
  "message": "Test notifications sent",
  "data": {
    "sent_channels": ["email", "push"],
    "failed_channels": []
  }
}
```

### POST `/api/settings/reset`
Reset settings to default values.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "sections": ["array of strings (optional): notifications, monitoring, purchasing, dashboard, privacy"]
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Settings reset to defaults",
  "data": {
    "reset_sections": ["notifications", "monitoring"]
  }
}
```

---

## Error Response Format

All API endpoints use consistent error response format:

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."],
      "password": ["The password must be at least 8 characters."]
    }
  },
  "timestamp": "2024-01-15T12:00:00Z",
  "request_id": "req_12345"
}
```

### HTTP Status Codes
- `200` - OK (Success)
- `201` - Created (Resource created successfully)
- `202` - Accepted (Request accepted for processing)
- `400` - Bad Request (Validation error or malformed request)
- `401` - Unauthorized (Authentication required or invalid token)
- `403` - Forbidden (Access denied)
- `404` - Not Found (Resource not found)
- `409` - Conflict (Resource already exists or conflict)
- `422` - Unprocessable Entity (Validation failed)
- `429` - Too Many Requests (Rate limit exceeded)
- `500` - Internal Server Error (Server error)
- `503` - Service Unavailable (System maintenance)

---

## Rate Limiting

API endpoints have the following rate limits:

- **Authentication endpoints**: 5 requests per minute per IP
- **General API endpoints**: 100 requests per minute per user
- **Dashboard endpoints**: 200 requests per minute per user
- **Manual check triggers**: 10 requests per minute per user

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642248000
```

---

## Pagination

Paginated endpoints return data in this format:

```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "total_pages": 10,
    "has_next": true,
    "has_previous": false,
    "next_page_url": "https://api.hdtickets.com/api/v1/resource?page=2",
    "prev_page_url": null
  }
}
```

---

## WebSocket Events (Real-time Updates)

The system broadcasts real-time events via WebSocket connections:

### Event Types:
- `ticket.found` - New tickets matching user criteria
- `purchase.success` - Successful ticket purchase
- `purchase.failed` - Failed purchase attempt  
- `account.validation_failed` - Ticket account validation failed
- `system.maintenance` - System maintenance notifications

### Event Format:
```json
{
  "event": "ticket.found",
  "data": {
    "criterion_uuid": "880e8400-e29b-41d4-a716-446655440003",
    "event_name": "Manchester United vs Liverpool",
    "tickets_available": 4,
    "price_range": {
      "min": 85.00,
      "max": 120.00
    },
    "timestamp": "2024-01-15T12:00:00Z"
  },
  "user_uuid": "550e8400-e29b-41d4-a716-446655440000"
}
```

This comprehensive API structure supports all the core functionality of the hdTickets system while maintaining RESTful principles, proper validation, and scalable architecture for handling 1000+ concurrent users.
