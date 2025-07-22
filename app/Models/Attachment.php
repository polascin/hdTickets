<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'ticket_attachments';

    protected $fillable = [
        'uuid',
        'ticket_id',
        'comment_id',
        'user_id',
        'filename',
        'filepath',
        'filetype',
        'filesize',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = Str::uuid();
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
     * Relationship: Ticket this attachment belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relationship: Comment this attachment belongs to
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Relationship: User who uploaded the attachment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by ticket
     */
    public function scopeByTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Scope: Filter by comment
     */
    public function scopeByComment($query, $commentId)
    {
        return $query->where('comment_id', $commentId);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Recent attachments
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Search attachments
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('filename', 'like', "%{$search}%");
    }
}
