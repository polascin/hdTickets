<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

use function array_slice;
use function count;
use function in_array;

class EnhancedLoginSecurity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for non-login requests
        if (! $this->isLoginRequest($request)) {
            return $next($request);
        }

        // Device fingerprinting validation
        $this->validateDeviceFingerprint($request);

        // Advanced rate limiting
        $this->applyAdvancedRateLimit($request);

        // Geolocation-based security
        $this->checkGeolocation($request);

        // Detect automated tools
        $this->detectAutomation($request);

        // Monitor for suspicious patterns
        $this->monitorSuspiciousActivity($request);

        return $next($request);
    }

    private function isLoginRequest(Request $request): bool
    {
        return $request->is('login') && $request->isMethod('POST');
    }

    private function validateDeviceFingerprint(Request $request): void
    {
        $fingerprint = $request->input('device_fingerprint');

        if (! $fingerprint) {
            Log::warning('Login attempt without device fingerprint', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return;
        }

        // Decode and validate fingerprint
        try {
            $decoded = json_decode(base64_decode((string) $fingerprint, TRUE), TRUE);

            // Validate fingerprint structure
            $requiredFields = ['userAgent', 'language', 'platform', 'timezone', 'screen', 'canvas'];
            foreach ($requiredFields as $field) {
                if (! isset($decoded[$field])) {
                    throw new InvalidArgumentException("Missing fingerprint field: {$field}");
                }
            }

            // Store fingerprint for future reference
            $email = $request->input('email');
            if ($email) {
                $cacheKey = "user_fingerprint:{$email}";
                $storedFingerprints = Cache::get($cacheKey, []);

                if (! in_array($fingerprint, $storedFingerprints, TRUE)) {
                    $storedFingerprints[] = $fingerprint;
                    Cache::put($cacheKey, array_slice($storedFingerprints, -5), now()->addMonths(3));

                    Log::info('New device fingerprint registered', [
                        'email' => $email,
                        'ip'    => $request->ip(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Invalid device fingerprint', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);
        }
    }

    private function applyAdvancedRateLimit(Request $request): void
    {
        $ip = $request->ip();
        $email = $request->input('email');

        // IP-based rate limiting
        $ipKey = "login_attempts_ip:{$ip}";
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            abort(429, 'Too many login attempts from this IP address.');
        }

        // Email-based rate limiting
        if ($email) {
            $emailKey = "login_attempts_email:{$email}";
            if (RateLimiter::tooManyAttempts($emailKey, 5)) {
                abort(429, 'Too many login attempts for this email address.');
            }
        }

        // Country-based rate limiting for high-risk locations
        $country = $this->getCountryFromIP($ip);
        if (in_array($country, config('security.high_risk_countries', []), TRUE)) {
            $countryKey = "login_attempts_country:{$country}";
            if (RateLimiter::tooManyAttempts($countryKey, 100)) {
                Log::warning('High login attempt rate from high-risk country', [
                    'country' => $country,
                    'ip'      => $ip,
                ]);
            }
        }
    }

    private function checkGeolocation(Request $request): void
    {
        $ip = $request->ip();
        $email = $request->input('email');

        if (! $email) {
            return;
        }

        // Get user's typical login locations
        $userLocationsKey = "user_locations:{$email}";
        $knownLocations = Cache::get($userLocationsKey, []);

        $currentLocation = $this->getLocationFromIP($ip);

        if (! empty($knownLocations) && $currentLocation) {
            $isKnownLocation = FALSE;

            foreach ($knownLocations as $location) {
                if ($this->calculateDistance($currentLocation, $location) < 100) { // 100km radius
                    $isKnownLocation = TRUE;

                    break;
                }
            }

            if (! $isKnownLocation) {
                Log::warning('Login from unusual location', [
                    'email'           => $email,
                    'ip'              => $ip,
                    'location'        => $currentLocation,
                    'known_locations' => $knownLocations,
                ]);

                // Flag for additional verification
                session(['require_additional_verification' => TRUE]);
            }
        }

        // Update known locations
        if ($currentLocation) {
            $knownLocations[] = $currentLocation;
            $knownLocations = array_slice(array_unique($knownLocations, SORT_REGULAR), -10);
            Cache::put($userLocationsKey, $knownLocations, now()->addMonths(6));
        }
    }

    private function detectAutomation(Request $request): void
    {
        $userAgent = $request->userAgent();
        $suspiciousUA = [
            'selenium', 'webdriver', 'phantom', 'headless', 'chrome-lighthouse',
            'crawler', 'bot', 'spider', 'scraper',
        ];

        foreach ($suspiciousUA as $pattern) {
            if (stripos((string) $userAgent, $pattern) !== FALSE) {
                Log::warning('Potential automated login attempt detected', [
                    'user_agent' => $userAgent,
                    'ip'         => $request->ip(),
                    'pattern'    => $pattern,
                ]);

                // Increase rate limiting for automated requests
                $botKey = "bot_attempts:{$request->ip()}";
                RateLimiter::hit($botKey, 3600); // 1 hour decay

                if (RateLimiter::attempts($botKey) > 5) {
                    abort(429, 'Automated requests not allowed');
                }

                break;
            }
        }
    }

    private function monitorSuspiciousActivity(Request $request): void
    {
        $ip = $request->ip();
        $request->input('email');

        // Monitor rapid-fire attempts
        $rapidKey = "rapid_attempts:{$ip}";
        $attempts = Cache::get($rapidKey, []);
        $attempts[] = time();

        // Keep only attempts from last 60 seconds
        $attempts = array_filter($attempts, fn ($timestamp): bool => $timestamp > time() - 60);

        if (count($attempts) > 5) {
            Log::warning('Rapid-fire login attempts detected', [
                'ip'                 => $ip,
                'attempts_in_minute' => count($attempts),
            ]);

            // Temporarily block IP
            Cache::put("blocked_ip:{$ip}", TRUE, now()->addMinutes(15));
        }

        Cache::put($rapidKey, $attempts, now()->addMinutes(2));

        // Check if IP is blocked
        if (Cache::has("blocked_ip:{$ip}")) {
            abort(429, 'IP temporarily blocked due to suspicious activity');
        }
    }

    private function getCountryFromIP(string $ip): string
    {
        // Skip for local/private IPs
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'Local';
        }

        // Check cache first
        $cacheKey = "ip_country:{$ip}";
        $country = Cache::get($cacheKey);

        if ($country !== NULL) {
            return $country;
        }

        // Skip API call in testing/development environments
        if (app()->environment(['testing', 'local'])) {
            return 'Unknown';
        }

        // Async API call with timeout and fallback
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'       => 2, // 2 second timeout
                    'ignore_errors' => TRUE,
                ],
            ]);

            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country", FALSE, $context);

            if ($response === FALSE) {
                Log::debug('Geolocation API unavailable for IP: ' . $ip);
                Cache::put($cacheKey, 'Unknown', now()->addHours(1));

                return 'Unknown';
            }

            $data = json_decode($response, TRUE);
            $country = $data['country'] ?? 'Unknown';

            // Cache for 24 hours
            Cache::put($cacheKey, $country, now()->addDay());

            return $country;
        } catch (Exception $e) {
            Log::debug('Geolocation lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            Cache::put($cacheKey, 'Unknown', now()->addHour());

            return 'Unknown';
        }
    }

    private function getLocationFromIP(string $ip): ?array
    {
        // Skip for local/private IPs
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return NULL;
        }

        // Check cache first
        $cacheKey = "ip_location:{$ip}";
        $location = Cache::get($cacheKey);

        if ($location !== NULL) {
            return $location;
        }

        // Skip API call in testing/development environments
        if (app()->environment(['testing', 'local'])) {
            return NULL;
        }

        // Async API call with timeout and fallback
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'       => 2, // 2 second timeout
                    'ignore_errors' => TRUE,
                ],
            ]);

            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=lat,lon,city,country", FALSE, $context);

            if ($response === FALSE) {
                Log::debug('Geolocation API unavailable for IP: ' . $ip);
                Cache::put($cacheKey, NULL, now()->addHours(1));

                return NULL;
            }

            $data = json_decode($response, TRUE);

            if (isset($data['lat'], $data['lon'])) {
                $location = [
                    'lat'     => (float) $data['lat'],
                    'lon'     => (float) $data['lon'],
                    'city'    => $data['city'] ?? '',
                    'country' => $data['country'] ?? '',
                ];

                // Cache for 24 hours
                Cache::put($cacheKey, $location, now()->addDay());

                return $location;
            }
        } catch (Exception $e) {
            Log::debug('Geolocation lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
        }

        // Cache null result for 1 hour to prevent repeated API calls
        Cache::put($cacheKey, NULL, now()->addHour());

        return NULL;
    }

    private function calculateDistance(array $point1, array $point2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($point1['lat']);
        $lonFrom = deg2rad($point1['lon']);
        $latTo = deg2rad($point2['lat']);
        $lonTo = deg2rad($point2['lon']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(sin($latDelta / 2) ** 2 +
            cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2));

        return $angle * $earthRadius;
    }
}
