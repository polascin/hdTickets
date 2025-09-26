<?php declare(strict_types=1);
// Environment diagnostic test
echo '<h1>Environment Diagnostic</h1>';
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '<p>Working Directory: ' . getcwd() . '</p>';

// Check if .env file exists
echo '<h2>.env File Check</h2>';
$envPath = __DIR__ . '/../.env';
echo '<p>.env file exists: ' . (file_exists($envPath) ? 'YES' : 'NO') . '</p>';
echo '<p>.env file readable: ' . (is_readable($envPath) ? 'YES' : 'NO') . '</p>';

if (file_exists($envPath) && is_readable($envPath)) {
    $envContent = file_get_contents($envPath);
    echo '<p>.env file size: ' . strlen($envContent) . ' bytes</p>';

    // Check for APP_KEY
    if (preg_match('/APP_KEY=(.+)/', $envContent, $matches)) {
        echo "<p style='color: green;'>APP_KEY found in .env: " . substr($matches[1], 0, 20) . '...</p>';
    } else {
        echo "<p style='color: red;'>APP_KEY NOT found in .env file</p>";
    }
}

// Test if we can load Laravel bootstrap
echo '<h2>Laravel Bootstrap Test</h2>';
$bootstrapPath = __DIR__ . '/../bootstrap/app.php';
echo '<p>Bootstrap file exists: ' . (file_exists($bootstrapPath) ? 'YES' : 'NO') . '</p>';

try {
    // Try to get environment variables directly
    echo '<h2>Direct Environment Test</h2>';
    if (function_exists('getenv')) {
        echo '<p>getenv() function available: YES</p>';
        $appKey = getenv('APP_KEY');
        echo '<p>APP_KEY via getenv(): ' . ($appKey ? substr($appKey, 0, 20) . '...' : 'NOT FOUND') . '</p>';
    }

    // Try $_ENV
    echo "<p>\$_ENV['APP_KEY']: " . (isset($_ENV['APP_KEY']) ? substr($_ENV['APP_KEY'], 0, 20) . '...' : 'NOT SET') . '</p>';

    // Try $_SERVER
    echo "<p>\$_SERVER['APP_KEY']: " . (isset($_SERVER['APP_KEY']) ? substr($_SERVER['APP_KEY'], 0, 20) . '...' : 'NOT SET') . '</p>';
} catch (Exception $e) {
    echo "<p style='color: red;'>Error testing environment: " . $e->getMessage() . '</p>';
}

echo "<p><a href='/'>Try Laravel App</a></p>";
