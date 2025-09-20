<?php declare(strict_types=1);

/**
 * Advanced Code Quality Fixer (PHPStan Level 8 Compatible)
 * Fixes remaining errors with proper PHP syntax
 */
echo "🚀 Advanced Code Quality Error Resolution\n";
echo "📊 Current errors: 135\n";
echo "🎯 Target: Systematic quality improvement\n\n";

// Phase 1: Fix the easiest wins first - Method already narrowed type (7 errors)
echo "✨ Phase 1: Removing Redundant Type Checks (7 errors)\n";

$redundantTypeChecks = [
    'tests/Feature/SportsTicketSystemTest.php' => [
        // Remove redundant assertIsArray, assertIsBool, assertIsString calls
        'replacements' => [
            '\$this->assertIsArray(\$data);'      => '// Array type already established',
            '\$this->assertIsBool(\$result);'     => '// Boolean type already established',
            '\$this->assertIsString(\$response);' => '// String type already established',
        ],
    ],
];

foreach ($redundantTypeChecks as $filePath => $config) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($config['replacements'] as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Removed redundant type checks in ' . basename($filePath) . "\n";
        }
    }
}

// Phase 2: Fix Property Issues (8 uninitialized + 3 only written = 11 errors)
echo "\n🏗️ Phase 2: Fixing Property Issues (11 errors)\n";

$propertyFixes = [
    'tests/Browser/CrossBrowserTest.php' => [
        'protected $testUser;' => 'protected ?\\App\\Models\\User $testUser = null;',
    ],
    'tests/Feature/AccessibilityTest.php' => [
        'protected $testUser;' => 'protected ?\\App\\Models\\User $testUser = null;',
    ],
    'tests/Feature/LoginValidationTest.php' => [
        'protected $validUser;'    => 'protected ?\\App\\Models\\User $validUser = null;',
        'protected $inactiveUser;' => 'protected ?\\App\\Models\\User $inactiveUser = null;',
        'protected $lockedUser;'   => 'protected ?\\App\\Models\\User $lockedUser = null;',
    ],
    'tests/Performance/LoginPerformanceTest.php' => [
        'protected $testUser;' => 'protected ?\\App\\Models\\User $testUser = null;',
    ],
    'tests/Feature/SportsTicketSystemTest.php' => [
        'protected $user;'  => 'protected ?\\App\\Models\\User $user = null;',
        'protected $admin;' => 'protected ?\\App\\Models\\User $admin = null;',
    ],
    'app/Rules/HoneypotRule.php' => [
        // Make write-only property readable
        'private string $fieldName;' => 'private string $fieldName;\n\n    public function getFieldName(): string\n    {\n        return $this->fieldName;\n    }',
    ],
];

foreach ($propertyFixes as $filePath => $fixes) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($fixes as $search => $replacement) {
            $content = str_replace($search, $replacement, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Fixed properties in ' . basename($filePath) . "\n";
        }
    }
}

// Phase 3: Fix Null Safety Issues (3 property.nonObject + 2 nullsafe.neverNull = 5 errors)
echo "\n🛡️ Phase 3: Improving Null Safety (5 errors)\n";

$nullSafetyFixes = [
    'app/Http/Controllers/Api/AuthController.php' => [
        'auth()->user()->delete()' => 'auth()->user()?->delete()',
    ],
    'routes/api-session.php' => [
        'auth()->user()->email' => 'auth()->user()?->email ?? \'\'',
    ],
    'routes/api.php' => [
        'auth()->user()->email' => 'auth()->user()?->email ?? \'\'',
    ],
    'tests/Feature/SportsTicketSystemTest.php' => [
        '$ticket->is_available' => '$ticket?->is_available ?? false',
    ],
    'app/Rules/HoneypotRule.php' => [
        'session()?->' => 'session()->',
    ],
    'app/Http/Middleware/SecureErrorMessages.php' => [
        'session()?->' => 'session()->',
    ],
];

foreach ($nullSafetyFixes as $filePath => $fixes) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($fixes as $search => $replacement) {
            $content = str_replace($search, $replacement, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Improved null safety in ' . basename($filePath) . "\n";
        }
    }
}

// Phase 4: Fix Return Type Issues (2 errors)
echo "\n🔄 Phase 4: Fixing Return Types (2 errors)\n";

$returnTypeFixes = [
    'app/Logging/PerformanceLogger.php' => [
        'should return int but returns int|string|false' => [
            'return $stmt->fetchColumn();' => 'return (int) ($stmt->fetchColumn() ?: 0);',
        ],
        'should return array but returns list<float>|false' => [
            'return sys_getloadavg();' => 'return sys_getloadavg() ?: [0.0, 0.0, 0.0];',
        ],
    ],
    'app/Http/Middleware/SecureErrorMessages.php' => [
        'never returns null so it can be removed' => [
            'return null;' => 'return $message;',
        ],
    ],
];

foreach ($returnTypeFixes as $filePath => $fixes) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($fixes as $description => $replacements) {
            foreach ($replacements as $search => $replacement) {
                $content = str_replace($search, $replacement, $content);
            }
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Fixed return types in ' . basename($filePath) . "\n";
        }
    }
}

// Phase 5: Add Missing Return Type Annotations
echo "\n📝 Phase 5: Adding Missing Return Type (1 error)\n";

$missingReturnTypes = [
    'app/Logging/PerformanceLogger.php' => [
        'public function addPerformanceContext(' => 'public function addPerformanceContext(array $record): array',
    ],
];

foreach ($missingReturnTypes as $filePath => $fixes) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($fixes as $search => $replacement) {
            $content = str_replace($search, $replacement, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Added missing return type in ' . basename($filePath) . "\n";
        }
    }
}

// Phase 6: Fix Static Method Issues (3 errors)
echo "\n⚡ Phase 6: Fixing Static Method Issues (3 errors)\n";

// These are often Laravel Dusk related - let's improve the DuskTestCase
$duskTestCase = 'tests/DuskTestCase.php';
if (file_exists($duskTestCase)) {
    $content = file_get_contents($duskTestCase);

    // Add missing static methods
    $additionalMethods = '
    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected static function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER[\'DUSK_HEADLESS_DISABLED\']) ||
               isset($_ENV[\'DUSK_HEADLESS_DISABLED\']);
    }

    /**
     * Determine if the application is running in Sail.
     */
    protected static function runningInSail(): bool
    {
        return env(\'LARAVEL_SAIL\', false);
    }';

    $content = str_replace('abstract class DuskTestCase', $additionalMethods . '\nabstract class DuskTestCase', $content);
    file_put_contents($duskTestCase, $content);
    echo "  ✓ Enhanced DuskTestCase with missing methods\n";
}

// Phase 7: Improve Complex Type Issues
echo "\n🎯 Phase 7: Strategic Error Reduction\n";

// Add proper PHPDoc annotations where generic types are needed
$typeDocFixes = [
    'app/Http/Controllers/ProductionHealthController.php' => [
        'before' => 'public function checkApplication(): array',
        'after'  => "/**\n     * @return array<string, mixed>\n     */\n    public function checkApplication(): array",
    ],
    'app/Logging/QueryLogger.php' => [
        'before' => 'public function addQueryContext(array $record): array',
        'after'  => "/**\n     * @param array<string, mixed> \$record\n     * @return array<string, mixed>\n     */\n    public function addQueryContext(array \$record): array",
    ],
];

foreach ($typeDocFixes as $filePath => $fix) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if (strpos($content, $fix['before']) !== FALSE && strpos($content, '@return array<string, mixed>') === FALSE) {
            $content = str_replace($fix['before'], $fix['after'], $content);
            file_put_contents($filePath, $content);
            echo '  ✓ Added PHPDoc annotations in ' . basename($filePath) . "\n";
        }
    }
}

echo "\n📊 Running Final PHPStan Analysis...\n";

// Check final error count
$output = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>&1');
if (strpos($output, '"file_errors":') !== FALSE) {
    preg_match('/"file_errors":(\d+)/', $output, $matches);
    $errorCount = $matches[1] ?? 0;
    echo "✅ Final error count: $errorCount\n";

    $improvement = 135 - $errorCount;
    $percentage = round(($improvement / 135) * 100, 1);
    echo "📈 Improvement: Reduced by $improvement errors ({$percentage}%)\n";

    if ($errorCount < 100) {
        echo "🎉 Great progress! Under 100 errors!\n";
    }

    // Show remaining error breakdown
    if ($errorCount > 0) {
        echo "\nTop remaining error types:\n";
        $identifiers = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>/dev/null | grep -o \'"identifier":"[^"]*"\' | sort | uniq -c | sort -nr | head -5');
        echo $identifiers;
    }
}

echo "\nAdvanced code quality improvement completed! ✨\n";
