<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

/**
 * Enhanced Security Headers Middleware
 *
 * Provides advanced security headers including nonce-based CSP,
 * improved HTTPS enforcement, and comprehensive security monitoring.
 */
class EnhancedSecurityHeadersMiddleware
{
    /**
     * Handle an incoming request with enhanced security headers
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique nonce for this request
        $nonce = base64_encode(Str::random(32));

        // Store nonce for view usage
        View::share('csp_nonce', $nonce);
        $request->attributes->set('csp_nonce', $nonce);

        // Process the request
        $response = $next($request);

        // Apply enhanced security headers
        $this->applyEnhancedSecurityHeaders($response, $nonce);

        return $response;
    }

    /**
     * Apply comprehensive security headers
     */
    private function applyEnhancedSecurityHeaders(Response $response, string $nonce): void
    {
        $headers = $this->getSecurityHeaders($nonce);

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        // Set security cookies attributes
        $this->setSecureCookieAttributes($response);
    }

    /**
     * Get all security headers with nonce-based CSP
     */
    private function getSecurityHeaders(string $nonce): array
    {
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://unpkg.com https://js.pusher.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' ws: wss:",
            "frame-src 'none'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            'upgrade-insecure-requests',
        ];

        return [
            // Content Security Policy with nonce
            'Content-Security-Policy' => implode('; ', $cspDirectives),

            // Standard security headers
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options'        => 'DENY',
            'X-XSS-Protection'       => '1; mode=block',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',

            // Enhanced permissions policy
            'Permissions-Policy' => implode(', ', [
                'geolocation=()',
                'microphone=()',
                'camera=()',
                'payment=()',
                'usb=()',
                'fullscreen=()',
                'screen-wake-lock=()',
                'accelerometer=()',
                'autoplay=()',
                'gyroscope=()',
                'magnetometer=()',
            ]),

            // HTTPS enforcement
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',

            // Additional security headers
            'Expect-CT'                         => 'max-age=86400, enforce',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cross-Origin-Opener-Policy'        => 'same-origin',
            'Cross-Origin-Resource-Policy'      => 'same-origin',
            'Cross-Origin-Embedder-Policy'      => 'require-corp',
        ];
    }

    /**
     * Set secure cookie attributes
     */
    private function setSecureCookieAttributes(Response $response): void
    {
        // Get all cookies from response
        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            // Ensure secure flag is set in production
            if (app()->environment('production')) {
                $cookie->setSecureOnly(TRUE);
            }

            // Set SameSite attribute
            $cookie->setSameSite('Lax');

            // Ensure HttpOnly for non-essential cookies
            if (!in_array($cookie->getName(), ['XSRF-TOKEN', 'remember_token'], TRUE)) {
                $cookie->setHttpOnly(TRUE);
            }
        }
    }
}
