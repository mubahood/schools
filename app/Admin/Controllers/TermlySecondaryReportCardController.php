<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\TermlySecondaryReportCard;
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
        $grid->model()
            ->orderBy('id', 'DESC')
            ->where('enterprise_id', $u->enterprise_id);

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('report_title', __('Report title'));
        $grid->column('has_u1', __('Has u1'));
        $grid->column('has_u2', __('Has u2'));
        $grid->column('has_u3', __('Has u3'));
        $grid->column('has_u4', __('Has u4'));
        $grid->column('has_u5', __('Has u5'));
        $grid->column('do_update', __('Do update'));
        $grid->column('generate_marks', __('Generate marks'));
        $grid->column('generate_marks_for_classes', __('Generate marks for classes'));
        $grid->column('delete_marks_for_non_active', __('Delete marks for non active'));
        $grid->column('submit_u1', __('Submit u1'));
        $grid->column('submit_u2', __('Submit u2'));
        $grid->column('submit_u3', __('Submit u3'));
        $grid->column('submit_u4', __('Submit u4'));
        $grid->column('submit_u5', __('Submit u5'));
        $grid->column('submit_project', __('Submit project'));
        $grid->column('submit_exam', __('Submit exam'));
        $grid->column('reports_generate', __('Reports generate'));
        $grid->column('reports_include_u1', __('Reports include u1'));
        $grid->column('reports_include_u2', __('Reports include u2'));
        $grid->column('reports_include_u3', __('Reports include u3'));
        $grid->column('reports_include_u4', __('Reports include u4'));
        $grid->column('reports_include_u5', __('Reports include u5'));
        $grid->column('reports_include_exam', __('Reports include exam'));
        $grid->column('reports_include_project', __('Reports include project'));
        $grid->column('reports_template', __('Reports template'));
        $grid->column('reports_who_fees_balance', __('Reports who fees balance'));
        $grid->column('reports_display_report_to_parents', __('Reports display report to parents'));
        $grid->column('hm_communication', __('Hm communication'));
        $grid->column('generate_class_teacher_comment', __('Generate class teacher comment'));
        $grid->column('generate_head_teacher_comment', __('Generate head teacher comment'));
        $grid->column('generate_positions', __('Generate positions'));
        $grid->column('display_positions', __('Display positions'));
        $grid->column('bottom_message', __('Bottom message'));

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
        $form->listbox('generate_marks_for_classes', 'Select classes to generate marks for')
            ->options($classes)
            ->required();


        $form->text('report_title', __('Report title'))->required();
        $form->radio('has_u1', __('Inlude UNIT 1 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u2', __('Inlude UNIT 2 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u3', __('Inlude UNIT 3 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('has_u4', __('Inlude UNIT 4 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('has_u5', __('Inlude UNIT 5 on report'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('generate_marks', __('Generate/Re-Generate Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('delete_marks_for_non_active', __('Delete marks for non active'))->default('No')->options(['Yes' => 'Yes', 'No' => 'No'])->required();
        $form->radio('submit_u1', __('Submit UNIT 1 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u2', __('Submit UNIT 2 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u3', __('Submit UNIT 3 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('Yes')->required();
        $form->radio('submit_u4', __('Submit UNIT 4 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_u5', __('Submit UNIT 5 marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_project', __('Submit Project Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('submit_exam', __('Submit Exam Marks'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->decimal('max_score_1', __('Max Score for UNIT 1'))->default(3.0)->required();
        $form->decimal('max_score_2', __('Max Score for UNIT 2'))->default(3.0)->required();
        $form->decimal('max_score_3', __('Max Score for UNIT 3'))->default(3.0)->required();
        $form->decimal('max_score_4', __('Max Score for UNIT 4'))->default(3.0)->required();
        $form->decimal('max_score_5', __('Max Score for UNIT 5'))->default(3.0)->required();
        $form->decimal('max_project_score', __('Max Score for Project'))->default(10.0)->required();
        $form->decimal('max_exam_score', __('Max Score for Exam'))->default(80.0)->required();

        $form->radio('reports_generate', __('Generate/Re-Generate Reports'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u1', __('Inlude UNIT 1 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u2', __('Inlude UNIT 2 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u3', __('Inlude UNIT 3 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u4', __('Inlude UNIT 4 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_u5', __('Inlude UNIT 5 in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_exam', __('Inlude Exam in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
        $form->radio('reports_include_project', __('Inlude Project in report cards'))->options(['Yes' => 'Yes', 'No' => 'No'])->default('No')->required();
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

        return $form;
    }
}
