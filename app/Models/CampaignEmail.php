<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaign Email Model
 * 
 * @property int $id
 * @property int $campaign_id
 * @property int $user_id
 * @property string $subject
 * @property string $body
 * @property string $status
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $opened_at
 * @property \Carbon\Carbon|null $clicked_at
 * @property array|null $tracking_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CampaignEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'subject',
        'body',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'tracking_data'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'tracking_data' => 'array'
    ];

    /**
     * Email statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_OPENED = 'opened';
    public const STATUS_CLICKED = 'clicked';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    /**
     * Get the campaign this email belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    /**
     * Get the user this email was sent to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email was opened
     */
    public function wasOpened(): bool
    {
        return !is_null($this->opened_at);
    }

    /**
     * Check if email was clicked
     */
    public function wasClicked(): bool
    {
        return !is_null($this->clicked_at);
    }

    /**
     * Mark email as opened
     */
    public function markAsOpened(): void
    {
        if (!$this->wasOpened()) {
            $this->update([
                'opened_at' => now(),
                'status' => self::STATUS_OPENED
            ]);
        }
    }

    /**
     * Mark email as clicked
     */
    public function markAsClicked(): void
    {
        if (!$this->wasClicked()) {
            $this->update([
                'clicked_at' => now(),
                'status' => self::STATUS_CLICKED
            ]);
        }
    }
}