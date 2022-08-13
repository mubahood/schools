<?php

namespace App\Admin\Controllers;

use App\Models\MenuItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MenuItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Dashboard items';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MenuItem());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('sub_title', __('Sub title'));
        $grid->column('role', __('Role'));
        $grid->column('image', __('Image'));
        $grid->column('link', __('Link'));

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
        $show = new Show(MenuItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('sub_title', __('Sub title'));
        $show->field('role', __('Role'));
        $show->field('image', __('Image'));
        $show->field('link', __('Link'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MenuItem());
        $roleModel = config('admin.database.roles_model');

        $form->text('title', __('Title'))->required();
        $form->text('sub_title', __('Sub title'))->required();
        $form->tags('role', 'Roles')->options($roleModel::pluck('name', 'slug'))->required();
        $form->text('image', __('Image'))->required();
        $form->text('link', __('Link'))->rules('required');

        return $form;
    }
}
