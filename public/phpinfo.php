<?php
echo "<h1>🚀 HD Tickets - LAMP Stack Installation Complete!</h1>";
echo "<h2>📋 System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";

echo "<h2>🧩 Installed PHP Extensions</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<div style='columns: 3; column-gap: 20px;'>";
foreach($extensions as $extension) {
    echo "<p>✅ " . $extension . "</p>";
}
echo "</div>";

echo "<h2>🔧 Important PHP Settings</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>max_input_vars</td><td>" . ini_get('max_input_vars') . "</td></tr>";
echo "<tr><td>date.timezone</td><td>" . ini_get('date.timezone') . "</td></tr>";
echo "</table>";

echo "<h2>🗄️ Database Connection Test</h2>";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo "<p style='color: green;'>✅ MySQL Connection: SUCCESS</p>";
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<p><strong>MySQL Version:</strong> " . $version . "</p>";
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL Connection: FAILED - " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📖 Complete PHP Configuration</h2>";
phpinfo();
?>
