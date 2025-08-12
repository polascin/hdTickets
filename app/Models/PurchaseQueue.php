<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

use function in_array;

class PurchaseQueue extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'uuid',
        'scraped_ticket_id',
        'selected_by_user_id',
        'user_id', // Add missing user_id field
        'status',
        'priority',
        'max_price',
        'quantity',
        'purchase_criteria',
        'notes',
        'scheduled_for',
        'expires_at',
        'started_processing_at',
        'completed_at',
        'transaction_id', // Add missing transaction_id field
        'metadata', // Add missing metadata field
    ];

    protected $casts = [
        'purchase_criteria'     => 'array',
        'scheduled_for'         => 'datetime',
        'expires_at'            => 'datetime',
        'started_processing_at' => 'datetime',
        'completed_at'          => 'datetime',
        'max_price'             => 'decimal:2',
        'metadata'              => 'array', // Add metadata casting
    ];

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_QUEUED,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get all available priorities
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ];
    }

    /**
     * Relationship: Scraped ticket that was selected for purchase
     */
    public function scrapedTicket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class);
    }

    /**
     * Relationship: User who selected this ticket for purchase
     */
    public function selectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }

    /**
     * Relationship: User (alias for selectedByUser for compatibility)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Purchase attempts for this queue item
     */
    public function purchaseAttempts(): HasMany
    {
        return $this->hasMany(PurchaseAttempt::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Latest purchase attempt
     */
    public function latestAttempt(): HasMany
    {
        return $this->hasMany(PurchaseAttempt::class)->latest();
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
     * Scope: Filter by priority
     *
     * @param mixed $query
     * @param mixed $priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Ready for processing
     *
     * @param mixed $query
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', self::STATUS_QUEUED)
            ->where(function ($q): void {
                $q->whereNull('scheduled_for')
                    ->orWhere('scheduled_for', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: High priority items
     *
     * @param mixed $query
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ]);
    }

    /**
     * Scope: Expired items
     *
     * @param mixed $query
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if queue item is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_QUEUED,
            self::STATUS_PROCESSING,
        ], TRUE);
    }

    /**
     * Check if queue item is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if queue item has failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if queue item is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if queue item is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if queue item is scheduled for future
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_for && $this->scheduled_for->isFuture();
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status'                => self::STATUS_PROCESSING,
            'started_processing_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status'       => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Cancel queue item
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED     => 'blue',
            self::STATUS_PROCESSING => 'yellow',
            self::STATUS_COMPLETED  => 'green',
            self::STATUS_FAILED     => 'red',
            self::STATUS_CANCELLED  => 'gray',
            default                 => 'gray',
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_CRITICAL => 'red',
            self::PRIORITY_URGENT   => 'orange',
            self::PRIORITY_HIGH     => 'yellow',
            self::PRIORITY_MEDIUM   => 'blue',
            self::PRIORITY_LOW      => 'gray',
            default                 => 'gray',
        };
    }

    /**
     * Get success rate for this queue item
     */
    public function getSuccessRate(): float
    {
        $totalAttempts = $this->purchaseAttempts()->count();
        if ($totalAttempts === 0) {
            return 0;
        }

        $successfulAttempts = $this->purchaseAttempts()->where('status', PurchaseAttempt::STATUS_SUCCESS)->count();

        return ($successfulAttempts / $totalAttempts) * 100;
    }

    /**
     * Get estimated processing time
     */
    public function getEstimatedProcessingTime(): ?string
    {
        if ($this->scheduled_for && $this->scheduled_for->isFuture()) {
            return $this->scheduled_for->diffForHumans();
        }

        return NULL;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($queue): void {
            if (empty($queue->uuid)) {
                $queue->uuid = (string) Str::uuid();
            }
        });
    }
}
