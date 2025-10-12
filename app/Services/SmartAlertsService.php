<?php

namespace App\Services;

use App\Models\User;
use App\Models\TicketAlert;
use App\Services\NotificationChannels\SmsNotificationService;
use App\Services\NotificationChannels\PushNotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmartAlertsService
{
    public function __construct(
        private SmsNotificationService $smsService,
        private PushNotificationService $pushService
    ) {}

    /**
     * Send multi-channel alert for ticket availability
     */
    public function sendTicketAlert(TicketAlert $alert, array $tickets): void
    {
        $user = $alert->user;
        $preferences = $user->notification_preferences ?? [];
        
        $alertData = [
            'alert' => $alert,
            'tickets' => $tickets,
            'user' => $user,
            'total_matches' => count($tickets),
            'price_range' => $this->calculatePriceRange($tickets),
            'platforms' => $this->getUniquePlatforms($tickets),
        ];

        // Send notifications based on user preferences
        if ($this->shouldSendEmail($preferences)) {
            $this->sendEmailAlert($alertData);
        }

        if ($this->shouldSendSms($preferences)) {
            $this->sendSmsAlert($alertData);
        }

        if ($this->shouldSendPush($preferences)) {
            $this->sendPushAlert($alertData);
        }

        // Update alert statistics
        $this->updateAlertStats($alert);
    }

    /**
     * Send price drop alert
     */
    public function sendPriceDropAlert(TicketAlert $alert, array $priceDrops): void
    {
        $user = $alert->user;
        $preferences = $user->notification_preferences ?? [];
        
        $alertData = [
            'alert' => $alert,
            'price_drops' => $priceDrops,
            'user' => $user,
            'type' => 'price_drop',
            'total_savings' => $this->calculateTotalSavings($priceDrops),
        ];

        if ($this->shouldSendEmail($preferences, 'price_alerts')) {
            $this->sendEmailAlert($alertData, 'price-drop');
        }

        if ($this->shouldSendSms($preferences, 'price_alerts')) {
            $this->sendSmsAlert($alertData, 'price-drop');
        }

        if ($this->shouldSendPush($preferences, 'price_alerts')) {
            $this->sendPushAlert($alertData, 'price-drop');
        }
    }

    /**
     * Send instant availability alert (high priority)
     */
    public function sendInstantAlert(TicketAlert $alert, array $tickets): void
    {
        $user = $alert->user;
        $preferences = $user->notification_preferences ?? [];
        
        // For instant alerts, we prioritize faster channels
        if ($this->shouldSendPush($preferences)) {
            $this->sendPushAlert([
                'alert' => $alert,
                'tickets' => $tickets,
                'user' => $user,
                'type' => 'instant',
                'priority' => 'high'
            ], 'instant');
        }

        if ($this->shouldSendSms($preferences)) {
            $this->sendSmsAlert([
                'alert' => $alert,
                'tickets' => $tickets,
                'user' => $user,
                'type' => 'instant'
            ], 'instant');
        }

        // Email as backup (slower but reliable)
        if ($this->shouldSendEmail($preferences)) {
            $this->sendEmailAlert([
                'alert' => $alert,
                'tickets' => $tickets,
                'user' => $user,
                'type' => 'instant'
            ], 'instant');
        }
    }

    private function sendEmailAlert(array $data, string $template = 'standard'): void
    {
        try {
            $mailClass = match($template) {
                'price-drop' => \App\Mail\PriceDropAlert::class,
                'instant' => \App\Mail\InstantTicketAlert::class,
                default => \App\Mail\TicketAvailabilityAlert::class,
            };

            Mail::to($data['user']->email)->send(new $mailClass($data));
            
            Log::info('Email alert sent', [
                'user_id' => $data['user']->id,
                'alert_id' => $data['alert']->id,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email alert', [
                'user_id' => $data['user']->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendSmsAlert(array $data, string $type = 'standard'): void
    {
        try {
            $message = $this->generateSmsMessage($data, $type);
            $this->smsService->send($data['user']->phone, $message);
            
            Log::info('SMS alert sent', [
                'user_id' => $data['user']->id,
                'alert_id' => $data['alert']->id,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS alert', [
                'user_id' => $data['user']->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendPushAlert(array $data, string $type = 'standard'): void
    {
        try {
            $payload = $this->generatePushPayload($data, $type);
            $this->pushService->send($data['user'], $payload);
            
            Log::info('Push notification sent', [
                'user_id' => $data['user']->id,
                'alert_id' => $data['alert']->id,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $data['user']->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function shouldSendEmail(array $preferences, string $type = 'general'): bool
    {
        return ($preferences['email_enabled'] ?? true) && 
               ($preferences["email_{$type}"] ?? true);
    }

    private function shouldSendSms(array $preferences, string $type = 'general'): bool
    {
        return ($preferences['sms_enabled'] ?? false) && 
               ($preferences["sms_{$type}"] ?? false) &&
               !empty($preferences['phone_number']);
    }

    private function shouldSendPush(array $preferences, string $type = 'general'): bool
    {
        return ($preferences['push_enabled'] ?? true) && 
               ($preferences["push_{$type}"] ?? true);
    }

    private function generateSmsMessage(array $data, string $type): string
    {
        return match($type) {
            'instant' => "🎟️ INSTANT ALERT: {$data['tickets'][0]['title']} tickets available! From £{$data['tickets'][0]['price']}. Act fast! hdtickets.com",
            'price-drop' => "💰 PRICE DROP: {$data['alert']['event_name']} tickets reduced by £{$data['total_savings']}! Check now: hdtickets.com",
            default => "🎫 HD Tickets: {$data['total_matches']} new matches found for '{$data['alert']['event_name']}'! View: hdtickets.com"
        };
    }

    private function generatePushPayload(array $data, string $type): array
    {
        return match($type) {
            'instant' => [
                'title' => '🚨 Instant Ticket Alert',
                'body' => "{$data['tickets'][0]['title']} tickets just became available!",
                'icon' => asset('images/logo-hdtickets-enhanced.svg'),
                'data' => [
                    'url' => route('tickets.show', $data['tickets'][0]['id']),
                    'type' => 'instant_alert',
                    'priority' => 'high'
                ]
            ],
            'price-drop' => [
                'title' => '💰 Price Drop Alert',
                'body' => "Save £{$data['total_savings']} on {$data['alert']['event_name']} tickets!",
                'icon' => asset('images/logo-hdtickets-enhanced.svg'),
                'data' => [
                    'url' => route('alerts.show', $data['alert']['id']),
                    'type' => 'price_drop'
                ]
            ],
            default => [
                'title' => '🎫 HD Tickets Alert',
                'body' => "{$data['total_matches']} new matches found for {$data['alert']['event_name']}",
                'icon' => asset('images/logo-hdtickets-enhanced.svg'),
                'data' => [
                    'url' => route('alerts.show', $data['alert']['id']),
                    'type' => 'availability'
                ]
            ]
        };
    }

    private function calculatePriceRange(array $tickets): array
    {
        $prices = array_column($tickets, 'price');
        return [
            'min' => min($prices),
            'max' => max($prices),
            'avg' => round(array_sum($prices) / count($prices), 2)
        ];
    }

    private function getUniquePlatforms(array $tickets): array
    {
        return array_unique(array_column($tickets, 'platform'));
    }

    private function calculateTotalSavings(array $priceDrops): float
    {
        return array_sum(array_column($priceDrops, 'saving_amount'));
    }

    private function updateAlertStats(TicketAlert $alert): void
    {
        $alert->increment('notifications_sent');
        $alert->update(['last_notification_at' => now()]);
    }
}