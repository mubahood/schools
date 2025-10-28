<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'title',
        'start_date',
        'end_date',
        'teacher_1_on_duty_id',
        'teacher_2_on_duty_id',
        'head_of_week_id',
        'total_days',
        'total_boys_present',
        'total_girls_present',
        'top_absentees',
        'top_punctuals',
        'remarks',
        'type',
        'pdf_processed',
        'pdf_path',
        'target_audience_type',
        'target_audience_data',
        'attendance_data'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_audience_data' => 'array',
        'attendance_data' => 'array'
    ];

    /**
     * Get target audience data as array
     */
    public function getTargetAudienceDataAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get the enterprise that owns the session report
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Get the first teacher on duty
     */
    public function teacher1()
    {
        return $this->belongsTo(User::class, 'teacher_1_on_duty_id');
    }

    /**
     * Get the second teacher on duty
     */
    public function teacher2()
    {
        return $this->belongsTo(User::class, 'teacher_2_on_duty_id');
    }

    /**
     * Get the head of week
     */
    public function headOfWeek()
    {
        return $this->belongsTo(User::class, 'head_of_week_id');
    }

    /**
     * Get total attendance
     */
    public function getTotalAttendanceAttribute()
    {
        return $this->total_boys_present + $this->total_girls_present;
    }

    /**
     * Get attendance percentage
     */
    public function getAttendancePercentageAttribute()
    {
        if ($this->total_days <= 0) {
            return 0;
        }

        $totalPossible = $this->total_days * 100; // Assuming 100 students per day
        $totalActual = $this->total_attendance;

        return $totalPossible > 0 ? round(($totalActual / $totalPossible) * 100, 2) : 0;
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for generated PDFs only
     */
    public function scopeWithPdf($query)
    {
        return $query->where('pdf_processed', 'Yes');
    }

    public function do_process()
    {

        $start_date = Carbon::parse($this->start_date);
        $end_date = Carbon::parse($this->end_date);
        if ($end_date->lessThan($start_date)) {
            throw new \Exception("End date must be after start date");
        }
        $total_days = $start_date->diffInDays($end_date) + 1;
        $total_boys_present = 0;
        $total_girls_present = 0;
        $top_absentees = 0;
        $top_punctuals = 0;

        // Get all participants in the date range with their student info
        $records = Participant::where([
            'participants.enterprise_id' => $this->enterprise_id,
            'participants.type' => $this->type,
        ])
            ->join('admin_users', 'participants.administrator_id', '=', 'admin_users.id')
            ->whereBetween('participants.created_at', [$start_date, $end_date])
            ->select('participants.*', 'admin_users.current_class_id', 'admin_users.sex')
            ->get();

        $ent = $this->enterprise;
        $activeTerm = $ent->active_term();
        $classes = AcademicClass::where('academic_year_id', $activeTerm->academic_year_id)->get();
        
        $target_audience_data = [];
        foreach ($classes as $class) {
            $data['title'] = $class->short_name;
            $data['title_long'] = $class->name;
            
            // Filter records by current_class_id from admin_users
            $classRecords = $records->where('current_class_id', $class->id);
            
            $data['male_present'] = $classRecords
                ->where('sex', 'Male')
                ->where('is_present', 1)
                ->count();
            
            $data['female_present'] = $classRecords
                ->where('sex', 'Female')
                ->where('is_present', 1)
                ->count();
            
            $data['male_absent'] = $classRecords
                ->where('sex', 'Male')
                ->where('is_present', 0)
                ->count();
            
            $data['female_absent'] = $classRecords
                ->where('sex', 'Female')
                ->where('is_present', 0)
                ->count();
            
            $data['total_students'] = $data['male_present'] + $data['female_present'] + $data['male_absent'] + $data['female_absent'];
            $data['male_present_percentage'] = $data['total_students'] > 0 ? round(($data['male_present'] / $data['total_students']) * 100, 2) : 0;
            $data['female_present_percentage'] = $data['total_students'] > 0 ? round(($data['female_present'] / $data['total_students']) * 100, 2) : 0;
            $data['male_absent_percentage'] = $data['total_students'] > 0 ? round(($data['male_absent'] / $data['total_students']) * 100, 2) : 0;
            $data['female_absent_percentage'] = $data['total_students'] > 0 ? round(($data['female_absent'] / $data['total_students']) * 100, 2) : 0;
            $target_audience_data[] = $data;
        }

        $top_absentees = Participant::where([
            'is_present' => 0,
            'enterprise_id' => $this->enterprise_id,
            'type' => $this->type,
        ])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('administrator_id')
            ->selectRaw('administrator_id, COUNT(*) as absence_count')
            ->orderByDesc('absence_count')
            ->take(10)
            ->get();
        $top_punctuals = Participant::where([
            'is_present' => 1,
            'enterprise_id' => $this->enterprise_id,
            'type' => $this->type,
        ])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('administrator_id')
            ->selectRaw('administrator_id, COUNT(*) as punctual_count')
            ->orderByDesc('punctual_count')
            ->take(10)
            ->get();
        $this->title = "{$this->type} Report: " . $start_date->toDateString() . " to " . $end_date->toDateString();

        $this->total_days = $total_days;
        $this->top_absentees = $top_absentees;
        $this->top_punctuals = $top_punctuals;
        $this->target_audience_data = $target_audience_data;
        $this->top_absentees = json_encode($top_absentees);
        $this->top_punctuals = json_encode($top_punctuals);
        $this->save();

        // Generate PDF
        try {
            $this->generatePDF();
        } catch (\Exception $e) {
            // Log error but don't fail the entire process
            Log::error("Failed to generate PDF for SessionReport {$this->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate PDF for this session report
     */
    public function generatePDF()
    {
        $ent = $this->enterprise;
        if (!$ent) {
            throw new \Exception("Enterprise not found for this report");
        }

        // Prepare data for PDF
        $data = [
            'report' => $this,
            'ent' => $ent,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.session-report', $data)
            ->setPaper('a4', 'portrait');

        // Create filename
        $filename = 'session-report-' . $this->id . '-' . date('Y-m-d-His') . '.pdf';
        $directory = 'session-reports/' . $ent->id . '/' . date('Y/m');
        
        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Full path
        $fullPath = $directory . '/' . $filename;

        // Save PDF to storage
        Storage::disk('public')->put($fullPath, $pdf->output());

        // Update report with PDF path
        $this->pdf_path = $fullPath;
        $this->pdf_processed = 'Yes';
        $this->save();

        return $fullPath;
    }
}
