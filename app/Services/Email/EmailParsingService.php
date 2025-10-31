<?php declare(strict_types=1);

namespace App\Services\Email;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function array_slice;
use function strlen;

/**
 * Email Parsing Service
 *
 * Extracts sports event and ticket information from email content
 * for the HD Tickets monitoring system.
 */
class EmailParsingService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('imap');
    }

    /**
     * Parse email content to extract sports event information
     *
     * @param array $emailData Complete email data
     *
     * @return array Parsed sports event information
     */
    public function parseEmailContent(array $emailData): array
    {
        $platform = $emailData['platform'] ?? 'unknown';
        $headers = $emailData['headers'] ?? [];
        $body = $emailData['body'] ?? '';

        $parsed = [
            'platform'      => $platform,
            'email_uid'     => $emailData['uid'] ?? NULL,
            'connection'    => $emailData['connection'] ?? NULL,
            'processed_at'  => now()->toISOString(),
            'sports_events' => [],
            'tickets'       => [],
            'metadata'      => $this->extractMetadata($headers, $body),
        ];

        try {
            // Use platform-specific parsing if available
            if (method_exists($this, 'parse' . Str::studly($platform) . 'Email')) {
                $method = 'parse' . Str::studly($platform) . 'Email';
                $platformData = $this->$method($headers, $body);
                $parsed = array_merge($parsed, $platformData);
            } else {
                // Use generic parsing
                $genericData = $this->parseGenericEmail($headers, $body);
                $parsed = array_merge($parsed, $genericData);
            }

            // Validate and clean parsed data
            $parsed = $this->validateParsedData($parsed);
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->error('Failed to parse email content', [
                    'platform' => $platform,
                    'uid'      => $emailData['uid'] ?? NULL,
                    'error'    => $e->getMessage(),
                ]);

            $parsed['parsing_error'] = $e->getMessage();
        }

        return $parsed;
    }

    /**
     * Get parsing statistics
     *
     * @return array Parsing statistics
     */
    public function getParsingStats(): array
    {
        return [
            'supported_platforms' => [
                'ticketmaster',
                'stubhub',
                'viagogo',
                'seatgeek',
                'tickpick',
                'generic',
            ],
            'sport_categories' => [
                'football',
                'basketball',
                'baseball',
                'hockey',
                'soccer',
                'tennis',
                'golf',
                'racing',
                'boxing',
                'mma',
            ],
        ];
    }

    /**
     * Parse Ticketmaster emails
     *
     * @param string $body Email body
     *
     * @return array Parsed data
     */
    private function parseTicketmasterEmail(string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        // Extract event information from Ticketmaster emails
        $eventPatterns = [
            'event_name'        => '/(?:Event|Show|Game):\s*(.+?)(?:\n|$)/i',
            'venue'             => '/(?:Venue|Location|Arena|Stadium):\s*(.+?)(?:\n|$)/i',
            'date'              => '/(?:Date|When):\s*(.+?)(?:\n|$)/i',
            'time'              => '/(?:Time):\s*(.+?)(?:\n|$)/i',
            'price'             => '/(?:Price|From|Starting at):\s*\$?([0-9,]+\.?[0-9]*)/i',
            'tickets_available' => '/(\d+)\s+(?:tickets?|seats?)\s+(?:available|remaining)/i',
        ];

        foreach ($eventPatterns as $key => $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $data['metadata'][$key] = trim($matches[1]);
            }
        }

        // Extract multiple events if present
        if (preg_match_all('/(?:Event|Show|Game):\s*(.+?)[\n\r]/i', $body, $matches)) {
            foreach ($matches[1] as $eventName) {
                $data['sports_events'][] = [
                    'name'            => trim($eventName),
                    'source_platform' => 'ticketmaster',
                    'category'        => $this->detectSportCategory($eventName),
                ];
            }
        }

        // Extract ticket information
        if (preg_match_all('/\$([0-9,]+\.?[0-9]*)\s*(?:each|per ticket)?/i', $body, $priceMatches)) {
            foreach ($priceMatches[1] as $price) {
                $data['tickets'][] = [
                    'price'               => (float) str_replace(',', '', $price),
                    'source_platform'     => 'ticketmaster',
                    'availability_status' => 'available',
                ];
            }
        }

        return $data;
    }

    /**
     * Parse StubHub emails
     *
     * @param string $body Email body
     *
     * @return array Parsed data
     */
    private function parseStubhubEmail(string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        // StubHub specific patterns
        $patterns = [
            'event_name' => '/(?:for|regarding|about)\s+(.+?)(?:\sat\s|\son\s|$)/i',
            'venue'      => '/(?:at|venue:)\s*(.+?)(?:\son\s|\n|$)/i',
            'date'       => '/(?:on|date:)\s*([A-Za-z]+,?\s+[A-Za-z]+\s+\d{1,2},?\s+\d{4})/i',
            'section'    => '/Section\s+([A-Z0-9]+)/i',
            'row'        => '/Row\s+([A-Z0-9]+)/i',
            'price_drop' => '/(?:dropped to|now)\s+\$([0-9,]+\.?[0-9]*)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $data['metadata'][$key] = trim($matches[1]);
            }
        }

        // Extract events
        if (isset($data['metadata']['event_name'])) {
            $data['sports_events'][] = [
                'name'            => $data['metadata']['event_name'],
                'source_platform' => 'stubhub',
                'category'        => $this->detectSportCategory($data['metadata']['event_name']),
                'venue'           => $data['metadata']['venue'] ?? NULL,
                'event_date'      => $this->parseEventDate($data['metadata']['date'] ?? NULL),
            ];
        }

        // Extract ticket listings
        if (preg_match_all('/\$([0-9,]+(?:\.[0-9]{2})?)\s*(?:each)?/i', $body, $priceMatches)) {
            foreach ($priceMatches[1] as $price) {
                $data['tickets'][] = [
                    'price'               => (float) str_replace(',', '', $price),
                    'source_platform'     => 'stubhub',
                    'section'             => $data['metadata']['section'] ?? NULL,
                    'row'                 => $data['metadata']['row'] ?? NULL,
                    'availability_status' => 'available',
                ];
            }
        }

        return $data;
    }

    /**
     * Parse Viagogo emails
     *
     * @param string $body Email body
     *
     * @return array Parsed data
     */
    private function parseViagogoEmail(string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        // Viagogo patterns
        $patterns = [
            'event_name' => '/(?:tickets for|regarding)\s+(.+?)(?:\sat\s|\son\s|$)/i',
            'venue'      => '/(?:at|venue)\s+(.+?)(?:\son\s|\n|$)/i',
            'alert_type' => '/(price alert|new tickets|recommendation)/i',
            'currency'   => '/([A-Z]{3})\s*([0-9,]+(?:\.[0-9]{2})?)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                if ($key === 'currency') {
                    $data['metadata']['currency'] = $matches[1];
                    $data['metadata']['price'] = $matches[2];
                } else {
                    $data['metadata'][$key] = trim($matches[1]);
                }
            }
        }

        // Extract events
        if (isset($data['metadata']['event_name'])) {
            $data['sports_events'][] = [
                'name'            => $data['metadata']['event_name'],
                'source_platform' => 'viagogo',
                'category'        => $this->detectSportCategory($data['metadata']['event_name']),
                'venue'           => $data['metadata']['venue'] ?? NULL,
            ];
        }

        return $data;
    }

    /**
     * Parse SeatGeek emails
     *
     * @param string $body Email body
     *
     * @return array Parsed data
     */
    private function parseSeatgeekEmail(string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        // SeatGeek patterns
        $patterns = [
            'deal_alert' => '/Deal Alert:\s*(.+?)(?:\n|$)/i',
            'event_name' => '/(?:for|tickets to)\s+(.+?)(?:\sat\s|\son\s|$)/i',
            'venue'      => '/(?:at)\s+(.+?)(?:\son\s|\n|$)/i',
            'deal_price' => '/\$([0-9,]+(?:\.[0-9]{2})?)\s*(?:\([0-9]+%\s*off\))?/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $data['metadata'][$key] = trim($matches[1]);
            }
        }

        // Extract events
        $eventName = $data['metadata']['event_name'] ?? $data['metadata']['deal_alert'] ?? NULL;
        if ($eventName) {
            $data['sports_events'][] = [
                'name'            => $eventName,
                'source_platform' => 'seatgeek',
                'category'        => $this->detectSportCategory($eventName),
                'venue'           => $data['metadata']['venue'] ?? NULL,
            ];
        }

        // Extract deals/tickets
        if (isset($data['metadata']['deal_price'])) {
            $data['tickets'][] = [
                'price'               => (float) str_replace(',', '', $data['metadata']['deal_price']),
                'source_platform'     => 'seatgeek',
                'is_deal'             => TRUE,
                'availability_status' => 'available',
            ];
        }

        return $data;
    }

    /**
     * Parse TickPick emails
     *
     * @param string $body Email body
     *
     * @return array Parsed data
     */
    private function parseTickpickEmail(string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        // TickPick patterns (emphasizes no fees)
        $patterns = [
            'event_name'  => '/(?:tickets for|for)\s+(.+?)(?:\sat\s|\son\s|$)/i',
            'venue'       => '/(?:at)\s+(.+?)(?:\son\s|\n|$)/i',
            'no_fees'     => '/(no fees?|fee-free)/i',
            'final_price' => '/\$([0-9,]+(?:\.[0-9]{2})?)\s*(?:final|total)?/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $data['metadata'][$key] = trim($matches[1]);
            }
        }

        // Extract events
        if (isset($data['metadata']['event_name'])) {
            $data['sports_events'][] = [
                'name'            => $data['metadata']['event_name'],
                'source_platform' => 'tickpick',
                'category'        => $this->detectSportCategory($data['metadata']['event_name']),
                'venue'           => $data['metadata']['venue'] ?? NULL,
                'no_fees'         => isset($data['metadata']['no_fees']) && ($data['metadata']['no_fees'] !== '' && $data['metadata']['no_fees'] !== '0'),
            ];
        }

        return $data;
    }

    /**
     * Parse generic sports event emails
     *
     * @param array  $headers Email headers
     * @param string $body    Email body
     *
     * @return array Parsed data
     */
    private function parseGenericEmail(array $headers, string $body): array
    {
        $data = [
            'sports_events' => [],
            'tickets'       => [],
        ];

        $subject = $headers['subject'] ?? '';

        // Generic patterns for sports events
        $eventPatterns = [
            '/(.+?)\s+(?:vs\.?|vs|v\.?|v)\s+(.+?)(?:\s+tickets|\s+game|\s+match|$)/i', // Team vs Team
            '/(.+?)\s+(?:tickets|game|match)\s+(?:at|@)\s+(.+?)(?:\n|$)/i', // Event at Venue
            '/(championship|playoff|cup|bowl|series|tournament)\s+(.+?)(?:\n|$)/i', // Tournaments
        ];

        foreach ($eventPatterns as $pattern) {
            if (preg_match($pattern, $subject . ' ' . $body, $matches)) {
                $eventName = trim($matches[0]);
                $category = $this->detectSportCategory($eventName);

                if ($category !== 'unknown') {
                    $data['sports_events'][] = [
                        'name'            => $eventName,
                        'source_platform' => 'generic',
                        'category'        => $category,
                    ];

                    break; // Only take the first match
                }
            }
        }

        // Generic price patterns
        if (preg_match_all('/\$([0-9,]+(?:\.[0-9]{2})?)/i', $body, $priceMatches)) {
            foreach (array_slice($priceMatches[1], 0, 5) as $price) { // Limit to 5 prices
                $cleanPrice = (float) str_replace(',', '', $price);
                if ($cleanPrice >= 10 && $cleanPrice <= 50000) { // Reasonable ticket price range
                    $data['tickets'][] = [
                        'price'               => $cleanPrice,
                        'source_platform'     => 'generic',
                        'availability_status' => 'available',
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Detect sport category from event name
     *
     * @param string $eventName Event name
     *
     * @return string Sport category
     */
    private function detectSportCategory(string $eventName): string
    {
        $eventLower = strtolower($eventName);

        $sportCategories = [
            'football'   => ['nfl', 'football', 'super bowl', 'cowboys', 'patriots', 'chiefs', 'rams', 'packers'],
            'basketball' => ['nba', 'basketball', 'lakers', 'warriors', 'celtics', 'bulls', 'heat', 'finals'],
            'baseball'   => ['mlb', 'baseball', 'yankees', 'dodgers', 'red sox', 'giants', 'mets', 'world series'],
            'hockey'     => ['nhl', 'hockey', 'rangers', 'bruins', 'penguins', 'blackhawks', 'stanley cup'],
            'soccer'     => ['mls', 'soccer', 'fc', 'united', 'city', 'galaxy', 'world cup', 'champions league'],
            'tennis'     => ['tennis', 'open', 'wimbledon', 'french open', 'australian open', 'us open'],
            'golf'       => ['golf', 'masters', 'pga', 'tournament', 'championship'],
            'racing'     => ['nascar', 'f1', 'formula', 'racing', 'indy 500', 'daytona'],
            'boxing'     => ['boxing', 'fight', 'heavyweight', 'championship'],
            'mma'        => ['ufc', 'mma', 'mixed martial arts'],
        ];

        foreach ($sportCategories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($eventLower, $keyword)) {
                    return $category;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Parse event date from various formats
     *
     * @param string|null $dateString Date string
     *
     * @return string|null Formatted date
     */
    private function parseEventDate(?string $dateString): ?string
    {
        if (!$dateString) {
            return NULL;
        }

        try {
            // Try various date formats
            $formats = [
                'M j, Y', // Jan 15, 2024
                'F j, Y', // January 15, 2024
                'Y-m-d',  // 2024-01-15
                'm/d/Y',  // 01/15/2024
                'd/m/Y',  // 15/01/2024
                'D, M j, Y', // Mon, Jan 15, 2024
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, trim($dateString));
                    if ($date && $date->year >= date('Y') && $date->year <= (date('Y') + 2)) {
                        return $date->toDateString();
                    }
                } catch (Exception) {
                    continue;
                }
            }

            // Try Carbon's flexible parsing
            $date = Carbon::parse($dateString);
            if ($date->year >= date('Y') && $date->year <= (date('Y') + 2)) {
                return $date->toDateString();
            }
        } catch (Exception $e) {
            Log::channel($this->config['logging']['channel'])
                ->debug('Failed to parse date', [
                    'date_string' => $dateString,
                    'error'       => $e->getMessage(),
                ]);
        }

        return NULL;
    }

    /**
     * Extract general metadata from email
     *
     * @param array  $headers Email headers
     * @param string $body    Email body
     *
     * @return array Metadata
     */
    private function extractMetadata(array $headers, string $body): array
    {
        $metadata = [
            'subject'       => $headers['subject'] ?? '',
            'from_email'    => $this->extractEmailAddress($headers['from'] ?? NULL),
            'message_id'    => $headers['message_id'] ?? '',
            'size'          => $headers['size'] ?? 0,
            'received_date' => $headers['date'] ?? '',
        ];

        // Extract URLs from email body
        if (preg_match_all('/(https?:\/\/[^\s\)]+)/i', $body, $urlMatches)) {
            $metadata['urls'] = array_slice($urlMatches[1], 0, 10); // Limit to 10 URLs
        }

        // Count key terms
        $bodyLower = strtolower($body);
        $keyTerms = ['ticket', 'event', 'game', 'match', 'stadium', 'arena', 'sports'];
        $metadata['keyword_frequency'] = [];

        foreach ($keyTerms as $term) {
            $metadata['keyword_frequency'][$term] = substr_count($bodyLower, $term);
        }

        return $metadata;
    }

    /**
     * Extract email address from header object
     *
     * @param object|null $headerObject Header object
     *
     * @return string|null Email address
     */
    private function extractEmailAddress(?object $headerObject): ?string
    {
        if (!$headerObject) {
            return NULL;
        }

        $mailbox = $headerObject->mailbox ?? '';
        $host = $headerObject->host ?? '';

        if ($mailbox && $host) {
            return $mailbox . '@' . $host;
        }

        return NULL;
    }

    /**
     * Validate and clean parsed data
     *
     * @param array $parsed Parsed data
     *
     * @return array Validated data
     */
    private function validateParsedData(array $parsed): array
    {
        // Remove empty sports events
        $parsed['sports_events'] = array_filter($parsed['sports_events'], fn (array $event): bool => !empty($event['name']) && strlen((string) $event['name']) > 3);

        // Validate ticket prices
        $parsed['tickets'] = array_filter($parsed['tickets'], fn (array $ticket): bool => isset($ticket['price'])
               && is_numeric($ticket['price'])
               && $ticket['price'] >= 1
               && $ticket['price'] <= 50000);

        // Ensure required fields
        $parsed['sports_events'] = array_values($parsed['sports_events']);
        $parsed['tickets'] = array_values($parsed['tickets']);

        return $parsed;
    }
}
