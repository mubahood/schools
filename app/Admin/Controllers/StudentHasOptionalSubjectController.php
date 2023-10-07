<?php

namespace App\Admin\Controllers;

use App\Models\StudentHasOptionalSubject;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StudentHasOptionalSubjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students Optional Subjects Selection';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentHasOptionalSubject());

        $grid->column('id', __('Id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('course_id', __('Course id'));
        $grid->column('main_course_id', __('Main course'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('student_has_class_id', __('Student has class id'));

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
        $show = new Show(StudentHasOptionalSubject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('course_id', __('Course id'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('student_has_class_id', __('Student has class id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentHasOptionalSubject());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);


        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
        );

        $form->select('administrator_id', __('Student'))
            ->options(function ($id) {
                $user = \App\Models\User::find($id);
                if ($user) {
                    return [$user->id => $user->name];
                }
            })
            ->ajax($ajax_url)
            ->load('student_has_class_id', '/api/ajax/select-student-has-class')
            ->rules('required');

        $form->select('student_has_class_id', __('Student has class id'))
            ->options(function ($id) {
                $user = \App\Models\StudentHasClass::find($id);
                if ($user) {
                    return [$user->id => $user->name];
                }
            })
            ->rules('required');
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('course_id', __('Course id'));
        $form->number('main_course_id', __('Main course id'));


        return $form;
    }
}
