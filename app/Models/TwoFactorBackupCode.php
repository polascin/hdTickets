<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorBackupCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'used_at',
    ];

    /**
     * Get the user that owns the backup code
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if backup code has been used
     */
    public function isUsed(): bool
    {
        return $this->used_at !== NULL;
    }

    /**
     * Mark backup code as used
     */
    public function markAsUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }
}
