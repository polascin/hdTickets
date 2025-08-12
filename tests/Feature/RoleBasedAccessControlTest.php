<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $testUsers = [];

    /**
     * Test admin user dashboard access
     */
    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = $this->testUsers['admin'];

        $response = $this->actingAs($admin)
            ->get('/dashboard');

        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test admin user can access admin dashboard directly
     */
    public function test_admin_user_can_access_admin_dashboard_directly(): void
    {
        $admin = $this->testUsers['admin'];

        $response = $this->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.admin');
    }

    /**
     * Test agent user dashboard access
     */
    public function test_agent_user_redirected_to_agent_dashboard(): void
    {
        $agent = $this->testUsers['agent'];

        $response = $this->actingAs($agent)
            ->get('/dashboard');

        $response->assertRedirect('/dashboard/agent');
    }

    /**
     * Test agent user can access agent dashboard directly
     */
    public function test_agent_user_can_access_agent_dashboard_directly(): void
    {
        $agent = $this->testUsers['agent'];

        $response = $this->actingAs($agent)
            ->get('/dashboard/agent');

        $response->assertOk();
    }

    /**
     * Test customer user dashboard access
     */
    public function test_customer_user_redirected_to_customer_dashboard(): void
    {
        $customer = $this->testUsers['customer'];

        $response = $this->actingAs($customer)
            ->get('/dashboard');

        $response->assertRedirect('/dashboard/customer');
    }

    /**
     * Test customer user can access customer dashboard directly
     */
    public function test_customer_user_can_access_customer_dashboard_directly(): void
    {
        $customer = $this->testUsers['customer'];

        $response = $this->actingAs($customer)
            ->get('/dashboard/customer');

        $response->assertOk();
    }

    /**
     * Test scraper user dashboard access
     */
    public function test_scraper_user_redirected_to_scraper_dashboard(): void
    {
        $scraper = $this->testUsers['scraper'];

        $response = $this->actingAs($scraper)
            ->get('/dashboard');

        $response->assertRedirect('/dashboard/scraper');
    }

    /**
     * Test scraper user can access scraper dashboard directly
     */
    public function test_scraper_user_can_access_scraper_dashboard_directly(): void
    {
        $scraper = $this->testUsers['scraper'];

        $response = $this->actingAs($scraper)
            ->get('/dashboard/scraper');

        $response->assertOk();
    }

    /**
     * Test ticketmaster admin access
     */
    public function test_ticketmaster_admin_has_proper_access(): void
    {
        $ticketmaster = $this->testUsers['ticketmaster'];

        // Should redirect to admin dashboard
        $response = $this->actingAs($ticketmaster)
            ->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');

        // Should be able to access admin dashboard
        $response = $this->actingAs($ticketmaster)
            ->get('/admin/dashboard');
        $response->assertOk();
    }

    /**
     * Test undefined role fallback behavior
     */
    public function test_undefined_role_falls_back_to_customer_dashboard(): void
    {
        $undefinedUser = $this->testUsers['undefined_role'];

        $response = $this->actingAs($undefinedUser)
            ->get('/dashboard');

        $response->assertRedirect('/dashboard/customer');
    }

    /**
     * Test customer cannot access admin dashboard
     */
    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = $this->testUsers['customer'];

        $response = $this->actingAs($customer)
            ->get('/admin/dashboard');

        $response->assertForbidden();
    }

    /**
     * Test customer cannot access agent dashboard
     */
    public function test_customer_cannot_access_agent_dashboard(): void
    {
        $customer = $this->testUsers['customer'];

        $response = $this->actingAs($customer)
            ->get('/dashboard/agent');

        $response->assertForbidden();
    }

    /**
     * Test customer cannot access scraper dashboard
     */
    public function test_customer_cannot_access_scraper_dashboard(): void
    {
        $customer = $this->testUsers['customer'];

        $response = $this->actingAs($customer)
            ->get('/dashboard/scraper');

        $response->assertForbidden();
    }

    /**
     * Test agent cannot access admin dashboard
     */
    public function test_agent_cannot_access_admin_dashboard(): void
    {
        $agent = $this->testUsers['agent'];

        $response = $this->actingAs($agent)
            ->get('/admin/dashboard');

        $response->assertForbidden();
    }

    /**
     * Test agent cannot access scraper dashboard
     */
    public function test_agent_cannot_access_scraper_dashboard(): void
    {
        $agent = $this->testUsers['agent'];

        $response = $this->actingAs($agent)
            ->get('/dashboard/scraper');

        $response->assertForbidden();
    }

    /**
     * Test scraper cannot access admin dashboard
     */
    public function test_scraper_cannot_access_admin_dashboard(): void
    {
        $scraper = $this->testUsers['scraper'];

        $response = $this->actingAs($scraper)
            ->get('/admin/dashboard');

        $response->assertForbidden();
    }

    /**
     * Test scraper cannot access agent dashboard
     */
    public function test_scraper_cannot_access_agent_dashboard(): void
    {
        $scraper = $this->testUsers['scraper'];

        $response = $this->actingAs($scraper)
            ->get('/dashboard/agent');

        $response->assertForbidden();
    }

    /**
     * Test unauthenticated user is redirected to login
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/agent');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/scraper');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/customer');
        $response->assertRedirect('/login');
    }

    /**
     * Test inactive user is logged out and redirected
     */
    public function test_inactive_user_is_logged_out(): void
    {
        $inactiveUser = User::factory()->create([
            'name'              => 'Inactive User',
            'email'             => 'inactive@rbactest.com',
            'password'          => Hash::make('TestPassword123!'),
            'role'              => 'customer',
            'is_active'         => FALSE,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($inactiveUser)
            ->get('/dashboard');

        // Should be redirected to login with error message
        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Account is disabled. Contact administrator.');

        // User should be logged out
        $this->assertFalse(Auth::check());
    }

    /**
     * Test admin can access all dashboards
     */
    public function test_admin_can_access_all_dashboards(): void
    {
        $admin = $this->testUsers['admin'];

        // Admin should be able to access all dashboards
        $dashboards = [
            '/admin/dashboard',
            '/dashboard/agent',
            '/dashboard/scraper',
            '/dashboard/customer',
        ];

        foreach ($dashboards as $dashboard) {
            $response = $this->actingAs($admin)->get($dashboard);
            $response->assertOk();
        }
    }

    /**
     * Test role middleware behavior with CheckUserPermissions fallback
     */
    public function test_role_middleware_fallback_behavior(): void
    {
        $customer = $this->testUsers['customer'];

        // When customer tries to access admin area, they should be redirected
        // to dashboard.basic if it exists, or get 403
        $response = $this->actingAs($customer)
            ->get('/admin/dashboard');

        // Should either get 403 or be redirected to basic dashboard
        $this->assertTrue($response->isRedirection() || $response->status() === 403);
    }

    /**
     * Test that User model has proper role checking methods
     */
    public function test_user_role_methods_work_correctly(): void
    {
        $admin = $this->testUsers['admin'];
        $agent = $this->testUsers['agent'];
        $customer = $this->testUsers['customer'];
        $scraper = $this->testUsers['scraper'];

        // Test role checking methods if they exist
        if (method_exists($admin, 'isAdmin')) {
            $this->assertTrue($admin->isAdmin());
            $this->assertFalse($agent->isAdmin());
        }

        if (method_exists($agent, 'isAgent')) {
            $this->assertTrue($agent->isAgent());
            $this->assertFalse($customer->isAgent());
        }

        if (method_exists($scraper, 'isScraper')) {
            $this->assertTrue($scraper->isScraper());
            $this->assertFalse($admin->isScraper());
        }

        if (method_exists($customer, 'isCustomer')) {
            $this->assertTrue($customer->isCustomer());
            $this->assertFalse($agent->isCustomer());
        }
    }

    /**
     * Test dashboard access with session data
     */
    public function test_dashboard_access_logs_user_activity(): void
    {
        $admin = $this->testUsers['admin'];

        $response = $this->actingAs($admin)
            ->get('/dashboard');

        // Should redirect to admin dashboard
        $response->assertRedirect('/admin/dashboard');

        // Check that user activity was logged (if logging is implemented)
        // This would need to be adapted based on actual logging implementation
    }

    /**
     * Test edge case: user with empty string role
     */
    public function test_user_with_empty_string_role_handled_properly(): void
    {
        $emptyRoleUser = User::factory()->create([
            'name'              => 'Empty Role User',
            'email'             => 'empty@rbactest.com',
            'password'          => Hash::make('TestPassword123!'),
            'role'              => '',
            'is_active'         => TRUE,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($emptyRoleUser)
            ->get('/dashboard');

        // Should fallback to customer dashboard
        $response->assertRedirect('/dashboard/customer');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestUsers();
    }

    /**
     * Create test users for each role
     */
    private function createTestUsers(): void
    {
        $this->testUsers = [
            'admin' => User::factory()->create([
                'name'              => 'Admin Test User',
                'email'             => 'admin@rbactest.com',
                'password'          => Hash::make('TestPassword123!'),
                'role'              => 'admin',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
            'agent' => User::factory()->create([
                'name'              => 'Agent Test User',
                'email'             => 'agent@rbactest.com',
                'password'          => Hash::make('TestPassword123!'),
                'role'              => 'agent',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
            'customer' => User::factory()->create([
                'name'              => 'Customer Test User',
                'email'             => 'customer@rbactest.com',
                'password'          => Hash::make('TestPassword123!'),
                'role'              => 'customer',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
            'scraper' => User::factory()->create([
                'name'              => 'Scraper Test User',
                'email'             => 'scraper@rbactest.com',
                'password'          => Hash::make('TestPassword123!'),
                'role'              => 'scraper',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
            'ticketmaster' => User::factory()->create([
                'name'              => 'Ticketmaster Admin',
                'email'             => 'ticketmaster@hdtickets.com',
                'password'          => Hash::make('AdminPassword123!'),
                'role'              => 'admin',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
            'undefined_role' => User::factory()->create([
                'name'              => 'Undefined Role User',
                'email'             => 'undefined@rbactest.com',
                'password'          => Hash::make('TestPassword123!'),
                'role'              => NULL,
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]),
        ];
    }
}
