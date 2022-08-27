<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CourseController extends AdminController
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
        $grid = new Grid(new Course());
        $grid->column('short_name', __('CODE'));
        $grid->column('name', __('Course Name'))->sortable();
        $grid->column('details', __('Details'))->hide();

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
        $show = new Show(Course::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Course());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default(1)->rules('required');
        $form->text('name', __('Name'))->rules('required');
        $form->text('short_name', __('Short Name'))->rules('required');
        $form->text('code', __('Subject code'))->rules('required');
        $form->radio('subject_type', __('Subject category'))
            ->options([
                'Primary' => 'Primary school subject',
                'Secondary' => 'O\'level subject',
                'Advanced' => 'For A\'level subject',
            ])
            ->rules('required');
        $form->radio('is_compulsory', __('Subject choice'))
            ->options([
                1 => 'Is compulsory subject',
                0 => 'Is optional subject',
            ])
            ->default(1)
            ->rules('required');

        $form->textarea('details', __('Details'));

        return $form;
    }
}
