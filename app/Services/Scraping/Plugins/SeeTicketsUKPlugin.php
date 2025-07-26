<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\ScraperPluginInterface;
use App\Services\ProxyRotationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class SeeTicketsUKPlugin implements ScraperPluginInterface
{
    private $enabled = true;
    private $config = [];
    private $proxyService;
    private $httpClient;
    
    private $baseUrl = 'https://www.seetickets.com';
    private $searchEndpoint = '/search';
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    public function __construct(?ProxyRotationService $proxyService = null)
    {
        $this->proxyService = $proxyService;
        $this->initializeHttpClient();
    }
    
    private function initializeHttpClient(): void
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-GB,en;q=0.9',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache'
            ]
        ]);
    }

    public function getInfo(): array
    {
        return [
            'name' => 'See Tickets UK',
            'description' => 'Popular UK ticketing platform for sports, concerts and events',
            'version' => '1.0.0',
            'platform' => 'seetickets_uk',
            'capabilities' => [
                'uk_events',
                'sports_tickets',
                'concert_tickets',
                'theater_shows',
                'festival_tickets',
                'comedy_shows'
            ],
            'rate_limit' => '1 request per 2 seconds',
            'supported_criteria' => [
                'keyword', 'location', 'date_range', 'category', 'venue'
            ],
            'coverage' => 'UK-wide events and venues'
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
        Log::info('See Tickets UK plugin enabled');
    }

    public function disable(): void
    {
        $this->enabled = false;
        Log::info('See Tickets UK plugin disabled');
    }

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        
        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }
        
        if (isset($config['user_agent'])) {
            $this->userAgent = $config['user_agent'];
            $this->initializeHttpClient();
        }
        
        Log::info('See Tickets UK plugin configured', ['config' => $config]);
    }

    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new \Exception('See Tickets UK plugin is disabled');
        }

        Log::info('Starting See Tickets UK scraping', $criteria);
        
        try {
            $searchUrl = $this->buildSearchUrl($criteria);
            $this->enforceRateLimit();
            $response = $this->makeRequest($searchUrl);
            $events = $this->parseSearchResults($response);
            $filteredEvents = $this->filterResults($events, $criteria);
            
            Log::info('See Tickets UK scraping completed', [
                'url' => $searchUrl,
                'results_found' => count($filteredEvents)
            ]);
            
            return $filteredEvents;
            
        } catch (\Exception $e) {
            Log::error('See Tickets UK scraping failed', [
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
        
        if (!empty($criteria['location'])) {
            $params['location'] = urlencode($criteria['location']);
        }
        
        if (!empty($criteria['category'])) {
            $params['category'] = $this->mapCategoryToSeeTickets($criteria['category']);
        }
        
        $queryString = http_build_query($params);
        return $this->baseUrl . $this->searchEndpoint . '?' . $queryString;
    }
    
    private function mapCategoryToSeeTickets(string $category): string
    {
        $mapping = [
            'music' => 'music',
            'sports' => 'sport',
            'theater' => 'theatre',
            'comedy' => 'comedy',
            'festival' => 'festivals'
        ];
        
        return $mapping[strtolower($category)] ?? 'all';
    }
    
    private function parseSearchResults(string $html): array
    {
        $events = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $eventNodes = $xpath->query('//div[contains(@class, "event")] | //div[contains(@class, "listing")] | //article[contains(@class, "event-card")]');
        
        foreach ($eventNodes as $eventNode) {
            try {
                $event = [
                    'platform' => 'seetickets_uk',
                    'event_name' => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "title")] | .//*[contains(@class, "name")]', $eventNode),
                    'venue' => $this->extractText($xpath, './/*[contains(@class, "venue")] | .//*[contains(@class, "location")]', $eventNode),
                    'event_date' => $this->extractAndParseDate($xpath, './/*[contains(@class, "date")] | .//*[contains(@class, "time")]', $eventNode),
                    'price_min' => $this->extractPrice($xpath, './/*[contains(@class, "price")] | .//*[contains(text(), "£")]', $eventNode),
                    'price_max' => $this->extractPrice($xpath, './/*[contains(@class, "price-max")] | .//*[contains(@class, "price-high")]', $eventNode),
                    'url' => $this->extractUrl($xpath, './/a[contains(@href, "/event/") or contains(@href, "/show/")]', $eventNode),
                    'availability_status' => $this->extractAvailabilityStatus($xpath, $eventNode),
                    'description' => $this->buildDescription($xpath, $eventNode),
                    'category' => $this->extractCategory($xpath, $eventNode),
                    'last_checked' => now(),
                    'scraped_at' => now()->toISOString()
                ];
                
                if (!empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse See Tickets UK event', ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return $events;
    }
    
    private function buildDescription(\DOMXPath $xpath, \DOMElement $eventNode): string
    {
        $parts = [];
        
        $description = $this->extractText($xpath, './/*[contains(@class, "description")] | .//*[contains(@class, "summary")]', $eventNode);
        if (!empty($description)) {
            $parts[] = $description;
        }
        
        // Add See Tickets UK specific features
        $earlyBird = $this->extractText($xpath, './/*[contains(text(), "Early Bird") or contains(text(), "Discount")]', $eventNode);
        if (!empty($earlyBird)) {
            $parts[] = "🎫 Special offers available";
        }
        
        $vip = $this->extractText($xpath, './/*[contains(text(), "VIP") or contains(text(), "Premium")]', $eventNode);
        if (!empty($vip)) {
            $parts[] = "⭐ VIP/Premium packages available";
        }
        
        if (empty($parts)) {
            $parts[] = "🎪 See Tickets UK - Your ticket to live events";
        }
        
        return implode("\n", $parts);
    }
    
    private function extractCategory(\DOMXPath $xpath, \DOMElement $eventNode): string
    {
        $categoryIndicators = [
            './/*[contains(text(), "Football") or contains(text(), "Premier League")]' => 'Sports',
            './/*[contains(text(), "Concert") or contains(text(), "Music")]' => 'Music',
            './/*[contains(text(), "Theatre") or contains(text(), "Musical")]' => 'Theatre',
            './/*[contains(text(), "Comedy") or contains(text(), "Stand-up")]' => 'Comedy',
            './/*[contains(text(), "Festival")]' => 'Festival'
        ];
        
        foreach ($categoryIndicators as $selector => $category) {
            $nodes = $xpath->query($selector, $eventNode);
            if ($nodes->length > 0) {
                return $category;
            }
        }
        
        return 'Event';
    }
    
    private function extractAvailabilityStatus(\DOMXPath $xpath, \DOMElement $eventNode): string
    {
        $statusIndicators = [
            './/*[contains(text(), "Sold Out") or contains(text(), "SOLD OUT")]' => 'sold_out',
            './/*[contains(text(), "Few left") or contains(text(), "Limited")]' => 'low_inventory',
            './/*[contains(text(), "Available") or contains(text(), "Buy Now")]' => 'available',
            './/*[contains(text(), "Pre-sale") or contains(text(), "Coming Soon")]' => 'not_on_sale',
            './/*[contains(@class, "price")]' => 'available'
        ];
        
        foreach ($statusIndicators as $selector => $status) {
            $nodes = $xpath->query($selector, $eventNode);
            if ($nodes->length > 0) {
                return $status;
            }
        }
        
        return 'unknown';
    }
    
    private function extractText(\DOMXPath $xpath, string $selector, \DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }
    
    private function extractUrl(\DOMXPath $xpath, string $selector, \DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        if ($nodes->length > 0) {
            $href = $nodes->item(0)->getAttribute('href');
            return strpos($href, 'http') === 0 ? $href : $this->baseUrl . $href;
        }
        return '';
    }
    
    private function extractPrice(\DOMXPath $xpath, string $selector, \DOMElement $context): ?float
    {
        $priceText = $this->extractText($xpath, $selector, $context);
        
        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }
        
        return null;
    }
    
    private function extractAndParseDate(\DOMXPath $xpath, string $selector, \DOMElement $context): ?string
    {
        $dateText = $this->extractText($xpath, $selector, $context);
        
        if (empty($dateText)) {
            return null;
        }
        
        try {
            // Clean up common date patterns
            $dateText = preg_replace('/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s*/i', '', $dateText);
            $dateText = trim($dateText);
            
            $date = Carbon::parse($dateText);
            return $date->toISOString();
        } catch (\Exception $e) {
            Log::warning('Failed to parse See Tickets UK date', ['date_text' => $dateText]);
            return null;
        }
    }
    
    private function filterResults(array $events, array $criteria): array
    {
        $filtered = $events;
        
        // Filter by price range
        if (!empty($criteria['min_price'])) {
            $filtered = array_filter($filtered, function($event) use ($criteria) {
                return empty($event['price_min']) || $event['price_min'] >= $criteria['min_price'];
            });
        }
        
        if (!empty($criteria['max_price'])) {
            $filtered = array_filter($filtered, function($event) use ($criteria) {
                return empty($event['price_max']) || $event['price_max'] <= $criteria['max_price'];
            });
        }
        
        // Filter by date range
        if (!empty($criteria['date_from'])) {
            $fromDate = Carbon::parse($criteria['date_from']);
            $filtered = array_filter($filtered, function($event) use ($fromDate) {
                if (empty($event['event_date'])) return true;
                $eventDate = Carbon::parse($event['event_date']);
                return $eventDate->gte($fromDate);
            });
        }
        
        if (!empty($criteria['date_to'])) {
            $toDate = Carbon::parse($criteria['date_to']);
            $filtered = array_filter($filtered, function($event) use ($toDate) {
                if (empty($event['event_date'])) return true;
                $eventDate = Carbon::parse($event['event_date']);
                return $eventDate->lte($toDate);
            });
        }
        
        // Limit results
        $maxResults = $criteria['max_results'] ?? 50;
        return array_slice(array_values($filtered), 0, $maxResults);
    }
    
    private function enforceRateLimit(): void
    {
        $lastRequest = Cache::get('seetickets_uk_last_request', 0);
        $timeSinceLastRequest = microtime(true) - $lastRequest;
        
        if ($timeSinceLastRequest < 2) {
            $sleepTime = 2 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }
        
        Cache::put('seetickets_uk_last_request', microtime(true), 60);
    }
    
    private function makeRequest(string $url): string
    {
        try {
            $options = [];
            
            if ($this->proxyService) {
                $proxy = $this->proxyService->getNextProxy();
                if ($proxy) {
                    $options['proxy'] = $proxy;
                    Log::debug('Using proxy for See Tickets UK request', ['proxy' => $proxy]);
                }
            }
            
            $response = $this->httpClient->get($url, $options);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP ' . $response->getStatusCode() . ' error');
            }
            
            return $response->getBody()->getContents();
            
        } catch (RequestException $e) {
            Log::error('See Tickets UK HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to fetch See Tickets UK page: ' . $e->getMessage());
        }
    }

    public function test(): array
    {
        try {
            Log::info('Testing See Tickets UK plugin');
            
            $testCriteria = [
                'keyword' => 'football',
                'max_results' => 1
            ];
            
            $results = $this->scrape($testCriteria);
            
            return [
                'status' => 'success',
                'message' => 'See Tickets UK plugin test successful',
                'test_results' => count($results),
                'sample_data' => !empty($results) ? $results[0] : null
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'See Tickets UK plugin test failed: ' . $e->getMessage()
            ];
        }
    }
}
