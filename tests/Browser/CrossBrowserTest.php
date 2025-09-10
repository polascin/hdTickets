<?php declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CrossBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $testUser;

    private string $testPassword = 'TestP@ssw0rd123!';

    public function setUp(): void
    {
        $this->testUser = User::factory()->create([
            'email'     => 'crossbrowser@test.com',
            'password'  => Hash::make($this->testPassword),
            'is_active' => TRUE,
            'role'      => 'customer',
        ]);
    }

    /**
     * @test
     */
    public function test_chrome_login_functionality(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertSee('Email Address')
                ->assertSee('Password')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard')
                ->assertAuthenticatedAs($this->testUser);
        });
    }

    /**
     * @test
     */
    public function test_firefox_compatibility(): void
    {
        // Note: This would require Firefox setup in your testing environment
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertVisible('#email')
                ->assertVisible('#password')
                ->assertVisible('#login-submit-btn')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_responsive_design_mobile(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->resize(375, 667) // iPhone 6/7/8 dimensions
                ->visit('/login')
                ->assertVisible('#login-form')
                ->assertVisible('#email')
                ->assertVisible('#password')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_responsive_design_tablet(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->resize(768, 1024) // iPad dimensions
                ->visit('/login')
                ->assertVisible('#login-form')
                ->assertVisible('#email')
                ->assertVisible('#password')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_form_validation_across_browsers(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', 'invalid-email')
                ->type('password', '')
                ->press('Sign In')
                ->waitFor('.hd-error-message', 5)
                ->assertSee('The email field must be a valid email address')
                ->assertSee('The password field is required');
        });
    }

    /**
     * @test
     */
    public function test_remember_me_functionality(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->check('remember')
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard')
                ->assertCookieValue(config('session.cookie'), NULL, FALSE); // Cookie should exist
        });
    }

    /**
     * @test
     */
    public function test_password_visibility_toggle(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('password', $this->testPassword)
                ->assertAttribute('#password', 'type', 'password')
                ->click('#password-toggle')
                ->pause(500)
                ->assertAttribute('#password', 'type', 'text')
                ->click('#password-toggle')
                ->pause(500)
                ->assertAttribute('#password', 'type', 'password');
        });
    }

    /**
     * @test
     */
    public function test_keyboard_navigation_accessibility(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->keys('body', '{tab}') // Tab to skip link
                ->assertFocused('a.hd-skip-nav')
                ->keys('body', '{tab}') // Tab to email field
                ->assertFocused('#email')
                ->type('#email', $this->testUser->email)
                ->keys('body', '{tab}') // Tab to password field
                ->assertFocused('#password')
                ->type('#password', $this->testPassword)
                ->keys('body', '{tab}') // Tab to remember checkbox
                ->assertFocused('#remember_me')
                ->keys('body', '{tab}') // Tab to submit button
                ->assertFocused('#login-submit-btn')
                ->keys('body', '{enter}') // Submit form
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_form_submission_loading_state(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitFor('.loading', 2) // Assuming loading class is added
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_error_handling_display(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', 'wrong@email.com')
                ->type('password', 'wrongpassword')
                ->press('Sign In')
                ->waitFor('.hd-error-message', 5)
                ->assertSee('Invalid login credentials')
                ->assertPresent('#hd-alert-region[aria-live="assertive"]');
        });
    }

    /**
     * @test
     */
    public function test_csrf_token_handling(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertPresent('input[name="_token"]')
                ->assertAttributeIsNotEmpty('input[name="_token"]', 'value');
        });
    }

    /**
     * @test
     */
    public function test_honeypot_field_hidden(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertPresent('input[name="website"]')
                ->assertAttribute('input[name="website"]', 'style', 'display: none;')
                ->assertAttribute('input[name="website"]', 'tabindex', '-1');
        });
    }

    /**
     * @test
     */
    public function test_mobile_browser_compatibility(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->resize(375, 667) // Mobile size
                ->visit('/login')
                ->waitFor('#login-form', 5)
                ->assertVisible('#login-form')
                ->assertVisible('.hd-form-group')
                ->assertVisible('#email')
                ->assertVisible('#password')
                ->assertVisible('#login-submit-btn')
                ->tap('#email')
                ->type('#email', $this->testUser->email)
                ->tap('#password')
                ->type('#password', $this->testPassword)
                ->tap('#login-submit-btn')
                ->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_screen_reader_announcements(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertPresent('#hd-status-region[aria-live="polite"]')
                ->assertPresent('#hd-alert-region[aria-live="assertive"]')
                ->type('email', 'invalid-email')
                ->press('Sign In')
                ->waitFor('.hd-error-message', 5)
                ->assertPresent('.hd-error-message[role="alert"]');
        });
    }

    /**
     * @test
     */
    public function test_color_contrast_accessibility(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                // Test that error messages have sufficient contrast
                ->type('email', '')
                ->press('Sign In')
                ->waitFor('.hd-error-message', 5)
                ->script('
                    const errorElement = document.querySelector(".hd-error-message");
                    const styles = window.getComputedStyle(errorElement);
                    return {
                        color: styles.color,
                        backgroundColor: styles.backgroundColor
                    };
                ');
            // Note: Actual contrast checking would require additional color analysis tools
        });
    }

    /**
     * @test
     */
    public function test_javascript_disabled_fallback(): void
    {
        $this->browse(function (Browser $browser): void {
            // Disable JavaScript and test form functionality
            $browser->disableJavaScript()
                ->visit('/login')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }

    /**
     * @test
     */
    public function test_session_timeout_handling(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard')
                ->pause(1000) // Wait to ensure session is established
                ->visit('/logout')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }

    /**
     * @test
     */
    public function test_form_auto_fill_compatibility(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->assertAttribute('#email', 'autocomplete', 'email username')
                ->assertAttribute('#password', 'autocomplete', 'current-password')
                ->type('email', $this->testUser->email)
                ->type('password', $this->testPassword)
                ->press('Sign In')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }
}
