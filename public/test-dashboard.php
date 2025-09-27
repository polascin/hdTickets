<?php
// Direct test to bypass platform check
define('LARAVEL_START', microtime(true));

require_once '/var/www/hdtickets/vendor/autoload.php';

$app = require_once '/var/www/hdtickets/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();

try {
    $response = $kernel->handle($request);
    
    if ($response->getStatusCode() == 200) {
        echo "SUCCESS: Dashboard accessible\n";
    } else {
        echo "HTTP " . $response->getStatusCode() . "\n";
        echo $response->getContent();
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>