<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\ProgressiveAssessment;
use App\Models\StudentTestRecord;
use App\Models\Subject;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StudentTestRecordController extends AdminController
{
    protected $title = 'Test Marks Entry';

    protected function grid()
    {
        $grid = new Grid(new StudentTestRecord());

        $u    = Admin::user();
        $ent  = $u->ent;
        $year = $ent->active_academic_year();

        $grid->perPages([200, 500, 1000]);

        $grid->export(function ($export) {
            $export->filename('test-records.csv');
            $export->except(['created_at', 'updated_at']);
            $export->originalValue(['t1_score','t2_score','t3_score','t4_score','t5_score',
                't6_score','t7_score','t8_score','t9_score','t10_score','average_score','remarks']);
        });

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('academic_class_id')
            ->orderBy('administrator_id');

        // Determine if teacher (restrict to their subjects)
        $isPrivileged = $u->isRole('administrator') || $u->isRole('dos') || $u->isRole('hm');

        if (!$isPrivileged) {
            // Must filter by a subject before showing rows
            if (!isset($_GET['subject_id'])) {
                // Show only records for this teacher's subjects
                $mySubjectIds = Subject::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->where(function ($q) use ($u) {
                    $q->where('subject_teacher', $u->id)
                      ->orWhere('teacher_1', $u->id)
                      ->orWhere('teacher_2', $u->id)
                      ->orWhere('teacher_3', $u->id);
                })->pluck('id')->toArray();

                if (empty($mySubjectIds)) {
                    admin_warning('No Records', 'You have no assigned subjects. Use the filter to select a subject.');
                    $grid->model()->where('enterprise_id', 0);
                } else {
                    $grid->model()->whereIn('subject_id', $mySubjectIds);
                }
            }
        }

        $grid->disableBatchActions();
        $grid->disableCreateButton();

        // Auto-expand filter for teachers
        $needsFilter = !$isPrivileged || !isset($_GET['progressive_assessment_id']);

        $grid->filter(function ($filter) use ($u, $year, $isPrivileged, $needsFilter) {
            if ($needsFilter) {
                $filter->expand();
            }
            $filter->disableIdFilter();

            // Progressive Assessment
            $paOptions = [];
            foreach (ProgressiveAssessment::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')->get() as $pa) {
                $paOptions[$pa->id] = $pa->title . ' (' . ($pa->term->name_text ?? '') . ')';
            }
            $filter->equal('progressive_assessment_id', 'Progressive Assessment')->select($paOptions);

            // Class
            $filter->equal('academic_class_id', 'Class')->select(
                AcademicClass::where(['enterprise_id' => $u->enterprise_id, 'academic_year_id' => $year->id])
                    ->orderBy('name')->get()->pluck('name_text', 'id')
            );

            // Stream
            $streams = [];
            foreach (AcademicClassSctream::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')->get() as $s) {
                if (!$s->academic_class || $s->academic_class->academic_year_id != $year->id) continue;
                $streams[$s->id] = $s->academic_class->short_name . ' – ' . $s->name;
            }
            $filter->equal('academic_class_sctream_id', 'Stream')->select($streams);

            // Subject
            $subjects = [];
            foreach (Subject::where('enterprise_id', $u->enterprise_id)
                ->orderBy('id', 'desc')->get() as $sub) {
                if (!$sub->academic_class || $sub->academic_class->academic_year_id != $year->id) continue;
                if (!$isPrivileged) {
                    if ($sub->subject_teacher != $u->id && $sub->teacher_1 != $u->id
                        && $sub->teacher_2 != $u->id && $sub->teacher_3 != $u->id) continue;
                }
                $subjects[$sub->id] = $sub->subject_name . ' – ' . $sub->academic_class->short_name;
            }
            $filter->equal('subject_id', 'Subject')->select($subjects);

            // Student
            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . '&user_type=student');
            $filter->equal('administrator_id', 'Student')
                ->select(function ($id) {
                    $a = Administrator::find($id);
                    return $a ? [$a->id => $a->name] : [];
                })->ajax($ajax_url);

            // Term
            $filter->equal('term_id', 'Term')->select(
                Term::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            );
        });

        // Determine number of tests from filtered PA (or max)
        $paId = $_GET['progressive_assessment_id'] ?? null;
        $numTests = 10;
        $pa = null;
        if ($paId) {
            $pa = ProgressiveAssessment::find($paId);
            if ($pa) $numTests = (int) $pa->number_of_tests;
        }

        // ── columns ──────────────────────────────────────────────────────────
        $grid->column('id', '#')->sortable()->hide();

        $grid->column('academic_class_id', 'Class')->display(function () {
            return $this->academic_class?->short_name ?? '—';
        })->sortable();

        $grid->column('academic_class_sctream_id', 'Stream')->display(function () {
            return $this->stream?->name ?? '—';
        })->sortable();

        $grid->column('administrator_id', 'Student')->display(function () {
            return $this->student?->name ?? '—';
        })->sortable();

        $grid->column('subject_id', 'Subject')->display(function () {
            return $this->subject?->subject_name ?? '—';
        })->sortable();

        // Test score columns — editable, shown only up to numTests
        for ($i = 1; $i <= $numTests; $i++) {
            $col   = "t{$i}_score";
            $label = "T{$i}";
            $grid->column($col, $label)->editable()->sortable();
        }

        $grid->column('average_score', 'Avg')->sortable();
        $grid->column('aggr_name', 'Grade')->sortable();
        $grid->column('aggr_value', 'Aggr')->sortable();
        $grid->column('remarks', 'Remarks')->editable()->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(StudentTestRecord::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('administrator_id', 'Student')->as(fn($v) => StudentTestRecord::find($id)?->student?->name ?? $v);
        $show->field('subject_id', 'Subject')->as(fn($v) => StudentTestRecord::find($id)?->subject?->subject_name ?? $v);
        for ($i = 1; $i <= 10; $i++) {
            $show->field("t{$i}_score", "Test {$i} Score");
            $show->field("t{$i}_submitted", "Test {$i} Submitted");
        }
        $show->field('average_score', 'Average Score');
        $show->field('aggr_name', 'Grade');
        $show->field('remarks', 'Remarks');
        return $show;
    }

    protected function form()
    {
        $form = new Form(new StudentTestRecord());
        $u    = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        $form->divider('Test Scores');
        for ($i = 1; $i <= 10; $i++) {
            $form->number("t{$i}_score", "Test {$i} Score")->min(0)->max(100);
        }
        $form->textarea('remarks', 'Remarks');

        return $form;
    }
}
