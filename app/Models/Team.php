<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Team Model
 *
 * Represents sports teams in the HD Tickets system
 */
class Team extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'sport',
        'league',
        'country',
        'city',
        'logo_url',
        'followers_count',
        'popularity_score',
        'status',
        'metadata',
    ];

    protected $casts = [
        'followers_count'  => 'integer',
        'popularity_score' => 'integer',
        'metadata'         => 'array',
    ];

    /**
     * Get all following relationships for this team
     */
    public function followings(): MorphMany
    {
        return $this->morphMany(Following::class, 'followable');
    }

    /**
     * Scope for teams by sport
     *
     * @param mixed $query
     */
    public function scopeBySport($query, string $sport)
    {
        return $query->where('sport', $sport);
    }

    /**
     * Scope for teams by league
     *
     * @param mixed $query
     */
    public function scopeByLeague($query, string $league)
    {
        return $query->where('league', $league);
    }

    /**
     * Scope for popular teams
     *
     * @param mixed $query
     */
    public function scopePopular($query)
    {
        return $query->where('popularity_score', '>', 70)
            ->orderByDesc('popularity_score');
    }

    /**
     * Scope for most followed teams
     *
     * @param mixed $query
     */
    public function scopeMostFollowed($query)
    {
        return $query->orderByDesc('followers_count');
    }
}
