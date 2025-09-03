<?php
/**
 * Profile Enhancements Comprehensive Test
 * 
 * Tests all the enhanced profile functionality including:
 * - Profile page loading and statistics
 * - Real-time updates
 * - Preferences management
 * - Performance optimizations
 * - Error handling
 */

echo "=== HD Tickets Profile Enhancements Test ===\n\n";

// Test 1: Profile Page Response
echo "1. Testing Profile Page Response...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://hdtickets.local/profile');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$responseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

echo "   HTTP Status: $httpCode\n";
echo "   Response Time: " . round($responseTime * 1000, 2) . "ms\n";

if ($httpCode === 200) {
    echo "   ✓ Profile page loads successfully\n";
} else {
    echo "   ✗ Profile page failed to load\n";
}

// Test 2: Check for Enhanced CSS and JavaScript
echo "\n2. Testing Enhanced UI Components...\n";
if ($response && $httpCode === 200) {
    $hasProgressRings = strpos($response, 'progress-ring') !== false;
    $hasRealTimeUpdates = strpos($response, 'updateProfileStats') !== false;
    $hasModernCSS = strpos($response, 'progress-text') !== false;
    $hasStatsCards = strpos($response, 'stats-value') !== false;
    
    echo "   Progress Rings: " . ($hasProgressRings ? "✓ Found" : "✗ Missing") . "\n";
    echo "   Real-time Updates: " . ($hasRealTimeUpdates ? "✓ Found" : "✗ Missing") . "\n";
    echo "   Modern CSS: " . ($hasModernCSS ? "✓ Found" : "✗ Missing") . "\n";
    echo "   Stats Cards: " . ($hasStatsCards ? "✓ Found" : "✗ Missing") . "\n";
    
    if ($hasProgressRings && $hasRealTimeUpdates && $hasModernCSS && $hasStatsCards) {
        echo "   ✓ All enhanced UI components present\n";
    } else {
        echo "   ⚠ Some UI enhancements may be missing\n";
    }
} else {
    echo "   ✗ Cannot test UI components - page not loaded\n";
}

// Test 3: Profile Stats API Endpoint
echo "\n3. Testing Profile Stats API...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://hdtickets.local/profile/stats');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$statsResponse = curl_exec($ch);
$statsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$statsResponseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

echo "   HTTP Status: $statsHttpCode\n";
echo "   Response Time: " . round($statsResponseTime * 1000, 2) . "ms\n";

if ($statsHttpCode === 200 && $statsResponse) {
    $statsData = json_decode($statsResponse, true);
    if ($statsData && is_array($statsData)) {
        echo "   ✓ Stats API returns valid JSON\n";
        
        // Check for expected fields
        $expectedFields = ['profile_completion', 'security_score', 'total_tickets', 'active_filters'];
        $foundFields = array_intersect($expectedFields, array_keys($statsData));
        
        echo "   Expected fields found: " . count($foundFields) . "/" . count($expectedFields) . "\n";
        foreach ($expectedFields as $field) {
            $status = isset($statsData[$field]) ? "✓" : "✗";
            echo "     $status $field\n";
        }
    } else {
        echo "   ✗ Stats API returns invalid JSON\n";
    }
} else {
    echo "   ✗ Stats API not accessible\n";
}

// Test 4: Check for Performance Optimizations
echo "\n4. Testing Performance Optimizations...\n";

// Check if response time is reasonable (under 500ms for cached requests)
if ($responseTime < 0.5) {
    echo "   ✓ Fast page load time (< 500ms)\n";
} else {
    echo "   ⚠ Page load time could be improved (" . round($responseTime * 1000, 2) . "ms)\n";
}

// Check if stats API is fast (should be cached)
if ($statsResponseTime < 0.3) {
    echo "   ✓ Fast stats API response (< 300ms)\n";
} else {
    echo "   ⚠ Stats API could be faster (" . round($statsResponseTime * 1000, 2) . "ms)\n";
}

// Test 5: Check Laravel Application Health
echo "\n5. Testing Laravel Application Health...\n";

try {
    // Include Laravel bootstrap
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    echo "   ✓ Laravel application bootstrapped successfully\n";
    
    // Test database connection
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Simple database check
    if (class_exists('Illuminate\Support\Facades\DB')) {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            echo "   ✓ Database connection successful\n";
        } catch (Exception $e) {
            echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
        }
    }
    
    // Check if User model exists and has required methods
    if (class_exists('App\Models\User')) {
        $userClass = new ReflectionClass('App\Models\User');
        $hasProfileCompletion = $userClass->hasMethod('getProfileCompletion');
        
        echo "   Profile completion method: " . ($hasProfileCompletion ? "✓ Found" : "✗ Missing") . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Laravel application error: " . $e->getMessage() . "\n";
}

// Test 6: File Structure Validation
echo "\n6. Validating Enhanced File Structure...\n";

$criticalFiles = [
    '/var/www/hdtickets/app/Http/Controllers/ProfileController.php' => 'Profile Controller',
    '/var/www/hdtickets/resources/views/profile/show.blade.php' => 'Profile View Template',
    '/var/www/hdtickets/routes/web.php' => 'Web Routes',
];

foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✓ $description exists (" . number_format($size) . " bytes)\n";
        
        // Check if file was recently modified (within last hour)
        $modified = filemtime($file);
        $isRecent = (time() - $modified) < 3600;
        if ($isRecent) {
            echo "     ↳ Recently modified (" . date('H:i:s', $modified) . ")\n";
        }
    } else {
        echo "   ✗ $description missing\n";
    }
}

// Test 7: Code Quality Check
echo "\n7. Basic Code Quality Validation...\n";

$controllerFile = '/var/www/hdtickets/app/Http/Controllers/ProfileController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Check for enhanced methods
    $enhancedMethods = [
        'updatePreferences' => 'Preferences update method',
        'calculateSecurityScore' => 'Security score calculation',
        'stats' => 'Statistics method',
    ];
    
    foreach ($enhancedMethods as $method => $description) {
        $hasMethod = strpos($controllerContent, "function $method") !== false;
        echo "   " . ($hasMethod ? "✓" : "✗") . " $description\n";
    }
    
    // Check for caching implementation
    $hasCaching = strpos($controllerContent, 'Cache::remember') !== false || 
                  strpos($controllerContent, 'Cache::forget') !== false;
    echo "   " . ($hasCaching ? "✓" : "✗") . " Caching implementation\n";
    
    // Check for error handling
    $hasErrorHandling = strpos($controllerContent, 'try {') !== false && 
                        strpos($controllerContent, 'catch') !== false;
    echo "   " . ($hasErrorHandling ? "✓" : "✗") . " Error handling\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Profile enhancement testing completed.\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

if ($httpCode === 200 && $statsHttpCode === 200) {
    echo "Status: ✓ Core functionality working\n";
} else {
    echo "Status: ⚠ Some issues detected\n";
}

echo "\nFor detailed functionality testing, please:\n";
echo "1. Visit https://hdtickets.local/profile in a browser\n";
echo "2. Test real-time statistics updates\n";
echo "3. Verify responsive design on different screen sizes\n";
echo "4. Test profile preferences updates\n";
echo "5. Check browser console for JavaScript errors\n";

echo "\n=== End of Test ===\n";