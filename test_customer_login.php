<?php
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Set up Laravel environment
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

echo "Testing customer dashboard access...\n\n";

// Find a customer user
$customer = User::where('role', 'customer')->first();

if (!$customer) {
    echo "No customer users found in database!\n";
    exit(1);
}

echo "Found customer user: {$customer->name} ({$customer->email})\n";
echo "User role: {$customer->role}\n";
echo "Is customer: " . ($customer->isCustomer() ? 'Yes' : 'No') . "\n\n";

// Simulate login
Auth::login($customer);

if (Auth::check()) {
    echo "✅ User successfully logged in\n";
    echo "Authenticated user: " . Auth::user()->name . "\n";
    echo "User ID: " . Auth::id() . "\n";
    echo "Is customer: " . (Auth::user()->isCustomer() ? 'Yes' : 'No') . "\n\n";
    
    // Test middleware logic
    try {
        $middleware = new App\Http\Middleware\CustomerMiddleware();
        
        // Create a mock request
        $request = new Illuminate\Http\Request();
        
        // Mock next closure
        $next = function ($request) {
            return new Illuminate\Http\Response('Dashboard content would load here', 200);
        };
        
        $response = $middleware->handle($request, $next);
        
        echo "✅ CustomerMiddleware passed successfully\n";
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response content: " . $response->getContent() . "\n";
        
    } catch (Exception $e) {
        echo "❌ CustomerMiddleware failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Login failed\n";
}

// Test controller with dependency injection
echo "\n--- Testing EnhancedDashboardController ---\n";
try {
    $analytics = app(App\Services\AnalyticsService::class);
    $recommendations = app(App\Services\RecommendationService::class);
    $controller = new App\Http\Controllers\EnhancedDashboardController($analytics, $recommendations);
    echo "✅ EnhancedDashboardController instantiated successfully\n";
    
    // Test if index method exists
    if (method_exists($controller, 'index')) {
        echo "✅ index() method exists\n";
    } else {
        echo "❌ index() method missing\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";