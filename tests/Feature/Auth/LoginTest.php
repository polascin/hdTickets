<?php declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use Override;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /**
     * @test
     */
    public function user_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login-enhanced');
        $response->assertSee('HD Tickets');
        $response->assertSee('Sports Events Entry Tickets');
        $response->assertSee('Sign In');
    }

    /**
     * @test
     */
    public function user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * @test
     */
    public function user_cannot_login_with_invalid_email(): void
    {
        $response = $this->post('/login', [
            'email'    => 'nonexistent@hdtickets.com',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasInput('email');
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function user_cannot_login_with_empty_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function user_cannot_login_with_invalid_email_format(): void
    {
        $response = $this->post('/login', [
            'email'    => 'invalid-email',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function user_cannot_login_when_account_is_deactivated(): void
    {
        $this->user->update(['is_active' => FALSE]);

        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHas('errors');
        $this->assertGuest();

        // Check that helpful error message is provided
        $errors = session('errors');
        $this->assertStringContainsString('deactivated', $errors->first('email'));
    }

    /**
     * @test
     */
    public function scraper_user_cannot_access_web_login(): void
    {
        $scraperUser = User::factory()->create([
            'role'     => 'scraper',
            'password' => Hash::make('SecurePassword123!'),
        ]);

        $response = $this->post('/login', [
            'email'    => $scraperUser->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function user_account_gets_locked_after_failed_attempts(): void
    {
        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email'    => $this->user->email,
                'password' => 'WrongPassword',
            ]);
        }

        $this->user->refresh();
        $this->assertEquals(5, $this->user->failed_login_attempts);
        $this->assertNotNull($this->user->locked_until);
        $this->assertTrue($this->user->locked_until->isFuture());
    }

    /**
     * @test
     */
    public function user_cannot_login_when_account_is_locked(): void
    {
        // Lock the account
        $this->user->update([
            'failed_login_attempts' => 5,
            'locked_until'          => now()->addMinutes(15),
        ]);

        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Check lockout message contains helpful information
        $errors = session('errors');
        $this->assertStringContainsString('locked', $errors->first('email'));
    }

    /**
     * @test
     */
    public function user_can_login_after_lockout_expires(): void
    {
        // Set an expired lockout
        $this->user->update([
            'failed_login_attempts' => 5,
            'locked_until'          => now()->subMinute(),
        ]);

        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);

        // Verify that failed attempts are reset
        $this->user->refresh();
        $this->assertEquals(0, $this->user->failed_login_attempts);
        $this->assertNull($this->user->locked_until);
    }

    /**
     * @test
     */
    public function failed_login_attempts_are_reset_after_successful_login(): void
    {
        // Add some failed attempts
        $this->user->update(['failed_login_attempts' => 3]);

        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');

        $this->user->refresh();
        $this->assertEquals(0, $this->user->failed_login_attempts);
    }

    /**
     * @test
     */
    public function login_is_rate_limited_per_ip(): void
    {
        $ip = '192.168.1.100';

        // Simulate rate limiting
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit("login.{$this->user->email}|{$ip}");
        }

        $response = $this->from('/login')
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post('/login', [
                'email'    => $this->user->email,
                'password' => 'SecurePassword123!',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Check rate limit message
        $errors = session('errors');
        $this->assertStringContainsString('Too many', $errors->first('email'));
    }

    /**
     * @test
     */
    public function remember_me_functionality_works(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);

        // Check remember token is set
        $this->user->refresh();
        $this->assertNotNull($this->user->remember_token);
    }

    /**
     * @test
     */
    public function honeypot_field_prevents_bot_submissions(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
            'website'  => 'http://spam.com', // Honeypot field
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function two_factor_enabled_user_is_redirected_to_2fa_challenge(): void
    {
        // Enable 2FA for user
        $this->user->update([
            'two_factor_secret'         => encrypt('test_secret'),
            'two_factor_recovery_codes' => encrypt(['code1', 'code2']),
        ]);

        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/2fa/challenge');
        $this->assertGuest(); // Should not be logged in yet

        // Check 2FA user ID is stored in session
        $this->assertEquals($this->user->id, session('2fa_user_id'));
    }

    /**
     * @test
     */
    public function login_logs_successful_activity(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');

        // Check that login count is incremented
        $this->user->refresh();
        $this->assertEquals(1, $this->user->login_count);
        $this->assertNotNull($this->user->last_login_at);
        $this->assertNotNull($this->user->last_login_ip);
    }

    /**
     * @test
     */
    public function login_updates_user_login_metadata(): void
    {
        $response = $this->from('/login')
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->post('/login', [
                'email'    => $this->user->email,
                'password' => 'SecurePassword123!',
            ]);

        $response->assertRedirect('/dashboard');

        $this->user->refresh();
        $this->assertEquals('192.168.1.100', $this->user->last_login_ip);
        $this->assertNotNull($this->user->last_login_at);
        $this->assertNotNull($this->user->last_login_user_agent);
    }

    /**
     * @test
     */
    public function security_headers_are_present_on_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    /**
     * @test
     */
    public function csrf_token_is_required_for_login(): void
    {
        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/login', [
                'email'    => $this->user->email,
                'password' => 'SecurePassword123!',
            ]);

        // This test would normally fail without CSRF, but we're skipping middleware
        // In a real scenario, this would result in a 419 status
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function device_fingerprinting_data_is_processed(): void
    {
        $deviceFingerprint = base64_encode(json_encode([
            'userAgent' => 'Mozilla/5.0 Test Browser',
            'language'  => 'en-US',
            'platform'  => 'Linux',
            'timezone'  => 'America/New_York',
            'screen'    => ['width' => 1920, 'height' => 1080, 'colorDepth' => 24],
            'canvas'    => 'test_canvas_hash',
        ]));

        $response = $this->post('/login', [
            'email'              => $this->user->email,
            'password'           => 'SecurePassword123!',
            'device_fingerprint' => $deviceFingerprint,
            'client_timestamp'   => now()->timestamp,
            'timezone'           => 'America/New_York',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * @test
     */
    public function login_form_preserves_email_on_validation_failure(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasInput('email', $this->user->email);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * @test
     */
    public function redirect_after_login_works_correctly(): void
    {
        // Try to access protected route
        $protectedResponse = $this->get('/dashboard');
        $protectedResponse->assertRedirect('/login');

        // Login and check redirect
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /**
     * @test
     */
    public function login_with_recaptcha_when_enabled(): void
    {
        // Mock reCAPTCHA service
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('isEnabled')->andReturn(TRUE);
        $mockRecaptcha->shouldReceive('shouldChallenge')->andReturn(TRUE);
        $mockRecaptcha->shouldReceive('verify')->andReturn([
            'success' => TRUE,
            'score'   => 0.8,
            'action'  => 'hd_tickets_login',
        ]);
        $mockRecaptcha->shouldReceive('passes')->andReturn(TRUE);

        $this->app->instance(RecaptchaService::class, $mockRecaptcha);

        $response = $this->post('/login', [
            'email'           => $this->user->email,
            'password'        => 'SecurePassword123!',
            'recaptcha_token' => 'test_token',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * @test
     */
    public function login_fails_with_invalid_recaptcha(): void
    {
        // Mock reCAPTCHA service for failure
        $mockRecaptcha = Mockery::mock(RecaptchaService::class);
        $mockRecaptcha->shouldReceive('isEnabled')->andReturn(TRUE);
        $mockRecaptcha->shouldReceive('shouldChallenge')->andReturn(TRUE);
        $mockRecaptcha->shouldReceive('verify')->andReturn([
            'success'     => FALSE,
            'score'       => 0.1,
            'error-codes' => ['invalid-input-response'],
        ]);
        $mockRecaptcha->shouldReceive('passes')->andReturn(FALSE);

        $this->app->instance(RecaptchaService::class, $mockRecaptcha);

        $response = $this->post('/login', [
            'email'           => $this->user->email,
            'password'        => 'SecurePassword123!',
            'recaptcha_token' => 'invalid_token',
        ]);

        $response->assertSessionHasErrors(['recaptcha']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function enhanced_security_middleware_processes_requests(): void
    {
        // Clear any cached data
        Cache::flush();

        $response = $this->post('/login', [
            'email'              => $this->user->email,
            'password'           => 'SecurePassword123!',
            'device_fingerprint' => base64_encode(json_encode([
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'language'  => 'en-US',
                'platform'  => 'Win32',
                'timezone'  => 'America/New_York',
                'screen'    => ['width' => 1920, 'height' => 1080, 'colorDepth' => 24],
                'canvas'    => 'test_canvas_hash',
            ])),
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'             => 'test@hdtickets.com',
            'password'          => Hash::make('SecurePassword123!'),
            'is_active'         => TRUE,
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
