<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MonitoringLog Model
 *
 * Stores detailed logs of monitoring attempts for analytics and debugging
 */
class MonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_monitor_id',
        'status',
        'response_time',
        'error_message',
        'platform',
        'data_found',
        'checked_at',
        'downtime_duration',
    ];

    protected $casts = [
        'response_time'     => 'float',
        'data_found'        => 'array',
        'checked_at'        => 'datetime',
        'downtime_duration' => 'integer',
    ];

    // Relationships

    public function eventMonitor(): BelongsTo
    {
        return $this->belongsTo(EventMonitor::class);
    }

    // Scopes

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeRecentlyChecked($query, int $hours = 24)
    {
        return $query->where('checked_at', '>=', now()->subHours($hours));
    }
}
