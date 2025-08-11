<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TicketAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sports_event_id',
        'alert_name',
        'max_price',
        'min_price',
        'min_quantity',
        'preferred_sections',
        'platforms',
        'status',
        'priority_score',
        'ml_prediction_data',
        'escalation_level',
        'last_escalated_at',
        'success_rate',
        'channel_preferences',
        'email_notifications',
        'sms_notifications',
        'auto_purchase',
        'last_checked_at',
        'triggered_at',
        'matches_found'
    ];

    protected $casts = [
        'max_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'min_quantity' => 'integer',
        'preferred_sections' => 'array',
        'platforms' => 'array',
        'priority_score' => 'integer',
        'ml_prediction_data' => 'array',
        'escalation_level' => 'integer',
        'success_rate' => 'decimal:4',
        'channel_preferences' => 'array',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'auto_purchase' => 'boolean',
        'last_escalated_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'triggered_at' => 'datetime'
    ];

    protected $dates = [
        'last_escalated_at',
        'last_checked_at',
        'triggered_at'
    ];

    protected static function boot()
    {
        parent::boot();

        // static::creating(function ($alert) {
        //     if (empty($alert->uuid)) {
        //         $alert->uuid = (string) Str::uuid();
        //     }
        // });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeNeedsCheck($query, $minutes = 15)
    {
        return $query->active()
                    ->where(function($q) use ($minutes) {
                        $q->whereNull('last_checked_at')
                          ->orWhere('last_checked_at', '<=', now()->subMinutes($minutes));
                    });
    }

    // Methods
    public function matchesTicket(ScrapedTicket $ticket): bool
    {
        // Check keywords match
        $keywords = strtolower($this->keywords);
        $eventTitle = strtolower($ticket->event_title);
        
        if (!str_contains($eventTitle, $keywords)) {
            return false;
        }

        // Check platform filter
        if ($this->platform && $this->platform !== $ticket->platform) {
            return false;
        }

        // Check price limit
        if ($this->max_price && $ticket->total_price > $this->max_price) {
            return false;
        }

        // Check additional filters if any
        if ($this->filters) {
            foreach ($this->filters as $key => $value) {
                switch ($key) {
                    case 'venue':
                        if (!str_contains(strtolower($ticket->venue), strtolower($value))) {
                            return false;
                        }
                        break;
                    case 'min_quantity':
                        if ($ticket->quantity_available < $value) {
                            return false;
                        }
                        break;
                    case 'section':
                        if ($ticket->section && !str_contains(strtolower($ticket->section), strtolower($value))) {
                            return false;
                        }
                        break;
                }
            }
        }

        return true;
    }

    public function incrementMatches(): void
    {
        // Increment the matches_found counter and update triggered_at
        $this->increment('matches_found');
        $this->update(['triggered_at' => now()]);
    }

    public function getFormattedMaxPriceAttribute(): ?string
    {
        return $this->max_price ? $this->currency . ' ' . number_format($this->max_price, 2) : null;
    }

    public function getLastCheckedAttribute(): ?string
    {
        return $this->last_checked_at ? $this->last_checked_at->diffForHumans() : 'Never';
    }

    public function getPlatformDisplayNameAttribute(): string
    {
        if (!$this->platform) {
            return 'All Platforms';
        }
        
        return match($this->platform) {
            'stubhub' => 'StubHub',
            'ticketmaster' => 'Ticketmaster',
            'viagogo' => 'Viagogo',
            default => ucfirst($this->platform)
        };
    }
}
