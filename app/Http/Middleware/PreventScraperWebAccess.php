<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\UserAgentHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
    /**
     * Handle
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is logged in and is a scraper
        if ($user && $user->isScraper()) {
            try {
                // Get device information safely
                $deviceInfo = UserAgentHelper::getDeviceInfo($request);
                $userAgent = UserAgentHelper::sanitise($deviceInfo['user_agent'] ?? NULL);

                // Log the unauthorized access attempt
                Log::warning('Scraper user attempted web access', [
                    'user_id'     => $user->id,
                    'username'    => $user->username,
                    'ip'          => $request->ip(),
                    'user_agent'  => $userAgent,
                    'device_info' => $deviceInfo,
                    'url'         => $request->fullUrl(),
                ]);

                // Log iOS-specific attempts for monitoring
                if ($deviceInfo['is_ios']) {
                    UserAgentHelper::logIOSRequest($request, 'scraper_web_access_attempt');
                }
            } catch (Throwable $e) {
                // Fallback logging if user agent parsing fails
                Log::warning('Scraper user attempted web access (UA parsing failed)', [
                    'user_id'  => $user->id,
                    'username' => $user->username,
                    'ip'       => $request->ip(),
                    'url'      => $request->fullUrl(),
                    'error'    => $e->getMessage(),
                ]);
            }

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
