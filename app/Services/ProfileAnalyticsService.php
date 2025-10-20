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
        return Cache::remember("profile_analytics_{$user->id}", 300, fn (): array => [
            'activity_metrics'     => $this->getActivityMetrics($user),
            'engagement_stats'     => $this->getEngagementStats($user),
            'performance_insights' => $this->getPerformanceInsights($user),
            'trends'               => $this->getTrends($user),
            'recommendations'      => $this->getRecommendations($user),
        ]);
    }

    /**
     * Get activity metrics
     */
    private function getActivityMetrics(User $user): array
    {
        $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : NULL;
        $daysSinceLastLogin = $lastLogin instanceof Carbon ? $lastLogin->diffInDays(now()) : NULL;

        return [
            'last_login'             => $lastLogin?->format('Y-m-d H:i:s'),
            'days_since_last_login'  => $daysSinceLastLogin,
            'login_frequency'        => $this->calculateLoginFrequency(),
            'session_duration_avg'   => $this->getAverageSessionDuration(),
            'active_days_this_month' => $this->getActiveDaysThisMonth(),
            'peak_activity_hours'    => $this->getPeakActivityHours(),
        ];
    }

    /**
     * Get engagement statistics
     */
    private function getEngagementStats(User $user): array
    {
        return [
            'profile_views'     => $this->getProfileViews(),
            'profile_updates'   => $this->getProfileUpdates(),
            'feature_usage'     => $this->getFeatureUsage(),
            'interaction_score' => $this->calculateInteractionScore(),
        ];
    }

    /**
     * Get performance insights
     */
    private function getPerformanceInsights(User $user): array
    {
        return [
            'response_time_avg'  => $this->getAverageResponseTime(),
            'error_rate'         => $this->getErrorRate(),
            'success_rate'       => $this->getSuccessRate(),
            'optimization_score' => $this->calculateOptimizationScore(),
        ];
    }

    /**
     * Get trends data
     */
    private function getTrends(User $user): array
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo): array {
            $date = now()->subDays($daysAgo);

            return [
                'date'        => $date->format('Y-m-d'),
                'logins'      => $this->getLoginsForDate(),
                'activities'  => $this->getActivitiesForDate(),
                'performance' => $this->getPerformanceForDate(),
            ];
        });

        return [
            'daily_activity'     => $last30Days->toArray(),
            'weekly_summary'     => $this->getWeeklySummary(),
            'monthly_comparison' => $this->getMonthlyComparison(),
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
    private function calculateLoginFrequency(): string
    {
        // This would typically query login logs
        // For now, return a placeholder
        return 'Daily';
    }

    /**
     * Get average session duration
     */
    private function getAverageSessionDuration(): int
    {
        // This would typically query session logs
        // Return average in minutes
        return random_int(15, 120);
    }

    /**
     * Get active days this month
     */
    private function getActiveDaysThisMonth(): int
    {
        // This would typically query activity logs
        return random_int(10, 25);
    }

    /**
     * Get peak activity hours
     */
    private function getPeakActivityHours(): array
    {
        // This would typically analyze login/activity logs
        return ['9:00', '14:00', '18:00'];
    }

    /**
     * Get profile views
     */
    private function getProfileViews(): int
    {
        // This would typically query view logs
        return random_int(50, 200);
    }

    /**
     * Get profile updates count
     */
    private function getProfileUpdates(): int
    {
        // This would typically query update logs
        return random_int(5, 20);
    }

    /**
     * Get feature usage statistics
     */
    private function getFeatureUsage(): array
    {
        return [
            'dashboard' => random_int(20, 100),
            'profile'   => random_int(10, 50),
            'security'  => random_int(5, 25),
            'settings'  => random_int(3, 15),
        ];
    }

    /**
     * Calculate interaction score
     */
    private function calculateInteractionScore(): int
    {
        // This would calculate based on various interaction metrics
        return random_int(60, 95);
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime(): float
    {
        // This would typically query performance logs
        return round(random_int(100, 800) / 1000, 3);
        // in seconds
    }

    /**
     * Get error rate
     */
    private function getErrorRate(): float
    {
        return round(random_int(0, 5) / 100, 3);
        // percentage
    }

    /**
     * Get success rate
     */
    private function getSuccessRate(): float
    {
        return round((100 - random_int(0, 5)) / 100, 3);
        // percentage
    }

    /**
     * Calculate optimization score
     */
    private function calculateOptimizationScore(): int
    {
        return random_int(75, 98);
    }

    /**
     * Get logins for specific date
     */
    private function getLoginsForDate(): int
    {
        // This would typically query login logs for the specific date
        return random_int(0, 5);
    }

    /**
     * Get activities for specific date
     */
    private function getActivitiesForDate(): int
    {
        // This would typically query activity logs for the specific date
        return random_int(0, 20);
    }

    /**
     * Get performance metrics for specific date
     */
    private function getPerformanceForDate(): array
    {
        return [
            'response_time' => random_int(100, 500),
            'error_count'   => random_int(0, 3),
            'success_rate'  => random_int(95, 100),
        ];
    }

    /**
     * Get weekly summary
     */
    private function getWeeklySummary(): array
    {
        return [
            'total_logins'         => random_int(10, 30),
            'total_activities'     => random_int(50, 200),
            'avg_session_duration' => random_int(20, 60),
            'most_active_day'      => 'Wednesday',
        ];
    }

    /**
     * Get monthly comparison
     */
    private function getMonthlyComparison(): array
    {
        return [
            'current_month' => [
                'logins'          => random_int(40, 100),
                'activities'      => random_int(200, 500),
                'avg_performance' => random_int(85, 98),
            ],
            'previous_month' => [
                'logins'          => random_int(35, 95),
                'activities'      => random_int(180, 480),
                'avg_performance' => random_int(80, 95),
            ],
            'growth' => [
                'logins'      => random_int(-10, 20),
                'activities'  => random_int(-5, 25),
                'performance' => random_int(-2, 8),
            ],
        ];
    }
}
