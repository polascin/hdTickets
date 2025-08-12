<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the 'env' service to the container
        $this->app->instance('env', env('APP_ENV', 'production'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
