<?php

namespace App\Admin\Controllers;

use App\Models\ClassTeacherComment;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ClassTeacherCommentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Class Teachers Comments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ClassTeacherComment());

        $grid->disableBatchActions();
        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ])->orderBy('min_score', 'asc');
        $grid->column('min_score', __('Min Score'))->sortable()->editable();
        $grid->column('max_score', __('Max score'))->sortable()->editable();
        $grid->column('comments', __('Comments'))->limit(100)->sortable();
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
        $show = new Show(ClassTeacherComment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('min_score', __('Min score'));
        $show->field('max_score', __('Max score'));
        $show->field('comments', __('Comments'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ClassTeacherComment());


        $form->decimal('min_score', __('Min score'))->rules('required');
        $form->decimal('max_score', __('Max score'))->rules('required');

        $form->html('
        <code>[STUDENT_NAME]</code>
        <code>[HE_SHE]</code>
        <code>[HIM_HER]</code>
        ', 'Key words');
        $form->textarea('comments', __('Comments'))
            ->help('Enter comments separated by commas. e.g. Excellent, Good, Average, Poor, Very Poor');



        $u = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id);

        $form->saving(function (Form $form) {
            $form->min_score = round($form->min_score, 2);
            $form->max_score = round($form->max_score, 2);
        });

        return $form;
    }
}
