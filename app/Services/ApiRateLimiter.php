<?php declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ApiRateLimiter
{
    /**
     * Check if API call is within rate limits
     *
     * @param string $platform Platform name to check
     * @param string $endpoint Endpoint name for granular limiting
     *
     * @return bool Whether the request can be made
     */
    /**
     * Check if can  make request
     */
    public function canMakeRequest(string $platform, string $endpoint = 'default'): bool
    {
        $config = config("ticket_apis.{$platform}.rate_limit");

        if (! $config) {
            return TRUE;
        }

        return $this->checkPerSecondLimit($platform, $endpoint, $config)
               && $this->checkPerHourLimit($platform, $endpoint, $config)
               && $this->checkPerDayLimit($platform, $endpoint, $config);
    }

    /**
     * Record an API request
     *
     * @param string $platform Platform name
     * @param string $endpoint Endpoint name for granular tracking
     */
    /**
     * RecordRequest
     */
    public function recordRequest(string $platform, string $endpoint = 'default'): void
    {
        $timestamp = now();

        // Record for per-second limit
        $secondKey = "api_requests:{$platform}:{$endpoint}:second:" . $timestamp->format('Y-m-d-H-i-s');
        Cache::increment($secondKey, 1);
        Cache::put($secondKey, Cache::get($secondKey, 0), 2); // Expire after 2 seconds

        // Record for per-hour limit
        $hourKey = "api_requests:{$platform}:{$endpoint}:hour:" . $timestamp->format('Y-m-d-H');
        Cache::increment($hourKey, 1);
        Cache::put($hourKey, Cache::get($hourKey, 0), 3660); // Expire after 61 minutes

        // Record for per-day limit
        $dayKey = "api_requests:{$platform}:{$endpoint}:day:" . $timestamp->format('Y-m-d');
        Cache::increment($dayKey, 1);
        Cache::put($dayKey, Cache::get($dayKey, 0), 86460); // Expire after 24+ hours
    }

    /**
     * Get time until next request is allowed
     *
     * @param string $platform Platform name
     * @param string $endpoint Endpoint name
     *
     * @return int Wait time in seconds
     */
    /**
     * Get  wait time
     */
    public function getWaitTime(string $platform, string $endpoint = 'default'): int
    {
        $config = config("ticket_apis.{$platform}.rate_limit");

        if (! $config) {
            return 0;
        }

        $waitTimes = [];

        // Check per-second limit
        if (isset($config['requests_per_second'])) {
            $secondKey = "api_requests:{$platform}:{$endpoint}:second:" . now()->format('Y-m-d-H-i-s');
            $currentSecond = Cache::get($secondKey, 0);

            if ($currentSecond >= $config['requests_per_second']) {
                $waitTimes[] = 1; // Wait 1 second
            }
        }

        return max([0, ...$waitTimes]);
    }

    /**
     * Check per-second rate limit
     *
     * @param string               $platform Platform name
     * @param string               $endpoint Endpoint name
     * @param array<string, mixed> $config   Rate limit configuration
     *
     * @return bool Whether limit is not exceeded
     */
    /**
     * CheckPerSecondLimit
     */
    protected function checkPerSecondLimit(string $platform, string $endpoint, array $config): bool
    {
        if (! isset($config['requests_per_second'])) {
            return TRUE;
        }

        $secondKey = "api_requests:{$platform}:{$endpoint}:second:" . now()->format('Y-m-d-H-i-s');
        $currentSecond = Cache::get($secondKey, 0);

        return $currentSecond < $config['requests_per_second'];
    }

    /**
     * Check per-hour rate limit
     *
     * @param string               $platform Platform name
     * @param string               $endpoint Endpoint name
     * @param array<string, mixed> $config   Rate limit configuration
     *
     * @return bool Whether limit is not exceeded
     */
    /**
     * CheckPerHourLimit
     */
    protected function checkPerHourLimit(string $platform, string $endpoint, array $config): bool
    {
        if (! isset($config['requests_per_hour'])) {
            return TRUE;
        }

        $hourKey = "api_requests:{$platform}:{$endpoint}:hour:" . now()->format('Y-m-d-H');
        $currentHour = Cache::get($hourKey, 0);

        return $currentHour < $config['requests_per_hour'];
    }

    /**
     * Check per-day rate limit
     *
     * @param string               $platform Platform name
     * @param string               $endpoint Endpoint name
     * @param array<string, mixed> $config   Rate limit configuration
     *
     * @return bool Whether limit is not exceeded
     */
    /**
     * CheckPerDayLimit
     */
    protected function checkPerDayLimit(string $platform, string $endpoint, array $config): bool
    {
        if (! isset($config['requests_per_day'])) {
            return TRUE;
        }

        $dayKey = "api_requests:{$platform}:{$endpoint}:day:" . now()->format('Y-m-d');
        $currentDay = Cache::get($dayKey, 0);

        return $currentDay < $config['requests_per_day'];
    }
}
