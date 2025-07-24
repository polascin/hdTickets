<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Services\PlatformMonitoringService;
use App\Events\TicketAvailabilityUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $platformMonitoringService;

    public function __construct(PlatformMonitoringService $platformMonitoringService)
    {
        $this->platformMonitoringService = $platformMonitoringService;
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $cacheKey = 'dashboard_stats_' . now()->format('YmdH');
        
        $stats = Cache::remember($cacheKey, 300, function () { // Cache for 5 minutes
            return [
                'active_monitors' => $this->getActiveMonitorsCount(),
                'tickets_found' => $this->getTicketsFoundToday(),
                'price_alerts' => $this->getPriceAlertsCount(),
                'success_rate' => $this->getOverallSuccessRate(),
                'platform_stats' => $this->getPlatformStats(),
                'high_demand_events' => $this->getHighDemandEvents(),
                'recent_updates' => $this->getRecentUpdates(),
            ];
        });

        return response()->json([
            'data' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get monitors data
     */
    public function monitors(Request $request): JsonResponse
    {
        // Mock monitors data - replace with actual monitor model queries
        $monitors = [
            [
                'id' => 1,
                'event_name' => 'Lakers vs Warriors',
                'venue_name' => 'Crypto.com Arena',
                'event_date' => now()->addDays(7)->toISOString(),
                'min_price' => 150,
                'max_price' => 800,
                'quantity_needed' => 2,
                'status' => 'active',
                'is_active' => true,
                'last_checked_at' => now()->subMinutes(5)->toISOString()
            ],
            [
                'id' => 2,
                'event_name' => 'NFL Championship',
                'venue_name' => 'SoFi Stadium',
                'event_date' => now()->addDays(14)->toISOString(),
                'min_price' => 300,
                'max_price' => 1200,
                'quantity_needed' => 4,
                'status' => 'checking',
                'is_active' => true,
                'last_checked_at' => now()->subMinutes(2)->toISOString()
            ],
        ];

        return response()->json([
            'data' => $monitors
        ]);
    }

    /**
     * Check a monitor now
     */
    public function checkMonitorNow(Request $request, $monitorId): JsonResponse
    {
        // Simulate checking a monitor
        sleep(1); // Simulate processing time

        // Broadcast update
        broadcast(new TicketAvailabilityUpdated("monitor-{$monitorId}", 'checking'));

        // Simulate finding tickets
        $foundTickets = rand(0, 5);
        if ($foundTickets > 0) {
            broadcast(new TicketAvailabilityUpdated("monitor-{$monitorId}", 'found'));
        } else {
            broadcast(new TicketAvailabilityUpdated("monitor-{$monitorId}", 'no-results'));
        }

        return response()->json([
            'message' => 'Monitor check initiated',
            'tickets_found' => $foundTickets
        ]);
    }

    /**
     * Toggle monitor status
     */
    public function toggleMonitor(Request $request, $monitorId): JsonResponse
    {
        // Simulate toggling monitor
        $isActive = $request->boolean('is_active', true);
        $status = $isActive ? 'active' : 'paused';

        // Broadcast status update
        broadcast(new TicketAvailabilityUpdated("monitor-{$monitorId}", $status));

        return response()->json([
            'message' => 'Monitor status updated',
            'is_active' => $isActive,
            'status' => $status
        ]);
    }

    /**
     * Get real-time platform health data
     */
    public function platformHealth(): JsonResponse
    {
        $platformStats = $this->platformMonitoringService->getAllPlatformStats(1); // Last hour
        
        return response()->json([
            'data' => $platformStats->map(function ($stats) {
                return [
                    'platform' => $stats['platform'],
                    'success_rate' => $stats['success_rate'],
                    'avg_response_time' => $stats['avg_response_time'],
                    'availability' => $stats['availability'],
                    'status' => $this->determinePlatformStatus($stats),
                    'last_success' => $stats['last_success'],
                    'total_requests' => $stats['total_requests'],
                    'failed_requests' => $stats['failed_requests'],
                ];
            })
        ]);
    }

    /**
     * Get high-demand tickets
     */
    public function highDemandTickets(Request $request): JsonResponse
    {
        $tickets = ScrapedTicket::highDemand()
            ->available()
            ->with([])
            ->orderBy('demand_score', 'desc')
            ->orderBy('scraped_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $tickets->map(function ($ticket) {
                return [
                    'uuid' => $ticket->uuid,
                    'platform' => $ticket->platform_display_name,
                    'event_title' => $ticket->event_title,
                    'venue' => $ticket->venue,
                    'event_date' => $ticket->event_date?->toISOString(),
                    'price' => $ticket->formatted_price,
                    'section' => $ticket->section,
                    'row' => $ticket->row,
                    'quantity_available' => $ticket->quantity_available,
                    'demand_score' => $ticket->demand_score,
                    'is_recent' => $ticket->is_recent,
                    'scraped_at' => $ticket->scraped_at->toISOString(),
                ];
            })
        ]);
    }

    // Private helper methods
    
    private function getActiveMonitorsCount(): int
    {
        // Replace with actual monitor model count
        return 8;
    }

    private function getTicketsFoundToday(): int
    {
        return ScrapedTicket::whereDate('scraped_at', today())->count();
    }

    private function getPriceAlertsCount(): int
    {
        // Replace with actual price alerts count
        return 12;
    }

    private function getOverallSuccessRate(): float
    {
        $stats = $this->platformMonitoringService->getAllPlatformStats(24);
        $totalRequests = $stats->sum('total_requests');
        $successfulRequests = $stats->sum('successful_requests');

        if ($totalRequests === 0) {
            return 0;
        }

        return round(($successfulRequests / $totalRequests) * 100, 1);
    }

    private function getPlatformStats(): array
    {
        return $this->platformMonitoringService->getAllPlatformStats(24)
            ->map(function ($stats) {
                return [
                    'platform' => $stats['platform'],
                    'success_rate' => $stats['success_rate'],
                    'total_requests' => $stats['total_requests'],
                    'status' => $this->determinePlatformStatus($stats),
                ];
            })
            ->toArray();
    }

    private function getHighDemandEvents(): array
    {
        return ScrapedTicket::highDemand()
            ->select('event_title', 'venue', 'event_date')
            ->groupBy('event_title', 'venue', 'event_date')
            ->orderBy('event_date')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'event_title' => $ticket->event_title,
                    'venue' => $ticket->venue,
                    'event_date' => $ticket->event_date?->toISOString(),
                ];
            })
            ->toArray();
    }

    private function getRecentUpdates(): array
    {
        return ScrapedTicket::orderBy('scraped_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                return [
                    'platform' => $ticket->platform_display_name,
                    'event_title' => $ticket->event_title,
                    'status' => $ticket->availability_status,
                    'scraped_at' => $ticket->scraped_at->toISOString(),
                ];
            })
            ->toArray();
    }

    private function determinePlatformStatus(array $stats): string
    {
        if ($stats['success_rate'] >= 80) {
            return 'healthy';
        } elseif ($stats['success_rate'] >= 50) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Get detailed analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '7d');
        $cacheKey = "dashboard_analytics_{$timeframe}";
        
        $data = Cache::remember($cacheKey, 900, function () use ($timeframe) {
            $days = $this->getTimeframeDays($timeframe);
            
            return [
                'ticket_volume' => $this->getTicketVolumeData($days),
                'platform_performance' => $this->getPlatformPerformanceData($days),
                'price_trends' => $this->getPriceTrendsData($days),
                'success_metrics' => $this->getSuccessMetrics($days),
                'user_activity' => $this->getUserActivityData($days)
            ];
        });
        
        return response()->json([
            'data' => $data,
            'timeframe' => $timeframe,
            'generated_at' => now()->toISOString()
        ]);
    }

    /**
     * Get real-time statistics
     */
    public function realtimeStats(Request $request): JsonResponse
    {
        // No caching for real-time data
        $data = [
            'current_active_scrapers' => $this->getActiveScrapersCount(),
            'tickets_found_last_hour' => $this->getTicketsFoundLastHour(),
            'platform_statuses' => $this->getCurrentPlatformStatuses(),
            'recent_alerts' => $this->getRecentAlerts(),
            'system_load' => $this->getSystemLoadMetrics(),
            'active_users' => $this->getActiveUsersCount()
        ];
        
        return response()->json([
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '24h');
        $cacheKey = "performance_metrics_{$timeframe}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($timeframe) {
            $hours = $this->getTimeframeHours($timeframe);
            
            return [
                'response_times' => $this->getResponseTimeMetrics($hours),
                'throughput' => $this->getThroughputMetrics($hours),
                'error_rates' => $this->getErrorRateMetrics($hours),
                'resource_usage' => $this->getResourceUsageMetrics($hours)
            ];
        });
        
        return response()->json([
            'data' => $data,
            'timeframe' => $timeframe
        ]);
    }

    /**
     * Get success rates
     */
    public function successRates(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '7d');
        $cacheKey = "success_rates_{$timeframe}";
        
        $data = Cache::remember($cacheKey, 600, function () use ($timeframe) {
            $days = $this->getTimeframeDays($timeframe);
            
            return [
                'overall_success_rate' => $this->getOverallSuccessRate(),
                'platform_success_rates' => $this->platformMonitoringService->getAllPlatformStats($days * 24),
                'scraping_success_by_hour' => $this->getScrapingSuccessByHour($days),
                'ticket_discovery_rate' => $this->getTicketDiscoveryRate($days)
            ];
        });
        
        return response()->json([
            'data' => $data,
            'timeframe' => $timeframe
        ]);
    }

    // Additional private helper methods
    
    private function getTimeframeDays(string $timeframe): int
    {
        return match ($timeframe) {
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7
        };
    }
    
    private function getTimeframeHours(string $timeframe): int
    {
        return match ($timeframe) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24
        };
    }
    
    private function getTicketVolumeData(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'daily_totals' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->selectRaw('DATE(scraped_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => (int) $item->count
                ])
                ->toArray(),
            'hourly_pattern' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->selectRaw('HOUR(scraped_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->map(fn($item) => [
                    'hour' => (int) $item->hour,
                    'count' => (int) $item->count
                ])
                ->toArray()
        ];
    }
    
    private function getPlatformPerformanceData(int $days): array
    {
        return $this->platformMonitoringService->getAllPlatformStats($days * 24)
            ->map(function ($stats) {
                return [
                    'platform' => $stats['platform'],
                    'success_rate' => $stats['success_rate'],
                    'avg_response_time' => $stats['avg_response_time'],
                    'total_requests' => $stats['total_requests'],
                    'status' => $this->determinePlatformStatus($stats)
                ];
            })
            ->toArray();
    }
    
    private function getPriceTrendsData(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'average_by_day' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->selectRaw('DATE(scraped_at) as date, AVG(price) as avg_price')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'avg_price' => round($item->avg_price, 2)
                ])
                ->toArray(),
            'by_platform' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->select('platform')
                ->selectRaw('AVG(price) as avg_price, MIN(price) as min_price, MAX(price) as max_price')
                ->groupBy('platform')
                ->get()
                ->map(fn($item) => [
                    'platform' => $item->platform,
                    'avg_price' => round($item->avg_price, 2),
                    'min_price' => round($item->min_price, 2),
                    'max_price' => round($item->max_price, 2)
                ])
                ->toArray()
        ];
    }
    
    private function getSuccessMetrics(int $days): array
    {
        return [
            'scraping_success_rate' => $this->getOverallSuccessRate(),
            'ticket_discovery_rate' => $this->getTicketDiscoveryRate($days),
            'alert_accuracy' => $this->getAlertAccuracy($days)
        ];
    }
    
    private function getUserActivityData(int $days): array
    {
        // Mock data - would integrate with actual user activity tracking
        return [
            'daily_active_users' => 245,
            'new_registrations' => 12,
            'active_monitors' => 89,
            'alerts_sent' => 156
        ];
    }
    
    private function getActiveScrapersCount(): int
    {
        // Mock data - would check actual scraper processes
        return 6;
    }
    
    private function getTicketsFoundLastHour(): int
    {
        return ScrapedTicket::where('scraped_at', '>=', now()->subHour())->count();
    }
    
    private function getCurrentPlatformStatuses(): array
    {
        return $this->platformMonitoringService->getAllPlatformStats(1)
            ->map(fn($stats) => [
                'platform' => $stats['platform'],
                'status' => $this->determinePlatformStatus($stats),
                'last_check' => $stats['last_success']
            ])
            ->toArray();
    }
    
    private function getRecentAlerts(): array
    {
        // Mock data - would fetch from alerts table
        return [
            ['type' => 'price_drop', 'event' => 'Lakers vs Warriors', 'time' => now()->subMinutes(5)->toISOString()],
            ['type' => 'new_tickets', 'event' => 'NFL Championship', 'time' => now()->subMinutes(15)->toISOString()]
        ];
    }
    
    private function getSystemLoadMetrics(): array
    {
        // Mock data - would integrate with system monitoring
        return [
            'cpu_usage' => 45.2,
            'memory_usage' => 68.7,
            'disk_usage' => 23.1,
            'network_io' => 12.4
        ];
    }
    
    private function getActiveUsersCount(): int
    {
        // Mock data - would check active sessions
        return 18;
    }
    
    private function getResponseTimeMetrics(int $hours): array
    {
        return $this->platformMonitoringService->getAllPlatformStats($hours)
            ->map(fn($stats) => [
                'platform' => $stats['platform'],
                'avg_response_time' => $stats['avg_response_time'],
                'min_response_time' => $stats['min_response_time'] ?? 0,
                'max_response_time' => $stats['max_response_time'] ?? 0
            ])
            ->toArray();
    }
    
    private function getThroughputMetrics(int $hours): array
    {
        return $this->platformMonitoringService->getAllPlatformStats($hours)
            ->map(fn($stats) => [
                'platform' => $stats['platform'],
                'requests_per_hour' => round($stats['total_requests'] / $hours, 2),
                'successful_requests_per_hour' => round($stats['successful_requests'] / $hours, 2)
            ])
            ->toArray();
    }
    
    private function getErrorRateMetrics(int $hours): array
    {
        return $this->platformMonitoringService->getAllPlatformStats($hours)
            ->map(fn($stats) => [
                'platform' => $stats['platform'],
                'error_rate' => round((1 - $stats['success_rate'] / 100) * 100, 2),
                'total_errors' => $stats['failed_requests']
            ])
            ->toArray();
    }
    
    private function getResourceUsageMetrics(int $hours): array
    {
        // Mock data - would integrate with system monitoring
        return [
            'database_connections' => 45,
            'cache_hit_rate' => 87.3,
            'queue_size' => 23,
            'memory_peak' => 512.5
        ];
    }
    
    private function getScrapingSuccessByHour(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->selectRaw('HOUR(scraped_at) as hour, COUNT(*) as successful_scrapes')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($item) => [
                'hour' => (int) $item->hour,
                'successful_scrapes' => (int) $item->successful_scrapes
            ])
            ->toArray();
    }
    
    private function getTicketDiscoveryRate(int $days): float
    {
        $totalChecks = $this->platformMonitoringService->getAllPlatformStats($days * 24)->sum('total_requests');
        $ticketsFound = ScrapedTicket::where('scraped_at', '>=', now()->subDays($days))->count();
        
        return $totalChecks > 0 ? round(($ticketsFound / $totalChecks) * 100, 2) : 0;
    }
    
    private function getAlertAccuracy(int $days): float
    {
        // Mock calculation - would track alert accuracy
        return 92.5;
    }
}
