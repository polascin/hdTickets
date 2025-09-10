<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use App\Models\UserLegalAcceptance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test suite for the modernized registration flow
 *
 * Tests the multi-step registration process, validation endpoints,
 * and integration with legal document acceptance system.
 */
class ModernRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function registration_page_displays_modern_stepper_interface(): void
    {
        $response = $this->get('/register/public');

        $response->assertStatus(200);
        $response->assertViewIs('auth.public-register');
        $response->assertViewHas('legalDocuments');

        // Check for modern UI elements
        $response->assertSee('Create Your Account');
        $response->assertSee('Step 1 of 3');
        $response->assertSee('Account');
        $response->assertSee('Security');
        $response->assertSee('Legal');

        // Check for Alpine.js integration
        $response->assertSee('x-data="registrationForm()"');
        $response->assertSee('currentStep');
    }

    #[Test]
    public function validation_endpoint_provides_field_level_feedback(): void
    {
        $response = $this->postJson('/register/public/validate', [
            'name'  => 'John',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => ['email'],
            'fields' => [
                'name'  => ['value', 'valid', 'touched'],
                'email' => ['value', 'valid', 'error', 'format_valid'],
            ],
            'timestamp',
        ]);

        $response->assertJson([
            'success' => FALSE,
            'fields'  => [
                'name'  => ['valid' => TRUE],
                'email' => ['valid' => FALSE, 'format_valid' => FALSE],
            ],
        ]);
    }

    #[Test]
    public function email_availability_endpoint_checks_uniqueness(): void
    {
        // Test available email
        $response = $this->postJson('/register/public/check-email', [
            'email' => 'available@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => TRUE,
            'available' => TRUE,
            'email'     => 'available@example.com',
        ]);

        // Create existing user
        User::factory()->create(['email' => 'taken@example.com']);

        // Test taken email
        $response = $this->postJson('/register/public/check-email', [
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => TRUE,
            'available' => FALSE,
            'email'     => 'taken@example.com',
        ]);
    }

    #[Test]
    public function password_strength_endpoint_provides_detailed_feedback(): void
    {
        $response = $this->postJson('/register/public/check-password', [
            'password' => 'weak',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'password_length',
            'strength_score',
            'strength_label',
            'requirements_met',
            'requirements_missing',
            'suggestions',
            'timestamp',
        ]);

        $response->assertJson([
            'success'        => TRUE,
            'strength_label' => 'weak',
            'strength_score' => 20,
        ]);

        $this->assertContains('At least 8 characters', $response->json('requirements_missing'));

        // Test strong password
        $response = $this->postJson('/register/public/check-password', [
            'password' => 'StrongP@ssw0rd123!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'        => TRUE,
            'strength_label' => 'strong',
            'strength_score' => 100,
        ]);
    }

    #[Test]
    public function complete_registration_flow_with_all_features(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'surname'               => $this->faker->lastName,
            'email'                 => $this->faker->unique()->safeEmail,
            'phone'                 => '+1234567890',
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'enable_2fa'            => TRUE,
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        $response->assertRedirect('/email/verify');
        $response->assertSessionHas('success');

        // Check user was created with correct attributes
        $this->assertDatabaseHas('users', [
            'email'               => $userData['email'],
            'role'                => User::ROLE_CUSTOMER,
            'is_active'           => TRUE,
            'registration_source' => 'public_web',
            'require_2fa'         => TRUE,
        ]);

        // Check 2FA secret was generated but not enabled
        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->two_factor_enabled);

        // Check legal acceptances were recorded
        foreach ($requiredTypes as $type) {
            $this->assertTrue(
                UserLegalAcceptance::where('user_id', $user->id)
                    ->whereHas('legalDocument', fn ($q) => $q->where('type', $type))
                    ->exists(),
            );
        }
    }

    #[Test]
    public function registration_fails_without_required_legal_acceptances(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            // Intentionally missing legal document acceptances
        ];

        $response = $this->post('/register/public', $userData);

        $response->assertSessionHasErrors();

        // Check that each required document acceptance is required
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $response->assertSessionHasErrorsIn('default', "accept_{$type}");
        }

        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => $userData['email'],
        ]);
    }

    #[Test]
    public function registration_handles_step_navigation_correctly(): void
    {
        // Test registration with current_step parameter
        $userData = [
            'name'                  => 'John',
            'surname'               => 'Doe',
            'email'                 => 'john@example.com',
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'current_step'          => 2, // Simulate being on step 2
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        // Should still complete registration successfully regardless of step
        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name'  => $userData['name'],
        ]);
    }

    #[Test]
    public function validation_endpoints_respect_rate_limiting(): void
    {
        // Test rate limiting on validation endpoint (60 requests per minute)
        for ($i = 0; $i < 65; $i++) {
            $response = $this->postJson('/register/public/validate', ['name' => "test{$i}"]);

            if ($i < 60) {
                $this->assertTrue($response->status() !== 429, "Request {$i} should not be rate limited");
            }
        }

        // The 61st+ request should be rate limited
        $response = $this->postJson('/register/public/validate', ['name' => 'test_rate_limit']);
        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function registration_preserves_server_side_validation_as_source_of_truth(): void
    {
        // Attempt registration with data that might pass client-side but fail server-side
        $userData = [
            'name'                  => str_repeat('a', 300), // Exceeds max 255 chars
            'email'                 => 'valid@example.com',
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        // Should fail with validation errors
        $response->assertSessionHasErrors(['name']);

        // User should not be created
        $this->assertDatabaseMissing('users', [
            'email' => $userData['email'],
        ]);
    }

    #[Test]
    public function registration_handles_optional_fields_correctly(): void
    {
        $userData = [
            'name' => 'Jane',
            // surname is optional
            'email' => 'jane@example.com',
            // phone is optional
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            // enable_2fa is optional (defaults to false)
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email'       => $userData['email'],
            'name'        => $userData['name'],
            'surname'     => NULL,
            'phone'       => NULL,
            'require_2fa' => FALSE,
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNull($user->two_factor_secret);
        $this->assertFalse($user->two_factor_enabled);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Create required legal documents for testing
        $this->createRequiredLegalDocuments();
    }

    private function createRequiredLegalDocuments(): void
    {
        $requiredTypes = LegalDocument::getRequiredForRegistration();

        foreach ($requiredTypes as $type) {
            LegalDocument::factory()->create([
                'type'                => $type,
                'is_active'           => TRUE,
                'requires_acceptance' => TRUE,
                'effective_date'      => now()->subDay(),
                'title'               => ucfirst(str_replace('_', ' ', $type)),
                'content'             => $this->faker->paragraphs(3, TRUE),
                'summary'             => $this->faker->sentence(),
                'version'             => '1.0',
            ]);
        }
    }
}
