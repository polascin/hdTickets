<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugAuthMiddleware
{
    /**
     * Handle an incoming request and log authentication debugging information
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->logAuthenticationState($request);
        
        $response = $next($request);
        
        $this->logRedirectionInfo($request, $response);
        
        return $response;
    }

    /**
     * Log current authentication state
     */
    private function logAuthenticationState(Request $request): void
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();
        
        $authData = [
            'timestamp' => now()->toISOString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $sessionId,
            'is_authenticated' => Auth::check(),
            'guard' => Auth::getDefaultDriver(),
        ];

        if ($user) {
            $authData['user'] = [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'email_verified' => $user->email_verified_at !== null,
                'last_login_at' => $user->last_login_at?->toISOString(),
            ];
            
            $authData['user_permissions'] = [
                'is_admin' => $user->isAdmin(),
                'is_agent' => $user->isAgent(),
                'is_customer' => $user->isCustomer(),
                'is_scraper' => $user->isScraper(),
                'can_access_system' => $user->canAccessSystem(),
                'can_login_to_web' => $user->canLoginToWeb(),
                'can_manage_users' => $user->canManageUsers(),
                'can_select_and_purchase_tickets' => $user->canSelectAndPurchaseTickets(),
                'can_make_purchase_decisions' => $user->canMakePurchaseDecisions(),
                'can_manage_monitoring' => $user->canManageMonitoring(),
            ];
        } else {
            $authData['user'] = null;
            $authData['user_permissions'] = null;
        }

        // Log session data (be careful with sensitive information)
        $sessionData = [];
        if ($request->hasSession()) {
            $sessionData = [
                'session_started' => $request->session()->isStarted(),
                'has_session_token' => $request->session()->has('_token'),
                'session_regenerated' => $request->session()->regenerated,
            ];
            
            // Add session authentication data if available
            if ($request->session()->has('login_web_' . Auth::getDefaultDriver() . '_' . sha1(Auth::getProvider()->getModel()))) {
                $sessionData['auth_session_exists'] = true;
            } else {
                $sessionData['auth_session_exists'] = false;
            }
        }
        
        $authData['session'] = $sessionData;

        Log::channel('auth_debug')->info('Authentication State Check', $authData);
    }

    /**
     * Log redirection information
     */
    private function logRedirectionInfo(Request $request, Response $response): void
    {
        $redirectData = [
            'timestamp' => now()->toISOString(),
            'url' => $request->fullUrl(),
            'response_status' => $response->getStatusCode(),
            'is_redirect' => $response->isRedirection(),
        ];

        if ($response->isRedirection()) {
            $redirectData['redirect_location'] = $response->headers->get('Location');
            
            $user = Auth::user();
            if ($user) {
                $redirectData['user_role'] = $user->role;
                $redirectData['user_id'] = $user->id;
                
                // Log the expected vs actual redirect based on role
                if ($user->isAdmin()) {
                    $redirectData['expected_redirect'] = 'admin.dashboard';
                } elseif ($user->isAgent()) {
                    $redirectData['expected_redirect'] = 'agent.dashboard';
                } else {
                    $redirectData['expected_redirect'] = 'dashboard.basic';
                }
            }
        }

        Log::channel('auth_debug')->info('Response and Redirection Info', $redirectData);
    }
}
