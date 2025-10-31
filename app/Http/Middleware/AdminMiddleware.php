<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @param string|null                  $permission Optional permission parameter
     */
    /**
     * Handle
     */
    public function handle(Request $request, Closure $next, ?string $permission = NULL): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
