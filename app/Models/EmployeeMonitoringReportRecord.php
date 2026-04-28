<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonitoringReportRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'report_name',
        'report_type',
        'parameters',
        'generated_by',
        'generated_at',
        'pdf_path',
        'excel_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
