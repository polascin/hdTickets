<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ConfigureNotificationChannels extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:setup-notifications 
                            {--slack : Configure Slack notifications}
                            {--discord : Configure Discord notifications}
                            {--telegram : Configure Telegram notifications}
                            {--all : Configure all notification channels}';

    /**
     * The console command description.
     */
    protected $description = 'Configure notification channels for the Advanced Analytics Dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Advanced Analytics Dashboard - Notification Channel Setup');
        $this->info('=' . str_repeat('=', 60));
        $this->newLine();

        $setupAll = $this->option('all');
        $setupSlack = $this->option('slack') || $setupAll;
        $setupDiscord = $this->option('discord') || $setupAll;
        $setupTelegram = $this->option('telegram') || $setupAll;

        if (!$setupSlack && !$setupDiscord && !$setupTelegram) {
            $this->info('📋 Available notification channels:');
            $this->line('   • Slack - Team collaboration notifications');
            $this->line('   • Discord - Community notifications');
            $this->line('   • Telegram - Mobile messaging');
            $this->newLine();
            
            $setupSlack = $this->confirm('Configure Slack notifications?');
            $setupDiscord = $this->confirm('Configure Discord notifications?');
            $setupTelegram = $this->confirm('Configure Telegram notifications?');
        }

        if ($setupSlack) {
            $this->configureSlack();
        }

        if ($setupDiscord) {
            $this->configureDiscord();
        }

        if ($setupTelegram) {
            $this->configureTelegram();
        }

        $this->displayConfiguration();
        $this->info('🎉 Notification channel configuration completed!');
    }

    private function configureSlack()
    {
        $this->info('📢 Configuring Slack Notifications');
        $this->newLine();

        $botToken = $this->ask('Enter your Slack Bot Token (starts with xoxb-)');
        $defaultChannel = $this->ask('Enter default channel (e.g., #alerts)', '#alerts');
        $signingSecret = $this->ask('Enter Slack Signing Secret (for webhooks)', '');

        if ($botToken) {
            $this->updateEnvFile([
                'SLACK_BOT_TOKEN' => $botToken,
                'SLACK_DEFAULT_CHANNEL' => $defaultChannel,
                'SLACK_SIGNING_SECRET' => $signingSecret,
            ]);

            $this->info('✅ Slack configuration saved to .env file');
        } else {
            $this->error('❌ Slack bot token is required');
        }

        $this->newLine();
    }

    private function configureDiscord()
    {
        $this->info('🎮 Configuring Discord Notifications');
        $this->newLine();

        $webhookUrl = $this->ask('Enter Discord Webhook URL');
        $botToken = $this->ask('Enter Discord Bot Token (optional)', '');

        if ($webhookUrl) {
            $this->updateEnvFile([
                'DISCORD_WEBHOOK_URL' => $webhookUrl,
                'DISCORD_BOT_TOKEN' => $botToken,
            ]);

            $this->info('✅ Discord configuration saved to .env file');
        } else {
            $this->error('❌ Discord webhook URL is required');
        }

        $this->newLine();
    }

    private function configureTelegram()
    {
        $this->info('📱 Configuring Telegram Notifications');
        $this->newLine();

        $botToken = $this->ask('Enter Telegram Bot Token');
        $chatId = $this->ask('Enter default Chat ID');

        if ($botToken && $chatId) {
            $this->updateEnvFile([
                'TELEGRAM_BOT_TOKEN' => $botToken,
                'TELEGRAM_CHAT_ID' => $chatId,
            ]);

            $this->info('✅ Telegram configuration saved to .env file');
        } else {
            $this->error('❌ Telegram bot token and chat ID are required');
        }

        $this->newLine();
    }

    private function updateEnvFile(array $values)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    private function displayConfiguration()
    {
        $this->info('📋 Current Notification Configuration:');
        $this->newLine();

        // Slack
        $slackToken = env('SLACK_BOT_TOKEN');
        $slackStatus = $slackToken ? '✅ Configured' : '❌ Not configured';
        $this->line("   Slack: {$slackStatus}");

        // Discord
        $discordWebhook = env('DISCORD_WEBHOOK_URL');
        $discordStatus = $discordWebhook ? '✅ Configured' : '❌ Not configured';
        $this->line("   Discord: {$discordStatus}");

        // Telegram
        $telegramToken = env('TELEGRAM_BOT_TOKEN');
        $telegramStatus = $telegramToken ? '✅ Configured' : '❌ Not configured';
        $this->line("   Telegram: {$telegramStatus}");

        $this->newLine();
        $this->info('💡 Next steps:');
        $this->line('   1. Run: php artisan config:cache');
        $this->line('   2. Test notifications with: php artisan analytics:test-notifications');
        $this->line('   3. Start queue workers: scripts/start-analytics-workers.bat');
        $this->newLine();
    }
}
