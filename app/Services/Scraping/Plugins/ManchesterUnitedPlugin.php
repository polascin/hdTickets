<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\ScraperPluginInterface;
use App\Services\ProxyRotationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class ManchesterUnitedPlugin implements ScraperPluginInterface
{
    private $enabled = true;
    private $config = [];
    private $proxyService;
    private $httpClient;
    
    // Manchester United specific configuration
    private $baseUrl = 'https://www.manutd.com';
    private $ticketsEndpoint = '/tickets-and-hospitality';
    private $fixturesEndpoint = '/fixtures';
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    public function __construct(ProxyRotationService $proxyService = null)
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
                'Pragma' => 'no-cache',
                'DNT' => '1'
            ]
        ]);
    }

    public function getInfo(): array
    {
        return [
            'name' => 'Manchester United',
            'description' => 'Official Manchester United ticket scraper for Old Trafford matches',
            'version' => '1.0.0',
            'platform' => 'official',
            'venue' => 'Old Trafford',
            'capabilities' => [
                'premier_league_matches',
                'champions_league_matches', 
                'cup_matches',
                'hospitality_packages',
                'season_tickets'
            ],
            'rate_limit' => '1 request per 3 seconds',
            'supported_criteria' => [
                'match_type', 'date_range', 'competition', 'ticket_type'
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
        Log::info('Manchester United plugin enabled');
    }

    public function disable(): void
    {
        $this->enabled = false;
        Log::info('Manchester United plugin disabled');
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
        
        Log::info('Manchester United plugin configured', ['config' => $config]);
    }

    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new \Exception('Manchester United plugin is disabled');
        }

        Log::info('Starting Manchester United ticket scraping', $criteria);
        
        try {
            // Get fixture data first
            $fixtures = $this->scrapeFixtures($criteria);
            
            // Get ticket availability for each fixture
            $ticketData = [];
            foreach ($fixtures as $fixture) {
                $this->enforceRateLimit();
                $tickets = $this->scrapeTicketAvailability($fixture);
                if (!empty($tickets)) {
                    $ticketData = array_merge($ticketData, $tickets);
                }
            }
            
            Log::info('Manchester United scraping completed', [
                'fixtures_found' => count($fixtures),
                'tickets_found' => count($ticketData)
            ]);
            
            return $ticketData;
            
        } catch (\Exception $e) {
            Log::error('Manchester United scraping failed', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    private function scrapeFixtures(array $criteria): array
    {
        $url = $this->baseUrl . $this->fixturesEndpoint;
        $html = $this->makeRequest($url);
        
        $fixtures = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        // Manchester United fixture selectors (these would need to be updated based on actual website structure)
        $fixtureNodes = $xpath->query('//div[contains(@class, "fixture-item")] | //div[contains(@class, "match-item")]');
        
        foreach ($fixtureNodes as $fixtureNode) {
            try {
                $fixture = $this->extractFixtureData($xpath, $fixtureNode);
                
                // Filter by criteria
                if ($this->matchesCriteria($fixture, $criteria)) {
                    $fixtures[] = $fixture;
                }
                
            } catch (\Exception $e) {
                Log::warning('Failed to parse Manchester United fixture', ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return $fixtures;
    }
    
    private function extractFixtureData(\DOMXPath $xpath, \DOMElement $fixtureNode): array
    {
        return [
            'id' => $this->extractAttribute($xpath, './/@data-fixture-id | .//@id', $fixtureNode),
            'opponent' => $this->extractText($xpath, './/*[contains(@class, "opponent")] | .//*[contains(@class, "team-away")]', $fixtureNode),
            'date' => $this->extractAndParseFixtureDate($xpath, './/*[contains(@class, "date")] | .//*[contains(@class, "kickoff")]', $fixtureNode),
            'competition' => $this->extractText($xpath, './/*[contains(@class, "competition")] | .//*[contains(@class, "tournament")]', $fixtureNode),
            'venue' => $this->extractText($xpath, './/*[contains(@class, "venue")]', $fixtureNode) ?: 'Old Trafford',
            'status' => $this->extractText($xpath, './/*[contains(@class, "status")]', $fixtureNode),
            'ticket_url' => $this->extractTicketUrl($xpath, fixtureNode)
        ];
    }
    
    private function extractTicketUrl(\DOMXPath $xpath, \DOMElement $fixtureNode): string
    {
        $ticketLinks = $xpath->query('.//a[contains(@href, "ticket") or contains(text(), "Tickets") or contains(@class, "ticket")]', $fixtureNode);
        
        if ($ticketLinks->length > 0) {
            $href = $ticketLinks->item(0)->getAttribute('href');
            return strpos($href, 'http') === 0 ? $href : $this->baseUrl . $href;
        }
        
        return '';
    }
    
    private function scrapeTicketAvailability(array $fixture): array
    {
        if (empty($fixture['ticket_url'])) {
            return [];
        }
        
        try {
            $html = $this->makeRequest($fixture['ticket_url']);
            return $this->parseTicketAvailability($html, $fixture);
            
        } catch (\Exception $e) {
            Log::warning('Failed to scrape ticket availability for fixture', [
                'fixture_id' => $fixture['id'],
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    private function parseTicketAvailability(string $html, array $fixture): array
    {
        $tickets = [];
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        // Ticket availability selectors
        $ticketSections = $xpath->query('//div[contains(@class, "ticket-section")] | //div[contains(@class, "seating-area")]');
        
        foreach ($ticketSections as $sectionNode) {
            try {
                $ticketInfo = $this->extractTicketInfo($xpath, $sectionNode, $fixture);
                if (!empty($ticketInfo)) {
                    $tickets[] = $ticketInfo;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse ticket section', ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        return $tickets;
    }
    
    private function extractTicketInfo(\DOMXPath $xpath, \DOMElement $sectionNode, array $fixture): array
    {
        $availability = $this->extractText($xpath, './/*[contains(@class, "availability")] | .//*[contains(@class, "status")]', $sectionNode);
        
        return [
            'platform' => 'official',
            'source' => 'manchester_united',
            'event_name' => 'Manchester United vs ' . ($fixture['opponent'] ?? 'TBC'),
            'venue' => $fixture['venue'] ?? 'Old Trafford',
            'date' => $fixture['date'],
            'competition' => $fixture['competition'] ?? 'Premier League',
            'section' => $this->extractText($xpath, './/*[contains(@class, "section")] | .//*[contains(@class, "area-name")]', $sectionNode),
            'price_min' => $this->extractPrice($xpath, './/*[contains(@class, "price-from")] | .//*[contains(@class, "min-price")]', $sectionNode),
            'price_max' => $this->extractPrice($xpath, './/*[contains(@class, "price-to")] | .//*[contains(@class, "max-price")]', $sectionNode),
            'availability_status' => $this->mapAvailabilityStatus($availability),
            'ticket_type' => $this->extractText($xpath, './/*[contains(@class, "ticket-type")]', $sectionNode) ?: 'General Admission',
            'url' => $fixture['ticket_url'],
            'fixture_id' => $fixture['id'] ?? null,
            'scraped_at' => now()->toISOString()
        ];
    }
    
    private function mapAvailabilityStatus(string $availability): string
    {
        $availability = strtolower($availability);
        
        if (strpos($availability, 'sold out') !== false || strpos($availability, 'unavailable') !== false) {
            return 'sold_out';
        }
        
        if (strpos($availability, 'limited') !== false || strpos($availability, 'few left') !== false) {
            return 'low_inventory';
        }
        
        if (strpos($availability, 'available') !== false || strpos($availability, 'on sale') !== false) {
            return 'available';
        }
        
        if (strpos($availability, 'not on sale') !== false || strpos($availability, 'coming soon') !== false) {
            return 'not_on_sale';  
        }
        
        return 'unknown';
    }
    
    private function matchesCriteria(array $fixture, array $criteria): bool
    {
        // Filter by date range
        if (!empty($criteria['date_from']) && !empty($fixture['date'])) {
            $fixtureDate = Carbon::parse($fixture['date']);
            $fromDate = Carbon::parse($criteria['date_from']);
            if ($fixtureDate->lt($fromDate)) {
                return false;
            }
        }
        
        if (!empty($criteria['date_to']) && !empty($fixture['date'])) {
            $fixtureDate = Carbon::parse($fixture['date']);
            $toDate = Carbon::parse($criteria['date_to']);
            if ($fixtureDate->gt($toDate)) {
                return false;
            }
        }
        
        // Filter by competition
        if (!empty($criteria['competition'])) {
            $competition = strtolower($fixture['competition'] ?? '');
            $targetCompetition = strtolower($criteria['competition']);
            if (strpos($competition, $targetCompetition) === false) {
                return false;
            }
        }
        
        return true;
    }
    
    private function extractText(\DOMXPath $xpath, string $selector, \DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }
    
    private function extractAttribute(\DOMXPath $xpath, string $selector, \DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : '';
    }
    
    private function extractPrice(\DOMXPath $xpath, string $selector, \DOMElement $context): ?float
    {
        $priceText = $this->extractText($xpath, $selector, $context);
        
        // Handle British pound prices
        if (preg_match('/£?(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }
        
        return null;
    }
    
    private function extractAndParseFixtureDate(\DOMXPath $xpath, string $selector, \DOMElement $context): ?string
    {
        $dateText = $this->extractText($xpath, $selector, $context);
        
        if (empty($dateText)) {
            return null;
        }
        
        try {
            // Try to parse various date formats used by Manchester United
            $date = Carbon::parse($dateText);
            return $date->toISOString();
        } catch (\Exception $e) {
            Log::warning('Failed to parse Manchester United fixture date', ['date_text' => $dateText]);
            return null;
        }
    }
    
    private function enforceRateLimit(): void
    {
        $lastRequest = Cache::get('manchester_united_last_request', 0);
        $timeSinceLastRequest = microtime(true) - $lastRequest;
        
        if ($timeSinceLastRequest < 3) {
            $sleepTime = 3 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }
        
        Cache::put('manchester_united_last_request', microtime(true), 60);
    }
    
    private function makeRequest(string $url): string
    {
        try {
            $options = [];
            
            // Use proxy if available
            if ($this->proxyService) {
                $proxy = $this->proxyService->getNextProxy();
                if ($proxy) {
                    $options['proxy'] = $proxy;
                    Log::debug('Using proxy for Manchester United request', ['proxy' => $proxy]);
                }
            }
            
            $response = $this->httpClient->get($url, $options);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP ' . $response->getStatusCode() . ' error');
            }
            
            return $response->getBody()->getContents();
            
        } catch (RequestException $e) {
            Log::error('Manchester United HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to fetch Manchester United page: ' . $e->getMessage());
        }
    }

    public function test(): array
    {
        try {
            Log::info('Testing Manchester United plugin');
            
            $testCriteria = [
                'date_from' => now()->toDateString(),
                'date_to' => now()->addMonths(3)->toDateString()
            ];
            
            $results = $this->scrape($testCriteria);
            
            return [
                'status' => 'success',
                'message' => 'Manchester United plugin test successful',
                'test_results' => count($results),
                'sample_data' => !empty($results) ? $results[0] : null
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Manchester United plugin test failed: ' . $e->getMessage()
            ];
        }
    }
}
