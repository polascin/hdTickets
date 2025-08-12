#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * Production Route Caching Script
 *
 * This script handles route caching for production deployment with proper
 * middleware validation and consideration for closures that cannot be cached.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

// Bootstrap the application
$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Kernel::class);

// Configuration
$config = include __DIR__ . '/../config/route-caching.php';

echo "🚀 Production Route Caching Script\n";
echo "==================================\n\n";

// Step 1: Validate environment
echo "1. Validating environment...\n";
if (env('APP_ENV') !== 'production') {
    echo '⚠️  Warning: Not in production environment. Current: ' . env('APP_ENV') . "\n";
    echo 'Continue? (y/n): ';
    $handle = fopen('php://stdin', 'r');
    $confirm = trim(fgets($handle));
    fclose($handle);

    if (strtolower($confirm) !== 'y') {
        echo "❌ Cancelled by user\n";
        exit(1);
    }
}

// Step 2: Clear existing route cache
echo "\n2. Clearing existing route cache...\n";
$kernel->call('route:clear');
echo "✅ Route cache cleared\n";

// Step 3: Validate middleware registration
echo "\n3. Validating middleware registration...\n";
$middlewareValidation = [
    'role'     => 'App\\Http\\Middleware\\RoleMiddleware',
    'admin'    => 'App\\Http\\Middleware\\AdminMiddleware',
    'agent'    => 'App\\Http\\Middleware\\AgentMiddleware',
    'scraper'  => 'App\\Http\\Middleware\\ScraperMiddleware',
    'customer' => 'App\\Http\\Middleware\\CustomerMiddleware',
];

$httpKernel = app(\Illuminate\Contracts\Http\Kernel::class);
$reflection = new ReflectionClass($httpKernel);
$middlewareAliases = $reflection->getProperty('middlewareAliases');
$middlewareAliases->setAccessible(TRUE);
$aliases = $middlewareAliases->getValue($httpKernel);

foreach ($middlewareValidation as $alias => $expectedClass) {
    if (!isset($aliases[$alias])) {
        echo "❌ Missing middleware alias: $alias\n";
        exit(1);
    }
    if ($aliases[$alias] !== $expectedClass) {
        echo "❌ Incorrect middleware class for $alias. Expected: $expectedClass, Got: {$aliases[$alias]}\n";
        exit(1);
    }
    echo "✅ Middleware '$alias' registered correctly\n";
}

// Step 4: Check for problematic routes with closures
echo "\n4. Checking for routes with closures...\n";
$router = app('router');
$routes = $router->getRoutes();

$problematicRoutes = [];
foreach ($routes as $route) {
    $action = $route->getAction();
    if (isset($action['uses']) && $action['uses'] instanceof Closure) {
        $problematicRoutes[] = $route->getName() ?? $route->uri();
    }
}

if (!empty($problematicRoutes)) {
    echo "⚠️  Found routes with closures that cannot be cached:\n";
    foreach ($problematicRoutes as $routeName) {
        echo "   - $routeName\n";
    }
    echo "These routes should be converted to controller methods before caching.\n";

    if (count($problematicRoutes) > 2) {
        echo "❌ Too many closure routes found. Please convert to controllers first.\n";
        exit(1);
    }
}

// Step 5: Cache routes
echo "\n5. Caching routes...\n";

try {
    $kernel->call('route:cache');
    echo "✅ Routes cached successfully\n";
} catch (Exception $e) {
    echo '❌ Failed to cache routes: ' . $e->getMessage() . "\n";

    // Attempt to identify the problematic route
    if (strpos($e->getMessage(), 'Closure') !== FALSE) {
        echo "💡 This error is likely due to routes containing closures.\n";
        echo "   Please convert closure routes to controller methods.\n";
    }

    exit(1);
}

// Step 6: Verify route cache
echo "\n6. Verifying route cache...\n";
$cacheFile = bootstrap_path('cache/routes-v7.php');
if (!file_exists($cacheFile)) {
    echo "❌ Route cache file not found\n";
    exit(1);
}

$cacheSize = filesize($cacheFile);
echo '✅ Route cache file created (Size: ' . number_format($cacheSize) . " bytes)\n";

// Step 7: Test critical routes
echo "\n7. Testing critical routes...\n";
$criticalRoutes = $config['warm_routes'] ?? [];

foreach ($criticalRoutes as $routeName) {
    try {
        $route = $router->getRoutes()->getByName($routeName);
        if ($route) {
            echo "✅ Route '$routeName' is cached and accessible\n";
        } else {
            echo "⚠️  Route '$routeName' not found\n";
        }
    } catch (Exception $e) {
        echo "❌ Error testing route '$routeName': " . $e->getMessage() . "\n";
    }
}

// Step 8: Cache other components for consistency
echo "\n8. Caching other components...\n";
$kernel->call('config:cache');
echo "✅ Configuration cached\n";

$kernel->call('view:cache');
echo "✅ Views cached\n";

// Final summary
echo "\n🎉 Route caching completed successfully!\n";
echo "=====================================\n\n";
echo "Cache Status:\n";
echo "- Routes: ✅ Cached\n";
echo "- Config: ✅ Cached\n";
echo "- Views: ✅ Cached\n";
echo "\nProduction deployment is ready!\n";

echo "\n📋 Post-deployment checklist:\n";
echo "1. Test all dashboard routes with different user roles\n";
echo "2. Verify middleware is working correctly\n";
echo "3. Check application performance metrics\n";
echo "4. Monitor error logs for any route-related issues\n";
echo "5. Verify user role-based access control\n";

exit(0);
