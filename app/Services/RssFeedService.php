<?php declare(strict_types=1);

namespace App\Services;

use App\Models\TicketSource;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFeedService
{
    protected $feedUrls = [
        'official_events' => [
            // Add legitimate RSS feeds here
            // 'https://example-venue.com/events.rss',
        ],
        'news_feeds' => [
            // Add event news RSS feeds
            // 'https://news-site.com/events.rss',
        ],
    ];

    /**
     * Parse RSS feed and extract event information
     */
    /**
     * ParseFeed
     */
    public function parseFeed(string $feedUrl): array
    {
        try {
            $response = Http::timeout(30)->get($feedUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch RSS feed: ' . $response->status());
            }

            $xml = simplexml_load_string($response->body());
            $events = [];

            foreach ($xml->channel->item as $item) {
                $events[] = [
                    'title'       => (string) $item->title,
                    'description' => (string) $item->description,
                    'link'        => (string) $item->link,
                    'pub_date'    => (string) $item->pubDate,
                    'category'    => (string) $item->category,
                ];
            }

            return $events;
        } catch (Exception $e) {
            Log::error('RSS feed parsing failed', [
                'url'   => $feedUrl,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process all configured RSS feeds
     */
    /**
     * ProcessAllFeeds
     */
    public function processAllFeeds(): int
    {
        $totalEvents = 0;

        foreach ($this->feedUrls as $category => $urls) {
            foreach ($urls as $url) {
                try {
                    $events = $this->parseFeed($url);
                    $processed = $this->saveEventsFromFeed($events, $category);
                    $totalEvents += $processed;

                    Log::info('Processed RSS feed', [
                        'url'          => $url,
                        'category'     => $category,
                        'events_count' => $processed,
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to process RSS feed', [
                        'url'   => $url,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $totalEvents;
    }

    /**
     * Save events from RSS feed
     */
    /**
     * SaveEventsFromFeed
     */
    protected function saveEventsFromFeed(array $events, string $category): int
    {
        $saved = 0;

        foreach ($events as $eventData) {
            try {
                $eventDate = $this->parseEventDate($eventData['description']);
                $venue = $this->parseVenue($eventData['description']);

                TicketSource::updateOrCreate([
                    'platform' => 'rss_feed',
                    'url'      => $eventData['link'],
                ], [
                    'name'                => $eventData['title'],
                    'event_name'          => $eventData['title'],
                    'event_date'          => $eventDate ?: now()->addDays(30),
                    'venue'               => $venue ?: 'TBD',
                    'availability_status' => TicketSource::STATUS_UNKNOWN,
                    'description'         => $eventData['description'],
                    'last_checked'        => now(),
                    'is_active'           => TRUE,
                ]);

                $saved++;
            } catch (Exception $e) {
                Log::warning('Failed to save RSS event', [
                    'event' => $eventData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $saved;
    }

    /**
     * Parse event date from description (implement based on feed format)
     */
    /**
     * ParseEventDate
     */
    protected function parseEventDate(string $description): ?string
    {
        // Implement date parsing logic based on your RSS feed format
        // This is a basic example - customize for actual feeds
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $description, $matches)) {
            return $matches[1] . ' 00:00:00';
        }

        return NULL;
    }

    /**
     * Parse venue from description (implement based on feed format)
     */
    /**
     * ParseVenue
     */
    protected function parseVenue(string $description): ?string
    {
        // Implement venue parsing logic based on your RSS feed format
        // This is a basic example - customize for actual feeds
        if (preg_match('/at (.+?)(?:\s|$)/', $description, $matches)) {
            return trim($matches[1]);
        }

        return NULL;
    }
}
