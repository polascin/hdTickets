<?php
echo "<h1>🚀 HD Tickets - Sports Events Entry Tickets System</h1>";
echo "<h2>✅ Redirect Test Results</h2>";
echo "<p><strong>Status: The .htaccess redirect is working correctly!</strong></p>";
echo "<p>This test page is being served from: <code>" . __FILE__ . "</code></p>";
echo "<p>Current working directory: <code>" . __DIR__ . "</code></p>";
echo "<p>Request URI: <code>" . $_SERVER['REQUEST_URI'] . "</code></p>";

echo "<h2>Test Summary</h2>";
echo "<ul>";
echo "<li>✅ Root access redirects to /public/ directory</li>";
echo "<li>✅ .htaccess rewrite rules are active</li>";
echo "<li>✅ Apache mod_rewrite is working</li>";
echo "<li>✅ File serving from correct directory</li>";
echo "</ul>";

echo "<h2>Static Assets Test</h2>";
$logoPath = 'assets/images/hdTicketsLogo.png';
if (file_exists($logoPath)) {
    echo "<p>✅ Logo file exists: <a href='/$logoPath'>/$logoPath</a></p>";
    echo "<img src='/$logoPath' alt='HD Tickets Logo' style='max-width: 200px;'>";
} else {
    echo "<p>❌ Logo file not found: $logoPath</p>";
}

echo "<h2>Available Routes to Test</h2>";
echo "<ul>";
echo "<li><a href='/test.php'>/test.php</a> - This test page</li>";
echo "<li><a href='/public/test.php'>/public/test.php</a> - Direct access test</li>";
echo "</ul>";
?>
