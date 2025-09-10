<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Override;
use Tests\TestCase;

/**
 * Enhanced Login System Feature Tests
 *
 * Covers:
 * - Standard login flow
 * - Enhanced login with security features
 * - Two-factor authentication
 * - Account lockout mechanisms
 * - Rate limiting
 * - Device fingerprinting
 * - Security logging
 */
class EnhancedLoginTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $testUser;

    private string $testPassword = 'SecurePassword123!';

    /**
     * @test
     */
    public function it_displays_the_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome back');
        $response->assertSee('Sign in to access your HD Tickets');
        $response->assertViewIs(Login::class);
    }

    /**
     * @test
     */
    public function it_displays_the_enhanced_login_page_when_configured(): void
    {
        config(['auth.enhanced_login' => TRUE]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Enhanced Security Login');
        $response->assertViewIs('auth.login-enhanced');
    }

    /**
     * @test
     */
    public function it_can_successfully_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email'            => $this->testUser->email,
            'password'         => $this->testPassword,
            'form_token'       => str()->random(40),
            'client_timestamp' => now()->timestamp,
            'timezone'         => 'UTC',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);

        // Check security updates
        $this->testUser->refresh();
        $this->assertEquals(0, $this->testUser->failed_login_attempts);
        $this->assertNull($this->testUser->locked_until);
        $this->assertNotNull($this->testUser->last_login_at);
        $this->assertEquals(1, $this->testUser->login_count);
    }

    /**
     * @test
     */
    public function it_rejects_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect('/login');
        $this->assertGuest();

        // Check failed attempt tracking
        $this->testUser->refresh();
        $this->assertEquals(1, $this->testUser->failed_login_attempts);
    }

    /**
     * @test
     */
    public function it_locks_account_after_multiple_failed_attempts(): void
    {
        // Make 4 failed attempts
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', [
                'email'    => $this->testUser->email,
                'password' => 'wrong-password',
            ]);
        }

        // 5th attempt should lock the account
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Check account is locked
        $this->testUser->refresh();
        $this->assertEquals(5, $this->testUser->failed_login_attempts);
        $this->assertNotNull($this->testUser->locked_until);
        $this->assertTrue($this->testUser->locked_until->isFuture());
    }

    /**
     * @test
     */
    public function it_rejects_login_for_locked_account(): void
    {
        $this->testUser->update([
            'failed_login_attempts' => 5,
            'locked_until'          => now()->addMinutes(15),
        ]);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'Your account is temporarily locked',
        ]);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_rejects_login_for_inactive_account(): void
    {
        $this->testUser->update(['is_active' => FALSE]);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'Your account has been deactivated',
        ]);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_rejects_login_for_scraper_accounts(): void
    {
        $this->testUser->update(['role' => 'scraper']);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrorsIn('default', [
            'email' => 'This account type cannot access the web interface',
        ]);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_enforces_rate_limiting(): void
    {
        $key = 'login_attempts_email:' . $this->testUser->email . '|127.0.0.1';

        // Hit the rate limit (5 attempts)
        RateLimiter::hit($key, 300);
        RateLimiter::hit($key, 300);
        RateLimiter::hit($key, 300);
        RateLimiter::hit($key, 300);
        RateLimiter::hit($key, 300);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertSessionHasErrors(['email', 'rate_limit_seconds']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_handles_two_factor_authentication_redirect(): void
    {
        // Enable 2FA for user
        $twoFactorService = app(TwoFactorAuthService::class);
        $secret = $twoFactorService->generateSecretKey();
        $this->testUser->update([
            'two_factor_secret' => encrypt($secret),
        ]);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertRedirect('/2fa/challenge');
        $this->assertGuest(); // Not logged in yet

        // Check session data for 2FA
        $this->assertEquals($this->testUser->id, Session::get('2fa_user_id'));
    }

    /**
     * @test
     */
    public function it_can_complete_two_factor_authentication(): void
    {
        // Setup 2FA
        $twoFactorService = app(TwoFactorAuthService::class);
        $secret = $twoFactorService->generateSecretKey();
        $this->testUser->update([
            'two_factor_secret' => encrypt($secret),
        ]);

        // Start login process
        $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        // Generate valid TOTP code
        $code = $twoFactorService->getCurrentCode($secret);

        // Complete 2FA
        $response = $this->post('/2fa/verify', [
            'code' => $code,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);

        // Check session cleanup
        $this->assertNull(Session::get('2fa_user_id'));
        $this->assertNull(Session::get('2fa_remember'));
    }

    /**
     * @test
     */
    public function it_handles_invalid_two_factor_codes(): void
    {
        // Setup 2FA
        $twoFactorService = app(TwoFactorAuthService::class);
        $secret = $twoFactorService->generateSecretKey();
        $this->testUser->update([
            'two_factor_secret' => encrypt($secret),
        ]);

        // Start login process
        $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        // Try invalid code
        $response = $this->post('/2fa/verify', [
            'code' => '000000',
        ]);

        $response->assertRedirect('/2fa/challenge');
        $response->assertSessionHasErrors('code');
        $this->assertGuest();

        // Check failed attempts incremented
        $this->testUser->refresh();
        $this->assertEquals(1, $this->testUser->failed_login_attempts);
    }

    /**
     * @test
     */
    public function it_can_use_recovery_codes_for_two_factor(): void
    {
        // Setup 2FA with recovery codes
        $twoFactorService = app(TwoFactorAuthService::class);
        $secret = $twoFactorService->generateSecretKey();
        $recoveryCodes = ['ABCD-1234', 'EFGH-5678'];

        $this->testUser->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        // Start login process
        $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        // Use recovery code
        $response = $this->post('/2fa/verify', [
            'code'     => 'ABCD-1234',
            'recovery' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);

        // Check recovery code is consumed
        $this->testUser->refresh();
        $remainingCodes = json_decode((string) decrypt($this->testUser->two_factor_recovery_codes), TRUE);
        $this->assertNotContains('ABCD-1234', $remainingCodes);
        $this->assertContains('EFGH-5678', $remainingCodes);
    }

    /**
     * @test
     */
    public function it_can_send_sms_backup_codes(): void
    {
        if (! config('services.twilio.sid')) {
            $this->markTestSkipped('Twilio not configured');
        }

        // Setup user with phone and 2FA
        $this->testUser->update([
            'phone'             => '+1234567890',
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        // Start login process
        $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        // Request SMS code
        $response = $this->post('/2fa/sms-code');

        $response->assertRedirect('/2fa/challenge');
        $response->assertSessionHas('success');
        $this->assertStringContainsString('SMS verification code sent', session('success'));
    }

    /**
     * @test
     */
    public function it_can_send_email_backup_codes(): void
    {
        // Setup 2FA
        $this->testUser->update([
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        // Start login process
        $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        // Request email code
        $response = $this->post('/2fa/email-code');

        $response->assertRedirect('/2fa/challenge');
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Email verification code sent', session('success'));
    }

    /**
     * @test
     */
    public function it_validates_required_login_fields(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_validates_email_format(): void
    {
        $response = $this->post('/login', [
            'email'    => 'invalid-email',
            'password' => $this->testPassword,
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_handles_honeypot_field(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => 'bot-filled-this',
        ]);

        // Should be rejected by honeypot rule
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_can_check_email_availability(): void
    {
        $response = $this->postJson('/login/check-email', [
            'email' => $this->testUser->email,
        ]);

        $response->assertOk();
        $response->assertJson([
            'exists' => TRUE,
        ]);
        $response->assertJsonStructure([
            'exists',
            'preferences' => [
                'theme',
                'language',
                'timezone',
                'two_factor_enabled',
                'last_login',
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_handles_nonexistent_email_check(): void
    {
        $response = $this->postJson('/login/check-email', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk();
        $response->assertJson([
            'exists'  => FALSE,
            'message' => 'Email not found',
        ]);
    }

    /**
     * @test
     */
    public function it_rate_limits_email_checks(): void
    {
        $email = 'test@example.com';

        // Make 10 requests to hit the limit
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/login/check-email', ['email' => $email]);
        }

        // 11th request should be rate limited
        $response = $this->postJson('/login/check-email', ['email' => $email]);

        $response->assertStatus(429);
        $response->assertJson(['error' => 'Too many requests']);
    }

    /**
     * @test
     */
    public function it_processes_device_fingerprinting(): void
    {
        $deviceFingerprint = base64_encode(json_encode([
            'userAgent' => 'Test User Agent',
            'language'  => 'en-US',
            'platform'  => 'Linux x86_64',
            'timezone'  => 'UTC',
            'screen'    => ['width' => 1920, 'height' => 1080],
        ]));

        $response = $this->post('/login', [
            'email'              => $this->testUser->email,
            'password'           => $this->testPassword,
            'device_fingerprint' => $deviceFingerprint,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);
    }

    /**
     * @test
     */
    public function it_remembers_user_when_requested(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'remember' => '1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);

        // Check remember token is set
        $this->testUser->refresh();
        $this->assertNotNull($this->testUser->remember_token);
    }

    /**
     * @test
     */
    public function it_clears_failed_attempts_on_successful_login(): void
    {
        // Set some failed attempts
        $this->testUser->update([
            'failed_login_attempts' => 3,
        ]);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertRedirect('/dashboard');

        // Check failed attempts are cleared
        $this->testUser->refresh();
        $this->assertEquals(0, $this->testUser->failed_login_attempts);
        $this->assertNull($this->testUser->locked_until);
    }

    /**
     * @test
     */
    public function it_logs_successful_login_activity(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertRedirect('/dashboard');

        // Check activity log (assuming activity logging is set up)
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id'   => $this->testUser->id,
            'description'  => 'User logged in successfully',
            'causer_type'  => User::class,
            'causer_id'    => $this->testUser->id,
        ]);
    }

    /**
     * @test
     */
    public function it_redirects_to_intended_url_after_login(): void
    {
        // Set intended URL in session
        session(['url.intended' => '/some-protected-page']);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
        ]);

        $response->assertRedirect('/some-protected-page');
    }

    /**
     * @test
     */
    public function it_handles_logout_correctly(): void
    {
        $this->actingAs($this->testUser);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_displays_two_factor_challenge_page(): void
    {
        Session::put('2fa_user_id', $this->testUser->id);

        $response = $this->get('/2fa/challenge');

        $response->assertOk();
        $response->assertSee('Two-Factor Authentication');
        $response->assertSee('Enter your authentication code');
    }

    /**
     * @test
     */
    public function it_redirects_to_login_if_no_2fa_session(): void
    {
        $response = $this->get('/2fa/challenge');

        $response->assertRedirect('/login');
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create([
            'email'                 => 'test@hdtickets.local',
            'password'              => Hash::make($this->testPassword),
            'is_active'             => TRUE,
            'role'                  => 'customer',
            'failed_login_attempts' => 0,
            'locked_until'          => NULL,
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clear rate limiters
        RateLimiter::clear('login_attempts_email:' . $this->testUser->email . '|127.0.0.1');
        RateLimiter::clear('email_check:127.0.0.1');

        // Clear cache
        Cache::flush();

        parent::tearDown();
    }
}
