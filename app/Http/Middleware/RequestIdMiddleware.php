<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->headers->get('X-Request-Id') ?: Uuid::uuid4()->toString();
        // Add to request for downstream usage
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        // Add header for correlation
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
