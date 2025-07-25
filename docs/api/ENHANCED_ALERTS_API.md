# Enhanced Alert System API Documentation

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens unless otherwise specified.

```bash
# Include token in headers
Authorization: Bearer {your-token}
```

## Base URL

```
https://your-domain.com/api/
```

## Notification Preferences API

### Get User Preferences

**GET** `/notifications/preferences`

Retrieve all notification preferences for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "notification_channels": {
      "critical": "slack",
      "high": "discord",
      "medium": "telegram",
      "normal": "push"
    },
    "favorite_teams": ["Lakers", "Warriors"],
    "price_drop_threshold": 20,
    "quiet_hours": {
      "enabled": true,
      "start": "23:00",
      "end": "07:00"
    },
    "escalation_enabled": true,
    "ml_predictions_enabled": true
  }
}
```

### Update Preferences

**PUT** `/notifications/preferences`

Update user notification preferences.

**Request Body:**
```json
{
  "notification_channels": {
    "critical": "slack",
    "high": "discord"
  },
  "favorite_teams": ["Lakers", "Celtics"],
  "price_drop_threshold": 15,
  "quiet_hours": {
    "enabled": true,
    "start": "22:00",
    "end": "08:00"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Preferences updated successfully",
  "data": {
    "updated_preferences": 5
  }
}
```

### Reset Preferences

**POST** `/notifications/preferences/reset`

Reset user preferences to system defaults.

**Response:**
```json
{
  "success": true,
  "message": "Preferences reset to defaults"
}
```

### Get Specific Preference

**GET** `/notifications/preferences/{key}`

Get a specific preference value.

**Parameters:**
- `key` (string): Preference key (e.g., 'notification_channels', 'favorite_teams')

**Response:**
```json
{
  "success": true,
  "data": {
    "key": "favorite_teams",
    "value": ["Lakers", "Warriors"],
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

## Notification Channels API

### List User Channels

**GET** `/notifications/channels`

Get all configured notification channels for the user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "channel": "slack",
      "webhook_url": "https://hooks.slack.com/...",
      "channel_name": "#ticket-alerts",
      "is_enabled": true,
      "is_configured": true,
      "last_used_at": "2024-01-15T10:30:00Z",
      "delivery_count": 42,
      "success_rate": 98.5
    },
    {
      "id": 2,
      "channel": "discord",
      "webhook_url": "https://discord.com/api/webhooks/...",
      "ping_role_id": "123456789",
      "is_enabled": true,
      "is_configured": true,
      "last_used_at": "2024-01-15T09:15:00Z",
      "delivery_count": 38,
      "success_rate": 97.2
    }
  ]
}
```

### Add Channel

**POST** `/notifications/channels`

Configure a new notification channel.

**Request Body:**
```json
{
  "channel": "slack",
  "webhook_url": "https://hooks.slack.com/services/...",
  "channel_name": "#alerts",
  "settings": {
    "mention_users": ["@john", "@jane"],
    "thread_replies": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Channel configured successfully",
  "data": {
    "id": 3,
    "channel": "slack",
    "is_enabled": true,
    "test_required": true
  }
}
```

### Update Channel

**PUT** `/notifications/channels/{channel}`

Update channel configuration.

**Parameters:**
- `channel` (string): Channel type or ID

**Request Body:**
```json
{
  "webhook_url": "https://hooks.slack.com/services/new-url",
  "is_enabled": true,
  "settings": {
    "mention_users": ["@admin"],
    "thread_replies": false
  }
}
```

### Test Channel

**POST** `/notifications/channels/{channel}/test`

Send a test notification to verify channel configuration.

**Response:**
```json
{
  "success": true,
  "message": "Test notification sent successfully",
  "data": {
    "delivery_time": 1.23,
    "response_code": 200,
    "channel_status": "healthy"
  }
}
```

### Toggle Channel

**POST** `/notifications/channels/{channel}/toggle`

Enable or disable a notification channel.

**Response:**
```json
{
  "success": true,
  "message": "Channel toggled successfully",
  "data": {
    "is_enabled": false
  }
}
```

## Enhanced Alerts Management API

### Get Alert Status

**GET** `/enhanced-alerts/status`

Get current alert system status and recent activity.

**Response:**
```json
{
  "success": true,
  "data": {
    "system_status": "operational",
    "alerts_sent_today": 15,
    "escalations_active": 2,
    "ml_predictions_enabled": true,
    "average_delivery_time": 2.1,
    "success_rate": 98.7,
    "recent_alerts": [
      {
        "id": 123,
        "ticket_id": 456,
        "priority": 4,
        "priority_label": "High",
        "event_name": "Lakers vs Warriors",
        "sent_at": "2024-01-15T10:30:00Z",
        "channels_used": ["slack", "discord"],
        "delivery_status": "delivered",
        "user_action": "acknowledged"
      }
    ]
  }
}
```

### Get Active Escalations

**GET** `/enhanced-alerts/escalations`

Get all active escalations for the user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "alert_id": 123,
      "ticket_id": 456,
      "priority": 5,
      "escalation_level": 2,
      "next_escalation_at": "2024-01-15T10:45:00Z",
      "attempts": 2,
      "max_attempts": 5,
      "channels_tried": ["slack", "discord"],
      "next_channels": ["sms", "phone"],
      "reason": "User inactive for 15 minutes",
      "can_cancel": true
    }
  ]
}
```

### Cancel Escalation

**POST** `/enhanced-alerts/escalations/{escalation}/cancel`

Cancel an active escalation.

**Response:**
```json
{
  "success": true,
  "message": "Escalation cancelled successfully",
  "data": {
    "escalation_id": 789,
    "cancelled_at": "2024-01-15T10:35:00Z"
  }
}
```

### Get Ticket Predictions

**GET** `/enhanced-alerts/predictions/{ticket}`

Get ML predictions for a specific ticket.

**Parameters:**
- `ticket` (integer): Ticket ID

**Response:**
```json
{
  "success": true,
  "data": {
    "ticket_id": 456,
    "predictions": {
      "availability_trend": "decreasing",
      "availability_change": -25,
      "price_trend": "increasing", 
      "price_change": 15,
      "demand_level": "high",
      "demand_score": 0.85,
      "confidence": 0.92
    },
    "recommendations": [
      {
        "type": "urgency",
        "message": "Tickets are selling fast. Purchase immediately to secure your spot.",
        "priority": "high",
        "confidence": 0.89
      },
      {
        "type": "price",
        "message": "Price is likely to increase by 10-20% in the next 6 hours.",
        "priority": "medium",
        "confidence": 0.76
      }
    ],
    "historical_accuracy": {
      "availability_predictions": 0.84,
      "price_predictions": 0.78,
      "demand_predictions": 0.91
    },
    "generated_at": "2024-01-15T10:30:00Z"
  }
}
```

### Submit Prediction Feedback

**POST** `/enhanced-alerts/feedback/prediction`

Submit feedback on ML prediction accuracy.

**Request Body:**
```json
{
  "ticket_id": 456,
  "prediction_id": "pred_123",
  "feedback": {
    "availability_accurate": true,
    "price_accurate": false,
    "demand_accurate": true,
    "purchased": true,
    "purchase_price": 125.00,
    "comments": "Price actually decreased, not increased"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Feedback submitted successfully",
  "data": {
    "feedback_id": "fb_789",
    "contribution_score": 5
  }
}
```

### Acknowledge Alert

**POST** `/enhanced-alerts/acknowledge/{alert}`

Acknowledge receipt of an alert.

**Response:**
```json
{
  "success": true,
  "message": "Alert acknowledged",
  "data": {
    "alert_id": 123,
    "acknowledged_at": "2024-01-15T10:35:00Z",
    "escalation_cancelled": true
  }
}
```

### Snooze Alert

**POST** `/enhanced-alerts/snooze/{alert}`

Snooze an alert for a specified duration.

**Request Body:**
```json
{
  "duration_minutes": 30,
  "reason": "In meeting"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Alert snoozed for 30 minutes",
  "data": {
    "alert_id": 123,
    "snoozed_until": "2024-01-15T11:05:00Z"
  }
}
```

## Analytics API

### Alert Analytics

**GET** `/analytics/alerts`

Get user-specific alert analytics.

**Query Parameters:**
- `period` (string): Time period ('day', 'week', 'month', 'year')
- `from` (date): Start date (YYYY-MM-DD)
- `to` (date): End date (YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "week",
    "total_alerts": 45,
    "alerts_by_priority": {
      "critical": 3,
      "high": 12,
      "medium": 20,
      "normal": 10
    },
    "delivery_stats": {
      "total_sent": 45,
      "delivered": 44,
      "failed": 1,
      "success_rate": 97.8
    },
    "user_engagement": {
      "acknowledged": 40,
      "snoozed": 3,
      "ignored": 2,
      "engagement_rate": 88.9
    },
    "average_response_time": 3.2,
    "escalations_triggered": 2,
    "tickets_purchased": 8,
    "conversion_rate": 17.8
  }
}
```

### Channel Performance

**GET** `/analytics/channels`

Get channel performance analytics.

**Response:**
```json
{
  "success": true,
  "data": {
    "channels": {
      "slack": {
        "total_sent": 25,
        "success_rate": 98.0,
        "avg_delivery_time": 1.2,
        "engagement_rate": 92.0,
        "user_satisfaction": 4.6
      },
      "discord": {
        "total_sent": 15,
        "success_rate": 96.7,
        "avg_delivery_time": 1.8,
        "engagement_rate": 86.7,
        "user_satisfaction": 4.4
      },
      "telegram": {
        "total_sent": 5,
        "success_rate": 100.0,
        "avg_delivery_time": 0.9,
        "engagement_rate": 100.0,
        "user_satisfaction": 4.8
      }
    },
    "recommendations": [
      "Telegram shows highest engagement rates",
      "Consider using Slack for critical alerts"
    ]
  }
}
```

## User Activity Tracking API

### Get Activity Status

**GET** `/activity/status`

Get current user activity status.

**Response:**
```json
{
  "success": true,
  "data": {
    "is_active": true,
    "last_activity": "2024-01-15T10:30:00Z",
    "activity_score": 85,
    "inactive_duration": 0,
    "escalation_risk": "low"
  }
}
```

### Send Heartbeat

**POST** `/activity/heartbeat`

Update user activity timestamp.

**Response:**
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:35:00Z"
}
```

## Webhook Endpoints

### Slack Event Handler

**POST** `/webhooks/slack/events`

Handle Slack events and interactions.

### Discord Event Handler

**POST** `/webhooks/discord/events`

Handle Discord webhook events.

### Telegram Webhook

**POST** `/webhooks/telegram/webhook`

Handle Telegram bot webhook events.

### Delivery Confirmation

**POST** `/webhooks/delivery/confirm`

Confirm delivery of notifications.

**Request Body:**
```json
{
  "notification_id": "notif_123",
  "channel": "slack",
  "status": "delivered",
  "timestamp": "2024-01-15T10:30:00Z",
  "metadata": {
    "message_id": "slack_msg_456",
    "channel_id": "C1234567890"
  }
}
```

## System Health API

### Health Check

**GET** `/system/health`

Get system health status.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:00Z",
  "services": {
    "enhanced_alerts": true,
    "ml_predictions": true,
    "escalation": true,
    "channels": {
      "slack": true,
      "discord": true,
      "telegram": true,
      "webhook": true
    }
  }
}
```

### System Metrics

**GET** `/system/metrics`

Get system performance metrics.

**Response:**
```json
{
  "queue_sizes": {
    "critical": 0,
    "high": 2,
    "medium": 5,
    "default": 8
  },
  "cache_stats": {
    "hit_ratio": "95%",
    "memory_usage": "45MB"
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## Error Responses

All endpoints may return the following error responses:

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated",
  "code": 401
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "This action is unauthorized",
  "code": 403
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid",
  "errors": {
    "webhook_url": ["The webhook url field is required."],
    "channel": ["The selected channel is invalid."]
  },
  "code": 422
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Internal server error",
  "code": 500
}
```

## Rate Limiting

Most endpoints are rate limited:
- **Authenticated requests**: 60 requests per minute
- **System endpoints**: 60 requests per minute
- **Webhook endpoints**: 1000 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1642248000
```

## SDKs and Examples

### JavaScript/Node.js Example

```javascript
const axios = require('axios');

const hdTicketsApi = axios.create({
  baseURL: 'https://your-domain.com/api/',
  headers: {
    'Authorization': 'Bearer your-token',
    'Content-Type': 'application/json'
  }
});

// Get user preferences
const preferences = await hdTicketsApi.get('/notifications/preferences');

// Update notification channels
await hdTicketsApi.put('/notifications/preferences', {
  notification_channels: {
    critical: 'slack',
    high: 'discord'
  }
});

// Test a channel
const testResult = await hdTicketsApi.post('/notifications/channels/slack/test');
```

### Python Example

```python
import requests

class HDTicketsAPI:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    
    def get_preferences(self):
        response = requests.get(
            f'{self.base_url}/notifications/preferences',
            headers=self.headers
        )
        return response.json()
    
    def test_channel(self, channel):
        response = requests.post(
            f'{self.base_url}/notifications/channels/{channel}/test',
            headers=self.headers
        )
        return response.json()

# Usage
api = HDTicketsAPI('https://your-domain.com/api', 'your-token')
preferences = api.get_preferences()
test_result = api.test_channel('slack')
```

### cURL Examples

```bash
# Get preferences
curl -X GET "https://your-domain.com/api/notifications/preferences" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json"

# Update preferences
curl -X PUT "https://your-domain.com/api/notifications/preferences" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "favorite_teams": ["Lakers", "Warriors"],
    "price_drop_threshold": 25
  }'

# Test Slack channel
curl -X POST "https://your-domain.com/api/notifications/channels/slack/test" \
  -H "Authorization: Bearer your-token"
```
