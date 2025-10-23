# Test Attributes Migration Guide

This document explains the migration from PHPUnit docblock annotations to PHP 8 attributes for test methods in the HDTickets project.

## Overview

PHPUnit 10+ deprecated docblock annotations in favour of PHP 8 attributes. This project has migrated all test methods from using `@test`, `@dataProvider`, etc. annotations to using PHP 8 attributes like `#[Test]`, `#[DataProvider('methodName')]`, etc.

## Benefits of Attributes

- **Type Safety**: Attributes are part of the PHP language and provide better IDE support
- **Performance**: No need to parse docblocks at runtime
- **Future-Proof**: PHPUnit 12+ will remove support for annotations entirely
- **Cleaner Code**: More concise and readable test declarations

## Migration Examples

### Basic Test Method

**Before:**
```php
/**
 * @test
 */
public function user_can_login(): void
{
    // test implementation
}
```

**After:**
```php
use PHPUnit\Framework\Attributes\Test;

#[Test]
public function user_can_login(): void
{
    // test implementation
}
```

### Data Provider

**Before:**
```php
/**
 * @test
 * @dataProvider userDataProvider
 */
public function user_validation_works(string $email, bool $isValid): void
{
    // test implementation
}

public function userDataProvider(): array
{
    return [
        ['valid@email.com', true],
        ['invalid-email', false],
    ];
}
```

**After:**
```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

#[Test]
#[DataProvider('userDataProvider')]
public function user_validation_works(string $email, bool $isValid): void
{
    // test implementation
}

public static function userDataProvider(): array
{
    return [
        ['valid@email.com', true],
        ['invalid-email', false],
    ];
}
```

### Complex Example with Multiple Attributes

**Before:**
```php
/**
 * @test
 * @group integration
 * @dataProvider paymentDataProvider  
 * @depends test_user_is_created
 */
public function payment_processing_works($paymentData): void
{
    // test implementation
}
```

**After:**
```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

#[Test]
#[Group('integration')]
#[DataProvider('paymentDataProvider')]
#[Depends('test_user_is_created')]
public function payment_processing_works($paymentData): void
{
    // test implementation
}
```

## Common Attribute Mappings

| Annotation | Attribute | Import Required |
|------------|-----------|-----------------|
| `@test` | `#[Test]` | `PHPUnit\Framework\Attributes\Test` |
| `@dataProvider method` | `#[DataProvider('method')]` | `PHPUnit\Framework\Attributes\DataProvider` |
| `@depends testMethod` | `#[Depends('testMethod')]` | `PHPUnit\Framework\Attributes\Depends` |
| `@group groupName` | `#[Group('groupName')]` | `PHPUnit\Framework\Attributes\Group` |
| `@covers Class::method` | `#[CoversFunction('Class::method')]` | `PHPUnit\Framework\Attributes\CoversFunction` |
| `@covers Class` | `#[CoversClass(Class::class)]` | `PHPUnit\Framework\Attributes\CoversClass` |
| `@runInSeparateProcess` | `#[RunInSeparateProcess]` | `PHPUnit\Framework\Attributes\RunInSeparateProcess` |
| `@small` | `#[Small]` | `PHPUnit\Framework\Attributes\Small` |
| `@medium` | `#[Medium]` | `PHPUnit\Framework\Attributes\Medium` |
| `@large` | `#[Large]` | `PHPUnit\Framework\Attributes\Large` |

## Writing New Tests

When writing new test methods, always use attributes:

```php
<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function example_test_passes(): void
    {
        $this->assertTrue(true);
    }
}
```

## Tools and Scripts

### Migration Script
If you need to migrate annotations to attributes:
```bash
composer run migrate-test-annotations
```

### CI Checks
To ensure no new annotations are introduced:
```bash
composer run check-test-attributes
```

This is also run in CI to prevent regressions.

## IDE Support

Most modern IDEs (PhpStorm, VS Code with PHP extensions) properly understand and support PHP 8 attributes, providing:

- Syntax highlighting
- Autocompletion for attribute names
- Quick navigation to data provider methods
- Better refactoring support

## Important Notes

1. **Data Provider Methods**: Should be `static` when using attributes (PHPUnit 10+ requirement)
2. **Import Statements**: Always add the required `use` statements for attribute classes
3. **Multiple Attributes**: Each attribute goes on its own line above the method
4. **Class-Level Attributes**: Some attributes can be applied at the class level (e.g., `#[CoversClass]`)

## Migration Status

✅ **Complete**: All 103 test annotations have been successfully migrated to attributes across the test suite.

- 102 `@test` annotations → `#[Test]` attributes  
- 1 `@dataProvider` annotation → `#[DataProvider]` attribute

## Further Reading

- [PHPUnit Attributes Documentation](https://docs.phpunit.de/en/10.5/attributes.html)
- [PHP 8 Attributes RFC](https://wiki.php.net/rfc/attributes_v2)
- [PHPUnit Migration Guide](https://docs.phpunit.de/en/10.5/migration.html)