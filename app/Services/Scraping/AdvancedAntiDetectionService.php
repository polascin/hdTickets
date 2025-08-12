<?php declare(strict_types=1);

namespace App\Services\Scraping;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdvancedAntiDetectionService
{
    protected array $browserFingerprints;

    protected array $tlsFingerprints;

    protected array $sessionPools;

    protected array $userAgents;

    protected CookieJar $cookieJar;

    public function __construct()
    {
        $this->initializeBrowserFingerprints();
        $this->initializeTlsFingerprints();
        $this->initializeUserAgents();
        $this->initializeSessionPools();
        $this->cookieJar = new CookieJar();
    }

    /**
     * Get a realistic browser session for the platform
     */
    public function getBrowserSession(string $platform): array
    {
        $cacheKey = "browser_session_{$platform}_" . date('H');

        return Cache::remember($cacheKey, 3600, function () use ($platform) {
            $browserType = $this->selectBrowserType($platform);
            $fingerprint = $this->browserFingerprints[$browserType];

            return [
                'user_agent'           => $this->getRandomUserAgent($browserType),
                'viewport'             => $fingerprint['viewport'][array_rand($fingerprint['viewport'])],
                'screen'               => $fingerprint['screen'][array_rand($fingerprint['screen'])],
                'language'             => $fingerprint['languages'][array_rand($fingerprint['languages'])],
                'timezone'             => $fingerprint['timezone'][array_rand($fingerprint['timezone'])],
                'platform'             => $fingerprint['platform'],
                'hardware_concurrency' => $fingerprint['hardware_concurrency'][array_rand($fingerprint['hardware_concurrency'])],
                'device_memory'        => $fingerprint['device_memory'][array_rand($fingerprint['device_memory'])],
                'browser_type'         => $browserType,
            ];
        });
    }

    /**
     * Generate advanced HTTP headers with anti-detection
     */
    public function generateAdvancedHeaders(string $platform, ?string $referer = NULL, array $customHeaders = []): array
    {
        $session = $this->getBrowserSession($platform);

        $baseHeaders = [
            'User-Agent'                => $session['user_agent'],
            'Accept'                    => $this->getAcceptHeader($session['browser_type']),
            'Accept-Language'           => $session['language'],
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => $referer ? 'same-origin' : 'none',
            'Sec-Fetch-User'            => '?1',
            'Cache-Control'             => 'max-age=0',
            'sec-ch-ua'                 => $this->generateSecChUa($session['browser_type']),
            'sec-ch-ua-mobile'          => '?0',
            'sec-ch-ua-platform'        => '"' . ($session['platform'] === 'Win32' ? 'Windows' : 'macOS') . '"',
        ];

        if ($referer) {
            $baseHeaders['Referer'] = $referer;
        }

        // Add browser-specific headers
        if (str_contains($session['browser_type'], 'chrome')) {
            $baseHeaders['sec-ch-ua-full-version-list'] = $this->generateFullVersionList();
            $baseHeaders['sec-ch-ua-arch'] = '"x86"';
            $baseHeaders['sec-ch-ua-bitness'] = '"64"';
        }

        // Merge with custom headers
        return array_merge($baseHeaders, $customHeaders);
    }

    /**
     * Implement intelligent delays with human-like patterns
     */
    public function humanLikeDelay(string $platform, string $action = 'page_load'): void
    {
        $delayPatterns = [
            'page_load' => [
                'min'          => 2000, 'max' => 5000, // 2-5 seconds
                'distribution' => 'normal',
            ],
            'search' => [
                'min'          => 1500, 'max' => 3500, // 1.5-3.5 seconds
                'distribution' => 'normal',
            ],
            'ticket_check' => [
                'min'          => 3000, 'max' => 8000, // 3-8 seconds (high-demand tickets)
                'distribution' => 'exponential',
            ],
            'navigation' => [
                'min'          => 800, 'max' => 2000, // 0.8-2 seconds
                'distribution' => 'uniform',
            ],
        ];

        $pattern = $delayPatterns[$action] ?? $delayPatterns['page_load'];
        $delay = $this->generateDelayWithDistribution($pattern);

        // Add platform-specific multipliers for high-demand sites
        $multipliers = [
            'real_madrid'     => 1.3,
            'barcelona'       => 1.3,
            'bayern_munich'   => 1.2,
            'manchester_city' => 1.4, // Highest due to Premier League demand
            'juventus'        => 1.1,
            'psg'             => 1.2,
        ];

        $delay *= ($multipliers[$platform] ?? 1.0);

        Log::info('Anti-detection delay applied', [
            'platform' => $platform,
            'action'   => $action,
            'delay_ms' => $delay,
        ]);

        usleep($delay * 1000); // Convert to microseconds
    }

    /**
     * Create HTTP client with advanced anti-detection
     */
    public function createAdvancedHttpClient(string $platform, array $options = []): Client
    {
        $session = $this->getBrowserSession($platform);
        $headers = $this->generateAdvancedHeaders($platform);

        $defaultOptions = [
            RequestOptions::HEADERS         => $headers,
            RequestOptions::COOKIES         => $this->getSessionCookies($platform),
            RequestOptions::TIMEOUT         => 45,
            RequestOptions::CONNECT_TIMEOUT => 15,
            RequestOptions::HTTP_ERRORS     => FALSE,
            RequestOptions::ALLOW_REDIRECTS => [
                'max'             => 3,
                'strict'          => FALSE,
                'referer'         => TRUE,
                'protocols'       => ['http', 'https'],
                'track_redirects' => TRUE,
            ],
            RequestOptions::VERIFY => FALSE, // For development - enable in production
            'curl'                 => [
                CURLOPT_TCP_NODELAY    => 1,
                CURLOPT_TCP_FASTOPEN   => 1,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
                CURLOPT_SSL_VERIFYPEER => FALSE, // For development
                CURLOPT_SSL_VERIFYHOST => FALSE, // For development
                CURLOPT_ENCODING       => '', // Enable all supported encodings
                CURLOPT_FOLLOWLOCATION => FALSE, // Handle redirects manually
            ],
        ];

        return new Client(array_merge_recursive($defaultOptions, $options));
    }

    /**
     * Handle JavaScript challenges and CAPTCHA detection
     */
    public function handleJavaScriptChallenge(string $html, string $platform): ?array
    {
        $challenges = [
            'cloudflare' => '/challenge-form/',
            'imperva'    => '/distil_r_blocked.html/',
            'datadome'   => '/datadome/',
            'perimeterx' => '/_pxCustomerLogo/',
            'recaptcha'  => '/recaptcha/',
            'hcaptcha'   => '/hcaptcha/',
        ];

        foreach ($challenges as $provider => $pattern) {
            if (preg_match($pattern, $html)) {
                Log::warning('Anti-bot challenge detected', [
                    'platform' => $platform,
                    'provider' => $provider,
                    'action'   => 'challenge_detected',
                ]);

                return [
                    'challenge_detected' => TRUE,
                    'provider'           => $provider,
                    'requires_solving'   => TRUE,
                    'retry_after'        => $this->calculateRetryDelay($provider),
                ];
            }
        }

        return NULL;
    }

    /**
     * Rotate session and reset fingerprints
     */
    public function rotateSession(string $platform): void
    {
        $cacheKeys = [
            "browser_session_{$platform}_" . date('H'),
            "session_cookies_{$platform}",
            "user_agent_{$platform}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::info('Session rotated for platform', ['platform' => $platform]);
    }

    /**
     * Check if platform is currently under rate limiting
     */
    public function isRateLimited(string $platform): bool
    {
        $limitKey = "rate_limit_{$platform}";

        return Cache::has($limitKey);
    }

    /**
     * Apply rate limiting for platform
     */
    public function applyRateLimit(string $platform, int $seconds = 300): void
    {
        $limitKey = "rate_limit_{$platform}";
        Cache::put($limitKey, TRUE, $seconds);

        Log::info('Rate limit applied', [
            'platform'         => $platform,
            'duration_seconds' => $seconds,
        ]);
    }

    /**
     * Initialize realistic browser fingerprints
     */
    protected function initializeBrowserFingerprints(): void
    {
        $this->browserFingerprints = [
            'chrome_windows' => [
                'viewport'             => ['1920x1080', '1366x768', '1536x864', '1440x900'],
                'screen'               => ['1920x1080', '1366x768', '1536x864', '1440x900'],
                'languages'            => ['en-US,en;q=0.9', 'en-GB,en;q=0.9', 'de-DE,de;q=0.9', 'es-ES,es;q=0.9'],
                'timezone'             => ['Europe/London', 'Europe/Berlin', 'Europe/Madrid', 'Europe/Paris'],
                'platform'             => 'Win32',
                'hardware_concurrency' => [4, 8, 12, 16],
                'device_memory'        => [4, 8, 16],
            ],
            'chrome_mac' => [
                'viewport'             => ['1440x900', '1680x1050', '1920x1080'],
                'screen'               => ['1440x900', '1680x1050', '1920x1080'],
                'languages'            => ['en-US,en;q=0.9', 'en-GB,en;q=0.9'],
                'timezone'             => ['Europe/London', 'America/New_York'],
                'platform'             => 'MacIntel',
                'hardware_concurrency' => [4, 8, 10],
                'device_memory'        => [8, 16, 32],
            ],
            'firefox_windows' => [
                'viewport'             => ['1920x1080', '1366x768', '1536x864'],
                'screen'               => ['1920x1080', '1366x768', '1536x864'],
                'languages'            => ['en-US,en;q=0.5', 'de-DE,de;q=0.5', 'es-ES,es;q=0.5'],
                'timezone'             => ['Europe/London', 'Europe/Berlin', 'Europe/Madrid'],
                'platform'             => 'Win32',
                'hardware_concurrency' => [4, 8, 12],
                'device_memory'        => [4, 8, 16],
            ],
            'safari_mac' => [
                'viewport'             => ['1440x900', '1680x1050', '1920x1080', '2560x1600'],
                'screen'               => ['1440x900', '1680x1050', '1920x1080', '2560x1600'],
                'languages'            => ['en-US,en;q=0.9', 'en-GB,en;q=0.9'],
                'timezone'             => ['Europe/London', 'America/New_York', 'Europe/Paris'],
                'platform'             => 'MacIntel',
                'hardware_concurrency' => [4, 8, 10, 12],
                'device_memory'        => [8, 16, 32],
            ],
        ];
    }

    /**
     * Initialize TLS fingerprints to mimic real browsers
     */
    protected function initializeTlsFingerprints(): void
    {
        $this->tlsFingerprints = [
            'chrome_120' => [
                'cipher_suites' => [
                    'TLS_AES_128_GCM_SHA256',
                    'TLS_AES_256_GCM_SHA384',
                    'TLS_CHACHA20_POLY1305_SHA256',
                    'TLS_ECDHE_ECDSA_WITH_AES_128_GCM_SHA256',
                    'TLS_ECDHE_RSA_WITH_AES_128_GCM_SHA256',
                ],
                'extensions' => [
                    'server_name',
                    'supported_groups',
                    'signature_algorithms',
                    'application_layer_protocol_negotiation',
                    'status_request',
                ],
                'curves' => ['X25519', 'secp256r1', 'secp384r1'],
            ],
            'firefox_120' => [
                'cipher_suites' => [
                    'TLS_AES_128_GCM_SHA256',
                    'TLS_CHACHA20_POLY1305_SHA256',
                    'TLS_AES_256_GCM_SHA384',
                    'TLS_ECDHE_ECDSA_WITH_AES_128_GCM_SHA256',
                    'TLS_ECDHE_RSA_WITH_AES_128_GCM_SHA256',
                ],
                'extensions' => [
                    'server_name',
                    'supported_groups',
                    'signature_algorithms',
                    'application_layer_protocol_negotiation',
                    'status_request',
                    'delegated_credentials',
                ],
                'curves' => ['X25519', 'secp256r1', 'secp384r1', 'secp521r1'],
            ],
        ];
    }

    /**
     * Initialize sophisticated user agents with version randomization
     */
    protected function initializeUserAgents(): void
    {
        $chromeVersions = ['119.0.0.0', '120.0.0.0', '121.0.0.0', '122.0.0.0'];
        $firefoxVersions = ['119.0', '120.0', '121.0', '122.0'];
        $safariVersions = ['17.1', '17.2', '17.3'];

        $this->userAgents = [
            'chrome_windows' => array_map(
                fn ($v) => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$v} Safari/537.36",
                $chromeVersions,
            ),
            'chrome_mac' => array_map(
                fn ($v) => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$v} Safari/537.36",
                $chromeVersions,
            ),
            'firefox_windows' => array_map(
                fn ($v) => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:{$v}) Gecko/20100101 Firefox/{$v}",
                $firefoxVersions,
            ),
            'safari_mac' => array_map(
                fn ($v) => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$v} Safari/605.1.15",
                $safariVersions,
            ),
        ];
    }

    /**
     * Initialize session pools for different platforms
     */
    protected function initializeSessionPools(): void
    {
        $this->sessionPools = [];
        foreach (['real_madrid', 'barcelona', 'bayern_munich', 'juventus', 'psg', 'manchester_city'] as $platform) {
            $this->sessionPools[$platform] = [
                'sessions'         => [],
                'rotation_index'   => 0,
                'last_used'        => [],
                'cooldown_periods' => [],
            ];
        }
    }

    /**
     * Select appropriate browser type based on platform demographics
     */
    protected function selectBrowserType(string $platform): string
    {
        $preferences = [
            'real_madrid'     => ['chrome_windows' => 0.4, 'chrome_mac' => 0.2, 'firefox_windows' => 0.3, 'safari_mac' => 0.1],
            'barcelona'       => ['chrome_windows' => 0.45, 'chrome_mac' => 0.15, 'firefox_windows' => 0.35, 'safari_mac' => 0.05],
            'bayern_munich'   => ['chrome_windows' => 0.5, 'chrome_mac' => 0.2, 'firefox_windows' => 0.25, 'safari_mac' => 0.05],
            'juventus'        => ['chrome_windows' => 0.4, 'chrome_mac' => 0.2, 'firefox_windows' => 0.3, 'safari_mac' => 0.1],
            'psg'             => ['chrome_windows' => 0.4, 'chrome_mac' => 0.25, 'firefox_windows' => 0.25, 'safari_mac' => 0.1],
            'manchester_city' => ['chrome_windows' => 0.35, 'chrome_mac' => 0.3, 'firefox_windows' => 0.2, 'safari_mac' => 0.15],
        ];

        $weights = $preferences[$platform] ?? $preferences['real_madrid'];
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;

        foreach ($weights as $browser => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $browser;
            }
        }

        return 'chrome_windows';
    }

    /**
     * Get random user agent for browser type
     */
    protected function getRandomUserAgent(string $browserType): string
    {
        $agents = $this->userAgents[$browserType] ?? $this->userAgents['chrome_windows'];

        return $agents[array_rand($agents)];
    }

    /**
     * Generate Accept header based on browser type
     */
    protected function getAcceptHeader(string $browserType): string
    {
        $acceptHeaders = [
            'chrome_windows'  => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'chrome_mac'      => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'firefox_windows' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'safari_mac'      => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];

        return $acceptHeaders[$browserType] ?? $acceptHeaders['chrome_windows'];
    }

    /**
     * Generate sec-ch-ua header
     */
    protected function generateSecChUa(string $browserType): string
    {
        $brands = [
            'chrome_windows'  => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'chrome_mac'      => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'firefox_windows' => '"Not_A Brand";v="99", "Firefox";v="120"',
            'safari_mac'      => '"Not_A Brand";v="99", "Safari";v="17"',
        ];

        return $brands[$browserType] ?? $brands['chrome_windows'];
    }

    /**
     * Generate full version list for Chrome
     */
    protected function generateFullVersionList(): string
    {
        return '"Not_A Brand";v="8.0.0.0", "Chromium";v="120.0.6099.109", "Google Chrome";v="120.0.6099.109"';
    }

    /**
     * Generate delay with specific distribution
     */
    protected function generateDelayWithDistribution(array $pattern): int
    {
        $min = $pattern['min'];
        $max = $pattern['max'];
        $distribution = $pattern['distribution'];

        switch ($distribution) {
            case 'normal':
                $mean = ($min + $max) / 2;
                $stddev = ($max - $min) / 6;

                return max($min, min($max, $this->normalRandom($mean, $stddev)));
            case 'exponential':
                $lambda = 1 / (($min + $max) / 2 - $min);

                return $min + (-log(1 - mt_rand() / mt_getrandmax()) / $lambda);
            case 'uniform':
            default:
                return mt_rand($min, $max);
        }
    }

    /**
     * Generate normal distribution random number
     */
    protected function normalRandom(float $mean, float $stddev): int
    {
        static $hasSpare = FALSE;
        static $spare;

        if ($hasSpare) {
            $hasSpare = FALSE;

            return (int) ($spare * $stddev + $mean);
        }

        $hasSpare = TRUE;
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        $mag = $stddev * sqrt(-2.0 * log($u));
        $spare = $mag * cos(2.0 * M_PI * $v);

        return (int) ($mag * sin(2.0 * M_PI * $v) + $mean);
    }

    /**
     * Get session cookies for platform
     */
    protected function getSessionCookies(string $platform): CookieJar
    {
        $cacheKey = "session_cookies_{$platform}";

        $cookies = Cache::get($cacheKey, []);
        $jar = new CookieJar();

        foreach ($cookies as $cookie) {
            $jar->setCookie(new \GuzzleHttp\Cookie\SetCookie($cookie));
        }

        return $jar;
    }

    /**
     * Calculate retry delay based on challenge provider
     */
    protected function calculateRetryDelay(string $provider): int
    {
        $delays = [
            'cloudflare' => 300, // 5 minutes
            'imperva'    => 600,    // 10 minutes
            'datadome'   => 180,   // 3 minutes
            'perimeterx' => 240, // 4 minutes
            'recaptcha'  => 120,  // 2 minutes
            'hcaptcha'   => 120,   // 2 minutes
        ];

        return $delays[$provider] ?? 300;
    }
}
