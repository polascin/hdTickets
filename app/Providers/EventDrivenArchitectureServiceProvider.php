<?php declare(strict_types=1);

namespace App\Providers;

use App\Application\EventHandlers\Ticket\TicketDiscoveredHandler;
use App\Infrastructure\EventBus\EventBusInterface;
use App\Infrastructure\EventBus\LaravelEventBus;
use App\Infrastructure\EventStore\EventStoreInterface;
use App\Infrastructure\EventStore\PostgreSqlEventStore;
use App\Infrastructure\Projections\ProjectionManager;
use App\Infrastructure\Projections\ProjectionManagerInterface;
use App\Infrastructure\Projections\TicketReadModelProjection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventDrivenArchitectureServiceProvider extends ServiceProvider
{
    /** Indicates if loading of the provider is deferred. */
    protected $defer = TRUE;

    /**
     * Register any application services.
     */
    /**
     * Register
     */
    public function register(): void
    {
        // Bind Event Store
        $this->app->singleton(EventStoreInterface::class, PostgreSqlEventStore::class);

        // Bind Event Bus
        $this->app->singleton(EventBusInterface::class, function ($app) {
            return new LaravelEventBus(
                $app->make(Dispatcher::class),
                $app->make(EventStoreInterface::class),
            );
        });

        // Bind Projection Manager
        $this->app->singleton(ProjectionManagerInterface::class, function ($app) {
            $manager = new ProjectionManager(
                $app->make(EventStoreInterface::class),
            );

            // Register projections
            $manager->register(new TicketReadModelProjection());

            return $manager;
        });

        // Register Event Handlers
        $this->app->singleton(TicketDiscoveredHandler::class, function ($app) {
            return new TicketDiscoveredHandler(
                $app->make(EventBusInterface::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        // Register event listeners with Laravel's event dispatcher
        $this->registerEventListeners();

        // Set up event subscriptions
        $this->setupEventSubscriptions();
    }

    /**
     * Get the services provided by the provider.
     */
    /**
     * Provides
     */
    public function provides(): array
    {
        return [
            EventStoreInterface::class,
            EventBusInterface::class,
            ProjectionManagerInterface::class,
            TicketDiscoveredHandler::class,
        ];
    }

    /**
     * RegisterEventListeners
     */
    private function registerEventListeners(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        // Register domain event listeners
        $dispatcher->listen(
            'App\Domain\Ticket\Events\TicketDiscovered',
            [TicketDiscoveredHandler::class, 'handle'],
        );

        // Add more event listeners here as needed
        // $dispatcher->listen('App\Domain\Ticket\Events\TicketPriceChanged', [TicketPriceChangedHandler::class, 'handle']);
        // $dispatcher->listen('App\Domain\Purchase\Events\PurchaseInitiated', [PurchaseInitiatedHandler::class, 'handle']);
    }

    /**
     * Set up event subscriptions
     */
    private function setupEventSubscriptions(): void
    {
        $eventBus = $this->app->make(EventBusInterface::class);

        // Subscribe to domain events for projection updates
        $eventBus->subscribe(
            'App\Domain\Ticket\Events\TicketDiscovered',
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            'App\Domain\Ticket\Events\TicketPriceChanged',
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            'App\Domain\Ticket\Events\TicketAvailabilityChanged',
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            'App\Domain\Ticket\Events\TicketSoldOut',
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );
    }
}
