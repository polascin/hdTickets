<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'false_positive'
    ];

    protected $casts = [
        'incident_data' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'false_positive' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'investigating', 'in_progress']);
    }

    /**
     * Check if incident is resolved
     *
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if incident is critical
     *
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->priority === 'critical';
    }

    /**
     * Resolve incident with notes
     *
     * @param string $notes
     * @param bool $falsePositive
     * @return bool
     */
    public function resolve(string $notes, bool $falsePositive = false): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
            'false_positive' => $falsePositive
        ]);
    }

    /**
     * Assign incident to user
     *
     * @param User $user
     * @return bool
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'assigned_to' => $user->id,
            'status' => $this->status === 'open' ? 'investigating' : $this->status
        ]);
    }

    /**
     * Escalate incident priority
     *
     * @return bool
     */
    public function escalate(): bool
    {
        $priorities = ['low', 'medium', 'high', 'critical'];
        $currentIndex = array_search($this->priority, $priorities);
        
        if ($currentIndex !== false && $currentIndex < count($priorities) - 1) {
            return $this->update(['priority' => $priorities[$currentIndex + 1]]);
        }

        return false;
    }

    /**
     * Get time since detection
     *
     * @return \Carbon\CarbonInterval
     */
    public function getTimeSinceDetection(): \Carbon\CarbonInterval
    {
        return $this->detected_at->diffAsCarbonInterval(now());
    }

    /**
     * Get resolution time (if resolved)
     *
     * @return \Carbon\CarbonInterval|null
     */
    public function getResolutionTime(): ?\Carbon\CarbonInterval
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->detected_at->diffAsCarbonInterval($this->resolved_at);
    }

    /**
     * Scope for open incidents
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'investigating', 'in_progress']);
    }

    /**
     * Scope for critical incidents
     */
    public function scopeCritical($query)
    {
        return $query->where(function($q) {
            $q->where('severity', 'critical')->orWhere('priority', 'critical');
        });
    }

    /**
     * Scope for unassigned incidents
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for recent incidents
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('detected_at', '>=', now()->subHours($hours));
    }
}
