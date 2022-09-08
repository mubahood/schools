<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SubjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Subjects';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        /*  $u = Admin::user();
        $ac = new AcademicYear();
        $ac->enterprise_id = $u->enterprise_id;
        $ac->name = 'Test year';
        $ac->starts = null;
        $ac->ends = null;
        $ac->details = 'Some details';
        $ac->is_active = 0;
        $ac->demo_id = 0;
        $ac->save();
        die("======Romina====="); */

        /* */



        /* 
        compulsory_subjects
        optional_subjects
        */

        // compulsory_subjects	
        // optional_subjects  	


        $grid = new Grid(new Subject());
        Utils::display_system_checklist();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();
            $teachers = [];
            foreach (Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'employee',
            ])->get() as $key => $a) {
                if ($a->isRole('teacher')) {
                    $teachers[$a['id']] = $a['name'] . " " . $a['id'];
                }
            }

            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->equal('subject_teacher', 'Fliter by teacher')->select($teachers);
        });



        $grid->model()
            ->orderBy('id', 'Desc')
            ->where('enterprise_id', Admin::user()->enterprise_id);
        $grid->column('id', __('#ID'))->sortable();
        $grid->column('subject_name', __('SUBJECT'))->sortable();
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($t) {
                return $this->academic_class->name;
            })->sortable();
        /* $grid->column('course_id', __('Course'))
            ->display(function ($t) {
                return $this->course->name;
            }); */

        $grid->column('subject_teacher', __('Subject Teacher'))
            ->display(function ($t) {
                return $this->teacher->name;
            });

        $grid->column('code', __('Code'))->sortable();
        $grid->column('details', __('Details'))->hide();

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
        $show = new Show(Subject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('subject_teacher', __('Subject teacher'));
        $show->field('code', __('Code'));
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
        /* $s = Subject::find(150);
        $s->details .= time().rand(1000,10000000);
        $s->save();  */

        $form = new Form(new Subject());

        Utils::display_system_checklist();

        $u = Admin::user();
        $teachers = [];
        foreach (Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->get() as $key => $a) {
            if ($a->isRole('teacher')) {
                $teachers[$a['id']] = $a['name'] . "  " . $a['id'];
            }
        }

        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->select('academic_class_id', 'Class')
            ->options(
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id
                ])->get()
                    ->pluck('name', 'id')
            )->rules('required');

        $form->select('course_id', 'Subject')
            ->options(
                Course::all()
                    ->pluck('name', 'id')
            )->rules('required');


        $form->select('subject_teacher', 'Subject teacher')
            ->options(
                $teachers
            )->rules('required');


        $form->text('code', __('Subject Code'));

        $form->radio('is_optional', 'Subject type')
            ->options([
                0 => 'Compulsory subject',
                1 => 'Optional subject',
            ])->rules('required');

        $form->textarea('details', __('Details'));

        return $form;
    }
}
