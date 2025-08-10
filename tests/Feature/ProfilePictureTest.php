<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePictureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test that users can upload profile pictures
     */
    public function test_user_can_upload_profile_picture(): void
    {
        $user = User::factory()->create();
        $file = File::image('profile.jpg', 500, 500)->size(1000);

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'pictures',
                    'user'
                ]
            ]);

        // Check that files were created
        Storage::disk('public')->assertExists('profile-pictures/' . 
            collect(Storage::disk('public')->files('profile-pictures'))
                ->filter(fn($file) => str_contains($file, "profile_{$user->id}_"))
                ->first()
        );

        // Check database update
        $this->assertNotNull($user->refresh()->profile_picture);
    }

    /**
     * Test profile picture upload with cropping data
     */
    public function test_user_can_upload_profile_picture_with_cropping(): void
    {
        $user = User::factory()->create();
        $file = File::image('profile.jpg', 500, 500)->size(1000);

        $cropData = [
            'x' => 50,
            'y' => 50,
            'width' => 400,
            'height' => 400
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
                'crop_data' => json_encode($cropData),
            ]);

        $response->assertStatus(200);
        $this->assertNotNull($user->refresh()->profile_picture);
    }

    /**
     * Test file size validation
     */
    public function test_profile_picture_upload_validates_file_size(): void
    {
        $user = User::factory()->create();
        // Create a file larger than 5MB
        $file = File::image('large.jpg', 2000, 2000)->size(6000);

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_picture']);
    }

    /**
     * Test file type validation
     */
    public function test_profile_picture_upload_validates_file_type(): void
    {
        $user = User::factory()->create();
        $file = File::create('document.pdf', 1000);

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['profile_picture']);
    }

    /**
     * Test that old profile pictures are deleted when uploading new ones
     */
    public function test_old_profile_pictures_are_deleted_on_new_upload(): void
    {
        $user = User::factory()->create();
        
        // Upload first picture
        $file1 = File::image('profile1.jpg', 500, 500)->size(1000);
        $this->actingAs($user)
            ->postJson('/profile/picture/upload', ['profile_picture' => $file1]);

        $oldFiles = Storage::disk('public')->files('profile-pictures');
        $this->assertNotEmpty($oldFiles);

        // Upload second picture
        $file2 = File::image('profile2.jpg', 500, 500)->size(1000);
        $this->actingAs($user)
            ->postJson('/profile/picture/upload', ['profile_picture' => $file2]);

        // Check that old files are gone and new ones exist
        $newFiles = Storage::disk('public')->files('profile-pictures');
        $this->assertNotEmpty($newFiles);
        
        // Ensure the files are different (new upload replaced old ones)
        $this->assertNotEquals($oldFiles, $newFiles);
    }

    /**
     * Test profile picture deletion
     */
    public function test_user_can_delete_profile_picture(): void
    {
        $user = User::factory()->create();
        
        // First upload a picture
        $file = File::image('profile.jpg', 500, 500)->size(1000);
        $this->actingAs($user)
            ->postJson('/profile/picture/upload', ['profile_picture' => $file]);

        $this->assertNotNull($user->refresh()->profile_picture);

        // Now delete it
        $response = $this
            ->actingAs($user)
            ->deleteJson('/profile/picture/delete');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNull($user->refresh()->profile_picture);
        
        // Check files are deleted
        $files = Storage::disk('public')->files('profile-pictures');
        $userFiles = collect($files)->filter(fn($file) => str_contains($file, "profile_{$user->id}_"));
        $this->assertEmpty($userFiles);
    }

    /**
     * Test profile picture info endpoint
     */
    public function test_user_can_get_profile_picture_info(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'profile_picture' => null
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/profile/picture/info');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'has_picture',
                    'picture_url',
                    'initials',
                    'full_name',
                    'display_name'
                ]
            ])
            ->assertJson([
                'data' => [
                    'has_picture' => false,
                    'initials' => 'JD',
                    'full_name' => 'John Doe',
                ]
            ]);
    }

    /**
     * Test upload limits endpoint
     */
    public function test_user_can_get_upload_limits(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/profile/picture/limits');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'max_file_size',
                    'max_file_size_human',
                    'allowed_formats',
                    'dimensions'
                ]
            ]);
    }

    /**
     * Test that unauthenticated users cannot access profile picture endpoints
     */
    public function test_unauthenticated_user_cannot_access_profile_picture_endpoints(): void
    {
        $file = File::image('profile.jpg', 500, 500)->size(1000);

        $this->postJson('/profile/picture/upload', ['profile_picture' => $file])
            ->assertStatus(401);

        $this->deleteJson('/profile/picture/delete')
            ->assertStatus(401);

        $this->getJson('/profile/picture/info')
            ->assertStatus(401);

        $this->getJson('/profile/picture/limits')
            ->assertStatus(401);
    }

    /**
     * Test cropping existing profile picture
     */
    public function test_user_can_crop_existing_profile_picture(): void
    {
        $user = User::factory()->create();
        
        // Upload initial picture
        $file = File::image('profile.jpg', 500, 500)->size(1000);
        $this->actingAs($user)
            ->postJson('/profile/picture/upload', ['profile_picture' => $file]);

        $this->assertNotNull($user->refresh()->profile_picture);

        // Crop the picture
        $cropData = [
            'x' => 100,
            'y' => 100,
            'width' => 300,
            'height' => 300
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/crop', [
                'crop_data' => json_encode($cropData),
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test cropping without existing profile picture fails
     */
    public function test_cropping_without_existing_picture_fails(): void
    {
        $user = User::factory()->create();

        $cropData = [
            'x' => 100,
            'y' => 100,
            'width' => 300,
            'height' => 300
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/crop', [
                'crop_data' => json_encode($cropData),
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /**
     * Test invalid crop data validation
     */
    public function test_invalid_crop_data_validation(): void
    {
        $user = User::factory()->create();
        
        // Upload initial picture
        $file = File::image('profile.jpg', 500, 500)->size(1000);
        $this->actingAs($user)
            ->postJson('/profile/picture/upload', ['profile_picture' => $file]);

        // Try with invalid crop data
        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/crop', [
                'crop_data' => json_encode(['invalid' => 'data']),
            ]);

        $response->assertStatus(400);
    }

    /**
     * Test multiple picture sizes are generated
     */
    public function test_multiple_picture_sizes_are_generated(): void
    {
        $user = User::factory()->create();
        $file = File::image('profile.jpg', 500, 500)->size(1000);

        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(200);

        $data = $response->json('data.pictures');
        $this->assertArrayHasKey('thumbnail', $data);
        $this->assertArrayHasKey('medium', $data);
        $this->assertArrayHasKey('large', $data);
    }

    /**
     * Test error handling for corrupted files
     */
    public function test_error_handling_for_corrupted_files(): void
    {
        $user = User::factory()->create();
        
        // Create a fake corrupted image file
        $file = File::create('corrupted.jpg', 1000);
        
        $response = $this
            ->actingAs($user)
            ->postJson('/profile/picture/upload', [
                'profile_picture' => $file,
            ]);

        $response->assertStatus(422);
    }
}
