<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\User;
use App\Services\AutomatedPurchaseEngine;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AutomatedPurchaseController extends Controller
{
    public function __construct(private AutomatedPurchaseEngine $purchaseEngine)
    {
        $this->middleware('auth:api');
    }

    /**
     * Evaluate purchase decision for a specific ticket
     */
    /**
     * EvaluatePurchaseDecision
     */
    public function evaluatePurchaseDecision(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:scraped_tickets,id',
            'user_id'   => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $ticket = ScrapedTicket::findOrFail($request->ticket_id);
            $user = $request->user_id ? User::findOrFail($request->user_id) : Auth::user();

            $decision = $this->purchaseEngine->evaluatePurchaseDecision($ticket, $user);

            return response()->json([
                'success' => TRUE,
                'data'    => $decision,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to evaluate purchase decision',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare prices across multiple platforms for an event
     */
    /**
     * CompareMultiPlatformPrices
     */
    public function compareMultiPlatformPrices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_title'  => 'required|string|min:3',
            'max_price'    => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'section'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $criteria = array_filter([
                'max_price'    => $request->max_price,
                'min_quantity' => $request->min_quantity,
                'section'      => $request->section,
            ]);

            $comparison = $this->purchaseEngine->compareMultiPlatformPrices(
                $request->event_title,
                $criteria,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => $comparison,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to compare platform prices',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute automated purchase
     */
    /**
     * ExecuteAutomatedPurchase
     */
    public function executeAutomatedPurchase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ticket_id'  => 'required|exists:scraped_tickets,id',
            'user_id'    => 'nullable|exists:users,id',
            'quantity'   => 'required|integer|min:1|max:8',
            'max_price'  => 'nullable|numeric|min:0',
            'priority'   => 'nullable|in:low,normal,high',
            'auto_retry' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Check if automated purchases are enabled
        if (!config('purchase_automation.enabled', FALSE)) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Automated purchases are currently disabled',
            ], 403);
        }

        try {
            $user = $request->user_id ? User::findOrFail($request->user_id) : Auth::user();

            // Check user's auto-purchase settings
            $userSettings = $user->preferences['auto_purchase'] ?? [];
            if (!($userSettings['enabled'] ?? FALSE)) {
                return response()->json([
                    'success' => FALSE,
                    'error'   => 'Automated purchases are disabled for this user',
                ], 403);
            }

            $purchaseRequest = [
                'ticket_id'  => $request->ticket_id,
                'user_id'    => $user->id,
                'quantity'   => $request->quantity,
                'max_price'  => $request->max_price,
                'priority'   => $request->priority ?? 'normal',
                'auto_retry' => $request->auto_retry ?? TRUE,
                'platform'   => ScrapedTicket::find($request->ticket_id)->platform,
            ];

            $result = $this->purchaseEngine->executeAutomatedPurchase($purchaseRequest);

            return response()->json([
                'success' => $result['success'],
                'data'    => $result,
            ], $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to execute automated purchase',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track and optimize purchase performance
     */
    /**
     * TrackAndOptimize
     */
    public function trackAndOptimize(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id'    => 'required|string',
            'platform'          => 'required|string',
            'success'           => 'required|boolean',
            'execution_time_ms' => 'required|integer',
            'final_price'       => 'nullable|numeric',
            'estimated_price'   => 'nullable|numeric',
            'user_id'           => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->purchaseEngine->trackAndOptimize($request->all());

            return response()->json([
                'success' => TRUE,
                'data'    => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to track and optimize',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get purchase automation configuration
     */
    /**
     * Get  configuration
     */
    public function getConfiguration(): JsonResponse
    {
        try {
            $config = config('purchase_automation');

            // Remove sensitive information
            unset($config['development'], $config['safety']['fraud_detection']);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'enabled'            => $config['enabled'],
                    'decision_algorithm' => $config['decision_algorithm'],
                    'platforms'          => array_keys($config['platforms']),
                    'checkout_settings'  => [
                        'max_concurrent_purchases' => $config['checkout']['max_concurrent_purchases'],
                        'purchase_timeout'         => $config['checkout']['purchase_timeout'],
                        'validation_rules'         => $config['checkout']['validation_rules'],
                    ],
                    'machine_learning' => [
                        'enabled' => $config['machine_learning']['enabled'],
                        'models'  => array_keys($config['machine_learning']['models']),
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to get configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user's automated purchase preferences
     */
    /**
     * UpdateUserPreferences
     */
    public function updateUserPreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'auto_purchase_enabled' => 'nullable|boolean',
            'min_score_threshold'   => 'nullable|integer|min:0|max:100',
            'max_price_per_ticket'  => 'nullable|numeric|min:0',
            'preferred_platforms'   => 'nullable|array',
            'preferred_platforms.*' => 'string',
            'preferred_sections'    => 'nullable|array',
            'preferred_sections.*'  => 'string',
            'max_daily_spend'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $preferences = $user->preferences ?? [];

            // Update auto-purchase preferences
            $preferences['auto_purchase'] = array_merge(
                $preferences['auto_purchase'] ?? [],
                array_filter([
                    'enabled'         => $request->auto_purchase_enabled,
                    'min_score'       => $request->min_score_threshold,
                    'max_price'       => $request->max_price_per_ticket,
                    'max_daily_spend' => $request->max_daily_spend,
                ]),
            );

            // Update general preferences
            if ($request->has('preferred_platforms')) {
                $preferences['preferred_platforms'] = $request->preferred_platforms;
            }

            if ($request->has('preferred_sections')) {
                $preferences['preferred_sections'] = $request->preferred_sections;
            }

            if ($request->has('max_price_per_ticket')) {
                $preferences['max_ticket_price'] = $request->max_price_per_ticket;
            }

            $user->update(['preferences' => $preferences]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Preferences updated successfully',
                'data'    => [
                    'auto_purchase'       => $preferences['auto_purchase'],
                    'preferred_platforms' => $preferences['preferred_platforms'] ?? [],
                    'preferred_sections'  => $preferences['preferred_sections'] ?? [],
                    'max_ticket_price'    => $preferences['max_ticket_price'] ?? NULL,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to update preferences',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get purchase automation statistics and insights
     */
    /**
     * Get  automation statistics
     */
    public function getAutomationStatistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period'   => 'nullable|in:24h,7d,30d,90d',
            'platform' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $period = $request->period ?? '30d';
            $platform = $request->platform;

            // Calculate date range
            $dateRange = match ($period) {
                '24h'   => now()->subDay(),
                '7d'    => now()->subWeek(),
                '30d'   => now()->subMonth(),
                '90d'   => now()->subMonths(3),
                default => now()->subMonth(),
            };

            // Get statistics from tracking tables
            $query = DB::table('purchase_tracking')
                ->where('created_at', '>=', $dateRange);

            if ($platform) {
                $query->where('platform', $platform);
            }

            $totalPurchases = $query->count();
            $successfulPurchases = $query->where('success', TRUE)->count();
            $successRate = $totalPurchases > 0 ? ($successfulPurchases / $totalPurchases) * 100 : 0;
            $averageExecutionTime = $query->avg('execution_time') ?? 0;
            $totalSpent = $query->where('success', TRUE)->sum('final_price') ?? 0;

            // Platform breakdown
            $platformStats = DB::table('purchase_tracking')
                ->select(
                    'platform',
                    DB::raw('COUNT(*) as total_attempts'),
                    DB::raw('SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful'),
                    DB::raw('AVG(execution_time) as avg_execution_time'),
                    DB::raw('SUM(CASE WHEN success = 1 THEN final_price ELSE 0 END) as total_spent'),
                )
                ->where('created_at', '>=', $dateRange)
                ->when($platform, fn ($query, $platform) => $query->where('platform', $platform))
                ->groupBy('platform')
                ->get()
                ->map(function ($stat) {
                    $stat->success_rate = $stat->total_attempts > 0 ?
                        ($stat->successful / $stat->total_attempts) * 100 : 0;

                    return $stat;
                });

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'period'  => $period,
                    'summary' => [
                        'total_purchases'        => $totalPurchases,
                        'successful_purchases'   => $successfulPurchases,
                        'success_rate'           => round($successRate, 2),
                        'average_execution_time' => round($averageExecutionTime, 2),
                        'total_spent'            => round($totalSpent, 2),
                    ],
                    'platform_breakdown' => $platformStats,
                    'recommendations'    => $this->generateStatisticsRecommendations([
                        'success_rate'       => $successRate,
                        'avg_execution_time' => $averageExecutionTime,
                        'platform_stats'     => $platformStats,
                    ]),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to get automation statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate recommendations based on statistics
     */
    /**
     * GenerateStatisticsRecommendations
     */
    private function generateStatisticsRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['success_rate'] < 75) {
            $recommendations[] = [
                'type'     => 'success_rate',
                'message'  => 'Success rate is below optimal. Consider adjusting decision thresholds.',
                'priority' => 'high',
            ];
        }

        if ($stats['avg_execution_time'] > 180000) { // 3 minutes
            $recommendations[] = [
                'type'     => 'performance',
                'message'  => 'Average execution time is high. Consider optimizing platform integrations.',
                'priority' => 'medium',
            ];
        }

        // Find best performing platform
        $bestPlatform = $stats['platform_stats']->sortByDesc('success_rate')->first();
        if ($bestPlatform && $bestPlatform->success_rate > 85) {
            $recommendations[] = [
                'type'     => 'platform_optimization',
                'message'  => "Consider prioritizing {$bestPlatform->platform} for better success rates.",
                'priority' => 'low',
            ];
        }

        return $recommendations;
    }
}
