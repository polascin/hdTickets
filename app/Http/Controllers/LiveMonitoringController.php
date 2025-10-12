<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Services\LiveMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LiveMonitoringController extends Controller
{
    public function __construct(
        private LiveMonitoringService $monitoringService
    ) {
    }

    /**
     * Show the live monitoring dashboard
     */
    public function index(): View
    {
        $stats = Cache::remember('live_monitoring_stats', 60, function () {
            return [
                'total_matches'       => ScrapedTicket::active()->count(),
                'high_demand_matches' => ScrapedTicket::highDemand()->count(),
                'platforms_monitored' => ScrapedTicket::distinct('platform')->count(),
                'price_drops_today'   => ScrapedTicket::where('price_changed_at', '>=', today())->count(),
                'new_matches_today'   => ScrapedTicket::whereDate('created_at', today())->count(),
            ];
        });

        $platforms = $this->monitoringService->getMonitoredPlatforms();
        $leagues = $this->monitoringService->getSupportedLeagues();

        return view('live-monitoring.index', compact('stats', 'platforms', 'leagues'));
    }

    /**
     * Get live match data for the dashboard
     */
    public function getLiveData(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string|in:upcoming,high_demand,recent,all',
            'league'   => 'nullable|string',
            'platform' => 'nullable|string',
            'limit'    => 'nullable|integer|min:1|max:50',
        ]);

        $category = $request->input('category', 'upcoming');
        $limit = $request->input('limit', 20);

        $data = match ($category) {
            'upcoming'    => $this->getUpcomingMatches($request, $limit),
            'high_demand' => $this->getHighDemandMatches($request, $limit),
            'recent'      => $this->getRecentMatches($request, $limit),
            'all'         => $this->getAllMatches($request, $limit),
            default       => $this->getUpcomingMatches($request, $limit),
        };

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
            'meta'    => [
                'category'    => $category,
                'total_count' => $data['pagination']['total'] ?? count($data['matches']),
                'updated_at'  => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Get real-time availability updates
     */
    public function getAvailabilityUpdates(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_ids'   => 'required|array',
            'ticket_ids.*' => 'integer|exists:scraped_tickets,id',
        ]);

        $ticketIds = $request->input('ticket_ids');
        $updates = [];

        foreach ($ticketIds as $ticketId) {
            $ticket = ScrapedTicket::find($ticketId);
            if ($ticket) {
                $updates[$ticketId] = [
                    'id'               => $ticket->id,
                    'title'            => $ticket->title,
                    'availability'     => $ticket->availability_status,
                    'price'            => $ticket->price,
                    'price_changed'    => $ticket->price_changed_at ? $ticket->price_changed_at->isAfter(now()->subMinutes(5)) : FALSE,
                    'last_updated'     => $ticket->updated_at->toISOString(),
                    'platform'         => $ticket->platform,
                    'status_indicator' => $this->getStatusIndicator($ticket),
                ];
            }
        }

        return response()->json([
            'success'   => TRUE,
            'updates'   => $updates,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get platform status information
     */
    public function getPlatformStatus(): JsonResponse
    {
        $platforms = $this->monitoringService->getPlatformStatus();

        return response()->json([
            'success'    => TRUE,
            'platforms'  => $platforms,
            'last_check' => now()->toISOString(),
        ]);
    }

    /**
     * Get user's monitoring preferences
     */
    public function getMonitoringPreferences(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['success' => FALSE, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $preferences = $user->monitoring_preferences ?? [
            'auto_refresh'              => TRUE,
            'refresh_interval'          => 30,
            'show_price_changes'        => TRUE,
            'show_availability_changes' => TRUE,
            'preferred_leagues'         => [],
            'preferred_platforms'       => [],
            'hide_sold_out'             => FALSE,
        ];

        return response()->json([
            'success'     => TRUE,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update user's monitoring preferences
     */
    public function updateMonitoringPreferences(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['success' => FALSE, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'auto_refresh'              => 'boolean',
            'refresh_interval'          => 'integer|min:10|max:300',
            'show_price_changes'        => 'boolean',
            'show_availability_changes' => 'boolean',
            'preferred_leagues'         => 'array',
            'preferred_platforms'       => 'array',
            'hide_sold_out'             => 'boolean',
        ]);

        $user = Auth::user();
        $currentPreferences = $user->monitoring_preferences ?? [];

        $newPreferences = array_merge($currentPreferences, $request->only([
            'auto_refresh',
            'refresh_interval',
            'show_price_changes',
            'show_availability_changes',
            'preferred_leagues',
            'preferred_platforms',
            'hide_sold_out',
        ]));

        $user->update(['monitoring_preferences' => $newPreferences]);

        return response()->json([
            'success'     => TRUE,
            'preferences' => $newPreferences,
        ]);
    }

    /**
     * Get trending matches (most viewed/searched)
     */
    public function getTrendingMatches(): JsonResponse
    {
        $trending = Cache::remember('trending_matches', 300, function () {
            return ScrapedTicket::select([
                'id', 'title', 'venue', 'event_date', 'price', 'platform',
                'availability_status', 'views_count', 'searches_count',
            ])
            ->where('event_date', '>=', now())
            ->where('is_available', TRUE)
            ->orderByDesc('views_count')
            ->orderByDesc('searches_count')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id'          => $ticket->id,
                    'title'       => $ticket->title,
                    'venue'       => $ticket->venue,
                    'event_date'  => $ticket->event_date,
                    'price'       => $ticket->price,
                    'platform'    => $ticket->platform,
                    'status'      => $this->getStatusIndicator($ticket),
                    'trend_score' => $ticket->views_count + ($ticket->searches_count * 2),
                ];
            });
        });

        return response()->json([
            'success'  => TRUE,
            'trending' => $trending,
        ]);
    }

    private function getUpcomingMatches(Request $request, int $limit): array
    {
        $query = ScrapedTicket::query()
            ->where('event_date', '>=', now())
            ->where('is_available', TRUE)
            ->orderBy('event_date');

        $this->applyFilters($query, $request);

        $matches = $query->paginate($limit);

        return [
            'matches'    => $matches->items()->map(fn ($ticket) => $this->formatTicketForDisplay($ticket)),
            'pagination' => [
                'current_page' => $matches->currentPage(),
                'last_page'    => $matches->lastPage(),
                'per_page'     => $matches->perPage(),
                'total'        => $matches->total(),
            ],
        ];
    }

    private function getHighDemandMatches(Request $request, int $limit): array
    {
        $query = ScrapedTicket::query()
            ->where('event_date', '>=', now())
            ->where(function ($q) {
                $q->where('availability_status', 'limited')
                  ->orWhere('availability_status', 'sold_out')
                  ->orWhere('is_high_demand', TRUE);
            })
            ->orderByDesc('views_count')
            ->orderBy('event_date');

        $this->applyFilters($query, $request);

        $matches = $query->paginate($limit);

        return [
            'matches'    => $matches->items()->map(fn ($ticket) => $this->formatTicketForDisplay($ticket)),
            'pagination' => [
                'current_page' => $matches->currentPage(),
                'last_page'    => $matches->lastPage(),
                'per_page'     => $matches->perPage(),
                'total'        => $matches->total(),
            ],
        ];
    }

    private function getRecentMatches(Request $request, int $limit): array
    {
        $query = ScrapedTicket::query()
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('created_at');

        $this->applyFilters($query, $request);

        $matches = $query->limit($limit)->get();

        return [
            'matches' => $matches->map(fn ($ticket) => $this->formatTicketForDisplay($ticket))->toArray(),
        ];
    }

    private function getAllMatches(Request $request, int $limit): array
    {
        $query = ScrapedTicket::query()
            ->orderByDesc('updated_at')
            ->orderBy('event_date');

        $this->applyFilters($query, $request);

        $matches = $query->paginate($limit);

        return [
            'matches'    => $matches->items()->map(fn ($ticket) => $this->formatTicketForDisplay($ticket)),
            'pagination' => [
                'current_page' => $matches->currentPage(),
                'last_page'    => $matches->lastPage(),
                'per_page'     => $matches->perPage(),
                'total'        => $matches->total(),
            ],
        ];
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('league')) {
            $query->where('sport_type', $request->input('league'));
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }
    }

    private function formatTicketForDisplay($ticket): array
    {
        return [
            'id'                     => $ticket->id,
            'title'                  => $ticket->title,
            'venue'                  => $ticket->venue,
            'location'               => $ticket->location,
            'event_date'             => $ticket->event_date,
            'price'                  => $ticket->price,
            'currency'               => $ticket->currency ?? 'GBP',
            'platform'               => $ticket->platform,
            'availability'           => $ticket->availability_status,
            'status_indicator'       => $this->getStatusIndicator($ticket),
            'price_changed_recently' => $ticket->price_changed_at && $ticket->price_changed_at->isAfter(now()->subHours(1)),
            'is_new'                 => $ticket->created_at->isAfter(now()->subHours(6)),
            'view_count'             => $ticket->views_count ?? 0,
            'url'                    => route('tickets.show', $ticket->id),
            'external_url'           => $ticket->url,
        ];
    }

    private function getStatusIndicator($ticket): array
    {
        if ($ticket->availability_status === 'sold_out') {
            return ['type' => 'danger', 'text' => 'Sold Out', 'icon' => 'x-circle'];
        }

        if ($ticket->availability_status === 'limited') {
            return ['type' => 'warning', 'text' => 'Limited', 'icon' => 'exclamation-triangle'];
        }

        if ($ticket->is_high_demand) {
            return ['type' => 'info', 'text' => 'High Demand', 'icon' => 'fire'];
        }

        if ($ticket->is_available) {
            return ['type' => 'success', 'text' => 'Available', 'icon' => 'check-circle'];
        }

        return ['type' => 'secondary', 'text' => 'Unknown', 'icon' => 'question-mark-circle'];
    }
}
