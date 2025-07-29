<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ActivityLogger;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
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
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            ActivityLogger::class,
            'log.activity',
        ];
    }
}
