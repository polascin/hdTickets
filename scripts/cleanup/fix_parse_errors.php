<?php

/**
 * Fix syntax errors introduced by our automated scripts
 */

$files = [
    'app/Exports/ResponseTimeExport.php',
    'app/Http/Controllers/Admin/RealTimeDashboardController.php',
    'app/Http/Controllers/AgentDashboardController.php',
    'app/Http/Controllers/Api/StubHubController.php',
    'app/Http/Controllers/Api/TickPickController.php',
    'app/Http/Controllers/Api/ViagogoController.php',
    'app/Http/Controllers/PaymentPlanController.php',
    'app/Http/Controllers/PurchaseDecisionController.php',
    'app/Http/Controllers/TicketSourceController.php',
    'app/Mail/PriceChangeNotification.php',
    'app/Mail/TicketAvailabilityNotification.php',
    'app/Providers/RefactoredAppServiceProvider.php',
    'app/Services/Scraping/BaseScraperPlugin.php',
    'tests/Unit/Services/NotificationServiceTest.php',
];

$filesFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix 1: Fix malformed parameter declarations like $$variable
    $content = preg_replace('/public function ([a-zA-Z_][a-zA-Z0-9_]*)\(\$\$([a-zA-Z_][a-zA-Z0-9_]*)\)/', 'public function $1($$2)', $content);
    
    // Fix 2: Fix malformed method parameters with extra dollar signs
    $content = preg_replace('/\(\$\$([a-zA-Z_][a-zA-Z0-9_]*)\):/', '($$1):', $content);
    
    // Fix 3: Fix broken type declarations in parameter lists
    $content = preg_replace('/\(([^)]*?)\$\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\):/', '($1$$2):', $content);
    
    // Fix 4: Fix type declarations that got corrupted
    $content = preg_replace('/: \\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/', ': \\App\\Models\\$1', $content);
    
    // Fix 5: Fix properties with doubled dollar signs
    $content = preg_replace('/protected \$\$([a-zA-Z_][a-zA-Z0-9_]*) =/', 'protected $$1 =', $content);
    $content = preg_replace('/private \$\$([a-zA-Z_][a-zA-Z0-9_]*) =/', 'private $$1 =', $content);
    
    // Fix 6: Fix malformed namespace separators
    $content = preg_replace('/\\\\\\\\\\\\([A-Z][a-zA-Z0-9_\\\\]*)/', '\\\\$1', $content);
    
    // Fix 7: Fix specific parameter type issues
    $specificFixes = [
        // Fix doubled parameter names
        'public function show($$paymentPlan):' => 'public function show($paymentPlan):',
        'public function edit($$paymentPlan):' => 'public function edit($paymentPlan):',
        'public function update(Request $request, $$paymentPlan):' => 'public function update(Request $request, $paymentPlan):',
        'public function destroy($$paymentPlan):' => 'public function destroy($paymentPlan):',
        'public function show($$purchaseQueue):' => 'public function show($purchaseQueue):',
        'public function show($$ticketSource):' => 'public function show($ticketSource):',
        'public function edit($$ticketSource):' => 'public function edit($ticketSource):',
        'public function update(Request $request, $$ticketSource):' => 'public function update(Request $request, $ticketSource):',
        'public function destroy($$ticketSource):' => 'public function destroy($ticketSource):',
        
        // Fix property declarations with extra dollar signs
        'protected $$antiDetection = null;' => 'protected $antiDetection = null;',
        'protected $$highDemandScraper = null;' => 'protected $highDemandScraper = null;',
        'protected $$pluginName = \'\';' => 'protected $pluginName = \'\';',
        'protected $$platform = \'\';' => 'protected $platform = \'\';',
        'protected $$description = \'\';' => 'protected $description = \'\';',
        'protected $$baseUrl = \'\';' => 'protected $baseUrl = \'\';',
        'protected $$venue = \'\';' => 'protected $venue = \'\';',
        'protected $$user = null;' => 'protected $user = null;',
        'protected $$admin = null;' => 'protected $admin = null;',
        'protected $$notificationService = null;' => 'protected $notificationService = null;',
        'protected $$scrapingService = null;' => 'protected $scrapingService = null;',
        
        // Fix broken namespace references
        ': \\\\App\\\\Models\\\\\\\\' => ': \\App\\Models\\',
        '(\\\\App\\\\Models\\\\\\\\' => '(\\App\\Models\\',
        
        // Fix malformed type hints in parameters
        'has invalid type \\\\App\\\\Models\\\\\\\\' => 'has invalid type \\App\\Models\\',
    ];
    
    foreach ($specificFixes as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    // Fix incomplete try-catch blocks
    if (strpos($content, 'Cannot use try without catch or finally') !== false || 
        preg_match('/try\s*\{\s*[^}]*\}\s*$/', $content)) {
        // Add a basic catch block to incomplete try statements
        $content = preg_replace(
            '/try\s*\{\s*([^}]*)\}\s*([^c]|$)/',
            "try {\n                $1\n            } catch (\\Exception \$e) {\n                // Handle exception\n                throw \$e;\n            }$2",
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $filesFixed++;
        echo "Fixed parse errors in: " . basename($file) . "\n";
    } else {
        echo "No parse errors found in: " . basename($file) . "\n";
    }
}

echo "\nParse Error Fix Summary:\n";
echo "Files processed: " . count($files) . "\n";
echo "Files fixed: $filesFixed\n";

echo "\nRunning specific file fixes...\n";

// Fix specific file issues individually
$specificFileFixes = [
    'app/Http/Controllers/PaymentPlanController.php' => [
        'public function show($$paymentPlan): \\\\Illuminate\\\\Contracts\\\\View\\\\View' => 'public function show($paymentPlan): \\Illuminate\\Contracts\\View\\View',
        'public function destroy($$paymentPlan): \\\\Illuminate\\\\Http\\\\RedirectResponse' => 'public function destroy($paymentPlan): \\Illuminate\\Http\\RedirectResponse',
    ],
    'app/Services/Scraping/BaseScraperPlugin.php' => [
        'protected $$antiDetection = null;' => 'protected $antiDetection = null;',
        'protected $$highDemandScraper = null;' => 'protected $highDemandScraper = null;',
    ]
];

$specificFixed = 0;
foreach ($specificFileFixes as $filename => $fixes) {
    if (!file_exists($filename)) {
        continue;
    }
    
    $content = file_get_contents($filename);
    $originalContent = $content;
    
    foreach ($fixes as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filename, $content);
        $specificFixed++;
        echo "Applied specific fixes to: " . basename($filename) . "\n";
    }
}

echo "Specific file fixes applied: $specificFixed\n";
echo "\nAll parse error fixes completed!\n";
