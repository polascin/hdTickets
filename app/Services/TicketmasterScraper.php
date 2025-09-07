<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketApis\TicketmasterClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function count;
use function strlen;

class TicketmasterScraper
{
    /** @var TicketmasterClient */
    protected $ticketmasterClient;

    /** @var int */
    protected $defaultUserId;

    /** @var int */
    protected $defaultCategoryId;

    public function __construct()
    {
        $config = [
            'enabled'        => TRUE,
            'base_url'       => 'https://app.ticketmaster.com/discovery/v2/',
            'timeout'        => 30,
            'retry_attempts' => 3,
            'retry_delay'    => 2,
        ];

        $this->ticketmasterClient = new TicketmasterClient($config);

        // Get or create default user for scraping
        $this->defaultUserId = $this->getDefaultUser();

        // Get or create default category for scraped tickets
        $this->defaultCategoryId = $this->getDefaultCategory();
    }

    /**
     * Search and import tickets from Ticketmaster
     *
     * @return array<string, mixed>
     */
    /**
     * SearchAndImportTickets
     */
    public function searchAndImportTickets(string $keyword, string $location = '', int $maxResults = 50): array
    {
        Log::info('Starting Ticketmaster scraping', [
            'keyword'     => $keyword,
            'location'    => $location,
            'max_results' => $maxResults,
        ]);

        try {
            $searchResults = $this->ticketmasterClient->scrapeSearchResults($keyword, $location, $maxResults);

            if (empty($searchResults)) {
                Log::warning('No search results found for keyword: ' . $keyword);

                return ['success' => FALSE, 'message' => 'No events found', 'imported' => 0];
            }

            $imported = 0;
            $errors = [];

            foreach ($searchResults as $eventData) {
                try {
                    // Get detailed event information
                    if (!empty($eventData['url'])) {
                        $detailedEvent = $this->ticketmasterClient->scrapeEventDetails($eventData['url']);
                        if (!empty($detailedEvent)) {
                            $eventData = array_merge($eventData, $detailedEvent);
                        }
                    }

                    // Import the event as a ticket
                    if ($this->importEventAsTicket($eventData)) {
                        $imported++;
                    }

                    // Add small delay to be respectful to the server
                    usleep(500000); // 0.5 second delay
                } catch (Exception $e) {
                    $errors[] = [
                        'event' => $eventData['name'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to import event', [
                        'event' => $eventData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Ticketmaster scraping completed', [
                'total_found' => count($searchResults),
                'imported'    => $imported,
                'errors'      => count($errors),
            ]);

            return [
                'success'     => TRUE,
                'total_found' => count($searchResults),
                'imported'    => $imported,
                'errors'      => $errors,
                'message'     => "Successfully imported {$imported} out of " . count($searchResults) . ' events',
            ];
        } catch (Exception $e) {
            Log::error('Ticketmaster scraping failed', [
                'keyword' => $keyword,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success'  => FALSE,
                'message'  => 'Scraping failed: ' . $e->getMessage(),
                'imported' => 0,
            ];
        }
    }

    /**
     * Get scraping statistics
     */
    /**
     * @return array<string, mixed>
     */
    /**
     * Get  scraping stats
     */
    public function getScrapingStats(): array
    {
        $totalScraped = Ticket::where('metadata->source', 'ticketmaster_scrape')->count();
        $recentlyScraped = Ticket::where('metadata->source', 'ticketmaster_scrape')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $categoryStats = Ticket::where('metadata->source', 'ticketmaster_scrape')
            ->selectRaw('COUNT(*) as count, category_id')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        return [
            'total_scraped'      => $totalScraped,
            'recently_scraped'   => $recentlyScraped,
            'category_breakdown' => $categoryStats,
            'last_scrape'        => Ticket::where('metadata->source', 'ticketmaster_scrape')
                ->latest('created_at')
                ->first()?->created_at,
        ];
    }

    /**
     * Import a single event as a ticket
     */
    /**
     * @param array<string, mixed> $eventData
     */
    /**
     * ImportEventAsTicket
     */
    private function importEventAsTicket(array $eventData): bool
    {
        try {
            // Check if ticket already exists (by URL or name)
            $existingTicket = Ticket::where('metadata->ticketmaster_url', $eventData['url'])
                ->orWhere(function ($query) use ($eventData): void {
                    $query->where('title', $eventData['name'])
                        ->where('metadata->source', 'ticketmaster_scrape');
                })
                ->first();

            if ($existingTicket) {
                Log::info('Ticket already exists, skipping: ' . $eventData['name']);

                return FALSE;
            }

            // Parse date and time
            $dueDate = $this->parseEventDate($eventData);

            // Create description from available data
            $description = $this->buildEventDescription($eventData);

            // Determine priority based on price range or keywords
            $priority = $this->determinePriority($eventData);

            // Create the ticket
            $ticket = Ticket::create([
                'title'       => $eventData['name'] ?? 'Unnamed Event',
                'description' => $description,
                'user_id'     => $this->defaultUserId,
                'category_id' => $this->defaultCategoryId,
                'status'      => Ticket::STATUS_OPEN,
                'priority'    => $priority,
                'source'      => Ticket::SOURCE_API,
                'due_date'    => $dueDate,
                'tags'        => $this->extractTags($eventData),
                'metadata'    => [
                    'source'           => 'ticketmaster_scrape',
                    'ticketmaster_url' => $eventData['url'] ?? '',
                    'venue'            => $eventData['venue'] ?? '',
                    'address'          => $eventData['address'] ?? '',
                    'price_range'      => $eventData['price_range'] ?? '',
                    'prices'           => $eventData['prices'] ?? [],
                    'image'            => $eventData['image'] ?? '',
                    'scraped_at'       => $eventData['scraped_at'] ?? now()->toISOString(),
                    'original_data'    => $eventData,
                ],
            ]);

            Log::info('Successfully imported ticket', [
                'ticket_id' => $ticket->id,
                'title'     => $ticket->title,
                'event_url' => $eventData['url'] ?? 'N/A',
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to import event as ticket', [
                'event' => $eventData['name'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Parse event date from various formats
     */
    /**
     * @param array<string, mixed> $eventData
     */
    /**
     * ParseEventDate
     */
    private function parseEventDate(array $eventData): ?Carbon
    {
        $dateString = $eventData['date_time'] ?? $eventData['date'] ?? NULL;

        if (empty($dateString)) {
            return NULL;
        }

        try {
            // Try various date formats
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d',
                'M d, Y H:i',
                'M d, Y',
                'F d, Y H:i A',
                'F d, Y',
                'd/m/Y H:i',
                'd/m/Y',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString);
                } catch (Exception $e) {
                    continue;
                }
            }

            // Try Carbon's flexible parsing
            return Carbon::parse($dateString);
        } catch (Exception $e) {
            Log::warning('Failed to parse event date: ' . $dateString);

            return NULL;
        }
    }

    /**
     * Build event description from scraped data
     */
    /**
     * @param array<string, mixed> $eventData
     */
    /**
     * BuildEventDescription
     */
    private function buildEventDescription(array $eventData): string
    {
        $parts = [];

        if (!empty($eventData['description'])) {
            $parts[] = $eventData['description'];
        }

        if (!empty($eventData['venue'])) {
            $parts[] = 'Venue: ' . $eventData['venue'];
        }

        if (!empty($eventData['address'])) {
            $parts[] = 'Address: ' . $eventData['address'];
        }

        if (!empty($eventData['date_time']) || !empty($eventData['date'])) {
            $dateTime = $eventData['date_time'] ?? $eventData['date'];
            $parts[] = 'Event Date: ' . $dateTime;
        }

        if (!empty($eventData['price_range'])) {
            $parts[] = 'Price Range: ' . $eventData['price_range'];
        }

        if (!empty($eventData['url'])) {
            $parts[] = 'Ticketmaster URL: ' . $eventData['url'];
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Determine priority based on event data
     */
    /**
     * @param array<string, mixed> $eventData
     */
    /**
     * DeterminePriority
     */
    private function determinePriority(array $eventData): string
    {
        $name = strtolower($eventData['name'] ?? '');
        $priceRange = $eventData['price_range'] ?? '';

        // High priority keywords
        $highPriorityKeywords = ['concert', 'festival', 'championship', 'final', 'playoff'];
        foreach ($highPriorityKeywords as $keyword) {
            if (strpos($name, $keyword) !== FALSE) {
                return Ticket::PRIORITY_HIGH;
            }
        }

        // Check if expensive (assuming high price = high priority)
        if (preg_match('/\$(\d+)/', $priceRange, $matches)) {
            $price = (int) ($matches[1]);
            if ($price > 200) {
                return Ticket::PRIORITY_HIGH;
            }
            if ($price > 100) {
                return Ticket::PRIORITY_MEDIUM;
            }
        }

        return Ticket::PRIORITY_MEDIUM;
    }

    /**
     * Extract tags from event data
     */
    /**
     * @param array<string, mixed> $eventData
     *
     * @return array<string>
     */
    /**
     * ExtractTags
     */
    private function extractTags(array $eventData): array
    {
        $tags = ['ticketmaster', 'scraped'];

        $name = strtolower($eventData['name'] ?? '');

        // Add category-based tags
        if (strpos($name, 'concert') !== FALSE || strpos($name, 'music') !== FALSE) {
            $tags[] = 'music';
        }
        if (strpos($name, 'sport') !== FALSE || strpos($name, 'game') !== FALSE) {
            $tags[] = 'sports';
        }
        if (strpos($name, 'theater') !== FALSE || strpos($name, 'play') !== FALSE) {
            $tags[] = 'theater';
        }
        if (strpos($name, 'comedy') !== FALSE) {
            $tags[] = 'comedy';
        }

        // Add venue as tag if available
        if (!empty($eventData['venue'])) {
            $venueTag = Str::slug($eventData['venue']);
            if (strlen($venueTag) <= 30) {
                $tags[] = $venueTag;
            }
        }

        return array_unique($tags);
    }

    /**
     * Get or create default user for scraping
     */
    /**
     * Get  default user
     */
    private function getDefaultUser(): int
    {
        $user = User::where('email', 'ticketmaster.scraper@hdtickets.local')->first();

        if (!$user) {
            $user = User::create([
                'name'              => 'Ticketmaster Scraper',
                'email'             => 'ticketmaster.scraper@hdtickets.local',
                'password'          => bcrypt(Str::random(32)),
                'role'              => User::ROLE_AGENT,
                'email_verified_at' => now(),
            ]);
        }

        return $user->id;
    }

    /**
     * Get or create default category for scraped tickets
     */
    /**
     * Get  default category
     */
    private function getDefaultCategory(): int
    {
        $category = Category::where('name', 'Ticketmaster Events')->first();

        if (!$category) {
            $category = Category::create([
                'name'        => 'Ticketmaster Events',
                'description' => 'Events imported from Ticketmaster scraping',
                'color'       => '#E31837', // Ticketmaster brand color
                'is_active'   => TRUE,
            ]);
        }

        return $category->id;
    }
}
