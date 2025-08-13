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

class ChelseaFCPlugin implements ScraperPluginInterface
{
    private $enabled = TRUE;

    private $config = [];

    private $proxyService;

    private $httpClient;

    private $baseUrl = 'https://www.chelseafc.com';

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
            'name'         => 'Chelsea FC',
            'description'  => 'Official Chelsea FC tickets - Stamford Bridge, Premier League, European competitions',
            'version'      => '1.0.0',
            'platform'     => 'chelsea',
            'capabilities' => [
                'premier_league',
                'champions_league',
                'stamford_bridge',
                'cup_matches',
                'hospitality_packages',
                'member_access',
            ],
            'rate_limit'         => '1 request per 2 seconds',
            'supported_criteria' => [
                'keyword', 'date_range', 'competition', 'match_type',
            ],
            'venue'        => 'Stamford Bridge',
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
        Log::info('Chelsea FC plugin enabled');
    }

    /**
     * Disable
     */
    public function disable(): void
    {
        $this->enabled = FALSE;
        Log::info('Chelsea FC plugin disabled');
    }

    /**
     * Configure
     */
    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        Log::info('Chelsea FC plugin configured', ['config' => $config]);
    }

    /**
     * Scrape
     */
    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception('Chelsea FC plugin is disabled');
        }

        Log::info('Starting Chelsea FC scraping', $criteria);

        try {
            $searchUrl = $this->buildSearchUrl($criteria);
            $this->enforceRateLimit();
            $response = $this->makeRequest($searchUrl);
            $events = $this->parseSearchResults($response);
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info('Chelsea FC scraping completed', [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error('Chelsea FC scraping failed', [
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
            $testCriteria = ['keyword' => 'premier league', 'max_results' => 1];
            $results = $this->scrape($testCriteria);

            return [
                'status'       => 'success',
                'message'      => 'Chelsea FC plugin test successful',
                'test_results' => count($results),
                'sample_data'  => ! empty($results) ? $results[0] : NULL,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Chelsea FC plugin test failed: ' . $e->getMessage(),
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

        $queryString = http_build_query($params);

        return $this->baseUrl . $this->ticketsEndpoint . '?' . $queryString;
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

        $eventNodes = $xpath->query('//div[contains(@class, "fixture")] | //div[contains(@class, "match")] | //article[contains(@class, "ticket-card")]');

        foreach ($eventNodes as $eventNode) {
            try {
                $event = [
                    'platform'            => 'chelsea',
                    'event_name'          => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "match-title")]', $eventNode),
                    'venue'               => 'Stamford Bridge',
                    'event_date'          => $this->extractAndParseDate($xpath, './/*[contains(@class, "date")]', $eventNode),
                    'price_min'           => $this->extractPrice($xpath, './/*[contains(@class, "price")] | .//*[contains(text(), "£")]', $eventNode),
                    'url'                 => $this->extractUrl($xpath, './/a[contains(@href, "/tickets")]', $eventNode),
                    'availability_status' => $this->extractAvailabilityStatus($xpath, $eventNode),
                    'description'         => '🔵 Chelsea FC Official - Stamford Bridge experience',
                    'competition'         => $this->extractCompetition($xpath, $eventNode),
                    'last_checked'        => now(),
                    'scraped_at'          => now()->toISOString(),
                ];

                if (! empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse Chelsea FC event', ['error' => $e->getMessage()]);

                continue;
            }
        }

        return $events;
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
            Log::warning('Failed to parse Chelsea FC date', ['date_text' => $dateText]);

            return NULL;
        }
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
            './/*[contains(text(), "Sold Out")]'     => 'sold_out',
            './/*[contains(text(), "Members Only")]' => 'not_on_sale',
            './/*[contains(text(), "Available")]'    => 'available',
            './/*[contains(@class, "price")]'        => 'available',
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
     * FilterResults
     */
    private function filterResults(array $events, array $criteria): array
    {
        $maxResults = $criteria['max_results'] ?? 50;

        return array_slice(array_values($events), 0, $maxResults);
    }

    /**
     * EnforceRateLimit
     */
    private function enforceRateLimit(): void
    {
        $lastRequest = Cache::get('chelsea_last_request', 0);
        $timeSinceLastRequest = microtime(TRUE) - $lastRequest;

        if ($timeSinceLastRequest < 2) {
            $sleepTime = 2 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }

        Cache::put('chelsea_last_request', microtime(TRUE), 60);
    }

    /**
     * MakeRequest
     */
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
                throw new Exception('HTTP ' . $response->getStatusCode() . ' error');
            }

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            Log::error('Chelsea FC HTTP request failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to fetch Chelsea FC page: ' . $e->getMessage());
        }
    }
}
