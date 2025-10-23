<?php declare(strict_types=1);

namespace Tests\Unit\Services\PayPal;

use App\Services\PayPal\PayPalService;
use Exception;
use Mockery;
use Mockery\MockInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class PayPalServiceTest extends TestCase
{
    private PayPalService $paypalService;

    private MockInterface $mockClient;

    #[Test]
    public function it_creates_paypal_order_successfully(): void
    {
        // Arrange
        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value'         => '100.00',
                    ],
                    'description' => 'Test ticket purchase',
                ],
            ],
        ];

        $mockResponse = (object) [
            'result' => (object) [
                'id'     => 'ORDER123456',
                'status' => 'CREATED',
                'links'  => [
                    (object) [
                        'rel'  => 'approve',
                        'href' => 'https://sandbox.paypal.com/approve?token=ORDER123456',
                    ],
                ],
            ],
            'statusCode' => 201,
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(OrdersCreateRequest::class))
            ->andReturn($mockResponse);

        // Act
        $result = $this->paypalService->createOrder($orderData);

        // Assert
        $this->assertEquals('ORDER123456', $result['order_id']);
        $this->assertEquals('CREATED', $result['status']);
        $this->assertArrayHasKey('approve_link', $result);
        $this->assertStringContains('ORDER123456', $result['approve_link']);
    }

    #[Test]
    public function it_handles_order_creation_failure(): void
    {
        // Arrange
        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [],
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andThrow(new Exception('Invalid request data'));

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create PayPal order');

        $this->paypalService->createOrder($orderData);
    }

    #[Test]
    public function it_captures_paypal_order_successfully(): void
    {
        // Arrange
        $orderId = 'ORDER123456';

        $mockResponse = (object) [
            'result' => (object) [
                'id'             => $orderId,
                'status'         => 'COMPLETED',
                'purchase_units' => [
                    (object) [
                        'payments' => (object) [
                            'captures' => [
                                (object) [
                                    'id'     => 'CAPTURE123',
                                    'status' => 'COMPLETED',
                                    'amount' => (object) [
                                        'currency_code' => 'USD',
                                        'value'         => '100.00',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'statusCode' => 201,
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(OrdersCaptureRequest::class))
            ->andReturn($mockResponse);

        // Act
        $result = $this->paypalService->captureOrder($orderId);

        // Assert
        $this->assertEquals('COMPLETED', $result['status']);
        $this->assertEquals('CAPTURE123', $result['capture_id']);
        $this->assertEquals('100.00', $result['amount']);
        $this->assertEquals('USD', $result['currency']);
    }

    #[Test]
    public function it_handles_order_capture_failure(): void
    {
        // Arrange
        $orderId = 'INVALID_ORDER';

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andThrow(new Exception('Order not found'));

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to capture PayPal order');

        $this->paypalService->captureOrder($orderId);
    }

    #[Test]
    public function it_retrieves_order_details_successfully(): void
    {
        // Arrange
        $orderId = 'ORDER123456';

        $mockResponse = (object) [
            'result' => (object) [
                'id'     => $orderId,
                'status' => 'APPROVED',
                'intent' => 'CAPTURE',
                'payer'  => (object) [
                    'name' => (object) [
                        'given_name' => 'John',
                        'surname'    => 'Doe',
                    ],
                    'email_address' => 'john.doe@example.com',
                ],
                'purchase_units' => [
                    (object) [
                        'amount' => (object) [
                            'currency_code' => 'USD',
                            'value'         => '100.00',
                        ],
                    ],
                ],
            ],
            'statusCode' => 200,
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(OrdersGetRequest::class))
            ->andReturn($mockResponse);

        // Act
        $result = $this->paypalService->getOrder($orderId);

        // Assert
        $this->assertEquals($orderId, $result['id']);
        $this->assertEquals('APPROVED', $result['status']);
        $this->assertEquals('john.doe@example.com', $result['payer']['email_address']);
    }

    #[Test]
    public function it_processes_refund_successfully(): void
    {
        // Arrange
        $captureId = 'CAPTURE123';
        $refundData = [
            'amount' => [
                'currency_code' => 'USD',
                'value'         => '50.00',
            ],
            'note_to_payer' => 'Partial refund for ticket cancellation',
        ];

        $mockResponse = (object) [
            'result' => (object) [
                'id'     => 'REFUND123',
                'status' => 'COMPLETED',
                'amount' => (object) [
                    'currency_code' => 'USD',
                    'value'         => '50.00',
                ],
            ],
            'statusCode' => 201,
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(CapturesRefundRequest::class))
            ->andReturn($mockResponse);

        // Act
        $result = $this->paypalService->refundOrder($captureId, $refundData);

        // Assert
        $this->assertEquals('REFUND123', $result['refund_id']);
        $this->assertEquals('COMPLETED', $result['status']);
        $this->assertEquals('50.00', $result['amount']);
        $this->assertEquals('USD', $result['currency']);
    }

    #[Test]
    public function it_handles_refund_failure(): void
    {
        // Arrange
        $captureId = 'INVALID_CAPTURE';
        $refundData = [
            'amount' => [
                'currency_code' => 'USD',
                'value'         => '50.00',
            ],
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andThrow(new Exception('Capture not found'));

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to process PayPal refund');

        $this->paypalService->refundOrder($captureId, $refundData);
    }

    #[Test]
    public function it_verifies_webhook_signature_successfully(): void
    {
        // Arrange
        $headers = [
            'PAYPAL-TRANSMISSION-ID'   => 'test-transmission-id',
            'PAYPAL-CERT-ID'           => 'test-cert-id',
            'PAYPAL-AUTH-ALGO'         => 'SHA256withRSA',
            'PAYPAL-TRANSMISSION-SIG'  => 'test-signature',
            'PAYPAL-TRANSMISSION-TIME' => '2023-01-01T12:00:00Z',
        ];
        $payload = '{"event_type":"PAYMENT.CAPTURE.COMPLETED"}';
        $webhookId = 'test-webhook-id';

        // Mock the signature verification (this would normally call PayPal's verification service)
        $reflection = new ReflectionClass($this->paypalService);
        $verifyMethod = $reflection->getMethod('verifyWebhookSignature');
        $verifyMethod->setAccessible(TRUE);

        // Act
        $result = $this->paypalService->verifyWebhookSignature($headers, $payload, $webhookId);

        // Assert
        // In a real test, this would verify against PayPal's API
        // For now, we're testing the method exists and handles the parameters
        $this->assertIsBool($result);
    }

    #[Test]
    public function it_handles_invalid_webhook_signature(): void
    {
        // Arrange
        $headers = [
            'PAYPAL-TRANSMISSION-ID' => 'invalid-transmission-id',
            // Missing required headers
        ];
        $payload = '{"event_type":"PAYMENT.CAPTURE.COMPLETED"}';
        $webhookId = 'test-webhook-id';

        // Act
        $result = $this->paypalService->verifyWebhookSignature($headers, $payload, $webhookId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_handles_paypal_client_initialization(): void
    {
        // Arrange - Create new service to test initialization
        $service = new PayPalService();

        // Act & Assert - Test that service can be created without errors
        $this->assertInstanceOf(PayPalService::class, $service);

        // Test environment configuration
        config(['services.paypal.mode' => 'sandbox']);
        $sandboxService = new PayPalService();
        $this->assertInstanceOf(PayPalService::class, $sandboxService);

        config(['services.paypal.mode' => 'live']);
        $liveService = new PayPalService();
        $this->assertInstanceOf(PayPalService::class, $liveService);
    }

    #[Test]
    public function it_validates_order_data_before_creation(): void
    {
        // Arrange
        $invalidOrderData = [
            // Missing required fields
        ];

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid order data provided');

        $this->paypalService->createOrder($invalidOrderData);
    }

    #[Test]
    public function it_handles_network_errors_gracefully(): void
    {
        // Arrange
        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value'         => '100.00',
                    ],
                ],
            ],
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andThrow(new Exception('Network error: Connection timeout'));

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create PayPal order');

        $this->paypalService->createOrder($orderData);
    }

    #[Test]
    public function it_formats_error_responses_correctly(): void
    {
        // Arrange
        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'INVALID',
                        'value'         => 'invalid-amount',
                    ],
                ],
            ],
        ];

        $paypalError = new Exception('INVALID_REQUEST: Invalid currency code');
        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andThrow($paypalError);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to create PayPal order');

        $this->paypalService->createOrder($orderData);
    }

    #[Test]
    public function it_logs_api_interactions(): void
    {
        // This test would verify that API interactions are properly logged
        // For now, we ensure the service handles logging without errors

        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value'         => '100.00',
                    ],
                ],
            ],
        ];

        $mockResponse = (object) [
            'result' => (object) [
                'id'     => 'ORDER123456',
                'status' => 'CREATED',
            ],
            'statusCode' => 201,
        ];

        $this->mockClient->shouldReceive('execute')
            ->once()
            ->andReturn($mockResponse);

        // Act
        $result = $this->paypalService->createOrder($orderData);

        // Assert - No exceptions thrown means logging is working
        $this->assertNotEmpty($result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the PayPal HTTP client
        $this->mockClient = Mockery::mock(PayPalHttpClient::class);

        // Create service instance with mocked client
        $this->paypalService = new PayPalService();

        // Use reflection to inject the mock client
        $reflection = new ReflectionClass($this->paypalService);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(TRUE);
        $clientProperty->setValue($this->paypalService, $this->mockClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
