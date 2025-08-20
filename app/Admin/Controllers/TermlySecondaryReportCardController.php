<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\TermlySecondaryReportCard;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TermlySecondaryReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Termly Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* $m = TermlySecondaryReportCard::find(1);
        TermlySecondaryReportCard::do_generate_reports($m);
        die("done");
        TermlySecondaryReportCard::do_process($m); */

        $grid = new Grid(new TermlySecondaryReportCard());
        $grid->disableCreateButton();
        $u = Admin::user();
        $grid->disableBatchActions();
        $grid->model()
            ->orderBy('id', 'DESC')
            ->where('enterprise_id', $u->enterprise_id);


        $grid->column('academic_year_id', __('Academic Year'))
            ->display(function ($academic_year_id) {
                if ($this->academic_year == null) return "N/A";
                return $this->academic_year->name;
            })->sortable()
            ->hide();
        $grid->column('term_id', __('Term'))
            ->display(function ($term_id) {
                if ($this->term == null) return "N/A";
                return 'Term ' . $this->term->name_text;
            })->sortable();
        $grid->column('report_title', __('Report Title'))
            ->sortable();
        $grid->column('has_u1', __('Has u1'))->hide();
        $grid->column('has_u2', __('Has u2'))->hide();
        $grid->column('has_u3', __('Has u3'))->hide();
        $grid->column('has_u4', __('Has u4'))->hide();
        $grid->column('has_u5', __('Has u5'))->hide();
        $grid->column('generate_marks_for_classes', __('Generate marks for classes'))->hide();
        $grid->column('submit_u1', __('Submit U1'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger'
            ])->sortable();
        $grid->column('submit_u2', __('Submit U2'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger'
            ])->sortable();
        $grid->column('submit_u3', __('Submit U3'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger'
            ])->sortable();
        $grid->column('submit_u4', __('Submit U4'))->hide();
        $grid->column('submit_u5', __('Submit U5'))->hide();
        $grid->column('submit_project', __('Submit Project'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger'
            ])->sortable();
        $grid->column('submit_exam', __('Submit Exam'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger'
            ])->sortable();
        // $grid->column('reports_generate', __('Reports generate'));
        // $grid->column('reports_include_u1', __('Reports include u1'));
        // $grid->column('reports_include_u2', __('Reports include u2'));
        // $grid->column('reports_include_u3', __('Reports include u3'));
        // $grid->column('reports_include_u4', __('Reports include u4'));
        // $grid->column('reports_include_u5', __('Reports include u5'));
        // $grid->column('reports_include_exam', __('Reports include exam'));
        // $grid->column('reports_include_project', __('Reports include project'));
        // $grid->column('reports_template', __('Reports template'));
        // $grid->column('reports_who_fees_balance', __('Reports who fees balance'));
        // $grid->column('reports_display_report_to_parents', __('Reports display report to parents'));
        // $grid->column('hm_communication', __('Hm communication'));
        // $grid->column('generate_class_teacher_comment', __('Generate class teacher comment'));
        // $grid->column('generate_head_teacher_comment', __('Generate head teacher comment'));
        // $grid->column('generate_positions', __('Generate positions'));
        // $grid->column('display_positions', __('Display positions'));
        // $grid->column('bottom_message', __('Bottom message'));
        $grid->column('marks_count', __('Total Marks'))
            ->display(function () {
                return number_format($this->marks_count());
            });


        $grid->column('submitted_marks_u1_count', __('Submitted U1 Marks'))
            ->display(function () {
                $tot = $this->marks_count();
                $tot_u1 = $this->submitted_marks_u1_count();
                $percentage_submitted = 0;
                if ($tot > 0) {
                    $percentage_submitted = ($tot_u1 / $tot) * 100;
                }
                return number_format($tot_u1) . " (" . number_format($percentage_submitted, 2) . "%)";
            });
        //for u2
        $grid->column('submitted_marks_u2_count', __('Submitted U2 Marks'))
            ->display(function () {
                $tot = $this->marks_count();
                $tot_u2 = $this->submitted_marks_u2_count();
                $percentage_submitted = 0;
                if ($tot > 0) {
                    $percentage_submitted = ($tot_u2 / $tot) * 100;
                }
                return number_format($tot_u2) . " (" . number_format($percentage_submitted, 2) . "%)";
            });

        //for u3
        $grid->column('submitted_marks_u3_count', __('Submitted U3 Marks'))
            ->display(function () {
                $tot = $this->marks_count();
                $tot_u3 = $this->submitted_marks_u3_count();
                $percentage_submitted = 0;
                if ($tot > 0) {
                    $percentage_submitted = ($tot_u3 / $tot) * 100;
                }
                return number_format($tot_u3) . " (" . number_format($percentage_submitted, 2) . "%)";
            });

        //for project
        $grid->column('submitted_marks_project_count', __('Submitted Project Marks'))
            ->display(function () {
                $tot = $this->marks_count();
                $tot_project = $this->submitted_project_count();
                $percentage_submitted = 0;
                if ($tot > 0) {
                    $percentage_submitted = ($tot_project / $tot) * 100;
                }
                return number_format($tot_project) . " (" . number_format($percentage_submitted, 2) . "%)";
            });

        //for exam
        $grid->column('submitted_marks_exam_count', __('Submitted Exam Marks'))
            ->display(function () {
                $tot = $this->marks_count();
                $tot_exam = $this->submitted_exam_count();
                $percentage_submitted = 0;
                if ($tot > 0) {
                    $percentage_submitted = ($tot_exam / $tot) * 100;
                }
                return number_format($tot_exam) . " (" . number_format($percentage_submitted, 2) . "%)";
            });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TermlySecondaryReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('report_title', __('Report title'));
        $show->field('has_u1', __('Has u1'));
        $show->field('has_u2', __('Has u2'));
        $show->field('has_u3', __('Has u3'));
        $show->field('has_u4', __('Has u4'));
        $show->field('has_u5', __('Has u5'));
        $show->field('do_update', __('Do update'));
        $show->field('generate_marks', __('Generate marks'));
        $show->field('generate_marks_for_classes', __('Generate marks for classes'));
        $show->field('delete_marks_for_non_active', __('Delete marks for non active'));
        $show->field('submit_u1', __('Submit u1'));
        $show->field('submit_u2', __('Submit u2'));
        $show->field('submit_u3', __('Submit u3'));
        $show->field('submit_u4', __('Submit u4'));
        $show->field('submit_u5', __('Submit u5'));
        $show->field('submit_project', __('Submit project'));
        $show->field('submit_exam', __('Submit exam'));
        $show->field('reports_generate', __('Reports generate'));
        $show->field('reports_include_u1', __('Reports include u1'));
        $show->field('reports_include_u2', __('Reports include u2'));
        $show->field('reports_include_u3', __('Reports include u3'));
        $show->field('reports_include_u4', __('Reports include u4'));
        $show->field('reports_include_u5', __('Reports include u5'));
        $show->field('reports_include_exam', __('Reports include exam'));
        $show->field('reports_include_project', __('Reports include project'));
        $show->field('reports_template', __('Reports template'));
        $show->field('reports_who_fees_balance', __('Reports who fees balance'));
        $show->field('reports_display_report_to_parents', __('Reports display report to parents'));
        $show->field('hm_communication', __('Hm communication'));
        $show->field('generate_class_teacher_comment', __('Generate class teacher comment'));
        $show->field('generate_head_teacher_comment', __('Generate head teacher comment'));
        $show->field('generate_positions', __('Generate positions'));
        $show->field('display_positions', __('Display positions'));
        $show->field('bottom_message', __('Bottom message'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TermlySecondaryReportCard());
        $u = Admin::user();
        if ($form->isCreating()) {
            return admin_error('This item cannot be created from this page. It is automatically created when a new term is created.');
        }

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->id);

        $year = $u->ent->active_academic_year();
        $academicClasses = AcademicClass::where([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $year->id,
        ])
            ->orderBy('id', 'DESC')
            ->get();
        $classes = [];
        foreach ($academicClasses as  $v) {
            $classes[$v->id] = $v->name_text;
        }
        $form->divider(strtoupper('Report Card Settings'));
        $form->text('report_title', __('Report title'))->required();
        $form->listbox('generate_marks_for_classes', 'Select classes to generate marks for')
            ->options($classes)
            ->required();


        $form->radio('has_u1', __('Include UNIT 1 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u2', __('Include UNIT 2 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u3', __('Include UNIT 3 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u4', __('Include UNIT 4 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('has_u5', __('Include UNIT 5 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();

        $form->divider(strtoupper('Marks Settings'));

        $form->decimal('max_score_1', __('Max Score for UNIT 1'))->default(3.0)->required();
        $form->decimal('max_score_2', __('Max Score for UNIT 2'))->default(3.0)->required();
        $form->decimal('max_score_3', __('Max Score for UNIT 3'))->default(3.0)->required();
        $form->decimal('max_score_4', __('Max Score for UNIT 4'))->default(3.0)->required();
        $form->decimal('max_score_5', __('Max Score for UNIT 5'))->default(3.0)->required();
        $form->decimal('max_project_score', __('Max Score for Project'))->default(10.0)->required();
        $form->decimal('max_exam_score', __('Max Score for Exam'))->default(80.0)->required();

        $form->radio('generate_marks', __('Generate/Re-Generate Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('delete_marks_for_non_active', __('Delete marks for non active'))->default('No')->options(['Yes' => 'Yes', 'No' => 'No'])->required();

        $form->divider(strtoupper('Submission Settings'));


        $form->radio('submit_u1', __('Submit UNIT 1 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u2', __('Submit UNIT 2 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u3', __('Submit UNIT 3 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u4', __('Submit UNIT 4 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_u5', __('Submit UNIT 5 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_project', __('Submit Project Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_exam', __('Submit Exam Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();


        $form->divider(strtoupper('Printed Report Card Settings'));

        $form->radio('reports_generate', __('Generate/Re-Generate Reports'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u1', __('Include UNIT 1 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u2', __('Include UNIT 2 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u3', __('Include UNIT 3 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u4', __('Include UNIT 4 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u5', __('Include UNIT 5 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_exam', __('Include Exam in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_project', __('Include Project in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radioCard('reports_template', __('Seelct Report Card Template'))
            ->options([
                '1' => 'Template 1',
                '2' => 'Template 2',
                '3' => 'Template 3',
            ])->default('1');
        $form->radio('reports_who_fees_balance', __('Display Fees Balance on Report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_display_report_to_parents', __('Display report cards to parents'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('generate_class_teacher_comment', __('Generate class teacher comment'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('generate_head_teacher_comment', __('Generate head teacher comment'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->quill('hm_communication', __('Hm Communication'));
        $form->radio('generate_positions', __('Generate Positions'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('display_positions', __('Display positions on report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->quill('bottom_message', __('Bottom message'));

        $form->disableReset();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
