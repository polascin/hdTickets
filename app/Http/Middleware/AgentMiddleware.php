<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        if (! $user->isAgent() && ! $user->isAdmin()) {
            abort(403, 'Access denied. Agent role required.');
        }

        return $next($request);
    }
}
