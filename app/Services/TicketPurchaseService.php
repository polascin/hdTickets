<?php declare(strict_types=1);

namespace App\Services;

use App\Domain\Purchase\Events\PurchaseCompleted;
use App\Domain\Purchase\Events\PurchaseFailed;
use App\Domain\Purchase\Events\PurchaseInitiated;
use App\Domain\Purchase\ValueObjects\PurchaseId;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Models\Ticket;
use App\Models\TicketPurchase;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function in_array;

class TicketPurchaseService
{
    /**
     * Attempt to purchase tickets for a user
     */
    public function purchaseTickets(
        User $user,
        Ticket $ticket,
        int $quantity = 1,
        array $purchaseOptions = [],
    ): array {
        try {
            // Validate purchase prerequisites
            $validation = $this->validatePurchase($user, $ticket, $quantity);
            if (! $validation['valid']) {
                return [
                    'success' => FALSE,
                    'error'   => 'validation_failed',
                    'message' => $validation['message'],
                    'data'    => $validation['data'] ?? [],
                ];
            }

            // Begin transaction for atomic purchase
            return DB::transaction(fn (): array => $this->executePurchase($user, $ticket, $quantity, $purchaseOptions));
        } catch (Exception $e) {
            Log::error('Ticket purchase failed', [
                'user_id'   => $user->id,
                'ticket_id' => $ticket->id,
                'quantity'  => $quantity,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return [
                'success' => FALSE,
                'error'   => 'purchase_failed',
                'message' => 'An unexpected error occurred during purchase',
                'data'    => [
                    'error_code'        => 'SYSTEM_ERROR',
                    'support_reference' => Str::uuid()->toString(),
                ],
            ];
        }
    }

    /**
     * Get purchase history for user
     */
    public function getUserPurchaseHistory(User $user, array $filters = []): array
    {
        $query = TicketPurchase::where('user_id', $user->id)
            ->with(['ticket'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        $purchases = $query->paginate($filters['per_page'] ?? 15);

        return [
            'purchases'  => $purchases->items(),
            'pagination' => [
                'current_page' => $purchases->currentPage(),
                'per_page'     => $purchases->perPage(),
                'total'        => $purchases->total(),
                'last_page'    => $purchases->lastPage(),
                'has_more'     => $purchases->hasMorePages(),
            ],
            'summary' => [
                'total_purchases'      => TicketPurchase::where('user_id', $user->id)->count(),
                'successful_purchases' => TicketPurchase::where('user_id', $user->id)
                    ->where('status', TicketPurchase::STATUS_COMPLETED)->count(),
                'total_spent' => TicketPurchase::where('user_id', $user->id)
                    ->where('status', TicketPurchase::STATUS_COMPLETED)
                    ->sum('total_amount'),
                'monthly_usage' => $user->getMonthlyTicketUsage(),
            ],
        ];
    }

    /**
     * Validate purchase requirements
     */
    private function validatePurchase(User $user, Ticket $ticket, int $quantity): array
    {
        // Check if user can purchase tickets
        if (! $this->canUserPurchaseTickets($user)) {
            return [
                'valid'   => FALSE,
                'message' => 'User role does not allow ticket purchases',
                'data'    => ['user_role' => $user->role],
            ];
        }

        // Check ticket availability
        if (! $ticket->is_available || $ticket->available_quantity < $quantity) {
            return [
                'valid'   => FALSE,
                'message' => 'Insufficient tickets available',
                'data'    => [
                    'requested'     => $quantity,
                    'available'     => $ticket->available_quantity,
                    'ticket_status' => $ticket->is_available ? 'available' : 'unavailable',
                ],
            ];
        }

        // Check subscription limits for customers
        if ($user->isCustomer()) {
            $limitCheck = $this->checkTicketLimits($user, $quantity);
            if (! $limitCheck['valid']) {
                return $limitCheck;
            }
        }

        // Check if ticket event is still upcoming
        if ($ticket->event_date && $ticket->event_date->isPast()) {
            return [
                'valid'   => FALSE,
                'message' => 'Cannot purchase tickets for past events',
                'data'    => [
                    'event_date'   => $ticket->event_date->toISOString(),
                    'current_date' => now()->toISOString(),
                ],
            ];
        }

        return ['valid' => TRUE];
    }

    /**
     * Check if user can purchase tickets based on role
     */
    private function canUserPurchaseTickets(User $user): bool
    {
        return in_array($user->role, [
            User::ROLE_CUSTOMER,
            User::ROLE_AGENT,
            User::ROLE_ADMIN,
        ], TRUE);
    }

    /**
     * Check ticket purchase limits for customers
     */
    private function checkTicketLimits(User $user, int $requestedQuantity): array
    {
        $subscription = $user->subscription;

        if (! $subscription) {
            return [
                'valid'   => FALSE,
                'message' => 'Active subscription required for ticket purchases',
                'data'    => [
                    'action_required'  => 'subscription',
                    'subscription_url' => route('subscription.plans'),
                ],
            ];
        }

        // Check if subscription is active
        if (! $subscription->isActive() && ! $subscription->isInFreeTrial()) {
            return [
                'valid'   => FALSE,
                'message' => 'Subscription is not active',
                'data'    => [
                    'subscription_status' => $subscription->status,
                    'action_required'     => 'subscription_renewal',
                ],
            ];
        }

        // Check monthly limits
        $monthlyLimit = $subscription->plan->ticket_limit ?? config('subscription.default_ticket_limit', 100);
        $currentUsage = $user->getMonthlyTicketUsage();

        if (($currentUsage + $requestedQuantity) > $monthlyLimit) {
            return [
                'valid'   => FALSE,
                'message' => 'Monthly ticket limit would be exceeded',
                'data'    => [
                    'monthly_limit' => $monthlyLimit,
                    'current_usage' => $currentUsage,
                    'requested'     => $requestedQuantity,
                    'available'     => max(0, $monthlyLimit - $currentUsage),
                    'reset_date'    => now()->endOfMonth()->format('Y-m-d'),
                ],
            ];
        }

        return ['valid' => TRUE];
    }

    /**
     * Execute the actual purchase
     */
    private function executePurchase(User $user, Ticket $ticket, int $quantity, array $options): array
    {
        $purchaseId = new PurchaseId(Str::uuid()->toString());
        $ticketId = new TicketId($ticket->uuid);
        $totalAmount = $this->calculateTotalAmount($ticket, $quantity, $options);

        // Create purchase record
        $purchase = TicketPurchase::create([
            'uuid'             => $purchaseId->value(),
            'user_id'          => $user->id,
            'ticket_id'        => $ticket->id,
            'quantity'         => $quantity,
            'unit_price'       => $ticket->price,
            'total_amount'     => $totalAmount,
            'currency'         => $ticket->currency ?? 'USD',
            'status'           => TicketPurchase::STATUS_PENDING,
            'purchase_method'  => $options['method'] ?? 'automated',
            'purchase_options' => $options,
            'initiated_at'     => now(),
        ]);

        // Fire purchase initiated event
        Event::dispatch(new PurchaseInitiated(
            $purchaseId,
            (string) $user->id,
            $ticketId,
            $totalAmount,
            $ticket->currency ?? 'USD',
            [
                'quantity'        => $quantity,
                'unit_price'      => $ticket->price,
                'purchase_method' => $options['method'] ?? 'automated',
                'user_role'       => $user->role,
            ],
        ));

        // Update ticket availability
        $this->updateTicketAvailability($ticket, $quantity);

        // Update user's monthly usage
        $user->incrementMonthlyTicketUsage($quantity);

        // Simulate purchase processing (in real implementation, this would integrate with external APIs)
        $purchaseResult = $this->processPurchase($options);

        if ($purchaseResult['success']) {
            $purchase->update([
                'status'              => TicketPurchase::STATUS_COMPLETED,
                'completed_at'        => now(),
                'confirmation_number' => $purchaseResult['confirmation_number'],
                'external_reference'  => $purchaseResult['external_reference'] ?? NULL,
            ]);

            // Fire purchase completed event
            Event::dispatch(new PurchaseCompleted(
                $purchaseId,
                (string) $user->id,
                $ticketId,
                $totalAmount,
                $purchaseResult['confirmation_number'],
            ));

            return [
                'success' => TRUE,
                'message' => 'Ticket purchase completed successfully',
                'data'    => [
                    'purchase_id'         => $purchase->uuid,
                    'confirmation_number' => $purchaseResult['confirmation_number'],
                    'quantity'            => $quantity,
                    'total_amount'        => $totalAmount,
                    'currency'            => $ticket->currency ?? 'USD',
                    'ticket_details'      => [
                        'title'        => $ticket->title,
                        'event_date'   => $ticket->event_date?->toISOString(),
                        'venue'        => $ticket->venue,
                        'seat_details' => $ticket->seat_details,
                    ],
                    'usage_info' => [
                        'monthly_usage' => $user->getMonthlyTicketUsage(),
                        'monthly_limit' => $user->subscription?->plan?->ticket_limit ?? config('subscription.default_ticket_limit'),
                        'remaining'     => max(0, ($user->subscription?->plan?->ticket_limit ?? config('subscription.default_ticket_limit')) - $user->getMonthlyTicketUsage()),
                    ],
                ],
            ];
        }
        $purchase->update([
            'status'              => TicketPurchase::STATUS_FAILED,
            'failed_at'           => now(),
            'failure_reason'      => $purchaseResult['error_message'],
            'external_error_code' => $purchaseResult['error_code'] ?? NULL,
        ]);

        // Rollback ticket availability
        $this->rollbackTicketAvailability($ticket, $quantity);

        // Rollback user usage
        $user->decrementMonthlyTicketUsage($quantity);

        // Fire purchase failed event
        Event::dispatch(new PurchaseFailed(
            $purchaseId,
            (string) $user->id,
            $ticketId,
            $purchaseResult['error_message'],
            $purchaseResult['error_code'] ?? 'UNKNOWN',
        ));

        return [
            'success' => FALSE,
            'error'   => 'purchase_processing_failed',
            'message' => $purchaseResult['error_message'] ?? 'Purchase processing failed',
            'data'    => [
                'purchase_id'   => $purchase->uuid,
                'error_code'    => $purchaseResult['error_code'] ?? 'UNKNOWN',
                'retry_allowed' => $purchaseResult['retry_allowed'] ?? TRUE,
            ],
        ];
    }

    /**
     * Calculate total purchase amount including fees
     */
    private function calculateTotalAmount(Ticket $ticket, int $quantity, array $options): float
    {
        $subtotal = $ticket->price * $quantity;
        $processingFee = $options['processing_fee'] ?? ($subtotal * 0.03); // 3% processing fee
        $serviceFee = $options['service_fee'] ?? 2.50; // Flat service fee

        return round($subtotal + $processingFee + $serviceFee, 2);
    }

    /**
     * Process the purchase with external systems (mock implementation)
     */
    private function processPurchase(array $options): array
    {
        // In real implementation, this would integrate with:
        // - Ticket platform APIs (Ticketmaster, etc.)
        // - Payment processors (Stripe, PayPal)
        // - Email/SMS notification services

        // Mock processing logic
        $successRate = $options['mock_success_rate'] ?? 0.85;
        $isSuccess = (mt_rand() / mt_getrandmax()) < $successRate;

        if ($isSuccess) {
            return [
                'success'             => TRUE,
                'confirmation_number' => 'HDT-' . strtoupper(Str::random(8)),
                'external_reference'  => 'EXT-' . Str::random(12),
                'processing_time_ms'  => mt_rand(1000, 5000),
            ];
        }
        $errors = [
            'PAYMENT_DECLINED'           => 'Payment method declined',
            'TICKET_NO_LONGER_AVAILABLE' => 'Tickets no longer available',
            'PLATFORM_ERROR'             => 'External platform error',
            'TIMEOUT'                    => 'Request timeout',
            'RATE_LIMITED'               => 'Rate limit exceeded',
        ];

        $errorCode = array_rand($errors);

        return [
            'success'       => FALSE,
            'error_code'    => $errorCode,
            'error_message' => $errors[$errorCode],
            'retry_allowed' => ! in_array($errorCode, ['PAYMENT_DECLINED', 'TICKET_NO_LONGER_AVAILABLE'], TRUE),
        ];
    }

    /**
     * Update ticket availability after purchase
     */
    private function updateTicketAvailability(Ticket $ticket, int $quantity): void
    {
        $ticket->update([
            'available_quantity' => max(0, $ticket->available_quantity - $quantity),
            'is_available'       => ($ticket->available_quantity - $quantity) > 0,
        ]);
    }

    /**
     * Rollback ticket availability on purchase failure
     */
    private function rollbackTicketAvailability(Ticket $ticket, int $quantity): void
    {
        $ticket->update([
            'available_quantity' => $ticket->available_quantity + $quantity,
            'is_available'       => TRUE,
        ]);
    }
}
