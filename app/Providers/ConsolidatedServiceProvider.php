<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\AlertEscalationService;
use App\Services\AnalyticsService;
use App\Services\AutomatedPurchaseEngine;
use App\Services\Core\ServiceOrchestrator;
use App\Services\EncryptionService;
use App\Services\EnhancedAlertSystem;
use App\Services\InAppNotificationService;
use App\Services\Interfaces\NotificationInterface;
use App\Services\Interfaces\PurchaseAutomationInterface;
use App\Services\Interfaces\ScrapingInterface;
use App\Services\Interfaces\TicketMonitoringInterface;
use App\Services\NotificationManager;
use App\Services\NotificationService;
use App\Services\PerformanceMonitoringService;
use App\Services\PlatformMonitoringService;
use App\Services\PurchaseAnalyticsService;
use App\Services\PurchaseService;
use App\Services\RealTimeMonitoringService;
use App\Services\Scraping\HighDemandTicketScraperService;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\TicketScrapingService;
use Exception;
use Illuminate\Support\ServiceProvider;
use Log;
use Override;

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
    #[Override]
    public function register(): void
    {
        // Register the service orchestrator as singleton
        $this->app->singleton(ServiceOrchestrator::class, fn ($app): ServiceOrchestrator => new ServiceOrchestrator());

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
    #[Override]
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
        $this->app->bind(ScrapingInterface::class, fn ($app) => $app->make(ServiceOrchestrator::class)->getService('scrapingService'));

        // Ticket Monitoring Interface
        $this->app->bind(TicketMonitoringInterface::class, fn ($app) => $app->make(ServiceOrchestrator::class)->getService('ticketMonitoringService'));

        // Purchase Automation Interface
        $this->app->bind(PurchaseAutomationInterface::class, fn ($app) => $app->make(ServiceOrchestrator::class)->getService('purchaseAutomationService'));

        // Notification Interface
        $this->app->bind(NotificationInterface::class, fn ($app) => $app->make(ServiceOrchestrator::class)->getService('notificationService'));
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
            $this->app->singleton($serviceName, fn ($app) => $app->make(ServiceOrchestrator::class)->getService($serviceName));
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
        $this->app->alias('analyticsService', AnalyticsService::class);

        // Encryption Service (keep existing)
        $this->app->alias('encryptionService', EncryptionService::class);

        // Notification Service (enhanced version)
        $this->app->alias('notificationService', NotificationService::class);

        // Legacy scraping services -> consolidated scraping service
        $legacyScrapingServices = [
            TicketScrapingService::class,
            HighDemandTicketScraperService::class,
            PluginBasedScraperManager::class,
        ];

        foreach ($legacyScrapingServices as $legacyService) {
            $this->app->bind($legacyService, fn ($app) => $app->make('scrapingService'));
        }

        // Legacy monitoring services -> consolidated monitoring service
        $legacyMonitoringServices = [
            RealTimeMonitoringService::class,
            PlatformMonitoringService::class,
            PerformanceMonitoringService::class,
        ];

        foreach ($legacyMonitoringServices as $legacyService) {
            $this->app->bind($legacyService, fn ($app) => $app->make('ticketMonitoringService'));
        }

        // Legacy purchase services -> consolidated purchase automation
        $legacyPurchaseServices = [
            PurchaseService::class,
            AutomatedPurchaseEngine::class,
            PurchaseAnalyticsService::class,
        ];

        foreach ($legacyPurchaseServices as $legacyService) {
            $this->app->bind($legacyService, fn ($app) => $app->make('purchaseAutomationService'));
        }

        // Legacy notification services -> consolidated notification service
        $legacyNotificationServices = [
            NotificationManager::class,
            InAppNotificationService::class,
            EnhancedAlertSystem::class,
            AlertEscalationService::class,
        ];

        foreach ($legacyNotificationServices as $legacyService) {
            $this->app->bind($legacyService, fn ($app) => $app->make('notificationService'));
        }
    }
}
