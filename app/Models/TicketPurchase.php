<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;

use function in_array;

/**
 * Ticket Purchase Model - Tracks sports event ticket purchases
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property int         $ticket_id
 * @property int         $quantity
 * @property float       $unit_price
 * @property float       $total_amount
 * @property string      $currency
 * @property string      $status
 * @property string      $purchase_method
 * @property array       $purchase_options
 * @property Carbon      $initiated_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $failed_at
 * @property string|null $confirmation_number
 * @property string|null $external_reference
 * @property string|null $failure_reason
 * @property string|null $external_error_code
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 * @property User        $user
 * @property Ticket      $ticket
 */
class TicketPurchase extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Purchase status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    // Purchase method constants
    public const METHOD_AUTOMATED = 'automated';

    public const METHOD_MANUAL = 'manual';

    public const METHOD_API = 'api';

    protected $fillable = [
        'uuid',
        'user_id',
        'ticket_id',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'status',
        'purchase_method',
        'purchase_options',
        'initiated_at',
        'completed_at',
        'failed_at',
        'confirmation_number',
        'external_reference',
        'failure_reason',
        'external_error_code',
    ];

    /**
     * Get the route key for the model
     */
    #[Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
        ];
    }

    /**
     * Get all purchase methods
     */
    public static function getMethods(): array
    {
        return [
            self::METHOD_AUTOMATED,
            self::METHOD_MANUAL,
            self::METHOD_API,
        ];
    }

    /**
     * Relationship: User who made the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Ticket being purchased
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Scope: Filter by status
     *
     * @param mixed $query
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by user
     *
     * @param mixed $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by purchase method
     *
     * @param mixed $query
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('purchase_method', $method);
    }

    /**
     * Scope: Completed purchases
     *
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Failed purchases
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Pending purchases
     *
     * @param mixed $query
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Scope: Recent purchases
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: This month's purchases
     *
     * @param mixed $query
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }

    /**
     * Scope: Filter by date range
     *
     * @param mixed $query
     */
    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if purchase is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if purchase is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ], TRUE);
    }

    /**
     * Check if purchase has failed
     */
    public function hasFailed(): bool
    {
        return in_array($this->status, [
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ], TRUE);
    }

    /**
     * Check if purchase can be retried
     */
    public function canBeRetried(): bool
    {
        return $this->hasFailed()
               && !in_array($this->external_error_code, ['PAYMENT_DECLINED', 'TICKET_NO_LONGER_AVAILABLE'], TRUE);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_PENDING, self::STATUS_PROCESSING => 'yellow',
            self::STATUS_FAILED, self::STATUS_CANCELLED => 'red',
            self::STATUS_REFUNDED => 'purple',
            default               => 'gray',
        };
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED  => 'Completed',
            self::STATUS_FAILED     => 'Failed',
            self::STATUS_CANCELLED  => 'Cancelled',
            self::STATUS_REFUNDED   => 'Refunded',
            default                 => 'Unknown',
        };
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmount(): string
    {
        return number_format((float) $this->total_amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get purchase processing time
     */
    public function getProcessingTime(): ?int
    {
        if ($this->completed_at && $this->initiated_at) {
            return $this->initiated_at->diffInSeconds($this->completed_at);
        }

        if ($this->failed_at && $this->initiated_at) {
            return $this->initiated_at->diffInSeconds($this->failed_at);
        }

        return NULL;
    }

    /**
     * Mark purchase as completed
     */
    public function markAsCompleted(string $confirmationNumber, ?string $externalReference = NULL): bool
    {
        return $this->update([
            'status'              => self::STATUS_COMPLETED,
            'completed_at'        => now(),
            'confirmation_number' => $confirmationNumber,
            'external_reference'  => $externalReference,
        ]);
    }

    /**
     * Mark purchase as failed
     */
    public function markAsFailed(string $reason, ?string $errorCode = NULL): bool
    {
        return $this->update([
            'status'              => self::STATUS_FAILED,
            'failed_at'           => now(),
            'failure_reason'      => $reason,
            'external_error_code' => $errorCode,
        ]);
    }

    /**
     * Mark purchase as cancelled
     */
    public function markAsCancelled(string $reason = 'User cancelled'): bool
    {
        return $this->update([
            'status'         => self::STATUS_CANCELLED,
            'failed_at'      => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Get purchase summary for user
     */
    public function getSummary(): array
    {
        return [
            'purchase_id'         => $this->uuid,
            'status'              => $this->status,
            'status_label'        => $this->getStatusLabel(),
            'ticket_title'        => $this->ticket->title,
            'quantity'            => $this->quantity,
            'unit_price'          => $this->unit_price,
            'total_amount'        => $this->total_amount,
            'currency'            => $this->currency,
            'formatted_amount'    => $this->getFormattedTotalAmount(),
            'purchase_method'     => $this->purchase_method,
            'initiated_at'        => $this->initiated_at->toISOString(),
            'completed_at'        => $this->completed_at?->toISOString(),
            'confirmation_number' => $this->confirmation_number,
            'ticket_details'      => [
                'event_date'   => $this->ticket->event_date?->toISOString(),
                'venue'        => $this->ticket->venue,
                'location'     => $this->ticket->location,
                'seat_details' => $this->ticket->seat_details,
            ],
        ];
    }

    /**
     * Boot the model
     */
    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($purchase): void {
            if (empty($purchase->uuid)) {
                $purchase->uuid = Str::uuid()->toString();
            }

            if (empty($purchase->initiated_at)) {
                $purchase->initiated_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'quantity'         => 'integer',
            'unit_price'       => 'decimal:2',
            'total_amount'     => 'decimal:2',
            'purchase_options' => 'array',
            'initiated_at'     => 'datetime',
            'completed_at'     => 'datetime',
            'failed_at'        => 'datetime',
        ];
    }
}
