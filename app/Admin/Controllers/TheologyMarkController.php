<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\StudentHasTheologyClass;
use App\Models\TheologyClass;
use App\Models\TheologyExam;
use App\Models\TheologyMark;
use App\Models\TheologySubject;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TheologyMarkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Theology Marks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {




        /*         





"" => 1
        "" => 1
        "" => 1
        "" => 2891
        "teacher_id" => 3025
        "score" => 0.0
        "remarks" => ""
        "is_submitted" => 0
        "is_missed" => 1 
        
        */
        $grid = new Grid(new TheologyMark());
        $grid->disableActions();
        $grid->disableCreateButton();
        /* foreach (Mark::where([
            'theology_exam_id' => 5
        ])->get() as $key => $v) {
            $v->score = rand(1000, 10000) % 41;
            $v->save();
        } */

        $subs_ids = [];
        $u = Admin::user(); 
        $ent = Enterprise::find($u->enterprise_id);
        $dpYear =  $ent->dpYear();
        if ($dpYear == null) {
            die("Display year not found.");
        } 
        if (Admin::user()->isRole('dos')) {
            foreach (TheologySubject::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('theology_course_id', 'asc')
                ->get() as $ex) {
                if ($ex->theology_class->academic_year_id != $dpYear->id) {
                    continue;
                }
                $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name;
            }
        } else {
            foreach (TheologySubject::where([
                'enterprise_id' => $u->enterprise_id
            ])
                ->orderBy('theology_course_id', 'asc')
                ->get() as $ex) {
                if (
                    $ex->subject_teacher == Admin::user()->id ||
                    $ex->teacher_1 == Admin::user()->id ||
                    $ex->teacher_2 == Admin::user()->id ||
                    $ex->teacher_3 == Admin::user()->id 
                    ) {
                    if ($ex->theology_class->academic_year_id != $dpYear->id) {
                        continue;
                    }
                    $subs_ids[] = $ex->id; 
                }
            }
        }
        
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
        ])->orderBy('id', 'DESC');

        if (!Admin::user()->isRole('dos')) {

       /*        $grid->model()->where(
                'theology_subject_id', 'in', '(89,98)' 
            );  */
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->disableActions();
        }

        $grid->disableBatchActions();

        $grid->export(function ($export) {
            $export->filename('School dynamics.csv');
            $export->except(['is_submitted']);
            $export->originalValue(['score', 'remarks']);
        });

        if (
            (!Admin::user()->isRole('dos')) &&
            ((!isset($_GET['theology_class_id'])) ||
                (!isset($_GET['theology_exam_id'])) ||
                (!isset($_GET['theology_subject_id'])) ||
                (((int)($_GET['theology_subject_id'])) < 1) ||
                (((int)($_GET['theology_exam_id'])) < 1) ||
                (((int)($_GET['theology_class_id'])) < 1))
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
                ((!isset($_GET['theology_class_id'])) ||
                    (!isset($_GET['theology_exam_id'])) ||
                    (!isset($_GET['theology_subject_id'])) ||
                    (((int)($_GET['theology_subject_id'])) < 1) ||
                    (((int)($_GET['theology_exam_id'])) < 1) ||
                    (((int)($_GET['theology_class_id'])) < 1))
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
            $filter->equal('theology_class_id', 'Filter by class')->select(TheologyClass::where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $year->id
            ])
                ->orderBy('id', 'Desc')
                ->get()->pluck('name_text', 'id'));


            $exams = [];
            foreach (TheologyExam::where([
                'enterprise_id' => $u->enterprise_id,
                'term_id' => $term->id,
            ])->orderBy('id', 'Desc')->get() as $ex) {
                $exams[$ex->id] = $ex->name_text;
            }
            $filter->equal('theology_exam_id', 'Filter by exam')->select($exams);

            $subs = [];
            $u = Admin::user(); 
            $ent = Enterprise::find($u->enterprise_id);
            $dpYear =  $ent->dpYear();
            if ($dpYear == null) {
                die("Display year not found.");
            } 
            if (Admin::user()->isRole('dos')) {
                foreach (TheologySubject::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('theology_course_id', 'asc')
                    ->get() as $ex) {
                    if ($ex->theology_class->academic_year_id != $dpYear->id) {
                        continue;
                    }
                    $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name;
                }
            } else {
                foreach (TheologySubject::where([
                    'enterprise_id' => $u->enterprise_id
                ])
                    ->orderBy('theology_course_id', 'asc')
                    ->get() as $ex) {
                    if (
                        $ex->subject_teacher == Admin::user()->id ||
                        $ex->teacher_1 == Admin::user()->id ||
                        $ex->teacher_2 == Admin::user()->id ||
                        $ex->teacher_3 == Admin::user()->id 
                        ) {
                        if ($ex->theology_class->academic_year_id != $dpYear->id) {
                            continue;
                        }
                        $subs[$ex->id] = $ex->course->name . " - " . $ex->theology_class->name . " - " . $dpYear->name; 
                    }
                }
            }
            
        

            $filter->equal('theology_subject_id', 'Filter by subject')->select($subs);


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



        $grid->column('id', __('#ID'))->sortable();
        $grid->column('student_id', __('Student'))->display(function () {
            if ($this->student == null) {
                return "-";
            }
            return $this->student->name;
        })->sortable();
        $grid->column('theology_exam_id', __('Exam'))
            ->display(function () {
                return $this->exam->name_text;
            })->sortable();

        $grid->column('theology_class_id', __('Class'))->display(function () {
            return $this->class->name;
        })->sortable();
        $grid->column('theology_subject_id', __('Subject'))->display(function () {
            return $this->subject->course->name;
        })->sortable();

        $grid->column('score', __('Score'))->sortable()->editable();
        $grid->column('remarks', __('Remarks'))->editable();
        /*  $grid->column('is_missed', __('Missed')); */
        $grid->column('is_submitted', __('Submitted'))->display(function ($st) {
            if ($st)
                return '<span class="bagde bagde-success">Submitted</span>';
            else
                return '<span class="bagde bagde-danger">Missing</span>';
        })->filter([
            1 => 'Submitted',
            0 => 'Missing',
        ])->sortable();

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




    // protected function grid()
    // {
    //     $grid = new Grid(new TheologyMark());

    //     $grid->column('id', __('Id')); 
    //     $grid->column('created_at', __('Created at'));
    //     $grid->column('updated_at', __('Updated at'));
    //     $grid->column('enterprise_id', __('Enterprise id'));
    //     $grid->column('theology_exam_id', __('Theology exam id'));
    //     $grid->column('theology_class_id', __('Theology class id'));
    //     $grid->column('theology_subject_id', __('Theology subject id'));
    //     $grid->column('student_id', __('Student id'));
    //     $grid->column('teacher_id', __('Teacher id'));
    //     $grid->column('score', __('Score'))->editable();
    //     $grid->column('remarks', __('Remarks'));
    //     $grid->column('is_submitted', __('Is submitted'));
    //     $grid->column('is_missed', __('Is missed'));

    //     return $grid;
    // }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TheologyMark::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('theology_exam_id', __('Theology exam id'));
        $show->field('theology_class_id', __('Theology class id'));
        $show->field('theology_subject_id', __('Theology subject id'));
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
        $form = new Form(new TheologyMark());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('theology_exam_id', __('Theology exam id'));
        $form->number('theology_class_id', __('Theology class id'));
        $form->number('theology_subject_id', __('Theology subject id'));
        $form->number('student_id', __('Student id'));
        $form->number('teacher_id', __('Teacher id'));
        $form->decimal('score', __('Score'))->default(0.00);
        $form->textarea('remarks', __('Remarks'));
        $form->switch('is_submitted', __('Is submitted'));
        $form->switch('is_missed', __('Is missed'))->default(1);

        return $form;
    }
}
