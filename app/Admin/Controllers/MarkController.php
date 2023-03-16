<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class MarkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Marks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Mark());


        $grid->export(function ($export) {
            $export->filename('School dynamics.csv');
            $export->except(['is_submitted']);
            $export->originalValue(['score', 'remarks']);
        });


        /* foreach (Mark::where([
            'exam_id' => 5
        ])->get() as $key => $v) {
            $v->score = rand(1000, 10000) % 41;
            $v->save();
        } */

        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        if (!Admin::user()->isRole('dos')) {

            $grid->model()->where([
                /*                 'teacher_id' => Admin::user()->id, */]);
        }

        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->disableBatchActions();



        if (
            (!Admin::user()->isRole('dos')) &&
            ((!isset($_GET['class_id'])) ||
                (!isset($_GET['exam_id'])) ||
                (!isset($_GET['subject_id'])) ||
                (((int)($_GET['subject_id'])) < 1) ||
                (((int)($_GET['exam_id'])) < 1) ||
                (((int)($_GET['class_id'])) < 1))
        ) {
            admin_success(
                'Alert',
                'Select class, exam and subject and press "search button" to enter marks.'
            );
            $grid->model()->where([
                'enterprise_id' => 0,
            ])->orderBy('id', 'DESC');
        }

        $grid->filter(function ($filter) {


            if (
                (!Admin::user()->isRole('dos')) &&
                ((!isset($_GET['class_id'])) ||
                    (!isset($_GET['exam_id'])) ||
                    (!isset($_GET['subject_id'])) ||
                    (((int)($_GET['subject_id'])) < 1) ||
                    (((int)($_GET['exam_id'])) < 1) ||
                    (((int)($_GET['class_id'])) < 1))
            ) {
                $filter->expand();
            }


            // Remove the default id filter 
            $filter->disableIdFilter();
            $ent = Admin::user()->ent;
            $year = $ent->dpYear();
            $term = $ent->active_term();

            // Add a column filter 
            $u = Admin::user();
            $filter->equal('class_id', 'Filter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $year->id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));


            $exams = [];
            foreach (Exam::where([
                'enterprise_id' => $u->enterprise_id,
                'term_id' => $term->id,
            ])->get() as $ex) {
                $exams[$ex->id] = $ex->name_text;
            }

            $filter->equal('exam_id', 'Filter by exam')->select($exams);

            $subs = [];
            foreach (Subject::where([
                'enterprise_id' => $u->enterprise_id,
            ])
                ->orderBy('id', 'desc')
                ->get() as $ex) {
                if($ex->academic_class->academic_year_id != $year->id){
                    continue;
                }

                
                if (Admin::user()->isRole('dos')) {
                    $subs[$ex->id] = $ex->subject_name . " - " . $ex->academic_class->name_text;
                } else {
                    if (
                        $ex->subject_teacher == Admin::user()->id ||
                        $ex->teacher_1 == Admin::user()->id ||
                        $ex->teacher_2 == Admin::user()->id ||
                        $ex->teacher_3 == Admin::user()->id
                    ) { 
                        $subs[$ex->id] = $ex->subject_name . " - " . $ex->academic_class->name_text;
                    }
                }
            }
            /* /
  "subject_name" => "Reading"
    "demo_id" => 0
    "is_optional" => 0
    "main_course_id" => 20
    "" => 0
    "" => 0
    "teacher_3" => 0
    "parent_course_id" => null
    "academic_year_id" => 3

*/
            $filter->equal('subject_id', 'Filter by subject')->select($subs);


            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );

            $filter->equal('student_id', 'Student')->select()->ajax($ajax_url);
        });



        $grid->column('id', __('#ID'))->hide()->sortable();
        $grid->column('student_id', __('Student'))->display(function () {
            if ($this->student == null) {
                return "-";
            }
            return $this->student->name;
        })->sortable();

        $grid->column('score', __('Score'))->sortable()->editable();
        $grid->column('remarks', __('Remarks'))->editable();

        $grid->column('exam_id', __('Exam'))
            ->display(function () {
                return $this->exam->name_text;
            })->sortable();

        $grid->column('class_id', __('Class'))->display(function () {
            return $this->class->name;
        })->sortable();
        $grid->column('subject_id', __('Subject'))->display(function () {
            return $this->subject->subject_name;
        })->sortable();


        /*  $grid->column('is_missed', __('Missed')); */
        $grid->column('is_submitted', __('Submitted'))->display(function ($st) {
            if ($st)
                return '<span class="bagde bagde-success">Submitted</span>';
            else
                return '<span class="bagde bagde-danger">Missing</span>';
        })
        ->filter([
            1 => 'Submitted',
            0 => 'Not Submitted',
        ])
        ->sortable();

        if (Admin::user()->isRole('dos')) {
            $grid->column('teacher.name', __('Teacher'))->sortable();
        } else {
            $grid->column('teacher.name', __('Teacher'))->sortable()->hide();
        }


        $grid->column('updated_at', __('Last Updat'))->display(function ($v) {
            return Utils::my_date_time($v);
        })->sortable();

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
        $show = new Show(Mark::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('exam_id', __('Exam id'));
        $show->field('class_id', __('Class id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('student_id', __('Student id'));
        $show->field('teacher_id', __('Teacher id'));
        $show->field('score', __('Score'));
        $show->field('remarks', __('Remarks'));
        $show->field('is_submitted', __('Is submitted'));
        $show->field('is_missed', __('Is missed'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new Mark());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('exam_id', __('Exam id'));
        $form->number('class_id', __('Class id'));
        $form->number('subject_id', __('Subject id'));
        $form->number('student_id', __('Student id'));
        $form->number('teacher_id', __('Teacher id'));
        $form->decimal('score', __('Score'))->default(0.00);
        $form->textarea('remarks', __('Remarks'));
        $form->switch('is_submitted', __('Is submitted'));
        $form->switch('is_missed', __('Is missed'))->default(1);

        return $form;
    }
}
