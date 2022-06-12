<?php

namespace App\Admin\Controllers;

use App\Models\Department;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DepartmentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Department';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Department());

        $grid->column('id', __('Id'));
        $grid->column('head_of_department', __('Head of department'));
        $grid->column('name', __('Name'));
        $grid->column('details', __('Details'));

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
        $show = new Show(Department::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('head_of_department', __('Head of department'));
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
        $form = new Form(new Department());
        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }

        $form->text('name', __('Name'))->required();
        $form->select('head_of_department', __('Head of department'))
            ->options($admins)
            ->required();


        $form->textarea('details', __('Department description'))->required();

        return $form;
    }
}
