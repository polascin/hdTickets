<?php

namespace App\Console\Commands;

use App\Services\Email\ImapConnectionService;
use App\Services\Email\EmailParsingService;
use Exception;
use Illuminate\Console\Command;

/**
 * Test IMAP Setup Command
 * 
 * Simple command to test the IMAP setup and verify all components work correctly.
 */
class TestImapSetup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hdtickets:test-imap';

    /**
     * The console command description.
     */
    protected $description = 'Test IMAP setup and verify all components are working';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing HD Tickets IMAP Setup');
        $this->info('================================');

        $allTestsPassed = true;

        // Test 1: IMAP Extension
        if (!$this->testImapExtension()) {
            $allTestsPassed = false;
        }

        // Test 2: Configuration
        if (!$this->testConfiguration()) {
            $allTestsPassed = false;
        }

        // Test 3: Service Classes
        if (!$this->testServiceClasses()) {
            $allTestsPassed = false;
        }

        // Test 4: Email Parsing
        if (!$this->testEmailParsing()) {
            $allTestsPassed = false;
        }

        $this->newLine();
        
        if ($allTestsPassed) {
            $this->info('✅ All IMAP tests passed! Setup is complete.');
            $this->info('📋 Next steps:');
            $this->info('   1. Configure your email credentials in .env');
            $this->info('   2. Run: php artisan hdtickets:monitor-emails --test-connection');
            $this->info('   3. Set up cron job to run: php artisan hdtickets:monitor-emails');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Some IMAP tests failed. Please check the output above.');
            return Command::FAILURE;
        }
    }

    /**
     * Test IMAP PHP extension
     */
    private function testImapExtension(): bool
    {
        $this->line('🔍 Testing IMAP PHP extension...');
        
        if (extension_loaded('imap')) {
            $this->info('  ✅ IMAP extension is loaded');
            
            // Test basic IMAP functions
            $functions = ['imap_open', 'imap_search', 'imap_fetchheader', 'imap_close'];
            foreach ($functions as $function) {
                if (function_exists($function)) {
                    $this->info("  ✅ Function {$function} is available");
                } else {
                    $this->error("  ❌ Function {$function} is not available");
                    return false;
                }
            }
            
            return true;
        } else {
            $this->error('  ❌ IMAP extension is not loaded');
            $this->error('     Install with: sudo apt install php8.3-imap');
            return false;
        }
    }

    /**
     * Test IMAP configuration
     */
    private function testConfiguration(): bool
    {
        $this->line('🔧 Testing IMAP configuration...');
        
        try {
            $config = config('imap');
            
            if (empty($config)) {
                $this->error('  ❌ IMAP configuration not found');
                return false;
            }

            $this->info('  ✅ IMAP configuration loaded');
            
            // Check required configuration sections
            $requiredSections = ['connections', 'monitoring', 'platform_patterns'];
            foreach ($requiredSections as $section) {
                if (isset($config[$section])) {
                    $this->info("  ✅ Configuration section '{$section}' present");
                } else {
                    $this->error("  ❌ Configuration section '{$section}' missing");
                    return false;
                }
            }
            
            // Check platform patterns
            $platformCount = count($config['platform_patterns'] ?? []);
            $this->info("  ✅ {$platformCount} platform patterns configured");
            
            return true;
            
        } catch (Exception $e) {
            $this->error('  ❌ Configuration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test service classes
     */
    private function testServiceClasses(): bool
    {
        $this->line('🏗️  Testing service classes...');
        
        try {
            // Test ImapConnectionService
            $connectionService = new ImapConnectionService();
            $this->info('  ✅ ImapConnectionService instantiated');
            
            $stats = $connectionService->getConnectionStats();
            $this->info('  ✅ Connection statistics retrieved');
            $this->info("      - Configured connections: {$stats['configured_connections']}");
            $this->info("      - Default connection: {$stats['default_connection']}");
            
            // Test EmailParsingService
            $parsingService = new EmailParsingService();
            $this->info('  ✅ EmailParsingService instantiated');
            
            $parsingStats = $parsingService->getParsingStats();
            $this->info('  ✅ Parsing statistics retrieved');
            $this->info("      - Supported platforms: " . count($parsingStats['supported_platforms']));
            $this->info("      - Sport categories: " . count($parsingStats['sport_categories']));
            
            return true;
            
        } catch (Exception $e) {
            $this->error('  ❌ Service class error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test email parsing functionality
     */
    private function testEmailParsing(): bool
    {
        $this->line('📧 Testing email parsing...');
        
        try {
            $parsingService = new EmailParsingService();
            
            // Test sample email data
            $testEmailData = [
                'uid' => 12345,
                'connection' => 'test',
                'platform' => 'ticketmaster',
                'headers' => [
                    'subject' => 'Cowboys vs Patriots - NFL Tickets Available!',
                    'from' => (object)['mailbox' => 'noreply', 'host' => 'ticketmaster.com'],
                    'message_id' => '<test@ticketmaster.com>',
                    'size' => 1024,
                    'date' => '2024-01-15 10:30:00',
                ],
                'body' => "Event: Dallas Cowboys vs New England Patriots\n" .
                         "Venue: AT&T Stadium\n" .
                         "Date: December 15, 2024\n" .
                         "Price: Starting at $89.50\n" .
                         "100 tickets available in various sections",
            ];
            
            $result = $parsingService->parseEmailContent($testEmailData);
            
            $this->info('  ✅ Email parsing completed');
            $this->info('      - Sports events found: ' . count($result['sports_events']));
            $this->info('      - Tickets found: ' . count($result['tickets']));
            
            if (!empty($result['sports_events'])) {
                $event = $result['sports_events'][0];
                $this->info("      - Event: {$event['name']}");
                $this->info("      - Category: {$event['category']}");
                $this->info("      - Platform: {$event['source_platform']}");
            }
            
            if (!empty($result['tickets'])) {
                $ticket = $result['tickets'][0];
                $this->info("      - Ticket price: $" . number_format($ticket['price'], 2));
                $this->info("      - Platform: {$ticket['source_platform']}");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->error('  ❌ Email parsing error: ' . $e->getMessage());
            return false;
        }
    }
}
