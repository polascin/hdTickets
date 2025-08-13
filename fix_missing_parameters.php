<?php

/**
 * Fix missing method parameters that cause "undefined variable" errors
 * 
 * This script identifies methods that use variables like $request, $paymentPlan, etc.
 * but don't have them as parameters, and adds the missing parameters.
 */

$files = [
    'app/Http/Controllers/PaymentPlanController.php',
    'app/Http/Controllers/PurchaseDecisionController.php', 
    'app/Http/Controllers/TicketScrapingController.php',
    'app/Http/Controllers/TicketSourceController.php',
    'app/Http/Controllers/UserContributionController.php',
    'app/Http/Controllers/UserPreferencesController.php',
];

$totalFixed = 0;
$filesFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern 1: Methods that use $request but don't have Request parameter
    $content = preg_replace(
        '/public function (store|update|destroy)\(\s*\): ([^{]+)\s*\{\s*([^}]*\$request)/',
        'public function $1(\\Illuminate\\Http\\Request $request): $2' . "\n    {\n        $3",
        $content
    );
    
    // Pattern 2: Methods with model parameters missing
    $content = preg_replace(
        '/public function (show|edit|update|destroy)\(\s*\): ([^{]+)\s*\{\s*([^}]*\$(paymentPlan|ticketSource|purchaseQueue))/',
        'public function $1($$$4): $2' . "\n    {\n        $3",
        $content
    );
    
    // Pattern 3: More specific fixes for common patterns
    $specificPatterns = [
        // Fix store methods that use $request
        '/public function store\(\): ([^{]+)\s*\{\s*\$request->validate/' => 'public function store(\\Illuminate\\Http\\Request $request): $1' . "\n    {\n        \$request->validate",
        
        // Fix update methods
        '/public function update\(\): ([^{]+)\s*\{\s*\$request->validate/' => 'public function update(\\Illuminate\\Http\\Request $request): $1' . "\n    {\n        \$request->validate",
        
        // Fix show methods that use model variables
        '/public function show\(\): ([^{]+)\s*\{\s*([^}]*)\$paymentPlan/' => 'public function show($paymentPlan): $1' . "\n    {\n        $2\$paymentPlan",
        '/public function show\(\): ([^{]+)\s*\{\s*([^}]*)\$ticketSource/' => 'public function show($ticketSource): $1' . "\n    {\n        $2\$ticketSource",
        '/public function show\(\): ([^{]+)\s*\{\s*([^}]*)\$purchaseQueue/' => 'public function show($purchaseQueue): $1' . "\n    {\n        $2\$purchaseQueue",
        '/public function show\(\): ([^{]+)\s*\{\s*([^}]*)\$ticket/' => 'public function show($ticket): $1' . "\n    {\n        $2\$ticket",
        '/public function show\(\): ([^{]+)\s*\{\s*([^}]*)\$alert/' => 'public function show($alert): $1' . "\n    {\n        $2\$alert",
        
        // Fix edit methods
        '/public function edit\(\): ([^{]+)\s*\{\s*([^}]*)\$paymentPlan/' => 'public function edit($paymentPlan): $1' . "\n    {\n        $2\$paymentPlan",
        '/public function edit\(\): ([^{]+)\s*\{\s*([^}]*)\$ticketSource/' => 'public function edit($ticketSource): $1' . "\n    {\n        $2\$ticketSource",
        
        // Fix destroy methods
        '/public function destroy\(\): ([^{]+)\s*\{\s*([^}]*)\$paymentPlan/' => 'public function destroy($paymentPlan): $1' . "\n    {\n        $2\$paymentPlan",
        '/public function destroy\(\): ([^{]+)\s*\{\s*([^}]*)\$ticketSource/' => 'public function destroy($ticketSource): $1' . "\n    {\n        $2\$ticketSource",
    ];
    
    foreach ($specificPatterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $filesFixed++;
        echo "Fixed parameters in: " . basename($file) . "\n";
    } else {
        echo "No changes needed: " . basename($file) . "\n";
    }
}

echo "\nParameter Fix Summary:\n";
echo "Files processed: " . count($files) . "\n";
echo "Files fixed: $filesFixed\n";

// Now let's handle a more specific approach for files with many undefined variables
echo "\nApplying targeted fixes to specific controller methods...\n";

// PaymentPlanController specific fixes
$paymentPlanFile = 'app/Http/Controllers/PaymentPlanController.php';
if (file_exists($paymentPlanFile)) {
    $content = file_get_contents($paymentPlanFile);
    $originalContent = $content;
    
    // Add Request parameter to store method
    $content = preg_replace(
        '/public function store\(\): \\\\Illuminate\\\\Http\\\\RedirectResponse\s*\{\s*\$request->validate/',
        'public function store(Request $request): \\Illuminate\\Http\\RedirectResponse' . "\n    {\n        \$request->validate",
        $content
    );
    
    // Add parameters to show method
    $content = preg_replace(
        '/public function show\(\): \\\\Illuminate\\\\Contracts\\\\View\\\\View\s*\{\s*return view/',
        'public function show($paymentPlan): \\Illuminate\\Contracts\\View\\View' . "\n    {\n        return view",
        $content
    );
    
    // Add parameters to edit method  
    $content = preg_replace(
        '/public function edit\(\): \\\\Illuminate\\\\Contracts\\\\View\\\\View\s*\{\s*return view/',
        'public function edit($paymentPlan): \\Illuminate\\Contracts\\View\\View' . "\n    {\n        return view",
        $content
    );
    
    // Add parameters to update method
    $content = preg_replace(
        '/public function update\(\): \\\\Illuminate\\\\Http\\\\RedirectResponse\s*\{\s*\$request->validate/',
        'public function update(Request $request, $paymentPlan): \\Illuminate\\Http\\RedirectResponse' . "\n    {\n        \$request->validate",
        $content
    );
    
    // Add parameters to destroy method
    $content = preg_replace(
        '/public function destroy\(\): \\\\Illuminate\\\\Http\\\\RedirectResponse\s*\{\s*try\s*\{\s*\$paymentPlan/',
        'public function destroy($paymentPlan): \\Illuminate\\Http\\RedirectResponse' . "\n    {\n        try {\n            \$paymentPlan",
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($paymentPlanFile, $content);
        echo "Applied specific fixes to PaymentPlanController.php\n";
    }
}

// Similar fixes for other controllers would go here...
echo "\nTargeted parameter fixes completed!\n";
