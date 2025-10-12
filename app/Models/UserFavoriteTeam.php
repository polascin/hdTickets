<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavoriteTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sport_type',
        'team_name',
        'team_slug',
        'league',
        'team_logo_url',
        'team_city',
        'aliases',
        'email_alerts',
        'push_alerts',
        'sms_alerts',
        'priority',
    ];

    /**
     * Get the user that owns this favorite team
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by sport type
     *
     * @param mixed $query
     */
    public function scopeBySport($query, string $sportType)
    {
        return $query->where('sport_type', $sportType);
    }

    /**
     * Scope to filter by league
     *
     * @param mixed $query
     */
    public function scopeByLeague($query, string $league)
    {
        return $query->where('league', $league);
    }

    /**
     * Scope to filter by priority
     *
     * @param mixed $query
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for high priority teams
     *
     * @param mixed $query
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 4);
    }

    /**
     * Scope for teams with email alerts enabled
     *
     * @param mixed $query
     */
    public function scopeWithEmailAlerts($query)
    {
        return $query->where('email_alerts', true);
    }

    /**
     * Scope for teams with push alerts enabled
     *
     * @param mixed $query
     */
    public function scopeWithPushAlerts($query)
    {
        return $query->where('push_alerts', true);
    }

    /**
     * Search teams by name or city
     *
     * @param mixed $query
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('team_name', 'LIKE', "%{$term}%")
                ->orWhere('team_city', 'LIKE', "%{$term}%")
                ->orWhereJsonContains('aliases', $term);
        });
    }

    /**
     * Generate team slug from name and city
     */
    /**
     * GenerateSlug
     */
    public function generateSlug(): string
    {
        $name = $this->team_city ? "{$this->team_city} {$this->team_name}" : $this->team_name;

        return strtolower(str_replace([' ', '&', '.'], ['-', 'and', ''], $name));
    }

    /**
     * Get all available sports
     */
    /**
     * Get  available sports
     */
    public static function getAvailableSports(): array
    {
        return [
            'football'           => 'Football (NFL)',
            'basketball'         => 'Basketball (NBA)',
            'baseball'           => 'Baseball (MLB)',
            'hockey'             => 'Hockey (NHL)',
            'soccer'             => 'Soccer (MLS)',
            'college_football'   => 'College Football',
            'college_basketball' => 'College Basketball',
            'tennis'             => 'Tennis',
            'golf'               => 'Golf',
            'auto_racing'        => 'Auto Racing',
            'boxing'             => 'Boxing/MMA',
            'other'              => 'Other Sports',
        ];
    }

    /**
     * Get leagues for a specific sport
     */
    /**
     * Get  leagues by sport
     */
    public static function getLeaguesBySport(string $sport): array
    {
        $leagues = [
            'football'           => ['NFL', 'XFL'],
            'basketball'         => ['NBA', 'WNBA', 'G League'],
            'baseball'           => ['MLB', 'Minor League'],
            'hockey'             => ['NHL', 'AHL'],
            'soccer'             => ['MLS', 'NWSL', 'USL'],
            'college_football'   => ['NCAA Division I', 'NCAA Division II', 'NCAA Division III'],
            'college_basketball' => ['NCAA Division I', 'NCAA Division II', 'NCAA Division III'],
            'tennis'             => ['ATP', 'WTA', 'Grand Slam'],
            'golf'               => ['PGA Tour', 'LPGA', 'Champions Tour'],
            'auto_racing'        => ['NASCAR', 'Formula 1', 'IndyCar'],
            'boxing'             => ['Professional Boxing', 'UFC', 'Bellator'],
        ];

        return $leagues[$sport] ?? ['Professional', 'Amateur'];
    }

    /**
     * Get popular teams for autocomplete
     */
    /**
     * Get  popular teams
     */
    public static function getPopularTeams(?string $sport = null): array
    {
        $query = self::select('team_name', 'team_city', 'league', 'sport_type')
            ->selectRaw('COUNT(*) as popularity')
            ->groupBy(['team_name', 'team_city', 'league', 'sport_type']);

        if ($sport) {
            $query->where('sport_type', $sport);
        }

        return $query->orderByDesc('popularity')
            ->limit(50)
            ->get()
            ->map(fn ($team): array => [
                'name'       => $team->team_name,
                'city'       => $team->team_city,
                'full_name'  => $team->team_city ? "{$team->team_city} {$team->team_name}" : $team->team_name,
                'league'     => $team->league,
                'sport'      => $team->sport_type,
                'popularity' => $team->popularity,
            ])
            ->toArray();
    }

    /**
     * Check if team matches search criteria
     */
    /**
     * MatchesSearch
     */
    public function matchesSearch(string $term): bool
    {
        $term = strtolower($term);

        return str_contains(strtolower($this->team_name), $term)
               || str_contains(strtolower($this->team_city), $term)
               || str_contains(strtolower($this->full_name), $term)
               || collect($this->aliases ?? [])->contains(fn ($alias): bool => str_contains(strtolower((string) $alias), $term));
    }

    /**
     * Get notification settings as array
     */
    /**
     * Get  notification settings
     */
    public function getNotificationSettings(): array
    {
        return [
            'email' => $this->email_alerts,
            'push'  => $this->push_alerts,
            'sms'   => $this->sms_alerts,
        ];
    }

    /**
     * Update notification settings
     */
    /**
     * UpdateNotificationSettings
     */
    public function updateNotificationSettings(array $settings): void
    {
        $this->update([
            'email_alerts' => $settings['email'] ?? $this->email_alerts,
            'push_alerts'  => $settings['push'] ?? $this->push_alerts,
            'sms_alerts'   => $settings['sms'] ?? $this->sms_alerts,
        ]);
    }

    /**
     * Get team statistics for dashboard
     */
    /**
     * Get  team stats
     */
    public static function getTeamStats(int $userId): array
    {
        $teams = self::where('user_id', $userId)->get();

        return [
            'total_teams'         => $teams->count(),
            'sports_count'        => $teams->groupBy('sport_type')->count(),
            'high_priority_count' => $teams->where('priority', '>=', 4)->count(),
            'email_alerts_count'  => $teams->where('email_alerts', true)->count(),
            'most_popular_sport'  => $teams->groupBy('sport_type')->sortByDesc(fn ($group) => $group->count())->keys()->first(),
            'by_sport'            => $teams->groupBy('sport_type')->map(fn ($group) => $group->count())->toArray(),
        ];
    }

    /**
     * Get  full name attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn () => $this->team_city ? "{$this->team_city} {$this->team_name}" : $this->team_name);
    }

    /**
     * Set  team slug attribute
     */
    protected function teamSlug(): Attribute
    {
        return Attribute::make(set: fn ($value): array => ['team_slug' => $value ?: $this->generateSlug()]);
    }

    protected function casts(): array
    {
        return [
            'aliases'      => 'array',
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
            'priority'     => 'integer',
        ];
    }
}
