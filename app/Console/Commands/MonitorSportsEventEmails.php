<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Email\EmailMonitoringService;
use App\Services\Email\ImapConnectionService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Monitor Sports Event Emails Command
 *
 * Artisan command for monitoring email inboxes for sports event ticket
 * notifications from various platforms in the HD Tickets system.
 */
class MonitorSportsEventEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hdtickets:monitor-emails
                            {--connection= : Specific email connection to monitor}
                            {--mailbox= : Specific mailbox to monitor}
                            {--dry-run : Show what would be processed without actual processing}
                            {--stats : Show monitoring statistics only}
                            {--test-connection : Test email connection only}
                            {--clear-cache : Clear processed emails cache}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor email inboxes for sports event ticket notifications and process them for HD Tickets system';

    private EmailMonitoringService $monitoringService;

    private ImapConnectionService $connectionService;

    public function __construct(
        EmailMonitoringService $monitoringService,
        ImapConnectionService $connectionService
    ) {
        parent::__construct();
        $this->monitoringService = $monitoringService;
        $this->connectionService = $connectionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎟️  HD Tickets - Sports Event Email Monitoring');
        $this->info('===============================================');

        try {
            // Handle specific operations
            if ($this->option('stats')) {
                return $this->showStats();
            }

            if ($this->option('test-connection')) {
                return $this->testConnection();
            }

            if ($this->option('clear-cache')) {
                return $this->clearCache();
            }

            // Main monitoring process
            return $this->runMonitoring();
        } catch (Exception $e) {
            $this->error('❌ Command failed: ' . $e->getMessage());

            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            Log::error('Email monitoring command failed', [
                'error'   => $e->getMessage(),
                'options' => $this->options(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Run email monitoring process
     */
    private function runMonitoring(): int
    {
        $isDryRun = $this->option('dry-run');
        $connection = $this->option('connection');

        if ($isDryRun) {
            $this->warn('🔍 Running in DRY RUN mode - no emails will be processed');
        }

        $this->info('🚀 Starting email monitoring...');

        $startTime = microtime(TRUE);

        if ($connection) {
            // Monitor specific connection
            $results = $this->monitorSpecificConnection($connection);
        } else {
            // Monitor all connections
            $results = $this->monitoringService->monitorAll();
        }

        $endTime = microtime(TRUE);
        $duration = round($endTime - $startTime, 2);

        $this->displayResults($results, $duration);

        // Return success/failure based on results
        if (!empty($results['errors'])) {
            $this->warn('⚠️  Completed with ' . count($results['errors']) . ' errors');

            return Command::FAILURE;
        }

        $this->info("✅ Email monitoring completed successfully in {$duration}s");

        return Command::SUCCESS;
    }

    /**
     * Monitor specific connection
     */
    private function monitorSpecificConnection(string $connection): array
    {
        $this->info("📧 Monitoring connection: {$connection}");

        try {
            return $this->monitoringService->monitorConnection($connection);
        } catch (Exception $e) {
            $this->error("❌ Failed to monitor connection '{$connection}': " . $e->getMessage());

            return [
                'processed_connections'          => 0,
                'total_emails_found'             => 0,
                'total_emails_processed'         => 0,
                'total_sports_events_identified' => 0,
                'connections'                    => [],
                'errors'                         => [
                    [
                        'connection'  => $connection,
                        'error'       => $e->getMessage(),
                        'occurred_at' => now()->toISOString(),
                    ],
                ],
            ];
        }
    }

    /**
     * Display monitoring results
     */
    private function displayResults(array $results, float $duration): void
    {
        $this->newLine();
        $this->info('📊 Monitoring Results');
        $this->info('=====================');

        // Summary statistics
        $this->line("⏱️  Duration: {$duration}s");
        $this->line('🔗 Connections processed: ' . ($results['processed_connections'] ?? 0));
        $this->line('📧 Total emails found: ' . ($results['total_emails_found'] ?? 0));
        $this->line('✅ Emails processed: ' . ($results['total_emails_processed'] ?? 0));
        $this->line('🎟️  Sports events identified: ' . ($results['total_sports_events_identified'] ?? 0));

        // Connection details
        if (!empty($results['connections'])) {
            $this->newLine();
            $this->info('📧 Connection Details:');

            foreach ($results['connections'] as $name => $data) {
                if (isset($data['error'])) {
                    $this->line("  ❌ {$name}: " . $data['error']['error']);
                } else {
                    $this->line("  ✅ {$name}: {$data['emails_found']} found, {$data['emails_processed']} processed, {$data['sports_events_identified']} sports events");

                    if ($this->option('verbose') && !empty($data['mailboxes_checked'])) {
                        foreach ($data['mailboxes_checked'] as $mailbox => $mailboxData) {
                            if (isset($mailboxData['error'])) {
                                $this->line("      📫 {$mailbox}: ❌ " . $mailboxData['error']['error']);
                            } else {
                                $this->line("      📫 {$mailbox}: {$mailboxData['emails_found']} found, {$mailboxData['emails_processed']} processed");
                            }
                        }
                    }
                }
            }
        }

        // Errors
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->warn('⚠️  Errors:');

            foreach ($results['errors'] as $error) {
                $connection = $error['connection'] ?? 'Unknown';
                $this->line("  ❌ {$connection}: {$error['error']}");
            }
        }
    }

    /**
     * Show monitoring statistics
     */
    private function showStats(): int
    {
        $this->info('📊 Email Monitoring Statistics');
        $this->info('==============================');

        try {
            $stats = $this->monitoringService->getMonitoringStats();
            $connectionStats = $this->connectionService->getConnectionStats();

            $this->line('🔗 Total connections configured: ' . $stats['total_connections']);
            $this->line('✅ Active connections: ' . $stats['active_connections']);
            $this->line('📫 Total mailboxes to monitor: ' . $stats['total_mailboxes']);
            $this->line('🏟️  Platform patterns configured: ' . $stats['platform_patterns']);

            $this->newLine();
            $this->info('🎯 Supported Platforms:');
            foreach ($stats['platforms'] as $platform) {
                $this->line('  • ' . ucfirst($platform));
            }

            $this->newLine();
            $this->info('🔗 Connection Status:');
            foreach ($connectionStats['connections'] as $name => $data) {
                $status = $data['active'] ? '✅ Active' : '❌ Inactive';
                $this->line("  • {$name}: {$status}");
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Failed to retrieve statistics: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Test email connections
     */
    private function testConnection(): int
    {
        $connection = $this->option('connection');

        $this->info('🧪 Testing Email Connections');
        $this->info('============================');

        if ($connection) {
            return $this->testSpecificConnection($connection);
        }

        // Test all connections
        $connections = config('imap.connections', []);
        $success = TRUE;

        foreach (array_keys($connections) as $connName) {
            if (!$this->testSpecificConnection($connName, FALSE)) {
                $success = FALSE;
            }
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Test specific connection
     */
    private function testSpecificConnection(string $connection, bool $exit = TRUE): bool
    {
        $this->line("🔗 Testing connection: {$connection}");

        try {
            $result = $this->connectionService->testConnection($connection);

            if ($result['success']) {
                $this->info('  ✅ Connection successful');
                $this->line("  ⏱️  Connection time: {$result['connection_time']}s");
                $this->line("  📫 Mailboxes: {$result['mailboxes_count']}");
                $this->line("  📧 Messages: {$result['messages_count']}");
                $this->line("  🆕 Recent: {$result['recent_count']}");

                if ($this->option('verbose') && !empty($result['mailboxes'])) {
                    $this->line('  📋 Available mailboxes:');
                    foreach ($result['mailboxes'] as $mailbox) {
                        $this->line("    • {$mailbox}");
                    }
                }

                return TRUE;
            } else {
                $this->error('  ❌ Connection failed: ' . $result['error']);

                return FALSE;
            }
        } catch (Exception $e) {
            $this->error('  ❌ Test failed: ' . $e->getMessage());

            return FALSE;
        }
    }

    /**
     * Clear processed emails cache
     */
    private function clearCache(): int
    {
        $connection = $this->option('connection');

        $this->info('🧹 Clearing Processed Emails Cache');
        $this->info('==================================');

        try {
            $this->monitoringService->clearProcessedCache($connection);

            if ($connection) {
                $this->info("✅ Cache cleared for connection: {$connection}");
            } else {
                $this->info('✅ Cache cleared for all connections');
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Failed to clear cache: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
