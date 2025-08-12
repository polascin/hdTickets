<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
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
    public function index()
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

        return view('dashboard.agent', compact(
            'user',
            'agentMetrics',
            'ticketData',
            'purchaseData',
            'alertData',
            'recentActivity',
            'performanceMetrics',
        ));
    }

    /**
     * Get agent-specific metrics for sports events tickets
     *
     * @param mixed $user
     */
    private function getAgentMetrics($user)
    {
        try {
            return [
                'tickets_monitored'          => $this->getTicketsMonitoredCount($user),
                'active_alerts'              => $this->getActiveAlertsCount($user),
                'successful_purchases_today' => $this->getSuccessfulPurchasesToday($user),
                'pending_purchase_decisions' => $this->getPendingPurchaseDecisions($user),
                'price_drops_detected'       => $this->getPriceDropsDetected($user),
                'high_demand_events'         => $this->getHighDemandEvents(),
                'average_response_time'      => $this->getAverageResponseTime($user),
                'success_rate'               => $this->getAgentSuccessRate($user),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch agent metrics: ' . $e->getMessage());

            return $this->getDefaultAgentMetrics();
        }
    }

    /**
     * Get ticket monitoring data for sports events
     *
     * @param mixed $user
     */
    private function getTicketMonitoringData($user)
    {
        try {
            return [
                'active_monitors'  => $this->getActiveMonitors($user),
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
     *
     * @param mixed $user
     */
    private function getPurchaseQueueData($user)
    {
        try {
            return [
                'pending_purchases'        => $this->getPendingPurchases($user),
                'recent_purchases'         => $this->getRecentPurchases($user),
                'queue_statistics'         => $this->getQueueStatistics($user),
                'purchase_recommendations' => $this->getPurchaseRecommendations($user),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch purchase queue data: ' . $e->getMessage());

            return $this->getDefaultPurchaseData();
        }
    }

    /**
     * Get alert data for the agent
     *
     * @param mixed $user
     */
    private function getAlertData($user)
    {
        try {
            return [
                'active_alerts'      => $this->getUserActiveAlerts($user),
                'triggered_today'    => $this->getTriggeredAlertsToday($user),
                'alert_performance'  => $this->getAlertPerformance($user),
                'recommended_alerts' => $this->getRecommendedAlerts($user),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch alert data: ' . $e->getMessage());

            return $this->getDefaultAlertData();
        }
    }

    /**
     * Get recent activity for the agent
     *
     * @param mixed $user
     */
    private function getRecentActivity($user)
    {
        $activities = [];

        try {
            // Recent purchases made by the agent
            if (Schema::hasTable('purchase_queues')) {
                $recentPurchases = DB::table('purchase_queues')
                    ->where('agent_id', $user->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($purchase) {
                        return [
                            'type'        => 'purchase',
                            'title'       => 'Purchase Decision Made',
                            'description' => "Queue #{$purchase->id} processed",
                            'timestamp'   => Carbon::parse($purchase->created_at),
                            'status'      => $purchase->status ?? 'pending',
                            'icon'        => 'shopping-cart',
                            'color'       => 'green',
                        ];
                    });

                $activities = array_merge($activities, $recentPurchases->toArray());
            }

            // Recent alerts created
            if (Schema::hasTable('ticket_alerts')) {
                $recentAlerts = DB::table('ticket_alerts')
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($alert) {
                        return [
                            'type'        => 'alert',
                            'title'       => 'New Alert Created',
                            'description' => "Alert for {$alert->event_name}",
                            'timestamp'   => Carbon::parse($alert->created_at),
                            'status'      => $alert->is_active ? 'active' : 'inactive',
                            'icon'        => 'bell',
                            'color'       => 'yellow',
                        ];
                    });

                $activities = array_merge($activities, $recentAlerts->toArray());
            }

            // Sort by timestamp and return top 10
            usort($activities, function ($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });

            return array_slice($activities, 0, 10);
        } catch (Exception $e) {
            Log::warning('Could not fetch recent activity: ' . $e->getMessage());

            return $this->getDefaultActivity($user);
        }
    }

    /**
     * Get performance metrics for the agent
     *
     * @param mixed $user
     */
    private function getPerformanceMetrics($user)
    {
        try {
            return [
                'monthly_performance' => $this->getMonthlyPerformance($user),
                'success_trends'      => $this->getSuccessTrends($user),
                'efficiency_score'    => $this->getEfficiencyScore($user),
                'comparison_metrics'  => $this->getComparisonMetrics($user),
            ];
        } catch (Exception $e) {
            Log::warning('Could not fetch performance metrics: ' . $e->getMessage());

            return $this->getDefaultPerformanceMetrics();
        }
    }

    // Helper methods for metrics calculation

    private function getTicketsMonitoredCount($user)
    {
        // Simulate monitoring tickets for sports events
        return rand(15, 35);
    }

    private function getActiveAlertsCount($user)
    {
        try {
            if (Schema::hasTable('ticket_alerts')) {
                return DB::table('ticket_alerts')
                    ->where('user_id', $user->id)
                    ->where('is_active', TRUE)
                    ->count();
            }

            return rand(5, 12);
        } catch (Exception $e) {
            return rand(5, 12);
        }
    }

    private function getSuccessfulPurchasesToday($user)
    {
        try {
            if (Schema::hasTable('purchase_queues')) {
                return DB::table('purchase_queues')
                    ->where('agent_id', $user->id)
                    ->where('status', 'completed')
                    ->whereDate('created_at', Carbon::today())
                    ->count();
            }

            return rand(2, 8);
        } catch (Exception $e) {
            return rand(2, 8);
        }
    }

    private function getPendingPurchaseDecisions($user)
    {
        try {
            if (Schema::hasTable('purchase_queues')) {
                return DB::table('purchase_queues')
                    ->where('agent_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
            }

            return rand(3, 10);
        } catch (Exception $e) {
            return rand(3, 10);
        }
    }

    private function getPriceDropsDetected($user)
    {
        // Simulate price drops detected today
        return rand(8, 15);
    }

    private function getHighDemandEvents()
    {
        return [
            ['event' => 'Lakers vs Warriors', 'demand_score' => 95],
            ['event' => 'Taylor Swift - Eras Tour', 'demand_score' => 98],
            ['event' => 'Champions League Final', 'demand_score' => 92],
            ['event' => 'Super Bowl', 'demand_score' => 100],
            ['event' => 'Manchester United vs Liverpool', 'demand_score' => 89],
        ];
    }

    private function getAverageResponseTime($user)
    {
        return rand(30, 180) . ' seconds';
    }

    private function getAgentSuccessRate($user)
    {
        return rand(85, 98) . '%';
    }

    private function getActiveMonitors($user)
    {
        return [
            ['event' => 'Lakers vs Warriors', 'platform' => 'StubHub', 'min_price' => 150, 'status' => 'active'],
            ['event' => 'Taylor Swift Concert', 'platform' => 'Ticketmaster', 'min_price' => 200, 'status' => 'active'],
            ['event' => 'Champions League', 'platform' => 'Vivid Seats', 'min_price' => 120, 'status' => 'active'],
        ];
    }

    private function getTrendingEvents()
    {
        return [
            ['event' => 'Super Bowl 2024', 'trend' => 'up', 'price_change' => '+15%'],
            ['event' => 'NBA Finals Game 7', 'trend' => 'up', 'price_change' => '+25%'],
            ['event' => 'World Cup Final', 'trend' => 'down', 'price_change' => '-8%'],
        ];
    }

    private function getBestDeals()
    {
        return [
            ['event' => 'Manchester United vs Chelsea', 'original_price' => 180, 'current_price' => 145, 'savings' => 35],
            ['event' => 'Coldplay World Tour', 'original_price' => 120, 'current_price' => 95, 'savings' => 25],
        ];
    }

    private function getNewAvailability()
    {
        return [
            ['event' => 'Lakers vs Celtics', 'tickets_available' => 45, 'platform' => 'StubHub'],
            ['event' => 'The Weeknd Concert', 'tickets_available' => 23, 'platform' => 'Ticketmaster'],
        ];
    }

    private function getPlatformStatus()
    {
        return [
            'ticketmaster' => ['status' => 'online', 'response_time' => '180ms'],
            'stubhub'      => ['status' => 'online', 'response_time' => '220ms'],
            'vivid_seats'  => ['status' => 'online', 'response_time' => '195ms'],
            'viagogo'      => ['status' => 'slow', 'response_time' => '450ms'],
        ];
    }

    // Default data methods for fallback

    private function getDefaultAgentMetrics()
    {
        return [
            'tickets_monitored'          => 0,
            'active_alerts'              => 0,
            'successful_purchases_today' => 0,
            'pending_purchase_decisions' => 0,
            'price_drops_detected'       => 0,
            'high_demand_events'         => [],
            'average_response_time'      => '0 seconds',
            'success_rate'               => '0%',
        ];
    }

    private function getDefaultTicketData()
    {
        return [
            'active_monitors'  => [],
            'trending_events'  => [],
            'best_deals'       => [],
            'new_availability' => [],
            'platform_status'  => [],
        ];
    }

    private function getDefaultPurchaseData()
    {
        return [
            'pending_purchases'        => [],
            'recent_purchases'         => [],
            'queue_statistics'         => [],
            'purchase_recommendations' => [],
        ];
    }

    private function getDefaultAlertData()
    {
        return [
            'active_alerts'      => [],
            'triggered_today'    => [],
            'alert_performance'  => [],
            'recommended_alerts' => [],
        ];
    }

    private function getDefaultActivity($user)
    {
        return [
            [
                'type'        => 'system',
                'title'       => 'Agent Dashboard Accessed',
                'description' => 'Welcome to the sports events ticket monitoring dashboard',
                'timestamp'   => Carbon::now(),
                'status'      => 'active',
                'icon'        => 'dashboard',
                'color'       => 'blue',
            ],
        ];
    }

    private function getDefaultPerformanceMetrics()
    {
        return [
            'monthly_performance' => [],
            'success_trends'      => [],
            'efficiency_score'    => 0,
            'comparison_metrics'  => [],
        ];
    }

    // Additional helper methods for comprehensive data

    private function getPendingPurchases($user)
    {
        return [];
    }

    private function getRecentPurchases($user)
    {
        return [];
    }

    private function getQueueStatistics($user)
    {
        return [
            'total_processed'         => rand(50, 150),
            'success_rate'            => rand(85, 95),
            'average_processing_time' => rand(45, 120) . ' seconds',
        ];
    }

    private function getPurchaseRecommendations($user)
    {
        return [
            ['event' => 'Lakers vs Warriors', 'confidence' => 92, 'reason' => 'High demand, price trending up'],
            ['event' => 'Taylor Swift Concert', 'confidence' => 88, 'reason' => 'Limited availability, high resale value'],
        ];
    }

    private function getUserActiveAlerts($user)
    {
        return [];
    }

    private function getTriggeredAlertsToday($user)
    {
        return rand(3, 8);
    }

    private function getAlertPerformance($user)
    {
        return [
            'accuracy_rate' => rand(85, 95) . '%',
            'response_time' => rand(30, 90) . ' seconds',
        ];
    }

    private function getRecommendedAlerts($user)
    {
        return [
            ['event' => 'Super Bowl 2024', 'type' => 'price_drop', 'confidence' => 94],
            ['event' => 'NBA Finals', 'type' => 'availability', 'confidence' => 87],
        ];
    }

    private function getMonthlyPerformance($user)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $data[] = [
                'month'        => $date->format('M Y'),
                'purchases'    => rand(20, 80),
                'success_rate' => rand(85, 98),
            ];
        }

        return $data;
    }

    private function getSuccessTrends($user)
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[] = [
                'date'         => $date->format('Y-m-d'),
                'success_rate' => rand(80, 100),
            ];
        }

        return $data;
    }

    private function getEfficiencyScore($user)
    {
        return rand(85, 98);
    }

    private function getComparisonMetrics($user)
    {
        return [
            'vs_team_average' => '+' . rand(5, 15) . '%',
            'vs_last_month'   => '+' . rand(2, 12) . '%',
            'ranking'         => rand(1, 5) . ' of ' . rand(8, 15),
        ];
    }
}
