<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Services\Interfaces\ServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * Abstract Base Service
 *
 * Provides common functionality for all services including
 * error handling, logging, and service lifecycle management.
 */
abstract class BaseService implements ServiceInterface
{
    protected array $dependencies = [];

    protected bool $initialized = FALSE;

    protected Carbon $startTime;

    protected array $config = [];

    public function __construct()
    {
        $this->startTime = Carbon::now();
        $this->loadConfiguration();
    }

    /**
     * Initialize the service with dependencies
     */
    /**
     * Initialize
     */
    public function initialize(array $dependencies = []): void
    {
        $this->dependencies = $dependencies;
        $this->initialized = TRUE;
        $this->onInitialize();

        Log::info('Service initialized', [
            'service'      => static::class,
            'dependencies' => array_keys($dependencies),
        ]);
    }

    /**
     * Get service health status
     */
    /**
     * Get  health status
     */
    public function getHealthStatus(): array
    {
        return [
            'service'      => static::class,
            'status'       => $this->initialized ? 'healthy' : 'not_initialized',
            'uptime'       => $this->startTime->diffInSeconds(Carbon::now()),
            'memory_usage' => memory_get_usage(TRUE),
            'last_check'   => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Clean up resources when service is destroyed
     */
    /**
     * Cleanup
     */
    public function cleanup(): void
    {
        $this->onCleanup();
        $this->initialized = FALSE;

        Log::info('Service cleaned up', [
            'service' => static::class,
            'uptime'  => $this->startTime->diffInSeconds(Carbon::now()),
        ]);
    }

    /**
     * Get dependency by name
     */
    /**
     * Get  dependency
     */
    protected function getDependency(string $name): mixed
    {
        return $this->dependencies[$name] ?? NULL;
    }

    /**
     * Check if service has required dependency
     */
    /**
     * Check if has  dependency
     */
    protected function hasDependency(string $name): bool
    {
        return isset($this->dependencies[$name]);
    }

    /**
     * Ensure service is initialized before operation
     */
    /**
     * EnsureInitialized
     */
    protected function ensureInitialized(): void
    {
        if (! $this->initialized) {
            throw new RuntimeException('Service must be initialized before use');
        }
    }

    /**
     * Handle service errors consistently
     */
    /**
     * HandleError
     */
    protected function handleError(Exception $exception, string $operation, array $context = []): void
    {
        Log::error('Service operation failed', [
            'service'   => static::class,
            'operation' => $operation,
            'error'     => $exception->getMessage(),
            'context'   => $context,
            'trace'     => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Log service operation
     */
    /**
     * LogOperation
     */
    protected function logOperation(string $operation, array $context = []): void
    {
        Log::info('Service operation', [
            'service'   => static::class,
            'operation' => $operation,
            'context'   => $context,
        ]);
    }

    /**
     * Load service configuration
     */
    /**
     * LoadConfiguration
     */
    protected function loadConfiguration(): void
    {
        $serviceName = $this->getServiceConfigKey();
        $this->config = config($serviceName, []);
    }

    /**
     * Get configuration value
     */
    /**
     * Get  config
     */
    protected function getConfig(string $key, mixed $default = NULL): mixed
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Get service configuration key
     */
    /**
     * Get  service config key
     */
    protected function getServiceConfigKey(): string
    {
        $className = class_basename(static::class);

        return 'services.' . strtolower(str_replace('Service', '', $className));
    }

    /**
     * Hook for service initialization
     */
    /**
     * OnInitialize
     */
    protected function onInitialize(): void
    {
        // Override in child classes
    }

    /**
     * Hook for service cleanup
     */
    /**
     * OnCleanup
     */
    protected function onCleanup(): void
    {
        // Override in child classes
    }

    /**
     * Validate required dependencies
     */
    /**
     * ValidateDependencies
     */
    protected function validateDependencies(array $required): void
    {
        foreach ($required as $dependency) {
            if (! $this->hasDependency($dependency)) {
                throw new InvalidArgumentException("Required dependency '{$dependency}' not provided");
            }
        }
    }
}
