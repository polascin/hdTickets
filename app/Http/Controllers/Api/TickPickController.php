<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketApis\TickPickClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                'api_key' => config('services.tickpick.api_key'),
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
                'api_key' => config('services.tickpick.api_key'),
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

    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keyword'  => 'required|string|min:2|max:100',
            'location' => 'nullable|string|max:100',
            'limit'    => 'nullable|integer|min:1|max:50',
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
                'api_key' => config('services.tickpick.api_key'),
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

    private function importEventAsTicket(array $eventData): bool
    {
        try {
            $existingTicket = Ticket::where('platform', 'tickpick')
                ->where('external_id', $eventData['id'] ?? NULL)
                ->first();

            if ($existingTicket) {
                return FALSE;
            }

            $ticket = new Ticket([
                'platform'    => 'tickpick',
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
            Log::error('Failed to import tickpick event as ticket', [
                'event_data' => $eventData,
                'error'      => $e->getMessage(),
            ]);

            return FALSE;
        }
    }
}
