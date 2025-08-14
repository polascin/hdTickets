<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class LiveNationClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.livenation.com';
        $this->respectRateLimit('livenation');
    }

    /**
     * SearchEvents
     */
    public function searchEvents(array $criteria): array
    {
        return $this->scrapeSearchResults($criteria['q'] ?? '', $criteria['location'] ?? '', $criteria['per_page'] ?? 50);
    }

    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        return $this->scrapeEventDetails($this->baseUrl . '/event/' . $eventId);
    }

    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    /**
     * Scrape LiveNation search results
     */
    /**
     * ScrapeSearchResults
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        $searchUrl = $this->buildSearchUrl($keyword, $location);

        try {
            $html = $this->makeScrapingRequest($searchUrl);
            $crawler = new Crawler($html);

            return $this->extractSearchResults($crawler, $maxResults);
        } catch (Exception $e) {
            Log::error('LiveNation scraping failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Scrape individual event details
     */
    /**
     * ScrapeEventDetails
     */
    public function scrapeEventDetails(string $url): array
    {
        try {
            $html = $this->makeScrapingRequest($url, ['referer' => $this->baseUrl]);
            $crawler = new Crawler($html);

            return $this->extractEventDetails($crawler, $url);
        } catch (Exception $e) {
            Log::error('Failed to scrape LiveNation event details: ' . $e->getMessage());

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
     * Extract search results from HTML (BaseWebScrapingClient requirement)
     */
    /**
     * ExtractSearchResults
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;

        // Look for different possible selectors for event listings
        $eventSelectors = [
            '.search-result-item',
            '.event-card',
            '.event-item',
            '[data-testid="event-card"]',
            '.ln-u-margin-bottom-xs',
        ];

        foreach ($eventSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$events, &$count, $maxResults) {
                    if ($count >= $maxResults) {
                        return FALSE;
                    }

                    $event = $this->extractEventFromNode($node);
                    if (! empty($event['name'])) {
                        $events[] = $event;
                        $count++;
                    }
                });

                break; // Use first selector that works
            }
        }

        return $events;
    }

    /**
     * Extract prices from crawler (BaseWebScrapingClient requirement)
     */
    /**
     * ExtractPrices
     */
    protected function extractPrices(Crawler $crawler): array
    {
        $prices = [];

        try {
            $priceNodes = $crawler->filter('.price, .ticket-price, [data-testid="price"], .price-range');
            $priceNodes->each(function (Crawler $node) use (&$prices): void {
                $priceText = $node->text('');

                // Extract price from text
                $price = 0;
                $currency = 'USD';
                if (preg_match('/\$([\d,]+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) (str_replace(',', '', $matches[1]));
                } elseif (preg_match('/£([\d,]+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) (str_replace(',', '', $matches[1]));
                    $currency = 'GBP';
                } elseif (preg_match('/€([\d,]+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) (str_replace(',', '', $matches[1]));
                    $currency = 'EUR';
                }

                if ($price > 0) {
                    $prices[] = [
                        'price'    => $price,
                        'currency' => $currency,
                        'section'  => 'General',
                    ];
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract LiveNation prices', ['error' => $e->getMessage()]);
        }

        return $prices;
    }

    /**
     * Extract event data from a single node
     */
    /**
     * ExtractEventFromNode
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        try {
            // Extract artist/event name
            $name = $this->trySelectors($node, [
                'h3 a',
                'h2 a',
                '.event-title a',
                '.artist-name a',
                'a[href*="/event/"]',
                '.card-title a',
            ]);

            // Extract event URL
            $link = $node->filter('a[href*="/event/"]')->first();
            $url = $link->count() > 0 ? $this->resolveUrl($link->attr('href')) : '';

            // Extract date and time
            $dateTime = $this->trySelectors($node, [
                '.event-date',
                '.date-display',
                'time',
                '[data-testid="event-date"]',
                '.concert-date',
            ]);

            // Parse date
            $parsedDate = $this->parseEventDate($dateTime);

            // Extract venue
            $venue = $this->trySelectors($node, [
                '.venue-name',
                '.event-venue',
                '.location',
                '[data-testid="venue"]',
                '.concert-venue',
            ]);

            // Extract city/location
            $city = $this->trySelectors($node, [
                '.event-city',
                '.venue-city',
                '.location-city',
                '.city-name',
            ]);

            // Extract price information
            $priceData = $this->extractPriceWithFallbacks($node);
            $priceRange = ! empty($priceData) ? $this->formatPriceRange($priceData) : '';

            $price = $this->trySelectors($node, [
                '.ticket-price',
                '.event-price',
                '.price-range',
                '[data-testid="price"]',
            ]) ?: $priceRange;

            // Extract genre/category
            $genre = $this->trySelectors($node, [
                '.event-genre',
                '.category',
                '.music-genre',
            ]);

            return [
                'name'        => trim($name),
                'url'         => $url,
                'date'        => trim($dateTime),
                'parsed_date' => $parsedDate,
                'venue'       => trim($venue),
                'city'        => trim($city),
                'price_range' => trim($price),
                'prices'      => $priceData,
                'genre'       => trim($genre),
                'source'      => 'livenation_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to extract event from node', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Extract ticket information from the event page
     */
    /**
     * ExtractTicketInfo
     */
    protected function extractTicketInfo(Crawler $crawler): array
    {
        $ticketInfo = [
            'available'    => FALSE,
            'tickets'      => [],
            'on_sale_date' => '',
        ];

        try {
            // Check if tickets are on sale
            $saleStatus = $crawler->filter('.ticket-status, .on-sale-status, [data-testid="sale-status"]');
            if ($saleStatus->count() > 0) {
                $statusText = $saleStatus->text();
                $ticketInfo['available'] = stripos($statusText, 'on sale') !== FALSE
                                          || stripos($statusText, 'available') !== FALSE;

                // Extract on-sale date if tickets not yet available
                if (preg_match('/on sale (.+?)(?:\s|$)/i', $statusText, $matches)) {
                    $ticketInfo['on_sale_date'] = trim($matches[1]);
                }
            }

            // Extract ticket types and prices
            $ticketNodes = $crawler->filter('.ticket-option, .price-level, [data-testid="ticket-type"]');
            $ticketNodes->each(function (Crawler $node) use (&$ticketInfo): void {
                $sectionName = $node->filter('.section-name, .ticket-type-name, h3')->text('');
                $priceText = $node->filter('.price, .ticket-price')->text('');

                // Extract price from text
                $price = 0;
                $currency = 'USD';
                if (preg_match('/\$(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                } elseif (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'GBP';
                } elseif (preg_match('/€(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'EUR';
                }

                if (! empty($sectionName)) {
                    $ticketInfo['tickets'][] = [
                        'section'       => trim($sectionName),
                        'price'         => $price,
                        'currency'      => $currency,
                        'original_text' => trim($priceText),
                    ];
                }
            });

            // Check for presale information
            $presaleInfo = $crawler->filter('.presale-info, .early-access, [data-testid="presale"]');
            if ($presaleInfo->count() > 0) {
                $ticketInfo['presale_available'] = TRUE;
                $ticketInfo['presale_info'] = $presaleInfo->text();
            }
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
            'id'      => $eventData['id'] ?? uniqid('livenation_'),
            'name'    => $eventData['name'] ?? 'Unnamed Event',
            'date'    => $eventData['date'] ?? NULL,
            'time'    => $eventData['time'] ?? NULL,
            'venue'   => $eventData['venue'] ?? 'TBD',
            'city'    => $eventData['city'] ?? '',
            'country' => $eventData['country'] ?? 'United States',
            'url'     => $eventData['url'] ?? '',
            'genre'   => $eventData['genre'] ?? '',
        ];
    }

    /**
     * Build search URL for LiveNation
     */
    /**
     * BuildSearchUrl
     */
    private function buildSearchUrl(string $keyword, string $location = ''): string
    {
        $baseUrl = 'https://www.livenation.com/search';
        $params = [
            'q'    => $keyword,
            'sort' => 'eventdate_asc',
        ];

        if (! empty($location)) {
            $params['location'] = $location;
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Extract detailed event information from event page
     */
    /**
     * ExtractEventDetails
     */
    private function extractEventDetails(Crawler $crawler, string $url): array
    {
        try {
            $name = $this->trySelectors($crawler, [
                'h1.event-title',
                'h1',
                '.artist-name',
                '[data-testid="event-title"]',
            ]);

            $description = $this->trySelectors($crawler, [
                '.event-description',
                '.artist-bio',
                '.event-info',
                '.description-content',
            ]);

            $dateTime = $this->trySelectors($crawler, [
                '.event-datetime',
                '.date-time',
                'time',
                '[data-testid="event-date-time"]',
            ]);

            $venue = $this->trySelectors($crawler, [
                '.venue-name',
                '.event-venue h2',
                '[data-testid="venue-name"]',
            ]);

            $address = $this->trySelectors($crawler, [
                '.venue-address',
                '.event-address',
                '.location-address',
            ]);

            $city = $this->trySelectors($crawler, [
                '.venue-city',
                '.event-city',
                '.location-city',
            ]);

            // Extract ticket information
            $ticketInfo = $this->extractTicketInfo($crawler);

            // Extract event image
            $image = '';
            $imgNode = $crawler->filter('.event-image img, .artist-image img, [data-testid="event-image"] img')->first();
            if ($imgNode->count() > 0) {
                $image = $imgNode->attr('src');
            }

            // Extract genre/category
            $genre = $this->trySelectors($crawler, [
                '.event-genre',
                '.music-category',
                '.genre-tag',
            ]);

            return [
                'name'        => trim($name),
                'description' => trim($description),
                'date_time'   => trim($dateTime),
                'venue'       => trim($venue),
                'address'     => trim($address),
                'city'        => trim($city),
                'genre'       => trim($genre),
                'ticket_info' => $ticketInfo,
                'image'       => $image,
                'url'         => $url,
                'source'      => 'livenation_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error extracting LiveNation event details: ' . $e->getMessage());

            return [];
        }
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
