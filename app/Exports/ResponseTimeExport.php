<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResponseTimeExport implements WithMultipleSheets
{
    protected $responseTimeData;
    protected $statistics;

    public function __construct($data)
    {
        $this->responseTimeData = $data['data'] ?? collect();
        $this->statistics = $data['statistics'] ?? [];
    }

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
    protected $responseTimeData;

    public function __construct($responseTimeData)
    {
        $this->responseTimeData = $responseTimeData;
    }

    public function collection()
    {
        return collect($this->responseTimeData);
    }

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
            'Category'
        ];
    }

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
            $ticket->category->name ?? 'Uncategorized'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ]
            ],
        ];
    }
}

class ResponseTimeStatsSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $statistics;

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

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

    public function headings(): array
    {
        return [
            'Metric',
            'Value'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1f2937']
                ]
            ],
        ];
    }
}
