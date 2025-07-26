<?php
/**
 * Standalone script to generate 1000+ fake users for hdTickets
 * This script works without Laravel Artisan and can handle database setup
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = '127.0.0.1';
$port = 3306;
$database = 'hdtickets';
$username = 'root';
$password = '';

echo "HDTickets Bulk User Generator\n";
echo "=============================\n\n";

try {
    // Connect to MySQL server (without specific database first)
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$database' created/verified\n";
    
    // Use the database
    $pdo->exec("USE `$database`");
    
    // Create users table
    $createUsersTable = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL UNIQUE,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','agent','customer') NOT NULL DEFAULT 'customer',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `remember_token` varchar(100) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createUsersTable);
    echo "✓ Users table created/verified\n\n";
    
    // Helper functions
    function generateRandomName() {
        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Mary', 'James', 'Jennifer', 'William', 'Linda', 'Richard', 'Patricia', 'Charles', 'Barbara', 'Joseph', 'Elizabeth', 'Thomas', 'Jessica', 'Christopher', 'Susan', 'Daniel', 'Karen', 'Matthew', 'Nancy', 'Anthony', 'Betty', 'Mark', 'Dorothy', 'Donald', 'Sandra', 'Steven', 'Ashley', 'Kenneth', 'Kimberly', 'Paul', 'Emily', 'Joshua', 'Donna', 'Kevin', 'Margaret', 'Brian', 'Ruth', 'George', 'Carol', 'Edward', 'Sharon', 'Ronald', 'Michelle'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
    
    function generateEmail($name, $counter) {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com', 'test.com'];
        $cleanName = strtolower(str_replace(' ', '.', $name));
        $cleanName = preg_replace('/[^a-z.]/', '', $cleanName);
        
        return $cleanName . '.' . $counter . '@' . $domains[array_rand($domains)];
    }
    
    function getWeightedRole() {
        $rand = rand(1, 100);
        if ($rand <= 3) return 'admin';
        if ($rand <= 15) return 'agent';
        return 'customer';
    }
    
    // Check existing user count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $existingUsers = $stmt->fetch()['count'];
    
    echo "Existing users in database: $existingUsers\n";
    
    if ($existingUsers > 0) {
        echo "Warning: Database already contains users. Continue? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($input) !== 'y') {
            echo "Operation cancelled.\n";
            exit(0);
        }
    }
    
    // Generate bulk users
    $batchSize = 100;
    $totalUsers = 1200;
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    echo "\nGenerating $totalUsers fake users...\n\n";
    
    $pdo->beginTransaction();
    
    $insertStmt = $pdo->prepare("
        INSERT INTO users (name, email, email_verified_at, password, role, is_active, remember_token, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $totalCreated = 0;
    
    for ($batch = 0; $batch < ceil($totalUsers / $batchSize); $batch++) {
        $batchStart = $batch * $batchSize;
        $batchEnd = min($batchStart + $batchSize, $totalUsers);
        $batchCount = $batchEnd - $batchStart;
        
        echo "Creating batch " . ($batch + 1) . " - $batchCount users (users $batchStart to $batchEnd)...\n";
        
        for ($i = $batchStart; $i < $batchEnd; $i++) {
            $name = generateRandomName();
            $email = generateEmail($name, $i + $existingUsers + 1);
            $role = getWeightedRole();
            $emailVerified = rand(1, 100) <= 80 ? date('Y-m-d H:i:s') : null; // 80% verified
            $isActive = rand(1, 100) <= 95 ? 1 : 0; // 95% active
            $rememberToken = bin2hex(random_bytes(10));
            
            $insertStmt->execute([
                $name,
                $email,
                $emailVerified,
                $hashedPassword,
                $role,
                $isActive,
                $rememberToken
            ]);
            
            $totalCreated++;
        }
        
        // Small delay to not overwhelm the database
        usleep(10000); // 10ms
    }
    
    echo "\nCreating specialized scraping accounts...\n";
    
    // Premium customers
    for ($i = 1; $i <= 50; $i++) {
        $insertStmt->execute([
            "Premium Customer $i",
            "premium.customer$i@scrapingtest.com",
            date('Y-m-d H:i:s'),
            password_hash('scraping123', PASSWORD_DEFAULT),
            'customer',
            1,
            bin2hex(random_bytes(10))
        ]);
        $totalCreated++;
    }
    
    // Platform-specific agents
    $platforms = ['stubhub', 'viagogo', 'seatgeek', 'tickpick', 'fanzone'];
    foreach ($platforms as $platform) {
        for ($i = 1; $i <= 20; $i++) {
            $insertStmt->execute([
                ucfirst($platform) . " Agent $i",
                "$platform.agent$i@scrapingtest.com",
                date('Y-m-d H:i:s'),
                password_hash('scraping123', PASSWORD_DEFAULT),
                'agent',
                1,
                bin2hex(random_bytes(10))
            ]);
            $totalCreated++;
        }
    }
    
    // Rotation pool users
    for ($i = 1; $i <= 100; $i++) {
        $firstName = ['Alex', 'Jordan', 'Casey', 'Taylor', 'Morgan', 'Riley', 'Avery', 'Quinn', 'Blake', 'Drew'][array_rand(['Alex', 'Jordan', 'Casey', 'Taylor', 'Morgan', 'Riley', 'Avery', 'Quinn', 'Blake', 'Drew'])];
        $lastName = ['Smith', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas'][array_rand(['Smith', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas'])];
        
        $insertStmt->execute([
            "$firstName $lastName",
            strtolower("$firstName.$lastName.$i@rotationpool.com"),
            date('Y-m-d H:i:s'),
            password_hash('rotation123', PASSWORD_DEFAULT),
            rand(1, 10) <= 7 ? 'customer' : 'agent', // 70% customers, 30% agents
            1,
            bin2hex(random_bytes(10))
        ]);
        $totalCreated++;
    }
    
    $pdo->commit();
    
    echo "\n✅ Successfully created $totalCreated users!\n\n";
    
    // Display statistics
    echo "=== USER STATISTICS ===\n";
    
    $stats = [
        'Total Users' => $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
        'Admins' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'],
        'Agents' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent'")->fetch()['count'],
        'Customers' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'],
        'Active Users' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch()['count'],
        'Verified Users' => $pdo->query("SELECT COUNT(*) as count FROM users WHERE email_verified_at IS NOT NULL")->fetch()['count'],
    ];
    
    foreach ($stats as $metric => $count) {
        echo sprintf("%-20s: %d\n", $metric, $count);
    }
    
    echo "\n=== SPECIALIZED ACCOUNTS ===\n";
    echo "Premium Customers: 50\n";
    echo "Platform Agents: 100 (20 per platform)\n";
    echo "Rotation Pool: 100\n";
    echo "Total Specialized: 250\n";
    
    echo "\n🎯 Users are ready for high-demand ticket scraping operations!\n";
    echo "\nCredentials:\n";
    echo "- Bulk users: password123\n";
    echo "- Premium users: scraping123\n";
    echo "- Rotation pool: rotation123\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPossible solutions:\n";
    echo "1. Make sure MySQL server is running (start Laragon)\n";
    echo "2. Check database credentials in .env file\n";
    echo "3. Ensure MySQL port 3306 is available\n";
    
    exit(1);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🚀 Ready to integrate with UserRotationService!\n";
echo "Use the service in your scraping operations like this:\n\n";

echo "Example PHP usage:\n";
echo "```php\n";
echo "\$rotationService = new \\App\\Services\\UserRotationService();\n";
echo "\$user = \$rotationService->getRotatedUser('stubhub', 'search');\n";
echo "echo \"Using user: \" . \$user->email;\n";
echo "```\n";

?>
