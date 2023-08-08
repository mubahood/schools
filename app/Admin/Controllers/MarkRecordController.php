<?php

namespace App\Admin\Controllers;

use App\Models\MarkRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MarkRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MarkRecord';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MarkRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('termly_report_card_id', __('Termly report card id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('academic_class_sctream_id', __('Academic class sctream id'));
        $grid->column('main_course_id', __('Main course id'));
        $grid->column('subject_id', __('Subject id'));
        $grid->column('bot_score', __('Bot score'));
        $grid->column('mot_score', __('Mot score'));
        $grid->column('eot_score', __('Eot score'));
        $grid->column('bot_is_submitted', __('Bot is submitted'));
        $grid->column('mot_is_submitted', __('Mot is submitted'));
        $grid->column('eot_is_submitted', __('Eot is submitted'));
        $grid->column('bot_missed', __('Bot missed'));
        $grid->column('mot_missed', __('Mot missed'));
        $grid->column('eot_missed', __('Eot missed'));
        $grid->column('initials', __('Initials'));
        $grid->column('remarks', __('Remarks'));

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
        $show = new Show(MarkRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('termly_report_card_id', __('Termly report card id'));
        $show->field('term_id', __('Term id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('academic_class_sctream_id', __('Academic class sctream id'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('bot_score', __('Bot score'));
        $show->field('mot_score', __('Mot score'));
        $show->field('eot_score', __('Eot score'));
        $show->field('bot_is_submitted', __('Bot is submitted'));
        $show->field('mot_is_submitted', __('Mot is submitted'));
        $show->field('eot_is_submitted', __('Eot is submitted'));
        $show->field('bot_missed', __('Bot missed'));
        $show->field('mot_missed', __('Mot missed'));
        $show->field('eot_missed', __('Eot missed'));
        $show->field('initials', __('Initials'));
        $show->field('remarks', __('Remarks'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MarkRecord());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('termly_report_card_id', __('Termly report card id'));
        $form->number('term_id', __('Term id'));
        $form->number('administrator_id', __('Administrator id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('academic_class_sctream_id', __('Academic class sctream id'));
        $form->number('main_course_id', __('Main course id'));
        $form->number('subject_id', __('Subject id'));
        $form->number('bot_score', __('Bot score'));
        $form->number('mot_score', __('Mot score'));
        $form->number('eot_score', __('Eot score'));
        $form->text('bot_is_submitted', __('Bot is submitted'))->default('No');
        $form->text('mot_is_submitted', __('Mot is submitted'))->default('No');
        $form->text('eot_is_submitted', __('Eot is submitted'))->default('No');
        $form->text('bot_missed', __('Bot missed'))->default('Yes');
        $form->text('mot_missed', __('Mot missed'))->default('Yes');
        $form->text('eot_missed', __('Eot missed'))->default('Yes');
        $form->text('initials', __('Initials'));
        $form->textarea('remarks', __('Remarks'));

        return $form;
    }
}
