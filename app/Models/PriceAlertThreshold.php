<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PriceAlertThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'ticket_id',
        'target_price',
        'alert_type',
        'percentage_threshold',
        'is_active',
        'last_triggered_at',
        'trigger_count',
        'notification_channels'
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'percentage_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
        'notification_channels' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($threshold) {
            if (empty($threshold->uuid)) {
                $threshold->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns this price alert threshold
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticket being monitored
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class, 'ticket_id');
    }

    /**
     * Scope for active thresholds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific alert type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Check if price threshold should trigger alert
     */
    public function shouldTrigger($currentPrice): bool
    {
        if (!$this->is_active) {
            return false;
        }

        switch ($this->alert_type) {
            case 'below':
                return $currentPrice <= $this->target_price;
            
            case 'above':
                return $currentPrice >= $this->target_price;
            
            case 'percentage_change':
                if (!$this->percentage_threshold) {
                    return false;
                }
                
                $basePrice = $this->ticket->price ?? $this->target_price;
                $changePercentage = (($currentPrice - $basePrice) / $basePrice) * 100;
                
                return abs($changePercentage) >= $this->percentage_threshold;
                
            default:
                return false;
        }
    }

    /**
     * Trigger the alert
     */
    public function trigger(): void
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Get formatted target price
     */
    public function getFormattedTargetPriceAttribute(): string
    {
        return '£' . number_format($this->target_price, 2);
    }
}
