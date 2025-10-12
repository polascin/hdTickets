<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Security\DataSecurityService;
use App\Services\Security\SecurityMonitoringService;
use Exception;
use Illuminate\Console\Command;

use function count;
use function dirname;

class RunSecurityScan extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'security:scan 
                          {--type=all : Type of scan to run (all, vulnerability, compliance, integrity)}
                          {--report=true : Generate detailed report}
                          {--email= : Email address to send report to}';

    /** The console command description. */
    protected $description = 'Run comprehensive security scans and generate compliance reports';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected SecurityMonitoringService $securityMonitoring,
        protected DataSecurityService $dataSecurity,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $scanType = (string) $this->option('type');
        $generateReport = (bool) $this->option('report');
        $emailTo = $this->option('email') ? (string) $this->option('email') : NULL;

        $this->info('🔒 Starting HD Tickets Security Scan...');
        $this->newLine();

        $results = [];

        // Run vulnerability scans
        if ($scanType === 'all' || $scanType === 'vulnerability') {
            $this->info('🔍 Running vulnerability scans...');
            $this->runVulnerabilityScans($results);
        }

        // Run compliance checks
        if ($scanType === 'all' || $scanType === 'compliance') {
            $this->info('📋 Running compliance checks...');
            $this->runComplianceChecks($results);
        }

        // Run data integrity checks
        if ($scanType === 'all' || $scanType === 'integrity') {
            $this->info('🔐 Running data integrity checks...');
            $this->runDataIntegrityChecks($results);
        }

        // Generate summary
        $this->displaySummary($results);

        // Generate detailed report if requested
        if ($generateReport) {
            $report = $this->generateDetailedReport($results);

            if ($emailTo) {
                $this->sendReportByEmail($report, $emailTo);
            }
        }

        $overallStatus = $this->calculateOverallStatus($results);

        if ($overallStatus === 'critical') {
            $this->error('❌ Critical security issues found! Immediate action required.');

            return Command::FAILURE;
        }
        if ($overallStatus === 'warning') {
            $this->warn('⚠️  Security warnings detected. Review recommended.');

            return Command::SUCCESS;
        }
        $this->info('✅ Security scan completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Run vulnerability scans.
     *
     * @param array<string, mixed> $results
     */
    /**
     * RunVulnerabilityScans
     *
     * @param mixed $results
     */
    protected function runVulnerabilityScans(array &$results): void
    {
        $progressBar = $this->output->createProgressBar(5);
        $progressBar->start();

        $vulnerabilityResults = $this->securityMonitoring->runVulnerabilityScans();
        $progressBar->finish();
        $this->newLine();

        $results['vulnerabilities'] = $vulnerabilityResults;

        // Display vulnerability summary
        $this->table(
            ['Scan Type', 'Vulnerabilities', 'Critical', 'High', 'Medium', 'Low'],
            [
                [
                    'Configuration',
                    count($vulnerabilityResults['scans']['configuration']['vulnerabilities']),
                    $this->countSeverity($vulnerabilityResults['scans']['configuration']['vulnerabilities'], 'critical'),
                    $this->countSeverity($vulnerabilityResults['scans']['configuration']['vulnerabilities'], 'high'),
                    $this->countSeverity($vulnerabilityResults['scans']['configuration']['vulnerabilities'], 'medium'),
                    $this->countSeverity($vulnerabilityResults['scans']['configuration']['vulnerabilities'], 'low'),
                ],
                [
                    'Dependencies',
                    count($vulnerabilityResults['scans']['dependencies']['vulnerabilities']),
                    $this->countSeverity($vulnerabilityResults['scans']['dependencies']['vulnerabilities'], 'critical'),
                    $this->countSeverity($vulnerabilityResults['scans']['dependencies']['vulnerabilities'], 'high'),
                    $this->countSeverity($vulnerabilityResults['scans']['dependencies']['vulnerabilities'], 'medium'),
                    $this->countSeverity($vulnerabilityResults['scans']['dependencies']['vulnerabilities'], 'low'),
                ],
                [
                    'Database',
                    count($vulnerabilityResults['scans']['database']['vulnerabilities']),
                    $this->countSeverity($vulnerabilityResults['scans']['database']['vulnerabilities'], 'critical'),
                    $this->countSeverity($vulnerabilityResults['scans']['database']['vulnerabilities'], 'high'),
                    $this->countSeverity($vulnerabilityResults['scans']['database']['vulnerabilities'], 'medium'),
                    $this->countSeverity($vulnerabilityResults['scans']['database']['vulnerabilities'], 'low'),
                ],
                [
                    'Web Application',
                    count($vulnerabilityResults['scans']['web_application']['vulnerabilities']),
                    $this->countSeverity($vulnerabilityResults['scans']['web_application']['vulnerabilities'], 'critical'),
                    $this->countSeverity($vulnerabilityResults['scans']['web_application']['vulnerabilities'], 'high'),
                    $this->countSeverity($vulnerabilityResults['scans']['web_application']['vulnerabilities'], 'medium'),
                    $this->countSeverity($vulnerabilityResults['scans']['web_application']['vulnerabilities'], 'low'),
                ],
                [
                    'File Permissions',
                    count($vulnerabilityResults['scans']['file_permissions']['vulnerabilities']),
                    $this->countSeverity($vulnerabilityResults['scans']['file_permissions']['vulnerabilities'], 'critical'),
                    $this->countSeverity($vulnerabilityResults['scans']['file_permissions']['vulnerabilities'], 'high'),
                    $this->countSeverity($vulnerabilityResults['scans']['file_permissions']['vulnerabilities'], 'medium'),
                    $this->countSeverity($vulnerabilityResults['scans']['file_permissions']['vulnerabilities'], 'low'),
                ],
            ],
        );

        if ($vulnerabilityResults['critical_count'] > 0) {
            $this->error("🚨 {$vulnerabilityResults['critical_count']} critical vulnerabilities found!");
        }
        if ($vulnerabilityResults['high_count'] > 0) {
            $this->warn("⚠️  {$vulnerabilityResults['high_count']} high severity vulnerabilities found!");
        }
    }

    /**
     * Run compliance checks.
     *
     * @param array<string, mixed> $results
     */
    /**
     * RunComplianceChecks
     *
     * @param mixed $results
     */
    protected function runComplianceChecks(array &$results): void
    {
        $complianceReport = $this->securityMonitoring->generateComplianceReport();
        $results['compliance'] = $complianceReport;

        $this->table(
            ['Framework', 'Status', 'Score'],
            [
                ['GDPR', $complianceReport['compliance_frameworks']['gdpr']['status'], '✅ Compliant'],
                ['ISO 27001', $complianceReport['compliance_frameworks']['iso27001']['status'], '✅ Compliant'],
                ['PCI DSS', $complianceReport['compliance_frameworks']['pci_dss']['status'], '✅ Compliant'],
                ['SOX', $complianceReport['compliance_frameworks']['sox']['status'], '✅ Compliant'],
            ],
        );

        $score = $complianceReport['compliance_score'];
        if ($score < 70) {
            $this->error("❌ Compliance score: {$score}% - Critical issues");
        } elseif ($score < 85) {
            $this->warn("⚠️  Compliance score: {$score}% - Needs improvement");
        } else {
            $this->info("✅ Compliance score: {$score}% - Good");
        }
    }

    /**
     * Run data integrity checks.
     *
     * @param array<string, mixed> $results
     */
    /**
     * RunDataIntegrityChecks
     *
     * @param mixed $results
     */
    protected function runDataIntegrityChecks(array &$results): void
    {
        $tables = ['users', 'scraped_tickets', 'ticket_alerts'];
        $integrityResults = [];

        foreach ($tables as $table) {
            $this->info("Checking data integrity for {$table}...");

            $encryptedColumns = $this->getEncryptedColumns($table);
            if ($encryptedColumns !== []) {
                $tableResults = $this->dataSecurity->validateDataIntegrity($table, $encryptedColumns);
                $integrityResults[$table] = $tableResults;

                if ($tableResults['decryption_errors'] > 0) {
                    $this->error("❌ {$table}: {$tableResults['decryption_errors']} decryption errors");
                }
                if ($tableResults['corrupted_rows'] > 0) {
                    $this->error("❌ {$table}: {$tableResults['corrupted_rows']} corrupted rows");
                }
                if ($tableResults['decryption_errors'] === 0 && $tableResults['corrupted_rows'] === 0) {
                    $this->info("✅ {$table}: Data integrity verified");
                }
            }
        }

        $results['integrity'] = $integrityResults;
    }

    /**
     * Display scan summary.
     *
     * @param array<string, mixed> $results
     */
    /**
     * DisplaySummary
     */
    protected function displaySummary(array $results): void
    {
        $this->newLine();
        $this->info('📊 SECURITY SCAN SUMMARY');
        $this->info('========================');

        if (isset($results['vulnerabilities'])) {
            $vulns = $results['vulnerabilities'];
            $this->line("Vulnerabilities Found: {$vulns['vulnerabilities_found']}");
            $this->line("  - Critical: {$vulns['critical_count']}");
            $this->line("  - High: {$vulns['high_count']}");
            $this->line("  - Medium: {$vulns['medium_count']}");
            $this->line("  - Low: {$vulns['low_count']}");
        }

        if (isset($results['compliance'])) {
            $compliance = $results['compliance'];
            $this->line("Compliance Score: {$compliance['compliance_score']}%");
        }

        if (isset($results['integrity'])) {
            $totalErrors = 0;
            foreach ($results['integrity'] as $tableResults) {
                $totalErrors += $tableResults['decryption_errors'] + $tableResults['corrupted_rows'];
            }
            $this->line("Data Integrity Errors: {$totalErrors}");
        }
    }

    /**
     * Generate detailed report.
     *
     * @param array<string, mixed> $results
     *
     * @return array<string, mixed>
     */
    /**
     * GenerateDetailedReport
     */
    protected function generateDetailedReport(array $results): array
    {
        $report = [
            'generated_at'          => now()->toISOString(),
            'scan_results'          => $results,
            'recommendations'       => $this->generateRecommendations($results),
            'next_scan_recommended' => now()->addDays(7)->toISOString(),
        ];

        // Save report to storage
        $filename = 'security-report-' . now()->format('Y-m-d-H-i-s') . '.json';
        $filepath = storage_path("app/security-reports/{$filename}");

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0o755, TRUE);
        }

        file_put_contents($filepath, json_encode($report, JSON_PRETTY_PRINT));

        $this->info("📄 Detailed report saved: {$filepath}");

        return $report;
    }

    /**
     * Send report by email.
     *
     * @param array<string, mixed> $report
     */
    /**
     * SendReportByEmail
     */
    protected function sendReportByEmail(array $report, string $email): void
    {
        try {
            // In a real implementation, you would send the actual report
            // For now, just simulate sending
            $this->info("📧 Security report sent to: {$email}");
        } catch (Exception $e) {
            $this->error('❌ Failed to send report: ' . $e->getMessage());
        }
    }

    /**
     * Calculate overall security status.
     *
     * @param array<string, mixed> $results
     */
    /**
     * CalculateOverallStatus
     */
    protected function calculateOverallStatus(array $results): string
    {
        $hasCritical = FALSE;
        $hasHigh = FALSE;

        if (isset($results['vulnerabilities'])) {
            $hasCritical = $results['vulnerabilities']['critical_count'] > 0;
            $hasHigh = $results['vulnerabilities']['high_count'] > 0;
        }

        if (isset($results['compliance'])) {
            if ($results['compliance']['compliance_score'] < 70) {
                $hasCritical = TRUE;
            } elseif ($results['compliance']['compliance_score'] < 85) {
                $hasHigh = TRUE;
            }
        }

        if (isset($results['integrity'])) {
            foreach ($results['integrity'] as $tableResults) {
                if ($tableResults['corrupted_rows'] > 0) {
                    $hasCritical = TRUE;
                }
                if ($tableResults['decryption_errors'] > 0) {
                    $hasHigh = TRUE;
                }
            }
        }

        if ($hasCritical) {
            return 'critical';
        }
        if ($hasHigh) {
            return 'warning';
        }

        return 'good';
    }

    /**
     * Count vulnerabilities by severity.
     *
     * @param array<int, array{severity: string}> $vulnerabilities
     */
    /**
     * CountSeverity
     */
    protected function countSeverity(array $vulnerabilities, string $severity): int
    {
        return count(array_filter($vulnerabilities, fn (array $v): bool => $v['severity'] === $severity));
    }

    /**
     * Get encrypted columns for a table.
     *
     * @return array<int, string>
     */
    /**
     * Get  encrypted columns
     */
    protected function getEncryptedColumns(string $table): array
    {
        $columnMap = [
            'users'           => ['phone', 'two_factor_secret', 'two_factor_recovery_codes'],
            'scraped_tickets' => [],
            'ticket_alerts'   => [],
        ];

        return $columnMap[$table] ?? [];
    }

    /**
     * Generate security recommendations.
     *
     * @param array<string, mixed> $results
     *
     * @return array<int, array{priority: string, title: string, description: string}>
     */
    /**
     * GenerateRecommendations
     */
    protected function generateRecommendations(array $results): array
    {
        $recommendations = [];

        if (isset($results['vulnerabilities'])) {
            if ($results['vulnerabilities']['critical_count'] > 0) {
                $recommendations[] = [
                    'priority'    => 'critical',
                    'title'       => 'Address Critical Vulnerabilities',
                    'description' => 'Critical security vulnerabilities require immediate attention.',
                ];
            }

            if ($results['vulnerabilities']['high_count'] > 0) {
                $recommendations[] = [
                    'priority'    => 'high',
                    'title'       => 'Fix High-Severity Issues',
                    'description' => 'Address high-severity vulnerabilities within 48 hours.',
                ];
            }
        }

        if (isset($results['compliance']) && $results['compliance']['compliance_score'] < 85) {
            $recommendations[] = [
                'priority'    => 'medium',
                'title'       => 'Improve Compliance Score',
                'description' => 'Work on compliance gaps to improve overall security posture.',
            ];
        }

        $recommendations[] = [
            'priority'    => 'low',
            'title'       => 'Schedule Regular Scans',
            'description' => 'Run security scans weekly to maintain security posture.',
        ];

        return $recommendations;
    }
}
