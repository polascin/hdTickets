<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Services\Interfaces\ScrapingInterface;
use App\Services\Scraping\Adapters\PlatformAdapterFactory;
use App\Services\Scraping\Traits\AntiDetectionTrait;
use App\Services\Scraping\Traits\RateLimitingTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

use function count;
use function in_array;

/**
 * Unified Scraping Service
 *
 * Consolidates all scraping functionality with platform adapters
 * and unified orchestration for sport events entry ticket monitoring.
 */
class ScrapingService extends BaseService implements ScrapingInterface
{
    use AntiDetectionTrait;
    use RateLimitingTrait;

    private PlatformAdapterFactory $adapterFactory;

    private array $enabledPlatforms = [];

    private array $scrapingMetrics = [];

    /**
     * Scrape tickets from all enabled platforms
     */
    /**
     * ScrapeAllPlatforms
     */
    public function scrapeAllPlatforms(array $criteria): array
    {
        $this->ensureInitialized();
        $this->logOperation('scrapeAllPlatforms', $criteria);

        $results = [];
        $totalResults = 0;
        $errors = [];
        $startTime = microtime(TRUE);

        foreach ($this->enabledPlatforms as $platform) {
            if (!$this->isPlatformEnabled($platform)) {
                continue;
            }

            try {
                $this->enforceRateLimit($platform);
                $adapter = $this->adapterFactory->create($platform);

                $platformResults = $this->scrapePlatform($adapter, $criteria);

                $results[$platform] = [
                    'status'        => 'success',
                    'results'       => $platformResults['data'],
                    'count'         => count($platformResults['data']),
                    'metadata'      => $platformResults['metadata'] ?? [],
                    'response_time' => $platformResults['response_time'] ?? 0,
                ];

                $totalResults += count($platformResults['data']);
                $this->updatePlatformMetrics($platform, TRUE, count($platformResults['data']));
            } catch (Exception $e) {
                $this->handleError($e, 'scrapeAllPlatforms', ['platform' => $platform]);

                $errors[] = [
                    'platform' => $platform,
                    'error'    => $e->getMessage(),
                    'code'     => $e->getCode(),
                ];

                $results[$platform] = [
                    'status'  => 'error',
                    'error'   => $e->getMessage(),
                    'results' => [],
                    'count'   => 0,
                ];

                $this->updatePlatformMetrics($platform, FALSE, 0);
            }
        }

        $totalDuration = (microtime(TRUE) - $startTime) * 1000;

        // Track analytics
        $this->getDependency('analyticsService')->trackEvent('scraping_completed', [
            'total_platforms'      => count($this->enabledPlatforms),
            'successful_platforms' => count($results) - count($errors),
            'total_results'        => $totalResults,
            'duration_ms'          => $totalDuration,
            'criteria'             => $criteria,
        ]);

        return [
            'summary' => [
                'total_platforms'      => count($this->enabledPlatforms),
                'successful_platforms' => count($results) - count($errors),
                'failed_platforms'     => count($errors),
                'total_results'        => $totalResults,
                'duration_ms'          => round($totalDuration, 2),
                'timestamp'            => Carbon::now()->toISOString(),
            ],
            'results' => $results,
            'errors'  => $errors,
        ];
    }

    /**
     * Scrape specific platform
     */
    /**
     * ScrapePlatform
     */
    public function scrapePlatform(string $platform, array $criteria): array
    {
        $this->ensureInitialized();

        if (!$this->isPlatformEnabled($platform)) {
            throw new InvalidArgumentException("Platform '{$platform}' is not enabled");
        }

        $this->enforceRateLimit($platform);
        $adapter = $this->adapterFactory->create($platform);

        return $this->scrapePlatformWithAdapter($adapter, $criteria);
    }

    /**
     * Get available platforms
     */
    /**
     * Get  available platforms
     */
    public function getAvailablePlatforms(): array
    {
        return $this->adapterFactory->getAvailablePlatforms();
    }

    /**
     * Enable platform
     */
    /**
     * EnablePlatform
     */
    public function enablePlatform(string $platform): void
    {
        if (!in_array($platform, $this->enabledPlatforms, TRUE)) {
            $this->enabledPlatforms[] = $platform;
            $this->saveEnabledPlatforms();
        }
    }

    /**
     * Disable platform
     */
    /**
     * DisablePlatform
     */
    public function disablePlatform(string $platform): void
    {
        $this->enabledPlatforms = array_filter(
            $this->enabledPlatforms,
            fn ($p) => $p !== $platform,
        );
        $this->saveEnabledPlatforms();
    }

    /**
     * Get scraping statistics
     */
    /**
     * Get  scraping statistics
     */
    public function getScrapingStatistics(): array
    {
        return [
            'platforms'     => $this->getPlatformStatistics(),
            'overall'       => $this->getOverallStatistics(),
            'health_status' => $this->getScrapingHealthStatus(),
        ];
    }

    /**
     * Schedule recurring scraping job
     */
    /**
     * ScheduleRecurringScraping
     */
    public function scheduleRecurringScraping(array $criteria, int $intervalMinutes = 30): string
    {
        $jobId = 'scraping_' . uniqid();

        Cache::put("scraping_schedule_{$jobId}", [
            'criteria'   => $criteria,
            'interval'   => $intervalMinutes,
            'next_run'   => Carbon::now()->addMinutes($intervalMinutes)->toISOString(),
            'created_at' => Carbon::now()->toISOString(),
            'status'     => 'active',
        ], 86400 * 7); // 7 days

        return $jobId;
    }

    /**
     * Update scraping criteria for scheduled job
     */
    /**
     * UpdateScheduledScraping
     */
    public function updateScheduledScraping(string $jobId, array $criteria): bool
    {
        $scheduleKey = "scraping_schedule_{$jobId}";
        $schedule = Cache::get($scheduleKey);

        if (!$schedule) {
            return FALSE;
        }

        $schedule['criteria'] = $criteria;
        $schedule['updated_at'] = Carbon::now()->toISOString();

        Cache::put($scheduleKey, $schedule, 86400 * 7);

        return TRUE;
    }

    /**
     * Cancel scheduled scraping
     */
    /**
     * Check if can cel scheduled scraping
     */
    public function cancelScheduledScraping(string $jobId): bool
    {
        $scheduleKey = "scraping_schedule_{$jobId}";

        return Cache::forget($scheduleKey);
    }

    /**
     * OnInitialize
     */
    protected function onInitialize(): void
    {
        $this->validateDependencies(['cacheService', 'analyticsService']);

        $this->adapterFactory = new PlatformAdapterFactory([
            'cacheService'      => $this->getDependency('cacheService'),
            'encryptionService' => $this->getDependency('encryptionService'),
        ]);

        $this->enabledPlatforms = $this->getConfig('enabled_platforms', [
            'ticketmaster', 'stubhub', 'seatgeek', 'viagogo', 'see_tickets',
            'manchester_united', 'arsenal_fc', 'chelsea_fc', 'liverpool_fc',
        ]);

        $this->loadScrapingMetrics();
    }

    /**
     * Private helper methods
     *
     * @param mixed $adapter
     */
    /**
     * ScrapePlatformWithAdapter
     *
     * @param mixed $adapter
     */
    private function scrapePlatformWithAdapter($adapter, array $criteria): array
    {
        $startTime = microtime(TRUE);

        try {
            $results = $adapter->scrape($criteria);
            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            return [
                'data'          => $results,
                'response_time' => round($responseTime, 2),
                'metadata'      => [
                    'platform'   => $adapter->getPlatformName(),
                    'scraped_at' => Carbon::now()->toISOString(),
                    'user_agent' => $adapter->getUserAgent(),
                    'proxy_used' => $adapter->getProxyUsed(),
                ],
            ];
        } catch (Exception $e) {
            $responseTime = (microtime(TRUE) - $startTime) * 1000;

            Log::error('Platform scraping failed', [
                'platform'      => $adapter->getPlatformName(),
                'error'         => $e->getMessage(),
                'response_time' => $responseTime,
                'criteria'      => $criteria,
            ]);

            throw $e;
        }
    }

    /**
     * Check if  platform enabled
     */
    private function isPlatformEnabled(string $platform): bool
    {
        return in_array($platform, $this->enabledPlatforms, TRUE)
               && $this->adapterFactory->isAvailable($platform);
    }

    /**
     * LoadScrapingMetrics
     */
    private function loadScrapingMetrics(): void
    {
        $this->scrapingMetrics = Cache::get('scraping_metrics', []);
    }

    /**
     * SaveEnabledPlatforms
     */
    private function saveEnabledPlatforms(): void
    {
        Cache::put('enabled_scraping_platforms', $this->enabledPlatforms, 86400 * 30);
    }

    /**
     * UpdatePlatformMetrics
     */
    private function updatePlatformMetrics(string $platform, bool $success, int $resultCount): void
    {
        $metricsKey = "platform_metrics_{$platform}";
        $metrics = Cache::get($metricsKey, [
            'total_runs'      => 0,
            'successful_runs' => 0,
            'total_results'   => 0,
            'last_run'        => NULL,
            'success_rate'    => 0,
        ]);

        $metrics['total_runs']++;
        if ($success) {
            $metrics['successful_runs']++;
            $metrics['total_results'] += $resultCount;
        }
        $metrics['last_run'] = Carbon::now()->toISOString();
        $metrics['success_rate'] = ($metrics['successful_runs'] / $metrics['total_runs']) * 100;
        $metrics['avg_results'] = $metrics['total_results'] / max($metrics['successful_runs'], 1);

        Cache::put($metricsKey, $metrics, 86400 * 30);
    }

    /**
     * Get  platform statistics
     */
    private function getPlatformStatistics(): array
    {
        $stats = [];

        foreach ($this->enabledPlatforms as $platform) {
            $metricsKey = "platform_metrics_{$platform}";
            $stats[$platform] = Cache::get($metricsKey, []);
        }

        return $stats;
    }

    /**
     * Get  overall statistics
     */
    private function getOverallStatistics(): array
    {
        $allStats = $this->getPlatformStatistics();

        $totalRuns = array_sum(array_column($allStats, 'total_runs'));
        $successfulRuns = array_sum(array_column($allStats, 'successful_runs'));
        $totalResults = array_sum(array_column($allStats, 'total_results'));

        return [
            'total_platforms'       => count($this->enabledPlatforms),
            'total_runs'            => $totalRuns,
            'successful_runs'       => $successfulRuns,
            'overall_success_rate'  => $totalRuns > 0 ? ($successfulRuns / $totalRuns) * 100 : 0,
            'total_results_scraped' => $totalResults,
            'avg_results_per_run'   => $successfulRuns > 0 ? $totalResults / $successfulRuns : 0,
        ];
    }

    /**
     * Get  scraping health status
     */
    private function getScrapingHealthStatus(): array
    {
        $stats = $this->getPlatformStatistics();
        $healthyPlatforms = 0;

        foreach ($stats as $platform => $platformStats) {
            $successRate = $platformStats['success_rate'] ?? 0;
            if ($successRate > 70) {
                $healthyPlatforms++;
            }
        }

        $healthPercentage = count($this->enabledPlatforms) > 0 ?
            ($healthyPlatforms / count($this->enabledPlatforms)) * 100 : 0;

        return [
            'overall_health' => round($healthPercentage, 2),
            'status'         => $healthPercentage > 80 ? 'healthy' :
                       ($healthPercentage > 50 ? 'warning' : 'critical'),
            'healthy_platforms' => $healthyPlatforms,
            'total_platforms'   => count($this->enabledPlatforms),
            'timestamp'         => Carbon::now()->toISOString(),
        ];
    }
}
