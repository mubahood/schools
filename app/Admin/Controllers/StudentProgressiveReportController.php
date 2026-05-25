<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\ProgressiveAssessment;
use App\Models\StudentProgressiveReport;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StudentProgressiveReportController extends AdminController
{
    protected $title = 'Test Reports';

    protected function grid()
    {
        $grid = new Grid(new StudentProgressiveReport());

        $u    = Admin::user();
        $ent  = $u->ent;
        $year = $ent->active_academic_year();

        $grid->perPages([100, 200, 500]);

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('academic_class_id')
            ->orderBy('position');

        $grid->disableBatchActions();
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();

            $record = $actions->row;
            $generateUrl = url('student-progressive-reports/' . $record->id . '/generate-pdf');
            $viewUrl     = $record->pdf_url
                ? url('storage/files/' . $record->pdf_url)
                : null;

            $btn = '<a href="' . $generateUrl . '" class="btn btn-xs btn-primary" title="Generate PDF" target="_blank">'
                . '<i class="fa fa-file-pdf-o"></i> PDF</a>';
            if ($viewUrl) {
                $btn .= '&nbsp;<a href="' . $viewUrl . '" class="btn btn-xs btn-success" title="View PDF" target="_blank">'
                    . '<i class="fa fa-eye"></i></a>';
            }
            $actions->append($btn);
        });

        $grid->filter(function ($filter) use ($u, $year) {
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
            $filter->equal('stream_id', 'Stream')->select($streams);

            // Term
            $filter->equal('term_id', 'Term')->select(
                Term::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('id', 'desc')->get()->pluck('name_text', 'id')
            );

            // Grade
            $filter->equal('grade', 'Grade')->select([
                '1' => 'Grade 1', '2' => 'Grade 2', '3' => 'Grade 3',
                '4' => 'Grade 4', 'U' => 'U (Ungraded)', 'X' => 'X (No marks)',
            ]);
        });

        // ── columns ──────────────────────────────────────────────────────────
        $grid->column('id', '#')->sortable()->hide();

        $grid->column('academic_class_id', 'Class')->display(function () {
            return $this->academic_class?->short_name ?? '—';
        })->sortable();

        $grid->column('stream_id', 'Stream')->display(function () {
            return $this->stream?->name ?? '—';
        })->sortable();

        $grid->column('student_id', 'Student')->display(function () {
            return $this->owner?->name ?? '—';
        })->sortable();

        $grid->column('progressive_assessment_id', 'Assessment')->display(function () {
            return $this->progressive_assessment?->title ?? '—';
        })->sortable();

        $grid->column('total_marks', 'Total Marks')->sortable();
        $grid->column('total_aggregates', 'Aggregates')->sortable();

        $grid->column('position', 'Position')->display(function ($pos) {
            if (!$pos) return '—';
            $suffix = match (($pos % 100 >= 11 && $pos % 100 <= 13) ? 0 : $pos % 10) {
                1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
            };
            return $pos . $suffix . ' / ' . ($this->total_students ?? '?');
        })->sortable();

        $grid->column('grade', 'Grade')
            ->label(['1' => 'success', '2' => 'success', '3' => 'warning', '4' => 'warning', 'U' => 'danger', 'X' => 'default'])
            ->sortable();

        $grid->column('is_ready', 'Visible')->using(['1' => 'Yes', '0' => 'No'])
            ->label(['1' => 'success', '0' => 'default'])
            ->sortable();

        $grid->column('date_generated', 'Generated')->display(function ($d) {
            return $d ? date('d M Y', strtotime($d)) : '—';
        })->sortable();

        $grid->column('pdf_url', 'Report')->display(function ($url) {
            if (!$url) return '<span class="label label-default">Not generated</span>';
            $fileUrl = url('storage/files/' . $url);
            return '<a href="' . $fileUrl . '" class="btn btn-xs btn-success" target="_blank">'
                . '<i class="fa fa-download"></i> Download</a>';
        });

        return $grid;
    }

    // ── Custom route: generate PDF for one student ───────────────────────────
    public function generatePdf($id)
    {
        $report = StudentProgressiveReport::findOrFail($id);
        $u      = Admin::user();

        if ($report->enterprise_id !== $u->enterprise_id) {
            abort(403);
        }

        try {
            $name = $report->download_self();
            admin_success('PDF Generated', 'Report card saved successfully.');
        } catch (\Throwable $e) {
            admin_error('Error', $e->getMessage());
            return redirect()->back();
        }

        $fileUrl = url('storage/files/' . $name);
        return redirect($fileUrl);
    }

    protected function detail($id)
    {
        $show = new Show(StudentProgressiveReport::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('student_text', 'Student');
        $show->field('academic_class_text', 'Class');
        $show->field('total_marks', 'Total Marks');
        $show->field('total_aggregates', 'Total Aggregates');
        $show->field('position', 'Position');
        $show->field('grade', 'Grade');
        $show->field('class_teacher_comment', 'Class Teacher Comment');
        $show->field('head_teacher_comment', 'Head Teacher Comment');
        $show->field('is_ready', 'Visible to Parents');
        $show->field('date_generated', 'Date Generated');
        return $show;
    }

    protected function form()
    {
        $form = new Form(new StudentProgressiveReport());
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        $form->textarea('class_teacher_comment', 'Class Teacher Comment');
        $form->textarea('head_teacher_comment', 'Head Teacher Comment');
        $form->radioCard('is_ready', 'Visible to Parents')
            ->options(['1' => 'Yes', '0' => 'No'])->default('0');

        return $form;
    }
}
