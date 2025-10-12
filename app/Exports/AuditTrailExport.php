<?php declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Activitylog\Models\Activity;

/**
 * @implements WithMapping<Activity>
 */
class AuditTrailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(protected array $filters = [])
    {
    }

    /**
     * @return Collection<int, Activity>
     */
    /**
     * Collection
     */
    public function collection(): Collection
    {
        $query = Activity::with(['causer', 'subject']);

        // Apply filters
        if (!empty($this->filters['log_name'])) {
            $query->where('log_name', $this->filters['log_name']);
        }

        if (!empty($this->filters['event'])) {
            $query->where('event', $this->filters['event']);
        }

        if (!empty($this->filters['causer_id'])) {
            $query->where('causer_id', $this->filters['causer_id']);
        }

        if (!empty($this->filters['subject_type'])) {
            $query->where('subject_type', $this->filters['subject_type']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array<int, string>
     */
    /**
     * Headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Log Name',
            'Event',
            'User',
            'User Email',
            'Subject Type',
            'Subject ID',
            'Description',
            'Changes',
            'IP Address',
            'User Agent',
            'Created At',
        ];
    }

    /**
     * @param Activity $activity
     *
     * @return array<int, int|string|null> Array shape: [id, log_name, event, user_name, user_email, subject_type, subject_id, description, changes, ip, user_agent, created_at]
     */
    /**
     * Map
     *
     * @param mixed $activity
     */
    public function map($activity): array
    {
        $causerName = $activity->causer?->name;
        $causerEmail = $activity->causer?->email;
        $subjectType = $activity->subject_type;
        $createdAt = $activity->created_at;
        $properties = $activity->properties ?? [];

        return [
            $activity->id,
            $activity->log_name,
            ucfirst((string) $activity->event),
            $causerName ? (string) $causerName : 'System',
            $causerEmail ? (string) $causerEmail : 'N/A',
            $subjectType ? class_basename((string) $subjectType) : 'N/A',
            $activity->subject_id,
            $activity->description,
            $this->formatChanges($activity->changes ?? []),
            $properties['ip'] ?? 'N/A',
            $properties['user_agent'] ?? 'N/A',
            $createdAt?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * Styles
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as header
            1 => [
                'font' => ['bold' => TRUE, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed>|\Illuminate\Support\Collection<string, mixed> $changes
     *
     * @return string Formatted changes string
     */
    /**
     * FormatChanges
     *
     * @param \Illuminate\Support\Collection $changes
     */
    private function formatChanges(array|\Illuminate\Support\Collection $changes): string
    {
        if (empty($changes)) {
            return 'No changes recorded';
        }

        // Convert Collection to array if needed
        if ($changes instanceof \Illuminate\Support\Collection) {
            $changes = $changes->toArray();
        }

        $formatted = [];

        if (isset($changes['old'], $changes['attributes'])) {
            foreach ($changes['attributes'] as $field => $newValue) {
                $oldValue = $changes['old'][$field] ?? 'N/A';
                $formatted[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }

        return implode('; ', $formatted);
    }
}
