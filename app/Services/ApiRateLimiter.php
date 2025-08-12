<?php declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ApiRateLimiter
{
    /**
     * Check if API call is within rate limits
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

    protected function checkPerSecondLimit(string $platform, string $endpoint, array $config): bool
    {
        if (! isset($config['requests_per_second'])) {
            return TRUE;
        }

        $secondKey = "api_requests:{$platform}:{$endpoint}:second:" . now()->format('Y-m-d-H-i-s');
        $currentSecond = Cache::get($secondKey, 0);

        return $currentSecond < $config['requests_per_second'];
    }

    protected function checkPerHourLimit(string $platform, string $endpoint, array $config): bool
    {
        if (! isset($config['requests_per_hour'])) {
            return TRUE;
        }

        $hourKey = "api_requests:{$platform}:{$endpoint}:hour:" . now()->format('Y-m-d-H');
        $currentHour = Cache::get($hourKey, 0);

        return $currentHour < $config['requests_per_hour'];
    }

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
