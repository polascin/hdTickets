<?php declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    #[Test]
    public function guest_gets_401_on_realtime_endpoint(): void
    {
        $this->getJson('/api/v1/dashboard/realtime')->assertStatus(401);
    }

    #[Test]
    public function customer_gets_realtime_payload(): void
    {
        $customer = $this->makeUser('customer');
        Sanctum::actingAs($customer);

        $res = $this->getJson('/api/v1/dashboard/realtime')->assertOk()->json();
        $this->assertTrue($res['success'] ?? false);
        $this->assertArrayHasKey('data', $res);
        $this->assertArrayHasKey('statistics', $res['data']);
        $this->assertArrayHasKey('recent_tickets', $res['data']);
    }

    #[Test]
    public function admin_gets_realtime_payload(): void
    {
        $admin = $this->makeUser('admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/dashboard/realtime')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    #[Test]
    public function scraper_gets_403_on_realtime_endpoint(): void
    {
        $scraper = $this->makeUser('scraper');
        Sanctum::actingAs($scraper);

        // Depending on role middleware application on the api route
        $this->getJson('/api/v1/dashboard/realtime')->assertStatus(403);
    }
}
