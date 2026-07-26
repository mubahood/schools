<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    protected $table = 'timetable_entries';

    const STATUS_DRAFT    = 'draft';
    const STATUS_ACTIVE   = 'active';
    const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'enterprise_id', 'academic_year_id', 'term_id',
        'academic_class_id', 'academic_class_sctream_id',
        'subject_id', 'teacher_id', 'timetable_room_id',
        'day_of_week', 'start_time', 'duration_minutes',
        'color', 'notes', 'is_active', 'status', 'created_by_id',
    ];

    public static $DAY_NAMES = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ];

    public static $DAY_COLORS = [
        1 => '#1b4332', 2 => '#457b9d', 3 => '#6a0572',
        4 => '#c77c00', 5 => '#c0392b', 6 => '#2b9348',
    ];

    public static $COLORS = [
        '#2d6a4f', '#0077b6', '#e63946', '#f4a261', '#457b9d',
        '#6a0572', '#2b9348', '#f72585', '#3a86ff', '#fb8500',
        '#8338ec', '#06d6a0',
    ];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }

    public function room()
    {
        return $this->belongsTo(TimetableRoom::class, 'timetable_room_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function startMinutes(): int
    {
        [$h, $m] = explode(':', substr($this->start_time, 0, 5));
        return (int)$h * 60 + (int)$m;
    }

    public function endMinutes(): int
    {
        return $this->startMinutes() + (int)$this->duration_minutes;
    }

    public function getDayNameAttribute(): string
    {
        return self::$DAY_NAMES[$this->day_of_week] ?? 'Unknown';
    }

    public function getDayColorAttribute(): string
    {
        return self::$DAY_COLORS[$this->day_of_week] ?? '#666';
    }

    public function getEndTimeAttribute(): string
    {
        $end = $this->endMinutes();
        return sprintf('%02d:%02d', intdiv($end, 60), $end % 60);
    }

    public function getDisplayColorAttribute(): string
    {
        if ($this->color) return $this->color;
        return self::$COLORS[(($this->subject_id ?? 0) - 1) % count(self::$COLORS)];
    }

    public static function checkConflicts(
        int $enterpriseId,
        int $dayOfWeek,
        string $startTime,
        int $durationMinutes,
        int $classId,
        int $teacherId,
        ?int $roomId = null,
        ?int $streamId = null,
        ?int $excludeId = null
    ): array {
        [$sh, $sm] = explode(':', substr($startTime, 0, 5));
        $startMin = (int)$sh * 60 + (int)$sm;
        $endMin   = $startMin + $durationMinutes;

        $base = TimetableEntry::where('enterprise_id', $enterpriseId)
            ->where('day_of_week', $dayOfWeek)
            ->whereIn('status', ['active', 'draft'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->with(['subject', 'teacher', 'academicClass']);

        $overlapFilter = function ($q) use ($startMin, $endMin) {
            $q->whereRaw("(TIME_TO_SEC(start_time)/60) < ?", [$endMin])
              ->whereRaw("(TIME_TO_SEC(start_time)/60 + duration_minutes) > ?", [$startMin]);
        };

        $classConflict = (clone $base)
            ->where('academic_class_id', $classId)
            ->where(function ($q) use ($streamId) {
                if ($streamId) {
                    $q->whereNull('academic_class_sctream_id')
                      ->orWhere('academic_class_sctream_id', $streamId);
                }
            })
            ->where($overlapFilter)->first();

        $teacherConflict = (clone $base)
            ->where('teacher_id', $teacherId)
            ->where($overlapFilter)->first();

        $roomConflict = null;
        if ($roomId) {
            $roomConflict = (clone $base)
                ->where('timetable_room_id', $roomId)
                ->where($overlapFilter)->first();
        }

        return compact('classConflict', 'teacherConflict', 'roomConflict');
    }
}
