<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Activity;
use App\Models\SecondaryCompetence;
use App\Models\SecondarySubject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SecondaryCompetenceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Competences';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new SecondaryCompetence());
        $grid->disableActions();
        $grid->disableCreateButton();


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


            $subs = [];
            foreach (SecondarySubject::where([
                'enterprise_id' => $u->ent->id, 
                'academic_year_id' => $u->ent->dpYear()->id
            ])->get() as $key => $value) {
                $subs[$value->id] = $value->subject_name . " - " . $value->academic_class->short_name;
            }
            $filter->equal('secondary_subject_id', 'Fliter by subject')->select($subs);


            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=User"
            );
            $filter->equal('administrator_id', 'Filter by student')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url); 
        });




        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');
        $grid->disableBatchActions();



        $grid->column('term_id', __('Term'))
            ->display(function ($x) {
                if ($this->term == null) {
                    return $x;
                }
                return 'Term ' . $this->term->name_text;
            })
            ->sortable();



        $grid->column('academic_class_id', __('Class'))->display(function () {
            return $this->academic_class->name_text;
        })->sortable();




        $grid->column('secondary_subject_id', __('Subject'))
            ->display(function ($x) {
                if ($this->secondary_subject == null) {
                    return $x;
                }
                return $this->secondary_subject->subject_name;
            })
            ->sortable();

        $grid->column('administrator_id', __('Student'))
            ->display(function ($x) {
                if ($this->student == null) {
                    return $x;
                }
                return $this->student->name;
            })
            ->sortable();
        

        /*         $grid->column('academic_year_id', __('Academic year id')); */
        $grid->column('score', __('Score'))->editable();
        $grid->column('submitted', __('Submitted'))
            ->using([
                1 => 'Submitted',
                0 => 'Not Submitted'
            ])->dot([
                1 => 'success',
                0 => 'danger',
            ])
            ->filter([
                1 => 'Submitted',
                0 => 'Not Submitted'
            ])
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
        $show = new Show(SecondaryCompetence::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('parent_course_id', __('Parent course id'));
        $show->field('secondary_subject_id', __('Secondary subject id'));
        $show->field('term_id', __('Term id'));
        $show->field('academic_year_id', __('Academic year id'));
        $show->field('score', __('Score'));
        $show->field('submitted', __('Submitted'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SecondaryCompetence());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('parent_course_id', __('Parent course id'));
        $form->number('secondary_subject_id', __('Secondary subject id'));
        $form->number('term_id', __('Term id'));
        $form->number('academic_year_id', __('Academic year id'));
        $form->decimal('score', __('Score'));
        $form->switch('submitted', __('Submitted'));

        return $form;
    }
}
