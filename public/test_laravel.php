<?php
// Direct test of Laravel bootstrap without Apache caching issues

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting Laravel bootstrap test...\n";

try {
    // Test basic file operations first
    $testFile = '/tmp/laravel_test_' . uniqid() . '.log';
    $handle = fopen($testFile, 'w');
    if (!$handle) {
        throw new Exception("Could not open test file");
    }
    fwrite($handle, "test");
    fclose($handle);
    unlink($testFile);
    echo "File operations: OK\n";
    
    // Now test Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoload: OK\n";
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "Bootstrap: OK\n";
    
    // Test basic Laravel functionality
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel created: OK\n";
    
    echo "All tests passed!\n";
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
