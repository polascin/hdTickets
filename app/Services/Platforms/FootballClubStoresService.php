<?php declare(strict_types=1);

namespace App\Services\Platforms;

use App\Models\ScrapedTicket;
use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;
use function in_array;
use function is_array;

class FootballClubStoresService extends BasePlatformService
{
    protected string $platformName = 'football_clubs';

    // Major European football club official ticket stores
    protected array $clubStores = [
        // Premier League
        'arsenal' => [
            'name'         => 'Arsenal FC',
            'url'          => 'https://www.arsenal.com/tickets',
            'api_endpoint' => 'https://www.arsenal.com/api/tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],
        'chelsea' => [
            'name'         => 'Chelsea FC',
            'url'          => 'https://www.chelseafc.com/en/tickets',
            'api_endpoint' => 'https://www.chelseafc.com/api/tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],
        'liverpool' => [
            'name'         => 'Liverpool FC',
            'url'          => 'https://www.liverpoolfc.com/tickets',
            'api_endpoint' => 'https://www.liverpoolfc.com/api/fixtures-and-tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],
        'manchester_united' => [
            'name'         => 'Manchester United',
            'url'          => 'https://www.manutd.com/en/tickets',
            'api_endpoint' => 'https://www.manutd.com/api/tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],
        'manchester_city' => [
            'name'         => 'Manchester City',
            'url'          => 'https://www.mancity.com/tickets',
            'api_endpoint' => 'https://www.mancity.com/api/tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],
        'tottenham' => [
            'name'         => 'Tottenham Hotspur',
            'url'          => 'https://www.tottenhamhotspur.com/tickets',
            'api_endpoint' => 'https://www.tottenhamhotspur.com/api/tickets',
            'league'       => 'Premier League',
            'country'      => 'England',
        ],

        // La Liga
        'real_madrid' => [
            'name'         => 'Real Madrid',
            'url'          => 'https://www.realmadrid.com/entradas',
            'api_endpoint' => 'https://www.realmadrid.com/api/entradas',
            'league'       => 'La Liga',
            'country'      => 'Spain',
        ],
        'barcelona' => [
            'name'         => 'FC Barcelona',
            'url'          => 'https://www.fcbarcelona.com/tickets',
            'api_endpoint' => 'https://www.fcbarcelona.com/api/tickets',
            'league'       => 'La Liga',
            'country'      => 'Spain',
        ],
        'atletico_madrid' => [
            'name'         => 'Atlético Madrid',
            'url'          => 'https://www.atleticodemadrid.com/entradas',
            'api_endpoint' => 'https://www.atleticodemadrid.com/api/entradas',
            'league'       => 'La Liga',
            'country'      => 'Spain',
        ],

        // Serie A
        'juventus' => [
            'name'         => 'Juventus',
            'url'          => 'https://www.juventus.com/it/biglietti',
            'api_endpoint' => 'https://www.juventus.com/api/biglietti',
            'league'       => 'Serie A',
            'country'      => 'Italy',
        ],
        'ac_milan' => [
            'name'         => 'AC Milan',
            'url'          => 'https://www.acmilan.com/it/biglietti',
            'api_endpoint' => 'https://www.acmilan.com/api/tickets',
            'league'       => 'Serie A',
            'country'      => 'Italy',
        ],
        'inter_milan' => [
            'name'         => 'Inter Milan',
            'url'          => 'https://www.inter.it/it/biglietti',
            'api_endpoint' => 'https://www.inter.it/api/biglietti',
            'league'       => 'Serie A',
            'country'      => 'Italy',
        ],

        // Bundesliga
        'bayern_munich' => [
            'name'         => 'Bayern Munich',
            'url'          => 'https://fcbayern.com/tickets',
            'api_endpoint' => 'https://fcbayern.com/api/tickets',
            'league'       => 'Bundesliga',
            'country'      => 'Germany',
        ],
        'borussia_dortmund' => [
            'name'         => 'Borussia Dortmund',
            'url'          => 'https://www.bvb.de/tickets',
            'api_endpoint' => 'https://www.bvb.de/api/tickets',
            'league'       => 'Bundesliga',
            'country'      => 'Germany',
        ],

        // Ligue 1
        'psg' => [
            'name'         => 'Paris Saint-Germain',
            'url'          => 'https://www.psg.fr/billetterie',
            'api_endpoint' => 'https://www.psg.fr/api/billetterie',
            'league'       => 'Ligue 1',
            'country'      => 'France',
        ],
    ];

    /**
     * Search for tickets across club stores
     */
    /**
     * SearchTickets
     */
    public function searchTickets(array $clubs, array $filters = []): array
    {
        $results = [];
        $errors = [];

        foreach ($clubs as $clubKey) {
            if (! isset($this->clubStores[$clubKey])) {
                $errors[] = "Unknown club: {$clubKey}";

                continue;
            }

            try {
                $clubData = $this->clubStores[$clubKey];
                $clubResults = $this->searchClubTickets($clubKey, $clubData, $filters);

                if ($clubResults['success']) {
                    $results[$clubKey] = $clubResults;
                } else {
                    $errors[] = "Failed to search {$clubData['name']}: " . $clubResults['error'];
                }
            } catch (Exception $e) {
                $errors[] = "Error searching {$clubKey}: " . $e->getMessage();
                Log::error('Football club search error', [
                    'club'  => $clubKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'             => ! empty($results),
            'clubs_searched'      => count($clubs),
            'successful_searches' => count($results),
            'results'             => $results,
            'errors'              => $errors,
        ];
    }

    /**
     * Import tickets from club stores
     */
    /**
     * ImportTickets
     */
    public function importTickets(array $clubs, array $filters = []): array
    {
        $searchResults = $this->searchTickets($clubs, $filters);

        if (! $searchResults['success']) {
            return [
                'success' => FALSE,
                'error'   => 'Search failed',
                'errors'  => $searchResults['errors'],
            ];
        }

        $imported = [];
        $errors = [];

        foreach ($searchResults['results'] as $clubKey => $clubResults) {
            foreach ($clubResults['fixtures'] as $fixture) {
                foreach ($fixture['ticket_categories'] as $ticketCategory) {
                    try {
                        $ticket = $this->createTicketRecord($ticketCategory, $fixture, $clubResults);
                        if ($ticket) {
                            $imported[] = $ticket;
                        }
                    } catch (Exception $e) {
                        $errors[] = "Failed to import ticket for {$fixture['opponent']}: " . $e->getMessage();
                    }
                }
            }
        }

        return [
            'success'          => count($imported) > 0,
            'imported_count'   => count($imported),
            'imported_tickets' => $imported,
            'clubs_processed'  => count($searchResults['results']),
            'errors'           => array_merge($errors, $searchResults['errors']),
        ];
    }

    /**
     * Get platform statistics
     */
    /**
     * Get  statistics
     */
    public function getStatistics(): array
    {
        $totalTickets = ScrapedTicket::where('platform', $this->platformName)->count();
        $availableTickets = ScrapedTicket::where('platform', $this->platformName)
            ->where('availability_status', 'available')->count();

        // Get club breakdown
        $clubStats = ScrapedTicket::where('platform', $this->platformName)
            ->selectRaw('JSON_EXTRACT(metadata, "$.club") as club, COUNT(*) as count')
            ->groupBy('club')
            ->get();

        // Get league breakdown
        $leagueStats = ScrapedTicket::where('platform', $this->platformName)
            ->selectRaw('JSON_EXTRACT(metadata, "$.league") as league, COUNT(*) as count')
            ->groupBy('league')
            ->get();

        return [
            'platform'          => $this->platformName,
            'total_tickets'     => $totalTickets,
            'available_tickets' => $availableTickets,
            'availability_rate' => $totalTickets > 0 ? round(($availableTickets / $totalTickets) * 100, 2) : 0,
            'supported_clubs'   => count($this->clubStores),
            'clubs'             => $clubStats->pluck('count', 'club')->toArray(),
            'leagues'           => $leagueStats->pluck('count', 'league')->toArray(),
            'last_updated'      => ScrapedTicket::where('platform', $this->platformName)
                ->max('updated_at'),
        ];
    }

    /**
     * Get supported clubs
     */
    /**
     * Get  supported clubs
     */
    public function getSupportedClubs(): array
    {
        return $this->clubStores;
    }

    /**
     * Search tickets for a specific club
     */
    /**
     * SearchClubTickets
     */
    private function searchClubTickets(string $clubKey, array $clubData, array $filters): array
    {
        $cacheKey = "football_club_tickets_{$clubKey}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($clubKey, $clubData, $filters) {
            try {
                // Try API endpoint first
                if ($this->hasApiAccess($clubData)) {
                    return $this->searchViaApi($clubKey, $clubData, $filters);
                }

                // Fallback to web scraping
                return $this->searchViaWebScraping($clubKey, $clubData, $filters);
            } catch (Exception $e) {
                Log::error('Club ticket search failed', [
                    'club'  => $clubKey,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'success'  => FALSE,
                    'error'    => $e->getMessage(),
                    'fixtures' => [],
                ];
            }
        });
    }

    /**
     * Search via club API
     */
    /**
     * SearchViaApi
     */
    private function searchViaApi(string $clubKey, array $clubData, array $filters): array
    {
        $params = $this->buildApiParams($filters, $clubData['country']);

        $response = Http::withHeaders([
            'User-Agent'      => $this->getRandomUserAgent(),
            'Accept'          => 'application/json',
            'Accept-Language' => $this->getLanguageForCountry($clubData['country']),
            'Referer'         => $clubData['url'],
        ])->timeout(30)->get($clubData['api_endpoint'], $params);

        if (! $response->successful()) {
            throw new Exception('API request failed: ' . $response->status());
        }

        $data = $response->json();

        return $this->parseApiResponse($data, $clubKey, $clubData);
    }

    /**
     * Search via web scraping
     */
    /**
     * SearchViaWebScraping
     */
    private function searchViaWebScraping(string $clubKey, array $clubData, array $filters): array
    {
        $response = Http::withHeaders([
            'User-Agent'      => $this->getRandomUserAgent(),
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => $this->getLanguageForCountry($clubData['country']),
            'Cache-Control'   => 'no-cache',
        ])->timeout(30)->get($clubData['url']);

        if (! $response->successful()) {
            throw new Exception('Web scraping failed: ' . $response->status());
        }

        return $this->parseWebPage($response->body(), $clubKey, $clubData);
    }

    /**
     * Parse API response
     */
    /**
     * ParseApiResponse
     */
    private function parseApiResponse(array $data, string $clubKey, array $clubData): array
    {
        $fixtures = [];

        // API response format varies by club, implement specific parsers
        switch ($clubKey) {
            case 'arsenal':
                $fixtures = $this->parseArsenalApi($data, $clubData);

                break;
            case 'chelsea':
                $fixtures = $this->parseChelseaApi($data, $clubData);

                break;
            case 'liverpool':
                $fixtures = $this->parseLiverpoolApi($data, $clubData);

                break;
            case 'real_madrid':
                $fixtures = $this->parseRealMadridApi($data, $clubData);

                break;
            case 'barcelona':
                $fixtures = $this->parseBarcelonaApi($data, $clubData);

                break;
            default:
                $fixtures = $this->parseGenericApi($data, $clubData);

                break;
        }

        return [
            'success'        => TRUE,
            'club'           => $clubData['name'],
            'club_key'       => $clubKey,
            'league'         => $clubData['league'],
            'country'        => $clubData['country'],
            'total_fixtures' => count($fixtures),
            'fixtures'       => $fixtures,
            'extracted_at'   => now()->toISOString(),
        ];
    }

    /**
     * Parse web page content
     */
    /**
     * ParseWebPage
     */
    private function parseWebPage(string $html, string $clubKey, array $clubData): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $fixtures = [];

        // Common selectors for fixture listings
        $selectors = $this->getSelectorsForClub($clubKey);

        $fixtureNodes = $xpath->query($selectors['fixture_container']);

        foreach ($fixtureNodes as $node) {
            try {
                $fixture = $this->extractFixtureFromNode($node, $xpath, $selectors, $clubData);
                if ($fixture) {
                    $fixtures[] = $fixture;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse fixture', [
                    'club'  => $clubKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'           => TRUE,
            'club'              => $clubData['name'],
            'club_key'          => $clubKey,
            'league'            => $clubData['league'],
            'country'           => $clubData['country'],
            'total_fixtures'    => count($fixtures),
            'fixtures'          => $fixtures,
            'extraction_method' => 'web_scraping',
            'extracted_at'      => now()->toISOString(),
        ];
    }

    /**
     * Parse Arsenal API response
     */
    /**
     * ParseArsenalApi
     */
    private function parseArsenalApi(array $data, array $clubData): array
    {
        $fixtures = [];

        if (isset($data['fixtures']) && is_array($data['fixtures'])) {
            foreach ($data['fixtures'] as $fixtureData) {
                $fixture = [
                    'id'                => $fixtureData['id'] ?? NULL,
                    'opponent'          => $this->extractOpponent($fixtureData['title'] ?? '', 'Arsenal'),
                    'competition'       => $fixtureData['competition'] ?? NULL,
                    'venue'             => $fixtureData['venue'] ?? 'Emirates Stadium',
                    'date'              => $this->parseEventDate($fixtureData['date'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($fixtureData['tickets'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date'] && ! empty($fixture['ticket_categories'])) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Parse Chelsea API response
     */
    /**
     * ParseChelseaApi
     */
    private function parseChelseaApi(array $data, array $clubData): array
    {
        $fixtures = [];

        if (isset($data['matches']) && is_array($data['matches'])) {
            foreach ($data['matches'] as $matchData) {
                $fixture = [
                    'id'                => $matchData['matchId'] ?? NULL,
                    'opponent'          => $this->extractOpponent($matchData['opponent'] ?? '', 'Chelsea'),
                    'competition'       => $matchData['competition'] ?? NULL,
                    'venue'             => $matchData['venue'] ?? 'Stamford Bridge',
                    'date'              => $this->parseEventDate($matchData['kickOff'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($matchData['ticketing'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date'] && ! empty($fixture['ticket_categories'])) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Parse Liverpool API response
     */
    /**
     * ParseLiverpoolApi
     */
    private function parseLiverpoolApi(array $data, array $clubData): array
    {
        $fixtures = [];

        if (isset($data['data']['fixtures']) && is_array($data['data']['fixtures'])) {
            foreach ($data['data']['fixtures'] as $fixtureData) {
                $fixture = [
                    'id'                => $fixtureData['fixtureId'] ?? NULL,
                    'opponent'          => $this->extractOpponent($fixtureData['opponent'] ?? '', 'Liverpool'),
                    'competition'       => $fixtureData['competition'] ?? NULL,
                    'venue'             => $fixtureData['venue'] ?? 'Anfield',
                    'date'              => $this->parseEventDate($fixtureData['matchDate'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($fixtureData['ticketInfo'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date'] && ! empty($fixture['ticket_categories'])) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Parse Real Madrid API response
     */
    /**
     * ParseRealMadridApi
     */
    private function parseRealMadridApi(array $data, array $clubData): array
    {
        $fixtures = [];

        if (isset($data['partidos']) && is_array($data['partidos'])) {
            foreach ($data['partidos'] as $partidoData) {
                $fixture = [
                    'id'                => $partidoData['id'] ?? NULL,
                    'opponent'          => $this->extractOpponent($partidoData['rival'] ?? '', 'Real Madrid'),
                    'competition'       => $partidoData['competicion'] ?? NULL,
                    'venue'             => $partidoData['estadio'] ?? 'Santiago Bernabéu',
                    'date'              => $this->parseEventDate($partidoData['fecha'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($partidoData['entradas'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date'] && ! empty($fixture['ticket_categories'])) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Parse Barcelona API response
     */
    /**
     * ParseBarcelonaApi
     */
    private function parseBarcelonaApi(array $data, array $clubData): array
    {
        $fixtures = [];

        if (isset($data['matches']) && is_array($data['matches'])) {
            foreach ($data['matches'] as $matchData) {
                $fixture = [
                    'id'                => $matchData['id'] ?? NULL,
                    'opponent'          => $this->extractOpponent($matchData['opponent'] ?? '', 'Barcelona'),
                    'competition'       => $matchData['competition'] ?? NULL,
                    'venue'             => $matchData['venue'] ?? 'Camp Nou',
                    'date'              => $this->parseEventDate($matchData['date'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($matchData['tickets'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date'] && ! empty($fixture['ticket_categories'])) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Parse generic API response
     */
    /**
     * ParseGenericApi
     */
    private function parseGenericApi(array $data, array $clubData): array
    {
        $fixtures = [];

        // Try common field names
        $possibleFixtureKeys = ['fixtures', 'matches', 'games', 'events', 'partidos'];
        $fixturesData = NULL;

        foreach ($possibleFixtureKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $fixturesData = $data[$key];

                break;
            }
        }

        if ($fixturesData) {
            foreach ($fixturesData as $item) {
                $fixture = [
                    'id'                => $item['id'] ?? $item['matchId'] ?? NULL,
                    'opponent'          => $this->extractOpponentGeneric($item, $clubData['name']),
                    'competition'       => $item['competition'] ?? $item['league'] ?? NULL,
                    'venue'             => $item['venue'] ?? $item['stadium'] ?? NULL,
                    'date'              => $this->parseEventDate($item['date'] ?? $item['kickoff'] ?? ''),
                    'ticket_categories' => $this->parseTicketCategories($item['tickets'] ?? $item['ticketing'] ?? []),
                    'club_data'         => $clubData,
                ];

                if ($fixture['date']) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }

    /**
     * Create ticket record in database
     */
    /**
     * CreateTicketRecord
     */
    private function createTicketRecord(array $ticketData, array $fixture, array $clubData): ?ScrapedTicket
    {
        try {
            return ScrapedTicket::updateOrCreate([
                'platform'    => $this->platformName,
                'event_title' => $clubData['club'] . ' vs ' . $fixture['opponent'],
                'section'     => $ticketData['category'],
                'price'       => $ticketData['price'],
            ], [
                'venue'               => $fixture['venue'],
                'event_date'          => $fixture['date'],
                'total_price'         => $ticketData['price'],
                'currency'            => $this->getCurrencyForCountry($clubData['country']),
                'availability_status' => $ticketData['available'] ? 'available' : 'sold_out',
                'last_seen'           => now(),
                'source_url'          => $clubData['url'] ?? NULL,
                'description'         => $fixture['competition'],
                'metadata'            => json_encode([
                    'club'                    => $clubData['club'],
                    'club_key'                => $clubData['club_key'],
                    'league'                  => $clubData['league'],
                    'country'                 => $clubData['country'],
                    'opponent'                => $fixture['opponent'],
                    'competition'             => $fixture['competition'],
                    'fixture_id'              => $fixture['id'],
                    'ticket_category_details' => $ticketData,
                    'extraction_method'       => 'club_api',
                    'last_updated'            => now()->toISOString(),
                ]),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create football club ticket record', [
                'error'       => $e->getMessage(),
                'fixture'     => $fixture,
                'ticket_data' => $ticketData,
            ]);

            return NULL;
        }
    }

    /**
     * Helper methods
     */
    /**
     * Check if has  api access
     */
    private function hasApiAccess(array $clubData): bool
    {
        return isset($clubData['api_endpoint']) && ! empty($clubData['api_endpoint']);
    }

    /**
     * BuildApiParams
     */
    private function buildApiParams(array $filters, string $country): array
    {
        $params = ['limit' => 50];

        if (isset($filters['date_from'])) {
            $params['from_date'] = Carbon::parse($filters['date_from'])->format('Y-m-d');
        }

        if (isset($filters['date_to'])) {
            $params['to_date'] = Carbon::parse($filters['date_to'])->format('Y-m-d');
        }

        if (isset($filters['competition'])) {
            $params['competition'] = $filters['competition'];
        }

        return $params;
    }

    /**
     * Get  language for country
     */
    private function getLanguageForCountry(string $country): string
    {
        return match ($country) {
            'Spain'   => 'es-ES,es;q=0.9,en;q=0.8',
            'Italy'   => 'it-IT,it;q=0.9,en;q=0.8',
            'Germany' => 'de-DE,de;q=0.9,en;q=0.8',
            'France'  => 'fr-FR,fr;q=0.9,en;q=0.8',
            default   => 'en-GB,en;q=0.9',
        };
    }

    /**
     * Get  currency for country
     */
    private function getCurrencyForCountry(string $country): string
    {
        return match ($country) {
            'England' => 'GBP',
            'Spain'   => 'EUR',
            'Italy'   => 'EUR',
            'Germany' => 'EUR',
            'France'  => 'EUR',
            default   => 'EUR',
        };
    }

    /**
     * ExtractOpponent
     */
    private function extractOpponent(string $title, string $homeTeam): string
    {
        $title = str_replace($homeTeam, '', $title);
        $title = trim(str_replace(['vs', 'v', '-', 'vs.'], '', $title));

        return $title ?: 'TBD';
    }

    /**
     * ExtractOpponentGeneric
     */
    private function extractOpponentGeneric(array $data, string $homeTeam): string
    {
        $possibleFields = ['opponent', 'away_team', 'visitor', 'rival'];

        foreach ($possibleFields as $field) {
            if (isset($data[$field]) && ! empty($data[$field])) {
                return $data[$field];
            }
        }

        // Try to extract from title
        if (isset($data['title'])) {
            return $this->extractOpponent($data['title'], $homeTeam);
        }

        return 'TBD';
    }

    /**
     * ParseTicketCategories
     */
    private function parseTicketCategories(array $tickets): array
    {
        $categories = [];

        foreach ($tickets as $ticket) {
            $category = [
                'category'     => $ticket['category'] ?? $ticket['name'] ?? 'General',
                'price'        => $this->extractPriceFromTicket($ticket),
                'available'    => $this->isTicketAvailable($ticket),
                'restrictions' => $ticket['restrictions'] ?? [],
                'seat_type'    => $this->extractSeatType($ticket),
            ];

            if ($category['price'] > 0) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /**
     * ExtractPriceFromTicket
     */
    private function extractPriceFromTicket(array $ticket): float
    {
        $priceFields = ['price', 'cost', 'amount', 'precio'];

        foreach ($priceFields as $field) {
            if (isset($ticket[$field])) {
                $price = is_numeric($ticket[$field]) ? (float) $ticket[$field] : $this->extractPrice($ticket[$field]);
                if ($price > 0) {
                    return $price;
                }
            }
        }

        return 0;
    }

    /**
     * Check if  ticket available
     */
    private function isTicketAvailable(array $ticket): bool
    {
        if (isset($ticket['available'])) {
            return (bool) $ticket['available'];
        }

        if (isset($ticket['status'])) {
            return ! in_array(strtolower($ticket['status']), ['sold_out', 'unavailable', 'agotado'], TRUE);
        }

        return TRUE; // Default to available
    }

    /**
     * ExtractSeatType
     */
    private function extractSeatType(array $ticket): string
    {
        $category = strtolower($ticket['category'] ?? $ticket['name'] ?? '');

        if (str_contains($category, 'vip') || str_contains($category, 'premium')) {
            return 'premium';
        }

        if (str_contains($category, 'hospitality') || str_contains($category, 'corporate')) {
            return 'hospitality';
        }

        if (str_contains($category, 'season') || str_contains($category, 'member')) {
            return 'season_ticket';
        }

        return 'standard';
    }

    /**
     * ParseEventDate
     */
    private function parseEventDate(string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return NULL;
        }

        try {
            return Carbon::parse($dateStr)->toDateTimeString();
        } catch (Exception $e) {
            Log::warning('Could not parse football club date', ['date_string' => $dateStr]);

            return NULL;
        }
    }

    /**
     * ExtractPrice
     */
    private function extractPrice(string $priceText): float
    {
        $priceText = preg_replace('/[^\d.,]/', '', $priceText);
        $priceText = str_replace(',', '', $priceText);

        return is_numeric($priceText) ? (float) $priceText : 0;
    }

    /**
     * Get  selectors for club
     */
    private function getSelectorsForClub(string $clubKey): array
    {
        // Club-specific CSS selectors for web scraping
        $selectors = [
            'arsenal' => [
                'fixture_container' => '//div[contains(@class, "fixture-item")]',
                'title'             => './/h3[@class="fixture-title"]',
                'date'              => './/time[@class="fixture-date"]',
                'tickets'           => './/div[@class="ticket-info"]',
            ],
            'chelsea' => [
                'fixture_container' => '//div[contains(@class, "match-item")]',
                'title'             => './/h2[@class="match-title"]',
                'date'              => './/span[@class="match-date"]',
                'tickets'           => './/div[@class="ticket-availability"]',
            ],
            // Add more club-specific selectors as needed
        ];

        // Default selectors
        return $selectors[$clubKey] ?? [
            'fixture_container' => '//div[contains(@class, "fixture") or contains(@class, "match")]',
            'title'             => './/h2 | .//h3',
            'date'              => './/time | .//span[contains(@class, "date")]',
            'tickets'           => './/div[contains(@class, "ticket")]',
        ];
    }

    /**
     * ExtractFixtureFromNode
     */
    private function extractFixtureFromNode(DOMNode $node, DOMXPath $xpath, array $selectors, array $clubData): ?array
    {
        // Extract fixture title
        $titleNode = $xpath->query($selectors['title'], $node)->item(0);
        if (! $titleNode) {
            return NULL;
        }

        $title = trim($titleNode->textContent);
        $opponent = $this->extractOpponent($title, $clubData['name']);

        // Extract date
        $dateNode = $xpath->query($selectors['date'], $node)->item(0);
        $date = NULL;
        if ($dateNode) {
            $dateStr = $dateNode->getAttribute('datetime') ?: $dateNode->textContent;
            $date = $this->parseEventDate($dateStr);
        }

        // Extract ticket info
        $ticketNodes = $xpath->query($selectors['tickets'], $node);
        $ticketCategories = [];

        foreach ($ticketNodes as $ticketNode) {
            // Extract ticket category info from HTML
            $category = $this->extractTicketCategoryFromHtml($ticketNode, $xpath);
            if ($category) {
                $ticketCategories[] = $category;
            }
        }

        return [
            'id'                => md5($title . $date),
            'opponent'          => $opponent,
            'competition'       => NULL, // May need additional extraction
            'venue'             => NULL, // May need additional extraction
            'date'              => $date,
            'ticket_categories' => $ticketCategories,
            'club_data'         => $clubData,
        ];
    }

    /**
     * ExtractTicketCategoryFromHtml
     */
    private function extractTicketCategoryFromHtml(DOMNode $ticketNode, DOMXPath $xpath): ?array
    {
        // Extract category name
        $categoryNode = $xpath->query('.//span[@class="category"] | .//h4', $ticketNode)->item(0);
        $category = $categoryNode ? trim($categoryNode->textContent) : 'General';

        // Extract price
        $priceNode = $xpath->query('.//span[@class="price"] | .//*[contains(@class, "price")]', $ticketNode)->item(0);
        if (! $priceNode) {
            return NULL;
        }

        $priceText = trim($priceNode->textContent);
        $price = $this->extractPrice($priceText);
        if ($price <= 0) {
            return NULL;
        }

        // Extract availability
        $availNode = $xpath->query('.//span[@class="availability"]', $ticketNode)->item(0);
        $available = TRUE;
        if ($availNode) {
            $availText = strtolower(trim($availNode->textContent));
            $available = ! str_contains($availText, 'sold out') && ! str_contains($availText, 'unavailable');
        }

        return [
            'category'     => $category,
            'price'        => $price,
            'available'    => $available,
            'restrictions' => [],
            'seat_type'    => $this->extractSeatType(['category' => $category]),
        ];
    }
}
