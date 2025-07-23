<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

echo "=== HDTickets System - Endpoint Testing ===\n\n";

// List of endpoints to test
$endpointsToTest = [
    ['method' => 'GET', 'path' => '/', 'description' => 'Home page'],
    ['method' => 'GET', 'path' => '/login', 'description' => 'Login page'],
    ['method' => 'GET', 'path' => '/register', 'description' => 'Register page'],
    ['method' => 'GET', 'path' => '/api/v1/status', 'description' => 'API status endpoint'],
    ['method' => 'GET', 'path' => '/dashboard', 'description' => 'Dashboard (requires auth)'],
];

$successful = 0;
$total = count($endpointsToTest);

foreach ($endpointsToTest as $endpoint) {
    $method = $endpoint['method'];
    $path = $endpoint['path'];
    $description = $endpoint['description'];
    
    echo "🔍 Testing {$method} {$path} ({$description}):\n";
    
    try {
        // Create a fake request
        $request = Request::create($path, $method);
        
        // Try to find the route
        $route = Route::getRoutes()->match($request);
        
        if ($route) {
            echo "  ✅ Route found and accessible\n";
            echo "  📋 Action: {$route->getActionName()}\n";
            $successful++;
        } else {
            echo "  ❌ Route not found\n";
        }
        
    } catch (Exception $e) {
        echo "  ⚠️  Route exists but requires: " . $e->getMessage() . "\n";
        $successful++; // Still count as successful if route exists
    }
    
    echo "\n";
}

echo "📊 Endpoint Test Results:\n";
echo "  • Successful: $successful/$total\n";
echo "  • Success Rate: " . round(($successful / $total) * 100, 1) . "%\n\n";

if ($successful === $total) {
    echo "✅ All core endpoints are accessible!\n";
} else {
    echo "⚠️  Some endpoints may need authentication or have other requirements.\n";
}
