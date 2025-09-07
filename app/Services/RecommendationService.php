<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function in_array;
use function is_array;
use function is_string;

/**
 * Recommendation Service
 *
 * Provides AI-powered and rule-based ticket recommendations for users
 * based on their preferences, behavior, and market trends.
 */
class RecommendationService
{
    private const CACHE_TTL = 900; // 15 minutes

    private const MAX_RECOMMENDATIONS = 10;

    /**
     * Get personalized ticket recommendations for a user
     */
    public function getPersonalizedRecommendations(User $user): array
    {
        $cacheKey = "recommendations_user_{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            try {
                $recommendations = [];

                // Get user preferences and behavior data
                $preferences = $this->getUserPreferences($user);
                $behaviorData = $this->getUserBehaviorData($user);

                // Get base tickets for recommendations
                $baseTickets = $this->getBaseTicketsForRecommendations();

                foreach ($baseTickets as $ticket) {
                    $score = $this->calculateRecommendationScore($ticket, $preferences, $behaviorData);

                    if ($score > 0.3) { // Minimum threshold
                        $recommendations[] = [
                            'ticket'           => $ticket,
                            'confidence_score' => $score * 100,
                            'match_reason'     => $this->getMatchReason($ticket, $preferences, $behaviorData),
                            'priority'         => $this->calculatePriority($score, $ticket),
                        ];
                    }
                }

                // Sort by confidence score and limit
                usort($recommendations, fn ($a, $b) => $b['confidence_score'] <=> $a['confidence_score']);

                return array_slice($recommendations, 0, self::MAX_RECOMMENDATIONS);
            } catch (Exception $e) {
                Log::error('Failed to generate recommendations: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get trending recommendations (not personalized)
     */
    public function getTrendingRecommendations(int $limit = 5): array
    {
        $cacheKey = 'trending_recommendations';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            try {
                $tickets = ScrapedTicket::where('active', TRUE)
                    ->where('price', '>', 0)
                    ->where('event_date', '>=', now())
                    ->where('demand_indicator', 'high')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();

                return $tickets->map(function ($ticket) {
                    return [
                        'ticket'           => $ticket,
                        'confidence_score' => 85, // Static high score for trending
                        'match_reason'     => 'Trending now',
                        'priority'         => 'high',
                    ];
                })->toArray();
            } catch (Exception $e) {
                Log::error('Failed to get trending recommendations: ' . $e->getMessage());

                return [];
            }
        });
    }

    /**
     * Get user preferences from various sources
     */
    private function getUserPreferences(User $user): array
    {
        // Default preferences
        $preferences = [
            'sports'              => [],
            'price_range'         => ['min' => 0, 'max' => 1000],
            'venues'              => [],
            'regions'             => [],
            'event_types'         => [],
            'preferred_platforms' => [],
        ];

        try {
            // Try to get from user preferences table/column
            if ($user->preferences) {
                $userPrefs = is_string($user->preferences) ?
                    json_decode($user->preferences, TRUE) :
                    $user->preferences;

                if (is_array($userPrefs)) {
                    $preferences = array_merge($preferences, $userPrefs);
                }
            }
        } catch (Exception $e) {
            Log::warning('Could not load user preferences: ' . $e->getMessage());
        }

        return $preferences;
    }

    /**
     * Get user behavior data for recommendations
     */
    private function getUserBehaviorData(User $user): array
    {
        $behaviorData = [
            'viewed_tickets'    => [],
            'favorite_sports'   => [],
            'price_sensitivity' => 'medium',
            'activity_level'    => 'medium',
        ];

        try {
            // Get recently viewed tickets (if tracking exists)
            // This would be implemented with proper user activity tracking

            // Get alerts data to understand preferences
            $alerts = $user->ticketAlerts()->active()->take(20)->get();

            foreach ($alerts as $alert) {
                if ($alert->criteria) {
                    $criteria = is_string($alert->criteria) ?
                        json_decode($alert->criteria, TRUE) :
                        $alert->criteria;

                    if (isset($criteria['sport'])) {
                        $behaviorData['favorite_sports'][] = $criteria['sport'];
                    }

                    if (isset($criteria['max_price'])) {
                        // Determine price sensitivity
                        if ($criteria['max_price'] < 100) {
                            $behaviorData['price_sensitivity'] = 'low';
                        } elseif ($criteria['max_price'] > 300) {
                            $behaviorData['price_sensitivity'] = 'high';
                        }
                    }
                }
            }

            // Clean up favorite sports
            $behaviorData['favorite_sports'] = array_unique($behaviorData['favorite_sports']);
        } catch (Exception $e) {
            Log::warning('Could not load user behavior data: ' . $e->getMessage());
        }

        return $behaviorData;
    }

    /**
     * Get base tickets for generating recommendations
     */
    private function getBaseTicketsForRecommendations(): Collection
    {
        try {
            return ScrapedTicket::where('active', TRUE)
                ->where('price', '>', 0)
                ->where('event_date', '>=', now())
                ->orderBy('created_at', 'desc')
                ->take(100) // Limit for performance
                ->get();
        } catch (Exception $e) {
            Log::error('Failed to get base tickets: ' . $e->getMessage());

            return collect();
        }
    }

    /**
     * Calculate recommendation score for a ticket based on user data
     */
    private function calculateRecommendationScore(ScrapedTicket $ticket, array $preferences, array $behaviorData): float
    {
        $score = 0.0;
        $factors = [];

        // Sport preference matching
        if (!empty($behaviorData['favorite_sports'])) {
            $ticketSport = $this->extractSportFromTitle($ticket->title);
            if (in_array($ticketSport, $behaviorData['favorite_sports'], TRUE)) {
                $factors['sport_match'] = 0.4;
            }
        }

        // Price range matching
        if (isset($preferences['price_range'])) {
            $priceScore = $this->calculatePriceScore(
                $ticket->price,
                $preferences['price_range']['min'],
                $preferences['price_range']['max'],
            );
            $factors['price_match'] = $priceScore * 0.3;
        }

        // Venue/location matching
        if (!empty($preferences['venues']) && $ticket->venue) {
            if (in_array($ticket->venue, $preferences['venues'], TRUE)) {
                $factors['venue_match'] = 0.2;
            }
        }

        // Platform preference
        if (!empty($preferences['preferred_platforms'])) {
            if (in_array($ticket->platform, $preferences['preferred_platforms'], TRUE)) {
                $factors['platform_match'] = 0.1;
            }
        }

        // Recency boost (newer tickets get slight boost)
        $daysSinceCreated = now()->diffInDays($ticket->created_at);
        if ($daysSinceCreated < 7) {
            $factors['recency_boost'] = 0.05 * (7 - $daysSinceCreated) / 7;
        }

        // Event date proximity (events happening soon get boost)
        if ($ticket->event_date) {
            $daysUntilEvent = now()->diffInDays($ticket->event_date);
            if ($daysUntilEvent >= 7 && $daysUntilEvent <= 30) {
                $factors['timing_boost'] = 0.1;
            }
        }

        // Demand indicator boost
        if ($ticket->demand_indicator === 'high') {
            $factors['demand_boost'] = 0.15;
        } elseif ($ticket->demand_indicator === 'medium') {
            $factors['demand_boost'] = 0.05;
        }

        // Calculate final score
        $score = array_sum($factors);

        // Cap at 1.0
        return min($score, 1.0);
    }

    /**
     * Calculate price matching score
     */
    private function calculatePriceScore(float $ticketPrice, float $minPrice, float $maxPrice): float
    {
        if ($ticketPrice < $minPrice || $ticketPrice > $maxPrice) {
            return 0.0;
        }

        // Perfect score if in the middle 50% of range
        $range = $maxPrice - $minPrice;
        $midPoint = $minPrice + ($range / 2);
        $quarterRange = $range / 4;

        if ($ticketPrice >= ($midPoint - $quarterRange) && $ticketPrice <= ($midPoint + $quarterRange)) {
            return 1.0;
        }

        // Partial score for being in range but not ideal
        return 0.7;
    }

    /**
     * Extract sport from ticket title
     */
    private function extractSportFromTitle(string $title): string
    {
        $title = strtolower($title);

        $sports = [
            'football'   => ['football', 'nfl', 'soccer', 'futbol'],
            'basketball' => ['basketball', 'nba', 'ncaa basketball'],
            'baseball'   => ['baseball', 'mlb', 'world series'],
            'hockey'     => ['hockey', 'nhl', 'ice hockey'],
            'tennis'     => ['tennis', 'wimbledon', 'us open tennis'],
            'golf'       => ['golf', 'pga', 'masters'],
            'racing'     => ['racing', 'nascar', 'f1', 'formula'],
            'boxing'     => ['boxing', 'fight', 'mma', 'ufc'],
            'wrestling'  => ['wrestling', 'wwe', 'wwf'],
            'concerts'   => ['concert', 'tour', 'live music'],
        ];

        foreach ($sports as $sport => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($title, $keyword)) {
                    return $sport;
                }
            }
        }

        return 'other';
    }

    /**
     * Get match reason for display
     */
    private function getMatchReason(ScrapedTicket $ticket, array $preferences, array $behaviorData): string
    {
        $reasons = [];

        $sport = $this->extractSportFromTitle($ticket->title);
        if (in_array($sport, $behaviorData['favorite_sports'] ?? [], TRUE)) {
            $reasons[] = "Matches your interest in {$sport}";
        }

        if ($ticket->demand_indicator === 'high') {
            $reasons[] = 'High demand event';
        }

        if (!empty($preferences['preferred_platforms'])
            && in_array($ticket->platform, $preferences['preferred_platforms'], TRUE)) {
            $reasons[] = 'From your preferred platform';
        }

        if (empty($reasons)) {
            $reasons[] = 'Popular event in your area';
        }

        return implode(' • ', $reasons);
    }

    /**
     * Calculate priority level
     */
    private function calculatePriority(float $score, ScrapedTicket $ticket): string
    {
        if ($score > 0.8) {
            return 'high';
        }
        if ($score > 0.6) {
            return 'medium';
        }

        return 'low';
    }
}
