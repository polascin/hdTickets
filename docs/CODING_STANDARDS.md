# HD Tickets - Coding Standards & PSR Compliance Guide

## 🎯 Overview

This document outlines the coding standards and best practices for the HD Tickets sport events entry ticket system. All code must adhere to PSR-12 coding standards with additional Laravel-specific conventions.

## 📋 Table of Contents

- [PSR Standards Compliance](#psr-standards-compliance)
- [Namespace Structure](#namespace-structure)
- [Code Formatting](#code-formatting)
- [Naming Conventions](#naming-conventions)
- [Documentation Standards](#documentation-standards)
- [Quality Assurance Tools](#quality-assurance-tools)
- [Development Workflow](#development-workflow)
- [Automated Code Quality](#automated-code-quality)

---

## 🏆 PSR Standards Compliance

### PSR-4 Autoloading
- **MUST** follow PSR-4 autoloading standards
- Namespace structure **MUST** match directory structure
- No manual `include` or `require` statements in application code

### PSR-12 Coding Style
- **MUST** follow PSR-12 coding style guide
- 120 character line length limit
- 4 spaces for indentation (no tabs)
- Proper bracket placement and spacing

---

## 🗂️ Namespace Structure

### Base Namespace
```
App\
├── Application\          # CQRS Application Layer
│   ├── Commands\         # Command handlers
│   ├── Queries\          # Query handlers
│   └── EventHandlers\    # Domain event handlers
├── Domain\               # Domain Driven Design
│   ├── Event\            # Event aggregate
│   ├── Ticket\           # Ticket aggregate  
│   ├── Purchase\         # Purchase aggregate
│   └── Shared\           # Shared domain logic
├── Infrastructure\       # Infrastructure concerns
│   ├── EventStore\       # Event sourcing
│   ├── Persistence\      # Data persistence
│   └── External\         # External integrations
├── Http\                 # Web layer
│   ├── Controllers\      # HTTP controllers
│   ├── Middleware\       # HTTP middleware
│   └── Requests\         # Form requests
└── Services\             # Application services
    ├── Core\             # Core business services
    ├── Scraping\         # Ticket scraping services
    └── Security\         # Security services
```

### Namespace Rules
- **MUST** declare namespace at top of file
- **MUST** match directory structure exactly
- **MUST** use fully qualified names for imports

**Example:**
```php
<?php

declare(strict_types=1);

namespace App\Domain\Event\Entities;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Shared\Events\DomainEventInterface;
use DateTimeImmutable;

/**
 * Sports Event Entity
 * 
 * Represents a sports event in the ticket monitoring system.
 * This is NOT a helpdesk ticket but a sports event entry ticket!
 */
final class SportsEvent
{
    // Implementation...
}
```

---

## 🎨 Code Formatting

### Line Length
- **MUST** keep lines under 120 characters
- **SHOULD** break long lines sensibly

### Indentation
- **MUST** use 4 spaces for indentation
- **MUST NOT** use tabs

### Braces
- Opening braces **MUST** be on the same line for control structures
- Opening braces **MUST** be on the next line for classes and methods

```php
<?php

declare(strict_types=1);

namespace App\Services\Core;

final class TicketMonitoringService
{
    public function monitorTicket(string $ticketId): bool
    {
        if ($this->isValidTicket($ticketId)) {
            return $this->startMonitoring($ticketId);
        }
        
        return false;
    }
}
```

### Spacing
- **MUST** have one space after control structure keywords
- **MUST** have one space around binary operators
- **MUST NOT** have trailing whitespace

---

## 🏷️ Naming Conventions

### Classes
- **MUST** use `StudlyCaps` (PascalCase)
- **SHOULD** be descriptive and specific
- Domain entities **SHOULD** reflect business language

```php
class SportsEvent           // ✅ Good
class TicketMonitoringService  // ✅ Good
class ticket_service        // ❌ Bad - snake_case
class TMS                   // ❌ Bad - acronym
```

### Methods
- **MUST** use `camelCase`
- **SHOULD** start with verbs
- **SHOULD** clearly indicate intent

```php
public function monitorTicketAvailability(): void    // ✅ Good
public function getUpcomingEvents(): Collection      // ✅ Good
public function monitor_tickets(): void              // ❌ Bad - snake_case
public function doStuff(): mixed                     // ❌ Bad - unclear intent
```

### Properties
- **MUST** use `camelCase`
- **SHOULD** be descriptive
- **MUST** have proper visibility

```php
private string $eventName;              // ✅ Good  
private DateTimeImmutable $eventDate;  // ✅ Good
public $data;                          // ❌ Bad - no type hint
private $n;                            // ❌ Bad - unclear name
```

### Constants
- **MUST** use `UPPER_CASE` with underscores
- **SHOULD** be descriptive

```php
public const MAX_RETRY_ATTEMPTS = 3;        // ✅ Good
public const DEFAULT_TIMEOUT = 30;         // ✅ Good
public const maxRetries = 3;               // ❌ Bad - camelCase
```

### Variables
- **MUST** use `camelCase`
- **SHOULD** be descriptive

```php
$eventSchedule = new EventSchedule();      // ✅ Good
$upcomingMatches = $this->getMatches();    // ✅ Good
$e = new Event();                          // ❌ Bad - too short
$event_schedule = new EventSchedule();     // ❌ Bad - snake_case
```

---

## 📝 Documentation Standards

### PHPDoc Blocks
- **MUST** document all public classes
- **MUST** document all public methods
- **SHOULD** document complex private methods
- **MUST** include `@throws` annotations

```php
<?php

declare(strict_types=1);

namespace App\Domain\Event\Entities;

/**
 * Sports Event Entity
 * 
 * Represents a sports event for ticket monitoring.
 * Note: This system handles sports event tickets, NOT helpdesk tickets.
 * 
 * @final
 */
final class SportsEvent
{
    /**
     * Create a new sports event
     * 
     * @param EventId      $id         The event identifier
     * @param string       $name       The event name
     * @param SportCategory $category   The sport category
     * @param EventDate    $eventDate  The event date
     * @param Venue        $venue      The venue information
     * 
     * @throws InvalidArgumentException When event data is invalid
     */
    public function __construct(
        private readonly EventId $id,
        private readonly string $name,
        private readonly SportCategory $category,
        private readonly EventDate $eventDate,
        private readonly Venue $venue,
    ) {
        $this->validate();
    }

    /**
     * Check if the event is upcoming
     * 
     * @return bool True if the event is in the future
     */
    public function isUpcoming(): bool
    {
        return $this->eventDate->isUpcoming();
    }

    /**
     * Validate event data
     * 
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Event name cannot be empty');
        }
    }
}
```

### Class Documentation
- **MUST** include class purpose
- **SHOULD** include usage examples for complex classes
- **MUST** clarify business context (sports tickets, not helpdesk)

### Method Documentation  
- **MUST** describe what the method does
- **MUST** document all parameters
- **MUST** document return types
- **MUST** document thrown exceptions

---

## 🔧 Quality Assurance Tools

### PHP CS Fixer
Automatically fixes PSR-12 violations:

```bash
# Check for violations
composer code-style-check

# Fix violations automatically
composer code-style
```

Configuration: `.php-cs-fixer.php`

### PHPStan
Static analysis for type safety:

```bash
# Run static analysis
composer static-analysis

# Run with maximum strictness (level 8)
./vendor/bin/phpstan analyse --level=8
```

Configuration: `phpstan.neon`

### Laravel Pint
Laravel's opinionated code style fixer:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

Configuration: `pint.json`

### PHPUnit
Comprehensive testing with coverage:

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

Configuration: `phpunit.xml`

---

## 🔄 Development Workflow

### Before Committing
1. **Run code style fixer**: `composer fix-code`
2. **Run static analysis**: `composer static-analysis` 
3. **Run tests**: `composer test`
4. **Check overall quality**: `composer code-quality`

### Pre-commit Hook
The pre-commit hook automatically:
- Checks PHP syntax
- Validates PSR-12 compliance
- Runs static analysis
- Validates PSR-4 namespaces

### Continuous Integration
Quality checks run automatically on:
- Pull requests
- Main branch pushes
- Release creation

---

## 🤖 Automated Code Quality

### Quality Metrics Dashboard
Access comprehensive metrics at:
```bash
# Generate metrics report
composer quality-metrics

# View at: storage/quality/metrics/index.html
```

### Code Coverage Reports
Track test coverage:
```bash
# Generate coverage report  
composer test-coverage

# View at: storage/quality/coverage/html/index.html
```

### Quality Gates
Code must pass:
- ✅ PSR-12 compliance (100%)
- ✅ PHPStan level 8 (0 errors)
- ✅ Test coverage > 80%
- ✅ Cyclomatic complexity < 10

---

## 🚨 Common Violations & Fixes

### PSR-4 Namespace Issues
```php
// ❌ Bad - namespace doesn't match path
// File: app/Services/Core/TicketService.php
namespace App\Services\TicketService;

// ✅ Good - namespace matches path  
// File: app/Services/Core/TicketService.php
namespace App\Services\Core;
```

### PSR-12 Formatting Issues
```php
// ❌ Bad - incorrect brace placement
class TicketService
{
public function monitor($id)
{
if($id)
{
return true;
}
}
}

// ✅ Good - proper PSR-12 formatting
class TicketService
{
    public function monitor(string $id): bool
    {
        if ($id) {
            return true;
        }
        
        return false;
    }
}
```

### Documentation Issues
```php
// ❌ Bad - no documentation
class EventService
{
    public function process($data)
    {
        // Implementation...
    }
}

// ✅ Good - proper documentation
/**
 * Event Processing Service
 * 
 * Handles processing of sports event data for ticket monitoring.
 * Note: This processes sports events, not helpdesk tickets.
 */
class EventService
{
    /**
     * Process event data
     * 
     * @param array<string, mixed> $data The event data to process
     * 
     * @return bool True if processing succeeded
     * 
     * @throws InvalidArgumentException When data is invalid
     */
    public function process(array $data): bool
    {
        // Implementation...
    }
}
```

---

## 📊 Quality Commands Reference

| Command | Purpose |
|---------|---------|
| `composer fix-code` | Fix PSR-12 violations |
| `composer code-style-check` | Check PSR-12 compliance |
| `composer static-analysis` | Run PHPStan analysis |
| `composer code-quality` | Run all quality checks |
| `composer test` | Run PHPUnit tests |
| `composer test-coverage` | Generate coverage report |
| `composer quality-metrics` | Generate metrics dashboard |
| `composer full-quality-check` | Complete quality audit |

---

## 🎯 Remember

This system handles **sports event entry tickets**, NOT helpdesk tickets! All documentation and naming should reflect this business domain clearly.

For questions about coding standards, consult this document or run the automated quality tools.
