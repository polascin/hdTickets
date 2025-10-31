<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function in_array;
use function sprintf;

/**
 * Payment Model
 *
 * Tracks payment transactions including:
 * - Payment processing details
 * - Stripe transaction references
 * - Payment status and amounts
 * - Refund and chargeback tracking
 * - Invoice and receipt data
 */
class Payment extends Model
{
    use HasFactory;

    // Payment statuses
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    // Payment method types
    public const METHOD_CARD = 'card';

    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    public const METHOD_PAYPAL = 'paypal';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'stripe_payment_intent_id',
        'stripe_invoice_id',
        'amount',
        'currency',
        'status',
        'payment_method_type',
        'payment_method_details',
        'description',
        'invoice_number',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'refund_amount'          => 'decimal:2',
        'payment_method_details' => 'array',
        'metadata'               => 'array',
        'paid_at'                => 'datetime',
        'refunded_at'            => 'datetime',
    ];

    protected $dates = [
        'paid_at',
        'refunded_at',
    ];

    /**
     * Get the user that owns the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the payment
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if payment was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment was refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED], TRUE);
    }

    /**
     * Get payment status badge info
     */
    public function getStatusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_SUCCEEDED => [
                'text'  => 'Paid',
                'color' => 'green',
                'icon'  => 'check-circle',
            ],
            self::STATUS_PENDING => [
                'text'  => 'Pending',
                'color' => 'yellow',
                'icon'  => 'clock',
            ],
            self::STATUS_FAILED => [
                'text'  => 'Failed',
                'color' => 'red',
                'icon'  => 'times-circle',
            ],
            self::STATUS_CANCELLED => [
                'text'  => 'Cancelled',
                'color' => 'gray',
                'icon'  => 'ban',
            ],
            self::STATUS_REFUNDED => [
                'text'  => 'Refunded',
                'color' => 'blue',
                'icon'  => 'undo',
            ],
            self::STATUS_PARTIALLY_REFUNDED => [
                'text'  => 'Partial Refund',
                'color' => 'orange',
                'icon'  => 'undo',
            ],
            default => [
                'text'  => 'Unknown',
                'color' => 'gray',
                'icon'  => 'question-circle',
            ],
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmount(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get formatted refund amount
     */
    public function getFormattedRefundAmount(): string
    {
        if (!$this->refund_amount) {
            return '$0.00';
        }

        return '$' . number_format($this->refund_amount, 2);
    }

    /**
     * Get payment method description
     */
    public function getPaymentMethodDescription(): string
    {
        $details = $this->payment_method_details ?? [];

        return match ($this->payment_method_type) {
            self::METHOD_CARD => sprintf(
                '**** **** **** %s (%s)',
                $details['last4'] ?? '****',
                strtoupper($details['brand'] ?? 'card'),
            ),
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_PAYPAL        => 'PayPal',
            default                    => 'Unknown Payment Method',
        };
    }

    /**
     * Get net amount after refunds
     */
    public function getNetAmount(): float
    {
        return $this->amount - ($this->refund_amount ?? 0);
    }

    /**
     * Scope to get successful payments
     *
     * @param mixed $query
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCEEDED);
    }

    /**
     * Scope to get failed payments
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get refunded payments
     *
     * @param mixed $query
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Scope to get payments for specific date range
     *
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get payments by amount range
     *
     * @param mixed $query
     * @param mixed $minAmount
     * @param mixed $maxAmount
     */
    public function scopeAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }
}
