<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Services\Interfaces\PurchaseAutomationInterface;
use App\Services\Patterns\ChainOfResponsibility\PurchaseDecisionChain;
use App\Services\Patterns\Strategy\PurchaseStrategyFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Override;

use function count;
use function in_array;

/**
 * Unified Purchase Automation Service
 *
 * Consolidates automated purchase decision making and execution
 * for sport events entry tickets with intelligent decision chains.
 */
class PurchaseAutomationService extends BaseService implements PurchaseAutomationInterface
{
    private const string QUEUE_PREFIX = 'purchase_queue:';

    private const string DECISION_PREFIX = 'purchase_decisions:';

    private const string AUTOMATION_PREFIX = 'purchase_automation:';

    private PurchaseDecisionChain $decisionChain;

    private PurchaseStrategyFactory $strategyFactory;

    private array $automationRules = [];

    private array $activeQueues = [];

    /**
     * Create automated purchase rule
     */
    /**
     * CreatePurchaseRule
     */
    public function createPurchaseRule(int $userId, array $criteria, array $conditions, array $preferences = []): string
    {
        $this->ensureInitialized();

        try {
            $ruleId = 'rule_' . uniqid();
            $rule = [
                'rule_id'              => $ruleId,
                'user_id'              => $userId,
                'criteria'             => $criteria,
                'conditions'           => $conditions,
                'preferences'          => $preferences,
                'status'               => 'active',
                'created_at'           => Carbon::now()->toISOString(),
                'triggered_count'      => 0,
                'successful_purchases' => 0,
                'total_spent'          => 0,
            ];

            $ruleKey = self::AUTOMATION_PREFIX . 'rule:' . $ruleId;
            Redis::hmset($ruleKey, $this->encryptRuleData($rule));
            Redis::expire($ruleKey, 86400 * 90); // 90 days

            // Add to user's rules
            Redis::sadd(self::AUTOMATION_PREFIX . 'user:' . $userId, $ruleId);

            $this->automationRules[$ruleId] = $rule;

            $this->logOperation('createPurchaseRule', ['rule_id' => $ruleId, 'user_id' => $userId]);

            $this->getDependency('analyticsService')->trackEvent('purchase_rule_created', [
                'rule_id'  => $ruleId,
                'user_id'  => $userId,
                'criteria' => $criteria,
            ]);

            return $ruleId;
        } catch (Exception $e) {
            $this->handleError($e, 'createPurchaseRule', ['user_id' => $userId]);

            throw $e;
        }
    }

    /**
     * Process purchase decision for available tickets
     */
    /**
     * ProcessPurchaseDecision
     */
    public function processPurchaseDecision(int $ticketId, array $availabilityData, array $userPreferences = []): array
    {
        $this->ensureInitialized();

        try {
            $decisionData = [
                'ticket_id'         => $ticketId,
                'availability_data' => $availabilityData,
                'user_preferences'  => $userPreferences,
                'timestamp'         => Carbon::now()->toISOString(),
            ];

            // Run through decision chain
            $decision = $this->decisionChain->process($decisionData);

            // Store decision record
            $decisionKey = self::DECISION_PREFIX . $ticketId . ':' . time();
            Redis::hmset($decisionKey, $decision);
            Redis::expire($decisionKey, 86400 * 7); // 7 days

            $this->logOperation('processPurchaseDecision', [
                'ticket_id'  => $ticketId,
                'decision'   => $decision['action'],
                'confidence' => $decision['confidence'],
            ]);

            // Execute purchase if decision is positive
            if ($decision['action'] === 'purchase' && $decision['confidence'] >= 0.7) {
                return $this->executePurchase($ticketId, $decision, $userPreferences);
            }

            return $decision;
        } catch (Exception $e) {
            $this->handleError($e, 'processPurchaseDecision', ['ticket_id' => $ticketId]);

            return [
                'action'     => 'error',
                'reason'     => $e->getMessage(),
                'confidence' => 0,
            ];
        }
    }

    /**
     * Execute automated purchase
     */
    /**
     * ExecutePurchase
     */
    public function executePurchase(int $ticketId, array $decision, array $userPreferences): array
    {
        $this->ensureInitialized();

        try {
            $purchaseId = 'purchase_' . uniqid();

            $purchaseData = [
                'purchase_id'      => $purchaseId,
                'ticket_id'        => $ticketId,
                'decision'         => $decision,
                'user_preferences' => $userPreferences,
                'status'           => 'pending',
                'created_at'       => Carbon::now()->toISOString(),
                'attempts'         => 0,
                'max_attempts'     => $userPreferences['max_attempts'] ?? 3,
            ];

            // Add to purchase queue
            $queueKey = self::QUEUE_PREFIX . 'pending';
            Redis::lpush($queueKey, json_encode($purchaseData));
            Redis::expire($queueKey, 86400); // 24 hours

            // Store purchase record
            $purchaseKey = self::QUEUE_PREFIX . 'purchase:' . $purchaseId;
            Redis::hmset($purchaseKey, $this->encryptPurchaseData($purchaseData));
            Redis::expire($purchaseKey, 86400 * 30); // 30 days

            $this->activeQueues[$purchaseId] = $purchaseData;

            $this->logOperation('executePurchase', [
                'purchase_id' => $purchaseId,
                'ticket_id'   => $ticketId,
            ]);

            // Process purchase immediately if configured
            if ($userPreferences['immediate_processing'] ?? FALSE) {
                return $this->processPurchaseQueue($purchaseId);
            }

            return [
                'purchase_id' => $purchaseId,
                'status'      => 'queued',
                'message'     => 'Purchase queued for processing',
            ];
        } catch (Exception $e) {
            $this->handleError($e, 'executePurchase', ['ticket_id' => $ticketId]);

            throw $e;
        }
    }

    /**
     * Process purchase queue
     */
    /**
     * ProcessPurchaseQueue
     */
    public function processPurchaseQueue(?string $purchaseId = NULL): array
    {
        $this->ensureInitialized();

        $results = [];

        if ($purchaseId) {
            // Process specific purchase
            $results[] = $this->processSinglePurchase($purchaseId);
        } else {
            // Process all pending purchases
            $queueKey = self::QUEUE_PREFIX . 'pending';

            while ($purchaseJson = Redis::rpop($queueKey)) {
                $purchaseData = json_decode($purchaseJson, TRUE);
                $results[] = $this->processSinglePurchase($purchaseData['purchase_id']);
            }
        }

        $this->getDependency('analyticsService')->trackEvent('purchase_queue_processed', [
            'total_processed' => count($results),
            'successful'      => count(array_filter($results, fn (array $r): bool => $r['status'] === 'success')),
        ]);

        return $results;
    }

    /**
     * Get purchase automation statistics
     */
    /**
     * Get  automation statistics
     */
    public function getAutomationStatistics(?int $userId = NULL): array
    {
        $this->ensureInitialized();

        if ($userId) {
            return $this->getUserAutomationStats($userId);
        }

        return $this->getGlobalAutomationStats();
    }

    /**
     * Update purchase rule
     */
    /**
     * UpdatePurchaseRule
     */
    public function updatePurchaseRule(string $ruleId, array $updates): bool
    {
        $this->ensureInitialized();

        try {
            $ruleKey = self::AUTOMATION_PREFIX . 'rule:' . $ruleId;
            $currentRule = Redis::hgetall($ruleKey);

            if (empty($currentRule)) {
                throw new InvalidArgumentException("Purchase rule {$ruleId} not found");
            }

            $decryptedRule = $this->decryptRuleData($currentRule);
            $updatedRule = array_merge($decryptedRule, $updates, [
                'updated_at' => Carbon::now()->toISOString(),
            ]);

            Redis::hmset($ruleKey, $this->encryptRuleData($updatedRule));
            $this->automationRules[$ruleId] = $updatedRule;

            return TRUE;
        } catch (Exception $e) {
            $this->handleError($e, 'updatePurchaseRule', ['rule_id' => $ruleId]);

            return FALSE;
        }
    }

    /**
     * Deactivate purchase rule
     */
    /**
     * DeactivatePurchaseRule
     */
    public function deactivatePurchaseRule(string $ruleId): bool
    {
        $this->ensureInitialized();

        try {
            return $this->updatePurchaseRule($ruleId, ['status' => 'inactive']);
        } catch (Exception $e) {
            $this->handleError($e, 'deactivatePurchaseRule', ['rule_id' => $ruleId]);

            return FALSE;
        }
    }

    /**
     * Get user's purchase rules
     */
    /**
     * Get  user purchase rules
     */
    public function getUserPurchaseRules(int $userId): array
    {
        $this->ensureInitialized();

        $ruleIds = Redis::smembers(self::AUTOMATION_PREFIX . 'user:' . $userId);
        $rules = [];

        foreach ($ruleIds as $ruleId) {
            $ruleKey = self::AUTOMATION_PREFIX . 'rule:' . $ruleId;
            $ruleData = Redis::hgetall($ruleKey);

            if (! empty($ruleData)) {
                $rules[] = $this->decryptRuleData($ruleData);
            }
        }

        return $rules;
    }

    /**
     * Check and trigger automation rules
     */
    /**
     * CheckAutomationTriggers
     */
    public function checkAutomationTriggers(int $ticketId, array $availabilityData): array
    {
        $this->ensureInitialized();

        $triggeredRules = [];

        foreach ($this->automationRules as $ruleId => $rule) {
            if ($rule['status'] !== 'active') {
                continue;
            }

            if ($this->evaluateRuleConditions($rule, $availabilityData)) {
                $result = $this->triggerAutomationRule($ruleId, $ticketId, $availabilityData);
                $triggeredRules[] = $result;
            }
        }

        return $triggeredRules;
    }

    /**
     * OnInitialize
     */
    #[Override]
    protected function onInitialize(): void
    {
        $this->validateDependencies([
            'ticketMonitoringService',
            'notificationService',
            'analyticsService',
            'encryptionService',
        ]);

        $this->decisionChain = new PurchaseDecisionChain([
            'analyticsService'  => $this->getDependency('analyticsService'),
            'encryptionService' => $this->getDependency('encryptionService'),
        ]);

        $this->strategyFactory = new PurchaseStrategyFactory();
        $this->loadAutomationRules();
        $this->loadActiveQueues();
    }

    /**
     * Private helper methods
     */
    /**
     * ProcessSinglePurchase
     */
    private function processSinglePurchase(string $purchaseId): array
    {
        try {
            $purchaseKey = self::QUEUE_PREFIX . 'purchase:' . $purchaseId;
            $purchaseData = Redis::hgetall($purchaseKey);

            if (empty($purchaseData)) {
                throw new InvalidArgumentException("Purchase {$purchaseId} not found");
            }

            $decryptedData = $this->decryptPurchaseData($purchaseData);

            // Select purchase strategy based on preferences
            $strategy = $this->strategyFactory->create(
                $decryptedData['user_preferences']['strategy'] ?? 'default',
            );

            // Execute purchase through strategy
            $result = $strategy->execute($decryptedData);

            // Update purchase record
            $decryptedData['status'] = $result['success'] ? 'completed' : 'failed';
            $decryptedData['result'] = $result;
            $decryptedData['completed_at'] = Carbon::now()->toISOString();

            Redis::hmset($purchaseKey, $this->encryptPurchaseData($decryptedData));

            // Send notification
            $this->sendPurchaseNotification($decryptedData, $result);

            return [
                'purchase_id' => $purchaseId,
                'status'      => $result['success'] ? 'success' : 'failed',
                'result'      => $result,
            ];
        } catch (Exception $e) {
            $this->handleError($e, 'processSinglePurchase', ['purchase_id' => $purchaseId]);

            return [
                'purchase_id' => $purchaseId,
                'status'      => 'error',
                'error'       => $e->getMessage(),
            ];
        }
    }

    /**
     * EvaluateRuleConditions
     */
    private function evaluateRuleConditions(array $rule, array $availabilityData): bool
    {
        foreach ($rule['conditions'] as $condition => $value) {
            switch ($condition) {
                case 'max_price':
                    if ($availabilityData['best_price'] && $availabilityData['best_price'] > $value) {
                        return FALSE;
                    }

                    break;
                case 'min_availability':
                    if ($availabilityData['total_available'] < $value) {
                        return FALSE;
                    }

                    break;
                case 'preferred_platforms':
                    $hasPreferredPlatform = FALSE;
                    foreach ($availabilityData['available_platforms'] as $platform) {
                        if (in_array($platform, $value, TRUE)) {
                            $hasPreferredPlatform = TRUE;

                            break;
                        }
                    }
                    if (! $hasPreferredPlatform) {
                        return FALSE;
                    }

                    break;
            }
        }

        return TRUE;
    }

    /**
     * TriggerAutomationRule
     */
    private function triggerAutomationRule(string $ruleId, int $ticketId, array $availabilityData): array
    {
        try {
            $rule = $this->automationRules[$ruleId];

            // Process purchase decision
            $decision = $this->processPurchaseDecision($ticketId, $availabilityData, $rule['preferences']);

            // Update rule stats
            $this->updateRuleStats($ruleId, $decision);

            return [
                'rule_id'   => $ruleId,
                'ticket_id' => $ticketId,
                'triggered' => TRUE,
                'decision'  => $decision,
            ];
        } catch (Exception $e) {
            $this->handleError($e, 'triggerAutomationRule', ['rule_id' => $ruleId]);

            return [
                'rule_id'   => $ruleId,
                'ticket_id' => $ticketId,
                'triggered' => FALSE,
                'error'     => $e->getMessage(),
            ];
        }
    }

    /**
     * UpdateRuleStats
     */
    private function updateRuleStats(string $ruleId, array $decision): void
    {
        $ruleKey = self::AUTOMATION_PREFIX . 'rule:' . $ruleId;

        Redis::hincrby($ruleKey, 'triggered_count', 1);
        Redis::hset($ruleKey, 'last_triggered', Carbon::now()->toISOString());

        if ($decision['action'] === 'purchase') {
            Redis::hincrby($ruleKey, 'successful_purchases', 1);
        }
    }

    /**
     * SendPurchaseNotification
     */
    private function sendPurchaseNotification(array $purchaseData, array $result): void
    {
        try {
            $this->getDependency('notificationService')->sendSystemNotification(
                $result['success'] ?
                    'Purchase completed successfully!' :
                    'Purchase failed: ' . ($result['message'] ?? 'Unknown error'),
                $result['success'] ? 'success' : 'error',
                [$purchaseData['user_preferences']['user_id'] ?? 0],
                [
                    'purchase_id' => $purchaseData['purchase_id'],
                    'ticket_id'   => $purchaseData['ticket_id'],
                    'result'      => $result,
                ],
            );
        } catch (Exception $e) {
            $this->handleError($e, 'sendPurchaseNotification', [
                'purchase_id' => $purchaseData['purchase_id'],
            ]);
        }
    }

    /**
     * LoadAutomationRules
     */
    private function loadAutomationRules(): void
    {
        // Load active automation rules from Redis
        $pattern = self::AUTOMATION_PREFIX . 'rule:*';
        $keys = Redis::keys($pattern);

        foreach ($keys as $key) {
            $ruleData = Redis::hgetall($key);
            if (! empty($ruleData)) {
                $decryptedRule = $this->decryptRuleData($ruleData);
                if ($decryptedRule['status'] === 'active') {
                    $this->automationRules[$decryptedRule['rule_id']] = $decryptedRule;
                }
            }
        }
    }

    /**
     * LoadActiveQueues
     */
    private function loadActiveQueues(): void
    {
        $queueKey = self::QUEUE_PREFIX . 'pending';
        $queueItems = Redis::lrange($queueKey, 0, -1);

        foreach ($queueItems as $itemJson) {
            $item = json_decode($itemJson, TRUE);
            if ($item) {
                $this->activeQueues[$item['purchase_id']] = $item;
            }
        }
    }

    /**
     * EncryptRuleData
     */
    private function encryptRuleData(array $rule): array
    {
        $encryptionService = $this->getDependency('encryptionService');
        $encrypted = $rule;

        $sensitiveFields = ['criteria', 'conditions', 'preferences'];
        foreach ($sensitiveFields as $field) {
            if (isset($rule[$field])) {
                $encrypted[$field] = $encryptionService->encryptJsonData($rule[$field]);
            }
        }

        return $encrypted;
    }

    /**
     * DecryptRuleData
     */
    private function decryptRuleData(array $encryptedRule): array
    {
        $encryptionService = $this->getDependency('encryptionService');
        $decrypted = $encryptedRule;

        $sensitiveFields = ['criteria', 'conditions', 'preferences'];
        foreach ($sensitiveFields as $field) {
            if (isset($encryptedRule[$field])) {
                $decrypted[$field] = $encryptionService->decryptJsonData($encryptedRule[$field]);
            }
        }

        return $decrypted;
    }

    /**
     * EncryptPurchaseData
     */
    private function encryptPurchaseData(array $purchase): array
    {
        $encryptionService = $this->getDependency('encryptionService');
        $encrypted = $purchase;

        $sensitiveFields = ['decision', 'user_preferences', 'result'];
        foreach ($sensitiveFields as $field) {
            if (isset($purchase[$field])) {
                $encrypted[$field] = $encryptionService->encryptJsonData($purchase[$field]);
            }
        }

        return $encrypted;
    }

    /**
     * DecryptPurchaseData
     */
    private function decryptPurchaseData(array $encryptedPurchase): array
    {
        $encryptionService = $this->getDependency('encryptionService');
        $decrypted = $encryptedPurchase;

        $sensitiveFields = ['decision', 'user_preferences', 'result'];
        foreach ($sensitiveFields as $field) {
            if (isset($encryptedPurchase[$field])) {
                $decrypted[$field] = $encryptionService->decryptJsonData($encryptedPurchase[$field]);
            }
        }

        return $decrypted;
    }

    /**
     * Get  user automation stats
     */
    private function getUserAutomationStats(int $userId): array
    {
        $rules = $this->getUserPurchaseRules($userId);

        return [
            'user_id'              => $userId,
            'total_rules'          => count($rules),
            'active_rules'         => count(array_filter($rules, fn (array $r): bool => $r['status'] === 'active')),
            'total_triggered'      => array_sum(array_column($rules, 'triggered_count')),
            'successful_purchases' => array_sum(array_column($rules, 'successful_purchases')),
            'total_spent'          => array_sum(array_column($rules, 'total_spent')),
            'timestamp'            => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Get  global automation stats
     */
    private function getGlobalAutomationStats(): array
    {
        return [
            'total_automation_rules' => count($this->automationRules),
            'active_queues'          => count($this->activeQueues),
            'queue_health'           => $this->getQueueHealth(),
            'timestamp'              => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Get  queue health
     */
    private function getQueueHealth(): string
    {
        $queueKey = self::QUEUE_PREFIX . 'pending';
        $queueSize = Redis::llen($queueKey);

        if ($queueSize > 100) {
            return 'critical';
        }
        if ($queueSize > 50) {
            return 'warning';
        }

        return 'healthy';
    }
}
