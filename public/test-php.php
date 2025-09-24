<?php
// Simple PHP test file to verify PHP version and basic functionality
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
try {
    $config = require __DIR__ . '/../config/database.php';
    $dbConfig = $config['connections']['mysql'];
    
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    echo "<p style='color: green;'>Database Connection: SUCCESS</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Users in database: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Connection: FAILED - " . $e->getMessage() . "</p>";
}

echo "<p><a href='/'>Go to Welcome Page</a></p>";
echo "<p><a href='/test-welcome.php'>Go to Static Test Welcome Page</a></p>";
?>