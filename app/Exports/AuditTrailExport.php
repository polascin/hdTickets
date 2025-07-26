<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AuditTrailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
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
            'Created At'
        ];
    }

    public function map($activity): array
    {
        return [
            $activity->id,
            $activity->log_name,
            ucfirst($activity->event),
            $activity->causer ? $activity->causer->name : 'System',
            $activity->causer ? $activity->causer->email : 'N/A',
            class_basename($activity->subject_type),
            $activity->subject_id,
            $activity->description,
            $this->formatChanges($activity->changes),
            $activity->properties['ip'] ?? 'N/A',
            $activity->properties['user_agent'] ?? 'N/A',
            $activity->created_at->format('Y-m-d H:i:s')
        ];
    }

    private function formatChanges(array $changes): string
    {
        if (empty($changes)) {
            return 'No changes recorded';
        }

        $formatted = [];
        
        if (isset($changes['old']) && isset($changes['attributes'])) {
            foreach ($changes['attributes'] as $field => $newValue) {
                $oldValue = $changes['old'][$field] ?? 'N/A';
                $formatted[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }

        return implode('; ', $formatted);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626']
                ]
            ],
        ];
    }
}
