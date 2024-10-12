<?php

namespace App\Admin\Controllers;

use App\Models\SubjectTeacherRemark;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class SubjectTeacherRemarkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Grades';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SubjectTeacherRemark());
        $grid->disableBatchActions();
        $u = Auth::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ])->orderBy('max_score', 'asc');
        $grid->column('comments', __('Comments'))->sortable()->editable(); 
        $grid->column('min_score', __('Min Score'))->sortable()->editable();
        $grid->column('max_score', __('Max score'))->sortable()->editable();

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
        $show = new Show(SubjectTeacherRemark::findOrFail($id));

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
        $form = new Form(new SubjectTeacherRemark());

        $form->decimal('min_score', __('Min score'))->rules('required');
        $form->decimal('max_score', __('Max score'))->rules('required');
        $form->text('comments', __('Grade'))->rules('required')->required();

        /*      $form->html('
        <code>[STUDENT_NAME]</code>
        <code>[HE_SHE]</code>
        <code>[HIM_HER]</code>
        ','Key words');
        $form->textarea('comments', __('Comments'))
            ->help('Enter comments separated by commas. e.g. Excellent, Good, Average, Poor, Very Poor');
 */


        $u = Admin::user();
        $form->hidden('enterprise_id')->default($u->enterprise_id);

        /*   $form->saving(function (Form $form) {
            $form->min_score = round($form->min_score, 2);
            $form->max_score = round($form->max_score, 2);
        }); */

        return $form;
    }
}
