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
    /** @var mixed */
    protected $responseTimeData;

    /** @var array<string, mixed> */
    protected array $statistics;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct($data)
    {
        $this->responseTimeData = $data['data'] ?? collect();
        $this->statistics = $data['statistics'] ?? [];
    }

    /**
     * @return array<int, mixed>
     */
    public function sheets(): array
    {
        return [
            0 => new ResponseTimeDataSheet($this->responseTimeData),
            1 => new ResponseTimeStatsSheet($this->statistics),
        ];
    }
}

class ResponseTimeDataSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /** @var mixed */
    protected $responseTimeData;

    /**
     * @param mixed $responseTimeData
     */
    public function __construct($responseTimeData)
    {
        $this->responseTimeData = $responseTimeData;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->responseTimeData);
    }

    /**
     * @return array<int, string>
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
     * @param mixed $ticket
     *
     * @return array<int, mixed>
     */
    public function map($ticket): array
    {
        return [
            $ticket->id ?? '',
            $ticket->title ?? '',
            $ticket->priority ?? '',
            $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : '',
            $ticket->first_response_at ? $ticket->first_response_at->format('Y-m-d H:i:s') : '',
            $ticket->response_minutes ?? 0,
            $ticket->user->name ?? 'N/A',
            $ticket->assignedTo->name ?? 'Unassigned',
            $ticket->category->name ?? 'Uncategorized',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => TRUE, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
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
    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            ['Average Response Time', round($this->statistics['avg_response_time'] ?? 0, 2) . ' minutes'],
            ['Median Response Time', round($this->statistics['median_response_time'] ?? 0, 2) . ' minutes'],
            ['Fastest Response', ($this->statistics['fastest_response'] ?? 0) . ' minutes'],
            ['Slowest Response', ($this->statistics['slowest_response'] ?? 0) . ' minutes'],
            ['Within 1 Hour', $this->statistics['within_1_hour'] ?? 0],
            ['Within 4 Hours', $this->statistics['within_4_hours'] ?? 0],
            ['Within 24 Hours', $this->statistics['within_24_hours'] ?? 0],
        ]);
    }

    /**
     * @return array<int, string>
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
