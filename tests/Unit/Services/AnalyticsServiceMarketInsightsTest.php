<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use PHPUnit\Framework\Attributes\Test;
class AnalyticsServiceMarketInsightsTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function market_insights_returns_expected_contract(): void
    {
        // Arrange: create a verified customer user
        $user = User::factory()->create([
            'role'              => User::ROLE_CUSTOMER,
            'email_verified_at' => now(),
        ]);

        $service = app(AnalyticsService::class);

        // Act
        $insights = $service->getMarketInsights($user);

        // Assert top-level keys
        $expectedKeys = [
            'price_trends',
            'platform_performance',
            'demand_analysis',
            'popular_categories',
            'seasonal_trends',
            'recommendation_score',
            'market_summary',
            'user_positioning',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $insights, "Missing key: {$key}");
        }

        // Assert types (allow empty arrays but types must match)
        $this->assertIsArray($insights['price_trends']);
        $this->assertIsArray($insights['platform_performance']);
        $this->assertIsArray($insights['demand_analysis']);
        $this->assertIsArray($insights['popular_categories']);
        $this->assertIsArray($insights['seasonal_trends']);
        $this->assertIsNumeric($insights['recommendation_score']);
        $this->assertIsString($insights['market_summary']);
        $this->assertIsString($insights['user_positioning']);
    }
}
