<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Override;

class AppServiceProvider extends ServiceProvider
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
        // Prevent running vendor Passport migrations as we maintain project-specific ones
        if (class_exists(Passport::class) && method_exists(Passport::class, 'ignoreMigrations')) {
            Passport::ignoreMigrations();
        }
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
