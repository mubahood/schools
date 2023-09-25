<?php

namespace App\Admin\Controllers;

use App\models\Visitor;
use Encore\Admin\Controllers\AdminController;
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
        $grid->column('updated_at', __('Updated at'))->hide();
        $grid->column('visitors_name', __('Visitors Name'));
        $grid->column('visitors_address', __('Visitors Address'));
        $grid->column('visitors_phone_number', __('Visitors phone number'));
        $grid->column('reason', __('Reason'));
        $grid->column('who_to_see', __('Who to see'));
        $grid->column('relationship', __('Relationship'));
        $grid->column('time_in', __('Time in'));
        $grid->column('time_out', __('Time out'));
        $grid->column('vehicle_number', __('Vehicle Number'));
        $grid->column('students_id', __('Students'));
        $grid->column('employee_id', __('Employee'));
        //  $grid->column('others', __('Others'));
        $grid->column('term', __('Term'));
        $grid->column('enterprise_id', __('Enterprise id'));

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
        $show->field('visitors_name', __('Visitors name'));
        $show->field('visitors_address', __('Visitors address'));
        $show->field('visitors_phone_number', __('Visitors phone number'));
        $show->field('reason', __('Reason'));
        $show->field('who_to_see', __('Who to see'));
        $show->field('relationship', __('Relationship'));
        $show->field('time_in', __('Time in'));
        $show->field('time_out', __('Time out'));
        $show->field('vehicle_number', __('Vehicle number'));
        $show->field('students_id', __('Students id'));
        $show->field('employee_id', __('Employee id'));
        $show->field('others', __('Others'));
        $show->field('term', __('Term'));
        $show->field('enterprise_id', __('Enterprise id'));

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

        $form->text('visitors_name', __('Visitors Name'));
        $form->text('visitors_address', __('Visitors Address'))->icon('fa-home');
        $form->text('visitors_phone_number', __('Visitors Phone Number'))->icon('fa-phone');
        $form->text('reason', __('Reason'));
        $form->radio('who_to_see', __('Who to see'))
            ->options([
                'Student' => 'Student',
                'Employee' => 'Employee',
                'Non' => 'Non',
            ])->when('Student', function (Form $form) {
                $form->number('students_id', __('Students'));
                $form->text('relationship', __('Relationship'));
            })->when('Employee', function (Form $form) {
                $form->number('employee_id', __('Employee'));
            });

        $form->time('time_in', __('Time in'))->default(date('H:i:s'));
        $form->time('time_out', __('Time out'))->default(date('H:i:s'));
        $form->text('vehicle_number', __('Vehicle number'));


        //  $form->text('others', __('Others'));
        $form->select('term', __('Term'))->options([
            'Term 1' => 'Term 1',
            'Term 2' => 'Term 2',
            'Term 3' => 'Term 3',
        ]);
       $form->number('enterprise_id', __('Enterprise id'));

        return $form;
    }
}
