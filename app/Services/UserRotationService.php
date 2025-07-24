<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserRotationService
{
    private const CACHE_KEY_PREFIX = 'user_rotation_';
    private const DEFAULT_CACHE_TTL = 3600; // 1 hour
    
    /**
     * Get a rotated user for scraping operations
     * 
     * @param string $platform The platform being scraped (stubhub, viagogo, etc.)
     * @param string $operation The type of operation (search, details, etc.)
     * @return User|null
     */
    public function getRotatedUser(string $platform = 'general', string $operation = 'scraping'): ?User
    {
        $cacheKey = $this->getCacheKey($platform, $operation);
        
        // Get or create rotation pool for this platform/operation
        $rotationPool = $this->getRotationPool($platform, $operation);
        
        if ($rotationPool->isEmpty()) {
            Log::warning("No users available for rotation", [
                'platform' => $platform,
                'operation' => $operation
            ]);
            return null;
        }
        
        // Get current rotation index
        $currentIndex = Cache::get($cacheKey . '_index', 0);
        
        // Get user from rotation pool
        $user = $rotationPool->get($currentIndex);
        
        // Increment index for next rotation (with wrap-around)
        $nextIndex = ($currentIndex + 1) % $rotationPool->count();
        Cache::put($cacheKey . '_index', $nextIndex, self::DEFAULT_CACHE_TTL);
        
        // Update user's last activity
        $this->updateUserActivity($user, $platform, $operation);
        
        Log::info("User rotated for scraping", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'platform' => $platform,
            'operation' => $operation,
            'rotation_index' => $currentIndex
        ]);
        
        return $user;
    }
    
    /**
     * Get multiple rotated users for batch operations
     * 
     * @param int $count Number of users to get
     * @param string $platform The platform being scraped
     * @param string $operation The type of operation
     * @return Collection
     */
    public function getMultipleRotatedUsers(int $count, string $platform = 'general', string $operation = 'scraping'): Collection
    {
        $users = collect();
        $rotationPool = $this->getRotationPool($platform, $operation);
        
        if ($rotationPool->isEmpty()) {
            return $users;
        }
        
        // Get unique users for batch operation
        $usedIndices = [];
        $maxAttempts = min($count * 2, $rotationPool->count()); // Prevent infinite loops
        $attempts = 0;
        
        while ($users->count() < $count && $attempts < $maxAttempts) {
            $user = $this->getRotatedUser($platform, $operation);
            
            if ($user && !$users->contains('id', $user->id)) {
                $users->push($user);
            }
            
            $attempts++;
        }
        
        return $users;
    }
    
    /**
     * Get rotation pool for specific platform and operation
     * 
     * @param string $platform
     * @param string $operation
     * @return Collection
     */
    private function getRotationPool(string $platform, string $operation): Collection
    {
        $cacheKey = $this->getCacheKey($platform, $operation) . '_pool';
        
        return Cache::remember($cacheKey, self::DEFAULT_CACHE_TTL, function () use ($platform, $operation) {
            return $this->buildRotationPool($platform, $operation);
        });
    }
    
    /**
     * Build rotation pool based on platform and operation
     * 
     * @param string $platform
     * @param string $operation
     * @return Collection
     */
    private function buildRotationPool(string $platform, string $operation): Collection
    {
        $query = User::where('is_active', true)
                    ->whereNotNull('email_verified_at');
        
        // Platform-specific user selection
        switch ($platform) {
            case 'stubhub':
            case 'viagogo':
            case 'seatgeek':
            case 'tickpick':
            case 'fanzone':
                // Prioritize platform-specific agents and premium customers
                $query->where(function ($q) use ($platform) {
                    $q->where('email', 'like', "%{$platform}%")
                      ->orWhere('email', 'like', '%premium%')
                      ->orWhere('email', 'like', '%rotationpool%')
                      ->orWhere('role', User::ROLE_AGENT);
                });
                break;
                
            case 'high_frequency':
                // For high-frequency operations, use rotation pool users
                $query->where('email', 'like', '%rotationpool%');
                break;
                
            case 'premium':
                // For premium operations, use premium customers and agents
                $query->where(function ($q) {
                    $q->where('email', 'like', '%premium%')
                      ->orWhere('role', User::ROLE_AGENT);
                });
                break;
                
            default:
                // General rotation - use all suitable users
                $query->where('role', '!=', User::ROLE_ADMIN);
                break;
        }
        
        // Operation-specific filtering
        if ($operation === 'search') {
            // For search operations, prefer customers
            $query->orderByRaw("CASE WHEN role = 'customer' THEN 0 ELSE 1 END");
        } elseif ($operation === 'details') {
            // For detail operations, prefer agents
            $query->orderByRaw("CASE WHEN role = 'agent' THEN 0 ELSE 1 END");
        }
        
        // Randomize the pool to avoid predictable patterns
        return $query->inRandomOrder()
                    ->limit(500) // Limit pool size for performance
                    ->get()
                    ->values(); // Reset collection keys
    }
    
    /**
     * Update user activity for tracking
     * 
     * @param User $user
     * @param string $platform
     * @param string $operation
     */
    private function updateUserActivity(User $user, string $platform, string $operation): void
    {
        $activityKey = "user_activity_{$user->id}";
        $activity = Cache::get($activityKey, []);
        
        $activity[] = [
            'platform' => $platform,
            'operation' => $operation,
            'timestamp' => now()->toISOString(),
        ];
        
        // Keep only last 10 activities per user
        $activity = array_slice($activity, -10);
        
        Cache::put($activityKey, $activity, self::DEFAULT_CACHE_TTL * 24); // Keep for 24 hours
    }
    
    /**
     * Get user activity history
     * 
     * @param User $user
     * @return array
     */
    public function getUserActivity(User $user): array
    {
        $activityKey = "user_activity_{$user->id}";
        return Cache::get($activityKey, []);
    }
    
    /**
     * Clear rotation cache (useful for refreshing user pools)
     * 
     * @param string|null $platform
     * @param string|null $operation
     */
    public function clearRotationCache(?string $platform = null, ?string $operation = null): void
    {
        if ($platform && $operation) {
            // Clear specific cache
            $cacheKey = $this->getCacheKey($platform, $operation);
            Cache::forget($cacheKey . '_pool');
            Cache::forget($cacheKey . '_index');
        } else {
            // Clear all rotation caches (this is more expensive)
            $platforms = ['general', 'stubhub', 'viagogo', 'seatgeek', 'tickpick', 'fanzone', 'ticketmaster', 'manchester_united', 'eventbrite', 'livenation', 'axs', 'high_frequency', 'premium'];
            $operations = ['scraping', 'search', 'details'];
            
            foreach ($platforms as $p) {
                foreach ($operations as $o) {
                    $cacheKey = $this->getCacheKey($p, $o);
                    Cache::forget($cacheKey . '_pool');
                    Cache::forget($cacheKey . '_index');
                }
            }
        }
        
        Log::info('Rotation cache cleared', [
            'platform' => $platform ?: 'all',
            'operation' => $operation ?: 'all'
        ]);
    }
    
    /**
     * Get rotation statistics
     * 
     * @return array
     */
    public function getRotationStatistics(): array
    {
        $totalUsers = User::where('is_active', true)->count();
        $verifiedUsers = User::where('is_active', true)
                            ->whereNotNull('email_verified_at')
                            ->count();
        
        $stats = [
            'total_active_users' => $totalUsers,
            'verified_users' => $verifiedUsers,
            'rotation_ready_users' => $verifiedUsers,
            'platform_specific' => [],
        ];
        
        // Platform-specific stats
        $platforms = ['stubhub', 'viagogo', 'seatgeek', 'tickpick', 'fanzone'];
        foreach ($platforms as $platform) {
            $count = User::where('is_active', true)
                        ->whereNotNull('email_verified_at')
                        ->where('email', 'like', "%{$platform}%")
                        ->count();
            $stats['platform_specific'][$platform] = $count;
        }
        
        // Special pools
        $stats['premium_users'] = User::where('is_active', true)
                                     ->where('email', 'like', '%premium%')
                                     ->count();
        
        $stats['rotation_pool_users'] = User::where('is_active', true)
                                           ->where('email', 'like', '%rotationpool%')
                                           ->count();
        
        return $stats;
    }
    
    /**
     * Generate cache key for platform and operation
     * 
     * @param string $platform
     * @param string $operation
     * @return string
     */
    private function getCacheKey(string $platform, string $operation): string
    {
        return self::CACHE_KEY_PREFIX . $platform . '_' . $operation;
    }
}
