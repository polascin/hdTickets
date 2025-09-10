<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Support\ServiceProvider;
use Override;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    /**
     * Register
     */
    #[Override]
    public function register(): void
    {
        // Bind the ActivityLogger service as a singleton
        $this->app->singleton(ActivityLogger::class, fn ($app): ActivityLogger => new ActivityLogger());

        // Bind 'log.activity' to the ActivityLogger class
        $this->app->bind('log.activity', ActivityLogger::class);
    }

    /**
     * Bootstrap services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
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
            ActivityLogger::class,
            'log.activity',
        ];
    }
}
