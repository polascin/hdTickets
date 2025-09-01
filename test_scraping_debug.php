<?php

// Test scraping functionality
require_once '/var/www/hdtickets/vendor/autoload.php';

try {
    // Test GuzzleHttp directly
    $client = new GuzzleHttp\Client();
    echo "GuzzleHttp client created successfully\n";
    
    // Test a simple HTTP request
    $response = $client->get('https://httpbin.org/get');
    echo "HTTP request successful. Status: " . $response->getStatusCode() . "\n";
    
    // Test Laravel app loading
    $app = require_once '/var/www/hdtickets/bootstrap/app.php';
    $app->boot();
    echo "Laravel app loaded successfully\n";
    
    // Test TicketScrapingService
    $service = $app->make(\App\Services\TicketScrapingService::class);
    echo "TicketScrapingService instantiated successfully\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}