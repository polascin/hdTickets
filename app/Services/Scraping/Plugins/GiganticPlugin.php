<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class GiganticPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Gigantic
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Popular Genres' => [
                'Indie Music',
                'Rock Concerts',
                'Electronic Music',
                'Folk & Acoustic',
                'Jazz & Blues',
                'Classical Music',
            ],
            'Event Types' => [
                'Music Festivals',
                'Concert Tours',
                'Comedy Shows',
                'Theater Shows',
                'Album Launch Events',
            ],
            'Popular Venues' => [
                'Roundhouse Camden',
                'Electric Brixton',
                'Koko Camden',
                'Village Underground',
                'Scala King\'s Cross',
                'Heaven London',
            ],
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $supportedVenues = [
            'roundhouse', 'electric brixton', 'koko', 'village underground',
            'scala', 'heaven', 'fabric', 'ministry of sound',
            'jazz cafe', 'union chapel', 'barbican', 'southbank centre',
        ];

        return in_array(strtolower($venue), $supportedVenues, TRUE);
    }

    /**
     * Get platform-specific filtering options
     */
    public function getFilterOptions(): array
    {
        return [
            'genres' => [
                'indie'      => 'Indie & Alternative',
                'rock'       => 'Rock & Metal',
                'electronic' => 'Electronic & Dance',
                'folk'       => 'Folk & Acoustic',
                'jazz'       => 'Jazz & Blues',
                'classical'  => 'Classical',
                'comedy'     => 'Comedy',
                'theater'    => 'Theater',
            ],
            'price_ranges' => [
                '0-25'   => 'Under £25',
                '25-50'  => '£25 - £50',
                '50-100' => '£50 - £100',
                '100+'   => 'Over £100',
            ],
            'dates' => [
                'today'      => 'Today',
                'tomorrow'   => 'Tomorrow',
                'this_week'  => 'This Week',
                'this_month' => 'This Month',
                'next_month' => 'Next Month',
            ],
        ];
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Gigantic';
        $this->platform = 'gigantic';
        $this->description = 'Gigantic - Popular UK ticket platform specializing in music, festivals, and live events';
        $this->baseUrl = 'https://www.gigantic.com';
        $this->venue = 'Various';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'concerts',
            'festivals',
            'indie_music',
            'rock_concerts',
            'electronic_music',
            'folk_music',
            'jazz',
            'classical',
            'comedy_shows',
            'theater',
            'multi_venue',
            'presales',
            'exclusive_access',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'location',
            'genre',
            'date',
            'venue',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/search';
    }

    /**
     * Build search URL for Gigantic
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'q'        => $query,
            'location' => $filters['location'] ?? '',
            'genre'    => $filters['genre'] ?? '',
            'date'     => $filters['date'] ?? '',
            'sort'     => $filters['sort'] ?? 'relevance',
        ];

        // Remove empty parameters
        $params = array_filter($params, function ($value) {
            return !empty($value);
        });

        return $this->baseUrl . '/search?' . http_build_query($params);
    }

    /**
     * Scrape tickets from Gigantic search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("Gigantic Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (!$response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Gigantic search results selectors
            $crawler->filter('.event-card, .listing, .search-result, .event-item')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Gigantic Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Gigantic Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Gigantic Plugin: Scraping error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $tickets = [];

        $crawler->filter('.event-card, .listing, .search-result, .event-item')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Gigantic Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, h2 a, h3 a, .name';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.date, .event-date, .when, time';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .location, .venue-name';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .from-price, .price-from';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.event-title, .title, h2 a, h3 a, .name');
            if (empty($title)) {
                return NULL;
            }

            $venue = $this->extractText($node, '.venue, .location, .venue-name');
            $date = $this->extractText($node, '.date, .event-date, .when, time');
            $priceText = $this->extractText($node, '.price, .cost, .from-price, .price-from');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title and venue
            $category = $this->determineCategory($title, $venue);

            return [
                'title'        => $title,
                'price'        => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $category,
                'availability' => 'available',
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('Gigantic Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse price from text
     */
    private function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return NULL;
        }

        // Handle "from £X" format common on Gigantic
        $cleanPrice = preg_replace('/from\s*£?/i', '', $priceText);
        $cleanPrice = preg_replace('/[^0-9.,£]/', '', $cleanPrice);
        $cleanPrice = str_replace(',', '', $cleanPrice);

        if (preg_match('/(\d+(?:\.\d{2})?)/', $cleanPrice, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $combined = $title . ' ' . $venue;

        if (preg_match('/festival|fest|gathering/', $combined)) {
            return 'festival';
        }
        if (preg_match('/concert|tour|live|music|band|singer|orchestra/', $combined)) {
            return 'concert';
        }
        if (preg_match('/comedy|stand.?up|comedian|funny/', $combined)) {
            return 'comedy';
        }
        if (preg_match('/theatre|theater|musical|show|play/', $combined)) {
            return 'theater';
        }
        if (preg_match('/jazz|blues|soul/', $combined)) {
            return 'jazz';
        }
        if (preg_match('/classical|symphony|philharmonic|opera/', $combined)) {
            return 'classical';
        }
        if (preg_match('/electronic|techno|house|dance|dj/', $combined)) {
            return 'electronic';
        }
        if (preg_match('/indie|alternative|rock|punk|metal/', $combined)) {
            return 'rock';
        }
        if (preg_match('/folk|acoustic|singer.songwriter/', $combined)) {
            return 'folk';
        }

        return 'concert'; // Default for most Gigantic events
    }
}
