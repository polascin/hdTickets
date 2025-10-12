<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaign Target Model
 * 
 * @property int $id
 * @property int $campaign_id
 * @property int $user_id
 * @property string $target_type
 * @property string $status
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CampaignTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'target_type',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Target statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * Get the campaign this target belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    /**
     * Get the user this target is for
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}