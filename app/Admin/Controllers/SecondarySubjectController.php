<?php

namespace App\Admin\Controllers;

use App\Models\SecondarySubject;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class SecondarySubjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SecondarySubject';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SecondarySubject());
        $grid->model()->where([
            'enterprise_id' => Auth::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('parent_course_id', __('Parent course id'));
        $grid->column('academic_year_id', __('Academic year id'));
        $grid->column('teacher_1', __('Teacher 1'));
        $grid->column('teacher_2', __('Teacher 2'));
        $grid->column('teacher_3', __('Teacher 3'));
        $grid->column('teacher_4', __('Teacher 4'));
        $grid->column('subject_name', __('Subject name'));
        $grid->column('details', __('Details'));
        $grid->column('code', __('Code'));
        $grid->column('is_optional', __('Is optional'));

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
        $show = new Show(SecondarySubject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('parent_course_id', __('Parent course id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('teacher_1', __('Teacher 1'));
        $show->field('teacher_2', __('Teacher 2'));
        $show->field('teacher_3', __('Teacher 3'));
        $show->field('teacher_4', __('Teacher 4'));
        $show->field('subject_name', __('Subject name'));
        $show->field('details', __('Details'));
        $show->field('code', __('Code'));
        $show->field('is_optional', __('Is optional'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondarySubject());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('parent_course_id', __('Parent course id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->number('teacher_1', __('Teacher 1'));
        $form->number('teacher_2', __('Teacher 2'));
        $form->number('teacher_3', __('Teacher 3'));
        $form->number('teacher_4', __('Teacher 4'));
        $form->textarea('subject_name', __('Subject name'));
        $form->textarea('details', __('Details'));
        $form->textarea('code', __('Code'));
        $form->switch('is_optional', __('Is optional'));

        return $form;
    }
}
