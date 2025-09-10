<?php declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerDashboardApiTest extends TestCase
{
    /**
     */
    #[Test]
    public function tiles_and_lists_payload_shapes_are_consistent(): void
    {
        $this->markTestIncomplete('Hit API endpoints for stats, tickets, recommendations, alerts; validate JSON shape and rate limits.');
    }
}
