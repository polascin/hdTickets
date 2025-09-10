<?php declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericArrayExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /** @var Collection<int, mixed> */
    protected Collection $data;

    /**
     * @param array<int, mixed>|Collection<int, mixed> $data
     * @param array<int, string>                       $headings
     */
    public function __construct(array $data, protected array $headings = [])
    {
        $this->data = $data instanceof Collection ? $data : collect($data);
    }

    /**
     * @return Collection<int, mixed>
     */
    /**
     * Collection
     */
    public function collection(): Collection
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
