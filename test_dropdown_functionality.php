<?php
/**
 * Comprehensive Dropdown Functionality Test Suite
 * Tests all dropdown implementations in the HD Tickets application
 */

require_once __DIR__ . '/vendor/autoload.php';

class DropdownTest 
{
    private $results = [];
    private $basePath;
    
    public function __construct()
    {
        $this->basePath = __DIR__;
    }
    
    public function runAllTests()
    {
        echo "🔍 Starting comprehensive dropdown functionality tests...\n\n";
        
        // Test navigation dropdowns
        $this->testNavigationDropdowns();
        
        // Test Alpine.js dropdown components
        $this->testAlpineDropdowns();
        
        // Test Bootstrap dropdowns
        $this->testBootstrapDropdowns();
        
        // Test CSS dropdown styling
        $this->testDropdownCSS();
        
        // Test accessibility
        $this->testDropdownAccessibility();
        
        // Test JavaScript functionality
        $this->testDropdownJavaScript();
        
        // Test responsive behavior
        $this->testResponsiveDropdowns();
        
        // Generate report
        $this->generateReport();
    }
    
    private function testNavigationDropdowns()
    {
        echo "📱 Testing Navigation Dropdowns...\n";
        
        $navFile = $this->basePath . '/resources/views/layouts/navigation.blade.php';
        
        if (!file_exists($navFile)) {
            $this->addResult('Navigation File', 'FAIL', 'Navigation blade file not found');
            return;
        }
        
        $content = file_get_contents($navFile);
        
        // Test admin dropdown implementation
        $adminDropdownTests = [
            'Admin dropdown trigger' => strpos($content, 'toggleAdminDropdown()') !== false,
            'Admin dropdown state' => strpos($content, 'adminDropdownOpen') !== false,
            'Admin dropdown x-show' => strpos($content, 'x-show="adminDropdownOpen"') !== false,
            'Admin dropdown click outside' => strpos($content, '@click.outside="adminDropdownOpen = false"') !== false,
            'Admin dropdown accessibility' => strpos($content, 'aria-expanded="adminDropdownOpen"') !== false,
            'Admin dropdown role' => strpos($content, 'role="menu"') !== false,
        ];
        
        foreach ($adminDropdownTests as $test => $result) {
            $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Missing implementation');
        }
        
        // Test profile dropdown implementation
        $profileDropdownTests = [
            'Profile dropdown trigger' => strpos($content, 'toggleProfileDropdown()') !== false,
            'Profile dropdown state' => strpos($content, 'profileDropdownOpen') !== false,
            'Profile dropdown x-show' => strpos($content, 'x-show="profileDropdownOpen"') !== false,
            'Profile dropdown click outside' => strpos($content, '@click.outside="profileDropdownOpen = false"') !== false,
            'Profile dropdown accessibility' => strpos($content, 'aria-expanded="profileDropdownOpen"') !== false,
        ];
        
        foreach ($profileDropdownTests as $test => $result) {
            $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Missing implementation');
        }
        
        // Test transition animations
        $transitionTests = [
            'Dropdown transitions' => strpos($content, 'x-transition:enter') !== false,
            'Transition classes' => strpos($content, 'dropdown-enter') !== false,
            'Animation timing' => strpos($content, 'duration') !== false,
        ];
        
        foreach ($transitionTests as $test => $result) {
            $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Missing transition implementation');
        }
    }
    
    private function testAlpineDropdowns()
    {
        echo "🏔️ Testing Alpine.js Dropdowns...\n";
        
        // Test generic dropdown component
        $dropdownFile = $this->basePath . '/resources/views/components/dropdown.blade.php';
        
        if (file_exists($dropdownFile)) {
            $content = file_get_contents($dropdownFile);
            
            $alpineTests = [
                'Alpine data binding' => strpos($content, 'x-data="{ open: false }"') !== false,
                'Click outside handling' => strpos($content, '@click.outside="open = false"') !== false,
                'Close directive' => strpos($content, '@close.stop="open = false"') !== false,
                'Show/hide logic' => strpos($content, 'x-show="open"') !== false,
                'Transition effects' => strpos($content, 'x-transition') !== false,
            ];
            
            foreach ($alpineTests as $test => $result) {
                $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Alpine.js feature missing');
            }
        } else {
            $this->addResult('Generic Dropdown Component', 'FAIL', 'Component file not found');
        }
        
        // Test UI dropdown component
        $uiDropdownFile = $this->basePath . '/resources/views/components/ui/dropdown.blade.php';
        
        if (file_exists($uiDropdownFile)) {
            $content = file_get_contents($uiDropdownFile);
            
            $uiTests = [
                'HD dropdown classes' => strpos($content, 'hd-dropdown') !== false,
                'Alignment options' => strpos($content, 'hd-dropdown--right') !== false,
                'Trigger functionality' => strpos($content, 'hd-dropdown__trigger') !== false,
                'Menu container' => strpos($content, 'hd-dropdown__menu') !== false,
            ];
            
            foreach ($uiTests as $test => $result) {
                $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'UI dropdown feature missing');
            }
        }
    }
    
    private function testBootstrapDropdowns()
    {
        echo "🅱️ Testing Bootstrap Dropdowns...\n";
        
        // Test admin views using Bootstrap dropdowns
        $adminFiles = [
            'reports' => $this->basePath . '/resources/views/admin/reports/index.blade.php',
            'user-profile' => $this->basePath . '/resources/views/admin/user-profile.blade.php',
        ];
        
        foreach ($adminFiles as $name => $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                $bootstrapTests = [
                    "Bootstrap toggle ($name)" => strpos($content, 'data-bs-toggle="dropdown"') !== false,
                    "Dropdown menu ($name)" => strpos($content, 'dropdown-menu') !== false,
                    "Dropdown items ($name)" => strpos($content, 'dropdown-item') !== false,
                ];
                
                foreach ($bootstrapTests as $test => $result) {
                    $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Bootstrap dropdown missing');
                }
            } else {
                $this->addResult("Bootstrap dropdown in $name", 'FAIL', 'File not found');
            }
        }
    }
    
    private function testDropdownCSS()
    {
        echo "🎨 Testing Dropdown CSS...\n";
        
        $cssFiles = [
            'navigation-enhanced.css' => $this->basePath . '/public/css/navigation-enhanced.css',
            'navigation-dashboard-fixes.css' => $this->basePath . '/public/css/navigation-dashboard-fixes.css',
        ];
        
        foreach ($cssFiles as $name => $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                $cssTests = [
                    "Dropdown positioning ($name)" => strpos($content, '.nav-dropdown') !== false,
                    "Dropdown transitions ($name)" => strpos($content, 'transition') !== false,
                    "Z-index handling ($name)" => strpos($content, 'z-index') !== false,
                    "Dropdown visibility ($name)" => strpos($content, 'opacity') !== false,
                ];
                
                foreach ($cssTests as $test => $result) {
                    $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'CSS property missing');
                }
            } else {
                $this->addResult("CSS file $name", 'FAIL', 'File not found');
            }
        }
    }
    
    private function testDropdownAccessibility()
    {
        echo "♿ Testing Dropdown Accessibility...\n";
        
        $navFile = $this->basePath . '/resources/views/layouts/navigation.blade.php';
        
        if (file_exists($navFile)) {
            $content = file_get_contents($navFile);
            
            $accessibilityTests = [
                'ARIA expanded attribute' => strpos($content, 'aria-expanded') !== false,
                'ARIA haspopup attribute' => strpos($content, 'aria-haspopup') !== false,
                'Menu role attribute' => strpos($content, 'role="menu"') !== false,
                'Menuitem roles' => strpos($content, 'role="menuitem"') !== false,
                'Aria labels' => strpos($content, 'aria-label') !== false,
                'Aria hidden for icons' => strpos($content, 'aria-hidden="true"') !== false,
                'Keyboard navigation support' => strpos($content, 'tabindex') !== false,
            ];
            
            foreach ($accessibilityTests as $test => $result) {
                $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Accessibility feature missing');
            }
        }
    }
    
    private function testDropdownJavaScript()
    {
        echo "⚡ Testing Dropdown JavaScript...\n";
        
        $jsFile = $this->basePath . '/resources/js/components/navigation.js';
        
        if (file_exists($jsFile)) {
            $content = file_get_contents($jsFile);
            
            $jsTests = [
                'Navigation data function' => strpos($content, 'window.navigationData') !== false,
                'Dropdown state management' => strpos($content, 'adminDropdownOpen') !== false,
                'Toggle functions' => strpos($content, 'toggleAdminDropdown') !== false,
                'Keyboard navigation' => strpos($content, 'handleArrowNavigation') !== false,
                'Focus management' => strpos($content, 'getFocusableElements') !== false,
                'Escape key handling' => strpos($content, 'setupEscapeKey') !== false,
                'Click outside detection' => strpos($content, 'setupClickOutside') !== false,
                'Accessibility announcements' => strpos($content, 'announce') !== false,
            ];
            
            foreach ($jsTests as $test => $result) {
                $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'JavaScript feature missing');
            }
        } else {
            $this->addResult('Navigation JavaScript', 'FAIL', 'JavaScript file not found');
        }
    }
    
    private function testResponsiveDropdowns()
    {
        echo "📱 Testing Responsive Dropdown Behavior...\n";
        
        $cssFile = $this->basePath . '/public/css/navigation-enhanced.css';
        
        if (file_exists($cssFile)) {
            $content = file_get_contents($cssFile);
            
            $responsiveTests = [
                'Mobile breakpoints' => strpos($content, '@media') !== false,
                'Mobile nav styling' => strpos($content, 'mobile-nav') !== false,
                'Touch target sizing' => strpos($content, 'min-height: 44px') !== false,
                'Mobile menu transitions' => strpos($content, 'mobile-nav-menu') !== false,
            ];
            
            foreach ($responsiveTests as $test => $result) {
                $this->addResult($test, $result ? 'PASS' : 'FAIL', $result ? '' : 'Responsive feature missing');
            }
        }
    }
    
    private function addResult($test, $status, $note = '')
    {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'note' => $note
        ];
        
        $icon = $status === 'PASS' ? '✅' : '❌';
        echo "  $icon $test: $status";
        if ($note) {
            echo " ($note)";
        }
        echo "\n";
    }
    
    private function generateReport()
    {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "📊 DROPDOWN FUNCTIONALITY TEST RESULTS\n";
        echo str_repeat('=', 80) . "\n\n";
        
        $total = count($this->results);
        $passed = count(array_filter($this->results, function($r) { return $r['status'] === 'PASS'; }));
        $failed = $total - $passed;
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed ✅\n";
        echo "Failed: $failed ❌\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 2) . "%\n\n";
        
        if ($failed > 0) {
            echo "FAILED TESTS:\n";
            echo str_repeat('-', 40) . "\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "❌ {$result['test']}";
                    if ($result['note']) {
                        echo " - {$result['note']}";
                    }
                    echo "\n";
                }
            }
        }
        
        echo "\n🏁 Dropdown functionality test completed.\n";
    }
}

// Run the tests
$tester = new DropdownTest();
$tester->runAllTests();