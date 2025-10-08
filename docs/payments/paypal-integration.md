# PayPal Integration Documentation

## Overview

HDTickets integrates with PayPal to provide secure payment processing for both subscription billing and individual ticket purchases. This integration supports both sandbox (development) and live (production) environments.

## Architecture

### Components

1. **PayPalService** (`app/Services/PayPal/PayPalService.php`)
   - Core PayPal SDK integration
   - Order creation, capture, and refund operations
   - Subscription management
   - Webhook signature verification

2. **PayPalSubscriptionService** (`app/Services/PayPal/PayPalSubscriptionService.php`)
   - Subscription lifecycle management
   - Recurring payment processing
   - Subscription status synchronisation

3. **PaymentService** (`app/Services/PaymentService.php`)
   - Orchestrates payment processing across providers
   - Handles transaction logging and error recovery
   - Manages customer records

4. **PayPalWebhookController** (`app/Http/Controllers/PayPalWebhookController.php`)
   - Receives and processes PayPal webhook events
   - Verifies webhook signatures for security
   - Updates local records based on PayPal events

## API Endpoints

### Subscription Endpoints

#### Create Subscription Payment
```
POST /subscription/payment
```

**Request Body:**
```json
{
    "plan_id": "premium",
    "payment_method": "paypal",
    "billing_cycle": "monthly"
}
```

**Response (Success):**
```json
{
    "success": true,
    "subscription_id": "I-BW452GLLEP1G",
    "approval_url": "https://www.paypal.com/webapps/billing/subscriptions/create?ba_token=BA-1234567890",
    "redirect_url": "/subscription/success"
}
```

#### Cancel Subscription
```
POST /subscription/cancel
```

**Request Body:**
```json
{
    "subscription_id": "I-BW452GLLEP1G",
    "reason": "Customer request"
}
```

### Ticket Purchase Endpoints

#### Create Ticket Purchase Order
```
POST /tickets/{ticket}/purchase
```

**Request Body:**
```json
{
    "quantity": 2,
    "payment_method": "paypal",
    "seat_preferences": {
        "section": "Lower level",
        "row": "A",
        "seat_type": "standard"
    },
    "special_requests": "Wheelchair accessible seats please",
    "accept_terms": true,
    "confirm_purchase": true
}
```

**Response (Success):**
```json
{
    "success": true,
    "order_id": "5O190127TN364715T",
    "approval_url": "https://www.paypal.com/checkoutnow?token=5O190127TN364715T",
    "purchase_attempt_id": 123
}
```

#### Capture Payment
```
POST /tickets/{ticket}/purchase/paypal/capture
```

**Request Body:**
```json
{
    "paypal_order_id": "5O190127TN364715T",
    "paypal_capture_id": "8MC585209K746392H"
}
```

#### Process Refund
```
POST /tickets/purchase/{attemptId}/refund
```

**Request Body:**
```json
{
    "amount": 50.00,
    "reason": "Customer request"
}
```

### Webhook Endpoint

#### PayPal Webhook Handler
```
POST /webhooks/paypal
```

**Headers Required:**
```
PAYPAL-TRANSMISSION-ID: {transmission_id}
PAYPAL-CERT-ID: {cert_id}
PAYPAL-AUTH-ALGO: SHA256withRSA
PAYPAL-TRANSMISSION-SIG: {signature}
PAYPAL-TRANSMISSION-TIME: {timestamp}
```

## Webhook Events

### Subscription Events

#### BILLING.SUBSCRIPTION.CREATED
Triggered when a subscription is successfully created.

**Payload Example:**
```json
{
    "id": "WH-4WR32APFA6648742G-67976317FL4543714",
    "event_type": "BILLING.SUBSCRIPTION.CREATED",
    "resource": {
        "id": "I-BW452GLLEP1G",
        "plan_id": "P-5ML4271244454362WXNWU5NQ",
        "status": "APPROVAL_PENDING",
        "subscriber": {
            "payer_id": "QYR5Z8XDVJNXQ",
            "email_address": "customer@example.com"
        },
        "billing_info": {
            "outstanding_balance": {
                "currency_code": "USD",
                "value": "0.00"
            }
        },
        "create_time": "2024-01-15T10:30:00Z",
        "update_time": "2024-01-15T10:30:00Z"
    }
}
```

#### BILLING.SUBSCRIPTION.ACTIVATED
Triggered when a subscription becomes active after approval.

#### BILLING.SUBSCRIPTION.CANCELLED
Triggered when a subscription is cancelled.

#### BILLING.SUBSCRIPTION.SUSPENDED
Triggered when a subscription is suspended due to payment failures.

#### PAYMENT.SALE.COMPLETED
Triggered when a subscription payment is successfully processed.

### Payment Events

#### PAYMENT.CAPTURE.COMPLETED
Triggered when a one-time payment is successfully captured.

**Payload Example:**
```json
{
    "id": "WH-94B42090HH8842219-2HC59197CT4848045",
    "event_type": "PAYMENT.CAPTURE.COMPLETED",
    "resource": {
        "id": "8MC585209K746392H",
        "status": "COMPLETED",
        "amount": {
            "currency_code": "USD",
            "value": "205.46"
        },
        "final_capture": true,
        "seller_protection": {
            "status": "ELIGIBLE",
            "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
            ]
        },
        "custom_id": "ticket_456",
        "invoice_id": "HDT_1705316400",
        "create_time": "2024-01-15T12:00:00Z",
        "update_time": "2024-01-15T12:00:00Z"
    }
}
```

#### PAYMENT.CAPTURE.DENIED
Triggered when a payment capture fails.

#### PAYMENT.CAPTURE.REFUNDED
Triggered when a payment is refunded.

## Configuration

### Environment Variables

#### Required Variables
```bash
# PayPal Mode
PAYPAL_MODE=sandbox  # or 'live' for production

# Sandbox Credentials
PAYPAL_SANDBOX_CLIENT_ID=your_sandbox_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_sandbox_client_secret

# Production Credentials
PAYPAL_LIVE_CLIENT_ID=your_live_client_id
PAYPAL_LIVE_CLIENT_SECRET=your_live_client_secret

# Webhook Configuration
PAYPAL_WEBHOOK_ID=your_webhook_id
PAYPAL_RECEIVER_EMAIL=walter.csoelle@gmail.com
```

#### Optional Variables
```bash
# Logging Level
PAYPAL_LOG_LEVEL=info

# Request Timeout (seconds)
PAYPAL_TIMEOUT=30

# Retry Configuration
PAYPAL_MAX_RETRIES=3
```

### Laravel Configuration

Configuration is stored in `config/services.php`:

```php
'paypal' => [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
        'base_url' => 'https://api.sandbox.paypal.com',
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
        'base_url' => 'https://api.paypal.com',
    ],
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    'receiver_email' => env('PAYPAL_RECEIVER_EMAIL'),
    'currency' => 'USD',
    'timeout' => env('PAYPAL_TIMEOUT', 30),
    'max_retries' => env('PAYPAL_MAX_RETRIES', 3),
],
```

## Testing

### Unit Tests

Run PayPal service unit tests:
```bash
vendor/bin/phpunit tests/Unit/Services/PayPal/PayPalServiceTest.php
```

### Feature Tests

Run PayPal subscription integration tests:
```bash
vendor/bin/phpunit tests/Feature/PayPal/PayPalSubscriptionTest.php
```

Run PayPal ticket purchase integration tests:
```bash
vendor/bin/phpunit tests/Feature/PayPal/PayPalTicketPurchaseTest.php
```

### Full PayPal Test Suite
```bash
vendor/bin/phpunit tests/Feature/PayPal/ tests/Unit/Services/PayPal/
```

### Sandbox Testing

1. **Create PayPal Developer Account**: Visit [PayPal Developer](https://developer.paypal.com/)
2. **Create Sandbox Application**: Generate client ID and secret
3. **Configure Environment**: Update `.env` with sandbox credentials
4. **Test Sandbox Transactions**: Use PayPal's test credit card numbers
5. **Verify Webhooks**: Use PayPal's webhook simulator

#### Test Credit Cards (Sandbox)
```
Visa: 4032035229458142
Mastercard: 5270533469265518
American Express: 374867965050128
```

## Deployment

### Pre-Deployment Checklist

#### 1. Environment Setup
- [ ] Production PayPal credentials configured
- [ ] Webhook endpoint URLs updated to production
- [ ] SSL certificates installed and verified
- [ ] Environment variables securely stored

#### 2. PayPal Configuration
- [ ] Live application created in PayPal Developer Console
- [ ] Webhook endpoints configured with live application
- [ ] Webhook events subscribed correctly
- [ ] Return URLs and cancel URLs updated

#### 3. Security Verification
- [ ] Webhook signature verification enabled
- [ ] IP whitelisting configured for PayPal servers
- [ ] Rate limiting enabled on webhook endpoints
- [ ] Audit logging configured for all PayPal transactions

#### 4. Database Migration
- [ ] PayPal-related database migrations executed
- [ ] Indexes created on PayPal ID columns
- [ ] Data backup created before migration

#### 5. Testing
- [ ] End-to-end payment flow tested in production
- [ ] Webhook delivery tested with live events
- [ ] Refund process verified
- [ ] Subscription lifecycle tested

### Production Deployment Steps

1. **Switch to Live Mode**:
   ```bash
   # Update environment
   PAYPAL_MODE=live
   
   # Clear configuration cache
   php artisan config:clear
   php artisan config:cache
   ```

2. **Verify PayPal Connection**:
   ```bash
   php artisan paypal:test-connection
   ```

3. **Create Webhook Subscriptions**:
   ```bash
   php artisan paypal:setup-webhooks
   ```

4. **Monitor Initial Transactions**:
   ```bash
   # Check PayPal transaction logs
   tail -f storage/logs/paypal.log
   
   # Monitor application logs
   tail -f storage/logs/laravel.log
   ```

### Post-Deployment Monitoring

#### Key Metrics to Monitor

1. **Transaction Success Rate**
   - Payment completion rate
   - Refund processing time
   - Webhook delivery success

2. **Error Tracking**
   - PayPal API errors
   - Webhook processing failures
   - Payment timeout incidents

3. **Performance Metrics**
   - Payment processing time
   - API response times
   - Database query performance

#### Monitoring Commands

```bash
# Check PayPal service status
php artisan paypal:health-check

# View recent PayPal transactions
php artisan paypal:transactions --recent

# Validate webhook configurations
php artisan paypal:verify-webhooks
```

## Error Handling

### Common Error Scenarios

#### 1. Payment Failures
- **Insufficient funds**: User redirected to PayPal to update payment method
- **Card declined**: Graceful error message with retry option
- **PayPal account restricted**: Customer service contact information provided

#### 2. Webhook Failures
- **Invalid signature**: Log security incident and ignore webhook
- **Duplicate events**: Implement idempotency checks
- **Processing errors**: Queue retry mechanism with exponential backoff

#### 3. API Connection Issues
- **Timeout errors**: Implement retry logic with circuit breaker
- **Rate limiting**: Implement request queuing and throttling
- **Maintenance mode**: Graceful degradation to alternative payment methods

### Error Response Handling

```php
try {
    $result = $this->paypalService->createOrder($orderData);
} catch (PayPalConnectionException $e) {
    // Network or connectivity issue
    $this->auditService->logPayPalError('connection_error', $e->getMessage());
    return $this->handlePaymentError('connection_failed');
} catch (PayPalApiException $e) {
    // PayPal API error
    $this->auditService->logPayPalError('api_error', $e->getPayPalResponse());
    return $this->handlePaymentError('payment_failed', $e->getPayPalMessage());
} catch (PayPalValidationException $e) {
    // Validation error
    return $this->handleValidationError($e->getValidationErrors());
}
```

## Troubleshooting

### Common Issues

#### 1. Webhook Not Receiving Events

**Symptoms**: PayPal events not processed, subscriptions stuck in pending

**Solutions**:
1. Verify webhook URL is accessible from internet
2. Check webhook signature verification
3. Validate webhook event subscriptions in PayPal Developer Console
4. Review server logs for processing errors

#### 2. Payment Processing Failures

**Symptoms**: Orders created but not captured, failed payment notifications

**Solutions**:
1. Check PayPal API credentials and permissions
2. Verify order status in PayPal Developer Console
3. Review payment capture logic and timing
4. Check for currency or amount formatting issues

#### 3. Subscription Status Synchronisation Issues

**Symptoms**: Local subscription status doesn't match PayPal

**Solutions**:
1. Implement subscription status sync command
2. Review webhook event processing logic
3. Check for missed webhook events
4. Manually sync problematic subscriptions

### Debugging Commands

```bash
# Enable PayPal debug mode
php artisan paypal:debug --enable

# Validate PayPal configuration
php artisan paypal:config-check

# Resend failed webhooks
php artisan paypal:retry-webhooks --failed

# Sync subscription status
php artisan paypal:sync-subscriptions --all
```

### Log Analysis

PayPal transaction logs are stored in `storage/logs/paypal.log`:

```bash
# View recent PayPal errors
grep "ERROR" storage/logs/paypal.log | tail -20

# Monitor webhook processing
grep "webhook" storage/logs/paypal.log | tail -10

# Check API response times
grep "api_response_time" storage/logs/paypal.log
```

## Security Considerations

### Data Protection

1. **PCI Compliance**: No credit card data is stored locally; all sensitive payment information is handled by PayPal
2. **Data Encryption**: PayPal IDs and transaction references are stored with database encryption
3. **Access Control**: PayPal administrative functions require admin role permissions
4. **Audit Trail**: All PayPal transactions are logged for compliance and debugging

### Webhook Security

1. **Signature Verification**: All webhook payloads are cryptographically verified
2. **IP Whitelisting**: Webhook endpoints only accept requests from PayPal IP ranges
3. **Rate Limiting**: Webhook endpoints are protected against DoS attacks
4. **Idempotency**: Duplicate webhook events are detected and ignored

### API Security

1. **Credential Management**: PayPal credentials are stored in environment variables
2. **Request Signing**: API requests include proper authentication headers
3. **SSL/TLS**: All PayPal communication uses HTTPS with certificate validation
4. **Token Expiration**: Access tokens are refreshed automatically before expiration

## Support

### PayPal Resources

- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [PayPal REST API Reference](https://developer.paypal.com/docs/api/)
- [PayPal Webhook Events](https://developer.paypal.com/docs/api/webhooks/v1/)

### Internal Support

- **Development Team**: Contact for integration issues
- **DevOps Team**: Contact for deployment and infrastructure issues
- **Customer Support**: Contact for payment-related customer issues

### Escalation Process

1. **Level 1**: Check logs and common troubleshooting steps
2. **Level 2**: Review PayPal Developer Console and webhook events
3. **Level 3**: Contact PayPal Merchant Technical Services
4. **Level 4**: Escalate to PayPal Developer Support

## Changelog

### Version 1.0.0 (2024-01-15)
- Initial PayPal integration implementation
- Subscription billing support
- One-time payment processing for ticket purchases
- Webhook event handling
- Comprehensive test suite
- Security and compliance measures