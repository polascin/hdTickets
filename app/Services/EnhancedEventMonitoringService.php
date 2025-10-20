<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\EventMonitor;
use App\Models\User;
use App\Services\NotificationChannels\PushNotificationService;
use App\Services\NotificationChannels\SmsNotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function array_slice;
use function count;

/**
 * Enhanced Event Monitoring Service
 *
 * Inspired by TicketScoutie's platform features:
 * - Sub-second monitoring updates
 * - Multi-platform ticket tracking
 * - Smart alert system
 * - Real-time dashboard data
 */
class EnhancedEventMonitoringService
{
    private array $platforms = [
        'ticketmaster',
        'stubhub',
        'seatgeek',
        'vivid_seats',
        'tickpick',
        'gametime',
    ];

    public function __construct(
        private PushNotificationService $pushService,
        private SmsNotificationService $smsService,
    ) {
    }

    /**
     * Start monitoring all active events with sub-second updates
     */
    public function startSubSecondMonitoring(): void
    {
        Log::info('Starting enhanced sub-second monitoring');

        $activeMonitors = EventMonitor::with(['event', 'user'])
            ->where('is_active', TRUE)
            ->where('next_check_at', '<=', now())
            ->orderBy('priority', 'desc')
            ->limit(50) // Process high-priority monitors first
            ->get();

        foreach ($activeMonitors as $monitor) {
            $this->monitorEventWithSubSecondUpdates($monitor);
        }

        $this->updateMonitoringStats();
    }

    /**
     * Monitor event with sub-second update capability
     */
    public function monitorEventWithSubSecondUpdates(EventMonitor $monitor): void
    {
        $startTime = microtime(TRUE);

        try {
            // Parallel platform monitoring for speed
            $platformData = $this->fetchFromAllPlatformsParallel($monitor->event);

            if (empty($platformData)) {
                $this->handleMonitoringError($monitor, 'No data from any platform');

                return;
            }

            $changes = $this->detectInstantChanges($monitor, $platformData);

            if (! empty($changes)) {
                $this->processInstantAlerts($monitor, $changes, $platformData);
            }

            $this->updateMonitorWithSubSecondData($monitor, $platformData, $startTime);
        } catch (Exception $e) {
            $this->handleMonitoringError($monitor, $e->getMessage());
        }
    }

    /**
     * Get enhanced real-time dashboard data
     */
    public function getEnhancedRealtimeData(User $user): array
    {
        $userMonitors = EventMonitor::where('user_id', $user->id)
            ->where('is_active', TRUE)
            ->with('event')
            ->orderBy('priority', 'desc')
            ->get();

        $realtimeData = [];
        $totalResponseTime = 0;
        $platformStats = [];

        foreach ($userMonitors as $monitor) {
            $cacheKey = "instant_event_{$monitor->event->id}";
            $eventData = Cache::get($cacheKey);

            if ($eventData) {
                $realtimeData[] = $eventData;
                $totalResponseTime += $monitor->last_response_time ?? 0;

                // Count platform statistics
                foreach ($eventData['platforms'] as $platform => $data) {
                    $platformStats[$platform] = ($platformStats[$platform] ?? 0) + count($data);
                }
            }
        }

        return [
            'events'   => $realtimeData,
            'monitors' => [
                'total'    => $userMonitors->count(),
                'active'   => $userMonitors->where('status', 'active')->count(),
                'critical' => $userMonitors->where('priority', 'critical')->count(),
                'high'     => $userMonitors->where('priority', 'high')->count(),
            ],
            'performance' => [
                'average_response_time' => $userMonitors->count() > 0 ? round($totalResponseTime / $userMonitors->count(), 2) : 0,
                'uptime_percentage'     => $this->calculateUptimePercentage($user),
                'alerts_sent_today'     => $this->getAlertsCountToday($user),
                'last_updated'          => now()->toISOString(),
            ],
            'platform_stats' => $platformStats,
            'global_feed'    => Cache::get('instant_global_feed', []),
        ];
    }

    /**
     * Fetch data from all platforms in parallel for speed
     */
    private function fetchFromAllPlatformsParallel(Event $event): array
    {
        $responses = [];
        $promises = [];

        // Create parallel HTTP requests
        foreach ($this->platforms as $platform) {
            if ($this->isPlatformEnabled($platform)) {
                $promises[$platform] = $this->createPlatformRequest($platform, $event);
            }
        }

        // Execute all requests in parallel
        $responses = Http::pool(fn () => $promises);

        $platformData = [];
        foreach ($responses as $platform => $response) {
            if ($response->successful()) {
                $data = $this->parseEventData($platform, $response->json());
                if (! empty($data)) {
                    $platformData[$platform] = $data;
                }
            }
        }

        return $platformData;
    }

    /**
     * Create HTTP request for specific platform
     */
    private function createPlatformRequest(string $platform, Event $event): callable
    {
        return function () use ($platform, $event) {
            $config = config("platforms.{$platform}");

            return Http::timeout(2) // Fast timeout for sub-second responses
                ->withHeaders($config['headers'] ?? [])
                ->get($config['base_url'] . $this->buildEventUrl($platform, $event));
        };
    }

    /**
     * Check if platform is enabled and configured
     */
    private function isPlatformEnabled(string $platform): bool
    {
        $config = config("platforms.{$platform}");

        return $config && ($config['enabled'] ?? FALSE);
    }

    /**
     * Build platform-specific event URL
     */
    private function buildEventUrl(string $platform, Event $event): string
    {
        return match ($platform) {
            'ticketmaster' => '/discovery/v2/events.json?keyword=' . urlencode($event->name) . '&city=' . urlencode($event->city ?? ''),
            'stubhub'      => '/catalog/events/v3?name=' . urlencode($event->name),
            'seatgeek'     => '/2/events?q=' . urlencode($event->name) . '&venue.city=' . urlencode($event->city ?? ''),
            'vivid_seats'  => '/listings?q=' . urlencode($event->name),
            'tickpick'     => '/api/events/search?q=' . urlencode($event->name),
            'gametime'     => '/api/search?query=' . urlencode($event->name),
            default        => '/search?q=' . urlencode($event->name),
        };
    }

    /**
     * Parse platform-specific response data with enhanced fields
     */
    private function parseEventData(string $platform, array $data): array
    {
        return match ($platform) {
            'ticketmaster' => $this->parseTicketmasterDataEnhanced($data),
            'stubhub'      => $this->parseStubHubDataEnhanced($data),
            'seatgeek'     => $this->parseSeatGeekDataEnhanced($data),
            'vivid_seats'  => $this->parseVividSeatsDataEnhanced($data),
            'tickpick'     => $this->parseTickPickDataEnhanced($data),
            'gametime'     => $this->parseGametimeDataEnhanced($data),
            default        => $this->parseGenericDataEnhanced($data),
        };
    }

    /**
     * Enhanced Ticketmaster parser with more detailed data
     */
    private function parseTicketmasterDataEnhanced(array $data): array
    {
        $events = $data['_embedded']['events'] ?? [];
        $parsed = [];

        foreach ($events as $event) {
            $parsed[] = [
                'platform'      => 'ticketmaster',
                'external_id'   => $event['id'],
                'name'          => $event['name'],
                'date'          => $event['dates']['start']['dateTime'] ?? NULL,
                'venue'         => $event['_embedded']['venues'][0]['name'] ?? 'Unknown',
                'city'          => $event['_embedded']['venues'][0]['city']['name'] ?? 'Unknown',
                'price_min'     => $event['priceRanges'][0]['min'] ?? NULL,
                'price_max'     => $event['priceRanges'][0]['max'] ?? NULL,
                'currency'      => $event['priceRanges'][0]['currency'] ?? 'USD',
                'available'     => $event['dates']['status']['code'] === 'onsale',
                'total_tickets' => $event['ticketLimit']['info'] ?? 'Unknown',
                'presale_info'  => $this->extractPresaleInfo($event),
                'url'           => $event['url'],
                'last_updated'  => now(),
                'response_time' => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced StubHub parser
     */
    private function parseStubHubDataEnhanced(array $data): array
    {
        $events = $data['events'] ?? [];
        $parsed = [];

        foreach ($events as $event) {
            $parsed[] = [
                'platform'      => 'stubhub',
                'external_id'   => $event['id'],
                'name'          => $event['name'],
                'date'          => $event['eventDateUTC'] ?? NULL,
                'venue'         => $event['venue']['name'] ?? 'Unknown',
                'city'          => $event['venue']['city'] ?? 'Unknown',
                'price_min'     => $event['ticketInfo']['minPrice'] ?? NULL,
                'price_max'     => $event['ticketInfo']['maxPrice'] ?? NULL,
                'currency'      => $event['ticketInfo']['currencyCode'] ?? 'USD',
                'available'     => ($event['ticketInfo']['totalTickets'] ?? 0) > 0,
                'total_tickets' => $event['ticketInfo']['totalTickets'] ?? 0,
                'listing_count' => $event['ticketInfo']['totalListings'] ?? 0,
                'url'           => "https://www.stubhub.com/event/{$event['id']}",
                'last_updated'  => now(),
                'response_time' => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced SeatGeek parser
     */
    private function parseSeatGeekDataEnhanced(array $data): array
    {
        $events = $data['events'] ?? [];
        $parsed = [];

        foreach ($events as $event) {
            $parsed[] = [
                'platform'         => 'seatgeek',
                'external_id'      => $event['id'],
                'name'             => $event['title'],
                'date'             => $event['datetime_utc'] ?? NULL,
                'venue'            => $event['venue']['name'] ?? 'Unknown',
                'city'             => $event['venue']['city'] ?? 'Unknown',
                'price_min'        => $event['stats']['lowest_price'] ?? NULL,
                'price_max'        => $event['stats']['highest_price'] ?? NULL,
                'average_price'    => $event['stats']['average_price'] ?? NULL,
                'currency'         => 'USD',
                'available'        => ($event['stats']['listing_count'] ?? 0) > 0,
                'total_tickets'    => $event['stats']['listing_count'] ?? 0,
                'popularity_score' => $event['popularity'] ?? 0,
                'url'              => $event['url'],
                'last_updated'     => now(),
                'response_time'    => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced Vivid Seats parser
     */
    private function parseVividSeatsDataEnhanced(array $data): array
    {
        $listings = $data['listings'] ?? [];
        $parsed = [];

        foreach ($listings as $listing) {
            $parsed[] = [
                'platform'      => 'vivid_seats',
                'external_id'   => $listing['id'],
                'name'          => $listing['event']['name'] ?? 'Unknown Event',
                'date'          => $listing['event']['date'] ?? NULL,
                'venue'         => $listing['event']['venue'] ?? 'Unknown',
                'city'          => $listing['event']['city'] ?? 'Unknown',
                'price_min'     => $listing['price'] ?? NULL,
                'price_max'     => $listing['price'] ?? NULL,
                'currency'      => $listing['currency'] ?? 'USD',
                'available'     => TRUE,
                'section'       => $listing['section'] ?? 'General',
                'row'           => $listing['row'] ?? 'Unknown',
                'quantity'      => $listing['quantity'] ?? 1,
                'url'           => $listing['url'] ?? '#',
                'last_updated'  => now(),
                'response_time' => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced TickPick parser
     */
    private function parseTickPickDataEnhanced(array $data): array
    {
        $events = $data['events'] ?? [];
        $parsed = [];

        foreach ($events as $event) {
            $parsed[] = [
                'platform'      => 'tickpick',
                'external_id'   => $event['id'],
                'name'          => $event['name'],
                'date'          => $event['datetime'] ?? NULL,
                'venue'         => $event['venue']['name'] ?? 'Unknown',
                'city'          => $event['venue']['city'] ?? 'Unknown',
                'price_min'     => $event['min_price'] ?? NULL,
                'price_max'     => $event['max_price'] ?? NULL,
                'currency'      => 'USD',
                'available'     => ($event['ticket_count'] ?? 0) > 0,
                'total_tickets' => $event['ticket_count'] ?? 0,
                'no_fees'       => TRUE, // TickPick's selling point
                'url'           => $event['url'] ?? '#',
                'last_updated'  => now(),
                'response_time' => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced Gametime parser
     */
    private function parseGametimeDataEnhanced(array $data): array
    {
        $events = $data['events'] ?? [];
        $parsed = [];

        foreach ($events as $event) {
            $parsed[] = [
                'platform'          => 'gametime',
                'external_id'       => $event['id'],
                'name'              => $event['title'],
                'date'              => $event['start_time'] ?? NULL,
                'venue'             => $event['venue']['name'] ?? 'Unknown',
                'city'              => $event['venue']['location'] ?? 'Unknown',
                'price_min'         => $event['lowest_price'] ?? NULL,
                'price_max'         => $event['highest_price'] ?? NULL,
                'currency'          => 'USD',
                'available'         => ($event['available_tickets'] ?? 0) > 0,
                'total_tickets'     => $event['available_tickets'] ?? 0,
                'last_minute_deals' => $event['deals'] ?? FALSE,
                'url'               => $event['deep_link'] ?? '#',
                'last_updated'      => now(),
                'response_time'     => microtime(TRUE),
            ];
        }

        return $parsed;
    }

    /**
     * Enhanced generic parser
     */
    private function parseGenericDataEnhanced(array $data): array
    {
        return [
            [
                'platform'      => 'unknown',
                'external_id'   => $data['id'] ?? uniqid(),
                'name'          => $data['name'] ?? 'Unknown Event',
                'date'          => $data['date'] ?? NULL,
                'venue'         => $data['venue'] ?? 'Unknown',
                'city'          => $data['city'] ?? 'Unknown',
                'price_min'     => $data['price_min'] ?? NULL,
                'price_max'     => $data['price_max'] ?? NULL,
                'currency'      => $data['currency'] ?? 'USD',
                'available'     => $data['available'] ?? FALSE,
                'total_tickets' => $data['total_tickets'] ?? 0,
                'url'           => $data['url'] ?? '#',
                'last_updated'  => now(),
                'response_time' => microtime(TRUE),
            ],
        ];
    }

    /**
     * Extract presale information
     */
    private function extractPresaleInfo(array $event): array
    {
        $presales = $event['sales']['presales'] ?? [];
        $info = [];

        foreach ($presales as $presale) {
            $info[] = [
                'name'  => $presale['name'] ?? 'Presale',
                'start' => $presale['startDateTime'] ?? NULL,
                'end'   => $presale['endDateTime'] ?? NULL,
                'url'   => $presale['url'] ?? NULL,
            ];
        }

        return $info;
    }

    /**
     * Detect instant changes with sub-second precision
     */
    private function detectInstantChanges(EventMonitor $monitor, array $newData): array
    {
        $lastData = $monitor->last_data ? json_decode($monitor->last_data, TRUE) : [];
        $changes = [];
        $threshold = $monitor->price_threshold ?? 0;

        foreach ($newData as $platform => $platformData) {
            $lastPlatformData = $lastData[$platform] ?? [];

            foreach ($platformData as $event) {
                $lastEvent = collect($lastPlatformData)->firstWhere('external_id', $event['external_id']);

                // New listing detected
                if (! $lastEvent) {
                    $changes[] = [
                        'type'     => 'new_listing',
                        'platform' => $platform,
                        'event'    => $event,
                        'urgency'  => 'high',
                        'message'  => "🚨 NEW: {$event['name']} tickets found on " . ucfirst($platform) . '!',
                    ];

                    continue;
                }

                // Availability change
                if ($event['available'] && ! $lastEvent['available']) {
                    $changes[] = [
                        'type'     => 'availability_restored',
                        'platform' => $platform,
                        'event'    => $event,
                        'urgency'  => 'high',
                        'message'  => "✅ BACK IN STOCK: {$event['name']} on " . ucfirst($platform) . '!',
                    ];
                }

                // Significant price drop
                if ($this->hasSignificantPriceChange($event, $lastEvent, $threshold)) {
                    $changes[] = [
                        'type'      => 'price_drop',
                        'platform'  => $platform,
                        'event'     => $event,
                        'old_event' => $lastEvent,
                        'urgency'   => 'medium',
                        'message'   => "💰 PRICE DROP: {$event['name']} now {$event['price_min']} (was {$lastEvent['price_min']})",
                    ];
                }

                // Low inventory alert
                if ($this->isLowInventory($event)) {
                    $changes[] = [
                        'type'     => 'low_inventory',
                        'platform' => $platform,
                        'event'    => $event,
                        'urgency'  => 'medium',
                        'message'  => "⚠️ LOW STOCK: Only {$event['total_tickets']} tickets left for {$event['name']}",
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Check for significant price changes
     */
    private function hasSignificantPriceChange(array $newEvent, array $oldEvent, float $threshold): bool
    {
        $newPrice = $newEvent['price_min'] ?? 0;
        $oldPrice = $oldEvent['price_min'] ?? 0;

        if ($newPrice === 0 || $oldPrice === 0) {
            return FALSE;
        }

        $priceDrop = $oldPrice - $newPrice;
        $percentageDrop = ($priceDrop / $oldPrice) * 100;

        return $priceDrop > $threshold || $percentageDrop >= 10; // 10% drop or threshold amount
    }

    /**
     * Check if inventory is low
     */
    private function isLowInventory(array $event): bool
    {
        $totalTickets = $event['total_tickets'] ?? 0;

        return $totalTickets > 0 && $totalTickets <= 5; // 5 or fewer tickets
    }

    /**
     * Process instant alerts with urgency-based delivery
     */
    private function processInstantAlerts(EventMonitor $monitor, array $changes, array $eventData): void
    {
        foreach ($changes as $change) {
            $this->sendUrgentNotification($monitor, $change);
            $this->logInstantChange($monitor, $change);
        }

        // Update real-time cache immediately
        $this->updateInstantCache($monitor->event, $eventData);

        // Broadcast to WebSocket for real-time dashboard updates
        $this->broadcastInstantUpdate($monitor->event, $changes);
    }

    /**
     * Send urgent notification based on change urgency
     */
    private function sendUrgentNotification(EventMonitor $monitor, array $change): void
    {
        $user = $monitor->user;
        $channels = $this->getNotificationChannels($monitor, $change['urgency']);
        $message = $change['message'];

        foreach ($channels as $channel) {
            try {
                match ($channel) {
                    'push' => $this->pushService->send($user, [
                        'title'   => 'HD Tickets Alert!',
                        'body'    => $message,
                        'urgency' => $change['urgency'],
                        'data'    => $change,
                    ]),
                    'sms'   => $this->smsService->send($user->phone, $message),
                    'email' => $this->sendInstantEmail($user, $message, $change),
                    default => NULL,
                };
            } catch (Exception $e) {
                Log::error("Failed to send urgent {$channel} notification", [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get notification channels based on urgency
     */
    private function getNotificationChannels(EventMonitor $monitor, string $urgency): array
    {
        $channels = $monitor->notification_channels ?? ['email'];

        // High urgency gets all channels
        if ($urgency === 'high') {
            return array_unique(array_merge($channels, ['push', 'sms']));
        }

        return $channels;
    }

    /**
     * Send instant email notification
     */
    private function sendInstantEmail(User $user, string $message, array $change): void
    {
        Mail::send('emails.instant-alert', compact('message', 'change'), function ($mail) use ($user, $change): void {
            $mail->to($user->email)
                ->subject("🚨 URGENT: HD Tickets Alert - {$change['type']}")
                ->priority(1); // High priority
        });
    }

    /**
     * Update instant cache for real-time dashboard
     */
    private function updateInstantCache(Event $event, array $eventData): void
    {
        $cacheKey = "instant_event_{$event->id}";
        $cacheData = [
            'event_id'              => $event->id,
            'last_updated'          => now()->toISOString(),
            'microsecond_timestamp' => microtime(TRUE),
            'platforms'             => $eventData,
            'summary'               => $this->generateEventSummary($eventData),
            'alerts'                => Cache::get("recent_alerts_{$event->id}", []),
        ];

        Cache::put($cacheKey, $cacheData, 60); // Cache for 1 minute

        // Update global instant feed
        $this->updateGlobalInstantFeed($cacheData);
    }

    /**
     * Generate event summary from all platforms
     */
    private function generateEventSummary(array $eventData): array
    {
        $allEvents = [];
        foreach ($eventData as $platformData) {
            $allEvents = array_merge($allEvents, $platformData);
        }

        if (empty($allEvents)) {
            return [];
        }

        $availableEvents = array_filter($allEvents, fn ($e) => $e['available']);
        $prices = array_filter(array_column($availableEvents, 'price_min'), fn ($p) => $p > 0);

        return [
            'total_listings'     => count($allEvents),
            'available_listings' => count($availableEvents),
            'platforms_count'    => count($eventData),
            'lowest_price'       => ! empty($prices) ? min($prices) : NULL,
            'highest_price'      => ! empty($prices) ? max($prices) : NULL,
            'average_price'      => ! empty($prices) ? round(array_sum($prices) / count($prices), 2) : NULL,
            'total_tickets'      => array_sum(array_column($availableEvents, 'total_tickets')),
        ];
    }

    /**
     * Broadcast instant update via WebSocket
     */
    private function broadcastInstantUpdate(Event $event, array $changes): void
    {
        // This would integrate with Laravel Echo/Pusher for real-time updates
        broadcast(new \App\Events\InstantTicketUpdate($event, $changes))->toOthers();
    }

    /**
     * Update monitor with sub-second data
     */
    private function updateMonitorWithSubSecondData(EventMonitor $monitor, array $eventData, float $startTime): void
    {
        $responseTime = (microtime(TRUE) - $startTime) * 1000; // Convert to milliseconds

        $monitor->update([
            'last_checked_at'    => now(),
            'last_data'          => json_encode($eventData),
            'check_count'        => $monitor->check_count + 1,
            'last_response_time' => $responseTime,
            'next_check_at'      => $this->calculateSubSecondNextCheck($monitor),
            'status'             => 'active',
        ]);

        // Log performance metrics
        $this->logPerformanceMetrics($monitor, $responseTime, count($eventData));
    }

    /**
     * Calculate next check time for sub-second monitoring
     */
    private function calculateSubSecondNextCheck(EventMonitor $monitor): Carbon
    {
        $interval = match ($monitor->priority) {
            'critical' => 0.5, // 500ms for critical events
            'high'     => 1,       // 1 second
            'medium'   => 5,     // 5 seconds
            'low'      => 30,       // 30 seconds
            default    => 10,      // 10 seconds
        };

        return now()->addSeconds($interval);
    }

    /**
     * Calculate user's monitoring uptime percentage
     */
    private function calculateUptimePercentage(User $user): float
    {
        // This would be calculated from actual uptime logs
        $successfulChecks = EventMonitor::where('user_id', $user->id)
            ->where('last_checked_at', '>=', now()->subDay())
            ->where('status', 'active')
            ->sum('check_count');

        $totalChecks = EventMonitor::where('user_id', $user->id)
            ->where('last_checked_at', '>=', now()->subDay())
            ->sum('check_count');

        return $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 2) : 100.0;
    }

    /**
     * Get alerts sent count for today
     */
    private function getAlertsCountToday(User $user): int
    {
        return Cache::get("alerts_sent_today_{$user->id}", 0);
    }

    /**
     * Update monitoring statistics
     */
    private function updateMonitoringStats(): void
    {
        $stats = [
            'total_monitors'        => EventMonitor::where('is_active', TRUE)->count(),
            'checks_last_minute'    => EventMonitor::where('last_checked_at', '>=', now()->subMinute())->count(),
            'average_response_time' => EventMonitor::whereNotNull('last_response_time')->avg('last_response_time'),
            'platform_health'       => $this->getPlatformHealthStatus(),
            'last_updated'          => now()->toISOString(),
        ];

        Cache::put('enhanced_monitoring_stats', $stats, 300); // Cache for 5 minutes
    }

    /**
     * Get platform health status
     */
    private function getPlatformHealthStatus(): array
    {
        $health = [];

        foreach ($this->platforms as $platform) {
            $recentChecks = Cache::get("platform_checks_{$platform}", []);
            $successRate = $this->calculatePlatformSuccessRate($recentChecks);

            $health[$platform] = [
                'status'        => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
                'success_rate'  => $successRate,
                'last_check'    => Cache::get("platform_last_check_{$platform}"),
                'response_time' => Cache::get("platform_response_time_{$platform}", 0),
            ];
        }

        return $health;
    }

    /**
     * Calculate platform success rate
     */
    private function calculatePlatformSuccessRate(array $checks): float
    {
        if (empty($checks)) {
            return 100.0;
        }

        $successful = array_filter($checks, fn ($check) => $check['success'] ?? FALSE);

        return round((count($successful) / count($checks)) * 100, 2);
    }

    /**
     * Handle monitoring errors with enhanced logging
     */
    private function handleMonitoringError(EventMonitor $monitor, string $error): void
    {
        $monitor->update([
            'last_checked_at' => now(),
            'error_count'     => $monitor->error_count + 1,
            'last_error'      => $error,
            'next_check_at'   => $this->calculateErrorRetryTime($monitor),
            'status'          => $monitor->error_count >= 3 ? 'error' : 'active',
        ]);

        Log::error('Enhanced event monitoring error', [
            'monitor_id'  => $monitor->id,
            'event_id'    => $monitor->event->id,
            'user_id'     => $monitor->user->id,
            'error'       => $error,
            'error_count' => $monitor->error_count,
            'priority'    => $monitor->priority,
        ]);
    }

    /**
     * Calculate error retry time with exponential backoff
     */
    private function calculateErrorRetryTime(EventMonitor $monitor): Carbon
    {
        $backoffSeconds = min(pow(2, $monitor->error_count), 300); // Max 5 minutes

        return now()->addSeconds($backoffSeconds);
    }

    /**
     * Log instant change for analytics
     */
    private function logInstantChange(EventMonitor $monitor, array $change): void
    {
        Log::info('Instant event change detected', [
            'monitor_id'            => $monitor->id,
            'event_id'              => $monitor->event->id,
            'user_id'               => $monitor->user->id,
            'change_type'           => $change['type'],
            'platform'              => $change['platform'],
            'urgency'               => $change['urgency'],
            'timestamp'             => now(),
            'microsecond_timestamp' => microtime(TRUE),
        ]);

        // Increment alerts counter
        $today = now()->format('Y-m-d');
        $key = "alerts_sent_today_{$monitor->user->id}";
        Cache::increment($key, 1);
        Cache::put($key, Cache::get($key, 1), now()->endOfDay());
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(EventMonitor $monitor, float $responseTime, int $platformCount): void
    {
        $metrics = [
            'monitor_id'            => $monitor->id,
            'response_time'         => $responseTime,
            'platforms_checked'     => $platformCount,
            'priority'              => $monitor->priority,
            'timestamp'             => now(),
            'microsecond_timestamp' => microtime(TRUE),
        ];

        // Store in cache for real-time metrics
        $metricsKey = "performance_metrics_{$monitor->id}";
        $existingMetrics = Cache::get($metricsKey, []);
        array_unshift($existingMetrics, $metrics);

        // Keep only last 100 metrics
        $existingMetrics = array_slice($existingMetrics, 0, 100);
        Cache::put($metricsKey, $existingMetrics, 3600); // Cache for 1 hour
    }

    /**
     * Update global instant feed
     */
    private function updateGlobalInstantFeed(array $eventData): void
    {
        $feedKey = 'instant_global_feed';
        $currentFeed = Cache::get($feedKey, []);

        // Add to feed and keep last 50 updates for performance
        array_unshift($currentFeed, $eventData);
        $currentFeed = array_slice($currentFeed, 0, 50);

        Cache::put($feedKey, $currentFeed, 1800); // Cache for 30 minutes
    }
}
