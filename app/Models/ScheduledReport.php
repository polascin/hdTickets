<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ScheduledReport Model
 *
 * Represents scheduled analytics reports in the HD Tickets system.
 *
 * @property int            $id
 * @property string         $name
 * @property string         $description
 * @property string         $type
 * @property string         $format
 * @property string         $schedule
 * @property array          $sections
 * @property array          $filters
 * @property array          $recipients
 * @property array          $options
 * @property array          $statistics
 * @property bool           $is_active
 * @property int            $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read User $creator
 */
class ScheduledReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'format',
        'schedule',
        'sections',
        'filters',
        'recipients',
        'options',
        'statistics',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sections'   => 'array',
        'filters'    => 'array',
        'recipients' => 'array',
        'options'    => 'array',
        'statistics' => 'array',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Available report types
     */
    public const TYPE_DAILY = 'daily';

    public const TYPE_WEEKLY = 'weekly';

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_CUSTOM = 'custom';

    /**
     * Available report formats
     */
    public const FORMAT_PDF = 'pdf';

    public const FORMAT_XLSX = 'xlsx';

    public const FORMAT_CSV = 'csv';

    public const FORMAT_JSON = 'json';

    /**
     * Available report sections
     */
    public const SECTION_OVERVIEW_METRICS = 'overview_metrics';

    public const SECTION_PLATFORM_PERFORMANCE = 'platform_performance';

    public const SECTION_PRICING_TRENDS = 'pricing_trends';

    public const SECTION_EVENT_POPULARITY = 'event_popularity';

    public const SECTION_MARKET_INTELLIGENCE = 'market_intelligence';

    public const SECTION_PREDICTIVE_INSIGHTS = 'predictive_insights';

    public const SECTION_ANOMALIES = 'anomalies';

    /**
     * Get the user who created this scheduled report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active reports
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
    }

    /**
     * Scope for reports of a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for reports with specific format
     */
    public function scopeWithFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Get all available report types
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_DAILY   => 'Daily',
            self::TYPE_WEEKLY  => 'Weekly',
            self::TYPE_MONTHLY => 'Monthly',
            self::TYPE_CUSTOM  => 'Custom',
        ];
    }

    /**
     * Get all available report formats
     */
    public static function getAvailableFormats(): array
    {
        return [
            self::FORMAT_PDF  => 'PDF',
            self::FORMAT_XLSX => 'Excel (XLSX)',
            self::FORMAT_CSV  => 'CSV',
            self::FORMAT_JSON => 'JSON',
        ];
    }

    /**
     * Get all available report sections
     */
    public static function getAvailableSections(): array
    {
        return [
            self::SECTION_OVERVIEW_METRICS     => 'Overview Metrics',
            self::SECTION_PLATFORM_PERFORMANCE => 'Platform Performance',
            self::SECTION_PRICING_TRENDS       => 'Pricing Trends',
            self::SECTION_EVENT_POPULARITY     => 'Event Popularity',
            self::SECTION_MARKET_INTELLIGENCE  => 'Market Intelligence',
            self::SECTION_PREDICTIVE_INSIGHTS  => 'Predictive Insights',
            self::SECTION_ANOMALIES            => 'Anomalies',
        ];
    }

    /**
     * Check if the report is due to run
     */
    public function isDue(): bool
    {
        if (!$this->is_active) {
            return FALSE;
        }

        $lastRun = $this->getLastRunTime();

        switch ($this->type) {
            case self::TYPE_DAILY:
                return $lastRun === NULL || $lastRun->lt(now()->startOfDay());
            case self::TYPE_WEEKLY:
                return $lastRun === NULL || $lastRun->lt(now()->startOfWeek());
            case self::TYPE_MONTHLY:
                return $lastRun === NULL || $lastRun->lt(now()->startOfMonth());
            case self::TYPE_CUSTOM:
                // For custom schedules, would need to parse the cron expression
                // This is a simplified implementation
                return TRUE;
            default:
                return FALSE;
        }
    }

    /**
     * Get the last run time from statistics
     */
    public function getLastRunTime(): ?\Carbon\Carbon
    {
        if (!isset($this->statistics['last_run'])) {
            return NULL;
        }

        return \Carbon\Carbon::parse($this->statistics['last_run']);
    }

    /**
     * Get the last successful run time
     */
    public function getLastSuccessfulRunTime(): ?\Carbon\Carbon
    {
        if (!isset($this->statistics['last_successful_run'])) {
            return NULL;
        }

        return \Carbon\Carbon::parse($this->statistics['last_successful_run']);
    }

    /**
     * Get the success rate for this report
     */
    public function getSuccessRate(): float
    {
        $totalRuns = $this->statistics['total_runs'] ?? 0;
        $successfulRuns = $this->statistics['successful_runs'] ?? 0;

        if ($totalRuns === 0) {
            return 0;
        }

        return ($successfulRuns / $totalRuns) * 100;
    }

    /**
     * Get the average generation time
     */
    public function getAverageGenerationTime(): float
    {
        return $this->statistics['avg_generation_time'] ?? 0;
    }

    /**
     * Get the total runs count
     */
    public function getTotalRuns(): int
    {
        return $this->statistics['total_runs'] ?? 0;
    }

    /**
     * Get the successful runs count
     */
    public function getSuccessfulRuns(): int
    {
        return $this->statistics['successful_runs'] ?? 0;
    }

    /**
     * Get the failed runs count
     */
    public function getFailedRuns(): int
    {
        return $this->statistics['failed_runs'] ?? 0;
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->statistics['last_error'] ?? NULL;
    }

    /**
     * Get the next scheduled run time
     */
    public function getNextRunTime(): ?\Carbon\Carbon
    {
        if (!$this->is_active) {
            return NULL;
        }

        switch ($this->type) {
            case self::TYPE_DAILY:
                return now()->addDay()->setTime(6, 0, 0);
            case self::TYPE_WEEKLY:
                return now()->next(\Carbon\Carbon::MONDAY)->setTime(8, 0, 0);
            case self::TYPE_MONTHLY:
                return now()->addMonth()->startOfMonth()->setTime(9, 0, 0);
            case self::TYPE_CUSTOM:
                // For custom schedules, would need to parse the cron expression
                // This is a simplified implementation
                return now()->addHour();
            default:
                return NULL;
        }
    }

    /**
     * Get human-readable schedule description
     */
    public function getScheduleDescription(): string
    {
        switch ($this->type) {
            case self::TYPE_DAILY:
                return 'Daily at 6:00 AM';
            case self::TYPE_WEEKLY:
                return 'Weekly on Monday at 8:00 AM';
            case self::TYPE_MONTHLY:
                return 'Monthly on the 1st at 9:00 AM';
            case self::TYPE_CUSTOM:
                return 'Custom schedule: ' . ($this->schedule ?? 'Not configured');
            default:
                return 'Unknown schedule';
        }
    }

    /**
     * Get formatted sections list
     */
    public function getFormattedSections(): array
    {
        $availableSections = self::getAvailableSections();
        $formatted = [];

        foreach ($this->sections as $section) {
            $formatted[] = $availableSections[$section] ?? $section;
        }

        return $formatted;
    }

    /**
     * Get formatted recipients list
     */
    public function getFormattedRecipients(): string
    {
        return implode(', ', $this->recipients);
    }

    /**
     * Check if the report includes a specific section
     */
    public function includesSection(string $section): bool
    {
        return in_array($section, $this->sections);
    }

    /**
     * Add a section to the report
     */
    public function addSection(string $section): void
    {
        if (!$this->includesSection($section)) {
            $sections = $this->sections;
            $sections[] = $section;
            $this->sections = $sections;
        }
    }

    /**
     * Remove a section from the report
     */
    public function removeSection(string $section): void
    {
        $this->sections = array_filter($this->sections, function ($s) use ($section) {
            return $s !== $section;
        });
    }

    /**
     * Add a recipient to the report
     */
    public function addRecipient(string $email): void
    {
        if (!in_array($email, $this->recipients)) {
            $recipients = $this->recipients;
            $recipients[] = $email;
            $this->recipients = $recipients;
        }
    }

    /**
     * Remove a recipient from the report
     */
    public function removeRecipient(string $email): void
    {
        $this->recipients = array_filter($this->recipients, function ($r) use ($email) {
            return $r !== $email;
        });
    }

    /**
     * Activate the report
     */
    public function activate(): void
    {
        $this->is_active = TRUE;
        $this->save();
    }

    /**
     * Deactivate the report
     */
    public function deactivate(): void
    {
        $this->is_active = FALSE;
        $this->save();
    }

    /**
     * Get report size information
     */
    public function getSizeInfo(): array
    {
        $lastSize = $this->statistics['last_file_size'] ?? 0;

        return [
            'last_size'      => $lastSize,
            'formatted_size' => $this->formatBytes($lastSize),
            'avg_size'       => $this->statistics['avg_file_size'] ?? 0,
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($size ? log($size) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $size /= (1 << (10 * $pow));

        return round($size, $precision) . ' ' . $units[$pow];
    }
}
