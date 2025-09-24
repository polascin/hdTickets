<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    public function __construct(
        protected TicketStatsService $ticketStatsService
    ) {
    }

    /**
     * Build analytics payload from statistics.
     *
     * @return array<string, mixed>
     */
    public function buildAnalytics(User $user): array
    {
        try {
            $stats = $this->ticketStatsService->getDashboardStats();

            return [
              'generated_at' => now()->toISOString(),
              'totals'       => [
                'available_tickets' => $stats['available_tickets'] ?? 0,
                'unique_events'     => $stats['monitored_events'] ?? ($stats['unique_events'] ?? 0),
              ],
              'trends' => [
                'demand' => [
                  'high_demand'       => $stats['high_demand_count'] ?? 0,
                  'demand_percentage' => isset($stats['available_tickets']) && ($stats['available_tickets'] > 0)
                    ? round(($stats['high_demand_count'] ?? 0) / max(1, $stats['available_tickets']) * 100, 2)
                    : 0,
                ],
                'pricing' => $stats['price_stats'] ?? [],
              ],
              'platforms' => $stats['platform_breakdown'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('AnalyticsService build failed', [
              'user_id' => $user->id ?? NULL,
              'error'   => $e->getMessage(),
            ]);

            return [
              'generated_at' => now()->toISOString(),
              'totals'       => [
                'available_tickets' => 0,
                'unique_events'     => 0,
              ],
              'trends' => [
                'demand' => [
                  'high_demand'       => 0,
                  'demand_percentage' => 0,
                ],
                'pricing' => [],
              ],
              'platforms' => [],
            ];
        }
    }
}
