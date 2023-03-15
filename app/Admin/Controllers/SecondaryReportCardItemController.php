<?php

namespace App\Admin\Controllers;

use App\Models\SecondaryReportCardItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryReportCardItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SecondaryReportCardItem';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SecondaryReportCardItem());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('secondary_subject_id', __('Secondary subject id'));
        $grid->column('secondary_report_card_id', __('Secondary report card id'));
        $grid->column('average_score', __('Average score'));
        $grid->column('generic_skills', __('Generic skills'));
        $grid->column('remarks', __('Remarks'));
        $grid->column('teacher', __('Teacher'));

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
        $show = new Show(SecondaryReportCardItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('secondary_subject_id', __('Secondary subject id'));
        $show->field('secondary_report_card_id', __('Secondary report card id'));
        $show->field('average_score', __('Average score'));
        $show->field('generic_skills', __('Generic skills'));
        $show->field('remarks', __('Remarks'));
        $show->field('teacher', __('Teacher'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryReportCardItem());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('secondary_subject_id', __('Secondary subject id'));
        $form->number('secondary_report_card_id', __('Secondary report card id'));
        $form->decimal('average_score', __('Average score'))->default(0.00);
        $form->textarea('generic_skills', __('Generic skills'));
        $form->textarea('remarks', __('Remarks'));
        $form->text('teacher', __('Teacher'));

        return $form;
    }
}
