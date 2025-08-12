# 🎯 HD Tickets - PSR Standards & Quality Assurance

Welcome to the HD Tickets sports event entry ticket monitoring system! This README covers the comprehensive PSR-4 autoloading and PSR-12 coding standards implementation.

> **Important**: This system handles sports event entry tickets, NOT helpdesk tickets!

## 🚀 Quick Start

### 1. Install Dependencies
```bash
# Install all quality tools
composer install

# Or use our Makefile
make install
```

### 2. Setup Quality Environment
```bash
# Complete setup with directory creation
make setup

# Manual setup
mkdir -p storage/quality/{logs,coverage/{html,xml},metrics}
mkdir -p storage/{phpstan,phpunit/cache}
chmod -R 775 storage
```

### 3. Run Quality Checks
```bash
# Complete quality suite
make full-check

# Individual checks
make fix          # Fix PSR-12 violations
make analyze      # PHPStan static analysis  
make test         # PHPUnit tests
make security     # Security audit
```

---

## 📋 PSR Standards Implementation

### ✅ PSR-4 Autoloading
- **Namespace Structure**: Matches directory structure exactly
- **Autoloader**: Composer PSR-4 autoloader configured
- **No Manual Includes**: All includes/requires removed from application code
- **Validation**: Automated PSR-4 compliance checking

### ✅ PSR-12 Coding Standards
- **Line Length**: 120 character limit enforced
- **Indentation**: 4 spaces (no tabs)
- **Brace Placement**: PSR-12 compliant
- **Naming**: StudlyCaps classes, camelCase methods/properties
- **Documentation**: Complete PHPDoc coverage

---

## 🛠️ Quality Tools Configuration

### PHP CS Fixer (PSR-12 Compliance)
```bash
# Check compliance
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run

# Auto-fix violations  
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php

# Using composer scripts
composer code-style-check  # Check only
composer code-style        # Fix violations
```

**Configuration**: `.php-cs-fixer.php`

### PHPStan (Static Analysis)
```bash
# Run analysis (Level 8 - strictest)
./vendor/bin/phpstan analyse --configuration=phpstan.neon

# Using composer
composer static-analysis
```

**Configuration**: `phpstan.neon`

### Laravel Pint (Alternative Code Style)
```bash
# Check style
./vendor/bin/pint --test

# Fix style
./vendor/bin/pint

# Using make
make pint-check
make pint-fix
```

**Configuration**: `pint.json`

### PHPUnit (Testing & Coverage)
```bash
# Run tests
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html=storage/quality/coverage/html

# Using make
make test
make test-coverage
```

**Configuration**: `phpunit.xml`

---

## 📁 Project Structure (PSR-4 Compliant)

```
app/
├── Application/           # CQRS Application Layer
│   ├── Commands/         # Command handlers
│   ├── Queries/          # Query handlers
│   └── EventHandlers/    # Domain event handlers
├── Domain/               # Domain Driven Design
│   ├── Event/            # Sports Event aggregate
│   │   ├── Entities/     # Event entities
│   │   ├── ValueObjects/ # Event value objects
│   │   └── Events/       # Domain events
│   ├── Ticket/           # Ticket aggregate
│   ├── Purchase/         # Purchase aggregate
│   └── Shared/           # Shared domain logic
├── Infrastructure/       # Infrastructure layer
│   ├── EventStore/       # Event sourcing
│   ├── Persistence/      # Data persistence
│   └── External/         # External integrations
├── Http/                 # Web layer
│   ├── Controllers/      # HTTP controllers
│   │   └── Api/          # API controllers
│   ├── Middleware/       # HTTP middleware
│   └── Requests/         # Form validation
└── Services/             # Application services
    ├── Core/             # Core business services
    ├── Scraping/         # Ticket scraping
    └── Security/         # Security services
```

### Namespace Examples

```php
<?php

declare(strict_types=1);

// ✅ Correct PSR-4 namespace
// File: app/Domain/Event/Entities/SportsEvent.php
namespace App\Domain\Event\Entities;

// ✅ Correct PSR-4 namespace  
// File: app/Services/Core/TicketMonitoringService.php
namespace App\Services\Core;

// ❌ Incorrect - doesn't match path
// File: app/Services/Core/TicketService.php
namespace App\Services\TicketService;
```

---

## 🔄 Development Workflow

### Before Every Commit
```bash
# Quick pre-commit checks
make pre-commit

# Or individual steps
make syntax-check    # PHP syntax validation
make check-style     # PSR-12 compliance check
make analyze         # Static analysis
make psr4-check      # Namespace validation
```

### Development Cycle
```bash
# 1. Write code following PSR standards
# 2. Run quality checks
make dev-workflow

# 3. Fix any issues
make fix

# 4. Final validation
make quality

# 5. Commit (pre-commit hook runs automatically)
git add .
git commit -m "feat: implement new feature"
```

### Pre-commit Hook
Automatically runs on every commit:
- ✅ PHP syntax validation
- ✅ PSR-12 compliance check
- ✅ PHPStan static analysis
- ✅ PSR-4 namespace validation

---

## 📊 Quality Metrics & Reports

### Coverage Reports
```bash
# Generate HTML coverage report
make test-coverage

# View at: storage/quality/coverage/html/index.html
```

### Quality Metrics Dashboard
```bash
# Generate comprehensive metrics
make metrics

# View at: storage/quality/metrics/index.html
```

### Security Audits
```bash
# Run security checks
make security

# Composer vulnerability check
composer audit
```

---

## 🤖 Continuous Integration

### GitHub Actions
Automated quality checks run on:
- 🔄 Every push to main/develop
- 🔀 Every pull request
- ⏰ Weekly scheduled runs

**Pipeline includes**:
- PSR-12 compliance validation
- PSR-4 namespace validation
- Static analysis (PHPStan Level 8)
- Complete test suite with coverage
- Security vulnerability scanning
- Performance analysis

### Quality Gates
All code must pass:
- ✅ 100% PSR-12 compliance
- ✅ 0 PHPStan errors (Level 8)
- ✅ >80% test coverage
- ✅ 0 security vulnerabilities
- ✅ PSR-4 namespace compliance

---

## 📚 Available Commands

### Make Commands
```bash
make help           # Show all available commands
make install        # Install dependencies
make setup          # Complete project setup
make quality        # Run all quality checks
make fix            # Fix PSR-12 violations
make analyze        # Static analysis
make test           # Run tests
make security       # Security audit
make metrics        # Generate metrics
make clean          # Clean cache files
make full-check     # Complete quality suite
make status         # Show project status
```

### Composer Scripts
```bash
composer code-style            # Fix code style
composer code-style-check      # Check code style
composer static-analysis       # Run PHPStan
composer code-quality         # Run quality checks
composer test                 # Run tests
composer test-coverage        # Generate coverage
composer quality-metrics      # Generate metrics
composer full-quality-check   # Complete audit
```

---

## 🔧 IDE Configuration

### VS Code Settings
```json
{
  "php.suggest.basic": false,
  "php.validate.executablePath": "/usr/bin/php",
  "phpcs.enable": true,
  "phpcs.standard": "PSR12",
  "phpcs.executablePath": "./vendor/bin/phpcs",
  "phpcsFixer.executablePath": "./vendor/bin/php-cs-fixer",
  "phpcsFixer.config": ".php-cs-fixer.php"
}
```

### PHPStorm Configuration
1. Go to Settings → PHP → Quality Tools
2. Set PHP CS Fixer path: `./vendor/bin/php-cs-fixer`
3. Set configuration file: `.php-cs-fixer.php`
4. Enable "Format on save"

---

## 🐛 Troubleshooting

### Common Issues

#### PSR-4 Autoloading Issues
```bash
# Problem: Class not found
# Solution: Check namespace matches directory
make psr4-check

# Regenerate autoloader
composer dump-autoload
```

#### PSR-12 Style Violations
```bash
# Problem: Code style violations
# Solution: Auto-fix with PHP CS Fixer
make fix

# Or check what will be fixed
make check-style
```

#### PHPStan Errors
```bash
# Problem: Static analysis failures
# Solution: Review and fix type issues
make analyze

# Common fixes:
# - Add type hints
# - Fix PHPDoc comments
# - Resolve undefined properties/methods
```

#### Test Failures
```bash
# Problem: Tests failing
# Solution: Run specific test suites
make test-unit      # Unit tests only
make test-feature   # Feature tests only

# Debug with coverage
make test-coverage
```

### Getting Help

1. **Check Documentation**: `docs/CODING_STANDARDS.md`
2. **Run Status Check**: `make status`
3. **View Quality Reports**: 
   - Coverage: `storage/quality/coverage/html/index.html`
   - Metrics: `storage/quality/metrics/index.html`

---

## 🎯 Quality Standards Summary

| Standard | Tool | Level | Status |
|----------|------|-------|--------|
| PSR-4 Autoloading | Custom Script | 100% | ✅ Implemented |
| PSR-12 Coding Style | PHP CS Fixer | Strict | ✅ Implemented |
| Static Analysis | PHPStan | Level 8 | ✅ Implemented |
| Test Coverage | PHPUnit | >80% | ✅ Implemented |
| Security | Composer Audit | 0 Vulns | ✅ Implemented |
| Documentation | PHPDoc | Complete | ✅ Implemented |

---

## 🚀 Next Steps

1. **Run Initial Setup**:
   ```bash
   make setup
   make full-check
   ```

2. **Review Quality Reports**:
   - Check coverage reports
   - Review metrics dashboard
   - Address any violations

3. **Configure IDE**:
   - Install quality tool plugins
   - Configure auto-formatting
   - Enable real-time validation

4. **Team Onboarding**:
   - Share coding standards documentation
   - Setup pre-commit hooks for all developers
   - Integrate with CI/CD pipeline

---

## 📞 Support

For questions about PSR standards implementation or quality assurance:

- 📖 **Documentation**: `docs/CODING_STANDARDS.md`
- 🔧 **Tools Help**: `make help`
- 📊 **Quality Status**: `make status`

Remember: This system handles **sports event entry tickets**, not helpdesk tickets! 🎟️⚽

---

*Happy coding with high-quality, PSR-compliant code! 🎉*
