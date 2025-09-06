<?php

namespace App\Http\Controllers;

use App\Services\Email\EmailMonitoringService;
use App\Services\Email\ImapConnectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * IMAP Dashboard Controller
 * 
 * Handles web-based dashboard for IMAP email monitoring
 * in the HD Tickets sports events system.
 */
class ImapDashboardController extends Controller
{
    private EmailMonitoringService $monitoringService;
    private ImapConnectionService $connectionService;

    public function __construct(
        EmailMonitoringService $monitoringService,
        ImapConnectionService $connectionService
    ) {
        $this->monitoringService = $monitoringService;
        $this->connectionService = $connectionService;
        
        // Require authentication
        $this->middleware('auth');
        
        // Only allow admin and agent roles
        $this->middleware('role:admin,agent');
    }

    /**
     * Display the main monitoring dashboard
     * 
     * @return View
     */
    public function index(): View
    {
        $data = [
            'monitoring_stats' => $this->monitoringService->getMonitoringStats(),
            'connection_stats' => $this->connectionService->getConnectionStats(),
            'page_title' => 'IMAP Email Monitoring Dashboard',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => null],
            ],
        ];

        return view('admin.imap.dashboard', $data);
    }

    /**
     * Display connection health status
     * 
     * @return View
     */
    public function connections(): View
    {
        $connections = config('imap.connections', []);
        $healthData = [];

        foreach (array_keys($connections) as $connectionName) {
            try {
                $testResult = $this->connectionService->testConnection($connectionName);
                $healthData[$connectionName] = array_merge(
                    $testResult,
                    ['name' => $connectionName]
                );
            } catch (\Exception $e) {
                $healthData[$connectionName] = [
                    'name' => $connectionName,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $data = [
            'connections' => $healthData,
            'page_title' => 'Email Connection Status',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Connections', 'url' => null],
            ],
        ];

        return view('admin.imap.connections', $data);
    }

    /**
     * Display platform configuration
     * 
     * @return View
     */
    public function platforms(): View
    {
        $platformPatterns = config('imap.platform_patterns', []);
        
        $data = [
            'platforms' => $platformPatterns,
            'page_title' => 'Platform Configuration',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Platforms', 'url' => null],
            ],
        ];

        return view('admin.imap.platforms', $data);
    }

    /**
     * Display monitoring logs
     * 
     * @param Request $request
     * @return View
     */
    public function logs(Request $request): View
    {
        $logFile = storage_path('logs/imap.log');
        $logs = [];
        
        if (file_exists($logFile)) {
            $lines = array_reverse(file($logFile));
            $logs = array_slice($lines, 0, 100); // Last 100 lines
        }

        $data = [
            'logs' => $logs,
            'log_file_exists' => file_exists($logFile),
            'log_file_size' => file_exists($logFile) ? filesize($logFile) : 0,
            'page_title' => 'IMAP Monitoring Logs',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Logs', 'url' => null],
            ],
        ];

        return view('admin.imap.logs', $data);
    }

    /**
     * Display statistics and analytics
     * 
     * @return View
     */
    public function analytics(): View
    {
        // Get analytics data (this would be more sophisticated in production)
        $analyticsData = [
            'daily_stats' => $this->getDailyStats(),
            'platform_performance' => $this->getPlatformPerformance(),
            'processing_trends' => $this->getProcessingTrends(),
            'error_analysis' => $this->getErrorAnalysis(),
        ];

        $data = array_merge($analyticsData, [
            'page_title' => 'IMAP Analytics & Statistics',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Analytics', 'url' => null],
            ],
        ]);

        return view('admin.imap.analytics', $data);
    }

    /**
     * Manual trigger for email monitoring
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function triggerMonitoring(Request $request)
    {
        $request->validate([
            'connection' => 'sometimes|string|max:50',
            'dry_run' => 'sometimes|boolean',
        ]);

        try {
            $connection = $request->input('connection');
            $dryRun = $request->boolean('dry_run', false);

            // Build artisan command
            $command = 'hdtickets:monitor-emails';
            if ($connection) {
                $command .= " --connection={$connection}";
            }
            if ($dryRun) {
                $command .= " --dry-run";
            }

            // Execute monitoring command
            \Artisan::call($command);
            $output = \Artisan::output();

            return back()->with('success', 'Email monitoring completed successfully')
                        ->with('command_output', $output);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to run email monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Clear processed emails cache
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'connection' => 'sometimes|string|max:50',
        ]);

        try {
            $connection = $request->input('connection');
            $this->monitoringService->clearProcessedCache($connection);

            $message = $connection 
                ? "Cache cleared for connection: {$connection}"
                : 'Cache cleared for all connections';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Get daily statistics
     * 
     * @return array
     */
    private function getDailyStats(): array
    {
        // This would come from actual data in production
        return [
            'emails_processed' => rand(50, 200),
            'sports_events_created' => rand(5, 25),
            'tickets_discovered' => rand(20, 100),
            'parsing_errors' => rand(0, 5),
            'connection_failures' => rand(0, 2),
        ];
    }

    /**
     * Get platform performance data
     * 
     * @return array
     */
    private function getPlatformPerformance(): array
    {
        return [
            'ticketmaster' => [
                'emails_processed' => rand(20, 50),
                'events_discovered' => rand(3, 10),
                'success_rate' => rand(85, 98),
                'avg_processing_time' => rand(200, 800),
            ],
            'stubhub' => [
                'emails_processed' => rand(15, 35),
                'events_discovered' => rand(2, 8),
                'success_rate' => rand(80, 95),
                'avg_processing_time' => rand(150, 600),
            ],
            'seatgeek' => [
                'emails_processed' => rand(10, 25),
                'events_discovered' => rand(1, 6),
                'success_rate' => rand(85, 92),
                'avg_processing_time' => rand(180, 700),
            ],
            'viagogo' => [
                'emails_processed' => rand(8, 20),
                'events_discovered' => rand(1, 5),
                'success_rate' => rand(82, 90),
                'avg_processing_time' => rand(220, 750),
            ],
        ];
    }

    /**
     * Get processing trends data
     * 
     * @return array
     */
    private function getProcessingTrends(): array
    {
        $trends = [];
        
        // Generate sample trend data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trends[] = [
                'date' => $date,
                'emails_processed' => rand(40, 180),
                'sports_events_created' => rand(3, 20),
                'tickets_discovered' => rand(15, 85),
            ];
        }
        
        return $trends;
    }

    /**
     * Get error analysis data
     * 
     * @return array
     */
    private function getErrorAnalysis(): array
    {
        return [
            'connection_errors' => rand(0, 3),
            'parsing_errors' => rand(1, 8),
            'authentication_failures' => rand(0, 2),
            'timeout_errors' => rand(0, 4),
            'common_error_types' => [
                'IMAP connection timeout' => rand(0, 3),
                'Authentication failed' => rand(0, 2),
                'Mailbox not found' => rand(0, 1),
                'Email parsing failed' => rand(1, 5),
            ],
        ];
    }
}
