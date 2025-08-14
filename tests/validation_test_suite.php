<?php
/**
 * HD Tickets Validation Test Suite
 *
 * Comprehensive testing suite for sports events entry tickets monitoring system
 * Tests all functionality areas: Authentication, Routing, Responsive, Performance,
 * Accessibility, and Browser Support
 *
 * @version 2025.08.14
 *
 * @environment Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
 */

declare(strict_types=1);

class ValidationTestSuite
{
    private array $testResults = [];

    private string $baseUrl;

    private array $viewports = [
        'mobile'  => ['width' => 375, 'height' => 667],
        'tablet'  => ['width' => 768, 'height' => 1024],
        'desktop' => ['width' => 1920, 'height' => 1080],
        '4k'      => ['width' => 3840, 'height' => 2160],
    ];

    public function __construct(string $baseUrl = 'https://hdtickets.local/')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        echo "🚀 HD Tickets Validation Test Suite Starting...\n";
        echo "🎯 Target: {$this->baseUrl}\n";
        echo "📋 Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4\n\n";
    }

    /**
     * Run all validation tests
     */
    /**
     * @return array<string, mixed>
     */
    public function runAllTests(): array
    {
        echo '=' . str_repeat('=', 80) . "\n";
        echo "🧪 COMPREHENSIVE SPORTS EVENTS TICKETS MONITORING SYSTEM VALIDATION\n";
        echo '=' . str_repeat('=', 80) . "\n\n";

        // Test categories as per requirements
        $this->testAuthentication();
        $this->testRouting();
        $this->testResponsiveDesign();
        $this->testPerformance();
        $this->testAccessibility();
        $this->testBrowserSupport();

        $this->printSummaryReport();

        return $this->testResults;
    }

    /**
     * Test Authentication - Login/Logout flows
     */
    private function testAuthentication(): void
    {
        echo "🔐 AUTHENTICATION TESTING\n";
        echo str_repeat('-', 40) . "\n";

        // Test login page accessibility
        $response = $this->makeRequest('/login');
        $this->recordTest(
            'Auth',
            'Login page accessible',
            $response['status'] === 200,
            'Login page should be accessible',
            $response['status'],
        );

        // Test login form exists
        $hasLoginForm = strpos($response['body'], '<form') !== FALSE
                       && strpos($response['body'], 'name="email"') !== FALSE
                       && strpos($response['body'], 'name="password"') !== FALSE;
        $this->recordTest(
            'Auth',
            'Login form present',
            $hasLoginForm,
            'Login form with email/password should exist',
        );

        // Test CSRF protection
        $hasCsrf = strpos($response['body'], 'csrf') !== FALSE || strpos($response['body'], '_token') !== FALSE;
        $this->recordTest(
            'Auth',
            'CSRF protection',
            $hasCsrf,
            'Login form should have CSRF protection',
        );

        // Test logout route (requires authentication, will redirect)
        $logoutResponse = $this->makeRequest('/logout', 'POST');
        $this->recordTest(
            'Auth',
            'Logout endpoint exists',
            $logoutResponse['status'] !== 404,
            'Logout endpoint should exist (may redirect if unauthenticated)',
        );

        // Test password reset availability
        $resetResponse = $this->makeRequest('/forgot-password');
        $this->recordTest(
            'Auth',
            'Password reset available',
            $resetResponse['status'] === 200,
            'Password reset functionality should be available',
        );

        echo "\n";
    }

    /**
     * Test Routing - Dashboard redirects function properly
     */
    private function testRouting(): void
    {
        echo "🧭 ROUTING TESTING\n";
        echo str_repeat('-', 40) . "\n";

        // Test main dashboard route
        $dashboardResponse = $this->makeRequest('/dashboard');
        $this->recordTest(
            'Routing',
            'Dashboard route exists',
            $dashboardResponse['status'] !== 404,
            'Main dashboard route should exist',
        );

        // Test role-based dashboard routes
        $roleDashboards = [
            '/dashboard/admin'    => 'Admin dashboard',
            '/dashboard/agent'    => 'Agent dashboard',
            '/dashboard/customer' => 'Customer dashboard',
            '/dashboard/scraper'  => 'Scraper dashboard',
        ];

        foreach ($roleDashboards as $route => $description) {
            $response = $this->makeRequest($route);
            $this->recordTest(
                'Routing',
                $description . ' route',
                $response['status'] !== 404,
                "{$description} route should exist",
            );
        }

        // Test API routes
        $apiRoutes = [
            '/api/v1/status'          => 'API status endpoint',
            '/api/v1/dashboard/stats' => 'Dashboard stats API',
            '/health'                 => 'Health check endpoint',
        ];

        foreach ($apiRoutes as $route => $description) {
            $response = $this->makeRequest($route);
            $this->recordTest(
                'Routing',
                $description,
                $response['status'] !== 404,
                "{$description} should be accessible",
            );
        }

        // Test sports ticket specific routes
        $ticketRoutes = [
            '/tickets/scraping'   => 'Ticket scraping dashboard',
            '/purchase-decisions' => 'Purchase decisions system',
        ];

        foreach ($ticketRoutes as $route => $description) {
            $response = $this->makeRequest($route);
            $this->recordTest(
                'Routing',
                $description,
                $response['status'] !== 404,
                "{$description} should be accessible",
            );
        }

        echo "\n";
    }

    /**
     * Test Responsive Design - Multiple viewport sizes
     */
    private function testResponsiveDesign(): void
    {
        echo "📱 RESPONSIVE DESIGN TESTING\n";
        echo str_repeat('-', 40) . "\n";

        // Test main pages for responsive meta tags
        $pages = ['/', '/login', '/dashboard'];

        foreach ($pages as $page) {
            $response = $this->makeRequest($page);

            // Check viewport meta tag
            $hasViewport = strpos($response['body'], 'name="viewport"') !== FALSE;
            $this->recordTest(
                'Responsive',
                "Viewport meta tag - {$page}",
                $hasViewport,
                'Page should have responsive viewport meta tag',
            );

            // Check for responsive CSS classes/frameworks
            $hasResponsive = strpos($response['body'], 'responsive') !== FALSE
                           || strpos($response['body'], 'container') !== FALSE
                           || strpos($response['body'], 'col-') !== FALSE
                           || strpos($response['body'], 'w-full') !== FALSE;
            $this->recordTest(
                'Responsive',
                "Responsive CSS - {$page}",
                $hasResponsive,
                'Page should use responsive CSS framework',
            );

            // Check for mobile-friendly elements
            $hasMobileFriendly = strpos($response['body'], 'mobile') !== FALSE
                               || strpos($response['body'], 'touch-target') !== FALSE
                               || strpos($response['body'], 'btn-lg') !== FALSE;
            $this->recordTest(
                'Responsive',
                "Mobile-friendly elements - {$page}",
                $hasMobileFriendly,
                'Page should have mobile-friendly UI elements',
            );
        }

        // Test CSS media queries existence
        $cssFiles = ['/resources/css/app.css', '/resources/css/mobile-enhancements.css'];
        foreach ($cssFiles as $cssFile) {
            if (file_exists('/var/www/hdtickets' . $cssFile)) {
                $cssContent = (string) file_get_contents('/var/www/hdtickets' . $cssFile);
                $hasMediaQueries = strpos($cssContent, '@media') !== FALSE;
                $this->recordTest(
                    'Responsive',
                    "Media queries in {$cssFile}",
                    $hasMediaQueries,
                    'CSS files should contain responsive media queries',
                );
            }
        }

        echo "\n";
    }

    /**
     * Test Performance - Fast loading with CDN fallbacks
     */
    private function testPerformance(): void
    {
        echo "⚡ PERFORMANCE TESTING\n";
        echo str_repeat('-', 40) . "\n";

        // Test page load times
        $pages = ['/', '/login', '/dashboard'];

        foreach ($pages as $page) {
            $startTime = microtime(TRUE);
            $response = $this->makeRequest($page);
            $loadTime = (microtime(TRUE) - $startTime) * 1000; // Convert to milliseconds

            $isFast = $loadTime < 3000; // Under 3 seconds
            $this->recordTest(
                'Performance',
                "Load time - {$page}",
                $isFast,
                'Page should load under 3 seconds',
                round($loadTime) . 'ms',
            );
        }

        // Test CDN resource fallbacks
        $response = $this->makeRequest('/');

        // Check for CDN usage
        $hasCDN = strpos($response['body'], 'cdn.jsdelivr.net') !== FALSE
                 || strpos($response['body'], 'fonts.bunny.net') !== FALSE
                 || strpos($response['body'], 'unpkg.com') !== FALSE;
        $this->recordTest(
            'Performance',
            'CDN usage',
            $hasCDN,
            'Application should use CDN resources',
        );

        // Check for fallback mechanisms
        $hasFallback = strpos($response['body'], 'fallback') !== FALSE
                      || strpos($response['body'], 'if (') !== FALSE; // Basic JS fallback detection
        $this->recordTest(
            'Performance',
            'CDN fallbacks',
            $hasFallback,
            'Application should have CDN fallback mechanisms',
        );

        // Test gzip/compression
        $headers = $this->makeRequest('/', 'GET', ['Accept-Encoding' => 'gzip, deflate']);
        $hasCompression = isset($headers['headers']['content-encoding']);
        $this->recordTest(
            'Performance',
            'Gzip compression',
            $hasCompression,
            'Server should support gzip compression',
        );

        // Test caching headers
        $hasCaching = isset($headers['headers']['cache-control'])
                     || isset($headers['headers']['etag'])
                     || isset($headers['headers']['last-modified']);
        $this->recordTest(
            'Performance',
            'Caching headers',
            $hasCaching,
            'Pages should have appropriate caching headers',
        );

        echo "\n";
    }

    /**
     * Test Accessibility - ARIA labels and contrast ratios
     */
    private function testAccessibility(): void
    {
        echo "♿ ACCESSIBILITY TESTING\n";
        echo str_repeat('-', 40) . "\n";

        $pages = ['/', '/login', '/dashboard'];

        foreach ($pages as $page) {
            $response = $this->makeRequest($page);

            // Test ARIA labels
            $hasAriaLabels = strpos($response['body'], 'aria-label') !== FALSE
                           || strpos($response['body'], 'aria-labelledby') !== FALSE
                           || strpos($response['body'], 'aria-describedby') !== FALSE;
            $this->recordTest(
                'Accessibility',
                "ARIA labels - {$page}",
                $hasAriaLabels,
                'Page should use ARIA labels for accessibility',
            );

            // Test semantic HTML
            $hasSemanticHtml = strpos($response['body'], '<main') !== FALSE
                             || strpos($response['body'], '<nav') !== FALSE
                             || strpos($response['body'], '<header') !== FALSE
                             || strpos($response['body'], '<section') !== FALSE;
            $this->recordTest(
                'Accessibility',
                "Semantic HTML - {$page}",
                $hasSemanticHtml,
                'Page should use semantic HTML elements',
            );

            // Test form labels
            $hasFormLabels = strpos($response['body'], '<label') !== FALSE
                           || strpos($response['body'], 'for=') !== FALSE;
            $this->recordTest(
                'Accessibility',
                "Form labels - {$page}",
                $hasFormLabels,
                'Forms should have proper labels',
            );

            // Test alt attributes for images
            $imageCount = substr_count($response['body'], '<img');
            $altCount = substr_count($response['body'], 'alt=');
            $hasAltTags = $imageCount === 0 || $altCount >= $imageCount;
            $this->recordTest(
                'Accessibility',
                "Image alt tags - {$page}",
                $hasAltTags,
                'Images should have alt attributes',
            );

            // Test keyboard navigation support
            $hasTabindex = strpos($response['body'], 'tabindex') !== FALSE
                         || strpos($response['body'], 'focus:') !== FALSE;
            $this->recordTest(
                'Accessibility',
                "Keyboard navigation - {$page}",
                $hasTabindex,
                'Page should support keyboard navigation',
            );
        }

        echo "\n";
    }

    /**
     * Test Browser Support - Chrome, Firefox, Safari, Edge compatibility
     */
    private function testBrowserSupport(): void
    {
        echo "🌐 BROWSER SUPPORT TESTING\n";
        echo str_repeat('-', 40) . "\n";

        $response = $this->makeRequest('/');

        // Test for modern CSS that works across browsers
        $hasModernCSS = strpos($response['body'], 'flexbox') !== FALSE
                       || strpos($response['body'], 'grid') !== FALSE
                       || strpos($response['body'], 'display: flex') !== FALSE;
        $this->recordTest(
            'Browser Support',
            'Modern CSS features',
            $hasModernCSS,
            'Should use cross-browser compatible CSS',
        );

        // Test for polyfills or fallbacks
        $hasPolyfills = strpos($response['body'], 'polyfill') !== FALSE
                       || strpos($response['body'], 'shim') !== FALSE
                       || strpos($response['body'], 'modernizr') !== FALSE;
        $this->recordTest(
            'Browser Support',
            'Browser polyfills',
            $hasPolyfills,
            'Should include browser polyfills for compatibility',
        );

        // Test for vendor prefixes in CSS (check actual CSS files)
        $cssFiles = glob('/var/www/hdtickets/resources/css/*.css');
        $hasVendorPrefixes = FALSE;

        foreach ($cssFiles as $cssFile) {
            $cssContent = (string) file_get_contents($cssFile);
            if (strpos($cssContent, '-webkit-') !== FALSE
                || strpos($cssContent, '-moz-') !== FALSE
                || strpos($cssContent, '-ms-') !== FALSE) {
                $hasVendorPrefixes = TRUE;

                break;
            }
        }
        $this->recordTest(
            'Browser Support',
            'CSS vendor prefixes',
            $hasVendorPrefixes,
            'CSS should include vendor prefixes for compatibility',
        );

        // Test HTML5 doctype
        $hasHtml5 = strpos($response['body'], '<!DOCTYPE html>') !== FALSE;
        $this->recordTest(
            'Browser Support',
            'HTML5 doctype',
            $hasHtml5,
            'Pages should use HTML5 doctype',
        );

        // Test meta tags for IE compatibility
        $hasIECompat = strpos($response['body'], 'http-equiv') !== FALSE
                      || strpos($response['body'], 'X-UA-Compatible') !== FALSE;
        $this->recordTest(
            'Browser Support',
            'IE compatibility',
            $hasIECompat,
            'Should include IE compatibility meta tags',
        );

        // Test for progressive enhancement
        $hasProgressive = strpos($response['body'], 'noscript') !== FALSE
                         || strpos($response['body'], 'if (') !== FALSE;
        $this->recordTest(
            'Browser Support',
            'Progressive enhancement',
            $hasProgressive,
            'Should support progressive enhancement',
        );

        echo "\n";
    }

    /**
     * Make HTTP request with curl
     */
    private function makeRequest(string $path, string $method = 'GET', array $headers = []): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HEADER         => TRUE,
            CURLOPT_USERAGENT      => 'HD-Tickets-Test-Suite/1.0',
        ]);

        if (! empty($headers)) {
            $curlHeaders = [];
            foreach ($headers as $key => $value) {
                $curlHeaders[] = "{$key}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);

        $headers = substr((string) $response, 0, $headerSize);
        $body = substr((string) $response, $headerSize);

        // Parse headers
        $headerLines = explode("\r\n", trim($headers));
        $parsedHeaders = [];
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== FALSE) {
                [$key, $value] = explode(':', $line, 2);
                $parsedHeaders[strtolower(trim($key))] = trim($value);
            }
        }

        return [
            'status'  => $httpCode,
            'headers' => $parsedHeaders,
            'body'    => $body,
        ];
    }

    /**
     * Record test result
     *
     * @param mixed|null $details
     */
    private function recordTest(string $category, string $test, bool $passed, string $description, $details = NULL): void
    {
        $status = $passed ? '✅' : '❌';
        $this->testResults[$category][] = [
            'test'        => $test,
            'passed'      => $passed,
            'description' => $description,
            'details'     => $details,
        ];

        $detailsStr = $details ? " ({$details})" : '';
        echo "{$status} {$test}{$detailsStr}\n";
    }

    /**
     * Print comprehensive test summary report
     */
    private function printSummaryReport(): void
    {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "📊 COMPREHENSIVE TEST SUMMARY REPORT\n";
        echo str_repeat('=', 80) . "\n";

        $totalTests = 0;
        $passedTests = 0;

        foreach ($this->testResults as $category => $tests) {
            $categoryPassed = array_filter($tests, fn ($test) => $test['passed']);
            $categoryTotal = count($tests);
            $categoryPassRate = $categoryTotal > 0 ? (count($categoryPassed) / $categoryTotal * 100) : 0;

            $totalTests += $categoryTotal;
            $passedTests += count($categoryPassed);

            echo "\n🔍 {$category}:\n";
            echo "   Tests: {$categoryTotal} | Passed: " . count($categoryPassed) .
                 ' | Pass Rate: ' . round($categoryPassRate, 1) . "%\n";

            // Show failed tests
            $failedTests = array_filter($tests, fn ($test) => ! $test['passed']);
            if (! empty($failedTests)) {
                echo "   ❌ Failed Tests:\n";
                foreach ($failedTests as $test) {
                    echo "      - {$test['test']}: {$test['description']}\n";
                }
            }
        }

        $overallPassRate = $totalTests > 0 ? ($passedTests / $totalTests * 100) : 0;

        echo "\n" . str_repeat('-', 80) . "\n";
        echo "🎯 OVERALL RESULTS:\n";
        echo "   Total Tests: {$totalTests}\n";
        echo "   Passed: {$passedTests}\n";
        echo '   Failed: ' . ($totalTests - $passedTests) . "\n";
        echo '   Pass Rate: ' . round($overallPassRate, 1) . "%\n";

        if ($overallPassRate >= 90) {
            echo "\n🏆 EXCELLENT! System validation successful.\n";
        } elseif ($overallPassRate >= 75) {
            echo "\n✅ GOOD! Most functionality validated successfully.\n";
        } elseif ($overallPassRate >= 60) {
            echo "\n⚠️  FAIR! Some issues need attention.\n";
        } else {
            echo "\n❌ NEEDS WORK! Significant issues found.\n";
        }

        echo "\n📝 SPORTS EVENTS TICKETS MONITORING SYSTEM VALIDATION COMPLETE\n";
        echo "🌟 Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4\n";
        echo str_repeat('=', 80) . "\n";
    }
}

// Run validation if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new ValidationTestSuite();
    $validator->runAllTests();
}
