<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonitoringRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'term_id',
        'due_date',
        'monitored_on',
        'time_in',
        'time_out',
        'hours',
        'duration_minutes',
        'subject_id',
        'academic_class_id',
        'employee_id',
        'comment',
        'monitor_name',
        'monitor_role',
        'status',
        'created_by',
        'updated_by',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'date',
        'monitored_on' => 'date',
        'hours' => 'decimal:2',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $record) {
            if (!empty($record->time_in) && !empty($record->time_out)) {
                $in = strtotime((string) $record->time_in);
                $out = strtotime((string) $record->time_out);

                if ($in !== false && $out !== false && $out >= $in) {
                    $minutes = (int) round(($out - $in) / 60);
                    $record->duration_minutes = $minutes;
                    $record->hours = round($minutes / 60, 2);
                }
            }

            if (empty($record->monitored_on) && !empty($record->due_date)) {
                $record->monitored_on = $record->due_date;
            }
        });
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
