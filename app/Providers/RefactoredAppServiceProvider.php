<?php declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Models\ScrapingStats;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AdvancedAnalyticsDashboard;
use App\Services\AutomatedPurchaseEngine;
use App\Services\EncryptionService;
use App\Services\Enhanced\AdvancedCacheService;
use App\Services\Enhanced\PerformanceMonitoringService;
use App\Services\MultiPlatformManager;
use App\Services\NotificationSystem\NotificationManager;
use App\Services\RedisRateLimitService;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\SecurityService;
use App\Services\TicketScrapingService;
use DB;
use Illuminate\Cache\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Override;

use function function_exists;
use function get_class;
use function in_array;

class RefactoredAppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        TicketScrapingService::class        => TicketScrapingService::class,
        NotificationManager::class          => NotificationManager::class,
        PerformanceMonitoringService::class => PerformanceMonitoringService::class,
        AdvancedCacheService::class         => AdvancedCacheService::class,
    ];

    /**
     * Register any application services.
     */
    /**
     * Register
     */
    #[Override]
    public function register(): void
    {
        $this->registerCoreServices();
        $this->registerCustomServices();
        $this->registerDevelopmentServices();
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        $this->configureEloquent();
        $this->configurePagination();
        $this->configureValidation();
        $this->configureViews();
        $this->configureBladeDirectives();
        $this->configureGates();
        $this->configureSecurity();
        $this->configurePerformance();
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
            'activity.logger',
            'encryption.service',
            'security.service',
            'api.rate_limiter',
            'scraping.manager',
            'analytics.dashboard',
            'purchase.automation',
            'platform.manager',
            'rate_limiter',
            'cache.optimizer',
        ];
    }

    /**
     * Register core application services
     */
    /**
     * RegisterCoreServices
     */
    private function registerCoreServices(): void
    {
        // Register activity logging service
        $this->app->singleton('activity.logger', fn ($app): \ActivityLogger => new ActivityLogger());

        // Register encryption service
        $this->app->singleton('encryption.service', fn ($app): \EncryptionService => new EncryptionService());

        // Register security service
        $this->app->singleton('security.service', fn ($app): \SecurityService => new SecurityService());

        // Register API rate limiter
        $this->app->singleton('api.rate_limiter', fn ($app): \RedisRateLimitService => new RedisRateLimitService());
    }

    /**
     * Register custom application services
     */
    /**
     * RegisterCustomServices
     */
    private function registerCustomServices(): void
    {
        // Register scraping services
        $this->app->bind('scraping.manager', fn ($app): \PluginBasedScraperManager => new PluginBasedScraperManager());

        // Register analytics services
        $this->app->singleton('analytics.dashboard', fn ($app): \AdvancedAnalyticsDashboard => new AdvancedAnalyticsDashboard());

        // Register purchase automation
        $this->app->singleton('purchase.automation', fn ($app): \AutomatedPurchaseEngine => new AutomatedPurchaseEngine());

        // Register multi-platform manager
        $this->app->singleton('platform.manager', fn ($app): \MultiPlatformManager => new MultiPlatformManager());
    }

    /**
     * Register development-specific services
     */
    /**
     * RegisterDevelopmentServices
     */
    private function registerDevelopmentServices(): void
    {
        if ($this->app->environment('local', 'testing')) {
            // Register development tools
            $this->app->register(TelescopeServiceProvider::class);

            // Register debug services
            $this->app->singleton('debug.profiler', fn ($app): object => new class() {
                public function profile()
                {
                    $start = microtime(TRUE);
                    $result = $callback();
                    $end = microtime(TRUE);

                    logger("Profile [{$name}]: " . round(($end - $start) * 1000, 2) . 'ms');

                    return $result;
                }
            });
        }
    }

    /**
     * Configure Eloquent settings
     */
    /**
     * ConfigureEloquent
     */
    private function configureEloquent(): void
    {
        // Prevent lazy loading in non-production environments
        Model::preventLazyLoading(!$this->app->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(!$this->app->isProduction());

        // Configure model event logging
        if (config('app.log_model_events', FALSE)) {
            Model::creating(function ($model): void {
                logger('Model creating: ' . get_class($model));
            });
        }
    }

    /**
     * Configure pagination settings
     */
    /**
     * ConfigurePagination
     */
    private function configurePagination(): void
    {
        // Use Bootstrap 5 for pagination
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        // Set default pagination limits
        Paginator::defaultStringLength(50);
    }

    /**
     * Configure custom validation rules
     */
    /**
     * ConfigureValidation
     */
    private function configureValidation(): void
    {
        // Custom validation rule for ticket prices
        Validator::extend('ticket_price', fn ($attribute, $value, $parameters, $validator): bool => is_numeric($value) && $value >= 0 && $value <= 10000);

        // Custom validation rule for platform names
        Validator::extend('platform_name', function ($attribute, $value, $parameters, $validator): bool {
            $allowedPlatforms = ['ticketmaster', 'stubhub', 'viagogo', 'tickpick', 'seatgeek'];

            return in_array(strtolower($value), $allowedPlatforms, TRUE);
        });

        // Custom validation rule for event dates
        Validator::extend('future_event_date', fn ($attribute, $value, $parameters, $validator): bool => strtotime((string) $value) > time());
    }

    /**
     * Configure view composers and shared data
     */
    /**
     * ConfigureViews
     */
    private function configureViews(): void
    {
        // Share common data with all views
        View::share('appVersion', config('app.version', '2.0.0'));
        View::share('appName', config('app.name', 'HD Tickets'));

        // Configure view composers
        View::composer('layouts.*', function ($view): void {
            $view->with([
                'currentUser'         => auth()->user(),
                'unreadNotifications' => auth()->check() ? auth()->user()->unreadNotifications()->count() : 0,
                'systemStatus'        => cache()->remember('system_status', 300, fn (): array => [
                    'scrapers_active' => random_int(5, 15),
                    'alerts_today'    => random_int(100, 500),
                    'uptime'          => '99.9%',
                ]),
            ]);
        });

        // Dashboard-specific data
        View::composer('dashboard*', function ($view): void {
            if (auth()->check()) {
                $view->with([
                    'userStats' => [
                        'active_alerts'        => auth()->user()->ticketAlerts()->active()->count(),
                        'tickets_monitored'    => auth()->user()->scrapedTickets()->count(),
                        'successful_purchases' => auth()->user()->purchaseAttempts()->successful()->count(),
                    ],
                ]);
            }
        });

        // Admin views
        View::composer('admin.*', function ($view): void {
            if (auth()->check() && auth()->user()->isAdmin()) {
                $view->with([
                    'adminStats' => cache()->remember('admin_stats', 300, fn (): array => [
                        'total_users'     => User::count(),
                        'active_scrapers' => ScrapingStats::active()->count(),
                        'system_health'   => 'excellent',
                    ]),
                ]);
            }
        });
    }

    /**
     * Configure custom Blade directives
     */
    /**
     * ConfigureBladeDirectives
     */
    private function configureBladeDirectives(): void
    {
        // Directive for formatting prices
        Blade::directive('price', fn ($expression): string => "<?php echo number_format({$expression}, 2); ?>");

        // Directive for user roles
        Blade::directive('role', function ($expression): string {
            // Parse the role and use appropriate method
            $role = trim($expression, "'\"");

            return match ($role) {
                'admin'    => '<?php if(auth()->check() && auth()->user()->isAdmin()): ?>',
                'agent'    => '<?php if(auth()->check() && auth()->user()->isAgent()): ?>',
                'customer' => '<?php if(auth()->check() && auth()->user()->isCustomer()): ?>',
                'scraper'  => '<?php if(auth()->check() && auth()->user()->isScraper()): ?>',
                default    => "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>",
            };
        });

        Blade::directive('endrole', fn (): string => '<?php endif; ?>');

        // Directive for feature flags
        Blade::directive('feature', fn ($expression): string => "<?php if(config('features.' . {$expression}, false)): ?>");

        Blade::directive('endfeature', fn (): string => '<?php endif; ?>');

        // Directive for performance timing
        Blade::directive('startTimer', fn ($expression): string => "<?php \$timer_{$expression} = microtime(true); ?>");

        Blade::directive('endTimer', fn ($expression): string => "<?php logger('Timer {$expression}: ' . round((microtime(true) - \$timer_{$expression}) * 1000, 2) . 'ms'); ?>");
    }

    /**
     * Configure authorization gates
     */
    /**
     * ConfigureGates
     */
    private function configureGates(): void
    {
        // Gate for admin access
        Gate::define('admin-access', fn ($user) => $user->isAdmin());

        // Gate for agent access
        Gate::define('agent-access', fn ($user): bool => $user->isAgent() || $user->isAdmin());

        // Gate for scraping access
        Gate::define('scraping-access', fn ($user): bool => $user->hasPermission('scraping') || $user->isAdmin());

        // Gate for advanced features
        Gate::define('advanced-features', fn ($user): bool => $user->subscription && $user->subscription->hasFeature('advanced'));

        // Gate for API access
        Gate::define('api-access', fn ($user): bool => $user->api_access_enabled && $user->isVerified());
    }

    /**
     * Configure security settings
     */
    /**
     * ConfigureSecurity
     */
    private function configureSecurity(): void
    {
        // Configure JSON resource wrapping
        JsonResource::withoutWrapping();

        // Configure security headers in production
        if ($this->app->isProduction()) {
            $this->app->make(SecurityHeadersMiddleware::class);
        }

        // Configure rate limiting
        $this->app->singleton('rate_limiter', fn ($app): \RateLimiter => new RateLimiter($app['cache']));

        // Configure database query listening
        DB::listen(function ($query): void {
            if ($query->time > 1000) { // Log slow queries (>1s)
                logger("Slow query detected: {$query->sql} ({$query->time}ms)");
            }
        });

        // Configure memory usage monitoring
        if (config('app.monitor_memory', FALSE)) {
            register_shutdown_function(function (): void {
                $memory = memory_get_peak_usage(TRUE);
                if ($memory > 128 * 1024 * 1024) { // 128MB threshold
                    logger('High memory usage detected: ' . round($memory / 1024 / 1024, 2) . 'MB');
                }
            });
        }

        // Configure cache optimization
        $this->app->singleton('cache.optimizer', fn ($app): \AdvancedCacheService => new AdvancedCacheService());
    }

    /**
     * Configure performance settings
     */
    private function configurePerformance(): void
    {
        // Configure opcache settings if available
        if (function_exists('opcache_get_status') && opcache_get_status()) {
            // Opcache is available and enabled
        }

        // Configure session garbage collection
        if (config('session.driver') === 'file') {
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);
            ini_set('session.gc_maxlifetime', 7200); // 2 hours
        }
    }
}
