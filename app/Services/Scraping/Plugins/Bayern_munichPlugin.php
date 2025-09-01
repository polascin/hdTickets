<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class Bayern_munichPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'FC Bayern Munich';
        $this->platform = 'bayern_munich';
        $this->description = 'Official FC Bayern Munich tickets - Bundesliga, Champions League, DFB-Pokal, Der Klassiker';
        $this->baseUrl = 'https://fcbayern.com';
        $this->venue = 'Allianz Arena';
        $this->currency = 'EUR';
        $this->language = 'de-DE';
        $this->rateLimitSeconds = 3; // German sites are moderately strict
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'bundesliga',
            'champions_league',
            'dfb_pokal',
            'europa_league',
            'super_cup',
            'hospitality_packages',
            'season_tickets',
            'allianz_arena_tours',
            'der_klassiker', // vs Borussia Dortmund
            'womens_football',
            'youth_teams',
        ];
    }

    /**
     * Get supported search criteria
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
            'price_range',
            'team', // Herren/Damen/Jugend
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
        return $this->baseUrl . '/de/tickets';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/de/tickets';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['suche'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['wettbewerb'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['date_from'])) {
            $params['datum_von'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['datum_bis'] = $criteria['date_to'];
        }

        return $baseSearchUrl . (! empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Map competition names to German equivalents
     */
    /**
     * MapCompetition
     */
    protected function mapCompetition(string $competition): string
    {
        $mapping = [
            'bundesliga'       => 'bundesliga',
            'champions league' => 'champions-league',
            'dfb pokal'        => 'dfb-pokal',
            'dfb-pokal'        => 'dfb-pokal',
            'uefa cup'         => 'uefa-pokal',
            'europa league'    => 'europa-league',
        ];

        return $mapping[strtolower($competition)] ?? 'alle';
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
            // Parse Bayern Munich specific event structure
            $crawler->filter('.match-card, .spiel, .match, .event, .ticket-item, .fixture')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse Bayern Munich event node', [
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
            Log::error('Failed to parse Bayern Munich events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (German)
     */
    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.match-title, .spiel-titel, .event-title, .titel, .gegner, .opponent, h3, h2, .title, .name';
    }

    /**
     * Get CSS selectors for date (German)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.match-date, .spiel-datum, .event-date, .datum, .date, time, .datetime, .zeit';
    }

    /**
     * Get CSS selectors for venue (German)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .stadion, .location, .ort, .austragungsort';
    }

    /**
     * Get CSS selectors for price (German)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .preis, .kosten, .cost, .ab, .from, .ticket-preis, .tarif';
    }

    /**
     * Get CSS selectors for availability (German)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .verfügbarkeit, .status, .ausverkauft, .sold-out, .verfügbar, .available';
    }
}
