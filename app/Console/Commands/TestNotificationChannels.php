<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserNotificationSettings;
use App\Services\NotificationChannels\SlackNotificationChannel;
use App\Services\NotificationChannels\DiscordNotificationChannel;
use App\Services\NotificationChannels\TelegramNotificationChannel;
use App\Services\NotificationChannels\WebhookNotificationChannel;
use Illuminate\Console\Command;

class TestNotificationChannels extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'enhanced-alerts:test-channels 
                            {--user= : User ID to test channels for}
                            {--channel= : Specific channel to test (slack, discord, telegram, webhook)}
                            {--all : Test all configured channels for the user}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification channels for enhanced alerts';

    protected $channels = [
        'slack' => SlackNotificationChannel::class,
        'discord' => DiscordNotificationChannel::class,
        'telegram' => TelegramNotificationChannel::class,
        'webhook' => WebhookNotificationChannel::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔔 Testing Enhanced Alert Notification Channels...');

        $userId = $this->option('user');
        $channelName = $this->option('channel');
        $testAll = $this->option('all');

        if (!$userId) {
            $this->error('❌ User ID is required. Use --user=ID');
            return Command::FAILURE;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return Command::FAILURE;
        }

        $this->info("Testing channels for user: {$user->name} ({$user->email})");
        $this->newLine();

        try {
            if ($channelName) {
                return $this->testSpecificChannel($user, $channelName);
            } elseif ($testAll) {
                return $this->testAllChannels($user);
            } else {
                return $this->testConfiguredChannels($user);
            }
        } catch (\Exception $e) {
            $this->error('❌ Testing failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Test a specific channel
     */
    protected function testSpecificChannel(User $user, string $channelName): int
    {
        if (!isset($this->channels[$channelName])) {
            $this->error("❌ Unknown channel: {$channelName}");
            $this->line('Available channels: ' . implode(', ', array_keys($this->channels)));
            return Command::FAILURE;
        }

        $this->info("🧪 Testing {$channelName} channel...");

        $channelClass = $this->channels[$channelName];
        $channel = new $channelClass();

        $result = $channel->testConnection($user);

        if ($result['success']) {
            $this->info("✅ {$channelName}: " . $result['message']);
            return Command::SUCCESS;
        } else {
            $this->error("❌ {$channelName}: " . $result['message']);
            return Command::FAILURE;
        }
    }

    /**
     * Test all available channels
     */
    protected function testAllChannels(User $user): int
    {
        $this->info('🧪 Testing all notification channels...');
        $this->newLine();

        $results = [];
        $overallSuccess = true;

        foreach ($this->channels as $channelName => $channelClass) {
            $this->line("Testing {$channelName}...");

            try {
                $channel = new $channelClass();
                $result = $channel->testConnection($user);

                $results[] = [
                    'Channel' => ucfirst($channelName),
                    'Status' => $result['success'] ? '✅ Success' : '❌ Failed',
                    'Message' => $result['message']
                ];

                if (!$result['success']) {
                    $overallSuccess = false;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'Channel' => ucfirst($channelName),
                    'Status' => '❌ Error',
                    'Message' => $e->getMessage()
                ];
                $overallSuccess = false;
            }
        }

        $this->newLine();
        $this->table(['Channel', 'Status', 'Message'], $results);

        if ($overallSuccess) {
            $this->info('✅ All channels tested successfully!');
            return Command::SUCCESS;
        } else {
            $this->warn('⚠️ Some channels failed testing. Check configuration.');
            return Command::FAILURE;
        }
    }

    /**
     * Test only configured channels for the user
     */
    protected function testConfiguredChannels(User $user): int
    {
        $settings = UserNotificationSettings::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->get();

        if ($settings->isEmpty()) {
            $this->warn('⚠️ No notification channels configured for this user');
            $this->line('Configure channels first or use --all to test all available channels');
            return Command::SUCCESS;
        }

        $this->info('🧪 Testing configured notification channels...');
        $this->newLine();

        $results = [];
        $overallSuccess = true;

        foreach ($settings as $setting) {
            $channelName = $setting->channel;
            $this->line("Testing configured {$channelName}...");

            try {
                if (!isset($this->channels[$channelName])) {
                    $results[] = [
                        'Channel' => ucfirst($channelName),
                        'Status' => '❌ Unknown',
                        'Message' => 'Channel implementation not found'
                    ];
                    $overallSuccess = false;
                    continue;
                }

                $channelClass = $this->channels[$channelName];
                $channel = new $channelClass();
                $result = $channel->testConnection($user);

                $results[] = [
                    'Channel' => ucfirst($channelName),
                    'Status' => $result['success'] ? '✅ Success' : '❌ Failed',
                    'Message' => $result['message'],
                    'Configuration' => $setting->isConfigured() ? 'Valid' : 'Invalid'
                ];

                if (!$result['success']) {
                    $overallSuccess = false;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'Channel' => ucfirst($channelName),
                    'Status' => '❌ Error',
                    'Message' => $e->getMessage(),
                    'Configuration' => 'Error'
                ];
                $overallSuccess = false;
            }
        }

        $this->newLine();
        $this->table(['Channel', 'Status', 'Message', 'Configuration'], $results);

        if ($overallSuccess) {
            $this->info('✅ All configured channels tested successfully!');
            return Command::SUCCESS;
        } else {
            $this->warn('⚠️ Some channels failed testing. Check configuration.');
            return Command::FAILURE;
        }
    }

    /**
     * Show channel configuration help
     */
    protected function showChannelHelp(): void
    {
        $this->info('Notification Channel Configuration Help');
        $this->newLine();

        foreach (UserNotificationSettings::getSupportedChannels() as $channel => $info) {
            $this->line("<info>{$info['name']}</info>");
            $this->line("  Description: {$info['description']}");
            $this->line("  Required: " . implode(', ', $info['required_fields']));
            if (!empty($info['optional_fields'])) {
                $this->line("  Optional: " . implode(', ', $info['optional_fields']));
            }
            $this->newLine();
        }
    }

    /**
     * Create sample test data
     */
    protected function createTestAlert(): array
    {
        return [
            'ticket' => [
                'id' => 999,
                'event_name' => 'Test Concert - Enhanced Alerts',
                'price' => 99.99,
                'quantity' => 2,
                'platform' => 'Test Platform',
                'venue' => 'Test Venue',
                'event_date' => now()->addDays(7)->toISOString()
            ],
            'alert' => [
                'id' => 999
            ],
            'priority' => 2,
            'priority_label' => 'Test',
            'prediction' => [
                'price_trend' => 'stable',
                'price_change' => 0,
                'availability_trend' => 'stable', 
                'availability_change' => 0,
                'demand_level' => 'medium'
            ],
            'context' => [
                'recommendation' => 'This is a test notification from Enhanced Alert System'
            ],
            'actions' => [
                'view_ticket' => 'https://example.com/test',
                'purchase_now' => 'https://example.com/test',
                'snooze_alert' => 'https://example.com/test'
            ]
        ];
    }
}
