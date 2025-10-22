<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scraper Blocking Middleware
 * 
 * Blocks known scraping user agents and headless browser signals
 * for sports event ticket monitoring system protection
 */
class ScraperBlockingMiddleware
{
    /**
     * Known bot/scraper user agent patterns
     */
    private array $botPatterns = [
        'bot', 'crawler', 'spider', 'scraper', 'parser', 'extractor',
        'curl', 'wget', 'python', 'java', 'go-http', 'node-fetch',
        'headlesschrome', 'phantomjs', 'selenium', 'playwright',
        'puppeteer', 'chromedriver', 'webdriver', 'automation'
    ];

    /**
     * Headless browser detection signals
     */
    private array $headlessSignals = [
        'headless', 'phantom', 'electron', 'nightmare', 'splash'
    ];

    /**
     * Handle incoming request and block suspicious agents
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        
        // Check for empty user agent (common scraper pattern)
        if (empty($userAgent)) {
            $this->logBlockedRequest($request, 'Empty user agent');
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Invalid request headers'
            ], 403);
        }

        // Check for bot patterns in user agent
        foreach ($this->botPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                $this->logBlockedRequest($request, "Bot pattern detected: {$pattern}");
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Automated access not permitted'
                ], 403);
            }
        }

        // Check for headless browser signals
        foreach ($this->headlessSignals as $signal) {
            if (str_contains($userAgent, $signal)) {
                $this->logBlockedRequest($request, "Headless signal detected: {$signal}");
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Automated browser access not permitted'
                ], 403);
            }
        }

        // Check for common automation headers
        $automationHeaders = [
            'HTTP_X_REQUESTED_WITH' => 'automation',
            'HTTP_X_AUTOMATION' => true,
            'HTTP_X_SCRAPER' => true,
        ];

        foreach ($automationHeaders as $header => $value) {
            if ($request->header($header) === $value) {
                $this->logBlockedRequest($request, "Automation header detected: {$header}");
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Automated access headers not permitted'
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Log blocked request for monitoring
     */
    private function logBlockedRequest(Request $request, string $reason): void
    {
        Log::warning('Scraper access blocked', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'reason' => $reason,
            'headers' => $request->headers->all()
        ]);
    }
}