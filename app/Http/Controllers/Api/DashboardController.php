<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Services\PlatformMonitoringService;
use App\Services\ActivityLogger;
use App\Services\AnalyticsService;
use App\Services\NotificationService;
use App\Services\Enhanced\AdvancedTicketCachingService;
use App\Events\TicketAvailabilityUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Exception;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private PlatformMonitoringService $platformMonitoringService,
        private AnalyticsService $analytics,
        private NotificationService $notifications,
        private AdvancedTicketCachingService $ticketCache
    ) {}

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            Log::info('Dashboard stats requested', ['user_id' => $request->user()?->id]);
            
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

            Log::info('Dashboard stats generated successfully');
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (Exception $e) {
            Log::error('Error generating dashboard stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve dashboard statistics',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get monitors data
     */
    public function monitors(Request $request): JsonResponse
    {
        try {
            Log::info('Dashboard monitors requested', ['user_id' => $request->user()?->id]);
            
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
                'success' => true,
                'data' => $monitors
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving monitors data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve monitors data',
                'error' => 'Internal server error'
            ], 500);
        }
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
        try {
            Log::info('High demand tickets requested', ['user_id' => $request->user()?->id]);
            
            $tickets = ScrapedTicket::highDemand()
                ->available()
                ->with([])
                ->orderBy('scraped_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tickets->map(function ($ticket) {
                    return [
                        'uuid' => $ticket->uuid,
                        'platform' => $ticket->platform_display_name ?? 'Unknown',
                        'event_title' => $ticket->event_title,
                        'venue' => $ticket->venue,
                        'event_date' => $ticket->event_date?->toISOString(),
                        'price' => $ticket->formatted_price ?? '$' . number_format($ticket->price, 2),
                        'section' => $ticket->section,
                        'row' => $ticket->row,
                        'quantity_available' => $ticket->quantity_available,
                        'demand_score' => $ticket->demand_score ?? 0,
                        'is_recent' => $ticket->is_recent ?? false,
                        'scraped_at' => $ticket->scraped_at->toISOString(),
                    ];
                })
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving high demand tickets', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve high demand tickets',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    // Private helper methods
    
    private function getActiveMonitorsCount(): int
    {
        // Replace with actual monitor model count
        return 8;
    }

    private function getTicketsFoundToday(): int
    {
        try {
            return ScrapedTicket::whereDate('scraped_at', today())->count();
        } catch (Exception $e) {
            Log::error('Error getting tickets found today', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPriceAlertsCount(): int
    {
        try {
            // Replace with actual price alerts count
            return 12;
        } catch (Exception $e) {
            Log::error('Error getting price alerts count', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getOverallSuccessRate(): float
    {
        try {
            $stats = $this->platformMonitoringService->getAllPlatformStats(24);
            $totalRequests = $stats->sum('total_requests');
            $successfulRequests = $stats->sum('successful_requests');

            if ($totalRequests === 0) {
                return 0;
            }

            return round(($successfulRequests / $totalRequests) * 100, 1);
        } catch (Exception $e) {
            Log::error('Error calculating overall success rate', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPlatformStats(): array
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Error getting platform stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getHighDemandEvents(): array
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Error getting high demand events', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getRecentUpdates(): array
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Error getting recent updates', ['error' => $e->getMessage()]);
            return [];
        }
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
        try {
            Log::info('Analytics data requested', ['user_id' => $request->user()?->id]);
            
            $timeframe = $request->get('timeframe', '7d');
            $cacheKey = "dashboard_analytics_{$timeframe}";
            
            $data = Cache::remember($cacheKey, 900, function () use ($timeframe) {
                $days = $this->getTimeframeDays($timeframe);
                
                return [
                    'ticket_volume' => $this->getTicketVolumeData($days),
                    'platform_performance' => $this->getPlatformPerformanceData($days),
                    'price_trends' => [], // Skip price trends due to missing price column
                    'success_metrics' => $this->getSuccessMetrics($days),
                    'user_activity' => $this->getUserActivityData($days)
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timeframe' => $timeframe,
                'generated_at' => now()->toISOString()
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving analytics data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve analytics data',
                'error' => 'Internal server error'
            ], 500);
        }
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

    /**
     * Log JavaScript errors and performance data from the frontend
     */
    public function logError(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'session_id' => 'required|string',
                'timestamp' => 'required|string',
                'errors' => 'required|array',
                'meta' => 'sometimes|array'
            ]);

            $activityLogger = app(ActivityLogger::class);

            $sessionId = $request->input('session_id');
            $errors = $request->input('errors', []);
            $meta = $request->input('meta', []);

            // Log each error individually
            foreach ($errors as $error) {
                $errorType = $error['type'] ?? 'unknown';
                $errorLevel = $this->determineErrorLevel($errorType, $error);

                $context = [
                    'session_id' => $sessionId,
                    'error_type' => $errorType,
                    'error_data' => $error,
                    'browser_meta' => $meta,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ];

                // Log based on error type and severity
                switch ($errorType) {
                    case 'javascript_error':
                    case 'unhandled_promise_rejection':
                        $activityLogger->logJavaScriptEvent('error', $context);
                        break;
                    
                    case 'console_error':
                    case 'console_warn':
                        $activityLogger->logJavaScriptEvent($errorType, $context);
                        break;
                    
                    case 'performance_issue':
                    case 'long_task':
                    case 'slow_operation':
                        Log::channel('performance')->warning('Frontend Performance Issue', $context);
                        break;
                    
                    case 'page_performance':
                        Log::channel('performance')->info('Page Performance Metrics', $context);
                        break;
                    
                    default:
                        $activityLogger->logJavaScriptEvent('custom_event', $context);
                }

                // Log critical errors with admin notification
                if ($errorLevel === 'critical') {
                    $exception = new \Exception(
                        $error['message'] ?? 'Frontend Critical Error',
                        0
                    );
                    $activityLogger->logCriticalError($exception, $context, true);
                }
            }

            // Log aggregated session info
            Log::channel('monitoring')->info('Frontend Error Batch Processed', [
                'session_id' => $sessionId,
                'error_count' => count($errors),
                'error_types' => array_count_values(array_column($errors, 'type')),
                'user_id' => auth()->id(),
                'browser_info' => $meta,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Errors logged successfully',
                'processed_count' => count($errors),
                'session_id' => $sessionId
            ]);

        } catch (Exception $e) {
            Log::error('Failed to log frontend errors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to log errors',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Refresh dashboard data
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            // Clear cache
            $cacheKey = 'dashboard:realtime:' . ($request->user()->id ?? 'guest');
            Cache::forget($cacheKey);

            // Get fresh data
            $data = [
                'timestamp' => Carbon::now()->toISOString(),
                'analytics' => $this->analytics->getRealTimeMetrics(),
                'tickets' => $this->getTicketData(),
                'notifications' => $this->getNotificationData($request->user()->id ?? null),
                'system_status' => $this->getSystemStatus(),
                'user_metrics' => $this->getUserMetrics($request->user()),
                'performance' => $this->getPerformanceMetrics(),
            ];

            // Track refresh event
            $this->analytics->trackEvent('dashboard_refresh', [
                'user_id' => $request->user()->id ?? null,
                'timestamp' => Carbon::now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Dashboard refreshed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard refresh error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh dashboard'
            ], 500);
        }
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? null;
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], 401);
            }

            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 20);

            $notifications = $this->notifications->getUserNotifications($userId, $page, $perPage);
            $unreadCount = $this->notifications->getUnreadNotificationCount($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications['notifications'],
                    'pagination' => $notifications['pagination'],
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Notifications API error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load notifications'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? null;
            $notificationId = $request->get('notification_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], 401);
            }

            if (!$notificationId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Notification ID required'
                ], 400);
            }

            $success = $this->notifications->markNotificationAsRead($notificationId, $userId);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Mark notification read API error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'notification_id' => $request->get('notification_id')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Get system status
     */
    public function systemStatus(): JsonResponse
    {
        try {
            $status = $this->getSystemStatus();

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('System status API error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load system status'
            ], 500);
        }
    }

    /**
     * Get live metrics for real-time updates
     */
    public function liveMetrics(): JsonResponse
    {
        try {
            $metrics = Cache::remember('dashboard:live_metrics', 10, function () {
                return [
                    'timestamp' => Carbon::now()->toISOString(),
                    'active_users' => $this->getActiveUsersCount(),
                    'tickets' => [
                        'total_available' => $this->getTotalAvailableTickets(),
                        'high_demand' => count($this->getHighDemandTickets()),
                        'recent_sales' => $this->getRecentSales(),
                    ],
                    'performance' => [
                        'response_time' => $this->getAverageResponseTime(),
                        'cache_hit_rate' => $this->getCacheHitRate(),
                        'error_rate' => $this->getErrorRate(),
                    ],
                    'alerts' => [
                        'active_count' => $this->getActiveAlertsCount(),
                        'recent' => $this->getRecentAlerts(5),
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Live metrics API error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load live metrics'
            ], 500);
        }
    }

    // Additional helper methods for new functionality
    private function getTicketData(): array
    {
        return [
            'total_available' => $this->getTotalAvailableTickets(),
            'high_demand' => $this->getHighDemandTickets(),
            'recent_updates' => $this->getRecentTicketUpdates(),
        ];
    }

    private function getTotalAvailableTickets(): int
    {
        return (int) Cache::remember('dashboard:total_tickets', 300, function () {
            return ScrapedTicket::where('availability_status', 'available')->count();
        });
    }

    private function getRecentTicketUpdates(): array
    {
        return Cache::remember('dashboard:recent_ticket_updates', 60, function () {
            return ScrapedTicket::orderBy('scraped_at', 'desc')
                ->limit(20)
                ->get([
                    'event_title',
                    'platform',
                    'availability_status',
                    'scraped_at'
                ])
                ->toArray();
        });
    }

    private function getNotificationData(?int $userId): array
    {
        if (!$userId) {
            return [
                'unread_count' => 0,
                'recent' => []
            ];
        }

        return [
            'unread_count' => $this->notifications->getUnreadNotificationCount($userId),
            'recent' => $this->notifications->getUserNotifications($userId, 1, 5)['notifications']
        ];
    }

    private function getSystemStatus(): array
    {
        return [
            'timestamp' => Carbon::now()->toISOString(),
            'health_score' => $this->calculateSystemHealthScore(),
            'services' => [
                'database' => $this->checkDatabaseStatus(),
                'redis' => $this->checkRedisStatus(),
                'storage' => $this->checkStorageStatus(),
            ],
            'performance' => [
                'memory_usage' => $this->getMemoryUsage(),
                'response_time' => $this->getAverageResponseTime(),
            ]
        ];
    }

    private function getUserMetrics($user): array
    {
        if (!$user) {
            return [];
        }

        return [
            'session_duration' => $this->getSessionDuration($user->id),
            'page_views' => $this->getSessionPageViews($user->id),
            'last_activity' => $user->updated_at,
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'cache_performance' => [
                'hit_rate' => $this->getCacheHitRate(),
                'size' => $this->getCacheSize(),
            ],
            'database_performance' => [
                'queries_per_second' => $this->getDatabaseQueriesPerSecond(),
                'average_query_time' => $this->getAverageQueryTime(),
            ],
            'api_performance' => [
                'requests_per_minute' => $this->getApiRequestsPerMinute(),
                'average_response_time' => $this->getAverageResponseTime(),
                'error_rate' => $this->getErrorRate(),
            ]
        ];
    }

    private function calculateSystemHealthScore(): int
    {
        $scores = [
            $this->checkDatabaseStatus() ? 25 : 0,
            $this->checkRedisStatus() ? 25 : 0,
            $this->getMemoryUsage() < 80 ? 25 : 0,
            $this->getErrorRate() < 5 ? 25 : 0,
        ];

        return array_sum($scores);
    }

    private function checkDatabaseStatus(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkRedisStatus(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkStorageStatus(): bool
    {
        return disk_free_space('/') > (1024 * 1024 * 1024); // 1GB free
    }

    private function getMemoryUsage(): float
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit == -1) return 0.0;
        
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        return ($memoryUsage / $memoryLimitBytes) * 100;
    }

    private function getCacheHitRate(): float
    {
        $hits = (int) Redis::get('cache:hits') ?: 0;
        $misses = (int) Redis::get('cache:misses') ?: 0;
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    private function getCacheSize(): int
    {
        return (int) Redis::dbsize();
    }

    private function getErrorRate(): float
    {
        return (float) Cache::get('dashboard:error_rate', 0.0);
    }

    private function getDatabaseQueriesPerSecond(): float
    {
        return (float) Cache::get('dashboard:db_qps', 0.0);
    }

    private function getAverageQueryTime(): float
    {
        return (float) Cache::get('dashboard:avg_query_time', 0.0);
    }

    private function getApiRequestsPerMinute(): int
    {
        return (int) Cache::get('dashboard:api_rpm', 0);
    }

    private function getSessionDuration(int $userId): int
    {
        $sessionStart = Redis::get("session:start:$userId");
        return $sessionStart ? time() - (int) $sessionStart : 0;
    }

    private function getSessionPageViews(int $userId): int
    {
        return (int) Redis::get("session:page_views:$userId") ?: 0;
    }

    private function getRecentSales(): int
    {
        return (int) Cache::remember('dashboard:recent_sales', 60, function () {
            // Mock data - would integrate with actual sales tracking
            return rand(10, 50);
        });
    }

    private function getActiveAlertsCount(): int
    {
        return (int) Cache::remember('dashboard:active_alerts', 60, function () {
            // Mock data - would check actual alerts
            return rand(5, 15);
        });
    }

    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Determine error severity level
     */
    private function determineErrorLevel(string $errorType, array $error): string
    {
        // Critical errors that need immediate attention
        $criticalTypes = [
            'javascript_error',
            'unhandled_promise_rejection'
        ];

        if (in_array($errorType, $criticalTypes)) {
            return 'critical';
        }

        // Performance issues
        if ($errorType === 'performance_issue' && ($error['duration'] ?? 0) > 5000) {
            return 'warning';
        }

        // Console errors
        if ($errorType === 'console_error') {
            return 'warning';
        }

        return 'info';
    }
}
