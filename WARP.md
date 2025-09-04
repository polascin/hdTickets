# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

**HD Tickets** is a comprehensive **Sports Events Entry Tickets Monitoring, Scraping and Purchase System** built with modern web technologies. This is **NOT a helpdesk ticket system** but specifically focused on sports event ticket discovery, monitoring, and automated purchasing.

### Architecture

The system follows **Domain-Driven Design (DDD)** principles with clear bounded contexts and separation of concerns:

```
app/
├── Domain/             # Business Logic Layer (DDD)
│   ├── Event/         # Sports events management
│   ├── Monitoring/    # Ticket monitoring and tracking
│   ├── Purchase/      # Purchase automation and decisions
│   ├── Shared/        # Shared domain components
│   ├── System/        # System-wide domain concerns
│   └── Ticket/        # Ticket-specific business logic
├── Application/       # Use Cases (Commands/Queries)
│   ├── Commands/      # Write operations
│   ├── EventHandlers/ # Domain event handlers
│   └── Queries/       # Read operations
├── Infrastructure/    # External integrations & persistence
│   ├── EventBus/      # Event distribution system
│   ├── EventStore/    # Event sourcing storage
│   ├── External/      # Third-party integrations
│   ├── Persistence/   # Data storage implementations
│   └── Projections/   # Read model projections
├── Services/          # Application services
│   ├── Analytics/     # Data analysis services
│   ├── Platforms/     # Platform-specific integrations
│   ├── Scraping/      # Web scraping services
│   └── Security/      # Security-related services
└── Http/              # Presentation layer
    ├── Controllers/   # HTTP request handlers
    ├── Middleware/    # Request/response middleware
    └── Resources/     # API response formatting
```

### Technology Stack

**Backend:**
- Laravel 11.45.2 with PHP 8.3.25
- MariaDB 10.4 for primary storage
- Redis 6.0+ for caching, sessions, and queues
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

**Platform:** Ubuntu 24.04 LTS + Apache2 + PHP 8.3.25 + MariaDB 10.4
**Frontend Environment:** Node.js 22.19.0 + npm 10.9.3

### Makefile Commands

```bash
# Development workflow
make dev-workflow          # Start all development servers
make setup                 # Complete project setup

# Testing
make test                  # Run all PHPUnit tests  
make test-unit            # Run unit tests only
make test-feature         # Run feature tests only
make test-coverage        # Generate HTML coverage reports

# Code quality
make quality              # Run all quality checks (style + analysis)
make fix                  # Fix PSR-12 code style violations
make analyze              # Run PHPStan static analysis
make psr4-check           # Validate PSR-4 namespace compliance

# Complete checks
make full-check           # Complete quality check suite
make pre-commit           # Fast pre-commit quality checks
make ci-pipeline          # Simulate CI/CD pipeline

# Maintenance
make clean                # Clean caches and temporary files
make security-check       # Run security audits
make metrics              # Generate quality metrics dashboard
```

### Composer Scripts

```bash
# Quality assurance scripts defined in composer.json
composer code-style       # Fix code style with PHP CS Fixer
composer static-analysis  # Run PHPStan analysis
composer test-coverage    # Generate test coverage reports
composer quality-metrics  # Generate code complexity metrics
composer full-quality-check # Complete quality assessment
```

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
# PHP code formatting (PSR-12 compliance)
vendor/bin/pint                                              # Laravel Pint formatter
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php     # PHP CS Fixer alternative

# Static analysis (PHPStan Level 8)
vendor/bin/phpstan analyse --configuration=phpstan.neon    # Static type analysis
vendor/bin/larastan                                         # Laravel-specific analysis

# Code complexity & metrics
vendor/bin/phpmetrics --report-html=storage/quality/metrics app/
vendor/bin/phpmd app/ html storage/quality/phpmd.html      # Mess detection

# JavaScript/TypeScript quality
npm run lint                    # ESLint for JS/TS
npm run lint:fix               # Auto-fix ESLint issues
npm run format                 # Prettier code formatting
npm run type-check             # TypeScript type checking

# Quality assurance workflows
make quality                   # Run style + analysis + PSR-4 checks
make full-check               # Complete suite: syntax + quality + tests + security
make pre-commit               # Fast pre-commit validation
make ci-pipeline              # Full CI/CD simulation

# Quality reports location
# - Coverage: storage/quality/coverage/html/index.html
# - Metrics: storage/quality/metrics/index.html  
# - PHPStan: Console output + baseline in phpstan-baseline.neon
# - Security: storage/logs/security-audit.json
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

1. **Event Domain Context** (`app/Domain/Event/`)
   - Core entities: `SportsEvent`, `EventSchedule`, `Venue`
   - Value objects: `EventId`, `EventDate`, `SportCategory`
   - Domain events: `SportEventCreated`, `SportEventMarkedAsHighDemand`
   - Services: Event validation and scheduling logic

2. **Monitoring Domain Context** (`app/Domain/Monitoring/`)
   - Handles ticket availability monitoring and alerting
   - Domain events: Price change notifications, availability alerts
   - Integration with scraping services for data collection

3. **Ticket Domain Context** (`app/Domain/Ticket/`)
   - Core entities: `Ticket`, `TicketSource`, `TicketAvailability`
   - Value objects: `Price`, `AvailabilityStatus`, `PlatformSource`
   - Domain events: `TicketPriceChanged`, `TicketAvailabilityChanged`

4. **Purchase Domain Context** (`app/Domain/Purchase/`)
   - Handles automated purchase decisions and queue management
   - Value objects: `PurchaseId`, `PurchaseStatus`
   - Purchase workflow orchestration

5. **System Domain Context** (`app/Domain/System/`)
   - System-wide concerns and cross-cutting functionality
   - System health monitoring and configuration
   - Platform-wide events and notifications

6. **Shared Domain Context** (`app/Domain/Shared/`)
   - Common value objects used across domains
   - Shared domain events and interfaces
   - Cross-domain utilities and abstractions

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
├── Feature/          # End-to-end functionality & HTTP endpoints
├── Unit/            # Individual component & domain logic testing
└── Integration/     # Cross-component & external service testing
```

### PHPUnit Configuration
- **Test Database:** In-memory SQLite for fast execution
- **Cache Directory:** `storage/phpunit/cache` for optimized performance
- **Coverage Reports:** HTML, XML, Clover, Cobertura formats
- **Coverage Directory:** `storage/quality/coverage/` with multiple formats
- **Memory Limit:** 512M for complex test scenarios
- **Logging:** JUnit XML, TeamCity, TestDox formats

### Test Suite Commands
```bash
# Run specific test suites
vendor/bin/phpunit --testsuite=Unit        # Domain logic tests
vendor/bin/phpunit --testsuite=Feature     # HTTP & application tests  
vendor/bin/phpunit --testsuite=Integration # External service tests

# Coverage reports (multiple formats)
vendor/bin/phpunit --coverage-html=storage/quality/coverage/html
vendor/bin/phpunit --coverage-clover=storage/quality/coverage/clover.xml
vendor/bin/phpunit --coverage-cobertura=storage/quality/coverage/cobertura.xml

# Using Makefile shortcuts
make test-unit      # Unit tests only
make test-feature   # Feature tests only  
make test-coverage  # All tests with HTML coverage report
```

### Coverage Configuration
- **Included:** All `app/` directory files
- **Excluded:** `app/Console/Commands/`, `app/helpers.php`
- **Thresholds:** Low: 50%, High: 90%
- **Path Coverage:** Enabled for detailed analysis
- **CRAP4J Threshold:** 30 for complexity analysis

### Key Testing Areas
- **Domain Logic:** Value objects, entities, domain services
- **API Endpoints:** Authentication, authorization, data validation
- **Web Scraping:** Scraper detection handling, data extraction
- **Service Integration:** Platform APIs, payment gateways
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

## Ticket Purchase System

### Overview

The HD Tickets platform features a comprehensive **Sports Event Ticket Purchase System** that enforces subscription-based limits for customers while providing unlimited access for agents and administrators. The system is built with Domain-Driven Design principles and includes robust validation, role-based permissions, and user-friendly interfaces.

### Architecture Components

**Backend Components:**
- `App\Services\TicketPurchaseService` - Core business logic for purchase operations
- `App\Http\Middleware\TicketPurchaseValidationMiddleware` - Request validation and eligibility checking
- `App\Http\Controllers\TicketPurchaseController` - HTTP request handling
- `App\Domain\Purchase\Models\TicketPurchase` - Domain model for purchase data
- `App\Http\Requests\TicketPurchaseRequest` - Input validation

**Frontend Components:**
- `resources/views/tickets/purchase.blade.php` - Purchase form and ticket details
- `resources/views/tickets/purchase-success.blade.php` - Purchase confirmation page
- `resources/views/tickets/purchase-failed.blade.php` - Error handling and troubleshooting
- `resources/views/tickets/purchase-history.blade.php` - User purchase history with filtering

### Purchase Workflow

#### 1. Eligibility Check
Before any purchase attempt, the system validates:
- **User Authentication:** Must be logged in
- **Subscription Status:** Customers need active subscription (agents/admins bypass)
- **Ticket Availability:** Ticket must be available for purchase
- **Quantity Limits:** Must not exceed available quantity or user limits
- **Monthly Usage:** Customers cannot exceed subscription ticket limits

#### 2. Purchase Process
```php
// Eligibility validation
$eligibility = $purchaseService->checkPurchaseEligibility($user, $ticket, $quantity);

if ($eligibility['can_purchase']) {
    // Create purchase record
    $purchase = $purchaseService->createPurchase($user, $ticket, $purchaseData);
    
    // Process payment (if integrated)
    // Confirm purchase
    $purchase = $purchaseService->confirmPurchase($purchase);
}
```

#### 3. Fee Calculation
The system automatically calculates:
- **Base Amount:** Unit price × Quantity
- **Processing Fee:** 3% of subtotal
- **Service Fee:** Fixed $2.50 per transaction
- **Total Amount:** Subtotal + Processing Fee + Service Fee

### Role-Based Access Control

#### Customer Role (`customer`)
- **Subscription Required:** Must have active subscription after free trial period
- **Monthly Limits:** Limited by subscription plan (default: 100 tickets/month)
- **Free Access:** 7-day free access period for new accounts
- **Validation:** All purchases validated against subscription limits

#### Agent Role (`agent`)
- **Unlimited Access:** No subscription or monthly limits required
- **Professional Use:** Designed for ticket selection professionals
- **Priority Access:** Can purchase any available quantity
- **Bypass Restrictions:** All purchase validations bypassed except availability

#### Administrator Role (`admin`)
- **Full System Access:** Complete administrative control
- **Unlimited Purchases:** No restrictions on ticket purchases
- **System Management:** Can manage all user purchases
- **Override Capabilities:** Can bypass all purchase restrictions

#### Scraper Role (`scraper`)
- **No Purchase Access:** Cannot access purchase functionality
- **System-Only Role:** Used exclusively for automated scraping operations

### API Endpoints

#### Purchase Flow Endpoints
```bash
# View purchase form
GET /tickets/{ticket}/purchase

# Submit purchase request
POST /tickets/{ticket}/purchase

# Purchase success page
GET /tickets/purchases/{purchase}/success

# Purchase failure page
GET /tickets/purchase-failed

# Purchase history
GET /tickets/purchase-history

# Cancel pending purchase
PATCH /tickets/purchases/{purchase}/cancel

# Export purchase history
GET /tickets/purchase-history/export?format=csv|pdf
```

#### API Response Format
```json
{
  "success": true,
  "message": "Purchase completed successfully!",
  "data": {
    "purchase_id": "PUR-20240904-ABC123",
    "status": "pending",
    "total_amount": 208.50
  }
}
```

#### Error Response Format
```json
{
  "success": false,
  "message": "Purchase validation failed",
  "reasons": [
    "Active subscription required",
    "Would exceed monthly ticket limit"
  ],
  "user_info": {
    "ticket_limit": 100,
    "monthly_usage": 85,
    "remaining_tickets": 15
  }
}
```

### Database Schema

#### TicketPurchases Table
```sql
CREATE TABLE ticket_purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ticket_id BIGINT UNSIGNED NOT NULL,
    purchase_id VARCHAR(50) UNIQUE NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    processing_fee DECIMAL(8,2) DEFAULT 0,
    service_fee DECIMAL(8,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'failed') DEFAULT 'pending',
    seat_preferences JSON NULL,
    special_requests TEXT NULL,
    payment_intent_id VARCHAR(255) NULL,
    payment_status VARCHAR(50) NULL,
    confirmed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_purchase_date (created_at),
    INDEX idx_status (status)
);
```

### Subscription Integration

#### Free Access Period
```php
// Configuration
config(['subscription.free_access_days' => 7]);

// Check if user is within free access period
$withinFreeAccess = $user->created_at->diffInDays(now()) <= config('subscription.free_access_days');
```

#### Monthly Usage Calculation
```php
// Calculate current month ticket usage
$monthlyUsage = TicketPurchase::where('user_id', $user->id)
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->whereIn('status', ['confirmed', 'pending'])
    ->sum('quantity');
```

#### Subscription Limits
- **Default Limit:** 100 tickets per month
- **Custom Plans:** Configurable via subscription model
- **Usage Tracking:** Real-time calculation of monthly consumption
- **Limit Enforcement:** Strict validation before purchase creation

### User Interface Features

#### Purchase Form
- **Ticket Information:** Event details, pricing, availability
- **Quantity Selection:** Dropdown with available quantities
- **Seat Preferences:** Optional section, row, and seat type selection
- **Special Requests:** Free-text field for accessibility needs
- **Price Calculation:** Real-time total calculation with fees
- **Terms Acceptance:** Mandatory legal document acknowledgment
- **Subscription Status:** Visual indication of usage and limits

#### Purchase History
- **Comprehensive Listing:** All user purchases with status indicators
- **Advanced Filtering:** By status, date range, event type
- **Pagination Support:** Efficient handling of large purchase histories
- **Export Functionality:** CSV and PDF export options
- **Action Menus:** Cancel pending, view details, download tickets
- **Usage Statistics:** Monthly usage summary and subscription status

#### Error Handling
- **Detailed Error Pages:** Specific error messages and troubleshooting
- **Recovery Actions:** Clear next steps for different failure types
- **Support Integration:** Contact information and support channels
- **FAQ Integration:** Common questions and solutions

### Security & Validation

#### Input Validation
```php
// TicketPurchaseRequest validation rules
public function rules(): array
{
    return [
        'quantity' => 'required|integer|min:1|max:50',
        'seat_preferences.section' => 'nullable|string|max:100',
        'seat_preferences.row' => 'nullable|string|max:50',
        'seat_preferences.seat_type' => 'in:standard,premium,vip,accessible',
        'special_requests' => 'nullable|string|max:1000',
        'accept_terms' => 'required|accepted',
        'confirm_purchase' => 'required|accepted'
    ];
}
```

#### Middleware Protection
- **Authentication Required:** All purchase endpoints require login
- **Role-Based Validation:** Middleware checks user permissions
- **Subscription Verification:** Real-time subscription status checking
- **Rate Limiting:** Prevent abuse and automated attacks
- **CSRF Protection:** All form submissions protected

#### Data Sanitization
- **Input Cleaning:** All user inputs sanitized and validated
- **SQL Injection Prevention:** Eloquent ORM with parameter binding
- **XSS Protection:** Output escaping in Blade templates
- **JSON Validation:** Structured data validation for preferences

### Testing Strategy

#### Unit Tests
- **Service Logic:** TicketPurchaseService comprehensive testing
- **Model Behavior:** TicketPurchase model methods and relationships
- **Middleware Validation:** Purchase eligibility checking
- **Fee Calculations:** Accurate fee and total calculations

#### Feature Tests
- **Complete Workflows:** End-to-end purchase processes
- **Role-Based Access:** Different user role behaviors
- **Subscription Enforcement:** Limit validation and enforcement
- **Error Scenarios:** Failure cases and error handling

#### Integration Tests
- **Database Operations:** Purchase creation and updates
- **External Services:** Payment gateway integrations (when available)
- **Email Notifications:** Purchase confirmation emails

### Configuration

#### Environment Variables
```bash
# Subscription settings
SUBSCRIPTION_FREE_ACCESS_DAYS=7
SUBSCRIPTION_DEFAULT_MONTHLY_FEE=29.99
SUBSCRIPTION_DEFAULT_TICKET_LIMIT=100
SUBSCRIPTION_AGENT_UNLIMITED_TICKETS=true

# Purchase fees
PURCHASE_PROCESSING_FEE_RATE=0.03
PURCHASE_SERVICE_FEE=2.50

# Purchase limits
PURCHASE_MAX_QUANTITY_PER_ORDER=50
PURCHASE_CANCELLATION_WINDOW_HOURS=24
```

### Monitoring & Analytics

#### Key Metrics
- **Purchase Volume:** Daily/monthly purchase statistics
- **Conversion Rates:** Success vs. failure ratios
- **User Behavior:** Most popular events and quantities
- **Subscription Impact:** Usage patterns by subscription type
- **Revenue Tracking:** Fee collection and revenue analysis

#### Logging
- **Purchase Attempts:** All purchase requests logged
- **Validation Failures:** Detailed failure reason tracking
- **Performance Metrics:** Response time and system performance
- **Error Tracking:** Exception monitoring and alerting

### Common Operations

#### Create New Purchase
```bash
# Via web interface
visit /tickets/{id}/purchase

# Via API
curl -X POST /api/tickets/{id}/purchase \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"quantity": 2, "accept_terms": true}'
```

#### Check Purchase Eligibility
```php
$eligibility = app(TicketPurchaseService::class)
    ->checkPurchaseEligibility($user, $ticket, $quantity);

if ($eligibility['can_purchase']) {
    // Proceed with purchase
} else {
    // Show error messages: $eligibility['reasons']
}
```

#### Cancel Pending Purchase
```php
$purchase = TicketPurchase::findOrFail($id);

if ($purchase->canBeCancelled()) {
    app(TicketPurchaseService::class)
        ->cancelPurchase($purchase, 'User requested cancellation');
}
```

### Troubleshooting

#### Common Issues
1. **"Active subscription required"** - Customer needs subscription
2. **"Would exceed monthly ticket limit"** - Subscription limit reached
3. **"Ticket is not available"** - Ticket sold out or removed
4. **"Not enough tickets available"** - Quantity exceeds availability

#### Debug Commands
```bash
# Check user subscription status
php artisan tinker
> $user = User::find(123);
> $user->hasActiveSubscription();
> $user->getMonthlyTicketUsage();

# View purchase details
> $purchase = TicketPurchase::with(['user', 'ticket'])->find(456);
> $purchase->toArray();
```

### Important Notes

- **Ticket Context:** This system handles sports event tickets, NOT helpdesk tickets
- **Role Verification:** Always verify user roles before granting purchase access
- **Subscription Enforcement:** Strict limits for customers, unlimited for agents/admins
- **Legal Compliance:** All purchases require terms acceptance
- **No Refunds Policy:** All sales are final as per terms of service
- **Mobile Optimization:** All purchase flows optimized for mobile devices
- **Real-time Updates:** Availability and pricing updated in real-time

## User Management System

### User Roles & Permissions

The platform implements a comprehensive role-based access control (RBAC) system with four distinct user roles:

**1. Customer (customer)**
- Public registration with email verification
- Limited ticket access based on subscription
- Monthly subscription required after free trial period
- Access to scraped tickets for purchase
- Limited features compared to agents

**2. Agent (agent)**
- Professional ticket selection and purchasing role
- **Unlimited ticket access** regardless of subscription
- Can select and purchase tickets without limits
- Access to monitoring and performance metrics
- Advanced ticket filtering and automation features

**3. Scraper (scraper)**
- **System-only role** for scraping bot functionality
- **Cannot login to web interface**
- Used exclusively for ticket scraping operations
- No access to system features or data
- Managed by admins only

**4. Administrator (admin)**
- **Full system access** without any limitations
- User management and role assignment
- System configuration and platform management
- Financial reports and analytics access
- API access management and configuration

### Registration & Authentication

**Public Customer Registration:**
- Email verification required
- Optional 2FA with Google Authenticator
- Optional mobile phone verification via SMS
- **Mandatory legal document acceptance:**
  - Terms of Service
  - Service Disclaimer  
  - Data Processing Agreement
  - Cookie Policy

**Enhanced Security Features:**
- Multi-factor authentication (2FA)
- Device fingerprinting and geolocation checks
- Enhanced login security with automated tool detection
- Session management with Redis backend
- Failed login attempt tracking and account lockout

### Subscription System

**Monthly Subscription Model:**
- Configurable monthly fee (default: $29.99)
- Configurable ticket limits (default: 100/month)
- Free access period (default: 7 days for new customers)
- **No money-back guarantee** policy enforced

**Payment Processing:**
- Stripe integration for credit card payments
- PayPal integration for alternative payments
- Webhook handling for subscription status updates
- Automatic subscription renewal

**Access Control:**
- Customers require active subscription after free period
- Agents have unlimited access regardless of subscription
- Subscription status checked on each login
- Graceful degradation for expired subscriptions

### Legal Compliance System

**Required Legal Documents:**
- Terms of Service (with "as-is" disclaimers)
- Service Disclaimer (no warranty, no refunds)
- Privacy Policy (GDPR compliant)
- Data Processing Agreement (GDPR requirements)
- Cookie Policy (tracking and analytics)
- Acceptable Use Policy
- Legal Notices

**Compliance Tracking:**
- User acceptance tracking with IP and timestamp
- Version control for legal document updates
- Audit trail for compliance reporting
- GDPR-compliant data processing records

### Ticket Purchase System

**Access Requirements:**
- Verified email address
- Active subscription (customers) or agent role
- Not exceeded monthly ticket limits

**Purchase Validation:**
- Real-time availability checking
- Quantity limits enforcement
- Monthly allowance tracking
- Payment processing integration

**Role-Based Limits:**
- **Customers:** Limited by subscription plan
- **Agents:** Unlimited ticket access
- **Scrapers:** No purchase access
- **Admins:** Unlimited access

### Configuration

Key environment variables for user management:

```bash
# Subscription Settings
SUBSCRIPTION_FREE_ACCESS_DAYS=7
SUBSCRIPTION_DEFAULT_MONTHLY_FEE=29.99
SUBSCRIPTION_DEFAULT_TICKET_LIMIT=100
SUBSCRIPTION_AGENT_UNLIMITED_TICKETS=true

# Payment Processing
STRIPE_KEY=pk_test_your_stripe_key
STRIPE_SECRET=sk_test_your_stripe_secret
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# SMS Verification
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# Service Terms
SERVICE_PROVIDED_AS_IS=true
SERVICE_NO_WARRANTY=true
SERVICE_NO_MONEY_BACK_GUARANTEE=true
```

### Testing

Comprehensive test suite covers:
- User registration with legal document acceptance
- Role-based access control validation
- Subscription and payment processing
- Legal document compliance tracking
- Phone and email verification flows
- Two-factor authentication setup

## Architecture Decisions

- **DDD Architecture:** Chosen for complex business logic and maintainability
- **CQRS Pattern:** Separates read/write operations for better scalability  
- **Event Sourcing:** Planned for audit trails and historical data
- **Microservice Ready:** Clear boundaries support future service extraction
- **API First:** All features accessible via RESTful APIs
- **Role-Based Security:** Comprehensive RBAC system with four distinct user roles
- **Legal Compliance First:** Mandatory legal document acceptance with audit trails
