<?php

namespace App\Admin\Controllers;

use App\Models\Visitor;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VisitorController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Visitor';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Visitor());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('nin', __('Nin'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('email', __('Email'));
        $grid->column('organization', __('Organization'));
        $grid->column('address', __('Address'));

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
        $show = new Show(Visitor::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('nin', __('Nin'));
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('organization', __('Organization'));
        $show->field('address', __('Address'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Visitor());

        $form->text('name', __('Name'));
        $form->text('nin', __('Nin'));
        $form->text('phone_number', __('Phone number'));
        $form->email('email', __('Email'));
        $form->text('organization', __('Organization'));
        $form->text('address', __('Address'));
        $u = Admin::user();
        $form->text('enterprise_id', __('enterprise_id'))->default($u->enterprise_id);

        return $form;
    }
}
