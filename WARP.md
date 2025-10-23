# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Technology Stack (Source of Truth)

- **Backend**: PHP ^8.3, Laravel ^11.0
- **Testing**: Pest (pestphp/pest ^2.36), not PHPUnit
- **Code Quality**: Laravel Pint, PHPStan/Larastan Level 8, PHP-CS-Fixer, Rector
- **Frontend**: Alpine.js 3.14, Tailwind CSS (transitioning from v4 legacy), Vite 7.x, TypeScript
- **Database**: MySQL/MariaDB with Redis (cache, sessions, queues)
- **Queues**: Laravel Horizon with Redis driver
- **Real-time**: Laravel Echo + Pusher for WebSocket communication
- **Authentication**: Laravel Sanctum + Passport (OAuth2)
- **Environment**: Local PHP environment (no Docker/Sail in this repository)

## Setup

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
```

## Common Commands

### Development Servers
```bash
php artisan serve          # Backend server (http://localhost:8000)
npm run dev                # Frontend dev server with HMR
php artisan horizon        # Queue worker + dashboard
```

### Testing (Pest Only)
```bash
# Full test suite
vendor/bin/pest
composer test

# Test suites
vendor/bin/pest --testsuite=Unit
vendor/bin/pest --testsuite=Feature  
vendor/bin/pest --testsuite=Integration

# Single test file
vendor/bin/pest tests/Feature/ModernCustomerDashboardTest.php

# Filter by test name
vendor/bin/pest --filter="dashboard"
vendor/bin/pest --filter="TicketPurchaseWorkflowTest::test_customer_can_purchase"

# With coverage
vendor/bin/pest --coverage
```

### Code Quality
```bash
# Format code
vendor/bin/pint
composer format

# Static analysis  
vendor/bin/phpstan analyse --configuration=phpstan.neon
composer static-analysis

# All quality checks (via Makefile)
make quality                # Style + analysis + PSR-4
make fix                    # Fix style violations
make analyze                # PHPStan analysis
make full-check             # Complete quality suite
```

### Frontend
```bash
npm run build              # Production build
npm run lint               # ESLint
npm run type-check         # TypeScript checking
npm run format             # Prettier
```

### Database & Queues
```bash
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan horizon        # Preferred queue management
php artisan optimize       # Cache config/routes/events/views
```

## Architecture Overview

HD Tickets is a **Comprehensive Sports Events Entry Tickets Monitoring, Scraping and Purchase System** (not a helpdesk system) built using Domain-Driven Design with Event Sourcing and CQRS patterns.

### Core Domains
- **Event Management**: Sports events, venues, schedules (`app/Domain/Event/`)
- **Ticket Monitoring**: Price tracking, availability monitoring (`app/Domain/Ticket/`)
- **Purchase Management**: Automated purchasing, payment processing (`app/Domain/Purchase/`)
- **User Management**: Authentication, roles, preferences
- **System Monitoring**: Alerts, notifications, analytics

### Data Flow
1. **Ingestion**: Plugin-based scrapers (`app/Services/Scraping/Plugins/`) monitor 40+ platforms (Ticketmaster, StubHub, etc.)
2. **Normalisation**: Anti-corruption layers transform external data to domain objects
3. **Event Processing**: Domain events trigger side effects via event handlers
4. **Decision Making**: Purchase automation engines evaluate opportunities
5. **Actions**: Automated purchase attempts, user notifications

### Key Infrastructure
- **Event Store**: PostgreSQL-based event sourcing with `app/Infrastructure/EventStore/`
- **CQRS**: Commands/Queries in `app/Application/` with separate read models
- **Background Processing**: Laravel Horizon manages scraping jobs, notifications, analytics
- **Real-time Updates**: WebSocket integration for live price/availability changes
- **Anti-Detection**: Proxy rotation, browser automation, rate limiting for scraping

### Console Commands
Notable artisan commands for sports ticket operations:
- `scrape:tickets` - Run platform scraping
- `monitor:events` - Event monitoring
- `test:scraping` - Validate scraper functionality
- `paypal:test` - Payment integration testing

## Project Rules for AI Assistants

- Use British English spelling and conventions
- Domain is sports events entry tickets (football, cricket, etc.), **never** helpdesk tickets
- Use Pest for testing, **never** PHPUnit
- Follow Domain-Driven Design patterns established in the codebase
- Respect the event-sourcing architecture when making changes

## Related Documentation

- Main project info: [README.md](README.md)
- Architecture details: [docs/architecture/](docs/architecture/)
- Development guides: [docs/development/](docs/development/)
- CI/CD: [.github/workflows/deploy.yml](.github/workflows/deploy.yml)
- Quality tools: [Makefile](Makefile), [phpstan.neon](phpstan.neon)