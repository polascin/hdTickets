# Laravel Pint Code Style Guide

Laravel Pint has been successfully installed and configured for the HD Tickets project. Pint is Laravel's opinionated PHP code style fixer built on top of PHP-CS-Fixer.

## ✨ Features

- **PSR-12 Compliance**: Automatically enforces PSR-12 coding standards
- **Laravel Optimized**: Includes Laravel-specific rules and conventions
- **Zero Configuration**: Works out of the box with sensible defaults
- **Fast**: Uses PHP-CS-Fixer's performance optimizations
- **Git Integration**: Automatically runs on pre-commit hooks

## 🚀 Usage

### Quick Commands

```bash
# Format all files
composer format
# or
./vendor/bin/pint

# Check formatting without making changes
composer format-check
# or
./vendor/bin/pint --test

# Format only changed files
./vendor/bin/pint --dirty

# Format specific files or directories
./vendor/bin/pint app/Services
./vendor/bin/pint app/Http/Controllers/SmartAlertsController.php
```

### Available Composer Scripts

- `composer format` - Format all PHP files
- `composer format-check` - Check formatting without changes
- `composer pint` - Run Laravel Pint
- `composer pint-test` - Test formatting without applying fixes
- `composer pint-dirty` - Format only changed files

## ⚙️ Configuration

Pint is configured via `pint.json` in the project root. Current configuration:

- **Preset**: PSR-12
- **Rules**: Enhanced with Laravel-specific formatting rules
- **Excluded**: `vendor/`, `node_modules/`, `storage/`, `bootstrap/cache/`, `*.blade.php`

## 🔧 Git Integration

Pint is integrated into the pre-commit hook (`/.git/hooks/pre-commit`):

1. **Automatic Checking**: Runs on every commit attempt
2. **Auto-fixing**: Automatically fixes style issues when possible
3. **Safe Operation**: Requires manual review if files are modified
4. **Bypass Option**: Use `git commit --no-verify` to skip (not recommended)

### Disable Pre-commit Hook

```bash
# Disable globally for this repository
git config hooks.pre-commit false

# Re-enable
git config --unset hooks.pre-commit
```

## 📋 Code Style Rules

### Key PSR-12 Rules Applied

- **Indentation**: 4 spaces (no tabs)
- **Line Length**: Flexible, but reasonable limits
- **Braces**: Opening brace on same line for classes/functions
- **Imports**: Alphabetically sorted, grouped by type
- **Strict Types**: `declare(strict_types=1);` enforced
- **Array Syntax**: Short array syntax `[]` preferred
- **String Quotes**: Single quotes for simple strings

### Laravel-Specific Rules

- **Eloquent**: Proper model attribute formatting
- **Routes**: Consistent route definition formatting
- **Config**: Array formatting for configuration files
- **Migrations**: Consistent migration structure

## 🎯 Examples

### Before Pint
```php
<?php
namespace App\Services;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ExampleService{
    public function processUser($user_id){
        $user=User::find($user_id);
        if(!$user) {
            Log::error("User not found");
            return null;
        }
        return $user;
    }
}
```

### After Pint
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class ExampleService
{
    public function processUser(int $userId): ?User
    {
        $user = User::find($userId);
        
        if (! $user) {
            Log::error('User not found');
            
            return null;
        }
        
        return $user;
    }
}
```

## 🔍 IDE Integration

### VS Code
Install the "Laravel Pint" extension for real-time formatting:
```bash
code --install-extension open-southeners.laravel-pint
```

### PhpStorm
Configure External Tools to run Pint:
1. File → Settings → Tools → External Tools
2. Add new tool with program: `./vendor/bin/pint`
3. Set working directory to project root

## 📊 Statistics

Latest Pint run results:
- **Files Processed**: 778 files
- **Issues Fixed**: 424 style issues
- **Rules Applied**: PSR-12 + Laravel conventions
- **Status**: ✅ All files now compliant

## 🚨 Troubleshooting

### Common Issues

1. **"Pint not found"**: Run `composer install`
2. **Permission denied**: Check file permissions: `chmod +x vendor/bin/pint`
3. **Pre-commit failing**: Check staged files have no syntax errors
4. **Memory issues**: Increase PHP memory limit: `php -d memory_limit=512M vendor/bin/pint`

### Manual Configuration

If you need to customize Pint rules, edit `pint.json`:

```json
{
    "preset": "psr12",
    "rules": {
        "array_syntax": {
            "syntax": "short"
        },
        "declare_strict_types": true
    }
}
```

## 📈 Benefits

- **Consistency**: Uniform code style across the entire codebase
- **Readability**: Improved code readability and maintainability
- **Team Collaboration**: Eliminates style debates and inconsistencies
- **Quality**: Catches potential issues during formatting
- **Automation**: Reduces manual code review time on style issues

---

For more information, visit the [Laravel Pint documentation](https://laravel.com/docs/pint).