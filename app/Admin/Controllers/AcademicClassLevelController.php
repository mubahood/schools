<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClassLevel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AcademicClassLevelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Academic Class Levels';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AcademicClassLevel());
        /* 
        $c = new AcademicClassLevel();
        $c->name = 'Senior six';
        $c->short_name = 'S.6';
        $c->is_final_class = 1;
        $c->category = 'A-Level';
        $c->details = $c->name;
        $c->save();

   
              'Nursery' => 'Nursery',
                'Primary' => 'Primary',
                'Secondary' => 'Secondary',
                'A-Level' => 'A-Level',
        */

        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'Desc');
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('short_name', __('Short name'))->sortable();
        $grid->column('is_final_class', __('Is final class'))
            ->using([
                1 => 'Final class',
                0 => 'Not Final class',
            ])
            ->sortable();
        $grid->column('category', __('Category'))->filter([
            'Nursery' => 'Nursery',
            'Primary' => 'Primary',
            'Secondary' => 'Secondary',
            'A-Level' => 'A-Level',
        ])->sortable();
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
        $show = new Show(AcademicClassLevel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('category', __('Category'));
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
        $form = new Form(new AcademicClassLevel());

        $form->text('name', __('Name'))->rules('required');
        $form->text('short_name', __('Short Name'))->rules('required');
        $form->select('category', __('Category'))
            ->options([
                'Nursery' => 'Nursery',
                'Primary' => 'Primary',
                'Secondary' => 'Secondary',
                'A-Level' => 'A-Level',
            ])->rules('required');
        $form->select('is_final_class', __('Is final class'))
            ->options([
                1 => 'Is final class',
                0 => 'Is not final class',
            ])->rules('required');

        $form->textarea('details', __('Details'));

        return $form;
    }
}
