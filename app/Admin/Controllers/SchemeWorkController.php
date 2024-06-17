<?php

namespace App\Admin\Controllers;

use App\Models\Subject;
use App\Models\Term;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchemeWorkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Scheme of Work';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $u = Admin::user();
        $active_year = \App\Models\AcademicYear::where([
            'enterprise_id' => $u->enterprise_id,
            'is_active' => 1
        ])->first();
        $conds = [
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $active_year->id
        ];
        $grid = new Grid(new Subject());
        $grid->disableBatchActions(); 
        $grid->quickSearch('subject_name', 'code')->placeholder('Quick search subject or code');
        $grid
            ->model()
            ->where($conds)
            ->orderBy('id', 'desc');
        $grid->column('subject_name', __('Subject'))->sortable();
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($class) {
                $c = \App\Models\AcademicClass::find($class);
                if ($c == null) {
                    return 'N/A';
                }
                return $c->name_text;
            })->sortable();
        $grid->column('subject_teacher', __('Teacher'))
            ->display(function ($teacher) {
                $t = \App\Models\User::find($teacher);
                if ($t == null) {
                    return 'N/A';
                }
                return $t->name;
            })->sortable();

        $grid->column('code', __('Code'))->sortable()->hide();
        $grid->column('teacher_1', __('Teacher 1'))
            ->display(function ($teacher) {
                $t = \App\Models\User::find($teacher);
                if ($t == null) {
                    return 'N/A';
                }
                return $t->name;
            })->sortable()
            ->hide();
        $grid->column('teacher_2', __('Teacher 2'))
            ->display(function ($teacher) {
                $t = \App\Models\User::find($teacher);
                if ($t == null) {
                    return 'N/A';
                }
                return $t->name;
            })->sortable()
            ->hide();
        $grid->column('teacher_3', __('Teacher 3'))
            ->display(function ($teacher) {
                $t = \App\Models\User::find($teacher);
                if ($t == null) {
                    return 'N/A';
                }
                return $t->name;
            })->sortable()->hide();

        $grid->disableBatchActions();
        $grid->column('primary', __('Primary'))
            ->display(function () {
                $link = url('scheme-of-work-print?id=' . $this->id);
                return "<a href='$link' target='_blank'>Print</a>";
            })->hide();
        //number of items
        $grid->column('items', __('Scheme Items'))
            ->display(function () {
                $u = Admin::user();
                $active = Term::where([
                    'enterprise_id' => $u->enterprise_id,
                    'is_active' => 1
                ])->first();
                if ($active == null) return "Active term not found";

                $items = \App\Models\SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $active->id
                ])->count();
                return $items;
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
        $show = new Show(Subject::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('subject_teacher', __('Subject teacher'));
        $show->field('code', __('Code'));
        $show->field('details', __('Details'));
        $show->field('course_id', __('Course id'));
        $show->field('subject_name', __('Subject name'));
        $show->field('demo_id', __('Demo id'));
        $show->field('is_optional', __('Is optional'));
        $show->field('main_course_id', __('Main course id'));
        $show->field('teacher_1', __('Teacher 1'));
        $show->field('teacher_2', __('Teacher 2'));
        $show->field('teacher_3', __('Teacher 3'));
        $show->field('parent_course_id', __('Parent course id'));
        $show->field('academic_year_id', __('Academic year id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Subject());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->number('academic_class_id', __('Academic class id'));
        $form->number('subject_teacher', __('Subject teacher'));
        $form->textarea('code', __('Code'));
        $form->textarea('details', __('Details'));
        $form->number('course_id', __('Course id'));
        $form->textarea('subject_name', __('Subject name'));
        $form->number('demo_id', __('Demo id'));
        $form->switch('is_optional', __('Is optional'))->default(1);
        $form->number('main_course_id', __('Main course id'))->default(1);
        $form->number('teacher_1', __('Teacher 1'));
        $form->number('teacher_2', __('Teacher 2'));
        $form->number('teacher_3', __('Teacher 3'));
        $form->number('parent_course_id', __('Parent course id'));
        $form->number('academic_year_id', __('Academic year id'));

        return $form;
    }
}
