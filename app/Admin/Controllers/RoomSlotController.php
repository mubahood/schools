<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\RoomSlot;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class RoomSlotController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Room Slots';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RoomSlot());

        $grid->quickCreate(function ($form) {

            $ent = Enterprise::find(Admin::user()->enterprise_id); 
            $form->text('name');
            $form->text('enterprise_id')->default($ent->id);

        });

        $grid->column('enterprise_id', __('Enterprise id'));
        $grid->column('building_id', __('Building id'));
        $grid->column('room_id', __('Room id'));
        $grid->column('current_student_id', __('Current student id'));
        $grid->column('name', __('Name'));
        $grid->column('status', __('Status'));

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
        $show = new Show(RoomSlot::findOrFail($id));

        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('id', __('Id'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('building_id', __('Building id'));
        $show->field('room_id', __('Room id'));
        $show->field('current_student_id', __('Current student id'));
        $show->field('name', __('Name'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RoomSlot());

        $form->number('enterprise_id', __('Enterprise id'));
        $form->text('building_id', __('Building id'));
        $form->text('room_id', __('Room id'));
        $form->number('current_student_id', __('Current student id'));
        $form->text('name', __('Name'));
        $form->text('status', __('Status'))->default('Vacant');

        return $form;
    }
}
