<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\Core\ServiceOrchestrator;
use App\Services\Interfaces\NotificationInterface;
use App\Services\Interfaces\PurchaseAutomationInterface;
use App\Services\Interfaces\ScrapingInterface;
use App\Services\Interfaces\TicketMonitoringInterface;
use Exception;
use Illuminate\Support\ServiceProvider;
use Log;

/**
 * Consolidated Service Provider
 *
 * Registers all consolidated services for the HD Tickets
 * sport events entry ticket monitoring and purchase system.
 */
class ConsolidatedServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    /**
     * Register
     */
    public function register(): void
    {
        // Register the service orchestrator as singleton
        $this->app->singleton(ServiceOrchestrator::class, function ($app) {
            return new ServiceOrchestrator();
        });

        // Register service interfaces as singletons
        $this->registerServiceInterfaces();

        // Register concrete services through orchestrator
        $this->registerConcreteServices();
    }

    /**
     * Bootstrap services
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        // Initialize the service orchestrator
        $orchestrator = $this->app->make(ServiceOrchestrator::class);

        try {
            $orchestrator->initialize();

            // Log successful initialization
            Log::info('Consolidated services initialized successfully', [
                'services' => $orchestrator->getAvailableServices(),
                'health'   => $orchestrator->getHealthStatus()['overall_health'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to initialize consolidated services', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't throw in production to prevent application crash
            if (app()->environment('local', 'testing')) {
                throw $e;
            }
        }
    }

    /**
     * Get the services provided by the provider
     */
    /**
     * Provides
     */
    public function provides(): array
    {
        return [
            ServiceOrchestrator::class,
            ScrapingInterface::class,
            TicketMonitoringInterface::class,
            PurchaseAutomationInterface::class,
            NotificationInterface::class,
            'encryptionService',
            'cacheService',
            'queueService',
            'analyticsService',
            'notificationService',
            'scrapingService',
            'ticketMonitoringService',
            'purchaseAutomationService',
            'userService',
            'authenticationService',
        ];
    }

    /**
     * Register service interfaces
     */
    /**
     * RegisterServiceInterfaces
     */
    private function registerServiceInterfaces(): void
    {
        // Scraping Service Interface
        $this->app->bind(ScrapingInterface::class, function ($app) {
            return $app->make(ServiceOrchestrator::class)->getService('scrapingService');
        });

        // Ticket Monitoring Interface
        $this->app->bind(TicketMonitoringInterface::class, function ($app) {
            return $app->make(ServiceOrchestrator::class)->getService('ticketMonitoringService');
        });

        // Purchase Automation Interface
        $this->app->bind(PurchaseAutomationInterface::class, function ($app) {
            return $app->make(ServiceOrchestrator::class)->getService('purchaseAutomationService');
        });

        // Notification Interface
        $this->app->bind(NotificationInterface::class, function ($app) {
            return $app->make(ServiceOrchestrator::class)->getService('notificationService');
        });
    }

    /**
     * Register concrete services
     */
    /**
     * RegisterConcreteServices
     */
    private function registerConcreteServices(): void
    {
        // Register each service as a singleton through the orchestrator
        $serviceNames = [
            'encryptionService',
            'cacheService',
            'queueService',
            'analyticsService',
            'notificationService',
            'scrapingService',
            'ticketMonitoringService',
            'purchaseAutomationService',
            'userService',
            'authenticationService',
        ];

        foreach ($serviceNames as $serviceName) {
            $this->app->singleton($serviceName, function ($app) use ($serviceName) {
                return $app->make(ServiceOrchestrator::class)->getService($serviceName);
            });
        }

        // Register legacy service aliases for backward compatibility
        $this->registerLegacyAliases();
    }

    /**
     * Register legacy service aliases for backward compatibility
     */
    /**
     * RegisterLegacyAliases
     */
    private function registerLegacyAliases(): void
    {
        // Analytics Service (keep existing)
        $this->app->alias('analyticsService', \App\Services\AnalyticsService::class);

        // Encryption Service (keep existing)
        $this->app->alias('encryptionService', \App\Services\EncryptionService::class);

        // Notification Service (enhanced version)
        $this->app->alias('notificationService', \App\Services\NotificationService::class);

        // Legacy scraping services -> consolidated scraping service
        $legacyScrapingServices = [
            \App\Services\TicketScrapingService::class,
            \App\Services\Scraping\HighDemandTicketScraperService::class,
            \App\Services\Scraping\PluginBasedScraperManager::class,
        ];

        foreach ($legacyScrapingServices as $legacyService) {
            $this->app->bind($legacyService, function ($app) {
                return $app->make('scrapingService');
            });
        }

        // Legacy monitoring services -> consolidated monitoring service
        $legacyMonitoringServices = [
            \App\Services\RealTimeMonitoringService::class,
            \App\Services\PlatformMonitoringService::class,
            \App\Services\PerformanceMonitoringService::class,
        ];

        foreach ($legacyMonitoringServices as $legacyService) {
            $this->app->bind($legacyService, function ($app) {
                return $app->make('ticketMonitoringService');
            });
        }

        // Legacy purchase services -> consolidated purchase automation
        $legacyPurchaseServices = [
            \App\Services\PurchaseService::class,
            \App\Services\AutomatedPurchaseEngine::class,
            \App\Services\PurchaseAnalyticsService::class,
        ];

        foreach ($legacyPurchaseServices as $legacyService) {
            $this->app->bind($legacyService, function ($app) {
                return $app->make('purchaseAutomationService');
            });
        }

        // Legacy notification services -> consolidated notification service
        $legacyNotificationServices = [
            \App\Services\NotificationManager::class,
            \App\Services\InAppNotificationService::class,
            \App\Services\EnhancedAlertSystem::class,
            \App\Services\AlertEscalationService::class,
        ];

        foreach ($legacyNotificationServices as $legacyService) {
            $this->app->bind($legacyService, function ($app) {
                return $app->make('notificationService');
            });
        }
    }
}
