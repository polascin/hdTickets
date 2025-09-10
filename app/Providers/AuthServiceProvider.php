<?php declare(strict_types=1);

namespace App\Providers;

use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    /**
     * Boot
     */
    public function boot(): void
    {
        // Configure Passport
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // SYSTEM ACCESS GATES
        Gate::define('access-system', function (User $user): bool {
            return $user->canAccessSystem(); // Blocks scrapers
        });

        Gate::define('web-login', function (User $user): bool {
            return $user->canLoginToWeb(); // Blocks scrapers
        });

        // ROLE-BASED ACCESS GATES
        Gate::define('admin-access', fn (User $user): bool => $user->isAdmin());

        Gate::define('agent-access', fn (User $user): bool => $user->isAgent() || $user->isAdmin());

        Gate::define('customer-access', fn (User $user): bool => $user->isCustomer() || $user->isAgent() || $user->isAdmin());

        // ADMIN PERMISSION GATES (System & Platform Configuration)
        Gate::define('manage-users', fn (User $user): bool => $user->canManageUsers());

        Gate::define('manage-system', fn (User $user): bool => $user->canManageSystem());

        Gate::define('manage-platforms', fn (User $user): bool => $user->canManagePlatforms());

        Gate::define('access-financials', fn (User $user): bool => $user->canAccessFinancials());

        Gate::define('manage-api-access', fn (User $user): bool => $user->canManageApiAccess());

        Gate::define('delete-any-data', fn (User $user): bool => $user->canDeleteAnyData());

        Gate::define('access_reports', fn (User $user): bool => $user->isAdmin());

        Gate::define('manage_users', fn (User $user): bool => $user->canManageUsers());

        // AGENT PERMISSION GATES (Ticket Selection, Purchasing, Monitoring)
        Gate::define('select-purchase-tickets', fn (User $user): bool => $user->canSelectAndPurchaseTickets());

        Gate::define('make-purchase-decisions', fn (User $user): bool => $user->canMakePurchaseDecisions());

        Gate::define('manage-monitoring', fn (User $user): bool => $user->canManageMonitoring());

        Gate::define('view-scraping-metrics', fn (User $user): bool => $user->canViewScrapingMetrics());

        // LEGACY GATES (for backward compatibility)
        Gate::define('manage-tickets', fn (User $user): bool => $user->isAgent() || $user->isAdmin());

        Gate::define('view-analytics', fn (User $user): bool => $user->isAdmin());

        Gate::define('create-tickets', fn (User $user): bool => $user->isCustomer() || $user->isAgent() || $user->isAdmin());
    }
}
