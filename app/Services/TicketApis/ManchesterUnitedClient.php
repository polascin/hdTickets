<?php declare(strict_types=1);

/**
 * HD Tickets Manchester United Official App Client
 *
 * @version 2025.07.v4.0
 */

namespace App\Services\TicketApis;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ManchesterUnitedClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.manutd.com';
        $this->respectRateLimit('manchester_united');
    }

    /**
     * SearchEvents
     */
    public function searchEvents(array $criteria): array
    {
        return $this->scrapeSearchResults($criteria['q'] ?? '', '', $criteria['per_page'] ?? 50);
    }

    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        return $this->scrapeEventDetails($this->baseUrl . '/tickets/fixtures/' . $eventId);
    }

    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        return [
            'id'       => $venueId,
            'name'     => 'Old Trafford',
            'address'  => 'Sir Matt Busby Way, Old Trafford, Manchester M16 0RA, UK',
            'capacity' => 74879,
            'city'     => 'Manchester',
            'country'  => 'United Kingdom',
        ];
    }

    /**
     * Scrape Manchester United fixture and ticket information
     */
    /**
     * ScrapeSearchResults
     */
    public function scrapeSearchResults(string $keyword = '', string $location = '', int $maxResults = 50): array
    {
        try {
            // Manchester United fixtures page
            $fixturesUrl = $this->baseUrl . '/fixtures-and-results';

            $html = $this->makeScrapingRequest($fixturesUrl);
            $crawler = new Crawler($html);

            return $this->extractFixtures($crawler, $maxResults, $keyword);
        } catch (Exception $e) {
            Log::error('Manchester United scraping failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Scrape individual match/event details
     */
    /**
     * ScrapeEventDetails
     */
    public function scrapeEventDetails(string $url): array
    {
        try {
            $html = $this->makeScrapingRequest($url, ['referer' => $this->baseUrl]);
            $crawler = new Crawler($html);

            return $this->extractMatchDetails($crawler, $url);
        } catch (Exception $e) {
            Log::error('Failed to scrape Manchester United match details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get  base url
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        return [
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language'           => 'en-US,en;q=0.5',
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];
    }

    /**
     * Extract search results from HTML (required by BaseWebScrapingClient)
     */
    /**
     * ExtractSearchResults
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        return $this->extractFixtures($crawler, $maxResults);
    }

    /**
     * Extract fixtures from the fixtures page
     */
    /**
     * ExtractFixtures
     */
    protected function extractFixtures(Crawler $crawler, int $maxResults, string $keyword = ''): array
    {
        $fixtures = [];
        $count = 0;

        // Different selectors for fixture listings
        $fixtureSelectors = [
            '.fixture-list .fixture-item',
            '.match-list .match-item',
            '[data-testid="fixture"]',
            '.fixture-card',
            '.upcoming-fixtures .fixture',
        ];

        foreach ($fixtureSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$fixtures, &$count, $maxResults, $keyword) {
                    if ($count >= $maxResults) {
                        return FALSE;
                    }

                    $fixture = $this->extractFixtureFromNode($node);
                    if (!empty($fixture['name'])) {
                        // Filter by keyword if provided
                        if (empty($keyword) || stripos($fixture['name'], $keyword) !== FALSE
                            || stripos($fixture['opponent'], $keyword) !== FALSE) {
                            $fixtures[] = $fixture;
                            $count++;
                        }
                    }
                });

                break;
            }
        }

        return $fixtures;
    }

    /**
     * Extract event data from node (required by BaseWebScrapingClient)
     */
    /**
     * ExtractEventFromNode
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        return $this->extractFixtureFromNode($node);
    }

    /**
     * Extract prices from crawler (required by BaseWebScrapingClient)
     */
    /**
     * ExtractPrices
     */
    protected function extractPrices(Crawler $crawler): array
    {
        $prices = [];

        try {
            $priceNodes = $crawler->filter('.ticket-price, .price-category, [data-testid="ticket-price"]');
            $priceNodes->each(function (Crawler $node) use (&$prices): void {
                $priceText = $node->text();

                if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $prices[] = [
                        'price'    => (float) ($matches[1]),
                        'currency' => 'GBP',
                        'section'  => 'General',
                    ];
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract Manchester United prices', ['error' => $e->getMessage()]);
        }

        return $prices;
    }

    /**
     * Extract fixture data from a single node
     */
    /**
     * ExtractFixtureFromNode
     */
    protected function extractFixtureFromNode(Crawler $node): array
    {
        try {
            // Extract opponent team
            $opponent = $this->trySelectors($node, [
                '.opponent-name',
                '.away-team',
                '.vs-team',
                'h3',
                '.team-name:not(.home-team)',
                '[data-testid="opponent"]',
            ]);

            // Extract match date and time
            $dateTime = $this->trySelectors($node, [
                '.match-date',
                '.fixture-date',
                'time',
                '.date-time',
                '[data-testid="match-date"]',
            ]);

            // Parse date
            $parsedDate = $this->parseEventDate($dateTime);

            // Extract competition
            $competition = $this->trySelectors($node, [
                '.competition',
                '.league',
                '.tournament',
                '[data-testid="competition"]',
                '.match-competition',
            ]);

            // Extract venue (home/away)
            $venue = $this->trySelectors($node, [
                '.venue',
                '.match-venue',
                '.location',
                '[data-testid="venue"]',
            ]);

            // If no venue specified, assume Old Trafford for home games
            if (empty($venue) || stripos($venue, 'home') !== FALSE) {
                $venue = 'Old Trafford, Manchester';
            }

            // Extract ticket link
            $ticketLink = '';
            $linkNode = $node->filter('a[href*="tickets"], a[href*="buy"], .ticket-link a')->first();
            if ($linkNode->count() > 0) {
                $href = $linkNode->attr('href');
                $ticketLink = $this->resolveUrl($href);
            }

            // Extract match status
            $status = $this->trySelectors($node, [
                '.match-status',
                '.status',
                '[data-testid="status"]',
                '.fixture-status',
            ]) ?: 'scheduled';

            return [
                'name'                => 'Manchester United vs ' . trim($opponent),
                'opponent'            => trim($opponent),
                'url'                 => $ticketLink ?: $this->baseUrl . '/tickets',
                'date'                => trim($dateTime),
                'parsed_date'         => $parsedDate,
                'venue'               => trim($venue),
                'competition'         => trim($competition),
                'status'              => trim($status),
                'home_team'           => 'Manchester United',
                'away_team'           => trim($opponent),
                'ticket_availability' => !empty($ticketLink) ? 'available' : 'check_website',
                'source'              => 'manchester_united_scrape',
                'scraped_at'          => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to extract fixture from node', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Extract detailed match information
     */
    /**
     * ExtractMatchDetails
     */
    protected function extractMatchDetails(Crawler $crawler, string $url): array
    {
        try {
            $matchTitle = $this->trySelectors($crawler, [
                'h1.match-title',
                'h1',
                '.event-title',
                '[data-testid="match-title"]',
            ]);

            $opponent = $this->trySelectors($crawler, [
                '.opponent-team',
                '.away-team-name',
                '.visiting-team',
            ]);

            $dateTime = $this->trySelectors($crawler, [
                '.match-datetime',
                '.kick-off-time',
                'time',
                '.event-date',
            ]);

            $venue = $this->trySelectors($crawler, [
                '.venue-name',
                '.stadium',
                '.match-venue',
            ]) ?: 'Old Trafford';

            $competition = $this->trySelectors($crawler, [
                '.competition-name',
                '.tournament',
                '.league-name',
            ]);

            // Extract ticket information
            $ticketInfo = $this->extractTicketInfo($crawler);

            // Extract match preview/description
            $description = $this->trySelectors($crawler, [
                '.match-preview',
                '.event-description',
                '.match-info p',
                '.description',
            ]);

            return [
                'name'        => trim($matchTitle) ?: ('Manchester United vs ' . trim($opponent)),
                'opponent'    => trim($opponent),
                'description' => trim($description),
                'date_time'   => trim($dateTime),
                'venue'       => trim($venue),
                'competition' => trim($competition),
                'home_team'   => 'Manchester United',
                'away_team'   => trim($opponent),
                'ticket_info' => $ticketInfo,
                'url'         => $url,
                'source'      => 'manchester_united_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error extracting Manchester United match details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Extract ticket information from match page
     */
    /**
     * ExtractTicketInfo
     */
    protected function extractTicketInfo(Crawler $crawler): array
    {
        $ticketInfo = [
            'available'  => FALSE,
            'prices'     => [],
            'categories' => [],
        ];

        try {
            // Check if tickets are available
            $ticketAvailability = $crawler->filter('.ticket-availability, .on-sale, [data-testid="ticket-status"]');
            if ($ticketAvailability->count() > 0) {
                $availabilityText = $ticketAvailability->text();
                $ticketInfo['available'] = stripos($availabilityText, 'on sale') !== FALSE
                                          || stripos($availabilityText, 'available') !== FALSE;
            }

            // Extract ticket categories and prices
            $priceNodes = $crawler->filter('.ticket-price, .price-category, [data-testid="ticket-price"]');
            $priceNodes->each(function (Crawler $node) use (&$ticketInfo): void {
                $priceText = $node->text();
                $categoryText = $node->closest('.ticket-category, .seat-type')->filter('.category-name')->text('');

                if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $ticketInfo['prices'][] = [
                        'category' => trim($categoryText) ?: 'General',
                        'price'    => (float) ($matches[1]),
                        'currency' => 'GBP',
                    ];
                }
            });

            // Extract seating categories
            $categoryNodes = $crawler->filter('.seating-category, .ticket-type, [data-testid="seat-category"]');
            $categoryNodes->each(function (Crawler $node) use (&$ticketInfo): void {
                $categoryName = $node->filter('.category-name, h3, .title')->text('');
                if (!empty($categoryName)) {
                    $ticketInfo['categories'][] = trim($categoryName);
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract ticket info', ['error' => $e->getMessage()]);
        }

        return $ticketInfo;
    }

    /**
     * TransformEventData
     */
    protected function transformEventData(array $eventData): array
    {
        return [
            'id'          => $eventData['id'] ?? uniqid('manutd_'),
            'name'        => $eventData['name'] ?? 'Manchester United Match',
            'date'        => $eventData['date'] ?? NULL,
            'time'        => $eventData['time'] ?? NULL,
            'venue'       => $eventData['venue'] ?? 'Old Trafford',
            'city'        => 'Manchester',
            'country'     => 'United Kingdom',
            'url'         => $eventData['url'] ?? $this->baseUrl . '/tickets',
            'competition' => $eventData['competition'] ?? '',
            'opponent'    => $eventData['opponent'] ?? '',
        ];
    }

    /**
     * Resolve relative URLs to absolute URLs
     */
    /**
     * ResolveUrl
     */
    private function resolveUrl(string $url): string
    {
        if (strpos($url, 'http') === 0) {
            return $url;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
