<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class BarcelonaPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'FC Barcelona';
        $this->platform = 'barcelona';
        $this->description = 'Official FC Barcelona tickets - La Liga, Champions League, Copa del Rey, El Clásico';
        $this->baseUrl = 'https://www.fcbarcelona.com';
        $this->venue = 'Camp Nou / Estadi Olímpic Lluís Companys';
        $this->currency = 'EUR';
        $this->language = 'es-ES';
        $this->rateLimitSeconds = 4; // Spanish sites are stricter
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'la_liga',
            'champions_league',
            'copa_del_rey',
            'supercopa_espana',
            'el_clasico',
            'hospitality_packages',
            'season_tickets',
            'camp_nou_tours',
            'womens_football',
            'el_clasico_femenino',
            'temporary_venue', // Due to Camp Nou renovation
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
            'team', // masculino/femenino
            'price_range',
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
        return $this->baseUrl . '/es/entradas';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/es/entradas';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['buscar'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competicion'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['date_from'])) {
            $params['fecha_desde'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['fecha_hasta'] = $criteria['date_to'];
        }

        return $baseSearchUrl . (! empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Map competition names to Spanish equivalents
     */
    /**
     * MapCompetition
     */
    protected function mapCompetition(string $competition): string
    {
        $mapping = [
            'la liga'          => 'laliga',
            'champions league' => 'champions',
            'copa del rey'     => 'copa-del-rey',
            'el clasico'       => 'el-clasico',
            'supercopa'        => 'supercopa',
            'europa league'    => 'europa-league',
        ];

        return $mapping[strtolower($competition)] ?? 'todos';
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
            // Parse Barcelona specific event structure
            $crawler->filter('.match-card, .partido, .match, .evento, .event-item, .fixture')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse Barcelona event node', [
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
            Log::error('Failed to parse Barcelona events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (Spanish)
     */
    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.match-title, .titulo-partido, .event-title, .titulo-evento, .nombre-partido, .rival, .opponent, h3, h2, .title, .titulo';
    }

    /**
     * Get CSS selectors for date (Spanish)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.match-date, .fecha-partido, .event-date, .fecha, .date, time, .datetime, .fecha-evento, .match-time';
    }

    /**
     * Get CSS selectors for venue (Spanish)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .estadio, .location, .lugar, .sede, .ground';
    }

    /**
     * Get CSS selectors for price (Spanish)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .precio, .coste, .cost, .desde, .from, .precio-entrada, .precio-ticket, .tarifa';
    }

    /**
     * Get CSS selectors for availability (Spanish)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .disponibilidad, .estado, .status, .agotado, .sold-out, .disponible, .available, .stock';
    }
}
