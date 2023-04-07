<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicClassFee;
use App\Models\AcademicClassLevel;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\MainCourse;
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


        //Utils::display_system_checklist();

        $grid = new Grid(new AcademicClass());
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where(
                [
                    'enterprise_id' => Admin::user()->enterprise_id,
                    'academic_year_id' => Admin::user()->ent->dp_year,
                ]
            );

        $grid->column('id', __('Class #ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('short_name', __('Short name'));
        $grid->column('academic_year_id', __('Academic year'))->display(function ($ay) {
            return $this->academic_year->name;
        })->sortable();
        $grid->column('class_teahcer_id', __('Class teahcer'))->display(function ($ay) {
            return $this->class_teacher->name;
        });

        

        $grid->actions(function ($x)
        {
            $x->disableDelete();
            $x->disableView();
        });
        
        $grid->column('details', __('Details'))->hide();
        $grid->column('streams', __('Streams'))->display(function ($ay) {
            return $this->academic_class_sctreams->count();
        });
        $grid->column('subjects', __('Subjects'))->display(function ($ay) {
            return count($this->subjects);
        });

        $grid->column('students', __('Students'))->display(function () {
            return count($this->students);
        });

        /*         $grid->column('competences', __('Competences'))->display(function () {
            return count($this->competences);
        }); */

        $grid->column('compulsory_subjects', __('Compulsory Subjects'))->hide();
        $grid->column('optional_subjects', __('Optional Subjects'))->hide();

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

        //Utils::display_system_checklist();

        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->tab('Basic info', function (Form $form) {

            $u = Admin::user();
            $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
            $class_levels = [];
            foreach (AcademicClassLevel::all() as $level) {
                if ($u->ent->type == 'Primary') {
                    if (
                        $level->category == 'Nursery' ||
                        $level->category == 'Primary'
                    ) {
                        $class_levels[$level->id] = $level->name . " - ($level->short_name)";
                    }
                } else if ($u->ent->type == 'Secondary') {
                    if (
                        $level->category == 'Secondary'
                    ) {
                        $class_levels[$level->id] = $level->name . " - ($level->short_name)";
                    }
                } else if ($u->ent->type == 'Advanced') {
                    $class_levels[$level->id] = $level->name . " - ($level->short_name)";
                }
            }


            if ($form->isCreating()) {
                $form->select('academic_year_id', 'Academic year')
                    ->options(
                        AcademicYear::where([
                            'enterprise_id' => $u->enterprise_id,
                        ])->get()
                            ->pluck('name', 'id')
                    )->rules('required');

                $form->select('academic_class_level_id', __('Class'))
                    ->options($class_levels)
                    ->rules('required');
            } else {
                $form->select('academic_year_id', 'Academic year')
                    ->readOnly()
                    ->options(
                        AcademicYear::where([
                            'enterprise_id' => $u->enterprise_id,
                        ])->get()
                            ->pluck('name', 'id')
                    )->rules('required');

                $form->select('academic_class_level_id', __('Class'))
                    ->options($class_levels)
                    ->readOnly()
                    ->rules('required');
            }






            $teachers = [];
            foreach (Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'employee',
            ])->get() as $key => $a) {
                $teachers[$a['id']] = $a['name'];
                /* if ($a->isRole('teacher')) {

                } */
            }


            $form->select('class_teahcer_id', 'Class teahcer')
                ->options(
                    $teachers
                )->rules('required');


            $form->textarea('details', __('Class Details'));

            $form->setWidth(8, 4);
        });


        $form->tab('Class streams', function (Form $form) {
            $form->morphMany('academic_class_sctreams', 'Click on new to add a stream to this class', function (Form\NestedForm $form) {
                $u = Admin::user();
                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $form->text('name', __('Class stream name'))->rules('required');
            });
        });

        $form->tab('Class Subjects', function (Form $form) {
            $form->morphMany('subjects', 'Click on new to add a subject to this class', function (Form\NestedForm $form) {
                $u = Admin::user();

                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $u = Admin::user();
                $ent = Utils::ent();
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



                $subjects = [];
                if ($u->ent->type == 'Primary') {
                    $subjects = MainCourse::where([
                        'subject_type' => 'Primary'
                    ])->orwhere([
                        'subject_type' =>  'Nursery'
                    ]) ->orwhere([
                        'subject_type' =>  'Other'
                    ])->get();
                }else{
                    $subjects = MainCourse::where([
                        'subject_type' => 'Secondary'
                    ])->get();
                }
 

                $form->select('course_id', 'Subject')
                    ->options(
                        $subjects->pluck('name','id')
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
                $form->select('teacher_1', 'Subject teacher 2')
                    ->options(
                        $teachers
                    );
                $form->select('teacher_2', 'Subject teacher 3')
                    ->options(
                        $teachers
                    );
                $form->select('teacher_3', 'Subject teacher 4')
                    ->options(
                        $teachers
                    );

                $form->text('details', __('Details'));
            });
        });

        $form->tab('Competences', function (Form $form) {
            $form->morphMany('competences', 'Click on new to add a Competence to this class', function (Form\NestedForm $form) {
                $u = Admin::user();

                $form->hidden('enterprise_id')->default($u->enterprise_id);
                $u = Admin::user();
                $ent = Utils::ent();
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

                $form->text('name', __('Competence Title'))->rules('required');
                $form->text('description', __('Description'));


                $form->select('teacher_1', 'Competence teacher')
                    ->options(
                        $teachers
                    )->rules('required');
                $form->select('teacher_2', 'Subject teacher 2')
                    ->options(
                        $teachers
                    );
                $form->select('teacher_3', 'Subject teacher 3')
                    ->options(
                        $teachers
                    );
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
