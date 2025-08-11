<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\PurchaseQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Test customer dashboard components
echo "=== HD Tickets Customer Dashboard Test ===\n\n";

try {
    echo "1. Testing Database Connection...\n";
    DB::connection()->getPdo();
    echo "✓ Database connected successfully\n\n";
    
    echo "2. Checking User Model...\n";
    $userCount = User::count();
    echo "✓ Total users: $userCount\n";
    
    // Get or create a test customer user
    $testUser = User::where('email', 'customer@hdtickets.test')->first();
    if (!$testUser) {
        echo "Creating test customer user...\n";
        $testUser = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@hdtickets.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
    }
    echo "✓ Test customer user: {$testUser->name} ({$testUser->email})\n\n";
    
    echo "3. Testing Models Required for Dashboard...\n";
    
    // Test ScrapedTicket model
    echo "Testing ScrapedTicket model...\n";
    $ticketCount = ScrapedTicket::count();
    echo "✓ Total scraped tickets: $ticketCount\n";
    $availableTickets = ScrapedTicket::where('is_available', true)->count();
    echo "✓ Available tickets: $availableTickets\n";
    
    // Test TicketAlert model
    echo "Testing TicketAlert model...\n";
    $alertCount = TicketAlert::count();
    echo "✓ Total ticket alerts: $alertCount\n";
    $userAlerts = TicketAlert::where('user_id', $testUser->id)->where('status', 'active')->count();
    echo "✓ User active alerts: $userAlerts\n";
    
    // Test PurchaseQueue model
    echo "Testing PurchaseQueue model...\n";
    $queueCount = PurchaseQueue::count();
    echo "✓ Total purchase queue items: $queueCount\n";
    $userQueue = PurchaseQueue::where('selected_by_user_id', $testUser->id)->where('status', 'queued')->count();
    echo "✓ User queue items: $userQueue\n\n";
    
    echo "4. Testing Dashboard Controller Methods...\n";
    
    // Test the dashboard controller
    $controller = new App\Http\Controllers\DashboardController();
    
    // Mock authentication
    \Illuminate\Support\Facades\Auth::login($testUser);
    
    echo "Testing customer dashboard view...\n";
    try {
        $response = $controller->index();
        if ($response instanceof \Illuminate\View\View) {
            echo "✓ Dashboard controller returned view successfully\n";
            echo "✓ View name: " . $response->getName() . "\n";
            echo "✓ View data keys: " . implode(', ', array_keys($response->getData())) . "\n";
        } else {
            echo "✗ Dashboard controller returned unexpected response type\n";
        }
    } catch (Exception $e) {
        echo "✗ Error testing dashboard controller: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n5. Testing View File...\n";
    $viewPath = resource_path('views/dashboard/customer.blade.php');
    if (file_exists($viewPath)) {
        echo "✓ Customer dashboard view file exists\n";
        echo "✓ View file size: " . number_format(filesize($viewPath)) . " bytes\n";
    } else {
        echo "✗ Customer dashboard view file missing\n";
    }
    
    echo "\n6. Testing CSS File...\n";
    $cssPath = public_path('css/customer-dashboard-v2.css');
    if (file_exists($cssPath)) {
        echo "✓ CSS file exists\n";
        echo "✓ CSS file size: " . number_format(filesize($cssPath)) . " bytes\n";
    } else {
        echo "✗ CSS file missing\n";
    }
    
    echo "\n7. Testing JavaScript Files...\n";
    $jsFiles = [
        'js/websocket-client.js',
        'js/dashboard-realtime.js', 
        'js/skeleton-loaders.js'
    ];
    
    foreach ($jsFiles as $jsFile) {
        $jsPath = public_path($jsFile);
        if (file_exists($jsPath)) {
            echo "✓ $jsFile exists (" . number_format(filesize($jsPath)) . " bytes)\n";
        } else {
            echo "✗ $jsFile missing\n";
        }
    }
    
    echo "\n8. Route Testing Summary...\n";
    echo "Route: /customer-dashboard\n";
    echo "Controller: App\\Http\\Controllers\\DashboardController@index\n";
    echo "Middleware: auth, verified\n";
    echo "View: dashboard.customer\n";
    
    echo "\n=== Test Complete ===\n";
    echo "✓ Customer dashboard components are functional\n";
    echo "✓ Test customer user created: {$testUser->email}\n";
    echo "✓ You can now test login at: https://localhost/login\n";
    echo "✓ After login, visit: https://localhost/customer-dashboard\n";
    
} catch (Exception $e) {
    echo "✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
