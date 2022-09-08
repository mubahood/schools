<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Utils;
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
        
        /* 
	
	
	
name	
short_name	
details	
demo_id	
compulsory_subjects	
optional_subjects	

*/

        /* $fee = AcademicClassFee::find(1); 
        if($fee!=null){
            $fee->name .= rand(100,1000);
            $fee->save();
            dd("Done"); 
        }else{
            dd("Bad");
        } */

        Utils::display_system_checklist();

        $grid = new Grid(new AcademicClass());
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where('enterprise_id', Admin::user()->enterprise_id);

        $grid->column('id', __('Class #ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('short_name', __('Short name'));
        $grid->column('academic_year_id', __('Academic year'))->display(function ($ay) {
            return $this->academic_year->name;
        })->sortable();
        $grid->column('class_teahcer_id', __('Class teahcer'))->display(function ($ay) {
            return $this->class_teacher->name;
        });

        $grid->column('details', __('Details'))->hide();
        $grid->column('streams', __('Streams'))->display(function ($ay) {
            return $this->academic_class_sctreams->count();
        });
        $grid->column('subjects', __('Subjects'))->display(function ($ay) {
            return $this->subjects->count();
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

        Utils::display_system_checklist();

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->tab('Basic info', function (Form $form) {

            $u = Admin::user();
            $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');


            $form->select('academic_year_id', 'Academic year')
                ->options(
                    AcademicYear::where([
                        'enterprise_id' => $u->enterprise_id,
                        'is_active' => 1,
                    ])->get()
                        ->pluck('name', 'id')
                )->rules('required');


            $form->text('name', __('Class Name'))->rules('required');
            $form->text('short_name', __('Class short name'))->rules('required');
            $form->text('compulsory_subjects', __('Number of compulsory subjects'))->rules('required');
            $form->text('optional_subjects', __('Number of optional subjects'))->rules('required');

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
        });

        $form->tab('Class Subjects', function (Form $form) {
            $form->morphMany('subjects', 'Click on new to add a subject to this class', function (Form\NestedForm $form) {
                $u = Admin::user();

                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $u = Admin::user();
                $teachers = [];
                foreach (Administrator::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type' => 'employee',
                ])->get() as $key => $a) {
                    if ($a->isRole('teacher')) {
                        $teachers[$a['id']] = $a['name'] . " #" . $a['id'];
                    }
                }
                $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');


                $form->select('course_id', 'Course')
                    ->options(
                        Course::all()
                            ->pluck('name', 'id')
                    )->rules('required');

                $form->radio('is_optional', 'Subject type')
                    ->options([
                        0 => 'Compulsory subject',
                        1 => 'Optional subject',
                    ])->rules('required');

                $form->select('subject_teacher', 'Subject teacher')
                    ->options(
                        $teachers
                    )->rules('required');

                $form->text('details', __('Details'));
            });
        });

        $form->tab('Class streams', function (Form $form) {
            $form->morphMany('academic_class_sctreams', 'Click on new to add a stream to this class', function (Form\NestedForm $form) {
                $u = Admin::user();
                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $form->text('name', __('Class stream name'))->rules('required');
            });
        });


        /* $form->tab('Fees', function (Form $form) {
            $form->morphMany('academic_class_fees', 'Click on new to add fees to this class', function (Form\NestedForm $form) {
                $u = Admin::user();
                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $form->text('name', __('Fee title'))->rules('required');
                $form->text('amount', __('Fee amount'))->rules('required')->rules('int')->attribute('type', 'number');
            });
        }); */



        return $form;
    }
}
