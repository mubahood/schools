<?php

namespace App\Admin\Controllers;

use App\Models\SchoolReport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SchoolReport';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolReport());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('pdf_path', __('Pdf path'));
        $grid->column('total_students', __('Total students'));
        $grid->column('expected_fees', __('Expected fees'));
        $grid->column('fees_collected_manual_entry', __('Fees collected manual entry'));
        $grid->column('fees_collected_schoolpay', __('Fees collected schoolpay'));
        $grid->column('fees_collected_total', __('Fees collected total'));
        $grid->column('fees_collected_other', __('Fees collected other'));

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
        $show = new Show(SchoolReport::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('pdf_path', __('Pdf path'));
        $show->field('total_students', __('Total students'));
        $show->field('expected_fees', __('Expected fees'));
        $show->field('fees_collected_manual_entry', __('Fees collected manual entry'));
        $show->field('fees_collected_schoolpay', __('Fees collected schoolpay'));
        $show->field('fees_collected_total', __('Fees collected total'));
        $show->field('fees_collected_other', __('Fees collected other'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchoolReport());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('term_id', __('Term id'));
        $form->textarea('pdf_path', __('Pdf path'));
        $form->number('total_students', __('Total students'));
        $form->number('expected_fees', __('Expected fees'));
        $form->number('fees_collected_manual_entry', __('Fees collected manual entry'));
        $form->number('fees_collected_schoolpay', __('Fees collected schoolpay'));
        $form->number('fees_collected_total', __('Fees collected total'));
        $form->number('fees_collected_other', __('Fees collected other'));

        return $form;
    }
}
