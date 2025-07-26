<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\ScraperPluginInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SilverstoneF1Plugin implements ScraperPluginInterface
{
    private bool $enabled = true;
    private array $config = [];
    private string $baseUrl = 'https://www.silverstone.co.uk';
    private string $venue = 'Silverstone Circuit';
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function getInfo(): array
    {
        return [
            'name' => 'Silverstone F1 Circuit',
            'description' => 'British Grand Prix and motorsport events at Silverstone Circuit',
            'version' => '1.0.0',
            'platform' => 'silverstone_f1',
            'capabilities' => [
                'formula_1',
                'british_grand_prix',
                'motorsport',
                'racing_events',
                'hospitality_packages'
            ],
            'rate_limit' => '1 request per 2 seconds',
            'supported_criteria' => [
                'keyword', 'date_range', 'event_type'
            ],
            'venue' => $this->venue,
            'competitions' => [
                'Formula 1', 'British Grand Prix', 'MotoGP', 'Motorsport Events'
            ]
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
        Log::info('Silverstone F1 plugin enabled');
    }

    public function disable(): void
    {
        $this->enabled = false;
        Log::info('Silverstone F1 plugin disabled');
    }

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        
        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }
        
        if (isset($config['user_agent'])) {
            $this->userAgent = $config['user_agent'];
        }
        
        Log::info('Silverstone F1 plugin configured', ['config' => $config]);
    }

    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new \Exception('Silverstone F1 plugin is disabled');
        }

        Log::info('Starting Silverstone F1 scraping', $criteria);
        
        try {
            // Build search URL
            $searchUrl = $this->buildSearchUrl($criteria);
            
            // Make request with rate limiting
            $this->enforceRateLimit();
            $response = $this->makeRequest($searchUrl);
            
            // Parse HTML response
            $events = $this->parseSearchResults($response);
            
            // Filter and format results
            $filteredEvents = $this->filterResults($events, $criteria);
            
            Log::info('Silverstone F1 scraping completed', [
                'url' => $searchUrl,
                'results_found' => count($filteredEvents)
            ]);
            
            return $filteredEvents;
            
        } catch (\Exception $e) {
            Log::error('Silverstone F1 scraping failed', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function buildSearchUrl(array $criteria): string
    {
        $params = [];
        
        if (!empty($criteria['keyword'])) {
            $params['q'] = urlencode($criteria['keyword']);
        }
        
        if (!empty($criteria['event_type'])) {
            $params['type'] = $this->mapEventType($criteria['event_type']);
        }
        
        $queryString = http_build_query($params);
        return $this->baseUrl . '/events' . ($queryString ? '?' . $queryString : '');
    }

    private function mapEventType(string $eventType): string
    {
        $mapping = [
            'formula 1' => 'f1',
            'f1' => 'f1',
            'british grand prix' => 'british-gp',
            'motogp' => 'motogp',
            'motorsport' => 'motorsport'
        ];
        
        return $mapping[strtolower($eventType)] ?? 'all';
    }

    private function makeRequest(string $url): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache'
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch Silverstone F1 page: HTTP {$response->status()}");
        }

        return $response->body();
    }

    private function parseSearchResults(string $html): array
    {
        $events = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        // Silverstone event selectors
        $eventNodes = $xpath->query('//div[contains(@class, "event")] | //div[contains(@class, "race")] | //article[contains(@class, "event-listing")] | //div[contains(@class, "motorsport")]');
        
        foreach ($eventNodes as $eventNode) {
            try {
                $event = $this->extractEventData($xpath, $eventNode);
                if (!empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse Silverstone F1 event', ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return $events;
    }

    private function extractEventData(\DOMXPath $xpath, \DOMElement $eventNode): array
    {
        return [
            'platform' => 'silverstone_f1',
            'event_name' => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "event-name")] | .//*[contains(@class, "race-title")]', $eventNode),
            'venue' => $this->extractText($xpath, './/*[contains(@class, "venue")] | .//*[contains(@class, "location")]', $eventNode) ?: $this->venue,
            'date' => $this->parseDate($this->extractText($xpath, './/*[contains(@class, "date")] | .//time', $eventNode)),
            'price_min' => null,
            'price_max' => null,
            'currency' => 'GBP',
            'url' => $this->extractUrl($xpath, './/a[@href]', $eventNode),
            'availability_status' => $this->normalizeAvailability($this->extractText($xpath, './/*[contains(@class, "availability")] | .//*[contains(@class, "status")]', $eventNode)),
            'category' => 'Sports',
            'subcategory' => 'Motorsport',
            'scraped_at' => now()->toISOString(),
        ];
    }

    private function extractText(\DOMXPath $xpath, string $query, \DOMElement $context): string
    {
        $nodes = $xpath->query($query, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function extractUrl(\DOMXPath $xpath, string $query, \DOMElement $context): string
    {
        $nodes = $xpath->query($query, $context);
        if ($nodes->length > 0) {
            $href = $nodes->item(0)->getAttribute('href');
            return $href && !filter_var($href, FILTER_VALIDATE_URL) ? $this->baseUrl . $href : $href;
        }
        return '';
    }

    private function parseDate(string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $parsed = \Carbon\Carbon::parse($dateString);
            return $parsed->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Failed to parse date", [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function normalizeAvailability(string $availability): string
    {
        $availability = strtolower(trim($availability));
        
        if (str_contains($availability, 'sold out') || str_contains($availability, 'unavailable')) {
            return 'sold_out';
        }
        
        if (str_contains($availability, 'available') || str_contains($availability, 'on sale')) {
            return 'available';
        }
        
        if (str_contains($availability, 'coming soon') || str_contains($availability, 'pre-sale')) {
            return 'coming_soon';
        }
        
        return 'unknown';
    }

    private function filterResults(array $events, array $criteria): array
    {
        // Apply additional filtering logic here if needed
        return array_filter($events, function($event) use ($criteria) {
            // Basic filtering - can be expanded
            if (!empty($criteria['keyword'])) {
                $keyword = strtolower($criteria['keyword']);
                $eventName = strtolower($event['event_name']);
                if (strpos($eventName, $keyword) === false) {
                    return false;
                }
            }
            
            return true;
        });
    }

    public function test(): array
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8'
                ])
                ->get($this->baseUrl . '/events');

            $duration = (microtime(true) - $startTime) * 1000;

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Successfully connected to Silverstone F1',
                    'response_time_ms' => round($duration, 2),
                    'status_code' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'message' => "HTTP {$response->status()}: Failed to connect to Silverstone F1",
                'status_code' => $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'exception' => get_class($e),
            ];
        }
    }

    private function enforceRateLimit(): void
    {
        $cacheKey = 'silverstone_f1_plugin_last_request';
        $lastRequest = Cache::get($cacheKey);
        
        if ($lastRequest) {
            $timeDiff = microtime(true) - $lastRequest;
            if ($timeDiff < 2) { // 2 second rate limit
                usleep((2 - $timeDiff) * 1000000);
            }
        }
        
        Cache::put($cacheKey, microtime(true), 60);
    }
}
