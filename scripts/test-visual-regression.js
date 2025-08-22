#!/usr/bin/env node

/**
 * HD Tickets Visual Regression Testing Suite
 * 
 * Comprehensive visual regression testing including:
 * - Cross-browser screenshot comparison
 * - Responsive design validation
 * - Component visual consistency
 * - Theme and dark mode testing
 * - Animation state capture
 */

const puppeteer = require('puppeteer');
const pixelmatch = require('pixelmatch');
const PNG = require('pngjs').PNG;
const fs = require('fs');
const path = require('path');

class VisualRegressionTester {
    constructor() {
        this.browser = null;
        this.results = {
            timestamp: new Date().toISOString(),
            testResults: {},
            summary: {},
            differences: [],
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
            { name: 'Mobile', width: 375, height: 667, deviceScaleFactor: 2 },
            { name: 'Tablet', width: 768, height: 1024, deviceScaleFactor: 1.5 },
            { name: 'Desktop', width: 1440, height: 900, deviceScaleFactor: 1 },
            { name: 'Large Desktop', width: 1920, height: 1080, deviceScaleFactor: 1 }
        ];
        
        this.themes = [
            { name: 'Light', mode: 'light' },
            { name: 'Dark', mode: 'dark' }
        ];
        
        this.testStates = [
            { name: 'Default', state: 'default' },
            { name: 'Loading', state: 'loading' },
            { name: 'Error', state: 'error' },
            { name: 'Empty', state: 'empty' }
        ];
        
        // Visual comparison thresholds
        this.thresholds = {
            pixel: 0.1,        // 10% pixel difference threshold
            mismatch: 0.05,    // 5% overall mismatch threshold
            antialiasing: true  // Enable antialiasing detection
        };
        
        this.baselinePath = './test-results/visual-regression/baselines';
        this.currentPath = './test-results/visual-regression/current';
        this.diffPath = './test-results/visual-regression/diffs';
    }

    async initialize() {
        console.log('📸 Initializing HD Tickets Visual Regression Testing Suite...');
        
        try {
            this.browser = await puppeteer.launch({
                headless: 'new',
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-web-security',
                    '--disable-features=TranslateUI',
                    '--disable-ipc-flooding-protection'
                ]
            });
            console.log('✅ Browser initialized for visual testing');
            
            // Ensure directories exist
            this.ensureDirectories();
        } catch (error) {
            console.error('❌ Failed to initialize browser:', error);
            throw error;
        }
    }

    ensureDirectories() {
        const dirs = [this.baselinePath, this.currentPath, this.diffPath];
        dirs.forEach(dir => {
            if (!fs.existsSync(dir)) {
                fs.mkdirSync(dir, { recursive: true });
            }
        });
    }

    async capturePageScreenshots(pageConfig, viewport, theme, state) {
        const testKey = `${pageConfig.name}_${viewport.name}_${theme.name}_${state.name}`;
        console.log(`\n📸 Capturing ${pageConfig.name} (${viewport.name}, ${theme.name}, ${state.name})...`);
        
        const page = await this.browser.newPage();
        
        try {
            await page.setViewport(viewport);
            
            // Set theme preference
            await page.emulateMediaFeatures([
                { name: 'prefers-color-scheme', value: theme.mode }
            ]);
            
            // Navigate to page
            const response = await page.goto(`http://localhost${pageConfig.url}`, {
                waitUntil: 'networkidle2',
                timeout: 30000
            });
            
            if (!response || !response.ok()) {
                throw new Error(`Failed to load page: ${response ? response.status() : 'No response'}`);
            }
            
            // Apply test state
            await this.applyTestState(page, state);
            
            // Wait for animations to complete
            await page.waitForTimeout(2000);
            
            // Hide dynamic content (timestamps, etc.)
            await page.evaluate(() => {
                // Hide elements that change frequently
                const dynamicSelectors = [
                    '[data-test="timestamp"]',
                    '[data-test="current-time"]',
                    '.timestamp',
                    '.time',
                    '.last-updated',
                    '[class*="time"]'
                ];
                
                dynamicSelectors.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => {
                        el.style.opacity = '0';
                    });
                });
                
                // Stabilize animations
                const style = document.createElement('style');
                style.textContent = `
                    *, *::before, *::after {
                        animation-duration: 0s !important;
                        animation-delay: 0s !important;
                        transition-duration: 0s !important;
                        transition-delay: 0s !important;
                    }
                `;
                document.head.appendChild(style);
            });
            
            // Take full page screenshot
            const screenshotPath = path.join(this.currentPath, `${testKey}.png`);
            await page.screenshot({
                path: screenshotPath,
                fullPage: true,
                type: 'png'
            });
            
            // Take viewport screenshot
            const viewportScreenshotPath = path.join(this.currentPath, `${testKey}_viewport.png`);
            await page.screenshot({
                path: viewportScreenshotPath,
                fullPage: false,
                type: 'png'
            });
            
            // Capture component-level screenshots
            const componentScreenshots = await this.captureComponentScreenshots(page, testKey);
            
            return {
                testKey,
                fullPage: screenshotPath,
                viewport: viewportScreenshotPath,
                components: componentScreenshots,
                success: true
            };
            
        } catch (error) {
            console.error(`❌ Error capturing ${testKey}:`, error);
            return {
                testKey,
                error: error.message,
                success: false
            };
        } finally {
            await page.close();
        }
    }

    async applyTestState(page, state) {
        switch (state.state) {
            case 'loading':
                await page.evaluate(() => {
                    // Add loading indicators
                    document.body.classList.add('loading-state');
                    
                    // Replace content with skeleton loaders
                    const contentElements = document.querySelectorAll('[data-test="dynamic-content"]');
                    contentElements.forEach(el => {
                        el.innerHTML = '<div class="skeleton-loader"></div>';
                    });
                });
                break;
                
            case 'error':
                await page.evaluate(() => {
                    // Inject error states
                    document.body.classList.add('error-state');
                    
                    const errorElements = document.querySelectorAll('[data-test="error-container"]');
                    errorElements.forEach(el => {
                        el.innerHTML = '<div class="error-message">Something went wrong</div>';
                        el.style.display = 'block';
                    });
                });
                break;
                
            case 'empty':
                await page.evaluate(() => {
                    // Show empty states
                    document.body.classList.add('empty-state');
                    
                    const listElements = document.querySelectorAll('[data-test="list-container"]');
                    listElements.forEach(el => {
                        el.innerHTML = '<div class="empty-state-message">No items found</div>';
                    });
                });
                break;
                
            default:
                // Default state - no changes needed
                break;
        }
    }

    async captureComponentScreenshots(page, testKey) {
        const componentScreenshots = {};
        
        const components = [
            { name: 'header', selector: 'header, [data-test="header"]' },
            { name: 'navigation', selector: 'nav, [data-test="navigation"]' },
            { name: 'sidebar', selector: '.sidebar, [data-test="sidebar"]' },
            { name: 'main-content', selector: 'main, [data-test="main-content"]' },
            { name: 'footer', selector: 'footer, [data-test="footer"]' },
            { name: 'modal', selector: '.modal, [data-test="modal"]' },
            { name: 'cards', selector: '.card, [data-test="card"]' },
            { name: 'forms', selector: 'form, [data-test="form"]' },
            { name: 'tables', selector: 'table, [data-test="table"]' }
        ];
        
        for (const component of components) {
            try {
                const elements = await page.$$(component.selector);
                
                if (elements.length > 0) {
                    // Screenshot first instance of each component type
                    const element = elements[0];
                    const componentPath = path.join(
                        this.currentPath, 
                        `${testKey}_component_${component.name}.png`
                    );
                    
                    await element.screenshot({
                        path: componentPath,
                        type: 'png'
                    });
                    
                    componentScreenshots[component.name] = componentPath;
                }
            } catch (error) {
                // Component not found or not visible - skip
                console.log(`  ⚠️ Component ${component.name} not found or not visible`);
            }
        }
        
        return componentScreenshots;
    }

    async compareScreenshots(currentPath, baselinePath, diffPath) {
        if (!fs.existsSync(baselinePath)) {
            // No baseline exists - current becomes baseline
            fs.copyFileSync(currentPath, baselinePath);
            return {
                status: 'new',
                message: 'New baseline created',
                pixelDiff: 0,
                percentageDiff: 0
            };
        }
        
        try {
            const currentImage = PNG.sync.read(fs.readFileSync(currentPath));
            const baselineImage = PNG.sync.read(fs.readFileSync(baselinePath));
            
            if (currentImage.width !== baselineImage.width || currentImage.height !== baselineImage.height) {
                return {
                    status: 'size_mismatch',
                    message: `Size mismatch: ${currentImage.width}x${currentImage.height} vs ${baselineImage.width}x${baselineImage.height}`,
                    pixelDiff: -1,
                    percentageDiff: -1
                };
            }
            
            const diffImage = new PNG({
                width: currentImage.width,
                height: currentImage.height
            });
            
            const pixelDiff = pixelmatch(
                currentImage.data,
                baselineImage.data,
                diffImage.data,
                currentImage.width,
                currentImage.height,
                {
                    threshold: this.thresholds.pixel,
                    includeAA: this.thresholds.antialiasing,
                    diffColor: [255, 0, 0],
                    diffColorAlt: [255, 255, 0]
                }
            );
            
            const totalPixels = currentImage.width * currentImage.height;
            const percentageDiff = (pixelDiff / totalPixels) * 100;
            
            // Save diff image if there are differences
            if (pixelDiff > 0) {
                fs.writeFileSync(diffPath, PNG.sync.write(diffImage));
            }
            
            const status = percentageDiff <= this.thresholds.mismatch ? 'pass' : 'fail';
            
            return {
                status,
                message: status === 'pass' ? 'Screenshots match' : `Visual differences found: ${percentageDiff.toFixed(2)}%`,
                pixelDiff,
                percentageDiff,
                diffImagePath: pixelDiff > 0 ? diffPath : null
            };
            
        } catch (error) {
            return {
                status: 'error',
                message: `Comparison failed: ${error.message}`,
                pixelDiff: -1,
                percentageDiff: -1
            };
        }
    }

    async testPageVisualRegression(pageConfig) {
        console.log(`\n🔍 Testing visual regression for ${pageConfig.name}...`);
        
        const pageResults = {
            page: pageConfig.name,
            url: pageConfig.url,
            screenshots: {},
            comparisons: {},
            overallStatus: 'pass',
            totalDifferences: 0
        };
        
        // Capture screenshots for all combinations
        for (const viewport of this.viewports) {
            for (const theme of this.themes) {
                for (const state of this.testStates) {
                    const captureResult = await this.capturePageScreenshots(pageConfig, viewport, theme, state);
                    
                    if (captureResult.success) {
                        const testKey = captureResult.testKey;
                        pageResults.screenshots[testKey] = captureResult;
                        
                        // Compare full page screenshot
                        const baselinePath = path.join(this.baselinePath, `${testKey}.png`);
                        const diffPath = path.join(this.diffPath, `${testKey}.png`);
                        
                        const comparison = await this.compareScreenshots(
                            captureResult.fullPage,
                            baselinePath,
                            diffPath
                        );
                        
                        pageResults.comparisons[testKey] = comparison;
                        
                        if (comparison.status === 'fail') {
                            pageResults.overallStatus = 'fail';
                            pageResults.totalDifferences++;
                        }
                        
                        // Compare component screenshots
                        for (const [componentName, componentPath] of Object.entries(captureResult.components)) {
                            const componentBaselinePath = path.join(this.baselinePath, `${testKey}_component_${componentName}.png`);
                            const componentDiffPath = path.join(this.diffPath, `${testKey}_component_${componentName}.png`);
                            
                            const componentComparison = await this.compareScreenshots(
                                componentPath,
                                componentBaselinePath,
                                componentDiffPath
                            );
                            
                            pageResults.comparisons[`${testKey}_component_${componentName}`] = componentComparison;
                            
                            if (componentComparison.status === 'fail') {
                                pageResults.overallStatus = 'fail';
                                pageResults.totalDifferences++;
                            }
                        }
                        
                        // Log comparison results
                        console.log(`    ${testKey}: ${comparison.status} (${comparison.percentageDiff?.toFixed(2)}% diff)`);
                    } else {
                        pageResults.screenshots[captureResult.testKey] = { error: captureResult.error };
                        pageResults.overallStatus = 'error';
                    }
                }
            }
        }
        
        return pageResults;
    }

    async runAllTests() {
        console.log('\n📸 Running comprehensive visual regression tests...');
        
        let totalTests = 0;
        let passedTests = 0;
        let failedTests = 0;
        let newBaselines = 0;
        
        for (const page of this.testPages) {
            const pageResults = await this.testPageVisualRegression(page);
            this.results.testResults[page.name] = pageResults;
            
            // Update counters
            Object.values(pageResults.comparisons).forEach(comparison => {
                totalTests++;
                if (comparison.status === 'pass') {
                    passedTests++;
                } else if (comparison.status === 'fail') {
                    failedTests++;
                } else if (comparison.status === 'new') {
                    newBaselines++;
                }
            });
        }
        
        this.generateSummary(totalTests, passedTests, failedTests, newBaselines);
        this.generateRecommendations();
        
        console.log('\n📝 Generating visual regression report...');
        await this.generateReport();
        
        console.log('\n✅ Visual regression testing complete!');
        console.log(`📊 Total tests: ${totalTests}`);
        console.log(`✅ Passed: ${passedTests}`);
        console.log(`❌ Failed: ${failedTests}`);
        console.log(`🆕 New baselines: ${newBaselines}`);
    }

    generateSummary(totalTests, passedTests, failedTests, newBaselines) {
        const errorTests = totalTests - passedTests - failedTests - newBaselines;
        
        this.results.summary = {
            totalTests,
            passedTests,
            failedTests,
            newBaselines,
            errorTests,
            successRate: totalTests > 0 ? (passedTests / totalTests) * 100 : 0,
            pages: Object.keys(this.results.testResults).length,
            criticalFailures: this.getCriticalFailures()
        };
    }

    getCriticalFailures() {
        const criticalFailures = [];
        
        Object.entries(this.results.testResults).forEach(([pageName, pageResults]) => {
            if (pageResults.overallStatus === 'fail' && 
                this.testPages.find(p => p.name === pageName)?.critical) {
                
                Object.entries(pageResults.comparisons).forEach(([testKey, comparison]) => {
                    if (comparison.status === 'fail' && comparison.percentageDiff > 5) {
                        criticalFailures.push({
                            page: pageName,
                            testKey,
                            percentageDiff: comparison.percentageDiff
                        });
                    }
                });
            }
        });
        
        return criticalFailures;
    }

    generateRecommendations() {
        const recommendations = [];
        const { criticalFailures, failedTests, successRate } = this.results.summary;
        
        if (criticalFailures.length > 0) {
            recommendations.push({
                type: 'critical',
                category: 'Critical Pages',
                issue: `${criticalFailures.length} critical page(s) have significant visual changes`,
                suggestions: [
                    'Review critical page layouts immediately',
                    'Check for CSS changes affecting core components',
                    'Validate responsive design integrity',
                    'Consider reverting recent changes if unintentional'
                ]
            });
        }
        
        if (successRate < 90) {
            recommendations.push({
                type: 'warning',
                category: 'Success Rate',
                issue: `Visual regression success rate is ${successRate.toFixed(1)}%`,
                suggestions: [
                    'Review failed test cases for patterns',
                    'Update baselines if changes are intentional',
                    'Implement stricter CSS change review process',
                    'Consider component-level visual testing'
                ]
            });
        }
        
        if (failedTests > 5) {
            recommendations.push({
                type: 'warning',
                category: 'Test Failures',
                issue: `${failedTests} visual tests are failing`,
                suggestions: [
                    'Prioritize fixes for high-impact visual changes',
                    'Check for cross-browser compatibility issues',
                    'Review theme and responsive design changes',
                    'Update test thresholds if needed'
                ]
            });
        }
        
        this.results.recommendations = recommendations;
    }

    async generateReport() {
        const jsonReport = JSON.stringify(this.results, null, 2);
        const reportDir = './test-results/visual-regression';
        fs.writeFileSync(path.join(reportDir, 'visual-regression-report.json'), jsonReport);
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport();
        fs.writeFileSync(path.join(reportDir, 'visual-regression-report.html'), htmlReport);
    }

    generateHTMLReport() {
        const { summary, testResults, recommendations } = this.results;
        
        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets Visual Regression Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; line-height: 1.6; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; color: #2563eb; }
        .stat-value.success { color: #10b981; }
        .stat-value.warning { color: #f59e0b; }
        .stat-value.error { color: #ef4444; }
        .stat-label { color: #64748b; margin-top: 5px; }
        .page-results { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-top: 15px; }
        .test-item { border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; }
        .test-item.pass { border-left: 4px solid #10b981; background: #f0fdf4; }
        .test-item.fail { border-left: 4px solid #ef4444; background: #fef2f2; }
        .test-item.new { border-left: 4px solid #3b82f6; background: #eff6ff; }
        .test-item.error { border-left: 4px solid #f59e0b; background: #fffbeb; }
        .screenshot-comparison { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .screenshot-item { flex: 1; min-width: 150px; }
        .screenshot-item img { width: 100%; height: auto; border-radius: 4px; border: 1px solid #e2e8f0; }
        .screenshot-label { font-size: 0.8em; color: #64748b; margin-bottom: 5px; }
        .diff-info { font-size: 0.9em; color: #64748b; margin-top: 5px; }
        .recommendations { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .recommendation { margin-bottom: 15px; padding: 15px; border-radius: 6px; }
        .recommendation.critical { background: #fef2f2; border-left: 4px solid #ef4444; }
        .recommendation.warning { background: #fffbeb; border-left: 4px solid #f59e0b; }
        .timestamp { color: #64748b; font-size: 0.9em; }
        .success-rate { font-size: 1.2em; font-weight: bold; }
        .success-rate.high { color: #10b981; }
        .success-rate.medium { color: #f59e0b; }
        .success-rate.low { color: #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 HD Tickets Visual Regression Report</h1>
            <p class="timestamp">Generated on ${new Date(this.results.timestamp).toLocaleString()}</p>
            <div class="success-rate ${this.getSuccessRateClass(summary.successRate)}">
                Success Rate: ${summary.successRate.toFixed(1)}%
            </div>
        </div>
        
        <div class="summary">
            <div class="stat-card">
                <div class="stat-value">${summary.totalTests}</div>
                <div class="stat-label">Total Tests</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value success">${summary.passedTests}</div>
                <div class="stat-label">Passed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value error">${summary.failedTests}</div>
                <div class="stat-label">Failed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value warning">${summary.newBaselines}</div>
                <div class="stat-label">New Baselines</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.pages}</div>
                <div class="stat-label">Pages Tested</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value error">${summary.criticalFailures.length}</div>
                <div class="stat-label">Critical Failures</div>
            </div>
        </div>
        
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
        
        <div class="page-results">
            <h2>📊 Detailed Results</h2>
            ${Object.entries(testResults).map(([pageName, pageResults]) => `
                <div class="page-results">
                    <h3>${pageName} ${pageResults.overallStatus === 'fail' ? '❌' : pageResults.overallStatus === 'pass' ? '✅' : '⚠️'}</h3>
                    <p><strong>URL:</strong> ${pageResults.url}</p>
                    <p><strong>Total Differences:</strong> ${pageResults.totalDifferences}</p>
                    
                    <div class="test-grid">
                        ${Object.entries(pageResults.comparisons).map(([testKey, comparison]) => `
                            <div class="test-item ${comparison.status}">
                                <h4>${testKey.replace(/_/g, ' ')}</h4>
                                <p>${comparison.message}</p>
                                ${comparison.percentageDiff >= 0 ? `
                                    <div class="diff-info">
                                        Pixel Difference: ${comparison.pixelDiff} pixels (${comparison.percentageDiff.toFixed(2)}%)
                                    </div>
                                ` : ''}
                                
                                ${comparison.diffImagePath ? `
                                    <div class="screenshot-comparison">
                                        <div class="screenshot-item">
                                            <div class="screenshot-label">Current</div>
                                            <img src="current/${testKey}.png" alt="Current screenshot" onerror="this.style.display='none'">
                                        </div>
                                        <div class="screenshot-item">
                                            <div class="screenshot-label">Baseline</div>
                                            <img src="baselines/${testKey}.png" alt="Baseline screenshot" onerror="this.style.display='none'">
                                        </div>
                                        <div class="screenshot-item">
                                            <div class="screenshot-label">Difference</div>
                                            <img src="diffs/${testKey}.png" alt="Difference screenshot" onerror="this.style.display='none'">
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('')}
        </div>
    </div>
</body>
</html>`;
    }

    getSuccessRateClass(rate) {
        if (rate >= 95) return 'high';
        if (rate >= 80) return 'medium';
        return 'low';
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main execution
async function main() {
    const tester = new VisualRegressionTester();
    
    try {
        await tester.initialize();
        await tester.runAllTests();
    } catch (error) {
        console.error('❌ Visual regression testing failed:', error);
        process.exit(1);
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main();
}

module.exports = VisualRegressionTester;
