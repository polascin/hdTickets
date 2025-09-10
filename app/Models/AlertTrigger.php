<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_alert_id',
        'scraped_ticket_id',
        'triggered_at',
        'match_score',
        'trigger_reason',
        'notification_sent',
        'user_acknowledged',
    ];

    // Relationships
    public function ticketAlert(): BelongsTo
    {
        return $this->belongsTo(TicketAlert::class);
    }

    public function scrapedTicket(): BelongsTo
    {
        return $this->belongsTo(ScrapedTicket::class);
    }

    protected function casts(): array
    {
        return [
            'triggered_at'      => 'datetime',
            'match_score'       => 'decimal:2',
            'notification_sent' => 'boolean',
            'user_acknowledged' => 'boolean',
        ];
    }
}
