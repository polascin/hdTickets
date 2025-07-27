<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

echo "<h1>Laravel Container Debug</h1>";

try {
    echo "<p>Step 1: Loading autoloader...</p>";
    require __DIR__."/../vendor/autoload.php";
    echo "<p>✅ Autoloader loaded successfully</p>";
    
    echo "<p>Step 2: Loading Laravel application...</p>";
    $app = require_once __DIR__."/../bootstrap/app.php";
    echo "<p>✅ Laravel application loaded successfully</p>";
    echo "<p>App class: " . get_class($app) . "</p>";
    
    echo "<p>Step 3: Checking if container has config service...</p>";
    if ($app->bound("config")) {
        echo "<p>✅ Config service is bound</p>";
    } else {
        echo "<p>❌ Config service is NOT bound</p>";
    }
    
    echo "<p>Step 4: Listing all bound services...</p>";
    $bindings = $app->getBindings();
    echo "<p>Total bindings: " . count($bindings) . "</p>";
    echo "<details><summary>Click to see all bindings</summary><ul>";
    foreach (array_keys($bindings) as $binding) {
        echo "<li>" . htmlspecialchars($binding) . "</li>";
    }
    echo "</ul></details>";
    
    echo "<p>Step 5: Checking specific core services...</p>";
    $coreServices = ["config", "app", "files", "log", "cache", "db", "events"];
    foreach ($coreServices as $service) {
        if ($app->bound($service)) {
            echo "<p>✅ {$service} is bound</p>";
        } else {
            echo "<p>❌ {$service} is NOT bound</p>";
        }
    }
    
    echo "<p>Step 6: Try to manually resolve config...</p>";
    try {
        $config = $app->make("config");
        echo "<p>✅ Config resolved successfully: " . get_class($config) . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ Config resolution failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error Occurred:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Throwable $e) {
    echo "<h2>❌ Fatal Error Occurred:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
