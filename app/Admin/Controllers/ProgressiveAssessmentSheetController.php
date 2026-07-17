<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\ProgressiveAssessment;
use App\Models\ProgressiveAssessmentSheet;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProgressiveAssessmentSheetController extends AdminController
{
    protected $title = 'PA Assessment Sheets';

    // ── GRID ─────────────────────────────────────────────────────────────────
    protected function grid()
    {
        $grid = new Grid(new ProgressiveAssessmentSheet());
        $u    = Admin::user();

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->column('id', '#')->sortable();

        $grid->column('title', 'Sheet Title')->display(function () {
            return $this->get_title();
        });

        $grid->column('progressive_assessment_id', 'Assessment')->display(function () {
            $pa = $this->progressive_assessment;
            return $pa ? ($pa->title . ' — ' . ($pa->term?->name_text ?? '')) : '—';
        });

        $grid->column('academic_class_id', 'Class/Stream')->display(function () {
            if ($this->type === 'Stream') {
                return $this->stream?->name_text ?? '—';
            }
            return $this->academic_class?->name_text ?? '—';
        });

        $grid->column('type', 'Scope')->using(['Class' => 'Class', 'Stream' => 'Stream'])
            ->label(['Class' => 'primary', 'Stream' => 'success']);

        $grid->column('total_students', 'Students')->sortable();

        $grid->column('grade_dist', 'Grade Distribution')->display(function () {
            $colors = ['1' => '#27ae60', '2' => '#2ecc71', '3' => '#f39c12', '4' => '#e67e22', 'U' => '#e74c3c', 'X' => '#95a5a6'];
            $grades = ['1' => $this->grade_1, '2' => $this->grade_2, '3' => $this->grade_3, '4' => $this->grade_4, 'U' => $this->grade_u, 'X' => $this->grade_x];
            $html   = '';
            foreach ($grades as $g => $cnt) {
                if ($cnt > 0) {
                    $html .= '<span style="background:' . $colors[$g] . ';color:#fff;padding:1px 6px;border-radius:3px;font-size:11px;margin:1px;display:inline-block">G' . $g . ':' . $cnt . '</span>';
                }
            }
            return $html ?: '—';
        });

        $grid->column('generated', 'PDF')->using(['Yes' => 'Ready', 'No' => 'Not yet'])
            ->label(['Yes' => 'success', 'No' => 'default']);

        $grid->column('actions_custom', 'Actions')->display(function () {
            $genUrl  = url('progressive-assessment-sheet-generate?id=' . $this->id);
            $pdfUrl  = $this->generated === 'Yes' && $this->pdf_link
                       ? url('storage/' . $this->pdf_link)
                       : null;

            $html  = '<a href="' . $genUrl . '" target="_blank" class="btn btn-xs btn-primary">';
            $html .= '<i class="fa fa-refresh"></i> Generate PDF</a> ';

            if ($pdfUrl) {
                $html .= '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-xs btn-success">';
                $html .= '<i class="fa fa-file-pdf-o"></i> Download</a>';
            }

            return $html;
        });

        return $grid;
    }

    // ── DETAIL ───────────────────────────────────────────────────────────────
    protected function detail($id)
    {
        $show = new Show(ProgressiveAssessmentSheet::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('title', 'Title');
        $show->field('type', 'Scope');
        $show->field('total_students', 'Total Students');
        $show->field('generated', 'PDF Generated');
        $show->field('created_at', 'Created');
        return $show;
    }

    // ── FORM ─────────────────────────────────────────────────────────────────
    protected function form()
    {
        $form = new Form(new ProgressiveAssessmentSheet());
        $u    = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        // Progressive Assessment selector
        $paOptions = [];
        foreach (ProgressiveAssessment::where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc')->get() as $pa) {
            $termName      = $pa->term?->name_text ?? '';
            $paOptions[$pa->id] = $pa->title . ($termName ? ' (' . $termName . ')' : '');
        }

        $form->select('progressive_assessment_id', 'Progressive Assessment')
            ->options($paOptions)
            ->rules('required')
            ->help('Select which progressive assessment this sheet covers.');

        $form->radio('type', 'Scope')
            ->options(['Class' => 'Whole Class', 'Stream' => 'Specific Stream'])
            ->default('Class')
            ->when('Class', function (Form $f) use ($u) {
                $year    = $u->ent->active_academic_year();
                $classes = [];
                foreach (AcademicClass::where([
                    'enterprise_id'    => $u->enterprise_id,
                    'academic_year_id' => $year->id,
                ])->orderBy('name')->get() as $c) {
                    $classes[$c->id] = $c->name_text;
                }
                $f->select('academic_class_id', 'Class')->options($classes)->rules('required');
            })
            ->when('Stream', function (Form $f) use ($u) {
                $year    = $u->ent->active_academic_year();
                $streams = [];
                $classes = AcademicClass::where([
                    'enterprise_id'    => $u->enterprise_id,
                    'academic_year_id' => $year->id,
                ])->with('streams')->get();
                foreach ($classes as $c) {
                    foreach ($c->streams ?? [] as $s) {
                        $streams[$s->id] = $s->name_text;
                    }
                }
                $f->select('academic_class_sctream_id', 'Stream')->options($streams)->rules('required');
            });

        if ($form->isEditing()) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
            });
            $form->divider('Re-run Computation');
            $form->html(
                '<div class="alert alert-info">Save this form to re-compute all statistics. Then use the <strong>Generate PDF</strong> button on the grid.</div>'
            );
        }

        return $form;
    }
}
