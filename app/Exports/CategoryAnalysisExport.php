<?php declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
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
 * @implements WithMapping<array{name?: string, total_tickets?: int, resolved_tickets?: int, overdue_tickets?: int, resolution_rate?: float, avg_resolution_time?: float}>
 */
class CategoryAnalysisExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCharts
{
    /** @var array<int, array{name: string, total_tickets: int, resolved_tickets: int, overdue_tickets: int, resolution_rate: float, avg_resolution_time: float}> */
    protected array $categoryData;

    /**
     * @param array<int, array{name: string, total_tickets: int, resolved_tickets: int, overdue_tickets: int, resolution_rate: float, avg_resolution_time: float}> $categoryData
     */
    public function __construct(array $categoryData)
    {
        $this->categoryData = $categoryData;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{name: string, total_tickets: int, resolved_tickets: int, overdue_tickets: int, resolution_rate: float, avg_resolution_time: float}>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->categoryData);
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
            'Category Name',
            'Total Tickets',
            'Resolved Tickets',
            'Overdue Tickets',
            'Resolution Rate (%)',
            'Avg Resolution Time (hours)',
        ];
    }

    /**
     * @param array{name?: string, total_tickets?: int, resolved_tickets?: int, overdue_tickets?: int, resolution_rate?: float, avg_resolution_time?: float} $category
     *
     * @return array<int, float|int|string> Array shape: [name, total_tickets, resolved_tickets, overdue_tickets, resolution_rate, avg_resolution_time]
     */
    /**
     * Map
     */
    public function map($category): array
    {
        return [
            $category['name'] ?? 'Unknown',
            $category['total_tickets'] ?? 0,
            $category['resolved_tickets'] ?? 0,
            $category['overdue_tickets'] ?? 0,
            $category['resolution_rate'] ?? 0,
            $category['avg_resolution_time'] ?? 0,
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
            // Style the first row as header
            1 => [
                'font' => ['bold' => TRUE, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
            // Add borders and alternating row colors
            'A:F' => [
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

        // Chart for resolution rates
        $categories = [];
        $resolutionRates = [];

        foreach ($data as $index => $category) {
            $categories[] = new DataSeriesValues('String', 'Worksheet!$A$' . ($index + 2), NULL, 1);
            $resolutionRates[] = $category['resolution_rate'];
        }

        $dataSeriesLabels = [
            new DataSeriesValues('String', 'Worksheet!$E$1', NULL, 1),
        ];

        $xAxisTickValues = $categories;

        $dataSeriesValues = [
            new DataSeriesValues('Number', 'Worksheet!$E$2:$E$' . ($data->count() + 1), NULL, $data->count()),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues,
        );

        $plotArea = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, FALSE);
        $title = new Title('Category Resolution Rates');

        $chart = new Chart(
            'categoryChart',
            $title,
            $legend,
            $plotArea,
        );

        $chart->setTopLeftPosition('H2');
        $chart->setBottomRightPosition('P15');

        return [$chart];
    }
}
