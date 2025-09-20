<?php declare(strict_types=1);

/**
 * Fix Parse Errors from Previous Array Type Fixes
 */
echo "🔧 Fixing Parse Errors Introduced by Array Type Fixes\n";

$parseErrorFixes = [
    'app/Http/Controllers/ProductionHealthController.php' => [
        // Fix malformed generic array syntax
        'array<string, mixed>'        => 'array',
        ': array<string, mixed>'      => ': array',
        'return array<string, mixed>' => 'return []',
    ],
    'app/Http/Middleware/SecureErrorMessages.php' => [
        'array<string, mixed> $messages' => 'array $messages',
        '): array<string, mixed>'        => '): array',
    ],
    'app/Logging/QueryLogger.php' => [
        'array<string, mixed> $record' => 'array $record',
        '): array<string, mixed>'      => '): array',
    ],
    'tests/validation_test_suite.php' => [
        'array<string, mixed> $' => 'array $',
        ': array<string, mixed>' => ': array',
        'array<string, mixed>('  => 'array(',
    ],
];

foreach ($parseErrorFixes as $filePath => $fixes) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;

        foreach ($fixes as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo '  ✓ Fixed parse errors in ' . basename($filePath) . "\n";
        }
    }
}

// Check for syntax errors specifically
$syntaxCheckFiles = [
    'app/Http/Controllers/ProductionHealthController.php',
    'app/Http/Middleware/SecureErrorMessages.php',
    'app/Logging/QueryLogger.php',
    'tests/validation_test_suite.php',
];

foreach ($syntaxCheckFiles as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors detected') !== FALSE) {
            echo '  ✅ Syntax OK: ' . basename($file) . "\n";
        } else {
            echo '  ❌ Syntax Error: ' . basename($file) . "\n";
            echo '      ' . trim($output) . "\n";
        }
    }
}

echo "\n📊 Checking PHPStan results...\n";

// Check final error count
$output = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>&1');
if (strpos($output, '"file_errors":') !== FALSE) {
    preg_match('/"file_errors":(\d+)/', $output, $matches);
    $errorCount = $matches[1] ?? 0;
    echo "✅ Final error count: $errorCount\n";

    // Check if there are still parse errors
    $parseErrors = substr_count($output, 'phpstan.parse');
    if ($parseErrors > 0) {
        echo "⚠️  Parse errors remaining: $parseErrors\n";
        echo "   Manual inspection may be needed for complex syntax issues.\n";
    } else {
        echo "🎉 All parse errors resolved!\n";

        // Show remaining error breakdown
        if ($errorCount > 0) {
            echo "\nRemaining error types:\n";
            $identifiers = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>/dev/null | grep -o \'"identifier":"[^"]*"\' | sort | uniq -c | sort -nr | head -10');
            echo $identifiers;
        }
    }
} else {
    echo "❌ Could not determine error count\n";
}

echo "\nParse error fix completed!\n";
