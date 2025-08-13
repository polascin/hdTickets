<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class Real_madridPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    /**
     * InitializePlugin
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Real Madrid CF';
        $this->platform = 'real_madrid';
        $this->description = 'Official Real Madrid CF tickets - La Liga, Champions League, Copa del Rey';
        $this->baseUrl = 'https://www.realmadrid.com';
        $this->venue = 'Santiago Bernabéu Stadium';
        $this->currency = 'EUR';
        $this->language = 'es-ES';
        $this->rateLimitSeconds = 4; // Spanish sites are stricter
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
            'la_liga',
            'champions_league',
            'copa_del_rey',
            'el_clasico',
            'hospitality_packages',
            'season_tickets',
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
        return $this->baseUrl . '/entradas';
    }

    /**
     * Build search URL from criteria
     */
    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/entradas';

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
            // Parse Real Madrid specific event structure
            $crawler->filter('.partido, .match, .evento, .event-card, .fixture')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to parse Real Madrid event node', [
                        'error'        => $e->getMessage(),
                        'html_snippet' => substr($node->html(), 0, 200),
                    ]);
                }
            });

            // Fallback: try to parse generic event structures
            if (empty($events)) {
                $crawler->filter('.card, .item, .entry')->each(function (Crawler $node) use (&$events): void {
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
            Log::error('Failed to parse Real Madrid events', [
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
        return '.titulo-partido, .match-title, .event-title, .titulo-evento, .nombre-partido, h3, h2, .title, .titulo';
    }

    /**
     * Get CSS selectors for date (Spanish)
     */
    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.fecha-partido, .match-date, .event-date, .fecha, .date, time, .datetime, .fecha-evento';
    }

    /**
     * Get CSS selectors for venue (Spanish)
     */
    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.estadio, .venue, .location, .lugar, .sede';
    }

    /**
     * Get CSS selectors for price (Spanish)
     */
    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.precio, .price, .coste, .cost, .desde, .from, .precio-entrada, .precio-ticket';
    }

    /**
     * Get CSS selectors for availability (Spanish)
     */
    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.disponibilidad, .availability, .estado, .status, .agotado, .sold-out, .disponible, .available';
    }
}
