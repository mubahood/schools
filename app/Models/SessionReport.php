<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
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
        // Fix: use startOfDay/endOfDay so records throughout the entire end date are included
        $start_date = Carbon::parse($this->start_date)->startOfDay();
        $end_date   = Carbon::parse($this->end_date)->endOfDay();

        if ($end_date->lessThan($start_date)) {
            throw new \Exception("End date must be after start date");
        }

        // total_days is the inclusive count of calendar days
        $total_days          = Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
        $total_boys_present  = 0;
        $total_girls_present = 0;

        // ── Audience filter (class / stream / all) ────────────────────────────
        $audienceType = $this->target_audience_type ?? 'ALL';
        $audienceData = is_array($this->target_audience_data) ? $this->target_audience_data : [];

        // Build base participant query.
        // NOTE: participants.academic_class_id is 0/null in this dataset — use
        // admin_users.current_class_id (the student's enrolled class) for class matching.
        $query = Participant::where([
            'participants.enterprise_id' => $this->enterprise_id,
            'participants.type'          => $this->type,
        ])
            ->join('admin_users', 'participants.administrator_id', '=', 'admin_users.id')
            ->whereBetween('participants.created_at', [$start_date, $end_date])
            ->select('participants.*', 'admin_users.sex', 'admin_users.current_class_id');

        // Apply audience filter to participants
        if ($audienceType === 'CLASS' && !empty($audienceData['class_ids'])) {
            $query->whereIn('admin_users.current_class_id', $audienceData['class_ids']);
        } elseif ($audienceType === 'STREAM' && !empty($audienceData['stream_ids'])) {
            $studentIds = DB::table('student_has_classes')
                ->whereIn('stream_id', $audienceData['stream_ids'])
                ->pluck('administrator_id');
            $query->whereIn('participants.administrator_id', $studentIds);
        }

        $records = $query->get();

        // ── Determine grouping rows ───────────────────────────────────────────
        if ($audienceType === 'STREAM') {
            // Build stream query — join classes so we can order class→stream and label rows
            $streamQuery = DB::table('academic_class_sctreams as s')
                ->join('academic_classes as c', 's.academic_class_id', '=', 'c.id')
                ->where('s.enterprise_id', $this->enterprise_id)
                ->orderByRaw("c.short_name")
                ->orderBy('s.name')
                ->select('s.id', 's.name as stream_name', 'c.short_name as class_short', 'c.id as class_id');

            // If specific stream IDs were chosen, limit to them
            if (!empty($audienceData['stream_ids'])) {
                $streamQuery->whereIn('s.id', $audienceData['stream_ids']);
            }

            $streamRows = $streamQuery->get();

            $target_audience_data = [];
            foreach ($streamRows as $stream) {
                $streamStudentIds = DB::table('student_has_classes')
                    ->where('stream_id', $stream->id)
                    ->pluck('administrator_id')
                    ->toArray();

                $data = $this->_tally($records->whereIn('administrator_id', $streamStudentIds));
                // Label: "P.1 BLUE" — class prefix + stream name
                $data['title']      = $stream->class_short . ' ' . $stream->stream_name;
                $data['title_long'] = $stream->class_short . ' — ' . $stream->stream_name;

                $total_boys_present  += $data['male_present'];
                $total_girls_present += $data['female_present'];
                $target_audience_data[] = $data;
            }
        } else {
            // Group report by academic class
            if ($audienceType === 'CLASS' && !empty($audienceData['class_ids'])) {
                $classes = AcademicClass::whereIn('id', $audienceData['class_ids'])
                    ->orderBy('short_name')->get();
            } else {
                // Filter by active academic year so we don't return classes from past years
                $ent = $this->enterprise;
                $activeYear = $ent ? $ent->active_academic_year() : null;
                $classQuery = AcademicClass::where('enterprise_id', $this->enterprise_id)
                    ->orderBy('short_name');
                if ($activeYear) {
                    $classQuery->where('academic_year_id', $activeYear->id);
                }
                $classes = $classQuery->get();
            }

            $target_audience_data = [];
            foreach ($classes as $class) {
                $data = $this->_tally($records->where('current_class_id', $class->id));
                $data['title']      = $class->short_name;
                $data['title_long'] = $class->name ?? $class->short_name;

                $total_boys_present  += $data['male_present'];
                $total_girls_present += $data['female_present'];
                $target_audience_data[] = $data;
            }
        }

        // ── Top absentees / punctuals ─────────────────────────────────────────
        $absenteeQuery = Participant::where([
            'is_present'    => 0,
            'enterprise_id' => $this->enterprise_id,
            'type'          => $this->type,
        ])->whereBetween('created_at', [$start_date, $end_date]);

        $punctualQuery = Participant::where([
            'is_present'    => 1,
            'enterprise_id' => $this->enterprise_id,
            'type'          => $this->type,
        ])->whereBetween('created_at', [$start_date, $end_date]);

        if ($audienceType === 'CLASS' && !empty($audienceData['class_ids'])) {
            $absenteeQuery->whereIn('academic_class_id', $audienceData['class_ids']);
            $punctualQuery->whereIn('academic_class_id', $audienceData['class_ids']);
        } elseif ($audienceType === 'STREAM' && !empty($audienceData['stream_ids'])) {
            $streamStudentIds = DB::table('student_has_classes')
                ->whereIn('stream_id', $audienceData['stream_ids'])
                ->pluck('administrator_id');
            $absenteeQuery->whereIn('administrator_id', $streamStudentIds);
            $punctualQuery->whereIn('administrator_id', $streamStudentIds);
        }

        $top_absentees = $absenteeQuery
            ->groupBy('administrator_id')
            ->selectRaw('administrator_id, COUNT(*) as absence_count')
            ->orderByDesc('absence_count')
            ->take(10)
            ->get();

        $top_punctuals = $punctualQuery
            ->groupBy('administrator_id')
            ->selectRaw('administrator_id, COUNT(*) as punctual_count')
            ->orderByDesc('punctual_count')
            ->take(10)
            ->get();

        // ── Build title ───────────────────────────────────────────────────────
        $typeLabel = str_replace('_', ' ', $this->type ?? 'ATTENDANCE');
        $scopeLabel = '';
        if ($audienceType === 'CLASS' && !empty($audienceData['class_ids'])) {
            $names = AcademicClass::whereIn('id', $audienceData['class_ids'])->pluck('short_name')->join(', ');
            $scopeLabel = " — {$names}";
        } elseif ($audienceType === 'STREAM' && !empty($audienceData['stream_ids'])) {
            $names = DB::table('academic_class_sctreams')->whereIn('id', $audienceData['stream_ids'])->pluck('name')->join(', ');
            $scopeLabel = " — {$names}";
        }
        $this->title = "{$typeLabel} Report{$scopeLabel}: "
            . Carbon::parse($this->start_date)->format('d M Y')
            . ' to '
            . Carbon::parse($this->end_date)->format('d M Y');

        $this->total_days          = $total_days;
        $this->total_boys_present  = $total_boys_present;
        $this->total_girls_present = $total_girls_present;
        $this->target_audience_data = $target_audience_data;
        $this->top_absentees       = json_encode($top_absentees);
        $this->top_punctuals       = json_encode($top_punctuals);
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
     * Tally present/absent counts by sex from a collection of participant records.
     */
    private function _tally($rows): array
    {
        $malePresent   = $rows->where('sex', 'Male')->where('is_present', 1)->count();
        $maleAbsent    = $rows->where('sex', 'Male')->where('is_present', 0)->count();
        $femalePresent = $rows->where('sex', 'Female')->where('is_present', 1)->count();
        $femaleAbsent  = $rows->where('sex', 'Female')->where('is_present', 0)->count();
        $total         = $malePresent + $maleAbsent + $femalePresent + $femaleAbsent;

        return [
            'male_present'            => $malePresent,
            'male_absent'             => $maleAbsent,
            'female_present'          => $femalePresent,
            'female_absent'           => $femaleAbsent,
            'total_students'          => $total,
            'male_present_percentage'   => $total > 0 ? round($malePresent   / $total * 100, 2) : 0,
            'male_absent_percentage'    => $total > 0 ? round($maleAbsent    / $total * 100, 2) : 0,
            'female_present_percentage' => $total > 0 ? round($femalePresent / $total * 100, 2) : 0,
            'female_absent_percentage'  => $total > 0 ? round($femaleAbsent  / $total * 100, 2) : 0,
        ];
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
