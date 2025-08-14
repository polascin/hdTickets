#!/usr/bin/env node

/**
 * HD Tickets Mobile Optimization Test Suite
 * This script tests various mobile optimization features
 */

const puppeteer = require('puppeteer');
const path = require('path');

class MobileOptimizationTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = {
            passed: 0,
            failed: 0,
            tests: []
        };
    }

    async init() {
        this.browser = await puppeteer.launch({
            headless: false, // Set to true for CI/CD
            defaultViewport: {
                width: 375,
                height: 667,
                deviceScaleFactor: 2,
                isMobile: true,
                hasTouch: true
            },
            args: ['--enable-touch-events']
        });
        
        this.page = await this.browser.newPage();
        await this.page.emulate(puppeteer.devices['iPhone 6']);
        
        console.log('🚀 Mobile optimization tester initialized');
    }

    async runTest(name, testFunction) {
        console.log(`\n📱 Running test: ${name}`);
        
        try {
            await testFunction();
            this.testResults.passed++;
            this.testResults.tests.push({ name, status: 'PASSED' });
            console.log(`✅ ${name} - PASSED`);
        } catch (error) {
            this.testResults.failed++;
            this.testResults.tests.push({ name, status: 'FAILED', error: error.message });
            console.log(`❌ ${name} - FAILED: ${error.message}`);
        }
    }

    async testViewportConfiguration() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test viewport meta tag
        const viewport = await this.page.$eval('meta[name="viewport"]', el => el.getAttribute('content'));
        if (!viewport.includes('width=device-width') || !viewport.includes('initial-scale=1')) {
            throw new Error('Viewport meta tag not properly configured');
        }
        
        // Test viewport height fix
        const vhValue = await this.page.evaluate(() => 
            getComputedStyle(document.documentElement).getPropertyValue('--vh')
        );
        if (!vhValue || vhValue === '1vh') {
            throw new Error('Viewport height fix not applied');
        }
    }

    async testTouchTargets() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test minimum touch target sizes
        const buttons = await this.page.$$('button, .touch-target');
        for (const button of buttons) {
            const boundingBox = await button.boundingBox();
            if (boundingBox.width < 44 || boundingBox.height < 44) {
                throw new Error(`Touch target too small: ${boundingBox.width}x${boundingBox.height}`);
            }
        }
        
        // Test touch feedback
        await this.page.tap('.touch-target');
        const hasActiveFeedback = await this.page.evaluate(() => {
            return document.querySelector('.touch-target:active') !== null;
        });
    }

    async testFormOptimization() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test input font size (should be 16px to prevent zoom on iOS)
        const inputFontSize = await this.page.$eval('input[type="email"]', el => 
            getComputedStyle(el).fontSize
        );
        if (inputFontSize !== '16px') {
            throw new Error(`Input font size should be 16px, got ${inputFontSize}`);
        }
        
        // Test inputmode attributes
        const emailInput = await this.page.$eval('input[type="email"]', el => 
            el.getAttribute('inputmode')
        );
        if (emailInput !== 'email') {
            throw new Error('Email input missing inputmode="email"');
        }
        
        const telInput = await this.page.$eval('input[type="tel"]', el => 
            el.getAttribute('inputmode')
        );
        if (telInput !== 'tel') {
            throw new Error('Tel input missing inputmode="tel"');
        }
        
        // Test form submission and error scrolling
        await this.page.click('button[type="submit"]');
        await this.page.waitForTimeout(500);
        
        const scrollPosition = await this.page.evaluate(() => window.pageYOffset);
        if (scrollPosition === 0) {
            // Should scroll to first invalid field
            console.log('⚠️  Form error scrolling may need verification');
        }
    }

    async testResponsiveBreakpoints() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test mobile breakpoint
        await this.page.setViewport({ width: 320, height: 568 });
        await this.page.waitForTimeout(100);
        
        const isMobileClass = await this.page.evaluate(() => 
            document.documentElement.classList.contains('mobile')
        );
        if (!isMobileClass) {
            throw new Error('Mobile class not applied at mobile breakpoint');
        }
        
        // Test tablet breakpoint
        await this.page.setViewport({ width: 768, height: 1024 });
        await this.page.waitForTimeout(100);
        
        const isDesktopClass = await this.page.evaluate(() => 
            document.documentElement.classList.contains('desktop')
        );
        if (!isDesktopClass) {
            throw new Error('Desktop class not applied at tablet breakpoint');
        }
    }

    async testOrientationHandling() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test portrait orientation
        await this.page.setViewport({ width: 375, height: 667 });
        await this.page.waitForTimeout(100);
        
        const isPortrait = await this.page.evaluate(() => 
            document.documentElement.classList.contains('portrait')
        );
        if (!isPortrait) {
            throw new Error('Portrait class not applied in portrait orientation');
        }
        
        // Test landscape orientation
        await this.page.setViewport({ width: 667, height: 375 });
        await this.page.waitForTimeout(100);
        
        const isLandscape = await this.page.evaluate(() => 
            document.documentElement.classList.contains('landscape')
        );
        if (!isLandscape) {
            throw new Error('Landscape class not applied in landscape orientation');
        }
    }

    async testAccessibility() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test skip navigation
        const skipLink = await this.page.$('.skip-navigation');
        if (skipLink) {
            // Test focus visibility
            await skipLink.focus();
            const isVisible = await this.page.evaluate(el => {
                const styles = getComputedStyle(el);
                return styles.top !== '-40px';
            }, skipLink);
            if (!isVisible) {
                throw new Error('Skip navigation not visible on focus');
            }
        }
        
        // Test ARIA attributes
        const inputs = await this.page.$$('input[required]');
        for (const input of inputs) {
            const ariaRequired = await input.evaluate(el => el.getAttribute('aria-required'));
            const hasValidAriaInvalid = await input.evaluate(el => 
                el.hasAttribute('aria-invalid')
            );
            // These should be set by the validation system
        }
        
        // Test focus indicators
        await this.page.focus('button');
        const focusOutlineWidth = await this.page.evaluate(() => {
            const focused = document.activeElement;
            return getComputedStyle(focused).outlineWidth;
        });
        if (focusOutlineWidth === '0px') {
            throw new Error('No focus outline on focused element');
        }
    }

    async testPerformanceOptimizations() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test image lazy loading
        const images = await this.page.$$('img');
        for (const img of images) {
            const loading = await img.evaluate(el => el.getAttribute('loading'));
            if (loading !== 'lazy') {
                console.log('⚠️  Image missing loading="lazy" attribute');
            }
        }
        
        // Test CSS classes for progressive enhancement
        const hasJSEnabled = await this.page.evaluate(() => 
            document.documentElement.classList.contains('js-enabled')
        );
        if (!hasJSEnabled) {
            throw new Error('Progressive enhancement classes not applied');
        }
        
        // Test hardware acceleration classes
        const acceleratedElements = await this.page.$$('.mobile-accelerated, .mobile-scroll');
        if (acceleratedElements.length === 0) {
            console.log('⚠️  No hardware accelerated elements found');
        }
    }

    async testConnectionHandling() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test offline simulation
        await this.page.setOfflineMode(true);
        await this.page.waitForTimeout(100);
        
        const offlineClass = await this.page.evaluate(() => 
            document.body.classList.contains('offline')
        );
        if (!offlineClass) {
            throw new Error('Offline class not applied when offline');
        }
        
        // Test online restoration
        await this.page.setOfflineMode(false);
        await this.page.waitForTimeout(100);
        
        const onlineClass = await this.page.evaluate(() => 
            document.body.classList.contains('online')
        );
        if (!onlineClass) {
            throw new Error('Online class not applied when back online');
        }
    }

    async testMobileNotifications() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test notification creation
        await this.page.click('button[onclick*="testNotification"]');
        await this.page.waitForTimeout(500);
        
        const notification = await this.page.$('.mobile-notification');
        if (!notification) {
            throw new Error('Mobile notification not created');
        }
        
        // Test notification visibility
        const isVisible = await notification.evaluate(el => 
            el.classList.contains('show')
        );
        if (!isVisible) {
            throw new Error('Mobile notification not visible');
        }
        
        // Test notification auto-removal
        await this.page.waitForTimeout(3500);
        const stillExists = await this.page.$('.mobile-notification');
        if (stillExists) {
            console.log('⚠️  Notification may not be auto-removing');
        }
    }

    async testSwipeGestures() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test swipe detection setup
        const swipeContainer = await this.page.$('.swipe-container');
        if (!swipeContainer) {
            throw new Error('No swipe containers found');
        }
        
        // Simulate swipe gesture
        const boundingBox = await swipeContainer.boundingBox();
        const startX = boundingBox.x + boundingBox.width * 0.8;
        const endX = boundingBox.x + boundingBox.width * 0.2;
        const y = boundingBox.y + boundingBox.height / 2;
        
        await this.page.touchscreen.tap(startX, y);
        await this.page.waitForTimeout(50);
        await this.page.touchscreen.tap(endX, y);
        
        // This would need additional event listener testing
        console.log('✓ Swipe gesture simulation completed');
    }

    async testSafeAreaHandling() {
        await this.page.goto('file://' + path.join(__dirname, 'mobile-demo.html'));
        
        // Test safe area classes
        const safeAreaElements = await this.page.$$('.safe-area, .safe-area-top, .safe-area-bottom');
        if (safeAreaElements.length === 0) {
            console.log('⚠️  No safe area elements found');
            return;
        }
        
        // Test CSS custom properties for safe areas
        const hasSafeAreaSupport = await this.page.evaluate(() => {
            return CSS.supports('padding', 'env(safe-area-inset-top)');
        });
        
        if (!hasSafeAreaSupport) {
            console.log('⚠️  Browser may not support safe area insets');
        }
    }

    async runAllTests() {
        await this.init();
        
        console.log('\n🔧 Starting HD Tickets Mobile Optimization Tests\n');
        
        await this.runTest('Viewport Configuration', () => this.testViewportConfiguration());
        await this.runTest('Touch Targets', () => this.testTouchTargets());
        await this.runTest('Form Optimization', () => this.testFormOptimization());
        await this.runTest('Responsive Breakpoints', () => this.testResponsiveBreakpoints());
        await this.runTest('Orientation Handling', () => this.testOrientationHandling());
        await this.runTest('Accessibility Features', () => this.testAccessibility());
        await this.runTest('Performance Optimizations', () => this.testPerformanceOptimizations());
        await this.runTest('Connection Handling', () => this.testConnectionHandling());
        await this.runTest('Mobile Notifications', () => this.testMobileNotifications());
        await this.runTest('Swipe Gestures', () => this.testSwipeGestures());
        await this.runTest('Safe Area Handling', () => this.testSafeAreaHandling());
        
        await this.cleanup();
        this.printResults();
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }

    printResults() {
        console.log('\n📊 Test Results Summary');
        console.log('========================');
        console.log(`✅ Passed: ${this.testResults.passed}`);
        console.log(`❌ Failed: ${this.testResults.failed}`);
        console.log(`📈 Success Rate: ${Math.round((this.testResults.passed / (this.testResults.passed + this.testResults.failed)) * 100)}%`);
        
        if (this.testResults.failed > 0) {
            console.log('\n❌ Failed Tests:');
            this.testResults.tests.filter(t => t.status === 'FAILED').forEach(test => {
                console.log(`  - ${test.name}: ${test.error}`);
            });
        }
        
        console.log('\n🎉 Mobile optimization testing completed!');
        
        // Exit with error code if tests failed
        process.exit(this.testResults.failed > 0 ? 1 : 0);
    }
}

// Manual testing checklist
function printManualTestingChecklist() {
    console.log('\n📋 Manual Testing Checklist');
    console.log('============================');
    console.log('Please manually verify these items on actual mobile devices:');
    console.log('');
    console.log('📱 iOS Testing:');
    console.log('  □ No zoom on input focus');
    console.log('  □ Safe area insets respected');
    console.log('  □ Smooth scrolling performance');
    console.log('  □ Haptic feedback works (if supported)');
    console.log('  □ Safari address bar handling');
    console.log('');
    console.log('🤖 Android Testing:');
    console.log('  □ Virtual keyboard detection');
    console.log('  □ Touch ripple effects');
    console.log('  □ Chrome address bar handling');
    console.log('  □ Back button behavior');
    console.log('');
    console.log('🌐 Cross-browser Testing:');
    console.log('  □ Safari on iOS');
    console.log('  □ Chrome on Android');
    console.log('  □ Firefox mobile');
    console.log('  □ Edge mobile');
    console.log('');
    console.log('🔍 Performance Testing:');
    console.log('  □ Fast initial paint (<1.6s)');
    console.log('  □ Smooth 60fps animations');
    console.log('  □ Low memory usage');
    console.log('  □ Offline functionality');
    console.log('');
    console.log('♿ Accessibility Testing:');
    console.log('  □ Screen reader compatibility');
    console.log('  □ Voice control navigation');
    console.log('  □ High contrast mode');
    console.log('  □ Reduced motion preferences');
    console.log('');
}

// Run tests if this script is executed directly
if (require.main === module) {
    const tester = new MobileOptimizationTester();
    
    // Check if puppeteer is available
    try {
        require('puppeteer');
        tester.runAllTests().catch(console.error);
    } catch (error) {
        console.log('⚠️  Puppeteer not found. Install it with: npm install puppeteer');
        console.log('Running manual testing checklist instead...\n');
        printManualTestingChecklist();
    }
}

module.exports = MobileOptimizationTester;
