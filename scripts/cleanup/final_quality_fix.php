<?php declare(strict_types=1);

/**
 * Final Parse Error Fix
 */
echo "🔧 Final Parse Error Resolution\n";

// Fix 1: PerformanceLogger method signature
$performanceLoggerFile = 'app/Logging/PerformanceLogger.php';
if (file_exists($performanceLoggerFile)) {
    $content = file_get_contents($performanceLoggerFile);

    // Fix malformed method signature
    $content = str_replace(
        'public function addPerformanceContext(array $record): array$record)',
        'public function addPerformanceContext(array $record): array',
        $content
    );

    file_put_contents($performanceLoggerFile, $content);
    echo "  ✓ Fixed PerformanceLogger method signature\n";
}

// Fix 2: DuskTestCase malformed class structure
$duskTestFile = 'tests/DuskTestCase.php';
if (file_exists($duskTestFile)) {
    $content = file_get_contents($duskTestFile);

    // Fix the malformed class structure
    $fixedContent = "<?php

namespace Tests;

use Laravel\\Dusk\\TestCase as BaseTestCase;
use Facebook\\WebDriver\\Chrome\\ChromeOptions;
use Facebook\\WebDriver\\Remote\\RemoteWebDriver;
use Facebook\\WebDriver\\Remote\\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected static function hasHeadlessDisabled(): bool
    {
        return isset(\$_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset(\$_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the application is running in Sail.
     */
    protected static function runningInSail(): bool
    {
        return env('LARAVEL_SAIL', false);
    }

    /**
     * Prepare for Dusk test execution
     */
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance
     */
    protected function driver(): RemoteWebDriver
    {
        \$options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, \$options
            )
        );
    }
}
";

    file_put_contents($duskTestFile, $fixedContent);
    echo "  ✓ Fixed DuskTestCase class structure\n";
}

// Syntax check
echo "\n🔍 Checking syntax...\n";

$filesToCheck = [
    'app/Logging/PerformanceLogger.php',
    'tests/DuskTestCase.php',
];

foreach ($filesToCheck as $file) {
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

echo "\n📊 Final PHPStan Check...\n";

// Check final results
$output = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>&1');
if (strpos($output, '"file_errors":') !== FALSE) {
    preg_match('/"file_errors":(\d+)/', $output, $matches);
    $errorCount = $matches[1] ?? 0;
    echo "✅ Final error count: $errorCount\n";

    // Check if parse errors are eliminated
    $parseErrors = substr_count($output, 'phpstan.parse');
    if ($parseErrors === 0) {
        echo "🎉 All parse errors eliminated!\n";

        if ($errorCount > 0) {
            echo "\nRemaining error breakdown:\n";
            $identifiers = shell_exec('vendor/bin/phpstan analyse --error-format=json 2>/dev/null | grep -o \'"identifier":"[^"]*"\' | sort | uniq -c | sort -nr | head -10');
            echo $identifiers;
        } else {
            echo "🏆 PERFECT SCORE: Zero PHPStan errors!\n";
        }
    } else {
        echo "⚠️  Parse errors remaining: $parseErrors\n";
    }
} else {
    echo "❌ Could not determine final error count\n";
}

echo "\nFinal code quality fix completed! ✨\n";
