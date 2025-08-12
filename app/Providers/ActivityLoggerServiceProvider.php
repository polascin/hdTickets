<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Support\ServiceProvider;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the ActivityLogger service as a singleton
        $this->app->singleton(ActivityLogger::class, function ($app) {
            return new ActivityLogger();
        });

        // Bind 'log.activity' to the ActivityLogger class
        $this->app->bind('log.activity', ActivityLogger::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ActivityLogger::class,
            'log.activity',
        ];
    }
}
