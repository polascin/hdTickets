<?php

namespace App\Providers;

use App\Services\AutomatedPurchaseEngine;
use App\Services\PurchaseAnalyticsService;
use App\Services\AdvancedAnalyticsDashboard;
use App\Services\PurchaseService;
use Illuminate\Support\ServiceProvider;

class PurchaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PurchaseAnalyticsService::class, function ($app) {
            return new PurchaseAnalyticsService();
        });

        $this->app->singleton(AdvancedAnalyticsDashboard::class, function ($app) {
            return new AdvancedAnalyticsDashboard();
        });

        $this->app->singleton(AutomatedPurchaseEngine::class, function ($app) {
            return new AutomatedPurchaseEngine(
                $app->make(PurchaseAnalyticsService::class),
                $app->make(AdvancedAnalyticsDashboard::class)
            );
        });

        $this->app->singleton(PurchaseService::class, function ($app) {
            return new PurchaseService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
