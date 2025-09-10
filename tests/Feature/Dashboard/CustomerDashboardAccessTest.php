<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

class CustomerDashboardAccessTest extends TestCase
{
    /** @test */
    public function customer_dashboard_route_access_matrix()
    {
        $this->markTestIncomplete('Assert: customers/admins 200 OK, scraper 403, guest redirect to login.');
    }
}

