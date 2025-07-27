# HDTickets - Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System
**Version:** 4.0.0  
**Author:** Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle  
**@author** Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

## Overview
HDTickets is a comprehensive, high-performance Sports Events Entry Tickets Monitoring, Scraping and Purchase System designed to monitor, scrape, and automate ticket purchases for major sporting events across multiple platforms. This advanced system provides real-time monitoring of ticket sales, intelligent price tracking, automated purchase capabilities, and sophisticated analytics from major ticket vendors including Ticketmaster, StubHub, Viagogo, SeatGeek, TickPick, and more.

## 🎯 Core Features

### 🎫 Advanced Ticket Scraping & Monitoring
- **Multi-Platform Scraping**: Automated scraping across Ticketmaster, StubHub, Viagogo, TickPick, SeatGeek, and more
- **Real-Time Data Collection**: Continuous monitoring with intelligent scheduling and anti-detection measures
- **Smart Filtering System**: Advanced filtering by sport, team, venue, location, price range, and availability status
- **High-Demand Detection**: AI-powered identification of high-demand events and tickets
- **Data Validation**: Comprehensive data validation and normalization across platforms

### 🚨 Enhanced Alert System
- **Smart Prioritization**: AI-driven alert prioritization based on multiple factors
- **Multi-Channel Notifications**: Slack, Discord, Telegram, SMS, email, and webhook integrations
- **Machine Learning Predictions**: Availability forecasting and price movement analysis
- **Escalation & Retry Logic**: Progressive alert escalation with intelligent retry mechanisms
- **Custom Alert Criteria**: User-defined triggers for specific events, prices, and availability changes

### 🛒 Automated Purchase System
- **Purchase Queue Management**: Priority-based automated purchase queue with risk management
- **Smart Account Selection**: Intelligent selection of best-performing platform accounts
- **Purchase Attempt Tracking**: Comprehensive tracking of purchase attempts and success rates
- **Risk Assessment**: Built-in risk management with configurable thresholds and approval workflows
- **Transaction Logging**: Complete audit trail for all purchase activities

### 📊 Advanced Analytics & Reporting
- **Real-Time Dashboards**: Live monitoring of system performance and ticket availability
- **AI-Powered Insights**: Machine learning predictions for demand patterns and price trends
- **Platform Performance Comparison**: Detailed analysis of success rates across different platforms
- **Custom Analytics**: Configurable dashboards with personalized metrics and KPIs
- **Data Export**: Multiple export formats (CSV, Excel, PDF, JSON) for reporting

### 👥 User Management & Security
- **Role-Based Access Control**: Granular permissions for Admin, Agent, and User roles
- **Two-Factor Authentication**: Enhanced security with 2FA support
- **Activity Logging**: Comprehensive audit trails for all user activities
- **Account Management**: Secure management of multiple platform accounts with encrypted storage
- **User Preferences**: Customizable notification preferences and dashboard configurations

## 🏗️ Technical Architecture

### Technology Stack
- **Backend**: Laravel 11.x with PHP 8.2+
- **Frontend**: Vue.js 3 with Inertia.js and Alpine.js
- **Database**: MySQL 8.4+ with Redis caching
- **Queue System**: Laravel Horizon for background job processing
- **Authentication**: Laravel Sanctum with 2FA support
- **WebSockets**: Real-time updates using Laravel Echo and Pusher
- **Styling**: Tailwind CSS with Bootstrap components
- **Charts**: Chart.js for analytics visualization

### System Components
- **Scraping Engine**: Multi-platform scraping with anti-detection measures
- **Enhanced Alert System**: AI-powered notifications with ML predictions
- **Purchase Automation**: Queue-based automated purchasing with risk management
- **Analytics Engine**: Advanced analytics with predictive insights
- **Real-Time Monitoring**: Live dashboards with system health monitoring
- **User Management**: Role-based access with comprehensive audit logging

## 📊 Supported Platforms

### Primary Platforms
- **Ticketmaster**: Official tickets for major venues and events
- **StubHub**: Secondary market tickets with verified sellers
- **SeatGeek**: Aggregated listings from multiple sources
- **Vivid Seats**: Premium and secondary market options
- **TickPick**: No-fee ticket marketplace

### Additional Platforms
- **Fanzone**: European sports events
- **Viagogo**: International ticket marketplace
- **TicketNetwork**: Broker and reseller network
- **Custom APIs**: Support for venue-specific ticketing systems

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Node.js 18+ and npm
- MySQL 8.4+
- Redis 6.0+
- Composer

### Installation Steps
```bash
# Clone the repository
git clone https://github.com/waltercsoelle/sports-ticket-monitor.git
cd sports-ticket-monitor

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database settings in .env file
# Then run migrations
php artisan migrate

# Seed database with initial data
php artisan db:seed

# Build frontend assets
npm run build

# Start the application
php artisan serve
```

### Configuration
Update your `.env` file with the following key configurations:

```env
# Application
APP_NAME="Sports Ticket Monitor"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sports_tickets
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration
QUEUE_CONNECTION=redis
HORIZON_PREFIX=sports_tickets

# Notification Services
MAIL_MAILER=smtp
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
```

## 📱 Usage Guide

### Setting Up Monitoring
1. **Create Account**: Register for a new user account
2. **Configure Preferences**: Set your sports interests, favorite teams, and preferred venues
3. **Add Monitoring Rules**: Create custom alerts for specific events, price ranges, or availability criteria
4. **Connect Platforms**: Link your accounts from various ticket platforms for enhanced monitoring

### Dashboard Features
- **Live Monitor**: Real-time view of active monitoring tasks
- **Alert Center**: Manage and review all notifications
- **Analytics**: View trends, success rates, and historical data
- **Account Settings**: Manage platform connections and notification preferences

### Creating Custom Alerts
```javascript
// Example alert configuration
{
  "event_type": "NFL",
  "team": "Kansas City Chiefs",
  "venue": "Arrowhead Stadium",
  "max_price": 250,
  "min_quantity": 2,
  "section_preference": ["Lower Level", "Club"],
  "notification_channels": ["email", "sms"],
  "priority": "high"
}
```

## 🔧 API Documentation

### Authentication
All API requests require authentication via Bearer token (Laravel Sanctum):
```bash
Authorization: Bearer {your_api_token}
```

### Core API Endpoints

#### Scraping Management
```bash
GET    /api/v1/scraping/tickets           # Get scraped tickets with filtering
GET    /api/v1/scraping/tickets/{uuid}    # Get specific ticket details
POST   /api/v1/scraping/start-scraping    # Initiate scraping for platforms
GET    /api/v1/scraping/statistics        # Get scraping statistics
GET    /api/v1/scraping/platforms         # Get platform status
DELETE /api/v1/scraping/cleanup          # Clean up old data
```

#### Alert Management
```bash
GET    /api/v1/alerts                     # Get user alerts
POST   /api/v1/alerts                     # Create new alert
GET    /api/v1/alerts/{uuid}              # Get specific alert
PUT    /api/v1/alerts/{uuid}              # Update alert
DELETE /api/v1/alerts/{uuid}              # Delete alert
POST   /api/v1/alerts/{uuid}/toggle       # Toggle alert status
POST   /api/v1/alerts/{uuid}/test         # Test alert criteria
```

#### Purchase Automation
```bash
GET    /api/v1/purchases/queue            # Get purchase queue
POST   /api/v1/purchases/queue            # Add to purchase queue
GET    /api/v1/purchases/attempts         # Get purchase attempts
POST   /api/v1/purchases/attempts/initiate # Start purchase attempt
GET    /api/v1/purchases/statistics       # Get purchase statistics
GET    /api/v1/purchases/configuration    # Get purchase config
```

#### Enhanced Analytics
```bash
GET    /api/v1/enhanced-analytics/charts          # Get chart data
GET    /api/v1/enhanced-analytics/insights/predictive # Predictive insights
GET    /api/v1/analytics/price-trends             # Price trend analysis
GET    /api/v1/analytics/demand-patterns          # Demand patterns
GET    /api/v1/analytics/platform-performance     # Platform comparison
```

#### Platform-Specific Endpoints
```bash
# StubHub
POST   /api/v1/stubhub/search             # Search StubHub events
POST   /api/v1/stubhub/import             # Import StubHub tickets

# Ticketmaster
POST   /api/v1/ticketmaster/search        # Search Ticketmaster events
POST   /api/v1/ticketmaster/import        # Import Ticketmaster tickets

# Viagogo
POST   /api/v1/viagogo/search             # Search Viagogo events
POST   /api/v1/viagogo/import             # Import Viagogo tickets

# TickPick
POST   /api/v1/tickpick/search            # Search TickPick events
POST   /api/v1/tickpick/import            # Import TickPick tickets
```

### Rate Limiting
- **Public routes**: 10 requests per minute
- **Authenticated routes**: 120 requests per minute
- **Scraping routes**: 60 requests per minute

For complete API documentation, see: [API-DOCUMENTATION.md](API-DOCUMENTATION.md)

## 📈 Performance Metrics

### System Capabilities
- **Concurrent Monitoring**: 1000+ events simultaneously
- **Response Time**: Sub-second alert delivery
- **Platform Coverage**: 5+ major ticket platforms
- **Update Frequency**: Every 30-60 seconds per event
- **Success Rate**: 99.5+ uptime with intelligent failover

### Monitoring Statistics
- **Events Tracked Daily**: 10,000+
- **Alerts Sent Monthly**: 50,000+
- **Price Points Monitored**: 1M+ per day
- **Users Served**: Scalable to 10,000+ concurrent users

## 🛡️ Security Features

### Data Protection
- **Encrypted Storage**: AES-256 encryption for sensitive data
- **Secure API Communication**: HTTPS with rate limiting
- **User Privacy**: No personal ticket purchase data stored
- **GDPR Compliant**: Full compliance with data protection regulations

### Platform Security
- **Account Isolation**: Secure handling of platform credentials
- **Proxy Rotation**: Anti-detection measures for web scraping
- **Rate Limiting**: Intelligent throttling to avoid platform blocks
- **Audit Logging**: Comprehensive system activity tracking

## 🤝 Contributing

We welcome contributions to improve the Sports Event Ticket Monitoring System. Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Standards
- Follow PSR-12 coding standards for PHP
- Use Vue 3 Composition API for frontend components
- Write comprehensive tests for new features
- Update documentation for any API changes

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Email**: support@sports-ticket-monitor.com
- **Documentation**: [https://docs.sports-ticket-monitor.com](https://docs.sports-ticket-monitor.com)
- **Issues**: [GitHub Issues](https://github.com/waltercsoelle/sports-ticket-monitor/issues)

## 🙏 Acknowledgments

- Built with Laravel and Vue.js
- Inspired by the need for fair and efficient sports ticket access
- Thanks to the open-source community for the amazing tools and libraries

---

**Note**: This system is designed for personal use and ticket availability monitoring only. Please respect the terms of service of all ticket platforms and use responsibly.
# Auto-sync configuration applied So 26. júl 2025, 14:45:56 CEST
