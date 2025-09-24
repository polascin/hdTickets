<?php
declare(strict_types=1);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Laravel Bootstrap Debug</h1>";

try {
    echo "<p>Starting Laravel bootstrap process...</p>";
    
    define('LARAVEL_START', microtime(true));
    
    // Include Composer autoloader
    echo "<p>Including Composer autoloader...</p>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>Autoloader included successfully!</p>";
    
    // Check if .env exists and is readable
    $envPath = __DIR__ . '/../.env';
    echo "<p>.env file status: " . (file_exists($envPath) && is_readable($envPath) ? "EXISTS & READABLE" : "MISSING OR UNREADABLE") . "</p>";
    
    // Check if bootstrap/app.php exists
    $bootstrapPath = __DIR__ . '/../bootstrap/app.php';
    echo "<p>Bootstrap file status: " . (file_exists($bootstrapPath) && is_readable($bootstrapPath) ? "EXISTS & READABLE" : "MISSING OR UNREADABLE") . "</p>";
    
    echo "<p>Requiring bootstrap/app.php...</p>";
    $app = require_once $bootstrapPath;
    echo "<p>Bootstrap loaded successfully!</p>";
    
    echo "<p>Creating kernel...</p>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p>Kernel created successfully!</p>";
    
    echo "<p>Creating request...</p>";
    $request = Illuminate\Http\Request::capture();
    echo "<p>Request created successfully!</p>";
    
    echo "<p>Processing request through kernel...</p>";
    $response = $kernel->handle($request);
    echo "<p>Request processed successfully!</p>";
    
    echo "<p>Sending response...</p>";
    $response->send();
    
    $kernel->terminate($request, $response);
    
} catch (Throwable $e) {
    echo "<h2 style='color: red;'>Error occurred:</h2>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
?>