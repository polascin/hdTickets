<?php

namespace App\Exports;

use App\Models\TicketPriceHistory;
use App\Models\ScrapedTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class PriceFluctuationExport implements WithMultipleSheets
{
    protected $trends;
    protected $startDate;
    protected $endDate;

    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

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
    protected $trends;
    protected $startDate;
    protected $endDate;

    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

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
                'ticket_id' => $trend->ticket_id,
                'title' => $ticket->title ?? 'Unknown Event',
                'platform' => $ticket->platform ?? 'Unknown',
                'avg_price' => round($trend->avg_price, 2),
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'volatility' => $volatility,
                'trend_direction' => $trendDirection,
                'data_points' => $priceHistory->count()
            ];
        });
    }

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
            'Data Points'
        ];
    }

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
            $item->data_points
        ];
    }

    private function calculateVolatility($priceHistory)
    {
        if ($priceHistory->count() < 2) {
            return 0;
        }

        $prices = $priceHistory->pluck('price')->toArray();
        $mean = array_sum($prices) / count($prices);
        $variance = array_sum(array_map(function($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices)) / count($prices);

        return sqrt($variance);
    }

    private function calculateTrendDirection($priceHistory)
    {
        if ($priceHistory->count() < 2) {
            return 'Stable';
        }

        $firstPrice = $priceHistory->first()->price;
        $lastPrice = $priceHistory->last()->price;
        $changePercent = (($lastPrice - $firstPrice) / $firstPrice) * 100;

        if ($changePercent > 5) {
            return 'Increasing';
        } elseif ($changePercent < -5) {
            return 'Decreasing';
        } else {
            return 'Stable';
        }
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

    public function charts()
    {
        $data = $this->collection();
        
        if ($data->isEmpty()) {
            return [];
        }

        // Create column chart for average prices
        $labels = [];
        $avgPrices = [];
        
        foreach ($data->take(10) as $index => $item) {
            $labels[] = new DataSeriesValues('String', 'Worksheet!$B$' . ($index + 2), null, 1);
            $avgPrices[] = new DataSeriesValues('Number', 'Worksheet!$D$' . ($index + 2), null, 1);
        }

        $dataSeriesLabels = [
            new DataSeriesValues('String', 'Worksheet!$D$1', null, 1),
        ];

        $xAxisTickValues = $labels;
        $dataSeriesValues = $avgPrices;

        $series = new DataSeries(
            DataSeries::TYPE_COLUMNCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('Average Price Comparison (Top 10 Events)');

        $chart = new Chart(
            'priceChart',
            $title,
            $legend,
            $plotArea
        );

        $chart->setTopLeftPosition('K2');
        $chart->setBottomRightPosition('S15');

        return [$chart];
    }
}

class PriceFluctuationDetailSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $trends;
    protected $startDate;
    protected $endDate;

    public function __construct($trends, $startDate, $endDate)
    {
        $this->trends = $trends;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

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
                    'ticket_id' => $trend->ticket_id,
                    'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
                    'price' => $record->price,
                    'quantity' => $record->quantity,
                    'source' => $record->source,
                    'price_change' => $record->price_change ?? 0
                ]);
            }
        }

        return $detailData;
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Recorded At',
            'Price ($)',
            'Quantity',
            'Source',
            'Price Change ($)'
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
