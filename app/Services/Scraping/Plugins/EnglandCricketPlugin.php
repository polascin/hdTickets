<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;

class EnglandCricketPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
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
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'England Cricket';
        $this->platform = 'england_cricket';
        $this->description = 'Official England Cricket tickets - Test matches, ODIs, T20Is across all venues';
        $this->baseUrl = 'https://www.ecb.co.uk';
        $this->venue = 'Various England Cricket Grounds';
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
            'test_matches',
            'odi_matches',
            't20i_matches',
            'ashes_series',
            'world_cup_matches',
            't20_world_cup',
            'the_hundred',
            'county_championship',
            'vitality_blast',
            'womens_cricket',
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
            'match_format',
            'opposition',
            'venue',
            'price_range',
            'ticket_type',
            'series',
        ];
    }

    protected function buildSearchUrl(array $criteria): string
    {
        return $this->baseUrl . '/tickets';
    }

    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.fixture-item, .match-item, .event-item')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseMatchItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse England Cricket match item', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse England Cricket search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    protected function parseMatchItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.match-title, .fixture-title, h2, h3');
            $opposition = $this->extractText($node, '.opposition, .vs, .teams');
            $venue = $this->extractText($node, '.venue, .ground');
            $date = $this->extractText($node, '.date, .match-date');
            $format = $this->extractText($node, '.format, .match-type');
            $priceText = $this->extractText($node, '.price, .from-price');
            $availability = $this->extractText($node, '.availability, .status');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return NULL;
            }

            return [
                'title'        => trim($title),
                'opposition'   => trim($opposition),
                'venue'        => trim($venue) ?: $this->venue,
                'location'     => $this->determineLocation($venue),
                'date'         => $this->parseDate($date),
                'match_format' => $this->determineMatchFormat($format, $title),
                'price'        => $this->parsePrice($priceText),
                'currency'     => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url'          => $link ? $this->buildFullUrl($link) : NULL,
                'platform'     => $this->platform,
                'category'     => 'cricket',
                'team'         => 'England',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse England Cricket match item', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    protected function determineMatchFormat(string $format, string $title): string
    {
        $lowerFormat = strtolower($format);
        $lowerTitle = strtolower($title);

        if (strpos($lowerFormat, 'test') !== FALSE || strpos($lowerTitle, 'test') !== FALSE) {
            return 'test';
        }
        if (strpos($lowerFormat, 'odi') !== FALSE || strpos($lowerTitle, 'odi') !== FALSE) {
            return 'odi';
        }
        if (strpos($lowerFormat, 't20') !== FALSE || strpos($lowerTitle, 't20') !== FALSE) {
            return 't20i';
        }

        return 'unknown';
    }

    protected function determineLocation(string $venue): string
    {
        $lowerVenue = strtolower($venue);

        if (strpos($lowerVenue, 'lords') !== FALSE) {
            return 'London';
        }
        if (strpos($lowerVenue, 'oval') !== FALSE) {
            return 'London';
        }
        if (strpos($lowerVenue, 'old trafford') !== FALSE) {
            return 'Manchester';
        }
        if (strpos($lowerVenue, 'headingley') !== FALSE) {
            return 'Leeds';
        }
        if (strpos($lowerVenue, 'edgbaston') !== FALSE) {
            return 'Birmingham';
        }

        return 'England';
    }

    protected function parseAvailability(string $status): string
    {
        $lowerStatus = strtolower($status);

        if (strpos($lowerStatus, 'sold out') !== FALSE) {
            return 'sold_out';
        }
        if (strpos($lowerStatus, 'available') !== FALSE) {
            return 'available';
        }

        return 'check_website';
    }

    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return NULL;
        }

        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    protected function buildFullUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Required abstract methods
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/tickets';
    }

    protected function getEventNameSelectors(): string
    {
        return '.match-title, .fixture-title, h2, h3';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .match-date';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue, .ground';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .from-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status';
    }
}
