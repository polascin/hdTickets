<?php declare(strict_types=1);

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PurchaseAttempt extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $encryptionService;

    protected $fillable = [
        'uuid',
        'purchase_queue_id',
        'scraped_ticket_id',
        'user_id',
        'status',
        'platform',
        'attempted_price',
        'attempted_quantity',
        'transaction_id',
        'confirmation_number',
        'final_price',
        'fees',
        'platform_fee',
        'total_paid',
        'purchase_details',
        'error_message',
        'failure_reason',
        'response_data',
        'metadata',
        'started_at',
        'completed_at',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'purchase_details' => 'encrypted:array',
        'response_data'    => 'encrypted:array',
        'metadata'         => 'array',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'next_retry_at'    => 'datetime',
        'attempted_price'  => 'decimal:2',
        'final_price'      => 'decimal:2',
        'total_paid'       => 'decimal:2',
        'fees'             => 'decimal:2',
        'platform_fee'     => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->encryptionService = app(EncryptionService::class);
    }

    /**
     * Relationship: Purchase queue this attempt belongs to
     */
    /**
     * PurchaseQueue
     */
    public function purchaseQueue(): BelongsTo
    {
        return $this->belongsTo(PurchaseQueue::class);
    }

    /**
     * Relationship: Scraped ticket this attempt is based on
     */
    /**
     * ScrapedTicket
     */
    public function scrapedTicket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class);
    }

    /**
     * Relationship: User who made the purchase attempt
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Get the ticket (alias for scrapedTicket)
     */
    /**
     * Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class, 'scraped_ticket_id');
    }

    /**
     * Scope: Filter by status
     *
     * @param mixed $query
     * @param mixed $status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Successful attempts
     *
     * @param mixed $query
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope: Failed attempts
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if attempt is successful
     */
    /**
     * Check if  success
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if attempt is failed
     */
    /**
     * Check if  failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if attempt is in progress
     */
    /**
     * Check if  in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if attempt is pending
     */
    /**
     * Check if  pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark as in progress
     */
    /**
     * MarkInProgress
     */
    public function markInProgress(): bool
    {
        return $this->update([
            'status'     => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as successful
     *
     * @param mixed $finalPrice
     * @param mixed $fees
     * @param mixed $totalPaid
     */
    /**
     * MarkSuccessful
     *
     * @param mixed $finalPrice
     * @param mixed $fees
     * @param mixed $totalPaid
     */
    public function markSuccessful(string $transactionId, string $confirmationNumber, $finalPrice, $fees, $totalPaid): bool
    {
        return $this->update([
            'status'              => self::STATUS_SUCCESS,
            'transaction_id'      => $transactionId,
            'confirmation_number' => $confirmationNumber,
            'final_price'         => $finalPrice,
            'fees'                => $fees,
            'total_paid'          => $totalPaid,
            'completed_at'        => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    /**
     * MarkFailed
     */
    public function markFailed(string $errorMessage, ?string $failureReason = NULL): bool
    {
        return $this->update([
            'status'         => self::STATUS_FAILED,
            'error_message'  => $errorMessage,
            'failure_reason' => $failureReason,
            'completed_at'   => now(),
        ]);
    }

    /**
     * Cancel the attempt
     */
    /**
     * Check if can cel
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Encrypt sensitive financial fields
     *
     * @param mixed $value
     */
    /**
     * Set  transaction id attribute
     *
     * @param mixed $value
     */
    public function setTransactionIdAttribute($value): void
    {
        $this->attributes['transaction_id'] = $this->encryptionService->encrypt($value);
    }

    /**
     * Get  transaction id attribute
     *
     * @param mixed $value
     */
    public function getTransactionIdAttribute($value): int
    {
        return $this->encryptionService->decrypt($value);
    }

    /**
     * Set  confirmation number attribute
     *
     * @param mixed $value
     */
    public function setConfirmationNumberAttribute($value): void
    {
        $this->attributes['confirmation_number'] = $this->encryptionService->encrypt($value);
    }

    public function getConfirmationNumberAttribute($value)
    {
        return $this->encryptionService->decrypt($value);
    }

    /**
     * Set  purchase details attribute
     *
     * @param mixed $value
     */
    public function setPurchaseDetailsAttribute($value): void
    {
        $this->attributes['purchase_details'] = $this->encryptionService->encryptJsonData($value);
    }

    public function getPurchaseDetailsAttribute($value)
    {
        return $this->encryptionService->decryptJsonData($value);
    }

    /**
     * Set  response data attribute
     *
     * @param mixed $value
     */
    public function setResponseDataAttribute($value): void
    {
        $this->attributes['response_data'] = $this->encryptionService->encryptJsonData($value);
    }

    /**
     * Get  response data attribute
     *
     * @param mixed $value
     *
     * @return array<string, mixed>
     */
    public function getResponseDataAttribute($value): array
    {
        return $this->encryptionService->decryptJsonData($value);
    }

    /**
     * Boot
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($attempt): void {
            if (empty($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }
        });
    }
}
