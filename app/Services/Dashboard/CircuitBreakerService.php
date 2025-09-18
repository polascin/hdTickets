<?php declare(strict_types=1);

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreakerService
{
    private const CACHE_PREFIX = 'circuit_breaker:';
    private const DEFAULT_FAILURE_THRESHOLD = 5;
    private const DEFAULT_RECOVERY_TIMEOUT = 60; // seconds
    private const DEFAULT_REQUEST_TIMEOUT = 10; // seconds

    /**
     * Circuit breaker states
     */
    private const STATE_CLOSED = 'closed';      // Normal operation
    private const STATE_OPEN = 'open';          // Circuit is open, failing fast
    private const STATE_HALF_OPEN = 'half_open'; // Testing if service is back

    /**
     * Execute a callable with circuit breaker protection
     */
    public function execute(string $serviceName, callable $callback, array $options = []): mixed
    {
        $failureThreshold = $options['failure_threshold'] ?? self::DEFAULT_FAILURE_THRESHOLD;
        $recoveryTimeout = $options['recovery_timeout'] ?? self::DEFAULT_RECOVERY_TIMEOUT;
        $requestTimeout = $options['request_timeout'] ?? self::DEFAULT_REQUEST_TIMEOUT;

        $state = $this->getCircuitState($serviceName);

        switch ($state) {
            case self::STATE_OPEN:
                if ($this->shouldAttemptReset($serviceName, $recoveryTimeout)) {
                    $this->setCircuitState($serviceName, self::STATE_HALF_OPEN);
                    return $this->executeWithTimeout($serviceName, $callback, $requestTimeout, $failureThreshold);
                }
                
                // Circuit is open, fail fast
                Log::warning("Circuit breaker is OPEN for service: {$serviceName}");
                throw new \Exception("Service {$serviceName} is currently unavailable (circuit breaker is open)");

            case self::STATE_HALF_OPEN:
                return $this->executeWithTimeout($serviceName, $callback, $requestTimeout, $failureThreshold);

            case self::STATE_CLOSED:
            default:
                return $this->executeWithTimeout($serviceName, $callback, $requestTimeout, $failureThreshold);
        }
    }

    /**
     * Execute callback with timeout and failure tracking
     */
    private function executeWithTimeout(string $serviceName, callable $callback, int $timeout, int $failureThreshold): mixed
    {
        $startTime = microtime(true);

        try {
            // Set a timeout for the operation
            $result = $this->executeWithTimeLimit($callback, $timeout);
            
            $executionTime = microtime(true) - $startTime;
            
            // Record successful execution
            $this->recordSuccess($serviceName);
            
            Log::debug("Circuit breaker SUCCESS for service: {$serviceName}", [
                'execution_time' => $executionTime,
                'state' => $this->getCircuitState($serviceName),
            ]);
            
            return $result;
            
        } catch (\Throwable $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Record failure
            $failures = $this->recordFailure($serviceName);
            
            Log::warning("Circuit breaker FAILURE for service: {$serviceName}", [
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'total_failures' => $failures,
                'failure_threshold' => $failureThreshold,
            ]);
            
            // Check if we should open the circuit
            if ($failures >= $failureThreshold) {
                $this->openCircuit($serviceName);
                Log::error("Circuit breaker OPENED for service: {$serviceName}", [
                    'failures' => $failures,
                    'threshold' => $failureThreshold,
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Execute with time limit (simplified - in production use proper async handling)
     */
    private function executeWithTimeLimit(callable $callback, int $timeout): mixed
    {
        // This is a simplified timeout implementation
        // In production, you would use proper async execution with timeouts
        $startTime = time();
        
        $result = $callback();
        
        $executionTime = time() - $startTime;
        if ($executionTime > $timeout) {
            throw new \Exception("Operation timed out after {$executionTime} seconds");
        }
        
        return $result;
    }

    /**
     * Get current circuit state
     */
    private function getCircuitState(string $serviceName): string
    {
        return Cache::get($this->getStateKey($serviceName), self::STATE_CLOSED);
    }

    /**
     * Set circuit state
     */
    private function setCircuitState(string $serviceName, string $state): void
    {
        Cache::put($this->getStateKey($serviceName), $state, 3600); // 1 hour TTL
    }

    /**
     * Record successful execution
     */
    private function recordSuccess(string $serviceName): void
    {
        // Reset failure count on success
        Cache::forget($this->getFailureKey($serviceName));
        
        // If we were in half-open state, close the circuit
        if ($this->getCircuitState($serviceName) === self::STATE_HALF_OPEN) {
            $this->setCircuitState($serviceName, self::STATE_CLOSED);
            Log::info("Circuit breaker CLOSED for service: {$serviceName} (recovered)");
        }
    }

    /**
     * Record failure and return total failure count
     */
    private function recordFailure(string $serviceName): int
    {
        $failureKey = $this->getFailureKey($serviceName);
        $failures = Cache::get($failureKey, 0) + 1;
        
        Cache::put($failureKey, $failures, 3600); // 1 hour TTL
        
        return $failures;
    }

    /**
     * Open the circuit breaker
     */
    private function openCircuit(string $serviceName): void
    {
        $this->setCircuitState($serviceName, self::STATE_OPEN);
        Cache::put($this->getOpenedAtKey($serviceName), time(), 3600);
    }

    /**
     * Check if we should attempt to reset the circuit
     */
    private function shouldAttemptReset(string $serviceName, int $recoveryTimeout): bool
    {
        $openedAt = Cache::get($this->getOpenedAtKey($serviceName));
        
        if (!$openedAt) {
            return true; // No record of when it was opened, allow reset
        }
        
        return (time() - $openedAt) >= $recoveryTimeout;
    }

    /**
     * Get circuit breaker statistics
     */
    public function getStats(string $serviceName): array
    {
        return [
            'service_name' => $serviceName,
            'state' => $this->getCircuitState($serviceName),
            'failure_count' => Cache::get($this->getFailureKey($serviceName), 0),
            'opened_at' => Cache::get($this->getOpenedAtKey($serviceName)),
            'last_check' => time(),
        ];
    }

    /**
     * Reset circuit breaker for a service
     */
    public function reset(string $serviceName): void
    {
        Cache::forget($this->getStateKey($serviceName));
        Cache::forget($this->getFailureKey($serviceName));
        Cache::forget($this->getOpenedAtKey($serviceName));
        
        Log::info("Circuit breaker RESET for service: {$serviceName}");
    }

    /**
     * Get all circuit breaker statistics
     */
    public function getAllStats(): array
    {
        $keys = Cache::getRedis()->keys(self::CACHE_PREFIX . '*');
        $services = [];
        
        foreach ($keys as $key) {
            $keyParts = explode(':', $key);
            if (count($keyParts) >= 3) {
                $serviceName = $keyParts[2];
                if (!in_array($serviceName, $services)) {
                    $services[] = $serviceName;
                }
            }
        }
        
        return array_map(fn($service) => $this->getStats($service), $services);
    }

    /**
     * Cache key helpers
     */
    private function getStateKey(string $serviceName): string
    {
        return self::CACHE_PREFIX . "state:{$serviceName}";
    }

    private function getFailureKey(string $serviceName): string
    {
        return self::CACHE_PREFIX . "failures:{$serviceName}";
    }

    private function getOpenedAtKey(string $serviceName): string
    {
        return self::CACHE_PREFIX . "opened_at:{$serviceName}";
    }
}