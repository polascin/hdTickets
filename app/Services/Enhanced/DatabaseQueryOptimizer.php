<?php declare(strict_types=1);

namespace App\Services\Enhanced;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

use function count;

class DatabaseQueryOptimizer
{
    private $connectionStats = [];

    private $queryStats = [];

    public function __construct()
    {
        $this->enableQueryLogging();
    }

    /**
     * Optimize scraped tickets queries with proper indexing
     */
    /**
     * Get  optimized tickets query
     */
    public function getOptimizedTicketsQuery(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = DB::table('scraped_tickets');

        // Use appropriate indexes based on filter combinations
        if (isset($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        if (isset($filters['is_high_demand'])) {
            $query->where('is_high_demand', $filters['is_high_demand']);
        }

        if (isset($filters['event_date_from']) || isset($filters['event_date_to'])) {
            if (isset($filters['event_date_from'])) {
                $query->where('event_date', '>=', $filters['event_date_from']);
            }
            if (isset($filters['event_date_to'])) {
                $query->where('event_date', '<=', $filters['event_date_to']);
            }
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            if (isset($filters['min_price'])) {
                $query->where('min_price', '>=', $filters['min_price']);
            }
            if (isset($filters['max_price'])) {
                $query->where('max_price', '<=', $filters['max_price']);
            }
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search optimization
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('venue', 'LIKE', "%{$search}%")
                    ->orWhere('search_keyword', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Get paginated results with optimized counting
     *
     * @param mixed $query
     */
    /**
     * Get  paginated results
     *
     * @param mixed $query
     */
    public function getPaginatedResults($query, int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;

        // Use SQL_CALC_FOUND_ROWS for efficient counting (MySQL)
        if (DB::getDriverName() === 'mysql') {
            $results = DB::select(
                "SELECT SQL_CALC_FOUND_ROWS * FROM ({$query->toSql()}) as subquery LIMIT {$offset}, {$perPage}",
                $query->getBindings(),
            );

            $totalCount = DB::select('SELECT FOUND_ROWS() as count')[0]->count;
        } else {
            // Fallback for other databases
            $results = $query->offset($offset)->limit($perPage)->get();
            $totalCount = $query->count();
        }

        return [
            'data'         => $results,
            'total'        => $totalCount,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => ceil($totalCount / $perPage),
        ];
    }

    /**
     * Bulk insert optimization with chunk processing
     */
    /**
     * BulkInsertOptimized
     */
    public function bulkInsertOptimized(string $table, array $data, int $chunkSize = 1000): bool
    {
        if (empty($data)) {
            return TRUE;
        }

        try {
            DB::transaction(function () use ($table, $data, $chunkSize): void {
                $chunks = array_chunk($data, $chunkSize);

                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            });

            Log::channel('performance')->info('Bulk insert completed', [
                'table'         => $table,
                'total_records' => count($data),
                'chunks'        => ceil(count($data) / $chunkSize),
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::channel('performance')->error('Bulk insert failed', [
                'table'         => $table,
                'error'         => $e->getMessage(),
                'records_count' => count($data),
            ]);

            return FALSE;
        }
    }

    /**
     * Bulk update optimization using CASE statements
     */
    /**
     * BulkUpdateOptimized
     */
    public function bulkUpdateOptimized(string $table, array $updates, string $keyColumn = 'id'): bool
    {
        if (empty($updates)) {
            return TRUE;
        }

        try {
            $ids = array_keys($updates);
            $cases = [];
            $bindings = [];

            // Build CASE statements for each column
            $columns = array_keys(reset($updates));

            foreach ($columns as $column) {
                $case = "CASE {$keyColumn}";
                foreach ($updates as $id => $data) {
                    if (isset($data[$column])) {
                        $case .= ' WHEN ? THEN ?';
                        $bindings[] = $id;
                        $bindings[] = $data[$column];
                    }
                }
                $case .= " ELSE {$column} END";
                $cases[] = "{$column} = {$case}";
            }

            $sql = "UPDATE {$table} SET " . implode(', ', $cases) . " WHERE {$keyColumn} IN (" . str_repeat('?,', count($ids) - 1) . '?)';
            $bindings = array_merge($bindings, $ids);

            DB::update($sql, $bindings);

            Log::channel('performance')->info('Bulk update completed', [
                'table'           => $table,
                'updated_records' => count($updates),
                'updated_columns' => $columns,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::channel('performance')->error('Bulk update failed', [
                'table'         => $table,
                'error'         => $e->getMessage(),
                'records_count' => count($updates),
            ]);

            return FALSE;
        }
    }

    /**
     * Analyze query performance and suggest optimizations
     */
    /**
     * AnalyzeQueryPerformance
     */
    public function analyzeQueryPerformance(string $sql, array $bindings = []): array
    {
        $analysis = [];

        try {
            // Get query execution plan
            $plan = DB::select("EXPLAIN {$sql}", $bindings);

            foreach ($plan as $step) {
                $analysis[] = [
                    'table'         => $step->table ?? NULL,
                    'type'          => $step->type ?? NULL,
                    'possible_keys' => $step->possible_keys ?? NULL,
                    'key'           => $step->key ?? NULL,
                    'key_len'       => $step->key_len ?? NULL,
                    'ref'           => $step->ref ?? NULL,
                    'rows'          => $step->rows ?? NULL,
                    'extra'         => $step->Extra ?? NULL,
                ];
            }

            // Identify potential issues
            $issues = $this->identifyQueryIssues($analysis);

            return [
                'execution_plan' => $analysis,
                'issues'         => $issues,
                'suggestions'    => $this->generateOptimizationSuggestions($issues),
            ];
        } catch (Exception $e) {
            Log::channel('performance')->error('Query analysis failed', [
                'sql'   => $sql,
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get database connection statistics
     */
    /**
     * Get  connection stats
     */
    public function getConnectionStats(): array
    {
        try {
            $stats = [];
            $connections = ['mysql', 'analytics'];

            foreach ($connections as $connection) {
                if (config("database.connections.{$connection}")) {
                    $pdo = DB::connection($connection)->getPdo();

                    $stats[$connection] = [
                        'active'      => $pdo ? TRUE : FALSE,
                        'server_info' => $pdo ? $pdo->getAttribute(PDO::ATTR_SERVER_INFO) : NULL,
                        'driver_name' => $pdo ? $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) : NULL,
                    ];
                }
            }

            // Get MySQL-specific statistics
            if (DB::getDriverName() === 'mysql') {
                $mysqlStats = DB::select('SHOW STATUS WHERE Variable_name IN (
                    "Threads_connected", "Threads_running", "Questions", "Slow_queries",
                    "Innodb_buffer_pool_reads", "Innodb_buffer_pool_read_requests"
                )');

                $stats['mysql_status'] = collect($mysqlStats)->pluck('Value', 'Variable_name')->toArray();
            }

            return $stats;
        } catch (Exception $e) {
            Log::channel('performance')->error('Failed to get connection stats', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Optimize database maintenance tasks
     */
    /**
     * PerformMaintenance
     */
    public function performMaintenance(): array
    {
        $results = [];

        try {
            // Analyze table statistics
            $results['table_analysis'] = $this->analyzeTableStatistics();

            // Update table statistics (MySQL)
            if (DB::getDriverName() === 'mysql') {
                $tables = ['scraped_tickets', 'users', 'ticket_alerts', 'purchase_queues'];

                foreach ($tables as $table) {
                    DB::statement("ANALYZE TABLE {$table}");
                }

                $results['table_optimization'] = 'Completed table analysis for ' . count($tables) . ' tables';
            }

            // Clean up old data
            $results['cleanup'] = $this->cleanupOldData();

            Log::channel('performance')->info('Database maintenance completed', $results);
        } catch (Exception $e) {
            Log::channel('performance')->error('Database maintenance failed', [
                'error' => $e->getMessage(),
            ]);

            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Get query performance statistics
     */
    /**
     * Get  query stats
     */
    public function getQueryStats(): array
    {
        return [
            'total_queries'          => count($this->queryStats),
            'slow_queries'           => count(array_filter($this->queryStats, fn ($q) => $q['time'] > 1000)),
            'average_execution_time' => count($this->queryStats) > 0
                ? round(array_sum(array_column($this->queryStats, 'time')) / count($this->queryStats), 2)
                : 0,
            'peak_execution_time' => count($this->queryStats) > 0
                ? max(array_column($this->queryStats, 'time'))
                : 0,
            'connection_distribution' => array_count_values(array_column($this->queryStats, 'connection')),
        ];
    }

    /**
     * Reset query statistics
     */
    /**
     * ResetQueryStats
     */
    public function resetQueryStats(): void
    {
        $this->queryStats = [];
    }

    /**
     * Enable query logging for performance monitoring
     */
    /**
     * EnableQueryLogging
     */
    private function enableQueryLogging(): void
    {
        if (config('app.debug')) {
            DB::listen(function ($query): void {
                $this->logQuery($query);
            });
        }
    }

    /**
     * Log query execution details
     *
     * @param mixed $query
     */
    /**
     * LogQuery
     *
     * @param mixed $query
     */
    private function logQuery($query): void
    {
        $executionTime = $query->time;
        $sql = $query->sql;

        // Log slow queries (> 1000ms)
        if ($executionTime > 1000) {
            Log::channel('performance')->warning('Slow query detected', [
                'sql'        => $sql,
                'bindings'   => $query->bindings,
                'time'       => $executionTime . 'ms',
                'connection' => $query->connectionName,
            ]);
        }

        // Track query statistics
        $this->queryStats[] = [
            'sql'        => $sql,
            'time'       => $executionTime,
            'connection' => $query->connectionName,
            'timestamp'  => now(),
        ];
    }

    /**
     * Identify potential performance issues
     */
    /**
     * IdentifyQueryIssues
     */
    private function identifyQueryIssues(array $executionPlan): array
    {
        $issues = [];

        foreach ($executionPlan as $step) {
            // Check for full table scans
            if ($step['type'] === 'ALL') {
                $issues[] = [
                    'type'        => 'full_table_scan',
                    'table'       => $step['table'],
                    'severity'    => 'high',
                    'description' => "Full table scan on {$step['table']} - consider adding indexes",
                ];
            }

            // Check for filesort operations
            if (str_contains($step['extra'] ?? '', 'Using filesort')) {
                $issues[] = [
                    'type'        => 'filesort',
                    'table'       => $step['table'],
                    'severity'    => 'medium',
                    'description' => 'Filesort operation detected - consider optimizing ORDER BY clauses',
                ];
            }

            // Check for temporary tables
            if (str_contains($step['extra'] ?? '', 'Using temporary')) {
                $issues[] = [
                    'type'        => 'temporary_table',
                    'table'       => $step['table'],
                    'severity'    => 'medium',
                    'description' => 'Temporary table created - consider query optimization',
                ];
            }

            // Check for high row examination
            if (($step['rows'] ?? 0) > 10000) {
                $issues[] = [
                    'type'        => 'high_row_examination',
                    'table'       => $step['table'],
                    'severity'    => 'medium',
                    'description' => "High number of rows examined ({$step['rows']}) - consider adding selective indexes",
                ];
            }
        }

        return $issues;
    }

    /**
     * Generate optimization suggestions
     */
    /**
     * GenerateOptimizationSuggestions
     */
    private function generateOptimizationSuggestions(array $issues): array
    {
        $suggestions = [];

        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'full_table_scan':
                    $suggestions[] = "Add index on frequently queried columns in {$issue['table']} table";

                    break;
                case 'filesort':
                    $suggestions[] = "Create composite index for ORDER BY columns in {$issue['table']} table";

                    break;
                case 'temporary_table':
                    $suggestions[] = "Optimize JOIN conditions and GROUP BY clauses for {$issue['table']} table";

                    break;
                case 'high_row_examination':
                    $suggestions[] = "Add selective indexes to reduce row examination in {$issue['table']} table";

                    break;
            }
        }

        return array_unique($suggestions);
    }

    /**
     * Analyze table statistics
     */
    /**
     * AnalyzeTableStatistics
     */
    private function analyzeTableStatistics(): array
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                $stats = DB::select('
                    SELECT 
                        table_name,
                        table_rows,
                        data_length,
                        index_length,
                        data_free,
                        (data_length + index_length) as total_size
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE()
                    ORDER BY total_size DESC
                ');

                return collect($stats)->map(function ($stat) {
                    return [
                        'table'      => $stat->table_name,
                        'rows'       => $stat->table_rows,
                        'data_size'  => $this->formatBytes($stat->data_length),
                        'index_size' => $this->formatBytes($stat->index_length),
                        'free_space' => $this->formatBytes($stat->data_free),
                        'total_size' => $this->formatBytes($stat->total_size),
                    ];
                })->toArray();
            }

            return [];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Clean up old data
     */
    /**
     * CleanupOldData
     */
    private function cleanupOldData(): array
    {
        $results = [];

        try {
            // Clean up old scraped tickets (older than 6 months)
            $oldTickets = DB::table('scraped_tickets')
                ->where('created_at', '<', Carbon::now()->subMonths(6))
                ->delete();

            $results['old_tickets_deleted'] = $oldTickets;

            // Clean up old activity logs (older than 3 months)
            $oldLogs = DB::table('activity_log')
                ->where('created_at', '<', Carbon::now()->subMonths(3))
                ->delete();

            $results['old_logs_deleted'] = $oldLogs;

            // Clean up expired purchase attempts
            $expiredAttempts = DB::table('purchase_attempts')
                ->where('status', 'failed')
                ->where('created_at', '<', Carbon::now()->subMonth())
                ->delete();

            $results['expired_attempts_deleted'] = $expiredAttempts;
        } catch (Exception $e) {
            $results['cleanup_error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Format bytes to human readable format
     */
    /**
     * FormatBytes
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
