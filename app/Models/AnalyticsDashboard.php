<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'last_accessed_at'
    ];

    protected $casts = [
        'configuration' => 'array',
        'widgets' => 'array',
        'filters' => 'array',
        'shared_with' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'last_accessed_at' => 'datetime',
        'refresh_interval' => 'integer'
    ];

    /**
     * Get the user that owns the dashboard
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public dashboards
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for user's accessible dashboards
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_public', true)
              ->orWhereJsonContains('shared_with', $userId);
        });
    }

    /**
     * Get default dashboard for user
     */
    public static function getDefaultForUser($userId)
    {
        return static::where('user_id', $userId)
                    ->where('is_default', true)
                    ->first();
    }

    /**
     * Create default dashboard for user
     */
    public static function createDefaultForUser($userId)
    {
        return static::create([
            'user_id' => $userId,
            'name' => 'Default Dashboard',
            'description' => 'Default analytics dashboard',
            'configuration' => [
                'layout' => 'grid',
                'columns' => 3,
                'theme' => 'light'
            ],
            'widgets' => [
                'price_trends',
                'demand_patterns',
                'success_rates',
                'platform_comparison',
                'real_time_metrics'
            ],
            'filters' => [
                'time_range' => '30d',
                'platforms' => [],
                'categories' => []
            ],
            'refresh_interval' => 300, // 5 minutes
            'is_default' => true,
            'is_public' => false
        ]);
    }

    /**
     * Update last accessed timestamp
     */
    public function markAccessed()
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Check if user can access this dashboard
     */
    public function canAccess($userId)
    {
        return $this->user_id === $userId || 
               $this->is_public || 
               in_array($userId, $this->shared_with ?? []);
    }

    /**
     * Share dashboard with users
     */
    public function shareWith(array $userIds)
    {
        $currentSharedWith = $this->shared_with ?? [];
        $newSharedWith = array_unique(array_merge($currentSharedWith, $userIds));
        
        $this->update(['shared_with' => $newSharedWith]);
    }

    /**
     * Remove sharing from users
     */
    public function removeSharing(array $userIds)
    {
        $currentSharedWith = $this->shared_with ?? [];
        $newSharedWith = array_diff($currentSharedWith, $userIds);
        
        $this->update(['shared_with' => array_values($newSharedWith)]);
    }
}
