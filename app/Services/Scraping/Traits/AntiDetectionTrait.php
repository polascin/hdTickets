<?php declare(strict_types=1);

namespace App\Services\Scraping\Traits;

use Illuminate\Support\Facades\Log;

use function strlen;

trait AntiDetectionTrait
{
    protected static array $userAgents = [
        // Windows Chrome (most common)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',

        // Mac Chrome
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',

        // Mac Safari
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Safari/605.1.15',

        // Windows Firefox
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/120.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',

        // Linux Chrome (for European servers)
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    ];

    /**
     * Get random user agent
     */
    /**
     * Get  random user agent
     */
    protected function getRandomUserAgent(): string
    {
        return self::$userAgents[array_rand(self::$userAgents)];
    }

    /**
     * Rotate headers to avoid detection
     */
    /**
     * RotateHeaders
     */
    protected function rotateHeaders(): array
    {
        $baseHeaders = [
            'User-Agent'                => $this->getRandomUserAgent(),
            'Accept'                    => $this->getRandomAcceptHeader(),
            'Accept-Language'           => $this->getAcceptLanguageHeader(),
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => mt_rand(0, 1) ? '1' : NULL,
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Cache-Control'             => mt_rand(0, 1) ? 'max-age=0' : 'no-cache',
        ];

        // Randomly add optional headers
        $optionalHeaders = [
            'Sec-Fetch-Dest' => ['document', 'empty'],
            'Sec-Fetch-Mode' => ['navigate', 'cors', 'same-origin'],
            'Sec-Fetch-Site' => ['none', 'same-origin', 'cross-site'],
            'Sec-CH-UA'      => [
                '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                '"Not_A Brand";v="99", "Google Chrome";v="119", "Chromium";v="119"',
            ],
            'Sec-CH-UA-Platform' => ['"Windows"', '"macOS"', '"Linux"'],
            'Sec-CH-UA-Mobile'   => ['?0'],
        ];

        foreach ($optionalHeaders as $header => $values) {
            if (mt_rand(0, 1)) {
                $baseHeaders[$header] = $values[array_rand($values)];
            }
        }

        // Add referer occasionally
        if (mt_rand(0, 3) === 0) {
            $baseHeaders['Referer'] = $this->baseUrl ?? 'https://www.google.com/';
        }

        // Filter out null values
        return array_filter($baseHeaders, fn ($value) => $value !== NULL);
    }

    /**
     * Get random Accept header
     */
    /**
     * Get  random accept header
     */
    protected function getRandomAcceptHeader(): string
    {
        $acceptHeaders = [
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        ];

        return $acceptHeaders[array_rand($acceptHeaders)];
    }

    /**
     * Add random delay to simulate human behavior
     */
    /**
     * RandomDelay
     */
    protected function randomDelay(): void
    {
        // European sites are more sensitive, add longer delays
        $minDelay = $this->isEuropeanPlatform() ? 2 : 1;
        $maxDelay = $this->isEuropeanPlatform() ? 6 : 4;

        $delay = mt_rand($minDelay * 1000, $maxDelay * 1000) / 1000; // Random delay between min-max seconds

        Log::debug('Random delay applied', [
            'delay_seconds' => $delay,
            'platform'      => $this->platform ?? 'unknown',
            'is_european'   => $this->isEuropeanPlatform(),
        ]);

        usleep((int) ($delay * 1000000));
    }

    /**
     * Simulate human-like browsing patterns
     */
    /**
     * SimulateHumanBehavior
     */
    protected function simulateHumanBehavior(): void
    {
        // Occasionally add longer pauses (simulate reading)
        if (mt_rand(1, 10) === 1) {
            $readingDelay = mt_rand(3, 8);
            Log::debug('Simulating reading behavior', [
                'delay_seconds' => $readingDelay,
                'platform'      => $this->platform ?? 'unknown',
            ]);
            sleep($readingDelay);
        }

        // Random micro-delays to simulate scrolling/interaction
        if (mt_rand(1, 5) === 1) {
            usleep(mt_rand(100000, 500000)); // 0.1-0.5 seconds
        }
    }

    /**
     * Get realistic viewport dimensions
     */
    /**
     * Get  viewport dimensions
     */
    protected function getViewportDimensions(): array
    {
        $commonResolutions = [
            ['width' => 1920, 'height' => 1080],
            ['width' => 1366, 'height' => 768],
            ['width' => 1536, 'height' => 864],
            ['width' => 1440, 'height' => 900],
            ['width' => 1280, 'height' => 720],
        ];

        return $commonResolutions[array_rand($commonResolutions)];
    }

    /**
     * Detect and handle bot detection mechanisms
     */
    /**
     * HandleBotDetection
     */
    protected function handleBotDetection(string $html): bool
    {
        // Common bot detection indicators
        $botDetectionPatterns = [
            'captcha',
            'cloudflare',
            'bot detected',
            'access denied',
            'blocked',
            'suspicious activity',
            'unusual traffic',
            // Multi-language patterns
            'acceso denegado',
            'zugriff verweigert',
            'accesso negato',
            'accès refusé',
        ];

        $html = strtolower($html);

        foreach ($botDetectionPatterns as $pattern) {
            if (str_contains($html, $pattern)) {
                Log::warning('Bot detection mechanism detected', [
                    'pattern'  => $pattern,
                    'platform' => $this->platform ?? 'unknown',
                ]);

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Generate session-like behavior
     */
    /**
     * InitializeSession
     */
    protected function initializeSession(): array
    {
        return [
            'session_id'  => $this->generateSessionId(),
            'start_time'  => microtime(TRUE),
            'page_views'  => 0,
            'total_delay' => 0,
        ];
    }

    /**
     * Generate realistic session ID
     */
    /**
     * GenerateSessionId
     */
    protected function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Check if IP rotation is needed
     */
    /**
     * ShouldRotateIP
     */
    protected function shouldRotateIP(): bool
    {
        $rotationKey = "ip_rotation_{$this->platform}";
        $requestCount = cache()->get($rotationKey, 0);

        // Rotate IP every 50 requests for European sites, 100 for others
        $rotationThreshold = $this->isEuropeanPlatform() ? 50 : 100;

        if ($requestCount >= $rotationThreshold) {
            cache()->put($rotationKey, 0, 3600);

            return TRUE;
        }

        cache()->increment($rotationKey, 1);
        cache()->expire($rotationKey, 3600);

        return FALSE;
    }

    /**
     * Add random typos to search queries (simulate human input)
     */
    /**
     * AddRandomTypos
     */
    protected function addRandomTypos(string $query): string
    {
        // Only add typos occasionally and for longer queries
        if (strlen($query) < 5 || mt_rand(1, 10) !== 1) {
            return $query;
        }

        $words = explode(' ', $query);
        $modifiedWords = [];

        foreach ($words as $word) {
            if (strlen($word) > 4 && mt_rand(1, 5) === 1) {
                // Occasionally swap adjacent characters
                $pos = mt_rand(1, strlen($word) - 2);
                $word = substr_replace($word, $word[$pos + 1] . $word[$pos], $pos, 2);
            }
            $modifiedWords[] = $word;
        }

        return implode(' ', $modifiedWords);
    }
}
