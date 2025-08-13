<?php declare(strict_types=1);

namespace App\Exports;

use App\Models\ScrapedTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function is_array;

/**
 * @implements WithMapping<ScrapedTicket>
 */
class ScrapedTicketsExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var array<int, string> */
    protected array $fields;

    /** @var array<string, mixed> */
    protected array $filters;

    /**
     * @param array<int, string>   $fields
     * @param array<string, mixed> $filters
     */
    public function __construct(array $fields = [], array $filters = [])
    {
        $this->fields = $fields ?: [
            'id', 'event_name', 'venue', 'event_date', 'price', 'section',
            'row', 'quantity', 'platform', 'status', 'scraped_at',
        ];
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ScrapedTicket>
     */
    /**
     * Collection
     */
    public function collection(): \Illuminate\Database\Eloquent\Collection
    {
        $query = ScrapedTicket::with(['category']);

        // Apply filters
        if (! empty($this->filters['platform'])) {
            $query->where('platform', $this->filters['platform']);
        }

        if (! empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->where('event_date', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->where('event_date', '<=', $this->filters['date_to']);
        }

        if (! empty($this->filters['min_price'])) {
            $query->where('price', '>=', $this->filters['min_price']);
        }

        if (! empty($this->filters['max_price'])) {
            $query->where('price', '<=', $this->filters['max_price']);
        }

        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query->orderBy('scraped_at', 'desc')->get();
    }

    /**
     * @return array<int, string>
     */
    /**
     * Headings
     */
    public function headings(): array
    {
        $headings = [];

        foreach ($this->fields as $field) {
            $headings[] = match ($field) {
                'id'           => 'ID',
                'event_name'   => 'Event Name',
                'venue'        => 'Venue',
                'event_date'   => 'Event Date',
                'price'        => 'Price ($)',
                'section'      => 'Section',
                'row'          => 'Row',
                'quantity'     => 'Quantity',
                'platform'     => 'Platform',
                'status'       => 'Status',
                'scraped_at'   => 'Scraped At',
                'category'     => 'Category',
                'external_id'  => 'External ID',
                'url'          => 'Ticket URL',
                'seats'        => 'Seat Details',
                'listing_type' => 'Listing Type',
                default        => ucfirst(str_replace('_', ' ', $field)),
            };
        }

        return $headings;
    }

    /**
     * @param ScrapedTicket $ticket
     *
     * @return array<int, mixed|string> Array with values based on selected fields
     */
    /**
     * Map
     */
    public function map($ticket): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            $data[] = match ($field) {
                'event_date' => $ticket->event_date ? $ticket->event_date->format('Y-m-d H:i:s') : '',
                'price'      => '$' . number_format($ticket->price, 2),
                'platform'   => ucfirst($ticket->platform),
                'status'     => ucfirst($ticket->status),
                'scraped_at' => $ticket->scraped_at ? $ticket->scraped_at->format('Y-m-d H:i:s') : '',
                'category'   => $ticket->category ? $ticket->category->name : 'Uncategorized',
                'seats'      => is_array($ticket->seats) ? implode(', ', $ticket->seats) : $ticket->seats,
                default      => $ticket->{$field} ?? '',
            };
        }

        return $data;
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
            // Style the first row as header
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
