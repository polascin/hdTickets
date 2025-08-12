<?php declare(strict_types=1);

namespace App\Services\Interfaces;

/**
 * Base Service Interface
 *
 * All services should implement this interface to ensure consistent
 * dependency injection and service lifecycle management.
 */
interface ServiceInterface
{
    /**
     * Initialize the service with dependencies
     */
    public function initialize(array $dependencies = []): void;

    /**
     * Get service health status
     */
    public function getHealthStatus(): array;

    /**
     * Clean up resources when service is destroyed
     */
    public function cleanup(): void;
}
