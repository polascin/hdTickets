<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        'App\Services\TicketScrapingService'                  => 'App\Services\TicketScrapingService',
        'App\Services\NotificationSystem\NotificationManager' => 'App\Services\NotificationSystem\NotificationManager',
        'App\Services\Enhanced\PerformanceMonitoringService'  => 'App\Services\Enhanced\PerformanceMonitoringService',
        'App\Services\Enhanced\AdvancedCacheService'          => 'App\Services\Enhanced\AdvancedCacheService',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerCoreServices();
        $this->registerCustomServices();
        $this->registerDevelopmentServices();
    }

    /**
     * Bootstrap any application services.
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
    private function registerCoreServices(): void
    {
        // Register activity logging service
        $this->app->singleton('activity.logger', function ($app) {
            return new \App\Services\ActivityLogger();
        });

        // Register encryption service
        $this->app->singleton('encryption.service', function ($app) {
            return new \App\Services\EncryptionService();
        });

        // Register security service
        $this->app->singleton('security.service', function ($app) {
            return new \App\Services\SecurityService();
        });

        // Register API rate limiter
        $this->app->singleton('api.rate_limiter', function ($app) {
            return new \App\Services\RedisRateLimitService();
        });
    }

    /**
     * Register custom application services
     */
    private function registerCustomServices(): void
    {
        // Register scraping services
        $this->app->bind('scraping.manager', function ($app) {
            return new \App\Services\Scraping\PluginBasedScraperManager();
        });

        // Register analytics services
        $this->app->singleton('analytics.dashboard', function ($app) {
            return new \App\Services\AdvancedAnalyticsDashboard();
        });

        // Register purchase automation
        $this->app->singleton('purchase.automation', function ($app) {
            return new \App\Services\AutomatedPurchaseEngine();
        });

        // Register multi-platform manager
        $this->app->singleton('platform.manager', function ($app) {
            return new \App\Services\MultiPlatformManager();
        });
    }

    /**
     * Register development-specific services
     */
    private function registerDevelopmentServices(): void
    {
        if ($this->app->environment('local', 'testing')) {
            // Register development tools
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);

            // Register debug services
            $this->app->singleton('debug.profiler', function ($app) {
                return new class() {
                    public function profile($callback, $name = 'operation')
                    {
                        $start = microtime(TRUE);
                        $result = $callback();
                        $end = microtime(TRUE);

                        logger("Profile [{$name}]: " . round(($end - $start) * 1000, 2) . 'ms');

                        return $result;
                    }
                };
            });
        }
    }

    /**
     * Configure Eloquent settings
     */
    private function configureEloquent(): void
    {
        // Prevent lazy loading in non-production environments
        Model::preventLazyLoading(! $this->app->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());

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
    private function configureValidation(): void
    {
        // Custom validation rule for ticket prices
        Validator::extend('ticket_price', function ($attribute, $value, $parameters, $validator) {
            return is_numeric($value) && $value >= 0 && $value <= 10000;
        });

        // Custom validation rule for platform names
        Validator::extend('platform_name', function ($attribute, $value, $parameters, $validator) {
            $allowedPlatforms = ['ticketmaster', 'stubhub', 'viagogo', 'tickpick', 'seatgeek'];

            return in_array(strtolower($value), $allowedPlatforms, TRUE);
        });

        // Custom validation rule for event dates
        Validator::extend('future_event_date', function ($attribute, $value, $parameters, $validator) {
            return strtotime($value) > time();
        });
    }

    /**
     * Configure view composers and shared data
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
                'systemStatus'        => cache()->remember('system_status', 300, function () {
                    return [
                        'scrapers_active' => random_int(5, 15),
                        'alerts_today'    => random_int(100, 500),
                        'uptime'          => '99.9%',
                    ];
                }),
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
                    'adminStats' => cache()->remember('admin_stats', 300, function () {
                        return [
                            'total_users'     => \App\Models\User::count(),
                            'active_scrapers' => \App\Models\ScrapingStats::active()->count(),
                            'system_health'   => 'excellent',
                        ];
                    }),
                ]);
            }
        });
    }

    /**
     * Configure custom Blade directives
     */
    private function configureBladeDirectives(): void
    {
        // Directive for formatting prices
        Blade::directive('price', function ($expression) {
            return "<?php echo number_format({$expression}, 2); ?>";
        });

        // Directive for user roles
        Blade::directive('role', function ($expression) {
            // Parse the role and use appropriate method
            $role = trim($expression, "'\"");
            switch ($role) {
                case 'admin':
                    return '<?php if(auth()->check() && auth()->user()->isAdmin()): ?>';
                case 'agent':
                    return '<?php if(auth()->check() && auth()->user()->isAgent()): ?>';
                case 'customer':
                    return '<?php if(auth()->check() && auth()->user()->isCustomer()): ?>';
                case 'scraper':
                    return '<?php if(auth()->check() && auth()->user()->isScraper()): ?>';
                default:
                    return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
            }
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });

        // Directive for feature flags
        Blade::directive('feature', function ($expression) {
            return "<?php if(config('features.' . {$expression}, false)): ?>";
        });

        Blade::directive('endfeature', function () {
            return '<?php endif; ?>';
        });

        // Directive for performance timing
        Blade::directive('startTimer', function ($expression) {
            return "<?php \$timer_{$expression} = microtime(true); ?>";
        });

        Blade::directive('endTimer', function ($expression) {
            return "<?php logger('Timer {$expression}: ' . round((microtime(true) - \$timer_{$expression}) * 1000, 2) . 'ms'); ?>";
        });
    }

    /**
     * Configure authorization gates
     */
    private function configureGates(): void
    {
        // Gate for admin access
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        // Gate for agent access
        Gate::define('agent-access', function ($user) {
            return $user->isAgent() || $user->isAdmin();
        });

        // Gate for scraping access
        Gate::define('scraping-access', function ($user) {
            return $user->hasPermission('scraping') || $user->isAdmin();
        });

        // Gate for advanced features
        Gate::define('advanced-features', function ($user) {
            return $user->subscription && $user->subscription->hasFeature('advanced');
        });

        // Gate for API access
        Gate::define('api-access', function ($user) {
            return $user->api_access_enabled && $user->isVerified();
        });
    }

    /**
     * Configure security settings
     */
    private function configureSecurity(): void
    {
        // Configure JSON resource wrapping
        JsonResource::withoutWrapping();

        // Configure security headers in production
        if ($this->app->isProduction()) {
            $this->app->make('App\Http\Middleware\SecurityHeadersMiddleware');
        }

        // Configure rate limiting
        $this->app->singleton('rate_limiter', function ($app) {
            return new \App\Services\ApiRateLimiter();
        });
    }

    /**
     * Configure performance optimizations
     */
    private function configurePerformance(): void
    {
        // Configure query optimization
        if (config('database.log_queries', FALSE)) {
            \Illuminate\Support\Facades\DB::listen(function ($query): void {
                if ($query->time > 1000) { // Log slow queries (>1s)
                    logger("Slow query detected: {$query->sql} ({$query->time}ms)");
                }
            });
        }

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
        $this->app->singleton('cache.optimizer', function ($app) {
            return new \App\Services\Enhanced\AdvancedCacheService();
        });
    }
}
