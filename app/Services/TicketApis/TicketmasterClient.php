<?php

namespace App\Services\TicketApis;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class TicketmasterClient extends BaseApiClient
{
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'Laravel Ticker Manager/1.0'
        ];
    }

    public function searchEvents(array $criteria): array
    {
        return $this->makeRequest('GET', 'events', $criteria);
    }

    public function getEvent(string $eventId): array
    {
        return $this->makeRequest('GET', "events/{$eventId}");
    }

    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    /**
     * Scrape Ticketmaster search results
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        $client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
            ]
        ]);

        $searchUrl = $this->buildSearchUrl($keyword, $location);
        
        try {
            $response = $client->get($searchUrl);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            return $this->extractSearchResults($crawler, $maxResults);
        } catch (\Exception $e) {
            \Log::error('Ticketmaster scraping failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Scrape individual event details
     */
    public function scrapeEventDetails(string $url): array
    {
        $client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ]
        ]);

        try {
            $response = $client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            return $this->extractEventDetails($crawler, $url);
        } catch (\Exception $e) {
            \Log::error('Failed to scrape event details: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build search URL for Ticketmaster
     */
    private function buildSearchUrl(string $keyword, string $location = ''): string
    {
        $baseUrl = 'https://www.ticketmaster.com/search';
        $params = [
            'q' => $keyword,
            'sort' => 'date,asc',
        ];

        if (!empty($location)) {
            $params['city'] = $location;
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Extract search results from HTML
     */
    private function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;

        // Look for different possible selectors for event listings
        $eventSelectors = [
            '[data-testid="event-tile"]',
            '.event-tile',
            '.search-result-item',
            '.event-card'
        ];

        foreach ($eventSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$events, &$count, $maxResults) {
                    if ($count >= $maxResults) {
                        return false;
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
     * Extract event data from a single node
     */
    private function extractEventFromNode(Crawler $node): array
    {
        try {
            // Try multiple selectors for different page layouts
            $name = $this->trySelectors($node, [
                'h3 a',
                '.event-name a',
                '[data-testid="event-name"] a',
                'h2 a',
                'a[href*="/event/"]'
            ]);

            $link = $node->filter('a[href*="/event/"]')->first();
            $url = $link->count() > 0 ? 'https://www.ticketmaster.com' . $link->attr('href') : '';

            $date = $this->trySelectors($node, [
                '.event-date',
                '[data-testid="event-date"]',
                '.date',
                'time'
            ]);

            $venue = $this->trySelectors($node, [
                '.venue-name',
                '[data-testid="venue-name"]',
                '.event-venue',
                '.venue'
            ]);

            $price = $this->trySelectors($node, [
                '.price-range',
                '[data-testid="price-range"]',
                '.event-price',
                '.price'
            ]);

            return [
                'name' => trim($name),
                'url' => $url,
                'date' => trim($date),
                'venue' => trim($venue),
                'price_range' => trim($price),
                'source' => 'ticketmaster_scrape'
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract detailed event information from event page
     */
    private function extractEventDetails(Crawler $crawler, string $url): array
    {
        try {
            $name = $this->trySelectors($crawler, [
                'h1[data-testid="event-name"]',
                'h1.event-name',
                'h1.event-title',
                'h1'
            ]);

            $description = $this->trySelectors($crawler, [
                '[data-testid="event-description"]',
                '.event-description',
                '.event-info p',
                '.description'
            ]);

            $dateTime = $this->trySelectors($crawler, [
                '[data-testid="event-date-time"]',
                '.event-datetime',
                '.date-time',
                'time'
            ]);

            $venue = $this->trySelectors($crawler, [
                '[data-testid="venue-name"]',
                '.venue-name',
                '.event-venue h2',
                '.venue h2'
            ]);

            $address = $this->trySelectors($crawler, [
                '[data-testid="venue-address"]',
                '.venue-address',
                '.address',
                '.event-venue .address'
            ]);

            $priceRange = $this->trySelectors($crawler, [
                '[data-testid="price-range"]',
                '.price-range',
                '.ticket-prices',
                '.price-info'
            ]);

            // Extract ticket prices
            $prices = $this->extractPrices($crawler);

            // Extract image
            $image = '';
            $imgNode = $crawler->filter('img[src*="ticketmaster"], .event-image img, [data-testid="event-image"] img')->first();
            if ($imgNode->count() > 0) {
                $image = $imgNode->attr('src');
            }

            return [
                'name' => trim($name),
                'description' => trim($description),
                'date_time' => trim($dateTime),
                'venue' => trim($venue),
                'address' => trim($address),
                'price_range' => trim($priceRange),
                'prices' => $prices,
                'image' => $image,
                'url' => $url,
                'source' => 'ticketmaster_scrape',
                'scraped_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            \Log::error('Error extracting event details: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract ticket prices from the page
     */
    private function extractPrices(Crawler $crawler): array
    {
        $prices = [];
        
        try {
            $priceNodes = $crawler->filter('.ticket-price, .price-level, [data-testid="price-level"]');
            
            $priceNodes->each(function (Crawler $node) use (&$prices) {
                $priceText = $node->text();
                $sectionText = $node->closest('tr, .section')->filter('.section-name, .seat-type')->text('');
                
                if (preg_match('/\$([\d,]+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $prices[] = [
                        'section' => trim($sectionText) ?: 'General',
                        'price' => floatval(str_replace(',', '', $matches[1])),
                        'currency' => 'USD'
                    ];
                }
            });
        } catch (\Exception $e) {
            // Ignore price extraction errors
        }
        
        return $prices;
    }

    /**
     * Try multiple selectors and return first match
     */
    private function trySelectors(Crawler $crawler, array $selectors): string
    {
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector)->first();
                if ($node->count() > 0) {
                    return $node->text();
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return '';
    }

    protected function transformEventData(array $eventData): array
    {
        return [
            'id' => $eventData['id'] ?? null,
            'name' => $eventData['name'] ?? 'Unnamed Event',
            'date' => $eventData['dates']['start']['localDate'] ?? null,
            'time' => $eventData['dates']['start']['localTime'] ?? null,
            'status' => $eventData['dates']['status']['code'] ?? 'unknown',
            'venue' => $eventData['_embedded']['venues'][0]['name'] ?? 'Unknown Venue',
            'city' => $eventData['_embedded']['venues'][0]['city']['name'] ?? 'Unknown City',
            'country' => $eventData['_embedded']['venues'][0]['country']['name'] ?? 'Unknown Country',
            'url' => $eventData['url'] ?? '',
        ];
    }
}
