<?php

namespace App\Services\TicketApis;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * Make HTTP request with retry logic and caching
     */
    protected function makeRequest(string $method, string $endpoint, array $params = [], bool $useCache = true): array
    {
        $cacheKey = $this->getCacheKey($method, $endpoint, $params);

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->executeRequest($method, $endpoint, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($useCache) {
                        Cache::put($cacheKey, $data, $this->getCacheTtl());
                    }

                    return $data;
                }

                throw new Exception("API request failed with status: " . $response->status());

            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay);
                }

                Log::warning("API request attempt {$attempt} failed", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::error("API request failed after {$this->retryAttempts} attempts", [
            'endpoint' => $endpoint,
            'error' => $lastException->getMessage()
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
        return $this->config['enabled'] ?? false;
    }

    /**
     * Transform API response to standard format
     */
    abstract protected function transformEventData(array $eventData): array;
}
