<?php declare(strict_types=1);

namespace App\Services\Interfaces;

/**
 * Scraping Service Interface
 *
 * Defines the contract for sport events entry ticket scraping services.
 */
interface ScrapingInterface
{
    /**
     * Scrape tickets from all enabled platforms
     *
     * @param array $criteria Search criteria for tickets
     *
     * @return array Results from all platforms
     */
    public function scrapeAllPlatforms(array $criteria): array;

    /**
     * Scrape specific platform
     *
     * @param string $platform Platform name
     * @param array  $criteria Search criteria
     *
     * @return array Platform results
     */
    public function scrapePlatform(string $platform, array $criteria): array;

    /**
     * Get available platforms
     *
     * @return array List of available platforms
     */
    public function getAvailablePlatforms(): array;

    /**
     * Enable platform for scraping
     *
     * @param string $platform Platform name
     */
    public function enablePlatform(string $platform): void;

    /**
     * Disable platform from scraping
     *
     * @param string $platform Platform name
     */
    public function disablePlatform(string $platform): void;

    /**
     * Get scraping statistics
     *
     * @return array Statistics data
     */
    public function getScrapingStatistics(): array;
}
