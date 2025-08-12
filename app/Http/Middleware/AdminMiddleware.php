<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, ?string $permission = NULL)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
