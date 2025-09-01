<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class LiveNationUKPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'LiveNation UK';
        $this->platform = 'livenation';
        $this->description = 'LiveNation UK - Major venue operator and concert promoter in the UK';
        $this->baseUrl = 'https://www.livenation.co.uk';
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
            'live_music',
            'rock_concerts',
            'pop_concerts',
            'indie_music',
            'festivals',
            'arena_shows',
            'theater_shows',
            'comedy_shows',
            'family_shows',
            'major_venues',
            'presales',
            'vip_packages',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'city',
            'date',
            'genre',
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
     * Build search URL for LiveNation UK
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];
        
        $params = [
            'keyword' => $query,
            'city' => $filters['city'] ?? '',
            'date' => $filters['date'] ?? '',
            'genre' => $filters['genre'] ?? '',
            'venue' => $filters['venue'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, function($value) {
            return !empty($value);
        });

        return $this->baseUrl . '/search?' . http_build_query($params);
    }

    /**
     * Scrape tickets from LiveNation UK search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("LiveNation UK Plugin: Scraping tickets from: $searchUrl");
            
            $response = $this->makeHttpRequest($searchUrl);
            if (!$response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // LiveNation search results selectors
            $crawler->filter('.event-card, .event-item, .listing, .search-result, .concert-listing')->each(function (Crawler $node) use (&$tickets) {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning("LiveNation UK Plugin: Error extracting ticket: " . $e->getMessage());
                }
            });

            Log::info("LiveNation UK Plugin: Found " . count($tickets) . " tickets");
            return $tickets;

        } catch (Exception $e) {
            Log::error("LiveNation UK Plugin: Scraping error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.event-title, .title, .name, h2 a, h3 a, .artist-name');
            if (empty($title)) {
                return null;
            }

            $venue = $this->extractText($node, '.venue, .location, .venue-name, .where');
            $date = $this->extractText($node, '.date, .event-date, .when, time, .show-date');
            $priceText = $this->extractText($node, '.price, .cost, .ticket-price, .from-price');
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

            // Extract LiveNation-specific info
            $presale = $this->extractText($node, '.presale, .early-access, .vip');
            $soldOut = $this->extractText($node, '.sold-out, .unavailable');

            return [
                'title' => $title,
                'price' => $price,
                'currency' => $this->currency,
                'venue' => $venue,
                'event_date' => $eventDate,
                'link' => $link,
                'platform' => $this->platform,
                'category' => $category,
                'availability' => !empty($soldOut) ? 'sold_out' : 'available',
                'presale_available' => !empty($presale),
                'scraped_at' => now(),
            ];

        } catch (Exception $e) {
            Log::warning("LiveNation UK Plugin: Error extracting ticket data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $tickets = [];

        $crawler->filter('.event-card, .event-item, .listing, .search-result, .concert-listing')->each(function (Crawler $node) use (&$tickets) {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning("LiveNation UK Plugin: Error extracting ticket: " . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, .name, h2 a, h3 a, .artist-name';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.date, .event-date, .when, time, .show-date';
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
        return '.price, .cost, .ticket-price, .from-price';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .presale';
    }

    /**
     * Parse price from text
     */
    private function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return null;
        }

        // Handle LiveNation price formats
        $cleanPrice = preg_replace('/from\s*£?|tickets from\s*£?/i', '', $priceText);
        $cleanPrice = preg_replace('/[^0-9.,£]/', '', $cleanPrice);
        $cleanPrice = str_replace(',', '', $cleanPrice);
        
        if (preg_match('/(\d+(?:\.\d{2})?)/', $cleanPrice, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $combined = $title . ' ' . $venue;

        if (preg_match('/festival|fest|outdoor|weekender/', $combined)) {
            return 'festival';
        }
        if (preg_match('/rock|metal|punk|alternative|indie/', $combined)) {
            return 'rock';
        }
        if (preg_match('/pop|mainstream|chart|commercial/', $combined)) {
            return 'pop';
        }
        if (preg_match('/jazz|blues|soul|r&b/', $combined)) {
            return 'jazz';
        }
        if (preg_match('/electronic|dance|techno|house|dj/', $combined)) {
            return 'electronic';
        }
        if (preg_match('/classical|symphony|opera|orchestral/', $combined)) {
            return 'classical';
        }
        if (preg_match('/comedy|stand.?up|comedian|funny/', $combined)) {
            return 'comedy';
        }
        if (preg_match('/family|children|kids|disney/', $combined)) {
            return 'family';
        }
        if (preg_match('/theater|theatre|musical|show|west end/', $combined)) {
            return 'theater';
        }

        return 'concert'; // Default for most LiveNation events
    }

    /**
     * Get search suggestions for LiveNation UK
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Popular Genres' => [
                'Rock Concerts',
                'Pop Music',
                'Indie Artists',
                'Electronic Music',
                'Classical Music',
                'Jazz & Blues'
            ],
            'Event Types' => [
                'Arena Concerts',
                'Theater Shows',
                'Music Festivals',
                'Comedy Shows',
                'Family Shows',
                'VIP Experiences'
            ],
            'Major Venues' => [
                'O2 Arena London',
                'Manchester Arena',
                'First Direct Arena Leeds',
                'Motorpoint Arena Nottingham',
                'SEC Armadillo Glasgow',
                'Motorpoint Arena Cardiff'
            ]
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $liveNationVenues = [
            'o2 arena', 'manchester arena', 'first direct arena',
            'motorpoint arena', 'sec armadillo', 'ovo hydro',
            'olympia london', 'eventim apollo', 'roundhouse'
        ];

        return in_array(strtolower($venue), $liveNationVenues);
    }

    /**
     * Get platform-specific filtering options
     */
    public function getFilterOptions(): array
    {
        return [
            'genres' => [
                'rock' => 'Rock & Alternative',
                'pop' => 'Pop & Mainstream',
                'indie' => 'Indie & Folk',
                'electronic' => 'Electronic & Dance',
                'jazz' => 'Jazz & Blues',
                'classical' => 'Classical',
                'comedy' => 'Comedy',
                'family' => 'Family Shows'
            ],
            'venues' => [
                'arena' => 'Arena Shows',
                'theater' => 'Theater Venues',
                'outdoor' => 'Outdoor Events',
                'intimate' => 'Intimate Venues'
            ],
            'price_ranges' => [
                '0-30' => 'Under £30',
                '30-60' => '£30 - £60',
                '60-120' => '£60 - £120',
                '120+' => 'Over £120'
            ],
            'special_offers' => [
                'presale' => 'Presale Available',
                'vip' => 'VIP Packages',
                'family' => 'Family Packages'
            ]
        ];
    }

    /**
     * Get platform-specific features
     */
    public function getPlatformFeatures(): array
    {
        return [
            'Exclusive presale access for members',
            'VIP packages and experiences available',
            'Mobile ticket delivery',
            'Venue partnerships with major arenas',
            'Artist pre-sale opportunities',
            'Flexible payment options'
        ];
    }
}
