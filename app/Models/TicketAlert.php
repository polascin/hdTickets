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
        'uuid',
        'user_id',
        'name',
        'keywords',
        'platform',
        'max_price',
        'currency',
        'filters',
        'is_active',
        'email_notifications',
        'sms_notifications',
        'matches_found',
        'last_triggered_at'
    ];

    protected $casts = [
        'max_price' => 'decimal:2',
        'filters' => 'array',
        'is_active' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'matches_found' => 'integer',
        'last_triggered_at' => 'datetime'
    ];

    protected $dates = [
        'last_triggered_at'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($alert) {
            if (empty($alert->uuid)) {
                $alert->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
                        $q->whereNull('last_triggered_at')
                          ->orWhere('last_triggered_at', '<=', now()->subMinutes($minutes));
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
        $this->increment('matches_found');
        $this->update(['last_triggered_at' => now()]);
    }

    public function getFormattedMaxPriceAttribute(): ?string
    {
        return $this->max_price ? $this->currency . ' ' . number_format($this->max_price, 2) : null;
    }

    public function getLastCheckedAttribute(): ?string
    {
        return $this->last_triggered_at ? $this->last_triggered_at->diffForHumans() : 'Never';
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
