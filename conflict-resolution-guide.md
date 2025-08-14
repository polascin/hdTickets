# Composer Conflict Resolution Guide
## Sports Event Tickets Monitoring System

### Step-by-Step Conflict Resolution

#### 1. Initial Conflict Detection
```bash
# Check for conflicts before updating
composer update --dry-run

# If conflicts are found, analyze them
composer diagnose
```

#### 2. Analyze Specific Conflicts
```bash
# Check why a specific package version cannot be installed
composer why-not <package> <version>

# Example: Why can't we use Laravel 11?
composer why-not laravel/framework ^11.0

# Check what packages depend on a specific package
composer depends <package>
composer depends --tree <package>
```

#### 3. Common Conflict Scenarios & Solutions

#### A. PHP Version Conflicts
Current requirement: `"php": "^8.4"`

**Problem**: Package requires older PHP version
```
composer why-not php ^8.3
```

**Solutions**:
1. Update the conflicting package to a version supporting PHP 8.4
2. Temporarily relax PHP constraint if necessary:
   ```json
   "php": "^8.3|^8.4"
   ```

#### B. Laravel Framework Conflicts
Current requirement: `"laravel/framework": "^12.0"`

**Problem**: Package incompatible with Laravel 12
```bash
composer why-not laravel/framework ^11.0
```

**Solutions**:
1. Find Laravel 12 compatible version of the package
2. Check package's GitHub for Laravel 12 support
3. Temporarily use dev versions if stable versions aren't available

#### C. Symfony Component Version Conflicts
Current requirements:
- `"symfony/dom-crawler": "^7.0"`
- `"symfony/css-selector": "^7.0"`

**Problem**: Version mismatch between Symfony components
```bash
composer why-not symfony/console ^6.0
```

**Solutions**:
1. Ensure all Symfony components use compatible versions
2. Let Laravel manage Symfony versions (remove explicit Symfony requirements)

#### 4. Resolution Workflow

```bash
# 1. Backup current working state
cp composer.json composer.json.backup
cp composer.lock composer.lock.backup

# 2. Try update with conflict resolution
composer update --with-dependencies

# 3. If conflicts occur, analyze
composer why-not <problematic-package> <problematic-version>

# 4. Adjust composer.json constraints
# Edit composer.json to relax version constraints

# 5. Try update again
composer update

# 6. If still failing, use more aggressive resolution
composer update --ignore-platform-reqs  # Use with caution!

# 7. Verify installation works
composer install
php artisan --version
```

#### 5. Package-Specific Solutions

#### Laravel Packages
```bash
# For Laravel-specific packages, check Laravel compatibility
composer show laravel/sanctum
composer show laravel/passport
composer show laravel/horizon
```

#### Third-party Packages
```bash
# For packages like Guzzle, Stripe, etc.
composer outdated
composer show <package-name>
```

#### 6. Emergency Recovery

If you break your dependencies:
```bash
# Restore from backup
cp composer.json.backup composer.json
cp composer.lock.backup composer.lock
composer install

# Or reset completely
rm composer.lock
rm -rf vendor/
composer install
```

#### 7. Monitoring for Future Conflicts

```bash
# Regular maintenance commands
composer outdated --direct
composer audit
composer validate
```

### Environment-Specific Notes

- **Ubuntu 24.04 LTS**: Ensure system packages are compatible
- **Apache2 + PHP 8.4**: Web server configuration should support PHP 8.4
- **MySQL/MariaDB**: Database drivers should be compatible with PHP 8.4

### Development vs Production

#### Development
- More permissive with version constraints
- Can use `--ignore-platform-reqs` if needed
- Regular updates and testing

#### Production
- Conservative with updates
- Always test in staging first
- Keep detailed logs of changes

### Contact and Support
For application-specific issues, refer to:
- Laravel 12.x documentation
- PHP 8.4 migration guide
- Package-specific GitHub repositories
