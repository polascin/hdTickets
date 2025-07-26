<?php

namespace App\Services\Scraping;

interface ScraperPluginInterface
{
    /**
     * Get plugin information
     */
    public function getInfo(): array;

    /**
     * Check if plugin is enabled
     */
    public function isEnabled(): bool;

    /**
     * Enable the plugin
     */
    public function enable(): void;

    /**
     * Disable the plugin
     */
    public function disable(): void;

    /**
     * Configure the plugin
     */
    public function configure(array $config): void;

    /**
     * Scrape data based on criteria
     */
    public function scrape(array $criteria): array;

    /**
     * Test plugin functionality
     */
    public function test(): array;
}
