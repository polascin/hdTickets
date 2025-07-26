<?php

namespace App\Services;

use App\Models\TicketAlert;
use App\Models\AlertEscalation;
use App\Models\User;
use App\Jobs\ProcessEscalatedAlert;
use App\Jobs\RetryFailedAlert;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AlertEscalationService
{
    protected $escalationRules;
    protected $retryStrategies;

    public function __construct()
    {
        $this->escalationRules = $this->loadEscalationRules();
        $this->retryStrategies = $this->loadRetryStrategies();
    }

    /**
     * Schedule alert escalation based on priority and conditions
     */
    public function scheduleEscalation(TicketAlert $alert, array $alertData): void
    {
        $priority = $alertData['priority'] ?? 2;
        
        // Don't escalate if user has recently been active
        if ($this->isUserRecentlyActive($alert->user_id)) {
            Log::info('Skipping escalation for recently active user', [
                'user_id' => $alert->user_id,
                'alert_id' => $alert->id
            ]);
            return;
        }

        // Get escalation strategy based on priority
        $strategy = $this->getEscalationStrategy($priority);
        
        if (!$strategy) {
            return; // No escalation needed
        }

        // Create escalation record
        $escalation = AlertEscalation::create([
            'alert_id' => $alert->id,
            'user_id' => $alert->user_id,
            'priority' => $priority,
            'strategy' => $strategy['name'],
            'scheduled_at' => now()->addMinutes($strategy['initial_delay']),
            'attempts' => 0,
            'max_attempts' => $strategy['max_attempts'],
            'status' => 'scheduled',
            'alert_data' => json_encode($alertData),
            'escalation_config' => json_encode($strategy)
        ]);

        // Schedule the escalation job
        ProcessEscalatedAlert::dispatch($escalation)
            ->delay(now()->addMinutes($strategy['initial_delay']));

        Log::info('Alert escalation scheduled', [
            'escalation_id' => $escalation->id,
            'alert_id' => $alert->id,
            'strategy' => $strategy['name'],
            'delay_minutes' => $strategy['initial_delay']
        ]);
    }

    /**
     * Process escalated alert
     */
    public function processEscalation(AlertEscalation $escalation): void
    {
        try {
            // Check if escalation is still valid
            if (!$this->isEscalationValid($escalation)) {
                $this->cancelEscalation($escalation, 'no_longer_valid');
                return;
            }

            // Increment attempt counter
            $escalation->increment('attempts');
            $escalation->update(['last_attempted_at' => now()]);

            // Get current strategy configuration
            $strategy = json_decode($escalation->escalation_config, true);
            $alertData = json_decode($escalation->alert_data, true);

            // Determine escalation level based on attempt number
            $escalationLevel = $this->getEscalationLevel($escalation->attempts, $strategy);
            
            // Send escalated notifications
            $success = $this->sendEscalatedNotifications($escalation, $alertData, $escalationLevel);

            if ($success) {
                $escalation->update(['status' => 'completed']);
                Log::info('Alert escalation completed successfully', [
                    'escalation_id' => $escalation->id,
                    'attempts' => $escalation->attempts
                ]);
            } else {
                $this->handleEscalationFailure($escalation, $strategy);
            }

        } catch (\Exception $e) {
            Log::error('Alert escalation failed', [
                'escalation_id' => $escalation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->handleEscalationFailure($escalation, json_decode($escalation->escalation_config, true));
        }
    }

    /**
     * Send escalated notifications with increased urgency
     */
    protected function sendEscalatedNotifications(AlertEscalation $escalation, array $alertData, array $escalationLevel): bool
    {
        $user = $escalation->user;
        $success = true;

        // Add escalation context to alert data
        $alertData['escalation'] = [
            'level' => $escalationLevel['level'],
            'attempt' => $escalation->attempts,
            'urgency' => $escalationLevel['urgency'],
            'message' => $escalationLevel['message']
        ];

        // Send through escalated channels
        foreach ($escalationLevel['channels'] as $channel) {
            try {
                if ($channel === 'sms' && $this->canSendSMS($user)) {
                    $this->sendEscalatedSMS($user, $alertData);
                } elseif ($channel === 'phone' && $this->canCallUser($user)) {
                    $this->initiatePhoneCall($user, $alertData);
                } elseif ($channel === 'emergency_contact' && $this->hasEmergencyContact($user)) {
                    $this->notifyEmergencyContact($user, $alertData);
                } elseif ($channel === 'slack_urgent') {
                    $this->sendUrgentSlackMessage($user, $alertData);
                } elseif ($channel === 'discord_ping') {
                    $this->sendDiscordPing($user, $alertData);
                } else {
                    // Use enhanced notification system
                    $enhancedAlertSystem = new EnhancedAlertSystem();
                    $enhancedAlertSystem->sendMultiChannelNotification($user, $alertData, [$channel]);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to send escalated notification via {$channel}", [
                    'escalation_id' => $escalation->id,
                    'channel' => $channel,
                    'error' => $e->getMessage()
                ]);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Handle escalation failure and schedule retry if appropriate
     */
    protected function handleEscalationFailure(AlertEscalation $escalation, array $strategy): void
    {
        if ($escalation->attempts >= $escalation->max_attempts) {
            $escalation->update(['status' => 'failed']);
            Log::warning('Alert escalation failed after max attempts', [
                'escalation_id' => $escalation->id,
                'attempts' => $escalation->attempts
            ]);
            return;
        }

        // Schedule retry with exponential backoff
        $retryDelay = $this->calculateRetryDelay($escalation->attempts, $strategy);
        
        $escalation->update([
            'status' => 'retrying',
            'next_retry_at' => now()->addMinutes($retryDelay)
        ]);

        // Schedule retry job
        ProcessEscalatedAlert::dispatch($escalation)
            ->delay(now()->addMinutes($retryDelay));

        Log::info('Alert escalation retry scheduled', [
            'escalation_id' => $escalation->id,
            'attempt' => $escalation->attempts + 1,
            'retry_delay' => $retryDelay
        ]);
    }

    /**
     * Check if escalation is still valid
     */
    protected function isEscalationValid(AlertEscalation $escalation): bool
    {
        $alert = $escalation->alert;
        
        // Check if alert is still active
        if (!$alert || !$alert->is_active) {
            return false;
        }

        // Check if user has already acknowledged
        if ($this->hasUserAcknowledged($escalation->user_id, $alert->id)) {
            return false;
        }

        // Check if user has become active
        if ($this->isUserRecentlyActive($escalation->user_id)) {
            return false;
        }

        // Check if ticket is still available
        $alertData = json_decode($escalation->alert_data, true);
        if (isset($alertData['ticket']['id'])) {
            $ticket = \App\Models\ScrapedTicket::find($alertData['ticket']['id']);
            if (!$ticket || !$ticket->is_available) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cancel escalation with reason
     */
    protected function cancelEscalation(AlertEscalation $escalation, string $reason): void
    {
        $escalation->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);

        Log::info('Alert escalation cancelled', [
            'escalation_id' => $escalation->id,
            'reason' => $reason
        ]);
    }

    /**
     * Get escalation strategy based on priority
     */
    protected function getEscalationStrategy(int $priority): ?array
    {
        return $this->escalationRules[$priority] ?? null;
    }

    /**
     * Get escalation level configuration
     */
    protected function getEscalationLevel(int $attempt, array $strategy): array
    {
        $levels = $strategy['levels'];
        
        // Find appropriate level based on attempt number
        foreach ($levels as $level) {
            if ($attempt <= $level['max_attempt']) {
                return $level;
            }
        }

        // Return highest level if exceeded
        return end($levels);
    }

    /**
     * Calculate retry delay with exponential backoff
     */
    protected function calculateRetryDelay(int $attempt, array $strategy): int
    {
        $baseDelay = $strategy['retry_base_delay'] ?? 5;
        $maxDelay = $strategy['retry_max_delay'] ?? 60;
        $multiplier = $strategy['retry_multiplier'] ?? 2;

        $delay = $baseDelay * pow($multiplier, $attempt - 1);
        return min($delay, $maxDelay);
    }

    /**
     * Check if user is recently active
     */
    protected function isUserRecentlyActive(int $userId): bool
    {
        $lastActivity = Cache::get("user_activity:{$userId}");
        
        if (!$lastActivity) {
            return false;
        }

        return Carbon::parse($lastActivity)->diffInMinutes(now()) <= 15;
    }

    /**
     * Check if user has acknowledged the alert
     */
    protected function hasUserAcknowledged(int $userId, int $alertId): bool
    {
        return Cache::has("alert_acknowledged:{$userId}:{$alertId}");
    }

    /**
     * Check if SMS can be sent to user
     */
    protected function canSendSMS(User $user): bool
    {
        // Check if user has phone number and SMS enabled
        return !empty($user->phone) && 
               ($user->preferences['sms_alerts'] ?? false);
    }

    /**
     * Check if user can be called
     */
    protected function canCallUser(User $user): bool
    {
        // Check if user has phone and allows phone calls
        return !empty($user->phone) && 
               ($user->preferences['phone_alerts'] ?? false) &&
               $this->isWithinCallHours();
    }

    /**
     * Check if user has emergency contact
     */
    protected function hasEmergencyContact(User $user): bool
    {
        return !empty($user->emergency_contact_phone) || 
               !empty($user->emergency_contact_email);
    }

    /**
     * Check if current time is within allowed calling hours
     */
    protected function isWithinCallHours(): bool
    {
        $hour = now()->hour;
        return $hour >= 8 && $hour <= 22; // 8 AM to 10 PM
    }

    /**
     * Send escalated SMS
     */
    protected function sendEscalatedSMS(User $user, array $alertData): void
    {
        $message = $this->buildEscalatedSMSMessage($alertData);
        
        // Use SMS service (Twilio, etc.)
        // Implementation would depend on chosen SMS provider
        Log::info('Escalated SMS sent', [
            'user_id' => $user->id,
            'phone' => $user->phone
        ]);
    }

    /**
     * Initiate phone call
     */
    protected function initiatePhoneCall(User $user, array $alertData): void
    {
        // Use voice service (Twilio Voice, etc.)
        Log::info('Phone call initiated', [
            'user_id' => $user->id,
            'phone' => $user->phone
        ]);
    }

    /**
     * Notify emergency contact
     */
    protected function notifyEmergencyContact(User $user, array $alertData): void
    {
        $message = $this->buildEmergencyContactMessage($user, $alertData);
        
        // Send to emergency contact via SMS/email
        Log::info('Emergency contact notified', [
            'user_id' => $user->id,
            'emergency_contact' => $user->emergency_contact_phone
        ]);
    }

    /**
     * Send urgent Slack message
     */
    protected function sendUrgentSlackMessage(User $user, array $alertData): void
    {
        // Send via Slack API with high priority
        Log::info('Urgent Slack message sent', [
            'user_id' => $user->id
        ]);
    }

    /**
     * Send Discord ping
     */
    protected function sendDiscordPing(User $user, array $alertData): void
    {
        // Send via Discord API with @everyone or role ping
        Log::info('Discord ping sent', [
            'user_id' => $user->id
        ]);
    }

    /**
     * Build escalated SMS message
     */
    protected function buildEscalatedSMSMessage(array $alertData): string
    {
        $ticket = $alertData['ticket'];
        $escalation = $alertData['escalation'];
        
        return "🚨 URGENT TICKET ALERT (Attempt {$escalation['attempt']}) 🚨\n" .
               "{$ticket['event_name']}\n" .
               "Price: \${$ticket['price']}\n" .
               "Available: {$ticket['quantity']} tickets\n" .
               "{$escalation['message']}\n" .
               "Act now: {$alertData['actions']['purchase_now']}";
    }

    /**
     * Build emergency contact message
     */
    protected function buildEmergencyContactMessage(User $user, array $alertData): string
    {
        $ticket = $alertData['ticket'];
        
        return "Emergency ticket alert for {$user->name}.\n" .
               "Event: {$ticket['event_name']}\n" .
               "This is an automated message. The user has requested urgent notifications for ticket availability.";
    }

    /**
     * Load escalation rules configuration
     */
    protected function loadEscalationRules(): array
    {
        return [
            5 => [ // Critical priority
                'name' => 'critical_immediate',
                'initial_delay' => 2, // 2 minutes
                'max_attempts' => 5,
                'retry_base_delay' => 3,
                'retry_max_delay' => 15,
                'retry_multiplier' => 1.5,
                'levels' => [
                    [
                        'level' => 1,
                        'max_attempt' => 2,
                        'urgency' => 'high',
                        'message' => 'Critical ticket alert requiring immediate attention!',
                        'channels' => ['sms', 'slack_urgent', 'push']
                    ],
                    [
                        'level' => 2,
                        'max_attempt' => 4,
                        'urgency' => 'critical',
                        'message' => 'URGENT: Ticket availability critical - act now!',
                        'channels' => ['sms', 'phone', 'slack_urgent', 'discord_ping']
                    ],
                    [
                        'level' => 3,
                        'max_attempt' => 5,
                        'urgency' => 'emergency',
                        'message' => 'FINAL ALERT: Last chance for tickets!',
                        'channels' => ['sms', 'phone', 'emergency_contact', 'slack_urgent']
                    ]
                ]
            ],
            4 => [ // High priority
                'name' => 'high_priority',
                'initial_delay' => 5, // 5 minutes
                'max_attempts' => 3,
                'retry_base_delay' => 5,
                'retry_max_delay' => 30,
                'retry_multiplier' => 2,
                'levels' => [
                    [
                        'level' => 1,
                        'max_attempt' => 2,
                        'urgency' => 'medium',
                        'message' => 'High priority ticket alert - check soon!',
                        'channels' => ['push', 'slack']
                    ],
                    [
                        'level' => 2,
                        'max_attempt' => 3,
                        'urgency' => 'high',
                        'message' => 'High priority alert - tickets may sell out!',
                        'channels' => ['sms', 'push', 'slack', 'discord']
                    ]
                ]
            ]
        ];
    }

    /**
     * Load retry strategies configuration
     */
    protected function loadRetryStrategies(): array
    {
        return [
            'exponential_backoff' => [
                'base_delay' => 5,
                'max_delay' => 60,
                'multiplier' => 2
            ],
            'linear_backoff' => [
                'base_delay' => 10,
                'max_delay' => 60,
                'increment' => 10
            ]
        ];
    }
}
