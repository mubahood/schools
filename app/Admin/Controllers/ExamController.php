<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\Exam;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ExamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Exam';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Exam());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('term_id', __('Term id'));
        $grid->column('type', __('Type'));
        $grid->column('name', __('Name'));
        $grid->column('max_mark', __('Max mark'));

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
        $show = new Show(Exam::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('term_id', __('Term id'));
        $show->field('type', __('Type'));
        $show->field('name', __('Name'));
        $show->field('max_mark', __('Max mark'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /* $e = Exam::find(1);
        $e->name .= rand(100000, 1000000000);
        $e->save();
        die("done");  */
        $form = new Form(new Exam());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');

        $form->number('term_id', __('Term id'))->default(1);
        $form->text('type', __('Type'))->default('B.O.T');
        $form->text('name', __('Name'))->default('Begining of term');
        $form->number('max_mark', __('Max mark'))->default(30); 

        $form->multipleSelect('classes')->options(AcademicClass::all()->pluck('name', 'id'));




        return $form;
    }
}
