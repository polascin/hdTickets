<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

class AxsClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.axs.com';
        $this->respectRateLimit('axs');
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
        return $this->scrapeEventDetails($this->baseUrl . '/events/' . $eventId);
    }

    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    /**
     * Scrape AXS search results
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
            Log::error('AXS scraping failed: ' . $e->getMessage());

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
            Log::error('Failed to scrape AXS event details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get  base url
     */
    #[Override]
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
            '.search-result',
            '.event-card',
            '.event-item',
            '[data-testid="event-card"]',
            '.event-listing',
        ];

        foreach ($eventSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$events, &$count, $maxResults) {
                    if ($count >= $maxResults) {
                        return FALSE;
                    }

                    $event = $this->extractEventFromNode($node);
                    if (!empty($event['name'])) {
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
            $priceNodes = $crawler->filter('.price, .ticket-price, [data-testid="price"], .price-display');
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
            Log::debug('Failed to extract AXS prices', ['error' => $e->getMessage()]);
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
            // Extract event name
            $name = $this->trySelectors($node, [
                'h3 a',
                'h2 a',
                '.event-title a',
                '.title a',
                'a[href*="/events/"]',
                '.card-title',
            ]);

            // Extract event URL
            $link = $node->filter('a[href*="/events/"]')->first();
            $url = $link->count() > 0 ? $this->resolveUrl($link->attr('href')) : '';

            // Extract date and time
            $dateTime = $this->trySelectors($node, [
                '.event-date',
                '.date',
                'time',
                '[data-testid="event-date"]',
                '.date-time',
            ]);

            // Parse date
            $parsedDate = $this->parseEventDate($dateTime);

            // Extract venue
            $venue = $this->trySelectors($node, [
                '.venue-name',
                '.event-venue',
                '.location',
                '[data-testid="venue"]',
            ]);

            // Extract city/location
            $city = $this->trySelectors($node, [
                '.event-city',
                '.venue-city',
                '.location-city',
            ]);

            // Extract price information
            $priceData = $this->extractPriceWithFallbacks($node);
            $priceRange = $priceData === [] ? '' : $this->formatPriceRange($priceData);

            $price = $this->trySelectors($node, [
                '.ticket-price',
                '.price',
                '.event-price',
                '[data-testid="price"]',
            ]) ?: $priceRange;

            // Extract category/genre
            $category = $this->trySelectors($node, [
                '.event-category',
                '.category',
                '.genre',
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
                'category'    => trim($category),
                'source'      => 'axs_scrape',
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
            'on_sale_info' => '',
        ];

        try {
            // Check if tickets are available
            $saleStatus = $crawler->filter('.ticket-status, .on-sale, [data-testid="ticket-status"]');
            if ($saleStatus->count() > 0) {
                $statusText = $saleStatus->text();
                $ticketInfo['available'] = stripos($statusText, 'tickets available') !== FALSE
                                          || stripos($statusText, 'on sale') !== FALSE;
                $ticketInfo['on_sale_info'] = trim($statusText);
            }

            // Extract ticket types and prices
            $ticketNodes = $crawler->filter('.ticket-type, .price-level, [data-testid="ticket-option"]');
            $ticketNodes->each(function (Crawler $node) use (&$ticketInfo): void {
                $typeName = $node->filter('.ticket-type-name, .section-name, h3')->text('');
                $priceText = $node->filter('.price, .ticket-price')->text('');
                $fees = $node->filter('.fees, .service-fee')->text('');

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

                // Extract fees
                $feeAmount = 0;
                if (preg_match('/\$(\d+(?:\.\d{2})?)/', $fees, $feeMatches)) {
                    $feeAmount = (float) ($feeMatches[1]);
                }

                if ($typeName !== '' && $typeName !== '0') {
                    $ticketInfo['tickets'][] = [
                        'type'                => trim($typeName),
                        'price'               => $price,
                        'fees'                => $feeAmount,
                        'total_price'         => $price + $feeAmount,
                        'currency'            => $currency,
                        'original_price_text' => trim($priceText),
                        'fee_text'            => trim($fees),
                    ];
                }
            });

            // Check for sold out status
            $soldOutIndicator = $crawler->filter('.sold-out, .unavailable, [data-testid="sold-out"]');
            if ($soldOutIndicator->count() > 0) {
                $ticketInfo['sold_out'] = TRUE;
                $ticketInfo['available'] = FALSE;
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
            'id'       => $eventData['id'] ?? uniqid('axs_'),
            'name'     => $eventData['name'] ?? 'Unnamed Event',
            'date'     => $eventData['date'] ?? NULL,
            'time'     => $eventData['time'] ?? NULL,
            'venue'    => $eventData['venue'] ?? 'TBD',
            'city'     => $eventData['city'] ?? '',
            'country'  => $eventData['country'] ?? '',
            'url'      => $eventData['url'] ?? '',
            'category' => $eventData['category'] ?? '',
        ];
    }

    /**
     * Build search URL for AXS
     */
    /**
     * BuildSearchUrl
     */
    private function buildSearchUrl(string $keyword, string $location = ''): string
    {
        $baseUrl = 'https://www.axs.com/search';
        $params = [
            'q' => $keyword,
        ];

        if ($location !== '' && $location !== '0') {
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
                '.event-name',
                '[data-testid="event-title"]',
            ]);

            $description = $this->trySelectors($crawler, [
                '.event-description',
                '.description',
                '.event-info',
                '.about-event',
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
                '.address',
            ]);

            $city = $this->trySelectors($crawler, [
                '.venue-city',
                '.event-city',
                '.city',
            ]);

            // Extract ticket information
            $ticketInfo = $this->extractTicketInfo($crawler);

            // Extract event image
            $image = '';
            $imgNode = $crawler->filter('.event-image img, .hero-image img, [data-testid="event-image"] img')->first();
            if ($imgNode->count() > 0) {
                $image = $imgNode->attr('src');
            }

            // Extract category/genre
            $category = $this->trySelectors($crawler, [
                '.event-category',
                '.category',
                '.genre-tag',
            ]);

            // Extract age restrictions
            $ageRestriction = $this->trySelectors($crawler, [
                '.age-restriction',
                '.age-limit',
                '[data-testid="age-restriction"]',
            ]);

            return [
                'name'            => trim($name),
                'description'     => trim($description),
                'date_time'       => trim($dateTime),
                'venue'           => trim($venue),
                'address'         => trim($address),
                'city'            => trim($city),
                'category'        => trim($category),
                'age_restriction' => trim($ageRestriction),
                'ticket_info'     => $ticketInfo,
                'image'           => $image,
                'url'             => $url,
                'source'          => 'axs_scrape',
                'scraped_at'      => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error extracting AXS event details: ' . $e->getMessage());

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
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        return rtrim((string) $this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
