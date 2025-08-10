<?php
// Direct Laravel test without going through full routing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Laravel Direct Test</h1>";

try {
    // Try to load Laravel
    require_once '../vendor/autoload.php';
    echo "✅ Composer autoload: OK<br>";
    
    $app = require_once '../bootstrap/app.php';
    echo "✅ Laravel App Bootstrap: OK<br>";
    
    // Check if we can create kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ HTTP Kernel: OK<br>";
    
    // Check basic Laravel functionality
    echo "✅ Laravel Version: " . app()->version() . "<br>";
    echo "✅ Environment: " . app()->environment() . "<br>";
    
    // Test database connection
    try {
        DB::connection()->getPdo();
        echo "✅ Database Connection: OK<br>";
    } catch (Exception $e) {
        echo "❌ Database Connection: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><strong>Laravel je pripravený!</strong><br>";
    echo "<a href='/'>Skúsiť hlavnú stránku</a><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
