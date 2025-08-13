<?php declare(strict_types=1);

namespace App\Services\Interfaces;

/**
 * Purchase Automation Service Interface
 *
 * Defines the contract for automated sport events entry ticket purchase services.
 */
interface PurchaseAutomationInterface
{
    /**
     * Create automated purchase rule
     *
     * @param int                  $userId      User ID
     * @param array<string, mixed> $criteria    Purchase criteria
     * @param array<string, mixed> $conditions  Purchase conditions
     * @param array<string, mixed> $preferences User preferences
     *
     * @return string Rule ID
     */
    public function createPurchaseRule(int $userId, array $criteria, array $conditions, array $preferences = []): string;

    /**
     * Process purchase decision for available tickets
     *
     * @param int                  $ticketId         Ticket ID
     * @param array<string, mixed> $availabilityData Ticket availability data
     * @param array<string, mixed> $userPreferences  User preferences
     *
     * @return array Purchase decision
     */
    public function processPurchaseDecision(int $ticketId, array $availabilityData, array $userPreferences = []): array;

    /**
     * Execute automated purchase
     *
     * @param int                  $ticketId        Ticket ID
     * @param array<string, mixed> $decision        Purchase decision
     * @param array<string, mixed> $userPreferences User preferences
     *
     * @return array Purchase result
     */
    public function executePurchase(int $ticketId, array $decision, array $userPreferences): array;

    /**
     * Process purchase queue
     *
     * @param string|null $purchaseId Optional specific purchase ID
     *
     * @return array Processing results
     */
    public function processPurchaseQueue(?string $purchaseId = NULL): array;

    /**
     * Get purchase automation statistics
     *
     * @param int|null $userId Optional user ID for user-specific stats
     *
     * @return array Statistics data
     */
    public function getAutomationStatistics(?int $userId = NULL): array;

    /**
     * Update purchase rule
     *
     * @param string               $ruleId  Rule ID
     * @param array<string, mixed> $updates Updates to apply
     *
     * @return bool Success status
     */
    public function updatePurchaseRule(string $ruleId, array $updates): bool;

    /**
     * Get user's purchase rules
     *
     * @param int $userId User ID
     *
     * @return array User's purchase rules
     */
    public function getUserPurchaseRules(int $userId): array;

    /**
     * Check and trigger automation rules
     *
     * @param int                  $ticketId         Ticket ID
     * @param array<string, mixed> $availabilityData Availability data
     *
     * @return array Triggered rules
     */
    public function checkAutomationTriggers(int $ticketId, array $availabilityData): array;
}
