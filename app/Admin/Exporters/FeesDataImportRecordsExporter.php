<?php

namespace App\Admin\Exporters;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Utils;

class FeesDataImportRecordsExporter extends AbstractExporter implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $fileName = 'Fees_Import_Records';
    protected $columns = [
        'id' => 'Record ID',
        'import_batch' => 'Import Batch',
        'row_number' => 'Row #',
        'student_name' => 'Student Name',
        'reg_number' => 'Reg Number',
        'school_pay' => 'School Pay',
        'previous_balance' => 'Previous Balance',
        'updated_balance' => 'Updated Balance',
        'total_amount' => 'Total Amount',
        'status' => 'Status',
        'retry_count' => 'Retries',
        'summary' => 'Summary',
        'error_message' => 'Error Message',
        'processed_at' => 'Processed At',
        'created_at' => 'Created At',
    ];

    public function collection()
    {
        return $this->getData()->load(['import', 'user', 'account']);
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->import ? ($record->import->batch_identifier ?? $record->fees_data_import_id) : $record->fees_data_import_id,
            $record->index,
            $record->user ? $record->user->name : 'N/A',
            $record->reg_number ?? '',
            $record->school_pay ?? '',
            $this->formatAmount($record->previous_fees_term_balance),
            $this->formatAmount($record->updated_balance),
            $this->formatAmount($record->total_amount),
            $record->status,
            $record->retry_count ?? 0,
            $record->summary ?? '',
            $record->error_message ?? '',
            $record->processed_at ? Utils::my_date_3($record->processed_at) : '',
            $record->created_at ? Utils::my_date_3($record->created_at) : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3c8dbc']
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Import Records';
    }

    protected function formatAmount($amount): string
    {
        if (is_null($amount)) {
            return '';
        }

        if ($amount > 0) {
            return 'UGX ' . number_format($amount, 2) . ' (Debt)';
        } elseif ($amount < 0) {
            return 'UGX ' . number_format(abs($amount), 2) . ' (Credit)';
        }

        return 'UGX 0.00';
    }

    /**
     * Export data to Excel file
     */
    public function export()
    {
        return Excel::download($this, $this->fileName . '_' . date('Y-m-d_His') . '.xlsx');
    }
}
