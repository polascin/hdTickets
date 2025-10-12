<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function in_array;

class AnalyticsDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'configuration',
        'widgets',
        'filters',
        'refresh_interval',
        'is_public',
        'is_default',
        'shared_with',
        'last_accessed_at',
    ];

    /**
     * Get the user that owns the dashboard
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public dashboards
     *
     * @param mixed $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for user's accessible dashboards
     *
     * @param mixed $query
     * @param mixed $userId
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId): void {
            $q->where('user_id', $userId)
                ->orWhere('is_public', true)
                ->orWhereJsonContains('shared_with', $userId);
        });
    }

    /**
     * Get default dashboard for user
     *
     * @param mixed $userId
     */
    public static function getDefaultForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Create default dashboard for user
     *
     * @param mixed $userId
     */
    public static function createDefaultForUser($userId)
    {
        return static::create([
            'user_id'       => $userId,
            'name'          => 'Default Dashboard',
            'description'   => 'Default analytics dashboard',
            'configuration' => [
                'layout'  => 'grid',
                'columns' => 3,
                'theme'   => 'light',
            ],
            'widgets' => [
                'price_trends',
                'demand_patterns',
                'success_rates',
                'platform_comparison',
                'real_time_metrics',
            ],
            'filters' => [
                'time_range' => '30d',
                'platforms'  => [],
                'categories' => [],
            ],
            'refresh_interval' => 300, // 5 minutes
            'is_default'       => true,
            'is_public'        => false,
        ]);
    }

    /**
     * Update last accessed timestamp
     */
    /**
     * MarkAccessed
     */
    public function markAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Check if user can access this dashboard
     */
    /**
     * Check if can  access
     */
    public function canAccess(): bool
    {
        return $this->user_id === $userId
               || $this->is_public
               || in_array($userId, $this->shared_with ?? [], true);
    }

    /**
     * Share dashboard with users
     */
    /**
     * ShareWith
     */
    public function shareWith(array $userIds): void
    {
        $currentSharedWith = $this->shared_with ?? [];
        $newSharedWith = array_unique(array_merge($currentSharedWith, $userIds));

        $this->update(['shared_with' => $newSharedWith]);
    }

    /**
     * Remove sharing from users
     */
    /**
     * RemoveSharing
     */
    public function removeSharing(array $userIds): void
    {
        $currentSharedWith = $this->shared_with ?? [];
        $newSharedWith = array_diff($currentSharedWith, $userIds);

        $this->update(['shared_with' => array_values($newSharedWith)]);
    }

    protected function casts(): array
    {
        return [
            'configuration'    => 'array',
            'widgets'          => 'array',
            'filters'          => 'array',
            'shared_with'      => 'array',
            'is_public'        => 'boolean',
            'is_default'       => 'boolean',
            'last_accessed_at' => 'datetime',
            'refresh_interval' => 'integer',
        ];
    }
}
