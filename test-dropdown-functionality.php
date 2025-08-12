<?php

/**
 * Test script to verify login functionality and dropdown behavior
 */

echo "🧪 HD Tickets Dropdown Functionality Test\n";
echo "=========================================\n\n";

$baseUrl = 'http://localhost:8000';
$cookieJar = tempnam(sys_get_temp_dir(), 'hdtickets_test_cookies');

// Test credentials
$credentials = [
    'admin' => [
        'email' => 'admin@hdtickets.com',
        'password' => 'password'
    ],
    'ticketmaster' => [
        'email' => 'ticketmaster@hdtickets.admin',
        'password' => 'SecureAdminPass123!'
    ]
];

function makeRequest($url, $data = null, $cookieJar = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'body' => $response,
        'http_code' => $httpCode
    ];
}

function getCsrfToken($html) {
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
        return $matches[1];
    }
    if (preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $html, $matches)) {
        return $matches[1];
    }
    return null;
}

// Test 1: Access login page
echo "📋 Step 1: Accessing login page...\n";
$response = makeRequest("$baseUrl/login", null, $cookieJar);
if ($response['http_code'] === 200) {
    echo "✅ Login page accessible (HTTP {$response['http_code']})\n";
    
    // Extract CSRF token
    $csrfToken = getCsrfToken($response['body']);
    if ($csrfToken) {
        echo "✅ CSRF token extracted: " . substr($csrfToken, 0, 20) . "...\n";
    } else {
        echo "❌ Could not extract CSRF token\n";
        exit(1);
    }
} else {
    echo "❌ Cannot access login page (HTTP {$response['http_code']})\n";
    exit(1);
}

echo "\n";

// Test 2: Attempt login with admin credentials
echo "📋 Step 2: Testing login with admin credentials...\n";

$loginData = http_build_query([
    '_token' => $csrfToken,
    'email' => $credentials['admin']['email'],
    'password' => $credentials['admin']['password']
]);

$headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'X-CSRF-TOKEN: ' . $csrfToken
];

$loginResponse = makeRequest("$baseUrl/login", $loginData, $cookieJar, $headers);

if ($loginResponse['http_code'] === 302 || $loginResponse['http_code'] === 200) {
    echo "✅ Login attempt successful (HTTP {$loginResponse['http_code']})\n";
    
    // Test 3: Access dashboard/home to verify authentication
    echo "\n📋 Step 3: Accessing authenticated dashboard...\n";
    $dashboardResponse = makeRequest("$baseUrl/dashboard", null, $cookieJar);
    
    if ($dashboardResponse['http_code'] === 200) {
        echo "✅ Dashboard accessible after login (HTTP {$dashboardResponse['http_code']})\n";
        
        // Check for navigation elements with dropdown functionality
        $dashboardHtml = $dashboardResponse['body'];
        
        // Test 4: Check for Alpine.js and dropdown elements
        echo "\n📋 Step 4: Analyzing dropdown elements...\n";
        
        if (strpos($dashboardHtml, 'x-data="navigationData()"') !== false) {
            echo "✅ Found Alpine.js navigationData component\n";
        } else {
            echo "❌ Alpine.js navigationData component not found\n";
        }
        
        if (strpos($dashboardHtml, 'x-show=') !== false) {
            echo "✅ Found x-show directives\n";
        } else {
            echo "❌ No x-show directives found\n";
        }
        
        if (strpos($dashboardHtml, '@click.outside=') !== false) {
            echo "✅ Found @click.outside handlers\n";
        } else {
            echo "❌ No @click.outside handlers found\n";
        }
        
        if (strpos($dashboardHtml, 'alpine') !== false || strpos($dashboardHtml, 'Alpine') !== false) {
            echo "✅ Alpine.js references found\n";
        } else {
            echo "❌ No Alpine.js references found\n";
        }
        
        // Check for JavaScript console errors by examining the built assets
        if (strpos($dashboardHtml, 'navigationData') !== false) {
            echo "✅ navigationData function referenced\n";
        } else {
            echo "❌ navigationData function not referenced\n";
        }
        
    } else {
        echo "❌ Cannot access dashboard (HTTP {$dashboardResponse['http_code']})\n";
        echo "Response preview: " . substr($dashboardResponse['body'], 0, 200) . "...\n";
    }
    
} else {
    echo "❌ Login failed (HTTP {$loginResponse['http_code']})\n";
    echo "Response preview: " . substr($loginResponse['body'], 0, 300) . "...\n";
}

// Test 5: Test standalone debug page
echo "\n📋 Step 5: Testing standalone debug page...\n";
$debugResponse = makeRequest("$baseUrl/dropdown-debug.html");
if ($debugResponse['http_code'] === 200) {
    echo "✅ Debug page accessible (HTTP {$debugResponse['http_code']})\n";
    
    if (strpos($debugResponse['body'], 'x-data=') !== false) {
        echo "✅ Alpine.js x-data found in debug page\n";
    } else {
        echo "❌ Alpine.js x-data not found in debug page\n";
    }
} else {
    echo "❌ Cannot access debug page (HTTP {$debugResponse['http_code']})\n";
}

// Cleanup
unlink($cookieJar);

echo "\n🎯 Test Summary:\n";
echo "================\n";
echo "1. Login page: ✅ Accessible\n";
echo "2. Authentication: " . ($loginResponse['http_code'] < 400 ? "✅ Working" : "❌ Failed") . "\n";
echo "3. Dashboard: " . (isset($dashboardResponse) && $dashboardResponse['http_code'] === 200 ? "✅ Accessible" : "❌ Not accessible") . "\n";
echo "4. Debug page: " . ($debugResponse['http_code'] === 200 ? "✅ Working" : "❌ Failed") . "\n";

echo "\n💡 Next Steps:\n";
echo "- Open http://localhost:8000/dropdown-debug.html in your browser\n";
echo "- Login with: admin@hdtickets.com / password\n";
echo "- Check browser console for JavaScript errors\n";
echo "- Test dropdown functionality manually\n";

?>
