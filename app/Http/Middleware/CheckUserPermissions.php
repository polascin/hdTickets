<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

class CheckUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @param string[]                     $roles
     */
    /**
     * Handle
     *
     * @param mixed $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Account is disabled. Contact administrator.');
        }

        if (!in_array($user->role, $roles, TRUE)) {
            return redirect()->route('dashboard.basic');
        }

        return $next($request);
    }
}
