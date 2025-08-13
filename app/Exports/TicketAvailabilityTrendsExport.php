<?php declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function count;

/**
 * @implements WithMapping<mixed>
 */
class TicketAvailabilityTrendsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /** @var \Illuminate\Support\Collection<int, object{status: string, total: int}> */
    protected \Illuminate\Support\Collection $trends;

    protected string $startDate;

    protected string $endDate;

    /**
     * @param \Illuminate\Support\Collection<int, object{status: string, total: int}> $trends
     */
    public function __construct(\Illuminate\Support\Collection $trends, string $startDate, string $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{status: string, total: int}>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->trends);
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
            'Status',
            'Total Count',
            'Percentage (%)',
            'Trend Analysis',
        ];
    }

    /**
     * @param object{status: string, total: int} $trend
     *
     * @return array<int, float|int|string> Array shape: [status, total, percentage, trend_analysis]
     */
    /**
     * Map
     *
     * @param mixed $trend
     */
    public function map($trend): array
    {
        $total = collect($this->trends)->sum('total');
        $percentage = $total > 0 ? round(($trend->total / $total) * 100, 2) : 0;

        return [
            ucfirst($trend->status),
            $trend->total,
            $percentage,
            $this->analyzeTrend($trend->status, $trend->total),
        ];
    }

    /**
     * @return array<int|string, array<string, mixed>>
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
            'A:D' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, Chart>
     */
    /**
     * Charts
     */
    public function charts(): array
    {
        $data = $this->collection();

        if ($data->isEmpty()) {
            return [];
        }

        // Create pie chart for status distribution
        $labels = [];
        $values = [];

        foreach ($data as $index => $trend) {
            $labels[] = new DataSeriesValues('String', 'Worksheet!$A$' . ($index + 2), NULL, 1);
            $values[] = new DataSeriesValues('Number', 'Worksheet!$B$' . ($index + 2), NULL, 1);
        }

        $dataSeriesLabels = [
            new DataSeriesValues('String', 'Worksheet!$B$1', NULL, 1),
        ];

        $xAxisTickValues = $labels;
        $dataSeriesValues = $values;

        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            NULL,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues,
        );

        $plotArea = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, FALSE);
        $title = new Title('Ticket Availability Status Distribution');

        $chart = new Chart(
            'availabilityChart',
            $title,
            $legend,
            $plotArea,
        );

        $chart->setTopLeftPosition('F2');
        $chart->setBottomRightPosition('N15');

        return [$chart];
    }

    /**
     * @return string Trend analysis description
     */
    /**
     * AnalyzeTrend
     */
    private function analyzeTrend(string $status, int $count): string
    {
        switch ($status) {
            case 'active':
                return $count > 100 ? 'High availability' : 'Moderate availability';
            case 'sold_out':
                return $count > 50 ? 'High demand' : 'Normal demand';
            case 'expired':
                return $count > 20 ? 'Many expired listings' : 'Few expired listings';
            default:
                return 'Analysis pending';
        }
    }
}
