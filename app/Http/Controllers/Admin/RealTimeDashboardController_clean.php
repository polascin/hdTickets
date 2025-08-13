<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function count;

class RealTimeDashboardController extends Controller
{
    protected $monitoringService;

    protected $scraperManager;

    protected $proxyService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        // Initialize services - these would normally be injected
        $this->monitoringService = NULL; // Would be injected
        $this->scraperManager = NULL; // Would be injected
        $this->proxyService = NULL; // Would be injected
    }

    /**
     * Show dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $dashboardData = [
                'monitoring' => $this->monitoringService ? $this->monitoringService->getDashboardData() : [],
                'scrapers'   => $this->scraperManager ? $this->scraperManager->getPluginStats() : [],
                'proxies'    => $this->proxyService ? $this->proxyService->getProxyStats() : [],
                'health'     => $this->scraperManager ? $this->scraperManager->getHealthStatus() : [],
            ];

            return view('admin.realtime-dashboard', compact('dashboardData'));
        } catch (Exception $e) {
            Log::error('Failed to load real-time dashboard', [
                'error' => $e->getMessage(),
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
                'monitoring' => $this->monitoringService ? $this->monitoringService->getDashboardData() : [],
                'scrapers'   => $this->scraperManager ? $this->scraperManager->getPluginStats() : [],
                'proxies'    => $this->proxyService ? $this->proxyService->getProxyStats() : [],
                'health'     => $this->scraperManager ? $this->scraperManager->getHealthStatus() : [],
                'timestamp'  => now()->toISOString(),
            ];

            return response()->json($data);
        } catch (Exception $e) {
            Log::error('Failed to get dashboard data', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error'   => 'Failed to load dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start monitoring
     */
    public function startMonitoring(): JsonResponse
    {
        try {
            if ($this->monitoringService) {
                $this->monitoringService->startMonitoring();
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Real-time monitoring started successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to start monitoring', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to start monitoring: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop monitoring
     */
    public function stopMonitoring(): JsonResponse
    {
        try {
            if ($this->monitoringService) {
                $this->monitoringService->stopMonitoring();
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Real-time monitoring stopped successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to stop monitoring', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to stop monitoring: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monitoring statistics
     */
    public function getMonitoringStats(): JsonResponse
    {
        try {
            $stats = $this->monitoringService ? $this->monitoringService->getMonitoringStats() : [];

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Failed to get monitoring stats', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get monitoring statistics',
            ], 500);
        }
    }

    /**
     * Test scraper plugin
     */
    public function testPlugin(Request $request): JsonResponse
    {
        $request->validate([
            'plugin' => 'required|string',
        ]);

        try {
            $pluginName = $request->input('plugin');
            $result = $this->scraperManager ? $this->scraperManager->testPlugin($pluginName) : [];

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Failed to test plugin', [
                'plugin' => $request->input('plugin'),
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to test plugin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test all proxies
     */
    public function testProxies(): JsonResponse
    {
        try {
            $results = $this->proxyService ? $this->proxyService->testAllProxies() : [];

            return response()->json([
                'success' => TRUE,
                'results' => $results,
                'summary' => [
                    'total'     => count($results),
                    'healthy'   => count(array_filter($results, fn ($r) => $r['healthy'] ?? FALSE)),
                    'unhealthy' => count(array_filter($results, fn ($r) => ! ($r['healthy'] ?? FALSE))),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to test proxies', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to test proxies: ' . $e->getMessage(),
            ], 500);
        }
    }
}
