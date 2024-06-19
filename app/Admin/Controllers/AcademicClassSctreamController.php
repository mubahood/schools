<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AcademicClassSctreamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Class Streams';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        //dd((new AcademicClassSctream())->getTable());
        $grid = new Grid(new AcademicClassSctream());
        $grid->disableBatchActions();
        //$grid->disableBatchActions();
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])
            ->orderBY('id', 'desc');

        $u = Admin::user();
        $classes =  AcademicClass::getAcademicClasses([
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $u->ent->dp_year,
        ]);

        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class->name == null) {
                    return $x;
                }
                return $this->academic_class->short_name . " - " . $this->academic_class->name_text;
            })
            ->sortable()
            ->filter($classes);

        // $grid->disableActions();
        $grid->actions(function ($x) {
            $x->disableDelete();
            $x->disableView();
        });
        $grid->column('name', __('Stream'))->sortable();
        $grid->column('students_count', __('Students'))
            ->display(function ($x) {
                return $this->studentHasClasses->count();
            });

        //teacher_id
        $grid->column('teacher_id', __('Teacher'))
            ->display(function ($x) {
                if ($this->teacher == null) {
                    return 'N/A';
                }
                return $this->teacher->name;
            })
            ->sortable();

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
        $show = new Show(AcademicClassSctream::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('name', __('Name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AcademicClassSctream());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);

        $form->select('academic_class_id', 'Class')
            ->options(
                AcademicClass::getAcademicClasses([
                    'enterprise_id' => $u->enterprise_id,
                    'academic_year_id' => $u->ent->dp_year,
                ])
            )->rules('required');


        $form->text('name', __('Stream Name'))->rules('required');

        $teachers = [];
        foreach (Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->get() as $key => $a) {
            $teachers[$a['id']] = $a['name'];
            /* if ($a->isRole('teacher')) {

            } */
        }


        $form->select('teacher_id', 'Class teahcer')
            ->options(
                $teachers
            );


        return $form;
    }
}
