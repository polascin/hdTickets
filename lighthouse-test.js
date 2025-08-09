#!/usr/bin/env node

/**
 * HD Tickets Dashboard - Lighthouse Performance Testing
 * Automated performance, accessibility, SEO, and best practices testing
 */

const lighthouse = require('lighthouse');
const chromeLauncher = require('chrome-launcher');
const fs = require('fs');
const path = require('path');

class LighthouseTester {
    constructor() {
        this.testUrl = 'http://localhost/hdtickets/public/';
        this.chrome = null;
        this.results = {};
        
        console.log('🚀 HD Tickets Dashboard - Lighthouse Performance Testing');
        console.log('=' + '='.repeat(60));
    }

    async run() {
        try {
            await this.launchChrome();
            await this.runDesktopTests();
            await this.runMobileTests();
            await this.closeChrome();
            await this.generateReport();
        } catch (error) {
            console.error('❌ Testing failed:', error.message);
            if (this.chrome) {
                await this.chrome.kill();
            }
        }
    }

    async launchChrome() {
        console.log('\n🌐 Launching Chrome...');
        this.chrome = await chromeLauncher.launch({
            chromeFlags: [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage'
            ]
        });
        console.log('✅ Chrome launched on port:', this.chrome.port);
    }

    async closeChrome() {
        if (this.chrome) {
            await this.chrome.kill();
            console.log('🔒 Chrome closed');
        }
    }

    async runDesktopTests() {
        console.log('\n🖥️  Running Desktop Performance Tests...');
        
        const desktopConfig = {
            extends: 'lighthouse:default',
            settings: {
                formFactor: 'desktop',
                throttling: {
                    rttMs: 40,
                    throughputKbps: 10 * 1024,
                    cpuSlowdownMultiplier: 1,
                    requestLatencyMs: 0,
                    downloadThroughputKbps: 0,
                    uploadThroughputKbps: 0,
                },
                screenEmulation: {
                    mobile: false,
                    width: 1350,
                    height: 940,
                    deviceScaleFactor: 1,
                    disabled: false,
                },
                emulatedUserAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.109 Safari/537.36'
            }
        };

        const desktopResult = await lighthouse(this.testUrl, {
            port: this.chrome.port,
            disableStorageReset: false,
        }, desktopConfig);

        this.results.desktop = this.extractMetrics(desktopResult);
        console.log('📊 Desktop tests completed');
    }

    async runMobileTests() {
        console.log('\n📱 Running Mobile Performance Tests...');
        
        const mobileConfig = {
            extends: 'lighthouse:default',
            settings: {
                formFactor: 'mobile',
                throttling: {
                    rttMs: 150,
                    throughputKbps: 1.6 * 1024,
                    cpuSlowdownMultiplier: 4,
                    requestLatencyMs: 0,
                    downloadThroughputKbps: 0,
                    uploadThroughputKbps: 0,
                },
                screenEmulation: {
                    mobile: true,
                    width: 375,
                    height: 667,
                    deviceScaleFactor: 2,
                    disabled: false,
                },
                emulatedUserAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1'
            }
        };

        const mobileResult = await lighthouse(this.testUrl, {
            port: this.chrome.port,
            disableStorageReset: false,
        }, mobileConfig);

        this.results.mobile = this.extractMetrics(mobileResult);
        console.log('📊 Mobile tests completed');
    }

    extractMetrics(lighthouseResult) {
        const lhr = lighthouseResult.lhr;
        
        return {
            scores: {
                performance: Math.round(lhr.categories.performance.score * 100),
                accessibility: Math.round(lhr.categories.accessibility.score * 100),
                bestPractices: Math.round(lhr.categories['best-practices'].score * 100),
                seo: Math.round(lhr.categories.seo.score * 100),
                pwa: lhr.categories.pwa ? Math.round(lhr.categories.pwa.score * 100) : 'N/A'
            },
            metrics: {
                firstContentfulPaint: lhr.audits['first-contentful-paint'].numericValue,
                largestContentfulPaint: lhr.audits['largest-contentful-paint'].numericValue,
                firstInputDelay: lhr.audits['max-potential-fid'].numericValue,
                cumulativeLayoutShift: lhr.audits['cumulative-layout-shift'].numericValue,
                speedIndex: lhr.audits['speed-index'].numericValue,
                totalBlockingTime: lhr.audits['total-blocking-time'].numericValue
            },
            opportunities: lhr.audits['unused-css-rules'] ? {
                unusedCSS: lhr.audits['unused-css-rules'].details?.items?.length || 0,
                unusedJavaScript: lhr.audits['unused-javascript'] ? lhr.audits['unused-javascript'].details?.items?.length || 0 : 0,
                unoptimizedImages: lhr.audits['uses-optimized-images'] ? lhr.audits['uses-optimized-images'].details?.items?.length || 0 : 0
            } : {}
        };
    }

    async generateReport() {
        console.log('\n' + '='.repeat(70));
        console.log('📊 LIGHTHOUSE PERFORMANCE TEST REPORT');
        console.log('='.repeat(70));
        
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        
        // Desktop Results
        console.log('\n🖥️  DESKTOP PERFORMANCE:');
        console.log('─'.repeat(30));
        this.printScores(this.results.desktop.scores);
        this.printMetrics('Desktop', this.results.desktop.metrics);
        
        // Mobile Results
        console.log('\n📱 MOBILE PERFORMANCE:');
        console.log('─'.repeat(30));
        this.printScores(this.results.mobile.scores);
        this.printMetrics('Mobile', this.results.mobile.metrics);
        
        // Recommendations
        console.log('\n🔍 PERFORMANCE ANALYSIS:');
        console.log('─'.repeat(30));
        this.analyzeResults();
        
        // Save detailed report
        const detailedReport = {
            timestamp: new Date().toISOString(),
            url: this.testUrl,
            results: this.results
        };
        
        const reportPath = path.join(__dirname, `lighthouse-report-${timestamp}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(detailedReport, null, 2));
        console.log(`\n💾 Detailed report saved: ${reportPath}`);
        
        console.log('\n' + '='.repeat(70));
        console.log(`Testing completed at ${new Date().toLocaleString()}`);
        console.log('='.repeat(70));
    }

    printScores(scores) {
        Object.entries(scores).forEach(([category, score]) => {
            const emoji = this.getScoreEmoji(score);
            const categoryName = category.charAt(0).toUpperCase() + category.slice(1).replace(/([A-Z])/g, ' $1');
            console.log(`${emoji} ${categoryName}: ${score}${typeof score === 'number' ? '/100' : ''}`);
        });
    }

    printMetrics(device, metrics) {
        console.log(`\n⚡ ${device} Core Web Vitals:`);
        console.log(`   • First Contentful Paint: ${Math.round(metrics.firstContentfulPaint)}ms ${this.getMetricStatus('fcp', metrics.firstContentfulPaint)}`);
        console.log(`   • Largest Contentful Paint: ${Math.round(metrics.largestContentfulPaint)}ms ${this.getMetricStatus('lcp', metrics.largestContentfulPaint)}`);
        console.log(`   • First Input Delay: ${Math.round(metrics.firstInputDelay)}ms ${this.getMetricStatus('fid', metrics.firstInputDelay)}`);
        console.log(`   • Cumulative Layout Shift: ${metrics.cumulativeLayoutShift.toFixed(3)} ${this.getMetricStatus('cls', metrics.cumulativeLayoutShift)}`);
        console.log(`   • Speed Index: ${Math.round(metrics.speedIndex)}ms`);
        console.log(`   • Total Blocking Time: ${Math.round(metrics.totalBlockingTime)}ms`);
    }

    getScoreEmoji(score) {
        if (typeof score !== 'number') return '📊';
        if (score >= 90) return '🟢';
        if (score >= 70) return '🟡';
        return '🔴';
    }

    getMetricStatus(metric, value) {
        const thresholds = {
            fcp: { good: 1800, poor: 3000 },
            lcp: { good: 2500, poor: 4000 },
            fid: { good: 100, poor: 300 },
            cls: { good: 0.1, poor: 0.25 }
        };

        const threshold = thresholds[metric];
        if (!threshold) return '';

        if (value <= threshold.good) return '🟢';
        if (value <= threshold.poor) return '🟡';
        return '🔴';
    }

    analyzeResults() {
        const desktopPerf = this.results.desktop.scores.performance;
        const mobilePerf = this.results.mobile.scores.performance;
        const desktopA11y = this.results.desktop.scores.accessibility;
        const mobileA11y = this.results.mobile.scores.accessibility;

        console.log('📈 Overall Assessment:');
        
        if (desktopPerf >= 90 && mobilePerf >= 90) {
            console.log('   🎉 EXCELLENT performance on both desktop and mobile!');
        } else if (desktopPerf >= 70 && mobilePerf >= 70) {
            console.log('   ✅ GOOD performance with room for optimization');
        } else {
            console.log('   ⚠️  Performance needs improvement');
        }

        if (desktopA11y >= 90 && mobileA11y >= 90) {
            console.log('   ♿ Accessibility standards well met');
        } else {
            console.log('   ♿ Accessibility can be improved');
        }

        console.log('\n💡 Key Recommendations:');
        
        if (mobilePerf < desktopPerf) {
            console.log('   • Focus on mobile performance optimization');
        }
        
        if (this.results.mobile.metrics.largestContentfulPaint > 2500) {
            console.log('   • Optimize Largest Contentful Paint (LCP)');
        }
        
        if (this.results.mobile.metrics.cumulativeLayoutShift > 0.1) {
            console.log('   • Reduce Cumulative Layout Shift (CLS)');
        }
        
        if (this.results.mobile.scores.accessibility < 90) {
            console.log('   • Improve accessibility features (ARIA labels, alt text)');
        }
    }
}

// Run the tests if called directly
if (require.main === module) {
    const tester = new LighthouseTester();
    tester.run().catch(console.error);
}

module.exports = LighthouseTester;
