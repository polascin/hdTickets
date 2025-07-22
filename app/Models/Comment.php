<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'ticket_comments';

    protected $fillable = [
        'uuid',
        'ticket_id',
        'user_id',
        'content',
        'type',
        'is_internal',
        'is_solution',
        'metadata',
        'edited_at',
        'edited_by'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_solution' => 'boolean',
        'metadata' => 'array',
        'edited_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Comment types
    const TYPE_COMMENT = 'comment';
    const TYPE_NOTE = 'note';
    const TYPE_REPLY = 'reply';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_SYSTEM = 'system';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = Str::uuid();
            }
        });

        static::created(function ($comment) {
            // Update ticket's last activity and first response time if needed
            if ($comment->ticket) {
                $updates = ['last_activity_at' => now()];
                
                // Set first response time if this is the first non-internal comment by staff
                if (!$comment->is_internal && 
                    $comment->user && 
                    $comment->user->isAgent() || $comment->user->isAdmin() &&
                    !$comment->ticket->first_response_at) {
                    $updates['first_response_at'] = now();
                }
                
                $comment->ticket->update($updates);
            }
        });

        static::updated(function ($comment) {
            // Track edit information
            if ($comment->isDirty('content') && !$comment->edited_at) {
                $comment->update([
                    'edited_at' => now(),
                    'edited_by' => auth()->id()
                ]);
            }
        });
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Get all available comment types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_COMMENT,
            self::TYPE_NOTE,
            self::TYPE_REPLY,
            self::TYPE_STATUS_CHANGE,
            self::TYPE_ASSIGNMENT,
            self::TYPE_SYSTEM,
        ];
    }

    /**
     * Relationship: Ticket this comment belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relationship: User who created the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: User who edited the comment
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Relationship: Comment attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Scope: Filter by ticket
     */
    public function scopeByTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Public comments only (not internal)
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope: Internal comments only
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope: Solution comments
     */
    public function scopeSolutions($query)
    {
        return $query->where('is_solution', true);
    }

    /**
     * Scope: Recent comments
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Search comments
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('content', 'like', "%{$search}%");
    }

    /**
     * Scope: Edited comments
     */
    public function scopeEdited($query)
    {
        return $query->whereNotNull('edited_at');
    }

    /**
     * Scope: Comments by staff (agents and admins)
     */
    public function scopeByStaff($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT]);
        });
    }

    /**
     * Scope: Comments by customers
     */
    public function scopeByCustomers($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('role', User::ROLE_CUSTOMER);
        });
    }

    /**
     * Scope: System-generated comments
     */
    public function scopeSystem($query)
    {
        return $query->where('type', self::TYPE_SYSTEM);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if comment is internal
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Check if comment is public
     */
    public function isPublic(): bool
    {
        return !$this->is_internal;
    }

    /**
     * Check if comment is marked as solution
     */
    public function isSolution(): bool
    {
        return $this->is_solution;
    }

    /**
     * Check if comment has been edited
     */
    public function isEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    /**
     * Check if comment is by staff member
     */
    public function isByStaff(): bool
    {
        return $this->user && ($this->user->isAgent() || $this->user->isAdmin());
    }

    /**
     * Check if comment is by customer
     */
    public function isByCustomer(): bool
    {
        return $this->user && $this->user->isCustomer();
    }

    /**
     * Check if comment is system generated
     */
    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Mark comment as solution
     */
    public function markAsSolution(): bool
    {
        // First, unmark any other solutions in the same ticket
        self::where('ticket_id', $this->ticket_id)
            ->where('id', '!=', $this->id)
            ->update(['is_solution' => false]);

        return $this->update(['is_solution' => true]);
    }

    /**
     * Unmark comment as solution
     */
    public function unmarkAsSolution(): bool
    {
        return $this->update(['is_solution' => false]);
    }

    /**
     * Get the excerpt of the comment content
     */
    public function getExcerptAttribute($length = 100): string
    {
        return Str::limit(strip_tags($this->content), $length);
    }

    /**
     * Get comment type color for UI
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_COMMENT => 'blue',
            self::TYPE_NOTE => 'gray',
            self::TYPE_REPLY => 'green',
            self::TYPE_STATUS_CHANGE => 'yellow',
            self::TYPE_ASSIGNMENT => 'orange',
            self::TYPE_SYSTEM => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get formatted created time
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get user display name
     */
    public function getUserDisplayNameAttribute(): string
    {
        if (!$this->user) {
            return 'System';
        }

        $name = $this->user->first_name && $this->user->last_name 
            ? $this->user->first_name . ' ' . $this->user->last_name
            : $this->user->username;

        return $name;
    }

    /**
     * Create a system comment for ticket status change
     */
    public static function createSystemComment(
        Ticket $ticket, 
        string $message, 
        ?User $user = null,
        array $metadata = []
    ): self {
        return self::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'content' => $message,
            'type' => self::TYPE_SYSTEM,
            'is_internal' => false,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a status change comment
     */
    public static function createStatusChangeComment(
        Ticket $ticket,
        string $oldStatus,
        string $newStatus,
        ?User $user = null
    ): self {
        $message = "Status changed from '{$oldStatus}' to '{$newStatus}'";
        
        return self::createSystemComment($ticket, $message, $user, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'change_type' => 'status'
        ]);
    }

    /**
     * Create an assignment comment
     */
    public static function createAssignmentComment(
        Ticket $ticket,
        ?User $assignedTo = null,
        ?User $assignedBy = null
    ): self {
        $message = $assignedTo 
            ? "Ticket assigned to {$assignedTo->username}"
            : "Ticket unassigned";
            
        return self::createSystemComment($ticket, $message, $assignedBy, [
            'assigned_to' => $assignedTo?->id,
            'assigned_by' => $assignedBy?->id,
            'change_type' => 'assignment'
        ]);
    }
}
