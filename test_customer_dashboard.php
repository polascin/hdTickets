<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "🔍 Testing Customer Dashboard Controller\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // Test 1: Check if User model and methods work
    echo "1. Testing User model and role methods...\n";
    
    $user = User::where('role', 'customer')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Test Customer',
            'surname' => 'User', 
            'username' => 'test.customer',
            'email' => 'test.customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        echo "   ✅ Created test customer user\n";
    } else {
        echo "   ✅ Found existing customer user\n";
    }
    
    echo "   - User ID: {$user->id}\n";
    echo "   - User role: {$user->role}\n";
    echo "   - isCustomer(): " . ($user->isCustomer() ? 'true' : 'false') . "\n";
    echo "   - canAccessSystem(): " . ($user->canAccessSystem() ? 'true' : 'false') . "\n";
    echo "   - canLoginToWeb(): " . ($user->canLoginToWeb() ? 'true' : 'false') . "\n";
    
    // Test 2: Check database connectivity and essential models
    echo "\n2. Testing database connectivity...\n";
    $userCount = User::count();
    echo "   ✅ Database connected, {$userCount} users in system\n";
    
    // Test 3: Check if DashboardController exists and can be instantiated
    echo "\n3. Testing DashboardController...\n";
    $controller = new DashboardController();
    echo "   ✅ DashboardController instantiated successfully\n";
    
    // Test 4: Test the index method with authentication
    echo "\n4. Testing controller index method with authentication...\n";
    
    // Login the user
    Auth::login($user);
    echo "   ✅ User authenticated: " . (Auth::check() ? 'true' : 'false') . "\n";
    echo "   ✅ Authenticated user ID: " . Auth::id() . "\n";
    echo "   ✅ Authenticated user role: " . Auth::user()->role . "\n";
    
    // Create a mock request
    $request = Request::create('/dashboard/customer', 'GET');
    $request->setUserResolver(function() use ($user) {
        return $user;
    });
    
    // Test the controller method
    try {
        $response = $controller->index();
        echo "   ✅ Controller index method executed successfully\n";
        echo "   ✅ Response type: " . get_class($response) . "\n";
        
        if (method_exists($response, 'getData')) {
            $data = $response->getData();
            echo "   ✅ Response data keys: " . implode(', ', array_keys((array)$data)) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller error: " . $e->getMessage() . "\n";
        echo "   📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "   📊 Stack trace:\n";
        echo "      " . str_replace("\n", "\n      ", $e->getTraceAsString()) . "\n";
    }
    
    echo "\n✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📊 Stack trace:\n";
    echo "   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . "\n";
}
