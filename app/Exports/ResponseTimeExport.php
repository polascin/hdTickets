<?php declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResponseTimeExport implements WithMultipleSheets
{
    /** @var \Illuminate\Support\Collection<int, object> */
    protected \Illuminate\Support\Collection $responseTimeData;

    /** @var array<string, mixed> */
    protected array $statistics;

    /**
     * @param array{data?: \Illuminate\Support\Collection<int, object>, statistics?: array<string, mixed>} $data
     */
    public function __construct(array $data)
    {
        $this->responseTimeData = $data['data'] ?? collect();
        $this->statistics = $data['statistics'] ?? [];
    }

    /**
     * @return array<int, mixed>
     */
    /**
     * Sheets
     */
    public function sheets(): array
    {
        return [
            0 => new ResponseTimeDataSheet($this->responseTimeData),
            1 => new ResponseTimeStatsSheet($this->statistics),
        ];
    }
}

/**
 * @implements WithMapping<object>
 */
class ResponseTimeDataSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int, object> */
    protected \Illuminate\Support\Collection $responseTimeData;

    /**
     * @param \Illuminate\Support\Collection<int, object> $responseTimeData
     */
    public function __construct(\Illuminate\Support\Collection $responseTimeData)
    {
        $this->responseTimeData = $responseTimeData;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->responseTimeData);
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
            'Ticket ID',
            'Title',
            'Priority',
            'Created At',
            'First Response At',
            'Response Time (minutes)',
            'User',
            'Assigned To',
            'Category',
        ];
    }

    /**
     * @param object $ticket
     *
     * @return array<int, int|string> Array shape: [id, title, priority, created_at, first_response_at, response_minutes, user_name, assigned_to, category]
     */
    /**
     * Map
     *
     * @param object $ticket
     */
    public function map($ticket): array
    {
        return [
            $ticket->id ?? '',
            $ticket->title ?? '',
            $ticket->priority ?? '',
            $ticket->created_at ?? '',
            $ticket->first_response_at ?? '',
            $ticket->response_minutes ?? 0,
            $ticket->user_name ?? '',
            $ticket->assigned_to ?? '',
            $ticket->category ?? '',
        ];
    }

    /**
     * Apply styles to the sheet
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => TRUE]],
        ];
    }
}

class ResponseTimeStatsSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var array<string, mixed> */
    protected array $statistics;

    /**
     * @param array<string, mixed> $statistics
     */
    public function __construct(array $statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect([
            ['Average Response Time', (string) (round($this->statistics['avg_response_time'] ?? 0, 2) . ' minutes')],
            ['Median Response Time', (string) (round($this->statistics['median_response_time'] ?? 0, 2) . ' minutes')],
            ['Fastest Response', (string) (($this->statistics['fastest_response'] ?? 0) . ' minutes')],
            ['Slowest Response', (string) (($this->statistics['slowest_response'] ?? 0) . ' minutes')],
            ['Within 1 Hour', (string) ($this->statistics['within_1_hour'] ?? 0)],
            ['Within 4 Hours', (string) ($this->statistics['within_4_hours'] ?? 0)],
            ['Within 24 Hours', (string) ($this->statistics['within_24_hours'] ?? 0)],
        ]);
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
            'Metric',
            'Value',
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
            1 => [
                'font' => ['bold' => TRUE, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1f2937'],
                ],
            ],
        ];
    }
}
