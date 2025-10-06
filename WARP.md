# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

Important
- This platform handles sports events entry tickets. It is NOT a helpdesk ticket system. Do not develop any helpdesk features.

Stack and versions (source of truth)
- Backend: PHP 8.3.x (composer.json ^8.3), Laravel 11.x (composer.json ^11.0)
- Frontend: Alpine.js, Tailwind CSS v4.1+, Vite, TypeScript (Node >= 18)
- Data/Infra: MySQL/MariaDB, Redis (cache, queues), Horizon
- Auth: Sanctum + Passport (OAuth2)
- Real-time: Laravel Echo + Pusher
- Scraping: Roach PHP, Symfony DOMCrawler
- Static analysis: PHPStan (current config level 5 via phpstan.neon), Larastan
- Formatters: Laravel Pint, PHP-CS-Fixer, Prettier, ESLint
- Testing: PHPUnit 11.x, Playwright (E2E), Vitest (frontend)

Commands (day-to-day)
Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Development servers
```bash
php artisan serve          # Backend dev server
npm run dev                # Vite dev server with HMR
php artisan horizon        # Queue worker + dashboard
make dev-workflow          # Fix -> analyze -> test
```

Build
```bash
npm run build
```

Testing (PHP)
```bash
php artisan test
vendor/bin/phpunit
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Integration

# Run a single test (examples)
vendor/bin/phpunit tests/Feature/TicketPurchaseWorkflowTest.php
vendor/bin/phpunit --filter TicketPurchaseWorkflowTest
vendor/bin/phpunit --filter 'TicketPurchaseWorkflowTest::test_specific_scenario'

# Coverage
make test-coverage
# HTML report: storage/quality/coverage/html/index.html
```

Testing (frontend)
```bash
npm run test                # Vitest unit tests
npm run test:coverage       # Vitest with coverage
npm run test:e2e           # Playwright E2E tests
npm run e2e:install        # Install Playwright browsers
npm run e2e                # Full E2E with server start
```

Code quality (PHP)
```bash
make quality               # Style + analysis
make analyze               # PHPStan
make fix                   # PHP-CS-Fixer (with project config)
vendor/bin/pint            # Laravel Pint (format)
vendor/bin/pint --test     # Pint dry-run
vendor/bin/phpstan analyse --configuration=phpstan.neon
```

Code quality (JS/TS)
```bash
npm run lint               # ESLint check
npm run lint:fix          # ESLint auto-fix
npm run format            # Prettier format
npm run format:check      # Prettier check (dry-run)
npm run type-check        # TypeScript type checking
npm run analyze           # Vite bundle analysis
npm run clean             # Clean build artifacts
```

Routes & tooling (useful shortcuts)
```bash
make routes-list
make routes-cache
make routes-clear
make routes-test
make security-check
make metrics
make full-check
make pre-commit
make clean
```

Testing environment (phpunit.xml)
- PHPUnit is configured to use MySQL (not in-memory SQLite):
  - DB_CONNECTION=mysql
  - DB_DATABASE=hdtickets_test_clean
  - DB_USERNAME=hdtickets
  - DB_PASSWORD=hdtickets
- Ensure a local test database is available with these credentials, or override via environment.
- PHPUnit 11.x: Strict testing with fail-fast options enabled
- Coverage reports: HTML (storage/quality/coverage/html/), XML, Clover, Cobertura formats

Architecture overview (big picture)
- Domain-Driven Design + CQRS
  - Domain (app/Domain): Event, Monitoring, Ticket, Purchase, System, Shared
    - Entities, ValueObjects, Domain Events per context (e.g., TicketPriceChanged, TicketAvailabilityChanged)
  - Application (app/Application):
    - Commands (writes) and Queries (reads)
    - EventHandlers coordinating domain operations
  - Infrastructure (app/Infrastructure):
    - EventBus (LaravelEventBus), EventStore (PostgreSqlEventStore + interfaces)
    - Persistence (Eloquent repositories), Projections (read models)
  - Services (app/Services):
    - Cross-cutting and domain orchestration (security, analytics, platforms, notifications)
    - Scraping:
      - Plugin-based architecture under app/Services/Scraping/Plugins (e.g., Ticketmaster, Viagogo, StubHub, SeeTickets, etc.)
      - Anti-detection, rate limiting, multi-language/currency traits
      - External anti-corruption layer (app/Infrastructure/External)
  - HTTP (app/Http):
    - Controllers, Form Requests, Resources
    - Extensive Middleware: role checks, subscription checks, API security, security headers, rate limiting, CSRF
  - Events:
    - Domain events under app/Domain/*/Events and app-level events under app/Events
  - Real-time & queues:
    - Laravel Echo + Pusher for broadcasts; Redis queues managed via Horizon
  - Auth & RBAC:
    - Passport + Sanctum; roles: admin, agent, customer, scraper
  - Purchase enforcement:
    - TicketPurchaseService, TicketPurchaseValidationMiddleware, TicketPurchaseRequest
    - Customers: subscription limits enforced; Agents/Admins: unlimited; Scrapers: no purchase

Docs & references
- Architecture: docs/architecture/README.md
- Development workflow & standards: docs/development/README.md
- Setup & environment: docs/setup/README.md

