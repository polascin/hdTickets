<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class EventimPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Eventim.de';
        $this->platform = 'eventim';
        $this->description = 'Eventim.de - Major European ticket platform for sports, concerts, and events';
        $this->baseUrl = 'https://www.eventim.de';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'de-DE';
        $this->rateLimitSeconds = 2; // Professional ticketing platforms are more lenient
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'football_tickets',
            'bundesliga',
            'champions_league',
            'concerts',
            'theater',
            'sports_events',
            'festivals',
            'comedy_shows',
            'multi_venue',
            'multi_city',
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
            'city',
            'venue',
            'category',
            'artist',
            'team',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/suche';
    }

    /**
     * Build search URL from criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/suche';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['term'] = $criteria['keyword'];
        }

        if (! empty($criteria['city'])) {
            $params['city'] = $criteria['city'];
        }

        if (! empty($criteria['category'])) {
            $params['kategorie'] = $this->mapCategory($criteria['category']);
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
     * Map categories to German equivalents
     */
    protected function mapCategory(string $category): string
    {
        $mapping = [
            'football'  => 'fussball',
            'sports'    => 'sport',
            'concerts'  => 'konzerte',
            'theater'   => 'theater',
            'comedy'    => 'comedy',
            'festivals' => 'festivals',
            'classical' => 'klassik',
            'musical'   => 'musical',
            'family'    => 'familie',
        ];

        return $mapping[strtolower($category)] ?? 'alle';
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            // Parse Eventim specific event structure
            $crawler->filter('.event-item, .search-result-item, .event-card, .result-item, ' .
                             '.event-entry, .ticket-item, .event-listing')->each(function (Crawler $node) use (&$events): void {
                                 try {
                                     $event = $this->parseEventNode($node);
                                     if ($event) {
                                         $events[] = $event;
                                     }
                                 } catch (Exception $e) {
                                     Log::warning('Failed to parse Eventim event node', [
                                         'error'        => $e->getMessage(),
                                         'html_snippet' => substr($node->html(), 0, 200),
                                     ]);
                                 }
                             });

            // Fallback: try to parse generic event structures
            if (empty($events)) {
                $crawler->filter('.card, .item, .event-row, .listing-item')->each(function (Crawler $node) use (&$events): void {
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
            Log::error('Failed to parse Eventim events', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Get CSS selectors for event name (German)
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, .event-name, .veranstaltung-titel, .event-headline, h3, h2, .name, .titel';
    }

    /**
     * Get CSS selectors for date (German)
     */
    protected function getDateSelectors(): string
    {
        return '.event-date, .datum, .date, time, .datetime, .event-time, .zeit, .termin';
    }

    /**
     * Get CSS selectors for venue (German)
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .location, .ort, .veranstaltungsort, .stadion, .halle';
    }

    /**
     * Get CSS selectors for price (German)
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .preis, .kosten, .cost, .ab, .from, .ticket-preis, .ab-preis';
    }

    /**
     * Get CSS selectors for availability (German)
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .verfügbarkeit, .status, .ausverkauft, .sold-out, .verfügbar, .available, .tickets-verfügbar';
    }
}
