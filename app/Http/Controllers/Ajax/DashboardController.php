<?php declare(strict_types=1);

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Ajax\Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\ScrapingStats;
use App\Models\TicketAlert;
use App\Models\UserPreference;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function count;

class DashboardController extends Controller
{
    /**
     * Get live tickets data for dashboard
     * Cached for 2 minutes for performance
     */
    /**
     * LiveTickets
     */
    public function liveTickets(Request $request): RedirectResponse
    {
        try {
            $cacheKey = 'dashboard_live_tickets_' . Auth::id();

            return Cache::remember($cacheKey, 120, function () {
                // Get live sports event tickets
                $tickets = ScrapedTicket::with(['category'])
                    ->where('is_available', TRUE)
                    ->where('scraped_at', '>=', now()->subHours(6))
                    ->orderBy('scraped_at', 'desc')
                    ->limit(20)
                    ->get();

                // Calculate real-time statistics
                $stats = [
                    'total_available'  => $tickets->count(),
                    'high_demand'      => $tickets->where('is_high_demand', TRUE)->count(),
                    'avg_price'        => $tickets->avg(fn ($ticket): float|int => ($ticket->min_price + $ticket->max_price) / 2),
                    'platforms_active' => $tickets->pluck('platform')->unique()->count(),
                    'sports_available' => $tickets->pluck('sport')->unique()->filter()->count(),
                    'last_updated'     => now()->toISOString(),
                ];

                // Group tickets by sport for better display
                $ticketsBySport = $tickets->groupBy('sport')->map(fn ($sportTickets): array => [
                    'count'          => $sportTickets->count(),
                    'avg_price'      => $sportTickets->avg(fn ($ticket): float|int => ($ticket->min_price + $ticket->max_price) / 2),
                    'platforms'      => $sportTickets->pluck('platform')->unique()->values(),
                    'latest_tickets' => $sportTickets->take(3)->values(),
                ]);

                return response()->json([
                    'success' => TRUE,
                    'data'    => [
                        'tickets'         => $tickets,
                        'stats'           => $stats,
                        'by_sport'        => $ticketsBySport,
                        'recommendations' => $this->getUserRecommendations(),
                    ],
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error fetching live tickets for dashboard', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to load live tickets data',
            ], 500);
        }
    }

    /**
     * Get personalized user recommendations
     */
    /**
     * UserRecommendations
     */
    public function userRecommendations(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => FALSE, 'error' => 'User not authenticated'], 401);
            }

            $cacheKey = "user_recommendations_{$user->id}";

            return Cache::remember($cacheKey, 600, function () use ($user) {
                // Get user preferences and alert patterns
                $userAlerts = TicketAlert::where('user_id', $user->id)
                    ->where('is_active', TRUE)
                    ->get();

                $preferences = UserPreference::getAlertPreferences($user->id);
                $favoriteTeams = $preferences['favorite_teams'] ?? [];
                $preferredSports = $preferences['preferred_sports'] ?? [];

                // Build recommendation query
                $query = ScrapedTicket::where('is_available', TRUE)
                    ->where('scraped_at', '>=', now()->subHours(12));

                // Apply user preferences
                if (!empty($favoriteTeams)) {
                    $query->where(function ($q) use ($favoriteTeams): void {
                        foreach ($favoriteTeams as $team) {
                            $q->orWhere('title', 'like', "%{$team}%")
                                ->orWhere('team', 'like', "%{$team}%");
                        }
                    });
                }

                if (!empty($preferredSports)) {
                    $query->whereIn('sport', $preferredSports);
                }

                // Get user's price range from alerts
                $avgMaxPrice = $userAlerts->avg('max_price');
                if ($avgMaxPrice) {
                    $query->where('min_price', '<=', $avgMaxPrice * 1.2); // 20% buffer
                }

                $recommendations = $query->orderBy('is_high_demand', 'desc')
                    ->orderBy('scraped_at', 'desc')
                    ->limit(10)
                    ->get();

                // Score recommendations based on user activity
                $scoredRecommendations = $recommendations->map(function ($ticket) use ($userAlerts, $favoriteTeams) {
                    $score = 50; // Base score

                    // Boost score for favorite teams
                    foreach ($favoriteTeams as $team) {
                        if (stripos((string) $ticket->title, (string) $team) !== FALSE) {
                            $score += 20;

                            break;
                        }
                    }

                    // Boost score for high demand tickets
                    if ($ticket->is_high_demand) {
                        $score += 15;
                    }

                    // Boost score for recently scraped tickets
                    if ($ticket->scraped_at >= now()->subHours(2)) {
                        $score += 10;
                    }

                    // Boost score if matches user alert keywords
                    foreach ($userAlerts as $alert) {
                        if (stripos((string) $ticket->title, (string) $alert->keywords) !== FALSE) {
                            $score += 25;

                            break;
                        }
                    }

                    $ticket->recommendation_score = min($score, 100);

                    return $ticket;
                });

                return response()->json([
                    'success' => TRUE,
                    'data'    => [
                        'recommendations'  => $scoredRecommendations->sortByDesc('recommendation_score')->values(),
                        'user_preferences' => [
                            'favorite_teams'   => $favoriteTeams,
                            'preferred_sports' => $preferredSports,
                            'active_alerts'    => $userAlerts->count(),
                            'avg_max_price'    => $avgMaxPrice,
                        ],
                    ],
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error fetching user recommendations', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to load recommendations',
            ], 500);
        }
    }

    /**
     * Get platform health status
     */
    /**
     * PlatformHealth
     */
    public function platformHealth(Request $request): RedirectResponse
    {
        try {
            $cacheKey = 'dashboard_platform_health';

            return Cache::remember($cacheKey, 300, function () {
                $platforms = ['stubhub', 'ticketmaster', 'viagogo', 'seatgeek', 'vivid_seats'];
                $healthData = [];

                foreach ($platforms as $platform) {
                    // Get recent scraping stats for this platform
                    $recentStats = ScrapingStats::where('platform', $platform)
                        ->where('created_at', '>=', now()->subHour())
                        ->get();

                    $successfulOps = $recentStats->where('status', 'success')->count();
                    $totalOps = $recentStats->count();
                    $successRate = $totalOps > 0 ? ($successfulOps / $totalOps) * 100 : 0;
                    $avgResponseTime = $recentStats->avg('response_time_ms') ?? 0;

                    // Determine health status
                    $status = 'healthy';
                    if ($successRate < 50 || $totalOps === 0) {
                        $status = 'down';
                    } elseif ($successRate < 80 || $avgResponseTime > 5000) {
                        $status = 'degraded';
                    }

                    $healthData[$platform] = [
                        'status'            => $status,
                        'success_rate'      => round($successRate, 2),
                        'operations_count'  => $totalOps,
                        'avg_response_time' => round($avgResponseTime, 2),
                        'last_check'        => $recentStats->max('created_at') ?? now(),
                    ];
                }

                // Calculate overall system health
                $healthyCount = collect($healthData)->where('status', 'healthy')->count();
                $totalCount = count($healthData);
                $overallHealth = $totalCount > 0 ? ($healthyCount / $totalCount) * 100 : 0;

                return response()->json([
                    'success' => TRUE,
                    'data'    => [
                        'platforms'      => $healthData,
                        'overall_health' => [
                            'score'         => round($overallHealth, 2),
                            'status'        => $overallHealth >= 80 ? 'healthy' : ($overallHealth >= 60 ? 'degraded' : 'critical'),
                            'healthy_count' => $healthyCount,
                            'total_count'   => $totalCount,
                        ],
                    ],
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error fetching platform health', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to load platform health data',
            ], 500);
        }
    }

    /**
     * Get price alerts for user
     */
    /**
     * PriceAlerts
     */
    public function priceAlerts(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => FALSE, 'error' => 'User not authenticated'], 401);
            }

            $cacheKey = "price_alerts_{$user->id}";

            return Cache::remember($cacheKey, 180, function () use ($user) {
                // Get user's active alerts
                $activeAlerts = TicketAlert::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->get();

                $alertResults = [];

                foreach ($activeAlerts as $alert) {
                    // Find matching tickets for this alert
                    $matchingTickets = ScrapedTicket::where('is_available', TRUE)
                        ->where('scraped_at', '>=', now()->subHours(24))
                        ->where('title', 'like', "%{$alert->keywords}%");

                    if ($alert->max_price) {
                        $matchingTickets->where('min_price', '<=', $alert->max_price);
                    }

                    if ($alert->sport) {
                        $matchingTickets->where('sport', $alert->sport);
                    }

                    if ($alert->platform) {
                        $matchingTickets->where('platform', $alert->platform);
                    }

                    $tickets = $matchingTickets->orderBy('min_price', 'asc')->limit(5)->get();

                    if ($tickets->count() > 0) {
                        $alertResults[] = [
                            'alert'               => $alert,
                            'matches_count'       => $tickets->count(),
                            'best_price'          => $tickets->min('min_price'),
                            'avg_price'           => $tickets->avg(fn ($ticket): float|int => ($ticket->min_price + $ticket->max_price) / 2),
                            'tickets'             => $tickets->take(3), // Show top 3 matches
                            'price_drop_detected' => $this->detectPriceDrop($alert, $tickets),
                        ];
                    }
                }

                // Get recent price drops across all sports
                $recentPriceDrops = ScrapedTicket::where('is_available', TRUE)
                    ->where('scraped_at', '>=', now()->subHours(6))
                    ->where('price_drop_percentage', '>', 10)
                    ->orderByDesc('price_drop_percentage')
                    ->limit(10)
                    ->get();

                return response()->json([
                    'success' => TRUE,
                    'data'    => [
                        'alert_results'            => $alertResults,
                        'active_alerts_count'      => $activeAlerts->count(),
                        'total_matches'            => collect($alertResults)->sum('matches_count'),
                        'recent_price_drops'       => $recentPriceDrops,
                        'price_drop_opportunities' => $recentPriceDrops->count(),
                    ],
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error fetching price alerts', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Unable to load price alerts',
            ], 500);
        }
    }

    /**
     * Helper method to get basic user recommendations
     */
    private function getUserRecommendations()
    {
        try {
            // Get popular/trending tickets for general recommendations
            return ScrapedTicket::where('is_available', TRUE)
                ->where('is_high_demand', TRUE)
                ->where('scraped_at', '>=', now()->subHours(6))
                ->orderBy('scraped_at', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'sport', 'venue', 'min_price', 'max_price', 'platform']);
        } catch (Exception $e) {
            Log::warning('Error getting user recommendations: ' . $e->getMessage());

            return collect([]);
        }
    }

    /**
     * Detect if there's been a price drop for alert
     *
     * @param mixed $alert
     * @param mixed $tickets
     */
    private function detectPriceDrop($alert, $tickets): array
    {
        try {
            // This is a simplified version - in production you'd compare with historical data
            $currentBestPrice = $tickets->min('min_price');
            $historicalAvg = $alert->last_matched_price ?? ($currentBestPrice * 1.2);

            if ($currentBestPrice < $historicalAvg * 0.9) { // 10% drop
                return [
                    'detected'        => TRUE,
                    'drop_percentage' => round((($historicalAvg - $currentBestPrice) / $historicalAvg) * 100, 2),
                    'previous_price'  => $historicalAvg,
                    'current_price'   => $currentBestPrice,
                ];
            }

            return ['detected' => FALSE];
        } catch (Exception $e) {
            return ['detected' => FALSE, 'error' => $e->getMessage()];
        }
    }
}
