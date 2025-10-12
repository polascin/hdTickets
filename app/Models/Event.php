<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Event Model
 *
 * Represents sports events in the HD Tickets system
 */
class Event extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'venue',
        'venue_id',
        'date',
        'sport',
        'league',
        'home_team',
        'away_team',
        'popularity_score',
        'status',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'date'             => 'datetime',
        'popularity_score' => 'integer',
        'metadata'         => 'array',
    ];

    /**
     * Get the tickets for this event
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the venue for this event
     */
    public function eventVenue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now());
    }

    /**
     * Scope for events by sport
     */
    public function scopeBySport($query, string $sport)
    {
        return $query->where('sport', $sport);
    }

    /**
     * Scope for high demand events
     */
    public function scopeHighDemand($query)
    {
        return $query->where('popularity_score', '>', 80);
    }
}
