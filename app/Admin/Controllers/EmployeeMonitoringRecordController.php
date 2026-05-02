<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\EmployeeMonitoringRecord;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class EmployeeMonitoringRecordController extends AdminController
{
    protected $title = 'Employee Monitoring Records';

    protected function grid()
    {
        $grid = new Grid(new EmployeeMonitoringRecord());
        $u = Admin::user();

        $grid->model()->where('enterprise_id', $u->enterprise_id)->orderBy('monitored_on', 'desc')->orderBy('id', 'desc');

        $grid->tools(function ($tools) {
            $tools->append('<a href="' . admin_url('employee-monitoring-dashboard') . '" class="btn btn-sm btn-info"><i class="fa fa-dashboard"></i> EMT Dashboard</a>');
        });

        $grid->disableBatchActions();

        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $filter->between('monitored_on', 'Date')->date();
            $filter->equal('term_id', 'Term')->select(
                Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            );
            $filter->equal('employee_id', 'Teacher')->select(
                User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
                    ->orderBy('name')->get()->pluck('name', 'id')
            );
            $filter->equal('subject_id', 'Subject')->select(
                Subject::where('enterprise_id', $u->enterprise_id)->orderBy('subject_name')->get()->pluck('subject_name', 'id')
            );
            $filter->equal('academic_class_id', 'Class')->select(
                AcademicClass::where('enterprise_id', $u->enterprise_id)->orderBy('name')->get()->pluck('name_text', 'id')
            );
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Skipped' => 'Skipped',
                'Completed' => 'Completed',
            ]);
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('monitored_on', 'Date')->display(function ($v) {
            return $v ? date('d M Y', strtotime($v)) : '-';
        })->sortable();
        $grid->column('term_id', 'Term')->display(function () {
            return optional($this->term)->name_text ?: '-';
        });
        $grid->column('employee_id', 'Teacher')->display(function () {
            return optional($this->employee)->name ?: '-';
        })->sortable();
        $grid->column('subject_id', 'Subject')->display(function () {
            return optional($this->subject)->subject_name ?: '-';
        });
        $grid->column('academic_class_id', 'Class')->display(function () {
            return optional($this->academicClass)->name_text ?: '-';
        });
        $grid->column('time_in', 'Time In');
        $grid->column('standard_time', 'Standard Time')->display(function ($v) {
            return $v ?: '<span class="text-muted">-</span>';
        });
        $grid->column('time_out', 'Time Out');
        $grid->column('hours', 'Hours')->sortable();
        $grid->column('_punctuality', 'Punctuality')->display(function () {
            if (empty($this->time_in) || empty($this->standard_time)) {
                return '<span class="label label-default">N/A</span>';
            }
            $stdSeconds = strtotime('1970-01-01 ' . $this->standard_time);
            $inSeconds  = strtotime('1970-01-01 ' . $this->time_in);
            if ($inSeconds === false || $stdSeconds === false) {
                return '<span class="label label-default">N/A</span>';
            }
            $diffMins = (int) round(($inSeconds - $stdSeconds) / 60);
            if ($diffMins <= 0) {
                return '<span class="label label-success">On Time</span>';
            }
            return '<span class="label label-danger">Late +' . $diffMins . ' min</span>';
        });
        $grid->column('monitor_name', 'Monitor');
        $grid->column('monitor_role', 'Role')->display(function ($v) {
            return $v ?: '-';
        });
        $grid->column('status', 'Status')->label([
            'Pending' => 'warning',
            'Skipped' => 'default',
            'Completed' => 'success',
        ]);
        $grid->column('comment', 'Comment')->limit(50);

        return $grid;
    }

    public function dashboard(Content $content)
    {
        $u = Admin::user();
        $query = EmployeeMonitoringRecord::where('enterprise_id', $u->enterprise_id);

        $total = (clone $query)->count();
        $thisWeek = (clone $query)->whereBetween('monitored_on', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count();
        $thisMonth = (clone $query)->whereMonth('monitored_on', now()->month)->whereYear('monitored_on', now()->year)->count();
        $pending = (clone $query)->where('status', 'Pending')->count();
        $skipped = (clone $query)->where('status', 'Skipped')->count();
        $completed = (clone $query)->where('status', 'Completed')->count();

        $totalHours = round((float) (clone $query)->sum('hours'), 2);
        $avgHours = $total > 0 ? round($totalHours / $total, 2) : 0.0;
        $avgDurationMinutes = (int) round((float) (clone $query)->avg('duration_minutes'));
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        $coverageRate = $total > 0
            ? round(((clone $query)->whereNotNull('time_in')->whereNotNull('time_out')->count() / $total) * 100, 1)
            : 0.0;

        $stats = [
            'total' => $total,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'pending' => $pending,
            'skipped' => $skipped,
            'completed' => $completed,
            'total_hours' => $totalHours,
            'avg_hours' => $avgHours,
            'avg_duration_minutes' => $avgDurationMinutes,
            'completion_rate' => $completionRate,
            'coverage_rate' => $coverageRate,
            'unique_teachers' => (clone $query)->whereNotNull('employee_id')->distinct('employee_id')->count('employee_id'),
            'unique_subjects' => (clone $query)->whereNotNull('subject_id')->distinct('subject_id')->count('subject_id'),
            'unique_classes' => (clone $query)->whereNotNull('academic_class_id')->distinct('academic_class_id')->count('academic_class_id'),
        ];

        $topTeachers = (clone $query)
            ->selectRaw('employee_id, count(*) as total, avg(hours) as avg_hours')
            ->whereNotNull('employee_id')
            ->groupBy('employee_id')
            ->orderByDesc('total')
            ->with('employee')
            ->limit(10)
            ->get();

        $topSubjects = (clone $query)
            ->selectRaw('subject_id, count(*) as total')
            ->whereNotNull('subject_id')
            ->groupBy('subject_id')
            ->orderByDesc('total')
            ->with('subject')
            ->limit(10)
            ->get();

        $topClasses = (clone $query)
            ->selectRaw('academic_class_id, count(*) as total, avg(hours) as avg_hours')
            ->whereNotNull('academic_class_id')
            ->groupBy('academic_class_id')
            ->orderByDesc('total')
            ->with('academicClass')
            ->limit(10)
            ->get();

        $statusBreakdown = [
            ['label' => 'Completed', 'count' => $completed],
            ['label' => 'Pending', 'count' => $pending],
            ['label' => 'Skipped', 'count' => $skipped],
        ];

        $start = now()->subDays(6)->startOfDay();
        $dailyRows = (clone $query)
            ->selectRaw('DATE(monitored_on) as day, count(*) as total, COALESCE(sum(hours), 0) as hours')
            ->whereDate('monitored_on', '>=', $start->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dailyMap = [];
        foreach ($dailyRows as $r) {
            $dailyMap[(string) $r->day] = [
                'total' => (int) $r->total,
                'hours' => round((float) $r->hours, 2),
            ];
        }

        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $row = $dailyMap[$key] ?? ['total' => 0, 'hours' => 0];
            $weeklyTrend[] = [
                'date' => $key,
                'label' => $day->format('D'),
                'count' => $row['total'],
                'hours' => $row['hours'],
            ];
        }

        $recent = (clone $query)->with(['employee', 'subject', 'academicClass'])->orderByDesc('id')->limit(15)->get();

        return $content
            ->title('Employee Monitoring Dashboard')
            ->description('Teacher monitoring trends and accountability snapshot')
            ->body(view('admin.employee-monitoring-dashboard', [
                'stats' => $stats,
                'topTeachers' => $topTeachers,
                'topSubjects' => $topSubjects,
                'topClasses' => $topClasses,
                'statusBreakdown' => $statusBreakdown,
                'weeklyTrend' => $weeklyTrend,
                'recent' => $recent,
            ]));
    }

    protected function detail($id)
    {
        $show = new Show(EmployeeMonitoringRecord::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('monitored_on', 'Monitoring Date');
        $show->field('due_date', 'Due Date');
        $show->field('term_id', 'Term')->as(function () {
            return optional($this->term)->name_text ?: '-';
        });
        $show->field('employee_id', 'Teacher')->as(function () {
            return optional($this->employee)->name ?: '-';
        });
        $show->field('subject_id', 'Subject')->as(function () {
            return optional($this->subject)->subject_name ?: '-';
        });
        $show->field('academic_class_id', 'Class')->as(function () {
            return optional($this->academicClass)->name_text ?: '-';
        });
        $show->field('time_in', 'Time In');
        $show->field('standard_time', 'Standard Entry Time');
        $show->field('time_out', 'Time Out');
        $show->field('hours', 'Hours');
        $show->field('duration_minutes', 'Duration (Minutes)');
        $show->field('monitor_name', 'Monitor Name');
        $show->field('monitor_role', 'Monitor Role');
        $show->field('status', 'Status');
        $show->field('comment', 'Comment');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new EmployeeMonitoringRecord());
        $u = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->hidden('created_by')->default($u->id);
        $form->hidden('updated_by')->default($u->id);

        $activeTerm = $u->ent ? $u->ent->active_term() : null;
        $activeDate = now()->toDateString();

        $form->date('monitored_on', 'Monitoring Date')->default($activeDate)->rules('required');
        $form->date('due_date', 'Due Date')->default($activeDate)->rules('required');

        $form->select('term_id', 'Due Term')
            ->options(Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get()->pluck('name_text', 'id'))
            ->default($activeTerm ? $activeTerm->id : null)
            ->rules('required');

        $form->select('employee_id', 'Teacher')
            ->options(User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])->orderBy('name')->get()->pluck('name', 'id'))
            ->rules('required');

        $form->select('subject_id', 'Subject')
            ->options(Subject::where('enterprise_id', $u->enterprise_id)->orderBy('subject_name')->get()->pluck('subject_name', 'id'))
            ->rules('required');

        $form->select('academic_class_id', 'Class')
            ->options(AcademicClass::where('enterprise_id', $u->enterprise_id)->orderBy('name')->get()->pluck('name_text', 'id'))
            ->rules('required');

        $form->time('time_in', 'Time In')->rules('required');
        $form->time('standard_time', 'Standard Entry Time')
            ->help('The expected/standard time the teacher should have entered class. Used to calculate punctuality (On Time / Late).')
            ->rules('nullable');
        $form->time('time_out', 'Time Out')->rules('required');
        $form->decimal('hours', 'Hours')->readonly();

        $form->text('monitor_name', 'Monitor Name')->rules('required');
        $form->select('monitor_role', 'Monitor Role')->options([
            'Class Monitor' => 'Class Monitor',
            'DOS' => 'DOS',
            'Other' => 'Other',
        ])->default('Class Monitor')->rules('required');

        $form->textarea('comment', 'Comment')->rows(3);
        $form->select('status', 'Status')->options([
            'Pending' => 'Pending',
            'Skipped' => 'Skipped',
            'Completed' => 'Completed',
        ])->default('Pending')->rules('required');

        $form->saving(function (Form $form) use ($u) {
            $form->updated_by = $u->id;

            if (!empty($form->time_in) && !empty($form->time_out) && strtotime((string) $form->time_out) < strtotime((string) $form->time_in)) {
                throw new \Exception('Time Out cannot be earlier than Time In.');
            }
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
