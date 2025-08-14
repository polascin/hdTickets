<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\PurchaseAttempt;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\UserFavoriteTeam;
use App\Models\UserFavoriteVenue;
use App\Models\UserPreference;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserActivityController extends Controller
{
    /**
     * Display the user activity dashboard
     */
    /**
     * Index
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $dateRange = $request->get('date_range', '30');
        $startDate = Carbon::now()->subDays((int) $dateRange);

        // Get dashboard data
        $data = [
            'user'          => $user,
            'alertsData'    => $this->getAlertsData($user->id, $startDate),
            'purchaseData'  => $this->getPurchaseData($user->id, $startDate),
            'searchData'    => $this->getSavedSearchesData($user->id),
            'watchlistData' => $this->getWatchlistData($user->id, $startDate),
            'dateRange'     => $dateRange,
            'activityStats' => $this->getActivityStats($user->id, $startDate),
            'chartData'     => $this->getChartData($user->id, $startDate),
        ];

        return view('profile.activity-dashboard', $data);
    }

    /**
     * AJAX endpoint for loading specific widget data
     */
    /**
     * Get  widget data
     *
     * @return array<string, mixed>
     */
    public function getWidgetData(Request $request): array
    {
        $widget = $request->get('widget');
        $userId = Auth::id();
        $dateRange = $request->get('date_range', '30');
        $startDate = Carbon::now()->subDays((int) $dateRange);

        switch ($widget) {
            case 'alerts':
                return response()->json($this->getAlertsData($userId, $startDate));
            case 'purchases':
                return response()->json($this->getPurchaseData($userId, $startDate));
            case 'searches':
                return response()->json($this->getSavedSearchesData($userId));
            case 'watchlist':
                return response()->json($this->getWatchlistData($userId, $startDate));
            case 'charts':
                return response()->json($this->getChartData($userId, $startDate));
            default:
                return response()->json(['error' => 'Invalid widget'], 400);
        }
    }

    /**
     * Export activity data
     */
    /**
     * ExportActivityData
     */
    public function exportActivityData(Request $request): Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $dateRange = $request->get('date_range', '30');
        $startDate = Carbon::now()->subDays((int) $dateRange);

        $data = [
            'user_info' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'export_date'     => Carbon::now()->toISOString(),
                'date_range_days' => $dateRange,
            ],
            'alerts'         => $this->getAlertsData($user->id, $startDate),
            'purchases'      => $this->getPurchaseData($user->id, $startDate),
            'searches'       => $this->getSavedSearchesData($user->id),
            'watchlist'      => $this->getWatchlistData($user->id, $startDate),
            'activity_stats' => $this->getActivityStats($user->id, $startDate),
        ];

        $filename = 'activity-dashboard-export-' . Carbon::now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Get ticket alerts data and analytics
     */
    /**
     * Get  alerts data
     */
    private function getAlertsData(int $userId, Carbon $startDate): array
    {
        $alerts = TicketAlert::forUser($userId)
            ->with('user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $activeAlerts = $alerts->where('status', 'active');
        $totalTriggers = $alerts->sum('matches_found');

        // Get recent triggers
        $recentTriggers = TicketAlert::forUser($userId)
            ->whereNotNull('triggered_at')
            ->where('triggered_at', '>=', $startDate)
            ->orderBy('triggered_at', 'desc')
            ->limit(10)
            ->get();

        // Get alert performance data
        $alertPerformance = TicketAlert::forUser($userId)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as alerts_created, SUM(matches_found) as total_matches')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_alerts'          => $alerts->count(),
            'active_alerts'         => $activeAlerts->count(),
            'paused_alerts'         => $alerts->where('status', 'paused')->count(),
            'total_triggers'        => $totalTriggers,
            'recent_triggers'       => $recentTriggers,
            'alert_performance'     => $alertPerformance,
            'avg_matches_per_alert' => $alerts->count() > 0 ? round($totalTriggers / $alerts->count(), 2) : 0,
            'most_successful_alert' => $alerts->sortByDesc('matches_found')->first(),
        ];
    }

    /**
     * Get purchase history and spending analytics
     */
    /**
     * Get  purchase data
     */
    private function getPurchaseData(int $userId, Carbon $startDate): array
    {
        $purchases = PurchaseAttempt::whereHas('purchaseQueue', function ($query) use ($userId): void {
            $query->where('user_id', $userId);
        })
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $successfulPurchases = $purchases->where('status', PurchaseAttempt::STATUS_SUCCESS);
        $totalSpent = $successfulPurchases->sum('total_paid');

        // Calculate monthly spending
        $monthlySpending = $successfulPurchases
            ->groupBy(function ($purchase) {
                return $purchase->created_at->format('Y-m');
            })
            ->map(function ($monthPurchases) {
                return $monthPurchases->sum('total_paid');
            });

        // Get platform breakdown
        $platformSpending = $successfulPurchases
            ->groupBy('platform')
            ->map(function ($platformPurchases) {
                return [
                    'count' => $platformPurchases->count(),
                    'total' => $platformPurchases->sum('total_paid'),
                    'avg'   => $platformPurchases->avg('total_paid'),
                ];
            });

        // Calculate success rate
        $successRate = $purchases->count() > 0 ?
            round(($successfulPurchases->count() / $purchases->count()) * 100, 2) : 0;

        return [
            'total_attempts'       => $purchases->count(),
            'successful_purchases' => $successfulPurchases->count(),
            'failed_attempts'      => $purchases->where('status', PurchaseAttempt::STATUS_FAILED)->count(),
            'total_spent'          => $totalSpent,
            'average_ticket_price' => $successfulPurchases->count() > 0 ?
                round($totalSpent / $successfulPurchases->count(), 2) : 0,
            'success_rate'       => $successRate,
            'monthly_spending'   => $monthlySpending,
            'platform_breakdown' => $platformSpending,
            'recent_purchases'   => $successfulPurchases->take(5),
            'highest_purchase'   => $successfulPurchases->sortByDesc('total_paid')->first(),
        ];
    }

    /**
     * Get saved searches and frequent queries data
     */
    /**
     * Get  saved searches data
     */
    private function getSavedSearchesData(int $userId): array
    {
        // Get search preferences from user preferences
        $searchPreferences = UserPreference::where('user_id', $userId)
            ->where('preference_category', 'searches')
            ->get()
            ->pluck('preference_value', 'preference_key');

        // Get frequent search terms from alerts (they represent common searches)
        $frequentQueries = TicketAlert::forUser($userId)
            ->selectRaw('alert_name, COUNT(*) as frequency')
            ->groupBy('alert_name')
            ->orderByDesc('frequency')
            ->limit(10)
            ->get();

        // Get saved team and venue searches
        $savedTeamSearches = UserFavoriteTeam::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $savedVenueSearches = UserFavoriteVenue::where('user_id', $userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'search_preferences'   => $searchPreferences,
            'frequent_queries'     => $frequentQueries,
            'saved_team_searches'  => $savedTeamSearches,
            'saved_venue_searches' => $savedVenueSearches,
            'total_saved_searches' => $savedTeamSearches->count() + $savedVenueSearches->count(),
        ];
    }

    /**
     * Get watchlist items with price trends
     */
    /**
     * Get  watchlist data
     */
    private function getWatchlistData(int $userId, Carbon $startDate): array
    {
        // Use favorite teams and venues as watchlist items
        $favoriteTeams = UserFavoriteTeam::where('user_id', $userId)->get();
        $favoriteVenues = UserFavoriteVenue::where('user_id', $userId)->get();

        // Get recent ticket data for watchlist analysis
        $watchlistTickets = ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->where(function ($query) use ($favoriteTeams, $favoriteVenues): void {
                foreach ($favoriteTeams as $team) {
                    $query->orWhere('event_title', 'LIKE', '%' . $team->team_name . '%');
                }
                foreach ($favoriteVenues as $venue) {
                    $query->orWhere('venue', 'LIKE', '%' . $venue->venue_name . '%');
                }
            })
            ->orderBy('scraped_at', 'desc')
            ->get();

        // Calculate price trends for watchlist items
        $priceTrends = $this->calculatePriceTrends($watchlistTickets);

        return [
            'favorite_teams'        => $favoriteTeams,
            'favorite_venues'       => $favoriteVenues,
            'total_watchlist_items' => $favoriteTeams->count() + $favoriteVenues->count(),
            'recent_tickets'        => $watchlistTickets->take(20),
            'price_trends'          => $priceTrends,
            'average_price'         => $watchlistTickets->avg('total_price'),
            'price_range'           => [
                'min' => $watchlistTickets->min('total_price'),
                'max' => $watchlistTickets->max('total_price'),
            ],
        ];
    }

    /**
     * Calculate price trends for tickets
     */
    /**
     * CalculatePriceTrends
     */
    private function calculatePriceTrends(object $tickets): array
    {
        $trends = [];

        $groupedTickets = $tickets->groupBy(function ($ticket) {
            return $ticket->event_title . ' - ' . $ticket->venue;
        });

        foreach ($groupedTickets as $eventName => $eventTickets) {
            $sortedTickets = $eventTickets->sortBy('scraped_at');
            $firstPrice = $sortedTickets->first()->total_price ?? 0;
            $lastPrice = $sortedTickets->last()->total_price ?? 0;

            $priceChange = $lastPrice - $firstPrice;
            $percentChange = $firstPrice > 0 ? round(($priceChange / $firstPrice) * 100, 2) : 0;

            $trends[$eventName] = [
                'first_price'    => $firstPrice,
                'last_price'     => $lastPrice,
                'price_change'   => $priceChange,
                'percent_change' => $percentChange,
                'ticket_count'   => $eventTickets->count(),
                'trend'          => $percentChange > 5 ? 'increasing' : ($percentChange < -5 ? 'decreasing' : 'stable'),
            ];
        }

        return $trends;
    }

    /**
     * Get general activity statistics
     */
    /**
     * Get  activity stats
     */
    private function getActivityStats(int $userId, Carbon $startDate): array
    {
        $loginHistory = LoginHistory::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $sessions = UserSession::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_logins'      => $loginHistory->count(),
            'successful_logins' => $loginHistory->where('successful', TRUE)->count(),
            'failed_logins'     => $loginHistory->where('successful', FALSE)->count(),
            'unique_login_days' => $loginHistory->groupBy(function ($login) {
                return $login->created_at->format('Y-m-d');
            })->count(),
            'total_sessions'           => $sessions->count(),
            'average_session_duration' => $this->calculateAverageSessionDuration($sessions),
            'most_active_day'          => $this->getMostActiveDay($loginHistory),
            'login_frequency'          => round($loginHistory->count() / max(1, $startDate->diffInDays(Carbon::now())), 2),
        ];
    }

    /**
     * Get chart data for visual analytics
     */
    /**
     * Get  chart data
     */
    private function getChartData(int $userId, Carbon $startDate): array
    {
        // Alert triggers over time
        $alertTriggers = TicketAlert::forUser($userId)
            ->selectRaw('DATE(triggered_at) as date, COUNT(*) as triggers')
            ->whereNotNull('triggered_at')
            ->where('triggered_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Purchase attempts over time
        $purchaseAttempts = PurchaseAttempt::whereHas('purchaseQueue', function ($query) use ($userId): void {
            $query->where('user_id', $userId);
        })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as attempts, SUM(CASE WHEN status = ? THEN total_paid ELSE 0 END) as spent', [PurchaseAttempt::STATUS_SUCCESS])
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Login activity over time
        $loginActivity = LoginHistory::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as logins')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'alert_triggers'    => $alertTriggers,
            'purchase_attempts' => $purchaseAttempts,
            'login_activity'    => $loginActivity,
        ];
    }

    /**
     * Calculate average session duration
     */
    /**
     * CalculateAverageSessionDuration
     */
    private function calculateAverageSessionDuration(object $sessions): int
    {
        $totalDuration = 0;
        $validSessions = 0;

        foreach ($sessions as $session) {
            if ($session->last_activity && $session->created_at) {
                $duration = Carbon::parse($session->last_activity)->diffInMinutes(Carbon::parse($session->created_at));
                $totalDuration += $duration;
                $validSessions++;
            }
        }

        return $validSessions > 0 ? round($totalDuration / $validSessions) : 0;
    }

    /**
     * Get the most active day of the week
     */
    /**
     * Get  most active day
     */
    private function getMostActiveDay(object $loginHistory): string
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $dayCount = $loginHistory->groupBy(function ($login) {
            return $login->created_at->dayOfWeek;
        })->map(function ($dayLogins) {
            return $dayLogins->count();
        });

        $mostActiveDay = $dayCount->keys()->sortByDesc(function ($day) use ($dayCount) {
            return $dayCount[$day];
        })->first();

        return $mostActiveDay !== NULL ? $dayNames[$mostActiveDay] : 'No data';
    }
}
