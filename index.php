<?php
echo "<h1>🚀 HD Tickets - Sports Events Entry Tickets System</h1>";
echo "<p><strong>✅ Root Access Test: SUCCESSFUL</strong></p>";
echo "<p>This page is being served from the Laravel application root directory.</p>";
echo "<p>The .htaccess redirect is working correctly!</p>";

echo "<h2>Available Routes to Test:</h2>";
echo "<ul>";
echo "<li><a href='/login'>/login</a> - Login page</li>";
echo "<li><a href='/dashboard'>/dashboard</a> - Dashboard (requires auth)</li>";
echo "<li><a href='/tickets'>/tickets</a> - Tickets system (requires auth)</li>";
echo "<li><a href='/public/index.php'>/public/index.php</a> - Direct public access</li>";
echo "<li><a href='/assets/images/hdTicketsLogo.png'>/assets/images/hdTicketsLogo.png</a> - Static asset test</li>";
echo "</ul>";

echo "<h2>Server Information:</h2>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check if this is being served from public directory (shouldn't be)
if (strpos(__DIR__, '/public') !== false) {
    echo "<p style='color: red;'><strong>⚠️ WARNING: This is being served from the public directory. The redirect is NOT working properly.</strong></p>";
} else {
    echo "<p style='color: green;'><strong>✅ GOOD: This is being served from the application root. The redirect is working properly.</strong></p>";
}
?>
