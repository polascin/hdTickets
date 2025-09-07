<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class Ac_milanPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    /**
     * InitializePlugin
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'AC Milan';
        $this->platform = 'ac_milan';
        $this->description = 'Official AC Milan tickets - Serie A, Champions League, Coppa Italia';
        $this->baseUrl = 'https://www.acmilan.com';
        $this->venue = 'San Siro';
        $this->currency = 'EUR';
        $this->language = 'it-IT';
        $this->rateLimitSeconds = 3; // Italian sites are moderately strict
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
            'serie_a',
            'champions_league',
            'coppa_italia',
            'europa_league',
            'hospitality_packages',
            'season_tickets',
            'stadium_tours',
            'derby_della_madonnina', // vs Inter Milan
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
        return $this->baseUrl . '/it/biglietti';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/it/biglietti';

        $params = [];

        if (!empty($criteria['keyword'])) {
            $params['cerca'] = $criteria['keyword'];
        }

        if (!empty($criteria['competition'])) {
            $params['competizione'] = $this->mapCompetition($criteria['competition']);
        }

        if (!empty($criteria['date_from'])) {
            $params['data_da'] = $criteria['date_from'];
        }

        if (!empty($criteria['date_to'])) {
            $params['data_a'] = $criteria['date_to'];
        }

        return $baseSearchUrl . (!empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Map competition names to Italian equivalents
     */
    /**
     * MapCompetition
     */
    protected function mapCompetition(string $competition): string
    {
        $mapping = [
            'serie a'               => 'serie-a',
            'champions league'      => 'champions-league',
            'coppa italia'          => 'coppa-italia',
            'europa league'         => 'europa-league',
            'supercoppa italiana'   => 'supercoppa-italiana',
            'derby della madonnina' => 'derby-della-madonnina',
        ];

        return $mapping[strtolower($competition)] ?? 'tutte';
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
            // Parse AC Milan specific event structure
            $crawler->filter('.match-card, .partita, .match, .evento, .biglietto-item, .fixture')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse AC Milan event node', [
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
            Log::error('Failed to parse AC Milan events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (Italian)
     */
    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.match-title, .titolo-partita, .event-title, .titolo-evento, .nome-partita, .avversario, .opponent, h3, h2, .title, .titolo';
    }

    /**
     * Get CSS selectors for date (Italian)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.match-date, .data-partita, .event-date, .data, .date, time, .datetime, .data-evento, .orario';
    }

    /**
     * Get CSS selectors for venue (Italian)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .stadio, .location, .luogo, .sede';
    }

    /**
     * Get CSS selectors for price (Italian)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .prezzo, .costo, .cost, .da, .from, .prezzo-biglietto, .tariffa';
    }

    /**
     * Get CSS selectors for availability (Italian)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .disponibilità, .stato, .status, .esaurito, .sold-out, .disponibile, .available';
    }
}
