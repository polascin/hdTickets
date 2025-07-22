<?php

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TicketScrapingService
{
    protected $platforms;

    public function __construct()
    {
        $this->platforms = [
            'stubhub' => [
                'base_url' => 'https://www.stubhub.com/api/search/catalog/events/v3',
                'search_endpoint' => '/search',
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                ]
            ],
            'ticketmaster' => [
                'base_url' => 'https://app.ticketmaster.com/discovery/v2/events.json',
                'api_key' => env('TICKETMASTER_API_KEY'),
                'headers' => [
                    'User-Agent' => 'HDTickets/1.0',
                    'Accept' => 'application/json',
                ]
            ],
            'viagogo' => [
                'base_url' => 'https://www.viagogo.com/api/v2',
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                ]
            ]
        ];
    }

    protected $manchesterUnitedKeywords = [
        'Manchester United',
        'Man Utd',
        'Man United',
        'MUFC',
        'Old Trafford',
        'Red Devils'
    ];

    protected $sportsKeywords = [
        'Premier League',
        'Champions League',
        'Europa League',
        'FA Cup',
        'Carabao Cup',
        'Manchester Derby',
        'El Clasico',
        'Liverpool vs Manchester United',
        'Arsenal vs Manchester United'
    ];

    /**
     * Search for Manchester United tickets across platforms
     */
    public function searchManchesterUnitedTickets($maxPrice = null, $dateRange = null)
    {
        $results = [];
        
        foreach ($this->manchesterUnitedKeywords as $keyword) {
            $platformResults = $this->searchAllPlatforms($keyword, [
                'sport' => 'football',
                'team' => 'Manchester United',
                'max_price' => $maxPrice,
                'date_range' => $dateRange,
                'priority' => 'high'
            ]);
            
            $results = array_merge($results, $platformResults);
        }
        
        return $this->processAndSaveResults($results, 'manchester_united');
    }

    /**
     * Search for high-demand sports tickets
     */
    public function searchHighDemandSportsTickets($filters = [])
    {
        $results = [];
        $keywords = array_merge($this->manchesterUnitedKeywords, $this->sportsKeywords);
        
        foreach ($keywords as $keyword) {
            $platformResults = $this->searchAllPlatforms($keyword, array_merge($filters, [
                'sport' => 'football',
                'demand' => 'high',
                'availability' => 'limited'
            ]));
            
            $results = array_merge($results, $platformResults);
        }
        
        return $this->processAndSaveResults($results, 'high_demand_sports');
    }

    /**
     * Search across all configured platforms
     */
    protected function searchAllPlatforms($keyword, $filters = [])
    {
        $allResults = [];
        
        // StubHub Search
        $stubhubResults = $this->searchStubHub($keyword, $filters);
        $allResults = array_merge($allResults, $stubhubResults);
        
        // Ticketmaster Search
        $ticketmasterResults = $this->searchTicketmaster($keyword, $filters);
        $allResults = array_merge($allResults, $ticketmasterResults);
        
        // Viagogo Search
        $viagogoResults = $this->searchViagogo($keyword, $filters);
        $allResults = array_merge($allResults, $viagogoResults);
        
        return $allResults;
    }

    /**
     * Search StubHub for tickets
     */
    protected function searchStubHub($keyword, $filters = [])
    {
        try {
            $cacheKey = "stubhub_search_" . md5($keyword . serialize($filters));
            
            return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($keyword, $filters) {
                $params = [
                    'q' => $keyword,
                    'sort' => 'price_asc',
                    'rows' => 50,
                    'start' => 0
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
                    'status' => $response->status(),
                    'keyword' => $keyword
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
     */
    protected function searchTicketmaster($keyword, $filters = [])
    {
        try {
            if (!$this->platforms['ticketmaster']['api_key']) {
                Log::warning('Ticketmaster API key not configured');
                return [];
            }
            
            $cacheKey = "ticketmaster_search_" . md5($keyword . serialize($filters));
            
            return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($keyword, $filters) {
                $params = [
                    'keyword' => $keyword,
                    'apikey' => $this->platforms['ticketmaster']['api_key'],
                    'size' => 50,
                    'sort' => 'date,asc',
                    'classificationName' => 'sports'
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
     */
    protected function searchViagogo($keyword, $filters = [])
    {
        try {
            // Note: Viagogo requires OAuth, this is a simplified version
            $cacheKey = "viagogo_search_" . md5($keyword . serialize($filters));
            
            return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($keyword, $filters) {
                // Simplified Viagogo search - would need proper OAuth implementation
                $params = [
                    'q' => $keyword,
                    'sort' => 'price',
                    'limit' => 50
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
     */
    protected function parseStubHubResults($data, $keyword)
    {
        $tickets = [];
        
        if (isset($data['events'])) {
            foreach ($data['events'] as $event) {
                $tickets[] = [
                    'platform' => 'stubhub',
                    'external_id' => $event['id'] ?? null,
                    'title' => $event['name'] ?? 'Unknown Event',
                    'venue' => $event['venue']['name'] ?? 'Unknown Venue',
                    'location' => $event['venue']['city'] ?? 'Unknown City',
                    'event_date' => isset($event['eventDateLocal']) ? Carbon::parse($event['eventDateLocal']) : null,
                    'min_price' => $event['ticketInfo']['minListPrice'] ?? null,
                    'max_price' => $event['ticketInfo']['maxListPrice'] ?? null,
                    'currency' => $event['ticketInfo']['currencyCode'] ?? 'USD',
                    'availability' => $event['ticketInfo']['totalTickets'] ?? 0,
                    'is_high_demand' => ($event['ticketInfo']['totalTickets'] ?? 0) < 100,
                    'ticket_url' => $event['webURI'] ?? null,
                    'scraped_at' => now(),
                    'search_keyword' => $keyword,
                    'metadata' => json_encode($event)
                ];
            }
        }
        
        return $tickets;
    }

    /**
     * Parse Ticketmaster API results
     */
    protected function parseTicketmasterResults($data, $keyword)
    {
        $tickets = [];
        
        if (isset($data['_embedded']['events'])) {
            foreach ($data['_embedded']['events'] as $event) {
                $venue = $event['_embedded']['venues'][0] ?? [];
                $priceRange = $event['priceRanges'][0] ?? [];
                
                $tickets[] = [
                    'platform' => 'ticketmaster',
                    'external_id' => $event['id'] ?? null,
                    'title' => $event['name'] ?? 'Unknown Event',
                    'venue' => $venue['name'] ?? 'Unknown Venue',
                    'location' => ($venue['city']['name'] ?? '') . ', ' . ($venue['country']['name'] ?? ''),
                    'event_date' => isset($event['dates']['start']['dateTime']) ? Carbon::parse($event['dates']['start']['dateTime']) : null,
                    'min_price' => $priceRange['min'] ?? null,
                    'max_price' => $priceRange['max'] ?? null,
                    'currency' => $priceRange['currency'] ?? 'USD',
                    'availability' => $event['pleaseNote'] ?? 'Available',
                    'is_high_demand' => isset($event['promoter']['name']) && str_contains($event['promoter']['name'], 'Official'),
                    'ticket_url' => $event['url'] ?? null,
                    'scraped_at' => now(),
                    'search_keyword' => $keyword,
                    'metadata' => json_encode($event)
                ];
            }
        }
        
        return $tickets;
    }

    /**
     * Generate mock Viagogo results for demonstration
     */
    protected function generateMockViagogo($keyword, $filters)
    {
        if (!str_contains(strtolower($keyword), 'manchester')) {
            return [];
        }
        
        return [
            [
                'platform' => 'viagogo',
                'external_id' => 'vg_' . uniqid(),
                'title' => 'Manchester United vs Liverpool',
                'venue' => 'Old Trafford',
                'location' => 'Manchester, UK',
                'event_date' => Carbon::now()->addDays(rand(7, 60)),
                'min_price' => rand(80, 150),
                'max_price' => rand(300, 800),
                'currency' => 'GBP',
                'availability' => rand(10, 100),
                'is_high_demand' => true,
                'ticket_url' => 'https://viagogo.com/sports-tickets/football/manchester-united',
                'scraped_at' => now(),
                'search_keyword' => $keyword,
                'metadata' => json_encode(['mock' => true])
            ]
        ];
    }

    /**
     * Process and save scraping results
     */
    protected function processAndSaveResults($results, $category)
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
                        'min_price' => $ticketData['min_price'],
                        'max_price' => $ticketData['max_price'],
                        'availability' => $ticketData['availability'],
                        'scraped_at' => $ticketData['scraped_at'],
                        'is_available' => true
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
        
        Log::info("Ticket scraping completed", [
            'category' => $category,
            'total_found' => count($results),
            'saved' => count($savedTickets),
            'high_demand' => $highDemandCount
        ]);
        
        return [
            'total_found' => count($results),
            'saved' => count($savedTickets),
            'high_demand' => $highDemandCount,
            'tickets' => $savedTickets
        ];
    }

    /**
     * Trigger alerts for high-demand tickets
     */
    protected function triggerHighDemandAlert($ticketData)
    {
        // Find users with alerts for this type of event
        $alerts = TicketAlert::where('is_active', true)
            ->where(function($query) use ($ticketData) {
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
     */
    protected function sendTicketAlert(User $user, $ticketData)
    {
        // Implementation for sending alerts (email, push notification, etc.)
        Log::info("High demand ticket alert sent", [
            'user_id' => $user->id,
            'ticket' => $ticketData['title'],
            'price' => $ticketData['min_price'],
            'platform' => $ticketData['platform']
        ]);
    }

    /**
     * Get trending Manchester United tickets
     */
    public function getTrendingManchesterUnitedTickets($limit = 20)
    {
        return ScrapedTicket::where(function($query) {
                $query->where('title', 'like', '%Manchester United%')
                      ->orWhere('title', 'like', '%Man Utd%')
                      ->orWhere('venue', 'like', '%Old Trafford%');
            })
            ->where('is_available', true)
            ->where('event_date', '>', now())
            ->orderBy('scraped_at', 'desc')
            ->orderBy('is_high_demand', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get best deals for sports tickets
     */
    public function getBestSportsDeals($sport = 'football', $limit = 50)
    {
        return ScrapedTicket::where('is_available', true)
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
                    'platforms' => $alert->platform ? [$alert->platform] : null,
                    'max_price' => $alert->max_price,
                    'currency' => $alert->currency,
                    'filters' => $alert->filters
                ]);

                $foundTickets = [];
                foreach ($matches as $platformTickets) {
                    foreach ($platformTickets as $ticket) {
                        if ($alert->matchesTicket($ticket)) {
                            $foundTickets[] = $ticket;
                        }
                    }
                }

                if (!empty($foundTickets)) {
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
    public function searchTickets(string $keywords, array $options = []): array
    {
        $platforms = $options['platforms'] ?? ['stubhub', 'ticketmaster', 'viagogo'];
        $maxPrice = $options['max_price'] ?? null;
        $currency = $options['currency'] ?? 'USD';
        $filters = $options['filters'] ?? [];

        $results = [];

        foreach ($platforms as $platform) {
            try {
                Log::info("Starting scrape for platform: {$platform}", ['keywords' => $keywords]);
                
                $tickets = $this->scrapePlatform($platform, $keywords, [
                    'max_price' => $maxPrice,
                    'currency' => $currency,
                    'filters' => $filters
                ]);

                $results[$platform] = $tickets;
                
                Log::info("Completed scrape for {$platform}", ['count' => count($tickets)]);

            } catch (\Exception $e) {
                Log::error("Error scraping {$platform}: " . $e->getMessage(), [
                    'platform' => $platform,
                    'keywords' => $keywords,
                    'exception' => $e->getTraceAsString()
                ]);
                $results[$platform] = [];
            }
        }

        return $results;
    }

    /**
     * Scrape a specific platform
     */
    private function scrapePlatform(string $platform, string $keywords, array $options = []): array
    {
        $cacheKey = "tickets:{$platform}:" . md5($keywords . serialize($options));
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($platform, $keywords, $options) {
            return match($platform) {
                'stubhub' => $this->searchStubHub($keywords, $options),
                'ticketmaster' => $this->searchTicketmaster($keywords, $options),
                'viagogo' => $this->searchViagogo($keywords, $options),
                default => []
            };
        });
    }

    /**
     * Send notification for alert matches
     */
    private function sendAlertNotification(TicketAlert $alert, array $tickets): void
    {
        // Implementation would depend on your notification system
        // Could use Laravel's notification system, email, SMS, etc.
        
        Log::info("Alert triggered for user {$alert->user_id}", [
            'alert_id' => $alert->id,
            'alert_name' => $alert->name,
            'tickets_found' => count($tickets)
        ]);

        // Example: Queue email notification
        // Mail::to($alert->user)->queue(new TicketAlertMail($alert, $tickets));
    }

    /**
     * Auto-purchase tickets based on criteria
     */
    public function attemptAutoPurchase($ticketId, $userId, $maxPrice)
    {
        $ticket = ScrapedTicket::findOrFail($ticketId);
        $user = User::findOrFail($userId);
        
        // Validate purchase criteria
        if ($ticket->min_price > $maxPrice) {
            return [
                'success' => false,
                'message' => 'Ticket price exceeds maximum budget'
            ];
        }
        
        if (!$ticket->is_available || !$ticket->ticket_url) {
            return [
                'success' => false,
                'message' => 'Ticket no longer available'
            ];
        }
        
        // For demonstration - in reality, this would integrate with payment systems
        Log::info("Auto-purchase attempt", [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'ticket' => $ticket->title,
            'price' => $ticket->min_price,
            'url' => $ticket->ticket_url
        ]);
        
        return [
            'success' => true,
            'message' => 'Purchase initiated - redirecting to ticket platform',
            'redirect_url' => $ticket->ticket_url,
            'ticket' => $ticket
        ];
    }
}
