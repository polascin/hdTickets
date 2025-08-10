<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DataExportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'export_type',
        'data_types',
        'format',
        'status',
        'file_path',
        'file_size',
        'expires_at',
        'error_message',
    ];

    protected $casts = [
        'data_types' => 'array',
        'expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const EXPORT_TYPE_FULL = 'full';
    const EXPORT_TYPE_PARTIAL = 'partial';

    const FORMAT_JSON = 'json';
    const FORMAT_CSV = 'csv';

    /**
     * Get the user that owns the export request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the export is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the export is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the export is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the export has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the download link has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the file is available for download
     */
    public function isAvailableForDownload(): bool
    {
        return $this->isCompleted() 
               && !$this->isExpired() 
               && $this->file_path 
               && Storage::exists($this->file_path);
    }

    /**
     * Get the download URL
     */
    public function getDownloadUrl(): ?string
    {
        if (!$this->isAvailableForDownload()) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);

        return true;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $filePath, int $fileSize): bool
    {
        if (!$this->isProcessing()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'expires_at' => now()->addDays(7), // File expires in 7 days
        ]);

        return true;
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);

        return true;
    }

    /**
     * Delete the export file
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }

        return true;
    }

    /**
     * Scope to get active exports
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Scope to get completed exports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get expired exports
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get available exports
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
                    ->where('expires_at', '>', now())
                    ->whereNotNull('file_path');
    }

    /**
     * Get all available export types
     */
    public static function getExportTypes(): array
    {
        return [
            self::EXPORT_TYPE_FULL,
            self::EXPORT_TYPE_PARTIAL,
        ];
    }

    /**
     * Get all available formats
     */
    public static function getFormats(): array
    {
        return [
            self::FORMAT_JSON,
            self::FORMAT_CSV,
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
        ];
    }
}
