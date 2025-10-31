<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;

class BandsintownPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
    #[Override]
    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new Exception("{$this->pluginName} plugin is disabled");
        }

        Log::info("Starting {$this->pluginName} scraping", $criteria);

        try {
            $this->applyRateLimit($this->platform);

            $searchUrl = $this->buildSearchUrl($criteria);
            $html = $this->makeHttpRequest($searchUrl);
            $events = $this->parseSearchResults($html);
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info("{$this->pluginName} scraping completed", [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error("{$this->pluginName} scraping failed", [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get events by artist
     */
    public function getEventsByArtist(string $artist, array $criteria = []): array
    {
        $criteria['artist'] = $artist;

        return $this->scrape($criteria);
    }

    /**
     * Get events by venue
     */
    public function getEventsByVenue(string $venue, array $criteria = []): array
    {
        $criteria['venue'] = $venue;

        return $this->scrape($criteria);
    }

    /**
     * Get events by location
     */
    public function getEventsByLocation(string $location, array $criteria = []): array
    {
        $criteria['location'] = $location;

        return $this->scrape($criteria);
    }

    /**
     * Get events by genre
     */
    public function getEventsByGenre(string $genre, array $criteria = []): array
    {
        $criteria['genre'] = $genre;

        return $this->scrape($criteria);
    }

    /**
     * Get concert events in a city
     */
    public function getConcertsInCity(string $city, array $criteria = []): array
    {
        $criteria['city'] = $city;

        return $this->scrape($criteria);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Bandsintown';
        $this->platform = 'bandsintown';
        $this->description = 'Bandsintown - Concert discovery and music event tracking platform';
        $this->baseUrl = 'https://www.bandsintown.com';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->language = 'en-US';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'concerts',
            'music_festivals',
            'live_music',
            'artist_tracking',
            'venue_events',
            'tour_dates',
            'indie_music',
            'rock',
            'pop',
            'hip_hop',
            'electronic',
            'folk',
            'country',
            'jazz',
            'classical',
            'reggae',
            'punk',
            'metal',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'artist',
            'location',
            'venue',
            'date_range',
            'genre',
            'keyword',
            'city',
            'radius',
        ];
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $params = [];

        if (!empty($criteria['artist'])) {
            // Artist-specific search
            $artistSlug = $this->slugify($criteria['artist']);

            return $this->baseUrl . '/a/' . $artistSlug;
        }

        if (!empty($criteria['location'])) {
            $params['location'] = urlencode((string) $criteria['location']);
        }

        if (!empty($criteria['city'])) {
            $params['city'] = urlencode((string) $criteria['city']);
        }

        if (!empty($criteria['venue'])) {
            $params['venue'] = urlencode((string) $criteria['venue']);
        }

        if (!empty($criteria['genre'])) {
            $params['genre'] = urlencode((string) $criteria['genre']);
        }

        if (!empty($criteria['keyword'])) {
            $params['search'] = urlencode((string) $criteria['keyword']);
        }

        if (!empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['start_date'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['end_date'] = $criteria['date_range']['end'];
            }
        }

        $queryString = http_build_query($params);

        // Use events search if we have search criteria
        if (!empty($criteria['location']) || !empty($criteria['city'])) {
            return $this->baseUrl . '/events?' . $queryString;
        }

        return $this->baseUrl . '/search?' . $queryString;
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            // Try multiple selectors for event items
            $eventSelectors = [
                '.event-item',
                '.event-card',
                '[data-testid="event-item"]',
                '.show-item',
                '.concert-listing',
            ];

            foreach ($eventSelectors as $selector) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$events): void {
                    try {
                        $event = $this->parseEventItem($node);
                        if ($event) {
                            $events[] = $event;
                        }
                    } catch (Exception $e) {
                        Log::debug('Failed to parse Bandsintown event item', ['error' => $e->getMessage()]);
                    }
                });

                // If we found events with this selector, break
                if ($events !== []) {
                    break;
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to parse Bandsintown search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event item
     */
    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $artist = $this->extractText($node, '.artist-name, .event-artist, h3, .performer-name');
            $venue = $this->extractText($node, '.venue-name, .event-venue, .location-name');
            $location = $this->extractText($node, '.location, .event-location, .city-state, .venue-location');
            $date = $this->extractText($node, '.date, .event-date, time, .show-date');
            $time = $this->extractText($node, '.time, .event-time, .door-time');
            $priceText = $this->extractText($node, '.price, .ticket-price, .admission');
            $link = $this->extractAttribute($node, 'a', 'href');
            $imageUrl = $this->extractAttribute($node, '.artist-image img, .event-image img', 'src');

            if ($artist === '' || $artist === '0') {
                return NULL;
            }

            // Create title from artist name
            $title = $artist . ($venue !== '' && $venue !== '0' ? ' at ' . $venue : '');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDate($date);
            $eventTime = $this->parseTime($time);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : NULL;

            // Build full image URL
            $fullImageUrl = $imageUrl ? $this->buildFullUrl($imageUrl) : NULL;

            return [
                'title'        => trim($title),
                'artist'       => trim($artist),
                'venue'        => trim($venue),
                'location'     => trim($location ?: $venue),
                'date'         => $eventDate,
                'time'         => $eventTime,
                'price'        => $price,
                'currency'     => $this->currency,
                'url'          => $fullUrl,
                'image_url'    => $fullImageUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => 'concert',
                'availability' => 'available',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse Bandsintown event item', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if ($priceText === '' || $priceText === '0') {
            return NULL;
        }

        // Check for free events
        if (preg_match('/free|gratis|gratuit/i', $priceText)) {
            return 0.0;
        }

        // Extract numeric price
        if (preg_match('/(?:[\$£€¥]|USD|GBP|EUR|JPY)\s*(\d+(?:[\.,]\d{2})?)/', $priceText, $matches)) {
            $price = str_replace(',', '', $matches[1]);

            return (float) $price;
        }

        return NULL;
    }

    /**
     * Parse time from text
     */
    protected function parseTime(string $timeText): ?string
    {
        if ($timeText === '' || $timeText === '0') {
            return NULL;
        }

        try {
            // Try to parse time
            $time = Carbon::parse($timeText);

            return $time->format('H:i');
        } catch (Exception $e) {
            Log::debug('Failed to parse Bandsintown time', ['time' => $timeText, 'error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse date from various formats
     */
    #[Override]
    protected function parseDate(string $dateText): ?string
    {
        if ($dateText === '' || $dateText === '0') {
            return NULL;
        }

        try {
            // Try common date formats
            $date = Carbon::parse($dateText);

            return $date->format('Y-m-d');
        } catch (Exception $e) {
            Log::debug('Failed to parse Bandsintown date', ['date' => $dateText, 'error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Create URL-friendly slug from artist name
     */
    protected function slugify(string $text): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', trim($text)));
    }

    /**
     * Build full URL
     */
    protected function buildFullUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Required abstract methods from BaseScraperPlugin

    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/events';
    }

    protected function getEventNameSelectors(): string
    {
        return '.artist-name, .event-artist, h3, .performer-name';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .event-date, time, .show-date';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue-name, .event-venue, .location-name';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .ticket-price, .admission';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.status, .availability, .sold-out';
    }
}
