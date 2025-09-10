<?php declare(strict_types=1);

namespace App\Models;

use App\Events\TicketAvailabilityUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;

use function in_array;

/**
 * Sports Event Entry Ticket Model
 *
 * @property int           $id
 * @property string        $uuid
 * @property int|null      $requester_id
 * @property int|null      $assignee_id
 * @property int|null      $category_id
 * @property string        $title
 * @property string|null   $description
 * @property string        $status
 * @property string        $priority
 * @property Carbon|null   $due_date
 * @property Carbon|null   $last_activity_at
 * @property string|null   $platform
 * @property string|null   $external_id
 * @property float|null    $price
 * @property string|null   $currency
 * @property int|null      $available_quantity
 * @property string|null   $location
 * @property string|null   $venue
 * @property Carbon|null   $event_date
 * @property string|null   $event_type
 * @property string|null   $performer_artist
 * @property string|null   $seat_details
 * @property bool|null     $is_available
 * @property string|null   $ticket_url
 * @property array|null    $scraping_metadata
 * @property string|null   $sport
 * @property array|null    $additional_metadata
 * @property string|null   $source
 * @property array|null    $tags
 * @property Carbon|null   $resolved_at
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon|null   $deleted_at
 * @property User|null     $user
 * @property User|null     $requester
 * @property User|null     $assignedTo
 * @property User|null     $assignee
 * @property Category|null $category
 * @property string        $priority_color
 * @property string        $status_color
 * @property string        $formatted_title
 */
class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Status constants
    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITY_CRITICAL = 'critical';

    // Source constants
    public const SOURCE_EMAIL = 'email';

    public const SOURCE_PHONE = 'phone';

    public const SOURCE_WEB = 'web';

    public const SOURCE_CHAT = 'chat';

    public const SOURCE_API = 'api';

    protected $fillable = [
        'uuid',
        'requester_id',
        'assignee_id',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'last_activity_at',
        // Event/Concert ticket fields
        'platform',
        'external_id',
        'price',
        'currency',
        'available_quantity',
        'location',
        'venue',
        'event_date',
        'event_type',
        'performer_artist',
        'seat_details',
        'is_available',
        'ticket_url',
        'scraping_metadata',
        'sport',
        'additional_metadata',
        'source',
        'tags',
        'resolved_at',
    ];

    protected $casts = ['deleted_at' => 'datetime'];

    /**
     * Get the route key for the model
     */
    /**
     * Get  route key name
     */
    #[Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get all available statuses
     *
     * @return array<int, string>
     */
    /**
     * Get  statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get all available priorities
     *
     * @return array<int, string>
     */
    /**
     * Get  priorities
     */
    public static function getPriorities(): array
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
     * Get all available sources
     *
     * @return array<int, string>
     */
    /**
     * Get  sources
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_EMAIL,
            self::SOURCE_PHONE,
            self::SOURCE_WEB,
            self::SOURCE_CHAT,
            self::SOURCE_API,
        ];
    }

    /**
     * Relationship: User who created the ticket (requester)
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relationship: User who requested the ticket
     */
    /**
     * Requester
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relationship: User assigned to handle the ticket
     */
    /**
     * AssignedTo
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Relationship: User assigned to handle the ticket (alias)
     */
    /**
     * Assignee
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Relationship: Ticket category
     */
    /**
     * Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
     * Scope: Filter by assignee
     *
     * @param mixed $query
     * @param mixed $userId
     */
    public function scopeByAssignee($query, $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    /**
     * Scope: Filter by category
     *
     * @param mixed $query
     * @param mixed $categoryId
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Filter by user (creator)
     *
     * @param mixed $query
     * @param mixed $userId
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('requester_id', $userId);
    }

    /**
     * Scope: Filter by source
     *
     * @param mixed $query
     * @param mixed $source
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Open tickets
     *
     * @param mixed $query
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING,
        ]);
    }

    /**
     * Scope: Closed tickets
     *
     * @param mixed $query
     */
    public function scopeClosed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Scope: High priority tickets
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
     * Scope: Overdue tickets
     *
     * @param mixed $query
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [
                self::STATUS_RESOLVED,
                self::STATUS_CLOSED,
                self::STATUS_CANCELLED,
            ]);
    }

    /**
     * Scope: Recent tickets
     *
     * @param mixed $query
     * @param mixed $days
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Filter by date range
     *
     * @param mixed $query
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Search tickets
     *
     * @param mixed $query
     * @param mixed $search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('uuid', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: With tag
     *
     * @param mixed $query
     * @param mixed $tag
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Check if ticket is open
     */
    /**
     * Check if  open
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING,
        ], TRUE);
    }

    /**
     * Check if ticket is closed
     */
    /**
     * Check if  closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ], TRUE);
    }

    /**
     * Check if ticket is overdue
     */
    /**
     * Check if  overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->isOpen();
    }

    /**
     * Check if ticket is high priority
     */
    /**
     * Check if  high priority
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, [
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
            self::PRIORITY_CRITICAL,
        ], TRUE);
    }

    /**
     * Mark ticket as resolved
     */
    /**
     * Resolve
     */
    public function resolve(): bool
    {
        return $this->update([
            'status'      => self::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Assign ticket to user
     */
    /**
     * AssignTo
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'assignee_id' => $user->id,
        ]);
    }

    /**
     * Add tag to ticket
     */
    /**
     * AddTag
     */
    public function addTag(string $tag): bool
    {
        $tags = $this->tags ?? [];
        if (! in_array($tag, $tags, TRUE)) {
            $tags[] = $tag;

            return $this->update(['tags' => $tags]);
        }

        return FALSE;
    }

    /**
     * Remove tag from ticket
     */
    /**
     * RemoveTag
     */
    public function removeTag(string $tag): bool
    {
        $tags = $this->tags ?? [];
        $key = array_search($tag, $tags, TRUE);
        if ($key !== FALSE) {
            unset($tags[$key]);

            return $this->update(['tags' => array_values($tags)]);
        }

        return FALSE;
    }

    /**
     * Get  priority color attribute
     */
    protected function priorityColor(): Attribute
    {
        return Attribute::make(get: fn (): string => match ($this->priority) {
            self::PRIORITY_CRITICAL => 'red',
            self::PRIORITY_URGENT   => 'orange',
            self::PRIORITY_HIGH     => 'yellow',
            self::PRIORITY_MEDIUM   => 'blue',
            self::PRIORITY_LOW      => 'gray',
            default                 => 'gray',
        });
    }

    /**
     * Get  status color attribute
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(get: fn (): string => match ($this->status) {
            self::STATUS_OPEN        => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_PENDING     => 'orange',
            self::STATUS_RESOLVED    => 'green',
            self::STATUS_CLOSED      => 'gray',
            self::STATUS_CANCELLED   => 'red',
            default                  => 'gray',
        });
    }

    /**
     * Get  formatted title attribute
     */
    protected function formattedTitle(): Attribute
    {
        return Attribute::make(get: fn (): string => "#{$this->id} - {$this->title}");
    }

    /**
     * Boot the model
     */
    /**
     * Boot
     */
    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($ticket): void {
            if (empty($ticket->uuid)) {
                $ticket->uuid = Str::uuid();
            }
            $ticket->last_activity_at = now();
        });

        static::updating(function ($ticket): void {
            $ticket->last_activity_at = now();

            // Broadcast ticket availability updates
            if ($ticket->isDirty('available_quantity') || $ticket->isDirty('is_available')) {
                event(new TicketAvailabilityUpdated($ticket));
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date'            => 'datetime',
            'last_activity_at'    => 'datetime',
            'event_date'          => 'datetime',
            'resolved_at'         => 'datetime',
            'is_available'        => 'boolean',
            'price'               => 'decimal:2',
            'available_quantity'  => 'integer',
            'tags'                => 'array',
            'scraping_metadata'   => 'array',
            'additional_metadata' => 'array',
            'metadata'            => 'array',
        ];
    }
}
