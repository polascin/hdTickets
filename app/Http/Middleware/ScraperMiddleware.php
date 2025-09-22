<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ScraperMiddleware
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
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        // Scrapers should be allowed for API routes, but not for web routes
        if (! $user->isScraper() && ! $user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Scraper role required.'], 403);
        }

        return $next($request);
    }
}
