<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use App\Models\UserLegalAcceptance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Override;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_public_registration_form_displays_legal_documents(): void
    {
        $response = $this->get('/register/public');

        $response->assertStatus(200);
        $response->assertViewIs('auth.public-register');
        $response->assertViewHas('legalDocuments');

        // Check that required documents are present
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $response->assertSee($type);
        }
    }

    public function test_successful_customer_registration(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'surname'               => $this->faker->lastName,
            'email'                 => $this->faker->unique()->safeEmail,
            'phone'                 => '+1234567890',
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'enable_2fa'            => FALSE,
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        $response->assertRedirect('/email/verify');
        $response->assertSessionHas('success');

        // Check user was created
        $this->assertDatabaseHas('users', [
            'email'               => $userData['email'],
            'role'                => User::ROLE_CUSTOMER,
            'is_active'           => TRUE,
            'registration_source' => 'public_web',
        ]);

        // Check legal acceptances were recorded
        $user = User::where('email', $userData['email'])->first();
        foreach ($requiredTypes as $type) {
            $this->assertDatabaseHas('user_legal_acceptances', [
                'user_id' => $user->id,
            ]);
        }
    }

    public function test_registration_requires_legal_document_acceptance(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            // Missing legal document acceptances
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

    public function test_registration_validates_email_uniqueness(): void
    {
        // Create existing user
        $existingUser = User::factory()->create();

        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $existingUser->email, // Duplicate email
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_with_2fa_enabled(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $this->faker->unique()->safeEmail,
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

        // Check user was created with 2FA setup
        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->require_2fa);
        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->two_factor_enabled); // Should be false until confirmed
    }

    public function test_registration_validates_phone_format(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $this->faker->unique()->safeEmail,
            'phone'                 => 'invalid-phone', // Invalid format
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $response = $this->post('/register/public', $userData);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_registration_fails_without_legal_documents(): void
    {
        // Remove all legal documents
        LegalDocument::query()->delete();

        $response = $this->get('/register/public');

        $response->assertStatus(503); // Service unavailable
    }

    public function test_user_legal_acceptance_tracking(): void
    {
        $user = User::factory()->create();
        $document = LegalDocument::factory()->create([
            'type'      => LegalDocument::TYPE_TERMS_OF_SERVICE,
            'version'   => '1.0',
            'is_active' => TRUE,
        ]);

        // Record acceptance
        $acceptance = UserLegalAcceptance::recordAcceptance(
            $user->id,
            $document->id,
            $document->version,
            UserLegalAcceptance::METHOD_REGISTRATION,
            '127.0.0.1',
            'TestUserAgent',
        );

        $this->assertNotNull($acceptance);
        $this->assertEquals($user->id, $acceptance->user_id);
        $this->assertEquals($document->id, $acceptance->legal_document_id);
        $this->assertEquals('1.0', $acceptance->document_version);
        $this->assertEquals(UserLegalAcceptance::METHOD_REGISTRATION, $acceptance->acceptance_method);
    }

    public function test_customer_role_assignment(): void
    {
        $userData = [
            'name'                  => $this->faker->firstName,
            'email'                 => $this->faker->unique()->safeEmail,
            'password'              => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        // Add legal document acceptances
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        foreach ($requiredTypes as $type) {
            $userData["accept_{$type}"] = TRUE;
        }

        $this->post('/register/public', $userData);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue($user->isCustomer());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isAgent());
        $this->assertFalse($user->isScraper());
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
            ]);
        }
    }
}
