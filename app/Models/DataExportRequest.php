<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

use function count;

class DataExportRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const EXPORT_TYPE_FULL = 'full';

    public const EXPORT_TYPE_PARTIAL = 'partial';

    public const FORMAT_JSON = 'json';

    public const FORMAT_CSV = 'csv';

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

    /**
     * Get the user that owns the export request
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the export is pending
     */
    /**
     * Check if  pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the export is processing
     */
    /**
     * Check if  processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the export is completed
     */
    /**
     * Check if  completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the export has failed
     */
    /**
     * Check if has  failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the download link has expired
     */
    /**
     * Check if  expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the file is available for download
     */
    /**
     * Check if  available for download
     */
    public function isAvailableForDownload(): bool
    {
        return $this->isCompleted()
               && ! $this->isExpired()
               && $this->file_path
               && Storage::exists($this->file_path);
    }

    /**
     * Get the download URL
     */
    /**
     * Get  download url
     */
    public function getDownloadUrl(): ?string
    {
        if (! $this->isAvailableForDownload()) {
            return NULL;
        }

        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size
     */
    /**
     * Get  formatted file size attribute
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (! $this->file_size) {
            return NULL;
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
    /**
     * MarkAsProcessing
     */
    public function markAsProcessing(): bool
    {
        if (! $this->isPending()) {
            return FALSE;
        }

        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);

        return TRUE;
    }

    /**
     * Mark as completed
     */
    /**
     * MarkAsCompleted
     */
    public function markAsCompleted(string $filePath, int $fileSize): bool
    {
        if (! $this->isProcessing()) {
            return FALSE;
        }

        $this->update([
            'status'     => self::STATUS_COMPLETED,
            'file_path'  => $filePath,
            'file_size'  => $fileSize,
            'expires_at' => now()->addDays(7), // File expires in 7 days
        ]);

        return TRUE;
    }

    /**
     * Mark as failed
     */
    /**
     * MarkAsFailed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->update([
            'status'        => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);

        return TRUE;
    }

    /**
     * Delete the export file
     */
    /**
     * DeleteFile
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }

        return TRUE;
    }

    /**
     * Scope to get active exports
     *
     * @param mixed $query
     */
    /**
     * ScopeActive
     *
     * @param mixed $query
     */
    public function scopeActive($query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Scope to get completed exports
     *
     * @param mixed $query
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get expired exports
     *
     * @param mixed $query
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope to get available exports
     *
     * @param mixed $query
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
    /**
     * Get  export types
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
    /**
     * Get  formats
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
    /**
     * Get  statuses
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
