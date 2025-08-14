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
                'name'          => env('APP_ENV', 'production'),
                'debug'         => env('APP_DEBUG', FALSE),
                'is_production' => config('APP_ENV') === 'production',
                'is_local'      => config('APP_ENV') === 'local',
                'is_testing'    => config('APP_ENV') === 'testing',
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
