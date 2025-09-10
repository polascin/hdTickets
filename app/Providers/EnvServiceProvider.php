<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Override;

class EnvServiceProvider extends ServiceProvider
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
        // Register a custom environment service that doesn't conflict with Laravel's core
        $this->app->singleton('app.environment', fn ($app) => $app->environment());

        // Register environment-specific configurations
        $this->app->singleton('environment.config', fn (): array => [
            'name'          => config('app.env', 'production'),
            'debug'         => config('app.debug', FALSE),
            'is_production' => config('app.env') === 'production',
            'is_local'      => config('app.env') === 'local',
            'is_testing'    => config('app.env') === 'testing',
        ]);
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
