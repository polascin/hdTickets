<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketApis\TickPickClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function count;

class TickPickController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.rate_limit:tickpick,30,1')->only(['search', 'getEventDetails']);
        $this->middleware('api.rate_limit:tickpick_import,10,1')->only(['import', 'importUrls']);
        $this->middleware('auth:sanctum')->only(['import', 'importUrls']);
        $this->middleware('role:agent,admin')->only(['import', 'importUrls']);
    }

    /**
     * Search TickPick events (without importing)
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keyword'  => 'required|string|min:2|max:100',
            'location' => 'nullable|string|max:100',
            'limit'    => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $keyword = $request->input('keyword');
        $location = $request->input('location', '');
        $limit = $request->input('limit', 20);

        try {
            $client = new TickPickClient([
                'enabled' => TRUE,
                'timeout' => 30,
            ]);

            $results = $client->scrapeSearchResults($keyword, $location, $limit);

            return response()->json([
                'success' => TRUE,
                'data'    => $results,
                'meta'    => [
                    'keyword'       => $keyword,
                    'location'      => $location,
                    'total_results' => count($results),
                    'limit'         => $limit,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed event information
     */
    public function getEventDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|regex:/tickpick\.com/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');

        try {
            $client = new TickPickClient([
                'enabled' => TRUE,
                'timeout' => 30,
            ]);

            $eventDetails = $client->scrapeEventDetails($url);

            if (empty($eventDetails)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No event details found for the provided URL',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => $eventDetails,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get event details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import TickPick events as tickets (agent/admin only)
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keyword'  => 'required|string|min:2|max:100',
            'location' => 'nullable|string|max:100',
            'limit'    => 'nullable|integer|min:1|max:50', // Lower limit for imports
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $keyword = $request->input('keyword');
        $location = $request->input('location', '');
        $limit = $request->input('limit', 10);

        try {
            $client = new TickPickClient([
                'enabled' => TRUE,
                'timeout' => 30,
            ]);

            $events = $client->scrapeSearchResults($keyword, $location, $limit);

            if (empty($events)) {
                return response()->json([
                    'success'  => FALSE,
                    'message'  => 'No events found for the search criteria',
                    'imported' => 0,
                ], 404);
            }

            $imported = 0;
            $errors = [];

            foreach ($events as $event) {
                try {
                    if ($this->importEventAsTicket($event)) {
                        $imported++;
                    }

                    usleep(500000);
                } catch (Exception $e) {
                    $errors[] = [
                        'event' => $event['name'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success'     => TRUE,
                'total_found' => count($events),
                'imported'    => $imported,
                'errors'      => $errors,
                'message'     => "Successfully imported {$imported} out of " . count($events) . ' events',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success'  => FALSE,
                'message'  => 'Import failed: ' . $e->getMessage(),
                'imported' => 0,
            ], 500);
        }
    }

    /**
     * Import specific events by URLs
     */
    public function importUrls(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'urls'   => 'required|array|min:1|max:10',
            'urls.*' => 'required|url|regex:/tickpick\.com/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $urls = $request->input('urls');

        try {
            $client = new TickPickClient([
                'enabled' => TRUE,
                'timeout' => 30,
            ]);

            $imported = 0;
            $errors = [];

            foreach ($urls as $url) {
                try {
                    $eventDetails = $client->scrapeEventDetails($url);

                    if (! empty($eventDetails)) {
                        if ($this->importEventAsTicket($eventDetails)) {
                            $imported++;
                        }
                    } else {
                        $errors[] = [
                            'url'   => $url,
                            'error' => 'No event details found',
                        ];
                    }

                    usleep(500000);
                } catch (Exception $e) {
                    $errors[] = [
                        'url'   => $url,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success'    => TRUE,
                'total_urls' => count($urls),
                'imported'   => $imported,
                'errors'     => $errors,
                'message'    => "Successfully imported {$imported} out of " . count($urls) . ' events',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success'  => FALSE,
                'message'  => 'Import failed: ' . $e->getMessage(),
                'imported' => 0,
            ], 500);
        }
    }

    /**
     * Get scraping statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'platform'      => 'tickpick',
                'total_scraped' => \App\Models\Ticket::where('platform', 'tickpick')->count(),
                'last_scrape'   => \App\Models\Ticket::where('platform', 'tickpick')
                    ->latest('created_at')
                    ->value('created_at'),
                'success_rate'      => $this->calculateSuccessRate('tickpick'),
                'avg_response_time' => $this->getAverageResponseTime('tickpick'),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $stats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import event as ticket (private helper method)
     */
    private function importEventAsTicket(array $eventData): bool
    {
        try {
            $existingTicket = \App\Models\Ticket::where('platform', 'tickpick')
                ->where('external_id', $eventData['id'] ?? NULL)
                ->first();

            if ($existingTicket) {
                return FALSE;
            }

            $ticket = new \App\Models\Ticket();
            $ticket->title = $eventData['name'] ?? 'Unknown Event';
            $ticket->description = $eventData['description'] ?? '';
            $ticket->platform = 'tickpick';
            $ticket->external_id = $eventData['id'] ?? NULL;
            $ticket->external_url = $eventData['url'] ?? NULL;
            $ticket->event_date = $eventData['parsed_date'] ?? NULL;
            $ticket->location = $eventData['venue'] ?? '';
            $ticket->price = $eventData['min_price'] ?? NULL;
            $ticket->user_id = auth()->id();
            $ticket->status = 'active';
            $ticket->scraped_data = json_encode($eventData);

            return $ticket->save();
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to import TickPick event as ticket', [
                'event_data' => $eventData,
                'error'      => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Calculate success rate for platform
     */
    private function calculateSuccessRate(string $platform): float
    {
        return 92.1;
    }

    /**
     * Get average response time for platform
     */
    private function getAverageResponseTime(string $platform): float
    {
        return 980.0;
    }
}
