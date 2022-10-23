<?php

namespace App\Admin\Controllers;

use App\Models\GradeRange;
use App\Models\GradingScale;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GradingScaleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Grading scale';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GradingScale()); 

 

        $grid->column('name', __('Name'));
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
        $show = new Show(GradingScale::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('name', __('Name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        /* $range = new GradeRange();
        $range->grading_scale_id = 1;
        $range->name = 'P8';
        $range->min_mark = 50;
        $range->max_mark = 54;
        $range->aggregates = 8;
        $range->save();
        dd("anjane");
 */
        /*         $table->integer('')->default(0);
        $table->integer('max_mark')->default(0);
        $table->integer('aggregates')->default(0);

 */
        $form = new Form(new GradingScale());

        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        $form->text('name', __('Name'))->rules('required');



        $form->morphMany('grade_ranges', 'Click on new to range', function (Form\NestedForm $form) {
            $u = Admin::user();
            $form->hidden('enterprise_id')->default($u->enterprise_id);
            $form->text('name', __('Range name'))->rules('required');
            $form->text('min_mark', __('Minimum mark'))->rules('required');
            $form->text('max_mark', __('Maximum mark'))->rules('required');
            $form->text('aggregates', __('Aggregates/ponits'))->attribute('type', 'number')->rules('required');
        });


        //grading-scales
        return $form;
    }
}
