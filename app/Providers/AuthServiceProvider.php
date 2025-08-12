<?php declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Ticket::class => \App\Policies\TicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // SYSTEM ACCESS GATES
        Gate::define('access-system', function (User $user) {
            return $user->canAccessSystem(); // Blocks scrapers
        });

        Gate::define('web-login', function (User $user) {
            return $user->canLoginToWeb(); // Blocks scrapers
        });

        // ROLE-BASED ACCESS GATES
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('agent-access', function (User $user) {
            return $user->isAgent() || $user->isAdmin();
        });

        Gate::define('customer-access', function (User $user) {
            return $user->isCustomer() || $user->isAgent() || $user->isAdmin();
        });

        // ADMIN PERMISSION GATES (System & Platform Configuration)
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-system', function (User $user) {
            return $user->canManageSystem();
        });

        Gate::define('manage-platforms', function (User $user) {
            return $user->canManagePlatforms();
        });

        Gate::define('access-financials', function (User $user) {
            return $user->canAccessFinancials();
        });

        Gate::define('manage-api-access', function (User $user) {
            return $user->canManageApiAccess();
        });

        Gate::define('delete-any-data', function (User $user) {
            return $user->canDeleteAnyData();
        });

        Gate::define('access_reports', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage_users', function (User $user) {
            return $user->canManageUsers();
        });

        // AGENT PERMISSION GATES (Ticket Selection, Purchasing, Monitoring)
        Gate::define('select-purchase-tickets', function (User $user) {
            return $user->canSelectAndPurchaseTickets();
        });

        Gate::define('make-purchase-decisions', function (User $user) {
            return $user->canMakePurchaseDecisions();
        });

        Gate::define('manage-monitoring', function (User $user) {
            return $user->canManageMonitoring();
        });

        Gate::define('view-scraping-metrics', function (User $user) {
            return $user->canViewScrapingMetrics();
        });

        // LEGACY GATES (for backward compatibility)
        Gate::define('manage-tickets', function (User $user) {
            return $user->isAgent() || $user->isAdmin();
        });

        Gate::define('view-analytics', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('create-tickets', function (User $user) {
            return $user->isCustomer() || $user->isAgent() || $user->isAdmin();
        });
    }
}
