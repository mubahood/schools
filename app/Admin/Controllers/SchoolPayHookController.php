<?php

namespace App\Admin\Controllers;

use App\Models\SchoolPayHook;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolPayHookController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Pay Hooks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolPayHook());
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'))->sortable();
        $grid->column('post_data', __('Post data'));
        $grid->column('get_data', __('Get data'));
        $grid->column('method', __('Method'));
        $grid->column('server_data', __('Server data'));

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
        $show = new Show(SchoolPayHook::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('post_data', __('Post data'));
        $show->field('get_data', __('Get data'));
        $show->field('method', __('Method'));
        $show->field('server_data', __('Server data'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SchoolPayHook());

        $form->textarea('post_data', __('Post data'));
        $form->textarea('get_data', __('Get data'));
        $form->textarea('method', __('Method'));
        $form->textarea('server_data', __('Server data'));

        return $form;
    }
}
