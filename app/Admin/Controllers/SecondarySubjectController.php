<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Activity;
use App\Models\Competence;
use App\Models\ParentCourse;
use App\Models\SecondaryCompetence;
use App\Models\SecondarySubject;
use Encore\Admin\Auth\Database\Administrator;
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
        $grid = new Grid(new SecondarySubject());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $u = Auth::user();
            $teachers = [];
            foreach (
                Administrator::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type' => 'employee',
                ])->get() as $key => $a
            ) {
                if ($a->isRole('teacher')) {
                    $teachers[$a['id']] = $a['name'] . "  " . $a['id'];
                }
            }
            $filter->equal('academic_year_id', 'By Academic year')->select(AcademicYear::where([
                'enterprise_id' => $u->enterprise_id,
            ])->get()->pluck('name', 'id'));

            $classes = [];
            foreach (
                AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->orderBy('id', 'desc')->get() as $key => $class
            ) {
                $classes[$class->id] = $class->name_text;
            }
            $filter->equal('academic_class_id', 'Filter By class')->select($classes);
            $filter->equal('teacher_1', 'Filter By Main Teacher')->select($teachers);
            $filter->equal('teacher_2', 'Filter By Teacher 2')->select($teachers);
            $filter->equal('teacher_3', 'Filter By Teacher 3')->select($teachers);
            $filter->equal('teacher_4', 'Filter By Teacher 4')->select($teachers);
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
                    return '-';
                }
                return $this->teacher1->name;
            })
            ->sortable();
        $grid->column('teacher_2', __('Teacher 2'))
            ->display(function ($x) {
                if ($this->teacher2 == null) {
                    return '-';
                }
                return $this->teacher2->name;
            })
            ->sortable();

        $grid->column('teacher_3', __('Teacher 3'))
            ->display(function ($x) {
                if ($this->teacher3 == null) {
                    return '-';
                }
                return $this->teacher3->name;
            })
            ->sortable();

        $grid->column('teacher_4', __('Teacher 4'))
            ->display(function ($x) {
                if ($this->teacher4 == null) {
                    return '-';
                }
                return $this->teacher4->name;
            })
            ->hide()
            ->sortable();
        $grid->column('details', __('Details'))->hide();
        $grid->column('code', __('Code'))->hide();

        $grid->column('is_optional', __('Is optional'))->using([
            1 => 'Optional',
            0 => 'Compulsory',
        ])->filter([
            1 => 'Optional',
            0 => 'Compulsory',
        ])->label([
            1 => 'warning',
            0 => 'success',
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

        if ($form->isCreating()) {
            $form->hidden('enterprise_id', __('Enterprise id'))->value(Auth::user()->ent->id);
            $form->select('academic_class_id', 'Class')
                ->options(
                    AcademicClass::getAcademicClasses([
                        'enterprise_id' => Auth::user()->enterprise_id,
                        'academic_year_id' => Auth::user()->ent->dp_year,
                    ])
                )->rules('required');


            $form->select('parent_course_id', 'Subject')
                ->options(
                    ParentCourse::selectSecondaryArray()
                )->rules('required');
        } else {
            $form->display('academic_class_id', 'Class')
                ->with(function ($x) {
                    if ($this->academic_class == null) {
                        return $x;
                    }
                    return $this->academic_class->short_name;
                });

            $form->display('parent_course_id', 'Subject')
                ->with(function ($x) {
                    if ($this->parent_course == null) {
                        return $x;
                    }
                    return $this->parent_course->name_text;
                });
            $form->text('subject_name', __('Subject name'));
            $form->text('code', __('Code'));
        }

        $u = Admin::user();
        $teachers = [];
        foreach (
            Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'employee',
            ])->get() as $key => $a
        ) {
            if ($a->isRole('teacher')) {
                $teachers[$a['id']] = $a['name'] . "  " . $a['id'];
            }
        }

        $form->select('teacher_1', 'Subject Main Teacher')
            ->options(
                $teachers
            )->rules('required');

        $form->select('teacher_2', 'Subject Teacher 2')
            ->options(
                $teachers
            );

        $form->select('teacher_3', 'Subject Teacher 3')
            ->options(
                $teachers
            );

        $form->select('teacher_4', 'Subject Teacher 4')
            ->options(
                $teachers
            );

        $form->radio('is_optional', __('Is Optional'))
            ->options([
                1 => 'Is Optional',
                0 => 'Is Compulsory',
            ])->rules('required');

        return $form;
    }
}
