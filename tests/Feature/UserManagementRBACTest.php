<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class UserManagementRBACTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private $adminUser;
    private $agentUser;
    private $customerUser;
    private $scraperUser;
    private $twoFactorAuthService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users with different roles
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin',
            'surname' => 'User',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->agentUser = User::factory()->create([
            'role' => 'agent',
            'name' => 'Agent',
            'surname' => 'User',
            'username' => 'agent_test',
            'email' => 'agent@test.com',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'customer',
            'name' => 'Customer',
            'surname' => 'User',
            'username' => 'customer_test',
            'email' => 'customer@test.com',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->scraperUser = User::factory()->create([
            'role' => 'scraper',
            'name' => 'Scraper',
            'surname' => 'Bot',
            'username' => 'scraper_test',
            'email' => 'scraper@test.com',
            'is_active' => true,
            'email_verified_at' => null, // Scrapers typically don't need email verification
        ]);

        $this->twoFactorAuthService = app(TwoFactorAuthService::class);
    }

    /** @test */
    public function test_user_roles_and_permissions_are_correctly_assigned()
    {
        // Test Admin permissions
        $this->assertTrue($this->adminUser->isAdmin());
        $this->assertTrue($this->adminUser->canManageUsers());
        $this->assertTrue($this->adminUser->canManageSystem());
        $this->assertTrue($this->adminUser->canAccessFinancials());
        $this->assertTrue($this->adminUser->canAccessSystem());
        $this->assertTrue($this->adminUser->canLoginToWeb());

        // Test Agent permissions
        $this->assertTrue($this->agentUser->isAgent());
        $this->assertTrue($this->agentUser->canSelectAndPurchaseTickets());
        $this->assertTrue($this->agentUser->canManageMonitoring());
        $this->assertTrue($this->agentUser->canViewScrapingMetrics());
        $this->assertFalse($this->agentUser->canManageUsers());
        $this->assertTrue($this->agentUser->canAccessSystem());
        $this->assertTrue($this->agentUser->canLoginToWeb());

        // Test Customer permissions
        $this->assertTrue($this->customerUser->isCustomer());
        $this->assertFalse($this->customerUser->canManageUsers());
        $this->assertFalse($this->customerUser->canSelectAndPurchaseTickets());
        $this->assertTrue($this->customerUser->canAccessSystem());
        $this->assertTrue($this->customerUser->canLoginToWeb());

        // Test Scraper restrictions
        $this->assertTrue($this->scraperUser->isScraper());
        $this->assertFalse($this->scraperUser->canAccessSystem());
        $this->assertFalse($this->scraperUser->canLoginToWeb());
        $this->assertTrue($this->scraperUser->isScrapingRotationUser());
        $this->assertFalse($this->scraperUser->canManageUsers());
    }

    /** @test */
    public function test_user_management_interface_access_control()
    {
        // Admin can access user management
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.users.index'));
        $response->assertStatus(200);

        // Agent cannot access user management
        $response = $this->actingAs($this->agentUser)
                         ->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Customer cannot access user management
        $response = $this->actingAs($this->customerUser)
                         ->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Scraper cannot access user management
        $response = $this->actingAs($this->scraperUser)
                         ->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function test_user_profile_comprehensive_information()
    {
        $userInfo = $this->adminUser->getEnhancedUserInfo();

        $this->assertArrayHasKey('basic_info', $userInfo);
        $this->assertArrayHasKey('profile', $userInfo);
        $this->assertArrayHasKey('last_login', $userInfo);
        $this->assertArrayHasKey('activity_stats', $userInfo);
        $this->assertArrayHasKey('account_creation', $userInfo);
        $this->assertArrayHasKey('permissions', $userInfo);
        $this->assertArrayHasKey('notifications', $userInfo);

        // Verify basic info
        $this->assertEquals('Admin User', $userInfo['basic_info']['full_name']);
        $this->assertEquals('admin_test', $userInfo['basic_info']['username']);
        $this->assertEquals('admin@test.com', $userInfo['basic_info']['email']);

        // Verify permissions
        $this->assertTrue($userInfo['permissions']['permissions']['manage_users']);
        $this->assertTrue($userInfo['permissions']['permissions']['manage_system']);
        $this->assertEquals('admin', $userInfo['permissions']['role']);
    }

    /** @test */
    public function test_bulk_user_operations()
    {
        $testUsers = User::factory()->count(5)->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Test bulk activation/deactivation
        $userIds = $testUsers->pluck('id')->toArray();
        
        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.users.bulk-deactivate'), [
                             'user_ids' => $userIds
                         ]);
        
        $response->assertStatus(200);
        
        foreach ($testUsers as $user) {
            $user->refresh();
            $this->assertFalse($user->is_active);
        }

        // Test bulk role assignment
        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.users.bulk-assign-role'), [
                             'user_ids' => $userIds,
                             'role' => 'agent'
                         ]);
        
        $response->assertStatus(200);
        
        foreach ($testUsers as $user) {
            $user->refresh();
            $this->assertEquals('agent', $user->role);
        }
    }

    /** @test */
    public function test_user_activity_logging()
    {
        // Create a user with activity logging
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testactivity@example.com',
            'role' => 'customer'
        ]);

        // Update user to trigger activity log
        $testUser->update([
            'name' => 'Updated Test User',
            'role' => 'agent'
        ]);

        // Check if activity was logged
        $activities = Activity::where('subject_id', $testUser->id)
                             ->where('subject_type', User::class)
                             ->get();

        $this->assertGreaterThan(0, $activities->count());
    }

    /** @test */
    public function test_two_factor_authentication_setup()
    {
        // Test 2FA is initially disabled
        $this->assertFalse($this->adminUser->two_factor_enabled);
        $this->assertNull($this->adminUser->two_factor_secret);
        $this->assertNull($this->adminUser->two_factor_confirmed_at);

        // Test 2FA setup generation
        $secret = $this->twoFactorAuthService->generateSecret($this->adminUser);
        $this->assertNotEmpty($secret);

        // Test QR code generation
        $qrCode = $this->twoFactorAuthService->generateQRCode($this->adminUser, $secret);
        $this->assertStringContains('svg', $qrCode);

        // Test 2FA enabling
        $this->twoFactorAuthService->enable2FA($this->adminUser, $secret);
        $this->adminUser->refresh();
        
        $this->assertTrue($this->adminUser->two_factor_enabled);
        $this->assertNotNull($this->adminUser->two_factor_secret);
        $this->assertNotNull($this->adminUser->two_factor_confirmed_at);
    }

    /** @test */
    public function test_two_factor_authentication_verification()
    {
        // Enable 2FA for user
        $secret = $this->twoFactorAuthService->generateSecret($this->adminUser);
        $this->twoFactorAuthService->enable2FA($this->adminUser, $secret);

        // Test valid code verification
        $validCode = $this->twoFactorAuthService->generateCurrentCode($secret);
        $this->assertTrue($this->twoFactorAuthService->verifyCode($this->adminUser, $validCode));

        // Test invalid code verification
        $this->assertFalse($this->twoFactorAuthService->verifyCode($this->adminUser, '000000'));
    }

    /** @test */
    public function test_two_factor_recovery_codes()
    {
        // Enable 2FA
        $secret = $this->twoFactorAuthService->generateSecret($this->adminUser);
        $this->twoFactorAuthService->enable2FA($this->adminUser, $secret);

        // Generate recovery codes
        $recoveryCodes = $this->twoFactorAuthService->generateRecoveryCodes($this->adminUser);
        $this->assertCount(8, $recoveryCodes);

        $this->adminUser->refresh();
        $this->assertCount(8, $this->adminUser->two_factor_recovery_codes);

        // Test recovery code verification
        $validRecoveryCode = $recoveryCodes[0];
        $this->assertTrue($this->twoFactorAuthService->verifyRecoveryCode($this->adminUser, $validRecoveryCode));

        // Test invalid recovery code
        $this->assertFalse($this->twoFactorAuthService->verifyRecoveryCode($this->adminUser, 'invalid-code'));
    }

    /** @test */
    public function test_user_search_and_filtering()
    {
        // Create additional test users
        User::factory()->create([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => 'agent',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'customer',
            'is_active' => false,
        ]);

        // Test search functionality
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.users.index', ['search' => 'john']));
        
        $response->assertStatus(200)
                 ->assertSee('John Doe');

        // Test role filtering
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.users.index', ['role' => 'agent']));
        
        $response->assertStatus(200);

        // Test status filtering
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.users.index', ['status' => 'inactive']));
        
        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_import_functionality()
    {
        $csvData = "name,surname,email,username,role,phone\n";
        $csvData .= "Import,User1,import1@test.com,import_user1,customer,1234567890\n";
        $csvData .= "Import,User2,import2@test.com,import_user2,agent,0987654321\n";

        $tempFile = tmpfile();
        fwrite($tempFile, $csvData);
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFilePath,
            'users.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.reports.import-users'), [
                             'file' => $uploadedFile
                         ]);

        $response->assertStatus(200);

        // Verify users were imported
        $this->assertDatabaseHas('users', [
            'email' => 'import1@test.com',
            'username' => 'import_user1',
            'role' => 'customer'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'import2@test.com',
            'username' => 'import_user2',
            'role' => 'agent'
        ]);

        fclose($tempFile);
    }

    /** @test */
    public function test_user_export_functionality()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.users.export'));

        $response->assertStatus(200)
                 ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_permission_matrix_configuration()
    {
        $permissions = $this->adminUser->getPermissions();
        
        $expectedAdminPermissions = [
            'can_access_system' => true,
            'manage_users' => true,
            'manage_system' => true,
            'manage_platforms' => true,
            'access_financials' => true,
            'manage_api_access' => true,
            'delete_any_data' => false, // Only root admin (ticketmaster)
            'select_and_purchase_tickets' => true,
            'manage_monitoring' => true,
            'view_scraping_metrics' => true,
        ];

        foreach ($expectedAdminPermissions as $permission => $expected) {
            $this->assertEquals($expected, $permissions[$permission], 
                "Permission {$permission} should be {$expected} for admin user");
        }
    }

    /** @test */
    public function test_root_admin_special_permissions()
    {
        // Create root admin user (ticketmaster)
        $rootAdmin = User::factory()->create([
            'name' => 'ticketmaster',
            'role' => 'admin',
            'email' => 'root@ticketmaster.com'
        ]);

        $this->assertTrue($rootAdmin->isRootAdmin());
        $this->assertTrue($rootAdmin->canDeleteAnyData());

        // Regular admin should not have root permissions
        $this->assertFalse($this->adminUser->isRootAdmin());
        $this->assertFalse($this->adminUser->canDeleteAnyData());
    }

    /** @test */
    public function test_user_notification_preferences()
    {
        $preferences = $this->adminUser->getNotificationPreferences();
        
        $this->assertArrayHasKey('email_notifications', $preferences);
        $this->assertArrayHasKey('push_notifications', $preferences);
        
        // Default should be true
        $this->assertTrue($preferences['email_notifications']);
        $this->assertTrue($preferences['push_notifications']);
    }

    /** @test */
    public function test_user_profile_display_methods()
    {
        $profileDisplay = $this->adminUser->getProfileDisplay();
        
        $this->assertArrayHasKey('initials', $profileDisplay);
        $this->assertArrayHasKey('full_name', $profileDisplay);
        $this->assertArrayHasKey('display_name', $profileDisplay);
        
        $this->assertEquals('AU', $profileDisplay['initials']);
        $this->assertEquals('Admin User', $profileDisplay['full_name']);
    }

    /** @test */
    public function test_bulk_notification_sending()
    {
        $testUsers = User::factory()->count(3)->create([
            'role' => 'customer',
            'is_active' => true,
            'email_notifications' => true,
        ]);

        $userIds = $testUsers->pluck('id')->toArray();

        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.users.bulk-notify'), [
                             'user_ids' => $userIds,
                             'subject' => 'Test Notification',
                             'message' => 'This is a test bulk notification',
                             'type' => 'info'
                         ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_analytics_widget_data()
    {
        // This would test the dashboard widget data
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.dashboard.user-analytics'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_users',
                     'active_users',
                     'new_users_this_month',
                     'engagement_score',
                     'role_distribution',
                     'recent_activity',
                     'verification_status'
                 ]);
    }
}
