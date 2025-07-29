<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

echo "<h1>Laravel Loading Test</h1>";

try {
    echo "<p>Step 1: Loading autoloader...</p>";
    require __DIR__."/../vendor/autoload.php";
    echo "<p>✅ Autoloader loaded successfully</p>";
    
    echo "<p>Step 2: Loading Laravel application...</p>";
    $app = require_once __DIR__."/../bootstrap/app.php";
    echo "<p>✅ Laravel application loaded successfully</p>";
    
    echo "<p>Step 3: Creating kernel...</p>";
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "<p>✅ HTTP Kernel created successfully</p>";
    
    echo "<p>Step 4: Capturing request...</p>";
    $request = \Illuminate\Http\Request::capture();
    echo "<p>✅ Request captured successfully</p>";
    
    echo "<p>Step 5: Handling request...</p>";
    $response = $kernel->handle($request);
    echo "<p>✅ Request handled successfully</p>";
    
    echo "<p>Step 6: Sending response...</p>";
    $response->send();
    
    $kernel->terminate($request, $response);
    
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
