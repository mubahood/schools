<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Activity;
use App\Models\Competence;
use App\Models\ParentCourse;
use App\Models\SecondaryCompetence;
use App\Models\SecondarySubject;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class SecondarySubjectController extends AdminController
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
   /*    
        $u = Admin::user();
        $ent = Admin::user()->ent; 
        $term = Admin::user()->ent->active_term();
        set_time_limit(-1);

        foreach (SecondarySubject::where('enterprise_id', 11)->get() as $key => $sub) {
            $acts = $sub->activities;
            if (count($acts) < 3) {
                for ($i = 0; $i < 3; $i++) {
                    $a = new Activity();
                    $a->enterprise_id = 11;
                    $a->term_id = $term->id;
                    $a->class_type = $sub->academic_class->short_name;
                    $a->subject_id = $sub->id;
                    $a->theme = $sub->subject_name . ' Theme';
                    $a->topic = $sub->subject_name . ' Topic ' . $i;
                    $a->description = 'Some details about this activity go here. Some details about this activity go here. Some details about this activity go here. Some details about this activity go here.';
                    $a->max_score =  3;
                    $a->save();
                }
            }
        }
        $comps = SecondaryCompetence::where(['enterprise_id' => 11,'score' => null])->get();
        //dd(count($comps));
        foreach ($comps as $key => $sub) {
            $sub->score = rand(0,3);
            if($sub->score < 3){
                $sub->score = $sub->score.'.'.rand(1,9);
            }else{
                $sub->score = $sub->score.'.0';
            }
            $sub->score = ((float)($sub->score));
            $sub->submitted = 1;
            $sub->save(); 
        }
        dd("done");  */

        $grid = new Grid(new SecondarySubject());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Auth::user();
            $filter->equal('academic_year_id', 'By Academic year')->select(AcademicYear::where([
                'enterprise_id' => $u->enterprise_id,
            ])->get()->pluck('name', 'id'));

            $classes = [];
            foreach (AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
            ])->orderBy('id', 'desc')->get() as $key => $class) {
                $classes[$class->id] = $class->name_text;
            }

            $filter->equal('academic_class_id', 'By class')->select($classes);
        });

        $grid->actions(function ($act) {
            $act->disableView();
            $act->disableDelete();
        });
        $grid->model()->where([
            'enterprise_id' => Auth::user()->enterprise_id,
        ])
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('academic_year_id', __('Year'))
            ->display(function ($x) {
                if ($this->year == null) {
                    return $x;
                }
                return $this->year->name;
            })
            ->sortable();

        $grid->quickSearch('subject_name')->placeholder('Seach by subject');
        $grid->disableBatchActions();


        $grid->column('academic_class_id', __('Class'))
            ->display(function ($x) {
                if ($this->academic_class == null) {
                    return $x;
                }
                return $this->academic_class->short_name;
            })
            ->sortable();

        $grid->column('subject_name', __('Subject'))->sortable();

        $grid->column('term_1', __('Term 1 - Activities'))
            ->display(function ($x) {
                $term = null;
                foreach ($this->year->terms as $key => $t) {
                    if ($t->name == '1') {
                        $term = $t;
                        break;
                    }
                }
                if ($term == null) {
                    return 'N/A';
                }
                $count = count($this->get_activities_in_term($term->id));
                return $count . "";
            });

        $grid->column('term_2', __('Term 2  - Activities'))
            ->display(function ($x) {
                $term = null;
                foreach ($this->year->terms as $key => $t) {
                    if ($t->name == '2') {
                        $term = $t;
                        break;
                    }
                }
                if ($term == null) {
                    return 'N/A';
                }
                $count = count($this->get_activities_in_term($term->id));
                return $count . "";
            });
        $grid->column('term_3', __('Term 3  - Activities'))
            ->display(function ($x) {
                $term = null;
                foreach ($this->year->terms as $key => $t) {
                    if ($t->name == '3') {
                        $term = $t;
                        break;
                    }
                }
                if ($term == null) {
                    return 'N/A';
                }
                $count = count($this->get_activities_in_term($term->id));
                return $count . "";
            });

        $grid->column('teacher_1', __('Teacher'))
            ->display(function ($x) {
                if ($this->teacher1 == null) {
                    return $x;
                }
                return $this->teacher1->name;
            })
            ->sortable();
        $grid->column('teacher_2', __('Teacher 2'))
            ->display(function ($x) {
                if ($this->teacher2 == null) {
                    return $x;
                }
                return $this->teacher2->name;
            })
            ->sortable();

        $grid->column('teacher_3', __('Teacher 3'))
            ->display(function ($x) {
                if ($this->teacher3 == null) {
                    return $x;
                }
                return $this->teacher3->name;
            })
            ->sortable();

        $grid->column('teacher_4', __('Teacher 4'))
            ->display(function ($x) {
                if ($this->teacher4 == null) {
                    return $x;
                }
                return $this->teacher4->name;
            })
            ->hide()
            ->sortable();
        $grid->column('details', __('Details'))->hide();
        $grid->column('code', __('Code'))->hide();

        $grid->column('is_optional', __('Is optional'))->using([
            0 => 'Optional',
            1 => 'Compulsory',
        ]);

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
        $show = new Show(SecondarySubject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('parent_course_id', __('Parent course id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('teacher_1', __('Teacher 1'));
        $show->field('teacher_2', __('Teacher 2'));
        $show->field('teacher_3', __('Teacher 3'));
        $show->field('teacher_4', __('Teacher 4'));
        $show->field('subject_name', __('Subject name'));
        $show->field('details', __('Details'));
        $show->field('code', __('Code'));
        $show->field('is_optional', __('Is optional'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondarySubject());

        $form->hidden('enterprise_id', __('Enterprise id'))->value(Auth::user()->ent->id);

        $form->select('academic_class_id', 'Class')
            ->options(
                AcademicClass::where([
                    'enterprise_id' => Auth::user()->enterprise_id,
                    'academic_year_id' => Auth::user()->ent->dp_year,
                ])->get()
                    ->pluck('name', 'id')
            )->rules('required');


        $form->select('parent_course_id', 'Subject')
            ->options(
                ParentCourse::where([
                    'type' => 'Secondary',
                ])
                    ->orwhere([
                        'type' => 'Advanced',
                    ])
                    ->get()
                    ->pluck('name', 'id')
            )->rules('required');


        $form->number('teacher_1', __('Teacher 1'));
        $form->number('teacher_2', __('Teacher 2'));
        $form->number('teacher_3', __('Teacher 3'));
        $form->number('teacher_4', __('Teacher 4'));
        $form->textarea('subject_name', __('Subject name'));
        $form->textarea('details', __('Details'));
        $form->textarea('code', __('Code'));
        $form->switch('is_optional', __('Is optional'));

        return $form;
    }
}
