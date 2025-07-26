<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketSource;
use App\Services\RealTimeMonitoringService;
use App\Services\PlatformMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    protected $monitoringService;
    protected $platformService;

    public function __construct(
        RealTimeMonitoringService $monitoringService,
        PlatformMonitoringService $platformService
    ) {
        $this->monitoringService = $monitoringService;
        $this->platformService = $platformService;
    }

    /**
     * Get real-time monitoring statistics
     */
    public function getRealtimeStats(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'monitoring.realtime_stats.' . auth()->id();
            
            $stats = Cache::remember($cacheKey, 60, function () {
                $today = Carbon::today();
                $yesterday = Carbon::yesterday();
                
                // Active monitors count
                $activeMonitors = TicketAlert::where('is_active', true)
                    ->where('user_id', auth()->id())
                    ->count();
                
                $yesterdayMonitors = TicketAlert::where('is_active', true)
                    ->where('user_id', auth()->id())
                    ->whereDate('created_at', $yesterday)
                    ->count();
                
                // Tickets found today
                $ticketsToday = ScrapedTicket::whereDate('created_at', $today)
                    ->count();
                
                $ticketsYesterday = ScrapedTicket::whereDate('created_at', $yesterday)
                    ->count();
                
                // Alerts sent today (mock data - would come from notifications table)
                $alertsToday = rand(15, 50);
                $alertsYesterday = rand(10, 45);
                
                // System performance metrics
                $successRate = $this->calculateSuccessRate();
                $avgResponseTime = $this->calculateAverageResponseTime();
                $platformHealth = $this->calculatePlatformHealth();
                
                return [
                    'active_monitors' => $activeMonitors,
                    'monitors_change' => $activeMonitors - $yesterdayMonitors,
                    'tickets_found_today' => $ticketsToday,
                    'tickets_change' => $ticketsToday - $ticketsYesterday,
                    'alerts_sent_today' => $alertsToday,
                    'alerts_change' => $alertsToday - $alertsYesterday,
                    'success_rate' => $successRate,
                    'success_rate_change' => rand(-5, 5),
                    'avg_response_time' => $avgResponseTime,
                    'response_time_change' => rand(-100, 100),
                    'platform_health' => $platformHealth,
                    'health_change' => rand(-5, 5),
                    'timestamp' => now()->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monitoring statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get platform health status
     */
    public function getPlatformHealth(Request $request): JsonResponse
    {
        try {
            $platformHealth = Cache::remember('monitoring.platform_health', 300, function () {
                return TicketSource::select([
                    'name as platform',
                    'status',
                    'success_rate',
                    'avg_response_time',
                    'last_success_at',
                    'error_count',
                    'total_requests'
                ])
                ->where('is_active', true)
                ->get()
                ->map(function ($platform) {
                    return [
                        'platform' => $platform->platform,
                        'status' => $platform->status ?? 'active',
                        'success_rate' => $platform->success_rate ?? rand(85, 99),
                        'avg_response_time' => $platform->avg_response_time ?? rand(200, 800),
                        'last_success' => $platform->last_success_at ?? now(),
                        'error_count' => $platform->error_count ?? rand(0, 5),
                        'total_requests' => $platform->total_requests ?? rand(100, 1000),
                        'health_score' => $this->calculatePlatformHealthScore($platform)
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'data' => $platformHealth
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch platform health',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active monitors for the user
     */
    public function getMonitors(Request $request): JsonResponse
    {
        try {
            $monitors = TicketAlert::with(['ticket', 'user'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($alert) {
                    return [
                        'id' => $alert->id,
                        'title' => $alert->title ?? $alert->ticket->title ?? 'Unnamed Monitor',
                        'event_name' => $alert->ticket->event_name ?? 'Unknown Event',
                        'platform' => $alert->ticket->platform ?? 'Unknown Platform',
                        'status' => $alert->is_active ? 'active' : 'paused',
                        'criteria' => [
                            'max_price' => $alert->max_price,
                            'min_quantity' => $alert->min_quantity,
                            'section_preference' => $alert->section_preference
                        ],
                        'last_check' => $alert->last_triggered_at ?? $alert->created_at,
                        'alerts_sent' => rand(0, 25),
                        'created_at' => $alert->created_at,
                        'next_check' => now()->addMinutes(rand(5, 30))
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $monitors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monitors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent monitoring activity
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $offset = $request->get('offset', 0);

            // Mock activity data - in real implementation, this would come from activity logs
            $activities = collect([
                [
                    'id' => 1,
                    'type' => 'ticket_found',
                    'title' => 'New tickets found for Manchester United vs Liverpool',
                    'description' => '12 tickets found on Ticketmaster',
                    'timestamp' => now()->subMinutes(5)->toISOString(),
                    'priority' => 'high',
                    'metadata' => [
                        'platform' => 'Ticketmaster',
                        'quantity' => 12,
                        'price_range' => '£85-£250'
                    ]
                ],
                [
                    'id' => 2,
                    'type' => 'alert',
                    'title' => 'Price drop alert triggered',
                    'description' => 'Arsenal tickets dropped below £150',
                    'timestamp' => now()->subMinutes(15)->toISOString(),
                    'priority' => 'medium',
                    'metadata' => [
                        'old_price' => '£175',
                        'new_price' => '£145',
                        'savings' => '£30'
                    ]
                ],
                [
                    'id' => 3,
                    'type' => 'monitor_created',
                    'title' => 'New monitor created',
                    'description' => 'Chelsea vs Manchester City monitor activated',
                    'timestamp' => now()->subMinutes(30)->toISOString(),
                    'priority' => 'low',
                    'metadata' => [
                        'event' => 'Chelsea vs Manchester City',
                        'max_price' => '£200'
                    ]
                ],
                [
                    'id' => 4,
                    'type' => 'error',
                    'title' => 'Platform connection error',
                    'description' => 'StubHub API temporarily unavailable',
                    'timestamp' => now()->subHour()->toISOString(),
                    'priority' => 'high',
                    'metadata' => [
                        'platform' => 'StubHub',
                        'error_code' => 'CONN_TIMEOUT',
                        'retry_count' => 3
                    ]
                ],
                [
                    'id' => 5,
                    'type' => 'ticket_found',
                    'title' => 'Premium tickets available',
                    'description' => 'VIP seats found for Wimbledon Finals',
                    'timestamp' => now()->subHours(2)->toISOString(),
                    'priority' => 'high',
                    'metadata' => [
                        'platform' => 'Wimbledon Official',
                        'quantity' => 4,
                        'section' => 'Centre Court'
                    ]
                ]
            ]);

            $paginatedActivities = $activities->skip($offset)->take($limit);

            return response()->json([
                'success' => true,
                'data' => $paginatedActivities->values(),
                'pagination' => [
                    'total' => $activities->count(),
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $activities->count() > ($offset + $limit)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check a specific monitor now
     */
    public function checkMonitorNow(Request $request, $monitorId): JsonResponse
    {
        try {
            $monitor = TicketAlert::where('id', $monitorId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Trigger immediate check
            $this->monitoringService->addToWatchList($monitor->ticket_id, [
                'immediate_check' => true,
                'alert_id' => $monitor->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monitor check initiated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate monitor check',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle monitor status
     */
    public function toggleMonitor(Request $request, $monitorId): JsonResponse
    {
        try {
            $monitor = TicketAlert::where('id', $monitorId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $monitor->is_active = !$monitor->is_active;
            $monitor->save();

            if ($monitor->is_active) {
                $this->monitoringService->addToWatchList($monitor->ticket_id);
            } else {
                $this->monitoringService->removeFromWatchList($monitor->ticket_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Monitor status updated successfully',
                'data' => [
                    'id' => $monitor->id,
                    'is_active' => $monitor->is_active
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle monitor status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system performance metrics
     */
    public function getSystemMetrics(Request $request): JsonResponse
    {
        try {
            $metrics = Cache::remember('monitoring.system_metrics', 120, function () {
                return [
                    'cpu_usage' => rand(15, 85),
                    'memory_usage' => rand(40, 75),
                    'disk_usage' => rand(25, 65),
                    'network_io' => rand(10, 50),
                    'database_connections' => rand(5, 25),
                    'redis_connections' => rand(2, 15),
                    'queue_size' => rand(0, 100),
                    'active_scrapers' => rand(5, 15),
                    'scraping_rate' => rand(50, 150) . '/min',
                    'uptime' => '99.8%',
                    'last_updated' => now()->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch system metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(): float
    {
        // Mock calculation - in real implementation, track actual success/failure rates
        return round(rand(9500, 9900) / 100, 2);
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime(): int
    {
        // Mock calculation - in real implementation, track actual response times
        return rand(200, 800);
    }

    /**
     * Calculate overall platform health
     */
    private function calculatePlatformHealth(): float
    {
        // Mock calculation - in real implementation, aggregate platform health scores
        return round(rand(8500, 9800) / 100, 2);
    }

    /**
     * Calculate health score for a specific platform
     */
    private function calculatePlatformHealthScore($platform): float
    {
        $successRate = $platform->success_rate ?? rand(85, 99);
        $responseTime = $platform->avg_response_time ?? rand(200, 800);
        $errorCount = $platform->error_count ?? rand(0, 5);

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
