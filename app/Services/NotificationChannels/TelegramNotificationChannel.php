<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

use App\Models\User;
use App\Models\UserNotificationSettings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationChannel
{
    protected $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    /**
     * Send notification to Telegram
     */
    /**
     * Send
     */
    public function send(User $user, array $alertData): bool
    {
        try {
            $telegramSettings = $this->getUserTelegramSettings($user);

            if (! $telegramSettings || ! $telegramSettings->is_enabled) {
                Log::info('Telegram notifications disabled for user', ['user_id' => $user->id]);

                return FALSE;
            }

            $message = $this->buildTelegramMessage($alertData);

            $chatId = $telegramSettings->chat_id;
            if (! $chatId) {
                Log::warning('No Telegram chat ID configured');

                return FALSE;
            }

            return $this->sendMessage($chatId, $message);
        } catch (Exception $e) {
            Log::error('Failed to send Telegram notification', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return FALSE;
        }
    }

    /**
     * Test Telegram connection
     */
    /**
     * TestConnection
     */
    public function testConnection(User $user): array
    {
        try {
            $testMessage = '*Test Event*
Price: $99.99
Available: 2 tickets
Platform: Test Platform';

            $telegramSettings = $this->getUserTelegramSettings($user);

            if (! $telegramSettings || ! $telegramSettings->chat_id) {
                return [
                    'success' => FALSE,
                    'message' => 'No Telegram chat ID configured',
                ];
            }

            $success = $this->sendMessage($telegramSettings->chat_id, $testMessage);

            return [
                'success' => $success,
                'message' => $success ? 'Telegram test notification sent successfully' : 'Failed to send Telegram test notification',
            ];
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => 'Telegram test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build Telegram message text
     */
    /**
     * BuildTelegramMessage
     */
    protected function buildTelegramMessage(array $alertData): string
    {
        $ticket = $alertData['ticket'];
        $priority = $alertData['priority_label'] ?? 'Normal';

        $message = "🎟️ *Ticket Alert - {$priority} Priority* \n" .
                   "*Event:* {$ticket['event_name']}\n" .
                   '*Price:* ' . $ticket['price'] . "\n" .
                   '*Available:* ' . $ticket['quantity'] . " tickets\n" .
                   '*Platform:* ' . $ticket['platform'] . "\n";

        if (! empty($ticket['venue'])) {
            $message .= "*Venue:* {$ticket['venue']}\n";
        }

        if (! empty($ticket['event_date'])) {
            $message .= '*Date:* ' . date('M j, Y g:i A', strtotime($ticket['event_date'])) . "\n";
        }

        return $message;
    }

    /**
     * Send Telegram message
     */
    /**
     * SendMessage
     */
    protected function sendMessage(string $chatId, string $message): bool
    {
        if (! $this->botToken) {
            Log::warning('No Telegram bot token configured');

            return FALSE;
        }

        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ]);

        if ($response->successful()) {
            Log::info('Telegram notification sent successfully');

            return TRUE;
        }
        Log::error('Telegram message failed', [
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);

        return FALSE;
    }

    /**
     * Get user's Telegram settings
     */
    protected function getUserTelegramSettings(User $user)
    {
        return Cache::remember("telegram_settings:{$user->id}", 3600, function () use ($user) {
            return UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', 'telegram')
                ->first();
        });
    }
}
