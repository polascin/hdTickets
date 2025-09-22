<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use function array_slice;

class AgentDashboardController extends Controller
{
    /**
     * Display the agent dashboard for sports events tickets
     */
    public function index(): View
    {
        $user = Auth::user();

        // Check if user has agent privileges or is admin
        if (! $user->isAgent() && ! $user->isAdmin()) {
            abort(403, 'Access denied. Agent privileges required.');
        }

        // Get agent-specific metrics for sports events tickets
        $agentMetrics = $this->getAgentMetrics($user);

        // Get ticket monitoring data
        $ticketData = $this->getTicketMonitoringData($user);

        // Get purchase queue data
        $purchaseData = $this->getPurchaseQueueData($user);

        // Get alert data
        $alertData = $this->getAlertData($user);

        // Get recent activity
        $recentActivity = $this->getRecentActivity($user);

        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($user);

        return view('dashboard.agent', ['user' => $user, 'agentMetrics' => $agentMetrics, 'ticketData' => $ticketData, 'purchaseData' => $purchaseData, 'alertData' => $alertData, 'recentActivity' => $recentActivity, 'performanceMetrics' => $performanceMetrics]);
    }

    /**
     * Get agent-specific metrics for sports events tickets
     */
    private function getAgentMetrics(User $user): array
    {
        try {
            return [
                'tickets_monitored'          => $this->getTicketsMonitoredCount(),
                'active_alerts'              => $this->getActiveAlertsCount(),
                'successful_purchases_today' => $this->getSuccessfulPurchasesToday(),
                'pending_purchase_decisions' => $this->getPendingPurchaseDecisions(),
                'price_drops_detected'       => $this->getPriceDropsDetected(),
                'high_demand_events'         => $this->getHighDemandEvents(),
                'average_response_time'      => $this->getAverageResponseTime(),
                'success_rate'               => $this->getAgentSuccessRate(),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch agent metrics: ' . $e->getMessage());

            return $this->getDefaultAgentMetrics();
        }
    }

    /**
     * Get ticket monitoring data for sports events
     */
    private function getTicketMonitoringData(User $user): array
    {
        try {
            return [
                'active_monitors'  => $this->getActiveMonitors(),
                'trending_events'  => $this->getTrendingEvents(),
                'best_deals'       => $this->getBestDeals(),
                'new_availability' => $this->getNewAvailability(),
                'platform_status'  => $this->getPlatformStatus(),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch ticket monitoring data: ' . $e->getMessage());

            return $this->getDefaultTicketData();
        }
    }

    /**
     * Get purchase queue data
     */
    private function getPurchaseQueueData(User $user): array
    {
        try {
            return [
                'pending_purchases'        => $this->getPendingPurchases(),
                'recent_purchases'         => $this->getRecentPurchases(),
                'queue_statistics'         => $this->getQueueStatistics(),
                'purchase_recommendations' => $this->getPurchaseRecommendations(),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch purchase queue data: ' . $e->getMessage());

            return $this->getDefaultPurchaseData();
        }
    }

    /**
     * Get alert data for the agent
     */
    private function getAlertData(User $user): array
    {
        try {
            return [
                'active_alerts'      => $this->getUserActiveAlerts(),
                'triggered_today'    => $this->getTriggeredAlertsToday(),
                'alert_performance'  => $this->getAlertPerformance(),
                'recommended_alerts' => $this->getRecommendedAlerts(),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch alert data: ' . $e->getMessage());

            return $this->getDefaultAlertData();
        }
    }

    /**
     * Get recent activity for the agent
     */
    private function getRecentActivity(User $user): array
    {
        $activities = [];

        try {
            // Recent purchases made by the agent
            if (Schema::hasTable('purchase_queues')) {
                $recentPurchases = DB::table('purchase_queues')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                foreach ($recentPurchases as $purchase) {
                    $activities[] = [
                        'type'        => 'purchase',
                        'description' => "Purchase attempt for {$purchase->event_name}",
                        'timestamp'   => $purchase->created_at,
                        'status'      => $purchase->status,
                    ];
                }
            }

            // Recent alerts triggered
            if (Schema::hasTable('ticket_alerts')) {
                $recentAlerts = DB::table('ticket_alerts')
                    ->where('user_id', $user->id)
                    ->where('triggered_at', '>=', Carbon::now()->subDays(7))
                    ->orderBy('triggered_at', 'desc')
                    ->limit(10)
                    ->get();

                foreach ($recentAlerts as $alert) {
                    $activities[] = [
                        'type'        => 'alert',
                        'description' => "Alert triggered: {$alert->event_name} - Price dropped to {$alert->target_price}",
                        'timestamp'   => $alert->triggered_at,
                        'status'      => 'triggered',
                    ];
                }
            }

            // Sort by timestamp and limit to recent 20
            usort($activities, fn (array $a, array $b): int => strtotime((string) $b['timestamp']) - strtotime((string) $a['timestamp']));

            return array_slice($activities, 0, 20);
        } catch (Exception $e) {
            Log::warning('Could not fetch recent activity: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get performance metrics for the agent
     */
    private function getPerformanceMetrics(User $user): array
    {
        try {
            return [
                'success_rate'          => $this->getAgentSuccessRate(),
                'average_response_time' => $this->getAverageResponseTime(),
                'total_purchases'       => $this->getTotalPurchases(),
                'money_saved'           => $this->getMoneySpotted(),
                'alerts_effectiveness'  => $this->getAlertsEffectiveness(),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch performance metrics: ' . $e->getMessage());

            return $this->getDefaultPerformanceMetrics();
        }
    }

    // Helper methods with default implementations
    private function getTicketsMonitoredCount(): int
    {
        return random_int(10, 50);
    }

    private function getActiveAlertsCount(): int
    {
        return random_int(5, 25);
    }

    private function getSuccessfulPurchasesToday(): int
    {
        return random_int(0, 5);
    }

    private function getPendingPurchaseDecisions(): int
    {
        return random_int(0, 10);
    }

    private function getPriceDropsDetected(): int
    {
        return random_int(0, 15);
    }

    private function getHighDemandEvents(): array
    {
        return [
            ['name' => 'NBA Finals Game 7', 'demand_score' => 95],
            ['name' => 'Super Bowl LVIII', 'demand_score' => 98],
            ['name' => 'World Series Game 6', 'demand_score' => 87],
        ];
    }

    private function getAverageResponseTime(): float
    {
        return round(random_int(100, 500) / 100, 2);
    }

    private function getAgentSuccessRate(): float
    {
        return round(random_int(75, 95), 1);
    }

    private function getDefaultAgentMetrics(): array
    {
        return [
            'tickets_monitored'          => 0,
            'active_alerts'              => 0,
            'successful_purchases_today' => 0,
            'pending_purchase_decisions' => 0,
            'price_drops_detected'       => 0,
            'high_demand_events'         => [],
            'average_response_time'      => 0.0,
            'success_rate'               => 0.0,
        ];
    }

    private function getActiveMonitors(): array
    {
        return [];
    }

    private function getTrendingEvents(): array
    {
        return [];
    }

    private function getBestDeals(): array
    {
        return [];
    }

    private function getNewAvailability(): array
    {
        return [];
    }

    private function getPlatformStatus(): array
    {
        return [];
    }

    private function getDefaultTicketData(): array
    {
        return [
            'active_monitors'  => [],
            'trending_events'  => [],
            'best_deals'       => [],
            'new_availability' => [],
            'platform_status'  => [],
        ];
    }

    private function getPendingPurchases(): array
    {
        return [];
    }

    private function getRecentPurchases(): array
    {
        return [];
    }

    private function getQueueStatistics(): array
    {
        return [];
    }

    private function getPurchaseRecommendations(): array
    {
        return [];
    }

    private function getDefaultPurchaseData(): array
    {
        return [
            'pending_purchases'        => [],
            'recent_purchases'         => [],
            'queue_statistics'         => [],
            'purchase_recommendations' => [],
        ];
    }

    private function getUserActiveAlerts(): array
    {
        return [];
    }

    private function getTriggeredAlertsToday(): array
    {
        return [];
    }

    private function getAlertPerformance(): array
    {
        return [];
    }

    private function getRecommendedAlerts(): array
    {
        return [];
    }

    private function getDefaultAlertData(): array
    {
        return [
            'active_alerts'      => [],
            'triggered_today'    => [],
            'alert_performance'  => [],
            'recommended_alerts' => [],
        ];
    }

    private function getTotalPurchases(): int
    {
        return random_int(0, 100);
    }

    private function getMoneySpotted(): float
    {
        return round(random_int(100, 5000), 2);
    }

    private function getAlertsEffectiveness(): float
    {
        return round(random_int(60, 90), 1);
    }

    private function getDefaultPerformanceMetrics(): array
    {
        return [
            'success_rate'          => 0.0,
            'average_response_time' => 0.0,
            'total_purchases'       => 0,
            'money_saved'           => 0.0,
            'alerts_effectiveness'  => 0.0,
        ];
    }
}
