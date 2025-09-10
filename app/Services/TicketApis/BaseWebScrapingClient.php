<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use App\Exceptions\RateLimitException;
use App\Exceptions\ScrapingDetectedException;
use App\Exceptions\TicketPlatformException;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function get_class;
use function in_array;
use function is_array;
use function strlen;

abstract class BaseWebScrapingClient extends BaseApiClient
{
    /** User-Agent rotation array for anti-detection */
    protected $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
    ];

    /** Proxy configuration */
    protected $proxyConfig;

    /** Session management */
    protected $sessionCookies = [];

    protected $sessionHeaders = [];

    /** Anti-detection settings */
    protected $minDelay = 1; // Minimum delay between requests (seconds)

    protected $maxDelay = 3; // Maximum delay between requests (seconds)

    protected $requestCount = 0;

    protected $lastRequestTime = 0;

    /**
     * Core scraping methods - must be implemented by clients
     */
    abstract public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array;

    abstract public function scrapeEventDetails(string $url): array;

    /**
     * Set proxy configuration
     */
    /**
     * Set  proxy config
     */
    public function setProxyConfig(array $proxyConfig): void
    {
        $this->proxyConfig = $proxyConfig;
    }

    /**
     * Set custom delay range
     */
    /**
     * Set  delay range
     */
    public function setDelayRange(float $minDelay, float $maxDelay): void
    {
        $this->minDelay = $minDelay;
        $this->maxDelay = $maxDelay;
    }

    /**
     * Override the fallback method to implement scraping
     */
    /**
     * FallbackToScraping
     */
    public function fallbackToScraping(array $criteria): array
    {
        if (! $this->hasScrapingFallback()) {
            throw new TicketPlatformException(
                "Scraping fallback not enabled for {$this->getPlatformName()}",
                500,
                NULL,
                $this->getPlatformName(),
                'scraping',
            );
        }

        $platform = $this->getPlatformName();
        $keyword = $criteria['keyword'] ?? '';
        $location = $criteria['location'] ?? '';
        $maxResults = $criteria['max_results'] ?? 50;

        try {
            $results = $this->scrapeSearchResults($keyword, $location, $maxResults);

            Log::channel('ticket_apis')->info('Platform search fallback successful', [
                'platform' => $platform,
                'keyword'  => $keyword,
                'location' => $location,
                'results'  => count($results),
                'method'   => 'scraping',
            ]);

            return $results;
        } catch (Exception $e) {
            Log::channel('ticket_apis')->error('Scraping fallback failed', [
                'platform'   => $platform,
                'keyword'    => $keyword,
                'error'      => $e->getMessage(),
                'error_type' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Get the base URL for the platform
     */
    /**
     * Get  base url
     */
    #[Override]
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    abstract protected function extractSearchResults(Crawler $crawler, int $maxResults): array;

    abstract protected function extractEventFromNode(Crawler $node): array;

    abstract protected function extractPrices(Crawler $crawler): array;

    /**
     * Make HTTP request with enhanced anti-detection measures and error handling
     */
    /**
     * MakeScrapingRequest
     */
    protected function makeScrapingRequest(string $url, array $options = []): string
    {
        $startTime = microtime(TRUE);
        $platform = $this->getPlatformName();

        // Enforce rate limiting and delays
        $this->respectRateLimit($platform);
        $this->enforceDelay();

        $headers = $this->buildAntiDetectionHeaders($options);
        $httpClient = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->withOptions($this->getRequestOptions());

        // Add proxy support if configured
        if ($this->proxyConfig) {
            $httpClient = $httpClient->withOptions([
                'proxy' => $this->proxyConfig,
            ]);
        }

        try {
            $response = $httpClient->get($url);
            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            // Handle different response statuses
            if ($response->successful()) {
                $this->updateRequestStats();
                $this->updateSession($response);

                // Check for bot detection indicators
                $body = $response->body();
                $this->detectAntiBot($body, $url, $platform);

                // Log successful scraping request
                Log::channel('ticket_apis')->info('Scraping request successful', [
                    'platform'         => $platform,
                    'url'              => $url,
                    'method'           => 'scraping',
                    'response_time_ms' => $responseTime,
                    'content_length'   => strlen($body),
                ]);

                return $body;
            }

            // Handle specific HTTP errors for scraping
            $this->handleScrapingError($response, $url, $platform);
        } catch (Exception $e) {
            $totalTime = (microtime(TRUE) - $startTime) * 1000;

            Log::channel('ticket_apis')->error('Scraping request failed', [
                'platform'      => $platform,
                'url'           => $url,
                'method'        => 'scraping',
                'total_time_ms' => $totalTime,
                'error'         => $e->getMessage(),
                'error_type'    => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Enforce random delay between requests
     */
    /**
     * EnforceDelay
     */
    protected function enforceDelay(): void
    {
        $now = microtime(TRUE);
        if ($this->lastRequestTime > 0) {
            $timeSinceLastRequest = $now - $this->lastRequestTime;
            $requiredDelay = random_int($this->minDelay * 1000, $this->maxDelay * 1000) / 1000;

            if ($timeSinceLastRequest < $requiredDelay) {
                $sleepTime = $requiredDelay - $timeSinceLastRequest;
                usleep($sleepTime * 1000000);
            }
        }
        $this->lastRequestTime = microtime(TRUE);
    }

    /**
     * Build anti-detection headers
     */
    /**
     * BuildAntiDetectionHeaders
     */
    protected function buildAntiDetectionHeaders(array $options = []): array
    {
        $headers = array_merge([
            'User-Agent'                => $this->getRandomUserAgent(),
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language'           => 'en-US,en;q=0.9',
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => 'none',
            'Sec-Fetch-User'            => '?1',
            'Cache-Control'             => 'max-age=0',
        ], $this->sessionHeaders);

        // Add referer for internal navigation
        if (isset($options['referer'])) {
            $headers['Referer'] = $options['referer'];
            $headers['Sec-Fetch-Site'] = 'same-origin';
        }

        // Merge with any custom headers
        if (isset($options['headers'])) {
            return array_merge($headers, $options['headers']);
        }

        return $headers;
    }

    /**
     * Get random User-Agent from rotation array
     */
    /**
     * Get  random user agent
     */
    protected function getRandomUserAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    /**
     * Get request options for HTTP client
     */
    /**
     * Get  request options
     */
    protected function getRequestOptions(): array
    {
        $options = [
            'verify'          => FALSE,
            'allow_redirects' => TRUE,
            'http_errors'     => FALSE,
        ];

        // Add cookies for session management
        if (! empty($this->sessionCookies)) {
            $options['cookies'] = $this->sessionCookies;
        }

        return $options;
    }

    /**
     * Update request statistics
     */
    /**
     * UpdateRequestStats
     */
    protected function updateRequestStats(): void
    {
        $this->requestCount++;

        // Adjust delays based on request frequency
        if ($this->requestCount % 10 === 0) {
            $this->minDelay = min($this->minDelay + 0.5, 5);
            $this->maxDelay = min($this->maxDelay + 1, 10);
        }
    }

    /**
     * Update session data from response
     *
     * @param mixed $response
     */
    /**
     * UpdateSession
     *
     * @param mixed $response
     */
    protected function updateSession($response): void
    {
        // Extract and store cookies for session management
        if (method_exists($response, 'cookies')) {
            foreach ($response->cookies() as $cookie) {
                $this->sessionCookies[$cookie->getName()] = $cookie->getValue();
            }
        }
    }

    /**
     * Try multiple selectors and return first match
     */
    /**
     * TrySelectors
     */
    protected function trySelectors(Crawler $crawler, array $selectors, ?string $attribute = NULL): string
    {
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector)->first();
                if ($node->count() > 0) {
                    return $attribute ? $node->attr($attribute) : $node->text();
                }
            } catch (Exception) {
                continue;
            }
        }

        return '';
    }

    /**
     * Extract JSON-LD structured data
     */
    /**
     * ExtractJsonLdData
     */
    protected function extractJsonLdData(Crawler $crawler, ?string $type = NULL): array
    {
        $data = [];

        try {
            $jsonLdNodes = $crawler->filter('script[type="application/ld+json"]');

            $jsonLdNodes->each(function (Crawler $node) use (&$data, $type): void {
                try {
                    $json = json_decode($node->text(), TRUE);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Handle array of structured data
                        if (is_array($json) && ! isset($json['@type'])) {
                            foreach ($json as $item) {
                                if (isset($item['@type']) && (! $type || $item['@type'] === $type)) {
                                    $data[] = $item;
                                }
                            }
                        } elseif (isset($json['@type']) && (! $type || $json['@type'] === $type)) {
                            $data[] = $json;
                        }
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse JSON-LD data', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract JSON-LD data', ['error' => $e->getMessage()]);
        }

        return $data;
    }

    /**
     * Dynamic selector detection - analyze page structure
     */
    /**
     * DetectSelectors
     */
    protected function detectSelectors(Crawler $crawler, string $contentType = 'event'): array
    {
        $selectors = [];

        try {
            switch ($contentType) {
                case 'event':
                    $selectors = $this->detectEventSelectors($crawler);

                    break;
                case 'price':
                    $selectors = $this->detectPriceSelectors($crawler);

                    break;
                case 'date':
                    $selectors = $this->detectDateSelectors($crawler);

                    break;
                case 'venue':
                    $selectors = $this->detectVenueSelectors($crawler);

                    break;
            }
        } catch (Exception $e) {
            Log::debug('Dynamic selector detection failed', [
                'content_type' => $contentType,
                'error'        => $e->getMessage(),
            ]);
        }

        return $selectors;
    }

    /**
     * Detect event-related selectors
     */
    /**
     * DetectEventSelectors
     */
    protected function detectEventSelectors(Crawler $crawler): array
    {
        $selectors = [];

        // Look for common event container patterns
        $eventContainerSelectors = [
            'div[class*="event"]',
            'article[class*="event"]',
            'div[class*="listing"]',
            'div[class*="card"]',
            'div[class*="result"]',
            'div[class*="item"]',
        ];

        foreach ($eventContainerSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $selectors['container'][] = $selector;
            }
        }

        return $selectors;
    }

    /**
     * Detect price-related selectors
     */
    /**
     * DetectPriceSelectors
     */
    protected function detectPriceSelectors(Crawler $crawler): array
    {
        $selectors = [];

        // Look for elements containing price indicators
        $priceIndicators = ['$', '€', '£', 'USD', 'EUR', 'GBP', 'price', 'cost'];

        foreach ($priceIndicators as $indicator) {
            $elements = $crawler->filter("*:contains('{$indicator}')");
            $elements->each(function (Crawler $node) use (&$selectors): void {
                $class = $node->attr('class');
                if ($class && ! in_array($class, $selectors, TRUE)) {
                    $selectors[] = '.' . str_replace(' ', '.', $class);
                }
            });
        }

        return array_unique($selectors);
    }

    /**
     * Detect date-related selectors
     */
    /**
     * DetectDateSelectors
     */
    protected function detectDateSelectors(Crawler $crawler): array
    {
        $selectors = [];

        // Look for common date/time patterns
        $datePatterns = [
            '/\d{1,2}\/\d{1,2}\/\d{4}/',
            '/\d{4}-\d{2}-\d{2}/',
            '/\w+\s+\d{1,2},\s+\d{4}/',
            '/\d{1,2}:\d{2}/',
        ];

        foreach ($datePatterns as $pattern) {
            $elements = $crawler->filter('*')->each(function (Crawler $node) use ($pattern) {
                if (preg_match($pattern, $node->text())) {
                    return $node;
                }
            });

            foreach (array_filter($elements) as $element) {
                if ($element && $element->attr('class')) {
                    $selectors[] = '.' . str_replace(' ', '.', $element->attr('class'));
                }
            }
        }

        return array_unique($selectors);
    }

    /**
     * Detect venue-related selectors
     */
    /**
     * DetectVenueSelectors
     */
    protected function detectVenueSelectors(Crawler $crawler): array
    {
        $selectors = [];

        // Look for venue-related keywords
        $venueKeywords = ['venue', 'location', 'place', 'stadium', 'theater', 'hall', 'center', 'arena'];

        foreach ($venueKeywords as $keyword) {
            $elements = $crawler->filter("[class*='{$keyword}']");
            $elements->each(function (Crawler $node) use (&$selectors): void {
                $class = $node->attr('class');
                if ($class) {
                    $selectors[] = '.' . str_replace(' ', '.', $class);
                }
            });
        }

        return array_unique($selectors);
    }

    /**
     * Extract price information with multiple fallback strategies
     */
    /**
     * ExtractPriceWithFallbacks
     */
    protected function extractPriceWithFallbacks(Crawler $crawler): array
    {
        $prices = [];

        // Strategy 1: JSON-LD structured data
        $jsonLdData = $this->extractJsonLdData($crawler, 'Event');
        foreach ($jsonLdData as $event) {
            if (isset($event['offers'])) {
                $offers = is_array($event['offers'][0] ?? []) ? $event['offers'] : [$event['offers']];
                foreach ($offers as $offer) {
                    if (isset($offer['price'])) {
                        $prices[] = [
                            'price'    => $offer['price'],
                            'currency' => $offer['priceCurrency'] ?? 'USD',
                            'section'  => $offer['name'] ?? 'General',
                        ];
                    }
                }
            }
        }

        // Strategy 2: Dynamic selector detection
        if ($prices === []) {
            $priceSelectors = $this->detectPriceSelectors($crawler);
            foreach ($priceSelectors as $selector) {
                try {
                    $priceNodes = $crawler->filter($selector);
                    $priceNodes->each(function (Crawler $node) use (&$prices): void {
                        $text = $node->text();
                        if (preg_match('/([€$£¥])\s*([0-9,]+(?:\.\d{2})?)/', $text, $matches)) {
                            $prices[] = [
                                'price'    => (float) (str_replace(',', '', $matches[2])),
                                'currency' => $this->mapCurrencySymbol($matches[1]),
                                'section'  => 'General',
                            ];
                        }
                    });
                } catch (Exception) {
                    continue;
                }
            }
        }

        // Strategy 3: Fallback to extractPrices method
        if ($prices === []) {
            $prices = $this->extractPrices($crawler);
        }

        return $prices;
    }

    /**
     * Map currency symbol to currency code
     */
    /**
     * MapCurrencySymbol
     */
    protected function mapCurrencySymbol(string $symbol): string
    {
        $map = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            '¥' => 'JPY',
        ];

        return $map[$symbol] ?? 'USD';
    }

    /**
     * Parse event date with multiple format support
     */
    /**
     * ParseEventDate
     */
    protected function parseEventDate(string $dateString): ?DateTime
    {
        if ($dateString === '' || $dateString === '0') {
            return NULL;
        }

        // Clean up the date string
        $dateString = trim((string) preg_replace('/\s+/', ' ', $dateString));

        // Remove common prefixes
        $prefixes = ['/^(Date:?|Event Date:?|When:?)\s*/i', '/^(on\s+)/i'];
        foreach ($prefixes as $prefix) {
            $dateString = preg_replace($prefix, '', (string) $dateString);
        }

        // Try multiple date formats
        $formats = [
            // English formats
            'M j, Y g:i A',
            'F j, Y g:i A',
            'M j, Y',
            'F j, Y',
            'j M Y H:i',
            'j F Y H:i',
            'j M Y',
            'j F Y',
            // ISO formats
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            // European formats
            'd.m.Y H:i',
            'd.m.Y',
            'd/m/Y H:i',
            'd/m/Y',
            // American formats
            'm/d/Y H:i',
            'm/d/Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = DateTime::createFromFormat($format, $dateString);
                if ($date && $date->format($format) === $dateString) {
                    return $date;
                }
            } catch (Exception) {
                continue;
            }
        }

        // Fallback to PHP's date parser
        try {
            return new DateTime($dateString);
        } catch (Exception) {
            Log::debug('Failed to parse date', ['date_string' => $dateString]);

            return NULL;
        }
    }

    /**
     * Normalize URL to absolute format
     */
    /**
     * NormalizeUrl
     */
    protected function normalizeUrl(string $url, ?string $baseUrl = NULL): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        if (! $baseUrl) {
            $baseUrl = $this->baseUrl;
        }

        if (str_starts_with($url, '/')) {
            return rtrim((string) $baseUrl, '/') . $url;
        }

        return rtrim((string) $baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Extract numeric value from price string
     */
    /**
     * ExtractNumericPrice
     */
    protected function extractNumericPrice(string $priceString): ?float
    {
        // Remove currency symbols and extract numeric value
        $cleaned = preg_replace('/[^\d.,]/', '', $priceString);
        $cleaned = str_replace(',', '', $cleaned);

        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return NULL;
    }

    /**
     * Rate limit based on platform-specific rules
     */
    /**
     * RespectRateLimit
     */
    protected function respectRateLimit(string $platform): void
    {
        $rateLimits = [
            'ticketmaster' => ['requests' => 5, 'window' => 60], // 5 requests per minute
            'stubhub'      => ['requests' => 10, 'window' => 60],     // 10 requests per minute
            'seatgeek'     => ['requests' => 20, 'window' => 60],    // 20 requests per minute
            'viagogo'      => ['requests' => 5, 'window' => 60],      // 5 requests per minute
            'tickpick'     => ['requests' => 15, 'window' => 60],    // 15 requests per minute
            'funzone'      => ['requests' => 10, 'window' => 60],     // 10 requests per minute
        ];

        if (isset($rateLimits[$platform])) {
            try {
                $limit = $rateLimits[$platform];
                $cacheKey = "rate_limit_{$platform}";

                $requests = Cache::get($cacheKey, []);
                $now = time();

                // Remove old requests outside the window
                $requests = array_filter($requests, fn ($timestamp): bool => ($now - $timestamp) < $limit['window']);

                // Check if we've exceeded the limit
                if (count($requests) >= $limit['requests']) {
                    $oldestRequest = min($requests);
                    $waitTime = $limit['window'] - ($now - $oldestRequest);
                    if ($waitTime > 0) {
                        Log::info("Rate limit reached for {$platform}, waiting {$waitTime} seconds");
                        sleep($waitTime);
                    }
                }

                // Record this request
                $requests[] = $now;
                Cache::put($cacheKey, $requests, $limit['window']);
            } catch (Exception $e) {
                // Handle gracefully if cache table doesn't exist (during migrations)
                Log::debug('Cache not available for rate limiting: ' . $e->getMessage());
                // Continue without rate limiting during migrations/bootstrap
            }
        }
    }

    /**
     * Handle scraping-specific HTTP errors
     *
     * @param mixed $response
     */
    /**
     * HandleScrapingError
     *
     * @param mixed $response
     */
    protected function handleScrapingError($response, string $url, string $platform): never
    {
        $statusCode = $response->status();
        $body = $response->body();

        switch ($statusCode) {
            case 403:
                // Could be bot detection
                if (str_contains(strtolower((string) $body), 'captcha')
                    || str_contains(strtolower((string) $body), 'cloudflare')
                    || str_contains(strtolower((string) $body), 'blocked')) {
                    throw new ScrapingDetectedException(
                        "Bot detection triggered for {$platform}: CAPTCHA or similar challenge detected",
                        $platform,
                    );
                }

                throw $this->createPlatformException(
                    "Access forbidden for {$platform} scraping",
                    $platform,
                    $statusCode,
                );

            case 429:
                $retryAfter = $response->header('Retry-After') ?? 300; // Default to 5 minutes

                throw new RateLimitException(
                    "Rate limit exceeded for {$platform} scraping",
                    is_numeric($retryAfter) ? (int) $retryAfter : 300,
                    $platform,
                );

            case 503:
                throw new ScrapingDetectedException(
                    "Service temporarily unavailable for {$platform} - possible anti-bot measure",
                    $platform,
                );

            default:
                throw $this->createPlatformException(
                    "Scraping request failed for {$platform} with status {$statusCode}: {$body}",
                    $platform,
                    $statusCode,
                );
        }
    }

    /**
     * Detect anti-bot measures in response content
     */
    /**
     * DetectAntiBot
     */
    protected function detectAntiBot(string $body, string $url, string $platform): void
    {
        $botDetectionPatterns = [
            '/captcha/i',
            '/cloudflare/i',
            '/access\s+denied/i',
            '/blocked/i',
            '/security\s+check/i',
            '/unusual\s+traffic/i',
            '/verify\s+you\s+are\s+human/i',
            '/challenge/i',
            '/protected\s+by\s+recaptcha/i',
            '/checking\s+your\s+browser/i',
        ];

        foreach ($botDetectionPatterns as $pattern) {
            if (preg_match($pattern, $body)) {
                throw new ScrapingDetectedException(
                    "Bot detection triggered for {$platform}: Anti-bot measure detected in response",
                    $platform,
                );
            }
        }

        // Check for suspiciously short responses (likely redirects to captcha pages)
        if (strlen($body) < 500 && str_contains($body, '<script>')) {
            throw new ScrapingDetectedException(
                "Suspicious response detected for {$platform}: Possible redirect to challenge page",
                $platform,
            );
        }
    }

    /**
     * Track selector effectiveness for monitoring
     */
    /**
     * TrackSelectorEffectiveness
     */
    protected function trackSelectorEffectiveness(string $selector, bool $successful, string $platform): void
    {
        $cacheKey = "selector_stats_{$platform}_{$selector}";
        $stats = Cache::get($cacheKey, ['successful' => 0, 'failed' => 0, 'last_used' => NULL]);

        if ($successful) {
            $stats['successful']++;
        } else {
            $stats['failed']++;
        }

        $stats['last_used'] = now()->toISOString();

        Cache::put($cacheKey, $stats, 3600 * 24); // Store for 24 hours

        // Log selector effectiveness periodically
        $totalAttempts = $stats['successful'] + $stats['failed'];
        if ($totalAttempts % 10 === 0) {
            $successRate = ($stats['successful'] / $totalAttempts) * 100;
            Log::channel('ticket_apis')->info('Selector effectiveness report', [
                'platform'       => $platform,
                'selector'       => $selector,
                'success_rate'   => round($successRate, 2),
                'total_attempts' => $totalAttempts,
                'successful'     => $stats['successful'],
                'failed'         => $stats['failed'],
            ]);
        }
    }
}
