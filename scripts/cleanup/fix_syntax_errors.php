<?php declare(strict_types=1);

/**
 * Fix syntax errors created by the automated return type script
 *
 * This script fixes:
 * 1. Generic array return types in method signatures (array<string, mixed>) -> array
 * 2. Missing curly braces after return types
 * 3. Method signature formatting issues
 */
$files = [
    'app/Http/Controllers/Admin/ReportsController.php',
    'app/Http/Controllers/Admin/ScrapingController.php',
    'app/Http/Controllers/AgentDashboardController.php',
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/PurchaseDecisionController.php',
    'app/Http/Controllers/ScraperDashboardController.php',
    'app/Http/Controllers/UserActivityController.php',
    'app/Models/PurchaseAttempt.php',
    'app/Models/User.php',
];

$totalFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";

        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;

    // Pattern 1: Fix generic array return types in method signatures
    // Example: function test(): array<string, mixed>{ -> function test(): array\n    {
    $content = preg_replace(
        '/(\s+(?:public|private|protected)\s+function\s+[^:]+):\s*array<[^>]+>\s*\{/',
        '$1: array' . "\n    {",
        $content
    );

    // Pattern 2: Fix missing space and newline before opening brace
    // Example: function test(): Type{ -> function test(): Type\n    {
    $content = preg_replace(
        '/(\s+(?:public|private|protected)\s+function\s+[^{]+):\s*([^\s{]+)\{/',
        '$1: $2' . "\n    {",
        $content
    );

    // Pattern 3: Fix missing space and newline in methods without return types
    // Example: function test(){ -> function test()\n    {
    $content = preg_replace(
        '/(\s+(?:public|private|protected)\s+function\s+[^{(]+\([^)]*\))\{/',
        '$1' . "\n    {",
        $content
    );

    // Pattern 4: Fix incorrect return type with colon formatting
    // Example: ): array<mixed, mixed>{  ->  ): array\n    {
    $content = preg_replace(
        '/(\):\s*)array<[^>]+>\s*\{/',
        '$1array' . "\n    {",
        $content
    );

    // Pattern 5: Fix malformed generic types that slipped through
    $content = preg_replace(
        '/:\s*([a-zA-Z\\\\]+)<[^>]+>\s*\{/',
        ': $1' . "\n    {",
        $content
    );

    // Pattern 6: Fix broken return type declarations that split incorrectly
    // Example: function test(): \Illuminate\Http\JsonResponse{ -> function test(): \Illuminate\Http\JsonResponse\n    {
    $content = preg_replace(
        '/(\s+function\s+[^:]+:\s*\\\\[^{]+)\{/',
        '$1' . "\n    {",
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $totalFixed++;
        echo "Fixed: $file\n";
    } else {
        echo "No changes needed: $file\n";
    }
}

echo "\nCompleted! Fixed $totalFixed files.\n";
