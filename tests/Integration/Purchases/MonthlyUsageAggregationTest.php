<?php

namespace Tests\Integration\Purchases;

use Tests\TestCase;

class MonthlyUsageAggregationTest extends TestCase
{
    /** @test */
    public function monthly_usage_includes_pending_and_confirmed_and_handles_boundaries()
    {
        $this->markTestIncomplete('Seed purchases across month/year boundaries; assert usage totals include pending+confirmed only.');
    }
}

