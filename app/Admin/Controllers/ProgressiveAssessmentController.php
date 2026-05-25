<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\GradingScale;
use App\Models\ProgressiveAssessment;
use App\Models\StudentProgressiveReport;
use App\Models\StudentTestRecord;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProgressiveAssessmentController extends AdminController
{
    protected $title = 'Progressive Assessment';

    // ── GRID ─────────────────────────────────────────────────────────────────
    protected function grid()
    {
        $grid = new Grid(new ProgressiveAssessment());

        $u = Admin::user();
        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->column('id', '#')->sortable();
        $grid->column('title', 'Title')->sortable();
        $grid->column('term.name', 'Term')->display(function () {
            return $this->term ? ($this->term->name_text ?? $this->term->name) : '—';
        });
        $grid->column('number_of_tests', 'Tests')->sortable();
        $grid->column('can_submit_tests', 'Open for Entry')->using(['Yes' => 'Yes', 'No' => 'No'])
            ->label(['Yes' => 'success', 'No' => 'danger']);

        $grid->column('records_count', 'Records')->display(function () {
            return number_format(StudentTestRecord::where('progressive_assessment_id', $this->id)->count());
        });
        $grid->column('reports_count', 'Reports')->display(function () {
            return number_format(StudentProgressiveReport::where('progressive_assessment_id', $this->id)->count());
        });

        $grid->column('classes', 'Classes')->display(function ($val) {
            if (!is_array($val) || empty($val)) return '—';
            return AcademicClass::whereIn('id', $val)->pluck('short_name')->implode(', ');
        });

        return $grid;
    }

    // ── DETAIL ───────────────────────────────────────────────────────────────
    protected function detail($id)
    {
        $show = new Show(ProgressiveAssessment::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('title', 'Title');
        $show->field('term_id', 'Term ID');
        $show->field('number_of_tests', 'Number of Tests');
        $show->field('can_submit_tests', 'Open for Entry');
        $show->field('created_at', 'Created');
        return $show;
    }

    // ── FORM ─────────────────────────────────────────────────────────────────
    protected function form()
    {
        $form = new Form(new ProgressiveAssessment());
        $u    = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableCreatingCheck();

        // Terms
        $terms = [];
        foreach (Term::where('enterprise_id', $u->enterprise_id)->orderBy('id', 'desc')->get() as $t) {
            $terms[$t->id] = ($t->academic_year->name ?? '') . ' – ' . $t->name;
        }

        // Grading scales
        $scales = GradingScale::pluck('name', 'id');

        // Classes
        $year = $u->ent->active_academic_year();
        $classes = [];
        foreach (AcademicClass::where([
            'enterprise_id'    => $u->enterprise_id,
            'academic_year_id' => $year->id,
        ])->orderBy('name')->get() as $c) {
            $classes[$c->id] = $c->name_text;
        }

        $form->divider('Basic Information');

        if ($form->isCreating()) {
            $form->select('term_id', 'Term')->options($terms)->rules('required');
        } else {
            $form->select('term_id', 'Term')->options($terms)->readOnly();
        }

        $form->text('title', 'Assessment Title')->rules('required')
            ->placeholder('e.g. Progressive Assessment – Term 1 2026');
        $form->select('grading_scale_id', 'Grading Scale')->options($scales)->rules('required');
        $form->number('number_of_tests', 'Number of Tests (1–10)')->default(10)
            ->min(1)->max(10)->rules('required|integer|min:1|max:10');
        $form->multipleSelect('classes', 'Classes to Track')->options($classes);

        $form->divider('Submission Settings');
        $form->radioCard('can_submit_tests', 'Allow teachers to enter test marks?')
            ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

        if ($form->isEditing()) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
            });

            $form->divider('Generate Records');
            $form->radioCard('generate_records', 'Generate / Re-generate test records for all students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('delete_records_for_non_active', 'Delete records for inactive students?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

            $form->divider('Generate Reports');
            $form->radioCard('reports_generate', 'Compute / Re-compute reports from current test marks?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('generate_positions', 'Generate / Re-generate student positions?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radio('positioning_type', 'Positioning by')
                ->options(['Class' => 'By Class', 'Stream' => 'By Stream'])->default('Class');
            $form->radioCard('generate_comments', 'Auto-generate class teacher comments?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');

            $form->divider('Report Card Settings');
            $form->radioCard('display_to_parents', 'Make reports visible to parents?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('No');
            $form->radioCard('display_positions', 'Show positions on report cards?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes');
            $form->radioCard('display_class_teacher_comments', 'Show class teacher comments?')
                ->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes');
            $form->textarea('hm_communication', 'Head Teacher Communication');
            $form->textarea('bottom_message', 'Bottom Message / Footer');
        }

        return $form;
    }
}
