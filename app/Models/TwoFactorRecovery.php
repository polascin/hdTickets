<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorRecovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    /**
     * Get the user that owns the recovery code
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if recovery code has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if recovery code has been used
     */
    public function isUsed(): bool
    {
        return $this->used_at !== NULL;
    }

    /**
     * Check if recovery code is valid (not used and not expired)
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    /**
     * Mark recovery code as used
     */
    public function markAsUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
