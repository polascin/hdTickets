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
    /** @var \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> */
    protected \Illuminate\Support\Collection $trends;

    protected string $startDate;

    protected string $endDate;

    /**
     * @param \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> $trends
     */
    public function __construct(\Illuminate\Support\Collection $trends, string $startDate, string $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
            0 => new PriceFluctuationSummarySheet($this->trends, $this->startDate, $this->endDate),
            1 => new PriceFluctuationDetailSheet($this->trends, $this->startDate, $this->endDate),
        ];
    }
}

/**
 * @implements WithMapping<object{ticket_id: int, title: string, platform: string, avg_price: float, min_price: float, max_price: float, volatility: float, trend_direction: string, data_points: int}>
 */
class PriceFluctuationSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCharts
{
    /** @var \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> */
    protected \Illuminate\Support\Collection $trends;

    protected string $startDate;

    protected string $endDate;

    /**
     * @param \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> $trends
     */
    public function __construct(\Illuminate\Support\Collection $trends, string $startDate, string $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{ticket_id: int, title: string, platform: string, avg_price: float, min_price: float, max_price: float, volatility: float, trend_direction: string, data_points: int}>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->trends)->map(function ($trend) {
            $ticket = ScrapedTicket::find($trend->ticket_id);
            $priceHistory = TicketPriceHistory::where('ticket_id', $trend->ticket_id)
                ->betweenDates($this->startDate, $this->endDate)
                ->orderBy('recorded_at')
                ->get();

            $minPrice = (float) ($priceHistory->min('price') ?? 0);
            $maxPrice = (float) ($priceHistory->max('price') ?? 0);
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
    /**
     * Headings
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
     * @param object{ticket_id: int, title: string, platform: string, avg_price: float, min_price: float, max_price: float, volatility: float, trend_direction: string, data_points: int} $item
     *
     * @return array<int, mixed> Array shape: [ticket_id, title, platform, avg_price, min_price, max_price, volatility, trend_direction, data_points]
     */
    /**
     * Map
     *
     * @param mixed $item
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
                    'startColor' => ['rgb' => '059669'],
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
     * @param \Illuminate\Support\Collection<int, object{price: float}> $priceHistory
     *
     * @return float Price volatility calculation
     */
    /**
     * CalculateVolatility
     */
    private function calculateVolatility(\Illuminate\Support\Collection $priceHistory): float
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
     * @param \Illuminate\Support\Collection<int, object{price: float}> $priceHistory
     *
     * @return string Trend direction (Increasing|Decreasing|Stable)
     */
    /**
     * CalculateTrendDirection
     */
    private function calculateTrendDirection(\Illuminate\Support\Collection $priceHistory): string
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
    /** @var \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> */
    protected \Illuminate\Support\Collection $trends;

    protected string $startDate;

    protected string $endDate;

    /**
     * @param \Illuminate\Support\Collection<int, object{ticket_id: int, avg_price: float}> $trends
     */
    public function __construct(\Illuminate\Support\Collection $trends, string $startDate, string $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{ticket_id: int, recorded_at: string, price: float, quantity: int, source: string, price_change: float}>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
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
    /**
     * Headings
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
