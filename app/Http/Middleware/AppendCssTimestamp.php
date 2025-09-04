<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use DOMDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function strlen;

/**
 * Appends a cache-busting timestamp query parameter to every local stylesheet
 * (<link rel="stylesheet" href="*.css">) and local script tag
 * (<script src="*.js"></script>) that does not already include a query
 * string. External (http/https/ protocol-relative) URLs are ignored.
 *
 * Centralizes asset cache-busting so Blade templates don't need inline
 * ?v={{ time() }} or filemtime() calls. Existing links/scripts that already
 * contain a query (hash, version, timestamp) are left untouched.
 */
class AppendCssTimestamp
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only process normal HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'text/html') === FALSE) {
            return $response;
        }

        $html = $response->getContent();
        if ($html === NULL || (stripos($html, '<link') === FALSE && stripos($html, '<script') === FALSE)) {
            return $response; // Fast skip
        }

        // Use DOMDocument for safer manipulation than regex
        libxml_use_internal_errors(TRUE);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        if (! $loaded) {
            return $response; // If parsing fails, leave response unmodified
        }

        $links = $dom->getElementsByTagName('link');
        $modified = FALSE;
        foreach ($links as $link) {
            $rel = strtolower($link->getAttribute('rel'));
            if ($rel !== 'stylesheet') {
                continue;
            }

            $href = $link->getAttribute('href');
            if ($href === '' || str_contains($href, '?')) {
                continue; // Already versioned or empty
            }
            if (preg_match('#^(https?:)?//#i', $href)) {
                continue; // External URL
            }
            if (! preg_match('/\.css$/i', parse_url($href, PHP_URL_PATH) ?? '')) {
                continue; // Not a CSS file
            }

            $publicPath = public_path(ltrim(parse_url($href, PHP_URL_PATH) ?? '', '/'));
            $timestamp = @filemtime($publicPath) ?: time();
            $link->setAttribute('href', $href . '?v=' . $timestamp);
            $modified = TRUE;
        }

        // Process <script src="..."> tags for local JS files
        $scripts = $dom->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if (! $script->hasAttribute('src')) {
                continue; // Inline script
            }
            $src = $script->getAttribute('src');
            if ($src === '' || str_contains($src, '?')) {
                continue; // Already versioned or empty
            }
            if (preg_match('#^(https?:)?//#i', $src)) {
                continue; // External
            }
            $path = parse_url($src, PHP_URL_PATH) ?? '';
            if (! preg_match('/\.js$/i', $path)) {
                continue; // Not a JS file
            }
            // Skip Vite dev client or hot reload endpoints (contain @vite or /@fs/ etc.)
            if (str_contains($src, '@vite') || str_contains($src, '/@fs/')) {
                continue;
            }
            $publicPath = public_path(ltrim($path, '/'));
            $timestamp = @filemtime($publicPath) ?: time();
            $script->setAttribute('src', $src . '?v=' . $timestamp);
            $modified = TRUE;
        }

        if ($modified) {
            $newHtml = $dom->saveHTML();
            if ($newHtml !== FALSE) {
                $response->setContent($newHtml);
                $response->headers->set('Content-Length', (string) strlen($newHtml));
            }
        }

        return $response;
    }
}
