<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    /**
     * Register
     */
    public function register(): void
    {
        // Register a custom environment service that doesn't conflict with Laravel's core
        $this->app->singleton('app.environment', function ($app) {
            return $app->environment();
        });

        // Register environment-specific configurations
        $this->app->singleton('environment.config', function () {
            return [
                'name'          => config('app.env', 'production'),
                'debug'         => config('app.debug', FALSE),
                'is_production' => config('app.env') === 'production',
                'is_local'      => config('app.env') === 'local',
                'is_testing'    => config('app.env') === 'testing',
            ];
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
    }
}
