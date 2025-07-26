<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminDashboardIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'is_active' => true
        ]);
    }

    public function test_admin_can_access_dashboard()
    {
$response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
$response->assertViewIs('dashboard.admin');
$response->assertSee('Sports Ticket Management Dashboard');
    }

    public function test_dashboard_displays_user_statistics_cards()
    {
        // Create test users with different roles
        User::factory()->count(5)->create(['role' => User::ROLE_AGENT]);
        User::factory()->count(3)->create(['role' => User::ROLE_CUSTOMER]);
        User::factory()->count(2)->create(['role' => User::ROLE_SCRAPER]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Platform Users');
        $response->assertSee('Active Monitors');
        $response->assertSee('User Role Distribution');
    }

    public function test_dashboard_displays_role_distribution_chart()
    {
        // Create users with different roles
        User::factory()->count(3)->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        User::factory()->count(5)->create(['role' => User::ROLE_AGENT, 'is_active' => true]);
        User::factory()->count(8)->create(['role' => User::ROLE_CUSTOMER, 'is_active' => true]);
        User::factory()->count(2)->create(['role' => User::ROLE_SCRAPER, 'is_active' => true]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('userStats');
        
        $userStats = $response->viewData('userStats');
        $this->assertArrayHasKey('by_role', $userStats);
        $this->assertEquals(4, $userStats['by_role']['admin']); // Including the test admin
        $this->assertEquals(5, $userStats['by_role']['agent']);
        $this->assertEquals(8, $userStats['by_role']['customer']);
        $this->assertEquals(2, $userStats['by_role']['scraper']);
    }

    public function test_dashboard_displays_recent_activity_feed()
    {
        // Create some test tickets for recent activity
        $category = Category::factory()->create(['name' => 'Sports Events']);
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'is_active' => true]);
        
        Ticket::factory()->count(3)->create([
            'requester_id' => $user->id,
            'assignee_id' => $this->adminUser->id,
            'category_id' => $category->id,
            'title' => 'Test Ticket',
            'created_at' => now()->subMinutes(10)
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Activity');
        $response->assertViewHas('recentActivity');
    }

    public function test_dashboard_displays_quick_actions_panel()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('quickActions');
        
        $quickActions = $response->viewData('quickActions');
        $this->assertIsArray($quickActions);
        $this->assertNotEmpty($quickActions);
        
        // Check for specific quick actions
        $actionTitles = collect($quickActions)->pluck('title')->toArray();
        $this->assertContains('Create New User', $actionTitles);
        $this->assertContains('System Health Check', $actionTitles);
        $this->assertContains('View Reports', $actionTitles);
    }

    public function test_role_distribution_chart_api_endpoint()
    {
        // Create users with different roles
        User::factory()->count(2)->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(4)->create(['role' => User::ROLE_AGENT]);
        User::factory()->count(6)->create(['role' => User::ROLE_CUSTOMER]);
        User::factory()->count(1)->create(['role' => User::ROLE_SCRAPER]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/chart/role-distribution.json');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'label',
                'value',
                'color'
            ]
        ]);

        $data = $response->json();
        $this->assertCount(4, $data); // admin, agent, customer, scraper roles
    }

    public function test_recent_activity_api_endpoint()
    {
        // Create some test activity
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'is_active' => true]);
        $category = Category::factory()->create();
        
        Ticket::factory()->create([
            'requester_id' => $user->id,
            'assignee_id' => $this->adminUser->id,
            'category_id' => $category->id,
            'title' => 'Test Activity Ticket',
            'created_at' => now()->subMinutes(5)
        ]);

$response = $this->actingAs($this->adminUser)
            ->get('/admin/activity/recent.json');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'type',
                'title',
                'description',
                'user',
                'timestamp',
                'status',
                'icon',
                'color'
            ]
        ]);
    }

    public function test_dashboard_shows_system_metrics()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('systemMetrics');
        
        $systemMetrics = $response->viewData('systemMetrics');
        $this->assertArrayHasKey('database_health', $systemMetrics);
        $this->assertArrayHasKey('cache_hit_rate', $systemMetrics);
        $this->assertArrayHasKey('response_time', $systemMetrics);
        $this->assertArrayHasKey('uptime', $systemMetrics);
        $this->assertArrayHasKey('active_sessions', $systemMetrics);
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $agentUser = User::factory()->create(['role' => User::ROLE_AGENT, 'is_active' => true]);
        $customerUser = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'is_active' => true]);

        // Test agent user
        $response = $this->actingAs($agentUser)
            ->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Test customer user
        $response = $this->actingAs($customerUser)
            ->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_dashboard_quick_actions_respect_permissions()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        $quickActions = $response->viewData('quickActions');
        
        // All quick actions should be available for admin
        foreach ($quickActions as $action) {
            if (isset($action['permission'])) {
                $this->assertTrue(
                    $this->adminUser->{$action['permission']}(),
                    "Admin should have permission: {$action['permission']}"
                );
            }
        }
    }

    public function test_dashboard_statistics_are_accurate()
    {
        // Create test data
        $agents = User::factory()->count(3)->create(['role' => User::ROLE_AGENT, 'is_active' => true]);
        $customers = User::factory()->count(5)->create(['role' => User::ROLE_CUSTOMER, 'is_active' => true]);
        $scrapers = User::factory()->count(2)->create(['role' => User::ROLE_SCRAPER, 'is_active' => true]);
        
        $category = Category::factory()->create();
        $tickets = Ticket::factory()->count(7)->create([
            'requester_id' => $customers->first()->id,
            'assignee_id' => $agents->first()->id,
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Check user statistics
        $userStats = $response->viewData('userStats');
        $this->assertEquals(11, $userStats['total']); // 1 admin + 3 agents + 5 customers + 2 scrapers + test admin
        $this->assertEquals(3, $userStats['by_role']['agent']);
        $this->assertEquals(5, $userStats['by_role']['customer']);
        $this->assertEquals(2, $userStats['by_role']['scraper']);
        
        // Check ticket statistics
        $totalTickets = $response->viewData('totalTickets');
        $this->assertEquals(7, $totalTickets);
    }
}
