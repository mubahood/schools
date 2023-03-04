<?php

namespace App\Admin\Controllers;

use App\Models\ParentCourse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ParentCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Main secondary courses';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ParentCourse());
        $grid->disableBatchActions();

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('s1_term1_topics');
        $grid->column('s1_term2_topics')->sortable();
        $grid->column('s1_term3_topics')->sortable();
        $grid->column('s2_term1_topics')->sortable();
        $grid->column('s2_term2_topics')->sortable();
        $grid->column('s2_term3_topics')->sortable();
        $grid->column('s3_term1_topics')->sortable();
        $grid->column('s3_term2_topics')->sortable();
        $grid->column('s3_term3_topics')->sortable();
        $grid->column('s4_term1_topics')->sortable();
        $grid->column('s4_term2_topics')->sortable();
        $grid->column('s4_term3_topics')->sortable();

   
        $grid->column('code', __('Code'))->sortable();

        $grid->column('papers', __('Papers'))->display(function () {
            return count($this->papers);
        });
        $grid->column('is_compulsory', __('Compulsory'))
            ->using([
                1 => 'Yes',
                0 => 'No'
            ])
            ->filter([
                1 => 'Compulsory',
                0 => 'Not Compulsory'
            ])
            ->sortable();
        $grid->column('type', __('Type'));
        $grid->column('is_verified', __('Verified'))
            ->using([
                1 => 'Yes',
                0 => 'No'
            ])
            ->filter([
                1 => 'Verified',
                0 => 'Not Verified'
            ])
            ->sortable();


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
        $show = new Show(ParentCourse::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('code', __('Code'));
        $show->field('type', __('Type'));
        $show->field('is_verified', __('Is verified'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ParentCourse());



        if ($form->isCreating()) {

            $form->text('name', __('Name'));
            $form->select('type', __('Type'))->options([
                'Secondary' => 'Secondary',
                'Advanced' => 'Advanced',
            ])
                ->default('Secondary');
        } else {

            $form->text('name', __('Name'))->readOnly();
            $form->select('type', __('Type'))->options([
                'Secondary' => 'Secondary',
                'Advanced' => 'Advanced',
            ])
                ->readOnly()
                ->default('Secondary');
        }
        $form->text('short_name', __('Short name'));
        $form->text('code', __('Code'));

        $form->radio('is_compulsory', __('Is compulsory?'))
            ->options([
                0 => 'No',
                1 => 'yes',
            ])->rules('required');


        $form->switch('is_verified', __('Is verified'))->default(1);
        
        $form->divider('S.1 Topics');
        $form->textarea('s1_term1_topics');
        $form->textarea('s1_term2_topics');
        $form->textarea('s1_term3_topics');

        $form->divider('S.2 Topics');
        $form->textarea('s2_term1_topics');
        $form->textarea('s2_term2_topics');
        $form->textarea('s2_term3_topics');
        $form->divider('S.3 Topics');
        $form->textarea('s3_term1_topics');
        $form->textarea('s3_term2_topics');
        $form->textarea('s3_term3_topics'); 
        
        $form->divider('S.4 Topics');
        $form->textarea('s4_term1_topics');
        $form->textarea('s4_term2_topics');
        $form->textarea('s4_term3_topics'); 

        /* 
        	 
	
	
	
	
	

        */
        $form->divider('Papers');
        $form->morphMany('papers', 'Click on new to add a paper to this course', function (Form\NestedForm $form) {

            $form->select('paper', __('Paper'))
                ->options([
                    1 => 'Paper 1',
                    2 => 'Paper 2',
                    3 => 'Paper 3',
                    4 => 'Paper 4',
                    5 => 'Paper 5',
                    6 => 'Paper 6',
                ])
                ->rules('required');
            $form->radio('is_compulsory', __('Is compulsory?'))
                ->options([
                    0 => 'No',
                    1 => 'yes',
                ]);
        });
        /* 

Full texts
id	
created_at	
updated_at	
name	
short_name	
code	
subject_type	
parent_course_id Ascending 1	

*/


        return $form;
    }
}
