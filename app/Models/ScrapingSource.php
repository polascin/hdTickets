<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Scraping Source Model
 * 
 * Manages ticket scraping source configurations for the HD Tickets platform.
 * Each source represents a ticket vendor/platform that can be scraped.
 * 
 * @property int $id
 * @property string $name Source name (e.g. StubHub, Vivid Seats)
 * @property string $base_url Base URL for scraping
 * @property int $rate_limit Requests per minute limit
 * @property string $priority Priority level (high, medium, low)
 * @property bool $enabled Whether the source is active
 * @property string $status Current status (online, offline, testing)
 * @property array $headers Custom headers for requests
 * @property array $config Additional configuration data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ScrapingSource extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scraping_sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'base_url',
        'rate_limit',
        'priority',
        'enabled',
        'status',
        'headers',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
        'rate_limit' => 'integer',
        'headers' => 'array',
        'config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'headers', // Hide sensitive header data in API responses
    ];

    /**
     * Priority levels
     */
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    /**
     * Status types
     */
    public const STATUS_ONLINE = 'online';
    public const STATUS_OFFLINE = 'offline';
    public const STATUS_TESTING = 'testing';
    public const STATUS_ERROR = 'error';

    /**
     * Get only enabled sources
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Get sources by priority
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get sources by status
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get high priority sources
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->byPriority(self::PRIORITY_HIGH);
    }

    /**
     * Get online sources
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnline($query)
    {
        return $query->byStatus(self::STATUS_ONLINE);
    }

    /**
     * Check if source is healthy (enabled and online)
     * 
     * @return bool
     */
    public function isHealthy(): bool
    {
        return $this->enabled && $this->status === self::STATUS_ONLINE;
    }

    /**
     * Get priority badge color for UI
     * 
     * @return string
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_HIGH => 'red',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_LOW => 'green',
            default => 'gray'
        };
    }

    /**
     * Get status badge color for UI
     * 
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ONLINE => 'green',
            self::STATUS_TESTING => 'yellow',
            self::STATUS_OFFLINE => 'red',
            self::STATUS_ERROR => 'red',
            default => 'gray'
        };
    }

    /**
     * Get formatted rate limit for display
     * 
     * @return string
     */
    public function getFormattedRateLimitAttribute(): string
    {
        return $this->rate_limit . ' req/min';
    }

    /**
     * Update source status
     * 
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $status): bool
    {
        return $this->update(['status' => $status]);
    }

    /**
     * Toggle enabled status
     * 
     * @return bool
     */
    public function toggle(): bool
    {
        return $this->update(['enabled' => !$this->enabled]);
    }

    /**
     * Get configuration value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set configuration value by key
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setConfig(string $key, $value): bool
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        
        return $this->update(['config' => $config]);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Set default values when creating
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = self::STATUS_OFFLINE;
            }
            
            if (empty($model->priority)) {
                $model->priority = self::PRIORITY_MEDIUM;
            }
            
            if (is_null($model->enabled)) {
                $model->enabled = true;
            }
            
            if (empty($model->rate_limit)) {
                $model->rate_limit = 60; // Default 60 requests per minute
            }
        });
    }
}