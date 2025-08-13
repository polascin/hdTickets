<?php declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericArrayExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int, mixed> */
    protected \Illuminate\Support\Collection $data;

    /** @var array<int, string> */
    protected array $headings;

    /**
     * @param array<int, mixed>|\Illuminate\Support\Collection<int, mixed> $data
     * @param array<int, string>                                           $headings
     */
    public function __construct(array $data, array $headings = [])
    {
        $this->data = $data instanceof \Illuminate\Support\Collection ? $data : collect($data);
        $this->headings = $headings;
    }

    /**
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return $this->data;
    }

    /**
     * @return array<int, string>
     */
    /**
     * Headings
     */
    public function headings(): array
    {
        return $this->headings;
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
}
