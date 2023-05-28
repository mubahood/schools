<?php

namespace App\Admin\Controllers;

use App\Models\Gen;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GenController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Gen';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Gen());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('class_name', __('Class name'));
        $grid->column('use_db_table', __('Use db table'));
        $grid->column('table_name', __('Table name'));
        $grid->column('fields', __('Fields'));
        $grid->column('file_id', __('File id'));
        $grid->column('gen', __('File'))->display(function () {
            return '<a target="_blank" href="' . url('gen?id=' . $this->id) . '">Generate</a>';
        });
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
        $show = new Show(Gen::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('class_name', __('Class name'));
        $show->field('use_db_table', __('Use db table'));
        $show->field('table_name', __('Table name'));
        $show->field('fields', __('Fields'));
        $show->field('file_id', __('File id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Gen());

        $form->text('class_name', __('Class name'));
        $form->radio('use_db_table', __('Use db table'))->options([
            'Yes' => 'Yes',
            'No' => 'No',
        ]);
        $form->text('table_name', __('Table name'));
        $form->text('end_point', __('end_point'));
        $form->textarea('fields', __('Fields'));
        $form->text('file_id', __('File ID'));

        return $form;
    }
}
