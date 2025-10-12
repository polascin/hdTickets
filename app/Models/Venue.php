<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Venue Model
 *
 * Represents sports venues in the HD Tickets system
 */
class Venue extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'city',
        'country',
        'address',
        'capacity',
        'image_url',
        'followers_count',
        'popularity_score',
        'venue_type',
        'status',
        'metadata',
    ];

    protected $casts = [
        'capacity'         => 'integer',
        'followers_count'  => 'integer',
        'popularity_score' => 'integer',
        'metadata'         => 'array',
    ];

    /**
     * Get all following relationships for this venue
     */
    public function followings(): MorphMany
    {
        return $this->morphMany(Following::class, 'followable');
    }

    /**
     * Get all events at this venue
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope for venues by city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope for venues by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('venue_type', $type);
    }

    /**
     * Scope for large venues
     */
    public function scopeLargeVenues($query)
    {
        return $query->where('capacity', '>', 50000);
    }

    /**
     * Scope for popular venues
     */
    public function scopePopular($query)
    {
        return $query->where('popularity_score', '>', 70)
                     ->orderByDesc('popularity_score');
    }

    /**
     * Scope for most followed venues
     */
    public function scopeMostFollowed($query)
    {
        return $query->orderByDesc('followers_count');
    }
}
