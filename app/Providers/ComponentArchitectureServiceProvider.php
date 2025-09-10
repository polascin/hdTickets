<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ComponentCommunication;
use App\Services\ComponentLifecycleManager;
use App\Services\ComponentRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Override;

use function count;
use function in_array;
use function is_string;
use function strlen;

/**
 * Component Architecture Service Provider
 *
 * Registers and configures the component architecture services
 * for Blade, Alpine.js, and Vue.js components in the HD Tickets platform.
 */
class ComponentArchitectureServiceProvider extends ServiceProvider
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
        // Register component registry as singleton
        $this->app->singleton(ComponentRegistry::class, fn ($app): ComponentRegistry => new ComponentRegistry());

        // Register component communication service as singleton
        $this->app->singleton(ComponentCommunication::class, fn ($app): ComponentCommunication => new ComponentCommunication());

        // Register component lifecycle manager as singleton
        $this->app->singleton(ComponentLifecycleManager::class, fn ($app): ComponentLifecycleManager => new ComponentLifecycleManager());

        // Create aliases for easier access
        $this->app->alias(ComponentRegistry::class, 'component.registry');
        $this->app->alias(ComponentCommunication::class, 'component.communication');
        $this->app->alias(ComponentLifecycleManager::class, 'component.lifecycle');
    }

    /**
     * Bootstrap services
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        // Register Blade components and directives
        $this->registerBladeComponents();
        $this->registerBladeDirectives();

        // Setup component auto-discovery
        $this->setupComponentDiscovery();

        // Register view composers for component data
        $this->registerViewComposers();

        // Setup component validation
        $this->setupComponentValidation();
    }

    /**
     * Get services provided by this provider
     */
    /**
     * Provides
     */
    #[Override]
    public function provides(): array
    {
        return [
            ComponentRegistry::class,
            ComponentCommunication::class,
            ComponentLifecycleManager::class,
            'component.registry',
            'component.communication',
            'component.lifecycle',
        ];
    }

    /**
     * Register Blade components
     */
    /**
     * RegisterBladeComponents
     */
    private function registerBladeComponents(): void
    {
        // Register common UI components
        Blade::aliasComponent('components.tickets.ticket-card', 'ticket_card');

        // Register form components
        Blade::aliasComponent('components.forms.event-filter', 'event_filter');

        // Register layout components
        Blade::aliasComponent('components.ui.modal', 'modal');
        Blade::aliasComponent('components.ui.alert', 'alert');
        Blade::aliasComponent('components.ui.button', 'button');
        Blade::aliasComponent('components.ui.card', 'card');
        Blade::aliasComponent('components.ui.table', 'table');

        // Register dashboard components
        Blade::aliasComponent('components.dashboard.stat-card', 'stat_card');
        Blade::aliasComponent('components.dashboard.live-ticker', 'live_ticker');
        Blade::aliasComponent('components.dashboard.quick-actions', 'quick_actions');
    }

    /**
     * Register custom Blade directives for component integration
     */
    /**
     * RegisterBladeDirectives
     */
    private function registerBladeDirectives(): void
    {
        // Directive for Alpine.js data binding
        Blade::directive('alpine', function ($expression): string {
            $communication = app(ComponentCommunication::class);

            return "<?php echo {$communication->createBladeToAlpineBinding($expression)}; ?>";
        });

        // Directive for Vue.js prop binding
        Blade::directive('vueProps', function ($expression): string {
            $communication = app(ComponentCommunication::class);
            $props = eval("return {$expression};");

            return "<?php echo '{$communication->createBladeToVueBinding($props)}'; ?>";
        });

        // Directive for component registration
        Blade::directive('registerComponent', fn ($expression): string => "<?php app('component.registry')->register({$expression}); ?>");

        // Directive for component lifecycle initialization
        Blade::directive('initComponent', fn ($expression): string => "<?php app('component.lifecycle')->initialize({$expression}); ?>");

        // Directive for component mounting
        Blade::directive('mountComponent', fn ($expression): string => "<?php app('component.lifecycle')->mount({$expression}); ?>");

        // Directive for lazy-loading Vue components
        Blade::directive('lazyVue', fn ($expression): string => "<?php echo json_encode(app('component.registry')->lazyLoad({$expression})); ?>");

        // Directive for component validation
        Blade::directive('validateComponent', fn ($expression): string => "<?php 
                \$validation = app('component.registry')->validate({$expression});
                if (!\$validation['valid']) {
                    throw new \\Exception('Component validation failed: ' . implode(', ', \$validation['errors']));
                }
            ?>");

        // Directive for sport events specific data
        Blade::directive('sportsData', fn ($expression): string => "<?php 
                \$sportsData = [
                    'categories' => ['football', 'rugby', 'cricket', 'tennis', 'other'],
                    'platforms' => ['ticketmaster', 'stubhub', 'seatgeek', 'official'],
                    'availability' => ['available', 'limited', 'sold_out', 'on_hold']
                ];
                echo 'x-data=\"' . htmlspecialchars(json_encode(\$sportsData)) . '\"';
            ?>");

        // Directive for CSRF token in Alpine/Vue components
        Blade::directive('componentToken', fn (): string => "<?php echo 'data-csrf-token=\"' . csrf_token() . '\"'; ?>");
    }

    /**
     * Setup automatic component discovery
     */
    /**
     * Set up component discovery
     */
    private function setupComponentDiscovery(): void
    {
        $registry = app(ComponentRegistry::class);
        $lifecycle = app(ComponentLifecycleManager::class);

        // Auto-register discovered components with lifecycle management
        View::composer('*', function ($view) use ($registry, $lifecycle): void {
            $viewName = $view->name();

            // Check if this is a component view
            if (str_contains($viewName, 'components.')) {
                $componentName = str_replace('components.', '', $viewName);
                $componentType = $this->determineComponentType($viewName);

                // Register component if not already registered
                if (! $registry->get($componentName)) {
                    $config = [
                        'auto_discovered' => TRUE,
                        'view_name'       => $viewName,
                        'category'        => $this->extractCategoryFromPath($viewName),
                    ];

                    $registry->register($componentName, $componentType, $config);
                    $lifecycle->register($componentName, $componentType, $config);
                }
            }
        });
    }

    /**
     * Register view composers for component data injection
     */
    /**
     * RegisterViewComposers
     */
    private function registerViewComposers(): void
    {
        // Inject component registry data into admin views
        View::composer(['admin.*', 'dashboard.*'], function ($view): void {
            $registry = app(ComponentRegistry::class);
            $lifecycle = app(ComponentLifecycleManager::class);

            $view->with([
                'componentStats'   => $registry->getStats(),
                'lifecycleStats'   => $lifecycle->getLifecycleStats(),
                'activeComponents' => $lifecycle->getActiveComponents()->count(),
            ]);
        });

        // Inject sports-specific data for ticket components
        View::composer('components.tickets.*', function ($view): void {
            $view->with([
                'sportCategories' => [
                    'football' => 'Football',
                    'rugby'    => 'Rugby',
                    'cricket'  => 'Cricket',
                    'tennis'   => 'Tennis',
                    'other'    => 'Other Sports',
                ],
                'platforms' => [
                    'ticketmaster' => 'Ticketmaster',
                    'stubhub'      => 'StubHub',
                    'seatgeek'     => 'SeatGeek',
                    'official'     => 'Official Website',
                ],
                'availabilityStatuses' => [
                    'available' => 'Available',
                    'limited'   => 'Limited Availability',
                    'sold_out'  => 'Sold Out',
                    'on_hold'   => 'On Hold',
                ],
            ]);
        });

        // Inject component communication helpers
        View::composer('*', function ($view): void {
            $communication = app(ComponentCommunication::class);

            $view->with([
                'componentCommunication' => $communication,
                'communicationPatterns'  => $communication->getCommunicationPatterns(),
            ]);
        });
    }

    /**
     * Setup component validation rules
     */
    /**
     * Set up component validation
     */
    private function setupComponentValidation(): void
    {
        $communication = app(ComponentCommunication::class);

        // Register prop validators for sports events platform
        $communication->registerPropValidator('ticket_id', fn ($value): bool => is_string($value) && preg_match('/^TKT-[A-Z0-9]{6}$/', $value));

        $communication->registerPropValidator('event_id', fn ($value): bool => is_string($value) && preg_match('/^EVT-\d{6}$/', $value));

        $communication->registerPropValidator('price', fn ($value): bool => is_numeric($value) && $value >= 0);

        $communication->registerPropValidator('venue', fn ($value): bool => is_string($value) && strlen($value) >= 2 && strlen($value) <= 100);

        $communication->registerPropValidator('sport_category', fn ($value): bool => in_array($value, ['football', 'rugby', 'cricket', 'tennis', 'other'], TRUE));

        $communication->registerPropValidator('availability_status', fn ($value): bool => in_array($value, ['available', 'limited', 'sold_out', 'on_hold'], TRUE));

        $communication->registerPropValidator('platform_source', fn ($value): bool => in_array($value, ['ticketmaster', 'stubhub', 'seatgeek', 'official'], TRUE));

        $communication->registerPropValidator('date', fn ($value): bool => is_string($value) && strtotime($value) !== FALSE);
    }

    /**
     * Determine component type from view name
     */
    /**
     * DetermineComponentType
     */
    private function determineComponentType(string $viewName): string
    {
        // Check for Vue components (typically in JS directories or have .vue extension)
        if (str_contains($viewName, 'vue') || str_contains($viewName, 'js.components')) {
            return 'vue';
        }

        // Check for Alpine components (typically have alpine in name or path)
        if (str_contains($viewName, 'alpine') || str_contains($viewName, 'interactive')) {
            return 'alpine';
        }

        // Default to Blade component
        return 'blade';
    }

    /**
     * Extract category from component path
     */
    /**
     * ExtractCategoryFromPath
     */
    private function extractCategoryFromPath(string $viewName): string
    {
        $pathParts = explode('.', $viewName);

        if (count($pathParts) >= 3) {
            return $pathParts[1]; // components.CATEGORY.component-name
        }

        // Check for specific categories
        if (str_contains($viewName, 'ticket')) {
            return 'sports-tickets';
        }
        if (str_contains($viewName, 'dashboard')) {
            return 'dashboard';
        }
        if (str_contains($viewName, 'form')) {
            return 'forms';
        }
        if (str_contains($viewName, 'ui')) {
            return 'ui';
        }
        if (str_contains($viewName, 'admin')) {
            return 'admin';
        }
        if (str_contains($viewName, 'analytics')) {
            return 'analytics';
        }

        return 'general';
    }
}
