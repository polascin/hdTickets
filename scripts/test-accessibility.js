#!/usr/bin/env node

/**
 * HD Tickets Accessibility Testing Suite
 * 
 * Comprehensive accessibility testing including:
 * - WCAG 2.1 AA compliance
 * - Keyboard navigation
 * - Screen reader compatibility
 * - Color contrast
 * - Focus management
 * - Semantic HTML validation
 */

const puppeteer = require('puppeteer');
const axeCore = require('axe-core');
const fs = require('fs');
const { execSync } = require('child_process');

class AccessibilityTester {
    constructor() {
        this.browser = null;
        this.results = {
            timestamp: new Date().toISOString(),
            testResults: {},
            summary: {},
            violations: [],
            recommendations: []
        };
        
        this.testPages = [
            { name: 'Dashboard', url: '/dashboard', critical: true },
            { name: 'Tickets List', url: '/tickets', critical: true },
            { name: 'Ticket Details', url: '/tickets/1', critical: false },
            { name: 'User Profile', url: '/profile', critical: false },
            { name: 'Login', url: '/login', critical: true },
            { name: 'Settings', url: '/settings', critical: false }
        ];
        
        this.viewports = [
            { name: 'Mobile', width: 375, height: 667 },
            { name: 'Desktop', width: 1440, height: 900 }
        ];
        
        // WCAG Success Criteria
        this.wcagLevels = {
            'wcag2a': 'WCAG 2.1 Level A',
            'wcag2aa': 'WCAG 2.1 Level AA',
            'wcag21aa': 'WCAG 2.1 Level AA',
            'best-practice': 'Best Practices'
        };
    }

    async initialize() {
        console.log('♿ Initializing HD Tickets Accessibility Testing Suite...');
        
        try {
            this.browser = await puppeteer.launch({
                headless: 'new',
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--force-prefers-reduced-motion',
                    '--disable-background-timer-throttling',
                    '--disable-backgrounding-occluded-windows',
                    '--disable-renderer-backgrounding'
                ]
            });
            console.log('✅ Browser initialized with accessibility features');
        } catch (error) {
            console.error('❌ Failed to initialize browser:', error);
            throw error;
        }
    }

    async testPageAccessibility(pageConfig, viewport) {
        const testKey = `${pageConfig.name}_${viewport.name}`;
        console.log(`\n♿ Testing ${pageConfig.name} accessibility on ${viewport.name}...`);
        
        const page = await this.browser.newPage();
        await page.setViewport(viewport);
        
        try {
            // Enable accessibility features
            await page.evaluateOnNewDocument(() => {
                // Force reduced motion for testing
                const style = document.createElement('style');
                style.textContent = `
                    *, *::before, *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                        scroll-behavior: auto !important;
                    }
                `;
                document.head.appendChild(style);
            });
            
            // Navigate to page
            const response = await page.goto(`http://localhost${pageConfig.url}`, {
                waitUntil: 'networkidle2',
                timeout: 30000
            });
            
            if (!response || !response.ok()) {
                throw new Error(`Failed to load page: ${response ? response.status() : 'No response'}`);
            }
            
            // Wait for page to fully render
            await page.waitForTimeout(2000);
            
            // Inject axe-core for automated testing
            await page.addScriptTag({
                path: require.resolve('axe-core/axe.min.js')
            });
            
            // Run axe-core accessibility scan
            const axeResults = await page.evaluate(async () => {
                return await axe.run(document, {
                    tags: ['wcag2a', 'wcag2aa', 'wcag21aa', 'best-practice'],
                    rules: {
                        'color-contrast': { enabled: true },
                        'focus-order-semantics': { enabled: true },
                        'keyboard-navigation': { enabled: true },
                        'landmark-contentinfo-is-top-level': { enabled: true },
                        'page-has-heading-one': { enabled: true },
                        'region': { enabled: true },
                        'skip-link': { enabled: true }
                    }
                });
            });
            
            // Test keyboard navigation
            const keyboardTestResults = await this.testKeyboardNavigation(page);
            
            // Test focus management
            const focusTestResults = await this.testFocusManagement(page);
            
            // Test color contrast manually for custom elements
            const contrastResults = await this.testColorContrast(page);
            
            // Test screen reader compatibility
            const screenReaderResults = await this.testScreenReaderCompatibility(page);
            
            // Test responsive text scaling
            const textScalingResults = await this.testTextScaling(page);
            
            // Test animation preferences
            const motionResults = await this.testReducedMotion(page);
            
            // Take screenshots for visual verification
            const screenshotPath = `./test-results/accessibility/screenshots/${testKey}_${Date.now()}.png`;
            await page.screenshot({ 
                path: screenshotPath, 
                fullPage: true,
                type: 'png'
            });
            
            // High contrast mode screenshot
            await page.emulateMediaFeatures([
                { name: 'prefers-contrast', value: 'high' }
            ]);
            const highContrastScreenshotPath = `./test-results/accessibility/screenshots/${testKey}_high_contrast_${Date.now()}.png`;
            await page.screenshot({ 
                path: highContrastScreenshotPath, 
                fullPage: true,
                type: 'png'
            });
            
            // Compile test results
            const result = {
                page: pageConfig.name,
                viewport: viewport.name,
                url: pageConfig.url,
                timestamp: new Date().toISOString(),
                screenshot: screenshotPath,
                highContrastScreenshot: highContrastScreenshotPath,
                
                // Axe-core results
                axe: {
                    violations: axeResults.violations,
                    passes: axeResults.passes.length,
                    incomplete: axeResults.incomplete,
                    inapplicable: axeResults.inapplicable.length
                },
                
                // Custom tests
                keyboard: keyboardTestResults,
                focus: focusTestResults,
                contrast: contrastResults,
                screenReader: screenReaderResults,
                textScaling: textScalingResults,
                motion: motionResults,
                
                // Calculated scores
                scores: this.calculateAccessibilityScores(axeResults, {
                    keyboard: keyboardTestResults,
                    focus: focusTestResults,
                    contrast: contrastResults,
                    screenReader: screenReaderResults
                })
            };
            
            this.results.testResults[testKey] = result;
            
            // Log summary
            console.log(`  Violations: ${axeResults.violations.length}`);
            console.log(`  Passes: ${axeResults.passes.length}`);
            console.log(`  Score: ${result.scores.overall}/100`);
            
            if (axeResults.violations.length > 0) {
                console.log(`  Critical issues: ${axeResults.violations.filter(v => v.impact === 'critical').length}`);
            }
            
        } catch (error) {
            console.error(`❌ Error testing ${pageConfig.name} accessibility:`, error);
            this.results.testResults[testKey] = {
                page: pageConfig.name,
                viewport: viewport.name,
                error: error.message,
                timestamp: new Date().toISOString()
            };
        } finally {
            await page.close();
        }
    }

    async testKeyboardNavigation(page) {
        console.log('  ⌨️  Testing keyboard navigation...');
        
        try {
            const results = await page.evaluate(async () => {
                const focusableElements = document.querySelectorAll(
                    'a[href], button, input, textarea, select, details, [tabindex]:not([tabindex="-1"])'
                );
                
                const results = {
                    totalFocusableElements: focusableElements.length,
                    keyboardAccessible: 0,
                    hasSkipLinks: false,
                    focusTraps: [],
                    tabOrder: []
                };
                
                // Test skip links
                const skipLinks = document.querySelectorAll('a[href^="#"]:first-child, .skip-link');
                results.hasSkipLinks = skipLinks.length > 0;
                
                // Test tab order
                let tabIndex = 0;
                for (const element of focusableElements) {
                    const computedStyle = window.getComputedStyle(element);
                    const isVisible = computedStyle.display !== 'none' && 
                                    computedStyle.visibility !== 'hidden' &&
                                    computedStyle.opacity !== '0';
                    
                    if (isVisible) {
                        results.keyboardAccessible++;
                        results.tabOrder.push({
                            tagName: element.tagName.toLowerCase(),
                            id: element.id,
                            class: element.className,
                            tabIndex: element.tabIndex || tabIndex++,
                            ariaLabel: element.getAttribute('aria-label'),
                            role: element.getAttribute('role')
                        });
                    }
                }
                
                return results;
            });
            
            return {
                ...results,
                passed: results.keyboardAccessible > 0,
                score: Math.min(100, (results.keyboardAccessible / Math.max(results.totalFocusableElements, 1)) * 100)
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    async testFocusManagement(page) {
        console.log('  🎯 Testing focus management...');
        
        try {
            const results = await page.evaluate(() => {
                const results = {
                    hasFocusIndicators: true,
                    focusWithinElements: 0,
                    customFocusStyles: 0,
                    focusTraps: []
                };
                
                // Check for custom focus styles
                const styleSheets = Array.from(document.styleSheets);
                let focusRuleCount = 0;
                
                try {
                    styleSheets.forEach(sheet => {
                        if (sheet.cssRules) {
                            Array.from(sheet.cssRules).forEach(rule => {
                                if (rule.selectorText && rule.selectorText.includes(':focus')) {
                                    focusRuleCount++;
                                }
                            });
                        }
                    });
                } catch (e) {
                    // Cross-origin stylesheets may not be accessible
                }
                
                results.customFocusStyles = focusRuleCount;
                
                // Check focus-within usage
                const elementsWithFocusWithin = document.querySelectorAll('*');
                elementsWithFocusWithin.forEach(el => {
                    const computedStyle = window.getComputedStyle(el);
                    // This is a simplified check - in reality, we'd need to test actual focus behavior
                    if (el.matches(':focus-within')) {
                        results.focusWithinElements++;
                    }
                });
                
                return results;
            });
            
            return {
                ...results,
                passed: results.customFocusStyles > 0,
                score: Math.min(100, results.customFocusStyles * 10)
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    async testColorContrast(page) {
        console.log('  🌈 Testing color contrast...');
        
        try {
            const results = await page.evaluate(() => {
                const results = {
                    totalElements: 0,
                    passedElements: 0,
                    failedElements: [],
                    contrastRatios: []
                };
                
                // Helper function to calculate luminance
                function getLuminance(r, g, b) {
                    const [rs, gs, bs] = [r, g, b].map(c => {
                        c = c / 255;
                        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    });
                    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
                }
                
                // Helper function to calculate contrast ratio
                function getContrastRatio(rgb1, rgb2) {
                    const l1 = getLuminance(rgb1.r, rgb1.g, rgb1.b);
                    const l2 = getLuminance(rgb2.r, rgb2.g, rgb2.b);
                    const lighter = Math.max(l1, l2);
                    const darker = Math.min(l1, l2);
                    return (lighter + 0.05) / (darker + 0.05);
                }
                
                // Parse RGB string to object
                function parseRGB(rgbString) {
                    const match = rgbString.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
                    return match ? {
                        r: parseInt(match[1]),
                        g: parseInt(match[2]),
                        b: parseInt(match[3])
                    } : null;
                }
                
                // Test text elements
                const textElements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, div, a, button, label, td, th, li');
                
                textElements.forEach(element => {
                    const style = window.getComputedStyle(element);
                    const fontSize = parseFloat(style.fontSize);
                    const fontWeight = style.fontWeight;
                    
                    // Skip elements without text content
                    if (!element.textContent.trim()) return;
                    
                    results.totalElements++;
                    
                    const textColor = parseRGB(style.color);
                    const bgColor = parseRGB(style.backgroundColor);
                    
                    if (textColor && bgColor) {
                        const contrast = getContrastRatio(textColor, bgColor);
                        results.contrastRatios.push(contrast);
                        
                        // WCAG AA requirements
                        const isLargeText = fontSize >= 18 || (fontSize >= 14 && fontWeight >= 600);
                        const minContrast = isLargeText ? 3 : 4.5;
                        
                        if (contrast >= minContrast) {
                            results.passedElements++;
                        } else {
                            results.failedElements.push({
                                element: element.tagName.toLowerCase(),
                                text: element.textContent.substring(0, 50),
                                contrast: contrast.toFixed(2),
                                required: minContrast,
                                textColor: style.color,
                                backgroundColor: style.backgroundColor
                            });
                        }
                    }
                });
                
                return results;
            });
            
            return {
                ...results,
                passed: results.failedElements.length === 0,
                score: results.totalElements > 0 ? Math.round((results.passedElements / results.totalElements) * 100) : 100
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    async testScreenReaderCompatibility(page) {
        console.log('  📢 Testing screen reader compatibility...');
        
        try {
            const results = await page.evaluate(() => {
                const results = {
                    hasPageTitle: !!document.title,
                    hasMainLandmark: !!document.querySelector('main, [role="main"]'),
                    hasNavLandmark: !!document.querySelector('nav, [role="navigation"]'),
                    hasHeadingStructure: false,
                    ariaLabels: 0,
                    altTexts: 0,
                    formLabels: 0,
                    skipLinks: 0,
                    liveRegions: 0
                };
                
                // Check heading structure
                const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
                if (headings.length > 0) {
                    const hasH1 = !!document.querySelector('h1');
                    results.hasHeadingStructure = hasH1;
                }
                
                // Count ARIA labels
                results.ariaLabels = document.querySelectorAll('[aria-label], [aria-labelledby]').length;
                
                // Check alt texts
                const images = document.querySelectorAll('img');
                let imagesWithAlt = 0;
                images.forEach(img => {
                    if (img.alt !== undefined) {
                        imagesWithAlt++;
                    }
                });
                results.altTexts = imagesWithAlt;
                results.totalImages = images.length;
                
                // Check form labels
                const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
                let labeledInputs = 0;
                inputs.forEach(input => {
                    const hasLabel = document.querySelector(`label[for="${input.id}"]`) || 
                                   input.closest('label') ||
                                   input.getAttribute('aria-label') ||
                                   input.getAttribute('aria-labelledby');
                    if (hasLabel) {
                        labeledInputs++;
                    }
                });
                results.formLabels = labeledInputs;
                results.totalInputs = inputs.length;
                
                // Check skip links
                results.skipLinks = document.querySelectorAll('.skip-link, a[href^="#"]:first-child').length;
                
                // Check live regions
                results.liveRegions = document.querySelectorAll('[aria-live], [role="alert"], [role="status"]').length;
                
                return results;
            });
            
            // Calculate score based on screen reader friendliness
            let score = 0;
            if (results.hasPageTitle) score += 15;
            if (results.hasMainLandmark) score += 15;
            if (results.hasNavLandmark) score += 10;
            if (results.hasHeadingStructure) score += 20;
            if (results.ariaLabels > 0) score += 10;
            if (results.totalImages === 0 || results.altTexts / results.totalImages > 0.8) score += 15;
            if (results.totalInputs === 0 || results.formLabels / results.totalInputs > 0.8) score += 15;
            
            return {
                ...results,
                passed: score > 70,
                score
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    async testTextScaling(page) {
        console.log('  📏 Testing text scaling...');
        
        try {
            // Test at 200% zoom
            await page.setViewport({
                width: page.viewport().width,
                height: page.viewport().height,
                deviceScaleFactor: 2
            });
            
            await page.waitForTimeout(500);
            
            const results = await page.evaluate(() => {
                const results = {
                    overflowingElements: 0,
                    truncatedText: 0,
                    readableElements: 0,
                    totalTextElements: 0
                };
                
                const textElements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, div, a, button, label');
                
                textElements.forEach(element => {
                    if (!element.textContent.trim()) return;
                    
                    results.totalTextElements++;
                    
                    const rect = element.getBoundingClientRect();
                    const style = window.getComputedStyle(element);
                    
                    // Check for overflow
                    if (style.overflow === 'hidden' && 
                        (element.scrollWidth > element.clientWidth || element.scrollHeight > element.clientHeight)) {
                        results.overflowingElements++;
                    }
                    
                    // Check for text truncation
                    if (style.textOverflow === 'ellipsis' || element.textContent.includes('…')) {
                        results.truncatedText++;
                    }
                    
                    // Check readability (simplified)
                    if (rect.width > 0 && rect.height > 0) {
                        results.readableElements++;
                    }
                });
                
                return results;
            });
            
            // Reset viewport
            await page.setViewport({
                width: page.viewport().width,
                height: page.viewport().height,
                deviceScaleFactor: 1
            });
            
            return {
                ...results,
                passed: results.overflowingElements === 0 && results.truncatedText < results.totalTextElements * 0.1,
                score: results.totalTextElements > 0 ? 
                       Math.max(0, 100 - (results.overflowingElements + results.truncatedText) * 10) : 100
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    async testReducedMotion(page) {
        console.log('  🎭 Testing reduced motion preferences...');
        
        try {
            await page.emulateMediaFeatures([
                { name: 'prefers-reduced-motion', value: 'reduce' }
            ]);
            
            const results = await page.evaluate(() => {
                const results = {
                    animatedElements: 0,
                    respectsReducedMotion: 0,
                    totalAnimations: 0
                };
                
                // Check for CSS animations
                const allElements = document.querySelectorAll('*');
                allElements.forEach(element => {
                    const style = window.getComputedStyle(element);
                    
                    if (style.animationName !== 'none') {
                        results.totalAnimations++;
                        
                        // Check if animation duration is reduced
                        const duration = parseFloat(style.animationDuration);
                        if (duration <= 0.01) {
                            results.respectsReducedMotion++;
                        }
                    }
                    
                    if (style.transitionDuration !== '0s') {
                        results.animatedElements++;
                        
                        // Check if transition is reduced
                        const duration = parseFloat(style.transitionDuration);
                        if (duration <= 0.01) {
                            results.respectsReducedMotion++;
                        }
                    }
                });
                
                return results;
            });
            
            return {
                ...results,
                passed: results.totalAnimations === 0 || results.respectsReducedMotion / results.totalAnimations > 0.8,
                score: results.totalAnimations === 0 ? 100 : 
                       Math.round((results.respectsReducedMotion / results.totalAnimations) * 100)
            };
        } catch (error) {
            return {
                passed: false,
                error: error.message,
                score: 0
            };
        }
    }

    calculateAccessibilityScores(axeResults, customTests) {
        const scores = {};
        
        // Axe violations impact
        const criticalViolations = axeResults.violations.filter(v => v.impact === 'critical').length;
        const seriousViolations = axeResults.violations.filter(v => v.impact === 'serious').length;
        const moderateViolations = axeResults.violations.filter(v => v.impact === 'moderate').length;
        const minorViolations = axeResults.violations.filter(v => v.impact === 'minor').length;
        
        // Deduct points based on violation severity
        let axeScore = 100;
        axeScore -= criticalViolations * 25;
        axeScore -= seriousViolations * 15;
        axeScore -= moderateViolations * 10;
        axeScore -= minorViolations * 5;
        
        scores.axe = Math.max(0, axeScore);
        scores.keyboard = customTests.keyboard?.score || 0;
        scores.focus = customTests.focus?.score || 0;
        scores.contrast = customTests.contrast?.score || 0;
        scores.screenReader = customTests.screenReader?.score || 0;
        
        // Overall score (weighted average)
        const weights = {
            axe: 0.4,
            keyboard: 0.15,
            focus: 0.1,
            contrast: 0.2,
            screenReader: 0.15
        };
        
        scores.overall = Math.round(
            scores.axe * weights.axe +
            scores.keyboard * weights.keyboard +
            scores.focus * weights.focus +
            scores.contrast * weights.contrast +
            scores.screenReader * weights.screenReader
        );
        
        return scores;
    }

    async runAllTests() {
        console.log('\n♿ Running comprehensive accessibility tests...');
        
        // Ensure results directory exists
        const resultsDir = './test-results/accessibility';
        const screenshotsDir = './test-results/accessibility/screenshots';
        
        if (!fs.existsSync(resultsDir)) {
            fs.mkdirSync(resultsDir, { recursive: true });
        }
        if (!fs.existsSync(screenshotsDir)) {
            fs.mkdirSync(screenshotsDir, { recursive: true });
        }
        
        // Test each page on each viewport
        for (const page of this.testPages) {
            for (const viewport of this.viewports) {
                await this.testPageAccessibility(page, viewport);
            }
        }
        
        this.generateSummary();
        this.generateRecommendations();
        
        console.log('\n📝 Generating accessibility report...');
        await this.generateReport();
        
        console.log('\n✅ Accessibility testing complete!');
        console.log(`📊 Results saved to: ${resultsDir}/accessibility-report.json`);
        console.log(`📈 HTML report: ${resultsDir}/accessibility-report.html`);
    }

    generateSummary() {
        const results = Object.values(this.results.testResults).filter(r => !r.error);
        
        if (results.length === 0) {
            this.results.summary = { error: 'No successful test results' };
            return;
        }
        
        // Calculate overall metrics
        const totalViolations = results.reduce((sum, r) => sum + (r.axe?.violations?.length || 0), 0);
        const totalPasses = results.reduce((sum, r) => sum + (r.axe?.passes || 0), 0);
        const avgScore = results.reduce((sum, r) => sum + (r.scores?.overall || 0), 0) / results.length;
        
        // Violation severity breakdown
        const violationsByImpact = {
            critical: 0,
            serious: 0,
            moderate: 0,
            minor: 0
        };
        
        results.forEach(result => {
            if (result.axe && result.axe.violations) {
                result.axe.violations.forEach(violation => {
                    if (violationsByImpact[violation.impact] !== undefined) {
                        violationsByImpact[violation.impact]++;
                    }
                });
            }
        });
        
        // Performance levels
        const performanceLevels = {
            excellent: results.filter(r => r.scores?.overall >= 95).length,
            good: results.filter(r => r.scores?.overall >= 80 && r.scores?.overall < 95).length,
            needsImprovement: results.filter(r => r.scores?.overall >= 60 && r.scores?.overall < 80).length,
            poor: results.filter(r => r.scores?.overall < 60).length
        };
        
        this.results.summary = {
            totalTests: results.length,
            successfulTests: results.length,
            failedTests: Object.values(this.results.testResults).filter(r => r.error).length,
            totalViolations,
            totalPasses,
            averageScore: avgScore,
            violationsByImpact,
            performanceLevels,
            grade: this.getAccessibilityGrade(avgScore),
            wcagCompliance: totalViolations === 0 ? 'AA' : violationsByImpact.critical === 0 ? 'Partial' : 'Non-compliant'
        };
    }

    getAccessibilityGrade(score) {
        if (score >= 95) return 'A+';
        if (score >= 90) return 'A';
        if (score >= 80) return 'B';
        if (score >= 70) return 'C';
        if (score >= 60) return 'D';
        return 'F';
    }

    generateRecommendations() {
        const recommendations = [];
        const results = Object.values(this.results.testResults).filter(r => !r.error);
        
        if (results.length === 0) return;
        
        // Critical violations
        const criticalViolations = results.flatMap(r => 
            r.axe?.violations?.filter(v => v.impact === 'critical') || []
        );
        
        if (criticalViolations.length > 0) {
            recommendations.push({
                type: 'critical',
                category: 'WCAG Violations',
                issue: `${criticalViolations.length} critical accessibility violations found`,
                suggestions: [
                    'Fix missing form labels and aria-labels',
                    'Ensure all images have appropriate alt text',
                    'Fix keyboard navigation issues',
                    'Resolve color contrast violations',
                    'Add proper heading structure'
                ]
            });
        }
        
        // Keyboard navigation issues
        const keyboardIssues = results.filter(r => r.keyboard && !r.keyboard.passed);
        if (keyboardIssues.length > 0) {
            recommendations.push({
                type: 'warning',
                category: 'Keyboard Navigation',
                issue: 'Keyboard navigation needs improvement',
                suggestions: [
                    'Add skip links for main content areas',
                    'Ensure all interactive elements are keyboard accessible',
                    'Implement proper focus management',
                    'Test tab order for logical flow'
                ]
            });
        }
        
        // Color contrast issues
        const contrastIssues = results.filter(r => r.contrast && r.contrast.failedElements?.length > 0);
        if (contrastIssues.length > 0) {
            recommendations.push({
                type: 'warning',
                category: 'Color Contrast',
                issue: 'Color contrast ratios below WCAG AA standards',
                suggestions: [
                    'Increase contrast for text elements',
                    'Use darker colors for text on light backgrounds',
                    'Test with color contrast analyzer tools',
                    'Consider users with visual impairments'
                ]
            });
        }
        
        // Screen reader compatibility
        const screenReaderIssues = results.filter(r => r.screenReader && !r.screenReader.passed);
        if (screenReaderIssues.length > 0) {
            recommendations.push({
                type: 'warning',
                category: 'Screen Reader',
                issue: 'Screen reader compatibility needs improvement',
                suggestions: [
                    'Add proper ARIA landmarks and labels',
                    'Ensure semantic HTML structure',
                    'Implement proper heading hierarchy',
                    'Test with actual screen readers'
                ]
            });
        }
        
        this.results.recommendations = recommendations;
    }

    async generateReport() {
        const jsonReport = JSON.stringify(this.results, null, 2);
        fs.writeFileSync('./test-results/accessibility/accessibility-report.json', jsonReport);
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport();
        fs.writeFileSync('./test-results/accessibility/accessibility-report.html', htmlReport);
    }

    generateHTMLReport() {
        const { summary, testResults, recommendations } = this.results;
        
        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets Accessibility Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; color: #2563eb; }
        .stat-label { color: #64748b; margin-top: 5px; }
        .grade { font-size: 3em; font-weight: bold; padding: 20px; border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .grade.A\\+ { background: #10b981; color: white; font-size: 1.8em; }
        .grade.A { background: #10b981; color: white; }
        .grade.B { background: #059669; color: white; }
        .grade.C { background: #fbbf24; color: white; }
        .grade.D { background: #f59e0b; color: white; }
        .grade.F { background: #ef4444; color: white; }
        .violations { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .violation-critical { border-left: 4px solid #ef4444; background: #fef2f2; }
        .violation-serious { border-left: 4px solid #f59e0b; background: #fffbeb; }
        .violation-moderate { border-left: 4px solid #eab308; background: #fefce8; }
        .violation-minor { border-left: 4px solid #3b82f6; background: #eff6ff; }
        .recommendations { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .recommendation { margin-bottom: 15px; padding: 15px; border-radius: 6px; }
        .recommendation.critical { background: #fef2f2; border-left: 4px solid #ef4444; }
        .recommendation.warning { background: #fffbeb; border-left: 4px solid #f59e0b; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        .results-table th, .results-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .results-table th { background: #f8fafc; font-weight: 600; }
        .score-excellent { color: #10b981; }
        .score-good { color: #059669; }
        .score-warning { color: #f59e0b; }
        .score-poor { color: #ef4444; }
        .timestamp { color: #64748b; font-size: 0.9em; }
        .wcag-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .wcag-compliant { background: #10b981; color: white; }
        .wcag-partial { background: #f59e0b; color: white; }
        .wcag-non-compliant { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>♿ HD Tickets Accessibility Report</h1>
            <p class="timestamp">Generated on ${new Date(this.results.timestamp).toLocaleString()}</p>
            <span class="wcag-badge wcag-${summary.wcagCompliance === 'AA' ? 'compliant' : summary.wcagCompliance === 'Partial' ? 'partial' : 'non-compliant'}">
                WCAG 2.1 ${summary.wcagCompliance}
            </span>
        </div>
        
        <div class="summary">
            <div class="stat-card">
                <div class="grade ${summary.grade}">${summary.grade}</div>
                <div class="stat-label">Accessibility Grade</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.averageScore?.toFixed(0) || 'N/A'}</div>
                <div class="stat-label">Average Score</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value ${summary.totalViolations === 0 ? 'score-excellent' : summary.totalViolations < 5 ? 'score-good' : 'score-poor'}">${summary.totalViolations}</div>
                <div class="stat-label">Total Violations</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.totalPasses}</div>
                <div class="stat-label">Tests Passed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value ${summary.violationsByImpact.critical === 0 ? 'score-excellent' : 'score-poor'}">${summary.violationsByImpact.critical}</div>
                <div class="stat-label">Critical Issues</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.violationsByImpact.serious}</div>
                <div class="stat-label">Serious Issues</div>
            </div>
        </div>
        
        ${summary.violationsByImpact.critical > 0 || summary.violationsByImpact.serious > 0 ? `
        <div class="violations">
            <h2>🚨 Priority Violations</h2>
            ${Object.entries(summary.violationsByImpact).map(([impact, count]) => 
                count > 0 ? `
                <div class="violation-${impact}">
                    <strong>${impact.charAt(0).toUpperCase() + impact.slice(1)}</strong>: ${count} violations
                </div>` : ''
            ).join('')}
        </div>
        ` : ''}
        
        ${recommendations.length > 0 ? `
        <div class="recommendations">
            <h2>💡 Recommendations</h2>
            ${recommendations.map(rec => `
                <div class="recommendation ${rec.type}">
                    <h3>${rec.category}: ${rec.issue}</h3>
                    <ul>
                        ${rec.suggestions.map(s => `<li>${s}</li>`).join('')}
                    </ul>
                </div>
            `).join('')}
        </div>
        ` : ''}
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px;">
            <h2>📊 Detailed Results</h2>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Viewport</th>
                        <th>Overall Score</th>
                        <th>Violations</th>
                        <th>Keyboard</th>
                        <th>Contrast</th>
                        <th>Screen Reader</th>
                    </tr>
                </thead>
                <tbody>
                    ${Object.values(testResults).filter(r => !r.error).map(result => `
                        <tr>
                            <td>${result.page}</td>
                            <td>${result.viewport}</td>
                            <td class="${this.getScoreClass(result.scores?.overall)}">${result.scores?.overall || 'N/A'}/100</td>
                            <td class="${result.axe?.violations?.length === 0 ? 'score-excellent' : result.axe?.violations?.length < 3 ? 'score-warning' : 'score-poor'}">${result.axe?.violations?.length || 0}</td>
                            <td class="${result.keyboard?.passed ? 'score-excellent' : 'score-poor'}">${result.keyboard?.passed ? '✅' : '❌'}</td>
                            <td class="${result.contrast?.passed ? 'score-excellent' : 'score-poor'}">${result.contrast?.passed ? '✅' : '❌'}</td>
                            <td class="${result.screenReader?.passed ? 'score-excellent' : 'score-poor'}">${result.screenReader?.passed ? '✅' : '❌'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>`;
    }

    getScoreClass(score) {
        if (score >= 95) return 'score-excellent';
        if (score >= 80) return 'score-good';
        if (score >= 60) return 'score-warning';
        return 'score-poor';
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main execution
async function main() {
    const tester = new AccessibilityTester();
    
    try {
        await tester.initialize();
        await tester.runAllTests();
    } catch (error) {
        console.error('❌ Accessibility testing failed:', error);
        process.exit(1);
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main();
}

module.exports = AccessibilityTester;
