<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $fields;
    protected $filters;

    public function __construct(array $fields = [], array $filters = [])
    {
        $this->fields = $fields ?: [
            'id', 'name', 'email', 'role', 'email_verified_at', 
            'last_login_at', 'created_at', 'updated_at'
        ];
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::query();

        // Apply filters
        if (!empty($this->filters['role'])) {
            $query->where('role', $this->filters['role']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['verified_only'])) {
            $query->whereNotNull('email_verified_at');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        $headings = [];
        
        foreach ($this->fields as $field) {
            $headings[] = match($field) {
                'id' => 'ID',
                'name' => 'Full Name',
                'email' => 'Email Address',
                'role' => 'User Role',
                'email_verified_at' => 'Email Verified',
                'last_login_at' => 'Last Login',
                'created_at' => 'Registration Date',
                'updated_at' => 'Last Updated',
                'phone' => 'Phone Number',
                'address' => 'Address',
                'city' => 'City',
                'country' => 'Country',
                'timezone' => 'Timezone',
                default => ucfirst(str_replace('_', ' ', $field))
            };
        }

        return $headings;
    }

    public function map($user): array
    {
        $data = [];
        
        foreach ($this->fields as $field) {
            $data[] = match($field) {
                'role' => ucfirst($user->role),
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'Not Verified',
                'last_login_at' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                default => $user->{$field} ?? ''
            };
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ]
            ],
        ];
    }
}
