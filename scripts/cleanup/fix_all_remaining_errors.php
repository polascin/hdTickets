<?php

/**
 * Comprehensive fix for all remaining PHPStan parse errors
 */

// Get current parse errors
$output = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>&1');

if (strpos($output, 'phpstan.parse') !== false) {
    echo "Fixing remaining parse errors...\n";
    
    // Define specific fixes for known problematic files
    $specificFileFixes = [
        'app/Http/Controllers/AgentDashboardController.php' => [
            // Fix malformed method signatures
            'public function getAgentMetrics(\App\Models\User' => 'public function getAgentMetrics($user',
            'public function getTicketMonitoringData(\App\Models\User' => 'public function getTicketMonitoringData($user',  
            'public function getPurchaseQueueData(\App\Models\User' => 'public function getPurchaseQueueData($user',
            'public function getAlertData(\App\Models\User' => 'public function getAlertData($user',
            'public function getRecentActivity(\App\Models\User' => 'public function getRecentActivity($user',
            'public function getPerformanceMetrics(\App\Models\User' => 'public function getPerformanceMetrics($user',
            // Fix incomplete method signatures
            '{' => ': array {',
        ],
        
        'app/Http/Controllers/Api/StubHubController.php' => [
            // Fix incomplete try-catch blocks
            'try {' => 'try {',
            'catch (\Exception $e) {' => '} catch (\Exception $e) {',
        ],
        
        'app/Http/Controllers/Api/TickPickController.php' => [
            // Fix incomplete try-catch blocks  
            'try {' => 'try {',
            'catch (\Exception $e) {' => '} catch (\Exception $e) {',
        ],
        
        'app/Http/Controllers/Api/ViagogoController.php' => [
            // Fix incomplete try-catch blocks
            'try {' => 'try {',
            'catch (\Exception $e) {' => '} catch (\Exception $e) {',
        ],
        
        'app/Providers/RefactoredAppServiceProvider.php' => [
            // Fix namespace issues
            '\\App\\Models\\\\' => '\\App\\Models\\',
            'if (' => 'if (',
        ],
        
        'tests/Unit/Services/ScrapingServiceTest.php' => [
            // Fix property declarations
            'protected $$scrapingService' => 'protected $scrapingService',
        ],
    ];
    
    $filesFixed = 0;
    
    foreach ($specificFileFixes as $filepath => $fixes) {
        if (!file_exists($filepath)) {
            continue;
        }
        
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        // Apply specific fixes
        foreach ($fixes as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Apply general fixes for common patterns
        $generalFixes = [
            // Fix doubled dollar signs
            '/\$\$([a-zA-Z_][a-zA-Z0-9_]*)/' => '$$$1',
            
            // Fix malformed namespaces  
            '/\\\\\\\\([A-Z][a-zA-Z0-9_\\\\]*)/' => '\\$1',
            
            // Fix incomplete method signatures
            '/public function ([a-zA-Z_][a-zA-Z0-9_]*)\([^)]*\)\s*\{/' => 'public function $1() {',
            
            // Fix malformed type declarations
            '/: \\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/' => ': $$$1',
        ];
        
        foreach ($generalFixes as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filepath, $content);
            $filesFixed++;
            echo "✓ Fixed: " . basename($filepath) . "\n";
        }
    }
    
    echo "\nApplying comprehensive fixes to all remaining files...\n";
    
    // Get list of files with parse errors
    preg_match_all('/"([^"]*\.php)":\s*\{\s*"errors"/', $output, $matches);
    $errorFiles = array_unique($matches[1]);
    
    foreach ($errorFiles as $filepath) {
        if (!file_exists($filepath) || in_array($filepath, array_keys($specificFileFixes))) {
            continue; // Skip already processed files
        }
        
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        // Apply comprehensive pattern fixes
        $comprehensiveFixes = [
            // Fix method parameter issues
            '/public function ([a-zA-Z_][a-zA-Z0-9_]*)\(([^)]*?)\$\$([a-zA-Z_][a-zA-Z0-9_]*)\)/' => 'public function $1($2$$$3)',
            
            // Fix property declarations
            '/protected \$\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/' => 'protected $$$1 =',
            '/private \$\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/' => 'private $$$1 =',
            
            // Fix namespace issues
            '/\\\\\\\\\\\\([A-Z][a-zA-Z0-9_\\\\]*)/' => '\\$1',
            
            // Fix type declarations
            '/: \\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/' => ': $$$1',
            '/\(\\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/' => '($$$1',
            
            // Fix malformed try-catch blocks
            '/try\s*\{\s*([^}]*)\}\s*catch\s*\(/' => "try {\n            $1\n        } catch (",
        ];
        
        foreach ($comprehensiveFixes as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filepath, $content);
            $filesFixed++;
            echo "✓ General fix: " . basename($filepath) . "\n";
        }
    }
    
    echo "\nFixed $filesFixed files. Running PHPStan to check results...\n";
    
    // Check current status
    $checkOutput = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>&1');
    $parseErrorCount = substr_count($checkOutput, 'phpstan.parse');
    
    echo "Parse errors remaining: $parseErrorCount\n";
    
    if ($parseErrorCount == 0) {
        echo "🎉 All parse errors resolved! Checking regular errors...\n";
        
        if (strpos($checkOutput, '"file_errors":') !== false) {
            preg_match('/"file_errors":(\d+)/', $checkOutput, $matches);
            $regularErrors = $matches[1] ?? 0;
            echo "✅ Regular PHPStan errors: $regularErrors\n";
            
            if ($regularErrors > 0) {
                echo "\nTop error types:\n";
                $identifiers = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>/dev/null | grep -o \'"identifier":"[^"]*"\' | sort | uniq -c | sort -nr | head -10');
                echo $identifiers;
            }
        }
    } else {
        echo "❌ Some parse errors remain. Manual fixes may be needed.\n";
    }
    
} else {
    echo "✅ No parse errors found!\n";
    
    // Get regular error summary
    if (strpos($output, '"file_errors":') !== false) {
        preg_match('/"file_errors":(\d+)/', $output, $matches);
        $regularErrors = $matches[1] ?? 0;
        echo "📊 Regular PHPStan errors: $regularErrors\n";
        
        if ($regularErrors > 0) {
            echo "\nError breakdown:\n";
            $identifiers = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>/dev/null | grep -o \'"identifier":"[^"]*"\' | sort | uniq -c | sort -nr | head -10');
            echo $identifiers;
        }
    }
}

echo "\nParse error fix completed!\n";
