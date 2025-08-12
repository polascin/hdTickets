<?php declare(strict_types=1);

namespace App\Services\Scraping\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function in_array;

trait RateLimitingTrait
{
    /**
     * Apply rate limiting for a specific platform
     */
    protected function applyRateLimit(string $platform): void
    {
        $cacheKey = "rate_limit_{$platform}";
        $lastRequest = Cache::get($cacheKey);

        if ($lastRequest) {
            $timeDiff = microtime(TRUE) - $lastRequest;
            $requiredDelay = $this->rateLimitSeconds ?? 2;

            if ($timeDiff < $requiredDelay) {
                $sleepTime = ($requiredDelay - $timeDiff) * 1000000; // Convert to microseconds
                usleep((int) $sleepTime);

                Log::debug('Rate limit applied', [
                    'platform'      => $platform,
                    'delay_seconds' => $requiredDelay - $timeDiff,
                ]);
            }
        }

        Cache::put($cacheKey, microtime(TRUE), 300); // Store for 5 minutes
    }

    /**
     * Get adaptive rate limit based on platform origin
     */
    protected function getAdaptiveRateLimit(): int
    {
        // European sites tend to be more restrictive
        return match ($this->language ?? 'en-GB') {
            'es-ES' => 4, // Spanish sites - more restrictive
            'de-DE' => 3, // German sites - strict
            'it-IT' => 3, // Italian sites - strict
            'fr-FR' => 3, // French sites - strict
            default => 2, // UK sites - moderate
        };
    }

    /**
     * Apply exponential backoff for failed requests
     */
    protected function applyBackoff(int $attempt): void
    {
        $baseDelay = 1; // 1 second base
        $maxDelay = 30; // 30 seconds max

        $delay = min($baseDelay * pow(2, $attempt), $maxDelay);
        $jitter = $delay * 0.1 * mt_rand(0, 1000) / 1000; // Add 10% jitter

        $totalDelay = $delay + $jitter;

        Log::info('Applying exponential backoff', [
            'attempt'       => $attempt,
            'delay_seconds' => $totalDelay,
            'platform'      => $this->platform ?? 'unknown',
        ]);

        usleep((int) ($totalDelay * 1000000));
    }

    /**
     * Check if rate limit is exceeded
     */
    protected function isRateLimitExceeded(string $platform): bool
    {
        $windowKey = "rate_limit_window_{$platform}";
        $requestsKey = "rate_limit_requests_{$platform}";

        $window = Cache::get($windowKey, time());
        $requests = Cache::get($requestsKey, 0);

        // Reset window if it's been more than 60 seconds
        if (time() - $window > 60) {
            Cache::put($windowKey, time(), 300);
            Cache::put($requestsKey, 0, 300);
            $requests = 0;
        }

        // European sites: max 20 requests per minute
        // UK sites: max 30 requests per minute
        $maxRequestsPerMinute = match ($this->language ?? 'en-GB') {
            'es-ES', 'de-DE', 'it-IT', 'fr-FR' => 15,
            default => 25,
        };

        if ($requests >= $maxRequestsPerMinute) {
            Log::warning('Rate limit exceeded', [
                'platform'    => $platform,
                'requests'    => $requests,
                'max_allowed' => $maxRequestsPerMinute,
            ]);

            return TRUE;
        }

        // Increment request counter
        Cache::put($requestsKey, $requests + 1, 300);

        return FALSE;
    }

    /**
     * Get intelligent delay based on time of day and platform
     */
    protected function getIntelligentDelay(): int
    {
        $hour = (int) date('H');
        $baseDelay = $this->rateLimitSeconds ?? 2;

        // European business hours (9-17 CET) - be more conservative
        if ($this->isEuropeanPlatform() && $hour >= 8 && $hour <= 16) {
            return $baseDelay * 2;
        }

        // Peak usage hours - be more conservative
        if ($hour >= 18 && $hour <= 22) {
            return (int) ($baseDelay * 1.5);
        }

        // Off-peak hours - can be more aggressive
        if ($hour >= 2 && $hour <= 6) {
            return max(1, (int) ($baseDelay * 0.7));
        }

        return $baseDelay;
    }

    /**
     * Check if this is a European platform
     */
    protected function isEuropeanPlatform(): bool
    {
        return in_array($this->language ?? 'en-GB', ['es-ES', 'de-DE', 'it-IT', 'fr-FR'], TRUE);
    }
}
