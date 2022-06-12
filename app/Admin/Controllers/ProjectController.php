<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Project';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());

        $grid->column('id', __('Id'));
        $grid->column('head_of_project', __('Head of project'));
        $grid->column('department_id', __('#ID'));
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
        $show = new Show(Project::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('head_of_project', __('Head of project'));
        $show->field('department_id', __('Department id'));
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
        $form = new Form(new Project());

        $admins = [];
        foreach (Administrator::all() as $key => $v) {
            $admins[$v->id] = $v->name . " - " . $v->id . " - ({$v->username})";
        }

        $form->text('name', __('Name'))->required();
        $form->text('short_name', __('Short Name'))->required();
        $form->select('head_of_project', __('Head of project'))
            ->options($admins)
            ->required();
        $form->textarea('details', __('Details'))->required();

        return $form;
    }
}
