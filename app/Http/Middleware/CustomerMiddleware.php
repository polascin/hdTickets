<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    /**
     * Handle
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Allow customers and admins to access customer dashboard
        // Admins have hierarchical access to all dashboards
        if (! ($user->isCustomer() || $user->isAdmin())) {
            abort(403, 'Access denied. Customer role or admin privileges required.');
        }

        return $next($request);
    }
}
