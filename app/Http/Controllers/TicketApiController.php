<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TicketApiManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function count;

class TicketApiController extends Controller
{
    protected $apiManager;

    public function __construct(TicketApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    /**
     * Show API integration dashboard
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $availablePlatforms = $this->apiManager->getAvailablePlatforms();

        return view('ticket-api.index', compact('availablePlatforms'));
    }

    /**
     * Search events via API
     */
    /**
     * Search
     */
    public function search(Request $request): Illuminate\Http\JsonResponse
    {
        $request->validate([
            'query'       => 'required|string|max:255',
            'city'        => 'nullable|string|max:100',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'platforms'   => 'nullable|array',
            'platforms.*' => 'string|in:' . implode(',', $this->apiManager->getAvailablePlatforms()),
        ]);

        $criteria = $this->buildSearchCriteria($request);
        $platforms = $request->input('platforms', []);

        try {
            $results = $this->apiManager->searchEvents($criteria, $platforms);

            return response()->json([
                'success' => TRUE,
                'data'    => $results,
                'summary' => $this->generateSearchSummary($results),
            ]);
        } catch (Exception $e) {
            Log::error('API search failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event details from specific platform
     */
    /**
     * Get  event
     */
    public function getEvent(Request $request, string $platform, string $eventId): Illuminate\Http\RedirectResponse
    {
        try {
            $event = $this->apiManager->getEvent($platform, $eventId);

            if (! $event) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Event not found',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => $event,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get event details', [
                'platform' => $platform,
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get event details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import events from APIs to database
     */
    /**
     * ImportEvents
     */
    public function importEvents(Request $request): Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'query'       => 'required|string|max:255',
            'platforms'   => 'required|array|min:1',
            'platforms.*' => 'string|in:' . implode(',', $this->apiManager->getAvailablePlatforms()),
            'save_to_db'  => 'boolean',
        ]);

        $criteria = $this->buildSearchCriteria($request);
        $platforms = $request->input('platforms');

        try {
            // Enable auto-save temporarily if requested
            $originalAutoSave = config('ticket_apis.auto_save', FALSE);
            if ($request->boolean('save_to_db')) {
                config(['ticket_apis.auto_save' => TRUE]);
            }

            $results = $this->apiManager->searchEvents($criteria, $platforms);

            // Restore original auto-save setting
            config(['ticket_apis.auto_save' => $originalAutoSave]);

            $importedCount = 0;
            foreach ($results as $platformResults) {
                $importedCount += count($platformResults);
            }

            return response()->json([
                'success' => TRUE,
                'message' => "Successfully imported {$importedCount} events",
                'data'    => $results,
                'summary' => $this->generateSearchSummary($results),
            ]);
        } catch (Exception $e) {
            Log::error('Event import failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test API connections
     */
    public function testConnections()
    {
        $platforms = $this->apiManager->getAvailablePlatforms();
        $results = [];

        foreach ($platforms as $platform) {
            try {
                // Try a simple search to test the connection
                $testResults = $this->apiManager->searchEvents(['q' => 'test', 'per_page' => 1], [$platform]);
                $results[$platform] = [
                    'status'  => 'connected',
                    'message' => 'API connection successful',
                ];
            } catch (Exception $e) {
                $results[$platform] = [
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $results,
        ]);
    }

    /**
     * Build search criteria from request
     */
    /**
     * BuildSearchCriteria
     */
    protected function buildSearchCriteria(Request $request): array
    {
        $criteria = [
            'q' => $request->input('query'),
        ];

        if ($request->filled('city')) {
            $criteria['venue.city'] = $request->input('city');
        }

        if ($request->filled('date_from')) {
            $criteria['datetime_utc.gte'] = $request->input('date_from') . 'T00:00:00Z';
        }

        if ($request->filled('date_to')) {
            $criteria['datetime_utc.lte'] = $request->input('date_to') . 'T23:59:59Z';
        }

        // Add Ticketmaster specific parameters
        if ($request->filled('query')) {
            $criteria['apikey'] = config('ticket_apis.ticketmaster.api_key');
            $criteria['keyword'] = $request->input('query');
        }

        return array_filter($criteria);
    }

    /**
     * Generate search summary
     */
    /**
     * GenerateSearchSummary
     */
    protected function generateSearchSummary(array $results): array
    {
        $summary = [
            'total_events' => 0,
            'platforms'    => [],
            'price_range'  => ['min' => NULL, 'max' => NULL],
        ];

        foreach ($results as $platform => $events) {
            $platformCount = count($events);
            $summary['total_events'] += $platformCount;
            $summary['platforms'][$platform] = $platformCount;

            // Calculate price range
            foreach ($events as $event) {
                if (isset($event['price_min']) && $event['price_min'] !== NULL) {
                    $summary['price_range']['min'] = $summary['price_range']['min'] === NULL
                        ? $event['price_min']
                        : min($summary['price_range']['min'], $event['price_min']);
                }

                if (isset($event['price_max']) && $event['price_max'] !== NULL) {
                    $summary['price_range']['max'] = $summary['price_range']['max'] === NULL
                        ? $event['price_max']
                        : max($summary['price_range']['max'], $event['price_max']);
                }
            }
        }

        return $summary;
    }
}
