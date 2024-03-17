<?php

namespace App\Admin\Controllers;

use App\Models\SchemWorkItem;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchemWorkItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Scheme Work Items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchemWorkItem());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('subject_id', __('Subject id'));
        $grid->column('teacher_id', __('Teacher id'));
        $grid->column('supervisor_id', __('Supervisor id'));
        $grid->column('teacher_status', __('Teacher status'));
        $grid->column('teacher_comment', __('Teacher comment'));
        $grid->column('supervisor_status', __('Supervisor status'));
        $grid->column('supervisor_comment', __('Supervisor comment'));
        $grid->column('status', __('Status'));
        $grid->column('week', __('Week'));
        $grid->column('period', __('Period'));
        $grid->column('topic', __('Topic'));
        $grid->column('competence', __('Competence'));
        $grid->column('methods', __('Methods'));
        $grid->column('skills', __('Skills'));
        $grid->column('suggested_activity', __('Suggested activity'));
        $grid->column('instructional_material', __('Instructional material'));
        $grid->column('references', __('References'));

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
        $show = new Show(SchemWorkItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('subject_id', __('Subject id'));
        $show->field('teacher_id', __('Teacher id'));
        $show->field('supervisor_id', __('Supervisor id'));
        $show->field('teacher_status', __('Teacher status'));
        $show->field('teacher_comment', __('Teacher comment'));
        $show->field('supervisor_status', __('Supervisor status'));
        $show->field('supervisor_comment', __('Supervisor comment'));
        $show->field('status', __('Status'));
        $show->field('week', __('Week'));
        $show->field('period', __('Period'));
        $show->field('topic', __('Topic'));
        $show->field('competence', __('Competence'));
        $show->field('methods', __('Methods'));
        $show->field('skills', __('Skills'));
        $show->field('suggested_activity', __('Suggested activity'));
        $show->field('instructional_material', __('Instructional material'));
        $show->field('references', __('References'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchemWorkItem());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);

        $active_term = Admin::user()->ent->active_term();

        $u = User::find(Admin::user()->id);
        $subs = [];
        foreach ($u->my_subjects() as $key => $value) {
            $subs[$value->id] = $value->name;
        }


        $form->select('term_id', __('Due term'))
            ->options(
                \App\Models\Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->get()->pluck('name_text', 'id')
            )
            ->default($active_term->id)
            ->rules('required')
            ->readOnly();

        $form->select('subject_id', __('Subject id'))
            ->options($subs);
        $form->number('teacher_id', __('Teacher id'));
        $form->number('supervisor_id', __('Supervisor id'));
        $form->text('teacher_status', __('Teacher status'))->default('pending');
        $form->textarea('teacher_comment', __('Teacher comment'));
        $form->text('supervisor_status', __('Supervisor status'))->default('pending');
        $form->textarea('supervisor_comment', __('Supervisor comment'));
        $form->text('status', __('Status'))->default('pending');
        $form->number('week', __('Week'))->default(1);
        $form->number('period', __('Period'))->default(1);
        $form->textarea('topic', __('Topic'));
        $form->textarea('competence', __('Competence'));
        $form->textarea('methods', __('Methods'));
        $form->textarea('skills', __('Skills'));
        $form->textarea('suggested_activity', __('Suggested activity'));
        $form->textarea('instructional_material', __('Instructional material'));
        $form->textarea('references', __('References'));

        return $form;
    }
}
