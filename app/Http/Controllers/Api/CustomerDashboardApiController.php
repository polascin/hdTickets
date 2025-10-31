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
use Throwable;

use function in_array;

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
        if (!$user = Auth::user()) {
            return response()->json(['success' => FALSE, 'message' => 'Unauthenticated'], 401);
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

        $response = response()->json([
            'success' => TRUE,
            'stats'   => $stats,
            'meta'    => [
                'refreshed_at' => now()->toISOString(),
                'cache_ttl'    => 60,
                'version'      => 'v2',
            ],
        ]);

        return $this->withCachingHeaders($response, 55);
    }

    /**
     * GET /api/dashboard/tickets
     * Supports filters: sport, platform, maxPrice, sort (field:direction)
     * Returns { tickets: [] }
     */
    public function tickets(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['success' => FALSE, 'message' => 'Unauthenticated'], 401);
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
                $query->priceRange(NULL, (float) $max);
            }

            // Basic demand metrics pre-aggregation (counts per sport and platform for current filter set)
            $demandMetrics = Cache::remember('api:dashboard:demand:' . md5(json_encode($request->all())), 60, function () use ($query) {
                $base = clone $query;

                return [
                    'by_sport'    => (clone $base)->selectRaw('sport, COUNT(*) as c')->groupBy('sport')->orderByDesc('c')->limit(10)->pluck('c', 'sport'),
                    'by_platform' => (clone $base)->selectRaw('platform, COUNT(*) as c')->groupBy('platform')->orderByDesc('c')->limit(10)->pluck('c', 'platform'),
                ];
            });

            // Sorting
            $sort = $request->get('sort', 'created_at:desc');
            [$field, $dir] = array_pad(explode(':', $sort), 2, 'desc');
            $allowed = ['created_at', 'price', 'event_date', 'min_price', 'max_price'];
            if (!in_array($field, $allowed, TRUE)) {
                $field = 'created_at';
            }
            $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
            // Map logical field 'price' to 'min_price'
            if ($field === 'price') {
                $field = 'min_price';
            }
            $query->orderBy($field, $dir);
            // Pagination (default 25)
            $perPage = (int) min(max($request->get('per_page', 25), 1), 100);
            $page = (int) max($request->get('page', 1), 1);
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            // Preload user's active alert keywords for approximate matching
            $alertKeywords = $this->getUserAlertKeywords(Auth::id());

            $tickets = collect($paginator->items())->map(function ($t) use ($alertKeywords) {
                $titleLower = strtolower((string) $t->title);
                $hasAlert = FALSE;
                foreach ($alertKeywords as $kw) {
                    if ($kw !== '' && str_contains($titleLower, $kw)) {
                        $hasAlert = TRUE;

                        break;
                    }
                }

                return [
                    'id'                 => $t->id,
                    'event_title'        => $t->title,
                    'venue_name'         => $t->venue,
                    'event_date'         => optional($t->event_date)->toISOString(),
                    'sport_category'     => $t->sport,
                    'platform_source'    => $t->platform_display_name ?? $t->platform,
                    'price'              => (float) ($t->min_price ?? 0),
                    'available_quantity' => (int) ($t->availability ?? 0),
                    'demand_level'       => $this->deriveDemandLevel($t->popularity_score),
                    'price_trend'        => $this->derivePriceTrend($t),
                    'has_alert'          => $hasAlert,
                ];
            });

            $response = response()->json([
                'success'    => TRUE,
                'tickets'    => $tickets,
                'count'      => $tickets->count(),
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                ],
                'demand' => [
                    'sport_distribution'    => $demandMetrics['by_sport'],
                    'platform_distribution' => $demandMetrics['by_platform'],
                ],
                'meta' => [
                    'refreshed_at' => now()->toISOString(),
                ],
            ]);

            return $this->withCachingHeaders($response, 30);
        } catch (Throwable $e) {
            Log::error('Dashboard tickets API failure', ['error' => $e->getMessage()]);

            // Graceful fallback to empty payload to avoid breaking clients
            return response()->json([
                'success'    => TRUE,
                'tickets'    => [],
                'count'      => 0,
                'pagination' => [
                    'total'        => 0,
                    'per_page'     => (int) ($request->get('per_page', 25)),
                    'current_page' => (int) ($request->get('page', 1)),
                    'last_page'    => 0,
                ],
                'demand' => [
                    'sport_distribution'    => [],
                    'platform_distribution' => [],
                ],
                'meta' => [
                    'refreshed_at' => now()->toISOString(),
                ],
            ]);
        }
    }

    /**
     * GET /api/dashboard/recommendations
     * Returns small set of recommended tickets with match score.
     */
    public function recommendations(): JsonResponse
    {
        if (!$user = Auth::user()) {
            return response()->json(['success' => FALSE, 'message' => 'Unauthenticated'], 401);
        }

        $cacheKey = 'api:dashboard:recs:user:' . $user->id;
        $recommendations = Cache::remember($cacheKey, 300, function () use ($user) {
            $prefs = $user->preferences ?? [];
            $favoriteTeams = $prefs['favorite_teams'] ?? [];

            $q = ScrapedTicket::available()->upcoming()->recent(48);
            if ($favoriteTeams) {
                $q->where(function ($qb) use ($favoriteTeams): void {
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

        $response = response()->json([
            'success'         => TRUE,
            'recommendations' => $recommendations,
            'meta'            => [
                'refreshed_at' => now()->toISOString(),
                'cache_ttl'    => 300,
            ],
        ]);

        return $this->withCachingHeaders($response, 180);
    }

    // ---------- Helpers ----------
    private function deriveDemandLevel(?float $score): string
    {
        if ($score === NULL) {
            return 'low';
        }

        return $score > 80 ? 'high' : ($score > 50 ? 'medium' : 'low');
    }

    private function derivePriceTrend(ScrapedTicket $ticket): string
    {
        // Basic heuristic: compare min_price to avg of (min+max)/2; placeholder for future historical price service
        if ($ticket->max_price && $ticket->min_price) {
            $mid = ($ticket->min_price + $ticket->max_price) / 2;
            if ($ticket->min_price > $mid * 1.1) {
                return 'up';
            }
            if ($ticket->min_price < $mid * 0.9) {
                return 'down';
            }
        }

        return 'stable';
    }

    private function getUserAlertKeywords(?int $userId): array
    {
        if (!$userId) {
            return [];
        }

        return Cache::remember('api:dashboard:user-alert-keywords:' . $userId, 60, function () use ($userId) {
            return TicketAlert::where('user_id', $userId)
                ->active()
                ->pluck('keywords')
                ->filter()
                ->map(fn ($kw) => strtolower(trim((string) $kw)))
                ->unique()
                ->values()
                ->toArray();
        });
    }

    private function withCachingHeaders(JsonResponse $response, int $ttlSeconds): JsonResponse
    {
        $response->headers->set('Cache-Control', 'private, max-age=' . $ttlSeconds . ', must-revalidate');
        $response->headers->set('X-RateLimit-Policy', 'burst=10;window=60');

        return $response;
    }
}
