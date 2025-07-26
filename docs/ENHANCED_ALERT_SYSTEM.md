# HDTickets Enhanced Alert System

## Overview

The Enhanced Alert System is a comprehensive, intelligent notification system that provides smart prioritization, machine learning predictions, multi-channel delivery, and escalation capabilities for ticket alerts.

## 🎯 Key Features

### Smart Prioritization
- **Multi-factor Analysis**: Price drops, availability changes, time urgency, user preferences
- **Dynamic Priority Calculation**: Real-time adjustment based on market conditions
- **User Behavior Learning**: Adapts to individual user engagement patterns

### Machine Learning Integration
- **Availability Prediction**: Forecasts ticket availability trends
- **Price Movement Analysis**: Predicts price increases/decreases
- **Demand Forecasting**: Estimates demand levels for events
- **Contextual Recommendations**: AI-generated buying advice

### Multi-Channel Notifications
- **Slack**: Rich formatted messages with interactive buttons
- **Discord**: Embedded messages with role mentions and reactions
- **Telegram**: Bot-based messaging with markdown formatting
- **Webhooks**: Standardized JSON payloads for custom integrations

### Escalation & Retry System
- **Smart Escalation**: Priority-based escalation strategies
- **Multiple Channels**: SMS, phone calls, emergency contacts
- **Retry Logic**: Exponential backoff with configurable delays
- **Activity Monitoring**: Prevents unnecessary escalations for active users

## 🏗️ Architecture

### Core Components

```
Enhanced Alert System
├── EnhancedAlertSystem (Main orchestrator)
├── TicketAvailabilityPredictor (ML engine)
├── AlertEscalationService (Escalation management)
├── Notification Channels
│   ├── SlackNotificationChannel
│   ├── DiscordNotificationChannel
│   ├── TelegramNotificationChannel
│   └── WebhookNotificationChannel
├── Models
│   ├── AlertEscalation
│   ├── UserNotificationSettings
│   ├── TicketPriceHistory
│   └── UserPreference
└── Jobs
    └── ProcessEscalatedAlert
```

### Data Flow

1. **Ticket Detection**: Scraping system detects matching tickets
2. **Smart Analysis**: ML predictor analyzes market conditions
3. **Priority Calculation**: Multi-factor priority algorithm
4. **Channel Selection**: User preferences determine delivery channels
5. **Notification Delivery**: Multi-channel notification dispatch
6. **Escalation Monitoring**: Tracks user activity and escalates if needed
7. **Analytics Collection**: Performance metrics and user engagement tracking

## 🚀 Setup & Configuration

### 1. Environment Configuration

Copy the example environment variables:
```bash
cp .env.enhanced-alerts.example .env.local
```

Add to your main `.env` file:
```bash
# Core Settings
ENHANCED_ALERTS_ENABLED=true
ML_PREDICTIONS_ENABLED=true
ALERT_ESCALATION_ENABLED=true

# Slack Integration
SLACK_NOTIFICATIONS_ENABLED=true
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
SLACK_BOT_TOKEN=xoxb-your-bot-token

# Discord Integration
DISCORD_NOTIFICATIONS_ENABLED=true
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR/WEBHOOK
DISCORD_BOT_TOKEN=your-bot-token

# Telegram Integration
TELEGRAM_NOTIFICATIONS_ENABLED=true
TELEGRAM_BOT_TOKEN=your-bot-token
```

### 2. Database Migration

Run the migrations to create required tables:
```bash
php artisan migrate
```

### 3. Queue Configuration

Configure your queue workers for different priority levels:
```bash
# High priority queues
php artisan queue:work --queue=alerts-critical,notifications-critical,escalations

# Standard queues
php artisan queue:work --queue=alerts-high,alerts-medium,alerts-default
```

### 4. Service Provider Registration

The services are automatically registered. Ensure the configuration is published:
```bash
php artisan vendor:publish --tag=enhanced-alerts-config
```

## 📱 Channel Setup

### Slack Integration

#### Option 1: Webhook (Recommended for simple setups)
1. Create a Slack app at https://api.slack.com/apps
2. Add an Incoming Webhook
3. Copy the webhook URL to `SLACK_WEBHOOK_URL`

#### Option 2: Bot API (For advanced features)
1. Create a Slack app with Bot Token Scopes:
   - `chat:write`
   - `chat:write.public`
   - `users:read`
2. Install app to workspace
3. Copy Bot User OAuth Token to `SLACK_BOT_TOKEN`

### Discord Integration

#### Option 1: Webhook
1. Go to your Discord server settings
2. Create a webhook in the desired channel
3. Copy webhook URL to `DISCORD_WEBHOOK_URL`

#### Option 2: Bot API
1. Create Discord application at https://discord.com/developers/applications
2. Create a bot and copy token to `DISCORD_BOT_TOKEN`
3. Add bot to server with appropriate permissions

### Telegram Integration

1. Create a bot via @BotFather on Telegram
2. Get the bot token and set `TELEGRAM_BOT_TOKEN`
3. Get your chat ID:
   - Send a message to your bot
   - Visit: `https://api.telegram.org/bot<token>/getUpdates`
   - Copy your chat ID

### Webhook Integration

For custom integrations, configure webhook endpoints:
```php
// User notification settings
UserNotificationSettings::create([
    'user_id' => $userId,
    'channel' => 'webhook',
    'webhook_url' => 'https://your-app.com/webhooks/tickets',
    'auth_type' => 'bearer',
    'auth_token' => 'your-api-token',
    'is_enabled' => true
]);
```

## 🔧 Usage Examples

### Basic Alert Processing

```php
use App\Services\EnhancedAlertSystem;

$enhancedAlerts = new EnhancedAlertSystem();

// Process a smart alert
$enhancedAlerts->processSmartAlert($ticket, $alert);
```

### User Preference Management

```php
use App\Models\UserPreference;

// Initialize user preferences
UserPreference::initializeDefaults($userId);

// Update notification channels
UserPreference::setValue($userId, 'notification_channels', [
    'critical' => 'slack',
    'high' => 'discord', 
    'medium' => 'telegram',
    'normal' => 'push'
]);

// Set favorite teams for priority boost
UserPreference::setValue($userId, 'favorite_teams', [
    'Lakers', 'Warriors', 'Celtics'
]);
```

### Channel Configuration

```php
use App\Models\UserNotificationSettings;

// Configure Slack notifications
UserNotificationSettings::create([
    'user_id' => $userId,
    'channel' => 'slack',
    'webhook_url' => 'https://hooks.slack.com/...',
    'channel_name' => '#ticket-alerts',
    'is_enabled' => true
]);

// Configure Discord with role mentions
UserNotificationSettings::create([
    'user_id' => $userId,
    'channel' => 'discord',
    'webhook_url' => 'https://discord.com/api/webhooks/...',
    'ping_role_id' => '123456789',
    'is_enabled' => true
]);
```

### Testing Notifications

```php
use App\Services\NotificationChannels\SlackNotificationChannel;

$slackChannel = new SlackNotificationChannel();
$result = $slackChannel->testConnection($user);

if ($result['success']) {
    echo "Slack test successful!";
} else {
    echo "Slack test failed: " . $result['message'];
}
```

## 🔍 Machine Learning Features

### Price Prediction

The ML system analyzes multiple factors:
- Historical price data
- Market demand indicators
- Seasonal patterns
- Platform reliability
- Event popularity

### Availability Forecasting

Predicts ticket availability based on:
- Current inventory levels
- Sales velocity
- Time until event
- Cross-platform availability

### Smart Recommendations

Generates contextual advice:
- "Price is trending down. Consider waiting for better deals."
- "High demand detected. Purchase immediately!"
- "Similar events average $150. This is a good deal."

## ⚡ Escalation System

### Priority-Based Escalation

```php
// Critical Priority (5)
- Initial delay: 2 minutes
- Max attempts: 5
- Channels: SMS, Phone, Slack (urgent), Discord (ping)

// High Priority (4)  
- Initial delay: 5 minutes
- Max attempts: 3
- Channels: SMS, Slack, Discord

// Medium Priority (3)
- No automatic escalation
- Channels: Push, Slack
```

### Escalation Triggers

- User inactivity for 15+ minutes
- High/Critical priority alerts
- Multiple failed delivery attempts
- User-configured emergency settings

### Smart Escalation Features

- **Activity Monitoring**: Tracks user activity to prevent spam
- **Time-based Rules**: Respects quiet hours (11 PM - 7 AM)
- **Emergency Contacts**: Notifies designated contacts for critical alerts
- **Retry Logic**: Exponential backoff with configurable delays

## 📊 Analytics & Monitoring

### Performance Metrics

- Alert delivery success rates
- Channel performance comparison
- User engagement tracking
- ML prediction accuracy
- Escalation effectiveness

### Monitoring Dashboard

Track key metrics:
```php
// Alert statistics
$stats = [
    'total_alerts' => AlertEscalation::count(),
    'success_rate' => AlertEscalation::completed()->count() / AlertEscalation::count(),
    'avg_delivery_time' => /* calculation */,
    'channel_performance' => /* per-channel metrics */
];
```

## 🛡️ Security Features

### Webhook Security

- **Signature Verification**: HMAC-SHA256 signatures
- **IP Whitelisting**: Restrict webhook sources
- **SSL Verification**: Ensure secure connections
- **Payload Size Limits**: Prevent large payload attacks

### Data Protection

- **Encryption**: Sensitive data encrypted at rest
- **Access Control**: User-specific notification settings
- **Audit Logging**: Comprehensive activity logs
- **Rate Limiting**: Prevent abuse and spam

## 🔧 Troubleshooting

### Common Issues

#### 1. Notifications Not Sending

Check configuration:
```bash
php artisan config:cache
php artisan queue:restart
```

Verify channel settings:
```php
$settings = UserNotificationSettings::where('user_id', $userId)
    ->where('channel', 'slack')
    ->first();

if (!$settings || !$settings->isConfigured()) {
    // Configure channel
}
```

#### 2. ML Predictions Failing

Enable fallback mode:
```env
ML_FALLBACK_ENABLED=true
```

Check cache:
```bash
php artisan cache:clear
```

#### 3. Escalations Not Working

Verify escalation settings:
```env
ESCALATION_ENABLED=true
QUEUE_ESCALATIONS=escalations
```

Check user activity tracking:
```php
Cache::put("user_activity:{$userId}", now(), 3600);
```

### Debug Mode

Enable debug mode for detailed logging:
```env
ALERT_DEBUG_MODE=true
LOG_LEVEL=debug
```

## 📚 API Reference

### Core Services

#### EnhancedAlertSystem

```php
// Process smart alert
processSmartAlert(ScrapedTicket $ticket, TicketAlert $alert): void

// Calculate priority
calculateSmartPriority(ScrapedTicket $ticket, TicketAlert $alert): int

// Build alert data
buildEnhancedAlertData(...): array
```

#### TicketAvailabilityPredictor

```php
// Get predictions
predictAvailabilityTrend(ScrapedTicket $ticket): array

// Extract features
extractFeatures(ScrapedTicket $ticket): array

// Calculate confidence
calculateConfidence(array $features): float
```

#### AlertEscalationService

```php
// Schedule escalation
scheduleEscalation(TicketAlert $alert, array $alertData): void

// Process escalation
processEscalation(AlertEscalation $escalation): void
```

### Notification Channels

All channels implement:
```php
// Send notification
send(User $user, array $alertData): bool

// Test connection
testConnection(User $user): array
```

## 🚀 Performance Optimization

### Caching Strategy

- **User Preferences**: 1 hour cache
- **ML Predictions**: 5 minute cache
- **Channel Settings**: 1 hour cache
- **Platform Reliability**: 30 minute cache

### Queue Optimization

```bash
# Supervisor configuration for high-throughput
[program:hdtickets-alerts-critical]
command=php artisan queue:work --queue=alerts-critical --tries=3 --timeout=300
numprocs=3

[program:hdtickets-alerts-high]
command=php artisan queue:work --queue=alerts-high,alerts-medium --tries=3
numprocs=2
```

### Database Optimization

Ensure proper indexing:
```sql
-- Alert escalations
CREATE INDEX idx_escalations_status_scheduled ON alert_escalations(status, scheduled_at);
CREATE INDEX idx_escalations_user_status ON alert_escalations(user_id, status);

-- Price history
CREATE INDEX idx_price_history_ticket_recorded ON ticket_price_histories(ticket_id, recorded_at);

-- User preferences  
CREATE INDEX idx_preferences_user_category ON user_preferences(user_id, category);
```

## 📝 Best Practices

### Configuration

1. **Environment-Specific Settings**: Use different configs for dev/staging/production
2. **Secure Secrets**: Store API tokens in secure vaults
3. **Rate Limiting**: Configure appropriate limits for each channel
4. **Monitoring**: Set up alerts for system health

### Development

1. **Testing**: Always test notification channels before deployment
2. **Fallbacks**: Configure fallback channels for reliability  
3. **Logging**: Comprehensive logging for debugging
4. **Graceful Degradation**: Handle service failures gracefully

### Scaling

1. **Queue Workers**: Scale workers based on alert volume
2. **Cache Optimization**: Use Redis for high-performance caching
3. **Database Indexing**: Ensure proper indexing for large datasets
4. **API Rate Limits**: Respect third-party service limits

## 🔄 Migration from Basic Alerts

### Step 1: Install Enhanced System

```bash
# Run migrations
php artisan migrate

# Initialize user preferences
php artisan enhanced-alerts:init-preferences
```

### Step 2: Configure Channels

```php
// Migrate existing alert preferences
foreach ($users as $user) {
    UserPreference::initializeDefaults($user->id);
    
    // Configure notification channels based on existing preferences
    if ($user->slack_webhook) {
        UserNotificationSettings::create([
            'user_id' => $user->id,
            'channel' => 'slack',
            'webhook_url' => $user->slack_webhook,
            'is_enabled' => true
        ]);
    }
}
```

### Step 3: Update Alert Processing

```php
// Replace basic alert processing
// OLD: BasicAlertSystem::process($ticket, $alert);

// NEW: Enhanced alert processing
$enhancedAlerts = new EnhancedAlertSystem();
$enhancedAlerts->processSmartAlert($ticket, $alert);
```

## 🆘 Support

### Documentation Links

- [API Documentation](./API.md)
- [Channel Setup Guide](./CHANNELS.md)
- [ML Configuration](./ML_SETUP.md)
- [Troubleshooting Guide](./TROUBLESHOOTING.md)

### Getting Help

1. **Check Logs**: Review application and queue logs
2. **Test Channels**: Use built-in testing methods
3. **Debug Mode**: Enable debug mode for detailed logging
4. **Community**: Join the HDTickets community for support

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Authors**: HDTickets Development Team
