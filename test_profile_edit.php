<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test request to profile edit
$request = Request::create('/profile/edit', 'GET');

try {
    $response = $kernel->handle($request);
    
    echo "HTTP Status: " . $response->getStatusCode() . "\n";
    echo "Content Type: " . $response->headers->get('content-type') . "\n";
    
    if ($response->getStatusCode() == 302) {
        echo "Redirect Location: " . $response->headers->get('location') . "\n";
    }
    
    if ($response->getStatusCode() == 200) {
        echo "Page rendered successfully\n";
    }
    
    if ($response->getStatusCode() >= 400) {
        echo "Error occurred:\n";
        echo substr($response->getContent(), 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

$kernel->terminate($request, $response);
