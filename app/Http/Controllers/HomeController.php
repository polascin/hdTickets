<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Main dashboard router/dispatcher - handles role-based routing after authentication
     *
     * This method serves as the central hub for authenticated users, determining
     * which dashboard to redirect them to based on their role. It does NOT render
     * any views directly - only redirects to appropriate dashboard routes.
     *
     * Role-based routing logic:
     * - admin: Full administrative access to all system features
     * - agent: Sales/support agents with limited admin capabilities
     * - scraper: Technical users focused on ticket scraping operations
     * - customer/default: Regular users purchasing tickets
     */
    /**
     * Index
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        // Ensure user is authenticated before proceeding with role-based routing
        $user = Auth::user();

        if (!$user) {
            Log::info('Unauthenticated user attempted to access dashboard, redirecting to login');

            return redirect()->route('login');
        }

        // Determine appropriate dashboard based on user role and log the redirection
        switch ($user->role) {
            case 'admin':
                Log::info('Admin user redirected to admin dashboard', ['user_id' => $user->id, 'email' => $user->email]);

                return redirect()->route('admin.dashboard');
            case 'agent':
                Log::info('Agent user redirected to agent dashboard', ['user_id' => $user->id, 'email' => $user->email]);

                return redirect()->route('dashboard.agent');
            case 'scraper':
                Log::info('Scraper user redirected to scraper dashboard', ['user_id' => $user->id, 'email' => $user->email]);

                return redirect()->route('dashboard.scraper');
            default:
                // Handle customers and any undefined roles with customer dashboard
                Log::info('User redirected to customer dashboard', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'role'    => $user->role ?? 'undefined',
                ]);

                return redirect()->route('dashboard.customer');
        }
    }

    /**
     * Display the application home/welcome page for non-authenticated users
     *
     * This method serves the main landing page of the HD Tickets sports events
     * entry ticket monitoring and purchase system. It's typically accessed
     * before user authentication.
     */
    /**
     * Welcome
     */
    public function welcome(): \Illuminate\Contracts\View\View
    {
        Log::info('Welcome page accessed by unauthenticated user');

        return view('welcome');
    }
}
