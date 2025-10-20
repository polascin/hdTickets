<?php declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * iOS Compatibility Test Suite
 *
 * Comprehensive tests to ensure iOS devices (iPhone, iPad)
 * can access the application without encountering errors.
 */
class IosCompatibilityTest extends TestCase
{
    /**
     * Common iOS user agent strings for testing
     */
    protected array $iosUserAgents = [
        // iPhone with iOS 15
        'iphone_ios15' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        
        // iPhone with iOS 16
        'iphone_ios16' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        
        // iPhone with iOS 17
        'iphone_ios17' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        
        // iPhone with iOS 18
        'iphone_ios18' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1',
        
        // iPad with iOS 15
        'ipad_ios15' => 'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        
        // iPad with iOS 17
        'ipad_ios17' => 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        
        // iPod Touch
        'ipod' => 'Mozilla/5.0 (iPod touch; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        
        // Malformed but valid iOS user agent
        'malformed' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0_1 like Mac OS X) AppleWebKit/605.1.15',
    ];

    /** @test */
    public function ios_devices_can_access_welcome_page(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/');

            $response->assertSuccessful();
            $this->assertNotEquals(500, $response->status(), "iOS device ({$name}) got 500 error on welcome page");
        }
    }

    /** @test */
    public function ios_devices_can_access_login_page(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/login');

            $response->assertSuccessful();
            $this->assertNotEquals(500, $response->status(), "iOS device ({$name}) got 500 error on login page");
        }
    }

    /** @test */
    public function ios_devices_can_access_register_page(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/register');

            $response->assertSuccessful();
            $this->assertNotEquals(500, $response->status(), "iOS device ({$name}) got 500 error on register page");
        }
    }

    /** @test */
    public function ios_devices_are_not_flagged_as_bots(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/');

            // Should not get blocked (403) or rate limited (429)
            $this->assertNotEquals(403, $response->status(), "iOS device ({$name}) was blocked as bot");
            $this->assertNotEquals(429, $response->status(), "iOS device ({$name}) was rate limited");
        }
    }

    /** @test */
    public function middleware_handles_ios_user_agents_without_errors(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/');

            // Should never return 500
            $this->assertNotEquals(500, $response->status(), "Middleware failed for iOS device ({$name})");
        }
    }

    /** @test */
    public function null_user_agent_does_not_cause_500_error(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => '',
        ])->get('/');

        $this->assertNotEquals(500, $response->status(), 'Null user agent caused 500 error');
    }

    /** @test */
    public function malicious_user_agent_does_not_cause_500_error(): void
    {
        $maliciousUserAgents = [
            '<script>alert("xss")</script>',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 99_99_99 like Mac OS X)',
            str_repeat('A', 2000), // Very long user agent
            '<?php system("ls"); ?>',
        ];

        foreach ($maliciousUserAgents as $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/');

            $this->assertNotEquals(500, $response->status(), 'Malicious user agent caused 500 error');
        }
    }

    /** @test */
    public function csp_headers_are_compatible_with_ios(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->iosUserAgents['iphone_ios17'],
        ])->get('/');

        $response->assertSuccessful();
        
        // Check that CSP header exists
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
        
        // Verify iOS-compatible directives are present
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('img-src', $csp);
    }

    /** @test */
    public function ios_devices_can_make_post_requests(): void
    {
        foreach (['iphone_ios17', 'ipad_ios17'] as $deviceKey) {
            $userAgent = $this->iosUserAgents[$deviceKey];
            
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
            ])->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            // Should not return 500 (422 validation error is expected)
            $this->assertNotEquals(500, $response->status(), "iOS device ({$deviceKey}) got 500 error on POST");
        }
    }

    /** @test */
    public function ios_safari_can_load_assets(): void
    {
        // Test that critical assets load without errors
        $assetPaths = [
            '/css/app.css',
            '/js/app.js',
        ];

        foreach ($assetPaths as $assetPath) {
            if (file_exists(public_path($assetPath))) {
                $response = $this->withHeaders([
                    'User-Agent' => $this->iosUserAgents['iphone_ios17'],
                ])->get($assetPath);

                $this->assertNotEquals(500, $response->status(), "Asset {$assetPath} returned 500 for iOS");
            }
        }
    }

    /** @test */
    public function security_middleware_does_not_block_ios(): void
    {
        foreach ($this->iosUserAgents as $name => $userAgent) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
            ])->get('/');

            // Security middleware should not block or cause errors
            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirection(),
                "Security middleware blocked iOS device ({$name})"
            );
        }
    }

    /** @test */
    public function ios_error_tracking_middleware_is_registered(): void
    {
        $middlewareClasses = app('router')->getMiddleware();
        
        $this->assertArrayHasKey('ios.error.tracker', $middlewareClasses, 
            'IosErrorTracker middleware is not registered');
    }

    /** @test */
    public function user_agent_helper_correctly_detects_ios_devices(): void
    {
        $request = $this->createRequest('/', 'GET', [
            'User-Agent' => $this->iosUserAgents['iphone_ios17'],
        ]);

        $isIOS = \App\Support\UserAgentHelper::isIOS($request);
        $this->assertTrue($isIOS, 'UserAgentHelper failed to detect iOS device');

        $deviceInfo = \App\Support\UserAgentHelper::getDeviceInfo($request);
        $this->assertEquals('iphone', $deviceInfo['device_type']);
        $this->assertTrue($deviceInfo['is_ios']);
        $this->assertNotNull($deviceInfo['ios_version']);
    }

    /** @test */
    public function user_agent_helper_handles_null_gracefully(): void
    {
        $request = $this->createRequest('/', 'GET', [
            'User-Agent' => '',
        ]);

        // Should not throw exception
        $isIOS = \App\Support\UserAgentHelper::isIOS($request);
        $this->assertFalse($isIOS);

        $deviceInfo = \App\Support\UserAgentHelper::getDeviceInfo($request);
        $this->assertIsArray($deviceInfo);
    }

    /** @test */
    public function geoip_api_failures_do_not_cause_500_errors(): void
    {
        // Simulate GeoIP API being unavailable by using local IP
        $response = $this->withHeaders([
            'User-Agent' => $this->iosUserAgents['iphone_ios17'],
            'X-Forwarded-For' => '127.0.0.1',
        ])->get('/');

        $this->assertNotEquals(500, $response->status(), 'GeoIP failure caused 500 error');
    }

    /**
     * Helper to create a request with custom headers
     */
    protected function createRequest(string $uri, string $method = 'GET', array $headers = []): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create($uri, $method);
        
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return $request;
    }
}
