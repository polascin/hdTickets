<?php declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class ProxyRotationService
{
    protected $proxies = [];

    protected $userAgents = [];

    protected $currentProxyIndex = 0;

    protected $proxyHealth = [];

    protected $rateLimits = [];

    public function __construct()
    {
        $this->loadProxies();
        $this->loadUserAgents();
        $this->loadProxyHealth();
    }

    /**
     * Get next available proxy with health check
     */
    /**
     * Get  next proxy
     */
    public function getNextProxy(): ?array
    {
        if (empty($this->proxies)) {
            return NULL;
        }

        $attempts = 0;
        $maxAttempts = count($this->proxies);

        while ($attempts < $maxAttempts) {
            $proxy = $this->proxies[$this->currentProxyIndex];
            $proxyKey = $this->getProxyKey($proxy);

            // Check if proxy is healthy
            if ($this->isProxyHealthy($proxyKey)) {
                $this->currentProxyIndex = ($this->currentProxyIndex + 1) % count($this->proxies);

                return $proxy;
            }

            // Move to next proxy
            $this->currentProxyIndex = ($this->currentProxyIndex + 1) % count($this->proxies);
            $attempts++;
        }

        Log::warning('No healthy proxies available, proceeding without proxy');

        return NULL;
    }

    /**
     * Get random user agent
     */
    /**
     * Get  random user agent
     */
    public function getRandomUserAgent(): string
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    /**
     * Get user agent with session persistence for a platform
     */
    /**
     * Get  persistent user agent
     */
    public function getPersistentUserAgent(string $platform): string
    {
        $cacheKey = "scraping.user_agent.{$platform}";
        $userAgent = Cache::get($cacheKey);

        if (!$userAgent) {
            $userAgent = $this->getRandomUserAgent();
            // Keep same user agent for 30 minutes per platform to maintain session consistency
            Cache::put($cacheKey, $userAgent, 30 * 60);
        }

        return $userAgent;
    }

    /**
     * Test proxy health
     */
    /**
     * TestProxy
     */
    public function testProxy(array $proxy): bool
    {
        $proxyKey = $this->getProxyKey($proxy);

        try {
            $proxyUrl = $this->formatProxyUrl($proxy);

            $response = Http::timeout(10)
                ->withOptions([
                    'proxy'  => $proxyUrl,
                    'verify' => FALSE,
                ])
                ->get('https://httpbin.org/ip');

            $isHealthy = $response->successful();

            $this->updateProxyHealth($proxyKey, $isHealthy);

            if ($isHealthy) {
                $responseData = $response->json();
                Log::info('Proxy test successful', [
                    'proxy'       => $proxyKey,
                    'returned_ip' => $responseData['origin'] ?? 'unknown',
                ]);
            }

            return $isHealthy;
        } catch (Exception $e) {
            Log::warning('Proxy test failed', [
                'proxy' => $proxyKey,
                'error' => $e->getMessage(),
            ]);

            $this->updateProxyHealth($proxyKey, FALSE);

            return FALSE;
        }
    }

    /**
     * Update proxy health status
     */
    /**
     * UpdateProxyHealth
     */
    public function updateProxyHealth(string $proxyKey, bool $success): void
    {
        $health = $this->proxyHealth[$proxyKey] ?? [
            'successes'      => 0,
            'failures'       => 0,
            'last_check'     => time(),
            'total_requests' => 0,
        ];

        if ($success) {
            $health['successes']++;
            $health['failures'] = max(0, $health['failures'] - 1); // Reduce failure count on success
        } else {
            $health['failures']++;
        }

        $health['total_requests']++;
        $health['last_check'] = time();
        $health['success_rate'] = $health['total_requests'] > 0
            ? ($health['successes'] / $health['total_requests']) * 100
            : 0;

        $this->proxyHealth[$proxyKey] = $health;

        // Cache for 1 hour
        Cache::put('scraping.proxy_health', $this->proxyHealth, 3600);
    }

    /**
     * Format proxy URL for HTTP client
     */
    /**
     * FormatProxyUrl
     */
    public function formatProxyUrl(array $proxy): string
    {
        $auth = '';
        if (isset($proxy['username'], $proxy['password'])) {
            $auth = $proxy['username'] . ':' . $proxy['password'] . '@';
        }

        $scheme = $proxy['type'] ?? 'http';

        return "{$scheme}://{$auth}{$proxy['host']}:{$proxy['port']}";
    }

    /**
     * Test all proxies and return health report
     */
    /**
     * TestAllProxies
     */
    public function testAllProxies(): array
    {
        $results = [];

        foreach ($this->proxies as $proxy) {
            $proxyKey = $this->getProxyKey($proxy);
            $isHealthy = $this->testProxy($proxy);

            $results[$proxyKey] = [
                'proxy'       => $proxy,
                'healthy'     => $isHealthy,
                'health_data' => $this->proxyHealth[$proxyKey] ?? NULL,
            ];
        }

        return $results;
    }

    /**
     * Add new proxy to rotation
     */
    /**
     * AddProxy
     */
    public function addProxy(array $proxy): void
    {
        $this->proxies[] = $proxy;
        Cache::put('scraping.proxies', $this->proxies, 3600 * 24);
    }

    /**
     * Remove proxy from rotation
     */
    /**
     * RemoveProxy
     */
    public function removeProxy(string $host, int $port): void
    {
        $this->proxies = array_filter($this->proxies, fn (array $proxy): bool => !($proxy['host'] === $host && $proxy['port'] === $port));

        Cache::put('scraping.proxies', array_values($this->proxies), 3600 * 24);
    }

    /**
     * Get proxy statistics
     */
    /**
     * Get  proxy stats
     */
    public function getProxyStats(): array
    {
        $stats = [
            'total_proxies'        => count($this->proxies),
            'healthy_proxies'      => 0,
            'unhealthy_proxies'    => 0,
            'untested_proxies'     => 0,
            'average_success_rate' => 0,
            'proxy_details'        => [],
        ];

        $totalSuccessRate = 0;
        $testedProxies = 0;

        foreach ($this->proxies as $proxy) {
            $proxyKey = $this->getProxyKey($proxy);
            $health = $this->proxyHealth[$proxyKey] ?? NULL;

            if (!$health) {
                $stats['untested_proxies']++;
                $stats['proxy_details'][$proxyKey] = [
                    'status' => 'untested',
                    'proxy'  => $proxy,
                ];
            } else {
                $testedProxies++;
                $isHealthy = $this->isProxyHealthy($proxyKey);

                if ($isHealthy) {
                    $stats['healthy_proxies']++;
                } else {
                    $stats['unhealthy_proxies']++;
                }

                $totalSuccessRate += $health['success_rate'] ?? 0;

                $stats['proxy_details'][$proxyKey] = [
                    'status' => $isHealthy ? 'healthy' : 'unhealthy',
                    'proxy'  => $proxy,
                    'health' => $health,
                ];
            }
        }

        if ($testedProxies > 0) {
            $stats['average_success_rate'] = $totalSuccessRate / $testedProxies;
        }

        return $stats;
    }

    /**
     * Clear proxy health cache
     */
    /**
     * ClearHealthCache
     */
    public function clearHealthCache(): void
    {
        $this->proxyHealth = [];
        Cache::forget('scraping.proxy_health');
    }

    /**
     * Get headers with anti-detection measures
     */
    /**
     * Get  anti detection headers
     */
    public function getAntiDetectionHeaders(string $platform = 'general', ?string $referer = NULL): array
    {
        $userAgent = $this->getPersistentUserAgent($platform);

        $headers = [
            'User-Agent'                => $userAgent,
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language'           => 'en-US,en;q=0.9',
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => $referer ? 'same-origin' : 'none',
            'Sec-Fetch-User'            => '?1',
            'Cache-Control'             => 'max-age=0',
        ];

        if ($referer) {
            $headers['Referer'] = $referer;
        }

        // Add some randomization to headers to avoid detection
        if (random_int(0, 1) !== 0) {
            $headers['Sec-CH-UA'] = '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"';
            $headers['Sec-CH-UA-Mobile'] = '?0';
            $headers['Sec-CH-UA-Platform'] = '"Windows"';
        }

        return $headers;
    }

    /**
     * Load proxy configurations from cache or config
     */
    /**
     * LoadProxies
     */
    protected function loadProxies(): void
    {
        try {
            $this->proxies = Cache::get('scraping.proxies', config('scraping.proxies', [
                // Free/Public proxies (for testing)
                [
                    'host'    => '103.152.112.162',
                    'port'    => 80,
                    'type'    => 'http',
                    'country' => 'US',
                    'status'  => 'active',
                ],
                [
                    'host'    => '47.74.152.29',
                    'port'    => 8888,
                    'type'    => 'http',
                    'country' => 'US',
                    'status'  => 'active',
                ],
                // Add paid proxy services here for production
                // Rotating residential proxies recommended for production use
            ]));
        } catch (Exception) {
            // Fallback to config if cache is not available
            $this->proxies = config('scraping.proxies', []);
            Log::debug('Cache not available during proxy loading, using config fallback');
        }
    }

    /**
     * Load user agent strings for rotation
     */
    /**
     * LoadUserAgents
     */
    protected function loadUserAgents(): void
    {
        $this->userAgents = [
            // Chrome on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',

            // Firefox on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:119.0) Gecko/20100101 Firefox/119.0',

            // Chrome on macOS
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',

            // Safari on macOS
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15',

            // Chrome on Linux
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',

            // Edge on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/119.0.0.0',

            // Mobile User Agents for better disguise
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        ];
    }

    /**
     * Load proxy health status from cache
     */
    /**
     * LoadProxyHealth
     */
    protected function loadProxyHealth(): void
    {
        try {
            $this->proxyHealth = Cache::get('scraping.proxy_health', []);
        } catch (Exception) {
            // Fallback to empty array if cache is not available
            $this->proxyHealth = [];
            Log::debug('Cache not available during proxy health loading, using empty fallback');
        }
    }

    /**
     * Check if proxy is healthy
     */
    /**
     * Check if  proxy healthy
     */
    protected function isProxyHealthy(string $proxyKey): bool
    {
        $health = $this->proxyHealth[$proxyKey] ?? NULL;

        if (!$health) {
            return TRUE; // Assume healthy if no data
        }

        // Consider proxy unhealthy if it failed more than 5 times in last hour
        $failures = $health['failures'] ?? 0;
        $lastCheck = $health['last_check'] ?? 0;

        return !($failures > 5 && (time() - $lastCheck) < 3600);
    }

    /**
     * Get unique key for proxy
     */
    /**
     * Get  proxy key
     */
    protected function getProxyKey(array $proxy): string
    {
        return $proxy['host'] . ':' . $proxy['port'];
    }
}
