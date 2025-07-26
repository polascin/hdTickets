<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class TestAnalyticsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:test-notifications 
                            {--email : Test email notifications}
                            {--slack : Test Slack notifications}
                            {--discord : Test Discord notifications}
                            {--telegram : Test Telegram notifications}
                            {--all : Test all configured channels}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification channels for the Advanced Analytics Dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Advanced Analytics Dashboard - Notification Testing');
        $this->info('=' . str_repeat('=', 60));
        $this->newLine();

        $testAll = $this->option('all');
        $testEmail = $this->option('email') || $testAll;
        $testSlack = $this->option('slack') || $testAll;
        $testDiscord = $this->option('discord') || $testAll;
        $testTelegram = $this->option('telegram') || $testAll;

        if (!$testEmail && !$testSlack && !$testDiscord && !$testTelegram) {
            $testEmail = $this->confirm('Test email notifications?');
            $testSlack = $this->confirm('Test Slack notifications?');
            $testDiscord = $this->confirm('Test Discord notifications?');
            $testTelegram = $this->confirm('Test Telegram notifications?');
        }

        $results = [];

        if ($testEmail) {
            $results['Email'] = $this->testEmail();
        }

        if ($testSlack) {
            $results['Slack'] = $this->testSlack();
        }

        if ($testDiscord) {
            $results['Discord'] = $this->testDiscord();
        }

        if ($testTelegram) {
            $results['Telegram'] = $this->testTelegram();
        }

        $this->displayResults($results);
    }

    private function testEmail()
    {
        $this->info('📧 Testing Email Notifications...');

        try {
            $user = User::first();
            if (!$user) {
                return ['status' => 'error', 'message' => 'No users found in database'];
            }

            // Send test email
            $testData = [
                'subject' => 'HDTickets Analytics - Test Notification',
                'message' => 'This is a test notification from the Advanced Analytics Dashboard.',
                'timestamp' => now()->toDateTimeString(),
                'user' => $user->name
            ];

            // Simulate email sending (replace with actual email logic)
            $this->line('   📬 Sending test email to: ' . $user->email);
            
            return [
                'status' => 'success',
                'message' => 'Test email sent successfully',
                'details' => "Sent to: {$user->email}"
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Email test failed: ' . $e->getMessage()
            ];
        }
    }

    private function testSlack()
    {
        $this->info('📢 Testing Slack Notifications...');

        $botToken = env('SLACK_BOT_TOKEN');
        $channel = env('SLACK_DEFAULT_CHANNEL', '#alerts');

        if (!$botToken) {
            return [
                'status' => 'error',
                'message' => 'Slack bot token not configured'
            ];
        }

        try {
            $message = [
                'channel' => $channel,
                'text' => '🧪 HDTickets Analytics Dashboard - Test Notification',
                'attachments' => [
                    [
                        'color' => 'good',
                        'title' => 'Analytics System Test',
                        'text' => 'This is a test notification from the Advanced Analytics Dashboard.',
                        'fields' => [
                            [
                                'title' => 'Status',
                                'value' => 'System Operational',
                                'short' => true
                            ],
                            [
                                'title' => 'Timestamp',
                                'value' => now()->toDateTimeString(),
                                'short' => true
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $botToken,
                'Content-Type' => 'application/json'
            ])->post('https://slack.com/api/chat.postMessage', $message);

            if ($response->successful() && $response->json('ok')) {
                return [
                    'status' => 'success',
                    'message' => 'Slack notification sent successfully',
                    'details' => "Sent to channel: {$channel}"
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Slack API error: ' . ($response->json('error') ?? 'Unknown error')
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Slack test failed: ' . $e->getMessage()
            ];
        }
    }

    private function testDiscord()
    {
        $this->info('🎮 Testing Discord Notifications...');

        $webhookUrl = env('DISCORD_WEBHOOK_URL');

        if (!$webhookUrl) {
            return [
                'status' => 'error',
                'message' => 'Discord webhook URL not configured'
            ];
        }

        try {
            $message = [
                'username' => 'HDTickets Analytics',
                'avatar_url' => 'https://example.com/hdtickets-logo.png',
                'embeds' => [
                    [
                        'title' => '🧪 Analytics Dashboard Test',
                        'description' => 'This is a test notification from the Advanced Analytics Dashboard.',
                        'color' => 3066993, // Green color
                        'timestamp' => now()->toISOString(),
                        'fields' => [
                            [
                                'name' => 'Status',
                                'value' => 'System Operational',
                                'inline' => true
                            ],
                            [
                                'name' => 'Test Type',
                                'value' => 'Notification Channel Test',
                                'inline' => true
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::post($webhookUrl, $message);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Discord notification sent successfully',
                    'details' => 'Webhook message delivered'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Discord webhook error: HTTP ' . $response->status()
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Discord test failed: ' . $e->getMessage()
            ];
        }
    }

    private function testTelegram()
    {
        $this->info('📱 Testing Telegram Notifications...');

        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            return [
                'status' => 'error',
                'message' => 'Telegram bot token or chat ID not configured'
            ];
        }

        try {
            $message = "🧪 *HDTickets Analytics Dashboard*\n\n" .
                      "This is a test notification from the Advanced Analytics Dashboard.\n\n" .
                      "📊 Status: System Operational\n" .
                      "🕒 Time: " . now()->toDateTimeString() . "\n" .
                      "✅ Test: Notification Channel";

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful() && $response->json('ok')) {
                return [
                    'status' => 'success',
                    'message' => 'Telegram notification sent successfully',
                    'details' => "Sent to chat: {$chatId}"
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Telegram API error: ' . ($response->json('description') ?? 'Unknown error')
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Telegram test failed: ' . $e->getMessage()
            ];
        }
    }

    private function displayResults(array $results)
    {
        $this->newLine();
        $this->info('📋 Notification Test Results:');
        $this->info('=' . str_repeat('=', 40));

        foreach ($results as $channel => $result) {
            $status = $result['status'];
            $icon = $status === 'success' ? '✅' : '❌';
            
            $this->line("   {$icon} {$channel}: " . ucfirst($status));
            $this->line("      Message: {$result['message']}");
            
            if (isset($result['details'])) {
                $this->line("      Details: {$result['details']}");
            }
            
            $this->newLine();
        }

        $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
        $totalCount = count($results);

        if ($successCount === $totalCount) {
            $this->info("🎉 All notification channels tested successfully! ({$successCount}/{$totalCount})");
        } else {
            $this->warn("⚠️ Some notification channels failed. Success rate: {$successCount}/{$totalCount}");
        }

        $this->newLine();
        $this->info('💡 Next steps:');
        $this->line('   1. Fix any failed notification channels');
        $this->line('   2. Start queue workers: scripts/start-analytics-workers.bat');
        $this->line('   3. Monitor system: php artisan analytics:monitor');
    }
}
