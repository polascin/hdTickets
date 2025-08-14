<?php declare(strict_types=1);

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Query Logger
 *
 * Custom logger for database query monitoring and optimization
 * Tracks slow queries, query patterns, and database performance
 */
class QueryLogger
{
    /**
     * Add query context to log records
     */
    /**
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    public function addQueryContext(array $record): array
    {
        // Add database connection information
        $record['extra']['db_connection'] = config('database.default');
        $record['extra']['timestamp'] = now()->toDateTimeString();

        // Parse query information from message if available
        if (isset($record['context']['query'])) {
            $record['extra']['query_type'] = $this->getQueryType($record['context']['query']);
            $record['extra']['tables_involved'] = $this->extractTables($record['context']['query']);
        }

        // Add performance thresholds
        if (isset($record['context']['time'])) {
            $time = (float) $record['context']['time'];
            $record['extra']['is_slow_query'] = $time > config('error-tracking.performance.slow_query_threshold', 1000);
            $record['extra']['performance_category'] = $this->categorizePerformance($time);
        }

        return $record;
    }

    /**
     * Get query type (SELECT, INSERT, UPDATE, DELETE, etc.)
     */
    private function getQueryType(string $query): string
    {
        $query = trim(strtoupper($query));

        if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE)/', $query, $matches)) {
            return $matches[1];
        }

        return 'UNKNOWN';
    }

    /**
     * Extract table names from query
     */
    private function extractTables(string $query): array
    {
        $tables = [];
        $query = strtolower($query);

        // Simple regex to extract table names (this is basic, can be improved)
        if (preg_match_all('/(?:from|join|into|update)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $query, $matches)) {
            $tables = array_unique($matches[1]);
        }

        return array_values($tables);
    }

    /**
     * Categorize query performance
     */
    private function categorizePerformance(float $timeMs): string
    {
        if ($timeMs < 100) {
            return 'fast';
        }
        if ($timeMs < 500) {
            return 'acceptable';
        }
        if ($timeMs < 1000) {
            return 'slow';
        }

        return 'very_slow';
    }

    /**
     * Customize the given logger instance
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                // Use JSON formatter for better query analysis
                $formatter = new JsonFormatter();
                $handler->setFormatter($formatter);

                // Add query-specific processors
                $handler->pushProcessor([$this, 'addQueryContext']);
            }
        }
    }
}
