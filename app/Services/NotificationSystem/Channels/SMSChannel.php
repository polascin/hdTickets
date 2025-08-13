<?php declare(strict_types=1);

namespace App\Services\NotificationSystem\Channels;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;
use Twilio\Rest\Client;

use function strlen;

class SMSChannel implements NotificationChannelInterface
{
    protected $twilio;

    public function __construct()
    {
        if ($this->isAvailable()) {
            $this->twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token'),
            );
        }
    }

    /**
     * Send
     */
    public function send(User $user, array $notification): bool
    {
        try {
            if (! $this->twilio) {
                Log::warning('Twilio not configured, skipping SMS notification');

                return FALSE;
            }

            if (! $user->phone || ! $user->sms_notifications_enabled) {
                Log::info('Skipping SMS notification - no phone or disabled', [
                    'user_id' => $user->id,
                    'type'    => $notification['type'],
                ]);

                return FALSE;
            }

            // Format message for SMS
            $message = $this->formatSMSMessage($notification);

            $this->twilio->messages->create(
                $user->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ],
            );

            Log::info('SMS notification sent successfully', [
                'user_id' => $user->id,
                'phone'   => $user->phone,
                'type'    => $notification['type'],
            ]);

            return TRUE;
        } catch (Throwable $e) {
            Log::error('Failed to send SMS notification', [
                'user_id' => $user->id,
                'phone'   => $user->phone,
                'type'    => $notification['type'],
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Check if  available
     */
    public function isAvailable(): bool
    {
        return ! empty(config('services.twilio.sid'))
               && ! empty(config('services.twilio.token'))
               && ! empty(config('services.twilio.from'));
    }

    /**
     * FormatSMSMessage
     */
    protected function formatSMSMessage(array $notification): string
    {
        $message = $notification['title'] . "\n\n";
        $message .= $notification['message'];

        switch ($notification['type']) {
            case 'price_drop':
                $data = $notification['data'];
                $savings = $data['old_price'] - $data['new_price'];
                $message .= "\n\nSavings: {$data['currency']}{$savings}";
                if (! empty($data['ticket_url'])) {
                    $message .= "\n\nView: " . $this->shortenUrl($data['ticket_url']);
                }

                break;
            case 'ticket_available':
                $data = $notification['data'];
                $message .= "\n\nPrice: {$data['currency']}{$data['price']}";
                if (! empty($data['venue'])) {
                    $message .= "\nVenue: {$data['venue']}";
                }
                if (! empty($data['ticket_url'])) {
                    $message .= "\n\nView: " . $this->shortenUrl($data['ticket_url']);
                }

                break;
            case 'system_status':
                $data = $notification['data'];
                if (! empty($data['affected_platforms'])) {
                    $message .= "\n\nAffected: " . implode(', ', $data['affected_platforms']);
                }

                break;
        }

        // SMS character limit consideration
        if (strlen($message) > 1500) {
            $message = substr($message, 0, 1500) . '...';
        }

        return $message;
    }

    /**
     * ShortenUrl
     */
    protected function shortenUrl(string $url): string
    {
        // Simple URL shortening for SMS - in production, use a proper service
        if (strlen($url) > 50) {
            return parse_url($url, PHP_URL_HOST) . '/...';
        }

        return $url;
    }
}
