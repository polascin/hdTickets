<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\AdvancedAnalyticsDashboard;
use App\Services\AutomatedPurchaseEngine;
use App\Services\PurchaseAnalyticsService;
use App\Services\PurchaseService;
use Illuminate\Support\ServiceProvider;
use Override;

class PurchaseServiceProvider extends ServiceProvider
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
        $this->app->singleton(PurchaseAnalyticsService::class, fn ($app): PurchaseAnalyticsService => new PurchaseAnalyticsService());

        $this->app->singleton(AdvancedAnalyticsDashboard::class, fn ($app): AdvancedAnalyticsDashboard => new AdvancedAnalyticsDashboard());

        // Bind AutomatedPurchaseEngine with the correct dependency signature
        $this->app->singleton(AutomatedPurchaseEngine::class, fn ($app): AutomatedPurchaseEngine => new AutomatedPurchaseEngine(
            $app->make(AdvancedAnalyticsDashboard::class),
        ));

        $this->app->singleton(PurchaseService::class, fn ($app): PurchaseService => new PurchaseService());
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
}
