<?php

namespace App\Admin\Controllers;

use App\Models\SecondaryReportCard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryReportCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SecondaryReportCard';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SecondaryReportCard());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('secondary_termly_report_card_id', __('Secondary termly report card id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('class_teacher_comment', __('Class teacher comment'));
        $grid->column('head_teacher_comment', __('Head teacher comment'));

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
        $show = new Show(SecondaryReportCard::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('term_id', __('Term id'));
        $show->field('secondary_termly_report_card_id', __('Secondary termly report card id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('class_teacher_comment', __('Class teacher comment'));
        $show->field('head_teacher_comment', __('Head teacher comment'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryReportCard());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('term_id', __('Term id'));
        $form->number('secondary_termly_report_card_id', __('Secondary termly report card id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->textarea('class_teacher_comment', __('Class teacher comment'));
        $form->textarea('head_teacher_comment', __('Head teacher comment'));

        return $form;
    }
}
