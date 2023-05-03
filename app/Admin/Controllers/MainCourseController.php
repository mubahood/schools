<?php

namespace App\Admin\Controllers;

use App\Models\MainCourse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MainCourseController extends AdminController
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
        
        $grid = new Grid(new MainCourse());
        $grid->column('id', __('ID'))->sortable(); 
        $grid->disableBatchActions();
        $grid->column('subject_type', __('Category'))->filter([
            'Nursery' => 'Nursery',
            'Primary' => 'Primary',
            'Secondary' => 'O\'level',
            'Advanced' => 'A\'level',
            'Other' => 'Other',
        ]);

        $grid->column('name', __('Subject name'))->sortable();
        $grid->column('code', __('Code'))->sortable();
        $grid->column('papers', __('Papers'))->display(function () {
            return count($this->papers);
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
        $show = new Show(MainCourse::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('code', __('Code'));
 
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MainCourse());

        $form->tab('Subject info', function ($form) {

            $form->radio('subject_type', __('Subject category'))
                ->options([
                    'Nursery' => 'Nursery subject',
                    'Primary' => 'Primary school subject',
                    'Secondary' => 'O\'level subject',
                    'Advanced' => 'For A\'level subject',
                    'Other' => 'Other',
                ])
                ->rules('required');

            $form->text('name', __('Name'))->rules('required');
            $form->text('short_name', __('Short name'))->rules('required');
            $form->text('code', __('Code'))->rules('required');
        });


        $form->tab('Papers', function ($form) {

            $form->html('Click on new to add a paper to this subject');
            $form->morphMany('papers', '', function (Form\NestedForm $form) {
                $form->select('name', 'Paper')
                    ->options([
                        1 => 'Paper 1',
                        2 => 'Paper 2',
                        3 => 'Paper 3',
                        4 => 'Paper 4',
                        5 => 'Paper 5',
                        6 => 'Paper 6',
                    ])
                    ->rules('required');
                $form->radio('is_compulsory', 'Paper choice')
                    ->options([
                        0 => 'Compulsory subject',
                        1 => 'Optional subject',
                    ])->rules('required');
            });
        });



        return $form;
    }
}
