<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MarketingDashboardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use function dirname;
use function in_array;
use function is_array;
use function strlen;

/**
 * Marketing & Dashboard Controller
 *
 * Handles comprehensive dashboard and marketing analytics including:
 * - Real-time dashboard overview and metrics
 * - User engagement and performance analytics
 * - Revenue analytics and business intelligence
 * - Marketing campaign management and insights
 * - Platform health monitoring and alerts
 */
class MarketingDashboardController extends Controller
{
    private MarketingDashboardService $dashboardService;

    public function __construct(MarketingDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        $this->middleware('auth');
    }

    /**
     * Get dashboard overview for current user
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $user = Auth::user();
            $dashboard = $this->dashboardService->getDashboardOverview($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $dashboard,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get user dashboard', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load dashboard data',
            ], 500);
        }
    }

    /**
     * Get admin dashboard overview (admin only)
     */
    public function getAdminDashboard(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has admin role
            if (!$user->hasRole('admin')) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $dashboard = $this->dashboardService->getDashboardOverview();

            return response()->json([
                'success' => TRUE,
                'data'    => $dashboard,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get admin dashboard', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load admin dashboard',
            ], 500);
        }
    }

    /**
     * Get real-time analytics data
     */
    public function getRealTimeAnalytics(): JsonResponse
    {
        try {
            $analytics = $this->dashboardService->getRealTimeAnalytics();

            return response()->json([
                'success' => TRUE,
                'data'    => $analytics,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get real-time analytics', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load real-time analytics',
            ], 500);
        }
    }

    /**
     * Get user engagement report
     */
    public function getUserEngagementReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'days' => 'nullable|integer|min:1|max:365',
            ]);

            $days = $validated['days'] ?? 30;
            $report = $this->dashboardService->getUserEngagementReport($days);

            return response()->json([
                'success' => TRUE,
                'data'    => $report,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get user engagement report', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate engagement report',
            ], 500);
        }
    }

    /**
     * Get revenue analytics report
     */
    public function getRevenueReport(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has admin role
            if (!$user->hasRole('admin')) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $validated = $request->validate([
                'months' => 'nullable|integer|min:1|max:24',
            ]);

            $months = $validated['months'] ?? 12;
            $report = $this->dashboardService->getRevenueReport($months);

            return response()->json([
                'success' => TRUE,
                'data'    => $report,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get revenue report', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate revenue report',
            ], 500);
        }
    }

    /**
     * Get marketing campaign analytics
     */
    public function getCampaignAnalytics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has admin or marketing role
            if (!$user->hasRole(['admin', 'marketing'])) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $validated = $request->validate([
                'campaign_id' => 'nullable|string',
            ]);

            $analytics = $this->dashboardService->getCampaignAnalytics(
                $validated['campaign_id'] ?? NULL,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => $analytics,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get campaign analytics', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load campaign analytics',
            ], 500);
        }
    }

    /**
     * Get marketing insights and recommendations
     */
    public function getMarketingInsights(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has admin or marketing role
            if (!$user->hasRole(['admin', 'marketing'])) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $insights = $this->dashboardService->getMarketingInsights();

            return response()->json([
                'success' => TRUE,
                'data'    => $insights,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get marketing insights', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load marketing insights',
            ], 500);
        }
    }

    /**
     * Get platform statistics summary
     */
    public function getPlatformStats(): JsonResponse
    {
        try {
            $stats = Cache::remember('platform_stats', 600, function () {
                return [
                    'users' => [
                        'total'         => User::count(),
                        'active_today'  => User::where('last_activity_at', '>=', now()->startOfDay())->count(),
                        'new_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                        'growth_rate'   => $this->calculateUserGrowthRate(),
                    ],
                    'subscriptions' => [
                        'total_active'    => Subscription::active()->count(),
                        'monthly_revenue' => Payment::where('status', 'succeeded')
                            ->where('created_at', '>=', now()->startOfMonth())
                            ->sum('amount'),
                        'plan_distribution' => $this->getPlanDistribution(),
                    ],
                    'events' => [
                        'total_events'     => Event::count(),
                        'monitored_events' => Event::whereHas('monitors', function ($q): void {
                            $q->where('is_active', TRUE);
                        })->count(),
                        'recent_additions' => Event::where('created_at', '>=', now()->subWeek())->count(),
                    ],
                    'system' => [
                        'uptime'             => '99.9%',
                        'response_time'      => '145ms',
                        'api_requests_today' => $this->getAPIRequestsToday(),
                        'last_updated'       => now()->toISOString(),
                    ],
                ];
            });

            return response()->json([
                'success' => TRUE,
                'data'    => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get platform stats', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load platform statistics',
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period'      => 'nullable|string|in:hour,day,week,month',
                'metric_type' => 'nullable|string|in:response_time,throughput,errors,uptime',
            ]);

            $period = $validated['period'] ?? 'day';
            $metricType = $validated['metric_type'] ?? 'all';

            $metrics = $this->getPerformanceData($period, $metricType);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'period'      => $period,
                    'metric_type' => $metricType,
                    'metrics'     => $metrics,
                    'timestamp'   => now()->toISOString(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load performance metrics',
            ], 500);
        }
    }

    /**
     * Export dashboard data
     */
    public function exportDashboardData(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'format'           => 'required|string|in:json,csv,xlsx',
                'data_type'        => 'required|string|in:user,admin,revenue,engagement',
                'date_range'       => 'nullable|array',
                'date_range.start' => 'nullable|date',
                'date_range.end'   => 'nullable|date|after_or_equal:date_range.start',
            ]);

            // Check permissions based on data type
            if (in_array($validated['data_type'], ['admin', 'revenue'], TRUE) && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $exportData = $this->generateExportData($validated, $user);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'download_url'  => $this->createExportFile($exportData, $validated['format']),
                    'file_size'     => strlen(json_encode($exportData)),
                    'records_count' => $this->countExportRecords($exportData),
                    'generated_at'  => now()->toISOString(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to export dashboard data', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export dashboard data',
            ], 500);
        }
    }

    /**
     * Get user activity timeline
     */
    public function getUserActivityTimeline(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id'          => 'nullable|integer|exists:users,id',
                'days'             => 'nullable|integer|min:1|max:90',
                'activity_types'   => 'nullable|array',
                'activity_types.*' => 'string|in:login,purchase,monitor,alert,subscription',
            ]);

            $user = Auth::user();
            $targetUserId = $validated['user_id'] ?? $user->id;

            // If requesting another user's data, check admin permission
            if ($targetUserId !== $user->id && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $days = $validated['days'] ?? 30;
            $activityTypes = $validated['activity_types'] ?? ['login', 'purchase', 'monitor', 'alert', 'subscription'];

            $timeline = $this->getUserActivityData($targetUserId, $days, $activityTypes);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'user_id'        => $targetUserId,
                    'period_days'    => $days,
                    'activity_types' => $activityTypes,
                    'timeline'       => $timeline,
                    'summary'        => $this->getActivitySummary($timeline),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get user activity timeline', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load user activity timeline',
            ], 500);
        }
    }

    // Private helper methods

    private function calculateUserGrowthRate(): float
    {
        $thisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();
        $lastWeek = User::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek(),
        ])->count();

        return $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 2) : 0;
    }

    private function getPlanDistribution(): array
    {
        return Subscription::active()
            ->selectRaw('plan_name, COUNT(*) as count')
            ->groupBy('plan_name')
            ->pluck('count', 'plan_name')
            ->toArray();
    }

    private function getAPIRequestsToday(): int
    {
        return \App\Models\UsageRecord::where('resource_type', 'api_requests')
            ->whereDate('recorded_at', now())
            ->sum('quantity');
    }

    private function getPerformanceData(string $period, string $metricType): array
    {
        // Mock performance data - in production, this would come from monitoring tools
        $timeRange = $this->getTimeRange($period);
        $data = [];

        foreach ($timeRange as $time) {
            $data[$time] = [
                'response_time' => rand(100, 200),
                'throughput'    => rand(800, 1200),
                'error_rate'    => rand(0, 5) / 10,
                'uptime'        => rand(995, 1000) / 10,
            ];
        }

        return $metricType === 'all' ? $data : array_map(fn ($d) => $d[$metricType] ?? 0, $data);
    }

    private function getTimeRange(string $period): array
    {
        $range = [];
        $intervals = match ($period) {
            'hour'  => 24,
            'day'   => 7,
            'week'  => 4,
            'month' => 12,
            default => 7,
        };

        $unit = match ($period) {
            'hour'  => 'hours',
            'day'   => 'days',
            'week'  => 'weeks',
            'month' => 'months',
            default => 'days',
        };

        for ($i = $intervals - 1; $i >= 0; $i--) {
            $time = now()->{"sub{$unit}"}($i);
            $range[] = $time->format($period === 'hour' ? 'H:i' : 'Y-m-d');
        }

        return $range;
    }

    private function generateExportData(array $params, User $user): array
    {
        return match ($params['data_type']) {
            'user'       => $this->dashboardService->getDashboardOverview($user),
            'admin'      => $this->dashboardService->getDashboardOverview(),
            'revenue'    => $this->dashboardService->getRevenueReport(),
            'engagement' => $this->dashboardService->getUserEngagementReport(),
            default      => [],
        };
    }

    private function createExportFile(array $data, string $format): string
    {
        $filename = 'dashboard_export_' . now()->format('Y_m_d_H_i_s') . '.' . $format;
        $path = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0o755, TRUE);
        }

        switch ($format) {
            case 'json':
                file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

                break;
            case 'csv':
                $this->arrayToCsv($data, $path);

                break;
            case 'xlsx':
                // Would require PhpSpreadsheet library
                $this->arrayToXlsx($data, $path);

                break;
        }

        return url('api/downloads/' . $filename);
    }

    private function countExportRecords(array $data): int
    {
        // Count total records in nested array structure
        $count = 0;
        array_walk_recursive($data, function () use (&$count): void {
            $count++;
        });

        return $count;
    }

    private function getUserActivityData(int $userId, int $days, array $activityTypes): array
    {
        // This would fetch actual activity data from activity logs
        $activities = [];
        $startDate = now()->subDays($days);

        // Mock activity data
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $activities[$date] = [];

            foreach ($activityTypes as $type) {
                $count = rand(0, 5);
                if ($count > 0) {
                    $activities[$date][] = [
                        'type'    => $type,
                        'count'   => $count,
                        'details' => $this->getActivityDetails($type, $count),
                    ];
                }
            }
        }

        return $activities;
    }

    private function getActivityDetails(string $type, int $count): array
    {
        return match ($type) {
            'login'        => ['sessions' => $count, 'avg_duration' => rand(300, 1800)],
            'purchase'     => ['tickets' => $count, 'total_amount' => rand(50, 500)],
            'monitor'      => ['events_added' => $count],
            'alert'        => ['notifications_sent' => $count],
            'subscription' => ['plan_changes' => $count],
            default        => [],
        };
    }

    private function getActivitySummary(array $timeline): array
    {
        $summary = [
            'total_days_active'  => 0,
            'most_active_day'    => NULL,
            'activity_breakdown' => [],
        ];

        foreach ($timeline as $date => $activities) {
            if (!empty($activities)) {
                $summary['total_days_active']++;

                $dailyTotal = array_sum(array_column($activities, 'count'));
                if (!$summary['most_active_day'] || $dailyTotal > $summary['most_active_day']['total']) {
                    $summary['most_active_day'] = ['date' => $date, 'total' => $dailyTotal];
                }

                foreach ($activities as $activity) {
                    $type = $activity['type'];
                    $summary['activity_breakdown'][$type] =
                        ($summary['activity_breakdown'][$type] ?? 0) + $activity['count'];
                }
            }
        }

        return $summary;
    }

    private function arrayToCsv(array $data, string $path): void
    {
        $file = fopen($path, 'w');

        // Flatten the array for CSV format
        $flattened = $this->flattenArray($data);

        if (!empty($flattened)) {
            // Write headers
            fputcsv($file, array_keys($flattened[0]));

            // Write data
            foreach ($flattened as $row) {
                fputcsv($file, $row);
            }
        }

        fclose($file);
    }

    private function arrayToXlsx(array $data, string $path): void
    {
        // Placeholder for Excel export - would require PhpSpreadsheet
        file_put_contents($path, json_encode($data));
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return [$result];
    }
}
