<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\ProxyRotationService;
use App\Services\Scraping\ScraperPluginInterface;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;

class LiverpoolFCPlugin implements ScraperPluginInterface
{
    private $enabled = TRUE;

    private $config = [];

    private $proxyService;

    private $httpClient;

    // Liverpool FC specific configuration
    private $baseUrl = 'https://www.liverpoolfc.com';

    private $ticketsEndpoint = '/tickets';

    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function __construct(?ProxyRotationService $proxyService = NULL)
    {
        $this->proxyService = $proxyService;
        $this->initializeHttpClient();
    }

    /**
     * Get  info
     */
    public function getInfo(): array
    {
        return [
            'name'         => 'Liverpool FC',
            'description'  => 'Official Liverpool FC tickets and hospitality',
            'version'      => '1.0.0',
            'platform'     => 'liverpoolfc',
            'capabilities' => [
                'premier_league',
                'champions_league',
                'cup_matches',
                'hospitality_packages',
                'season_tickets',
                'member_access',
            ],
            'rate_limit'         => '1 request per 2 seconds',
            'supported_criteria' => [
                'keyword', 'date_range', 'competition', 'match_type',
            ],
            'venue'        => 'Anfield Stadium',
            'competitions' => [
                'Premier League', 'Champions League', 'FA Cup', 'Carabao Cup',
            ],
        ];
    }

    /**
     * Check if  enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable
     */
    public function enable(): void
    {
        $this->enabled = TRUE;
        Log::info('Liverpool FC plugin enabled');
    }

    /**
     * Disable
     */
    public function disable(): void
    {
        $this->enabled = FALSE;
        Log::info('Liverpool FC plugin disabled');
    }

    /**
     * Configure
     */
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

        Log::info('Liverpool FC plugin configured', ['config' => $config]);
    }

    /**
     * Scrape
     */
    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception('Liverpool FC plugin is disabled');
        }

        Log::info('Starting Liverpool FC scraping', $criteria);

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

            Log::info('Liverpool FC scraping completed', [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error('Liverpool FC scraping failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Test
     */
    public function test(): array
    {
        try {
            Log::info('Testing Liverpool FC plugin');

            $testCriteria = [
                'keyword'     => 'premier league',
                'max_results' => 1,
            ];

            $results = $this->scrape($testCriteria);

            return [
                'status'       => 'success',
                'message'      => 'Liverpool FC plugin test successful',
                'test_results' => count($results),
                'sample_data'  => ! empty($results) ? $results[0] : NULL,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Liverpool FC plugin test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * InitializeHttpClient
     */
    private function initializeHttpClient(): void
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'verify'  => FALSE,
            'headers' => [
                'User-Agent'      => $this->userAgent,
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-GB,en;q=0.9',
                'Cache-Control'   => 'no-cache',
                'Pragma'          => 'no-cache',
            ],
        ]);
    }

    /**
     * BuildSearchUrl
     */
    private function buildSearchUrl(array $criteria): string
    {
        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['q'] = urlencode($criteria['keyword']);
        }

        if (! empty($criteria['competition'])) {
            $params['competition'] = $this->mapCompetition($criteria['competition']);
        }

        $queryString = http_build_query($params);

        return $this->baseUrl . $this->ticketsEndpoint . '?' . $queryString;
    }

    /**
     * MapCompetition
     */
    private function mapCompetition(string $competition): string
    {
        $mapping = [
            'premier league'   => 'premier-league',
            'champions league' => 'champions-league',
            'fa cup'           => 'fa-cup',
            'carabao cup'      => 'carabao-cup',
        ];

        return $mapping[strtolower($competition)] ?? 'all';
    }

    /**
     * ParseSearchResults
     */
    private function parseSearchResults(string $html): array
    {
        $events = [];
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Liverpool FC event selectors
        $eventNodes = $xpath->query('//div[contains(@class, "fixture")] | //div[contains(@class, "match")] | //article[contains(@class, "ticket-listing")]');

        foreach ($eventNodes as $eventNode) {
            try {
                $event = $this->extractEventData($xpath, $eventNode);
                if (! empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse Liverpool FC event', ['error' => $e->getMessage()]);

                continue;
            }
        }

        return $events;
    }

    /**
     * ExtractEventData
     */
    private function extractEventData(DOMXPath $xpath, DOMElement $eventNode): array
    {
        return [
            'platform'            => 'liverpoolfc',
            'event_name'          => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "match-name")] | .//*[contains(@class, "fixture-title")]', $eventNode),
            'venue'               => 'Anfield Stadium',
            'event_date'          => $this->extractAndParseDate($xpath, './/*[contains(@class, "date")] | .//*[contains(@class, "match-date")]', $eventNode),
            'price_min'           => $this->extractPrice($xpath, './/*[contains(@class, "price")] | .//*[contains(text(), "£")]', $eventNode),
            'price_max'           => $this->extractPrice($xpath, './/*[contains(@class, "price-max")] | .//*[contains(@class, "price-high")]', $eventNode),
            'url'                 => $this->extractUrl($xpath, './/a[contains(@href, "/tickets")] | .//a[contains(@class, "ticket-link")]', $eventNode),
            'availability_status' => $this->extractAvailabilityStatus($xpath, $eventNode),
            'description'         => $this->buildDescription($xpath, $eventNode),
            'competition'         => $this->extractCompetition($xpath, $eventNode),
            'last_checked'        => now(),
            'scraped_at'          => now()->toISOString(),
        ];
    }

    /**
     * BuildDescription
     */
    private function buildDescription(DOMXPath $xpath, DOMElement $eventNode): string
    {
        $parts = [];

        $description = $this->extractText($xpath, './/*[contains(@class, "description")] | .//*[contains(@class, "match-info")]', $eventNode);
        if (! empty($description)) {
            $parts[] = $description;
        }

        // Add Liverpool FC specific features
        $memberAccess = $this->extractText($xpath, './/*[contains(text(), "Member") or contains(text(), "Season Ticket")]', $eventNode);
        if (! empty($memberAccess)) {
            $parts[] = '🎫 Member/Season Ticket Holder Access';
        }

        $hospitality = $this->extractText($xpath, './/*[contains(text(), "Hospitality") or contains(text(), "VIP")]', $eventNode);
        if (! empty($hospitality)) {
            $parts[] = '🍽️ Hospitality packages available';
        }

        return implode("\n", $parts);
    }

    /**
     * ExtractCompetition
     */
    private function extractCompetition(DOMXPath $xpath, DOMElement $eventNode): string
    {
        $competitionIndicators = [
            './/*[contains(text(), "Premier League")]'   => 'Premier League',
            './/*[contains(text(), "Champions League")]' => 'Champions League',
            './/*[contains(text(), "FA Cup")]'           => 'FA Cup',
            './/*[contains(text(), "Carabao")]'          => 'Carabao Cup',
        ];

        foreach ($competitionIndicators as $selector => $competition) {
            $nodes = $xpath->query($selector, $eventNode);
            if ($nodes->length > 0) {
                return $competition;
            }
        }

        return 'Football Match';
    }

    /**
     * ExtractAvailabilityStatus
     */
    private function extractAvailabilityStatus(DOMXPath $xpath, DOMElement $eventNode): string
    {
        $statusIndicators = [
            './/*[contains(text(), "Sold Out") or contains(text(), "SOLD OUT")]' => 'sold_out',
            './/*[contains(text(), "Few left") or contains(text(), "Limited")]'  => 'low_inventory',
            './/*[contains(text(), "Available") or contains(text(), "Buy Now")]' => 'available',
            './/*[contains(text(), "Members Only")]'                             => 'not_on_sale',
            './/*[contains(@class, "price")]'                                    => 'available',
        ];

        foreach ($statusIndicators as $selector => $status) {
            $nodes = $xpath->query($selector, $eventNode);
            if ($nodes->length > 0) {
                return $status;
            }
        }

        return 'unknown';
    }

    /**
     * ExtractText
     */
    private function extractText(DOMXPath $xpath, string $selector, DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);

        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    /**
     * ExtractUrl
     */
    private function extractUrl(DOMXPath $xpath, string $selector, DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        if ($nodes->length > 0) {
            $href = $nodes->item(0)->getAttribute('href');

            return strpos($href, 'http') === 0 ? $href : $this->baseUrl . $href;
        }

        return '';
    }

    /**
     * ExtractPrice
     */
    private function extractPrice(DOMXPath $xpath, string $selector, DOMElement $context): ?float
    {
        $priceText = $this->extractText($xpath, $selector, $context);

        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * ExtractAndParseDate
     */
    private function extractAndParseDate(DOMXPath $xpath, string $selector, DOMElement $context): ?string
    {
        $dateText = $this->extractText($xpath, $selector, $context);

        if (empty($dateText)) {
            return NULL;
        }

        try {
            $date = Carbon::parse($dateText);

            return $date->toISOString();
        } catch (Exception $e) {
            Log::warning('Failed to parse Liverpool FC date', ['date_text' => $dateText]);

            return NULL;
        }
    }

    /**
     * FilterResults
     */
    private function filterResults(array $events, array $criteria): array
    {
        $filtered = $events;

        // Filter by price range
        if (! empty($criteria['min_price'])) {
            $filtered = array_filter($filtered, function ($event) use ($criteria) {
                return empty($event['price_min']) || $event['price_min'] >= $criteria['min_price'];
            });
        }

        if (! empty($criteria['max_price'])) {
            $filtered = array_filter($filtered, function ($event) use ($criteria) {
                return empty($event['price_max']) || $event['price_max'] <= $criteria['max_price'];
            });
        }

        // Filter by date range
        if (! empty($criteria['date_from'])) {
            $fromDate = Carbon::parse($criteria['date_from']);
            $filtered = array_filter($filtered, function ($event) use ($fromDate) {
                if (empty($event['event_date'])) {
                    return TRUE;
                }
                $eventDate = Carbon::parse($event['event_date']);

                return $eventDate->gte($fromDate);
            });
        }

        if (! empty($criteria['date_to'])) {
            $toDate = Carbon::parse($criteria['date_to']);
            $filtered = array_filter($filtered, function ($event) use ($toDate) {
                if (empty($event['event_date'])) {
                    return TRUE;
                }
                $eventDate = Carbon::parse($event['event_date']);

                return $eventDate->lte($toDate);
            });
        }

        // Limit results
        $maxResults = $criteria['max_results'] ?? 50;

        return array_slice(array_values($filtered), 0, $maxResults);
    }

    /**
     * EnforceRateLimit
     */
    private function enforceRateLimit(): void
    {
        $lastRequest = Cache::get('liverpoolfc_last_request', 0);
        $timeSinceLastRequest = microtime(TRUE) - $lastRequest;

        if ($timeSinceLastRequest < 2) {
            $sleepTime = 2 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }

        Cache::put('liverpoolfc_last_request', microtime(TRUE), 60);
    }

    /**
     * MakeRequest
     */
    private function makeRequest(string $url): string
    {
        try {
            $options = [];

            // Use proxy if available
            if ($this->proxyService) {
                $proxy = $this->proxyService->getNextProxy();
                if ($proxy) {
                    $options['proxy'] = $proxy;
                    Log::debug('Using proxy for Liverpool FC request', ['proxy' => $proxy]);
                }
            }

            $response = $this->httpClient->get($url, $options);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('HTTP ' . $response->getStatusCode() . ' error');
            }

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            Log::error('Liverpool FC HTTP request failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to fetch Liverpool FC page: ' . $e->getMessage());
        }
    }
}
