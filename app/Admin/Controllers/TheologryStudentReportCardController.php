<?php

namespace App\Admin\Controllers;

use App\Models\TheologryStudentReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TheologryStudentReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Report Cards';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TheologryStudentReportCard());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('student_id', __('Student id'));
        $grid->column('theology_class_id', __('Theology class id'));
        $grid->column('theology_termly_report_card_id', __('Theology termly report card id'));
        $grid->column('total_students', __('Total students'));
        $grid->column('total_aggregates', __('Total aggregates'));
        $grid->column('total_marks', __('Total marks'));
        $grid->column('position', __('Position'));
        $grid->column('class_teacher_comment', __('Class teacher comment'));
        $grid->column('head_teacher_comment', __('Head teacher comment'));
        $grid->column('class_teacher_commented', __('Class teacher commented'));
        $grid->column('head_teacher_commented', __('Head teacher commented'));

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
        $show = new Show(TheologryStudentReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('student_id', __('Student id'));
        $show->field('theology_class_id', __('Theology class id'));
        $show->field('theology_termly_report_card_id', __('Theology termly report card id'));
        $show->field('total_students', __('Total students'));
        $show->field('total_aggregates', __('Total aggregates'));
        $show->field('total_marks', __('Total marks'));
        $show->field('position', __('Position'));
        $show->field('class_teacher_comment', __('Class teacher comment'));
        $show->field('head_teacher_comment', __('Head teacher comment'));
        $show->field('class_teacher_commented', __('Class teacher commented'));
        $show->field('head_teacher_commented', __('Head teacher commented'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TheologryStudentReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('student_id', __('Student id'));
        $form->number('theology_class_id', __('Theology class id'));
        $form->number('theology_termly_report_card_id', __('Theology termly report card id'));
        $form->number('total_students', __('Total students'));
        $form->number('total_aggregates', __('Total aggregates'));
        $form->decimal('total_marks', __('Total marks'))->default(0.00);
        $form->number('position', __('Position'));
        $form->textarea('class_teacher_comment', __('Class teacher comment'));
        $form->textarea('head_teacher_comment', __('Head teacher comment'));
        $form->switch('class_teacher_commented', __('Class teacher commented'));
        $form->switch('head_teacher_commented', __('Head teacher commented'));

        return $form;
    }
}
