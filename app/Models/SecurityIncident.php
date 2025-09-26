<?php declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function count;
use function in_array;

/**
 * SecurityIncident Model
 *
 * Represents security incidents that require investigation and response
 */
class SecurityIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'severity',
        'status',
        'priority',
        'affected_user_id',
        'source_ip',
        'detection_method',
        'incident_data',
        'detected_at',
        'assigned_to',
        'resolved_at',
        'resolution_notes',
        'false_positive',
    ];

    /**
     * Affected user (if applicable)
     */
    public function affectedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affected_user_id');
    }

    /**
     * User assigned to handle this incident
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Security events related to this incident
     */
    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class, 'incident_id');
    }

    /**
     * Check if incident is open/active
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'investigating', 'in_progress'], TRUE);
    }

    /**
     * Check if incident is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if incident is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->priority === 'critical';
    }

    /**
     * Resolve incident with notes
     */
    public function resolve(string $notes, bool $falsePositive = FALSE): bool
    {
        return $this->update([
            'status'           => 'resolved',
            'resolved_at'      => now(),
            'resolution_notes' => $notes,
            'false_positive'   => $falsePositive,
        ]);
    }

    /**
     * Assign incident to user
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'assigned_to' => $user->id,
            'status'      => $this->status === 'open' ? 'investigating' : $this->status,
        ]);
    }

    /**
     * Escalate incident priority
     */
    public function escalate(): bool
    {
        $priorities = ['low', 'medium', 'high', 'critical'];
        $currentIndex = array_search($this->priority, $priorities, TRUE);

        if ($currentIndex !== FALSE && $currentIndex < count($priorities) - 1) {
            return $this->update(['priority' => $priorities[$currentIndex + 1]]);
        }

        return FALSE;
    }

    /**
     * Get time since detection
     */
    public function getTimeSinceDetection(): CarbonInterval
    {
        return $this->detected_at->diffAsCarbonInterval(now());
    }

    /**
     * Get resolution time (if resolved)
     */
    public function getResolutionTime(): ?CarbonInterval
    {
        if (! $this->resolved_at) {
            return NULL;
        }

        return $this->detected_at->diffAsCarbonInterval($this->resolved_at);
    }

    /**
     * Scope for open incidents
     *
     * @param mixed $query
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'investigating', 'in_progress']);
    }

    /**
     * Scope for critical incidents
     *
     * @param mixed $query
     */
    public function scopeCritical($query)
    {
        return $query->where(function ($q): void {
            $q->where('severity', 'critical')->orWhere('priority', 'critical');
        });
    }

    /**
     * Scope for unassigned incidents
     *
     * @param mixed $query
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for recent incidents
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('detected_at', '>=', now()->subHours($hours));
    }

    protected function casts(): array
    {
        return [
            'incident_data'  => 'array',
            'detected_at'    => 'datetime',
            'resolved_at'    => 'datetime',
            'false_positive' => 'boolean',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }
}
