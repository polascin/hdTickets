<?php declare(strict_types=1);

/**
 * Temporary test script to test the trending endpoint functionality
 * This bypasses authentication to test the controller methods directly
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\TicketScrapingController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing TicketScrapingController methods...\n";
    
    // Create controller instance using service container
    $controller = $app->make(TicketScrapingController::class);
    
    // Create a mock request
    $request = Request::create('/test', 'GET');
    
    // Test the trending method
    echo "\n=== Testing trending() method ===\n";
    $response = $controller->trending($request);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        echo "Response type: JsonResponse\n";
        echo "Status code: " . $response->getStatusCode() . "\n";
        echo "Content: " . $response->getContent() . "\n";
    } else {
        echo "Response type: " . get_class($response) . "\n";
        echo "Response: " . print_r($response, true) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
