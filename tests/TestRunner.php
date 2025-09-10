<?php declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;

use function count;
use function sprintf;

/**
 * Comprehensive Test Runner for HD Tickets
 *
 * This class provides utilities for running the complete test suite
 * with proper setup, teardown, and coverage reporting.
 */
class TestRunner
{
    private array $testSuites = [
        'unit'        => 'Unit Tests',
        'integration' => 'Integration Tests',
        'feature'     => 'Feature Tests',
        'performance' => 'Performance Tests',
        'endtoend'    => 'End-to-End Tests',
    ];

    private array $coverageThresholds = [
        'lines'     => 80,
        'functions' => 80,
        'classes'   => 80,
        'methods'   => 80,
    ];

    private string $coverageDir = 'storage/quality/coverage';

    private string $reportDir = 'storage/quality/reports';

    public function __construct()
    {
        $this->ensureDirectoriesExist();
    }

    /**
     * Run all test suites with coverage
     */
    public function runAllTests(): array
    {
        $results = [
            'start_time' => now(),
            'suites'     => [],
            'coverage'   => [],
            'summary'    => [],
        ];

        echo "🚀 Starting HD Tickets Comprehensive Test Suite\n";
        echo '=' . str_repeat('=', 50) . "\n";

        foreach ($this->testSuites as $suite => $name) {
            echo "\n📋 Running {$name}...\n";
            $results['suites'][$suite] = $this->runTestSuite($suite);
        }

        // Generate coverage report
        echo "\n📊 Generating Coverage Report...\n";
        $results['coverage'] = $this->generateCoverageReport();

        // Generate performance metrics
        echo "\n⚡ Collecting Performance Metrics...\n";
        $results['performance_metrics'] = $this->collectPerformanceMetrics();

        // Generate summary
        $results['summary'] = $this->generateSummary($results);
        $results['end_time'] = now();

        echo "\n" . $this->formatSummaryReport($results);

        return $results;
    }

    /**
     * Run specific test suite
     */
    public function runTestSuite(string $suite): array
    {
        $startTime = microtime(TRUE);

        $command = match ($suite) {
            'unit'        => 'vendor/bin/phpunit --testsuite=Unit --coverage-clover=storage/quality/coverage/unit-clover.xml',
            'integration' => 'vendor/bin/phpunit --testsuite=Integration --coverage-clover=storage/quality/coverage/integration-clover.xml',
            'feature'     => 'vendor/bin/phpunit --testsuite=Feature --coverage-clover=storage/quality/coverage/feature-clover.xml',
            'performance' => 'vendor/bin/phpunit tests/Performance --coverage-clover=storage/quality/coverage/performance-clover.xml',
            'endtoend'    => 'vendor/bin/phpunit tests/EndToEnd --coverage-clover=storage/quality/coverage/e2e-clover.xml',
            default       => throw new InvalidArgumentException("Unknown test suite: {$suite}"),
        };

        // Set up test environment
        $this->setupTestEnvironment();

        // Execute tests
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        // Clean up test environment
        $this->cleanupTestEnvironment();

        $endTime = microtime(TRUE);

        return [
            'suite'          => $suite,
            'name'           => $this->testSuites[$suite],
            'execution_time' => $endTime - $startTime,
            'success'        => $returnCode === 0,
            'output'         => implode("\n", $output),
            'return_code'    => $returnCode,
        ];
    }

    /**
     * Run only unit tests with coverage
     */
    public function runUnitTests(): array
    {
        echo "🧪 Running Unit Tests Only...\n";

        $results = [
            'unit'     => $this->runTestSuite('unit'),
            'coverage' => $this->generateCoverageReport(),
        ];

        $this->checkCoverageThresholds($results['coverage']);

        return $results;
    }

    /**
     * Run performance tests
     */
    public function runPerformanceTests(): array
    {
        echo "⚡ Running Performance Tests...\n";

        // Optimize test environment for performance testing
        $this->optimizeForPerformance();

        $results = $this->runTestSuite('performance');

        // Generate performance report
        $performanceMetrics = $this->collectPerformanceMetrics();

        return [
            'performance_tests' => $results,
            'metrics'           => $performanceMetrics,
        ];
    }

    /**
     * Generate JMeter performance test scripts
     */
    public function generateJMeterScripts(): void
    {
        $jmeterTemplate = $this->getJMeterTemplate();

        $scriptPath = 'tests/Performance/jmeter-load-test.jmx';
        file_put_contents($scriptPath, $jmeterTemplate);

        echo "📄 JMeter test script generated: {$scriptPath}\n";
        echo "Run with: jmeter -n -t {$scriptPath} -l results.jtl\n";
    }

    /**
     * Generate coverage report
     */
    private function generateCoverageReport(): array
    {
        // Generate HTML coverage report
        $coverageCommand = 'vendor/bin/phpunit --coverage-html=' . $this->coverageDir . '/html';
        $coverageCommand .= ' --coverage-text=' . $this->coverageDir . '/text.txt';
        $coverageCommand .= ' --coverage-xml=' . $this->coverageDir . '/xml';
        exec($coverageCommand . ' 2>&1', $output);
        // Parse coverage results
        $coverageData = $this->parseCoverageResults();

        return [
            'html_report'      => $this->coverageDir . '/html/index.html',
            'text_report'      => $this->coverageDir . '/text.txt',
            'xml_report'       => $this->coverageDir . '/xml',
            'metrics'          => $coverageData,
            'thresholds'       => $this->coverageThresholds,
            'meets_thresholds' => $this->checkCoverageThresholds($coverageData),
        ];
    }

    /**
     * Parse coverage results from clover XML
     */
    private function parseCoverageResults(): array
    {
        $coverageFile = $this->coverageDir . '/clover.xml';

        if (! file_exists($coverageFile)) {
            return [
                'lines'     => 0,
                'functions' => 0,
                'classes'   => 0,
                'methods'   => 0,
            ];
        }

        $xml = simplexml_load_file($coverageFile);
        $metrics = $xml->project->metrics;

        return [
            'lines'           => (float) ($metrics['coveredstatements'] / $metrics['statements'] * 100),
            'functions'       => (float) ($metrics['coveredmethods'] / $metrics['methods'] * 100),
            'classes'         => (float) ($metrics['coveredclasses'] / $metrics['classes'] * 100),
            'methods'         => (float) ($metrics['coveredmethods'] / $metrics['methods'] * 100),
            'total_lines'     => (int) $metrics['statements'],
            'covered_lines'   => (int) $metrics['coveredstatements'],
            'total_methods'   => (int) $metrics['methods'],
            'covered_methods' => (int) $metrics['coveredmethods'],
        ];
    }

    /**
     * Check if coverage meets thresholds
     */
    private function checkCoverageThresholds(array $coverage): bool
    {
        foreach ($this->coverageThresholds as $metric => $threshold) {
            if (($coverage[$metric] ?? 0) < $threshold) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Collect performance metrics
     */
    private function collectPerformanceMetrics(): array
    {
        return [
            'memory_peak'      => memory_get_peak_usage(TRUE),
            'memory_current'   => memory_get_usage(TRUE),
            'execution_time'   => microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT'] ?? 0,
            'database_queries' => $this->getDatabaseQueryCount(),
            'cache_hits'       => $this->getCacheHitRate(),
            'queue_jobs'       => $this->getQueueJobCount(),
        ];
    }

    /**
     * Generate test summary
     */
    private function generateSummary(array $results): array
    {
        $passedTests = 0;
        $failedTests = 0;
        $totalTime = 0;

        foreach ($results['suites'] as $suite) {
            $totalTime += $suite['execution_time'];

            if ($suite['success']) {
                $passedTests++;
            } else {
                $failedTests++;
            }
        }

        $coverageMeetsThreshold = $this->checkCoverageThresholds($results['coverage']['metrics'] ?? []);

        return [
            'total_suites'             => count($results['suites']),
            'passed_suites'            => $passedTests,
            'failed_suites'            => $failedTests,
            'total_execution_time'     => $totalTime,
            'coverage_meets_threshold' => $coverageMeetsThreshold,
            'overall_success'          => $failedTests === 0 && $coverageMeetsThreshold,
        ];
    }

    /**
     * Format summary report for console output
     */
    private function formatSummaryReport(array $results): string
    {
        $summary = $results['summary'];
        $coverage = $results['coverage']['metrics'] ?? [];

        $report = "\n" . str_repeat('=', 60) . "\n";
        $report .= "🎯 HD TICKETS TEST SUITE SUMMARY\n";
        $report .= str_repeat('=', 60) . "\n";

        $report .= sprintf(
            "📊 Test Suites: %d total, %d passed, %d failed\n",
            $summary['total_suites'],
            $summary['passed_suites'],
            $summary['failed_suites'],
        );

        $report .= sprintf("⏱️  Total Execution Time: %.2f seconds\n", $summary['total_execution_time']);

        if (! empty($coverage)) {
            $report .= sprintf(
                "📈 Code Coverage: %.1f%% lines, %.1f%% methods\n",
                $coverage['lines'] ?? 0,
                $coverage['methods'] ?? 0,
            );
        }

        $report .= sprintf(
            "🎭 Overall Result: %s\n",
            $summary['overall_success'] ? '✅ PASSED' : '❌ FAILED',
        );

        $report .= str_repeat('=', 60) . "\n";

        // Add detailed suite results
        foreach ($results['suites'] as $suite) {
            $status = $suite['success'] ? '✅' : '❌';
            $report .= sprintf(
                "%s %s (%.2fs)\n",
                $status,
                $suite['name'],
                $suite['execution_time'],
            );
        }

        return $report;
    }

    /**
     * Setup test environment
     */
    private function setupTestEnvironment(): void
    {
        // Refresh test database
        Artisan::call('migrate:fresh', ['--env' => 'testing']);

        // Clear all caches
        Cache::flush();

        // Clear queue
        Queue::purge();

        // Set test-specific configuration
        config(['app.env' => 'testing']);
        config(['database.default' => 'sqlite']);
        config(['mail.default' => 'array']);
    }

    /**
     * Cleanup test environment
     */
    private function cleanupTestEnvironment(): void
    {
        // Clear test data
        DB::purge();

        // Clear caches
        Cache::flush();

        // Reset configuration
        app()->boot();
    }

    /**
     * Optimize environment for performance testing
     */
    private function optimizeForPerformance(): void
    {
        // Disable debug mode
        config(['app.debug' => FALSE]);

        // Use database connection pooling
        config(['database.connections.mysql.pool' => TRUE]);

        // Optimize cache settings
        config(['cache.default' => 'redis']);

        // Disable unnecessary logging
        config(['logging.default' => 'null']);
    }

    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist(): void
    {
        $directories = [
            $this->coverageDir,
            $this->reportDir,
            $this->coverageDir . '/html',
            $this->coverageDir . '/xml',
            'storage/quality/logs',
        ];

        foreach ($directories as $dir) {
            if (! file_exists($dir)) {
                mkdir($dir, 0o755, TRUE);
            }
        }
    }

    /**
     * Get database query count
     */
    private function getDatabaseQueryCount(): int
    {
        // This would need to be implemented based on your query logging
        return 0;
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): float
    {
        // This would need to be implemented based on your cache metrics
        return 0.0;
    }

    /**
     * Get queue job count
     */
    private function getQueueJobCount(): int
    {
        // This would need to be implemented based on your queue metrics
        return 0;
    }

    /**
     * Get JMeter test template
     */
    private function getJMeterTemplate(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2" properties="5.0" jmeter="5.4.1">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan" testname="HD Tickets Load Test" enabled="true">
      <stringProp name="TestPlan.comments">Load test for HD Tickets API</stringProp>
      <boolProp name="TestPlan.functional_mode">false</boolProp>
      <boolProp name="TestPlan.tearDown_on_shutdown">true</boolProp>
      <boolProp name="TestPlan.serialize_threadgroups">false</boolProp>
      <elementProp name="TestPlan.arguments" elementType="Arguments" guiclass="ArgumentsPanel" testclass="Arguments" testname="User Defined Variables" enabled="true">
        <collectionProp name="Arguments.arguments">
          <elementProp name="BASE_URL" elementType="Argument">
            <stringProp name="Argument.name">BASE_URL</stringProp>
            <stringProp name="Argument.value">http://localhost:8000</stringProp>
          </elementProp>
        </collectionProp>
      </elementProp>
      <stringProp name="TestPlan.user_define_classpath"></stringProp>
    </TestPlan>
    <hashTree>
      <ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="API Load Test" enabled="true">
        <stringProp name="ThreadGroup.on_sample_error">continue</stringProp>
        <elementProp name="ThreadGroup.main_controller" elementType="LoopController" guiclass="LoopControlPanel" testclass="LoopController" testname="Loop Controller" enabled="true">
          <boolProp name="LoopController.continue_forever">false</boolProp>
          <stringProp name="LoopController.loops">10</stringProp>
        </elementProp>
        <stringProp name="ThreadGroup.num_threads">50</stringProp>
        <stringProp name="ThreadGroup.ramp_time">30</stringProp>
        <boolProp name="ThreadGroup.scheduler">false</boolProp>
        <stringProp name="ThreadGroup.duration"></stringProp>
        <stringProp name="ThreadGroup.delay"></stringProp>
        <boolProp name="ThreadGroup.same_user_on_next_iteration">true</boolProp>
      </ThreadGroup>
      <hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="GET Tickets List" enabled="true">
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments" testname="User Defined Variables" enabled="true">
            <collectionProp name="Arguments.arguments"/>
          </elementProp>
          <stringProp name="HTTPSampler.domain">${BASE_URL}</stringProp>
          <stringProp name="HTTPSampler.port"></stringProp>
          <stringProp name="HTTPSampler.protocol">http</stringProp>
          <stringProp name="HTTPSampler.contentEncoding"></stringProp>
          <stringProp name="HTTPSampler.path">/api/tickets</stringProp>
          <stringProp name="HTTPSampler.method">GET</stringProp>
          <boolProp name="HTTPSampler.follow_redirects">true</boolProp>
          <boolProp name="HTTPSampler.auto_redirects">false</boolProp>
          <boolProp name="HTTPSampler.use_keepalive">true</boolProp>
          <boolProp name="HTTPSampler.DO_MULTIPART_POST">false</boolProp>
          <stringProp name="HTTPSampler.embedded_url_re"></stringProp>
          <stringProp name="HTTPSampler.connect_timeout"></stringProp>
          <stringProp name="HTTPSampler.response_timeout"></stringProp>
        </HTTPSamplerProxy>
        <hashTree/>
      </hashTree>
    </hashTree>
  </hashTree>
</jmeterTestPlan>';
    }
}
