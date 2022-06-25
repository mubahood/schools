<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\StudentHasClass;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StudentHasClassController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Classes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentHasClass());
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ]);

        $grid->column('id', __('Id'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('academic_class_id', __('Academic class id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('stream_id', __('Stream id'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('created_at', __('Created at'));
        $grid->column('academic_year_id', __('Academic year id'));

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
        $show = new Show(StudentHasClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('stream_id', __('Stream id'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('academic_year_id', __('Academic year id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentHasClass());
        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        $form->select('administrator_id', 'Student')->options(function () {
            return Administrator::where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'student',
            ])->get()->pluck('name', 'id');
        })
            ->rules('required');

        $form->select('academic_class_id', 'Class')->options(function () {
            return AcademicClass::where([
                'enterprise_id' => Admin::user()->enterprise_id,
            ])->get()->pluck('name', 'id');
        })
            ->rules('required');


        $form->number('stream_id', __('Stream id'));
        $form->number('academic_year_id', __('Academic year id'));

        return $form;
    }
}
