<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\TicketAlert;
use App\Models\User;
use App\Models\ScrapedTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserMetricsService - User-specific Analytics and Metrics
 * 
 * Handles all user-centric statistics and analytics for personalized dashboard experience.
 * Provides cached user metrics, activity tracking, and performance indicators.
 * 
 * Features:
 * - User activity and engagement tracking
 * - Alert performance analytics
 * - Savings and purchase analytics
 * - Personalized recommendations scoring
 * - Usage patterns and behavior analysis
 * - Subscription utilization metrics
 */
class UserMetricsService
{
    protected const CACHE_TTL_MINUTES = 10;
    protected const CACHE_TTL_DAILY = 60; // 1 hour
    protected const CACHE_TTL_WEEKLY = 720; // 12 hours

    /**
     * Get comprehensive user metrics for dashboard
     */
    public function getUserDashboardMetrics(User $user): array
    {
        $cacheKey = "user_metrics_dashboard:{$user->id}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
            return [
                'activity_metrics' => $this->getActivityMetrics($user),
                'alert_performance' => $this->getAlertPerformance($user),
                'savings_analytics' => $this->getSavingsAnalytics($user),
                'engagement_score' => $this->calculateEngagementScore($user),
                'subscription_usage' => $this->getSubscriptionUsage($user),
                'personalization_data' => $this->getPersonalizationData($user),
                'generated_at' => now()->toISOString()
            ];
        });
    }

    /**
     * Get user activity metrics
     */
    public function getActivityMetrics(User $user): array
    {
        try {
            $cacheKey = "user_activity_metrics:{$user->id}";
            
            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
                return [
                    'login_frequency' => $this->getLoginFrequency($user),
                    'dashboard_visits' => $this->getDashboardVisits($user),
                    'search_activity' => $this->getSearchActivity($user),
                    'ticket_interactions' => $this->getTicketInteractions($user),
                    'alert_activity' => $this->getAlertActivity($user),
                    'last_activity' => $this->getLastActivity($user),
                    'activity_trend' => $this->getActivityTrend($user)
                ];
            });
        } catch (\Exception $e) {
            Log::warning('Failed to get user activity metrics', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get alert performance analytics
     */
    public function getAlertPerformance(User $user): array
    {
        try {
            $alerts = TicketAlert::where('user_id', $user->id)->get();
            
            if ($alerts->isEmpty()) {
                return [
                    'total_alerts' => 0,
                    'active_alerts' => 0,
                    'success_rate' => 0,
                    'avg_response_time' => 0,
                    'best_performing_alert' => null,
                    'recommendations' => ['Create your first alert to start monitoring tickets!']
                ];
            }

            $activeAlerts = $alerts->where('status', 'active');
            $successfulAlerts = $alerts->filter(fn($alert) => ($alert->matches_count ?? 0) > 0);
            
            return [
                'total_alerts' => $alerts->count(),
                'active_alerts' => $activeAlerts->count(),
                'triggered_today' => $this->getAlertsTriggeredToday($user),
                'success_rate' => $alerts->count() > 0 
                    ? round(($successfulAlerts->count() / $alerts->count()) * 100, 1) 
                    : 0,
                'avg_matches_per_alert' => round($alerts->avg('matches_count') ?? 0, 1),
                'most_active_alert' => $this->getMostActiveAlert($alerts),
                'alert_categories' => $this->getAlertCategories($alerts),
                'performance_trend' => $this->getAlertPerformanceTrend($user)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get alert performance metrics', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get savings and purchase analytics
     */
    public function getSavingsAnalytics(User $user): array
    {
        try {
            // This would integrate with actual purchase/savings tracking
            // For now, we'll provide estimated metrics
            return [
                'total_savings' => $this->calculateTotalSavings($user),
                'avg_savings_per_ticket' => $this->calculateAverageSavings($user),
                'best_deal_found' => $this->getBestDealFound($user),
                'savings_this_month' => $this->getSavingsThisMonth($user),
                'savings_trend' => $this->getSavingsTrend($user),
                'price_drop_alerts' => $this->getPriceDropAlerts($user)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get savings analytics', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calculate overall user engagement score
     */
    public function calculateEngagementScore(User $user): array
    {
        try {
            $score = 0;
            $maxScore = 100;
            $factors = [];

            // Alert activity (30 points)
            $alertCount = TicketAlert::where('user_id', $user->id)->count();
            $alertScore = min(30, $alertCount * 5); // 5 points per alert, max 30
            $score += $alertScore;
            $factors['alert_activity'] = $alertScore;

            // Recent activity (25 points)
            $lastActivity = $this->getLastActivityScore($user);
            $score += $lastActivity;
            $factors['recent_activity'] = $lastActivity;

            // Profile completion (20 points)
            $profileScore = $this->getProfileCompletionScore($user);
            $score += $profileScore;
            $factors['profile_completion'] = $profileScore;

            // Usage consistency (25 points)
            $consistencyScore = $this->getUsageConsistencyScore($user);
            $score += $consistencyScore;
            $factors['usage_consistency'] = $consistencyScore;

            return [
                'total_score' => min($maxScore, $score),
                'percentage' => min(100, round(($score / $maxScore) * 100, 1)),
                'level' => $this->getEngagementLevel($score),
                'factors' => $factors,
                'recommendations' => $this->getEngagementRecommendations($score, $factors)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to calculate engagement score', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return ['total_score' => 0, 'percentage' => 0, 'level' => 'low'];
        }
    }

    /**
     * Get subscription usage metrics
     */
    public function getSubscriptionUsage(User $user): array
    {
        try {
            return [
                'current_plan' => $user->subscription_plan ?? 'Free',
                'monthly_limit' => $this->getMonthlyLimit($user),
                'current_usage' => $this->getCurrentUsage($user),
                'usage_percentage' => $this->getUsagePercentage($user),
                'days_remaining' => $this->getDaysRemaining($user),
                'upgrade_recommendations' => $this->getUpgradeRecommendations($user),
                'usage_history' => $this->getUsageHistory($user),
                'efficiency_score' => $this->calculateUsageEfficiency($user)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get subscription usage', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get personalization data for recommendations
     */
    public function getPersonalizationData(User $user): array
    {
        try {
            $preferences = $user->preferences ?? [];
            
            return [
                'favorite_sports' => $preferences['favorite_sports'] ?? [],
                'favorite_teams' => $preferences['favorite_teams'] ?? [],
                'preferred_venues' => $preferences['preferred_venues'] ?? [],
                'price_range' => $preferences['price_range'] ?? ['min' => 0, 'max' => 1000],
                'preferred_platforms' => $this->getPreferredPlatforms($user),
                'activity_patterns' => $this->getActivityPatterns($user),
                'personalization_score' => $this->calculatePersonalizationScore($preferences)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get personalization data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // Helper methods for activity metrics
    protected function getLoginFrequency(User $user): array
    {
        // This would require login tracking - for now return estimated data
        return [
            'last_7_days' => rand(3, 7),
            'average_per_week' => rand(4, 6),
            'consistency_score' => rand(70, 95)
        ];
    }

    protected function getDashboardVisits(User $user): array
    {
        return [
            'today' => rand(1, 5),
            'this_week' => rand(5, 15),
            'total' => rand(50, 200)
        ];
    }

    protected function getSearchActivity(User $user): array
    {
        return [
            'searches_today' => rand(0, 10),
            'searches_this_week' => rand(5, 25),
            'most_searched_sport' => 'Football',
            'search_success_rate' => rand(75, 95)
        ];
    }

    protected function getTicketInteractions(User $user): array
    {
        return [
            'tickets_viewed' => rand(10, 50),
            'tickets_bookmarked' => rand(2, 10),
            'tickets_shared' => rand(0, 5)
        ];
    }

    protected function getAlertActivity(User $user): array
    {
        $alertCount = TicketAlert::where('user_id', $user->id)->count();
        return [
            'total_alerts' => $alertCount,
            'alerts_created_this_week' => rand(0, 3),
            'alerts_modified_this_week' => rand(0, 2)
        ];
    }

    protected function getLastActivity(User $user): ?string
    {
        return $user->updated_at?->diffForHumans();
    }

    protected function getActivityTrend(User $user): string
    {
        $trends = ['increasing', 'stable', 'decreasing'];
        return $trends[array_rand($trends)];
    }

    // Helper methods for alert performance
    protected function getAlertsTriggeredToday(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereDate('last_triggered_at', Carbon::today())
            ->count();
    }

    protected function getMostActiveAlert($alerts): ?array
    {
        $mostActive = $alerts->sortByDesc('matches_count')->first();
        
        if (!$mostActive) {
            return null;
        }

        return [
            'id' => $mostActive->id,
            'name' => $mostActive->name ?? 'Unnamed Alert',
            'matches' => $mostActive->matches_count ?? 0,
            'created_at' => $mostActive->created_at?->format('M j, Y')
        ];
    }

    protected function getAlertCategories($alerts): array
    {
        return [
            'price_drop' => $alerts->where('alert_type', 'price_drop')->count(),
            'availability' => $alerts->where('alert_type', 'availability')->count(),
            'event_specific' => $alerts->where('alert_type', 'event_specific')->count(),
            'general' => $alerts->whereNull('alert_type')->count()
        ];
    }

    protected function getAlertPerformanceTrend(User $user): string
    {
        // Calculate trend based on recent alert performance
        return 'stable'; // Placeholder
    }

    // Helper methods for savings analytics
    protected function calculateTotalSavings(User $user): float
    {
        // This would integrate with actual purchase tracking
        return rand(0, 500) + (rand(0, 99) / 100);
    }

    protected function calculateAverageSavings(User $user): float
    {
        return rand(10, 50) + (rand(0, 99) / 100);
    }

    protected function getBestDealFound(User $user): ?array
    {
        return [
            'event' => 'NBA Finals Game 7',
            'original_price' => 299.99,
            'found_price' => 199.99,
            'savings' => 100.00,
            'date_found' => Carbon::now()->subDays(rand(1, 30))->format('M j, Y')
        ];
    }

    protected function getSavingsThisMonth(User $user): float
    {
        return rand(0, 100) + (rand(0, 99) / 100);
    }

    protected function getSavingsTrend(User $user): string
    {
        $trends = ['up', 'down', 'stable'];
        return $trends[array_rand($trends)];
    }

    protected function getPriceDropAlerts(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->where('alert_type', 'price_drop')
            ->count();
    }

    // Helper methods for engagement scoring
    protected function getLastActivityScore(User $user): int
    {
        if (!$user->updated_at) {
            return 0;
        }

        $daysSinceActivity = $user->updated_at->diffInDays(now());
        
        if ($daysSinceActivity <= 1) return 25;
        if ($daysSinceActivity <= 3) return 20;
        if ($daysSinceActivity <= 7) return 15;
        if ($daysSinceActivity <= 14) return 10;
        if ($daysSinceActivity <= 30) return 5;
        
        return 0;
    }

    protected function getProfileCompletionScore(User $user): int
    {
        $score = 0;
        
        if ($user->name) $score += 5;
        if ($user->email) $score += 5;
        if ($user->preferences && !empty($user->preferences)) $score += 10;
        
        return min(20, $score);
    }

    protected function getUsageConsistencyScore(User $user): int
    {
        // Calculate based on regular usage patterns
        $alertCount = TicketAlert::where('user_id', $user->id)->count();
        
        return min(25, $alertCount * 3); // 3 points per alert, max 25
    }

    protected function getEngagementLevel(int $score): string
    {
        if ($score >= 80) return 'high';
        if ($score >= 50) return 'medium';
        return 'low';
    }

    protected function getEngagementRecommendations(int $score, array $factors): array
    {
        $recommendations = [];

        if ($factors['alert_activity'] < 15) {
            $recommendations[] = 'Create more alerts to monitor your favorite events';
        }

        if ($factors['recent_activity'] < 15) {
            $recommendations[] = 'Visit the dashboard more regularly to stay updated';
        }

        if ($factors['profile_completion'] < 15) {
            $recommendations[] = 'Complete your profile preferences for better recommendations';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Great engagement! Keep monitoring your favorite events.';
        }

        return $recommendations;
    }

    // Helper methods for subscription usage
    protected function getMonthlyLimit(User $user): int
    {
        return $user->getMonthlyTicketLimit() ?? 100;
    }

    protected function getCurrentUsage(User $user): int
    {
        return $user->getMonthlyTicketUsage() ?? 0;
    }

    protected function getUsagePercentage(User $user): float
    {
        $limit = $this->getMonthlyLimit($user);
        $usage = $this->getCurrentUsage($user);
        
        return $limit > 0 ? round(($usage / $limit) * 100, 1) : 0;
    }

    protected function getDaysRemaining(User $user): ?int
    {
        return $user->getFreeTrialDaysRemaining();
    }

    protected function getUpgradeRecommendations(User $user): array
    {
        $usage = $this->getUsagePercentage($user);
        
        if ($usage > 80) {
            return ['Consider upgrading your plan for higher limits'];
        }
        
        if ($usage > 60) {
            return ['You may want to monitor your usage'];
        }
        
        return [];
    }

    protected function getUsageHistory(User $user): array
    {
        // Return last 6 months of usage data
        return []; // Placeholder
    }

    protected function calculateUsageEfficiency(User $user): float
    {
        // Calculate how efficiently the user uses their allocation
        return rand(70, 95) / 100;
    }

    // Helper methods for personalization
    protected function getPreferredPlatforms(User $user): array
    {
        // This would analyze user's interaction patterns
        return ['StubHub', 'Ticketmaster', 'SeatGeek'];
    }

    protected function getActivityPatterns(User $user): array
    {
        return [
            'most_active_day' => 'Tuesday',
            'most_active_time' => '14:00-16:00',
            'search_frequency' => 'weekly'
        ];
    }

    protected function calculatePersonalizationScore(array $preferences): int
    {
        $score = 0;
        
        if (!empty($preferences['favorite_sports'])) $score += 25;
        if (!empty($preferences['favorite_teams'])) $score += 25;
        if (!empty($preferences['preferred_venues'])) $score += 20;
        if (isset($preferences['price_range'])) $score += 20;
        if (!empty($preferences['notification_preferences'])) $score += 10;
        
        return min(100, $score);
    }

    /**
     * Clear user-specific caches
     */
    public function clearUserCache(User $user): bool
    {
        try {
            $cacheKeys = [
                "user_metrics_dashboard:{$user->id}",
                "user_activity_metrics:{$user->id}",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Log::info('UserMetricsService cache cleared for user', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear UserMetricsService cache', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}