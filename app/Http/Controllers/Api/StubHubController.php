<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketApis\StubHubClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function count;

class StubHubController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.rate_limit:stubhub,30,1')->only(['search', 'getEventDetails']);
        $this->middleware('api.rate_limit:stubhub_import,10,1')->only(['import', 'importUrls']);
        $this->middleware('auth:sanctum')->only(['import', 'importUrls']);
        $this->middleware('role:agent,admin')->only(['import', 'importUrls']);
    }

    /**
     * Search StubHub events (without importing)
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
            $client = new StubHubClient([
                'enabled'   => TRUE,
                'api_key'   => config('services.stubhub.api_key'),
                'app_token' => config('services.stubhub.app_token'),
                'timeout'   => 30,
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
            'url' => 'required|url|regex:/stubhub\.com/',
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
            $client = new StubHubClient([
                'enabled'   => TRUE,
                'api_key'   => config('services.stubhub.api_key'),
                'app_token' => config('services.stubhub.app_token'),
                'timeout'   => 30,
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
     * Import StubHub events as tickets (agent/admin only)
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
            $client = new StubHubClient([
                'enabled'   => TRUE,
                'api_key'   => config('services.stubhub.api_key'),
                'app_token' => config('services.stubhub.app_token'),
                'timeout'   => 30,
            ]);

            // Search for events
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
                    // Import event as ticket using the established pattern
                    if ($this->importEventAsTicket($event)) {
                        $imported++;
                    }
                    // Add delay between imports
                    usleep(500000); // 0.5 second delay
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
            'urls.*' => 'required|url|regex:/stubhub\.com/',
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
            $client = new StubHubClient([
                'enabled'   => TRUE,
                'api_key'   => config('services.stubhub.api_key'),
                'app_token' => config('services.stubhub.app_token'),
                'timeout'   => 30,
            ]);

            $imported = 0;
            $errors = [];

            foreach ($urls as $url) {
                try {
                    // Get event details
                    $eventDetails = $client->scrapeEventDetails($url);

                    if (!empty($eventDetails)) {
                        if ($this->importEventAsTicket($eventDetails)) {
                            $imported++;
                        }
                    } else {
                        $errors[] = [
                            'url'   => $url,
                            'error' => 'No event details found',
                        ];
                    }

                    // Add delay between requests
                    usleep(500000); // 0.5 second delay
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
            // Get statistics from database
            $stats = [
                'platform'      => 'stubhub',
                'total_scraped' => \App\Models\Ticket::where('platform', 'stubhub')->count(),
                'last_scrape'   => \App\Models\Ticket::where('platform', 'stubhub')
                    ->latest('created_at')
                    ->value('created_at'),
                'success_rate'      => $this->calculateSuccessRate('stubhub'),
                'avg_response_time' => $this->getAverageResponseTime('stubhub'),
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
            // Check if ticket already exists
            $existingTicket = \App\Models\Ticket::where('platform', 'stubhub')
                ->where('external_id', $eventData['id'] ?? NULL)
                ->first();

            if ($existingTicket) {
                return FALSE; // Already exists
            }

            // Create new ticket
            $ticket = new \App\Models\Ticket([
                'platform'    => 'stubhub',
                'external_id' => $eventData['id'] ?? NULL,
                'title'       => $eventData['name'] ?? 'Unknown Event',
                'price'       => $eventData['price'] ?? 0.00,
                'currency'    => $eventData['currency'] ?? 'USD',
                'venue'       => $eventData['venue'] ?? '',
                'event_date'  => $eventData['date'] ?? now(),
                'category'    => $eventData['category'] ?? 'General',
                'description' => $eventData['description'] ?? '',
                'url'         => $eventData['url'] ?? '',
                'status'      => 'available',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $ticket->save();

            return TRUE;
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to import StubHub event as ticket', [
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
        // This would be implemented based on your logging/metrics system
        return 85.5; // Placeholder
    }

    /**
     * Get average response time for platform
     */
    private function getAverageResponseTime(string $platform): float
    {
        // This would be implemented based on your logging/metrics system
        return 1250.0; // Placeholder in milliseconds
    }
}
