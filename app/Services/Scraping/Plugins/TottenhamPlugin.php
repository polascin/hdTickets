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

class TottenhamPlugin implements ScraperPluginInterface
{
    private $enabled = TRUE;

    private $config = [];

    private $proxyService;

    private $httpClient;

    private $baseUrl = 'https://www.tottenhamhotspur.com';

    private $ticketsEndpoint = '/tickets';

    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function __construct(?ProxyRotationService $proxyService = NULL)
    {
        $this->proxyService = $proxyService;
        $this->initializeHttpClient();
    }

    public function getInfo(): array
    {
        return [
            'name'               => 'Tottenham Hotspur',
            'description'        => 'Official Tottenham Hotspur tickets - New stadium, Premier League, European competitions',
            'version'            => '1.0.0',
            'platform'           => 'tottenham',
            'capabilities'       => ['official_tickets', 'premium_seating', 'hospitality_packages'],
            'rate_limit'         => '1 request per 2 seconds',
            'supported_criteria' => ['keyword', 'date_range'],
            'venue'              => 'Tottenham Hotspur Stadium',
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = TRUE;
        Log::info('Tottenham Hotspur plugin enabled');
    }

    public function disable(): void
    {
        $this->enabled = FALSE;
        Log::info('Tottenham Hotspur plugin disabled');
    }

    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        Log::info('Tottenham Hotspur plugin configured', ['config' => $config]);
    }

    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception('Tottenham Hotspur plugin is disabled');
        }

        Log::info('Starting Tottenham Hotspur scraping', $criteria);

        try {
            $searchUrl = $this->buildSearchUrl($criteria);
            $this->enforceRateLimit();
            $response = $this->makeRequest($searchUrl);
            $events = $this->parseSearchResults($response);
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info('Tottenham Hotspur scraping completed', [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error('Tottenham Hotspur scraping failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function test(): array
    {
        try {
            $testCriteria = ['keyword' => 'tickets', 'max_results' => 1];
            $results = $this->scrape($testCriteria);

            return [
                'status'       => 'success',
                'message'      => 'Tottenham Hotspur plugin test successful',
                'test_results' => count($results),
                'sample_data'  => ! empty($results) ? $results[0] : NULL,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Tottenham Hotspur plugin test failed: ' . $e->getMessage(),
            ];
        }
    }

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

    private function buildSearchUrl(array $criteria): string
    {
        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['q'] = urlencode($criteria['keyword']);
        }

        $queryString = http_build_query($params);

        return $this->baseUrl . $this->ticketsEndpoint . '?' . $queryString;
    }

    private function parseSearchResults(string $html): array
    {
        $events = [];
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $eventNodes = $xpath->query('//div[contains(@class, "event")] | //div[contains(@class, "fixture")] | //article[contains(@class, "ticket-card")]');

        foreach ($eventNodes as $eventNode) {
            try {
                $event = [
                    'platform'            => 'tottenham',
                    'event_name'          => $this->extractText($xpath, './/h3 | .//h2 | .//*[contains(@class, "title")]', $eventNode),
                    'venue'               => 'Tottenham Hotspur Stadium',
                    'event_date'          => $this->extractAndParseDate($xpath, './/*[contains(@class, "date")]', $eventNode),
                    'price_min'           => $this->extractPrice($xpath, './/*[contains(@class, "price")] | .//*[contains(text(), "£")]', $eventNode),
                    'url'                 => $this->extractUrl($xpath, './/a[contains(@href, "/tickets")]', $eventNode),
                    'availability_status' => $this->extractAvailabilityStatus($xpath, $eventNode),
                    'description'         => '⚪🔵 Tottenham Hotspur Official - Tottenham Hotspur Stadium experience',
                    'last_checked'        => now(),
                    'scraped_at'          => now()->toISOString(),
                ];

                if (! empty($event['event_name'])) {
                    $events[] = $event;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse Tottenham Hotspur event', ['error' => $e->getMessage()]);

                continue;
            }
        }

        return $events;
    }

    private function extractText(DOMXPath $xpath, string $selector, DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);

        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }

    private function extractUrl(DOMXPath $xpath, string $selector, DOMElement $context): string
    {
        $nodes = $xpath->query($selector, $context);
        if ($nodes->length > 0) {
            $href = $nodes->item(0)->getAttribute('href');

            return strpos($href, 'http') === 0 ? $href : $this->baseUrl . $href;
        }

        return '';
    }

    private function extractPrice(DOMXPath $xpath, string $selector, DOMElement $context): ?float
    {
        $priceText = $this->extractText($xpath, $selector, $context);

        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

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
            Log::warning('Failed to parse Tottenham Hotspur date', ['date_text' => $dateText]);

            return NULL;
        }
    }

    private function extractAvailabilityStatus(DOMXPath $xpath, DOMElement $eventNode): string
    {
        $statusIndicators = [
            './/*[contains(text(), "Sold Out")]'  => 'sold_out',
            './/*[contains(text(), "Available")]' => 'available',
            './/*[contains(@class, "price")]'     => 'available',
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
        $lastRequest = Cache::get('tottenham_last_request', 0);
        $timeSinceLastRequest = microtime(TRUE) - $lastRequest;

        if ($timeSinceLastRequest < 2) {
            $sleepTime = 2 - $timeSinceLastRequest;
            usleep($sleepTime * 1000000);
        }

        Cache::put('tottenham_last_request', microtime(TRUE), 60);
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
                throw new Exception('HTTP ' . $response->getStatusCode() . ' error');
            }

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            Log::error('Tottenham Hotspur HTTP request failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to fetch Tottenham Hotspur page: ' . $e->getMessage());
        }
    }
}
