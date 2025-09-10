<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Purchase\Models\TicketPurchase;
use App\Models\User;
use App\Services\Analytics\UserBehaviorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;
use function in_array;

/**
 * HD Tickets AI-Powered Recommendation Engine
 *
 * Comprehensive recommendation system that analyzes user behavior, preferences,
 * and market trends to provide personalized sports event recommendations,
 * optimal pricing strategies, and intelligent alert settings.
 *
 * Features:
 * - Machine Learning-based event recommendations
 * - Dynamic pricing optimization suggestions
 * - Behavioral pattern analysis
 * - Collaborative filtering with similar users
 * - Real-time recommendation updates
 * - A/B testing for recommendation strategies
 *
 * @version 1.0.0
 */
class RecommendationEngineService
{
    // Recommendation weights and thresholds
    private const array WEIGHTS = [
        'user_preferences'   => 0.25,
        'purchase_history'   => 0.20,
        'browsing_behavior'  => 0.15,
        'similar_users'      => 0.15,
        'trending_events'    => 0.10,
        'seasonal_patterns'  => 0.08,
        'location_proximity' => 0.07,
    ];

    private array $config;

    public function __construct(
        private UserBehaviorService $userBehaviorService,
    ) {
        $this->config = config('recommendations', [
            'cache_ttl'               => 3600, // 1 hour
            'max_recommendations'     => 20,
            'min_confidence_score'    => 0.3,
            'enable_ml_features'      => TRUE,
            'collaborative_threshold' => 0.7,
        ]);
    }

    /**
     * Generate comprehensive personalized recommendations for a user
     */
    public function generateRecommendations(User $user, array $options = []): array
    {
        $cacheKey = "recommendations:user:{$user->id}:" . md5(serialize($options));

        return Cache::remember($cacheKey, $this->config['cache_ttl'], function () use ($user, $options): array {
            Log::info("Generating recommendations for user {$user->id}", $options);

            try {
                $startTime = microtime(TRUE);

                // Gather user context and preferences
                $userContext = $this->buildUserContext($user);

                // Generate different types of recommendations
                $recommendations = [
                    'events'  => $this->recommendEvents($user, $userContext, $options),
                    'pricing' => $this->recommendPricingStrategies($user, $userContext),
                    'alerts'  => $this->recommendAlertSettings($user, $userContext),
                    'teams'   => $this->recommendTeams($user, $userContext),
                    'venues'  => $this->recommendVenues($userContext),
                    'meta'    => [
                        'generated_at'           => now()->toISOString(),
                        'generation_time_ms'     => round((microtime(TRUE) - $startTime) * 1000, 2),
                        'user_context_score'     => $userContext['confidence_score'],
                        'recommendation_version' => '1.0',
                    ],
                ];

                // Apply A/B testing variants if enabled
                if ($this->config['enable_ml_features']) {
                    $recommendations = $this->applyABTestingVariants($user, $recommendations);
                }

                Log::info("Generated recommendations for user {$user->id} in {$recommendations['meta']['generation_time_ms']}ms");

                return $recommendations;
            } catch (Exception $e) {
                Log::error("Failed to generate recommendations for user {$user->id}: " . $e->getMessage());

                return $this->getFallbackRecommendations();
            }
        });
    }

    /**
     * Clear recommendation cache for a user
     */
    public function clearUserCache(User $user): void
    {
        $patterns = [
            "recommendations:user:{$user->id}:*",
            "similar_users:{$user->id}",
            "user_context:{$user->id}",
        ];

        foreach ($patterns as $pattern) {
            Cache::tags(['recommendations', 'user_' . $user->id])->flush();
        }

        Log::info("Cleared recommendation cache for user {$user->id}");
    }

    /**
     * Get recommendation performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'cache_hit_rate'          => $this->calculateCacheHitRate(),
            'average_generation_time' => $this->calculateAverageGenerationTime(),
            'recommendation_accuracy' => $this->calculateRecommendationAccuracy(),
            'user_engagement_rate'    => $this->calculateEngagementRate(),
        ];
    }

    /**
     * Build comprehensive user context for recommendations
     */
    private function buildUserContext(User $user): array
    {
        $behaviorData = $this->userBehaviorService->getUserBehaviorProfile($user->id);
        $purchaseHistory = $this->getPurchaseHistory($user);
        $preferences = $this->getUserPreferences($user);

        return [
            'user_id'              => $user->id,
            'behavior_profile'     => $behaviorData,
            'purchase_history'     => $purchaseHistory,
            'preferences'          => $preferences,
            'demographics'         => $this->getDemographics($user),
            'activity_patterns'    => $this->getActivityPatterns($user),
            'price_sensitivity'    => $this->calculatePriceSensitivity($purchaseHistory),
            'favorite_sports'      => $this->extractFavoriteSports($purchaseHistory),
            'location_preferences' => $this->getLocationPreferences($user, $purchaseHistory),
            'confidence_score'     => $this->calculateContextConfidence($behaviorData, $purchaseHistory, $preferences),
        ];
    }

    /**
     * Generate personalized event recommendations
     */
    private function recommendEvents(User $user, array $userContext, array $options = []): array
    {
        $limit = $options['limit'] ?? $this->config['max_recommendations'];
        $filters = $options['filters'] ?? [];

        // Get base query for available events
        $query = $this->getEventsBaseQuery($filters);

        // Apply different recommendation strategies
        $strategies = [
            'preference_based'         => $this->getPreferenceBasedEvents($query, $userContext),
            'collaborative_filtering'  => $this->getCollaborativeEvents($user, $userContext),
            'trending_events'          => $this->getTrendingEvents($query),
            'seasonal_recommendations' => $this->getSeasonalEvents($query, $userContext),
            'price_optimal'            => $this->getPriceOptimalEvents($query, $userContext),
        ];

        // Merge and score all recommendations
        $allRecommendations = collect();
        foreach ($strategies as $strategyName => $events) {
            foreach ($events as $event) {
                $event['strategy'] = $strategyName;
                $event['base_score'] = $event['score'] ?? 0.5;
                $allRecommendations->push($event);
            }
        }

        // Remove duplicates and calculate final scores
        $finalRecommendations = $this->deduplicateAndScore($allRecommendations, $userContext);

        // Apply business rules and filtering
        $finalRecommendations = $this->applyBusinessRules($finalRecommendations, $user);

        return [
            'recommendations'  => $finalRecommendations->take($limit)->values()->toArray(),
            'total_considered' => $allRecommendations->count(),
            'strategies_used'  => array_keys($strategies),
            'confidence_score' => $this->calculateRecommendationConfidence($finalRecommendations, $userContext),
        ];
    }

    /**
     * Generate pricing strategy recommendations
     */
    private function recommendPricingStrategies(User $user, array $userContext): array
    {
        $priceSensitivity = $userContext['price_sensitivity'];
        $purchaseHistory = $userContext['purchase_history'];

        $strategies = [];

        // Budget-conscious strategy
        if ($priceSensitivity > 0.7) {
            $strategies[] = [
                'type'                 => 'budget_conscious',
                'title'                => 'Budget Saver',
                'description'          => 'Focus on discounted and value tickets',
                'target_discount'      => 0.15,
                'max_price_multiplier' => 0.8,
                'priority_features'    => ['early_bird_discounts', 'group_discounts', 'season_passes'],
                'confidence'           => 0.85,
            ];
        }

        // Premium experience strategy
        if ($priceSensitivity < 0.4 && $this->hasHighValuePurchases($purchaseHistory)) {
            $strategies[] = [
                'type'                 => 'premium_experience',
                'title'                => 'Premium Experience',
                'description'          => 'Priority access to VIP and premium seating',
                'target_discount'      => 0.05,
                'max_price_multiplier' => 1.5,
                'priority_features'    => ['vip_access', 'premium_seating', 'hospitality_packages'],
                'confidence'           => 0.78,
            ];
        }

        // Balanced strategy (default)
        $strategies[] = [
            'type'                 => 'balanced',
            'title'                => 'Smart Value',
            'description'          => 'Balance between price and quality',
            'target_discount'      => 0.10,
            'max_price_multiplier' => 1.2,
            'priority_features'    => ['good_seats', 'reasonable_price', 'popular_events'],
            'confidence'           => 0.70,
        ];

        // Dynamic pricing alerts
        $strategies[] = [
            'type'                   => 'dynamic_alerts',
            'title'                  => 'Price Drop Alerts',
            'description'            => 'Get notified when prices drop for your interests',
            'recommended_thresholds' => $this->calculateOptimalPriceThresholds($userContext),
            'confidence'             => 0.82,
        ];

        return [
            'strategies'                => $strategies,
            'current_price_sensitivity' => $priceSensitivity,
            'recommended_budget_range'  => $this->calculateRecommendedBudget($userContext),
            'savings_potential'         => $this->calculateSavingsPotential($user, $strategies),
        ];
    }

    /**
     * Generate alert setting recommendations
     */
    private function recommendAlertSettings(User $user, array $userContext): array
    {
        $behaviorProfile = $userContext['behavior_profile'];

        $recommendations = [];

        // Optimal alert timing based on user activity patterns
        $activityPatterns = $userContext['activity_patterns'];
        $recommendations['optimal_timing'] = [
            'peak_activity_hours'         => $activityPatterns['peak_hours'] ?? [18, 19, 20],
            'preferred_notification_time' => $this->calculateOptimalNotificationTime($activityPatterns),
            'quiet_hours_suggestion'      => $this->suggestQuietHours($activityPatterns),
            'confidence'                  => 0.75,
        ];

        // Alert frequency recommendations
        $recommendations['frequency'] = [
            'price_alerts'        => $this->recommendAlertFrequency('price', $userContext),
            'availability_alerts' => $this->recommendAlertFrequency('availability', $userContext),
            'event_announcements' => $this->recommendAlertFrequency('announcements', $userContext),
        ];

        // Smart alert filtering
        $recommendations['smart_filters'] = [
            'minimum_savings_threshold'      => $this->calculateMinSavingsThreshold($userContext),
            'exclude_low_probability_events' => $behaviorProfile['risk_tolerance'] ?? 'medium',
            'location_radius_km'             => $this->recommendLocationRadius($userContext),
            'confidence'                     => 0.80,
        ];

        // Personalized alert categories
        $recommendations['categories'] = $this->recommendAlertCategories($user, $userContext);

        return $recommendations;
    }

    /**
     * Recommend teams based on user behavior and preferences
     */
    private function recommendTeams(User $user, array $userContext): array
    {
        $favoriteTeams = $userContext['preferences']['favorite_teams'] ?? [];
        $purchaseHistory = $userContext['purchase_history'];

        // Find teams from purchase history
        $purchasedTeams = $this->extractTeamsFromPurchases($purchaseHistory);

        // Get similar users' favorite teams
        $similarUsers = $this->findSimilarUsers($user, $userContext);
        $collaborativeTeams = $this->getCollaborativeTeams($similarUsers);

        // Get trending teams in user's location/sports
        $trendingTeams = $this->getTrendingTeams($userContext);

        // Combine and score recommendations
        $allTeams = collect()
            ->merge($purchasedTeams->map(fn ($team): array => array_merge($team, ['source' => 'purchase_history', 'score' => 0.9])))
            ->merge($collaborativeTeams->map(fn ($team): array => array_merge($team, ['source' => 'similar_users', 'score' => 0.7])))
            ->merge($trendingTeams->map(fn ($team): array => array_merge($team, ['source' => 'trending', 'score' => 0.6])));

        // Remove already followed teams
        $existingTeamIds = collect($favoriteTeams)->pluck('id');
        $recommendations = $allTeams->reject(fn ($team) => $existingTeamIds->contains($team['id']));

        return [
            'recommendations' => $recommendations->sortByDesc('score')->take(10)->values()->toArray(),
            'reasoning'       => $this->generateTeamRecommendationReasoning($recommendations),
            'confidence'      => 0.72,
        ];
    }

    /**
     * Recommend venues based on user preferences and behavior
     */
    private function recommendVenues(array $userContext): array
    {
        $locationPrefs = $userContext['location_preferences'];
        $purchaseHistory = $userContext['purchase_history'];

        // Venues from purchase history
        $visitedVenues = $this->extractVenuesFromPurchases($purchaseHistory);

        // Popular venues in user's area
        $nearbyVenues = $this->getPopularVenuesNearby($locationPrefs);

        // Venues hosting user's favorite sports/teams
        $relevantVenues = $this->getVenuesForUserInterests($userContext);

        $allVenues = collect()
            ->merge($visitedVenues->map(fn ($venue): array => array_merge($venue, ['source' => 'visited', 'score' => 0.8])))
            ->merge($nearbyVenues->map(fn ($venue): array => array_merge($venue, ['source' => 'nearby', 'score' => 0.6])))
            ->merge($relevantVenues->map(fn ($venue): array => array_merge($venue, ['source' => 'interests', 'score' => 0.7])));

        return [
            'recommendations' => $allVenues->unique('id')->sortByDesc('score')->take(8)->values()->toArray(),
            'location_based'  => $nearbyVenues->count(),
            'interest_based'  => $relevantVenues->count(),
            'confidence'      => 0.68,
        ];
    }

    /**
     * Find users with similar behavior patterns for collaborative filtering
     */
    private function findSimilarUsers(User $user, array $userContext): Collection
    {
        $cacheKey = "similar_users:{$user->id}";

        return Cache::remember($cacheKey, 1800, function () use ($user, $userContext) {
            $userVector = $this->buildUserVector($userContext);

            // Get candidate users (users with some activity in the last 6 months)
            $candidates = User::whereHas('ticketPurchases', function ($query): void {
                $query->where('created_at', '>=', Carbon::now()->subMonths(6));
            })
                ->where('id', '!=', $user->id)
                ->limit(1000)
                ->get();

            $similarities = [];

            foreach ($candidates as $candidate) {
                $candidateContext = $this->buildUserContext($candidate);
                $candidateVector = $this->buildUserVector($candidateContext);

                $similarity = $this->calculateCosineSimilarity($userVector, $candidateVector);

                if ($similarity >= $this->config['collaborative_threshold']) {
                    $similarities[] = [
                        'user'       => $candidate,
                        'similarity' => $similarity,
                        'context'    => $candidateContext,
                    ];
                }
            }

            return collect($similarities)
                ->sortByDesc('similarity')
                ->take(20);
        });
    }

    /**
     * Build user feature vector for similarity calculations
     */
    private function buildUserVector(array $userContext): array
    {
        $vector = [];

        // Sports preferences (one-hot encoding)
        $allSports = ['football', 'basketball', 'baseball', 'hockey', 'soccer', 'tennis', 'golf'];
        foreach ($allSports as $sport) {
            $vector[] = in_array($sport, $userContext['favorite_sports'] ?? [], TRUE) ? 1 : 0;
        }

        // Price sensitivity
        $vector[] = $userContext['price_sensitivity'] ?? 0.5;

        // Activity level (normalized)
        $activityScore = ($userContext['behavior_profile']['session_count'] ?? 0) / 100;
        $vector[] = min($activityScore, 1.0);

        // Purchase frequency (normalized)
        $purchaseScore = count($userContext['purchase_history']) / 10;
        $vector[] = min($purchaseScore, 1.0);

        // Location preferences (simplified)
        $vector[] = empty($userContext['location_preferences']['primary_city']) ? 0 : 1;

        return $vector;
    }

    /**
     * Calculate cosine similarity between two user vectors
     */
    private function calculateCosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;
        $counter = count($vectorA);

        for ($i = 0; $i < $counter; $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA === 0 || $magnitudeB === 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Get user's purchase history with analysis
     */
    private function getPurchaseHistory(User $user): array
    {
        return TicketPurchase::where('user_id', $user->id)
            ->with(['ticket.event'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($purchase): array => [
                'id'         => $purchase->id,
                'event_name' => $purchase->ticket->event->name ?? 'Unknown',
                'sport'      => $purchase->ticket->event->sport ?? NULL,
                'venue'      => $purchase->ticket->event->venue ?? NULL,
                'price'      => $purchase->total_amount,
                'date'       => $purchase->created_at,
                'event_date' => $purchase->ticket->event->date ?? NULL,
            ])->toArray();
    }

    /**
     * Calculate user's price sensitivity based on purchase patterns
     */
    private function calculatePriceSensitivity(array $purchaseHistory): float
    {
        if ($purchaseHistory === []) {
            return 0.5; // Default neutral sensitivity
        }

        $prices = collect($purchaseHistory)->pluck('price');
        $avgPrice = $prices->avg();
        $maxPrice = $prices->max();

        // Lower ratio = higher price sensitivity
        $priceVariability = $prices->std() / max($avgPrice, 1);
        $priceRange = ($maxPrice - $prices->min()) / max($avgPrice, 1);

        // Normalize to 0-1 scale (1 = very price sensitive)
        $sensitivity = 1 - min(($priceVariability + $priceRange) / 4, 1);

        return round($sensitivity, 2);
    }

    /**
     * Extract user's favorite sports from behavior and purchase data
     */
    private function extractFavoriteSports(array $purchaseHistory): array
    {
        $sportsCount = [];

        // Count from purchase history
        foreach ($purchaseHistory as $purchase) {
            if ($purchase['sport']) {
                $sportsCount[$purchase['sport']] = ($sportsCount[$purchase['sport']] ?? 0) + 1;
            }
        }

        // Sort by frequency and return top sports
        arsort($sportsCount);

        return array_keys(array_slice($sportsCount, 0, 5));
    }

    /**
     * Apply A/B testing variants to recommendations
     */
    private function applyABTestingVariants(User $user, array $recommendations): array
    {
        $variant = $this->getUserABVariant($user);

        switch ($variant) {
            case 'personalized_heavy':
                // Increase weight for personal preferences
                $recommendations['events']['recommendations'] = $this->boostPersonalizedResults(
                    $recommendations['events']['recommendations'],
                    0.2,
                );

                break;
            case 'collaborative_heavy':
                // Increase weight for collaborative filtering
                $recommendations['events']['recommendations'] = $this->boostCollaborativeResults(
                    $recommendations['events']['recommendations'],
                    0.15,
                );

                break;
            case 'trending_focus':
                // Boost trending events
                $recommendations['events']['recommendations'] = $this->boostTrendingResults(
                    $recommendations['events']['recommendations'],
                    0.1,
                );

                break;
        }

        $recommendations['meta']['ab_variant'] = $variant;

        return $recommendations;
    }

    /**
     * Get fallback recommendations for error cases
     */
    private function getFallbackRecommendations(): array
    {
        return [
            'events' => [
                'recommendations' => $this->getPopularEvents()->take(10)->toArray(),
                'fallback'        => TRUE,
            ],
            'pricing' => [
                'strategies' => [
                    [
                        'type'        => 'balanced',
                        'title'       => 'Smart Value',
                        'description' => 'Balance between price and quality',
                        'confidence'  => 0.5,
                    ],
                ],
            ],
            'alerts' => [
                'optimal_timing' => ['peak_activity_hours' => [18, 19, 20]],
                'frequency'      => ['price_alerts' => 'daily'],
            ],
            'teams'  => ['recommendations' => []],
            'venues' => ['recommendations' => []],
            'meta'   => [
                'generated_at' => now()->toISOString(),
                'fallback'     => TRUE,
                'error'        => 'Failed to generate personalized recommendations',
            ],
        ];
    }

    /**
     * Calculate optimal price thresholds for alerts
     */
    private function calculateOptimalPriceThresholds(array $userContext): array
    {
        $priceSensitivity = $userContext['price_sensitivity'];
        $purchaseHistory = $userContext['purchase_history'];

        if (empty($purchaseHistory)) {
            return [
                'small_drop'  => 0.05,  // 5%
                'medium_drop' => 0.15, // 15%
                'large_drop'  => 0.25,   // 25%
            ];
        }

        collect($purchaseHistory)->pluck('price')->avg();

        // Adjust thresholds based on price sensitivity
        $multiplier = $priceSensitivity * 0.5 + 0.5; // 0.5 to 1.0

        return [
            'small_drop'  => round(0.03 * $multiplier, 3),
            'medium_drop' => round(0.12 * $multiplier, 3),
            'large_drop'  => round(0.20 * $multiplier, 3),
        ];
    }

    /**
     * Get base query for events with common filters
     */
    private function getEventsBaseQuery(array $filters): Builder
    {
        $query = DB::table('scraped_tickets')
            ->select([
                'id',
                'title',
                'sport',
                'location',
                'platform',
                'price',
                'event_date',
                'created_at',
                'updated_at',
            ])
            ->where('status', 'active')
            ->where('event_date', '>', now());

        if (! empty($filters['sports'])) {
            $query->whereIn('sport', $filters['sports']);
        }

        if (! empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query;
    }

    /**
     * Get preference-based event recommendations
     */
    private function getPreferenceBasedEvents(Builder $query, array $userContext): Collection
    {
        $favoriteSports = $userContext['favorite_sports'];
        $locationPrefs = $userContext['location_preferences'];

        $preferenceQuery = clone $query;

        if (! empty($favoriteSports)) {
            $preferenceQuery->whereIn('sport', $favoriteSports);
        }

        if (! empty($locationPrefs['primary_city'])) {
            $preferenceQuery->where('location', 'like', '%' . $locationPrefs['primary_city'] . '%');
        }

        return $preferenceQuery->limit(15)
            ->get()
            ->map(fn ($event): array => array_merge((array) $event, ['score' => 0.85]));
    }

    /**
     * Get collaborative filtering recommendations
     */
    private function getCollaborativeEvents(User $user, array $userContext): Collection
    {
        $similarUsers = $this->findSimilarUsers($user, $userContext);

        if ($similarUsers->isEmpty()) {
            return collect();
        }

        $recommendedEvents = collect();

        foreach ($similarUsers as $similarUserData) {
            $similarUser = $similarUserData['user'];
            $recentPurchases = TicketPurchase::where('user_id', $similarUser->id)
                ->where('created_at', '>=', Carbon::now()->subMonths(3))
                ->with(['ticket'])
                ->limit(5)
                ->get();

            foreach ($recentPurchases as $purchase) {
                if ($purchase->ticket && $purchase->ticket->event_date > now()) {
                    $event = (array) $purchase->ticket;
                    $event['score'] = 0.7 * $similarUserData['similarity'];
                    $recommendedEvents->push($event);
                }
            }
        }

        return $recommendedEvents->take(10);
    }

    /**
     * Get currently trending events
     */
    private function getTrendingEvents(Builder $query): Collection
    {
        // Get events with high recent activity
        $trendingQuery = clone $query;

        return $trendingQuery->leftJoin('ticket_purchases', 'scraped_tickets.id', '=', 'ticket_purchases.ticket_id')
            ->select('scraped_tickets.*', DB::raw('COUNT(ticket_purchases.id) as purchase_count'))
            ->where('ticket_purchases.created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('scraped_tickets.id')
            ->orderBy('purchase_count', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($event): array => array_merge((array) $event, ['score' => 0.65]));
    }

    /**
     * Remove duplicate events and calculate final scores
     */
    private function deduplicateAndScore(Collection $events, array $userContext): Collection
    {
        return $events->groupBy('id')
            ->map(function ($duplicates) {
                $event = $duplicates->first();

                // Calculate weighted average score
                $totalWeight = 0;
                $weightedScore = 0;

                foreach ($duplicates as $duplicate) {
                    $weight = self::WEIGHTS[$duplicate['strategy']] ?? 0.1;
                    $weightedScore += $duplicate['base_score'] * $weight;
                    $totalWeight += $weight;
                }

                $event['final_score'] = $totalWeight > 0 ? $weightedScore / $totalWeight : 0.5;
                $event['recommendation_reasons'] = $duplicates->pluck('strategy')->unique()->toArray();

                return $event;
            })
            ->filter(fn ($event): bool => $event['final_score'] >= $this->config['min_confidence_score'])
            ->sortByDesc('final_score');
    }

    /**
     * Apply business rules to filter recommendations
     */
    private function applyBusinessRules(Collection $recommendations, User $user): Collection
    {
        return $recommendations->filter(function ($event) use ($user): bool {
            // Filter out events that are too expensive for price-sensitive users
            if (isset($event['price'])) {
                $userBudget = $this->estimateUserBudget($user);
                if ($event['price'] > $userBudget * 2) {
                    return FALSE;
                }
            }

            // Filter out sold out events
            return ! (isset($event['availability']) && $event['availability'] === 'sold_out');
        });
    }

    /**
     * Estimate user's budget based on purchase history
     */
    private function estimateUserBudget(User $user): float
    {
        $recentPurchases = TicketPurchase::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->pluck('total_amount');

        if ($recentPurchases->isEmpty()) {
            return 100.0; // Default budget
        }

        return $recentPurchases->avg() * 1.2; // 20% above average
    }

    /**
     * Get A/B testing variant for user
     */
    private function getUserABVariant(User $user): string
    {
        $hash = md5($user->id . 'ab_test');
        $hashValue = hexdec(substr($hash, 0, 8));
        $variant = $hashValue % 4;

        return match($variant) {
            0       => 'control',
            1       => 'personalized_heavy',
            2       => 'collaborative_heavy',
            3       => 'trending_focus',
            default => 'control',
        };
    }

    // Additional private helper methods would be implemented here...
    // This includes methods like calculateCacheHitRate(), getPopularEvents(),
    // extractTeamsFromPurchases(), etc.
}
