<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealTimeMonitoringService;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\ProxyRotationService;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class RealTimeDashboardController extends Controller
{
    protected $monitoringService;
    protected $scraperManager;
    protected $proxyService;
    protected $notificationService;

    public function __construct(
        RealTimeMonitoringService $monitoringService,
        PluginBasedScraperManager $scraperManager,
        ProxyRotationService $proxyService,
        InAppNotificationService $notificationService
    ) {
        $this->monitoringService = $monitoringService;
        $this->scraperManager = $scraperManager;
        $this->proxyService = $proxyService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show the real-time monitoring dashboard
     */
    public function dashboard()
    {
        try {
            $dashboardData = [
                'monitoring' => $this->monitoringService->getDashboardData(),
                'scrapers' => $this->scraperManager->getPluginStats(),
                'proxies' => $this->proxyService->getProxyStats(),
                'health' => $this->scraperManager->getHealthStatus()
            ];

            return view('admin.realtime-dashboard', compact('dashboardData'));

        } catch (Exception $e) {
            Log::error('Failed to load real-time dashboard', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * Get live dashboard data (AJAX endpoint)
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $data = [
                'monitoring' => $this->monitoringService->getDashboardData(),
                'scrapers' => $this->scraperManager->getPluginStats(),
                'proxies' => $this->proxyService->getProxyStats(),
                'health' => $this->scraperManager->getHealthStatus(),
                'timestamp' => now()->toISOString()
            ];

            return response()->json($data);

        } catch (Exception $e) {
            Log::error('Failed to get dashboard data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start monitoring
     */
    public function startMonitoring(): JsonResponse
    {
        try {
            $this->monitoringService->startMonitoring();

            return response()->json([
                'success' => true,
                'message' => 'Real-time monitoring started successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to start monitoring', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop monitoring
     */
    public function stopMonitoring(): JsonResponse
    {
        try {
            $this->monitoringService->stopMonitoring();

            return response()->json([
                'success' => true,
                'message' => 'Real-time monitoring stopped successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to stop monitoring', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to stop monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monitoring statistics
     */
    public function getMonitoringStats(): JsonResponse
    {
        try {
            $stats = $this->monitoringService->getMonitoringStats();

            return response()->json($stats);

        } catch (Exception $e) {
            Log::error('Failed to get monitoring stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get monitoring statistics'
            ], 500);
        }
    }

    /**
     * Test scraper plugin
     */
    public function testPlugin(Request $request): JsonResponse
    {
        $request->validate([
            'plugin' => 'required|string'
        ]);

        try {
            $pluginName = $request->input('plugin');
            $result = $this->scraperManager->testPlugin($pluginName);

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('Failed to test plugin', [
                'plugin' => $request->input('plugin'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to test plugin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test all proxies
     */
    public function testProxies(): JsonResponse
    {
        try {
            $results = $this->proxyService->testAllProxies();

            return response()->json([
                'success' => true,
                'results' => $results,
                'summary' => [
                    'total' => count($results),
                    'healthy' => count(array_filter($results, fn($r) => $r['healthy'])),
                    'unhealthy' => count(array_filter($results, fn($r) => !$r['healthy']))
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to test proxies', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to test proxies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update monitoring settings
     */
    public function updateMonitoringSettings(Request $request): JsonResponse
    {
        $request->validate([
            'interval' => 'sometimes|integer|min:10|max:3600',
            'alert_thresholds' => 'sometimes|array',
            'alert_thresholds.price_change_percentage' => 'sometimes|numeric|min:0|max:100',
            'alert_thresholds.availability_change' => 'sometimes|boolean',
            'alert_thresholds.new_tickets' => 'sometimes|boolean'
        ]);

        try {
            if ($request->has('interval')) {
                $this->monitoringService->setMonitoringInterval($request->input('interval'));
            }

            if ($request->has('alert_thresholds')) {
                $this->monitoringService->setAlertThresholds($request->input('alert_thresholds'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Monitoring settings updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update monitoring settings', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable/disable scraper plugin
     */
    public function togglePlugin(Request $request): JsonResponse
    {
        $request->validate([
            'plugin' => 'required|string',
            'enabled' => 'required|boolean'
        ]);

        try {
            $pluginName = $request->input('plugin');
            $enabled = $request->input('enabled');

            if ($enabled) {
                $this->scraperManager->enablePlugin($pluginName);
            } else {
                $this->scraperManager->disablePlugin($pluginName);
            }

            return response()->json([
                'success' => true,
                'message' => "Plugin {$pluginName} " . ($enabled ? 'enabled' : 'disabled') . ' successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle plugin', [
                'plugin' => $request->input('plugin'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle plugin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add ticket to monitoring watchlist
     */
    public function addToWatchlist(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'criteria' => 'sometimes|array'
        ]);

        try {
            $ticketId = $request->input('ticket_id');
            $criteria = $request->input('criteria', []);

            $this->monitoringService->addToWatchList($ticketId, $criteria);

            return response()->json([
                'success' => true,
                'message' => "Ticket {$ticketId} added to monitoring watchlist"
            ]);

        } catch (Exception $e) {
            Log::error('Failed to add ticket to watchlist', [
                'ticket_id' => $request->input('ticket_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add ticket to watchlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove ticket from monitoring watchlist
     */
    public function removeFromWatchlist(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_id' => 'required|integer'
        ]);

        try {
            $ticketId = $request->input('ticket_id');
            $this->monitoringService->removeFromWatchList($ticketId);

            return response()->json([
                'success' => true,
                'message' => "Ticket {$ticketId} removed from monitoring watchlist"
            ]);

        } catch (Exception $e) {
            Log::error('Failed to remove ticket from watchlist', [
                'ticket_id' => $request->input('ticket_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove ticket from watchlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test notification
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'sometimes|integer|exists:users,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'sometimes|string|in:low,normal,high,urgent'
        ]);

        try {
            $userId = $request->input('user_id', auth()->id());
            $user = \App\Models\User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $notification = $this->notificationService->sendNotification(
                $user,
                $request->input('type'),
                $request->input('title'),
                $request->input('message'),
                ['test' => true],
                $request->input('priority', 'normal')
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'notification' => $notification
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send test notification', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system performance metrics
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $metrics = [
                'monitoring' => $this->monitoringService->getMonitoringStats(),
                'scrapers' => $this->scraperManager->getPluginStats(),
                'proxies' => $this->proxyService->getProxyStats(),
                'system' => [
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'uptime' => time() - $_SERVER['REQUEST_TIME_FLOAT'] ?? 0
                ]
            ];

            return response()->json($metrics);

        } catch (Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get performance metrics'
            ], 500);
        }
    }

    /**
     * Export monitoring data
     */
    public function exportMonitoringData(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'sometimes|string|in:json,csv',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from'
        ]);

        try {
            $format = $request->input('format', 'json');
            $dateFrom = $request->input('date_from', now()->subDays(7));
            $dateTo = $request->input('date_to', now());

            // This would typically generate and return export data
            // For now, return current statistics
            $data = [
                'export_info' => [
                    'format' => $format,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'generated_at' => now()->toISOString()
                ],
                'monitoring_stats' => $this->monitoringService->getMonitoringStats(),
                'scraper_stats' => $this->scraperManager->getPluginStats(),
                'proxy_stats' => $this->proxyService->getProxyStats()
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Monitoring data exported successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to export monitoring data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export monitoring data: ' . $e->getMessage()
            ], 500);
        }
    }
}
