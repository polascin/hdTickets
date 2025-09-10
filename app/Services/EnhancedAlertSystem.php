<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PriceAlertThreshold;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\SmartTicketAlert;
use App\Services\NotificationChannels\DiscordNotificationChannel;
use App\Services\NotificationChannels\SlackNotificationChannel;
use App\Services\NotificationChannels\TelegramNotificationChannel;
use App\Services\NotificationChannels\WebhookNotificationChannel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

use function count;
use function in_array;
use function strlen;

class EnhancedAlertSystem
{
    protected TicketAvailabilityPredictor $mlPredictor;

    protected AlertEscalationService $escalationService;

    protected $customChannels;

    public function __construct()
    {
        $this->mlPredictor = new TicketAvailabilityPredictor();
        $this->escalationService = new AlertEscalationService();
        $this->customChannels = [
            'slack'    => new SlackNotificationChannel(),
            'discord'  => new DiscordNotificationChannel(),
            'telegram' => new TelegramNotificationChannel(),
            'webhook'  => new WebhookNotificationChannel(),
        ];
    }

    /**
     * Monitor dynamic price conditions and alert as necessary
     */
    /**
     * MonitorDynamicPriceConditions
     */
    public function monitorDynamicPriceConditions(): void
    {
        $thresholds = PriceAlertThreshold::active()->get();

        foreach ($thresholds as $threshold) {
            $ticket = $threshold->ticket;
            $currentPrice = $ticket->price;

            if ($threshold->shouldTrigger($currentPrice)) {
                $this->triggerDynamicAlert($threshold, $currentPrice);
            }
        }
    }

    /**
     * Process smart alert with ML-based prioritization
     */
    /**
     * ProcessSmartAlert
     */
    public function processSmartAlert(ScrapedTicket $ticket, TicketAlert $alert): void
    {
        try {
            // Calculate smart priority based on multiple factors
            $priority = $this->calculateSmartPriority($ticket, $alert);

            // Get ML prediction for ticket availability trend
            $prediction = $this->mlPredictor->predictAvailabilityTrend($ticket);

            // Create enhanced alert data
            $alertData = $this->buildEnhancedAlertData($ticket, $alert, $priority, $prediction);

            // Determine notification channels based on priority and user preferences
            $channels = $this->getOptimalNotificationChannels($alert->user, $priority);

            // Send notifications through selected channels
            $this->sendMultiChannelNotification($alert->user, $alertData, $channels);

            // Update alert statistics
            $this->updateAlertStatistics($alert, $priority, $channels);

            // Schedule escalation if needed
            if ($priority >= AlertPriority::HIGH) {
                $this->escalationService->scheduleEscalation($alert, $alertData);
            }

            Log::info('Smart alert processed successfully', [
                'alert_id'   => $alert->id,
                'ticket_id'  => $ticket->id,
                'priority'   => $priority,
                'channels'   => array_keys($channels),
                'prediction' => $prediction,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process smart alert', [
                'alert_id'  => $alert->id,
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Trigger alert for a price condition
     */
    /**
     * TriggerDynamicAlert
     */
    protected function triggerDynamicAlert(PriceAlertThreshold $threshold, float $currentPrice): void
    {
        $alertData = [
            'ticket'        => $threshold->ticket->toArray(),
            'threshold'     => $threshold->toArray(),
            'current_price' => $currentPrice,
            'actions'       => [
                'view_ticket'   => route('tickets.scraping.show', $threshold->ticket_id),
                'manage_alerts' => route('alerts.manage'),
            ],
            'metadata' => [
                'triggered_at' => now()->toISOString(),
            ],
        ];

        $channels = $this->getOptimalNotificationChannels($threshold->user, AlertPriority::HIGH);
        $this->sendMultiChannelNotification($threshold->user, $alertData, $channels);
        $threshold->trigger();
    }

    /**
     * Calculate smart priority based on multiple factors
     */
    /**
     * CalculateSmartPriority
     */
    protected function calculateSmartPriority(ScrapedTicket $ticket, TicketAlert $alert): int
    {
        $priority = AlertPriority::NORMAL;
        $factors = [];

        // Price-based priority
        if ($alert->max_price && $ticket->price <= $alert->max_price) {
            $priceDifference = $alert->max_price - $ticket->price;
            $priceRatio = $priceDifference / $alert->max_price;

            if ($priceRatio > 0.3) {
                $priority = AlertPriority::HIGH;
                $factors[] = 'significant_price_drop';
            } elseif ($priceRatio > 0.15) {
                $priority = max($priority, AlertPriority::MEDIUM);
                $factors[] = 'moderate_price_drop';
            }
        }

        // Quantity-based priority
        if ($alert->min_quantity && $ticket->quantity >= $alert->min_quantity) {
            if ($ticket->quantity >= $alert->min_quantity * 2) {
                $priority = max($priority, AlertPriority::MEDIUM);
                $factors[] = 'abundant_quantity';
            } else {
                $factors[] = 'adequate_quantity';
            }
        }

        // Time-based urgency (event date proximity)
        if ($ticket->event_date) {
            $daysUntilEvent = Carbon::parse($ticket->event_date)->diffInDays(now());

            if ($daysUntilEvent <= 7) {
                $priority = max($priority, AlertPriority::HIGH);
                $factors[] = 'event_imminent';
            } elseif ($daysUntilEvent <= 30) {
                $priority = max($priority, AlertPriority::MEDIUM);
                $factors[] = 'event_approaching';
            }
        }

        // Scarcity-based priority (low availability across platforms)
        $totalAvailability = $this->getTotalTicketAvailability($ticket->event_name, $ticket->event_date);
        if ($totalAvailability <= 10) {
            $priority = AlertPriority::CRITICAL;
            $factors[] = 'extreme_scarcity';
        } elseif ($totalAvailability <= 50) {
            $priority = max($priority, AlertPriority::HIGH);
            $factors[] = 'limited_availability';
        }

        // User preference-based priority
        $userPrefs = $this->getUserEventPreferences($alert->user_id, $ticket);
        if ($userPrefs['is_favorite_team'] ?? FALSE) {
            $priority = max($priority, AlertPriority::HIGH);
            $factors[] = 'favorite_team';
        }

        if ($userPrefs['is_preferred_venue'] ?? FALSE) {
            $priority = max($priority, AlertPriority::MEDIUM);
            $factors[] = 'preferred_venue';
        }

        // Historical success rate
        $userSuccessRate = $this->getUserAlertSuccessRate($alert->user_id);
        if ($userSuccessRate < 0.3) {
            $priority = max(1, $priority - 1);
            $factors[] = 'low_user_engagement';
        }

        // Platform reliability factor
        $platformReliability = $this->getPlatformReliability($ticket->platform);
        if ($platformReliability < 0.8) {
            $priority = max($priority, AlertPriority::MEDIUM);
            $factors[] = 'unreliable_platform';
        }

        // Store priority factors for analysis
        Cache::put("alert_factors:{$alert->id}", $factors, 3600);

        return $priority;
    }

    /**
     * Build enhanced alert data with predictions and context
     */
    /**
     * BuildEnhancedAlertData
     */
    protected function buildEnhancedAlertData(ScrapedTicket $ticket, TicketAlert $alert, int $priority, array $prediction): array
    {
        return [
            'ticket'         => $ticket->toArray(),
            'alert'          => $alert->toArray(),
            'priority'       => $priority,
            'priority_label' => AlertPriority::getLabel($priority),
            'prediction'     => $prediction,
            'context'        => [
                'time_until_event'   => Carbon::parse($ticket->event_date)->diffForHumans(),
                'price_comparison'   => $this->getPriceComparison($ticket),
                'availability_trend' => $this->getAvailabilityTrend($ticket),
                'similar_events'     => $this->getSimilarEventsData($ticket),
                'recommendation'     => $this->generateRecommendation($ticket, $alert, $prediction),
            ],
            'actions' => [
                'view_ticket'  => route('tickets.scraping.show', $ticket->id),
                'purchase_now' => route('tickets.scraping.purchase', $ticket->id),
                'set_reminder' => route('alerts.create'),
                'snooze_alert' => route('alerts.snooze', $alert->id),
            ],
            'metadata' => [
                'alert_id'     => $alert->id,
                'ticket_id'    => $ticket->id,
                'generated_at' => now()->toISOString(),
                'expires_at'   => now()->addHours(2)->toISOString(),
            ],
        ];
    }

    /**
     * Get optimal notification channels based on priority and user preferences
     */
    /**
     * Get  optimal notification channels
     */
    protected function getOptimalNotificationChannels(User $user, int $priority): array
    {
        $channels = ['database']; // Always include database channel

        // Get user notification preferences
        $preferences = UserPreference::where('user_id', $user->id)
            ->where('key', 'notification_channels')
            ->first();

        $userChannels = $preferences ? json_decode((string) $preferences->value, TRUE) : [];

        // Add channels based on priority level
        switch ($priority) {
            case AlertPriority::CRITICAL:
                $channels = array_merge($channels, [
                    'mail', 'sms', 'push',
                    $userChannels['critical'] ?? 'slack',
                ]);

                break;
            case AlertPriority::HIGH:
                $channels = array_merge($channels, [
                    'mail', 'push',
                    $userChannels['high'] ?? 'discord',
                ]);

                break;
            case AlertPriority::MEDIUM:
                $channels = array_merge($channels, [
                    'push',
                    $userChannels['medium'] ?? 'telegram',
                ]);

                break;
            default:
                $channels[] = $userChannels['normal'] ?? 'push';

                break;
        }

        // Remove disabled channels
        $disabledChannels = $userChannels['disabled'] ?? [];
        $channels = array_diff($channels, $disabledChannels);

        return array_unique($channels);
    }

    /**
     * Send notification through multiple channels
     */
    /**
     * SendMultiChannelNotification
     */
    protected function sendMultiChannelNotification(User $user, array $alertData, array $channels): void
    {
        $notification = new SmartTicketAlert($alertData);

        // Use Laravel's notification system for standard channels
        $standardChannels = array_intersect($channels, ['mail', 'database', 'sms', 'push']);
        if ($standardChannels !== []) {
            $user->notify($notification);
        }

        // Use custom channels for external integrations
        $customChannels = array_intersect($channels, ['slack', 'discord', 'telegram', 'webhook']);
        foreach ($customChannels as $channel) {
            if (isset($this->customChannels[$channel])) {
                try {
                    $this->customChannels[$channel]->send($user, $alertData);
                } catch (Exception $e) {
                    Log::warning("Failed to send notification via {$channel}", [
                        'user_id' => $user->id,
                        'channel' => $channel,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Get total ticket availability across all platforms
     */
    /**
     * Get  total ticket availability
     */
    protected function getTotalTicketAvailability(string $eventName, ?string $eventDate): int
    {
        return ScrapedTicket::where('event_name', 'LIKE', "%{$eventName}%")
            ->when($eventDate, fn ($query) => $query->whereDate('event_date', $eventDate))
            ->where('is_available', TRUE)
            ->sum('quantity');
    }

    /**
     * Get user's event preferences
     */
    /**
     * Get  user event preferences
     */
    protected function getUserEventPreferences(int $userId, ScrapedTicket $ticket): array
    {
        $preferences = Cache::remember("user_preferences:{$userId}", 3600, fn () => UserPreference::where('user_id', $userId)
            ->whereIn('key', ['favorite_teams', 'preferred_venues', 'event_types'])
            ->pluck('value', 'key')
            ->map(fn ($value): mixed => json_decode((string) $value, TRUE))
            ->toArray());

        $favoriteTeams = $preferences['favorite_teams'] ?? [];
        $preferredVenues = $preferences['preferred_venues'] ?? [];

        return [
            'is_favorite_team'    => $this->isEventForFavoriteTeam($ticket, $favoriteTeams),
            'is_preferred_venue'  => in_array($ticket->venue, $preferredVenues, TRUE),
            'event_type_priority' => $this->getEventTypePriority($ticket, $preferences['event_types'] ?? []),
        ];
    }

    /**
     * Check if event is for user's favorite team
     */
    /**
     * Check if  event for favorite team
     */
    protected function isEventForFavoriteTeam(ScrapedTicket $ticket, array $favoriteTeams): bool
    {
        foreach ($favoriteTeams as $team) {
            if (stripos($ticket->event_name, (string) $team) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get user's alert success rate
     */
    /**
     * Get  user alert success rate
     */
    protected function getUserAlertSuccessRate(int $userId): float
    {
        $totalAlerts = TicketAlert::where('user_id', $userId)->count();
        $successfulAlerts = TicketAlert::where('user_id', $userId)
            ->where('last_triggered_at', '>=', now()->subDays(30))
            ->whereHas('user.purchaseAttempts', function ($query): void {
                $query->where('status', 'completed')
                    ->where('created_at', '>=', now()->subDays(30));
            })
            ->count();

        return $totalAlerts > 0 ? $successfulAlerts / $totalAlerts : 0.5;
    }

    /**
     * Get platform reliability score
     */
    /**
     * Get  platform reliability
     */
    protected function getPlatformReliability(string $platform): float
    {
        return Cache::remember("platform_reliability:{$platform}", 1800, function (): int|float {
            // Calculate based on successful scraping attempts, response times, error rates
            $totalAttempts = 100; // Mock data - would come from actual metrics
            $successfulAttempts = random_int(75, 95);

            return $successfulAttempts / $totalAttempts;
        });
    }

    /**
     * Generate smart recommendation based on data analysis
     */
    /**
     * GenerateRecommendation
     */
    protected function generateRecommendation(ScrapedTicket $ticket, TicketAlert $alert, array $prediction): string
    {
        $recommendations = [];

        // Price-based recommendations
        if ($prediction['price_trend'] === 'decreasing') {
            $recommendations[] = 'Price is trending down. Consider waiting for better deals.';
        } elseif ($prediction['price_trend'] === 'increasing') {
            $recommendations[] = 'Price is rising. Consider purchasing soon.';
        }

        // Availability-based recommendations
        if ($prediction['availability_trend'] === 'decreasing') {
            $recommendations[] = 'Availability is decreasing rapidly. Act quickly!';
        }

        // Time-based recommendations
        $daysUntilEvent = Carbon::parse($ticket->event_date)->diffInDays(now());
        if ($daysUntilEvent <= 7) {
            $recommendations[] = 'Event is within a week. Last chance to secure tickets!';
        }

        return $recommendations === []
            ? 'Good opportunity based on your preferences.'
            : implode(' ', $recommendations);
    }

    /**
     * Get price comparison data
     */
    /**
     * Get  price comparison
     */
    protected function getPriceComparison(ScrapedTicket $ticket): array
    {
        $similarTickets = ScrapedTicket::where('event_name', 'LIKE', "%{$ticket->event_name}%")
            ->where('id', '!=', $ticket->id)
            ->where('is_available', TRUE)
            ->get();

        if ($similarTickets->isEmpty()) {
            return ['status' => 'no_comparison_available'];
        }

        $prices = $similarTickets->pluck('price')->toArray();
        $avgPrice = array_sum($prices) / count($prices);
        $minPrice = min($prices);
        $maxPrice = max($prices);

        return [
            'current_price' => $ticket->price,
            'average_price' => round($avgPrice, 2),
            'min_price'     => $minPrice,
            'max_price'     => $maxPrice,
            'comparison'    => $ticket->price < $avgPrice ? 'below_average' : 'above_average',
            'savings'       => $ticket->price < $avgPrice ? round($avgPrice - $ticket->price, 2) : 0,
        ];
    }

    /**
     * Get availability trend data
     */
    /**
     * Get  availability trend
     */
    protected function getAvailabilityTrend(ScrapedTicket $ticket): array
    {
        // Mock trend data - in real implementation, this would analyze historical data
        return [
            'trend'             => 'decreasing',
            'change_percentage' => -15,
            'total_available'   => random_int(50, 200),
            'platforms_count'   => random_int(3, 8),
        ];
    }

    /**
     * Update alert statistics
     */
    /**
     * UpdateAlertStatistics
     */
    protected function updateAlertStatistics(TicketAlert $alert, int $priority, array $channels): void
    {
        $alert->increment('times_triggered');
        $alert->update([
            'last_triggered_at' => now(),
            'last_priority'     => $priority,
            'last_channels'     => json_encode($channels),
        ]);
    }

    /**
     * Get similar events data for context
     */
    /**
     * Get  similar events data
     */
    protected function getSimilarEventsData(ScrapedTicket $ticket): array
    {
        // Extract team names or key terms from event name
        $eventTerms = $this->extractEventTerms($ticket->event_name);

        $similarEvents = ScrapedTicket::where(function ($query) use ($eventTerms): void {
            foreach ($eventTerms as $term) {
                $query->orWhere('event_name', 'LIKE', "%{$term}%");
            }
        })
            ->where('id', '!=', $ticket->id)
            ->where('event_date', '>=', now())
            ->limit(5)
            ->get(['event_name', 'price', 'platform', 'event_date']);

        return $similarEvents->map(fn ($event): array => [
            'event_name' => $event->event_name,
            'price'      => $event->price,
            'platform'   => $event->platform,
            'event_date' => $event->event_date,
        ])->toArray();
    }

    /**
     * Extract key terms from event name for similarity matching
     */
    /**
     * ExtractEventTerms
     */
    protected function extractEventTerms(string $eventName): array
    {
        // Remove common words and extract meaningful terms
        $commonWords = ['vs', 'v', 'at', 'the', 'and', 'or', 'in', 'on', 'game', 'match', 'event'];
        $terms = explode(' ', strtolower($eventName));

        return array_filter($terms, fn ($term): bool => strlen((string) $term) > 2 && ! in_array($term, $commonWords, TRUE));
    }

    /**
     * Get event type priority based on user preferences
     */
    /**
     * Get  event type priority
     */
    protected function getEventTypePriority(ScrapedTicket $ticket, array $eventTypes): int
    {
        foreach ($eventTypes as $type => $priority) {
            if (stripos($ticket->event_name, (string) $type) !== FALSE) {
                return $priority;
            }
        }

        return 1; // Default priority
    }
}

/**
 * Alert Priority Constants
 */
class AlertPriority
{
    public const CRITICAL = 5;

    public const HIGH = 4;

    public const MEDIUM = 3;

    public const NORMAL = 2;

    public const LOW = 1;

    /**
     * Get  label
     */
    public static function getLabel(int $priority): string
    {
        $labels = [
            self::CRITICAL => 'Critical',
            self::HIGH     => 'High',
            self::MEDIUM   => 'Medium',
            self::NORMAL   => 'Normal',
            self::LOW      => 'Low',
        ];

        return $labels[$priority] ?? 'Unknown';
    }
}
