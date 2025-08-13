<?php

/**
 * Final comprehensive fix for remaining parse errors
 */

// Get the current parse errors from PHPStan
$output = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>&1');

if (strpos($output, 'phpstan.parse') !== false) {
    echo "Found parse errors, attempting to fix...\n";
    
    // Extract file names from the JSON output
    preg_match_all('/"([^"]*\.php)":\s*\{\s*"errors"/', $output, $matches);
    $errorFiles = array_unique($matches[1]);
    
    foreach ($errorFiles as $filepath) {
        // Skip if file doesn't exist
        if (!file_exists($filepath)) {
            continue;
        }
        
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        echo "Fixing: $filepath\n";
        
        // Comprehensive fixes for common parse issues
        $fixes = [
            // Fix doubled dollar signs in parameters
            '/\(\$\$([a-zA-Z_][a-zA-Z0-9_]*)\)/' => '($$1)',
            '/public function ([a-zA-Z_][a-zA-Z0-9_]*)\(\$\$([a-zA-Z_][a-zA-Z0-9_]*)\):/' => 'public function $1($$2):',
            
            // Fix malformed type declarations
            '/: \\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/' => ': \\App\\Models\\$1',
            '/\(\\\\App\\\\Models\\\\\\\\([A-Z][a-zA-Z0-9_]*)/' => '(\\App\\Models\\$1',
            
            // Fix property declarations with extra dollar signs
            '/protected \$\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/' => 'protected $$1 =',
            '/private \$\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=/' => 'private $$1 =',
            
            // Fix malformed namespace separators
            '/\\\\\\\\\\\\([A-Z][a-zA-Z0-9_\\\\]*)/' => '\\$1',
            
            // Fix incomplete try-catch blocks
            '/try\s*\{\s*([^}]*)\}\s*([^c]|$)/' => "try {\n                $1\n            } catch (\\Exception \$e) {\n                throw \$e;\n            }$2",
        ];
        
        foreach ($fixes as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // File-specific fixes based on common patterns
        $basename = basename($filepath);
        
        // Agent dashboard controller specific fixes
        if (strpos($basename, 'AgentDashboardController') !== false) {
            $content = preg_replace('/public function ([a-zA-Z_][a-zA-Z0-9_]*)\(\s*\{/', 'public function $1() {', $content);
        }
        
        // Controller parameter fixes
        if (strpos($filepath, 'Controllers') !== false) {
            // Fix missing Request parameters
            $content = preg_replace('/public function (store|update)\(\): ([^{]+)\s*\{\s*\$request/', 'public function $1(\\Illuminate\\Http\\Request $request): $2 { $request', $content);
            
            // Fix show/edit/destroy methods with model parameters
            $content = preg_replace('/public function (show|edit|destroy)\(\): ([^{]+)\s*\{\s*([^}]*\$(paymentPlan|ticketSource|purchaseQueue|ticket|alert))/', 'public function $1($$4): $2 { $3$$4', $content);
        }
        
        // Service class fixes
        if (strpos($filepath, 'Services') !== false || strpos($filepath, 'tests') !== false) {
            // Fix property initialization
            $content = preg_replace('/protected \$([a-zA-Z_][a-zA-Z0-9_]*);(\s*\/)/', 'protected $$1 = null;$2', $content);
        }
        
        // Save if changed
        if ($content !== $originalContent) {
            file_put_contents($filepath, $content);
            echo "  ✓ Fixed parse errors in $basename\n";
        } else {
            echo "  - No changes needed for $basename\n";
        }
    }
    
    echo "\nRunning PHPStan again to check for remaining errors...\n";
    
    // Check if parse errors are resolved
    $checkOutput = shell_exec('vendor/bin/phpstan analyse --level=1 --error-format=json 2>&1');
    $parseErrorCount = substr_count($checkOutput, 'phpstan.parse');
    
    echo "Remaining parse errors: $parseErrorCount\n";
    
    if ($parseErrorCount > 0) {
        echo "Some parse errors remain. Manual inspection may be needed.\n";
    } else {
        echo "✅ All parse errors have been resolved!\n";
        
        // Now get the regular error count
        if (strpos($checkOutput, '"file_errors":') !== false) {
            preg_match('/"file_errors":(\d+)/', $checkOutput, $errorMatches);
            $errorCount = $errorMatches[1] ?? 'unknown';
            echo "📊 Regular PHPStan errors remaining: $errorCount\n";
        }
    }
    
} else {
    echo "✅ No parse errors found!\n";
    
    // Show regular error breakdown
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        if (strpos($line, '"identifier"') !== false) {
            echo $line . "\n";
        }
    }
}

echo "\nParse error fix completed!\n";
