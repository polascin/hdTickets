<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $validUser;

    private User $inactiveUser;

    private User $lockedUser;

    private string $validPassword = 'ValidP@ssw0rd123!';

    public function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->validUser = User::factory()->create([
            'email'                 => 'valid@test.com',
            'password'              => Hash::make($this->validPassword),
            'is_active'             => TRUE,
            'failed_login_attempts' => 0,
            'locked_until'          => NULL,
            'role'                  => 'customer',
        ]);

        $this->inactiveUser = User::factory()->create([
            'email'     => 'inactive@test.com',
            'password'  => Hash::make($this->validPassword),
            'is_active' => FALSE,
            'role'      => 'customer',
        ]);

        $this->lockedUser = User::factory()->create([
            'email'                 => 'locked@test.com',
            'password'              => Hash::make($this->validPassword),
            'is_active'             => TRUE,
            'locked_until'          => now()->addMinutes(15),
            'failed_login_attempts' => 5,
            'role'                  => 'customer',
        ]);
    }

    /**
     * @test
     */
    public function test_login_displays_form(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('auth.login');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertSee('Remember me');
        $response->assertSee('Sign In');

        // Check for accessibility features
        $response->assertSee('id="login-form"');
        $response->assertSee('role="form"');
        $response->assertSee('aria-labelledby="login-form-title"');
    }

    /**
     * @test
     */
    public function test_login_with_valid_credentials_succeeds(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => '', // Honeypot field
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->validUser);

        // Verify user login tracking
        $this->validUser->refresh();
        $this->assertEquals(0, $this->validUser->failed_login_attempts);
        $this->assertNull($this->validUser->locked_until);
        $this->assertNotNull($this->validUser->last_login_at);
    }

    /**
     * @test
     */
    public function test_login_with_remember_me_sets_cookie(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'remember' => TRUE,
            'website'  => '',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->validUser);

        // Check for remember me cookie
        $response->assertCookie(Auth::getRecallerName());
    }

    /**
     * @test
     */
    public function test_login_with_invalid_email_fails(): void
    {
        $response = $this->post('/login', [
            'email'    => 'nonexistent@test.com',
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertSessionHasErrors(['email' => 'Invalid login credentials. Please check your email and password.']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function test_login_with_invalid_password_fails(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => 'WrongPassword123!',
            'website'  => '',
        ]);

        $response->assertSessionHasErrors(['email' => 'Invalid login credentials. Please check your email and password.']);
        $this->assertGuest();

        // Verify failed attempt tracking
        $this->validUser->refresh();
        $this->assertEquals(1, $this->validUser->failed_login_attempts);
    }

    /**
     * @test
     */
    public function test_login_with_inactive_account_fails(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->inactiveUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertSessionHasErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function test_login_with_locked_account_fails(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->lockedUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertSessionHasErrors(['email' => 'Your account is temporarily locked. Please try again later.']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email'                 => 'locktest@test.com',
            'password'              => Hash::make($this->validPassword),
            'is_active'             => TRUE,
            'failed_login_attempts' => 4,
            'role'                  => 'customer',
        ]);

        // This should be the 5th failed attempt
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'WrongPassword123!',
            'website'  => '',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());
    }

    /**
     * @test
     */
    public function test_failed_attempts_reset_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email'                 => 'resettest@test.com',
            'password'              => Hash::make($this->validPassword),
            'is_active'             => TRUE,
            'failed_login_attempts' => 3,
            'role'                  => 'customer',
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    /**
     * @test
     */
    public function test_honeypot_protection_blocks_bots(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => 'bot-filled-value', // Bot detection
        ]);

        $response->assertSessionHasErrors(['website']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function test_csrf_protection_is_enforced(): void
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'email'    => $this->validUser->email,
                'password' => $this->validPassword,
                'website'  => '',
            ]);

        // Without CSRF token, request should fail
        $this->withMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /**
     * @test
     */
    public function test_rate_limiting_prevents_brute_force(): void
    {
        $email = 'ratelimit@test.com';

        // Make 5 failed attempts quickly
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email'    => $email,
                'password' => 'wrong-password',
                'website'  => '',
            ]);
        }

        // The 6th attempt should be rate limited
        $response->assertSessionHasErrors();
        $errors = $response->getSession()->get('errors')->getBag('default');
        $this->assertStringContains('Too many login attempts', $errors->first('email'));
    }

    /**
     * @test
     */
    public function test_email_validation_rules(): void
    {
        // Test empty email
        $response = $this->post('/login', [
            'email'    => '',
            'password' => $this->validPassword,
            'website'  => '',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test invalid email format
        $response = $this->post('/login', [
            'email'    => 'invalid-email-format',
            'password' => $this->validPassword,
            'website'  => '',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test valid email format but non-existent user
        $response = $this->post('/login', [
            'email'    => 'valid@format.com',
            'password' => $this->validPassword,
            'website'  => '',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * @test
     */
    public function test_password_validation_rules(): void
    {
        // Test empty password
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => '',
            'website'  => '',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * @test
     */
    public function test_user_login_activity_logging(): void
    {
        // Clear any existing activity logs
        activity()->disableLogging();
        activity()->enableLogging();

        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertRedirect('/dashboard');

        // Check that login activity was logged
        $this->assertDatabaseHas('activity_log', [
            'subject_id'   => $this->validUser->id,
            'subject_type' => User::class,
            'description'  => 'User logged in successfully',
        ]);
    }

    /**
     * @test
     */
    public function test_login_with_two_factor_authentication_enabled(): void
    {
        // Enable 2FA for user
        $this->validUser->update([
            'two_factor_secret'  => 'test-secret',
            'two_factor_enabled' => TRUE,
        ]);

        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        // Should redirect to 2FA challenge instead of dashboard
        $response->assertRedirect('/two-factor-challenge');
        $this->assertTrue(Session::has('2fa_user_id'));
        $this->assertEquals($this->validUser->id, Session::get('2fa_user_id'));
    }

    /**
     * @test
     */
    public function test_scraper_users_cannot_login(): void
    {
        $scraperUser = User::factory()->create([
            'email'     => 'scraper@test.com',
            'password'  => Hash::make($this->validPassword),
            'is_active' => TRUE,
            'role'      => 'scraper',
        ]);

        $response = $this->post('/login', [
            'email'    => $scraperUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertSessionHasErrors(['email' => 'This account type cannot access the web interface.']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function test_login_form_accessibility_attributes(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(Response::HTTP_OK);

        // Check for accessibility attributes
        $content = $response->getContent();
        $this->assertStringContains('aria-labelledby="login-form-title"', $content);
        $this->assertStringContains('aria-describedby="login-form-description"', $content);
        $this->assertStringContains('aria-required="true"', $content);
        $this->assertStringContains('role="form"', $content);
        $this->assertStringContains('class="hd-sr-only"', $content);
        $this->assertStringContains('aria-live="polite"', $content);
        $this->assertStringContains('Skip to main content', $content);
    }

    /**
     * @test
     */
    public function test_login_session_regeneration(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->validUser->email,
            'password' => $this->validPassword,
            'website'  => '',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->validUser);

        // Session should be regenerated for security
        $this->assertNotEquals(
            $response->getSession()->getId(),
            $this->app['session']->getId(),
        );
    }

    protected function tearDown(): void
    {
        // Clear rate limiter
        RateLimiter::clear('login.attempts:' . request()->ip());

        parent::tearDown();
    }
}
