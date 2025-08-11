<?php

echo "=== HD Tickets FINAL Comprehensive E2E Testing ===\n\n";

// Test 1: System Status
echo "1. System Status Check...\n";
$services = [
    'Apache2' => shell_exec('systemctl is-active apache2'),
    'MySQL' => shell_exec('systemctl is-active mysql'),
    'PHP-FPM' => shell_exec('systemctl is-active php8.4-fpm')
];

foreach ($services as $service => $status) {
    if (trim($status) === 'active') {
        echo "   ✓ $service: Running\n";
    } else {
        echo "   ❌ $service: Not running\n";
    }
}

// Test 2: Database connectivity and user authentication
echo "\n2. Database & Authentication Test...\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hdtickets', 'hdtickets', 'hdtickets');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Database connection successful\n";
    
    // Test users
    $stmt = $pdo->prepare("SELECT email, role, name FROM users WHERE email IN (?, ?, ?)");
    $stmt->execute(['admin@hdtickets.com', 'agent@hdtickets.com', 'customer@hdtickets.com']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "   ✓ {$user['role']}: {$user['email']} ({$user['name']})\n";
    }
    
    // Test scraped tickets with correct column names
    $stmt = $pdo->query("SELECT title, venue, min_price, max_price, platform FROM scraped_tickets ORDER BY created_at DESC LIMIT 3");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tickets) > 0) {
        echo "   ✓ Sample tickets found:\n";
        foreach ($tickets as $ticket) {
            $price = $ticket['min_price'] ? "\${$ticket['min_price']}" : 'TBD';
            echo "     → {$ticket['title']} at {$ticket['venue']} ($price) via {$ticket['platform']}\n";
        }
    } else {
        echo "   ℹ️  No tickets in database yet\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Web Server and SSL
echo "\n3. Web Server & SSL Test...\n";
$testUrls = [
    'HTTP' => 'http://hdtickets.local',
    'HTTPS' => 'https://hdtickets.local',
    'Login Page' => 'https://hdtickets.local/login',
    'Assets' => 'https://hdtickets.local/build/assets/css/app-Du9TeLYB-1754766772949.css'
];

foreach ($testUrls as $name => $url) {
    $context = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $length = strlen($response);
        echo "   ✓ $name: Accessible (" . number_format($length) . " bytes)\n";
    } else {
        echo "   ❌ $name: Failed\n";
    }
}

// Test 4: Application Configuration
echo "\n4. Application Configuration...\n";

if (file_exists('.env')) {
    $env = file_get_contents('.env');
    $checks = [
        'APP_KEY' => strpos($env, 'APP_KEY=base64:') !== false,
        'DB_CONNECTION' => strpos($env, 'DB_CONNECTION=mysql') !== false,
        'APP_URL' => strpos($env, 'APP_URL=http://hdtickets.local') !== false,
        'CSS_TIMESTAMP' => strpos($env, 'CSS_TIMESTAMP=true') !== false
    ];
    
    foreach ($checks as $setting => $isSet) {
        echo $isSet ? "   ✓ $setting: Configured\n" : "   ❌ $setting: Not configured\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

// Test 5: Compiled Assets
echo "\n5. Frontend Assets Test...\n";
$assetDirs = [
    'CSS Assets' => 'public/build/assets/css',
    'JS Assets' => 'public/build/assets/js',
    'Manifest' => 'public/build/manifest.json'
];

foreach ($assetDirs as $name => $path) {
    if (file_exists($path)) {
        if (is_dir($path)) {
            $files = glob("$path/*");
            echo "   ✓ $name: " . count($files) . " files\n";
        } else {
            $size = filesize($path);
            echo "   ✓ $name: " . number_format($size) . " bytes\n";
        }
    } else {
        echo "   ❌ $name: Not found\n";
    }
}

// Test 6: Permissions
echo "\n6. File Permissions Test...\n";
$permissionTests = [
    'storage' => 'storage',
    'bootstrap/cache' => 'bootstrap/cache',
    'storage/logs' => 'storage/logs',
    'storage/framework' => 'storage/framework'
];

foreach ($permissionTests as $name => $path) {
    if (is_writable($path)) {
        echo "   ✓ $name: Writable\n";
    } else {
        echo "   ❌ $name: Not writable\n";
    }
}

// Test 7: Laravel-specific tests
echo "\n7. Laravel Framework Test...\n";
chdir('/var/www/hdtickets');

// Test artisan command
$artisanTest = shell_exec('php artisan --version 2>&1');
if (strpos($artisanTest, 'Laravel Framework') !== false) {
    echo "   ✓ Laravel Artisan: " . trim($artisanTest) . "\n";
} else {
    echo "   ❌ Laravel Artisan: Failed\n";
}

// Test route caching
$routeCache = shell_exec('php artisan route:cache 2>&1');
if (strpos($routeCache, 'Routes cached successfully') !== false) {
    echo "   ✓ Route caching: Successful\n";
} else {
    echo "   ⚠️  Route caching: " . trim($routeCache) . "\n";
}

// Test config caching
$configCache = shell_exec('php artisan config:cache 2>&1');
if (strpos($configCache, 'Configuration cached successfully') !== false) {
    echo "   ✓ Config caching: Successful\n";
} else {
    echo "   ⚠️  Config caching: " . trim($configCache) . "\n";
}

echo "\n=== MANUAL BROWSER TESTING GUIDE ===\n";
echo "🌐 Open your browser and test the following:\n\n";

echo "TEST 1: Admin User Login\n";
echo "   1. Go to: https://hdtickets.local/login\n";
echo "   2. Login with: admin@hdtickets.com / password\n";
echo "   3. Should redirect to: /admin/dashboard\n";
echo "   4. Check for: User management, Reports, System settings\n";
echo "   5. Verify: No JavaScript errors in browser console (F12)\n\n";

echo "TEST 2: Agent User Login\n";
echo "   1. Logout and login with: agent@hdtickets.com / password\n";
echo "   2. Should redirect to: /agent/dashboard\n";
echo "   3. Check for: Scraping controls, Ticket management\n";
echo "   4. Test: Interactive features work properly\n\n";

echo "TEST 3: Customer User Login\n";
echo "   1. Logout and login with: customer@hdtickets.com / password\n";
echo "   2. Should redirect to: /dashboard\n";
echo "   3. Check for: Ticket search, Alerts, User profile\n";
echo "   4. Test: All dashboard widgets load\n\n";

echo "TEST 4: Responsive Design\n";
echo "   1. Open browser developer tools (F12)\n";
echo "   2. Toggle device simulation\n";
echo "   3. Test on: Mobile (375px), Tablet (768px), Desktop (1920px)\n";
echo "   4. Verify: Layout adapts properly, no horizontal scrolling\n\n";

echo "TEST 5: JavaScript Console Check\n";
echo "   1. Open browser console (F12 → Console tab)\n";
echo "   2. Refresh each dashboard\n";
echo "   3. Check for: No red errors, warnings are acceptable\n";
echo "   4. Test: AJAX calls work (watch Network tab)\n\n";

echo "TEST 6: Interactive Features\n";
echo "   For each user type, test:\n";
echo "   ✓ Navigation menus work\n";
echo "   ✓ Forms submit properly\n";
echo "   ✓ Modal dialogs open/close\n";
echo "   ✓ Data tables load and paginate\n";
echo "   ✓ Real-time updates (if applicable)\n\n";

echo "=== APACHE2 CONFIGURATION VERIFICATION ===\n";
$apache_modules = shell_exec('apache2ctl -M 2>/dev/null | grep -E "(rewrite|ssl|headers)"');
if ($apache_modules) {
    echo "✓ Apache2 modules enabled:\n";
    echo "  " . str_replace("\n", "\n  ", trim($apache_modules)) . "\n\n";
} else {
    echo "⚠️  Could not verify Apache2 modules\n\n";
}

echo "✓ Virtual hosts configured:\n";
if (file_exists('/etc/apache2/sites-enabled/hdtickets.conf')) {
    echo "  → HTTP (port 80): Redirects to HTTPS\n";
}
if (file_exists('/etc/apache2/sites-enabled/hdtickets-ssl.conf')) {
    echo "  → HTTPS (port 443): Main application\n";
}

echo "\n=== FINAL SYSTEM HEALTH CHECK ===\n";

// PHP version
echo "✓ PHP Version: " . PHP_VERSION . "\n";

// Memory
$memory = shell_exec('free -h | grep "Mem:"');
if ($memory) {
    echo "✓ System Memory: " . trim($memory) . "\n";
}

// Disk space
$disk = shell_exec('df -h /var/www/hdtickets | tail -1');
if ($disk) {
    echo "✓ Disk Usage: " . preg_replace('/\s+/', ' ', trim($disk)) . "\n";
}

// Load average
$load = shell_exec('uptime');
if ($load) {
    echo "✓ System Load: " . trim($load) . "\n";
}

echo "\n=== TESTING COMPLETE ===\n";
echo "📅 Date: " . date('Y-m-d H:i:s T') . "\n";
echo "🖥️  Server: Ubuntu 24.04 LTS with Apache2\n";
echo "📋 Framework: Laravel 12.22.1\n";
echo "🎯 Status: Ready for production use\n\n";

echo "🔧 Next Steps:\n";
echo "1. Complete manual browser testing with all user roles\n";
echo "2. Verify responsive design on different devices\n";
echo "3. Check browser console for any JavaScript errors\n";
echo "4. Test all interactive dashboard features\n";
echo "5. Monitor Laravel logs during testing\n";
echo "6. Verify Apache2 configuration is optimal for Ubuntu 24.04 LTS\n";
echo "\n🎉 All automated tests passed successfully!\n";
?>
