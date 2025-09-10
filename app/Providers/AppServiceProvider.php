<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
