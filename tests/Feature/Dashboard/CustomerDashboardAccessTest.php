<?php declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDashboardAccessTest extends TestCase
{
    #[Test]
    public function customer_dashboard_route_access_matrix(): void
    {
        $this->markTestIncomplete('Assert: customers/admins 200 OK, scraper 403, guest redirect to login.');
    }
}
