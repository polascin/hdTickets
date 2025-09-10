<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Email\EmailMonitoringService;
use App\Services\Email\ImapConnectionService;
use Artisan;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function array_slice;

/**
 * IMAP Dashboard Controller
 *
 * Handles web-based dashboard for IMAP email monitoring
 * in the HD Tickets sports events system.
 */
class ImapDashboardController extends Controller
{
    public function __construct(
        private EmailMonitoringService $monitoringService,
        private ImapConnectionService $connectionService,
    ) {
        // Require authentication
        $this->middleware('auth');

        // Only allow admin and agent roles
        $this->middleware('role:admin,agent');
    }

    /**
     * Display the main monitoring dashboard
     */
    public function index(): View
    {
        $data = [
            'monitoring_stats' => $this->monitoringService->getMonitoringStats(),
            'connection_stats' => $this->connectionService->getConnectionStats(),
            'page_title'       => 'IMAP Email Monitoring Dashboard',
            'breadcrumbs'      => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => NULL],
            ],
        ];

        return view('admin.imap.dashboard', $data);
    }

    /**
     * Display connection health status
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
                    ['name' => $connectionName],
                );
            } catch (Exception $e) {
                $healthData[$connectionName] = [
                    'name'    => $connectionName,
                    'success' => FALSE,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        $data = [
            'connections' => $healthData,
            'page_title'  => 'Email Connection Status',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Connections', 'url' => NULL],
            ],
        ];

        return view('admin.imap.connections', $data);
    }

    /**
     * Display platform configuration
     */
    public function platforms(): View
    {
        $platformPatterns = config('imap.platform_patterns', []);

        $data = [
            'platforms'   => $platformPatterns,
            'page_title'  => 'Platform Configuration',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Platforms', 'url' => NULL],
            ],
        ];

        return view('admin.imap.platforms', $data);
    }

    /**
     * Display monitoring logs
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
            'logs'            => $logs,
            'log_file_exists' => file_exists($logFile),
            'log_file_size'   => file_exists($logFile) ? filesize($logFile) : 0,
            'page_title'      => 'IMAP Monitoring Logs',
            'breadcrumbs'     => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Logs', 'url' => NULL],
            ],
        ];

        return view('admin.imap.logs', $data);
    }

    /**
     * Display statistics and analytics
     */
    public function analytics(): View
    {
        // Get analytics data (this would be more sophisticated in production)
        $analyticsData = [
            'daily_stats'          => $this->getDailyStats(),
            'platform_performance' => $this->getPlatformPerformance(),
            'processing_trends'    => $this->getProcessingTrends(),
            'error_analysis'       => $this->getErrorAnalysis(),
        ];

        $data = array_merge($analyticsData, [
            'page_title'  => 'IMAP Analytics & Statistics',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'IMAP Monitoring', 'url' => route('admin.imap.dashboard')],
                ['name' => 'Analytics', 'url' => NULL],
            ],
        ]);

        return view('admin.imap.analytics', $data);
    }

    /**
     * Manual trigger for email monitoring
     *
     * @return RedirectResponse
     */
    public function triggerMonitoring(Request $request)
    {
        $request->validate([
            'connection' => 'sometimes|string|max:50',
            'dry_run'    => 'sometimes|boolean',
        ]);

        try {
            $connection = $request->input('connection');
            $dryRun = $request->boolean('dry_run', FALSE);

            // Build artisan command
            $command = 'hdtickets:monitor-emails';
            if ($connection) {
                $command .= " --connection={$connection}";
            }
            if ($dryRun) {
                $command .= ' --dry-run';
            }

            // Execute monitoring command
            Artisan::call($command);
            $output = Artisan::output();

            return back()->with('success', 'Email monitoring completed successfully')
                ->with('command_output', $output);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to run email monitoring: ' . $e->getMessage());
        }
    }

    /**
     * Clear processed emails cache
     *
     * @return RedirectResponse
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
        } catch (Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Get daily statistics
     */
    private function getDailyStats(): array
    {
        // This would come from actual data in production
        return [
            'emails_processed'      => random_int(50, 200),
            'sports_events_created' => random_int(5, 25),
            'tickets_discovered'    => random_int(20, 100),
            'parsing_errors'        => random_int(0, 5),
            'connection_failures'   => random_int(0, 2),
        ];
    }

    /**
     * Get platform performance data
     */
    private function getPlatformPerformance(): array
    {
        return [
            'ticketmaster' => [
                'emails_processed'    => random_int(20, 50),
                'events_discovered'   => random_int(3, 10),
                'success_rate'        => random_int(85, 98),
                'avg_processing_time' => random_int(200, 800),
            ],
            'stubhub' => [
                'emails_processed'    => random_int(15, 35),
                'events_discovered'   => random_int(2, 8),
                'success_rate'        => random_int(80, 95),
                'avg_processing_time' => random_int(150, 600),
            ],
            'seatgeek' => [
                'emails_processed'    => random_int(10, 25),
                'events_discovered'   => random_int(1, 6),
                'success_rate'        => random_int(85, 92),
                'avg_processing_time' => random_int(180, 700),
            ],
            'viagogo' => [
                'emails_processed'    => random_int(8, 20),
                'events_discovered'   => random_int(1, 5),
                'success_rate'        => random_int(82, 90),
                'avg_processing_time' => random_int(220, 750),
            ],
        ];
    }

    /**
     * Get processing trends data
     */
    private function getProcessingTrends(): array
    {
        $trends = [];

        // Generate sample trend data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trends[] = [
                'date'                  => $date,
                'emails_processed'      => random_int(40, 180),
                'sports_events_created' => random_int(3, 20),
                'tickets_discovered'    => random_int(15, 85),
            ];
        }

        return $trends;
    }

    /**
     * Get error analysis data
     */
    private function getErrorAnalysis(): array
    {
        return [
            'connection_errors'       => random_int(0, 3),
            'parsing_errors'          => random_int(1, 8),
            'authentication_failures' => random_int(0, 2),
            'timeout_errors'          => random_int(0, 4),
            'common_error_types'      => [
                'IMAP connection timeout' => random_int(0, 3),
                'Authentication failed'   => random_int(0, 2),
                'Mailbox not found'       => random_int(0, 1),
                'Email parsing failed'    => random_int(1, 5),
            ],
        ];
    }
}
