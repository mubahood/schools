<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeMonitoringReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Term',
            'Teacher',
            'Subject',
            'Class',
            'Time In',
            'Time Out',
            'Hours',
            'Monitor Name',
            'Monitor Role',
            'Status',
            'Comment',
        ];
    }
}
