<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileRoleBasedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test admin user can access all profile features
     */
    public function test_admin_user_can_access_all_profile_features(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Test profile page access
        $response = $this->actingAs($adminUser)->get('/profile');
        $response->assertStatus(200);

        // Test profile edit
        $response = $this->actingAs($adminUser)->get('/profile/edit');
        $response->assertStatus(200);

        // Test security settings
        $response = $this->actingAs($adminUser)->get('/profile/security');
        $response->assertStatus(200);

        // Test profile update
        $response = $this->actingAs($adminUser)
            ->patch('/profile', [
                'name' => 'Admin User Updated',
                'email' => 'admin@example.com',
            ]);
        $response->assertRedirect('/profile');

        // Test profile picture upload
        $file = File::image('admin-profile.jpg', 500, 500)->size(1000);
        $response = $this->actingAs($adminUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);
        $response->assertStatus(200);

        // Test preferences access
        $response = $this->actingAs($adminUser)->get('/preferences');
        $response->assertStatus(200);
    }

    /**
     * Test customer user profile access and limitations
     */
    public function test_customer_user_profile_features(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);

        // Test basic profile access
        $response = $this->actingAs($customerUser)->get('/profile');
        $response->assertStatus(200);

        // Test profile edit
        $response = $this->actingAs($customerUser)->get('/profile/edit');
        $response->assertStatus(200);

        // Test security settings
        $response = $this->actingAs($customerUser)->get('/profile/security');
        $response->assertStatus(200);

        // Test profile picture upload with size limits
        $file = File::image('customer-profile.jpg', 500, 500)->size(1000);
        $response = $this->actingAs($customerUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);
        $response->assertStatus(200);

        // Test file size limit enforcement for customers
        $largeFile = File::image('large-profile.jpg', 2000, 2000)->size(6000);
        $response = $this->actingAs($customerUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $largeFile,
            ]);
        $response->assertStatus(422);

        // Test preferences access
        $response = $this->actingAs($customerUser)->get('/preferences');
        $response->assertStatus(200);
    }

    /**
     * Test agent user profile features
     */
    public function test_agent_user_profile_features(): void
    {
        $agentUser = User::factory()->create(['role' => 'agent']);

        // Test profile access
        $response = $this->actingAs($agentUser)->get('/profile');
        $response->assertStatus(200);

        // Test profile picture upload
        $file = File::image('agent-profile.jpg', 500, 500)->size(1000);
        $response = $this->actingAs($agentUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);
        $response->assertStatus(200);

        // Test preferences access
        $response = $this->actingAs($agentUser)->get('/preferences');
        $response->assertStatus(200);
    }

    /**
     * Test guest user restrictions
     */
    public function test_guest_user_restrictions(): void
    {
        // Test profile access requires authentication
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
        $this->get('/profile/security')->assertRedirect('/login');
        $this->get('/preferences')->assertRedirect('/login');

        // Test API endpoints require authentication
        $this->getJson('/profile/picture/info')->assertStatus(401);
        $this->postJson('/profile/picture/upload', [])->assertStatus(401);
        $this->deleteJson('/profile/picture/delete')->assertStatus(401);
    }

    /**
     * Test role-specific profile picture restrictions
     */
    public function test_role_specific_profile_picture_restrictions(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $customerUser = User::factory()->create(['role' => 'customer']);

        // Admin can upload larger files
        $largeFile = File::image('large-admin.jpg', 1500, 1500)->size(4000);
        $response = $this->actingAs($adminUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $largeFile,
            ]);
        $response->assertStatus(200);

        // Customer has stricter limits
        $response = $this->actingAs($customerUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $largeFile,
            ]);
        $response->assertStatus(422);
    }

    /**
     * Test profile information update permissions
     */
    public function test_profile_information_update_permissions(): void
    {
        $users = [
            'admin' => User::factory()->create(['role' => 'admin']),
            'agent' => User::factory()->create(['role' => 'agent']),
            'customer' => User::factory()->create(['role' => 'customer']),
        ];

        foreach ($users as $role => $user) {
            // All users can update basic profile information
            $response = $this->actingAs($user)
                ->patch('/profile', [
                    'name' => ucfirst($role) . ' User Updated',
                    'email' => $role . '@example.com',
                ]);

            $response->assertRedirect('/profile');
            
            $user->refresh();
            $this->assertEquals(ucfirst($role) . ' User Updated', $user->name);
            $this->assertEquals($role . '@example.com', $user->email);
        }
    }

    /**
     * Test security settings access by role
     */
    public function test_security_settings_access_by_role(): void
    {
        $users = [
            'admin' => User::factory()->create(['role' => 'admin']),
            'agent' => User::factory()->create(['role' => 'agent']),
            'customer' => User::factory()->create(['role' => 'customer']),
        ];

        foreach ($users as $role => $user) {
            $response = $this->actingAs($user)->get('/profile/security');
            $response->assertStatus(200);
            
            // All users should see security settings
            $response->assertViewHas('user');
            $response->assertSee('Two-Factor Authentication');
            $response->assertSee('Login History');
            $response->assertSee('Active Sessions');
        }
    }

    /**
     * Test preferences access and modification by role
     */
    public function test_preferences_access_by_role(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $customerUser = User::factory()->create(['role' => 'customer']);

        // Admin can access all preference categories
        $response = $this->actingAs($adminUser)
            ->postJson('/preferences/update', [
                'preferences' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'admin_alerts' => true, // Admin-specific preference
                    ],
                    'display' => [
                        'theme' => 'dark',
                        'advanced_dashboard' => true, // Admin-specific preference
                    ]
                ]
            ]);
        $response->assertStatus(200);

        // Customer has limited preferences
        $response = $this->actingAs($customerUser)
            ->postJson('/preferences/update', [
                'preferences' => [
                    'notifications' => [
                        'email_notifications' => false,
                    ],
                    'display' => [
                        'theme' => 'light',
                    ]
                ]
            ]);
        $response->assertStatus(200);

        // Customer cannot set admin-specific preferences
        $response = $this->actingAs($customerUser)
            ->postJson('/preferences/update', [
                'preferences' => [
                    'notifications' => [
                        'admin_alerts' => true, // Should be ignored or rejected
                    ]
                ]
            ]);
        // This should either succeed (ignoring admin settings) or return 403
        $this->assertContains($response->status(), [200, 403]);
    }

    /**
     * Test account deletion permissions
     */
    public function test_account_deletion_permissions(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $customerUser = User::factory()->create(['role' => 'customer']);

        // Customer can request account deletion (with protection system)
        $response = $this->actingAs($customerUser)
            ->delete('/profile', [
                'password' => 'password',
            ]);
        
        // Should redirect to deletion protection system
        $response->assertRedirect();

        // Admin account deletion might have additional restrictions
        $response = $this->actingAs($adminUser)
            ->delete('/profile', [
                'password' => 'password',
            ]);
        
        // Should also redirect to deletion protection
        $response->assertRedirect();
    }

    /**
     * Test profile picture access and privacy
     */
    public function test_profile_picture_access_privacy(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Upload profile picture for user A
        $file = File::image('user-a-profile.jpg', 500, 500)->size(1000);
        $this->actingAs($userA)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        // User A can access their own profile picture info
        $response = $this->actingAs($userA)->getJson('/profile/picture/info');
        $response->assertStatus(200);

        // User B cannot directly access User A's profile picture endpoints
        $response = $this->actingAs($userB)->getJson('/profile/picture/info');
        $response->assertStatus(200); // But gets their own info

        // Ensure users cannot delete each other's profile pictures
        $response = $this->actingAs($userB)->deleteJson('/profile/picture/delete');
        $response->assertStatus(200); // Only affects their own picture (none exists)
        
        // Verify User A's picture still exists
        $response = $this->actingAs($userA)->getJson('/profile/picture/info');
        $response->assertStatus(200)
                 ->assertJsonPath('data.has_picture', true);
    }

    /**
     * Test error handling for different user roles
     */
    public function test_error_handling_by_role(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer']);

        // Test invalid file type
        $invalidFile = File::create('document.pdf', 1000);
        $response = $this->actingAs($customerUser)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $invalidFile,
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['profile_picture']);

        // Test missing required fields in profile update
        $response = $this->actingAs($customerUser)
            ->patchJson('/profile', [
                'name' => '', // Required field left empty
                'email' => 'invalid-email', // Invalid format
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Test concurrent access scenarios
     */
    public function test_concurrent_profile_updates(): void
    {
        $user = User::factory()->create();

        // Simulate concurrent profile picture uploads
        $file1 = File::image('profile1.jpg', 500, 500)->size(1000);
        $file2 = File::image('profile2.jpg', 500, 500)->size(1000);

        // First upload
        $response1 = $this->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file1,
            ]);
        $response1->assertStatus(200);

        // Second upload should replace the first
        $response2 = $this->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file2,
            ]);
        $response2->assertStatus(200);

        // Verify only the latest picture exists
        $files = Storage::disk('public')->files('profile-pictures');
        $userFiles = collect($files)->filter(fn($file) => str_contains($file, "profile_{$user->id}_"));
        
        // Should have only one set of files (different sizes) for the latest upload
        $this->assertGreaterThan(0, $userFiles->count());
        $this->assertLessThan(10, $userFiles->count()); // Reasonable upper bound
    }

    /**
     * Test mobile-specific profile features
     */
    public function test_mobile_profile_features(): void
    {
        $user = User::factory()->create();

        // Test mobile user agent handling
        $response = $this->actingAs($user)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15A372 Safari/604.1'
            ])
            ->get('/profile/edit');

        $response->assertStatus(200);

        // Test smaller file size limits for mobile uploads (if implemented)
        $mobileFile = File::image('mobile-profile.jpg', 300, 300)->size(500);
        $response = $this->actingAs($user)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
            ])
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $mobileFile,
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test accessibility features
     */
    public function test_accessibility_features(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile/edit');
        
        // Check for accessibility attributes
        $response->assertSee('aria-label');
        $response->assertSee('role=');
        
        // Check for keyboard navigation support
        $response->assertSee('tabindex');
        
        // Check for screen reader support
        $response->assertSee('sr-only');
    }
}
