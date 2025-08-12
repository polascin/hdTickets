<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Symfony\Component\HttpFoundation\Response;

class PreventScraperWebAccess
{
    /**
     * Handle an incoming request.
     *
     * Prevent scraper users from accessing the web interface.
     * Scraper users are meant for rotation only and should have no system access.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is logged in and is a scraper
        if ($user && $user->isScraper()) {
            // Log the unauthorized access attempt
            Log::warning('Scraper user attempted web access', [
                'user_id'    => $user->id,
                'username'   => $user->username,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url'        => $request->fullUrl(),
            ]);

            // Log out the scraper user and redirect with error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'access_denied' => 'Access denied. This account type cannot access the web interface.',
            ]);
        }

        return $next($request);
    }
}
