<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use App\Services\AuditService;
use App\Services\PayPal\PayPalService;
use App\Services\PayPal\PayPalSubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function __construct(
        private PayPalService $paypalService,
        private PayPalSubscriptionService $paypalSubscriptionService,
        private AuditService $auditService,
    ) {
    }

    /**
     * Handle PayPal webhook events
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $eventData = json_decode($payload, TRUE);
        $eventType = $eventData['event_type'] ?? 'unknown';
        $webhookId = $eventData['id'] ?? NULL;

        // Initial audit log for webhook reception
        $this->auditService->logPayPalWebhook(
            event_type: $eventType,
            webhook_id: $webhookId,
            payload: $eventData ?: [],
            ip_address: $request->ip(),
            user_agent: $request->userAgent(),
            status: 'received',
        );

        try {
            // Get webhook headers
            $headers = $this->extractPayPalHeaders($request);
            $configWebhookId = config('services.paypal.webhook_id');

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($headers, $payload, $configWebhookId)) {
                // Audit security violation
                $this->auditService->logSecurityEvent(
                    event: 'paypal_webhook_signature_verification_failed',
                    ip_address: $request->ip(),
                    user_agent: $request->userAgent(),
                    data: [
                        'headers'    => $headers,
                        'webhook_id' => $configWebhookId,
                        'event_type' => $eventType,
                    ],
                );

                Log::warning('PayPal webhook signature verification failed', [
                    'headers'    => $headers,
                    'webhook_id' => $configWebhookId,
                    'event_type' => $eventType,
                ]);

                return response('Unauthorized', 401);
            }

            // Parse webhook event
            if (!$eventData || !isset($eventData['event_type'])) {
                $this->auditService->logPayPalWebhook(
                    event_type: 'invalid_payload',
                    webhook_id: $webhookId,
                    payload: $eventData ?: [],
                    ip_address: $request->ip(),
                    user_agent: $request->userAgent(),
                    status: 'failed',
                    error: 'Invalid webhook payload',
                );

                Log::error('Invalid PayPal webhook payload', ['payload' => $payload]);

                return response('Invalid payload', 400);
            }

            Log::info('PayPal webhook received', [
                'event_type'    => $eventType,
                'event_id'      => $webhookId,
                'resource_type' => $eventData['resource_type'] ?? 'unknown',
            ]);

            // Handle the webhook event
            $this->handleWebhookEvent($eventData);

            // Audit successful processing
            $this->auditService->logPayPalWebhook(
                event_type: $eventType,
                webhook_id: $webhookId,
                payload: $eventData,
                ip_address: $request->ip(),
                user_agent: $request->userAgent(),
                status: 'processed',
            );

            return response('Webhook handled successfully', 200);
        } catch (Exception $e) {
            // Audit failed processing
            $this->auditService->logPayPalWebhook(
                event_type: $eventType,
                webhook_id: $webhookId,
                payload: $eventData ?: [],
                ip_address: $request->ip(),
                user_agent: $request->userAgent(),
                status: 'failed',
                error: $e->getMessage(),
            );

            Log::error('PayPal webhook processing failed', [
                'event_type' => $eventType,
                'webhook_id' => $webhookId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response('Webhook processing failed', 500);
        }
    }

    /**
     * Handle specific webhook event types
     */
    private function handleWebhookEvent(array $event): void
    {
        $eventType = $event['event_type'];
        $resource = $event['resource'] ?? [];

        match ($eventType) {
            // Subscription events
            'BILLING.SUBSCRIPTION.CREATED'   => $this->handleSubscriptionCreated($resource),
            'BILLING.SUBSCRIPTION.ACTIVATED' => $this->handleSubscriptionActivated($resource),
            'BILLING.SUBSCRIPTION.CANCELLED' => $this->handleSubscriptionCancelled($resource),
            'BILLING.SUBSCRIPTION.SUSPENDED' => $this->handleSubscriptionSuspended($resource),
            'BILLING.SUBSCRIPTION.EXPIRED'   => $this->handleSubscriptionExpired($resource),

            // Payment events
            'PAYMENT.CAPTURE.COMPLETED' => $this->handlePaymentCaptureCompleted($resource),
            'PAYMENT.CAPTURE.DENIED'    => $this->handlePaymentCaptureDenied($resource),
            'PAYMENT.CAPTURE.REFUNDED'  => $this->handlePaymentCaptureRefunded($resource),

            // Subscription payment events
            'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED' => $this->handleSubscriptionPaymentCompleted($resource),
            'BILLING.SUBSCRIPTION.PAYMENT.FAILED'    => $this->handleSubscriptionPaymentFailed($resource),

            default => Log::info('Unhandled PayPal webhook event', [
                'event_type' => $eventType,
                'resource'   => $resource,
            ]),
        };
    }

    /**
     * Handle subscription created event
     */
    private function handleSubscriptionCreated(array $resource): void
    {
        $subscriptionId = $resource['id'] ?? NULL;
        if (!$subscriptionId) {
            Log::error('PayPal subscription created webhook missing subscription ID');

            return;
        }

        Log::info('PayPal subscription created webhook processed', [
            'subscription_id' => $subscriptionId,
            'status'          => $resource['status'] ?? 'unknown',
        ]);
    }

    /**
     * Handle subscription activated event
     */
    private function handleSubscriptionActivated(array $resource): void
    {
        $subscriptionId = $resource['id'] ?? NULL;
        if (!$subscriptionId) {
            Log::error('PayPal subscription activated webhook missing subscription ID');

            return;
        }

        $subscription = $this->paypalSubscriptionService->activateSubscription($subscriptionId);

        if ($subscription) {
            // Audit successful subscription activation
            $this->auditService->logPayPalSubscription(
                action: 'activated',
                subscription_id: $subscriptionId,
                user_id: $subscription->user_id,
                amount: NULL,
                currency: NULL,
                status: 'active',
                metadata: $resource,
            );

            Log::info('PayPal subscription activated via webhook', [
                'subscription_id'       => $subscriptionId,
                'local_subscription_id' => $subscription->id,
            ]);
        } else {
            // Audit failed activation
            $this->auditService->logPayPalSubscription(
                action: 'activation_failed',
                subscription_id: $subscriptionId,
                user_id: NULL,
                amount: NULL,
                currency: NULL,
                status: 'failed',
                metadata: array_merge($resource, ['error' => 'Local subscription not found']),
            );

            Log::warning('PayPal subscription activation failed - local subscription not found', [
                'subscription_id' => $subscriptionId,
            ]);
        }
    }

    /**
     * Handle subscription cancelled event
     */
    private function handleSubscriptionCancelled(array $resource): void
    {
        $subscriptionId = $resource['id'] ?? NULL;
        if (!$subscriptionId) {
            Log::error('PayPal subscription cancelled webhook missing subscription ID');

            return;
        }

        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update([
                'status'   => 'cancelled',
                'ends_at'  => now()->endOfMonth(),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'cancelled_via_webhook_at' => now()->toISOString(),
                    'paypal_status'            => $resource['status'] ?? 'cancelled',
                ]),
            ]);

            // Audit subscription cancellation
            $this->auditService->logPayPalSubscription(
                action: 'cancelled',
                subscription_id: $subscriptionId,
                user_id: $subscription->user_id,
                amount: NULL,
                currency: NULL,
                status: 'cancelled',
                metadata: $resource,
            );

            Log::info('PayPal subscription cancelled via webhook', [
                'subscription_id'       => $subscriptionId,
                'local_subscription_id' => $subscription->id,
            ]);
        } else {
            // Audit failed cancellation
            $this->auditService->logPayPalSubscription(
                action: 'cancellation_failed',
                subscription_id: $subscriptionId,
                user_id: NULL,
                amount: NULL,
                currency: NULL,
                status: 'failed',
                metadata: array_merge($resource, ['error' => 'Local subscription not found']),
            );

            Log::warning('PayPal subscription cancellation webhook - local subscription not found', [
                'subscription_id' => $subscriptionId,
            ]);
        }
    }

    /**
     * Handle subscription suspended event
     */
    private function handleSubscriptionSuspended(array $resource): void
    {
        $subscriptionId = $resource['id'] ?? NULL;
        if (!$subscriptionId) {
            Log::error('PayPal subscription suspended webhook missing subscription ID');

            return;
        }

        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update([
                'status'   => 'suspended',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'suspended_via_webhook_at' => now()->toISOString(),
                    'paypal_status'            => $resource['status'] ?? 'suspended',
                    'suspension_reason'        => $resource['status_change_note'] ?? 'Unknown',
                ]),
            ]);

            // Audit subscription suspension
            $this->auditService->logPayPalSubscription(
                action: 'suspended',
                subscription_id: $subscriptionId,
                user_id: $subscription->user_id,
                amount: NULL,
                currency: NULL,
                status: 'suspended',
                metadata: $resource,
            );

            Log::info('PayPal subscription suspended via webhook', [
                'subscription_id'       => $subscriptionId,
                'local_subscription_id' => $subscription->id,
                'reason'                => $resource['status_change_note'] ?? 'Unknown',
            ]);
        }
    }

    /**
     * Handle subscription expired event
     */
    private function handleSubscriptionExpired(array $resource): void
    {
        $subscriptionId = $resource['id'] ?? NULL;
        if (!$subscriptionId) {
            Log::error('PayPal subscription expired webhook missing subscription ID');

            return;
        }

        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update([
                'status'   => 'expired',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'expired_via_webhook_at' => now()->toISOString(),
                    'paypal_status'          => $resource['status'] ?? 'expired',
                ]),
            ]);

            Log::info('PayPal subscription expired via webhook', [
                'subscription_id'       => $subscriptionId,
                'local_subscription_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Handle payment capture completed event
     */
    private function handlePaymentCaptureCompleted(array $resource): void
    {
        $captureId = $resource['id'] ?? NULL;
        $amount = $resource['amount']['value'] ?? NULL;
        $currency = $resource['amount']['currency_code'] ?? NULL;

        if (!$captureId) {
            Log::error('PayPal payment capture completed webhook missing capture ID');

            return;
        }

        // Check if this is related to a subscription payment or one-time purchase
        $customId = $resource['custom_id'] ?? NULL;
        $invoiceId = $resource['invoice_id'] ?? NULL;

        // Audit payment capture completion
        $this->auditService->logPayPalTransaction(
            action: 'payment_captured',
            transaction_id: $captureId,
            amount: (float) ($amount ?? 0),
            currency: $currency ?? 'USD',
            status: 'completed',
            metadata: $resource,
        );

        Log::info('PayPal payment capture completed', [
            'capture_id' => $captureId,
            'amount'     => $amount,
            'currency'   => $currency,
            'custom_id'  => $customId,
            'invoice_id' => $invoiceId,
        ]);

        // Here you would typically update the purchase_attempts table
        // or handle the successful payment based on your business logic
    }

    /**
     * Handle payment capture denied event
     */
    private function handlePaymentCaptureDenied(array $resource): void
    {
        $captureId = $resource['id'] ?? NULL;
        $reason = $resource['status_details']['reason'] ?? 'Unknown';
        $amount = $resource['amount']['value'] ?? NULL;
        $currency = $resource['amount']['currency_code'] ?? NULL;

        // Audit payment denial
        $this->auditService->logPayPalTransaction(
            action: 'payment_denied',
            transaction_id: $captureId,
            amount: (float) ($amount ?? 0),
            currency: $currency ?? 'USD',
            status: 'denied',
            metadata: array_merge($resource, ['denial_reason' => $reason]),
        );

        Log::warning('PayPal payment capture denied', [
            'capture_id' => $captureId,
            'reason'     => $reason,
            'resource'   => $resource,
        ]);
    }

    /**
     * Handle payment capture refunded event
     */
    private function handlePaymentCaptureRefunded(array $resource): void
    {
        $refundId = $resource['id'] ?? NULL;
        $amount = $resource['amount']['value'] ?? NULL;
        $currency = $resource['amount']['currency_code'] ?? NULL;

        Log::info('PayPal payment refunded', [
            'refund_id' => $refundId,
            'amount'    => $amount,
            'currency'  => $currency,
        ]);
    }

    /**
     * Handle subscription payment completed event
     */
    private function handleSubscriptionPaymentCompleted(array $resource): void
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? NULL;
        $amount = $resource['amount']['total'] ?? NULL;
        $currency = $resource['amount']['currency'] ?? NULL;

        if (!$subscriptionId) {
            Log::error('PayPal subscription payment completed webhook missing subscription ID');

            return;
        }

        $paymentDetails = [
            'amount'         => $amount,
            'currency'       => $currency,
            'payment_id'     => $resource['id'] ?? NULL,
            'payment_method' => 'paypal',
        ];

        $subscription = $this->paypalSubscriptionService->processRenewal($subscriptionId, $paymentDetails);

        if ($subscription) {
            // Audit successful subscription payment
            $this->auditService->logPayPalSubscription(
                action: 'payment_completed',
                subscription_id: $subscriptionId,
                user_id: $subscription->user_id,
                amount: (float) ($amount ?? 0),
                currency: $currency,
                status: 'active',
                metadata: $resource,
            );

            Log::info('PayPal subscription payment processed via webhook', [
                'subscription_id'       => $subscriptionId,
                'local_subscription_id' => $subscription->id,
                'amount'                => $amount,
                'currency'              => $currency,
            ]);
        }
    }

    /**
     * Handle subscription payment failed event
     */
    private function handleSubscriptionPaymentFailed(array $resource): void
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? NULL;
        $reason = $resource['failure_reason'] ?? 'Unknown';
        $amount = $resource['amount']['total'] ?? NULL;
        $currency = $resource['amount']['currency'] ?? NULL;

        // Find the subscription for audit logging
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();

        // Audit failed payment
        $this->auditService->logFailedPayment(
            payment_method: 'paypal',
            transaction_id: $resource['id'] ?? NULL,
            user_id: $subscription?->user_id,
            amount: (float) ($amount ?? 0),
            currency: $currency ?? 'USD',
            failure_reason: $reason,
            metadata: $resource,
        );

        Log::warning('PayPal subscription payment failed', [
            'subscription_id' => $subscriptionId,
            'reason'          => $reason,
            'resource'        => $resource,
        ]);

        // Here you might want to notify the user or take other action
        // based on payment failure
    }

    /**
     * Extract PayPal headers from request
     */
    private function extractPayPalHeaders(Request $request): array
    {
        return [
            'PAYPAL-TRANSMISSION-ID'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            'PAYPAL-CERT-ID'           => $request->header('PAYPAL-CERT-ID'),
            'PAYPAL-AUTH-ALGO'         => $request->header('PAYPAL-AUTH-ALGO'),
            'PAYPAL-TRANSMISSION-SIG'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'PAYPAL-TRANSMISSION-TIME' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];
    }

    /**
     * Verify PayPal webhook signature
     */
    private function verifyWebhookSignature(array $headers, string $payload, ?string $webhookId): bool
    {
        // Skip verification in development if webhook ID is not configured
        if (app()->environment('local') && empty($webhookId)) {
            Log::warning('PayPal webhook signature verification skipped in development');

            return TRUE;
        }

        if (empty($webhookId)) {
            Log::error('PayPal webhook ID not configured');

            return FALSE;
        }

        try {
            return $this->paypalService->verifyWebhookSignature($headers, $payload, $webhookId);
        } catch (Exception $e) {
            Log::error('PayPal webhook signature verification error', [
                'error' => $e->getMessage(),
            ]);

            return FALSE;
        }
    }
}
