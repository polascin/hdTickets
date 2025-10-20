<?php declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

use function strlen;

/**
 * UserAgentHelper - Safe user agent parsing and device detection
 *
 * Provides defensive parsing of user agent strings with specific
 * iOS and Safari detection capabilities to prevent middleware failures.
 */
class UserAgentHelper
{
    /**
     * Safely extract the user agent string from a request
     */
    public static function get(Request $request): ?string
    {
        try {
            $userAgent = $request->userAgent();

            return $userAgent !== NULL && $userAgent !== '' ? $userAgent : NULL;
        } catch (Throwable $e) {
            Log::warning('Failed to extract user agent from request', [
                'error'      => $e->getMessage(),
                'ip'         => $request->ip(),
                'request_id' => $request->header('X-Request-ID'),
            ]);

            return NULL;
        }
    }

    /**
     * Check if the request is from an iOS device
     */
    public static function isIOS(Request $request): bool
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL) {
            return FALSE;
        }

        $userAgent = strtolower($userAgent);

        return str_contains($userAgent, 'iphone')
               || str_contains($userAgent, 'ipad')
               || str_contains($userAgent, 'ipod');
    }

    /**
     * Check if the request is from Safari browser
     */
    public static function isSafari(Request $request): bool
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL) {
            return FALSE;
        }

        $userAgent = strtolower($userAgent);

        // Safari contains "Safari" but not "Chrome" or "Chromium"
        return str_contains($userAgent, 'safari')
               && ! str_contains($userAgent, 'chrome')
               && ! str_contains($userAgent, 'chromium');
    }

    /**
     * Extract iOS version from user agent
     *
     * @return string|null iOS version (e.g., "16.0", "17.1") or null if not iOS or version not found
     */
    public static function getIOSVersion(Request $request): ?string
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL || ! self::isIOS($request)) {
            return NULL;
        }

        try {
            // Match patterns like "OS 16_0", "OS 17_1", "OS 18_0_1"
            if (preg_match('/OS (\d+)_(\d+)(?:_(\d+))?/i', $userAgent, $matches)) {
                $major = $matches[1];
                $minor = $matches[2] ?? '0';
                $patch = $matches[3] ?? '';

                return $patch !== '' ? "{$major}.{$minor}.{$patch}" : "{$major}.{$minor}";
            }
        } catch (Throwable $e) {
            Log::debug('Failed to parse iOS version from user agent', [
                'error'      => $e->getMessage(),
                'user_agent' => $userAgent,
            ]);
        }

        return NULL;
    }

    /**
     * Extract Safari version from user agent
     *
     * @return string|null Safari version (e.g., "16.0", "17.0") or null if not Safari or version not found
     */
    public static function getSafariVersion(Request $request): ?string
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL || ! self::isSafari($request)) {
            return NULL;
        }

        try {
            // Match patterns like "Version/16.0", "Version/17.1"
            if (preg_match('/Version\/(\d+)\.(\d+)/i', $userAgent, $matches)) {
                return "{$matches[1]}.{$matches[2]}";
            }
        } catch (Throwable $e) {
            Log::debug('Failed to parse Safari version from user agent', [
                'error'      => $e->getMessage(),
                'user_agent' => $userAgent,
            ]);
        }

        return NULL;
    }

    /**
     * Get device type from user agent
     *
     * @return string Device type: 'iphone', 'ipad', 'ipod', 'android', 'desktop', 'unknown'
     */
    public static function getDeviceType(Request $request): string
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'iphone')) {
            return 'iphone';
        }

        if (str_contains($userAgent, 'ipad')) {
            return 'ipad';
        }

        if (str_contains($userAgent, 'ipod')) {
            return 'ipod';
        }

        if (str_contains($userAgent, 'android')) {
            return 'android';
        }

        if (str_contains($userAgent, 'mobile')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet')) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Check if user agent appears to be from an automated tool/bot
     */
    public static function isAutomatedTool(Request $request): bool
    {
        $userAgent = self::get($request);

        if ($userAgent === NULL) {
            return FALSE;
        }

        $userAgent = strtolower($userAgent);

        $botIndicators = [
            'bot', 'spider', 'crawler', 'scraper',
            'selenium', 'webdriver', 'phantom', 'headless',
            'curl', 'wget', 'python-requests', 'java/',
            'go-http-client', 'okhttp', 'apache-httpclient',
        ];

        foreach ($botIndicators as $indicator) {
            if (str_contains($userAgent, $indicator)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get comprehensive device information
     *
     * @return array{
     *     user_agent: string|null,
     *     is_ios: bool,
     *     is_safari: bool,
     *     ios_version: string|null,
     *     safari_version: string|null,
     *     device_type: string,
     *     is_automated: bool
     * }
     */
    public static function getDeviceInfo(Request $request): array
    {
        return [
            'user_agent'     => self::get($request),
            'is_ios'         => self::isIOS($request),
            'is_safari'      => self::isSafari($request),
            'ios_version'    => self::getIOSVersion($request),
            'safari_version' => self::getSafariVersion($request),
            'device_type'    => self::getDeviceType($request),
            'is_automated'   => self::isAutomatedTool($request),
        ];
    }

    /**
     * Log iOS-specific request information
     */
    public static function logIOSRequest(Request $request, string $context = 'general'): void
    {
        if (! self::isIOS($request)) {
            return;
        }

        $deviceInfo = self::getDeviceInfo($request);

        Log::info("iOS request detected - {$context}", [
            'context'     => $context,
            'device_info' => $deviceInfo,
            'ip'          => $request->ip(),
            'path'        => $request->path(),
            'method'      => $request->method(),
            'request_id'  => $request->header('X-Request-ID'),
        ]);
    }

    /**
     * Check if user agent is valid and parseable
     */
    public static function isValidUserAgent(?string $userAgent): bool
    {
        if ($userAgent === NULL || $userAgent === '') {
            return FALSE;
        }

        // Basic validation: should contain at least one alphabetic character
        // and not be excessively long (> 1000 chars is suspicious)
        return strlen($userAgent) > 0
               && strlen($userAgent) < 1000
               && preg_match('/[a-zA-Z]/', $userAgent) === 1;
    }

    /**
     * Sanitise user agent string for logging
     */
    public static function sanitise(?string $userAgent, int $maxLength = 255): string
    {
        if ($userAgent === NULL || $userAgent === '') {
            return 'unknown';
        }

        // Remove potential injection attempts
        $sanitised = preg_replace('/[<>\'"]/', '', $userAgent);

        // Truncate if too long
        if (strlen((string) $sanitised) > $maxLength) {
            $sanitised = substr((string) $sanitised, 0, $maxLength) . '...';
        }

        return $sanitised ?? 'unknown';
    }
}
