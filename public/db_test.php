<?php
echo "<h1>🗄️ Database Connection Test</h1>";

try {
    $pdo = new PDO('mysql:host=localhost', 'root', 'root123');
    echo "<p style='color: green;'>✅ MySQL Connection: SUCCESS</p>";
    
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<p><strong>MySQL Version:</strong> " . $version . "</p>";
    
    // Test creating a database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS hdtickets_test");
    echo "<p style='color: green;'>✅ Database Creation: SUCCESS</p>";
    
    // Test creating a table
    $pdo->exec("USE hdtickets_test");
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100))");
    echo "<p style='color: green;'>✅ Table Creation: SUCCESS</p>";
    
    // Test inserting data
    $stmt = $pdo->prepare("INSERT INTO test_table (name) VALUES (?)");
    $stmt->execute(['HD Tickets Test ' . date('Y-m-d H:i:s')]);
    echo "<p style='color: green;'>✅ Data Insertion: SUCCESS</p>";
    
    // Test selecting data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_table");
    $count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✅ Data Selection: SUCCESS - {$count} records found</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL Connection: FAILED - " . $e->getMessage() . "</p>";
}
?>
