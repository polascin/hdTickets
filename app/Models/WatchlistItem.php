<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Basic Watchlist Item model (lightweight stub until full implementation)
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $ticket_id
 * @property array|null  $criteria
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property User        $user
 * @property Ticket      $ticket
 */
class WatchlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'criteria',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
