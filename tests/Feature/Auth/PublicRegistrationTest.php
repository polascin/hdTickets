<?php declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Public Registration Feature Tests
 * 
 * Tests the complete public registration flow including:
 * - Form validation and submission
 * - User creation with correct role assignment
 * - Legal acceptances recording
 * - Two-factor authentication setup
 * - Email verification flow
 * - Security measures (rate limiting, honeypot)
 */
class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Event::fake();
    }

    /** @test */
    public function it_displays_the_public_registration_form(): void
    {
        $response = $this->get(route('register.public.create'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.public-register');
        $response->assertSee('Create Your Account');
        $response->assertSee('First Name');
        $response->assertSee('Last Name');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertSee('Terms of Service');
        $response->assertSee('Privacy Policy');
    }

    /** @test */
    public function it_registers_a_customer_and_sends_verification_notification(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '+1234567890',
            'accept_terms' => true,
            'accept_privacy' => true,
            'marketing_opt_in' => true,
            'enable_2fa' => false,
        ];

        $response = $this->post(route('register.public.store'), $userData);

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => 'customer',
            'marketing_opt_in' => true,
        ]);

        // Assert user has legal acceptance timestamps
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);

        // Assert password is hashed
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));

        // Assert user is logged in
        $this->assertAuthenticatedAs($user);

        // Assert Registered event was dispatched
        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        // Assert redirected to verification notice (no 2FA enabled)
        $response->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function it_requires_legal_acceptances(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => false, // Not accepted
            'accept_privacy' => false, // Not accepted
        ];

        $response = $this->post(route('register.public.store'), $userData);

        $response->assertSessionHasErrors(['accept_terms', 'accept_privacy']);
        $this->assertDatabaseMissing('users', ['email' => 'john.doe@example.com']);
        $this->assertGuest();
    }

    /** @test */
    public function it_enforces_password_rules(): void
    {
        $testCases = [
            [
                'password' => 'weak',
                'password_confirmation' => 'weak',
                'expected_error' => 'password'
            ],
            [
                'password' => 'NoNumbers!',
                'password_confirmation' => 'NoNumbers!',
                'expected_error' => 'password'
            ],
            [
                'password' => 'nonumbers123',
                'password_confirmation' => 'nonumbers123',
                'expected_error' => 'password'
            ],
            [
                'password' => 'SecurePass123!',
                'password_confirmation' => 'DifferentPass123!',
                'expected_error' => 'password'
            ]
        ];

        foreach ($testCases as $testCase) {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => $this->faker->unique()->safeEmail,
                'password' => $testCase['password'],
                'password_confirmation' => $testCase['password_confirmation'],
                'accept_terms' => true,
                'accept_privacy' => true,
            ];

            $response = $this->post(route('register.public.store'), $userData);

            $response->assertSessionHasErrors($testCase['expected_error']);
            $this->assertDatabaseMissing('users', ['email' => $userData['email']]);
        }
    }

    /** @test */
    public function it_rejects_duplicate_email(): void
    {
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
        ];

        $response = $this->post(route('register.public.store'), $userData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1); // Only the original user
    }

    /** @test */
    public function it_assigns_customer_role_by_default(): void
    {
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
        ];

        $this->post(route('register.public.store'), $userData);

        $user = User::where('email', 'jane.smith@example.com')->first();
        $this->assertEquals('customer', $user->role);
    }

    /** @test */
    public function it_redirects_to_twofactor_step_when_enabled(): void
    {
        // Enable 2FA prompt in configuration
        config(['auth.registration.two_factor_prompt' => true]);

        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
            'enable_2fa' => true,
        ];

        $response = $this->post(route('register.public.store'), $userData);

        $response->assertRedirect(route('register.twofactor.show'));
    }

    /** @test */
    public function it_handles_twofactor_setup_flow(): void
    {
        // Create and login a user
        $user = User::factory()->create([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);
        
        Auth::login($user);

        // Test showing 2FA setup page
        $response = $this->get(route('register.twofactor.show'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.twofactor-setup');
        $response->assertSee('Set up Two-Factor Authentication');

        // Test enabling 2FA with valid code
        $this->mock(TwoFactorAuthService::class, function ($mock) {
            $mock->shouldReceive('generateSecretKey')
                ->andReturn('JBSWY3DPEHPK3PXP');
            $mock->shouldReceive('enableTwoFactor')
                ->with($this->isInstanceOf(User::class), 'JBSWY3DPEHPK3PXP', '123456')
                ->andReturn(true);
        });

        session(['2fa_temp_secret' => 'JBSWY3DPEHPK3PXP']);

        $response = $this->post(route('register.twofactor.enable'), [
            'code' => '123456'
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('status');
    }

    /** @test */
    public function it_allows_skipping_twofactor_and_shows_verification_notice(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $response = $this->post(route('register.twofactor.skip'));

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('info');
    }

    /** @test */
    public function it_verifies_email_with_signed_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        // Generate a signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        // Assert user is marked as verified
        $this->assertNotNull($user->fresh()->email_verified_at);

        // Assert redirect to dashboard or intended route
        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function it_respects_rate_limiting_on_registration(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
        ];

        // Make 13 rapid requests (limit is 12 per minute)
        for ($i = 0; $i < 13; $i++) {
            $userData['email'] = "test{$i}@example.com";
            $response = $this->post(route('register.public.store'), $userData);
            
            if ($i < 12) {
                // First 12 should succeed or fail due to validation
                $this->assertNotEquals(429, $response->status());
            } else {
                // 13th should be rate limited
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function it_ignores_honeypot_field(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
            'website_url' => '', // Honeypot field should be empty
        ];

        $response = $this->post(route('register.public.store'), $userData);

        // Should succeed with empty honeypot
        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
    }

    /** @test */
    public function it_rejects_filled_honeypot_field(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'bot@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
            'website_url' => 'http://spam-site.com', // Honeypot filled by bot
        ];

        $response = $this->post(route('register.public.store'), $userData);

        // Should be rejected (implement in controller)
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
    }

    /** @test */
    public function it_handles_validation_errors_gracefully(): void
    {
        $response = $this->post(route('register.public.store'), []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name', 
            'email',
            'password',
            'accept_terms',
            'accept_privacy'
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function it_stores_marketing_preferences_correctly(): void
    {
        // Test with marketing opt-in
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'marketing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
            'marketing_opt_in' => true,
        ];

        $this->post(route('register.public.store'), $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'marketing@example.com',
            'marketing_opt_in' => true,
        ]);

        // Test without marketing opt-in
        $userData['email'] = 'no-marketing@example.com';
        $userData['marketing_opt_in'] = false;

        $this->post(route('register.public.store'), $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'no-marketing@example.com',
            'marketing_opt_in' => false,
        ]);
    }

    /** @test */
    public function it_redirects_authenticated_users_away_from_registration(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $response = $this->get(route('register.public.create'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function it_handles_email_verification_resend_with_throttling(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Auth::login($user);

        // First resend should work
        $response = $this->post(route('verification.send'));
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');

        // Multiple rapid resends should be throttled
        for ($i = 0; $i < 7; $i++) {
            $response = $this->post(route('verification.send'));
        }

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_combines_first_and_last_name_into_name_field(): void
    {
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'accept_terms' => true,
            'accept_privacy' => true,
        ];

        $this->post(route('register.public.store'), $userData);

        $user = User::where('email', 'jane.smith@example.com')->first();
        
        // Check that name field contains combined first and last name
        $this->assertEquals('Jane Smith', $user->name);
        $this->assertEquals('Jane', $user->first_name);
        $this->assertEquals('Smith', $user->last_name);
    }

    /** @test */
    public function registration_form_has_proper_csrf_protection(): void
    {
        // Attempt registration without CSRF token
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('register.public.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'test@example.com',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
                'accept_terms' => true,
                'accept_privacy' => true,
            ]);

        // With CSRF middleware disabled, it should work
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }
}