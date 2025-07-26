<?php

namespace App\Services\Scraping;

use App\Services\Scraping\AdvancedAntiDetectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;

class HighDemandTicketScraperService
{
    protected AdvancedAntiDetectionService $antiDetection;
    protected array $priorityEvents = [];
    protected array $queueStrategies = [];
    
    public function __construct(AdvancedAntiDetectionService $antiDetection)
    {
        $this->antiDetection = $antiDetection;
        $this->initializePriorityEvents();
        $this->initializeQueueStrategies();
    }

    /**
     * Initialize high-priority events that require special handling
     */
    protected function initializePriorityEvents(): void
    {
        $this->priorityEvents = [
            'el_clasico' => [
                'platforms' => ['real_madrid', 'barcelona'],
                'keywords' => ['Real Madrid vs Barcelona', 'Barcelona vs Real Madrid', 'El Clásico', 'Clasico'],
                'queue_strategy' => 'aggressive',
                'monitoring_interval' => 30, // seconds
                'pre_sale_monitoring' => true,
                'demand_level' => 'extreme',
            ],
            'champions_league_final' => [
                'platforms' => ['real_madrid', 'barcelona', 'bayern_munich', 'manchester_city', 'psg', 'juventus'],
                'keywords' => ['Champions League Final', 'UCL Final', 'Final Champions'],
                'queue_strategy' => 'aggressive',
                'monitoring_interval' => 15,
                'pre_sale_monitoring' => true,
                'demand_level' => 'extreme',
            ],
            'der_klassiker' => [
                'platforms' => ['bayern_munich', 'borussia_dortmund'],
                'keywords' => ['Bayern München vs Borussia Dortmund', 'Der Klassiker', 'Bayern vs Dortmund'],
                'queue_strategy' => 'aggressive',
                'monitoring_interval' => 60,
                'demand_level' => 'very_high',
            ],
            'manchester_derby' => [
                'platforms' => ['manchester_city', 'manchester_united'],
                'keywords' => ['Manchester City vs Manchester United', 'Manchester Derby', 'City vs United'],
                'queue_strategy' => 'aggressive',
                'monitoring_interval' => 45,
                'demand_level' => 'very_high',
            ],
            'champions_league_knockout' => [
                'platforms' => ['real_madrid', 'barcelona', 'bayern_munich', 'manchester_city', 'psg', 'juventus'],
                'keywords' => ['Champions League', 'Round of 16', 'Quarter Final', 'Semi Final'],
                'queue_strategy' => 'moderate',
                'monitoring_interval' => 120,
                'demand_level' => 'high',
            ]
        ];
    }

    /**
     * Initialize queue management strategies
     */
    protected function initializeQueueStrategies(): void
    {
        $this->queueStrategies = [
            'aggressive' => [
                'concurrent_sessions' => 5,
                'retry_attempts' => 10,
                'retry_delay_base' => 1000, // milliseconds
                'retry_delay_multiplier' => 1.2,
                'session_rotation_frequency' => 3, // requests
                'bypass_queue_attempts' => true,
                'pre_queue_monitoring' => true,
            ],
            'moderate' => [
                'concurrent_sessions' => 3,
                'retry_attempts' => 6,
                'retry_delay_base' => 2000,
                'retry_delay_multiplier' => 1.5,
                'session_rotation_frequency' => 5,
                'bypass_queue_attempts' => false,
                'pre_queue_monitoring' => true,
            ],
            'conservative' => [
                'concurrent_sessions' => 1,
                'retry_attempts' => 3,
                'retry_delay_base' => 3000,
                'retry_delay_multiplier' => 2.0,
                'session_rotation_frequency' => 10,
                'bypass_queue_attempts' => false,
                'pre_queue_monitoring' => false,
            ]
        ];
    }

    /**
     * Scrape high-demand football tickets with queue management
     */
    public function scrapeHighDemandTickets(string $platform, array $criteria): array
    {
        $eventType = $this->identifyEventType($criteria);
        $strategy = $this->selectScrapingStrategy($eventType, $platform);
        
        Log::info("Starting high-demand ticket scraping", [
            'platform' => $platform,
            'event_type' => $eventType,
            'strategy' => $strategy,
            'criteria' => $criteria
        ]);

        // Check if we're already in a queue for this platform
        if ($this->isInQueue($platform)) {
            return $this->handleQueueScraping($platform, $criteria, $strategy);
        }

        // Check for pre-sale monitoring
        if ($this->requiresPreSaleMonitoring($eventType)) {
            return $this->monitorPreSaleAvailability($platform, $criteria, $eventType);
        }

        // Standard high-demand scraping
        return $this->executeHighDemandScraping($platform, $criteria, $strategy);
    }

    /**
     * Identify event type based on criteria
     */
    protected function identifyEventType(array $criteria): string
    {
        $keyword = strtolower($criteria['keyword'] ?? '');
        
        foreach ($this->priorityEvents as $eventType => $config) {
            foreach ($config['keywords'] as $eventKeyword) {
                if (str_contains($keyword, strtolower($eventKeyword))) {
                    return $eventType;
                }
            }
        }
        
        // Check for general high-demand indicators
        if (str_contains($keyword, 'final') || 
            str_contains($keyword, 'derby') || 
            str_contains($keyword, 'champions league')) {
            return 'high_demand_general';
        }
        
        return 'standard';
    }

    /**
     * Select scraping strategy based on event type and platform
     */
    protected function selectScrapingStrategy(string $eventType, string $platform): string
    {
        if (isset($this->priorityEvents[$eventType])) {
            return $this->priorityEvents[$eventType]['queue_strategy'] ?? 'moderate';
        }
        
        // Platform-specific defaults for high-demand events
        $platformStrategies = [
            'real_madrid' => 'aggressive',
            'barcelona' => 'aggressive', 
            'manchester_city' => 'aggressive',
            'bayern_munich' => 'moderate',
            'psg' => 'moderate',
            'juventus' => 'moderate',
        ];
        
        return $platformStrategies[$platform] ?? 'conservative';
    }

    /**
     * Execute high-demand scraping with advanced strategies
     */
    protected function executeHighDemandScraping(string $platform, array $criteria, string $strategy): array
    {
        $strategyConfig = $this->queueStrategies[$strategy];
        $sessions = [];
        $results = [];
        
        // Create multiple concurrent sessions
        for ($i = 0; $i < $strategyConfig['concurrent_sessions']; $i++) {
            $sessions[] = $this->createHighDemandSession($platform, $i);
        }
        
        $attempt = 0;
        $maxAttempts = $strategyConfig['retry_attempts'];
        
        while ($attempt < $maxAttempts && empty($results)) {
            $attempt++;
            
            Log::info("High-demand scraping attempt", [
                'platform' => $platform,
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'concurrent_sessions' => count($sessions)
            ]);
            
            // Try each session concurrently
            $sessionResults = [];
            foreach ($sessions as $sessionIndex => $client) {
                try {
                    $sessionResult = $this->scrapeWithSession($client, $platform, $criteria, $sessionIndex);
                    
                    if (!empty($sessionResult)) {
                        $sessionResults[] = $sessionResult;
                    }
                    
                    // Check for queue detection
                    if ($this->detectQueuePage($sessionResult['html'] ?? '')) {
                        $this->markInQueue($platform);
                        return $this->handleQueueScraping($platform, $criteria, $strategy);
                    }
                    
                } catch (RequestException $e) {
                    Log::warning("Session request failed", [
                        'platform' => $platform,
                        'session' => $sessionIndex,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Rotate session on failure
                    if ($attempt % $strategyConfig['session_rotation_frequency'] === 0) {
                        $sessions[$sessionIndex] = $this->createHighDemandSession($platform, $sessionIndex);
                    }
                }
                
                // Apply session-specific delays
                $this->antiDetection->humanLikeDelay($platform, 'ticket_check');
            }
            
            // Merge results from all sessions
            if (!empty($sessionResults)) {
                $results = $this->mergeSessionResults($sessionResults);
            }
            
            // Apply retry delay with exponential backoff
            if (empty($results) && $attempt < $maxAttempts) {
                $delay = $strategyConfig['retry_delay_base'] * 
                        pow($strategyConfig['retry_delay_multiplier'], $attempt - 1);
                        
                Log::info("Applying retry delay", [
                    'platform' => $platform,
                    'delay_ms' => $delay,
                    'attempt' => $attempt
                ]);
                
                usleep($delay * 1000);
            }
        }
        
        return $results;
    }

    /**
     * Create optimized session for high-demand scraping
     */
    protected function createHighDemandSession(string $platform, int $sessionIndex): \GuzzleHttp\Client
    {
        $client = $this->antiDetection->createAdvancedHttpClient($platform, [
            'curl' => [
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 300,
                CURLOPT_TCP_KEEPINTVL => 60,
                CURLOPT_MAXCONNECTS => 10,
                CURLOPT_FRESH_CONNECT => $sessionIndex === 0 ? 1 : 0, // Force new connection for first session
            ]
        ]);
        
        // Pre-warm the session by visiting the homepage
        try {
            $this->preWarmSession($client, $platform);
        } catch (\Exception $e) {
            Log::warning("Session pre-warming failed", [
                'platform' => $platform,
                'session' => $sessionIndex,
                'error' => $e->getMessage()
            ]);
        }
        
        return $client;
    }

    /**
     * Pre-warm session to establish cookies and avoid cold starts
     */
    protected function preWarmSession(\GuzzleHttp\Client $client, string $platform): void
    {
        $baseUrls = [
            'real_madrid' => 'https://www.realmadrid.com',
            'barcelona' => 'https://www.fcbarcelona.com',
            'bayern_munich' => 'https://fcbayern.com',
            'manchester_city' => 'https://www.mancity.com',
            'psg' => 'https://www.psg.fr',
            'juventus' => 'https://www.juventus.com',
        ];
        
        $baseUrl = $baseUrls[$platform] ?? 'https://www.example.com';
        
        // Visit homepage first
        $response = $client->get($baseUrl);
        
        // Brief delay to mimic human behavior
        $this->antiDetection->humanLikeDelay($platform, 'navigation');
        
        // Visit tickets page to establish session
        $ticketsPath = $this->getTicketsPath($platform);
        if ($ticketsPath) {
            $client->get($baseUrl . $ticketsPath);
        }
    }

    /**
     * Get tickets page path for each platform
     */
    protected function getTicketsPath(string $platform): string
    {
        $paths = [
            'real_madrid' => '/entradas',
            'barcelona' => '/es/entradas', 
            'bayern_munich' => '/de/tickets',
            'manchester_city' => '/tickets',
            'psg' => '/billetterie',
            'juventus' => '/it/biglietti',
        ];
        
        return $paths[$platform] ?? '/tickets';
    }

    /**
     * Scrape with specific session
     */
    protected function scrapeWithSession(\GuzzleHttp\Client $client, string $platform, array $criteria, int $sessionIndex): array
    {
        $searchUrl = $this->buildHighDemandSearchUrl($platform, $criteria);
        
        Log::debug("Session scraping request", [
            'platform' => $platform,
            'session' => $sessionIndex,
            'url' => $searchUrl
        ]);
        
        $response = $client->get($searchUrl);
        $html = $response->getBody()->getContents();
        
        // Check for anti-bot challenges
        $challenge = $this->antiDetection->handleJavaScriptChallenge($html, $platform);
        if ($challenge) {
            throw new \Exception("Anti-bot challenge detected: " . $challenge['provider']);
        }
        
        // Parse tickets from response
        $tickets = $this->parseHighDemandTickets($html, $platform);
        
        return [
            'tickets' => $tickets,
            'html' => $html,
            'session' => $sessionIndex,
            'response_code' => $response->getStatusCode(),
            'timestamp' => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Build search URL optimized for high-demand tickets
     */
    protected function buildHighDemandSearchUrl(string $platform, array $criteria): string
    {
        $baseUrls = [
            'real_madrid' => 'https://www.realmadrid.com/entradas',
            'barcelona' => 'https://www.fcbarcelona.com/es/entradas',
            'bayern_munich' => 'https://fcbayern.com/de/tickets',
            'manchester_city' => 'https://www.mancity.com/tickets',
            'psg' => 'https://www.psg.fr/billetterie',
            'juventus' => 'https://www.juventus.com/it/biglietti',
        ];
        
        $baseUrl = $baseUrls[$platform] ?? '';
        $params = [];
        
        // Add high-demand specific parameters
        if (!empty($criteria['keyword'])) {
            $params['q'] = $criteria['keyword'];
            $params['search'] = $criteria['keyword'];
        }
        
        if (!empty($criteria['date_from'])) {
            $params['from'] = $criteria['date_from'];
        }
        
        // Add availability filter to prioritize available tickets
        $params['availability'] = 'available';
        $params['sort'] = 'date_asc';
        
        return $baseUrl . (!empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Parse tickets with high-demand specific logic
     */
    protected function parseHighDemandTickets(string $html, string $platform): array
    {
        $tickets = [];
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
        
        // Platform-specific selectors for high-demand events
        $selectors = $this->getHighDemandSelectors($platform);
        
        try {
            $crawler->filter($selectors['event_container'])->each(function ($node) use (&$tickets, $platform, $selectors) {
                $ticket = $this->parseHighDemandTicket($node, $platform, $selectors);
                if ($ticket && $this->isHighDemandTicket($ticket)) {
                    $tickets[] = $ticket;
                }
            });
        } catch (\Exception $e) {
            Log::error("High-demand ticket parsing failed", [
                'platform' => $platform,
                'error' => $e->getMessage()
            ]);
        }
        
        // Sort by availability and demand level
        return $this->prioritizeTickets($tickets);
    }

    /**
     * Get high-demand specific selectors
     */
    protected function getHighDemandSelectors(string $platform): array
    {
        $selectors = [
            'real_madrid' => [
                'event_container' => '.match-card, .event-card, .ticket-card',
                'title' => '.match-title, .event-title',
                'date' => '.match-date, .event-date',
                'availability' => '.availability, .status',
                'price' => '.price, .precio',
                'category' => '.category, .categoria',
            ],
            'barcelona' => [
                'event_container' => '.match-item, .event-item, .ticket-item',
                'title' => '.match-name, .event-name',
                'date' => '.match-datetime, .event-datetime',
                'availability' => '.availability-status, .estado',
                'price' => '.price, .precio',
                'category' => '.match-category, .categoria',
            ],
            // Add other platforms...
        ];
        
        return $selectors[$platform] ?? $selectors['real_madrid'];
    }

    /**
     * Parse individual high-demand ticket
     */
    protected function parseHighDemandTicket($node, string $platform, array $selectors): ?array
    {
        try {
            $title = $this->extractText($node, $selectors['title']);
            if (empty($title)) return null;
            
            $availability = $this->extractText($node, $selectors['availability']);
            $availabilityStatus = $this->normalizeAvailabilityStatus($availability);
            
            // Skip sold out tickets unless specifically monitoring
            if ($availabilityStatus === 'sold_out' && !$this->isMonitoringMode()) {
                return null;
            }
            
            return [
                'title' => $title,
                'date' => $this->extractText($node, $selectors['date']),
                'availability' => $availabilityStatus,
                'price' => $this->extractText($node, $selectors['price']),
                'category' => $this->extractText($node, $selectors['category']),
                'platform' => $platform,
                'demand_level' => $this->calculateDemandLevel($title, $availability),
                'scraped_at' => Carbon::now()->toISOString(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract text using CSS selector
     */
    protected function extractText($node, string $selector): string
    {
        try {
            $element = $node->filter($selector)->first();
            return $element->count() > 0 ? trim($element->text()) : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Normalize availability status for high-demand tickets
     */
    protected function normalizeAvailabilityStatus(string $availability): string
    {
        $status = strtolower(trim($availability));
        
        if (str_contains($status, 'available') || 
            str_contains($status, 'on sale') || 
            str_contains($status, 'tickets remaining')) {
            return 'available';
        }
        
        if (str_contains($status, 'sold out') || 
            str_contains($status, 'agotado') || 
            str_contains($status, 'ausverkauft')) {
            return 'sold_out';
        }
        
        if (str_contains($status, 'coming soon') || 
            str_contains($status, 'pre-sale')) {
            return 'coming_soon';
        }
        
        if (str_contains($status, 'limited') || 
            str_contains($status, 'few remaining')) {
            return 'limited';
        }
        
        return 'unknown';
    }

    /**
     * Check if ticket qualifies as high-demand
     */
    protected function isHighDemandTicket(array $ticket): bool
    {
        $highDemandKeywords = [
            'final', 'clásico', 'clasico', 'derby', 'champions league',
            'real madrid', 'barcelona', 'bayern', 'manchester', 'psg'
        ];
        
        $title = strtolower($ticket['title']);
        foreach ($highDemandKeywords as $keyword) {
            if (str_contains($title, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Calculate demand level for ticket
     */
    protected function calculateDemandLevel(string $title, string $availability): string
    {
        $title = strtolower($title);
        
        // Extreme demand events
        if (str_contains($title, 'clásico') || 
            str_contains($title, 'final champions') ||
            str_contains($title, 'real madrid vs barcelona')) {
            return 'extreme';
        }
        
        // Very high demand
        if (str_contains($title, 'champions league') || 
            str_contains($title, 'derby') ||
            str_contains($title, 'final')) {
            return 'very_high';
        }
        
        // High demand based on availability
        if (str_contains(strtolower($availability), 'limited') ||
            str_contains(strtolower($availability), 'few remaining')) {
            return 'high';
        }
        
        return 'medium';
    }

    /**
     * Prioritize tickets by availability and demand
     */
    protected function prioritizeTickets(array $tickets): array
    {
        usort($tickets, function ($a, $b) {
            // Available tickets first
            if ($a['availability'] === 'available' && $b['availability'] !== 'available') {
                return -1;
            }
            if ($b['availability'] === 'available' && $a['availability'] !== 'available') {
                return 1;
            }
            
            // Then by demand level
            $demandOrder = ['extreme' => 0, 'very_high' => 1, 'high' => 2, 'medium' => 3];
            return ($demandOrder[$a['demand_level']] ?? 4) - ($demandOrder[$b['demand_level']] ?? 4);
        });
        
        return $tickets;
    }

    /**
     * Merge results from multiple sessions
     */
    protected function mergeSessionResults(array $sessionResults): array
    {
        $allTickets = [];
        
        foreach ($sessionResults as $result) {
            $allTickets = array_merge($allTickets, $result['tickets']);
        }
        
        // Remove duplicates based on title and date
        $uniqueTickets = [];
        $seen = [];
        
        foreach ($allTickets as $ticket) {
            $key = $ticket['title'] . '|' . $ticket['date'];
            if (!isset($seen[$key])) {
                $uniqueTickets[] = $ticket;
                $seen[$key] = true;
            }
        }
        
        return $this->prioritizeTickets($uniqueTickets);
    }

    /**
     * Check if queue page is detected
     */
    protected function detectQueuePage(string $html): bool
    {
        $queueIndicators = [
            'queue-it', 'waiting room', 'virtual queue', 'queue position',
            'estimated wait time', 'you are in line', 'please wait'
        ];
        
        $htmlLower = strtolower($html);
        foreach ($queueIndicators as $indicator) {
            if (str_contains($htmlLower, $indicator)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle queue-based scraping
     */
    protected function handleQueueScraping(string $platform, array $criteria, string $strategy): array
    {
        Log::info("Handling queue-based scraping", [
            'platform' => $platform,
            'strategy' => $strategy
        ]);
        
        // Monitor queue position and wait times
        $queueInfo = $this->monitorQueuePosition($platform);
        
        // If queue bypass is enabled in strategy, attempt it
        $strategyConfig = $this->queueStrategies[$strategy];
        if ($strategyConfig['bypass_queue_attempts']) {
            $bypassResults = $this->attemptQueueBypass($platform, $criteria);
            if (!empty($bypassResults)) {
                return $bypassResults;
            }
        }
        
        // Otherwise, wait in queue intelligently
        return $this->waitInQueue($platform, $criteria, $queueInfo);
    }

    /**
     * Monitor queue position
     */
    protected function monitorQueuePosition(string $platform): array
    {
        // Implementation would monitor actual queue systems
        return [
            'position' => 0,
            'estimated_wait' => 0,
            'queue_active' => true,
        ];
    }

    /**
     * Attempt to bypass queue using various techniques
     */
    protected function attemptQueueBypass(string $platform, array $criteria): array
    {
        Log::info("Attempting queue bypass", ['platform' => $platform]);
        
        // Try direct URL access to ticket pages
        // Try different entry points
        // Use cached session tokens
        
        return []; // Implementation would return bypassed results if successful
    }

    /**
     * Wait in queue intelligently
     */
    protected function waitInQueue(string $platform, array $criteria, array $queueInfo): array
    {
        Log::info("Waiting in queue", [
            'platform' => $platform,
            'queue_info' => $queueInfo
        ]);
        
        // Implementation would wait and monitor queue progress
        return [];
    }

    /**
     * Monitor pre-sale availability
     */
    protected function monitorPreSaleAvailability(string $platform, array $criteria, string $eventType): array
    {
        Log::info("Monitoring pre-sale availability", [
            'platform' => $platform,
            'event_type' => $eventType
        ]);
        
        $config = $this->priorityEvents[$eventType];
        $interval = $config['monitoring_interval'];
        
        // Set up continuous monitoring
        $this->schedulePreSaleMonitoring($platform, $criteria, $eventType, $interval);
        
        return [
            'status' => 'monitoring',
            'event_type' => $eventType,
            'monitoring_interval' => $interval,
            'message' => 'Pre-sale monitoring activated'
        ];
    }

    /**
     * Schedule pre-sale monitoring
     */
    protected function schedulePreSaleMonitoring(string $platform, array $criteria, string $eventType, int $interval): void
    {
        // Implementation would use Laravel's job queue system
        Log::info("Pre-sale monitoring scheduled", [
            'platform' => $platform,
            'event_type' => $eventType,
            'interval' => $interval
        ]);
    }

    /**
     * Check if platform is in queue
     */
    protected function isInQueue(string $platform): bool
    {
        return Cache::has("queue_active_{$platform}");
    }

    /**
     * Mark platform as in queue
     */
    protected function markInQueue(string $platform): void
    {
        Cache::put("queue_active_{$platform}", true, 3600); // 1 hour
    }

    /**
     * Check if pre-sale monitoring is required
     */
    protected function requiresPreSaleMonitoring(string $eventType): bool
    {
        return isset($this->priorityEvents[$eventType]) && 
               ($this->priorityEvents[$eventType]['pre_sale_monitoring'] ?? false);
    }

    /**
     * Check if in monitoring mode
     */
    protected function isMonitoringMode(): bool
    {
        return Cache::get('scraper_monitoring_mode', false);
    }
}
