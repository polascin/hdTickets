<?php
// Simple test that doesn't involve Laravel
echo "<h1>Simple PHP Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Current Working Directory: " . getcwd() . "</p>";

// Test if Laravel bootstrap files exist
echo "<p>Laravel Bootstrap exists: " . (file_exists(__DIR__ . '/../bootstrap/app.php') ? 'YES' : 'NO') . "</p>";
echo "<p>.env file exists: " . (file_exists(__DIR__ . '/../.env') ? 'YES' : 'NO') . "</p>";

// Test basic environment loading without Laravel
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    if (strpos($envContent, 'DB_USERNAME=hdtickets') !== false) {
        echo "<p style='color: green;'>.env file contains correct DB_USERNAME</p>";
    } else {
        echo "<p style='color: red;'>.env file missing correct DB_USERNAME</p>";
    }
}

echo "<p><a href='/'>Try Laravel App</a></p>";
?>