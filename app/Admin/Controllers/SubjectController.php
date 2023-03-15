<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\MainCourse;
use App\Models\Subject;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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



        $grid = new Grid(new Subject());




        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'academic_year_id' => Admin::user()->ent->dp_year,
        ])
            ->orderBy('id', 'Desc');
        $grid->disableBatchActions();

        if (!Admin::user()->isRole('dos')) {
            $grid->model()->where('subject_teacher', Admin::user()->id);
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }

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
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => Admin::user()->ent->dp_year,
            ])->get()
                ->pluck('name_text', 'id'));
            $filter->equal('subject_teacher', 'Fliter by teacher')->select($teachers);
        });



        $grid->model()
            ->orderBy('id', 'Desc')
            ->where('enterprise_id', Admin::user()->enterprise_id);
        $grid->column('id', __('#ID'))->sortable();
        $grid->column('subject_name', __('SUBJECT'))
            ->display(function ($t) {
                return $this->course->name;
            });
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($t) {
                if ($this->academic_class == null) {
                    return "#{$this->id}";
                }
                return $this->academic_class->name
                    . " - " .
                    $this->academic_class->academic_year->name;
            })->sortable();
        /* $grid->column('course_id', __('Course'))
            ->display(function ($t) {
                return $this->course->name;
            }); */

        $grid->column('subject_teacher', __('Subject Teacher'))
            ->display(function ($t) {
                return $this->teacher->name;
            });

        $grid->column('code', __('Code'))->hide()->sortable();
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
                    'enterprise_id' => $u->enterprise_id,
                    'academic_year_id' => Admin::user()->ent->dp_year,
                ])->get()
                    ->pluck('name', 'id')
            )->rules('required');
 
        $ent = Utils::ent();

        $subjects = [];
        if ($u->ent->type == 'Primary') {
            $subjects = MainCourse::where([
                'subject_type' => 'Primary'
            ])
                ->orwhere([
                    'subject_type' =>  'Nursery'
                ])
                ->orwhere([
                    'subject_type' =>  'Other'
                ])
                ->get();
        } else {
            $subjects = MainCourse::where([
                'subject_type' => 'Secondary'
            ])->get();
        }


        $form->select('course_id', 'Subject')
            ->options(
                $subjects->pluck('name', 'id')
            )->rules('required');


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
