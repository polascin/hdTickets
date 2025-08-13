<?php declare(strict_types=1);

namespace App\Channels;

use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function get_class;
use function strlen;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     *
     * @return array<string, mixed>|null
     */
    /**
     * Send
     *
     * @param mixed $notifiable
     */
    public function send($notifiable, Notification $notification): ?array
    {
        // Get the phone number - check multiple possible attributes
        $phone = $this->getPhoneNumber($notifiable);

        if (! $phone) {
            Log::warning('SMS notification not sent: No phone number found for user', [
                'user_id'      => $notifiable->id ?? NULL,
                'notification' => get_class($notification),
            ]);

            return NULL;
        }

        // Get SMS message content
        if (! method_exists($notification, 'toSms')) {
            Log::error('SMS notification not sent: toSms method not implemented', [
                'notification' => get_class($notification),
            ]);

            return NULL;
        }

        $message = $notification->toSms($notifiable);

        if (empty($message)) {
            Log::warning('SMS notification not sent: Empty message content');

            return NULL;
        }

        try {
            return $this->sendSms($phone, $message, $notifiable);
        } catch (Exception $e) {
            Log::error('Failed to send SMS notification', [
                'phone'   => $phone,
                'message' => $message,
                'error'   => $e->getMessage(),
                'user_id' => $notifiable->id ?? NULL,
            ]);

            return NULL;
        }
    }

    /**
     * Get phone number from notifiable entity.
     *
     * @param mixed $notifiable
     */
    /**
     * Get  phone number
     *
     * @param mixed $notifiable
     */
    protected function getPhoneNumber($notifiable): ?string
    {
        // Try different common phone number attribute names
        $phoneFields = ['phone', 'phone_number', 'mobile', 'cell_phone'];

        foreach ($phoneFields as $field) {
            if (isset($notifiable->{$field}) && ! empty($notifiable->{$field})) {
                return $this->formatPhoneNumber($notifiable->{$field});
            }
        }

        return NULL;
    }

    /**
     * Format phone number to E.164 standard.
     */
    /**
     * FormatPhoneNumber
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/\D/', '', $phone);

        if ($cleaned === NULL || $cleaned === '') {
            return $phone; // Return original if cleaning failed
        }

        // Add country code if missing (default to US +1)
        if (strlen($cleaned) === 10) {
            $cleaned = '1' . $cleaned;
        }

        return '+' . $cleaned;
    }

    /**
     * Send SMS using configured service.
     *
     * @param mixed $notifiable
     *
     * @return array<string, mixed>
     */
    /**
     * SendSms
     *
     * @param mixed $notifiable
     */
    protected function sendSms(string $phone, string $message, $notifiable): array
    {
        $smsService = config('services.sms.default', 'twilio');

        switch ($smsService) {
            case 'twilio':
                $data = $this->sendViaTwilio($phone, $message);

                if (config('services.twilio.status_callback')) {
                    $data['status_callback'] = config('services.twilio.status_callback');
                }

                return $data;
            case 'nexmo':
                return $this->sendViaNexmo($phone, $message);
            case 'log':
                return $this->sendViaLog($phone, $message, $notifiable);
            default:
                throw new Exception("Unsupported SMS service: {$smsService}");
        }
    }

    /**
     * Send SMS via Twilio.
     *
     * @return array<string, mixed>
     */
    /**
     * SendViaTwilio
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.token');
        $fromNumber = config('services.twilio.from');

        if (! $accountSid || ! $authToken || ! $fromNumber) {
            throw new Exception('Twilio credentials not configured');
        }

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From'           => $fromNumber,
                'To'             => $phone,
                'Body'           => $message,
                'StatusCallback' => config('services.twilio.status_callback'),
            ]);

        if (! $response->successful()) {
            throw new Exception('Twilio API error: ' . $response->body());
        }

        $data = $response->json();

        Log::info('SMS sent via Twilio', [
            'phone'  => $phone,
            'sid'    => $data['sid'] ?? NULL,
            'status' => $data['status'] ?? NULL,
        ]);

        return $data;
    }

    /**
     * Send SMS via Nexmo (Vonage).
     *
     * @return array<string, mixed>
     */
    /**
     * SendViaNexmo
     */
    protected function sendViaNexmo(string $phone, string $message): array
    {
        $apiKey = config('services.nexmo.key');
        $apiSecret = config('services.nexmo.secret');
        $fromNumber = config('services.nexmo.from');

        if (! $apiKey || ! $apiSecret || ! $fromNumber) {
            throw new Exception('Nexmo credentials not configured');
        }

        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret,
            'from'       => $fromNumber,
            'to'         => $phone,
            'text'       => $message,
        ]);

        if (! $response->successful()) {
            throw new Exception('Nexmo API error: ' . $response->body());
        }

        $data = $response->json();

        Log::info('SMS sent via Nexmo', [
            'phone'      => $phone,
            'message_id' => $data['messages'][0]['message-id'] ?? NULL,
            'status'     => $data['messages'][0]['status'] ?? NULL,
        ]);

        return $data;
    }

    /**
     * Mock SMS sending by logging (for testing/development).
     *
     * @param mixed $notifiable
     *
     * @return array<string, mixed>
     */
    /**
     * SendViaLog
     *
     * @param mixed $notifiable
     */
    protected function sendViaLog(string $phone, string $message, $notifiable): array
    {
        Log::info('SMS NOTIFICATION (Log Channel)', [
            'to'        => $phone,
            'user_id'   => $notifiable->id ?? NULL,
            'username'  => $notifiable->username ?? NULL,
            'message'   => $message,
            'timestamp' => now()->toISOString(),
        ]);

        return [
            'status'  => 'logged',
            'phone'   => $phone,
            'message' => $message,
            'sent_at' => now()->toISOString(),
        ];
    }
}
