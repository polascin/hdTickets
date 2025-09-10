<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class TicketSwapPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for TicketSwap
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Popular Festivals' => [
                'Tomorrowland Belgium',
                'Lowlands Netherlands',
                'Rock am Ring Germany',
                'Primavera Sound Barcelona',
                'Roskilde Denmark',
                'Reading Festival UK',
            ],
            'Football Matches' => [
                'Champions League',
                'Premier League',
                'La Liga',
                'Bundesliga',
                'Serie A',
                'International Matches',
            ],
            'Concert Tours' => [
                'Stadium Tours',
                'Arena Concerts',
                'Theater Shows',
                'Classical Concerts',
                'DJ Sets',
            ],
            'Event Types' => [
                'Sold Out Events',
                'Music Festivals',
                'Football Tickets',
                'Concert Tours',
                'Theater Shows',
            ],
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        // TicketSwap supports resale for most major venues
        $majorVenues = [
            'wembley', 'old trafford', 'camp nou', 'allianz arena',
            'o2 arena', 'ziggo dome', 'accor arena', 'royal albert hall',
            'amsterdam arena', 'san siro', 'bernabeu', 'olympiastadion',
        ];

        return in_array(strtolower($venue), $majorVenues, TRUE);
    }

    /**
     * Get platform-specific filtering options
     */
    public function getFilterOptions(): array
    {
        return [
            'categories' => [
                'music'     => 'Music & Concerts',
                'festivals' => 'Festivals',
                'sports'    => 'Sports',
                'theater'   => 'Theater & Shows',
                'comedy'    => 'Comedy',
                'other'     => 'Other Events',
            ],
            'countries' => [
                'gb' => 'United Kingdom',
                'nl' => 'Netherlands',
                'de' => 'Germany',
                'fr' => 'France',
                'be' => 'Belgium',
                'es' => 'Spain',
                'it' => 'Italy',
                'dk' => 'Denmark',
            ],
            'price_ranges' => [
                '0-50'    => 'Under €50',
                '50-100'  => '€50 - €100',
                '100-250' => '€100 - €250',
                '250+'    => 'Over €250',
            ],
            'availability' => [
                'verified'  => 'Verified Sellers Only',
                'instant'   => 'Instant Download',
                'protected' => 'Buyer Protection',
            ],
        ];
    }

    /**
     * Get platform-specific warnings/notes
     */
    public function getPlatformNotes(): array
    {
        return [
            'Resale platform - prices may be above face value',
            'All transactions are protected by buyer guarantee',
            'Tickets are verified before listing',
            'Popular events may sell out quickly',
            'Prices fluctuate based on demand',
        ];
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'TicketSwap';
        $this->platform = 'ticketswap';
        $this->description = 'TicketSwap - European ticket resale platform for sold-out events';
        $this->baseUrl = 'https://www.ticketswap.com';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'en-EU';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'resale_platform',
            'sold_out_events',
            'festivals',
            'concerts',
            'football_tickets',
            'theater',
            'sports_events',
            'verified_tickets',
            'buyer_protection',
            'multi_country',
            'multi_currency',
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
            'category',
            'country',
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
     * Build search URL for TicketSwap
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'q'        => $query,
            'location' => $filters['location'] ?? '',
            'date'     => $filters['date'] ?? '',
            'category' => $filters['category'] ?? '',
            'country'  => $filters['country'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => ! empty($value));

        return $this->baseUrl . '/search?' . http_build_query($params);
    }

    /**
     * Scrape tickets from TicketSwap search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("TicketSwap Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // TicketSwap search results selectors
            $crawler->filter('.event-card, .listing-item, .search-result, .ticket-listing')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('TicketSwap Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('TicketSwap Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('TicketSwap Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.event-card, .listing-item, .search-result, .ticket-listing')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('TicketSwap Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, .listing-title, h2 a, h3 a, .name';
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
        return '.venue, .location, .venue-name, .where';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .listing-price, .amount';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .verified';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.event-title, .title, .listing-title, h2 a, h3 a, .name');
            if ($title === '' || $title === '0') {
                return NULL;
            }

            $venue = $this->extractText($node, '.venue, .location, .venue-name, .where');
            $date = $this->extractText($node, '.date, .event-date, .when, time');
            $priceText = $this->extractText($node, '.price, .cost, .listing-price, .amount');
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

            // Extract additional TicketSwap-specific info
            $ticketCount = $this->extractText($node, '.ticket-count, .quantity, .available');
            $verified = $this->extractText($node, '.verified, .safe, .protected');

            return [
                'title'        => $title,
                'price'        => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $category,
                'availability' => 'resale',
                'ticket_count' => $this->parseTicketCount($ticketCount),
                'verified'     => $verified !== '' && $verified !== '0',
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('TicketSwap Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse price from text
     */
    private function parsePrice(string $priceText): ?float
    {
        if ($priceText === '' || $priceText === '0') {
            return NULL;
        }

        // Handle various European currency formats
        $cleanPrice = preg_replace('/[^0-9.,€£$]/', '', $priceText);
        $cleanPrice = str_replace(',', '.', $cleanPrice);

        if (preg_match('/(\d+(?:\.\d{2})?)/', $cleanPrice, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * Parse ticket count from text
     */
    private function parseTicketCount(string $countText): ?int
    {
        if ($countText === '' || $countText === '0') {
            return NULL;
        }

        if (preg_match('/(\d+)/', $countText, $matches)) {
            return (int) $matches[1];
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

        if (preg_match('/festival|fest|gathering|weekender/', $combined)) {
            return 'festival';
        }
        if (preg_match('/concert|tour|live|music|band|artist|dj/', $combined)) {
            return 'concert';
        }
        if (preg_match('/football|soccer|fc|united|city|liverpool|arsenal|champions|premier|league/', $combined)) {
            return 'football';
        }
        if (preg_match('/theater|theatre|musical|show|opera|ballet/', $combined)) {
            return 'theater';
        }
        if (preg_match('/comedy|stand.?up|comedian/', $combined)) {
            return 'comedy';
        }
        if (preg_match('/sport|championship|tournament|match|game/', $combined)) {
            return 'sports';
        }

        return 'other';
    }
}
