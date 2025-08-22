# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

**HD Tickets** is a comprehensive **Sports Events Entry Tickets Monitoring, Scraping and Purchase System** built with modern web technologies. This is **NOT a helpdesk ticket system** but specifically focused on sports event ticket discovery, monitoring, and automated purchasing.

### Architecture

The system follows **Domain-Driven Design (DDD)** principles with clear bounded contexts and separation of concerns:

```
app/
├── Domain/             # Business Logic Layer
│   ├── Event/         # Sports events, venues, schedules
│   ├── Ticket/        # Ticket monitoring & availability
│   ├── Purchase/      # Purchase decisions & automation
│   ├── User/          # Authentication & user management  
│   ├── Notification/  # Multi-channel alerts
│   └── Analytics/     # Reporting & insights
├── Application/       # Use Cases (Commands/Queries)
├── Infrastructure/    # External integrations & persistence
└── Presentation/      # HTTP Controllers & Console
```

### Technology Stack

**Backend:**
- Laravel 12 with PHP 8.4
- MySQL/MariaDB for primary storage
- Redis for caching, sessions, and queues
- Laravel Horizon for queue management

**Frontend:**
- Alpine.js for reactive components
- Tailwind CSS with sports-themed design system
- Vite for modern build tooling
- TypeScript for type safety

**Real-time & Integration:**
- Laravel Echo + Pusher for WebSocket communication
- Roach PHP for web scraping
- PWA features with service workers

## Development Environment

**Platform:** Ubuntu 24.04 LTS + Apache2 + PHP 8.4 + MySQL/MariaDB 10.4

## Essential Commands

### Quick Setup
```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build
```

### Development Servers
```bash
# Laravel development server
php artisan serve

# Frontend assets with hot reload
npm run dev

# Queue worker with dashboard
php artisan horizon

# All development servers (using Makefile)
make dev-workflow
```

### Testing
```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Specific test suites
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Integration

# With coverage
vendor/bin/phpunit --coverage-html=storage/quality/coverage/html

# Using Makefile
make test
make test-coverage
```

### Code Quality

```bash
# PHP code formatting
vendor/bin/pint
# or
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php

# Static analysis (PHPStan level 5)
vendor/bin/phpstan analyse --configuration=phpstan.neon

# JavaScript/TypeScript linting
npm run lint
npm run lint:fix

# All quality checks
make quality
make full-check
```

### Build & Deployment
```bash
# Production build
npm run build

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue in production
php artisan horizon
```

## Domain-Driven Design Structure

### Bounded Contexts

1. **Event Management Context**
   - Core entities: `SportsEvent`, `EventSchedule`, `Venue`
   - Value objects: `EventId`, `EventDate`, `SportCategory`
   - Domain events: `SportEventCreated`, `SportEventMarkedAsHighDemand`

2. **Ticket Monitoring Context**
   - Entities: `MonitoredTicket`, `TicketSource`
   - Value objects: `Price`, `AvailabilityStatus`, `PlatformSource`
   - Domain events: `TicketPriceChanged`, `TicketAvailabilityChanged`

3. **Purchase Management Context**
   - Handles automated purchase decisions and queue management
   - Value objects: `PurchaseId`, `PurchaseStatus`

4. **User Management Context**
   - **Roles:** Admin, Agent, Customer, Scraper
   - Features: Multi-factor authentication, device fingerprinting
   - Enhanced login security with geolocation checks

5. **Notification Context**
   - Multi-channel notifications (email, SMS, webhooks, browser push)
   - Real-time alerts for price changes and availability

6. **Analytics Context**
   - Performance metrics, price trends, user behavior analysis

### CQRS Implementation

Commands and queries are separated for better scalability:

```php
// Commands (Write operations)
CreateSportsEventCommand
UpdateTicketPriceCommand
PurchaseTicketCommand

// Queries (Read operations)
GetUpcomingEventsQuery
GetTicketPriceHistoryQuery
GetUserDashboardQuery
```

## Security Features

### Authentication & Authorization
- **Multi-factor Authentication (2FA)** with Google Authenticator
- **Enhanced Login Security:** Device fingerprinting, automated tool detection
- **Role-Based Access Control (RBAC):** Admin, Agent, Customer, Scraper roles
- **Session Management:** Redis-backed secure sessions
- **API Security:** Laravel Passport (OAuth2) + Sanctum (SPA)

### Security Headers & Middleware
- CSRF protection
- Rate limiting per user/endpoint
- Security headers middleware
- Input validation with Laravel Form Requests

## Real-time Features

### WebSocket Integration
```php
// Broadcasting events
event(new TicketPriceChanged($ticket));
event(new TicketAvailabilityUpdated($ticket));
```

```javascript
// Frontend listening
Echo.channel('tickets')
    .listen('TicketPriceChanged', (e) => {
        // Update UI with new price
    });
```

### PWA Features
- Service worker for offline functionality
- Push notifications for price alerts
- Mobile-first responsive design
- Installable web application

## Frontend Development

### Alpine.js Components
Interactive components are built with Alpine.js for lightweight reactivity:
- Dashboard widgets
- Real-time price displays
- Form validation
- Modal dialogs

### Tailwind CSS Theme
Sports-focused design system with:
- Custom color palette (stadium-inspired colors)
- Enterprise-grade components
- Dark mode support
- Mobile-first responsive breakpoints

### Build Configuration
Vite configuration includes:
- Hot module replacement for development
- Code splitting for optimal loading
- TypeScript compilation
- Asset optimization

## Database Strategy

### Primary Storage
- **MySQL/PostgreSQL** for transactional data
- **Eloquent ORM** with relationships and scopes
- **Database migrations** for version control
- **Seeders** for test data and initial setup

### Caching Strategy
- **Redis** for session storage, cache, and queues
- **Model caching** for frequently accessed data
- **Query result caching** for expensive operations
- **Full-page caching** for public content

### Queue Management
- **Laravel Horizon** dashboard for monitoring
- **Redis driver** for queue backend
- **Failed job handling** with automatic retries
- **Job prioritization** for critical operations

## Testing Strategy

### Test Structure
```
tests/
├── Feature/          # End-to-end functionality
├── Unit/            # Individual component testing
├── Integration/     # Cross-component testing
├── Browser/         # Laravel Dusk browser tests
└── Performance/     # Load and performance tests
```

### Key Testing Areas
- **Domain Logic:** Value objects, entities, domain services
- **API Endpoints:** Authentication, authorization, data validation
- **Web Scraping:** Scraper detection handling, data extraction
- **Real-time Features:** WebSocket events, notifications
- **Security:** Authentication flows, authorization checks

## Web Scraping & Monitoring

### Roach PHP Integration
- **Professional scraping framework** for reliable data extraction
- **Respect for robots.txt** and rate limiting
- **Proxy support** for distributed scraping
- **Anti-detection measures** built-in

### Platform Sources
- Multiple ticket platform integrations
- **Anti-corruption layers** protect domain from external changes
- **Rate limiting** and respectful scraping practices
- **Error handling** for platform unavailability

## Production Considerations

### Environment Configuration
```bash
# Production optimizations
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
HORIZON_BALANCE=auto

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

### Performance Optimization
- **Opcache** enabled for PHP performance  
- **Redis clustering** for high availability
- **CDN integration** for static assets
- **Database query optimization** with indexes

### Monitoring
- **Laravel Horizon** for queue monitoring
- **Laravel Telescope** for development debugging  
- **Error tracking** with comprehensive logging
- **Performance metrics** collection

### Deployment
- **Zero-downtime deployments** with queue management
- **Health check endpoints** for load balancers
- **SSL/TLS configuration** for security
- **Backup strategies** for data protection

## Common Development Tasks

### Adding New Sports Event Types
1. Update `SportCategory` value object with new categories
2. Add relevant database migrations
3. Update scraper configurations for new platforms
4. Add tests for new event types

### Implementing New Notification Channels  
1. Create notification class in `app/Mail/` or `app/Notifications/`
2. Configure channel in notification settings
3. Update user preferences for new channel
4. Add tests for notification delivery

### Adding New Scraper Platforms
1. Create platform-specific scraper in `app/Services/Scraping/`
2. Implement anti-corruption layer for data transformation
3. Add platform source configuration
4. Create integration tests with mock data

## Important Notes

- **Ticket Context:** This system handles sports event tickets, NOT helpdesk tickets
- **Role Verification:** Always verify user roles before granting access to features
- **Rate Limiting:** Respect external platform rate limits in scraping
- **Real-time Updates:** Use WebSocket events for live price/availability changes
- **Security First:** All user input must be validated and sanitized
- **Mobile Experience:** Ensure all features work well on mobile devices

## Architecture Decisions

- **DDD Architecture:** Chosen for complex business logic and maintainability
- **CQRS Pattern:** Separates read/write operations for better scalability  
- **Event Sourcing:** Planned for audit trails and historical data
- **Microservice Ready:** Clear boundaries support future service extraction
- **API First:** All features accessible via RESTful APIs
