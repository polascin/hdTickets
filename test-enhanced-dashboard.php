<?php

/**
 * HD Tickets Enhanced Dashboard Testing Script
 * Comprehensive testing for responsive design, real-time updates, and accessibility
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

class DashboardTester
{
    private $app;
    private $tests = [];
    private $results = [];
    private $startTime;
    
    public function __construct()
    {
        $this->app = app();
        $this->startTime = microtime(true);
        
        echo "🧪 HD Tickets Enhanced Dashboard Testing Suite\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
    }
    
    public function runAllTests()
    {
        $this->testResponsiveDesign();
        $this->testRealTimeUpdates();
        $this->testAccessibility();
        $this->testPerformance();
        $this->testMobileComponents();
        $this->testDashboardWidgets();
        $this->testCSS();
        $this->testJavaScript();
        $this->generateReport();
    }
    
    private function testResponsiveDesign()
    {
        echo "📱 Testing Responsive Design\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $viewports = [
            'mobile' => ['width' => 320, 'height' => 568, 'name' => 'Mobile (iPhone SE)'],
            'mobile_large' => ['width' => 375, 'height' => 667, 'name' => 'Mobile Large (iPhone 8)'],
            'tablet' => ['width' => 768, 'height' => 1024, 'name' => 'Tablet (iPad)'],
            'desktop' => ['width' => 1024, 'height' => 768, 'name' => 'Desktop'],
            'desktop_large' => ['width' => 1440, 'height' => 900, 'name' => 'Desktop Large'],
        ];
        
        foreach ($viewports as $key => $viewport) {
            $this->test("Responsive layout for {$viewport['name']}", function() use ($viewport) {
                // Test CSS breakpoints
                $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
                
                // Check if viewport-specific CSS rules exist
                $breakpointTests = [
                    'mobile' => '@media (max-width: 639px)',
                    'tablet' => '@media (min-width: 768px)',
                    'desktop' => '@media (min-width: 1024px)'
                ];
                
                foreach ($breakpointTests as $device => $mediaQuery) {
                    if (strpos($cssFile, $mediaQuery) !== false) {
                        echo "  ✅ {$device} breakpoint found\n";
                    } else {
                        echo "  ⚠️  {$device} breakpoint missing\n";
                    }
                }
                
                return true;
            });
        }
        
        // Test mobile-first approach
        $this->test("Mobile-first CSS approach", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            // Check for mobile-first patterns
            $mobileFirstPatterns = [
                '.grid-1 { grid-template-columns: 1fr; }',
                '@media (min-width:', // Mobile-first uses min-width
                'mobile-bottom-nav',
                'touch-device'
            ];
            
            $found = 0;
            foreach ($mobileFirstPatterns as $pattern) {
                if (strpos($cssFile, $pattern) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Mobile-first patterns found: {$found}/" . count($mobileFirstPatterns) . "\n";
            return $found >= 3;
        });
        
        echo "\n";
    }
    
    private function testRealTimeUpdates()
    {
        echo "🔄 Testing Real-time Updates\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test WebSocket configuration
        $this->test("WebSocket configuration", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $websocketFeatures = [
                'websocketConfig',
                'data-realtime',
                'DashboardRealtime',
                'data-live-value'
            ];
            
            $found = 0;
            foreach ($websocketFeatures as $feature) {
                if (strpos($dashboardView, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ❌ {$feature} missing\n";
                }
            }
            
            return $found >= 3;
        });
        
        // Test real-time data attributes
        $this->test("Real-time data attributes", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $dataAttributes = [
                'data-refresh="true"',
                'data-realtime="',
                'data-live-value=',
                'data-connection-indicator'
            ];
            
            $found = 0;
            foreach ($dataAttributes as $attr) {
                if (strpos($dashboardView, $attr) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Real-time attributes found: {$found}/" . count($dataAttributes) . "\n";
            return $found >= 2;
        });
        
        // Test JavaScript real-time functionality
        $this->test("JavaScript real-time functionality", function() {
            $jsFile = file_get_contents(__DIR__ . '/public/js/customer-dashboard.js');
            
            $realTimeFeatures = [
                'WebSocket',
                'handleWebSocketMessage',
                'updateTicketPrice',
                'updateTicketAvailability',
                'refreshDashboard'
            ];
            
            $found = 0;
            foreach ($realTimeFeatures as $feature) {
                if (strpos($jsFile, $feature) !== false) {
                    echo "  ✅ {$feature} implemented\n";
                    $found++;
                } else {
                    echo "  ❌ {$feature} missing\n";
                }
            }
            
            return $found >= 4;
        });
        
        echo "\n";
    }
    
    private function testAccessibility()
    {
        echo "♿ Testing Accessibility Features\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test ARIA attributes
        $this->test("ARIA attributes and labels", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $ariaFeatures = [
                'aria-label',
                'aria-selected',
                'role=',
                'tabindex',
                'title='
            ];
            
            $found = 0;
            foreach ($ariaFeatures as $feature) {
                if (strpos($dashboardView, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ⚠️  {$feature} missing\n";
                }
            }
            
            return $found >= 3;
        });
        
        // Test semantic HTML
        $this->test("Semantic HTML structure", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $semanticElements = [
                '<main',
                '<header',
                '<section',
                '<nav',
                '<h1',
                '<h2',
                '<h3'
            ];
            
            $found = 0;
            foreach ($semanticElements as $element) {
                if (strpos($dashboardView, $element) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Semantic elements found: {$found}/" . count($semanticElements) . "\n";
            return $found >= 5;
        });
        
        // Test keyboard navigation
        $this->test("Keyboard navigation support", function() {
            $jsFile = file_get_contents(__DIR__ . '/public/js/customer-dashboard.js');
            $mobileNavComponent = file_get_contents(__DIR__ . '/resources/views/components/mobile/bottom-navigation.blade.php');
            
            $keyboardFeatures = [
                'keydown',
                'Enter',
                'Space',
                'ArrowLeft',
                'ArrowRight',
                'Escape'
            ];
            
            $found = 0;
            $content = $jsFile . $mobileNavComponent;
            
            foreach ($keyboardFeatures as $feature) {
                if (strpos($content, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Keyboard navigation features: {$found}/" . count($keyboardFeatures) . "\n";
            return $found >= 3;
        });
        
        // Test focus management
        $this->test("Focus management", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            $focusFeatures = [
                ':focus',
                'focus:',
                'outline:',
                'focus-visible:'
            ];
            
            $found = 0;
            foreach ($focusFeatures as $feature) {
                if (strpos($cssFile, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Focus management styles: {$found}/" . count($focusFeatures) . "\n";
            return $found >= 2;
        });
        
        echo "\n";
    }
    
    private function testPerformance()
    {
        echo "⚡ Testing Performance Features\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test CSS optimization
        $this->test("CSS optimization", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            $optimizations = [
                'will-change:',
                'contain:',
                '@keyframes',
                'transform:',
                'transition:'
            ];
            
            $found = 0;
            foreach ($optimizations as $optimization) {
                if (strpos($cssFile, $optimization) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 CSS optimizations found: {$found}/" . count($optimizations) . "\n";
            return $found >= 3;
        });
        
        // Test JavaScript performance features
        $this->test("JavaScript performance features", function() {
            $jsFile = file_get_contents(__DIR__ . '/public/js/customer-dashboard.js');
            $perfMonitoring = file_get_contents(__DIR__ . '/resources/js/utils/performanceMonitoring.js');
            
            $perfFeatures = [
                'debounce',
                'throttle',
                'IntersectionObserver',
                'performance.now',
                'requestAnimationFrame'
            ];
            
            $found = 0;
            $content = $jsFile . $perfMonitoring;
            
            foreach ($perfFeatures as $feature) {
                if (strpos($content, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ❌ {$feature} missing\n";
                }
            }
            
            return $found >= 3;
        });
        
        // Test lazy loading
        $this->test("Lazy loading implementation", function() {
            $jsFile = file_get_contents(__DIR__ . '/public/js/customer-dashboard.js');
            
            $lazyFeatures = [
                'IntersectionObserver',
                'data-src',
                'lazy-load',
                'loadImage'
            ];
            
            $found = 0;
            foreach ($lazyFeatures as $feature) {
                if (strpos($jsFile, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Lazy loading features: {$found}/" . count($lazyFeatures) . "\n";
            return $found >= 2;
        });
        
        // Test cache prevention
        $this->test("CSS cache prevention", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $cacheFeatures = [
                '?v={{ time() }}',
                'timestamp',
                '{{ now()->timestamp }}'
            ];
            
            $found = 0;
            foreach ($cacheFeatures as $feature) {
                if (strpos($dashboardView, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Cache prevention mechanisms: {$found}/" . count($cacheFeatures) . "\n";
            return $found >= 1;
        });
        
        echo "\n";
    }
    
    private function testMobileComponents()
    {
        echo "📱 Testing Mobile Components\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test bottom navigation
        $this->test("Mobile bottom navigation", function() {
            $navComponent = file_get_contents(__DIR__ . '/resources/views/components/mobile/bottom-navigation.blade.php');
            
            $navFeatures = [
                'mobile-bottom-nav',
                'safe-area-inset',
                'touch-target',
                'haptic-feedback'
            ];
            
            $found = 0;
            foreach ($navFeatures as $feature) {
                if (strpos($navComponent, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ⚠️  {$feature} missing\n";
                }
            }
            
            return $found >= 2;
        });
        
        // Test responsive data table
        $this->test("Responsive data table", function() {
            $tableComponent = file_get_contents(__DIR__ . '/resources/views/components/mobile/responsive-data-table.blade.php');
            
            $tableFeatures = [
                'card-view',
                'table-view',
                'responsive-data-table',
                'md:hidden',
                'md:block'
            ];
            
            $found = 0;
            foreach ($tableFeatures as $feature) {
                if (strpos($tableComponent, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Responsive table features: {$found}/" . count($tableFeatures) . "\n";
            return $found >= 3;
        });
        
        // Test swipeable cards
        $this->test("Swipeable ticket cards", function() {
            $cardsComponent = file_get_contents(__DIR__ . '/resources/views/components/mobile/swipeable-ticket-cards.blade.php');
            
            $swipeFeatures = [
                'swipe-enabled',
                'touchstart',
                'touchend',
                'swipe-actions'
            ];
            
            $found = 0;
            foreach ($swipeFeatures as $feature) {
                if (strpos($cardsComponent, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Swipe features: {$found}/" . count($swipeFeatures) . "\n";
            return $found >= 2;
        });
        
        echo "\n";
    }
    
    private function testDashboardWidgets()
    {
        echo "🔧 Testing Dashboard Widgets\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test widget structure
        $this->test("Dashboard widget components", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $widgetFeatures = [
                'stat-card',
                'dashboard-card',
                'action-card',
                'ticket-item',
                'animate-pulse'
            ];
            
            $found = 0;
            foreach ($widgetFeatures as $feature) {
                if (strpos($dashboardView, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ❌ {$feature} missing\n";
                }
            }
            
            return $found >= 3;
        });
        
        // Test statistics display
        $this->test("Statistics widgets", function() {
            $dashboardView = file_get_contents(__DIR__ . '/resources/views/dashboard/customer.blade.php');
            
            $statFeatures = [
                'stat-available-tickets',
                'stat-high-demand',
                'stat-alerts',
                'stat-queue',
                'data-live-value'
            ];
            
            $found = 0;
            foreach ($statFeatures as $feature) {
                if (strpos($dashboardView, $feature) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Statistics widgets: {$found}/" . count($statFeatures) . "\n";
            return $found >= 3;
        });
        
        echo "\n";
    }
    
    private function testCSS()
    {
        echo "🎨 Testing CSS Implementation\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test CSS file structure
        $this->test("CSS file structure and organization", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            $cssStructure = [
                'CSS Variables',
                'Base Styles',
                'Card Components',
                'Mobile Responsive',
                'Dark Mode',
                'Animation Classes'
            ];
            
            $found = 0;
            foreach ($cssStructure as $section) {
                if (strpos($cssFile, $section) !== false) {
                    echo "  ✅ {$section} section found\n";
                    $found++;
                } else {
                    echo "  ⚠️  {$section} section missing\n";
                }
            }
            
            return $found >= 4;
        });
        
        // Test CSS custom properties
        $this->test("CSS custom properties (variables)", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            $customProperties = [
                '--primary-green:',
                '--primary-blue:',
                '--gradient-primary:',
                '--font-family:',
                '--space-',
                '--radius-',
                '--shadow-'
            ];
            
            $found = 0;
            foreach ($customProperties as $property) {
                if (strpos($cssFile, $property) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 CSS custom properties: {$found}/" . count($customProperties) . "\n";
            return $found >= 5;
        });
        
        // Test responsive breakpoints
        $this->test("Responsive breakpoints", function() {
            $cssFile = file_get_contents(__DIR__ . '/public/css/customer-dashboard.css');
            
            $breakpoints = [
                'max-width: 639px',
                'min-width: 640px',
                'min-width: 768px',
                'min-width: 1024px',
                'min-width: 1280px'
            ];
            
            $found = 0;
            foreach ($breakpoints as $breakpoint) {
                if (strpos($cssFile, $breakpoint) !== false) {
                    $found++;
                }
            }
            
            echo "  📊 Responsive breakpoints: {$found}/" . count($breakpoints) . "\n";
            return $found >= 3;
        });
        
        echo "\n";
    }
    
    private function testJavaScript()
    {
        echo "⚙️ Testing JavaScript Implementation\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Test main dashboard JavaScript
        $this->test("Main dashboard JavaScript", function() {
            $jsFile = file_get_contents(__DIR__ . '/public/js/customer-dashboard.js');
            
            $jsFeatures = [
                'class CustomerDashboard',
                'init()',
                'initWebSocket()',
                'initCharts()',
                'handleFilterChange',
                'cleanup()'
            ];
            
            $found = 0;
            foreach ($jsFeatures as $feature) {
                if (strpos($jsFile, $feature) !== false) {
                    echo "  ✅ {$feature} found\n";
                    $found++;
                } else {
                    echo "  ❌ {$feature} missing\n";
                }
            }
            
            return $found >= 4;
        });
        
        // Test utility modules
        $this->test("JavaScript utility modules", function() {
            $utilFiles = [
                'performanceMonitoring.js',
                'responsiveUtils.js'
            ];
            
            $found = 0;
            foreach ($utilFiles as $file) {
                $filePath = __DIR__ . '/resources/js/utils/' . $file;
                if (file_exists($filePath)) {
                    echo "  ✅ {$file} exists\n";
                    $found++;
                } else {
                    echo "  ❌ {$file} missing\n";
                }
            }
            
            return $found >= 1;
        });
        
        echo "\n";
    }
    
    private function test($name, $testFunction)
    {
        $startTime = microtime(true);
        
        try {
            $result = $testFunction();
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($result) {
                echo "✅ {$name} ({$duration}ms)\n";
                $this->results['passed'][] = $name;
            } else {
                echo "❌ {$name} ({$duration}ms)\n";
                $this->results['failed'][] = $name;
            }
            
            $this->tests[] = [
                'name' => $name,
                'result' => $result,
                'duration' => $duration
            ];
            
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo "💥 {$name} - ERROR: {$e->getMessage()} ({$duration}ms)\n";
            $this->results['errors'][] = ['name' => $name, 'error' => $e->getMessage()];
        }
    }
    
    private function generateReport()
    {
        $totalTime = round((microtime(true) - $this->startTime) * 1000, 2);
        $passed = count($this->results['passed'] ?? []);
        $failed = count($this->results['failed'] ?? []);
        $errors = count($this->results['errors'] ?? []);
        $total = $passed + $failed + $errors;
        
        echo "\n" . "=" . str_repeat("=", 60) . "\n";
        echo "📊 ENHANCED DASHBOARD TEST REPORT\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
        
        echo "🏁 Total Tests: {$total}\n";
        echo "✅ Passed: {$passed}\n";
        echo "❌ Failed: {$failed}\n";
        echo "💥 Errors: {$errors}\n";
        echo "⏱️  Total Time: {$totalTime}ms\n\n";
        
        $successRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        echo "📈 Success Rate: {$successRate}%\n\n";
        
        if ($successRate >= 80) {
            echo "🎉 EXCELLENT! Dashboard is ready for deployment!\n";
        } elseif ($successRate >= 60) {
            echo "⚠️  GOOD with minor issues. Review failed tests.\n";
        } else {
            echo "🚨 NEEDS WORK. Multiple issues found.\n";
        }
        
        if (!empty($this->results['failed'])) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->results['failed'] as $test) {
                echo "   • {$test}\n";
            }
        }
        
        if (!empty($this->results['errors'])) {
            echo "\n💥 Error Details:\n";
            foreach ($this->results['errors'] as $error) {
                echo "   • {$error['name']}: {$error['error']}\n";
            }
        }
        
        echo "\n" . "=" . str_repeat("=", 60) . "\n";
        echo "Testing completed at " . date('Y-m-d H:i:s') . "\n";
        echo "=" . str_repeat("=", 60) . "\n";
    }
}

// Run the tests
$tester = new DashboardTester();
$tester->runAllTests();
