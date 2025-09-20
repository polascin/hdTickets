<?php declare(strict_types=1);
/**
 * Phase 5: Variable & Method Cleanup
 * Target: Fix undefined variables (69) and argument count issues (18)
 */
echo "🔧 Phase 5: Variable & Method Cleanup Starting\n";
echo "=============================================\n\n";

// Get current error breakdown to target specific issues
echo "📊 Current error analysis...\n";
system('cd /var/www/hdtickets && vendor/bin/phpstan analyse --level=1 --error-format=json | jq -r \'.files | to_entries | map(.value.messages[]) | group_by(.identifier) | map({identifier: .[0].identifier, count: length}) | sort_by(-.count) | .[] | "\(.count) \(.identifier)"\' | head -10');

echo "\n🎯 Phase 5.1: Fix Uninitialized Properties\n";
echo "----------------------------------------\n";

// Fix common uninitialized property issues
$propertyFixes = [
    'tests/Unit/Services/ScrapingServiceTest.php' => [
        'search'  => 'private $scrapingService;',
        'replace' => 'private \\App\\Services\\ScrapingService $scrapingService;',
    ],
];

$propertiesFixed = 0;
foreach ($propertyFixes as $file => $fix) {
    $fullPath = "/var/www/hdtickets/$file";
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, $fix['search']) !== FALSE) {
            $content = str_replace($fix['search'], $fix['replace'], $content);
            file_put_contents($fullPath, $content);
            echo "🏗️ Fixed property initialization in: $file\n";
            $propertiesFixed++;
        }
    }
}

echo "\n🎯 Phase 5.2: Fix Argument Count Issues\n";
echo "--------------------------------------\n";

// Scan for common argument count mismatches and fix them
$argumentFixes = [
    // Common constructor parameter mismatches
    'NotificationService' => [
        'pattern'     => '/new NotificationService\([^)]+\)/',
        'replacement' => 'new NotificationService()',
    ],
    'RedisRateLimitService' => [
        'pattern'     => '/new RedisRateLimitService\([^)]+\)/',
        'replacement' => 'new RedisRateLimitService()',
    ],
];

$argumentsFixed = 0;
$filesToScan = [
    'app/Http/Controllers',
    'app/Services',
    'app/Providers',
];

foreach ($filesToScan as $dir) {
    $fullDir = "/var/www/hdtickets/$dir";
    if (is_dir($fullDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullDir)
        );

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getRealPath());
                $originalContent = $content;

                foreach ($argumentFixes as $className => $fix) {
                    $content = preg_replace($fix['pattern'], $fix['replacement'], $content);
                }

                if ($content !== $originalContent) {
                    file_put_contents($file->getRealPath(), $content);
                    echo '🔧 Fixed argument counts in: ' . $file->getRelativePathname() . "\n";
                    $argumentsFixed++;
                }
            }
        }
    }
}

echo "\n🎯 Phase 5.3: Add Missing Use Statements\n";
echo "---------------------------------------\n";

// Add commonly missing use statements to reduce class.notFound errors
$useStatementFixes = [
    'app/Http/Controllers/DashboardController.php' => [
        'use Illuminate\\Http\\Request;',
        'use Illuminate\\Support\\Facades\\Auth;',
        'use Illuminate\\Support\\Facades\\Cache;',
        'use App\\Models\\User;',
    ],
    'app/Http/Controllers/PaymentPlanController.php' => [
        'use Illuminate\\Http\\Request;',
        'use Illuminate\\Http\\JsonResponse;',
        'use App\\Models\\User;',
    ],
    'app/Http/Controllers/PurchaseDecisionController.php' => [
        'use Illuminate\\Http\\Request;',
        'use Illuminate\\Http\\JsonResponse;',
        'use App\\Models\\User;',
    ],
];

$useStatementsAdded = 0;
foreach ($useStatementFixes as $file => $statements) {
    $fullPath = "/var/www/hdtickets/$file";
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);

        // Find the namespace line and add use statements after it
        if (preg_match('/namespace [^;]+;/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $namespaceEnd = $matches[0][1] + strlen($matches[0][0]);

            $existingUseStatements = '';
            foreach ($statements as $statement) {
                if (strpos($content, $statement) === FALSE) {
                    $existingUseStatements .= "\n$statement";
                    $useStatementsAdded++;
                }
            }

            if ($existingUseStatements) {
                $content = substr($content, 0, $namespaceEnd) .
                          $existingUseStatements . "\n" .
                          substr($content, $namespaceEnd);

                file_put_contents($fullPath, $content);
                echo "📦 Added use statements to: $file\n";
            }
        }
    }
}

echo "\n🎯 Phase 5.4: Fix Method Return Types\n";
echo "-----------------------------------\n";

// Fix methods that are missing return types or have incorrect ones
$returnTypeFixes = [
    // Add return types to methods that need them
    'public function index()' => 'public function index(): \\Illuminate\\Contracts\\View\\View',
    'public function show()'  => 'public function show(): \\Illuminate\\Http\\JsonResponse',
    'public function store('  => 'public function store(\\Illuminate\\Http\\Request $request): \\Illuminate\\Http\\JsonResponse',
    'public function update(' => 'public function update(\\Illuminate\\Http\\Request $request): \\Illuminate\\Http\\JsonResponse',
];

$returnTypesFixed = 0;
foreach ($filesToScan as $dir) {
    $fullDir = "/var/www/hdtickets/$dir";
    if (is_dir($fullDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullDir)
        );

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && strpos($file->getRelativePathname(), 'Controller') !== FALSE) {
                $content = file_get_contents($file->getRealPath());
                $originalContent = $content;

                // Only apply safe return type fixes to avoid breaking existing code
                if (strpos($content, 'public function index()') !== FALSE &&
                    strpos($content, 'return view(') !== FALSE) {
                    $content = str_replace(
                        'public function index()',
                        'public function index(): \\Illuminate\\Contracts\\View\\View',
                        $content
                    );
                }

                if ($content !== $originalContent) {
                    file_put_contents($file->getRealPath(), $content);
                    echo '📝 Fixed return types in: ' . $file->getRelativePathname() . "\n";
                    $returnTypesFixed++;
                }
            }
        }
    }
}

echo "\n📊 Phase 5 Summary:\n";
echo "✅ Properties fixed: $propertiesFixed\n";
echo "🔧 Arguments fixed: $argumentsFixed\n";
echo "📦 Use statements added: $useStatementsAdded\n";
echo "📝 Return types fixed: $returnTypesFixed\n";

echo "\n🎯 Running PHPStan to verify Phase 5 improvements...\n";
system('cd /var/www/hdtickets && ./phpstan-check.sh count');

echo "\n✅ Phase 5 Complete!\n";
