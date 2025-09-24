<?php
// Test .env loading with Dotenv
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>Manual .env Loading Test</h1>";

try {
    // Try to manually load .env using Laravel's Dotenv
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    
    echo "<p style='color: green;'>Dotenv loaded successfully!</p>";
    
    // Check if APP_KEY is now available
    $appKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?? null;
    echo "<p>APP_KEY after Dotenv load: " . ($appKey ? substr($appKey, 0, 20) . "..." : "NOT FOUND") . "</p>";
    
    // Try to get other env vars
    echo "<p>APP_NAME: " . ($_ENV['APP_NAME'] ?? getenv('APP_NAME') ?? 'NOT FOUND') . "</p>";
    echo "<p>APP_ENV: " . ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'NOT FOUND') . "</p>";
    echo "<p>DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'NOT FOUND') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading .env: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='/'>Try Laravel App</a></p>";
?>