<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        $user = Auth::user();

        // Check if user is admin
        if (!$user->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Account is disabled. Contact administrator.');
        }

        // Check specific permission if provided
        if ($permission) {
            $permissions = $user->getPermissions();
            
            if (!isset($permissions[$permission]) || !$permissions[$permission]) {
                abort(403, "Access denied. Missing permission: {$permission}");
            }
        }

        return $next($request);
    }
}
