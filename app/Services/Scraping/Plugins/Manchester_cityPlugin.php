<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class Manchester_cityPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    /**
     * InitializePlugin
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Manchester City FC';
        $this->platform = 'manchester_city';
        $this->description = 'Official Manchester City FC tickets - Premier League, Champions League, FA Cup';
        $this->baseUrl = 'https://www.mancity.com';
        $this->venue = 'Etihad Stadium';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 3;
    }

    /**
     * Get plugin capabilities
     */
    /**
     * Get  capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'premier_league',
            'champions_league',
            'fa_cup',
            'carabao_cup',
            'hospitality_packages',
            'season_tickets',
            'stadium_tours',
        ];
    }

    /**
     * Get supported search criteria
     */
    /**
     * Get  supported criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'date_range',
            'competition',
            'match_type',
            'opponent',
            'section',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    /**
     * Get  test url
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/tickets';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/tickets';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['search'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competition'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['date_from'])) {
            $params['date_from'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['date_to'] = $criteria['date_to'];
        }

        return $baseSearchUrl . (! empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Map competition names to English equivalents
     */
    /**
     * MapCompetition
     */
    protected function mapCompetition(string $competition): string
    {
        $mapping = [
            'premier league'   => 'premier-league',
            'champions league' => 'champions-league',
            'fa cup'           => 'fa-cup',
            'carabao cup'      => 'carabao-cup',
            'community shield' => 'community-shield',
            'europa league'    => 'europa-league',
        ];

        return $mapping[strtolower($competition)] ?? 'all';
    }

    /**
     * Parse search results from HTML
     */
    /**
     * ParseSearchResults
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            // Parse Manchester City specific event structure
            $crawler->filter('.fixture, .match, .event, .ticket-item, .match-card')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse Manchester City event node', [
                        'error'        => $e->getMessage(),
                        'html_snippet' => substr($node->html(), 0, 200),
                    ]);
                }
            });

            // Fallback: try to parse generic event structures
            if (empty($events)) {
                $crawler->filter('.card, .item, .entry, .event-row')->each(function (Crawler $node) use (&$events): void {
                    try {
                        $event = $this->parseEventNode($node);
                        if ($event) {
                            $events[] = $event;
                        }
                    } catch (Exception $e) {
                        // Silently continue for fallback parsing
                    }
                });
            }
        } catch (Exception $e) {
            Log::error('Failed to parse Manchester City events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (English)
     */
    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.fixture-title, .match-title, .event-title, .opponent, .vs, h3, h2, .title, .name';
    }

    /**
     * Get CSS selectors for date (English)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.fixture-date, .match-date, .event-date, .date, time, .datetime, .kick-off';
    }

    /**
     * Get CSS selectors for venue (English)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .stadium, .location, .ground';
    }

    /**
     * Get CSS selectors for price (English)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .from, .ticket-price, .tariff';
    }

    /**
     * Get CSS selectors for availability (English)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .available, .on-sale';
    }
}
