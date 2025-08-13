<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class PsgPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    /**
     * InitializePlugin
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Paris Saint-Germain';
        $this->platform = 'psg';
        $this->description = 'Official Paris Saint-Germain tickets - Ligue 1, Champions League, Coupe de France';
        $this->baseUrl = 'https://www.psg.fr';
        $this->venue = 'Parc des Princes';
        $this->currency = 'EUR';
        $this->language = 'fr-FR';
        $this->rateLimitSeconds = 3; // French sites are moderately strict
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
            'ligue_1',
            'champions_league',
            'coupe_de_france',
            'coupe_de_la_ligue',
            'europa_league',
            'hospitality_packages',
            'season_tickets',
            'stadium_tours',
            'le_classique', // vs Marseille
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
        return $this->baseUrl . '/billetterie';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/billetterie';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['recherche'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competition'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['date_from'])) {
            $params['date_debut'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['date_fin'] = $criteria['date_to'];
        }

        return $baseSearchUrl . (! empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Map competition names to French equivalents
     */
    /**
     * MapCompetition
     */
    protected function mapCompetition(string $competition): string
    {
        $mapping = [
            'ligue 1'               => 'ligue-1',
            'champions league'      => 'champions-league',
            'coupe de france'       => 'coupe-de-france',
            'coupe de la ligue'     => 'coupe-de-la-ligue',
            'europa league'         => 'europa-league',
            'le classique'          => 'le-classique',
            'trophee des champions' => 'trophee-des-champions',
        ];

        return $mapping[strtolower($competition)] ?? 'toutes';
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
            // Parse PSG specific event structure
            $crawler->filter('.match-card, .match, .evento, .billet-item, .fixture, .rencontre')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse PSG event node', [
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
            Log::error('Failed to parse PSG events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (French)
     */
    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.match-title, .titre-match, .event-title, .titre-evenement, .nom-match, .adversaire, .opponent, h3, h2, .title, .titre';
    }

    /**
     * Get CSS selectors for date (French)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.match-date, .date-match, .event-date, .date, time, .datetime, .date-evenement, .heure';
    }

    /**
     * Get CSS selectors for venue (French)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .stade, .location, .lieu, .emplacement';
    }

    /**
     * Get CSS selectors for price (French)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .prix, .cout, .cost, .a-partir-de, .from, .prix-billet, .tarif';
    }

    /**
     * Get CSS selectors for availability (French)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .disponibilite, .statut, .status, .epuise, .sold-out, .disponible, .available';
    }
}
