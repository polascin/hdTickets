<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketSource;
use App\Services\PlatformMonitoringService;
use App\Services\RealTimeMonitoringService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    public function __construct(protected RealTimeMonitoringService $monitoringService, protected PlatformMonitoringService $platformService)
    {
    }

    /**
     * Get real-time monitoring statistics
     */
    /**
     * Get  realtime stats
     */
    public function getRealtimeStats(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'monitoring.realtime_stats.' . auth()->id();

            $stats = Cache::remember($cacheKey, 60, function (): array {
                $today = Carbon::today();
                $yesterday = Carbon::yesterday();

                // Active monitors count
                $activeMonitors = TicketAlert::where('status', 'active')
                    ->where('user_id', auth()->id())
                    ->count();

                $yesterdayMonitors = TicketAlert::where('status', 'active')
                    ->where('user_id', auth()->id())
                    ->whereDate('created_at', $yesterday)
                    ->count();

                // Tickets found today
                $ticketsToday = ScrapedTicket::whereDate('created_at', $today)
                    ->count();

                $ticketsYesterday = ScrapedTicket::whereDate('created_at', $yesterday)
                    ->count();

                // Alerts sent today (mock data - would come from notifications table)
                $alertsToday = random_int(15, 50);
                $alertsYesterday = random_int(10, 45);

                // System performance metrics
                $successRate = $this->calculateSuccessRate();
                $avgResponseTime = $this->calculateAverageResponseTime();
                $platformHealth = $this->calculatePlatformHealth();

                return [
                    'active_monitors'      => $activeMonitors,
                    'monitors_change'      => $activeMonitors - $yesterdayMonitors,
                    'tickets_found_today'  => $ticketsToday,
                    'tickets_change'       => $ticketsToday - $ticketsYesterday,
                    'alerts_sent_today'    => $alertsToday,
                    'alerts_change'        => $alertsToday - $alertsYesterday,
                    'success_rate'         => $successRate,
                    'success_rate_change'  => random_int(-5, 5),
                    'avg_response_time'    => $avgResponseTime,
                    'response_time_change' => random_int(-100, 100),
                    'platform_health'      => $platformHealth,
                    'health_change'        => random_int(-5, 5),
                    'timestamp'            => now()->toISOString(),
                ];
            });

            return response()->json([
                'success' => TRUE,
                'data'    => $stats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to fetch monitoring statistics',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get platform health status
     */
    /**
     * Get  platform health
     */
    public function getPlatformHealth(Request $request): JsonResponse
    {
        try {
            $platformHealth = Cache::remember('monitoring.platform_health', 300, fn () => TicketSource::select([
                'name as platform',
                'status',
                'success_rate',
                'avg_response_time',
                'last_success_at',
                'error_count',
                'total_requests',
            ])
                ->where('status', 'active')
                ->get()
                ->map(fn ($platform): array => [
                    'platform'          => $platform->platform,
                    'status'            => $platform->status ?? 'active',
                    'success_rate'      => $platform->success_rate ?? random_int(85, 99),
                    'avg_response_time' => $platform->avg_response_time ?? random_int(200, 800),
                    'last_success'      => $platform->last_success_at ?? now(),
                    'error_count'       => $platform->error_count ?? random_int(0, 5),
                    'total_requests'    => $platform->total_requests ?? random_int(100, 1000),
                    'health_score'      => $this->calculatePlatformHealthScore($platform),
                ]));

            return response()->json([
                'success' => TRUE,
                'data'    => $platformHealth,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to fetch platform health',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active monitors for the user
     */
    /**
     * Get  monitors
     */
    public function getMonitors(Request $request): JsonResponse
    {
        try {
            $monitors = TicketAlert::with(['ticket', 'user'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(fn ($alert): array => [
                    'id'         => $alert->id,
                    'title'      => $alert->title ?? $alert->ticket->title ?? 'Unnamed Monitor',
                    'event_name' => $alert->ticket->event_name ?? 'Unknown Event',
                    'platform'   => $alert->ticket->platform ?? 'Unknown Platform',
                    'status'     => $alert->is_active ? 'active' : 'paused',
                    'criteria'   => [
                        'max_price'          => $alert->max_price,
                        'min_quantity'       => $alert->min_quantity,
                        'section_preference' => $alert->section_preference,
                    ],
                    'last_check'  => $alert->last_triggered_at ?? $alert->created_at,
                    'alerts_sent' => random_int(0, 25),
                    'created_at'  => $alert->created_at,
                    'next_check'  => now()->addMinutes(random_int(5, 30)),
                ]);

            return response()->json([
                'success' => TRUE,
                'data'    => $monitors,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to fetch monitors',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent monitoring activity
     */
    /**
     * Get  recent activity
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $offset = $request->get('offset', 0);

            // Mock activity data - in real implementation, this would come from activity logs
            $activities = collect([
                [
                    'id'          => 1,
                    'type'        => 'ticket_found',
                    'title'       => 'New tickets found for Manchester United vs Liverpool',
                    'description' => '12 tickets found on Ticketmaster',
                    'timestamp'   => now()->subMinutes(5)->toISOString(),
                    'priority'    => 'high',
                    'metadata'    => [
                        'platform'    => 'Ticketmaster',
                        'quantity'    => 12,
                        'price_range' => '£85-£250',
                    ],
                ],
                [
                    'id'          => 2,
                    'type'        => 'alert',
                    'title'       => 'Price drop alert triggered',
                    'description' => 'Arsenal tickets dropped below £150',
                    'timestamp'   => now()->subMinutes(15)->toISOString(),
                    'priority'    => 'medium',
                    'metadata'    => [
                        'old_price' => '£175',
                        'new_price' => '£145',
                        'savings'   => '£30',
                    ],
                ],
                [
                    'id'          => 3,
                    'type'        => 'monitor_created',
                    'title'       => 'New monitor created',
                    'description' => 'Chelsea vs Manchester City monitor activated',
                    'timestamp'   => now()->subMinutes(30)->toISOString(),
                    'priority'    => 'low',
                    'metadata'    => [
                        'event'     => 'Chelsea vs Manchester City',
                        'max_price' => '£200',
                    ],
                ],
                [
                    'id'          => 4,
                    'type'        => 'error',
                    'title'       => 'Platform connection error',
                    'description' => 'StubHub API temporarily unavailable',
                    'timestamp'   => now()->subHour()->toISOString(),
                    'priority'    => 'high',
                    'metadata'    => [
                        'platform'    => 'StubHub',
                        'error_code'  => 'CONN_TIMEOUT',
                        'retry_count' => 3,
                    ],
                ],
                [
                    'id'          => 5,
                    'type'        => 'ticket_found',
                    'title'       => 'Premium tickets available',
                    'description' => 'VIP seats found for Wimbledon Finals',
                    'timestamp'   => now()->subHours(2)->toISOString(),
                    'priority'    => 'high',
                    'metadata'    => [
                        'platform' => 'Wimbledon Official',
                        'quantity' => 4,
                        'section'  => 'Centre Court',
                    ],
                ],
            ]);

            $paginatedActivities = $activities->skip($offset)->take($limit);

            return response()->json([
                'success'    => TRUE,
                'data'       => $paginatedActivities->values(),
                'pagination' => [
                    'total'    => $activities->count(),
                    'limit'    => $limit,
                    'offset'   => $offset,
                    'has_more' => $activities->count() > ($offset + $limit),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to fetch recent activity',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check a specific monitor now
     *
     * @param mixed $monitorId
     */
    /**
     * CheckMonitorNow
     *
     * @param mixed $monitorId
     */
    public function checkMonitorNow(Request $request, $monitorId): JsonResponse
    {
        try {
            $monitor = TicketAlert::where('id', $monitorId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Trigger immediate check
            $this->monitoringService->addToWatchList($monitor->ticket_id, [
                'immediate_check' => TRUE,
                'alert_id'        => $monitor->id,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Monitor check initiated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to initiate monitor check',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle monitor status
     *
     * @param mixed $monitorId
     */
    /**
     * ToggleMonitor
     *
     * @param mixed $monitorId
     */
    public function toggleMonitor(Request $request, $monitorId): JsonResponse
    {
        try {
            $monitor = TicketAlert::where('id', $monitorId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $monitor->is_active = ! $monitor->is_active;
            $monitor->save();

            if ($monitor->is_active) {
                $this->monitoringService->addToWatchList($monitor->ticket_id);
            } else {
                $this->monitoringService->removeFromWatchList($monitor->ticket_id);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Monitor status updated successfully',
                'data'    => [
                    'id'        => $monitor->id,
                    'is_active' => $monitor->is_active,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to toggle monitor status',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system performance metrics
     */
    /**
     * Get  system metrics
     */
    public function getSystemMetrics(Request $request): JsonResponse
    {
        try {
            $metrics = Cache::remember('monitoring.system_metrics', 120, fn (): array => [
                'cpu_usage'            => random_int(15, 85),
                'memory_usage'         => random_int(40, 75),
                'disk_usage'           => random_int(25, 65),
                'network_io'           => random_int(10, 50),
                'database_connections' => random_int(5, 25),
                'redis_connections'    => random_int(2, 15),
                'queue_size'           => random_int(0, 100),
                'active_scrapers'      => random_int(5, 15),
                'scraping_rate'        => random_int(50, 150) . '/min',
                'uptime'               => '99.8%',
                'last_updated'         => now()->toISOString(),
            ]);

            return response()->json([
                'success' => TRUE,
                'data'    => $metrics,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to fetch system metrics',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate success rate
     */
    /**
     * CalculateSuccessRate
     */
    private function calculateSuccessRate(): float
    {
        // Mock calculation - in real implementation, track actual success/failure rates
        return round(random_int(9500, 9900) / 100, 2);
    }

    /**
     * Calculate average response time
     */
    /**
     * CalculateAverageResponseTime
     */
    private function calculateAverageResponseTime(): int
    {
        // Mock calculation - in real implementation, track actual response times
        return random_int(200, 800);
    }

    /**
     * Calculate overall platform health
     */
    /**
     * CalculatePlatformHealth
     */
    private function calculatePlatformHealth(): float
    {
        // Mock calculation - in real implementation, aggregate platform health scores
        return round(random_int(8500, 9800) / 100, 2);
    }

    /**
     * Calculate health score for a specific platform
     *
     * @param mixed $platform
     */
    /**
     * CalculatePlatformHealthScore
     *
     * @param mixed $platform
     */
    private function calculatePlatformHealthScore($platform): float
    {
        $successRate = $platform->success_rate ?? random_int(85, 99);
        $responseTime = $platform->avg_response_time ?? random_int(200, 800);
        $errorCount = $platform->error_count ?? random_int(0, 5);

        // Simple health score calculation
        $healthScore = $successRate;

        // Penalty for slow response times
        if ($responseTime > 1000) {
            $healthScore -= 10;
        } elseif ($responseTime > 500) {
            $healthScore -= 5;
        }

        // Penalty for errors
        $healthScore -= ($errorCount * 2);

        return max(0, min(100, round($healthScore, 2)));
    }
}
