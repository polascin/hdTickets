<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebPushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_token',
        'user_agent',
        'is_active',
        'last_used_at',
    ];

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark subscription as used
     */
    /**
     * MarkAsUsed
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Disable subscription
     */
    /**
     * Disable
     */
    public function disable(): void
    {
        $this->update(['is_active' => FALSE]);
    }

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }
}
