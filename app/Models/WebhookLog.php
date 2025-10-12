<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WebhookLog Model
 * 
 * Stores detailed logs of webhook delivery attempts for:
 * - Delivery tracking and debugging
 * - Performance analytics
 * - Retry logic management
 * - Compliance and auditing
 */
class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_id',
        'event_type',
        'payload',
        'status',
        'response_code',
        'response_body',
        'response_time',
        'error_message',
        'attempt_number',
        'delivered_at',
        'user_agent',
        'ip_address'
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'response_time' => 'float',
        'attempt_number' => 'integer',
        'delivered_at' => 'datetime'
    ];

    // Relationships

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    // Status Methods

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRetry(): bool
    {
        return $this->attempt_number > 1;
    }

    // Performance Analytics

    public function getResponseTimeCategory(): string
    {
        if ($this->response_time <= 500) {
            return 'fast';
        } elseif ($this->response_time <= 2000) {
            return 'normal';
        } elseif ($this->response_time <= 5000) {
            return 'slow';
        } else {
            return 'very_slow';
        }
    }

    public function getStatusCategory(): string
    {
        if ($this->response_code >= 200 && $this->response_code < 300) {
            return 'success';
        } elseif ($this->response_code >= 300 && $this->response_code < 400) {
            return 'redirect';
        } elseif ($this->response_code >= 400 && $this->response_code < 500) {
            return 'client_error';
        } elseif ($this->response_code >= 500) {
            return 'server_error';
        } else {
            return 'unknown';
        }
    }

    // Error Analysis

    public function getErrorType(): ?string
    {
        if (!$this->error_message) {
            return null;
        }

        $errorMessage = strtolower($this->error_message);

        if (str_contains($errorMessage, 'timeout')) {
            return 'timeout';
        } elseif (str_contains($errorMessage, 'connection')) {
            return 'connection';
        } elseif (str_contains($errorMessage, 'ssl') || str_contains($errorMessage, 'certificate')) {
            return 'ssl';
        } elseif (str_contains($errorMessage, 'dns')) {
            return 'dns';
        } elseif ($this->response_code >= 500) {
            return 'server_error';
        } elseif ($this->response_code >= 400) {
            return 'client_error';
        } else {
            return 'unknown';
        }
    }

    public function shouldRetry(): bool
    {
        // Don't retry client errors (4xx) except for 408, 429
        if ($this->response_code >= 400 && $this->response_code < 500) {
            return in_array($this->response_code, [408, 429]);
        }

        // Retry server errors (5xx) and network errors
        return $this->response_code >= 500 || !$this->response_code;
    }

    // Data Extraction

    public function getPayloadSize(): int
    {
        return strlen(json_encode($this->payload));
    }

    public function getResponseSize(): int
    {
        return strlen($this->response_body ?? '');
    }

    public function hasResponseBody(): bool
    {
        return !empty($this->response_body);
    }

    // Scopes

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeRetries($query)
    {
        return $query->where('attempt_number', '>', 1);
    }

    public function scopeFirstAttempts($query)
    {
        return $query->where('attempt_number', 1);
    }

    public function scopeRecentlyDelivered($query, int $hours = 24)
    {
        return $query->where('delivered_at', '>=', now()->subHours($hours));
    }

    public function scopeFastResponse($query, int $maxMs = 1000)
    {
        return $query->where('response_time', '<=', $maxMs);
    }

    public function scopeSlowResponse($query, int $minMs = 5000)
    {
        return $query->where('response_time', '>=', $minMs);
    }

    public function scopeByResponseCode($query, int $code)
    {
        return $query->where('response_code', $code);
    }

    public function scopeClientErrors($query)
    {
        return $query->whereBetween('response_code', [400, 499]);
    }

    public function scopeServerErrors($query)
    {
        return $query->whereBetween('response_code', [500, 599]);
    }

    // Accessors

    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->response_time < 1000) {
            return round($this->response_time) . 'ms';
        } else {
            return round($this->response_time / 1000, 2) . 's';
        }
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'success' => ['class' => 'success', 'text' => 'Success'],
            'failed' => ['class' => 'danger', 'text' => 'Failed'],
            'pending' => ['class' => 'warning', 'text' => 'Pending'],
            default => ['class' => 'secondary', 'text' => 'Unknown']
        };
    }

    public function getResponseCodeBadgeAttribute(): array
    {
        if ($this->response_code >= 200 && $this->response_code < 300) {
            return ['class' => 'success', 'text' => $this->response_code];
        } elseif ($this->response_code >= 300 && $this->response_code < 400) {
            return ['class' => 'info', 'text' => $this->response_code];
        } elseif ($this->response_code >= 400 && $this->response_code < 500) {
            return ['class' => 'warning', 'text' => $this->response_code];
        } elseif ($this->response_code >= 500) {
            return ['class' => 'danger', 'text' => $this->response_code];
        } else {
            return ['class' => 'secondary', 'text' => 'N/A'];
        }
    }

    public function getAttemptBadgeAttribute(): array
    {
        if ($this->attempt_number === 1) {
            return ['class' => 'primary', 'text' => 'First'];
        } else {
            return ['class' => 'warning', 'text' => "Retry #{$this->attempt_number}"];
        }
    }

    public function getDeliveredAgoAttribute(): string
    {
        return $this->delivered_at ? $this->delivered_at->diffForHumans() : 'Unknown';
    }

    public function getTruncatedErrorAttribute(): string
    {
        if (!$this->error_message) {
            return '';
        }

        return strlen($this->error_message) > 100 
            ? substr($this->error_message, 0, 100) . '...'
            : $this->error_message;
    }
}