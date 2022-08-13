<?php

namespace App\Admin\Controllers;

use App\Models\Demo;
use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DemoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Demo';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        /* $d = Demo::find(5);
        $d->temp .= rand(100, 1000);
        $d->generate_marks = 1;
        $d->save();
        die("Romina"); */
        $grid = new Grid(new Demo());

        $grid->column('id', __('Id'));
        $grid->column('enterprise.name', __('Enterprise id'));
        $grid->column('teachers', __('Teachers'))->display(
            function () {
                return $this->teachers->count();
            }
        );
        $grid->column('courses', __('Courses'))->display(
            function () {
                return $this->courses->count();
            }
        );

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
        $show = new Show(Demo::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('generate_teachers', __('Generate teachers'));
        $show->field('number_of_teachers', __('Number of teachers'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Demo());

        $form->select('enterprise_id', __('Enterprise'))
            ->options(
                Enterprise::all()->pluck('name', 'id')
            )
            ->rules('required');

        $form->radio('generate_teachers', __('Generate teachers'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required')
            ->when('1', function (Form $form) {
                $form->text('number_of_teachers', __('Number of teachers'))
                    ->rules('required|int')
                    ->attribute('type', 'number')
                    ->help("How many teachers do you want to be generated?");
            });



        $form->radio('create_courses', __('Create courses'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required')
            ->when('1', function (Form $form) {

                $form->radio('courses_type', __('Create courses'))->options([
                    'primary' => "Uganda Primary school courses",
                    'o_level' => "Uganda O'level courses",
                    'a_level' => "Uganda A'level courses",
                ])
                    ->rules('required');
            });


        $form->radio('create_term', __('Create terms'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required');



        $form->radio('create_classes', __('Create classess'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required')
            ->when('1', function (Form $form) {

                $form->radio('classes_type', __('Select classes'))->options([
                    'primary' => "Uganda Primary school classes",
                    'o_level' => "Uganda O'level classes",
                    'a_level' => "Uganda A'level classes",
                ])
                    ->rules('required');
            });



        $form->radio('create_subjects', __('Create subjects'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required');


        $form->radio('create_grade_scale', __('Create grading system'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required')
            ->when('1', function (Form $form) {
                $form->radio('grade_scale_type', __('Select grading scale'))->options([
                    'primary' => "Uganda Primary school grading",
                    'o_level' => "Uganda O'level grading",
                    'a_level' => "Uganda A'level grading",
                ])
                    ->rules('required');
            });

        $form->radio('generate_students', __('Generate students'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required')
            ->when('1', function (Form $form) {
                $form->text('number_of_students', __('Number of students'))
                    ->rules('required|int')
                    ->attribute('type', 'number')
                    ->help("How many students do you want to be generated?");
            });

        $form->radio('generate_marks', __('Generate marks'))->options([
            1 => "Yes",
            0 => "No",
        ])->default(0)
            ->rules('required');

        return $form;
    }
}
