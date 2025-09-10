<?php declare(strict_types=1);

namespace App\Providers;

use App\Application\EventHandlers\Ticket\TicketDiscoveredHandler;
use App\Domain\Ticket\Events\TicketAvailabilityChanged;
use App\Domain\Ticket\Events\TicketDiscovered;
use App\Domain\Ticket\Events\TicketPriceChanged;
use App\Domain\Ticket\Events\TicketSoldOut;
use App\Infrastructure\EventBus\EventBusInterface;
use App\Infrastructure\EventBus\LaravelEventBus;
use App\Infrastructure\EventStore\EventStoreInterface;
use App\Infrastructure\EventStore\PostgreSqlEventStore;
use App\Infrastructure\Projections\ProjectionManager;
use App\Infrastructure\Projections\ProjectionManagerInterface;
use App\Infrastructure\Projections\TicketReadModelProjection;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Override;

class EventDrivenArchitectureServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    /**
     * Register
     */
    #[Override]
    public function register(): void
    {
        // Bind Event Store
        $this->app->singleton(EventStoreInterface::class, PostgreSqlEventStore::class);

        // Bind Event Bus
        $this->app->singleton(EventBusInterface::class, fn ($app): LaravelEventBus => new LaravelEventBus(
            $app->make(Dispatcher::class),
            $app->make(EventStoreInterface::class),
        ));

        // Bind Projection Manager
        $this->app->singleton(ProjectionManagerInterface::class, function ($app): ProjectionManager {
            $manager = new ProjectionManager(
                $app->make(EventStoreInterface::class),
            );

            // Register projections
            $manager->register(new TicketReadModelProjection());

            return $manager;
        });

        // Register Event Handlers
        $this->app->singleton(TicketDiscoveredHandler::class, fn ($app): TicketDiscoveredHandler => new TicketDiscoveredHandler(
            $app->make(EventBusInterface::class),
        ));
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
    #[Override]
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
            TicketDiscovered::class,
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
            TicketDiscovered::class,
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            TicketPriceChanged::class,
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            TicketAvailabilityChanged::class,
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );

        $eventBus->subscribe(
            TicketSoldOut::class,
            function ($event): void {
                $this->app->make(ProjectionManagerInterface::class)->project($event);
            },
        );
    }
}
