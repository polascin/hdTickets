<?php

namespace App\Services\NotificationChannels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    private string $apiKey;
    private string $apiSecret;
    private string $fromNumber;
    private string $provider;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiSecret = config('services.sms.api_secret');
        $this->fromNumber = config('services.sms.from_number', 'HD Tickets');
        $this->provider = config('services.sms.provider', 'twilio');
    }

    /**
     * Send SMS notification
     */
    public function send(string $phoneNumber, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('SMS service not configured');
            return false;
        }

        try {
            $response = match($this->provider) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $message),
                'nexmo' => $this->sendViaNexmo($phoneNumber, $message),
                'textlocal' => $this->sendViaTextLocal($phoneNumber, $message),
                default => throw new \InvalidArgumentException("Unsupported SMS provider: {$this->provider}")
            };

            if ($response['success']) {
                Log::info('SMS sent successfully', [
                    'to' => $phoneNumber,
                    'provider' => $this->provider,
                    'message_id' => $response['message_id'] ?? null
                ]);
                return true;
            }

            Log::error('SMS failed to send', [
                'to' => $phoneNumber,
                'provider' => $this->provider,
                'error' => $response['error'] ?? 'Unknown error'
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'to' => $phoneNumber,
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $phoneNumber, string $message): array
    {
        $accountSid = $this->apiKey;
        $authToken = $this->apiSecret;
        
        $response = Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $this->fromNumber,
                'To' => $this->formatPhoneNumber($phoneNumber),
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['sid'] ?? null
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Twilio API error'
        ];
    }

    /**
     * Send SMS via Nexmo/Vonage
     */
    private function sendViaNexmo(string $phoneNumber, string $message): array
    {
        $response = Http::asForm()->post('https://rest.nexmo.com/sms/json', [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'from' => $this->fromNumber,
            'to' => $this->formatPhoneNumber($phoneNumber),
            'text' => $message,
            'type' => 'text'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $messages = $data['messages'] ?? [];
            
            if (!empty($messages) && $messages[0]['status'] === '0') {
                return [
                    'success' => true,
                    'message_id' => $messages[0]['message-id'] ?? null
                ];
            }
            
            return [
                'success' => false,
                'error' => $messages[0]['error-text'] ?? 'Nexmo API error'
            ];
        }

        return [
            'success' => false,
            'error' => 'Nexmo API request failed'
        ];
    }

    /**
     * Send SMS via TextLocal (UK-focused)
     */
    private function sendViaTextLocal(string $phoneNumber, string $message): array
    {
        $response = Http::asForm()->post('https://api.textlocal.in/send/', [
            'apikey' => $this->apiKey,
            'numbers' => $this->formatPhoneNumber($phoneNumber),
            'message' => $message,
            'sender' => $this->fromNumber,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            if ($data['status'] === 'success') {
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null
                ];
            }
            
            return [
                'success' => false,
                'error' => $data['errors'][0]['message'] ?? 'TextLocal API error'
            ];
        }

        return [
            'success' => false,
            'error' => 'TextLocal API request failed'
        ];
    }

    /**
     * Format phone number for international use
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add UK country code if not present
        if (strlen($phoneNumber) === 11 && substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '44' . substr($phoneNumber, 1);
        }
        
        // Add + prefix for international format
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }
        
        return $phoneNumber;
    }

    /**
     * Check if SMS service is properly configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Basic validation for UK phone numbers
        return preg_match('/^(\+44|44|0)[1-9]\d{8,9}$/', $cleaned) === 1;
    }

    /**
     * Get SMS delivery status
     */
    public function getDeliveryStatus(string $messageId): ?string
    {
        try {
            return match($this->provider) {
                'twilio' => $this->getTwilioStatus($messageId),
                'nexmo' => $this->getNexmoStatus($messageId),
                default => null
            };
        } catch (\Exception $e) {
            Log::error('Failed to get SMS delivery status', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function getTwilioStatus(string $messageId): ?string
    {
        $accountSid = $this->apiKey;
        $authToken = $this->apiSecret;
        
        $response = Http::withBasicAuth($accountSid, $authToken)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages/{$messageId}.json");

        if ($response->successful()) {
            return $response->json()['status'] ?? null;
        }

        return null;
    }

    private function getNexmoStatus(string $messageId): ?string
    {
        $response = Http::get('https://rest.nexmo.com/search/message', [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'id' => $messageId
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['status'] ?? null;
        }

        return null;
    }
}