<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use App\Exceptions\RateLimitException;
use App\Exceptions\TicketPlatformException;
use App\Exceptions\TimeoutException;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;
use function get_class;
use function is_array;

abstract class BaseApiClient
{
    protected $config;

    protected $baseUrl;

    protected $timeout;

    protected $retryAttempts;

    protected $retryDelay;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['base_url'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;
        $this->retryAttempts = $config['retry_attempts'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 1;
    }

    /**
     * Search for events
     */
    abstract public function searchEvents(array $criteria): array;

    /**
     * Get event details by ID
     */
    abstract public function getEvent(string $eventId): array;

    /**
     * Get venue details
     */
    abstract public function getVenue(string $venueId): array;

    /**
     * Check if API is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? FALSE;
    }

    /**
     * Check if scraping fallback is available and enabled
     */
    public function hasScrapingFallback(): bool
    {
        return isset($this->config['scraping']['enabled']) && $this->config['scraping']['enabled'];
    }

    /**
     * Attempt to fall back to scraping method
     * This method should be implemented by child classes that support scraping
     */
    public function fallbackToScraping(array $criteria): array
    {
        throw new TicketPlatformException(
            "Scraping fallback not implemented for {$this->getPlatformName()}",
            500,
            NULL,
            $this->getPlatformName(),
            'scraping',
        );
    }

    /**
     * Get the base URL for the platform
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Make HTTP request with enhanced error handling, retry logic and caching
     */
    protected function makeRequest(string $method, string $endpoint, array $params = [], bool $useCache = TRUE): array
    {
        $startTime = microtime(TRUE);
        $platform = $this->getPlatformName();
        $cacheKey = $this->getCacheKey($method, $endpoint, $params);

        if ($useCache && Cache::has($cacheKey)) {
            Log::channel('ticket_apis')->info('Cache hit for API request', [
                'platform' => $platform,
                'endpoint' => $endpoint,
                'method'   => $method,
            ]);

            return Cache::get($cacheKey);
        }

        $attempt = 0;
        $lastException = NULL;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->executeRequest($method, $endpoint, $params);
                $responseTime = (microtime(TRUE) - $startTime) * 1000; // Convert to milliseconds

                // Handle successful response
                if ($response->successful()) {
                    $data = $response->json();

                    if ($useCache) {
                        Cache::put($cacheKey, $data, $this->getCacheTtl());
                    }

                    // Log successful API request
                    Log::channel('ticket_apis')->info('API request successful', [
                        'platform'         => $platform,
                        'endpoint'         => $endpoint,
                        'method'           => 'api',
                        'response_time_ms' => $responseTime,
                        'results_count'    => is_array($data) ? count($data) : 1,
                        'attempt'          => $attempt + 1,
                    ]);

                    return $data;
                }

                // Handle different HTTP error codes
                $this->handleHttpError($response, $platform);
            } catch (ConnectionException $e) {
                $lastException = new TimeoutException(
                    "Connection timeout for {$platform}: " . $e->getMessage(),
                    $platform,
                    'api',
                );
            } catch (RequestException $e) {
                if (str_contains($e->getMessage(), 'timeout')) {
                    $lastException = new TimeoutException(
                        "Request timeout for {$platform}: " . $e->getMessage(),
                        $platform,
                        'api',
                    );
                } else {
                    $lastException = $this->createPlatformException(
                        "Request failed for {$platform}: " . $e->getMessage(),
                        $platform,
                    );
                }
            } catch (TicketPlatformException $e) {
                $lastException = $e;

                // Don't retry rate limit exceptions immediately
                if ($e instanceof RateLimitException && $e->getRetryAfter()) {
                    sleep($e->getRetryAfter());
                }
            } catch (Exception $e) {
                $lastException = $this->createPlatformException(
                    "Unexpected error for {$platform}: " . $e->getMessage(),
                    $platform,
                );
            }

            $attempt++;

            // Log the failed attempt
            Log::channel('ticket_apis')->warning("API request attempt {$attempt} failed", [
                'platform'   => $platform,
                'endpoint'   => $endpoint,
                'method'     => 'api',
                'attempt'    => $attempt,
                'error'      => $lastException->getMessage(),
                'error_type' => get_class($lastException),
            ]);

            // Apply exponential backoff for retries
            if ($attempt < $this->retryAttempts) {
                $backoffDelay = $this->retryDelay * (2 ** ($attempt - 1));
                sleep($backoffDelay);
            }
        }

        $totalTime = (microtime(TRUE) - $startTime) * 1000;

        // Log final failure
        Log::channel('ticket_apis')->error("API request failed after {$this->retryAttempts} attempts", [
            'platform'      => $platform,
            'endpoint'      => $endpoint,
            'method'        => 'api',
            'total_time_ms' => $totalTime,
            'final_error'   => $lastException->getMessage(),
            'error_type'    => get_class($lastException),
        ]);

        throw $lastException;
    }

    /**
     * Execute the actual HTTP request
     */
    protected function executeRequest(string $method, string $endpoint, array $params = []): Response
    {
        $http = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders());

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        switch (strtoupper($method)) {
            case 'GET':
                return $http->get($url, $params);
            case 'POST':
                return $http->post($url, $params);
            case 'PUT':
                return $http->put($url, $params);
            case 'DELETE':
                return $http->delete($url, $params);
            default:
                throw new Exception("Unsupported HTTP method: {$method}");
        }
    }

    /**
     * Generate cache key for request
     */
    protected function getCacheKey(string $method, string $endpoint, array $params): string
    {
        return 'api_' . static::class . '_' . md5($method . $endpoint . serialize($params));
    }

    /**
     * Get cache TTL from config
     */
    protected function getCacheTtl(): int
    {
        return config('ticket_apis.cache_ttl', 3600);
    }

    /**
     * Get HTTP headers for requests
     */
    abstract protected function getHeaders(): array;

    /**
     * Transform API response to standard format
     */
    abstract protected function transformEventData(array $eventData): array;

    /**
     * Get platform name for logging and error handling
     */
    protected function getPlatformName(): string
    {
        $className = get_class($this);
        $platformName = str_replace(['App\\Services\\TicketApis\\', 'Client'], '', $className);

        return strtolower($platformName);
    }

    /**
     * Handle HTTP error responses
     */
    protected function handleHttpError(Response $response, string $platform): void
    {
        $statusCode = $response->status();
        $body = $response->body();

        switch ($statusCode) {
            case 429:
                $retryAfter = $response->header('Retry-After') ?? $response->header('X-RateLimit-Reset') ?? 60;

                throw new RateLimitException(
                    "Rate limit exceeded for {$platform}",
                    is_numeric($retryAfter) ? (int) $retryAfter : 60,
                    $platform,
                );

            case 401:
                throw $this->createPlatformException(
                    "Authentication failed for {$platform}: Invalid API credentials",
                    $platform,
                    $statusCode,
                );

            case 403:
                throw $this->createPlatformException(
                    "Access forbidden for {$platform}: Insufficient permissions",
                    $platform,
                    $statusCode,
                );

            case 404:
                throw $this->createPlatformException(
                    "Resource not found on {$platform}",
                    $platform,
                    $statusCode,
                );

            case 500:
            case 502:
            case 503:
            case 504:
                throw $this->createPlatformException(
                    "Server error on {$platform} (HTTP {$statusCode})",
                    $platform,
                    $statusCode,
                );

            default:
                throw $this->createPlatformException(
                    "API request failed for {$platform} with status {$statusCode}: {$body}",
                    $platform,
                    $statusCode,
                );
        }
    }

    /**
     * Create platform-specific exception
     */
    protected function createPlatformException(string $message, string $platform, int $code = 0): TicketPlatformException
    {
        $exceptionClass = 'App\\Exceptions\\' . ucfirst($platform) . 'Exception';

        if (class_exists($exceptionClass)) {
            return new $exceptionClass($message, $code, NULL, $platform, 'api');
        }

        return new TicketPlatformException($message, $code, NULL, $platform, 'api');
    }
}
