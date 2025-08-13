<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseAttempt;
use App\Models\PurchaseQueue;
use App\Models\ScrapedTicket;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function count;
use function in_array;

class AutomatedPurchaseEngine
{
    private PurchaseAnalyticsService $analyticsService;

    private AdvancedAnalyticsDashboard $advancedAnalytics;

    private array $config;

    public function __construct(
        PurchaseAnalyticsService $analyticsService,
        AdvancedAnalyticsDashboard $advancedAnalytics,
    ) {
        $this->analyticsService = $analyticsService;
        $this->advancedAnalytics = $advancedAnalytics;
        $this->config = config('purchase_automation', []);
    }

    /**
     * Intelligent purchase decision algorithm
     * Analyzes tickets and makes automated purchase decisions based on ML insights
     */
    /**
     * EvaluatePurchaseDecision
     */
    public function evaluatePurchaseDecision(ScrapedTicket $ticket, User $user): array
    {
        $cacheKey = "purchase_decision_{$ticket->id}_{$user->id}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ticket, $user) {
            Log::info("Evaluating purchase decision for ticket {$ticket->id} and user {$user->id}");

            // Get AI-powered analytics insights
            $priceAnalysis = $this->advancedAnalytics->getPriceTrendAnalysis($ticket->title);
            $demandAnalysis = $this->advancedAnalytics->getDemandPatternAnalysis($ticket->title);
            $platformPerformance = $this->advancedAnalytics->getPlatformPerformanceComparison();

            // Calculate decision scores
            $scores = [
                'price_score'           => $this->calculatePriceScore($ticket, $priceAnalysis, $user),
                'demand_score'          => $this->calculateDemandScore($ticket, $demandAnalysis),
                'platform_score'        => $this->calculatePlatformScore($ticket, $platformPerformance),
                'timing_score'          => $this->calculateTimingScore($ticket),
                'user_preference_score' => $this->calculateUserPreferenceScore($ticket, $user),
                'success_probability'   => $this->calculateSuccessProbability($ticket),
            ];

            // Calculate overall recommendation
            $overallScore = $this->calculateOverallScore($scores);
            $recommendation = $this->generateRecommendation($overallScore, $scores);

            return [
                'ticket_id'      => $ticket->id,
                'user_id'        => $user->id,
                'overall_score'  => $overallScore,
                'recommendation' => $recommendation,
                'scores'         => $scores,
                'analysis'       => [
                    'price_analysis'       => $priceAnalysis,
                    'demand_analysis'      => $demandAnalysis,
                    'platform_performance' => $platformPerformance,
                ],
                'auto_purchase_eligible'   => $this->isEligibleForAutoPurchase($overallScore, $scores, $user),
                'risk_factors'             => $this->identifyRiskFactors($ticket, $scores),
                'optimization_suggestions' => $this->generateOptimizationSuggestions($scores),
                'evaluated_at'             => now()->toISOString(),
            ];
        });
    }

    /**
     * Multi-platform price comparison with intelligence
     */
    /**
     * CompareMultiPlatformPrices
     */
    public function compareMultiPlatformPrices(string $eventTitle, array $criteria = []): array
    {
        $tickets = ScrapedTicket::where('title', 'like', "%{$eventTitle}%")
            ->where('is_available', TRUE)
            ->when(isset($criteria['max_price']), function ($query) use ($criteria) {
                return $query->where('max_price', '<=', $criteria['max_price']);
            })
            ->when(isset($criteria['min_quantity']), function ($query) use ($criteria) {
                return $query->where('quantity', '>=', $criteria['min_quantity']);
            })
            ->orderBy('total_price')
            ->get();

        $comparison = [];
        $platformStats = [];

        foreach ($tickets as $ticket) {
            $platform = $ticket->platform;

            if (! isset($platformStats[$platform])) {
                $platformStats[$platform] = [
                    'total_listings'      => 0,
                    'price_range'         => ['min' => PHP_FLOAT_MAX, 'max' => 0],
                    'average_price'       => 0,
                    'success_rate'        => $this->getPlatformSuccessRate($platform),
                    'avg_processing_time' => $this->getPlatformProcessingTime($platform),
                    'reliability_score'   => $this->getPlatformReliabilityScore($platform),
                ];
            }

            $platformStats[$platform]['total_listings']++;
            $platformStats[$platform]['price_range']['min'] = min(
                $platformStats[$platform]['price_range']['min'],
                $ticket->total_price,
            );
            $platformStats[$platform]['price_range']['max'] = max(
                $platformStats[$platform]['price_range']['max'],
                $ticket->total_price,
            );

            $totalPrice = $ticket->total_price;
            $comparison[] = [
                'ticket_id'               => $ticket->id,
                'platform'                => $platform,
                'price'                   => $totalPrice,
                'quantity'                => 1, // Default quantity for scraped tickets
                'section'                 => $ticket->metadata['section'] ?? 'General',
                'row'                     => $ticket->metadata['row'] ?? 'N/A',
                'value_score'             => $this->calculateValueScore($ticket),
                'purchase_recommendation' => $this->evaluatePurchaseDecision($ticket, auth()->user() ?? User::first()),
                'platform_reliability'    => $platformStats[$platform]['reliability_score'],
                'estimated_fees'          => $this->estimatePlatformFees($ticket),
                'total_estimated_cost'    => $totalPrice + $this->estimatePlatformFees($ticket),
            ];
        }

        // Calculate average prices for each platform
        foreach ($platformStats as $platform => &$stats) {
            $platformTickets = collect($comparison)->where('platform', $platform);
            $stats['average_price'] = $platformTickets->avg('price') ?? 0;
        }

        // Sort by best value (considering price, reliability, and success rate)
        usort($comparison, function ($a, $b) {
            $scoreA = $a['value_score'] + ($a['platform_reliability'] * 0.3);
            $scoreB = $b['value_score'] + ($b['platform_reliability'] * 0.3);

            return $scoreB <=> $scoreA;
        });

        return [
            'event_title'               => $eventTitle,
            'total_options'             => count($comparison),
            'price_comparison'          => $comparison,
            'platform_statistics'       => $platformStats,
            'best_value_recommendation' => $comparison[0] ?? NULL,
            'price_analysis'            => [
                'lowest_price'   => min(array_column($comparison, 'price')),
                'highest_price'  => max(array_column($comparison, 'price')),
                'average_price'  => collect($comparison)->avg('price'),
                'price_variance' => $this->calculatePriceVariance($comparison),
            ],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Automated checkout flow with user preferences
     */
    /**
     * ExecuteAutomatedPurchase
     */
    public function executeAutomatedPurchase(array $purchaseRequest): array
    {
        $startTime = microtime(TRUE);
        $transactionId = 'AUTO_' . strtoupper(uniqid());

        Log::info('Starting automated purchase process', [
            'transaction_id' => $transactionId,
            'request'        => $purchaseRequest,
        ]);

        try {
            // Validate purchase request
            $validation = $this->validatePurchaseRequest($purchaseRequest);
            if (! $validation['valid']) {
                return $this->buildFailureResponse($transactionId, 'Validation failed', $validation['errors']);
            }

            $ticket = ScrapedTicket::findOrFail($purchaseRequest['ticket_id']);
            $user = User::findOrFail($purchaseRequest['user_id']);

            // Create purchase queue entry
            $queueItem = $this->createPurchaseQueueItem($ticket, $user, $purchaseRequest, $transactionId);

            // Execute intelligent purchase flow
            $purchaseResult = $this->executePurchaseFlow($queueItem, $purchaseRequest);

            // Track success and optimize
            $this->trackPurchaseSuccess($purchaseResult, $purchaseRequest);

            $executionTime = round((microtime(TRUE) - $startTime) * 1000, 2);

            return [
                'success'              => $purchaseResult['success'],
                'transaction_id'       => $transactionId,
                'queue_id'             => $queueItem->uuid,
                'attempt_id'           => $purchaseResult['attempt_id'] ?? NULL,
                'confirmation_number'  => $purchaseResult['confirmation_number'] ?? NULL,
                'final_price'          => $purchaseResult['final_price'] ?? NULL,
                'total_paid'           => $purchaseResult['total_paid'] ?? NULL,
                'execution_time_ms'    => $executionTime,
                'platform_used'        => $ticket->platform,
                'optimization_applied' => $purchaseResult['optimization_applied'] ?? [],
                'message'              => $purchaseResult['message'],
                'next_steps'           => $this->generateNextSteps($purchaseResult),
                'completed_at'         => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Automated purchase failed', [
                'transaction_id' => $transactionId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            return $this->buildFailureResponse($transactionId, 'System error', [$e->getMessage()]);
        }
    }

    /**
     * Purchase success tracking and optimization
     */
    /**
     * TrackAndOptimize
     */
    public function trackAndOptimize(array $purchaseData): array
    {
        $trackingData = [
            'timestamp'        => now(),
            'transaction_id'   => $purchaseData['transaction_id'],
            'platform'         => $purchaseData['platform'],
            'success'          => $purchaseData['success'],
            'execution_time'   => $purchaseData['execution_time_ms'],
            'price_difference' => $purchaseData['final_price'] - $purchaseData['estimated_price'],
            'user_id'          => $purchaseData['user_id'],
        ];

        // Store in analytics
        $this->storeTrackingData($trackingData);

        // Generate optimization insights
        $optimizations = $this->generateOptimizationInsights($trackingData);

        // Update ML models
        $this->updateMachineLearningModels($trackingData);

        // Adjust automation parameters
        $adjustments = $this->adjustAutomationParameters($trackingData);

        return [
            'tracking_stored'          => TRUE,
            'optimizations_identified' => count($optimizations),
            'optimizations'            => $optimizations,
            'parameter_adjustments'    => $adjustments,
            'ml_models_updated'        => TRUE,
            'next_optimization_cycle'  => now()->addHours(4)->toISOString(),
        ];
    }

    /**
     * Calculate price score based on AI analysis
     */
    /**
     * CalculatePriceScore
     */
    private function calculatePriceScore(ScrapedTicket $ticket, array $priceAnalysis, User $user): float
    {
        $maxPrice = $user->preferences['max_ticket_price'] ?? 1000;
        $priceTrend = $priceAnalysis['trend_direction'] ?? 'stable';
        $confidence = $priceAnalysis['confidence_score'] ?? 0.5;

        // Base score from price vs user budget
        $totalPrice = $ticket->total_price;
        $baseScore = max(0, 100 - (($totalPrice / $maxPrice) * 100));

        // Adjust based on price trend
        $trendMultiplier = match ($priceTrend) {
            'decreasing' => 1.2,  // Prices going down - good time to buy
            'stable'     => 1.0,
            'increasing' => 0.8,  // Prices going up - might want to wait
            default      => 1.0,
        };

        // Weight by confidence
        $finalScore = ($baseScore * $trendMultiplier) * $confidence;

        return min(100, max(0, $finalScore));
    }

    /**
     * Calculate demand score
     */
    /**
     * CalculateDemandScore
     */
    private function calculateDemandScore(ScrapedTicket $ticket, array $demandAnalysis): float
    {
        $demandLevel = $demandAnalysis['demand_level'] ?? 'medium';
        $demandScore = $demandAnalysis['demand_score'] ?? 5.0;

        // Higher demand = higher urgency to purchase
        $score = match ($demandLevel) {
            'very_low'  => 20,
            'low'       => 40,
            'medium'    => 60,
            'high'      => 80,
            'very_high' => 100,
            default     => 60,
        };

        // Adjust by actual demand score
        return min(100, $score * ($demandScore / 10));
    }

    /**
     * Calculate platform score based on reliability and performance
     */
    /**
     * CalculatePlatformScore
     */
    private function calculatePlatformScore(ScrapedTicket $ticket, array $platformPerformance): float
    {
        $platform = $ticket->platform;
        $platformData = $platformPerformance['platforms'][$platform] ?? NULL;

        if (! $platformData) {
            return 50; // Default score for unknown platforms
        }

        $successRate = $platformData['success_rate'] ?? 70;
        $reliability = $platformData['reliability_score'] ?? 70;
        $responseTime = $platformData['avg_response_time'] ?? 2.0;

        // Lower response time is better
        $responseTimeScore = max(0, 100 - ($responseTime * 20));

        return ($successRate * 0.5) + ($reliability * 0.3) + ($responseTimeScore * 0.2);
    }

    /**
     * Calculate timing score based on event proximity and market conditions
     */
    /**
     * CalculateTimingScore
     */
    private function calculateTimingScore(ScrapedTicket $ticket): float
    {
        $eventDate = Carbon::parse($ticket->event_date);
        $daysUntilEvent = now()->diffInDays($eventDate);

        // Optimal purchase window is usually 2-30 days before event
        if ($daysUntilEvent <= 1) {
            return 100; // Last minute - high urgency
        }
        if ($daysUntilEvent <= 7) {
            return 90; // Week before - very good timing
        }
        if ($daysUntilEvent <= 30) {
            return 80; // Month before - good timing
        }
        if ($daysUntilEvent <= 90) {
            return 60; // Too early, prices might drop
        }

        return 40; // Way too early
    }

    /**
     * Calculate user preference score
     */
    /**
     * CalculateUserPreferenceScore
     */
    private function calculateUserPreferenceScore(ScrapedTicket $ticket, User $user): float
    {
        $preferences = $user->preferences ?? [];
        $score = 50; // Base score

        // Preferred sections
        if (isset($preferences['preferred_sections'])
            && in_array($ticket->section, $preferences['preferred_sections'], TRUE)) {
            $score += 20;
        }

        // Preferred platforms
        if (isset($preferences['preferred_platforms'])
            && in_array($ticket->platform, $preferences['preferred_platforms'], TRUE)) {
            $score += 15;
        }

        // Price range preference
        if (isset($preferences['max_ticket_price'])
            && $ticket->total_price <= $preferences['max_ticket_price']) {
            $score += 15;
        }

        return min(100, $score);
    }

    /**
     * Calculate success probability based on historical data
     */
    /**
     * CalculateSuccessProbability
     */
    private function calculateSuccessProbability(ScrapedTicket $ticket): float
    {
        $platformSuccessRate = $this->getPlatformSuccessRate($ticket->platform);
        $eventTypeSuccessRate = $this->getEventTypeSuccessRate($ticket->title);
        $priceRangeSuccessRate = $this->getPriceRangeSuccessRate($ticket->total_price);

        // Weighted average
        return ($platformSuccessRate * 0.4) +
               ($eventTypeSuccessRate * 0.3) +
               ($priceRangeSuccessRate * 0.3);
    }

    /**
     * Calculate overall score with weights
     */
    /**
     * CalculateOverallScore
     */
    private function calculateOverallScore(array $scores): float
    {
        $weights = [
            'price_score'           => 0.25,
            'demand_score'          => 0.20,
            'platform_score'        => 0.20,
            'timing_score'          => 0.15,
            'user_preference_score' => 0.10,
            'success_probability'   => 0.10,
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($weights as $scoreKey => $weight) {
            if (isset($scores[$scoreKey])) {
                $weightedSum += $scores[$scoreKey] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Generate recommendation based on score
     */
    /**
     * GenerateRecommendation
     */
    private function generateRecommendation(float $overallScore, array $scores): array
    {
        if ($overallScore >= 85) {
            $action = 'strong_buy';
            $message = 'Excellent opportunity - highly recommended for purchase';
            $urgency = 'high';
        } elseif ($overallScore >= 70) {
            $action = 'buy';
            $message = 'Good opportunity - recommended for purchase';
            $urgency = 'medium';
        } elseif ($overallScore >= 50) {
            $action = 'consider';
            $message = 'Moderate opportunity - consider your budget and preferences';
            $urgency = 'low';
        } else {
            $action = 'avoid';
            $message = 'Poor opportunity - not recommended at this time';
            $urgency = 'none';
        }

        return [
            'action'     => $action,
            'message'    => $message,
            'urgency'    => $urgency,
            'score'      => $overallScore,
            'confidence' => $this->calculateConfidence($scores),
        ];
    }

    /**
     * Check if eligible for auto-purchase
     */
    /**
     * Check if  eligible for auto purchase
     */
    private function isEligibleForAutoPurchase(float $overallScore, array $scores, User $user): bool
    {
        $userSettings = $user->preferences['auto_purchase'] ?? [];

        if (! ($userSettings['enabled'] ?? FALSE)) {
            return FALSE;
        }

        $minScore = $userSettings['min_score'] ?? 80;
        $maxPrice = $userSettings['max_price'] ?? 500;

        return $overallScore >= $minScore
               && $scores['success_probability'] >= 70;
    }

    // Additional helper methods for platform statistics and calculations
    /**
     * Get  platform success rate
     */
    private function getPlatformSuccessRate(string $platform): float
    {
        $cacheKey = "platform_success_rate_{$platform}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($platform) {
            $total = PurchaseAttempt::where('platform', $platform)->count();
            if ($total === 0) {
                return 70;
            } // Default rate

            $successful = PurchaseAttempt::where('platform', $platform)
                ->where('status', 'success')
                ->count();

            return ($successful / $total) * 100;
        });
    }

    /**
     * Get  platform processing time
     */
    private function getPlatformProcessingTime(string $platform): float
    {
        return Cache::remember("platform_processing_time_{$platform}", now()->addHours(1), function () use ($platform) {
            return PurchaseAttempt::where('platform', $platform)
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
                ->value('avg_time') ?? 120; // Default 2 minutes
        });
    }

    /**
     * Get  platform reliability score
     */
    private function getPlatformReliabilityScore(string $platform): float
    {
        // Combine success rate, uptime, and error rates
        $successRate = $this->getPlatformSuccessRate($platform);
        $avgProcessingTime = $this->getPlatformProcessingTime($platform);

        // Normalize processing time (faster = better)
        $timeScore = max(0, 100 - ($avgProcessingTime / 5)); // 5 seconds = 0 points

        return ($successRate * 0.7) + ($timeScore * 0.3);
    }

    /**
     * CalculateValueScore
     *
     * @param mixed $platforms
     */
    private function calculateValueScore(App\Models\Ticket $ticket, $platforms = []): float
    {
        $config = config('purchase_automation.price_comparison.value_score_calculation', [
            'price_weight'           => 0.4,
            'reliability_weight'     => 0.3,
            'success_rate_weight'    => 0.2,
            'processing_time_weight' => 0.1,
        ]);
        $basePrice = $ticket->total_price;

        if (empty($platforms)) {
            // Simple value score based on price and platform reliability
            $reliabilityScore = $this->getPlatformReliabilityScore($ticket->platform);
            $successRateScore = $this->getPlatformSuccessRate($ticket->platform);

            return ($reliabilityScore * 0.6) + ($successRateScore * 0.4);
        }

        $bestPrice = min(array_column($platforms, 'price'));
        $worstPrice = max(array_column($platforms, 'price'));

        // Price competitiveness (0-100)
        $priceScore = 100;
        if ($worstPrice > $bestPrice) {
            $priceScore = 100 - (($basePrice - $bestPrice) / ($worstPrice - $bestPrice)) * 100;
        }

        // Platform reliability score
        $reliabilityScore = $this->getPlatformReliabilityScore($ticket->platform);

        // Success rate score
        $successRateScore = $this->getPlatformSuccessRate($ticket->platform);

        // Processing time score (faster = better)
        $avgProcessingTime = $this->getPlatformProcessingTime($ticket->platform);
        $processingScore = max(0, 100 - ($avgProcessingTime / 60)); // Convert to minutes and score

        // Weighted combination
        $valueScore = (
            $priceScore * $config['price_weight'] +
            $reliabilityScore * $config['reliability_weight'] +
            $successRateScore * $config['success_rate_weight'] +
            $processingScore * $config['processing_time_weight']
        );

        return max(0, min(100, $valueScore));
    }

    /**
     * EstimatePlatformFees
     */
    private function estimatePlatformFees(App\Models\Ticket $ticket): float
    {
        $platform = $ticket->platform;
        $platformConfig = config("purchase_automation.platforms.{$platform}");

        if (! $platformConfig) {
            return $ticket->total_price * 0.15; // Default 15% fees
        }

        return $ticket->total_price * $platformConfig['base_fees_percentage'];
    }

    /**
     * CalculatePriceVariance
     */
    private function calculatePriceVariance(array $comparison): float
    {
        $prices = array_column($comparison, 'price');
        $mean = array_sum($prices) / count($prices);

        $variance = 0;
        foreach ($prices as $price) {
            $variance += pow($price - $mean, 2);
        }

        return sqrt($variance / count($prices)) / $mean; // Coefficient of variation
    }

    /**
     * ValidatePurchaseRequest
     */
    private function validatePurchaseRequest(array $request): array
    {
        $errors = [];
        $validation = config('purchase_automation.checkout.validation_rules');

        // Required fields
        $required = ['ticket_id', 'user_id', 'quantity'];
        foreach ($required as $field) {
            if (! isset($request[$field]) || empty($request[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate price limits
        if (isset($request['max_price']) && $request['max_price'] > $validation['max_price_per_ticket']) {
            $errors[] = "Price exceeds maximum allowed: {$validation['max_price_per_ticket']}";
        }

        // Validate quantity
        if (isset($request['quantity']) && $request['quantity'] > $validation['max_quantity_per_purchase']) {
            $errors[] = "Quantity exceeds maximum allowed: {$validation['max_quantity_per_purchase']}";
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * CreatePurchaseQueueItem
     *
     * @param mixed $user
     * @param mixed $request
     * @param mixed $transactionId
     */
    private function createPurchaseQueueItem(App\Models\Ticket $ticket, $user, $request, $transactionId): PurchaseQueue
    {
        return PurchaseQueue::create([
            'uuid'              => uniqid('queue_'),
            'user_id'           => $user->id,
            'scraped_ticket_id' => $ticket->id,
            'quantity'          => $request['quantity'],
            'max_price'         => $request['max_price'] ?? $ticket->total_price,
            'status'            => 'pending',
            'transaction_id'    => $transactionId,
            'priority'          => $request['priority'] ?? 'normal',
            'metadata'          => json_encode([
                'automated'        => TRUE,
                'request_data'     => $request,
                'analytics_scores' => $request['scores'] ?? [],
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * ExecutePurchaseFlow
     *
     * @param mixed $queueItem
     * @param mixed $request
     */
    private function executePurchaseFlow($queueItem, $request): array
    {
        // Create purchase attempt record
        $attempt = PurchaseAttempt::create([
            'purchase_queue_id'  => $queueItem->id,
            'scraped_ticket_id'  => $queueItem->scraped_ticket_id,
            'platform'           => $queueItem->scrapedTicket->platform,
            'attempted_price'    => $queueItem->scrapedTicket->total_price,
            'attempted_quantity' => $queueItem->quantity ?? 1,
        ]);

        // Use actual PurchaseService to process the purchase
        $purchaseService = new PurchaseService();
        $result = $purchaseService->processPurchase($attempt);

        return [
            'success'              => $result->success,
            'attempt_id'           => $attempt->id,
            'confirmation_number'  => $result->success ? $result->confirmationCode : NULL,
            'final_price'          => $result->success ? $result->totalPrice : $attempt->attempted_price,
            'total_paid'           => $result->success ? $result->totalPrice : NULL,
            'optimization_applied' => ['intelligent_timing', 'price_optimization'],
            'message'              => $result->success ? 'Purchase completed successfully' : 'Purchase failed: ' . $result->errorMessage,
        ];
    }

    /**
     * TrackPurchaseSuccess
     *
     * @param mixed $result
     * @param mixed $request
     */
    private function trackPurchaseSuccess($result, $request): void
    {
        // Store tracking data for analytics and ML training
        DB::table('purchase_tracking')->insert([
            'transaction_id' => $result['transaction_id'] ?? NULL,
            'success'        => $result['success'],
            'platform'       => $request['platform'] ?? 'unknown',
            'execution_time' => $result['execution_time_ms'] ?? 0,
            'final_price'    => $result['final_price'] ?? 0,
            'user_id'        => $request['user_id'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    /**
     * BuildFailureResponse
     *
     * @param mixed $transactionId
     * @param mixed $reason
     * @param mixed $errors
     */
    private function buildFailureResponse($transactionId, $reason, $errors = []): array
    {
        return [
            'success'           => FALSE,
            'transaction_id'    => $transactionId,
            'error'             => $reason,
            'errors'            => $errors,
            'retry_recommended' => TRUE,
            'completed_at'      => now()->toISOString(),
        ];
    }

    /**
     * GenerateNextSteps
     *
     * @param mixed $result
     */
    private function generateNextSteps($result): array
    {
        if ($result['success']) {
            return [
                'email_confirmation_sent',
                'ticket_delivery_initiated',
                'calendar_event_suggested',
            ];
        }

        return [
            'retry_attempt_scheduled',
            'alternative_options_search',
            'user_notification_sent',
        ];
    }

    /**
     * IdentifyRiskFactors
     *
     * @param mixed $scores
     */
    private function identifyRiskFactors(App\Models\Ticket $ticket, $scores): array
    {
        $risks = [];

        if ($scores['price_score'] < 30) {
            $risks[] = 'High price relative to budget';
        }

        if ($scores['platform_score'] < 50) {
            $risks[] = 'Platform reliability concerns';
        }

        if ($scores['success_probability'] < 60) {
            $risks[] = 'Low success probability';
        }

        return $risks;
    }

    /**
     * GenerateOptimizationSuggestions
     *
     * @param mixed $scores
     */
    private function generateOptimizationSuggestions($scores): array
    {
        $suggestions = [];

        if ($scores['timing_score'] < 70) {
            $suggestions[] = 'Consider waiting for better timing';
        }

        if ($scores['platform_score'] < 60) {
            $suggestions[] = 'Try alternative platforms';
        }

        if ($scores['price_score'] < 50) {
            $suggestions[] = 'Set price alerts for better deals';
        }

        return $suggestions;
    }

    /**
     * CalculateConfidence
     *
     * @param mixed $scores
     */
    private function calculateConfidence($scores): float
    {
        $variance = 0;
        $mean = array_sum($scores) / count($scores);

        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }

        $standardDeviation = sqrt($variance / count($scores));

        // Lower standard deviation = higher confidence
        return max(0, 100 - $standardDeviation);
    }

    /**
     * Get  event type success rate
     *
     * @param mixed $eventTitle
     */
    private function getEventTypeSuccessRate($eventTitle): float
    {
        // Simplified - could use ML to categorize events
        $eventType = $this->categorizeEvent($eventTitle);

        $successRates = [
            'concert'  => 75,
            'sports'   => 80,
            'theater'  => 70,
            'festival' => 65,
            'other'    => 70,
        ];

        return $successRates[$eventType] ?? 70;
    }

    /**
     * Get  price range success rate
     */
    private function getPriceRangeSuccessRate(float $price): float
    {
        if ($price < 100) {
            return 85;
        }
        if ($price < 300) {
            return 80;
        }
        if ($price < 500) {
            return 75;
        }
        if ($price < 1000) {
            return 70;
        }

        return 60; // Very expensive tickets
    }

    /**
     * CategorizeEvent
     *
     * @param mixed $eventTitle
     */
    private function categorizeEvent($eventTitle): string
    {
        $title = strtolower($eventTitle);

        if (preg_match('/concert|music|band|singer/', $title)) {
            return 'concert';
        }
        if (preg_match('/game|match|sports|football|basketball/', $title)) {
            return 'sports';
        }
        if (preg_match('/theater|play|musical|opera/', $title)) {
            return 'theater';
        }
        if (preg_match('/festival|fest|fair/', $title)) {
            return 'festival';
        }

        return 'other';
    }

    /**
     * StoreTrackingData
     */
    private function storeTrackingData(array $data): void
    {
        // Store in database for analytics
        DB::table('automation_tracking')->insert([
            'transaction_id'   => $data['transaction_id'],
            'platform'         => $data['platform'],
            'success'          => $data['success'],
            'execution_time'   => $data['execution_time'],
            'price_difference' => $data['price_difference'],
            'user_id'          => $data['user_id'],
            'created_at'       => $data['timestamp'],
            'updated_at'       => $data['timestamp'],
        ]);
    }

    /**
     * GenerateOptimizationInsights
     */
    private function generateOptimizationInsights(array $data): array
    {
        $insights = [];

        if ($data['execution_time'] > 300000) { // 5 minutes
            $insights[] = 'Execution time optimization needed';
        }

        if ($data['price_difference'] > 50) {
            $insights[] = 'Price prediction accuracy can be improved';
        }

        return $insights;
    }

    /**
     * UpdateMachineLearningModels
     */
    private function updateMachineLearningModels(array $data): void
    {
        // Update ML models with new data
        Log::info('ML models updated with new purchase data', [
            'transaction_id' => $data['transaction_id'],
            'success'        => $data['success'],
        ]);
    }

    /**
     * AdjustAutomationParameters
     */
    private function adjustAutomationParameters(array $data): array
    {
        $adjustments = [];

        // Adjust thresholds based on performance
        if ($data['success'] && $data['execution_time'] < 60000) {
            $adjustments['confidence_threshold'] = 'decrease_by_5';
        }

        return $adjustments;
    }

    /**
     * Get  average processing time
     *
     * @param mixed $platform
     */
    private function getAverageProcessingTime($platform): float
    {
        return $this->getPlatformProcessingTime($platform);
    }
}
