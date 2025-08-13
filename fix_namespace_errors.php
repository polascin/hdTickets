<?php

/**
 * Fix incorrect class namespaces created by automated scripts
 * 
 * This script fixes patterns like:
 * App\Http\Controllers\Admin\Illuminate\Http\JsonResponse -> \Illuminate\Http\JsonResponse
 * App\Http\Controllers\Illuminate\Contracts\View\View -> \Illuminate\Contracts\View\View
 */

// Get all PHP files that might have namespace issues
$finder = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('app'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$totalFixed = 0;
$filesFixed = 0;

foreach ($finder as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    
    $filepath = $file->getRealPath();
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Pattern 1: Fix malformed Laravel class references in return types
    $patterns = [
        // Return type patterns
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => '\\\\$1',
        '/App\\\\Models\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => '\\\\$1',
        '/App\\\\Services\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => '\\\\$1',
        '/App\\\\Mail\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => '\\\\$1',
        '/App\\\\Http\\\\Middleware\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => '\\\\$1',
        '/App\\\\Exports\\\\[^\\\\]*\\\\(App\\\\Models\\\\[^\\s{;]+)/' => '\\\\$1',
        
        // Parameter type patterns
        '/\\$[a-zA-Z_][a-zA-Z0-9_]*\\s+of\\s+method\\s+[^\\s]+\\s+has\\s+invalid\\s+type\\s+App\\\\[^\\\\]+\\\\(App\\\\Models\\\\[^\\s\\.]+)/' => '\\\\$1',
        
        // Fix specific common incorrect patterns
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\Illuminate\\\\Http\\\\JsonResponse/' => '\\\\Illuminate\\\\Http\\\\JsonResponse',
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\Illuminate\\\\Http\\\\RedirectResponse/' => '\\\\Illuminate\\\\Http\\\\RedirectResponse',
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\Illuminate\\\\Contracts\\\\View\\\\View/' => '\\\\Illuminate\\\\Contracts\\\\View\\\\View',
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\Illuminate\\\\Http\\\\Request/' => '\\\\Illuminate\\\\Http\\\\Request',
        
        // Model reference fixes
        '/App\\\\Exports\\\\App\\\\Models\\\\/' => '\\\\App\\\\Models\\\\',
        '/App\\\\Mail\\\\App\\\\Models\\\\/' => '\\\\App\\\\Models\\\\',
        '/App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\App\\\\Models\\\\/' => '\\\\App\\\\Models\\\\',
        '/App\\\\Services\\\\[^\\\\]*\\\\App\\\\Models\\\\/' => '\\\\App\\\\Models\\\\',
        '/App\\\\Http\\\\Middleware\\\\App\\\\Models\\\\/' => '\\\\App\\\\Models\\\\',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Additional specific fixes for common Laravel class references
    $specificFixes = [
        // Fix return type declarations
        ': App\\Http\\Controllers\\Admin\\Illuminate\\Http\\JsonResponse' => ': \\Illuminate\\Http\\JsonResponse',
        ': App\\Http\\Controllers\\Admin\\Illuminate\\Http\\RedirectResponse' => ': \\Illuminate\\Http\\RedirectResponse', 
        ': App\\Http\\Controllers\\Admin\\Illuminate\\Contracts\\View\\View' => ': \\Illuminate\\Contracts\\View\\View',
        ': App\\Http\\Controllers\\Illuminate\\Http\\JsonResponse' => ': \\Illuminate\\Http\\JsonResponse',
        ': App\\Http\\Controllers\\Illuminate\\Http\\RedirectResponse' => ': \\Illuminate\\Http\\RedirectResponse',
        ': App\\Http\\Controllers\\Illuminate\\Contracts\\View\\View' => ': \\Illuminate\\Contracts\\View\\View',
        
        // Fix parameter declarations
        '(App\\Http\\Controllers\\Admin\\Illuminate\\Http\\Request $' => '(\\Illuminate\\Http\\Request $',
        '(App\\Http\\Controllers\\Illuminate\\Http\\Request $' => '(\\Illuminate\\Http\\Request $',
        
        // Fix specific model references
        'App\\Exports\\App\\Models\\' => '\\App\\Models\\',
        'App\\Mail\\App\\Models\\' => '\\App\\Models\\',
        'App\\Services\\App\\Models\\' => '\\App\\Models\\',
        'App\\Http\\Controllers\\App\\Models\\' => '\\App\\Models\\',
        'App\\Http\\Middleware\\App\\Models\\' => '\\App\\Models\\',
    ];
    
    foreach ($specificFixes as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        $filesFixed++;
        $totalFixed += substr_count($originalContent, 'App\\Http\\Controllers\\') - substr_count($content, 'App\\Http\\Controllers\\');
        echo "Fixed: " . $file->getFilename() . "\n";
    }
}

echo "\nNamespace Fix Summary:\n";
echo "Files fixed: $filesFixed\n";
echo "Estimated fixes applied: $totalFixed\n";

// Also create a more targeted script for the most common issues
echo "\nRunning targeted namespace fixes...\n";

$targetedFiles = [
    'app/Http/Controllers/Admin/ReportsController.php',
    'app/Http/Controllers/Admin/ScrapingController.php',
    'app/Http/Controllers/Admin/SystemController.php',
    'app/Http/Controllers/Admin/UserManagementController.php',
    'app/Http/Controllers/AgentDashboardController.php',
    'app/Http/Controllers/Ajax/DashboardController.php',
    'app/Http/Controllers/Ajax/TicketLazyLoadController.php',
    'app/Http/Controllers/Api/EnhancedAnalyticsController.php',
    'app/Http/Controllers/Api/PerformanceMetricsController.php',
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/PaymentPlanController.php',
    'app/Http/Controllers/PurchaseDecisionController.php',
    'app/Http/Controllers/ScraperDashboardController.php',
    'app/Http/Controllers/SettingsExportController.php',
    'app/Http/Controllers/TicketApiController.php',
    'app/Http/Controllers/TicketScrapingController.php',
    'app/Http/Controllers/TicketSourceController.php',
    'app/Http/Controllers/UserActivityController.php',
    'app/Http/Controllers/UserContributionController.php',
    'app/Http/Controllers/UserFavoriteTeamController.php',
    'app/Http/Controllers/UserFavoriteVenueController.php',
    'app/Http/Controllers/UserPreferencesController.php',
    'app/Http/Controllers/UserPricePreferenceController.php',
];

$targetedFixed = 0;
foreach ($targetedFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Very specific pattern matching for the most common issues
    $targeted_patterns = [
        // Method return type fixes
        '/:\s*App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => ': \\\\$1',
        '/:\s*App\\\\Models\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => ': \\\\$1',
        '/:\s*App\\\\Services\\\\[^\\\\]*\\\\(Illuminate\\\\[^\\s{;]+)/' => ': \\\\$1',
        
        // Parameter type fixes in method signatures
        '/\\(([^)]*?)App\\\\Http\\\\Controllers\\\\[^\\\\]*\\\\(Illuminate\\\\Http\\\\Request)\\s+\\$/' => '($1\\\\$2 $',
        '/\\(([^)]*?)App\\\\[^\\\\]*\\\\(App\\\\Models\\\\[^\\s]+)\\s+\\$/' => '($1\\\\$2 $',
    ];
    
    foreach ($targeted_patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $targetedFixed++;
        echo "Targeted fix applied to: " . basename($file) . "\n";
    }
}

echo "Targeted fixes applied to $targetedFixed files.\n";
echo "\nNamespace cleanup completed!\n";
