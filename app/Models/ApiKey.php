<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use function in_array;

/**
 * API Key Model
 *
 * Manages API keys for external integrations with:
 * - Secure key storage and validation
 * - Permission-based access control
 * - Rate limiting and usage tracking
 * - Expiration and revocation management
 */
class ApiKey extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'key_hash',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active',
        'rate_limit',
        'usage_count',
        'last_ip',
        'revoked_at',
    ];

    protected $casts = [
        'permissions'  => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
        'rate_limit'   => 'integer',
        'usage_count'  => 'integer',
        'revoked_at'   => 'datetime',
    ];

    protected $hidden = [
        'key_hash',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Validation and Security Methods

    public function validateKey(string $providedKey): bool
    {
        $hashedProvidedKey = hash('sha256', $providedKey);

        return hash_equals($this->key_hash, $hashedProvidedKey);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && !$this->isExpired()
            && !$this->isRevoked();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isRevoked(): bool
    {
        return NULL !== $this->revoked_at;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], TRUE)
            || in_array('admin', $this->permissions ?? [], TRUE);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return !empty(array_intersect($permissions, $this->permissions ?? []))
            || in_array('admin', $this->permissions ?? [], TRUE);
    }

    // Usage Tracking

    public function recordUsage(?string $ipAddress = NULL): void
    {
        $this->update([
            'last_used_at' => now(),
            'usage_count'  => $this->usage_count + 1,
            'last_ip'      => $ipAddress,
        ]);
    }

    public function getDailyUsage(?Carbon $date = NULL): int
    {
        $date ??= now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // This would typically query an api_usage_logs table
        // For now, returning 0 as placeholder
        return 0;
    }

    public function getHourlyUsage(?Carbon $hour = NULL): int
    {
        $hour ??= now();
        $startOfHour = $hour->copy()->startOfHour();
        $endOfHour = $hour->copy()->endOfHour();

        // This would typically query an api_usage_logs table
        // For now, returning 0 as placeholder
        return 0;
    }

    public function isRateLimited(): bool
    {
        if (!$this->rate_limit) {
            return FALSE;
        }

        $currentUsage = $this->getHourlyUsage();

        return $currentUsage >= $this->rate_limit;
    }

    public function getRemainingRequests(): int
    {
        if (!$this->rate_limit) {
            return PHP_INT_MAX;
        }

        $currentUsage = $this->getHourlyUsage();

        return max(0, $this->rate_limit - $currentUsage);
    }

    // Status and Analytics

    public function getStatus(): string
    {
        if ($this->isRevoked()) {
            return 'revoked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isRateLimited()) {
            return 'rate_limited';
        }

        return 'active';
    }

    public function getHealthScore(): float
    {
        if (!$this->isValid()) {
            return 0.0;
        }

        $score = 100.0;

        // Deduct points for high usage relative to limit
        if ($this->rate_limit) {
            $usageRatio = $this->getHourlyUsage() / $this->rate_limit;
            if ($usageRatio > 0.8) {
                $score -= (($usageRatio - 0.8) * 100); // Up to 20 points deduction
            }
        }

        // Deduct points for approaching expiration
        if ($this->expires_at) {
            $daysUntilExpiry = now()->diffInDays($this->expires_at, FALSE);
            if ($daysUntilExpiry <= 7) {
                $score -= (7 - $daysUntilExpiry) * 5; // Up to 35 points deduction
            }
        }

        return max(0.0, min(100.0, $score));
    }

    public function getUsageStats(int $days = 30): array
    {
        $stats = [
            'total_requests'  => $this->usage_count,
            'daily_average'   => 0,
            'peak_day'        => NULL,
            'recent_activity' => [],
        ];

        // This would be implemented with proper usage logging
        // For now, returning basic stats
        if ($this->created_at) {
            $daysActive = max(1, now()->diffInDays($this->created_at));
            $stats['daily_average'] = round($this->usage_count / $daysActive, 2);
        }

        return $stats;
    }

    // Management Operations

    public function revoke(?string $reason = NULL): bool
    {
        return $this->update([
            'is_active'         => FALSE,
            'revoked_at'        => now(),
            'revocation_reason' => $reason,
        ]);
    }

    public function activate(): bool
    {
        if ($this->isExpired()) {
            return FALSE;
        }

        return $this->update([
            'is_active'  => TRUE,
            'revoked_at' => NULL,
        ]);
    }

    public function updatePermissions(array $permissions): bool
    {
        $validPermissions = ['read', 'write', 'admin'];
        $filteredPermissions = array_filter(
            $permissions,
            fn ($perm) => in_array($perm, $validPermissions, TRUE),
        );

        return $this->update(['permissions' => array_unique($filteredPermissions)]);
    }

    public function extendExpiration(int $days): bool
    {
        $newExpiration = $this->expires_at
            ? $this->expires_at->addDays($days)
            : now()->addDays($days);

        return $this->update(['expires_at' => $newExpiration]);
    }

    public function updateRateLimit(int $requestsPerHour): bool
    {
        return $this->update(['rate_limit' => max(1, $requestsPerHour)]);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE)
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNull('revoked_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    public function scopeByPermission($query, string $permission)
    {
        return $query->whereJsonContains('permissions', $permission);
    }

    public function scopeRecentlyUsed($query, int $hours = 24)
    {
        return $query->where('last_used_at', '>=', now()->subHours($hours));
    }

    public function scopeUnused($query, int $days = 30)
    {
        return $query->where(function ($q) use ($days): void {
            $q->whereNull('last_used_at')
                ->orWhere('last_used_at', '<=', now()->subDays($days));
        });
    }

    // Mutators and Accessors

    public function getFormattedKeyAttribute(): string
    {
        return 'hdt_' . str_repeat('*', 28) . substr($this->key_hash, -4);
    }

    public function getPermissionLabelsAttribute(): array
    {
        $labels = [
            'read'  => 'Read Access',
            'write' => 'Write Access',
            'admin' => 'Admin Access',
        ];

        return array_map(
            fn ($perm) => $labels[$perm] ?? $perm,
            $this->permissions ?? [],
        );
    }

    public function getExpiresInAttribute(): ?string
    {
        if (!$this->expires_at) {
            return 'Never';
        }

        if ($this->isExpired()) {
            return 'Expired ' . $this->expires_at->diffForHumans();
        }

        return $this->expires_at->diffForHumans();
    }

    public function getLastUsedAttribute(): ?string
    {
        return $this->last_used_at?->diffForHumans() ?? 'Never used';
    }
}
