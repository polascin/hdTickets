<?php

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
        // Define gates for role-based authorization
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('agent-access', function (User $user) {
            return $user->isAgent() || $user->isAdmin();
        });

        Gate::define('customer-access', function (User $user) {
            return $user->isCustomer() || $user->isAgent() || $user->isAdmin();
        });

        // Define gates for specific permissions
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

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
