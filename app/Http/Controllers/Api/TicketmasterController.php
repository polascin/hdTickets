<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketmasterScraper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionClass;

use function count;

class TicketmasterController extends Controller
{
    /**
     * Search Ticketmaster events (without importing)
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
            $client = new TicketmasterClient([
                'enabled'  => TRUE,
                'base_url' => 'https://app.ticketmaster.com/discovery/v2/',
                'timeout'  => 30,
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
            'url' => 'required|url',
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
            $client = new TicketmasterClient([
                'enabled'  => TRUE,
                'base_url' => 'https://app.ticketmaster.com/discovery/v2/',
                'timeout'  => 30,
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
     * Import Ticketmaster events as tickets
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
            $scraper = new TicketmasterScraper();
            $result = $scraper->searchAndImportTickets($keyword, $location, $limit);

            if ($result['success']) {
                return response()->json($result, 200);
            }

            return response()->json($result, 500);
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
            $scraper = new TicketmasterScraper();
            $stats = $scraper->getScrapingStats();

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
     * Import specific events by URLs
     */
    public function importUrls(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'urls'   => 'required|array|min:1|max:10',
            'urls.*' => 'required|url',
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
            $scraper = new TicketmasterScraper();
            $client = new TicketmasterClient([
                'enabled'  => TRUE,
                'base_url' => 'https://app.ticketmaster.com/discovery/v2/',
                'timeout'  => 30,
            ]);

            $imported = 0;
            $errors = [];

            foreach ($urls as $url) {
                try {
                    // Get event details
                    $eventDetails = $client->scrapeEventDetails($url);

                    if (! empty($eventDetails)) {
                        // Use reflection to access private method
                        $reflection = new ReflectionClass($scraper);
                        $importMethod = $reflection->getMethod('importEventAsTicket');
                        $importMethod->setAccessible(TRUE);

                        if ($importMethod->invoke($scraper, $eventDetails)) {
                            $imported++;
                        }
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
}
