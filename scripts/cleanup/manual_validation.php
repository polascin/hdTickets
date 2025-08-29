<?php
/**
 * HD Tickets Manual Validation Script
 * 
 * Manual validation of critical system components for sports events
 * entry tickets monitoring system functionality
 * 
 * @version 2025.08.14
 * @environment Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
 */

declare(strict_types=1);

echo "🚀 HD TICKETS MANUAL VALIDATION\n";
echo "=" . str_repeat("=", 60) . "\n";
echo "🎯 Sports Events Entry Tickets Monitoring System\n";
echo "🌟 Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4\n\n";

$results = [
    'Authentication' => [],
    'Routing' => [],
    'Responsive' => [],
    'Performance' => [], 
    'Accessibility' => [],
    'Browser Support' => []
];

// Test 1: Authentication - File Structure Validation
echo "🔐 AUTHENTICATION TESTING\n";
echo str_repeat("-", 40) . "\n";

$authFiles = [
    '/var/www/hdtickets/resources/views/auth/login.blade.php' => 'Login view exists',
    '/var/www/hdtickets/app/Http/Controllers/Auth/LoginController.php' => 'Login controller exists', 
    '/var/www/hdtickets/routes/auth.php' => 'Auth routes file exists'
];

foreach ($authFiles as $file => $description) {
    $exists = file_exists($file);
    echo ($exists ? "✅" : "❌") . " $description\n";
    $results['Authentication'][] = ['test' => $description, 'passed' => $exists];
}

// Check login view contains form elements
if (file_exists('/var/www/hdtickets/resources/views/auth/login.blade.php')) {
    $loginContent = file_get_contents('/var/www/hdtickets/resources/views/auth/login.blade.php');
    $hasEmailField = strpos($loginContent, 'name="email"') !== false;
    $hasPasswordField = strpos($loginContent, 'name="password"') !== false;
    $hasCsrf = strpos($loginContent, '@csrf') !== false || strpos($loginContent, 'csrf') !== false;
    
    echo ($hasEmailField ? "✅" : "❌") . " Login form has email field\n";
    echo ($hasPasswordField ? "✅" : "❌") . " Login form has password field\n"; 
    echo ($hasCsrf ? "✅" : "❌") . " Login form has CSRF protection\n";
    
    $results['Authentication'][] = ['test' => 'Email field exists', 'passed' => $hasEmailField];
    $results['Authentication'][] = ['test' => 'Password field exists', 'passed' => $hasPasswordField];
    $results['Authentication'][] = ['test' => 'CSRF protection', 'passed' => $hasCsrf];
}
echo "\n";

// Test 2: Routing - Route Files Validation
echo "🧭 ROUTING TESTING\n";
echo str_repeat("-", 40) . "\n";

$routeFiles = [
    '/var/www/hdtickets/routes/web.php' => 'Web routes file exists',
    '/var/www/hdtickets/routes/api.php' => 'API routes file exists',
    '/var/www/hdtickets/routes/admin.php' => 'Admin routes file exists'
];

foreach ($routeFiles as $file => $description) {
    $exists = file_exists($file);
    echo ($exists ? "✅" : "❌") . " $description\n";
    $results['Routing'][] = ['test' => $description, 'passed' => $exists];
}

// Check web routes contains dashboard routes
if (file_exists('/var/www/hdtickets/routes/web.php')) {
    $webRoutes = file_get_contents('/var/www/hdtickets/routes/web.php');
    $hasDashboard = strpos($webRoutes, '/dashboard') !== false;
    $hasLogin = strpos($webRoutes, '/login') !== false;
    $hasRoleRoutes = strpos($webRoutes, 'agent') !== false && strpos($webRoutes, 'admin') !== false;
    
    echo ($hasDashboard ? "✅" : "❌") . " Dashboard routes defined\n";
    echo ($hasLogin ? "✅" : "❌") . " Login routes defined\n";
    echo ($hasRoleRoutes ? "✅" : "❌") . " Role-based routes defined\n";
    
    $results['Routing'][] = ['test' => 'Dashboard routes', 'passed' => $hasDashboard];
    $results['Routing'][] = ['test' => 'Login routes', 'passed' => $hasLogin];
    $results['Routing'][] = ['test' => 'Role-based routes', 'passed' => $hasRoleRoutes];
}
echo "\n";

// Test 3: Responsive Design - CSS Files Validation
echo "📱 RESPONSIVE DESIGN TESTING\n";
echo str_repeat("-", 40) . "\n";

$responsiveFiles = [
    '/var/www/hdtickets/resources/css/app.css' => 'Main CSS file exists',
    '/var/www/hdtickets/resources/css/mobile-enhancements.css' => 'Mobile enhancements CSS exists'
];

foreach ($responsiveFiles as $file => $description) {
    $exists = file_exists($file);
    echo ($exists ? "✅" : "❌") . " $description\n";
    $results['Responsive'][] = ['test' => $description, 'passed' => $exists];
}

// Check CSS files for responsive features
$cssFiles = glob('/var/www/hdtickets/resources/css/*.css');
$hasMediaQueries = false;
$hasMobileClasses = false;
$hasViewportUnits = false;

foreach ($cssFiles as $cssFile) {
    $cssContent = file_get_contents($cssFile);
    if (strpos($cssContent, '@media') !== false) $hasMediaQueries = true;
    if (strpos($cssContent, 'mobile') !== false || strpos($cssContent, 'touch-target') !== false) $hasMobileClasses = true;
    if (strpos($cssContent, 'vh') !== false || strpos($cssContent, 'vw') !== false) $hasViewportUnits = true;
}

echo ($hasMediaQueries ? "✅" : "❌") . " CSS contains media queries\n";
echo ($hasMobileClasses ? "✅" : "❌") . " CSS contains mobile-friendly classes\n";
echo ($hasViewportUnits ? "✅" : "❌") . " CSS uses viewport units\n";

$results['Responsive'][] = ['test' => 'Media queries', 'passed' => $hasMediaQueries];
$results['Responsive'][] = ['test' => 'Mobile classes', 'passed' => $hasMobileClasses];
$results['Responsive'][] = ['test' => 'Viewport units', 'passed' => $hasViewportUnits];
echo "\n";

// Test 4: Performance - Configuration and Assets
echo "⚡ PERFORMANCE TESTING\n";
echo str_repeat("-", 40) . "\n";

// Check for CDN configurations in layout files
$layoutFiles = glob('/var/www/hdtickets/resources/views/layouts/*.blade.php');
$hasCdnReferences = false;
$hasGzipConfig = false;
$hasCacheConfig = false;

foreach ($layoutFiles as $layoutFile) {
    $layoutContent = file_get_contents($layoutFile);
    if (strpos($layoutContent, 'cdn.') !== false || strpos($layoutContent, 'jsdelivr') !== false) {
        $hasCdnReferences = true;
    }
}

// Check Apache config for gzip
$apacheConfigs = glob('/etc/apache2/sites-*/*.conf');
foreach ($apacheConfigs as $config) {
    if (strpos($config, 'hdtickets') !== false && is_readable($config)) {
        $configContent = file_get_contents($config);
        if (strpos($configContent, 'mod_deflate') !== false || strpos($configContent, 'gzip') !== false) {
            $hasGzipConfig = true;
        }
        if (strpos($configContent, 'Cache-Control') !== false || strpos($configContent, 'Expires') !== false) {
            $hasCacheConfig = true;
        }
    }
}

echo ($hasCdnReferences ? "✅" : "❌") . " CDN references found in layouts\n";
echo ($hasGzipConfig ? "✅" : "❌") . " Gzip configuration found\n";
echo ($hasCacheConfig ? "✅" : "❌") . " Cache headers configuration found\n";

// Check for asset compilation
$hasCompiledAssets = file_exists('/var/www/hdtickets/public/build/manifest.json');
$hasMinifiedAssets = false;
if ($hasCompiledAssets) {
    $manifest = json_decode(file_get_contents('/var/www/hdtickets/public/build/manifest.json'), true);
    $hasMinifiedAssets = !empty($manifest);
}

echo ($hasCompiledAssets ? "✅" : "❌") . " Compiled assets found\n";
echo ($hasMinifiedAssets ? "✅" : "❌") . " Asset manifest found\n";

$results['Performance'][] = ['test' => 'CDN references', 'passed' => $hasCdnReferences];
$results['Performance'][] = ['test' => 'Gzip config', 'passed' => $hasGzipConfig];
$results['Performance'][] = ['test' => 'Cache config', 'passed' => $hasCacheConfig];
$results['Performance'][] = ['test' => 'Compiled assets', 'passed' => $hasCompiledAssets];
echo "\n";

// Test 5: Accessibility - HTML and CSS Structure
echo "♿ ACCESSIBILITY TESTING\n";
echo str_repeat("-", 40) . "\n";

$viewFiles = glob('/var/www/hdtickets/resources/views/**/*.blade.php');
$hasAriaLabels = false;
$hasSemanticHtml = false;
$hasProperLabels = false;

foreach (array_slice($viewFiles, 0, 10) as $viewFile) { // Check first 10 files
    $viewContent = file_get_contents($viewFile);
    if (strpos($viewContent, 'aria-') !== false) $hasAriaLabels = true;
    if (strpos($viewContent, '<main') !== false || strpos($viewContent, '<nav') !== false || 
        strpos($viewContent, '<header') !== false) $hasSemanticHtml = true;
    if (strpos($viewContent, '<label') !== false && strpos($viewContent, 'for=') !== false) $hasProperLabels = true;
}

echo ($hasAriaLabels ? "✅" : "❌") . " ARIA labels found in templates\n";
echo ($hasSemanticHtml ? "✅" : "❌") . " Semantic HTML elements found\n";
echo ($hasProperLabels ? "✅" : "❌") . " Proper form labels found\n";

$results['Accessibility'][] = ['test' => 'ARIA labels', 'passed' => $hasAriaLabels];
$results['Accessibility'][] = ['test' => 'Semantic HTML', 'passed' => $hasSemanticHtml];
$results['Accessibility'][] = ['test' => 'Form labels', 'passed' => $hasProperLabels];
echo "\n";

// Test 6: Browser Support - CSS and JS Features
echo "🌐 BROWSER SUPPORT TESTING\n";
echo str_repeat("-", 40) . "\n";

$hasVendorPrefixes = false;
$hasPolyfills = false;
$hasProgressiveEnhancement = false;

// Check CSS for vendor prefixes
foreach ($cssFiles as $cssFile) {
    $cssContent = file_get_contents($cssFile);
    if (strpos($cssContent, '-webkit-') !== false || strpos($cssContent, '-moz-') !== false) {
        $hasVendorPrefixes = true;
    }
}

// Check JS files for polyfills
$jsFiles = glob('/var/www/hdtickets/resources/js/*.js');
foreach ($jsFiles as $jsFile) {
    $jsContent = file_get_contents($jsFile);
    if (strpos($jsContent, 'polyfill') !== false || strpos($jsContent, 'fallback') !== false) {
        $hasPolyfills = true;
    }
}

// Check layouts for progressive enhancement
foreach ($layoutFiles as $layoutFile) {
    $layoutContent = file_get_contents($layoutFile);
    if (strpos($layoutContent, 'noscript') !== false) {
        $hasProgressiveEnhancement = true;
    }
}

echo ($hasVendorPrefixes ? "✅" : "❌") . " CSS vendor prefixes found\n";
echo ($hasPolyfills ? "✅" : "❌") . " JavaScript polyfills found\n";
echo ($hasProgressiveEnhancement ? "✅" : "❌") . " Progressive enhancement found\n";

$results['Browser Support'][] = ['test' => 'Vendor prefixes', 'passed' => $hasVendorPrefixes];
$results['Browser Support'][] = ['test' => 'Polyfills', 'passed' => $hasPolyfills];
$results['Browser Support'][] = ['test' => 'Progressive enhancement', 'passed' => $hasProgressiveEnhancement];
echo "\n";

// Summary Report
echo str_repeat("=", 60) . "\n";
echo "📊 VALIDATION SUMMARY REPORT\n";
echo str_repeat("=", 60) . "\n";

$totalTests = 0;
$passedTests = 0;

foreach ($results as $category => $tests) {
    $categoryPassed = array_filter($tests, fn($test) => $test['passed']);
    $categoryTotal = count($tests);
    $passRate = $categoryTotal > 0 ? (count($categoryPassed) / $categoryTotal * 100) : 0;
    
    $totalTests += $categoryTotal;
    $passedTests += count($categoryPassed);
    
    echo "\n🔍 $category: " . count($categoryPassed) . "/$categoryTotal (" . round($passRate, 1) . "%)\n";
}

$overallPassRate = $totalTests > 0 ? ($passedTests / $totalTests * 100) : 0;

echo "\n" . str_repeat("-", 60) . "\n";
echo "🎯 OVERALL RESULTS:\n";
echo "   Total Tests: $totalTests\n";
echo "   Passed: $passedTests\n";
echo "   Failed: " . ($totalTests - $passedTests) . "\n";
echo "   Pass Rate: " . round($overallPassRate, 1) . "%\n";

if ($overallPassRate >= 90) {
    echo "\n🏆 EXCELLENT! System validation successful.\n";
} elseif ($overallPassRate >= 75) {
    echo "\n✅ GOOD! Most functionality validated successfully.\n";
} elseif ($overallPassRate >= 60) {
    echo "\n⚠️  FAIR! Some issues need attention.\n";  
} else {
    echo "\n❌ NEEDS WORK! Significant issues found.\n";
}

echo "\n📝 HD TICKETS MANUAL VALIDATION COMPLETE\n";
echo "🌟 Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4\n";
echo str_repeat("=", 60) . "\n";

// Additional Specific Tests for Sports Events System
echo "\n🏟️  SPORTS EVENTS SPECIFIC VALIDATION\n";
echo str_repeat("-", 60) . "\n";

// Check for sports-specific controllers and models
$sportsFiles = [
    '/var/www/hdtickets/app/Http/Controllers/TicketScrapingController.php' => 'Ticket scraping controller',
    '/var/www/hdtickets/app/Http/Controllers/PurchaseDecisionController.php' => 'Purchase decision controller',
    '/var/www/hdtickets/app/Http/Controllers/Api/TicketmasterController.php' => 'Ticketmaster API controller',
    '/var/www/hdtickets/app/Http/Controllers/Api/StubHubController.php' => 'StubHub API controller'
];

$sportsSpecificPassed = 0;
$sportsSpecificTotal = count($sportsFiles);

foreach ($sportsFiles as $file => $description) {
    $exists = file_exists($file);
    echo ($exists ? "✅" : "❌") . " $description\n";
    if ($exists) $sportsSpecificPassed++;
}

echo "\n🎯 Sports Events System: $sportsSpecificPassed/$sportsSpecificTotal (" . 
     round(($sportsSpecificPassed / $sportsSpecificTotal * 100), 1) . "%)\n";

echo "\n✨ VALIDATION NOTES:\n";
echo "- This is a Sports Events Entry Tickets monitoring system (NOT helpdesk)\n";
echo "- System includes Ticketmaster, StubHub, Viagogo, and TickPick integration\n";
echo "- Role-based access: Admin, Agent, Customer, Scraper\n";
echo "- Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4\n";
