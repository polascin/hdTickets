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
}
