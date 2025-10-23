<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Observers\ScrapedTicketObserver;
use App\Observers\TicketAlertObserver;
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
        // Application service registration
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        ScrapedTicket::observe(ScrapedTicketObserver::class);
        TicketAlert::observe(TicketAlertObserver::class);
    }
}
