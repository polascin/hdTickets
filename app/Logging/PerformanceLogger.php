<?php declare(strict_types=1);

namespace App\Logging;

use Exception;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\DB;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;

use function defined;
use function function_exists;

/**
 * Performance Logger
 *
 * Custom logger for performance monitoring and metrics
 * Tracks request times, memory usage, and system performance
 */
class PerformanceLogger
{
    /**
     * Add performance context to log records
     * Compatible with both Monolog v2 (array) and v3+ (LogRecord)
     */
    public function addPerformanceContext(array|LogRecord $record): array|LogRecord
    {
        // Handle both Monolog v2 (array) and v3+ (LogRecord object)
        if ($record instanceof LogRecord) {
            // Monolog v3+ uses LogRecord objects
            $extra = $record->extra;
            $extra['response_time'] = $this->getResponseTime();
            $extra['cpu_usage'] = $this->getCpuUsage();
            $extra['load_average'] = $this->getLoadAverage();
            $extra['connection_count'] = $this->getConnectionCount();

            return $record->with(extra: $extra);
        }
        // Monolog v2 uses arrays
        $record['extra']['response_time'] = $this->getResponseTime();
        $record['extra']['cpu_usage'] = $this->getCpuUsage();
        $record['extra']['load_average'] = $this->getLoadAverage();
        $record['extra']['connection_count'] = $this->getConnectionCount();

        return $record;
    }

    /**
     * Get current response time since request start
     */
    private function getResponseTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(TRUE) - LARAVEL_START) * 1000, 2);
        }

        return 0.0;
    }

    /**
     * Get current CPU usage percentage
     */
    private function getCpuUsage(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg() ?: [0.0, 0.0, 0.0];

            return round($load[0] * 100, 2);
        }

        return 0.0;
    }

    /**
     * Get system load average
     */
    /**
     * @return array<int, float>
     */
    private function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            return (sys_getloadavg() ?: [0.0, 0.0, 0.0]) ?: [0.0, 0.0, 0.0];
        }

        return [0, 0, 0];
    }

    /**
     * Get database connection count
     */
    private function getConnectionCount(): int
    {
        try {
            $result = DB::connection()->getPdo()->query('SHOW STATUS LIKE "Threads_connected"')->fetchColumn(1);

            return (int) ($result ?? 0);
        } catch (Exception) {
            return 0;
        }
    }

    /**
     * Customize the given logger instance
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                // Add performance-specific processors
                $handler->pushProcessor(new MemoryUsageProcessor());
                $handler->pushProcessor(new ProcessIdProcessor());
                $handler->pushProcessor($this->addPerformanceContext(...));

                // Custom formatter for performance logs
                $formatter = new LineFormatter(
                    "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                    'Y-m-d H:i:s.u',
                    TRUE,
                    TRUE,
                );
                $handler->setFormatter($formatter);
            }
        }
    }
}
