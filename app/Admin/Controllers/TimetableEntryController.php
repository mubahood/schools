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
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class TimetableEntryController extends AdminController
{
    protected $title = 'Timetable Entries';

    protected function grid()
    {
        $u    = Admin::user();
        $grid = new Grid(new TimetableEntry());

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderByRaw('day_of_week, start_time')
            ->with(['academicClass', 'subject', 'teacher', 'room', 'stream', 'term']);

        $grid->disableExport();
        $grid->disableColumnSelector();

        $grid->column('id', '#')->sortable();
        $grid->column('day_label', 'Day')->display(function () {
            $colors = [1=>'#1b4332',2=>'#457b9d',3=>'#6a0572',4=>'#f4a261',5=>'#e63946',6=>'#2b9348'];
            $c = $colors[$this->day_of_week] ?? '#666';
            $name = TimetableEntry::$DAY_NAMES[$this->day_of_week] ?? '?';
            return "<span style='background:{$c};color:#fff;padding:3px 10px;border-radius:12px;font-size:.78rem;font-weight:700'>{$name}</span>";
        });
        $grid->column('start_time', 'Time')->display(function () {
            return "<code style='font-size:.85rem'>{$this->start_time} – {$this->end_time}</code>";
        });
        $grid->column('duration_minutes', 'Mins')->sortable();
        $grid->column('class_name', 'Class')->display(function () {
            $cls = optional($this->academicClass)->name ?? '—';
            $str = optional($this->stream)->name;
            return $str ? "{$cls} <small style='color:#888'>({$str})</small>" : $cls;
        });
        $grid->column('subject_name', 'Subject')->display(function () {
            $color = $this->display_color;
            $name  = optional($this->subject)->subject_name ?? '—';
            return "<span style='background:{$color};color:#fff;padding:2px 9px;border-radius:10px;font-size:.8rem'>{$name}</span>";
        });
        $grid->column('teacher_name', 'Teacher')->display(function () {
            return optional($this->teacher)->name ?? '—';
        });
        $grid->column('room_name', 'Room')->display(function () {
            return optional($this->room)->name ?? '—';
        });
        $grid->column('term_name', 'Term')->display(function () {
            return optional($this->term)->name ?? 'All terms';
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();
            $filter->equal('day_of_week', 'Day')->select(TimetableEntry::$DAY_NAMES);

            $classes = AcademicClass::where('enterprise_id', $u->enterprise_id)
                ->orderBy('name')->pluck('name', 'id');
            $filter->equal('academic_class_id', 'Class')->select($classes);

            $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
                ->orderBy('name')->pluck('name', 'id');
            $filter->equal('teacher_id', 'Teacher')->select($teachers);

            $terms = Term::where('enterprise_id', $u->enterprise_id)
                ->orderByDesc('id')->pluck('name', 'id');
            $filter->equal('term_id', 'Term')->select($terms);
        });

        // Header: link to visual view
        $grid->header(function () use ($u) {
            $viewUrl    = admin_url('timetable-view');
            $dashUrl    = admin_url('timetable-dashboard');
            $workUrl    = admin_url('timetable-workload');
            return '<div style="padding:10px 0 4px;display:flex;gap:8px;flex-wrap:wrap">'
                . "<a href='{$dashUrl}' class='btn btn-default btn-sm'><i class='fa fa-bar-chart'></i> Dashboard</a>"
                . "<a href='{$viewUrl}' class='btn btn-default btn-sm'><i class='fa fa-calendar'></i> Visual Timetable</a>"
                . "<a href='{$workUrl}' class='btn btn-default btn-sm'><i class='fa fa-user-clock'></i> Workload Analysis</a>"
                . '</div>';
        });

        return $grid;
    }

    protected function detail($id)
    {
        // Redirect to edit — no standalone show page needed
        return redirect(admin_url("timetable-entries/{$id}/edit"));
    }

    protected function form()
    {
        $u    = Admin::user();
        $ent  = $u->ent;
        $form = new Form(new TimetableEntry());

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->hidden('created_by_id')->default($u->id);

        // Row 1: Year & Term
        $years = AcademicYear::where('enterprise_id', $u->enterprise_id)
            ->orderByDesc('id')->pluck('name', 'id');
        $form->select('academic_year_id', 'Academic Year')
            ->options($years)
            ->default($ent->academic_year_id ?? null)
            ->rules('required');

        $terms = Term::where('enterprise_id', $u->enterprise_id)
            ->orderByDesc('id')->pluck('name', 'id');
        $form->select('term_id', 'Term (optional)')
            ->options(['' => '— All terms —'] + $terms->toArray());

        // Row 2: Class & Stream
        $classes = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')->pluck('name', 'id');
        $form->select('academic_class_id', 'Class')
            ->options($classes)
            ->rules('required')
            ->help('The class this period belongs to');

        $streams = AcademicClassSctream::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')->get()
            ->mapWithKeys(fn($s) => [$s->id => optional($s->academicClass)->name . ' — ' . $s->name]);
        $form->select('academic_class_sctream_id', 'Stream (optional)')
            ->options(['' => '— whole class —'] + $streams->toArray());

        // Row 3: Subject & Teacher
        $subjects = Subject::where('enterprise_id', $u->enterprise_id)
            ->orderBy('subject_name')->pluck('subject_name', 'id');
        $form->select('subject_id', 'Subject')
            ->options($subjects)
            ->rules('required');

        $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
            ->orderBy('name')->pluck('name', 'id');
        $form->select('teacher_id', 'Teacher')
            ->options($teachers)
            ->rules('required');

        // Row 4: Day & Time
        $form->select('day_of_week', 'Day of Week')
            ->options(TimetableEntry::$DAY_NAMES)
            ->rules('required');

        $form->time('start_time', 'Start Time')
            ->rules('required')
            ->default('07:40:00');

        $form->number('duration_minutes', 'Duration (minutes)')
            ->default(40)
            ->min(10)
            ->max(300)
            ->rules('required|integer|min:10')
            ->help('Common: 40 min (single), 80 min (double)');

        // Row 5: Room & Color
        $rooms = TimetableRoom::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)->orderBy('name')
            ->get()->mapWithKeys(fn($r) => [$r->id => $r->display_name]);
        $form->select('timetable_room_id', 'Room (optional)')
            ->options(['' => '— no room assigned —'] + $rooms->toArray());

        $form->color('color', 'Display Color (optional)')
            ->help('Leave blank to auto-assign color by subject');

        $form->textarea('notes', 'Notes')->rows(2);

        // Conflict checker script
        $checkUrl = admin_url('timetable/check-conflict');
        $form->html(<<<HTML
<div id="conflict-panel" style="display:none;margin-top:12px;padding:12px 16px;border-radius:8px;border:1px solid #e3e8ee;background:#f9fbfc">
    <div style="font-weight:700;font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;margin-bottom:8px">
        <i class="fa fa-shield"></i> Conflict Check
    </div>
    <div id="conflict-class"    style="margin:3px 0;font-size:.88rem"></div>
    <div id="conflict-teacher"  style="margin:3px 0;font-size:.88rem"></div>
    <div id="conflict-room"     style="margin:3px 0;font-size:.88rem"></div>
</div>
<script>
(function() {
    var CHECK_URL = '{$checkUrl}';
    var fields = ['academic_class_id','academic_class_sctream_id','teacher_id',
                  'timetable_room_id','day_of_week','start_time','duration_minutes'];
    function getVal(name) {
        var el = document.querySelector('[name="'+name+'"]');
        return el ? el.value : '';
    }
    function icon(ok) {
        return ok
            ? '<i class="fa fa-check-circle" style="color:#28a745"></i>'
            : '<i class="fa fa-exclamation-triangle" style="color:#dc3545"></i>';
    }
    function runCheck() {
        var classId    = getVal('academic_class_id');
        var teacherId  = getVal('teacher_id');
        var day        = getVal('day_of_week');
        var start      = getVal('start_time');
        var duration   = getVal('duration_minutes');
        if (!classId || !teacherId || !day || !start || !duration) return;
        document.getElementById('conflict-panel').style.display = 'block';
        ['class','teacher','room'].forEach(function(k) {
            document.getElementById('conflict-'+k).innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking '+k+'…';
        });
        var params = new URLSearchParams({
            class_id:    classId,
            stream_id:   getVal('academic_class_sctream_id'),
            teacher_id:  teacherId,
            room_id:     getVal('timetable_room_id'),
            day:         day,
            start:       start,
            duration:    duration,
            exclude_id:  getVal('id') || ''
        });
        fetch(CHECK_URL + '?' + params)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                document.getElementById('conflict-class').innerHTML   = icon(!d.class_conflict)   + ' Class: '   + (d.class_conflict   ? '<b style="color:#dc3545">Conflict — ' + d.class_conflict   + '</b>' : 'Free');
                document.getElementById('conflict-teacher').innerHTML = icon(!d.teacher_conflict) + ' Teacher: ' + (d.teacher_conflict ? '<b style="color:#dc3545">Conflict — ' + d.teacher_conflict + '</b>' : 'Free');
                document.getElementById('conflict-room').innerHTML    = icon(!d.room_conflict)    + ' Room: '    + (d.room_conflict    ? '<b style="color:#dc3545">Conflict — ' + d.room_conflict    + '</b>' : 'Free or not assigned');
            });
    }
    document.addEventListener('DOMContentLoaded', function() {
        fields.forEach(function(name) {
            var el = document.querySelector('[name="'+name+'"]');
            if (el) el.addEventListener('change', runCheck);
        });
    });
})();
</script>
HTML, 'Conflict Checker');

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        return $form;
    }
}
