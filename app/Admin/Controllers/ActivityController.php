<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Activity;
use App\Models\Enterprise;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ActivityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Activities';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Activity());
        $grid->disableBatchActions();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Admin::user();

            $filter->equal('term_id', 'Fliter by term')->select(Term::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));

            $filter->equal('academic_class_id', 'Fliter by class')->select(AcademicClass::where([
                'enterprise_id' => $u->enterprise_id
            ])->get()
                ->pluck('name_text', 'id'));

            $u = Admin::user();
            $class = AcademicClass::where([
                'enterprise_id' => $u->ent->id,
                'academic_class_level_id' => 11,
                'academic_year_id' => $u->ent->dpYear()->id
            ])->first();
            if ($class == null) {
                die("S.1 not found.");
            }
            $subs = [];
            foreach ($class->secondarySubjects as $key => $value) {
                $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
            }
            $filter->equal('subject_id', 'Fliter by subject')->select($subs);
        });


        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                /*   'type' => 'STUDENT_ACCOUNT' */
            ]);

        $grid->column('term_id', __('Term'))
            ->display(function ($x) {
                if ($this->term == null) {
                    return $x;
                }
                return 'Term ' . $this->term->name_text;
            })
            ->sortable();

        $grid->column('academic_year_id', __('Academic year'))
            ->display(function ($x) {
                if ($this->year == null) {
                    return $x;
                }
                return $this->year->name;
            })
            ->hide()
            ->sortable();

        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class == null) {
                    return $x;
                }
                return $this->academic_class->short_name;
            })
            ->sortable();
        $grid->column('parent_course_id', __('Subject'))
            ->display(function ($x) {
                if ($this->parent_course == null) {
                    return $x;
                }
                return $this->parent_course->name;
            })
            ->sortable();

        $grid->column('theme', __('Theme'))->hide();
        $grid->column('topic', __('Topic'))
            ->sortable();
        $grid->column('description', __('Description'))->sortable()->hide();
        $grid->column('max_score', __('Max Score'));
        $grid->column('competences', __('Competences'))
            ->display(function ($x) {
                if ($this->competences == null) {
                    return 'N/A';
                }
                return count($this->competences);
            });
        $grid->column('submitted_text', __('Submitted'));

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
        $show = new Show(Activity::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('term_id', __('Term id'));
        $show->field('class_type', __('Class type'));
        $show->field('theme', __('Theme'));
        $show->field('topic', __('Topic'));
        $show->field('description', __('Description'));
        $show->field('max_score', __('Max score'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
       /*  $a = new Activity();
        $a->enterprise_id = 11;
        $a->term_id = 16;
        $a->class_type = 'S.1';
        $a->subject_id = 57;
        $a->theme = 'Test English Theme';
        $a->topic = 'Test English Topic';
        $a->description = 'Some details about this activity';
        $a->max_score =  3;
        $a->save(); */

        $form = new Form(new Activity());

        $u = Admin::user();
        $ent = Enterprise::find($u->enterprise_id);
        $dpYear =  $ent->dpYear();
        if ($dpYear == null) {
            die("Display year not found.");
        }
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $terms = [];
        foreach ($dpYear->terms as $key => $value) {
            $terms[$value->id] = "Term " . $value->name . " - " . $dpYear->name;
        }

        $form->radio('term_id', __('Due Term'))->options($terms)->rules('required');
        $form->radio('class_type', 'Class')
            ->options([
                'S.1' => 'S.1',
                'S.2' => 'S.2',
                'S.3' => 'S.3',
                'S.4' => 'S.4',
            ])
            ->when('S.1', function ($f) {
                $u = Admin::user();
                $class = AcademicClass::where([
                    'enterprise_id' => $u->ent->id,
                    'academic_class_level_id' => 11,
                    'academic_year_id' => $u->ent->dpYear()->id
                ])->first();
                if ($class == null) {
                    die("S.1 not found.");
                }
                $subs = [];
                foreach ($class->secondarySubjects as $key => $value) {
                    $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
                }
                $f->select('subject_id', 'Select subject')
                    ->options($subs)
                    ->rules('required');
            })
            ->when('S.2', function ($f) {
                $u = Admin::user();
                $class = AcademicClass::where([
                    'enterprise_id' => $u->ent->id,
                    'academic_class_level_id' => 12,
                    'academic_year_id' => $u->ent->dpYear()->id
                ])->first();
                if ($class == null) {
                    die("S.1 not found.");
                }
                $subs = [];
                foreach ($class->secondarySubjects as $key => $value) {
                    $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
                }
                $f->select('subject_id', 'Select subject')
                    ->options($subs)
                    ->rules('required');
            })
            ->when('S.3', function ($f) {
                $u = Admin::user();
                $class = AcademicClass::where([
                    'enterprise_id' => $u->ent->id,
                    'academic_class_level_id' => 13,
                    'academic_year_id' => $u->ent->dpYear()->id
                ])->first();
                if ($class == null) {
                    die("S.1 not found.");
                }
                $subs = [];
                foreach ($class->secondarySubjects as $key => $value) {
                    $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
                }
                $f->select('subject_id', 'Select subject')
                    ->options($subs)
                    ->rules('required');
            })
            ->when('S.4', function ($f) {
                $u = Admin::user();
                $class = AcademicClass::where([
                    'enterprise_id' => $u->ent->id,
                    'academic_class_level_id' => 14,
                    'academic_year_id' => $u->ent->dpYear()->id
                ])->first();
                if ($class == null) {
                    die("S.1 not found.");
                }
                $subs = [];
                foreach ($class->secondarySubjects as $key => $value) {
                    $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
                }
                $f->select('subject_id', 'Select subject')
                    ->options($subs)
                    ->rules('required');
            })
            ->rules('required')
            ->required();
        /* 
"id" => 48
"created_at" => "2023-02-21 18:33:44"
"updated_at" => "2023-02-21 18:33:44"
"enterprise_id" => 11
"academic_year_id" => 6
"class_teahcer_id" => 3229
"name" => "Senior one"
"short_name" => "S.1"
"details" => "Senior one"
"demo_id" => 0
"compulsory_subjects" => 0
"optional_subjects" => 0
"class_type" => "Secondary"
"" => 11
*/

        /* 
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('', __('Main course id'));
        $form->textarea('', __('Class type'));
*/



        $form->text('theme', __('Activity Theme'))->rules('required');
        $form->text('topic', __('Topic'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');
        $form->decimal('max_score', __('Maximum score'))->rules('required');

        return $form;
    }
}
