<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AcademicClassController extends AdminController
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
        $grid = new Grid(new AcademicClass());
        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id);
        $grid->column('id', __('Class #ID'))->sortable();
        $grid->column('academic_year_id', __('Academic year'))->display(function ($ay) {
            return $this->academic_year->name;
        });
        $grid->column('class_teahcer_id', __('Class teahcer'))->display(function ($ay) {
            return $this->class_teacher->name;
        });
        $grid->column('name', __('Name'));
        $grid->column('short_name', __('Short name'));
        $grid->column('details', __('Details'))->hide();
        $grid->column('streams', __('Streams'))->display(function ($ay) {
            return $this->academic_class_sctreams->count();
        });

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
        $show = new Show(AcademicClass::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('class_teahcer_id', __('Class teahcer id'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AcademicClass());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');


        $form->select('academic_year_id', 'Academic year')
            ->options(
                AcademicYear::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get()
                    ->pluck('name', 'id')
            )->rules('required');

        $form->text('name', __('Class Name'))->rules('required');
        $form->text('short_name', __('Class short name'))->rules('required');

        $teachers = [];
        foreach (Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->get() as $key => $a) {
            if ($a->isRole('teacher')) {
                $teachers[$a['id']] = $a['name'];
            }
        }


        $form->select('class_teahcer_id', 'Class teahcer')
            ->options(
                $teachers
            )->rules('required');


        $form->textarea('details', __('Class Details'));

        $form->setWidth(8, 4);


        $form->morphMany('academic_class_sctreams', 'Click on new to add a stream to this class', function (Form\NestedForm $form) {
            $u = Admin::user();
            $form->hidden('enterprise_id')->default($u->enterprise_id);
            $form->text('details', __('Class stream name'))->rules('required');
        });


        return $form;
    }
}
