<?php declare(strict_types=1);

namespace Tests\Integration\Purchases;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MonthlyUsageAggregationTest extends TestCase
{
    /**
     */
    #[Test]
    public function monthly_usage_includes_pending_and_confirmed_and_handles_boundaries(): void
    {
        $this->markTestIncomplete('Seed purchases across month/year boundaries; assert usage totals include pending+confirmed only.');
    }
}
