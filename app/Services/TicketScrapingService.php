<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Services\TicketApis\StubHubClient;
use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\ViagogoClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class TicketScrapingService
{
    /** @var array<string, mixed> */
    protected $platforms;

    /** @var array<string> */
    protected $manchesterUnitedKeywords = [
        'Manchester United',
        'Man Utd',
        'Man United',
        'MUFC',
        'Old Trafford',
        'Red Devils',
    ];

    /** @var array<string> */
    protected $sportsKeywords = [
        'Premier League',
        'Champions League',
        'Europa League',
        'FA Cup',
        'Carabao Cup',
        'Manchester Derby',
        'El Clasico',
        'Liverpool vs Manchester United',
        'Arsenal vs Manchester United',
    ];

    public function __construct()
    {
        $this->platforms = [
            'stubhub' => [
                'client' => new StubHubClient($this->getConfig('stubhub') ?? []),
            ],
            'ticketmaster' => [
                'client' => new TicketmasterClient($this->getConfig('ticketmaster') ?? []),
            ],
            'viagogo' => [
                'client' => new ViagogoClient($this->getConfig('viagogo') ?? []),
            ],
        ];

        $this->userRotation = new UserRotationService();
    }

    /**
     * Search for Manchester United tickets across platforms
     *
     * @param mixed|null $maxPrice
     * @param mixed|null $dateRange
     *
     * @return array<string, mixed>
     */
    public function searchManchesterUnitedTickets($maxPrice = NULL, $dateRange = NULL): array
    {
        $results = [];

        foreach ($this->manchesterUnitedKeywords as $keyword) {
            $platformResults = $this->searchAllPlatforms($keyword, [
                'sport'      => 'football',
                'team'       => 'Manchester United',
                'max_price'  => $maxPrice,
                'date_range' => $dateRange,
                'priority'   => 'high',
            ]);

            $results = array_merge($results, $platformResults);
        }

        return $this->processAndSaveResults($results, 'manchester_united');
    }

    /**
     * Search for high-demand sports tickets
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function searchHighDemandSportsTickets(array $filters = []): array
    {
        $results = [];
        $keywords = array_merge($this->manchesterUnitedKeywords, $this->sportsKeywords);

        foreach ($keywords as $keyword) {
            $platformResults = $this->searchAllPlatforms($keyword, array_merge($filters, [
                'sport'        => 'football',
                'demand'       => 'high',
                'availability' => 'limited',
            ]));

            $results = array_merge($results, $platformResults);
        }

        return $this->processAndSaveResults($results, 'high_demand_sports');
    }

    /**
     * Get trending Manchester United tickets
     *
     * @param int $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,ScrapedTicket>
     */
    public function getTrendingManchesterUnitedTickets($limit = 20)
    {
        return ScrapedTicket::where(function ($query): void {
            $query->where('title', 'like', '%Manchester United%')
                ->orWhere('title', 'like', '%Man Utd%')
                ->orWhere('venue', 'like', '%Old Trafford%');
        })
            ->where('is_available', TRUE)
            ->where('event_date', '>', now())
            ->orderBy('scraped_at', 'desc')
            ->orderBy('is_high_demand', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get best deals for sports tickets
     *
     * @param mixed $sport
     * @param int   $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,ScrapedTicket>
     */
    public function getBestSportsDeals($sport = 'football', $limit = 50)
    {
        return ScrapedTicket::where('is_available', TRUE)
            ->where('event_date', '>', now())
            ->whereNotNull('min_price')
            ->orderBy('min_price', 'asc')
            ->orderBy('scraped_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check alerts and notify users of matches
     */
    public function checkAlerts(): int
    {
        $alertsChecked = 0;
        $alerts = TicketAlert::needsCheck()->with('user')->get();

        foreach ($alerts as $alert) {
            try {
                $matches = $this->searchTickets($alert->keywords, [
                    'platforms' => $alert->platform ? [$alert->platform] : NULL,
                    'max_price' => $alert->max_price,
                    'currency'  => $alert->currency,
                    'filters'   => $alert->filters,
                ]);

                $foundTickets = [];
                foreach ($matches as $platformTickets) {
                    foreach ($platformTickets as $ticket) {
                        if ($alert->matchesTicket($ticket)) {
                            $foundTickets[] = $ticket;
                        }
                    }
                }

                if (! empty($foundTickets)) {
                    $alert->incrementMatches();
                    $this->sendAlertNotification($alert, $foundTickets);
                }

                $alertsChecked++;
            } catch (\Exception $e) {
                Log::error("Alert check error for alert {$alert->id}: " . $e->getMessage());
            }
        }

        return $alertsChecked;
    }

    /**
     * Search for tickets with specific keywords and options
     */
    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function searchTickets(string $keywords, array $options = []): array
    {
        $platforms = $options['platforms'] ?? ['stubhub', 'ticketmaster', 'viagogo'];
        $maxPrice = $options['max_price'] ?? NULL;
        $currency = $options['currency'] ?? 'USD';
        $filters = $options['filters'] ?? [];

        $results = [];

        foreach ($platforms as $platform) {
            try {
                Log::info("Starting scrape for platform: {$platform}", ['keywords' => $keywords]);

                $tickets = $this->scrapePlatform($platform, $keywords, [
                    'max_price' => $maxPrice,
                    'currency'  => $currency,
                    'filters'   => $filters,
                ]);

                $results[$platform] = $tickets;

                Log::info("Completed scrape for {$platform}", ['count' => count($tickets)]);
            } catch (\Exception $e) {
                Log::error("Error scraping {$platform}: " . $e->getMessage(), [
                    'platform'  => $platform,
                    'keywords'  => $keywords,
                    'exception' => $e->getTraceAsString(),
                ]);
                $results[$platform] = [];
            }
        }

        return $results;
    }

    /**
     * Auto-purchase tickets based on criteria
     *
     * @param mixed $ticketId
     * @param mixed $userId
     * @param mixed $maxPrice
     *
     * @return array<string, mixed>
     */
    public function attemptAutoPurchase($ticketId, $userId, $maxPrice): array
    {
        $ticket = ScrapedTicket::findOrFail($ticketId);
        $user = User::findOrFail($userId);

        // Validate purchase criteria
        if ($ticket->min_price > $maxPrice) {
            return [
                'success' => FALSE,
                'message' => 'Ticket price exceeds maximum budget',
            ];
        }

        if (! $ticket->is_available || ! $ticket->ticket_url) {
            return [
                'success' => FALSE,
                'message' => 'Ticket no longer available',
            ];
        }

        // For demonstration - in reality, this would integrate with payment systems
        Log::info('Auto-purchase attempt', [
            'user_id'   => $userId,
            'ticket_id' => $ticketId,
            'ticket'    => $ticket->title,
            'price'     => $ticket->min_price,
            'url'       => $ticket->ticket_url,
        ]);

        return [
            'success'      => TRUE,
            'message'      => 'Purchase initiated - redirecting to ticket platform',
            'redirect_url' => $ticket->ticket_url,
            'ticket'       => $ticket,
        ];
    }

    /**
     * Search across all configured platforms
     *
     * @param mixed                $keyword
     * @param array<string, mixed> $filters
     *
     * @return array<int, mixed>
     */
    protected function searchAllPlatforms($keyword, array $filters = []): array
    {
        $allResults = [];
        foreach ($this->platforms as $platform => $config) {
            $client = $config['client'];
            $user = $this->userRotation->getRotatedUser($platform, 'search');
            if ($user) {
                $results = $client->searchEvents(array_merge(['keyword' => $keyword], $filters));
                $allResults = array_merge($allResults, $results);
            } else {
                Log::warning("No user available for platform: {$platform}");
            }
        }

        return $allResults;
    }

    /**
     * Search StubHub for tickets
     *
     * @param mixed $keyword
     * @param mixed $filters
     */
    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    protected function searchStubHub(string $keyword, array $filters = [])
    {
        try {
            $cacheKey = 'stubhub_search_' . md5($keyword . serialize($filters));

            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($keyword, $filters) {
                $params = [
                    'q'     => $keyword,
                    'sort'  => 'price_asc',
                    'rows'  => 50,
                    'start' => 0,
                ];

                if (isset($filters['max_price'])) {
                    $params['maxPrice'] = $filters['max_price'];
                }

                if (isset($filters['date_range'])) {
                    $params['dateLocal'] = $filters['date_range'];
                }

                $response = Http::withHeaders($this->platforms['stubhub']['headers'])
                    ->timeout(30)
                    ->get($this->platforms['stubhub']['base_url'], $params);

                if ($response->successful()) {
                    return $this->parseStubHubResults($response->json(), $keyword);
                }

                Log::warning('StubHub API request failed', [
                    'status'  => $response->status(),
                    'keyword' => $keyword,
                ]);

                return [];
            });
        } catch (\Exception $e) {
            Log::error('StubHub search error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Search Ticketmaster for tickets
     *
     * @param mixed $keyword
     * @param mixed $filters
     */
    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    protected function searchTicketmaster(string $keyword, array $filters = [])
    {
        try {
            if (! $this->platforms['ticketmaster']['api_key']) {
                Log::warning('Ticketmaster API key not configured');

                return [];
            }

            $cacheKey = 'ticketmaster_search_' . md5($keyword . serialize($filters));

            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($keyword, $filters) {
                $params = [
                    'keyword'            => $keyword,
                    'apikey'             => $this->platforms['ticketmaster']['api_key'],
                    'size'               => 50,
                    'sort'               => 'date,asc',
                    'classificationName' => 'sports',
                ];

                if (isset($filters['date_range'])) {
                    $params['startDateTime'] = $filters['date_range'] . 'T00:00:00Z';
                }

                $response = Http::withHeaders($this->platforms['ticketmaster']['headers'])
                    ->timeout(30)
                    ->get($this->platforms['ticketmaster']['base_url'], $params);

                if ($response->successful()) {
                    return $this->parseTicketmasterResults($response->json(), $keyword);
                }

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Ticketmaster search error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Search Viagogo for tickets
     *
     * @param mixed $keyword
     * @param mixed $filters
     */
    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    protected function searchViagogo(string $keyword, array $filters = [])
    {
        try {
            // Note: Viagogo requires OAuth, this is a simplified version
            $cacheKey = 'viagogo_search_' . md5($keyword . serialize($filters));

            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($keyword, $filters) {
                // Simplified Viagogo search - would need proper OAuth implementation
                $params = [
                    'q'     => $keyword,
                    'sort'  => 'price',
                    'limit' => 50,
                ];

                // Simulated results for demo - replace with actual API call
                return $this->generateMockViagogo($keyword, $filters);
            });
        } catch (\Exception $e) {
            Log::error('Viagogo search error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Parse StubHub API results
     *
     * @param mixed $keyword
     */
    /**
     * @return array<int,array<string,mixed>>
     */
    protected function parseStubHubResults(mixed $data, string $keyword)
    {
        $tickets = [];

        if (isset($data['events'])) {
            foreach ($data['events'] as $event) {
                $tickets[] = [
                    'platform'       => 'stubhub',
                    'external_id'    => $event['id'] ?? NULL,
                    'title'          => $event['name'] ?? 'Unknown Event',
                    'venue'          => $event['venue']['name'] ?? 'Unknown Venue',
                    'location'       => $event['venue']['city'] ?? 'Unknown City',
                    'event_date'     => isset($event['eventDateLocal']) ? Carbon::parse($event['eventDateLocal']) : NULL,
                    'min_price'      => $event['ticketInfo']['minListPrice'] ?? NULL,
                    'max_price'      => $event['ticketInfo']['maxListPrice'] ?? NULL,
                    'currency'       => $event['ticketInfo']['currencyCode'] ?? 'USD',
                    'availability'   => $event['ticketInfo']['totalTickets'] ?? 0,
                    'is_high_demand' => ($event['ticketInfo']['totalTickets'] ?? 0) < 100,
                    'ticket_url'     => $event['webURI'] ?? NULL,
                    'scraped_at'     => now(),
                    'search_keyword' => $keyword,
                    'metadata'       => json_encode($event),
                ];
            }
        }

        return $tickets;
    }

    /**
     * Parse Ticketmaster API results
     *
     * @param mixed $keyword
     */
    /**
     * @return array<int,array<string,mixed>>
     */
    protected function parseTicketmasterResults(mixed $data, string $keyword)
    {
        $tickets = [];

        if (isset($data['_embedded']['events'])) {
            foreach ($data['_embedded']['events'] as $event) {
                $venue = $event['_embedded']['venues'][0] ?? [];
                $priceRange = $event['priceRanges'][0] ?? [];

                $tickets[] = [
                    'platform'       => 'ticketmaster',
                    'external_id'    => $event['id'] ?? NULL,
                    'title'          => $event['name'] ?? 'Unknown Event',
                    'venue'          => $venue['name'] ?? 'Unknown Venue',
                    'location'       => ($venue['city']['name'] ?? '') . ', ' . ($venue['country']['name'] ?? ''),
                    'event_date'     => isset($event['dates']['start']['dateTime']) ? Carbon::parse($event['dates']['start']['dateTime']) : NULL,
                    'min_price'      => $priceRange['min'] ?? NULL,
                    'max_price'      => $priceRange['max'] ?? NULL,
                    'currency'       => $priceRange['currency'] ?? 'USD',
                    'availability'   => $event['pleaseNote'] ?? 'Available',
                    'is_high_demand' => isset($event['promoter']['name']) && str_contains($event['promoter']['name'], 'Official'),
                    'ticket_url'     => $event['url'] ?? NULL,
                    'scraped_at'     => now(),
                    'search_keyword' => $keyword,
                    'metadata'       => json_encode($event),
                ];
            }
        }

        return $tickets;
    }

    /**
     * Generate mock Viagogo results for demonstration
     *
     * @param mixed $keyword
     * @param mixed $filters
     */
    /**
     * Generate mock Viagogo results for testing.
     *
     * @param array<string,mixed> $filters
     *
     * @return array<int,array<string,mixed>>
     */
    protected function generateMockViagogo(string $keyword, array $filters)
    {
        if (! str_contains(strtolower($keyword), 'manchester')) {
            return [];
        }

        return [
            [
                'platform'       => 'viagogo',
                'external_id'    => 'vg_' . uniqid(),
                'title'          => 'Manchester United vs Liverpool',
                'venue'          => 'Old Trafford',
                'location'       => 'Manchester, UK',
                'event_date'     => Carbon::now()->addDays(rand(7, 60)),
                'min_price'      => rand(80, 150),
                'max_price'      => rand(300, 800),
                'currency'       => 'GBP',
                'availability'   => rand(10, 100),
                'is_high_demand' => TRUE,
                'ticket_url'     => 'https://viagogo.com/sports-tickets/football/manchester-united',
                'scraped_at'     => now(),
                'search_keyword' => $keyword,
                'metadata'       => json_encode(['mock' => TRUE]),
            ],
        ];
    }

    /**
     * Process and save scraping results
     *
     * @param array<int, mixed> $results
     * @param mixed             $category
     *
     * @return array<string, mixed>
     */
    protected function processAndSaveResults(array $results, $category): array
    {
        $savedTickets = [];
        $highDemandCount = 0;

        foreach ($results as $ticketData) {
            try {
                // Check if ticket already exists
                $existing = ScrapedTicket::where('external_id', $ticketData['external_id'])
                    ->where('platform', $ticketData['platform'])
                    ->first();

                if ($existing) {
                    // Update existing ticket
                    $existing->update([
                        'min_price'    => $ticketData['min_price'],
                        'max_price'    => $ticketData['max_price'],
                        'availability' => $ticketData['availability'],
                        'scraped_at'   => $ticketData['scraped_at'],
                        'is_available' => TRUE,
                    ]);
                    $savedTickets[] = $existing;
                } else {
                    // Create new ticket
                    $ticket = ScrapedTicket::create($ticketData);
                    $savedTickets[] = $ticket;
                }

                if ($ticketData['is_high_demand']) {
                    $highDemandCount++;
                    $this->triggerHighDemandAlert($ticketData);
                }
            } catch (\Exception $e) {
                Log::error('Error saving scraped ticket: ' . $e->getMessage(), $ticketData);
            }
        }

        Log::info('Ticket scraping completed', [
            'category'    => $category,
            'total_found' => count($results),
            'saved'       => count($savedTickets),
            'high_demand' => $highDemandCount,
        ]);

        return [
            'total_found' => count($results),
            'saved'       => count($savedTickets),
            'high_demand' => $highDemandCount,
            'tickets'     => $savedTickets,
        ];
    }

    /**
     * Trigger alerts for high-demand tickets
     *
     * @param mixed $ticketData
     */
    protected function triggerHighDemandAlert($ticketData): void
    {
        // Find users with alerts for this type of event
        $alerts = TicketAlert::where('is_active', TRUE)
            ->where(function ($query) use ($ticketData): void {
                $query->where('keywords', 'like', '%' . $ticketData['search_keyword'] . '%')
                    ->orWhere('keywords', 'like', '%Manchester United%')
                    ->orWhere('keywords', 'like', '%high demand%');
            })
            ->get();

        foreach ($alerts as $alert) {
            // Check price criteria
            if ($alert->max_price && $ticketData['min_price'] > $alert->max_price) {
                continue;
            }

            // Send notification (you can implement email, SMS, etc.)
            $this->sendTicketAlert($alert->user, $ticketData);
        }
    }

    /**
     * Send ticket alert to user
     *
     * @param array<string, mixed> $ticketData
     */
    protected function sendTicketAlert(User $user, $ticketData): void
    {
        // Implementation for sending alerts (email, push notification, etc.)
        try {
            $user->notify(new \App\Notifications\HighValueTicketAlert(
                new ScrapedTicket($ticketData),
                new TicketAlert(['name' => 'Custom Alert']),
                100,
            ));
            Log::info('High demand ticket alert sent', [
                'user_id'  => $user->id,
                'ticket'   => $ticketData['title'],
                'price'    => $ticketData['min_price'],
                'platform' => $ticketData['platform'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send high demand ticket alert', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param mixed $platform
     *
     * @return array<string, mixed>|null
     */
    private function getConfig($platform)
    {
        return config("ticket_apis.{$platform}");
    }

    /**
     * Scrape a specific platform
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function scrapePlatform(string $platform, string $keywords, array $options = []): array
    {
        $cacheKey = "tickets:{$platform}:" . md5($keywords . serialize($options));

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($platform, $keywords, $options) {
            return match ($platform) {
                'stubhub'      => $this->searchStubHub($keywords, $options),
                'ticketmaster' => $this->searchTicketmaster($keywords, $options),
                'viagogo'      => $this->searchViagogo($keywords, $options),
                default        => [],
            };
        });
    }

    /**
     * Send notification for alert matches
     *
     * @param array<string, mixed> $tickets
     */
    private function sendAlertNotification(TicketAlert $alert, array $tickets): void
    {
        try {
            // Send notification for each matching ticket
            foreach ($tickets as $ticketData) {
                // Convert array to ScrapedTicket model
                $scrapedTicket = new ScrapedTicket($ticketData);

                // Calculate match score based on alert criteria
                $matchScore = $this->calculateMatchScore($alert, $ticketData);

                // Send the high-value ticket alert notification
                $alert->user->notify(new \App\Notifications\HighValueTicketAlert(
                    $scrapedTicket,
                    $alert,
                    $matchScore,
                ));

                Log::info('HighValueTicketAlert notification sent', [
                    'user_id'      => $alert->user_id,
                    'alert_id'     => $alert->id,
                    'alert_name'   => $alert->name,
                    'ticket_title' => $ticketData['event_title'] ?? $ticketData['title'] ?? 'Unknown',
                    'match_score'  => $matchScore,
                ]);
            }

            Log::info("Alert triggered for user {$alert->user_id}", [
                'alert_id'      => $alert->id,
                'alert_name'    => $alert->name,
                'tickets_found' => count($tickets),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send alert notifications', [
                'alert_id'      => $alert->id,
                'user_id'       => $alert->user_id,
                'error'         => $e->getMessage(),
                'tickets_count' => count($tickets),
            ]);
        }
    }

    /**
     * Calculate match score for alert and ticket
     */
    /**
     * Calculate match score between alert and ticket data.
     *
     * @param array<string,mixed> $ticketData
     */
    private function calculateMatchScore(TicketAlert $alert, array $ticketData): int
    {
        $score = 0;

        // Keyword matching (40 points)
        $keywords = strtolower($alert->keywords ?? '');
        $ticketTitle = strtolower($ticketData['event_title'] ?? $ticketData['title'] ?? '');
        $searchKeyword = strtolower($ticketData['search_keyword'] ?? '');

        if (str_contains($ticketTitle, $keywords) || str_contains($searchKeyword, $keywords)) {
            $score += 40;
        }

        // Platform matching (20 points)
        if ($alert->platform && $alert->platform === $ticketData['platform']) {
            $score += 20;
        }

        // Price criteria (30 points)
        if ($alert->max_price) {
            $ticketPrice = $ticketData['min_price'] ?? $ticketData['price'] ?? 0;
            if ($ticketPrice <= $alert->max_price) {
                $score += 30;
                // Bonus for better value
                $priceRatio = $ticketPrice / $alert->max_price;
                if ($priceRatio <= 0.8) { // 20% under budget
                    $score += 10;
                }
            }
        } else {
            $score += 15; // No price restriction is somewhat good
        }

        // High demand bonus (10 points)
        if ($ticketData['is_high_demand'] ?? FALSE) {
            $score += 10;
        }

        return min(100, $score); // Cap at 100
    }
}
