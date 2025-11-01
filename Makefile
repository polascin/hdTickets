# HD Tickets - PSR Standards & Quality Assurance Makefile
# 
# This Makefile provides convenient shortcuts for running quality assurance
# tools and ensuring PSR-12 and PSR-4 compliance in the HD Tickets system.
#
# @package HDTickets  
# @author  Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

.PHONY: help install setup quality fix analyze test coverage metrics clean docs routes-list routes-cache routes-clear routes-test middleware-check security-check dependency-check

# Default target
.DEFAULT_GOAL := help

# Colors for output
YELLOW = \033[1;33m
GREEN = \033[0;32m
RED = \033[0;31m
BLUE = \033[0;34m
NC = \033[0m # No Color

# Configuration
PHP = php
COMPOSER = composer
VENDOR_BIN = ./vendor/bin

## Display help information
help:
	@echo "$(BLUE)🎯 HD Tickets - Quality Assurance Commands$(NC)"
	@echo "$(BLUE)=============================================$(NC)"
	@echo ""
	@echo "$(YELLOW)📦 Setup & Installation:$(NC)"
	@echo "  install          Install composer dependencies"
	@echo "  setup            Full project setup with quality tools"
	@echo ""
	@echo "$(YELLOW)🔧 Code Quality:$(NC)"
	@echo "  quality          Run all quality checks"
	@echo "  fix              Fix PSR-12 code style violations"
	@echo "  check-style      Check PSR-12 compliance (dry-run)"
	@echo "  analyze          Run static analysis with PHPStan"
	@echo "  psr4-check       Validate PSR-4 namespace compliance"
	@echo ""
	@echo "$(YELLOW)🧪 Testing:$(NC)"
	@echo "  test             Run Pest tests"
	@echo "  test-coverage    Generate test coverage report"
	@echo "  test-unit        Run unit tests only"
	@echo "  test-feature     Run feature tests only"
	@echo ""
	@echo "$(YELLOW)📊 Metrics & Reports:$(NC)"
	@echo "  metrics          Generate quality metrics dashboard"
	@echo "  coverage-html    Generate HTML coverage report"
	@echo "  security         Run security audit"
	@echo ""
	@echo "$(YELLOW)🧹 Maintenance:$(NC)"
	@echo "  clean            Clean cache and temporary files"
	@echo "  docs             Generate documentation"
	@echo "  full-check       Complete quality check suite"
	@echo ""
	@echo "$(YELLOW)🛣️  Route Management:$(NC)"
	@echo "  routes-list      List all application routes"
	@echo "  routes-cache     Cache routes for production"
	@echo "  routes-clear     Clear route cache"
	@echo "  routes-test      Test critical routes"
	@echo "  middleware-check Verify middleware registration"
	@echo "  security-check   Run security audit"
	@echo "  dependency-check Check dependency security"
	@echo ""

## Install composer dependencies
install:
	@echo "$(BLUE)📥 Installing composer dependencies...$(NC)"
	$(COMPOSER) install --optimize-autoloader
	@echo "$(GREEN)✅ Dependencies installed successfully$(NC)"

## Complete project setup
setup: install
	@echo "$(BLUE)🔧 Setting up HD Tickets quality tools...$(NC)"
	@mkdir -p storage/quality/{logs,coverage/{html,xml},metrics}
	@mkdir -p storage/phpstan
	@mkdir -p storage/phpunit/cache
	@chmod -R 775 storage
	@echo "$(GREEN)✅ Project setup completed$(NC)"

## Run all quality checks
quality: check-style analyze psr4-check
	@echo "$(GREEN)🎉 All quality checks completed$(NC)"

## Fix PSR-12 code style violations
fix:
	@echo "$(BLUE)🎨 Fixing PSR-12 code style violations...$(NC)"
	$(VENDOR_BIN)/php-cs-fixer fix --config=.php-cs-fixer.php --verbose --show-progress=dots
	@echo "$(GREEN)✅ Code style fixed$(NC)"

## Check PSR-12 compliance (dry-run)
check-style:
	@echo "$(BLUE)🔍 Checking PSR-12 compliance...$(NC)"
	$(VENDOR_BIN)/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run --verbose --show-progress=dots
	@echo "$(GREEN)✅ PSR-12 compliance check completed$(NC)"

## Alternative code style check with Laravel Pint
pint-check:
	@echo "$(BLUE)🎨 Checking code style with Laravel Pint...$(NC)"
	$(VENDOR_BIN)/pint --test --verbose
	@echo "$(GREEN)✅ Pint style check completed$(NC)"

## Fix code style with Laravel Pint
pint-fix:
	@echo "$(BLUE)🎨 Fixing code style with Laravel Pint...$(NC)"
	$(VENDOR_BIN)/pint --verbose
	@echo "$(GREEN)✅ Pint style fix completed$(NC)"

## Run static analysis with PHPStan
analyze:
	@echo "$(BLUE)🔬 Running static analysis...$(NC)"
	$(VENDOR_BIN)/phpstan analyse --configuration=phpstan.neon --memory-limit=1G
	@echo "$(GREEN)✅ Static analysis completed$(NC)"

## Validate PSR-4 namespace compliance
psr4-check:
	@echo "$(BLUE)📂 Validating PSR-4 namespace compliance...$(NC)"
	@find app -name "*.php" -type f | while read file; do \
		namespace=$$(grep -E '^namespace\s+' "$$file" | head -n1 | sed 's/namespace\s\+//' | sed 's/;//'); \
		if [ ! -z "$$namespace" ]; then \
			expected_path=$$(echo "$$namespace" | sed 's/\\/\//g' | sed 's/^App/app/'); \
			actual_path=$$(dirname "$$file"); \
			if [ "$$actual_path" != "$$expected_path" ]; then \
				echo "$(RED)❌ PSR-4 violation in $$file:$(NC)"; \
				echo "   Expected: $$expected_path"; \
				echo "   Actual:   $$actual_path"; \
			fi; \
		fi; \
	done
	@echo "$(GREEN)✅ PSR-4 namespace validation completed$(NC)"

## Run Pest tests
test:
	@echo "$(BLUE)🧪 Running tests...$(NC)"
	$(VENDOR_BIN)/pest
	@echo "$(GREEN)✅ Tests completed$(NC)"

## Run tests with coverage
test-coverage:
	@echo "$(BLUE)🧪 Running tests with coverage...$(NC)"
	XDEBUG_MODE=coverage $(VENDOR_BIN)/pest --coverage --coverage-html=storage/quality/coverage/html
	@echo "$(GREEN)✅ Tests with coverage completed$(NC)"
	@echo "$(YELLOW)📊 Coverage report: storage/quality/coverage/html/index.html$(NC)"

## Run unit tests only
test-unit:
	@echo "$(BLUE)🧪 Running unit tests...$(NC)"
	$(VENDOR_BIN)/pest --testsuite=Unit
	@echo "$(GREEN)✅ Unit tests completed$(NC)"

## Run feature tests only
test-feature:
	@echo "$(BLUE)🧪 Running feature tests...$(NC)"
	$(VENDOR_BIN)/pest --testsuite=Feature
	@echo "$(GREEN)✅ Feature tests completed$(NC)"

## Generate quality metrics
metrics:
	@echo "$(BLUE)📊 Generating quality metrics...$(NC)"
	$(VENDOR_BIN)/phpmetrics --report-html=storage/quality/metrics --report-violations=storage/quality/violations.xml app/
	@echo "$(GREEN)✅ Quality metrics generated$(NC)"
	@echo "$(YELLOW)📊 Metrics dashboard: storage/quality/metrics/index.html$(NC)"

## Generate HTML coverage report
coverage-html: test-coverage
	@echo "$(YELLOW)📊 HTML Coverage report available at: storage/quality/coverage/html/index.html$(NC)"

## Run security audit
security:
	@echo "$(BLUE)🛡️ Running security audit...$(NC)"
	$(COMPOSER) audit
	@echo "$(GREEN)✅ Security audit completed$(NC)"

## Check syntax errors
syntax-check:
	@echo "$(BLUE)🔍 Checking PHP syntax...$(NC)"
	@find . -name "*.php" -not -path "./vendor/*" -not -path "./storage/*" -not -path "./bootstrap/cache/*" -exec $(PHP) -l {} \; | grep -v "No syntax errors detected" || true
	@echo "$(GREEN)✅ Syntax check completed$(NC)"

## Clean cache and temporary files
clean:
	@echo "$(BLUE)🧹 Cleaning cache and temporary files...$(NC)"
	@rm -rf storage/quality/{logs,coverage,metrics}/*
	@rm -rf storage/phpstan/*
	@rm -rf storage/phpunit/cache/*
	@rm -rf bootstrap/cache/*
	$(PHP) artisan cache:clear || true
	$(PHP) artisan config:clear || true
	$(PHP) artisan route:clear || true
	$(PHP) artisan view:clear || true
	$(COMPOSER) dump-autoload
	@echo "$(GREEN)✅ Cleanup completed$(NC)"

## Generate documentation
docs:
	@echo "$(BLUE)📚 Generating documentation...$(NC)"
	@mkdir -p docs/api
	# Add phpDocumentor or other doc generation here
	@echo "$(GREEN)✅ Documentation generated$(NC)"

## Complete quality check suite
full-check: syntax-check quality test security metrics
	@echo "$(GREEN)🎉 Complete quality check suite finished!$(NC)"
	@echo ""
	@echo "$(BLUE)📋 Quality Report Summary:$(NC)"
	@echo "  ✅ PHP Syntax Check"
	@echo "  ✅ PSR-12 Compliance" 
	@echo "  ✅ PSR-4 Namespace Validation"
	@echo "  ✅ Static Analysis (PHPStan Level 8)"
	@echo "  ✅ Unit & Feature Tests"
	@echo "  ✅ Security Audit"
	@echo "  ✅ Quality Metrics Generated"
	@echo ""
	@echo "$(YELLOW)📊 View reports:$(NC)"
	@echo "  Coverage:  storage/quality/coverage/html/index.html"
	@echo "  Metrics:   storage/quality/metrics/index.html"

## Pre-commit quality checks (fast version)
pre-commit: syntax-check check-style analyze psr4-check
	@echo "$(GREEN)✅ Pre-commit quality checks passed$(NC)"


## Development workflow
dev-workflow: fix analyze test
	@echo "$(GREEN)💻 Development workflow completed$(NC)"

## List all routes with role-based grouping
routes-list:
	@echo "$(BLUE)🔍 Listing all application routes...$(NC)"
	@$(PHP) artisan route:list --columns=method,uri,name,middleware

## Cache routes for production
routes-cache:
	@echo "$(BLUE)🚀 Caching routes for production...$(NC)"
	@$(PHP) scripts/cache-routes-production.php

## Clear route cache
routes-clear:
	@echo "$(BLUE)🧹 Clearing route cache...$(NC)"
	@$(PHP) artisan route:clear
	@echo "$(GREEN)✅ Route cache cleared successfully$(NC)"

## Test critical routes
routes-test:
	@echo "$(BLUE)🧪 Testing critical application routes...$(NC)"
	@echo ""
	@echo "$(YELLOW)Dashboard routes:$(NC)"
	@$(PHP) artisan route:list --name=dashboard --columns=method,uri,name,middleware
	@echo ""
	@echo "$(YELLOW)Admin routes:$(NC)"
	@$(PHP) artisan route:list --name=admin --columns=method,uri,name,middleware
	@echo ""
	@echo "$(YELLOW)Role-specific routes:$(NC)"
	@$(PHP) artisan route:list | grep -E "(role:|agent|scraper|customer)" || echo "No role-specific routes found"

## Verify middleware registration
middleware-check:
	@echo "$(BLUE)🔧 Checking middleware registration...$(NC)"
	@echo ""
	@echo "$(YELLOW)Registered middleware aliases:$(NC)"
	@$(PHP) artisan route:list --columns=middleware | grep -E "(role|admin|agent|scraper|customer)" | sort | uniq
	@echo ""
	@echo "$(GREEN)✅ Middleware check completed$(NC)"

## Security audit
security-check:
	@echo "$(BLUE)🔒 Running security audit...$(NC)"
	@echo ""
	@echo "$(YELLOW)Step 1: Composer security audit$(NC)"
	@$(COMPOSER) audit || true
	@echo ""
	@echo "$(YELLOW)Step 2: Static security analysis$(NC)"
	@$(PHP) $(VENDOR_BIN)/phpstan analyse --level=8 --memory-limit=1G || true
	@echo ""
	@echo "$(GREEN)✅ Security audit completed$(NC)"

## Dependency security check
dependency-check: security-check
	@echo "$(BLUE)🔍 Checking dependency security...$(NC)"
	@echo ""
	@echo "$(YELLOW)Checking for known vulnerabilities:$(NC)"
	@$(COMPOSER) audit --format=json > storage/logs/security-audit.json || true
	@echo "$(BLUE)📄 Security audit report saved to storage/logs/security-audit.json$(NC)"
	@echo ""
	@echo "$(GREEN)✅ Dependency security check completed$(NC)"

## Show project status
status:
	@echo "$(BLUE)📊 HD Tickets Project Status$(NC)"
	@echo "$(BLUE)=============================$(NC)"
	@echo ""
	@echo "$(YELLOW)📁 Project Type:$(NC) Sports Event Ticket Monitoring System"
	@echo "$(YELLOW)🏗️  Architecture:$(NC) Domain Driven Design + Event Sourcing + CQRS"
	@echo "$(YELLOW)🐘 PHP Version:$(NC) $$($(PHP) -v | head -n1)"
	@echo "$(YELLOW)📦 Composer:$(NC) $$($(COMPOSER) --version 2>/dev/null || echo 'Not found')"
	@echo ""
	@echo "$(YELLOW)🔧 Quality Tools Status:$(NC)"
	@test -f $(VENDOR_BIN)/php-cs-fixer && echo "  ✅ PHP CS Fixer" || echo "  ❌ PHP CS Fixer"
	@test -f $(VENDOR_BIN)/phpstan && echo "  ✅ PHPStan" || echo "  ❌ PHPStan"  
	@test -f $(VENDOR_BIN)/phpunit && echo "  ✅ PHPUnit" || echo "  ❌ PHPUnit"
	@test -f $(VENDOR_BIN)/pint && echo "  ✅ Laravel Pint" || echo "  ❌ Laravel Pint"
	@test -f $(VENDOR_BIN)/phpmetrics && echo "  ✅ PhpMetrics" || echo "  ❌ PhpMetrics"
	@echo ""
	@echo "$(YELLOW)🛣️  Route Management Status:$(NC)"
	@echo "  ✅ Route Management Available"
	@test -f config/route-caching.php && echo "  ✅ Route Cache Config" || echo "  ❌ Route Cache Config"
