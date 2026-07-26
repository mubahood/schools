<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TimetableEntry;
use App\Models\TimetableRoom;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    // ─────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────
    public function dashboard(Content $content)
    {
        $u   = Admin::user();
        $ent = $u->ent;

        $yearId = request('year_id', $ent->academic_year_id);
        $termId = request('term_id');

        $base = TimetableEntry::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)
            ->where('academic_year_id', $yearId)
            ->when($termId, fn($q) => $q->where('term_id', $termId));

        // Stats
        $totalPeriods      = (clone $base)->count();
        $totalClasses      = (clone $base)->distinct('academic_class_id')->count('academic_class_id');
        $totalTeachers     = (clone $base)->distinct('teacher_id')->count('teacher_id');
        $totalHoursPerWeek = (clone $base)->sum(DB::raw('duration_minutes / 60'));

        // Periods by day
        $byDay = (clone $base)->select('day_of_week', DB::raw('count(*) as cnt'))
            ->groupBy('day_of_week')->orderBy('day_of_week')->get()
            ->mapWithKeys(fn($r) => [TimetableEntry::$DAY_NAMES[$r->day_of_week] => $r->cnt]);

        // Top 10 teacher loads
        $teacherLoads = (clone $base)->select('teacher_id',
                DB::raw('count(*) as periods'),
                DB::raw('sum(duration_minutes) as total_mins'))
            ->groupBy('teacher_id')->orderByDesc('periods')->limit(10)->get()
            ->map(function ($r) {
                $r->teacher_name = optional(User::find($r->teacher_id))->name ?? 'Unknown';
                return $r;
            });

        // Class schedule completeness: only classes belonging to the selected academic year
        $classStats = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->where('academic_year_id', $yearId)
            ->orderBy('name')->get()->map(function ($c) use ($base, $yearId) {
            $totalSubjects    = Subject::where('academic_class_id', $c->id)->count();
            $scheduledSubjects = (clone $base)->where('academic_class_id', $c->id)
                ->distinct('subject_id')->count('subject_id');
            return [
                'name'       => $c->name,
                'total'      => $totalSubjects,
                'scheduled'  => $scheduledSubjects,
                'periods'    => (clone $base)->where('academic_class_id', $c->id)->count(),
            ];
        })->values();

        $years = AcademicYear::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();
        $terms = Term::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();

        return $content
            ->title('Timetable Dashboard')
            ->breadcrumb(['text' => 'Timetable', 'url' => '#'], ['text' => 'Dashboard'])
            ->body(view('admin.timetable.dashboard', compact(
                'totalPeriods', 'totalClasses', 'totalTeachers', 'totalHoursPerWeek',
                'byDay', 'teacherLoads', 'classStats', 'years', 'terms', 'yearId', 'termId'
            )));
    }

    // ─────────────────────────────────────────────
    // VISUAL TIMETABLE VIEW
    // ─────────────────────────────────────────────
    public function view(Content $content)
    {
        $u   = Admin::user();
        $ent = $u->ent;

        $years    = AcademicYear::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();
        $terms    = Term::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();
        $classes  = AcademicClass::where('enterprise_id', $u->enterprise_id)->orderBy('name')->get();
        $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
            ->orderBy('name')->get();
        $rooms    = TimetableRoom::where('enterprise_id', $u->enterprise_id)->where('is_active', 1)->orderBy('name')->get();
        $streams  = AcademicClassSctream::where('enterprise_id', $u->enterprise_id)->orderBy('name')->get();

        $defaultYearId = $ent->academic_year_id;
        $defaultTermId = optional($ent->dpTerm())->id;

        return $content
            ->title('Timetable View')
            ->breadcrumb(['text' => 'Timetable', 'url' => '#'], ['text' => 'View'])
            ->body(view('admin.timetable.view', compact(
                'years', 'terms', 'classes', 'teachers', 'rooms', 'streams',
                'defaultYearId', 'defaultTermId'
            )));
    }

    // ─────────────────────────────────────────────
    // WORKLOAD ANALYSIS
    // ─────────────────────────────────────────────
    public function workload(Content $content)
    {
        $u   = Admin::user();
        $ent = $u->ent;

        $yearId   = request('year_id', $ent->academic_year_id);
        $termId   = request('term_id');
        $teacherId = request('teacher_id');

        $base = TimetableEntry::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)
            ->where('academic_year_id', $yearId)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->with(['subject', 'academicClass', 'stream', 'room']);

        $teacherIds = $teacherId
            ? [$teacherId]
            : (clone $base)->distinct('teacher_id')->pluck('teacher_id')->toArray();

        $workloads = collect($teacherIds)->map(function ($tid) use ($base) {
            $entries = (clone $base)->where('teacher_id', $tid)->get();
            $byDay   = [];
            foreach (TimetableEntry::$DAY_NAMES as $num => $name) {
                $byDay[$num] = $entries->where('day_of_week', $num)->values();
            }
            $totalPeriods  = $entries->count();
            $totalMins     = $entries->sum('duration_minutes');
            $hoursPerWeek  = round($totalMins / 60, 1);

            return [
                'teacher'       => User::find($tid),
                'by_day'        => $byDay,
                'total_periods' => $totalPeriods,
                'hours_per_week'=> $hoursPerWeek,
                'status'        => $hoursPerWeek <= 20 ? 'normal' : ($hoursPerWeek <= 30 ? 'warning' : 'danger'),
            ];
        })->filter(fn($w) => $w['teacher'] !== null)->sortByDesc('hours_per_week')->values();

        $years    = AcademicYear::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();
        $terms    = Term::where('enterprise_id', $u->enterprise_id)->orderByDesc('id')->get();
        $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
            ->orderBy('name')->get();

        return $content
            ->title('Teacher Workload Analysis')
            ->breadcrumb(['text' => 'Timetable', 'url' => '#'], ['text' => 'Workload'])
            ->body(view('admin.timetable.workload', compact(
                'workloads', 'years', 'terms', 'teachers', 'yearId', 'termId', 'teacherId'
            )));
    }

    // ─────────────────────────────────────────────
    // PDF EXPORT
    // ─────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $u      = Admin::user();
        $ent    = $u->ent;
        $yearId = $request->get('year_id', $ent->academic_year_id);
        $termId = $request->get('term_id');
        $classId   = $request->get('class_id');
        $teacherId = $request->get('teacher_id');

        $entries = TimetableEntry::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)
            ->where('academic_year_id', $yearId)
            ->when($termId,    fn($q) => $q->where('term_id', $termId))
            ->when($classId,   fn($q) => $q->where('academic_class_id', $classId))
            ->when($teacherId, fn($q) => $q->where('teacher_id', $teacherId))
            ->with(['subject', 'academicClass', 'stream', 'teacher', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $year    = AcademicYear::find($yearId);
        $term    = Term::find($termId);
        $class   = AcademicClass::find($classId);
        $teacher = User::find($teacherId);

        // Build grid: rows = unique time slots, cols = days
        $timeSlots = $entries->pluck('start_time')->unique()->sort()->values();
        $grid = [];
        foreach ($timeSlots as $slot) {
            $row = ['time' => $slot];
            foreach (TimetableEntry::$DAY_NAMES as $day => $name) {
                $row[$day] = $entries->where('day_of_week', $day)
                    ->where('start_time', $slot)->values();
            }
            $grid[] = $row;
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('print.timetable', compact('entries', 'grid', 'timeSlots', 'ent', 'year', 'term', 'class', 'teacher'))
            ->setPaper('a4', 'landscape');

        $filename = 'Timetable-' . ($class ? $class->name . '-' : '') . ($teacher ? $teacher->name . '-' : '') . now()->format('Y-m-d') . '.pdf';
        return $pdf->stream($filename);
    }

    // ─────────────────────────────────────────────
    // AJAX: entries for the visual grid
    // ─────────────────────────────────────────────
    public function entriesApi(Request $request): JsonResponse
    {
        $u = Admin::user();

        $entries = TimetableEntry::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)
            ->when($request->year_id,   fn($q) => $q->where('academic_year_id', $request->year_id))
            ->when($request->term_id,   fn($q) => $q->where('term_id', $request->term_id))
            ->when($request->class_id,  fn($q) => $q->where('academic_class_id', $request->class_id))
            ->when($request->stream_id, fn($q) => $q->where('academic_class_sctream_id', $request->stream_id))
            ->when($request->teacher_id,fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->room_id,   fn($q) => $q->where('timetable_room_id', $request->room_id))
            ->with(['subject', 'academicClass', 'stream', 'teacher', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(function ($e) {
                return [
                    'id'           => $e->id,
                    'day'          => $e->day_of_week,
                    'day_name'     => $e->day_name,
                    'start_time'   => substr($e->start_time, 0, 5),
                    'end_time'     => $e->end_time,
                    'duration'     => $e->duration_minutes,
                    'subject'      => optional($e->subject)->subject_name ?? '—',
                    'class'        => optional($e->academicClass)->name ?? '—',
                    'stream'       => optional($e->stream)->name,
                    'teacher'      => optional($e->teacher)->name ?? '—',
                    'room'         => optional($e->room)->name,
                    'color'        => $e->display_color,
                    'edit_url'     => admin_url('timetable-entries/' . $e->id . '/edit'),
                    'notes'        => $e->notes,
                ];
            });

        return response()->json($entries);
    }

    // ─────────────────────────────────────────────
    // AJAX: conflict check
    // ─────────────────────────────────────────────
    public function checkConflict(Request $request): JsonResponse
    {
        $u = Admin::user();

        $conflicts = TimetableEntry::checkConflicts(
            enterpriseId:    $u->enterprise_id,
            dayOfWeek:       (int) $request->day,
            startTime:       $request->start,
            durationMinutes: (int) $request->duration,
            classId:         (int) $request->class_id,
            teacherId:       (int) $request->teacher_id,
            roomId:          $request->room_id ? (int) $request->room_id : null,
            streamId:        $request->stream_id ? (int) $request->stream_id : null,
            excludeId:       $request->exclude_id ? (int) $request->exclude_id : null,
        );

        $fmt = function ($e) {
            if (!$e) return null;
            $subj = optional($e->subject)->subject_name ?? '?';
            $cls  = optional($e->academicClass)->name ?? '?';
            return "{$subj} ({$cls}, {$e->start_time}–{$e->end_time})";
        };

        return response()->json([
            'class_conflict'   => $fmt($conflicts['classConflict']),
            'teacher_conflict' => $fmt($conflicts['teacherConflict']),
            'room_conflict'    => $fmt($conflicts['roomConflict']),
        ]);
    }

    // ─────────────────────────────────────────────
    // ENTRIES CRUD API
    // ─────────────────────────────────────────────

    private function formatEntryJson(TimetableEntry $e): array
    {
        $e->loadMissing(['subject', 'academicClass', 'stream', 'teacher', 'room']);
        return [
            'id'           => $e->id,
            'day'          => $e->day_of_week,
            'day_name'     => $e->day_name,
            'day_color'    => $e->day_color,
            'start_time'   => substr($e->start_time, 0, 5),
            'end_time'     => $e->end_time,
            'duration'     => $e->duration_minutes,
            'subject'      => optional($e->subject)->subject_name ?? '—',
            'subject_id'   => $e->subject_id,
            'class'        => optional($e->academicClass)->name ?? '—',
            'class_id'     => $e->academic_class_id,
            'stream'       => optional($e->stream)->name,
            'stream_id'    => $e->academic_class_sctream_id,
            'teacher'      => optional($e->teacher)->name ?? '—',
            'teacher_id'   => $e->teacher_id,
            'room'         => optional($e->room)->name,
            'room_id'      => $e->timetable_room_id,
            'color'        => $e->display_color,
            'raw_color'    => $e->color,
            'notes'        => $e->notes,
            'status'       => $e->status ?? 'active',
        ];
    }

    public function apiList(Request $request): JsonResponse
    {
        $u = Admin::user();
        $entries = TimetableEntry::where('enterprise_id', $u->enterprise_id)
            ->when($request->class_id,   fn($q) => $q->where('academic_class_id', $request->class_id))
            ->when($request->teacher_id, fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->day,        fn($q) => $q->where('day_of_week', $request->day))
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->with(['subject', 'academicClass', 'stream', 'teacher', 'room'])
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get()->map(fn($e) => $this->formatEntryJson($e));
        return response()->json($entries);
    }

    public function apiShow($id): JsonResponse
    {
        $u = Admin::user();
        $entry = TimetableEntry::where('enterprise_id', $u->enterprise_id)->findOrFail($id);
        return response()->json($this->formatEntryJson($entry));
    }

    public function apiStore(Request $request): JsonResponse
    {
        $u = Admin::user();
        $data = $request->validate([
            'academic_class_id'           => 'required|integer',
            'academic_class_sctream_id'   => 'nullable|integer',
            'subject_id'                  => 'required|integer',
            'teacher_id'                  => 'required|integer',
            'timetable_room_id'           => 'nullable|integer',
            'day_of_week'                 => 'required|integer|between:1,6',
            'start_time'                  => 'required|string',
            'duration_minutes'            => 'required|integer|min:10|max:300',
            'color'                       => 'nullable|string|max:20',
            'notes'                       => 'nullable|string',
            'status'                      => 'nullable|in:draft,active,disabled',
        ]);
        $data['enterprise_id'] = $u->enterprise_id;
        $data['created_by_id'] = $u->id;
        $data['status']        = $data['status'] ?? 'active';
        $data['is_active']     = $data['status'] === 'active' ? 1 : 0;
        $entry = TimetableEntry::create($data);
        return response()->json(['success' => true, 'entry' => $this->formatEntryJson($entry)]);
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $u = Admin::user();
        $entry = TimetableEntry::where('enterprise_id', $u->enterprise_id)->findOrFail($id);
        $data = $request->validate([
            'academic_class_id'           => 'required|integer',
            'academic_class_sctream_id'   => 'nullable|integer',
            'subject_id'                  => 'required|integer',
            'teacher_id'                  => 'required|integer',
            'timetable_room_id'           => 'nullable|integer',
            'day_of_week'                 => 'required|integer|between:1,6',
            'start_time'                  => 'required|string',
            'duration_minutes'            => 'required|integer|min:10|max:300',
            'color'                       => 'nullable|string|max:20',
            'notes'                       => 'nullable|string',
            'status'                      => 'nullable|in:draft,active,disabled',
        ]);
        $data['is_active'] = ($data['status'] ?? 'active') === 'active' ? 1 : 0;
        $entry->update($data);
        return response()->json(['success' => true, 'entry' => $this->formatEntryJson($entry->fresh())]);
    }

    public function apiDestroy($id): JsonResponse
    {
        $u = Admin::user();
        TimetableEntry::where('enterprise_id', $u->enterprise_id)->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function apiDuplicate($id): JsonResponse
    {
        $u = Admin::user();
        $orig = TimetableEntry::where('enterprise_id', $u->enterprise_id)->findOrFail($id);
        $copy = $orig->replicate();
        $copy->status     = 'draft';
        $copy->is_active  = 0;
        $copy->created_by_id = $u->id;
        $copy->save();
        return response()->json(['success' => true, 'entry' => $this->formatEntryJson($copy)]);
    }

    // ─────────────────────────────────────────────
    // CASCADE AJAX
    // ─────────────────────────────────────────────

    public function apiStreamsByClass(Request $request): JsonResponse
    {
        $u = Admin::user();
        $streams = AcademicClassSctream::where('enterprise_id', $u->enterprise_id)
            ->where('academic_class_id', $request->class_id)
            ->orderBy('name')->get(['id', 'name']);
        return response()->json($streams);
    }

    public function apiSubjectsByClass(Request $request): JsonResponse
    {
        $u = Admin::user();
        $subjects = Subject::where('enterprise_id', $u->enterprise_id)
            ->where('academic_class_id', $request->class_id)
            ->orderBy('subject_name')->get(['id', 'subject_name as name']);
        return response()->json($subjects);
    }
}
