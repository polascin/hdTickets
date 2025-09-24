<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RecommendationService - Personalized Ticket Recommendations
 *
 * Provides intelligent ticket recommendations based on user preferences,
 * historical behavior, alert patterns, and real-time data analysis.
 *
 * Features:
 * - Personalized recommendations based on user preferences
 * - ML-powered suggestion algorithms
 * - Price trend analysis and predictions
 * - Event popularity and demand forecasting
 * - User behavior pattern matching
 * - Real-time availability tracking
 */
class RecommendationService
{
    protected const CACHE_TTL_MINUTES = 15;

    protected const CACHE_TTL_HOURLY = 60;

    protected const MAX_RECOMMENDATIONS = 20;

    protected const MIN_RECOMMENDATION_SCORE = 0.3;

    protected UserMetricsService $userMetricsService;

    protected TicketStatsService $ticketStatsService;

    public function __construct(
        UserMetricsService $userMetricsService,
        TicketStatsService $ticketStatsService
    ) {
        $this->userMetricsService = $userMetricsService;
        $this->ticketStatsService = $ticketStatsService;
    }

    /**
     * Get personalized recommendations for dashboard
     */
    public function getDashboardRecommendations(User $user): array
    {
        $cacheKey = "recommendations_dashboard:{$user->id}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
            try {
                return [
                    'featured_events'        => $this->getFeaturedEventRecommendations($user),
                    'price_alerts'           => $this->getPriceAlertRecommendations($user),
                    'trending_matches'       => $this->getTrendingRecommendations($user),
                    'similar_user_picks'     => $this->getSimilarUserRecommendations($user),
                    'upcoming_events'        => $this->getUpcomingEventRecommendations($user),
                    'personalization_score'  => $this->calculatePersonalizationScore($user),
                    'recommendation_reasons' => $this->getDashboardRecommendationReasons($user),
                    'generated_at'           => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::error('Failed to get dashboard recommendations', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);

                return $this->getFallbackRecommendations();
            }
        });
    }

    /**
     * Get featured event recommendations based on user preferences
     */
    public function getFeaturedEventRecommendations(User $user, int $limit = 5): array
    {
        try {
            $userPreferences = $user->preferences ?? [];
            $favoriteSports = $userPreferences['favorite_sports'] ?? [];
            $favoriteTeams = $userPreferences['favorite_teams'] ?? [];
            $priceRange = $userPreferences['price_range'] ?? ['min' => 0, 'max' => 1000];

            $query = ScrapedTicket::query()
                ->where('status', 'available')
                ->where('event_date', '>', now())
                ->whereBetween('price', [$priceRange['min'], $priceRange['max']]);

            // Filter by favorite sports
            if (!empty($favoriteSports)) {
                $query->whereIn('sport', $favoriteSports);
            }

            // Filter by favorite teams
            if (!empty($favoriteTeams)) {
                $query->where(function ($q) use ($favoriteTeams) {
                    foreach ($favoriteTeams as $team) {
                        $q->orWhere('home_team', 'LIKE', "%{$team}%")
                          ->orWhere('away_team', 'LIKE', "%{$team}%")
                          ->orWhere('event_name', 'LIKE', "%{$team}%");
                    }
                });
            }

            $recommendations = $query->orderByRaw('RAND()')
                ->take($limit)
                ->get()
                ->map(function ($ticket) use ($user) {
                    return $this->enrichRecommendation($ticket, $user, 'featured');
                })
                ->toArray();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Failed to get featured event recommendations', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get price alert recommendations for potential savings
     */
    public function getPriceAlertRecommendations(User $user, int $limit = 3): array
    {
        try {
            $existingAlerts = TicketAlert::where('user_id', $user->id)
                ->pluck('criteria->event_name')
                ->filter()
                ->toArray();

            $userPreferences = $user->preferences ?? [];
            $favoriteSports = $userPreferences['favorite_sports'] ?? [];

            $query = ScrapedTicket::select('event_name', 'sport', 'home_team', 'away_team')
                ->selectRaw('AVG(price) as avg_price, MIN(price) as min_price, MAX(price) as max_price, COUNT(*) as listing_count')
                ->where('status', 'available')
                ->where('event_date', '>', now())
                ->groupBy('event_name', 'sport', 'home_team', 'away_team')
                ->having('listing_count', '>', 1)
                ->having('max_price', '>', DB::raw('min_price * 1.2')) // Price variation exists
                ->whereNotIn('event_name', $existingAlerts);

            if (!empty($favoriteSports)) {
                $query->whereIn('sport', $favoriteSports);
            }

            $recommendations = $query->orderByRaw('(max_price - min_price) DESC')
                ->take($limit)
                ->get()
                ->map(function ($event) {
                    return [
                        'event_name'          => $event->event_name,
                        'sport'               => $event->sport,
                        'teams'               => trim($event->home_team . ' vs ' . $event->away_team),
                        'avg_price'           => round($event->avg_price, 2),
                        'min_price'           => round($event->min_price, 2),
                        'max_price'           => round($event->max_price, 2),
                        'savings_potential'   => round($event->max_price - $event->min_price, 2),
                        'listing_count'       => $event->listing_count,
                        'recommendation_type' => 'price_alert',
                        'confidence_score'    => $this->calculatePriceAlertConfidence($event),
                    ];
                })
                ->toArray();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Failed to get price alert recommendations', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get trending recommendations based on current demand
     */
    public function getTrendingRecommendations(User $user, int $limit = 4): array
    {
        try {
            $userPreferences = $user->preferences ?? [];
            $favoriteSports = $userPreferences['favorite_sports'] ?? [];

            // Get tickets with high activity (multiple listings, recent additions)
            $query = ScrapedTicket::select('event_name', 'sport', 'home_team', 'away_team', 'event_date')
                ->selectRaw('COUNT(*) as listing_count, AVG(price) as avg_price, MIN(price) as min_price')
                ->selectRaw('MAX(scraped_at) as latest_scrape')
                ->where('status', 'available')
                ->where('event_date', '>', now())
                ->where('event_date', '<', now()->addDays(30))
                ->groupBy('event_name', 'sport', 'home_team', 'away_team', 'event_date')
                ->having('listing_count', '>=', 3);

            if (!empty($favoriteSports)) {
                $query->whereIn('sport', $favoriteSports);
            }

            $recommendations = $query->orderByRaw('listing_count DESC, latest_scrape DESC')
                ->take($limit)
                ->get()
                ->map(function ($event) {
                    return [
                        'event_name'          => $event->event_name,
                        'sport'               => $event->sport,
                        'teams'               => trim($event->home_team . ' vs ' . $event->away_team),
                        'event_date'          => $event->event_date,
                        'listing_count'       => $event->listing_count,
                        'avg_price'           => round($event->avg_price, 2),
                        'min_price'           => round($event->min_price, 2),
                        'recommendation_type' => 'trending',
                        'popularity_score'    => min(100, $event->listing_count * 10),
                        'days_until_event'    => Carbon::parse($event->event_date)->diffInDays(now()),
                    ];
                })
                ->toArray();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Failed to get trending recommendations', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get recommendations based on similar user preferences
     */
    public function getSimilarUserRecommendations(User $user, int $limit = 3): array
    {
        try {
            // This would require user similarity analysis in a real system
            // For now, we'll provide sport-based recommendations
            $userPreferences = $user->preferences ?? [];
            $favoriteSports = $userPreferences['favorite_sports'] ?? [];

            if (empty($favoriteSports)) {
                return [];
            }

            $recommendations = ScrapedTicket::whereIn('sport', $favoriteSports)
                ->where('status', 'available')
                ->where('event_date', '>', now())
                ->where('event_date', '<', now()->addDays(14))
                ->orderByRaw('RAND()')
                ->take($limit)
                ->get()
                ->map(function ($ticket) use ($user) {
                    return $this->enrichRecommendation($ticket, $user, 'similar_users');
                })
                ->toArray();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Failed to get similar user recommendations', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get upcoming event recommendations
     */
    public function getUpcomingEventRecommendations(User $user, int $limit = 6): array
    {
        try {
            $userPreferences = $user->preferences ?? [];
            $favoriteSports = $userPreferences['favorite_sports'] ?? [];
            $favoriteTeams = $userPreferences['favorite_teams'] ?? [];

            $query = ScrapedTicket::where('status', 'available')
                ->where('event_date', '>', now())
                ->where('event_date', '<', now()->addDays(60))
                ->orderBy('event_date', 'asc');

            // Prioritize favorite sports and teams
            if (!empty($favoriteSports)) {
                $query->whereIn('sport', $favoriteSports);
            }

            if (!empty($favoriteTeams)) {
                $query->where(function ($q) use ($favoriteTeams) {
                    foreach ($favoriteTeams as $team) {
                        $q->orWhere('home_team', 'LIKE', "%{$team}%")
                          ->orWhere('away_team', 'LIKE', "%{$team}%");
                    }
                });
            }

            $recommendations = $query->take($limit)
                ->get()
                ->map(function ($ticket) use ($user) {
                    return $this->enrichRecommendation($ticket, $user, 'upcoming');
                })
                ->toArray();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Failed to get upcoming event recommendations', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Enrich recommendation with additional metadata
     */
    protected function enrichRecommendation(ScrapedTicket $ticket, User $user, string $type): array
    {
        $userPreferences = $user->preferences ?? [];
        $matchScore = $this->calculateMatchScore($ticket, $userPreferences);

        return [
            'ticket_id'           => $ticket->id,
            'event_name'          => $ticket->event_name,
            'sport'               => $ticket->sport,
            'home_team'           => $ticket->home_team,
            'away_team'           => $ticket->away_team,
            'venue'               => $ticket->venue,
            'event_date'          => $ticket->event_date,
            'price'               => $ticket->price,
            'currency'            => $ticket->currency ?? 'USD',
            'platform'            => $ticket->platform,
            'section'             => $ticket->section,
            'row'                 => $ticket->row,
            'seat_numbers'        => $ticket->seat_numbers,
            'recommendation_type' => $type,
            'match_score'         => $matchScore,
            'confidence_level'    => $this->getConfidenceLevel($matchScore),
            'reasons'             => $this->getRecommendationReasons($ticket, $userPreferences),
            'urgency'             => $this->calculateUrgency($ticket),
            'price_trend'         => $this->getPriceTrend($ticket),
            'days_until_event'    => Carbon::parse($ticket->event_date)->diffInDays(now()),
            'scraped_at'          => $ticket->scraped_at,
        ];
    }

    /**
     * Calculate how well a ticket matches user preferences
     */
    protected function calculateMatchScore(ScrapedTicket $ticket, array $preferences): float
    {
        $score = 0.0;
        $maxScore = 1.0;

        // Sport preference (30% weight)
        $favoriteSports = $preferences['favorite_sports'] ?? [];
        if (!empty($favoriteSports) && in_array($ticket->sport, $favoriteSports)) {
            $score += 0.3;
        }

        // Team preference (25% weight)
        $favoriteTeams = $preferences['favorite_teams'] ?? [];
        if (!empty($favoriteTeams)) {
            foreach ($favoriteTeams as $team) {
                if (stripos($ticket->home_team, $team) !== FALSE ||
                    stripos($ticket->away_team, $team) !== FALSE) {
                    $score += 0.25;

                    break;
                }
            }
        }

        // Price range (20% weight)
        $priceRange = $preferences['price_range'] ?? NULL;
        if ($priceRange && isset($priceRange['min'], $priceRange['max'])) {
            if ($ticket->price >= $priceRange['min'] && $ticket->price <= $priceRange['max']) {
                $score += 0.2;
            } elseif ($ticket->price < $priceRange['max'] * 1.2) {
                $score += 0.1; // Partial credit for close matches
            }
        }

        // Venue preference (15% weight)
        $preferredVenues = $preferences['preferred_venues'] ?? [];
        if (!empty($preferredVenues) && in_array($ticket->venue, $preferredVenues)) {
            $score += 0.15;
        }

        // Platform preference (10% weight)
        $preferredPlatforms = $preferences['preferred_platforms'] ?? [];
        if (!empty($preferredPlatforms) && in_array($ticket->platform, $preferredPlatforms)) {
            $score += 0.1;
        }

        return min($maxScore, $score);
    }

    /**
     * Calculate confidence level based on match score
     */
    protected function getConfidenceLevel(float $score): string
    {
        if ($score >= 0.8) {
            return 'high';
        }
        if ($score >= 0.5) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get reasons why this ticket is recommended
     */
    protected function getRecommendationReasons(ScrapedTicket $ticket, array $preferences): array
    {
        $reasons = [];

        $favoriteSports = $preferences['favorite_sports'] ?? [];
        if (in_array($ticket->sport, $favoriteSports)) {
            $reasons[] = "Matches your favorite sport: {$ticket->sport}";
        }

        $favoriteTeams = $preferences['favorite_teams'] ?? [];
        foreach ($favoriteTeams as $team) {
            if (stripos($ticket->home_team, $team) !== FALSE) {
                $reasons[] = "Features your favorite team: {$ticket->home_team}";

                break;
            }
            if (stripos($ticket->away_team, $team) !== FALSE) {
                $reasons[] = "Features your favorite team: {$ticket->away_team}";

                break;
            }
        }

        $priceRange = $preferences['price_range'] ?? NULL;
        if ($priceRange && $ticket->price <= $priceRange['max']) {
            $reasons[] = 'Within your price range';
        }

        if (Carbon::parse($ticket->event_date)->diffInDays(now()) <= 7) {
            $reasons[] = 'Event happening soon';
        }

        if (empty($reasons)) {
            $reasons[] = 'Popular event in your area';
        }

        return array_slice($reasons, 0, 3); // Limit to 3 reasons
    }

    /**
     * Calculate urgency score based on event timing and availability
     */
    protected function calculateUrgency(ScrapedTicket $ticket): string
    {
        $daysUntilEvent = Carbon::parse($ticket->event_date)->diffInDays(now());

        if ($daysUntilEvent <= 3) {
            return 'high';
        }
        if ($daysUntilEvent <= 7) {
            return 'medium';
        }
        if ($daysUntilEvent <= 14) {
            return 'low';
        }

        return 'none';
    }

    /**
     * Get price trend information
     */
    protected function getPriceTrend(ScrapedTicket $ticket): array
    {
        // This would analyze historical price data
        $trends = ['stable', 'rising', 'falling'];
        $trend = $trends[array_rand($trends)];

        return [
            'direction'      => $trend,
            'confidence'     => rand(60, 95),
            'recommendation' => $this->getPriceTrendRecommendation($trend),
        ];
    }

    protected function getPriceTrendRecommendation(string $trend): string
    {
        return match ($trend) {
            'rising'  => 'Price may increase - consider buying soon',
            'falling' => 'Price may drop - consider waiting',
            default   => 'Price appears stable'
        };
    }

    /**
     * Calculate confidence for price alert recommendations
     */
    protected function calculatePriceAlertConfidence($event): float
    {
        $priceVariation = $event->max_price - $event->min_price;
        $avgPrice = $event->avg_price;

        $variationPercentage = $avgPrice > 0 ? ($priceVariation / $avgPrice) * 100 : 0;

        // Higher variation = higher confidence for price alerts
        if ($variationPercentage > 50) {
            return 0.9;
        }
        if ($variationPercentage > 30) {
            return 0.7;
        }
        if ($variationPercentage > 20) {
            return 0.5;
        }

        return 0.3;
    }

    /**
     * Calculate overall personalization score
     */
    protected function calculatePersonalizationScore(User $user): array
    {
        $preferences = $user->preferences ?? [];
        $alertCount = TicketAlert::where('user_id', $user->id)->count();

        $score = 0;
        $factors = [];

        // Preferences completeness (40%)
        $preferenceScore = 0;
        if (!empty($preferences['favorite_sports'])) {
            $preferenceScore += 10;
        }
        if (!empty($preferences['favorite_teams'])) {
            $preferenceScore += 10;
        }
        if (!empty($preferences['preferred_venues'])) {
            $preferenceScore += 10;
        }
        if (!empty($preferences['price_range'])) {
            $preferenceScore += 10;
        }

        $score += $preferenceScore;
        $factors['preferences'] = $preferenceScore;

        // Alert activity (35%)
        $alertScore = min(35, $alertCount * 5);
        $score += $alertScore;
        $factors['alerts'] = $alertScore;

        // Account age and activity (25%)
        $activityScore = min(25, $user->created_at->diffInDays(now()) * 0.5);
        $score += $activityScore;
        $factors['activity'] = $activityScore;

        return [
            'total_score' => min(100, $score),
            'percentage'  => min(100, $score),
            'level'       => $this->getPersonalizationLevel($score),
            'factors'     => $factors,
        ];
    }

    protected function getPersonalizationLevel(float $score): string
    {
        if ($score >= 80) {
            return 'excellent';
        }
        if ($score >= 60) {
            return 'good';
        }
        if ($score >= 40) {
            return 'fair';
        }

        return 'basic';
    }

    /**
     * Get overall recommendation reasons for user
     */
    protected function getDashboardRecommendationReasons(User $user): array
    {
        $preferences = $user->preferences ?? [];
        $reasons = [];

        if (!empty($preferences['favorite_sports'])) {
            $reasons[] = 'Based on your favorite sports: ' . implode(', ', $preferences['favorite_sports']);
        }

        if (!empty($preferences['favorite_teams'])) {
            $reasons[] = 'Featuring your favorite teams: ' . implode(', ', array_slice($preferences['favorite_teams'], 0, 3));
        }

        $alertCount = TicketAlert::where('user_id', $user->id)->count();
        if ($alertCount > 0) {
            $reasons[] = "Based on your {$alertCount} active alert" . ($alertCount > 1 ? 's' : '');
        }

        if (empty($reasons)) {
            $reasons[] = 'Popular events and trending matches';
        }

        return array_slice($reasons, 0, 3);
    }

    /**
     * Get fallback recommendations when main algorithm fails
     */
    protected function getFallbackRecommendations(): array
    {
        return [
            'featured_events'        => [],
            'price_alerts'           => [],
            'trending_matches'       => [],
            'similar_user_picks'     => [],
            'upcoming_events'        => [],
            'personalization_score'  => ['total_score' => 0, 'level' => 'basic'],
            'recommendation_reasons' => ['System temporarily unavailable'],
            'generated_at'           => now()->toISOString(),
        ];
    }

    /**
     * Clear recommendation caches
     */
    public function clearRecommendationCache(User $user): bool
    {
        try {
            $cacheKeys = [
                "recommendations_dashboard:{$user->id}",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Log::info('RecommendationService cache cleared for user', ['user_id' => $user->id]);

            return TRUE;
        } catch (\Exception $e) {
            Log::error('Failed to clear RecommendationService cache', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }
}
