<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaign Interaction Model
 *
 * @property int            $id
 * @property int            $campaign_id
 * @property int            $user_id
 * @property string         $action
 * @property \Carbon\Carbon $timestamp
 * @property array|null     $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CampaignInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'action',
        'timestamp',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'metadata'  => 'array',
    ];

    /**
     * Interaction actions
     */
    public const ACTION_SENT = 'sent';

    public const ACTION_DELIVERED = 'delivered';

    public const ACTION_OPENED = 'opened';

    public const ACTION_CLICKED = 'clicked';

    public const ACTION_CONVERTED = 'converted';

    public const ACTION_UNSUBSCRIBED = 'unsubscribed';

    public const ACTION_BOUNCED = 'bounced';

    public const ACTION_FAILED = 'failed';

    /**
     * Get the campaign this interaction belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    /**
     * Get the user who performed this interaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
