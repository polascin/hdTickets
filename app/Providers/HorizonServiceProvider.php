<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

use function in_array;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = NULL) {
            // In local environment, allow all authenticated users
            if (app()->environment('local')) {
                return $user !== null;
            }
            
            // In production, check against specific admin emails
            // This should be configured via environment variables or config files
            $adminEmail = config('horizon.admin_email');
            
            return $user && $adminEmail && $user->email === $adminEmail;
        });
    }
}
