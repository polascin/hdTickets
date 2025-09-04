<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProfileAnalyticsService
{
    /**
     * Get comprehensive profile analytics
     */
    public function getAnalytics(User $user): array
    {
        return Cache::remember("profile_analytics_{$user->id}", 300, function () use ($user) {
            return [
                'activity_metrics'     => $this->getActivityMetrics($user),
                'engagement_stats'     => $this->getEngagementStats($user),
                'performance_insights' => $this->getPerformanceInsights($user),
                'trends'               => $this->getTrends($user),
                'recommendations'      => $this->getRecommendations($user),
            ];
        });
    }

    /**
     * Get activity metrics
     */
    private function getActivityMetrics(User $user): array
    {
        $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : NULL;
        $daysSinceLastLogin = $lastLogin ? $lastLogin->diffInDays(now()) : NULL;

        return [
            'last_login'             => $lastLogin?->format('Y-m-d H:i:s'),
            'days_since_last_login'  => $daysSinceLastLogin,
            'login_frequency'        => $this->calculateLoginFrequency($user),
            'session_duration_avg'   => $this->getAverageSessionDuration($user),
            'active_days_this_month' => $this->getActiveDaysThisMonth($user),
            'peak_activity_hours'    => $this->getPeakActivityHours($user),
        ];
    }

    /**
     * Get engagement statistics
     */
    private function getEngagementStats(User $user): array
    {
        return [
            'profile_views'     => $this->getProfileViews($user),
            'profile_updates'   => $this->getProfileUpdates($user),
            'feature_usage'     => $this->getFeatureUsage($user),
            'interaction_score' => $this->calculateInteractionScore($user),
        ];
    }

    /**
     * Get performance insights
     */
    private function getPerformanceInsights(User $user): array
    {
        return [
            'response_time_avg'  => $this->getAverageResponseTime($user),
            'error_rate'         => $this->getErrorRate($user),
            'success_rate'       => $this->getSuccessRate($user),
            'optimization_score' => $this->calculateOptimizationScore($user),
        ];
    }

    /**
     * Get trends data
     */
    private function getTrends(User $user): array
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo) use ($user) {
            $date = now()->subDays($daysAgo);

            return [
                'date'        => $date->format('Y-m-d'),
                'logins'      => $this->getLoginsForDate($user, $date),
                'activities'  => $this->getActivitiesForDate($user, $date),
                'performance' => $this->getPerformanceForDate($user, $date),
            ];
        });

        return [
            'daily_activity'     => $last30Days->toArray(),
            'weekly_summary'     => $this->getWeeklySummary($user),
            'monthly_comparison' => $this->getMonthlyComparison($user),
        ];
    }

    /**
     * Get personalized recommendations
     */
    private function getRecommendations(User $user): array
    {
        $recommendations = [];

        // Profile completion recommendations
        $profileCompletion = $user->getProfileCompletion();
        if ($profileCompletion['percentage'] < 80) {
            $recommendations[] = [
                'type'        => 'profile_completion',
                'priority'    => 'high',
                'title'       => 'Complete Your Profile',
                'description' => 'Your profile is ' . $profileCompletion['percentage'] . '% complete. Complete it to improve your experience.',
                'action'      => 'profile.edit',
                'icon'        => 'fas fa-user-plus',
            ];
        }

        // Security recommendations
        if (! $user->two_factor_secret) {
            $recommendations[] = [
                'type'        => 'security',
                'priority'    => 'high',
                'title'       => 'Enable Two-Factor Authentication',
                'description' => 'Secure your account with two-factor authentication.',
                'action'      => 'profile.security',
                'icon'        => 'fas fa-shield-alt',
            ];
        }

        // Activity recommendations
        $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : NULL;
        if ($lastLogin && $lastLogin->diffInDays(now()) > 7) {
            $recommendations[] = [
                'type'        => 'engagement',
                'priority'    => 'medium',
                'title'       => 'Stay Active',
                'description' => 'You haven\'t been active recently. Check out new features!',
                'action'      => 'dashboard',
                'icon'        => 'fas fa-chart-line',
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate login frequency
     */
    private function calculateLoginFrequency(User $user): string
    {
        // This would typically query login logs
        // For now, return a placeholder
        return 'Daily';
    }

    /**
     * Get average session duration
     */
    private function getAverageSessionDuration(User $user): int
    {
        // This would typically query session logs
        // Return average in minutes
        return rand(15, 120);
    }

    /**
     * Get active days this month
     */
    private function getActiveDaysThisMonth(User $user): int
    {
        // This would typically query activity logs
        return rand(10, 25);
    }

    /**
     * Get peak activity hours
     */
    private function getPeakActivityHours(User $user): array
    {
        // This would typically analyze login/activity logs
        return ['9:00', '14:00', '18:00'];
    }

    /**
     * Get profile views
     */
    private function getProfileViews(User $user): int
    {
        // This would typically query view logs
        return rand(50, 200);
    }

    /**
     * Get profile updates count
     */
    private function getProfileUpdates(User $user): int
    {
        // This would typically query update logs
        return rand(5, 20);
    }

    /**
     * Get feature usage statistics
     */
    private function getFeatureUsage(User $user): array
    {
        return [
            'dashboard' => rand(20, 100),
            'profile'   => rand(10, 50),
            'security'  => rand(5, 25),
            'settings'  => rand(3, 15),
        ];
    }

    /**
     * Calculate interaction score
     */
    private function calculateInteractionScore(User $user): int
    {
        // This would calculate based on various interaction metrics
        return rand(60, 95);
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime(User $user): float
    {
        // This would typically query performance logs
        return round(rand(100, 800) / 1000, 3); // in seconds
    }

    /**
     * Get error rate
     */
    private function getErrorRate(User $user): float
    {
        return round(rand(0, 5) / 100, 3); // percentage
    }

    /**
     * Get success rate
     */
    private function getSuccessRate(User $user): float
    {
        return round((100 - rand(0, 5)) / 100, 3); // percentage
    }

    /**
     * Calculate optimization score
     */
    private function calculateOptimizationScore(User $user): int
    {
        return rand(75, 98);
    }

    /**
     * Get logins for specific date
     */
    private function getLoginsForDate(User $user, Carbon $date): int
    {
        // This would typically query login logs for the specific date
        return rand(0, 5);
    }

    /**
     * Get activities for specific date
     */
    private function getActivitiesForDate(User $user, Carbon $date): int
    {
        // This would typically query activity logs for the specific date
        return rand(0, 20);
    }

    /**
     * Get performance metrics for specific date
     */
    private function getPerformanceForDate(User $user, Carbon $date): array
    {
        return [
            'response_time' => rand(100, 500),
            'error_count'   => rand(0, 3),
            'success_rate'  => rand(95, 100),
        ];
    }

    /**
     * Get weekly summary
     */
    private function getWeeklySummary(User $user): array
    {
        return [
            'total_logins'         => rand(10, 30),
            'total_activities'     => rand(50, 200),
            'avg_session_duration' => rand(20, 60),
            'most_active_day'      => 'Wednesday',
        ];
    }

    /**
     * Get monthly comparison
     */
    private function getMonthlyComparison(User $user): array
    {
        return [
            'current_month' => [
                'logins'          => rand(40, 100),
                'activities'      => rand(200, 500),
                'avg_performance' => rand(85, 98),
            ],
            'previous_month' => [
                'logins'          => rand(35, 95),
                'activities'      => rand(180, 480),
                'avg_performance' => rand(80, 95),
            ],
            'growth' => [
                'logins'      => rand(-10, 20),
                'activities'  => rand(-5, 25),
                'performance' => rand(-2, 8),
            ],
        ];
    }
}
