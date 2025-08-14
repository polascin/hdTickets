<?php
/**
 * Critical PHPStan Issues Fixer
 * Targets the most important issues first
 */

// 1. Fix missing property types
function addPropertyTypes($filePath, $content) {
    // Add property types for known patterns
    $content = preg_replace(
        '/^(\s*)(protected|private|public)\s+(\$[a-zA-Z0-9_]+);$/m',
        '$1$2 mixed $3;',
        $content
    );
    
    return $content;
}

// 2. Fix method return types
function addReturnTypes($filePath, $content) {
    // Add return types for common controller methods
    $patterns = [
        // Controllers
        '/public function index\(\)(\s*{)/' => 'public function index(): \\Illuminate\\Contracts\\View\\View$1',
        '/public function store\(([^)]*)\)(\s*{)/' => 'public function store($1): \\Illuminate\\Http\\RedirectResponse$2',
        '/public function update\(([^)]*)\)(\s*{)/' => 'public function update($1): \\Illuminate\\Http\\RedirectResponse$2',
        '/public function destroy\(([^)]*)\)(\s*{)/' => 'public function destroy($1): \\Illuminate\\Http\\RedirectResponse$2',
        '/public function show\(([^)]*)\)(\s*{)/' => 'public function show($1): \\Illuminate\\Contracts\\View\\View$2',
        '/public function edit\(([^)]*)\)(\s*{)/' => 'public function edit($1): \\Illuminate\\Contracts\\View\\View$2',
        '/public function create\(\)(\s*{)/' => 'public function create(): \\Illuminate\\Contracts\\View\\View$1',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

// 3. Fix missing parameter types
function addParameterTypes($filePath, $content) {
    // Common parameter patterns
    $patterns = [
        '/function\s+([a-zA-Z0-9_]+)\(\s*\$request([,)])/' => 'function $1(\\Illuminate\\Http\\Request $request$2',
        '/function\s+([a-zA-Z0-9_]+)\(\s*\$user([,)])/' => 'function $1(\\App\\Models\\User $user$2',
        '/function\s+([a-zA-Z0-9_]+)\(\s*\$id([,)])/' => 'function $1(int $id$2',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    return $content;
}

// 4. Fix PHPDoc array types
function fixArrayTypes($content) {
    // Fix generic array types
    $content = preg_replace(
        '/\*\s*@return\s+array\s*$/m',
        '* @return array<string, mixed>',
        $content
    );
    
    $content = preg_replace(
        '/\*\s*@param\s+array\s+(\$[a-zA-Z0-9_]+)/',
        '* @param array<string, mixed> $1',
        $content
    );
    
    return $content;
}

// 5. Fix view() function calls
function fixViewCalls($content) {
    // Replace view() calls with proper view-string type
    $content = preg_replace(
        '/return view\(([^)]+)\)/',
        'return view($1)',
        $content
    );
    
    return $content;
}

// Process specific high-priority files
$highPriorityFiles = [
    'app/Http/Controllers/Admin/DashboardController.php',
    'app/Http/Controllers/Admin/ReportsController.php', 
    'app/Http/Controllers/Admin/UserManagementController.php',
    'app/Http/Controllers/Admin/TicketManagementController.php',
    'app/Http/Controllers/Admin/ScrapingController.php',
    'app/Http/Controllers/Admin/SystemController.php',
    'app/Http/Controllers/AgentDashboardController.php',
    'app/Http/Controllers/Api/DashboardController.php',
    'app/Http/Controllers/Api/AnalyticsController.php',
];

$processedFiles = 0;

foreach ($highPriorityFiles as $file) {
    $fullPath = "/var/www/hdtickets/{$file}";
    
    if (!file_exists($fullPath)) {
        continue;
    }
    
    $content = file_get_contents($fullPath);
    $originalContent = $content;
    
    // Apply fixes
    $content = addPropertyTypes($fullPath, $content);
    $content = addReturnTypes($fullPath, $content);
    $content = addParameterTypes($fullPath, $content);
    $content = fixArrayTypes($content);
    $content = fixViewCalls($content);
    
    if ($content !== $originalContent) {
        file_put_contents($fullPath, $content);
        echo "Fixed: {$file}\n";
        $processedFiles++;
    }
}

echo "\nHigh-priority fixes applied to {$processedFiles} files.\n";
