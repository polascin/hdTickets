#!/usr/bin/env node

/**
 * HD Tickets Comprehensive Layout Testing Suite
 * 
 * Master test runner that orchestrates all testing phases:
 * - Performance Testing (Core Web Vitals, loading metrics)
 * - Accessibility Testing (WCAG 2.1 AA compliance)
 * - Visual Regression Testing (cross-browser, responsive)
 * - Generates comprehensive final report
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Import our test suites
const PerformanceTester = require('./test-performance');
const AccessibilityTester = require('./test-accessibility');
const VisualRegressionTester = require('./test-visual-regression');

class ComprehensiveLayoutTester {
    constructor() {
        this.results = {
            timestamp: new Date().toISOString(),
            testSuites: {},
            overallSummary: {},
            criticalIssues: [],
            recommendations: [],
            grade: null
        };
        
        this.outputPath = './test-results';
        this.finalReportPath = path.join(this.outputPath, 'comprehensive-layout-report.html');
    }

    async initialize() {
        console.log('🚀 HD Tickets Comprehensive Layout Testing Suite');
        console.log('==================================================');
        console.log('Running complete validation of layout improvements...\n');
        
        // Ensure output directory exists
        if (!fs.existsSync(this.outputPath)) {
            fs.mkdirSync(this.outputPath, { recursive: true });
        }
        
        // Check if dependencies are installed
        await this.checkDependencies();
        
        console.log('✅ Test suite initialized\n');
    }

    async checkDependencies() {
        const requiredPackages = [
            'puppeteer',
            'axe-core', 
            'pixelmatch',
            'pngjs'
        ];
        
        const missingPackages = [];
        
        for (const pkg of requiredPackages) {
            try {
                require.resolve(pkg);
            } catch (e) {
                missingPackages.push(pkg);
            }
        }
        
        if (missingPackages.length > 0) {
            console.log('⚠️  Installing missing test dependencies...');
            try {
                execSync(`npm install ${missingPackages.join(' ')}`, { 
                    stdio: 'inherit',
                    cwd: process.cwd()
                });
                console.log('✅ Dependencies installed');
            } catch (error) {
                console.error('❌ Failed to install dependencies:', error.message);
                console.log('\nPlease run manually:');
                console.log(`npm install ${missingPackages.join(' ')}`);
                process.exit(1);
            }
        }
    }

    async runPerformanceTesting() {
        console.log('\n📊 PHASE 1: Performance Testing');
        console.log('================================');
        
        try {
            const performanceTester = new PerformanceTester();
            await performanceTester.initialize();
            await performanceTester.runAllTests();
            await performanceTester.cleanup();
            
            // Load results
            const resultsPath = path.join(this.outputPath, 'performance-report.json');
            if (fs.existsSync(resultsPath)) {
                this.results.testSuites.performance = JSON.parse(fs.readFileSync(resultsPath, 'utf8'));
                console.log('✅ Performance testing completed');
            }
        } catch (error) {
            console.error('❌ Performance testing failed:', error.message);
            this.results.testSuites.performance = { error: error.message };
        }
    }

    async runAccessibilityTesting() {
        console.log('\n♿ PHASE 2: Accessibility Testing');
        console.log('=================================');
        
        try {
            const accessibilityTester = new AccessibilityTester();
            await accessibilityTester.initialize();
            await accessibilityTester.runAllTests();
            await accessibilityTester.cleanup();
            
            // Load results
            const resultsPath = path.join(this.outputPath, 'accessibility', 'accessibility-report.json');
            if (fs.existsSync(resultsPath)) {
                this.results.testSuites.accessibility = JSON.parse(fs.readFileSync(resultsPath, 'utf8'));
                console.log('✅ Accessibility testing completed');
            }
        } catch (error) {
            console.error('❌ Accessibility testing failed:', error.message);
            this.results.testSuites.accessibility = { error: error.message };
        }
    }

    async runVisualRegressionTesting() {
        console.log('\n📸 PHASE 3: Visual Regression Testing');
        console.log('=====================================');
        
        try {
            const visualTester = new VisualRegressionTester();
            await visualTester.initialize();
            await visualTester.runAllTests();
            await visualTester.cleanup();
            
            // Load results
            const resultsPath = path.join(this.outputPath, 'visual-regression', 'visual-regression-report.json');
            if (fs.existsSync(resultsPath)) {
                this.results.testSuites.visualRegression = JSON.parse(fs.readFileSync(resultsPath, 'utf8'));
                console.log('✅ Visual regression testing completed');
            }
        } catch (error) {
            console.error('❌ Visual regression testing failed:', error.message);
            this.results.testSuites.visualRegression = { error: error.message };
        }
    }

    generateOverallSummary() {
        console.log('\n📋 PHASE 4: Analysis & Summary');
        console.log('==============================');
        
        const { performance, accessibility, visualRegression } = this.results.testSuites;
        
        // Overall metrics
        let totalTests = 0;
        let passedTests = 0;
        let failedTests = 0;
        
        // Performance metrics
        const performanceScore = performance?.summary?.averageMetrics?.overallScore || 0;
        const performanceGrade = performance?.summary?.overallGrade || 'F';
        
        if (performance?.summary) {
            totalTests += performance.summary.successfulTests || 0;
            failedTests += performance.summary.failedTests || 0;
        }
        
        // Accessibility metrics
        const accessibilityScore = accessibility?.summary?.averageScore || 0;
        const accessibilityGrade = accessibility?.summary?.grade || 'F';
        const wcagCompliance = accessibility?.summary?.wcagCompliance || 'Non-compliant';
        
        if (accessibility?.summary) {
            totalTests += accessibility.summary.successfulTests || 0;
            failedTests += accessibility.summary.failedTests || 0;
        }
        
        // Visual regression metrics
        const visualSuccessRate = visualRegression?.summary?.successRate || 0;
        const visualFailures = visualRegression?.summary?.failedTests || 0;
        
        if (visualRegression?.summary) {
            totalTests += visualRegression.summary.totalTests || 0;
            failedTests += visualRegression.summary.failedTests || 0;
            passedTests += visualRegression.summary.passedTests || 0;
        }
        
        // Calculate overall grade
        const overallScore = (performanceScore + accessibilityScore + visualSuccessRate) / 3;
        const overallGrade = this.calculateOverallGrade(overallScore);
        
        // Critical issues compilation
        const criticalIssues = [];
        
        // Performance critical issues
        if (performanceScore < 70) {
            criticalIssues.push({
                category: 'Performance',
                severity: 'critical',
                issue: `Low performance score: ${performanceScore}/100`,
                impact: 'User experience severely affected'
            });
        }
        
        // Accessibility critical issues
        if (accessibility?.summary?.violationsByImpact?.critical > 0) {
            criticalIssues.push({
                category: 'Accessibility',
                severity: 'critical',
                issue: `${accessibility.summary.violationsByImpact.critical} critical WCAG violations`,
                impact: 'Site inaccessible to users with disabilities'
            });
        }
        
        // Visual regression critical issues
        if (visualRegression?.summary?.criticalFailures?.length > 0) {
            criticalIssues.push({
                category: 'Visual',
                severity: 'critical',
                issue: `${visualRegression.summary.criticalFailures.length} critical visual regressions`,
                impact: 'Core pages have significant visual changes'
            });
        }
        
        // Compile recommendations
        const allRecommendations = [];
        if (performance?.recommendations) allRecommendations.push(...performance.recommendations);
        if (accessibility?.recommendations) allRecommendations.push(...accessibility.recommendations);
        if (visualRegression?.recommendations) allRecommendations.push(...visualRegression.recommendations);
        
        this.results.overallSummary = {
            totalTests,
            passedTests: totalTests - failedTests,
            failedTests,
            successRate: totalTests > 0 ? ((totalTests - failedTests) / totalTests) * 100 : 0,
            
            // Individual suite scores
            performanceScore,
            performanceGrade,
            accessibilityScore,
            accessibilityGrade,
            wcagCompliance,
            visualSuccessRate,
            
            // Overall metrics
            overallScore,
            overallGrade,
            
            // Issue counts
            criticalIssuesCount: criticalIssues.filter(i => i.severity === 'critical').length,
            totalRecommendations: allRecommendations.length
        };
        
        this.results.criticalIssues = criticalIssues;
        this.results.recommendations = allRecommendations;
        this.results.grade = overallGrade;
        
        console.log('✅ Analysis completed');
    }

    calculateOverallGrade(score) {
        if (score >= 90) return 'A+';
        if (score >= 85) return 'A';
        if (score >= 80) return 'A-';
        if (score >= 75) return 'B+';
        if (score >= 70) return 'B';
        if (score >= 65) return 'B-';
        if (score >= 60) return 'C+';
        if (score >= 55) return 'C';
        if (score >= 50) return 'C-';
        if (score >= 45) return 'D+';
        if (score >= 40) return 'D';
        return 'F';
    }

    async generateFinalReport() {
        console.log('\n📊 PHASE 5: Final Report Generation');
        console.log('===================================');
        
        // Save comprehensive JSON report
        const jsonReport = JSON.stringify(this.results, null, 2);
        fs.writeFileSync(path.join(this.outputPath, 'comprehensive-layout-report.json'), jsonReport);
        
        // Generate comprehensive HTML report
        const htmlReport = this.generateHTMLReport();
        fs.writeFileSync(this.finalReportPath, htmlReport);
        
        console.log('✅ Final report generated');
        console.log(`📄 HTML Report: ${this.finalReportPath}`);
        console.log(`📄 JSON Report: ${path.join(this.outputPath, 'comprehensive-layout-report.json')}`);
    }

    generateHTMLReport() {
        const { overallSummary, criticalIssues, recommendations, testSuites } = this.results;
        
        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets Comprehensive Layout Report</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: #333; line-height: 1.6; min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .header { 
            background: white; padding: 40px; border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px; text-align: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .header h1 { 
            margin: 0 0 10px 0; font-size: 2.5em; font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .timestamp { color: #666; font-size: 1.1em; }
        
        .grade-display { 
            display: inline-block; font-size: 4em; font-weight: bold; 
            width: 120px; height: 120px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            margin: 20px auto; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .grade-A\\+ { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .grade-A { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .grade-B { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .grade-C { background: linear-gradient(135deg, #fce38a 0%, #f38181 100%); }
        .grade-D { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
        .grade-F { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
        
        .summary-grid { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;
            transition: transform 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-value { font-size: 2.2em; font-weight: 700; margin-bottom: 5px; }
        .stat-label { color: #666; font-size: 0.95em; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .suite-results { 
            background: white; padding: 30px; border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 25px; 
        }
        .suite-header { 
            display: flex; align-items: center; margin-bottom: 20px; 
            padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;
        }
        .suite-icon { font-size: 2em; margin-right: 15px; }
        .suite-title { font-size: 1.5em; font-weight: 600; color: #333; }
        
        .metrics-row { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
            gap: 15px; margin: 15px 0; 
        }
        .metric { 
            padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center;
            border-left: 4px solid #007bff;
        }
        .metric.excellent { border-left-color: #28a745; background: #f8fff9; }
        .metric.good { border-left-color: #20c997; background: #f0fff4; }
        .metric.warning { border-left-color: #ffc107; background: #fffdf0; }
        .metric.poor { border-left-color: #dc3545; background: #fff5f5; }
        
        .critical-issues { 
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); 
            padding: 25px; border-radius: 12px; margin-bottom: 25px; color: #721c24;
        }
        .critical-issues h2 { margin-top: 0; }
        
        .issue { 
            background: rgba(255,255,255,0.9); padding: 15px; border-radius: 8px; 
            margin-bottom: 10px; border-left: 4px solid #dc3545;
        }
        
        .recommendations { 
            background: white; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 25px; 
        }
        .recommendation { 
            padding: 15px; border-radius: 8px; margin-bottom: 15px;
        }
        .recommendation.critical { background: #fff5f5; border-left: 4px solid #dc3545; }
        .recommendation.warning { background: #fffdf0; border-left: 4px solid #ffc107; }
        
        .footer { 
            text-align: center; padding: 30px; color: white; 
            background: rgba(255,255,255,0.1); border-radius: 12px; margin-top: 30px;
        }
        
        .progress-bar { 
            width: 100%; height: 8px; background: #e9ecef; border-radius: 4px; 
            overflow: hidden; margin: 10px 0;
        }
        .progress-fill { 
            height: 100%; transition: width 0.3s ease;
            background: linear-gradient(90deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
        }
        
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .header { padding: 20px; }
            .grade-display { width: 80px; height: 80px; font-size: 2.5em; }
            .suite-results { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 HD Tickets Layout Report</h1>
            <p class="timestamp">Generated ${new Date(this.results.timestamp).toLocaleString()}</p>
            <div class="grade-display grade-${overallSummary.overallGrade?.replace('+', '\\\\+')}">${overallSummary.overallGrade}</div>
            <p style="font-size: 1.2em; margin: 10px 0 0 0;">Overall Score: ${overallSummary.overallScore?.toFixed(1)}/100</p>
        </div>
        
        <div class="summary-grid">
            <div class="stat-card">
                <div class="stat-value" style="color: #007bff;">${overallSummary.totalTests}</div>
                <div class="stat-label">Total Tests</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%;"></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: #28a745;">${overallSummary.passedTests}</div>
                <div class="stat-label">Tests Passed</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${overallSummary.successRate}%; background: #28a745;"></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: #dc3545;">${overallSummary.failedTests}</div>
                <div class="stat-label">Tests Failed</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${100 - overallSummary.successRate}%; background: #dc3545;"></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: #ffc107;">${overallSummary.criticalIssuesCount}</div>
                <div class="stat-label">Critical Issues</div>
            </div>
        </div>
        
        ${criticalIssues.length > 0 ? `
        <div class="critical-issues">
            <h2>🚨 Critical Issues Requiring Immediate Attention</h2>
            ${criticalIssues.map(issue => `
                <div class="issue">
                    <h3>${issue.category}: ${issue.issue}</h3>
                    <p><strong>Impact:</strong> ${issue.impact}</p>
                </div>
            `).join('')}
        </div>
        ` : ''}
        
        <div class="suite-results">
            <div class="suite-header">
                <div class="suite-icon">📊</div>
                <div class="suite-title">Performance Testing Results</div>
            </div>
            
            <div class="metrics-row">
                <div class="metric ${this.getMetricClass(overallSummary.performanceScore)}">
                    <div style="font-size: 1.8em; font-weight: bold;">${overallSummary.performanceScore || 'N/A'}</div>
                    <div>Overall Score</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.5em; font-weight: bold;">${overallSummary.performanceGrade}</div>
                    <div>Grade</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.3em;">${testSuites.performance?.summary?.averageMetrics?.lcp?.toFixed(0) || 'N/A'}ms</div>
                    <div>Avg LCP</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.3em;">${testSuites.performance?.summary?.averageMetrics?.cls?.toFixed(3) || 'N/A'}</div>
                    <div>Avg CLS</div>
                </div>
            </div>
        </div>
        
        <div class="suite-results">
            <div class="suite-header">
                <div class="suite-icon">♿</div>
                <div class="suite-title">Accessibility Testing Results</div>
            </div>
            
            <div class="metrics-row">
                <div class="metric ${this.getMetricClass(overallSummary.accessibilityScore)}">
                    <div style="font-size: 1.8em; font-weight: bold;">${overallSummary.accessibilityScore?.toFixed(0) || 'N/A'}</div>
                    <div>Overall Score</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.5em; font-weight: bold;">${overallSummary.accessibilityGrade}</div>
                    <div>Grade</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.3em;">${overallSummary.wcagCompliance}</div>
                    <div>WCAG Compliance</div>
                </div>
                <div class="metric ${testSuites.accessibility?.summary?.totalViolations > 0 ? 'poor' : 'excellent'}">
                    <div style="font-size: 1.3em;">${testSuites.accessibility?.summary?.totalViolations || 0}</div>
                    <div>Total Violations</div>
                </div>
            </div>
        </div>
        
        <div class="suite-results">
            <div class="suite-header">
                <div class="suite-icon">📸</div>
                <div class="suite-title">Visual Regression Testing Results</div>
            </div>
            
            <div class="metrics-row">
                <div class="metric ${this.getMetricClass(overallSummary.visualSuccessRate)}">
                    <div style="font-size: 1.8em; font-weight: bold;">${overallSummary.visualSuccessRate?.toFixed(1) || 'N/A'}%</div>
                    <div>Success Rate</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.3em;">${testSuites.visualRegression?.summary?.passedTests || 0}</div>
                    <div>Tests Passed</div>
                </div>
                <div class="metric ${testSuites.visualRegression?.summary?.failedTests > 0 ? 'poor' : 'excellent'}">
                    <div style="font-size: 1.3em;">${testSuites.visualRegression?.summary?.failedTests || 0}</div>
                    <div>Tests Failed</div>
                </div>
                <div class="metric">
                    <div style="font-size: 1.3em;">${testSuites.visualRegression?.summary?.newBaselines || 0}</div>
                    <div>New Baselines</div>
                </div>
            </div>
        </div>
        
        ${recommendations.length > 0 ? `
        <div class="recommendations">
            <h2>💡 Recommendations</h2>
            ${recommendations.slice(0, 10).map(rec => `
                <div class="recommendation ${rec.type}">
                    <h3>${rec.category || rec.metric}: ${rec.issue}</h3>
                    <ul>
                        ${(rec.suggestions || []).slice(0, 3).map(s => `<li>${s}</li>`).join('')}
                    </ul>
                </div>
            `).join('')}
        </div>
        ` : ''}
        
        <div class="footer">
            <h3>🎉 Layout Testing Complete</h3>
            <p>Comprehensive validation of HD Tickets layout improvements has been completed.</p>
            <p>Review the individual test reports for detailed analysis and specific recommendations.</p>
            <p style="margin-top: 20px; font-size: 0.9em; opacity: 0.8;">
                Generated by HD Tickets Layout Testing Suite v1.0
            </p>
        </div>
    </div>
</body>
</html>`;
    }

    getMetricClass(score) {
        if (score >= 90) return 'excellent';
        if (score >= 70) return 'good';
        if (score >= 50) return 'warning';
        return 'poor';
    }

    displayFinalSummary() {
        console.log('\n🎉 COMPREHENSIVE TESTING COMPLETE');
        console.log('=================================');
        console.log(`📊 Overall Grade: ${this.results.grade}`);
        console.log(`📈 Overall Score: ${this.results.overallSummary.overallScore?.toFixed(1)}/100`);
        console.log(`✅ Tests Passed: ${this.results.overallSummary.passedTests}/${this.results.overallSummary.totalTests}`);
        console.log(`❌ Tests Failed: ${this.results.overallSummary.failedTests}`);
        console.log(`🚨 Critical Issues: ${this.results.overallSummary.criticalIssuesCount}`);
        
        console.log('\n📋 Individual Suite Scores:');
        console.log(`   📊 Performance: ${this.results.overallSummary.performanceScore}/100 (${this.results.overallSummary.performanceGrade})`);
        console.log(`   ♿ Accessibility: ${this.results.overallSummary.accessibilityScore?.toFixed(0)}/100 (${this.results.overallSummary.accessibilityGrade})`);
        console.log(`   📸 Visual Regression: ${this.results.overallSummary.visualSuccessRate?.toFixed(1)}% success rate`);
        
        if (this.results.criticalIssues.length > 0) {
            console.log('\n🚨 Critical Issues Found:');
            this.results.criticalIssues.forEach(issue => {
                console.log(`   ${issue.category}: ${issue.issue}`);
            });
        }
        
        console.log(`\n📄 View comprehensive report: ${this.finalReportPath}`);
        console.log('\n' + '='.repeat(50));
    }

    async runAllTests() {
        try {
            await this.initialize();
            
            // Run all test suites
            await this.runPerformanceTesting();
            await this.runAccessibilityTesting();
            await this.runVisualRegressionTesting();
            
            // Generate summary and reports
            this.generateOverallSummary();
            await this.generateFinalReport();
            
            // Display final summary
            this.displayFinalSummary();
            
            // Exit with appropriate code
            const hasErrors = this.results.overallSummary.criticalIssuesCount > 0 || 
                             this.results.overallSummary.overallScore < 70;
            process.exit(hasErrors ? 1 : 0);
            
        } catch (error) {
            console.error('\n❌ Comprehensive testing failed:', error);
            process.exit(1);
        }
    }
}

// Main execution
async function main() {
    const tester = new ComprehensiveLayoutTester();
    await tester.runAllTests();
}

// Run if called directly
if (require.main === module) {
    main().catch(error => {
        console.error('❌ Fatal error:', error);
        process.exit(1);
    });
}

module.exports = ComprehensiveLayoutTester;
