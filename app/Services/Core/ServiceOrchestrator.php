<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Services\AnalyticsService;
use App\Services\EncryptionService;
use App\Services\Interfaces\ServiceInterface;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

use function count;
use function in_array;

/**
 * Service Orchestrator
 *
 * Manages the lifecycle and dependencies of all consolidated services
 * in the HD Tickets sport events entry ticket monitoring system.
 */
class ServiceOrchestrator
{
    private array $services = [];

    private array $serviceDefinitions = [];

    private array $dependencyGraph = [];

    private bool $initialized = FALSE;

    public function __construct()
    {
        $this->defineServices();
    }

    /**
     * Initialize all services with proper dependency injection
     */
    /**
     * Initialize
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            // Resolve dependency order
            $initializationOrder = $this->resolveDependencyOrder();

            // Initialize services in dependency order
            foreach ($initializationOrder as $serviceName) {
                $this->initializeService($serviceName);
            }

            $this->initialized = TRUE;

            Log::info('Service orchestrator initialized successfully', [
                'total_services'       => count($this->services),
                'initialization_order' => $initializationOrder,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to initialize service orchestrator', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get service instance
     */
    /**
     * Get  service
     */
    public function getService(string $serviceName): ServiceInterface
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        if (! isset($this->services[$serviceName])) {
            throw new InvalidArgumentException("Service '{$serviceName}' not found");
        }

        return $this->services[$serviceName];
    }

    /**
     * Check if service exists
     */
    /**
     * Check if has  service
     */
    public function hasService(string $serviceName): bool
    {
        return isset($this->services[$serviceName]);
    }

    /**
     * Get all available services
     */
    /**
     * Get  available services
     */
    public function getAvailableServices(): array
    {
        return array_keys($this->serviceDefinitions);
    }

    /**
     * Get service health status
     */
    /**
     * Get  health status
     */
    public function getHealthStatus(): array
    {
        $healthData = [
            'orchestrator_status' => $this->initialized ? 'healthy' : 'not_initialized',
            'total_services'      => count($this->services),
            'services'            => [],
            'overall_health'      => 'unknown',
            'timestamp'           => Carbon::now()->toISOString(),
        ];

        $healthyServices = 0;
        $totalServices = count($this->services);

        foreach ($this->services as $name => $service) {
            try {
                $serviceHealth = $service->getHealthStatus();
                $healthData['services'][$name] = $serviceHealth;

                if ($serviceHealth['status'] === 'healthy') {
                    $healthyServices++;
                }
            } catch (Exception $e) {
                $healthData['services'][$name] = [
                    'status' => 'error',
                    'error'  => $e->getMessage(),
                ];
            }
        }

        // Calculate overall health
        if ($totalServices > 0) {
            $healthPercentage = ($healthyServices / $totalServices) * 100;
            $healthData['overall_health'] = match (TRUE) {
                $healthPercentage >= 90 => 'excellent',
                $healthPercentage >= 75 => 'healthy',
                $healthPercentage >= 50 => 'warning',
                default                 => 'critical',
            };
            $healthData['health_percentage'] = round($healthPercentage, 2);
        }

        return $healthData;
    }

    /**
     * Restart service
     */
    /**
     * RestartService
     */
    public function restartService(string $serviceName): bool
    {
        try {
            if (! isset($this->services[$serviceName])) {
                throw new InvalidArgumentException("Service '{$serviceName}' not found");
            }

            // Cleanup current service
            $this->services[$serviceName]->cleanup();

            // Reinitialize service
            $this->initializeService($serviceName);

            Log::info('Service restarted successfully', ['service' => $serviceName]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to restart service', [
                'service' => $serviceName,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Shutdown all services gracefully
     */
    /**
     * Shutdown
     */
    public function shutdown(): void
    {
        Log::info('Starting service orchestrator shutdown');

        foreach ($this->services as $name => $service) {
            try {
                $service->cleanup();
                Log::info('Service shutdown completed', ['service' => $name]);
            } catch (Exception $e) {
                Log::error('Service shutdown failed', [
                    'service' => $name,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->services = [];
        $this->initialized = FALSE;

        Log::info('Service orchestrator shutdown completed');
    }

    /**
     * Get service statistics
     */
    /**
     * Get  service statistics
     */
    public function getServiceStatistics(): array
    {
        return [
            'total_services'      => count($this->services),
            'initialized'         => $this->initialized,
            'memory_usage'        => memory_get_usage(TRUE),
            'peak_memory'         => memory_get_peak_usage(TRUE),
            'service_definitions' => array_keys($this->serviceDefinitions),
            'dependency_graph'    => $this->dependencyGraph,
            'uptime'              => $this->initialized ? time() - $this->getInitializationTime() : 0,
        ];
    }

    /**
     * Define all services and their dependencies
     */
    /**
     * DefineServices
     */
    private function defineServices(): void
    {
        $this->serviceDefinitions = [
            // Core infrastructure services (no dependencies)
            'encryptionService' => [
                'class'        => EncryptionService::class,
                'dependencies' => [],
            ],

            'cacheService' => [
                'class'        => CacheService::class,
                'dependencies' => [],
            ],

            'queueService' => [
                'class'        => QueueService::class,
                'dependencies' => [],
            ],

            // Analytics service (depends on cache)
            'analyticsService' => [
                'class'        => AnalyticsService::class,
                'dependencies' => ['cacheService'],
            ],

            // Notification service (depends on analytics)
            'notificationService' => [
                'class'        => NotificationService::class,
                'dependencies' => ['analyticsService', 'encryptionService'],
            ],

            // Scraping service (depends on cache, analytics, encryption)
            'scrapingService' => [
                'class'        => ScrapingService::class,
                'dependencies' => ['cacheService', 'analyticsService', 'encryptionService'],
            ],

            // Ticket monitoring (depends on scraping, notification, analytics)
            'ticketMonitoringService' => [
                'class'        => TicketMonitoringService::class,
                'dependencies' => ['scrapingService', 'notificationService', 'analyticsService'],
            ],

            // Purchase automation (depends on monitoring, notification, analytics, encryption)
            'purchaseAutomationService' => [
                'class'        => PurchaseAutomationService::class,
                'dependencies' => ['ticketMonitoringService', 'notificationService', 'analyticsService', 'encryptionService'],
            ],

            // User service
            'userService' => [
                'class'        => UserService::class,
                'dependencies' => ['encryptionService', 'analyticsService'],
            ],

            // Authentication service
            'authenticationService' => [
                'class'        => AuthenticationService::class,
                'dependencies' => ['encryptionService', 'userService'],
            ],
        ];

        $this->buildDependencyGraph();
    }

    /**
     * Build dependency graph for services
     */
    /**
     * BuildDependencyGraph
     */
    private function buildDependencyGraph(): void
    {
        $this->dependencyGraph = [];

        foreach ($this->serviceDefinitions as $serviceName => $definition) {
            $this->dependencyGraph[$serviceName] = $definition['dependencies'];
        }
    }

    /**
     * Resolve service initialization order based on dependencies
     */
    /**
     * ResolveDependencyOrder
     */
    private function resolveDependencyOrder(): array
    {
        $resolved = [];
        $resolving = [];

        $resolve = function ($serviceName) use (&$resolve, &$resolved, &$resolving): void {
            if (in_array($serviceName, $resolved, TRUE)) {
                return;
            }

            if (in_array($serviceName, $resolving, TRUE)) {
                throw new RuntimeException("Circular dependency detected for service: {$serviceName}");
            }

            $resolving[] = $serviceName;

            foreach ($this->dependencyGraph[$serviceName] as $dependency) {
                $resolve($dependency);
            }

            $resolved[] = $serviceName;
            $resolving = array_filter($resolving, fn ($s): bool => $s !== $serviceName);
        };

        foreach (array_keys($this->serviceDefinitions) as $serviceName) {
            $resolve($serviceName);
        }

        return $resolved;
    }

    /**
     * Initialize individual service
     */
    /**
     * InitializeService
     */
    private function initializeService(string $serviceName): void
    {
        if (! isset($this->serviceDefinitions[$serviceName])) {
            throw new InvalidArgumentException("Service definition not found: {$serviceName}");
        }

        $definition = $this->serviceDefinitions[$serviceName];

        // Create service instance
        $serviceClass = $definition['class'];
        if (! class_exists($serviceClass)) {
            throw new RuntimeException("Service class not found: {$serviceClass}");
        }

        $service = new $serviceClass();

        if (! $service instanceof ServiceInterface) {
            throw new RuntimeException("Service must implement ServiceInterface: {$serviceClass}");
        }

        // Prepare dependencies
        $dependencies = [];
        foreach ($definition['dependencies'] as $dependencyName) {
            if (! isset($this->services[$dependencyName])) {
                throw new RuntimeException("Dependency not available: {$dependencyName} for service: {$serviceName}");
            }
            $dependencies[$dependencyName] = $this->services[$dependencyName];
        }

        // Initialize service with dependencies
        $service->initialize($dependencies);

        $this->services[$serviceName] = $service;

        Log::info('Service initialized', [
            'service'      => $serviceName,
            'class'        => $serviceClass,
            'dependencies' => array_keys($dependencies),
        ]);
    }

    /**
     * Get initialization time from cache
     */
    /**
     * Get  initialization time
     */
    private function getInitializationTime(): int
    {
        return Cache::get('service_orchestrator_init_time', time());
    }
}
