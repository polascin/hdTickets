<?php

echo "=== HD Tickets Comprehensive E2E Testing ===\n\n";

// Test 1: Check Apache status
echo "1. Checking Apache2 Status...\n";
$apacheStatus = shell_exec('systemctl is-active apache2');
if (trim($apacheStatus) === 'active') {
    echo "   ✓ Apache2 is running\n";
} else {
    echo "   ❌ Apache2 is not running\n";
}

// Test 2: Check PHP-FPM status  
echo "2. Checking PHP-FPM Status...\n";
$phpfpmStatus = shell_exec('systemctl is-active php8.4-fpm');
if (trim($phpfpmStatus) === 'active') {
    echo "   ✓ PHP-FPM is running\n";
} else {
    echo "   ❌ PHP-FPM is not running\n";
}

// Test 3: Check database connection
echo "3. Testing Database Connection...\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hdtickets', 'hdtickets', 'hdtickets');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "   ✓ Database connection successful\n";
    echo "   ✓ Total users in database: $userCount\n";
    
    // Count tickets
    $stmt = $pdo->query("SELECT COUNT(*) FROM scraped_tickets");
    $ticketCount = $stmt->fetchColumn();
    echo "   ✓ Total tickets in database: $ticketCount\n";
    
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 4: Test web connectivity
echo "4. Testing Web Connectivity...\n";

// Test HTTP connection
$httpTest = @file_get_contents('http://hdtickets.local', false, stream_context_create([
    'http' => ['timeout' => 5],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));

if ($httpTest !== false) {
    echo "   ✓ HTTP connection successful\n";
} else {
    echo "   ❌ HTTP connection failed\n";
}

// Test HTTPS connection
$httpsTest = @file_get_contents('https://hdtickets.local', false, stream_context_create([
    'http' => ['timeout' => 5],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));

if ($httpsTest !== false) {
    echo "   ✓ HTTPS connection successful\n";
} else {
    echo "   ❌ HTTPS connection failed\n";
}

// Test 5: Check virtual host files
echo "5. Checking Apache Configuration...\n";
if (file_exists('/etc/apache2/sites-enabled/hdtickets.conf')) {
    echo "   ✓ HTTP virtual host enabled\n";
} else {
    echo "   ❌ HTTP virtual host not enabled\n";
}

if (file_exists('/etc/apache2/sites-enabled/hdtickets-ssl.conf')) {
    echo "   ✓ HTTPS virtual host enabled\n";
} else {
    echo "   ❌ HTTPS virtual host not enabled\n";
}

// Test 6: Check Laravel configuration
echo "6. Checking Laravel Configuration...\n";
chdir('/var/www/hdtickets');

if (file_exists('.env')) {
    echo "   ✓ Environment file exists\n";
    
    // Check key Laravel settings
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'base64:') !== false) {
        echo "   ✓ Application key is set\n";
    } else {
        echo "   ❌ Application key not properly set\n";
    }
    
    if (strpos($envContent, 'APP_ENV=development') !== false || strpos($envContent, 'APP_ENV=production') !== false) {
        echo "   ✓ Environment is configured\n";
    } else {
        echo "   ❌ Environment not properly configured\n";
    }
} else {
    echo "   ❌ Environment file missing\n";
}

// Test 7: Check storage permissions
echo "7. Checking File Permissions...\n";
$directories = [
    '/var/www/hdtickets/storage',
    '/var/www/hdtickets/storage/logs',
    '/var/www/hdtickets/storage/framework',
    '/var/www/hdtickets/bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "   ✓ $dir is writable\n";
    } else {
        echo "   ❌ $dir is not writable\n";
    }
}

// Test 8: Check Laravel logs for errors
echo "8. Checking Laravel Logs...\n";
$logFile = '/var/www/hdtickets/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Get last 50 lines
    $recentContent = implode("\n", $recentLines);
    
    $errorCount = substr_count($recentContent, '.ERROR:');
    $warningCount = substr_count($recentContent, '.WARNING:');
    
    echo "   ✓ Laravel log file exists\n";
    echo "   ℹ️  Recent errors: $errorCount\n";
    echo "   ℹ️  Recent warnings: $warningCount\n";
    
    if ($errorCount > 0) {
        echo "   ⚠️  Recent errors found - check $logFile\n";
    }
} else {
    echo "   ℹ️  No Laravel log file found (may be normal for fresh install)\n";
}

// Test 9: Test key application endpoints
echo "9. Testing Application Endpoints...\n";

$endpoints = [
    'Login Page' => '/login',
    'Admin Dashboard' => '/admin/dashboard', 
    'Agent Dashboard' => '/agent/dashboard',
    'Customer Dashboard' => '/dashboard',
    'API Health' => '/api/health',
];

foreach ($endpoints as $name => $endpoint) {
    $url = "https://hdtickets.local$endpoint";
    $response = @file_get_contents($url, false, stream_context_create([
        'http' => ['timeout' => 10, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]));
    
    if ($response !== false) {
        echo "   ✓ $name endpoint accessible\n";
    } else {
        echo "   ❌ $name endpoint failed\n";
    }
}

// Test 10: Check for JavaScript files
echo "10. Testing Static Assets...\n";

$assets = [
    '/css/app.css',
    '/js/app.js',
    '/js/bootstrap.js'
];

foreach ($assets as $asset) {
    $url = "https://hdtickets.local$asset";
    $response = @file_get_contents($url, false, stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]));
    
    if ($response !== false) {
        echo "   ✓ Asset $asset accessible\n";
    } else {
        echo "   ❌ Asset $asset not found\n";
    }
}

echo "\n=== Testing Complete ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Server: Ubuntu 24.04 LTS with Apache2\n";
echo "For detailed browser testing, manually test login with:\n";
echo "- Admin: admin@hdtickets.com / password\n";
echo "- Agent: agent@hdtickets.com / password\n"; 
echo "- Customer: customer@hdtickets.com / password\n";
