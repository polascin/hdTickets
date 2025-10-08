<?php declare(strict_types=1);

namespace App\Services\PayPal;

use App\Models\PaymentPlan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Controllers\OrdersController;
use PaypalServerSdkLib\Controllers\PaymentsController;
use PaypalServerSdkLib\Controllers\SubscriptionsController;
use PaypalServerSdkLib\Controllers\WebhooksController;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\BillingCycleBuilder;
use PaypalServerSdkLib\Models\Builders\FrequencyBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentMethodBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentPreferencesBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\PlanBuilder;
use PaypalServerSdkLib\Models\Builders\PricingSchemeBuilder;
use PaypalServerSdkLib\Models\Builders\ProductBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\SubscriptionBuilder;
use PaypalServerSdkLib\Models\Builders\SubscriptionRequestPostBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\CurrencyCode;
use PaypalServerSdkLib\Models\Interval;
use PaypalServerSdkLib\Models\ProductCategory;
use PaypalServerSdkLib\Models\ProductType;
use PaypalServerSdkLib\Models\SetupFeeFailureAction;
use PaypalServerSdkLib\Models\TenureType;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

class PayPalService
{
    private PaypalServerSdkClient $client;
    private OrdersController $ordersController;
    private PaymentsController $paymentsController;
    private SubscriptionsController $subscriptionsController;
    private WebhooksController $webhooksController;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize PayPal client based on environment configuration
     */
    private function initializeClient(): void
    {
        $mode = config('services.paypal.mode', 'sandbox');
        $clientId = config("services.paypal.{$mode}.client_id");
        $clientSecret = config("services.paypal.{$mode}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("PayPal {$mode} credentials not configured properly");
        }

        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init($clientId, $clientSecret)
            )
            ->environment($mode === 'production' ? Environment::PRODUCTION : Environment::SANDBOX)
            ->build();

        $this->ordersController = new OrdersController($this->client);
        $this->paymentsController = new PaymentsController($this->client);
        $this->subscriptionsController = new SubscriptionsController($this->client);
        $this->webhooksController = new WebhooksController($this->client);
    }

    /**
     * Create PayPal order for one-time payment (e.g., ticket purchases)
     */
    public function createOrder(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        try {
            $orderRequest = OrderRequestBuilder::init(
                CheckoutPaymentIntent::CAPTURE,
                [
                    PurchaseUnitRequestBuilder::init(
                        AmountWithBreakdownBuilder::init($currency, (string)$amount)
                    )->build(),
                ]
            )->build();

            $response = $this->ordersController->ordersCreate($orderRequest);
            $order = json_decode($response->getBody(), true);

            Log::info('PayPal order created', [
                'order_id' => $order['id'],
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata,
            ]);

            return [
                'id' => $order['id'],
                'status' => $order['status'],
                'amount' => $amount,
                'currency' => $currency,
                'approve_link' => $this->extractApproveLink($order['links'] ?? []),
                'capture_link' => $this->extractCaptureLink($order['links'] ?? []),
            ];
        } catch (Exception $e) {
            Log::error('PayPal order creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency,
            ]);

            throw new Exception('Failed to create PayPal order: ' . $e->getMessage());
        }
    }

    /**
     * Capture PayPal order payment
     */
    public function captureOrder(string $orderId): array
    {
        try {
            $response = $this->ordersController->ordersCapture($orderId);
            $capture = json_decode($response->getBody(), true);

            Log::info('PayPal order captured', [
                'order_id' => $orderId,
                'capture_id' => $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                'status' => $capture['status'],
            ]);

            return [
                'order_id' => $orderId,
                'capture_id' => $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                'status' => $capture['status'],
                'amount' => $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
                'currency' => $capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('PayPal order capture failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to capture PayPal order: ' . $e->getMessage());
        }
    }

    /**
     * Refund PayPal capture
     */
    public function refundCapture(string $captureId, float $amount, string $currency = 'USD'): array
    {
        try {
            $refundRequest = [
                'amount' => [
                    'value' => (string)$amount,
                    'currency_code' => $currency,
                ],
            ];

            $response = $this->paymentsController->capturesRefund($captureId, $refundRequest);
            $refund = json_decode($response->getBody(), true);

            Log::info('PayPal refund processed', [
                'capture_id' => $captureId,
                'refund_id' => $refund['id'],
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'refund_id' => $refund['id'],
                'status' => $refund['status'],
                'amount' => $amount,
                'currency' => $currency,
            ];
        } catch (Exception $e) {
            Log::error('PayPal refund failed', [
                'capture_id' => $captureId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to refund PayPal capture: ' . $e->getMessage());
        }
    }

    /**
     * Create PayPal subscription product
     */
    public function createProduct(string $name, string $description, string $category = 'SOFTWARE'): string
    {
        try {
            $product = ProductBuilder::init(
                $name,
                ProductType::SERVICE
            )
                ->description($description)
                ->category($this->mapProductCategory($category))
                ->build();

            $response = $this->subscriptionsController->productsCreate($product);
            $productData = json_decode($response->getBody(), true);

            Log::info('PayPal product created', [
                'product_id' => $productData['id'],
                'name' => $name,
            ]);

            return $productData['id'];
        } catch (Exception $e) {
            Log::error('PayPal product creation failed', [
                'error' => $e->getMessage(),
                'name' => $name,
            ]);

            throw new Exception('Failed to create PayPal product: ' . $e->getMessage());
        }
    }

    /**
     * Create PayPal subscription plan
     */
    public function createSubscriptionPlan(PaymentPlan $paymentPlan, string $productId): string
    {
        try {
            $plan = PlanBuilder::init(
                $productId,
                $paymentPlan->name,
                [
                    BillingCycleBuilder::init(
                        FrequencyBuilder::init(
                            $this->mapInterval($paymentPlan->billing_cycle),
                            1
                        ),
                        TenureType::REGULAR,
                        1,
                        PricingSchemeBuilder::init(
                            MoneyBuilder::init(CurrencyCode::USD, (string)$paymentPlan->price)
                        )
                    )->build(),
                ]
            )
                ->paymentPreferences(
                    PaymentPreferencesBuilder::init()
                        ->setupFeeFailureAction(SetupFeeFailureAction::CANCEL)
                        ->paymentFailureThreshold(3)
                        ->build()
                )
                ->build();

            $response = $this->subscriptionsController->plansCreate($plan);
            $planData = json_decode($response->getBody(), true);

            Log::info('PayPal subscription plan created', [
                'plan_id' => $planData['id'],
                'payment_plan_id' => $paymentPlan->id,
                'name' => $paymentPlan->name,
                'price' => $paymentPlan->price,
            ]);

            return $planData['id'];
        } catch (Exception $e) {
            Log::error('PayPal subscription plan creation failed', [
                'error' => $e->getMessage(),
                'payment_plan_id' => $paymentPlan->id,
            ]);

            throw new Exception('Failed to create PayPal subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Create PayPal subscription
     */
    public function createSubscription(string $planId, User $user): array
    {
        try {
            $subscription = SubscriptionRequestPostBuilder::init(
                $planId
            )
                ->subscriber([
                    'name' => [
                        'given_name' => $user->name,
                        'surname' => $user->surname ?? '',
                    ],
                    'email_address' => $user->email,
                ])
                ->build();

            $response = $this->subscriptionsController->subscriptionsCreate($subscription);
            $subscriptionData = json_decode($response->getBody(), true);

            Log::info('PayPal subscription created', [
                'subscription_id' => $subscriptionData['id'],
                'plan_id' => $planId,
                'user_id' => $user->id,
                'status' => $subscriptionData['status'],
            ]);

            return [
                'id' => $subscriptionData['id'],
                'status' => $subscriptionData['status'],
                'plan_id' => $planId,
                'approve_link' => $this->extractApproveLink($subscriptionData['links'] ?? []),
            ];
        } catch (Exception $e) {
            Log::error('PayPal subscription creation failed', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'user_id' => $user->id,
            ]);

            throw new Exception('Failed to create PayPal subscription: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription details
     */
    public function getSubscriptionDetails(string $subscriptionId): array
    {
        try {
            $response = $this->subscriptionsController->subscriptionsGet($subscriptionId);
            $subscription = json_decode($response->getBody(), true);

            return [
                'id' => $subscription['id'],
                'status' => $subscription['status'],
                'plan_id' => $subscription['plan_id'],
                'start_time' => $subscription['start_time'] ?? null,
                'next_billing_time' => $subscription['billing_info']['next_billing_time'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get PayPal subscription details', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to get subscription details: ' . $e->getMessage());
        }
    }

    /**
     * Cancel PayPal subscription
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'User requested cancellation'): bool
    {
        try {
            $cancelRequest = [
                'reason' => $reason,
            ];

            $this->subscriptionsController->subscriptionsCancel($subscriptionId, $cancelRequest);

            Log::info('PayPal subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('PayPal subscription cancellation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to cancel PayPal subscription: ' . $e->getMessage());
        }
    }

    /**
     * Verify PayPal webhook signature
     */
    public function verifyWebhookSignature(array $headers, string $body, string $webhookId): bool
    {
        try {
            $verifyRequest = [
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'cert_id' => $headers['PAYPAL-CERT-ID'] ?? '',
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($body, true),
            ];

            $response = $this->webhooksController->verifyWebhookSignature($verifyRequest);
            $verification = json_decode($response->getBody(), true);

            return ($verification['verification_status'] ?? '') === 'SUCCESS';
        } catch (Exception $e) {
            Log::error('PayPal webhook verification failed', [
                'error' => $e->getMessage(),
                'webhook_id' => $webhookId,
            ]);

            return false;
        }
    }

    /**
     * Extract approve link from PayPal links array
     */
    private function extractApproveLink(array $links): ?string
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }

        return null;
    }

    /**
     * Extract capture link from PayPal links array
     */
    private function extractCaptureLink(array $links): ?string
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'capture') {
                return $link['href'];
            }
        }

        return null;
    }

    /**
     * Map billing cycle to PayPal interval
     */
    private function mapInterval(string $billingCycle): string
    {
        return match (strtolower($billingCycle)) {
            'monthly' => Interval::MONTH,
            'yearly', 'annual' => Interval::YEAR,
            'weekly' => Interval::WEEK,
            'daily' => Interval::DAY,
            default => Interval::MONTH,
        };
    }

    /**
     * Map product category to PayPal enum
     */
    private function mapProductCategory(string $category): string
    {
        return match (strtoupper($category)) {
            'SOFTWARE' => ProductCategory::SOFTWARE,
            'DIGITAL_MEDIA' => ProductCategory::DIGITAL_MEDIA_BOOKS_MOVIES_MUSIC,
            'SERVICES' => ProductCategory::SERVICE,
            default => ProductCategory::SOFTWARE,
        };
    }

    /**
     * Get PayPal client for advanced operations
     */
    public function getClient(): PaypalServerSdkClient
    {
        return $this->client;
    }
}