#!/usr/bin/env node

/**
 * HD Tickets Layout Performance Testing Suite
 * 
 * Comprehensive performance testing for all layout improvements including:
 * - Core Web Vitals (CLS, FID, LCP)
 * - Loading performance
 * - Rendering performance
 * - Memory usage
 * - Network efficiency
 */

const puppeteer = require('puppeteer');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

class LayoutPerformanceTester {
    constructor() {
        this.browser = null;
        this.results = {
            timestamp: new Date().toISOString(),
            testResults: {},
            summary: {},
            recommendations: []
        };
        
        // Test configuration
        this.testPages = [
            { name: 'Dashboard', url: '/dashboard', critical: true },
            { name: 'Tickets List', url: '/tickets', critical: true },
            { name: 'Ticket Details', url: '/tickets/1', critical: false },
            { name: 'User Profile', url: '/profile', critical: false },
            { name: 'Reports', url: '/reports', critical: false },
            { name: 'Settings', url: '/settings', critical: false }
        ];
        
        this.viewports = [
            { name: 'Mobile', width: 375, height: 667, deviceScaleFactor: 2 },
            { name: 'Tablet', width: 768, height: 1024, deviceScaleFactor: 1.5 },
            { name: 'Desktop', width: 1440, height: 900, deviceScaleFactor: 1 },
            { name: 'Large Desktop', width: 1920, height: 1080, deviceScaleFactor: 1 }
        ];
        
        // Performance thresholds (Web Vitals)
        this.thresholds = {
            lcp: { good: 2500, needsImprovement: 4000 }, // ms
            fid: { good: 100, needsImprovement: 300 },   // ms
            cls: { good: 0.1, needsImprovement: 0.25 },  // score
            ttfb: { good: 600, needsImprovement: 1500 }, // ms
            fcp: { good: 1800, needsImprovement: 3000 }  // ms
        };
    }

    async initialize() {
        console.log('🚀 Initializing HD Tickets Performance Testing Suite...');
        
        try {
            this.browser = await puppeteer.launch({
                headless: 'new',
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--no-first-run',
                    '--no-zygote',
                    '--disable-gpu'
                ]
            });
            console.log('✅ Browser initialized successfully');
        } catch (error) {
            console.error('❌ Failed to initialize browser:', error);
            throw error;
        }
    }

    async testPagePerformance(pageConfig, viewport) {
        const testKey = `${pageConfig.name}_${viewport.name}`;
        console.log(`\n📊 Testing ${pageConfig.name} on ${viewport.name}...`);
        
        const page = await this.browser.newPage();
        await page.setViewport(viewport);
        
        try {
            // Enable performance monitoring
            await page.evaluateOnNewDocument(() => {
                window.performanceMetrics = {
                    navigationStart: performance.now(),
                    loadEventEnd: null,
                    domContentLoaded: null,
                    firstPaint: null,
                    firstContentfulPaint: null,
                    largestContentfulPaint: null,
                    cumulativeLayoutShift: 0,
                    firstInputDelay: null,
                    totalBlockingTime: 0
                };
                
                // Monitor CLS
                let clsValue = 0;
                new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                        }
                    }
                    window.performanceMetrics.cumulativeLayoutShift = clsValue;
                }).observe({ type: 'layout-shift', buffered: true });
                
                // Monitor LCP
                new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    const lastEntry = entries[entries.length - 1];
                    window.performanceMetrics.largestContentfulPaint = lastEntry.startTime;
                }).observe({ type: 'largest-contentful-paint', buffered: true });
                
                // Monitor FID
                new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        window.performanceMetrics.firstInputDelay = entry.processingStart - entry.startTime;
                    }
                }).observe({ type: 'first-input', buffered: true });
                
                // Monitor paint metrics
                new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.name === 'first-paint') {
                            window.performanceMetrics.firstPaint = entry.startTime;
                        } else if (entry.name === 'first-contentful-paint') {
                            window.performanceMetrics.firstContentfulPaint = entry.startTime;
                        }
                    }
                }).observe({ type: 'paint', buffered: true });
            });
            
            // Start navigation and timing
            const navigationStart = Date.now();
            const response = await page.goto(`http://localhost${pageConfig.url}`, {
                waitUntil: 'networkidle2',
                timeout: 30000
            });
            
            if (!response || !response.ok()) {
                throw new Error(`Failed to load page: ${response ? response.status() : 'No response'}`);
            }
            
            // Wait for additional processing
            await page.waitForTimeout(2000);
            
            // Collect performance metrics
            const metrics = await page.evaluate(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                const paint = performance.getEntriesByType('paint');
                
                return {
                    ...window.performanceMetrics,
                    // Navigation timing
                    domainLookupStart: navigation.domainLookupStart,
                    domainLookupEnd: navigation.domainLookupEnd,
                    connectStart: navigation.connectStart,
                    connectEnd: navigation.connectEnd,
                    requestStart: navigation.requestStart,
                    responseStart: navigation.responseStart,
                    responseEnd: navigation.responseEnd,
                    domInteractive: navigation.domInteractive,
                    domContentLoadedEventStart: navigation.domContentLoadedEventStart,
                    domContentLoadedEventEnd: navigation.domContentLoadedEventEnd,
                    domComplete: navigation.domComplete,
                    loadEventStart: navigation.loadEventStart,
                    loadEventEnd: navigation.loadEventEnd,
                    
                    // Resource loading
                    transferSize: navigation.transferSize,
                    encodedBodySize: navigation.encodedBodySize,
                    decodedBodySize: navigation.decodedBodySize,
                    
                    // Calculated metrics
                    ttfb: navigation.responseStart - navigation.requestStart,
                    domProcessingTime: navigation.domContentLoadedEventStart - navigation.responseEnd,
                    totalPageLoadTime: navigation.loadEventEnd - navigation.fetchStart
                };
            });
            
            // Collect resource metrics
            const resourceMetrics = await page.evaluate(() => {
                const resources = performance.getEntriesByType('resource');
                return {
                    totalResources: resources.length,
                    cssResources: resources.filter(r => r.name.includes('.css')).length,
                    jsResources: resources.filter(r => r.name.includes('.js')).length,
                    imageResources: resources.filter(r => r.initiatorType === 'img').length,
                    totalTransferSize: resources.reduce((sum, r) => sum + (r.transferSize || 0), 0),
                    slowestResource: resources.reduce((slowest, r) => 
                        r.duration > (slowest?.duration || 0) ? r : slowest, null
                    )
                };
            });
            
            // Take screenshot for visual verification
            const screenshotPath = `./test-results/screenshots/${testKey}_${Date.now()}.png`;
            await page.screenshot({ 
                path: screenshotPath, 
                fullPage: true,
                type: 'png'
            });
            
            // Memory usage
            const memoryUsage = await page.evaluate(() => {
                if (performance.memory) {
                    return {
                        usedJSHeapSize: performance.memory.usedJSHeapSize,
                        totalJSHeapSize: performance.memory.totalJSHeapSize,
                        jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
                    };
                }
                return null;
            });
            
            // Compile test results
            const result = {
                page: pageConfig.name,
                viewport: viewport.name,
                url: pageConfig.url,
                timestamp: new Date().toISOString(),
                navigationTime: Date.now() - navigationStart,
                screenshot: screenshotPath,
                
                // Core Web Vitals
                webVitals: {
                    lcp: metrics.largestContentfulPaint,
                    fid: metrics.firstInputDelay,
                    cls: metrics.cumulativeLayoutShift,
                    fcp: metrics.firstContentfulPaint,
                    ttfb: metrics.ttfb
                },
                
                // Performance scores
                scores: this.calculatePerformanceScores({
                    lcp: metrics.largestContentfulPaint,
                    fid: metrics.firstInputDelay || 0,
                    cls: metrics.cumulativeLayoutShift,
                    ttfb: metrics.ttfb,
                    fcp: metrics.firstContentfulPaint
                }),
                
                // Additional metrics
                metrics: {
                    ...metrics,
                    memory: memoryUsage,
                    resources: resourceMetrics
                }
            };
            
            this.results.testResults[testKey] = result;
            
            // Log key metrics
            console.log(`  LCP: ${metrics.largestContentfulPaint?.toFixed(0) || 'N/A'}ms`);
            console.log(`  CLS: ${metrics.cumulativeLayoutShift?.toFixed(3) || 'N/A'}`);
            console.log(`  TTFB: ${metrics.ttfb?.toFixed(0) || 'N/A'}ms`);
            console.log(`  Total Load: ${metrics.totalPageLoadTime?.toFixed(0) || 'N/A'}ms`);
            
        } catch (error) {
            console.error(`❌ Error testing ${pageConfig.name} on ${viewport.name}:`, error);
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

    calculatePerformanceScores(metrics) {
        const scores = {};
        
        // LCP Score (0-100)
        if (metrics.lcp) {
            if (metrics.lcp <= this.thresholds.lcp.good) {
                scores.lcp = 100;
            } else if (metrics.lcp <= this.thresholds.lcp.needsImprovement) {
                scores.lcp = Math.round(100 - ((metrics.lcp - this.thresholds.lcp.good) / (this.thresholds.lcp.needsImprovement - this.thresholds.lcp.good)) * 50);
            } else {
                scores.lcp = Math.max(0, 50 - ((metrics.lcp - this.thresholds.lcp.needsImprovement) / this.thresholds.lcp.needsImprovement) * 50);
            }
        }
        
        // FID Score (0-100)
        if (metrics.fid !== null && metrics.fid !== undefined) {
            if (metrics.fid <= this.thresholds.fid.good) {
                scores.fid = 100;
            } else if (metrics.fid <= this.thresholds.fid.needsImprovement) {
                scores.fid = Math.round(100 - ((metrics.fid - this.thresholds.fid.good) / (this.thresholds.fid.needsImprovement - this.thresholds.fid.good)) * 50);
            } else {
                scores.fid = Math.max(0, 50 - ((metrics.fid - this.thresholds.fid.needsImprovement) / this.thresholds.fid.needsImprovement) * 50);
            }
        }
        
        // CLS Score (0-100)
        if (metrics.cls !== null && metrics.cls !== undefined) {
            if (metrics.cls <= this.thresholds.cls.good) {
                scores.cls = 100;
            } else if (metrics.cls <= this.thresholds.cls.needsImprovement) {
                scores.cls = Math.round(100 - ((metrics.cls - this.thresholds.cls.good) / (this.thresholds.cls.needsImprovement - this.thresholds.cls.good)) * 50);
            } else {
                scores.cls = Math.max(0, 50 - ((metrics.cls - this.thresholds.cls.needsImprovement) / this.thresholds.cls.needsImprovement) * 50);
            }
        }
        
        // Overall score
        const validScores = Object.values(scores).filter(s => s !== null && s !== undefined);
        scores.overall = validScores.length > 0 ? Math.round(validScores.reduce((sum, score) => sum + score, 0) / validScores.length) : 0;
        
        return scores;
    }

    async runAllTests() {
        console.log('\n🧪 Running comprehensive performance tests...');
        
        // Ensure results directory exists
        const resultsDir = './test-results';
        const screenshotsDir = './test-results/screenshots';
        
        if (!fs.existsSync(resultsDir)) {
            fs.mkdirSync(resultsDir, { recursive: true });
        }
        if (!fs.existsSync(screenshotsDir)) {
            fs.mkdirSync(screenshotsDir, { recursive: true });
        }
        
        // Test each page on each viewport
        for (const page of this.testPages) {
            for (const viewport of this.viewports) {
                await this.testPagePerformance(page, viewport);
            }
        }
        
        this.generateSummary();
        this.generateRecommendations();
        
        console.log('\n📝 Generating performance report...');
        await this.generateReport();
        
        console.log('\n✅ Performance testing complete!');
        console.log(`📊 Results saved to: ${resultsDir}/performance-report.json`);
        console.log(`📈 HTML report: ${resultsDir}/performance-report.html`);
    }

    generateSummary() {
        const results = Object.values(this.results.testResults).filter(r => !r.error);
        
        if (results.length === 0) {
            this.results.summary = { error: 'No successful test results' };
            return;
        }
        
        // Calculate averages
        const avgLCP = this.calculateAverage(results, 'webVitals.lcp');
        const avgCLS = this.calculateAverage(results, 'webVitals.cls');
        const avgTTFB = this.calculateAverage(results, 'webVitals.ttfb');
        const avgOverallScore = this.calculateAverage(results, 'scores.overall');
        
        // Count by performance level
        const performanceLevels = {
            excellent: results.filter(r => r.scores?.overall >= 90).length,
            good: results.filter(r => r.scores?.overall >= 70 && r.scores?.overall < 90).length,
            needsImprovement: results.filter(r => r.scores?.overall >= 50 && r.scores?.overall < 70).length,
            poor: results.filter(r => r.scores?.overall < 50).length
        };
        
        this.results.summary = {
            totalTests: results.length,
            successfulTests: results.length,
            failedTests: Object.values(this.results.testResults).filter(r => r.error).length,
            averageMetrics: {
                lcp: avgLCP,
                cls: avgCLS,
                ttfb: avgTTFB,
                overallScore: avgOverallScore
            },
            performanceLevels,
            overallGrade: this.getOverallGrade(avgOverallScore)
        };
    }

    calculateAverage(results, path) {
        const values = results.map(r => this.getNestedValue(r, path)).filter(v => v !== null && v !== undefined && !isNaN(v));
        return values.length > 0 ? values.reduce((sum, val) => sum + val, 0) / values.length : null;
    }

    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => current && current[key], obj);
    }

    getOverallGrade(score) {
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
        
        // LCP recommendations
        const avgLCP = this.calculateAverage(results, 'webVitals.lcp');
        if (avgLCP > this.thresholds.lcp.needsImprovement) {
            recommendations.push({
                type: 'critical',
                metric: 'LCP',
                issue: `Average LCP of ${avgLCP.toFixed(0)}ms exceeds threshold`,
                suggestions: [
                    'Optimize images with WebP format and proper sizing',
                    'Implement critical CSS inlining',
                    'Consider CDN for static assets',
                    'Optimize server response times',
                    'Preload key resources'
                ]
            });
        }
        
        // CLS recommendations  
        const avgCLS = this.calculateAverage(results, 'webVitals.cls');
        if (avgCLS > this.thresholds.cls.needsImprovement) {
            recommendations.push({
                type: 'critical',
                metric: 'CLS',
                issue: `Average CLS of ${avgCLS.toFixed(3)} indicates layout instability`,
                suggestions: [
                    'Add explicit dimensions to images and videos',
                    'Reserve space for dynamic content',
                    'Use CSS aspect-ratio for responsive media',
                    'Avoid inserting content above existing content',
                    'Use transform instead of changing layout properties'
                ]
            });
        }
        
        // Resource optimization
        const avgResources = this.calculateAverage(results, 'metrics.resources.totalResources');
        if (avgResources > 100) {
            recommendations.push({
                type: 'warning',
                metric: 'Resources',
                issue: `High number of resources (${avgResources.toFixed(0)}) may impact performance`,
                suggestions: [
                    'Bundle and minify CSS and JavaScript',
                    'Implement resource hints (preload, prefetch)',
                    'Use image sprites for small icons',
                    'Enable HTTP/2 server push',
                    'Implement service worker caching'
                ]
            });
        }
        
        // Mobile performance
        const mobileResults = results.filter(r => r.viewport === 'Mobile');
        const avgMobileScore = this.calculateAverage(mobileResults, 'scores.overall');
        if (avgMobileScore < 70) {
            recommendations.push({
                type: 'warning',
                metric: 'Mobile Performance',
                issue: `Mobile performance score of ${avgMobileScore.toFixed(0)} needs improvement`,
                suggestions: [
                    'Implement mobile-first responsive design',
                    'Optimize touch interactions',
                    'Reduce JavaScript bundle size',
                    'Use intersection observer for lazy loading',
                    'Minimize third-party scripts'
                ]
            });
        }
        
        this.results.recommendations = recommendations;
    }

    async generateReport() {
        const jsonReport = JSON.stringify(this.results, null, 2);
        fs.writeFileSync('./test-results/performance-report.json', jsonReport);
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport();
        fs.writeFileSync('./test-results/performance-report.html', htmlReport);
        
        // Generate CSV for analysis
        const csvReport = this.generateCSVReport();
        fs.writeFileSync('./test-results/performance-report.csv', csvReport);
    }

    generateHTMLReport() {
        const { summary, testResults, recommendations } = this.results;
        
        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets Performance Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; color: #2563eb; }
        .stat-label { color: #64748b; margin-top: 5px; }
        .grade { font-size: 3em; font-weight: bold; padding: 20px; border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .grade.A { background: #10b981; color: white; }
        .grade.B { background: #059669; color: white; }
        .grade.C { background: #fbbf24; color: white; }
        .grade.D { background: #f59e0b; color: white; }
        .grade.F { background: #ef4444; color: white; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        .results-table th, .results-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .results-table th { background: #f8fafc; font-weight: 600; }
        .recommendations { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .recommendation { margin-bottom: 15px; padding: 15px; border-radius: 6px; }
        .recommendation.critical { background: #fef2f2; border-left: 4px solid #ef4444; }
        .recommendation.warning { background: #fffbeb; border-left: 4px solid #f59e0b; }
        .metric-good { color: #10b981; }
        .metric-warning { color: #f59e0b; }
        .metric-poor { color: #ef4444; }
        .timestamp { color: #64748b; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 HD Tickets Performance Report</h1>
            <p class="timestamp">Generated on ${new Date(this.results.timestamp).toLocaleString()}</p>
        </div>
        
        <div class="summary">
            <div class="stat-card">
                <div class="grade ${summary.overallGrade}">${summary.overallGrade}</div>
                <div class="stat-label">Overall Grade</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.averageMetrics.overallScore?.toFixed(0) || 'N/A'}</div>
                <div class="stat-label">Average Score</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value ${this.getMetricClass(summary.averageMetrics.lcp, 'lcp')}">${summary.averageMetrics.lcp?.toFixed(0) || 'N/A'}ms</div>
                <div class="stat-label">Average LCP</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value ${this.getMetricClass(summary.averageMetrics.cls, 'cls')}">${summary.averageMetrics.cls?.toFixed(3) || 'N/A'}</div>
                <div class="stat-label">Average CLS</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.successfulTests}</div>
                <div class="stat-label">Tests Passed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value">${summary.failedTests}</div>
                <div class="stat-label">Tests Failed</div>
            </div>
        </div>
        
        ${recommendations.length > 0 ? `
        <div class="recommendations">
            <h2>🔍 Recommendations</h2>
            ${recommendations.map(rec => `
                <div class="recommendation ${rec.type}">
                    <h3>${rec.metric}: ${rec.issue}</h3>
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
                        <th>Score</th>
                        <th>LCP (ms)</th>
                        <th>CLS</th>
                        <th>TTFB (ms)</th>
                        <th>Load Time (ms)</th>
                    </tr>
                </thead>
                <tbody>
                    ${Object.values(testResults).filter(r => !r.error).map(result => `
                        <tr>
                            <td>${result.page}</td>
                            <td>${result.viewport}</td>
                            <td>${result.scores?.overall || 'N/A'}</td>
                            <td class="${this.getMetricClass(result.webVitals?.lcp, 'lcp')}">${result.webVitals?.lcp?.toFixed(0) || 'N/A'}</td>
                            <td class="${this.getMetricClass(result.webVitals?.cls, 'cls')}">${result.webVitals?.cls?.toFixed(3) || 'N/A'}</td>
                            <td class="${this.getMetricClass(result.webVitals?.ttfb, 'ttfb')}">${result.webVitals?.ttfb?.toFixed(0) || 'N/A'}</td>
                            <td>${result.metrics?.totalPageLoadTime?.toFixed(0) || 'N/A'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>`;
    }

    getMetricClass(value, metric) {
        if (!value) return '';
        
        const thresholds = this.thresholds[metric];
        if (!thresholds) return '';
        
        if (value <= thresholds.good) return 'metric-good';
        if (value <= thresholds.needsImprovement) return 'metric-warning';
        return 'metric-poor';
    }

    generateCSVReport() {
        const results = Object.values(this.results.testResults).filter(r => !r.error);
        if (results.length === 0) return '';
        
        const headers = [
            'Page', 'Viewport', 'Timestamp', 'Overall Score',
            'LCP', 'FID', 'CLS', 'FCP', 'TTFB',
            'Total Resources', 'CSS Resources', 'JS Resources',
            'Transfer Size', 'Load Time', 'DOM Processing Time'
        ];
        
        const rows = results.map(result => [
            result.page,
            result.viewport,
            result.timestamp,
            result.scores?.overall || '',
            result.webVitals?.lcp || '',
            result.webVitals?.fid || '',
            result.webVitals?.cls || '',
            result.webVitals?.fcp || '',
            result.webVitals?.ttfb || '',
            result.metrics?.resources?.totalResources || '',
            result.metrics?.resources?.cssResources || '',
            result.metrics?.resources?.jsResources || '',
            result.metrics?.resources?.totalTransferSize || '',
            result.metrics?.totalPageLoadTime || '',
            result.metrics?.domProcessingTime || ''
        ]);
        
        return [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Main execution
async function main() {
    const tester = new LayoutPerformanceTester();
    
    try {
        await tester.initialize();
        await tester.runAllTests();
    } catch (error) {
        console.error('❌ Performance testing failed:', error);
        process.exit(1);
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main();
}

module.exports = LayoutPerformanceTester;
