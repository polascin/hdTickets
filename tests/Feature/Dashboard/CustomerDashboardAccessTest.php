<?php declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_is_redirected_to_login_when_accessing_customer_dashboard(): void
    {
        $this->get('/dashboard/customer')->assertRedirect('/login');
    }

    #[Test]
    public function customer_can_access_customer_dashboard(): void
    {
        $customer = $this->makeUser('customer');
        $this->actingAs($customer)
            ->get('/dashboard/customer')
            ->assertOk()
            ->assertSee('Customer Dashboard');
    }

    #[Test]
    public function admin_can_access_customer_dashboard(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)
            ->get('/dashboard/customer')
            ->assertOk();
    }

    #[Test]
    public function scraper_gets_403_on_customer_dashboard(): void
    {
        $scraper = $this->makeUser('scraper');
        $this->actingAs($scraper)
            ->get('/dashboard/customer')
            ->assertForbidden();
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }
}
