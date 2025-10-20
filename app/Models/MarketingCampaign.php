<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use function in_array;

/**
 * Marketing Campaign Model
 *
 * @property int                 $id
 * @property string              $name
 * @property string|null         $description
 * @property string              $type
 * @property string              $status
 * @property string              $target_audience
 * @property string              $schedule_type
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $launched_at
 * @property array               $content
 * @property array               $settings
 * @property int                 $created_by
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 */
class MarketingCampaign extends Model
{
    use HasFactory;

    /** Campaign types */
    public const TYPE_EMAIL = 'email';

    public const TYPE_PUSH = 'push';

    public const TYPE_IN_APP = 'in_app';

    public const TYPE_SMS = 'sms';

    /** Campaign statuses */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CANCELLED = 'cancelled';

    /** Schedule types */
    public const SCHEDULE_IMMEDIATE = 'immediate';

    public const SCHEDULE_SCHEDULED = 'scheduled';

    public const SCHEDULE_RECURRING = 'recurring';

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'target_audience',
        'schedule_type',
        'scheduled_at',
        'launched_at',
        'content',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'content'      => 'array',
        'settings'     => 'array',
        'scheduled_at' => 'datetime',
        'launched_at'  => 'datetime',
    ];

    /**
     * Get the user who created this campaign
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get campaign targets
     */
    public function targets(): HasMany
    {
        return $this->hasMany(CampaignTarget::class);
    }

    /**
     * Get campaign emails
     */
    public function emails(): HasMany
    {
        return $this->hasMany(CampaignEmail::class);
    }

    /**
     * Get campaign analytics
     */
    public function analytics(): HasOne
    {
        return $this->hasOne(CampaignAnalytics::class);
    }

    /**
     * Get campaign interactions
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(CampaignInteraction::class);
    }

    /**
     * Scope for active campaigns
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for completed campaigns
     *
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for email campaigns
     *
     * @param mixed $query
     */
    public function scopeEmail($query)
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    /**
     * Check if campaign is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], TRUE);
    }

    /**
     * Check if campaign can be launched
     */
    public function canBeLaunched(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if campaign can be paused
     */
    public function canBePaused(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
