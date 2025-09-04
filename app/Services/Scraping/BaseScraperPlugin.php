<?php declare(strict_types=1);

namespace App\Services\Scraping;

use App\Services\Scraping\Traits\AntiDetectionTrait;
use App\Services\Scraping\Traits\CurrencyHandlingTrait;
use App\Services\Scraping\Traits\MultiLanguageTrait;
use App\Services\Scraping\Traits\RateLimitingTrait;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function get_class;

abstract class BaseScraperPlugin implements ScraperPluginInterface
{
    use RateLimitingTrait;
    use AntiDetectionTrait;
    use CurrencyHandlingTrait;
    use MultiLanguageTrait;

    protected $proxyService;

    protected AdvancedAntiDetectionService $antiDetection;

    protected HighDemandTicketScraperService $highDemandScraper;

    protected bool $enabled = TRUE;

    protected array $config = [];

    protected string $pluginName;

    protected string $platform;

    protected string $description;

    protected string $version = '1.0.0';

    protected string $baseUrl;

    protected string $venue;

    protected string $currency = 'GBP';

    protected string $language = 'en-GB';

    protected int $timeout = 30;

    protected int $rateLimitSeconds = 2;

    protected int $maxRetries = 3;

    /**
     * Constructor - compatible with existing plugin system
     *
     * @param mixed|null $proxyService
     */
    public function __construct($proxyService = NULL)
    {
        $this->proxyService = $proxyService;
        $this->initializePlugin();
        $this->loadConfiguration();
    }

    /**
     * Get plugin metadata
     */
    /**
     * Get  info
     */
    public function getInfo(): array
    {
        return [
            'name'               => $this->pluginName,
            'description'        => $this->description,
            'version'            => $this->version,
            'platform'           => $this->platform,
            'capabilities'       => $this->getCapabilities(),
            'rate_limit'         => "1 request per {$this->rateLimitSeconds} seconds",
            'supported_criteria' => $this->getSupportedCriteria(),
            'venue'              => $this->venue,
            'currency'           => $this->currency,
            'language'           => $this->language,
            'enabled'            => $this->enabled,
        ];
    }

    /**
     * Check if plugin is enabled
     */
    /**
     * Check if  enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the plugin
     */
    /**
     * Enable
     */
    public function enable(): void
    {
        $this->enabled = TRUE;
        Log::info("{$this->pluginName} plugin enabled");
    }

    /**
     * Disable the plugin
     */
    /**
     * Disable
     */
    public function disable(): void
    {
        $this->enabled = FALSE;
        Log::info("{$this->pluginName} plugin disabled");
    }

    /**
     * Configure the plugin
     */
    /**
     * Configure
     */
    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);

        // Apply configuration overrides
        if (isset($config['base_url'])) {
            $this->baseUrl = $config['base_url'];
        }

        if (isset($config['currency'])) {
            $this->currency = $config['currency'];
        }

        if (isset($config['language'])) {
            $this->language = $config['language'];
        }

        if (isset($config['timeout'])) {
            $this->timeout = $config['timeout'];
        }

        if (isset($config['rate_limit_seconds'])) {
            $this->rateLimitSeconds = $config['rate_limit_seconds'];
        }

        Log::info("{$this->pluginName} plugin configured", ['config' => $config]);
    }

    /**
     * Test plugin connectivity
     */
    /**
     * Test
     */
    public function test(): array
    {
        try {
            $startTime = microtime(TRUE);

            $testUrl = $this->getTestUrl();
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($testUrl);

            $duration = (microtime(TRUE) - $startTime) * 1000;

            if ($response->successful()) {
                return [
                    'success'          => TRUE,
                    'message'          => "Successfully connected to {$this->pluginName}",
                    'response_time_ms' => round($duration, 2),
                    'status_code'      => $response->status(),
                ];
            }

            return [
                'success'     => FALSE,
                'message'     => "HTTP {$response->status()}: Failed to connect to {$this->pluginName}",
                'status_code' => $response->status(),
            ];
        } catch (Exception $e) {
            return [
                'success'   => FALSE,
                'message'   => 'Connection failed: ' . $e->getMessage(),
                'exception' => get_class($e),
            ];
        }
    }

    /**
     * Main scraping method
     */
    /**
     * Scrape
     */
    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception("{$this->pluginName} plugin is disabled");
        }

        Log::info("Starting {$this->pluginName} scraping", $criteria);

        try {
            // Apply rate limiting
            $this->applyRateLimit($this->platform);

            // Build search URL
            $searchUrl = $this->buildSearchUrl($criteria);

            // Make request with anti-detection measures
            $response = $this->makeRequest($searchUrl);

            // Parse results
            $events = $this->parseSearchResults($response);

            // Filter and format results
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info("{$this->pluginName} scraping completed", [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error("{$this->pluginName} scraping failed", [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Initialize plugin-specific settings
     */
    abstract protected function initializePlugin(): void;

    /**
     * Get plugin capabilities
     */
    abstract protected function getCapabilities(): array;

    /**
     * Get supported search criteria
     */
    abstract protected function getSupportedCriteria(): array;

    /**
     * Get test URL for connectivity check
     */
    abstract protected function getTestUrl(): string;

    /**
     * Build search URL from criteria
     */
    abstract protected function buildSearchUrl(array $criteria): string;

    /**
     * Parse search results from HTML
     */
    abstract protected function parseSearchResults(string $html): array;

    /**
     * Make HTTP request with anti-detection
     */
    /**
     * MakeRequest
     */
    protected function makeRequest(string $url): string
    {
        $headers = $this->getHeaders();
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders($headers)
                    ->get($url);

                if ($response->successful()) {
                    return $response->body();
                }

                // If blocked, try with different headers
                if ($response->status() === 403 || $response->status() === 429) {
                    $headers = $this->rotateHeaders();
                    $this->randomDelay();
                }

                $retries++;
            } catch (Exception $e) {
                $retries++;
                if ($retries >= $this->maxRetries) {
                    throw new Exception("Failed to fetch {$this->pluginName} page after {$this->maxRetries} retries: " . $e->getMessage());
                }

                $this->randomDelay();
            }
        }

        throw new Exception("Failed to fetch {$this->pluginName} page: HTTP {$response->status()}");
    }

    /**
     * Get HTTP headers with anti-detection
     */
    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        return [
            'User-Agent'                => $this->getRandomUserAgent(),
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language'           => $this->getAcceptLanguageHeader(),
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => 'none',
            'Cache-Control'             => 'max-age=0',
            'Referer'                   => $this->baseUrl,
        ];
    }

    /**
     * Parse individual event from DOM node
     */
    /**
     * ParseEventNode
     */
    protected function parseEventNode(Crawler $node): ?array
    {
        try {
            // Extract basic event data
            $eventName = $this->extractText($node, $this->getEventNameSelectors());
            if (empty($eventName)) {
                return NULL;
            }

            $date = $this->extractText($node, $this->getDateSelectors());
            $venue = $this->extractText($node, $this->getVenueSelectors()) ?: $this->venue;
            $priceText = $this->extractText($node, $this->getPriceSelectors());
            $url = $this->extractUrl($node, 'a');
            $availability = $this->extractText($node, $this->getAvailabilitySelectors());

            // Parse price information
            $priceInfo = $this->parsePriceInfo($priceText);

            // Fix relative URLs
            if ($url && ! filter_var($url, FILTER_VALIDATE_URL)) {
                $url = $this->baseUrl . $url;
            }

            return [
                'event_name'          => trim($eventName),
                'venue'               => $venue,
                'date'                => $this->parseDate($date),
                'price_min'           => $priceInfo['min'],
                'price_max'           => $priceInfo['max'],
                'currency'            => $this->currency,
                'url'                 => $url,
                'platform'            => $this->platform,
                'availability_status' => $this->normalizeAvailability($availability),
                'category'            => $this->getEventCategory($eventName),
                'subcategory'         => $this->getEventSubcategory($eventName),
                'scraped_at'          => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::warning("Failed to parse {$this->pluginName} event node", [
                'error' => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Get CSS selectors for event name (multi-language)
     */
    abstract protected function getEventNameSelectors(): string;

    /**
     * Get CSS selectors for date (multi-language)
     */
    abstract protected function getDateSelectors(): string;

    /**
     * Get CSS selectors for venue (multi-language)
     */
    abstract protected function getVenueSelectors(): string;

    /**
     * Get CSS selectors for price (multi-language)
     */
    abstract protected function getPriceSelectors(): string;

    /**
     * Get CSS selectors for availability (multi-language)
     */
    abstract protected function getAvailabilitySelectors(): string;

    /**
     * Extract text from node using CSS selectors
     */
    /**
     * ExtractText
     */
    protected function extractText(Crawler $node, string $selectors): string
    {
        try {
            $element = $node->filter($selectors)->first();

            return $element->count() > 0 ? trim($element->text()) : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Extract URL from node
     */
    /**
     * ExtractUrl
     */
    protected function extractUrl(Crawler $node, string $selector = 'a'): string
    {
        try {
            $element = $node->filter($selector)->first();

            return $element->count() > 0 ? trim($element->attr('href') ?? '') : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Determine event category
     */
    /**
     * Get  event category
     */
    protected function getEventCategory(string $eventName): string
    {
        $eventName = strtolower($eventName);

        // Football/Soccer detection
        if ($this->isFootballEvent($eventName)) {
            return 'Sports';
        }

        // Music detection
        if ($this->isMusicEvent($eventName)) {
            return 'Music';
        }

        // Theater detection
        if ($this->isTheaterEvent($eventName)) {
            return 'Theater';
        }

        return 'Entertainment';
    }

    /**
     * Determine event subcategory
     */
    /**
     * Get  event subcategory
     */
    protected function getEventSubcategory(string $eventName): string
    {
        if ($this->isFootballEvent($eventName)) {
            return 'Football';
        }

        return 'General';
    }

    /**
     * Filter results based on criteria
     */
    /**
     * Parse date string into standardized format
     */
    protected function parseDate(string $dateText): ?string
    {
        if (empty($dateText)) {
            return NULL;
        }

        try {
            // Try to parse the date
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateText);
            if ($date === FALSE) {
                $date = new DateTime($dateText);
            }

            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            Log::warning("Failed to parse date: {$dateText}", ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * FilterResults
     */
    protected function filterResults(array $events, array $criteria): array
    {
        return array_filter($events, function ($event) use ($criteria) {
            // Basic keyword filtering
            if (! empty($criteria['keyword'])) {
                $keyword = strtolower($criteria['keyword']);
                $eventName = strtolower($event['event_name']);
                if (! str_contains($eventName, $keyword)) {
                    return FALSE;
                }
            }

            // Date range filtering
            if (! empty($criteria['date_from']) && ! empty($event['date'])) {
                if ($event['date'] < $criteria['date_from']) {
                    return FALSE;
                }
            }

            if (! empty($criteria['date_to']) && ! empty($event['date'])) {
                if ($event['date'] > $criteria['date_to']) {
                    return FALSE;
                }
            }

            return TRUE;
        });
    }

    /**
     * Load configuration from config files
     */
    /**
     * LoadConfiguration
     */
    protected function loadConfiguration(): void
    {
        $configKey = strtolower(str_replace(['Plugin', 'FC', 'CF'], ['', '', ''], class_basename($this)));
        $config = config("scraping.plugins.{$configKey}", []);

        if (! empty($config)) {
            $this->configure($config);
        }
    }
}
