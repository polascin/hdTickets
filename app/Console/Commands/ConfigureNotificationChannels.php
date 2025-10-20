<?php declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class ConfigureNotificationChannels extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'analytics:setup-notifications 
                            {--slack : Configure Slack notifications}
                            {--discord : Configure Discord notifications}
                            {--telegram : Configure Telegram notifications}
                            {--all : Configure all notification channels}';

    /** The console command description. */
    protected $description = 'Configure notification channels for the Advanced Analytics Dashboard';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('🔔 Advanced Analytics Dashboard - Notification Channel Setup');
        $this->info('=' . str_repeat('=', 60));
        $this->newLine();

        $setupAll = (bool) $this->option('all');
        $setupSlack = (bool) $this->option('slack') || $setupAll;
        $setupDiscord = (bool) $this->option('discord') || $setupAll;
        $setupTelegram = (bool) $this->option('telegram') || $setupAll;

        if (! $setupSlack && ! $setupDiscord && ! $setupTelegram) {
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

        return Command::SUCCESS;
    }

    /**
     * Configure Slack notification settings.
     */
    /**
     * ConfigureSlack
     */
    private function configureSlack(): void
    {
        $this->info('📢 Configuring Slack Notifications');
        $this->newLine();

        $botToken = (string) $this->ask('Enter your Slack Bot Token (starts with xoxb-)');
        $defaultChannel = (string) $this->ask('Enter default channel (e.g., #alerts)', '#alerts');
        $signingSecret = (string) $this->ask('Enter Slack Signing Secret (for webhooks)', '');

        if ($botToken !== '' && $botToken !== '0') {
            $this->updateEnvFile([
                'SLACK_BOT_TOKEN'       => $botToken,
                'SLACK_DEFAULT_CHANNEL' => $defaultChannel,
                'SLACK_SIGNING_SECRET'  => $signingSecret,
            ]);

            $this->info('✅ Slack configuration saved to .env file');
        } else {
            $this->error('❌ Slack bot token is required');
        }

        $this->newLine();
    }

    /**
     * Configure Discord notification settings.
     */
    /**
     * ConfigureDiscord
     */
    private function configureDiscord(): void
    {
        $this->info('🎮 Configuring Discord Notifications');
        $this->newLine();

        $webhookUrl = (string) $this->ask('Enter Discord Webhook URL');
        $botToken = (string) $this->ask('Enter Discord Bot Token (optional)', '');

        if ($webhookUrl !== '' && $webhookUrl !== '0') {
            $this->updateEnvFile([
                'DISCORD_WEBHOOK_URL' => $webhookUrl,
                'DISCORD_BOT_TOKEN'   => $botToken,
            ]);

            $this->info('✅ Discord configuration saved to .env file');
        } else {
            $this->error('❌ Discord webhook URL is required');
        }

        $this->newLine();
    }

    /**
     * Configure Telegram notification settings.
     */
    /**
     * ConfigureTelegram
     */
    private function configureTelegram(): void
    {
        $this->info('📱 Configuring Telegram Notifications');
        $this->newLine();

        $botToken = (string) $this->ask('Enter Telegram Bot Token');
        $chatId = (string) $this->ask('Enter default Chat ID');

        if ($botToken && $chatId) {
            $this->updateEnvFile([
                'TELEGRAM_BOT_TOKEN' => $botToken,
                'TELEGRAM_CHAT_ID'   => $chatId,
            ]);

            $this->info('✅ Telegram configuration saved to .env file');
        } else {
            $this->error('❌ Telegram bot token and chat ID are required');
        }

        $this->newLine();
    }

    /**
     * Update environment file with new values.
     *
     * @param array<string, string> $values
     */
    /**
     * UpdateEnvFile
     */
    private function updateEnvFile(array $values): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        if ($envContent === FALSE) {
            throw new RuntimeException('Could not read .env file');
        }

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $result = preg_replace($pattern, $replacement, $envContent);
                if ($result !== NULL) {
                    $envContent = $result;
                }
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Display current notification configuration status.
     */
    /**
     * DisplayConfiguration
     */
    private function displayConfiguration(): void
    {
        $this->info('📋 Current Notification Configuration:');
        $this->newLine();

        // Slack
        $slackToken = config('services.slack.bot_token');
        $slackStatus = $slackToken ? '✅ Configured' : '❌ Not configured';
        $this->line("   Slack: {$slackStatus}");

        // Discord
        $discordWebhook = config('services.discord.webhook_url');
        $discordStatus = $discordWebhook ? '✅ Configured' : '❌ Not configured';
        $this->line("   Discord: {$discordStatus}");

        // Telegram
        $telegramToken = config('services.telegram.bot_token');
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
