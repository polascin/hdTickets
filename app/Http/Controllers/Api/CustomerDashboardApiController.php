<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight API endpoints backing the enhanced customer dashboard (v2).
 * Provides stats, filtered tickets, and recommendations in the exact JSON
 * structure expected by public/js/customer-dashboard-enhanced-v2.js
 */
class CustomerDashboardApiController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Returns simplified stats object { available_tickets, new_today, monitored_events, active_alerts, price_alerts, triggered_today }
     */
    public function stats(Request $request): JsonResponse
    {
        if (! $user = Auth::user()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $cacheKey = 'api:dashboard:stats:user:' . $user->id;
        $stats = Cache::remember($cacheKey, 60, function () use ($user) {
            $available = ScrapedTicket::available()->count();
            $newToday = ScrapedTicket::whereDate('scraped_at', now()->toDateString())->count();
            $activeAlerts = TicketAlert::where('user_id', $user->id)->where('status', 'active')->count();
            $triggeredToday = TicketAlert::where('user_id', $user->id)
                ->whereHas('matches', fn ($q) => $q->whereDate('created_at', now()->toDateString()))
                ->count();
            $monitoredEvents = ScrapedTicket::available()
                ->selectRaw('COUNT(DISTINCT CONCAT(title, venue, event_date)) as c')
                ->value('c') ?? 0;

            return [
                'available_tickets' => (int) $available,
                'new_today'         => (int) $newToday,
                'monitored_events'  => (int) $monitoredEvents,
                'active_alerts'     => (int) $activeAlerts,
                'price_alerts'      => (int) $activeAlerts, // alias
                'triggered_today'   => (int) $triggeredToday,
            ];
        });

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'meta'    => [
                'refreshed_at' => now()->toISOString(),
                'cache_ttl'    => 60,
            ],
        ]);
    }

    /**
     * GET /api/dashboard/tickets
     * Supports filters: sport, platform, maxPrice, sort (field:direction)
     * Returns { tickets: [] }
     */
    public function tickets(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $query = ScrapedTicket::query()->available()->recent(24);

            if ($sport = $request->get('sport')) {
                $query->bySport($sport);
            }
            if ($platform = $request->get('platform')) {
                $query->byPlatform($platform);
            }
            if ($max = $request->get('maxPrice')) {
                $query->priceRange(null, (float) $max);
            }

            // Sorting
            $sort = $request->get('sort', 'created_at:desc');
            [$field, $dir] = array_pad(explode(':', $sort), 2, 'desc');
            $allowed = ['created_at', 'price', 'event_date', 'min_price', 'max_price'];
            if (! in_array($field, $allowed, true)) {
                $field = 'created_at';
            }
            $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
            // Map logical field 'price' to 'min_price'
            if ($field === 'price') {
                $field = 'min_price';
            }
            $query->orderBy($field, $dir);

            $tickets = $query->limit(50)->get()->map(fn ($t) => [
                'id'                => $t->id,
                'event_title'       => $t->title,
                'venue_name'        => $t->venue,
                'event_date'        => optional($t->event_date)->toISOString(),
                'sport_category'    => $t->sport,
                'platform_source'   => $t->platform_display_name ?? $t->platform,
                'price'             => (float) $t->min_price ?? 0,
                'available_quantity'=> (int) ($t->availability ?? 0),
                'demand_level'      => $t->popularity_score > 80 ? 'high' : ($t->popularity_score > 50 ? 'medium' : 'low'),
                'price_trend'       => 'stable', // placeholder
                'has_alert'         => false, // will be toggled client-side when user sets alert
            ]);

            return response()->json([
                'success' => true,
                'tickets' => $tickets,
                'count'   => $tickets->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Dashboard tickets API failure', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load tickets'], 500);
        }
    }

    /**
     * GET /api/dashboard/recommendations
     * Returns small set of recommended tickets with match score.
     */
    public function recommendations(): JsonResponse
    {
        if (! $user = Auth::user()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $cacheKey = 'api:dashboard:recs:user:' . $user->id;
        $recommendations = Cache::remember($cacheKey, 300, function () use ($user) {
            $prefs = $user->preferences ?? [];
            $favoriteTeams = $prefs['favorite_teams'] ?? [];

            $q = ScrapedTicket::available()->upcoming()->recent(48);
            if ($favoriteTeams) {
                $q->where(function ($qb) use ($favoriteTeams) {
                    foreach ($favoriteTeams as $team) {
                        $qb->orWhere('title', 'like', "%{$team}%")
                           ->orWhere('team', 'like', "%{$team}%");
                    }
                });
            }

            return $q->orderByDesc('popularity_score')
                ->limit(10)
                ->get()
                ->map(fn ($t) => [
                    'id'          => $t->id,
                    'event_title' => $t->title,
                    'event_date'  => optional($t->event_date)->toISOString(),
                    'price'       => (float) $t->min_price ?? 0,
                    'match_score' => (int) ($t->popularity_score ?? 50),
                ]);
        });

        return response()->json([
            'success'         => true,
            'recommendations' => $recommendations,
            'meta'            => [
                'refreshed_at' => now()->toISOString(),
                'cache_ttl'    => 300,
            ],
        ]);
    }
}