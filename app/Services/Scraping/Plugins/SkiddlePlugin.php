<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class SkiddlePlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Skiddle
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Popular Genres' => [
                'Drum & Bass',
                'House Music',
                'Techno',
                'Electronic Dance',
                'Indie Music',
                'Alternative Rock',
            ],
            'Event Types' => [
                'Club Nights',
                'Music Festivals',
                'Warehouse Raves',
                'Student Events',
                'Alternative Nights',
                'Underground Events',
            ],
            'Popular Cities' => [
                'London',
                'Manchester',
                'Birmingham',
                'Leeds',
                'Bristol',
                'Glasgow',
                'Newcastle',
                'Liverpool',
            ],
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $supportedVenues = [
            'fabric', 'ministry of sound', 'egg london', 'printworks',
            'warehouse project', 'albert hall', 'gorilla manchester',
            'motion bristol', 'sub club glasgow', 'boiler room',
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
                'clubbing'      => 'Club Nights',
                'drum_and_bass' => 'Drum & Bass',
                'house'         => 'House Music',
                'techno'        => 'Techno',
                'electronic'    => 'Electronic',
                'indie'         => 'Indie & Alternative',
                'festival'      => 'Festivals',
            ],
            'event_types' => [
                'club'     => 'Club Events',
                'festival' => 'Festivals',
                'rave'     => 'Raves',
                'gig'      => 'Live Music',
                'student'  => 'Student Events',
            ],
            'price_ranges' => [
                'free'  => 'Free Events',
                '0-10'  => 'Under £10',
                '10-25' => '£10 - £25',
                '25-50' => '£25 - £50',
                '50+'   => 'Over £50',
            ],
        ];
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Skiddle';
        $this->platform = 'skiddle';
        $this->description = 'Skiddle - UK platform for indie music, clubbing, festivals, and alternative events';
        $this->baseUrl = 'https://www.skiddle.com';
        $this->venue = 'Various';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 1;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'clubbing',
            'nightlife',
            'indie_music',
            'electronic_music',
            'drum_and_bass',
            'house_music',
            'techno',
            'festivals',
            'raves',
            'alternative_events',
            'student_events',
            'underground_music',
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
            'date',
            'genre',
            'type',
            'radius',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/whats-on/search/';
    }

    /**
     * Build search URL for Skiddle
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'keywords'  => $query,
            'where'     => $filters['location'] ?? '',
            'date'      => $filters['date'] ?? '',
            'genre'     => $filters['genre'] ?? '',
            'eventcode' => $filters['type'] ?? '',
            'radius'    => $filters['radius'] ?? '25',
        ];

        // Remove empty parameters
        $params = array_filter($params, function ($value) {
            return ! empty($value);
        });

        return $this->baseUrl . '/whats-on/search/?' . http_build_query($params);
    }

    /**
     * Scrape tickets from Skiddle search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("Skiddle Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Skiddle search results selectors
            $crawler->filter('.event, .event-item, .listing, .event-card')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Skiddle Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Skiddle Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Skiddle Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.event, .event-item, .listing, .event-card')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Skiddle Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, h2 a, h3 a, .name, .event-name';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.date, .when, .event-date, time, .starts';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .location, .venue-name, .where';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .admission, .entry';
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
            $title = $this->extractText($node, '.event-title, .title, h2 a, h3 a, .name, .event-name');
            if (empty($title)) {
                return NULL;
            }

            $venue = $this->extractText($node, '.venue, .location, .venue-name, .where');
            $date = $this->extractText($node, '.date, .when, .event-date, time, .starts');
            $priceText = $this->extractText($node, '.price, .cost, .admission, .entry');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && ! filter_var($link, FILTER_VALIDATE_URL)) {
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
            Log::warning('Skiddle Plugin: Error extracting ticket data: ' . $e->getMessage());

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

        // Handle Skiddle price formats
        $cleanPrice = strtolower($priceText);

        // Handle free events
        if (preg_match('/free|complimentary/i', $cleanPrice)) {
            return 0.00;
        }

        // Extract price numbers
        $cleanPrice = preg_replace('/[^0-9.,£$]/', '', $priceText);
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

        if (preg_match('/club|clubbing|club night|night club/', $combined)) {
            return 'clubbing';
        }
        if (preg_match('/drum.?and.?bass|dnb|d&b|jungle/', $combined)) {
            return 'drum_and_bass';
        }
        if (preg_match('/house|deep house|tech house|progressive house/', $combined)) {
            return 'house';
        }
        if (preg_match('/techno|minimal|detroit/', $combined)) {
            return 'techno';
        }
        if (preg_match('/electronic|edm|dance|trance|ambient/', $combined)) {
            return 'electronic';
        }
        if (preg_match('/rave|warehouse|underground|illegal/', $combined)) {
            return 'rave';
        }
        if (preg_match('/festival|fest|gathering|outdoor/', $combined)) {
            return 'festival';
        }
        if (preg_match('/indie|alternative|rock|punk|metal/', $combined)) {
            return 'indie';
        }
        if (preg_match('/student|uni|university|freshers/', $combined)) {
            return 'student';
        }

        return 'nightlife'; // Default for most Skiddle events
    }
}
