<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Dashboard Access ===\n\n";

// Check if our test user exists
$testUser = User::where('email', 'test@example.com')->first();

if (!$testUser) {
    echo "❌ Test user does not exist. Creating one...\n";
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'customer'
    ]);
    echo "✅ Test user created successfully!\n";
} else {
    echo "✅ Test user exists\n";
}

echo "User details:\n";
echo "- Name: {$testUser->name}\n";
echo "- Email: {$testUser->email}\n";
echo "- Role: {$testUser->role}\n";
echo "- Active: " . ($testUser->is_active ? 'Yes' : 'No') . "\n\n";

// Test authentication programmatically
Auth::login($testUser);

if (Auth::check()) {
    echo "✅ Authentication successful!\n";
    echo "Authenticated user: " . Auth::user()->name . "\n";
    echo "User ID: " . Auth::id() . "\n";
    echo "Role: " . Auth::user()->role . "\n";
} else {
    echo "❌ Authentication failed\n";
}

// Test role-based dashboard access
echo "\n=== Available Dashboard Routes ===\n";
echo "General Dashboard: /dashboard\n";

switch ($testUser->role) {
    case 'admin':
        echo "Admin Dashboard: /admin/dashboard\n";
        break;
    case 'agent':
        echo "Agent Dashboard: /agent/dashboard\n";
        break;
    case 'customer':
        echo "Customer Dashboard: /customer/dashboard\n";
        break;
}

echo "\n=== Manual Testing Instructions ===\n";
echo "1. Open browser to: http://localhost/hdtickets/public/login\n";
echo "2. Enter credentials:\n";
echo "   Email: test@example.com\n";
echo "   Password: password123\n";
echo "3. After successful login, you should be redirected to dashboard\n";
echo "4. Or directly access: http://localhost/hdtickets/public/dashboard\n";

?>
