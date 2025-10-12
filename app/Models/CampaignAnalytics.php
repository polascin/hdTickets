<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaign Analytics Model
 * 
 * @property int $id
 * @property int $campaign_id
 * @property int $total_targets
 * @property int $messages_sent
 * @property int $messages_failed
 * @property float $delivery_rate
 * @property int $opens
 * @property int $clicks
 * @property int $conversions
 * @property int $unsubscribes
 * @property float $open_rate
 * @property float $click_rate
 * @property float $conversion_rate
 * @property float $unsubscribe_rate
 * @property array|null $additional_metrics
 * @property \Carbon\Carbon $last_updated
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CampaignAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'total_targets',
        'messages_sent',
        'messages_failed',
        'delivery_rate',
        'opens',
        'clicks',
        'conversions',
        'unsubscribes',
        'open_rate',
        'click_rate',
        'conversion_rate',
        'unsubscribe_rate',
        'additional_metrics',
        'last_updated'
    ];

    protected $casts = [
        'delivery_rate' => 'float',
        'open_rate' => 'float',
        'click_rate' => 'float',
        'conversion_rate' => 'float',
        'unsubscribe_rate' => 'float',
        'additional_metrics' => 'array',
        'last_updated' => 'datetime'
    ];

    /**
     * Get the campaign these analytics belong to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    /**
     * Calculate and update rates
     */
    public function updateRates(): void
    {
        $this->delivery_rate = $this->total_targets > 0 ? 
            round(($this->messages_sent / $this->total_targets) * 100, 2) : 0;

        $this->open_rate = $this->messages_sent > 0 ? 
            round(($this->opens / $this->messages_sent) * 100, 2) : 0;

        $this->click_rate = $this->messages_sent > 0 ? 
            round(($this->clicks / $this->messages_sent) * 100, 2) : 0;

        $this->conversion_rate = $this->messages_sent > 0 ? 
            round(($this->conversions / $this->messages_sent) * 100, 2) : 0;

        $this->unsubscribe_rate = $this->messages_sent > 0 ? 
            round(($this->unsubscribes / $this->messages_sent) * 100, 2) : 0;

        $this->last_updated = now();
        $this->save();
    }
}