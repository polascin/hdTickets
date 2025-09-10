<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'method',
        'operation',
        'url',
        'search_criteria',
        'status',
        'response_time_ms',
        'results_count',
        'error_type',
        'error_message',
        'selectors_used',
        'selector_effectiveness',
        'user_agent',
        'ip_address',
        'started_at',
        'completed_at',
    ];

    /**
     * Scope for successful operations
     *
     * @param mixed $query
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed operations
     *
     * @param mixed $query
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific platform
     *
     * @param mixed $query
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for specific method (api or scraping)
     *
     * @param mixed $query
     */
    public function scopeMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope for recent operations (within last N hours)
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Get success rate for a platform
     */
    /**
     * Get  success rate
     */
    public static function getSuccessRate(string $platform, int $hours = 24): float
    {
        $total = static::platform($platform)->recent($hours)->count();

        if ($total === 0) {
            return 0.0;
        }

        $successful = static::platform($platform)->recent($hours)->successful()->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average response time for a platform
     */
    /**
     * Get  average response time
     */
    public static function getAverageResponseTime(string $platform, int $hours = 24): float
    {
        return static::platform($platform)
            ->recent($hours)
            ->successful()
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms') ?? 0.0;
    }

    /**
     * Get platform availability (based on recent success rate)
     */
    /**
     * Get  platform availability
     */
    public static function getPlatformAvailability(string $platform, int $hours = 1): bool
    {
        $successRate = static::getSuccessRate($platform, $hours);

        return $successRate >= 70.0; // Consider platform available if success rate is >= 70%
    }

    /**
     * Get error statistics for a platform
     */
    /**
     * Get  error stats
     */
    public static function getErrorStats(string $platform, int $hours = 24): array
    {
        return static::platform($platform)
            ->recent($hours)
            ->failed()
            ->selectRaw('error_type, COUNT(*) as count')
            ->groupBy('error_type')
            ->pluck('count', 'error_type')
            ->toArray();
    }

    /**
     * Get selector effectiveness stats
     */
    /**
     * Get  selector stats
     */
    public static function getSelectorStats(string $platform, int $hours = 24): array
    {
        return static::platform($platform)
            ->recent($hours)
            ->whereNotNull('selector_effectiveness')
            ->get(['selector_effectiveness'])
            ->pluck('selector_effectiveness')
            ->filter()
            ->collapse()
            ->groupBy(fn ($item, $key) => $key)
            ->map(fn ($group): array => [
                'total_uses'      => $group->sum('uses'),
                'successful_uses' => $group->sum('successful'),
                'success_rate'    => $group->sum('uses') > 0 ?
                    round(($group->sum('successful') / $group->sum('uses')) * 100, 2) : 0,
            ])
            ->toArray();
    }

    protected function casts(): array
    {
        return [
            'search_criteria'        => 'array',
            'selectors_used'         => 'array',
            'selector_effectiveness' => 'array',
            'started_at'             => 'datetime',
            'completed_at'           => 'datetime',
        ];
    }
}
