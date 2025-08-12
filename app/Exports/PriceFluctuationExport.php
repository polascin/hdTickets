<?php declare(strict_types=1);

namespace App\Exports;

use App\Models\ScrapedTicket;
use App\Models\TicketPriceHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
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

class PriceFluctuationExport implements WithMultipleSheets
{
    /** @var mixed */
    protected $trends;

    /** @var mixed */
    protected $startDate;

    /** @var mixed */
    protected $endDate;

    /**
     * @param mixed $trends
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return array<int, mixed>
     */
    public function sheets(): array
    {
        return [
            0 => new PriceFluctuationSummarySheet($this->trends, $this->startDate, $this->endDate),
            1 => new PriceFluctuationDetailSheet($this->trends, $this->startDate, $this->endDate),
        ];
    }
}

class PriceFluctuationSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCharts
{
    /** @var mixed */
    protected $trends;

    /** @var mixed */
    protected $startDate;

    /** @var mixed */
    protected $endDate;

    /**
     * @param mixed $trends
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->trends)->map(function ($trend) {
            $ticket = ScrapedTicket::find($trend->ticket_id);
            $priceHistory = TicketPriceHistory::where('ticket_id', $trend->ticket_id)
                ->betweenDates($this->startDate, $this->endDate)
                ->orderBy('recorded_at')
                ->get();

            $minPrice = $priceHistory->min('price') ?? 0;
            $maxPrice = $priceHistory->max('price') ?? 0;
            $volatility = $this->calculateVolatility($priceHistory);
            $trendDirection = $this->calculateTrendDirection($priceHistory);

            return (object) [
                'ticket_id'       => $trend->ticket_id,
                'title'           => $ticket->title ?? 'Unknown Event',
                'platform'        => $ticket->platform ?? 'Unknown',
                'avg_price'       => round($trend->avg_price, 2),
                'min_price'       => $minPrice,
                'max_price'       => $maxPrice,
                'volatility'      => $volatility,
                'trend_direction' => $trendDirection,
                'data_points'     => $priceHistory->count(),
            ];
        });
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Ticket ID',
            'Event Title',
            'Platform',
            'Average Price ($)',
            'Min Price ($)',
            'Max Price ($)',
            'Price Volatility',
            'Trend Direction',
            'Data Points',
        ];
    }

    /**
     * @param mixed $item
     *
     * @return array<int, mixed>
     */
    public function map($item): array
    {
        return [
            $item->ticket_id,
            $item->title,
            ucfirst($item->platform),
            $item->avg_price,
            $item->min_price,
            $item->max_price,
            round($item->volatility, 2),
            $item->trend_direction,
            $item->data_points,
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

    /**
     * @return array<Chart>
     */
    public function charts(): array
    {
        $data = $this->collection();

        if ($data->isEmpty()) {
            return [];
        }

        // Create column chart for average prices
        $labels = [];
        $avgPrices = [];

        foreach ($data->take(10) as $index => $item) {
            $labels[] = new DataSeriesValues('String', 'Worksheet!$B$' . ($index + 2), NULL, 1);
            $avgPrices[] = new DataSeriesValues('Number', 'Worksheet!$D$' . ($index + 2), NULL, 1);
        }

        $dataSeriesLabels = [
            new DataSeriesValues('String', 'Worksheet!$D$1', NULL, 1),
        ];

        $xAxisTickValues = $labels;
        $dataSeriesValues = $avgPrices;

        $series = new DataSeries(
            DataSeries::TYPE_COLUMNCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues,
        );

        $plotArea = new PlotArea(NULL, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, NULL, FALSE);
        $title = new Title('Average Price Comparison (Top 10 Events)');

        $chart = new Chart(
            'priceChart',
            $title,
            $legend,
            $plotArea,
        );

        $chart->setTopLeftPosition('K2');
        $chart->setBottomRightPosition('S15');

        return [$chart];
    }

    /**
     * @param mixed $priceHistory
     */
    private function calculateVolatility($priceHistory): float
    {
        if ($priceHistory->count() < 2) {
            return 0;
        }

        $prices = $priceHistory->pluck('price')->toArray();
        $mean = array_sum($prices) / count($prices);
        $variance = array_sum(array_map(function ($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices)) / count($prices);

        return sqrt($variance);
    }

    /**
     * @param mixed $priceHistory
     */
    private function calculateTrendDirection($priceHistory): string
    {
        if ($priceHistory->count() < 2) {
            return 'Stable';
        }

        $firstPrice = $priceHistory->first()->price;
        $lastPrice = $priceHistory->last()->price;
        $changePercent = (($lastPrice - $firstPrice) / $firstPrice) * 100;

        if ($changePercent > 5) {
            return 'Increasing';
        }
        if ($changePercent < -5) {
            return 'Decreasing';
        }

        return 'Stable';
    }
}

class PriceFluctuationDetailSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var mixed */
    protected $trends;

    /** @var mixed */
    protected $startDate;

    /** @var mixed */
    protected $endDate;

    /**
     * @param mixed $trends
     * @param mixed $startDate
     * @param mixed $endDate
     */
    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $detailData = collect();

        foreach ($this->trends->take(5) as $trend) {
            $priceHistory = TicketPriceHistory::where('ticket_id', $trend->ticket_id)
                ->betweenDates($this->startDate, $this->endDate)
                ->orderBy('recorded_at')
                ->get();

            foreach ($priceHistory as $record) {
                $detailData->push([
                    'ticket_id'    => $trend->ticket_id,
                    'recorded_at'  => $record->recorded_at->format('Y-m-d H:i:s'),
                    'price'        => $record->price,
                    'quantity'     => $record->quantity,
                    'source'       => $record->source,
                    'price_change' => $record->price_change ?? 0,
                ]);
            }
        }

        return $detailData;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Ticket ID',
            'Recorded At',
            'Price ($)',
            'Quantity',
            'Source',
            'Price Change ($)',
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
