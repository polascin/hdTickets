<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\ScraperPluginInterface;
use App\Services\ProxyRotationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class ArsenalFCPlugin implements ScraperPluginInterface
{
    private $enabled = true;
    private $config = [];
    private $proxyService;
    private $httpClient;
    
    private $baseUrl = 'https://www.arsenal.com';
    private $ticketsEndpoint = '/tickets';
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
            'name' => 'Arsenal FC',
            'description' => 'Official Arsenal FC tickets - Emirates Stadium, Premier League, European competitions',
            'version' => '1.0.0',
            'platform' => 'arsenal',
            'capabilities' => [
                'premier_league',
                'champions_league',
                'emirates_stadium',
                'cup_matches',
                'hospitality_packages',
                'member_access'
            ],
            'rate_limit' => '1 request per 2 seconds',
            'supported_criteria' => [
                'keyword', 'date_range', 'competition', 'match_type'
            ],
            'venue' => 'Emirates Stadium',
            'competitions' => [
                'Premier League', 'Champions League', 'FA Cup', 'Carabao Cup'
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
        Log::info('Arsenal FC plugin enabled');
    }

    public function disable(): void
    {
        $this->enabled = false;
        Log::info('Arsenal FC plugin disabled');
    }

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        Log::info('Arsenal FC plugin configured', ['config' => $config]);
    }

    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new \Exception('Arsenal FC plugin is disabled');
        }

        Log::info('Starting Arsenal FC scraping', $criteria);
        
        try {
            $searchUrl = $this->buildSearchUrl($criteria);
            $this->enforceRateLimit();
            $response = $this->makeRequest($searchUrl);
            $events = $this->parseSearchResults($response);
            $filteredEvents = $this->filterResults($events, $criteria);
            
            Log::info('Arsenal FC scraping completed', [
                'url' => $searchUrl,
                'results_found' => count($filteredEvents)
            ]);
            
            return $filteredEvents;
            
        } catch (\Exception $e) {
            Log::error('Arsenal FC scraping failed', [
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
        
        $queryString = http_build_query($params);
        return $this->baseUrl . $this->ticketsEndpoint . '?' . $queryString;
    }
    
    private function parseSearchResults(string $html): array
    {
        $events = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $eventNodes = $xpath->query('//div[contains(@class, "fixture")] | //div[contains(@class, "match")] | //article[contains(@class, "ticket-card")]');
        
        foreach ($eventNodes as $eventNode) {
            try {
                $event = [
                    'platform' => 'arsenal',
                    'event_name' => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "match-title")]', $eventNode),
                    'venue' => 'Emirates Stadium',
                    'event_date' => $this->extractAndParseDate($xpath, './/*[contains(@class, "date")]', $eventNode),
                    'price_min' => $this->extractPrice($xpath, './/*[contains(@class, "price")] | .//*[contains(text(), "£")]', $eventNode),
                    'url' => $this->extractUrl($xpath, './/a[contains(@href, "/tickets")]', $eventNode),
                    'availability_status' => $this->extractAvailabilityStatus($xpath, $eventNode),
                    'description' => '🔴⚪ Arsenal FC Official - Emirates Stadium experience',
                    'competition' => $this->extractCompetition($xpath, $eventNode),
                    'last_checked' => now(),
                    'scraped_at' => now()->toISOString()
                ];
                
                if (!empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse Arsenal FC event', ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return $events;
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
            $date = Carbon::parse($dateText);
            return $date->toISOString();
        } catch (\Exception $e) {
            Log::warning('Failed to parse Arsenal FC date', ['date_text' => $dateText]);
            return null;
        }
    }
    
    private function extractCompetition(\DOMXPath $xpath, \DOMElement $eventNode): string
    {
        $competitionIndicators = [
            './/*[contains(text(), "Premier League")]' => 'Premier League',
            './/*[contains(text(), "Champions League")]' => 'Champions League',
            './/*[contains(text(), "FA Cup")]' => 'FA Cup',
            './/*[contains(text(), "Carabao")]' => 'Carabao Cup'
        ];
        
        foreach ($competitionIndicators as $selector => $competition) {
            $nodes = $xpath->query($selector, $eventNode);
            if ($nodes->length > 0) {
                return $competition;
            }
        }
        
        return 'Football Match';
    }
    
    private function extractAvailabilityStatus(\DOMXPath $xpath, \DOMElement $eventNode): string
    {
        $statusIndicators = [
            './/*[contains(text(), "Sold Out")]' => 'sold_out',
            './/*[contains(text(), "Members Only")]' => 'not_on_sale',
            './/*[contains(text(), "Available")]' => 'available',
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
    
    private function filterResults(array $events, array $criteria): array
    {
        $maxResults = $criteria['max_results'] ?? 50;
        return array_slice(array_values($events), 0, $maxResults);
    }
    
    private function enforceRateLimit(): void
    {
        $lastRequest = Cache::get('arsenal_last_request', 0);
        $timeSinceLastRequest = microtime(true) - $lastRequest;
        
        if ($timeSinceLastRequest < 2) {
            $sleepTime = 2 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }
        
        Cache::put('arsenal_last_request', microtime(true), 60);
    }
    
    private function makeRequest(string $url): string
    {
        try {
            $options = [];
            
            if ($this->proxyService) {
                $proxy = $this->proxyService->getNextProxy();
                if ($proxy) {
                    $options['proxy'] = $proxy;
                }
            }
            
            $response = $this->httpClient->get($url, $options);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP ' . $response->getStatusCode() . ' error');
            }
            
            return $response->getBody()->getContents();
            
        } catch (RequestException $e) {
            Log::error('Arsenal FC HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to fetch Arsenal FC page: ' . $e->getMessage());
        }
    }

    public function test(): array
    {
        try {
            $testCriteria = ['keyword' => 'premier league', 'max_results' => 1];
            $results = $this->scrape($testCriteria);
            
            return [
                'status' => 'success',
                'message' => 'Arsenal FC plugin test successful',
                'test_results' => count($results),
                'sample_data' => !empty($results) ? $results[0] : null
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Arsenal FC plugin test failed: ' . $e->getMessage()
            ];
        }
    }
}
