<?php declare(strict_types=1);

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

class CheckApiRole
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @param string[]                     $roles Variable number of role arguments
     */
    /**
     * Handle
     *
     * @param mixed $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if user has any of the required roles
        if (! in_array($user->role, $roles, TRUE)) {
            return response()->json([
                'message' => 'Forbidden. Required roles: ' . implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
