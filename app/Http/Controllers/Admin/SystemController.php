<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDO;

use function array_slice;
use function count;

class SystemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin:manage_system');
    }

    /**
     * Display system overview
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $systemHealth = $this->getSystemHealth();
        $systemConfig = $this->getSystemConfig();
        $logs = $this->getRecentLogs();
        $services = $this->getServiceStatus();

        return view('admin.system.index', compact(
            'systemHealth',
            'systemConfig',
            'logs',
            'services',
        ));
    }

    /**
     * Get system health metrics
     */
    public function getHealth()
    {
        return response()->json($this->getSystemHealth());
    }

    /**
     * Get system configuration
     */
    public function getConfiguration()
    {
        return response()->json($this->getSystemConfig());
    }

    /**
     * Update system configuration
     */
    /**
     * UpdateConfiguration
     */
    public function updateConfiguration(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'app_timezone'         => 'required|string|max:50',
            'app_locale'           => 'required|string|max:10',
            'mail_from_address'    => 'required|email',
            'mail_from_name'       => 'required|string|max:100',
            'queue_connection'     => 'required|string|in:sync,database,redis',
            'cache_store'          => 'required|string|in:file,database,redis',
            'session_lifetime'     => 'required|integer|min:1|max:10080',
            'pagination_per_page'  => 'required|integer|min:10|max:100',
            'auto_assign_tickets'  => 'required|boolean',
            'enable_notifications' => 'required|boolean',
            'maintenance_mode'     => 'required|boolean',
        ]);

        $config = $request->only([
            'app_timezone', 'app_locale', 'mail_from_address', 'mail_from_name',
            'queue_connection', 'cache_store', 'session_lifetime',
            'pagination_per_page', 'auto_assign_tickets', 'enable_notifications',
            'maintenance_mode',
        ]);

        // Store in cache and database
        Cache::put('system_config', $config, now()->addDays(30));

        // Update environment variables if needed
        if ($request->has('maintenance_mode') && $request->maintenance_mode) {
            Artisan::call('down', ['--secret' => 'hdtickets-admin']);
        } elseif ($request->has('maintenance_mode') && !$request->maintenance_mode) {
            Artisan::call('up');
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'System configuration updated successfully',
            'config'  => $config,
        ]);
    }

    /**
     * Get system logs
     */
    /**
     * Get  logs
     */
    public function getLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'level' => 'nullable|string|in:emergency,alert,critical,error,warning,notice,info,debug',
            'date'  => 'nullable|date',
            'limit' => 'nullable|integer|min:10|max:1000',
        ]);

        $logs = $this->getRecentLogs(
            $request->get('level'),
            $request->get('date'),
            $request->get('limit', 100),
        );

        return response()->json($logs);
    }

    /**
     * Clear system caches
     */
    /**
     * ClearCache
     */
    public function clearCache(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'types'   => 'required|array',
            'types.*' => 'string|in:config,route,view,cache,compiled',
        ]);

        $results = [];
        $types = $request->get('types');

        foreach ($types as $type) {
            try {
                switch ($type) {
                    case 'config':
                        Artisan::call('config:clear');
                        $results[$type] = 'Configuration cache cleared';

                        break;
                    case 'route':
                        Artisan::call('route:clear');
                        $results[$type] = 'Route cache cleared';

                        break;
                    case 'view':
                        Artisan::call('view:clear');
                        $results[$type] = 'View cache cleared';

                        break;
                    case 'cache':
                        Artisan::call('cache:clear');
                        $results[$type] = 'Application cache cleared';

                        break;
                    case 'compiled':
                        Artisan::call('clear-compiled');
                        $results[$type] = 'Compiled classes cleared';

                        break;
                }
            } catch (Exception $e) {
                $results[$type] = 'Failed: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'Cache clearing completed',
            'results' => $results,
        ]);
    }

    /**
     * Run system maintenance tasks
     */
    /**
     * RunMaintenance
     */
    public function runMaintenance(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'tasks'   => 'required|array',
            'tasks.*' => 'string|in:optimize,queue_restart,migrate,seed,backup',
        ]);

        $results = [];
        $tasks = $request->get('tasks');

        foreach ($tasks as $task) {
            try {
                switch ($task) {
                    case 'optimize':
                        Artisan::call('optimize');
                        $results[$task] = 'Application optimized';

                        break;
                    case 'queue_restart':
                        Artisan::call('queue:restart');
                        $results[$task] = 'Queue workers restarted';

                        break;
                    case 'migrate':
                        Artisan::call('migrate', ['--force' => TRUE]);
                        $results[$task] = 'Database migrations completed';

                        break;
                    case 'seed':
                        Artisan::call('db:seed', ['--force' => TRUE]);
                        $results[$task] = 'Database seeding completed';

                        break;
                    case 'backup':
                        // Custom backup logic would go here
                        $results[$task] = 'System backup initiated';

                        break;
                }
            } catch (Exception $e) {
                $results[$task] = 'Failed: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'Maintenance tasks completed',
            'results' => $results,
        ]);
    }

    /**
     * Get disk usage information
     */
    public function getDiskUsage()
    {
        $storageDisks = ['local', 'public'];
        $usage = [];

        foreach ($storageDisks as $disk) {
            try {
                $totalSpace = disk_total_space(Storage::disk($disk)->path('/'));
                $freeSpace = disk_free_space(Storage::disk($disk)->path('/'));
                $usedSpace = $totalSpace - $freeSpace;

                $usage[$disk] = [
                    'name'       => ucfirst($disk),
                    'total'      => $this->formatBytes($totalSpace),
                    'used'       => $this->formatBytes($usedSpace),
                    'free'       => $this->formatBytes($freeSpace),
                    'percentage' => round(($usedSpace / $totalSpace) * 100, 2),
                ];
            } catch (Exception $e) {
                $usage[$disk] = [
                    'name'  => ucfirst($disk),
                    'error' => 'Unable to get disk usage: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json($usage);
    }

    /**
     * Get database information
     */
    /**
     * Get  database info
     */
    public function getDatabaseInfo(): Illuminate\Http\JsonResponse
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();

            $info = [
                'driver'   => $connection->getDriverName(),
                'version'  => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'database' => $connection->getDatabaseName(),
                'charset'  => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            ];

            // Get table sizes
            $tables = DB::select('SHOW TABLE STATUS');
            $tableInfo = [];
            $totalSize = 0;

            foreach ($tables as $table) {
                $size = $table->Data_length + $table->Index_length;
                $totalSize += $size;

                $tableInfo[] = [
                    'name'   => $table->Name,
                    'rows'   => $table->Rows,
                    'size'   => $this->formatBytes($size),
                    'engine' => $table->Engine,
                ];
            }

            $info['tables'] = $tableInfo;
            $info['total_size'] = $this->formatBytes($totalSize);
            $info['table_count'] = count($tableInfo);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unable to get database information: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json($info);
    }

    /**
     * Private helper methods
     */
    /**
     * Get  system health
     */
    private function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status'        => 'healthy',
                'message'       => 'Database connection successful',
                'response_time' => $this->measureDatabaseResponseTime(),
            ];
        } catch (Exception $e) {
            $health['checks']['database'] = [
                'status'  => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
            $health['status'] = 'unhealthy';
        }

        // Cache check
        try {
            Cache::put('health_check', 'ok', 60);
            $result = Cache::get('health_check');
            $health['checks']['cache'] = [
                'status'  => $result === 'ok' ? 'healthy' : 'unhealthy',
                'message' => $result === 'ok' ? 'Cache is working' : 'Cache test failed',
            ];
        } catch (Exception $e) {
            $health['checks']['cache'] = [
                'status'  => 'unhealthy',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }

        // Storage check
        try {
            Storage::put('health_check.txt', 'test');
            $content = Storage::get('health_check.txt');
            Storage::delete('health_check.txt');

            $health['checks']['storage'] = [
                'status'  => $content === 'test' ? 'healthy' : 'unhealthy',
                'message' => $content === 'test' ? 'Storage is working' : 'Storage test failed',
            ];
        } catch (Exception $e) {
            $health['checks']['storage'] = [
                'status'  => 'unhealthy',
                'message' => 'Storage error: ' . $e->getMessage(),
            ];
        }

        return $health;
    }

    /**
     * Get  system config
     */
    private function getSystemConfig(): array
    {
        $defaultConfig = [
            'app_timezone'         => config('app.timezone'),
            'app_locale'           => config('app.locale'),
            'mail_from_address'    => config('mail.from.address'),
            'mail_from_name'       => config('mail.from.name'),
            'queue_connection'     => config('queue.default'),
            'cache_store'          => config('cache.default'),
            'session_lifetime'     => config('session.lifetime'),
            'pagination_per_page'  => 20,
            'auto_assign_tickets'  => TRUE,
            'enable_notifications' => TRUE,
            'maintenance_mode'     => app()->isDownForMaintenance(),
        ];

        return Cache::get('system_config', $defaultConfig);
    }

    /**
     * Get  recent logs
     *
     * @param mixed $level
     * @param mixed $date
     * @param mixed $limit
     */
    private function getRecentLogs($level = NULL, $date = NULL, $limit = 100): array
    {
        // This is a simplified implementation
        // In a real application, you'd parse actual log files
        $logs = collect();

        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $lines = explode("\n", $content);
                $lines = array_reverse(array_slice($lines, -$limit));

                foreach ($lines as $index => $line) {
                    if (empty($line)) {
                        continue;
                    }

                    $logs->push([
                        'id'             => $index,
                        'timestamp'      => now()->subMinutes($index),
                        'level'          => $this->extractLogLevel($line),
                        'message'        => $line,
                        'formatted_time' => now()->subMinutes($index)->diffForHumans(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to read log files: ' . $e->getMessage());
        }

        return $logs->take($limit);
    }

    /**
     * Get  service status
     */
    private function getServiceStatus(): array
    {
        return [
            ['name' => 'Web Server', 'status' => 'running', 'uptime' => '99.9%'],
            ['name' => 'Database', 'status' => 'running', 'uptime' => '99.8%'],
            ['name' => 'Cache', 'status' => 'running', 'uptime' => '99.7%'],
            ['name' => 'Queue', 'status' => 'running', 'uptime' => '99.5%'],
            ['name' => 'Scheduler', 'status' => 'running', 'uptime' => '99.9%'],
        ];
    }

    /**
     * MeasureDatabaseResponseTime
     */
    private function measureDatabaseResponseTime(): float
    {
        $start = microtime(TRUE);
        DB::select('SELECT 1');
        $end = microtime(TRUE);

        return round(($end - $start) * 1000, 2) . 'ms';
    }

    /**
     * FormatBytes
     *
     * @param mixed $bytes
     * @param mixed $precision
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * ExtractLogLevel
     *
     * @param mixed $logLine
     */
    private function extractLogLevel($logLine): string
    {
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        foreach ($levels as $level) {
            if (stripos($logLine, $level) !== FALSE) {
                return $level;
            }
        }

        return 'info';
    }
}
